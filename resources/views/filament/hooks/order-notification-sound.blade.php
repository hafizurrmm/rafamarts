{{-- নতুন অর্ডারের নোটিফিকেশন এলে অ্যাডমিন প্যানেলে বিপ সাউন্ড বাজায়।
     কোনো সাউন্ড ফাইল লাগে না — ব্রাউজারের WebAudio দিয়েই টোন বানানো হয়। --}}
<script>
(function () {
    let ctx = null;
    let lastCount = null;

    // ব্রাউজার পলিসি: ইউজার একবার পেজে ক্লিক/কী-প্রেস না করলে সাউন্ড বাজতে দেয় না।
    function unlock() {
        try {
            ctx = ctx || new (window.AudioContext || window.webkitAudioContext)();
            if (ctx.state === 'suspended') ctx.resume();
        } catch (e) {}
    }
    ['click', 'keydown', 'touchstart'].forEach(function (ev) {
        document.addEventListener(ev, unlock, { passive: true });
    });

    function beep(freq, start, dur) {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, ctx.currentTime + start);
        gain.gain.exponentialRampToValueAtTime(0.4, ctx.currentTime + start + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + dur);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(ctx.currentTime + start);
        osc.stop(ctx.currentTime + start + dur + 0.05);
    }

    function playChime() {
        if (!ctx || ctx.state !== 'running') return;
        // তিনটা ছোট টোন (ডিং-ডং-ডিং)
        beep(880, 0, 0.25);
        beep(1175, 0.25, 0.25);
        beep(1568, 0.5, 0.4);
    }

    // বেল আইকনের ব্যাজে আনরিড সংখ্যা পড়ে
    function readUnread() {
        const btn = document.querySelector('[class*="database-notifications-btn"]');
        if (!btn) return null;
        const badge = btn.querySelector('.fi-badge, .fi-icon-btn-badge-ctn, [class*="badge"]');
        if (!badge) return 0;
        const n = parseInt((badge.textContent || '').replace(/\D/g, ''), 10);
        return isNaN(n) ? 0 : n;
    }

    setInterval(function () {
        const current = readUnread();
        if (current === null) return;
        if (lastCount !== null && current > lastCount) playChime();
        lastCount = current;
    }, 2000);
})();
</script>
