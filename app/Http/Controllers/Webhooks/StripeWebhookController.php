<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Notifications\PackagePurchasedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');

        if (! $secret) {
            return response()->json(['message' => 'Stripe webhook secret not configured.'], 202);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException|\UnexpectedValueException $exception) {
            return response()->json(['message' => 'Invalid Stripe webhook signature.'], 400);
        }

        $type = $event->type;
        $object = $event->data->object;

        if ($type === 'checkout.session.completed') {
            $userId = (int) ($object->metadata->user_id ?? 0);
            $packageId = (int) ($object->metadata->package_id ?? 0);
            $user = $userId ? User::find($userId) : null;
            $package = $packageId ? Package::find($packageId) : null;

            if ($user) {
                $user->forceFill([
                    'stripe_customer_id' => $object->customer ?? $user->stripe_customer_id,
                    'current_plan_id' => $package?->id ?? $user->current_plan_id,
                    'status' => 'active',
                ])->save();

                if ($package) {
                    $user->notify(new PackagePurchasedNotification($package, route('login')));
                }
            }
        }

        if ($type === 'customer.subscription.deleted') {
            $customerId = $object->customer ?? null;
            if ($customerId) {
                User::where('stripe_customer_id', $customerId)->update(['current_plan_id' => null]);
            }
        }

        return response()->json(['received' => true]);
    }
}
