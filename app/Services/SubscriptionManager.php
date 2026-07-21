<?php

namespace App\Services;

use App\Models\AgentLeadQuota;
use App\Models\AgentSubscription;
use App\Models\Package;
use App\Models\SubscriptionHistory;
use App\Models\User;
use App\Support\PlanCapabilities;
use Illuminate\Support\Facades\DB;

/**
 * The one place subscriptions are mutated. Keeps the four moving parts in sync
 * for every assign / upgrade / downgrade / cancel / reactivate:
 *
 *   1. users.current_plan_id      — the effective plan (drives capabilities)
 *   2. agent_subscriptions        — billing/status record (latest active)
 *   3. agent_lead_quotas          — current-month lead allotment
 *   4. subscription_history       — immutable audit trail
 *
 * Nothing else in the app should write these tables directly.
 */
class SubscriptionManager
{
    /**
     * Relative rank of lead plans, used to classify a switch as an upgrade or
     * downgrade. VA plans are unranked (cross-category switch = "changed").
     */
    private const LEAD_RANK = [
        'starter-leads' => 1,
        'growth-leads' => 2,
        'elite-leads' => 3,
    ];

    /**
     * Assign or switch a user's package. Auto-classifies the change as
     * assigned / upgraded / downgraded / changed and records history.
     */
    public function changePlan(
        User $user,
        Package $newPackage,
        string $performedBy = 'admin',
        ?User $actor = null,
        ?string $reference = null
    ): AgentSubscription {
        $oldPackage = $user->currentPlan; // effective plan before the change
        $action = $this->classify($oldPackage, $newPackage);

        return DB::transaction(function () use ($user, $newPackage, $oldPackage, $action, $performedBy, $actor, $reference) {
            $this->deactivateActive($user, 'superseded');

            $subscription = $this->activate($user, $newPackage, $performedBy, $reference);

            $this->syncQuota($user, $newPackage);

            $this->log($user, $subscription, $oldPackage, $newPackage, $action, $performedBy, $actor);

            return $subscription;
        });
    }

    /**
     * Cancel the active subscription. Removes plan capabilities immediately
     * (current_plan_id nulled) while preserving the historical record.
     */
    public function cancel(User $user, string $performedBy = 'admin', ?User $actor = null): void
    {
        $oldPackage = $user->currentPlan;

        DB::transaction(function () use ($user, $oldPackage, $performedBy, $actor) {
            $subscription = $user->activeAgentSubscription;

            if ($subscription) {
                $subscription->update([
                    'is_active' => false,
                    'payment_status' => 'cancelled',
                    'ends_at' => $subscription->ends_at ?? now(),
                ]);
            }

            $user->update(['current_plan_id' => null]);

            $this->log($user, $subscription, $oldPackage, null, 'cancelled', $performedBy, $actor);
        });
    }

    /**
     * Reactivate a cancelled/expired subscription. Restores the given package,
     * or the most recently held package when none is supplied.
     */
    public function reactivate(
        User $user,
        ?Package $package = null,
        string $performedBy = 'admin',
        ?User $actor = null
    ): ?AgentSubscription {
        $package ??= $user->agentSubscription?->package; // latest of any status

        if (! $package) {
            return null;
        }

        return DB::transaction(function () use ($user, $package, $performedBy, $actor) {
            $this->deactivateActive($user, 'superseded');

            $subscription = $this->activate($user, $package, $performedBy);

            $this->syncQuota($user, $package);

            $this->log($user, $subscription, null, $package, 'reactivated', $performedBy, $actor);

            return $subscription;
        });
    }

    /**
     * Deactivate whatever subscription is currently active (without touching
     * current_plan_id — callers decide the effective plan).
     */
    private function deactivateActive(User $user, string $status): void
    {
        $active = $user->activeAgentSubscription;
        if ($active) {
            $active->update(['is_active' => false, 'payment_status' => $status]);
        }
    }

    /**
     * Create a fresh active, paid subscription and make it the effective plan.
     */
    private function activate(User $user, Package $package, string $performedBy, ?string $reference = null): AgentSubscription
    {
        $subscription = AgentSubscription::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'payment_status' => 'paid',
            'payment_provider' => $performedBy,
            'payment_reference' => $reference ?? $this->reference($performedBy, $user, $package),
            'payment_amount' => $package->preferredCheckoutAmount(),
            'starts_at' => now(),
            'ends_at' => $package->billing_type === 'yearly' ? now()->addYear() : null,
            'is_active' => true,
        ]);

        $user->forceFill(['current_plan_id' => $package->id])->save();
        $user->setRelation('currentPlan', $package);
        $user->unsetRelation('activeAgentSubscription');

        return $subscription;
    }

    private function syncQuota(User $user, Package $package): void
    {
        AgentLeadQuota::updateOrCreate(
            ['user_id' => $user->id, 'month' => now()->format('Y-m')],
            [
                'package_id' => $package->id,
                'monthly_quota' => $package->monthly_lead_quota ?? 0,
                'remaining_count' => $package->monthly_lead_quota ?? 0,
                'overdue_count' => 0,
            ]
        );
    }

    private function log(
        User $user,
        ?AgentSubscription $subscription,
        ?Package $from,
        ?Package $to,
        string $action,
        string $performedBy,
        ?User $actor
    ): void {
        SubscriptionHistory::create([
            'user_id' => $user->id,
            'agent_subscription_id' => $subscription?->id,
            'from_package_id' => $from?->id,
            'to_package_id' => $to?->id,
            'action' => $action,
            'performed_by' => $performedBy,
            'performed_by_user_id' => $actor?->id,
            'note' => trim(sprintf(
                '%s → %s',
                $from ? PlanCapabilities::label($from->slug) : 'No Plan',
                $to ? PlanCapabilities::label($to->slug) : 'No Plan'
            )),
        ]);
    }

    /**
     * Classify a switch relative to the previous plan.
     */
    private function classify(?Package $old, Package $new): string
    {
        if (! $old) {
            return 'assigned';
        }

        if ($old->id === $new->id) {
            return 'reactivated';
        }

        $oldRank = self::LEAD_RANK[PlanCapabilities::canonicalize($old->slug)] ?? null;
        $newRank = self::LEAD_RANK[PlanCapabilities::canonicalize($new->slug)] ?? null;

        if ($oldRank !== null && $newRank !== null) {
            return match (true) {
                $newRank > $oldRank => 'upgraded',
                $newRank < $oldRank => 'downgraded',
                default => 'changed',
            };
        }

        return 'changed';
    }

    private function reference(string $performedBy, User $user, Package $package): string
    {
        $prefix = strtoupper(str_replace(['-', ' ', '_'], '', $performedBy)) ?: 'SYS';

        return sprintf('%s-PLAN-%d-%d-%d', $prefix, $user->id, $package->id, now()->timestamp);
    }
}
