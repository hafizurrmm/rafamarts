@php
    $end = null;
    try { $end = !empty($data['end_at']) ? \Carbon\Carbon::parse($data['end_at'], 'Asia/Dhaka') : null; } catch (\Throwable $e) {}
    $url = \App\Support\PageBlocks::safeUrl($data['button_url'] ?? null);
    $style = $data['style'] ?? 'dark';
    $wrap = ['dark' => 'brand-bg-secondary text-white', 'brand' => 'brand-bg text-white', 'light' => 'bg-white text-gray-800 border border-gray-200'][$style] ?? 'brand-bg-secondary text-white';
    $box = $style === 'light' ? 'bg-gray-100' : 'bg-black/20';
@endphp
@if($end)
    <section class="rounded-2xl p-6 md:p-10 text-center {{ $wrap }}"
             data-countdown="{{ $end->toIso8601String() }}"
             data-expired="{{ $data['expired_text'] ?? 'অফারটি শেষ হয়ে গেছে।' }}">
        @if(!empty($data['heading']))
            <h2 class="text-2xl md:text-4xl font-extrabold">{{ $data['heading'] }}</h2>
        @endif
        @if(!empty($data['subheading']))
            <p class="mt-2 text-base md:text-lg opacity-90">{{ $data['subheading'] }}</p>
        @endif

        <div class="flex justify-center gap-2.5 md:gap-5 my-5 md:my-6" data-cd-boxes>
            @foreach(['days' => 'দিন', 'hours' => 'ঘণ্টা', 'minutes' => 'মিনিট', 'seconds' => 'সেকেন্ড'] as $unit => $label)
                <div class="{{ $box }} rounded-xl px-3 py-3 md:px-5 min-w-[4.2rem] md:min-w-[5.5rem]">
                    <div class="text-3xl md:text-5xl font-extrabold tabular-nums" data-cd="{{ $unit }}">00</div>
                    <div class="text-xs md:text-sm opacity-80 mt-1">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        <p data-cd-expired class="hidden text-lg md:text-xl font-semibold my-5"></p>

        @if(!empty($data['button_text']) && $url)
            <a href="{{ $url }}" class="inline-block bg-white text-gray-900 font-bold px-7 py-3 rounded-full hover:opacity-90 shadow">{{ $data['button_text'] }}</a>
        @endif
    </section>

    @pushOnce('scripts', 'countdown-js')
        <script>
            (function () {
                document.querySelectorAll('[data-countdown]').forEach(function (el) {
                    var end = new Date(el.dataset.countdown).getTime();
                    var boxes = el.querySelector('[data-cd-boxes]');
                    var expired = el.querySelector('[data-cd-expired]');
                    var timer = null;

                    function set(unit, value) {
                        var node = el.querySelector('[data-cd="' + unit + '"]');
                        if (node) node.textContent = String(value).padStart(2, '0');
                    }

                    function tick() {
                        var diff = Math.floor((end - Date.now()) / 1000);

                        if (isNaN(diff) || diff <= 0) {
                            boxes.classList.add('hidden');
                            expired.textContent = el.dataset.expired;
                            expired.classList.remove('hidden');
                            if (timer) clearInterval(timer);
                            return;
                        }

                        set('days', Math.floor(diff / 86400));
                        set('hours', Math.floor((diff % 86400) / 3600));
                        set('minutes', Math.floor((diff % 3600) / 60));
                        set('seconds', diff % 60);
                    }

                    tick();
                    timer = setInterval(tick, 1000);
                });
            })();
        </script>
    @endPushOnce
@endif
