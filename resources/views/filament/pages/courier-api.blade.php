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
