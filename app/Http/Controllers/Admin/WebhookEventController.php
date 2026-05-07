<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookEventController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('webhook_events.view'), 403);

        $filters = [
            'provider' => (string) $request->query('provider', ''),
            'event' => trim((string) $request->query('event', '')),
            'processed' => (string) $request->query('processed', ''),
        ];

        $query = WebhookEvent::query()->latest('id');

        if ($filters['provider'] !== '') {
            $query->where('provider', $filters['provider']);
        }

        if ($filters['event'] !== '') {
            $query->where('event', 'like', '%' . $filters['event'] . '%');
        }

        if ($filters['processed'] === '1') {
            $query->whereNotNull('processed_at');
        } elseif ($filters['processed'] === '0') {
            $query->whereNull('processed_at');
        }

        $events = $query->paginate(30)->withQueryString();

        return view('pages.admin.webhook-events.index', [
            'events' => $events,
            'filters' => $filters,
            'providers' => WebhookEvent::query()->select('provider')->distinct()->orderBy('provider')->pluck('provider'),
            'meta' => [
                'title' => 'Webhook Events | OmniReferral',
                'description' => 'Monitor inbound webhook events and payloads for Stripe and GoHighLevel.',
            ],
        ]);
    }

    public function show(Request $request, WebhookEvent $webhookEvent): View
    {
        abort_unless($request->user()?->can('webhook_events.view'), 403);

        return view('pages.admin.webhook-events.show', [
            'event' => $webhookEvent,
            'meta' => [
                'title' => 'Webhook Event #' . $webhookEvent->id . ' | OmniReferral',
            ],
        ]);
    }
}

