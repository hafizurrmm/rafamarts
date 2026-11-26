<x-filament-panels::page>
    <style>
        .ml-card { background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 1rem; padding: 1.25rem; }
        .dark .ml-card { background: #18181b; border-color: rgba(255,255,255,.1); }
        .ml-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(10.5rem, 1fr)); gap: 1rem; }
        .ml-item { border: 1px solid rgba(0,0,0,.08); border-radius: .85rem; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
        .dark .ml-item { background: #18181b; border-color: rgba(255,255,255,.1); }
        .ml-thumb { aspect-ratio: 1 / 1; width: 100%; object-fit: cover; background: #f1f5f9; display: block; }
        .ml-meta { padding: .5rem .6rem 0; font-size: .72rem; line-height: 1.3; }
        .ml-name { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ml-sub { opacity: .6; }
        .ml-actions { display: flex; gap: .4rem; padding: .5rem .6rem .6rem; }
        .ml-btn { flex: 1; font-size: .72rem; font-weight: 600; padding: .35rem .4rem; border-radius: .5rem; border: 1px solid rgba(0,0,0,.12); background: transparent; cursor: pointer; text-align: center; }
        .ml-btn:hover { background: rgba(0,0,0,.05); }
        .ml-btn.del { flex: 0 0 auto; color: #dc2626; border-color: rgba(220,38,38,.35); }
        .ml-search { width: 100%; max-width: 22rem; padding: .5rem .8rem; border-radius: .6rem; border: 1px solid rgba(0,0,0,.15); background: transparent; }
    </style>

    <div class="ml-card">
        <form wire:submit="upload">
            {{ $this->form }}
            <div style="margin-top:1rem">
                <x-filament::button type="submit" color="success" icon="heroicon-o-arrow-up-tray" wire:loading.attr="disabled" wire:target="upload">আপলোড করুন</x-filament::button>
                <span style="font-size:.8rem;opacity:.65;margin-left:.6rem">JPG, PNG, WebP, GIF — প্রতিটি সর্বোচ্চ ৫ MB</span>
            </div>
        </form>
    </div>

    @php $lib = $this->files(); @endphp

    <div class="ml-card">
        <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between;margin-bottom:1rem">
            <div style="font-weight:700">সব ছবি <span style="opacity:.6;font-weight:500">({{ $lib['total'] }})</span></div>
            <input type="search" class="ml-search" placeholder="ফাইলের নাম দিয়ে খুঁজুন…" wire:model.live.debounce.400ms="search">
        </div>

        @if(empty($lib['items']))
            <div style="text-align:center;padding:2.5rem 0;opacity:.6">এখনো কোনো ছবি নেই। উপরে থেকে আপলোড করুন।</div>
        @else
            <div class="ml-grid">
                @foreach($lib['items'] as $f)
                    <div class="ml-item" wire:key="m-{{ $f['name'] }}">
                        <a href="{{ $f['url'] }}" target="_blank" rel="noopener"><img class="ml-thumb" src="{{ $f['url'] }}" alt="{{ $f['name'] }}" loading="lazy"></a>
                        <div class="ml-meta">
                            <div class="ml-name" title="{{ $f['name'] }}">{{ $f['name'] }}</div>
                            <div class="ml-sub">{{ number_format($f['size'] / 1024, 0) }} KB · {{ \Carbon\Carbon::createFromTimestamp($f['time'])->format('d M Y') }}</div>
                        </div>
                        <div class="ml-actions" x-data="{ ok: false }">
                            <button type="button" class="ml-btn"
                                x-on:click="navigator.clipboard.writeText(@js($f['url'])).then(() => { ok = true; setTimeout(() => ok = false, 1500) })"
                                x-text="ok ? 'কপি হয়েছে ✓' : 'লিংক কপি'">লিংক কপি</button>
                            <button type="button" class="ml-btn del" title="মুছুন"
                                wire:click="delete(@js($f['path']))"
                                wire:confirm="এই ছবিটি মুছে ফেলবেন? যেখানে এটা ব্যবহার করা আছে সেখানে ছবি দেখা যাবে না।">🗑</button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($lib['total'] > count($lib['items']))
                <div style="text-align:center;margin-top:1.25rem">
                    <x-filament::button color="gray" wire:click="loadMore">আরও দেখান</x-filament::button>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
