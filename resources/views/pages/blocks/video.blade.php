@php $embed = \App\Support\PageBlocks::youtubeEmbed($data['youtube_url'] ?? null); @endphp
@if($embed)
    <section class="max-w-4xl mx-auto w-full">
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="aspect-video rounded-2xl overflow-hidden bg-black">
            <iframe src="{{ $embed }}" class="w-full h-full" loading="lazy" title="{{ $data['heading'] ?? 'ভিডিও' }}"
                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </section>
@endif
