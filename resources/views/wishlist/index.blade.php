@extends('layouts.app')

@section('title', 'পছন্দের তালিকা')

@section('content')
    <h1 class="text-2xl md:text-3xl font-bold text-center mb-6">❤️ আমার পছন্দের তালিকা</h1>

    <div id="wl-empty" class="hidden max-w-md mx-auto bg-white rounded-xl shadow-sm border p-8 text-center">
        <p class="text-gray-600 mb-4">আপনার পছন্দের তালিকা এখনো খালি।</p>
        <a href="{{ route('home') }}" class="inline-block brand-bg text-white font-semibold px-6 py-2 rounded-full">কেনাকাটা শুরু করুন</a>
    </div>

    <div id="wl-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-4"></div>
@endsection

@push('scripts')
<script>
    (function () {
        const grid = document.getElementById('wl-grid');
        const empty = document.getElementById('wl-empty');

        function checkEmpty() { empty.classList.toggle('hidden', grid.children.length > 0); }

        async function load() {
            const ids = window.Wishlist.read();
            if (!ids.length) { checkEmpty(); return; }
            try {
                const res = await fetch('{{ route('wishlist.items') }}?ids=' + ids.join(','), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                grid.innerHTML = await res.text();
                // আর পাওয়া যায় না (ডিলিট/নিষ্ক্রিয়) এমন আইডি পরিষ্কার করি
                const found = [...grid.querySelectorAll('.wl-card')].map(c => +c.dataset.id);
                window.Wishlist.write(ids.filter(id => found.includes(id)));
                document.querySelectorAll('.wl-btn').forEach(b => b.classList.add('active'));
                document.querySelectorAll('[data-wl-count]').forEach(el => el.textContent = found.length);
            } catch (e) {}
            checkEmpty();
        }

        // হার্ট চাপলে কার্ড তালিকা থেকে সরে যাবে
        document.addEventListener('wishlist:change', function (e) {
            grid.querySelectorAll('.wl-card').forEach(c => { if (!e.detail.ids.includes(+c.dataset.id)) c.remove(); });
            checkEmpty();
        });

        window.Wishlist.ready.then(load);
    })();
</script>
@endpush
