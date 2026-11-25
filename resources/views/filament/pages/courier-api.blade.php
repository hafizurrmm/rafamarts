<x-filament-panels::page>
    <style>
        .cr-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; align-items: start; }
        @media (max-width: 900px) { .cr-grid { grid-template-columns: 1fr; } }
        .cr-card { background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 1rem; padding: 1.25rem; }
        .dark .cr-card { background: #18181b; border-color: rgba(255,255,255,.1); }
        .cr-head { display: flex; align-items: center; gap: .85rem; margin-bottom: 1rem; }
        .cr-logo { width: 2.75rem; height: 2.75rem; border-radius: .75rem; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .cr-name { font-weight: 700; font-size: 1.05rem; }
        .cr-badge { display: inline-block; margin-top: .15rem; font-size: .7rem; padding: .1rem .5rem; border-radius: 999px; }
        .cr-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .85rem; }
        .cr-fields .full { grid-column: 1 / -1; }
        .cr-fields label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: .3rem; }
        .cr-actions { display: flex; gap: .6rem; margin-top: 1rem; }
    </style>

    @php $ls = $this->lastSync(); @endphp
    <div class="cr-card" style="margin-bottom:1.25rem">
        <div class="cr-head">
            <div class="cr-logo" style="background:#0f766e">↻</div>
            <div>
                <div class="cr-name">কুরিয়ার স্ট্যাটাস অটো-সিঙ্ক</div>
                <span class="cr-badge" style="color:{{ $ls['stale'] ? '#b45309' : '#047857' }};background:{{ $ls['stale'] ? '#fef3c7' : '#d1fae5' }}">
                    {{ $ls['text'] ? 'শেষ সিঙ্ক: ' . $ls['text'] : 'এখনো সিঙ্ক হয়নি' }}
                </span>
            </div>
        </div>
        <div style="display:grid;gap:.6rem">
            <label style="display:flex;align-items:center;gap:.6rem;font-weight:600;font-size:.9rem">
                <x-filament::input.checkbox wire:model="data.courier_auto_sync" />
                <span>প্রতি ১৫ মিনিটে চলমান অর্ডারের কুরিয়ার স্ট্যাটাস নিজে থেকে আপডেট করুন</span>
            </label>
            <label style="display:flex;align-items:center;gap:.6rem;font-weight:600;font-size:.9rem">
                <x-filament::input.checkbox wire:model="data.courier_sync_update_order" />
                <span>কুরিয়ার Delivered হলে অর্ডার Completed, Returned হলে অর্ডার Returned করুন</span>
            </label>
        </div>
        <div class="cr-actions">
            <x-filament::button color="success" icon="heroicon-o-bookmark-square" wire:click="saveSync">Save</x-filament::button>
            <x-filament::button color="gray" icon="heroicon-o-arrow-path" wire:click="syncNow" wire:loading.attr="disabled" wire:target="syncNow">এখনই সিঙ্ক করুন</x-filament::button>
        </div>
        <details style="margin-top:.9rem;font-size:.82rem">
            <summary style="cursor:pointer;font-weight:600">স্বয়ংক্রিয়ভাবে চালাতে একবার Cron সেট করুন (cPanel → Cron Jobs)</summary>
            <p style="margin:.5rem 0">সময়: প্রতি মিনিট (<code>* * * * *</code>), কমান্ড:</p>
            <code style="display:block;padding:.5rem .7rem;background:rgba(0,0,0,.05);border-radius:.5rem;word-break:break-all">/usr/local/bin/ea-php{{ PHP_MAJOR_VERSION }}{{ PHP_MINOR_VERSION }} {{ base_path('artisan') }} schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
            <p style="margin:.5rem 0 0;opacity:.75">শুধু শেষ ৪৫ দিনের Pending/Processing/On Hold অর্ডার চেক হয়, প্রতি রানে সর্বোচ্চ ৫০টি। উপরের "শেষ সিঙ্ক" হলুদ থাকলে Cron চলছে না।</p>
        </details>
    </div>

    <div class="cr-grid">
        @foreach(\App\Filament\Pages\CourierApi::couriers() as $id => $c)
            @php [$label, $fg, $bg] = $this->statusFor($id); @endphp
            <div class="cr-card">
                <div class="cr-head">
                    <div class="cr-logo" style="background: {{ $c['color'] }}">{{ $c['badge'] }}</div>
                    <div>
                        <div class="cr-name">{{ $c['name'] }}</div>
                        <span class="cr-badge" style="color: {{ $fg }}; background: {{ $bg }}">{{ $label }}</span>
                    </div>
                </div>

                <div class="cr-fields">
                    @foreach($c['fields'] as $key => $f)
                        @php [$flabel, $type, $placeholder, $width] = $f; @endphp
                        <div class="{{ $width === 'full' ? 'full' : '' }}">
                            <label>{{ $flabel }}</label>
                            @if($type === 'select')
                                <x-filament::input.wrapper>
                                    <x-filament::input.select wire:model="data.{{ $key }}">
                                        @foreach($f[4] as $val => $text)
                                            <option value="{{ $val }}">{{ $text }}</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            @else
                                <x-filament::input.wrapper>
                                    <x-filament::input :type="$type === 'secret' ? 'password' : 'text'" :placeholder="$placeholder" wire:model="data.{{ $key }}" autocomplete="off" />
                                </x-filament::input.wrapper>
                            @endif
                        </div>
                    @endforeach
                </div>

                <label style="display:flex;align-items:center;gap:.6rem;margin-top:1rem;font-weight:600;font-size:.9rem">
                    <x-filament::input.checkbox wire:model="data.{{ $c['enabled'] }}" />
                    <span>চালু করুন (Active)</span>
                </label>

                <div class="cr-actions">
                    <x-filament::button color="success" icon="heroicon-o-bookmark-square" wire:click="save('{{ $id }}')">Save</x-filament::button>
                    <x-filament::button color="gray" icon="heroicon-o-trash" wire:click="clear('{{ $id }}')" wire:confirm="{{ $c['name'] }} এর সব তথ্য মুছে ফেলবেন?">Clear</x-filament::button>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
