<x-filament-widgets::widget>
    <style>
        .os-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem; }
        @media (max-width: 900px) { .os-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .os-card { display: flex; flex-direction: column; gap: .75rem; padding: 1rem 1.1rem; border-radius: .9rem; background: #fff;
                   border: 1px solid rgba(0,0,0,.07); box-shadow: 0 1px 2px rgba(0,0,0,.04); text-decoration: none; transition: box-shadow .15s, transform .15s; }
        .os-card:hover { box-shadow: 0 6px 16px rgba(0,0,0,.08); transform: translateY(-1px); }
        .os-card.is-active { outline: 2px solid currentColor; }
        .os-ico { width: 2.25rem; height: 2.25rem; border-radius: .6rem; display: flex; align-items: center; justify-content: center; }
        .os-ico svg { width: 1.2rem; height: 1.2rem; }
        .os-num { font-size: 1.6rem; font-weight: 700; line-height: 1; color: #111827; }
        .os-lbl { font-size: .82rem; color: #6b7280; margin-top: .25rem; }
        .dark .os-card { background: #18181b; border-color: rgba(255,255,255,.1); }
        .dark .os-num { color: #fff; }
    </style>

    <div class="os-grid">
        @foreach($cards as $c)
            <a href="{{ $c['url'] }}" class="os-card {{ $c['active'] ? 'is-active' : '' }}" style="color: {{ $c['color'] }}">
                <span class="os-ico" style="background: {{ $c['tint'] }}; color: {{ $c['color'] }}">
                    <x-filament::icon :icon="$c['icon']" />
                </span>
                <span>
                    <div class="os-num">{{ $c['count'] }}</div>
                    <div class="os-lbl">{{ $c['label'] }}</div>
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
