@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . \App\Models\Setting::get('site_name', config('shop.name')))
@section('meta_description', $page->meta_description)
@section('meta_image', \App\Support\PageBlocks::img($page->og_image))

@section('content')
    <style>
        /* রিচ টেক্সট ব্লকের স্টাইল (Tailwind CDN এ typography প্লাগইন নেই, তাই হাতে লেখা) */
        .rich-content { line-height: 1.8; }
        .rich-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.25rem 0 .5rem; }
        .rich-content h3 { font-size: 1.2rem; font-weight: 700; margin: 1rem 0 .4rem; }
        .rich-content p { margin: .6rem 0; }
        .rich-content ul { list-style: disc; padding-left: 1.5rem; margin: .6rem 0; }
        .rich-content ol { list-style: decimal; padding-left: 1.5rem; margin: .6rem 0; }
        .rich-content a { color: var(--brand-primary); text-decoration: underline; }
        .rich-content blockquote { border-left: 4px solid var(--brand-primary); padding: .25rem 1rem; color: #4b5563; margin: .8rem 0; }
    </style>

    @if(!$page->is_published)
        <div class="mb-4 rounded bg-yellow-100 text-yellow-800 px-4 py-2 text-sm">
            ⚠️ এটা ড্রাফট পেজ, শুধু আপনি (অ্যাডমিন) দেখতে পাচ্ছেন। সাধারণ ভিজিটর ৪০৪ দেখবে। পাবলিশ করতে অ্যাডমিন থেকে "পাবলিশ করুন" অন করুন।
        </div>
    @endif

    @if($page->show_title)
        <h1 class="text-2xl md:text-3xl font-bold mb-5 text-center">{{ $page->title }}</h1>
    @endif

    <div class="space-y-6 md:space-y-8">
        @foreach(array_values($page->content ?? []) as $block)
            @php $type = preg_replace('/[^a-z_]/', '', (string) ($block['type'] ?? '')); @endphp
            @includeIf('pages.blocks.' . $type, ['data' => $block['data'] ?? []])
        @endforeach
    </div>
@endsection
