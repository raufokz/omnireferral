<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadFollowUpReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class LeadFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:follow-up-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send follow-up reminder notifications for stale leads';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $leads = Lead::dueForFollowUp()->with('assignedAgent', 'activities')->get();

        if ($leads->isEmpty()) {
            $this->info('No follow-up leads due.');
            return 0;
        }

        foreach ($leads as $lead) {
            $assigned = $lead->assignedAgent;

            // Log activity
            $lead->activities()->create([
                'user_id' => $assigned?->id,
                'type' => 'reminder',
                'value' => now()->toDateTimeString(),
                'content' => 'Automated follow-up reminder triggered',
                'due_at' => now()->addDay(),
            ]);

            // Notify assigned agent
            if ($assigned) {
                $assigned->notify(new LeadFollowUpReminderNotification($lead));
            }

            // Notify admins & staff
            $admins = User::whereIn('role', ['admin', 'staff'])->get();
            Notification::send($admins, new LeadFollowUpReminderNotification($lead));

            $this->info('Reminder sent for Lead #' . $lead->lead_number . ' to agent: ' . ($assigned?->name ?? 'unassigned'));
        }

        $this->info('Total reminders sent: ' . $leads->count());
        return 0;
    }
}
