<?php

namespace App\Jobs;

use App\Services\FacebookConversionsService;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Dispatched with ->afterResponse() from controllers, e.g.:
 *
 *   SendFacebookConversionEvent::dispatch($name, $id, $customData, $userData, $url)->afterResponse();
 *
 * ->afterResponse() runs this AFTER the page has already been sent to the visitor's
 * browser (Laravel flushes the HTTP response, then runs this in the same process
 * during termination) — so the ~100-300ms round-trip to graph.facebook.com never
 * slows down a page load or checkout submission. No queue worker is required for
 * this to work. If you later add `implements \Illuminate\Contracts\Queue\ShouldQueue`
 * here and configure a real queue (QUEUE_CONNECTION=database + a cron-driven
 * `queue:work --stop-when-empty`), failed sends also get automatic retries —
 * worth doing once traffic is high enough that an occasional dropped event matters.
 */
class SendFacebookConversionEvent
{
    use Dispatchable;

    public function __construct(
        public string $eventName,
        public string $eventId,
        public array $customData,
        public array $userData,
        public string $eventSourceUrl,
    ) {
    }

    public function handle(FacebookConversionsService $service): void
    {
        $service->sendEvent($this->eventName, $this->eventId, $this->customData, $this->userData, $this->eventSourceUrl);
    }
}
