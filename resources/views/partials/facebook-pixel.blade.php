@php
    $fbEnabled = \App\Models\Setting::get('fb_enabled', false);
    $fbPixelId = \App\Models\Setting::get('fb_pixel_id');
    // pull() reads AND removes in one call, so an AddToCart event flashed by the
    // previous request (see CartController@add) fires exactly once, on the very
    // next page load, then never again.
    $fbPendingEvents = session()->pull('fb_pending_events', []);
@endphp

@if($fbEnabled && $fbPixelId)
    {{-- Meta Pixel base code --}}
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $fbPixelId }}');
        fbq('track', 'PageView');

        {{-- Events carried over from the previous request (e.g. "Add to Cart" was a form
             POST + redirect, so the pixel fires here, on the page the browser lands on,
             using the SAME event_id the server already sent to the Conversions API —
             Meta dedupes the two into one higher-quality event. --}}
        @foreach($fbPendingEvents as $event)
            fbq('track', '{{ $event['name'] }}', {!! json_encode($event['data'] ?? []) !!}, {eventID: '{{ $event['event_id'] }}'});
        @endforeach

        {{-- Page-specific events (ViewContent, InitiateCheckout, Purchase) pushed by the view itself --}}
        @stack('fb_page_events')
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
             src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1" alt="">
    </noscript>
@endif
