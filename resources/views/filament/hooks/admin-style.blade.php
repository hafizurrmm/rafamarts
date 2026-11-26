<style>
@if(\App\Support\Ui::adminTheme() === 'emerald')
    /* প্রফেশনাল অ্যাডমিন লুক: গাঢ় সবুজ সাইডবার (লাইট মোডে) */
    html:not(.dark) .fi-sidebar { background: #0f3a2c; border-right: 0; }
    html:not(.dark) .fi-sidebar-header { background: #0f3a2c; box-shadow: none; border-bottom: 1px solid rgba(255,255,255,.08); }
    html:not(.dark) .fi-sidebar-header a, html:not(.dark) .fi-sidebar-header div { color: #fff; }
    html:not(.dark) .fi-sidebar-group-label,
    html:not(.dark) .fi-sidebar-group-button svg { color: rgba(255,255,255,.55); }
    html:not(.dark) .fi-sidebar-item-button,
    html:not(.dark) .fi-sidebar-item-button .fi-sidebar-item-label,
    html:not(.dark) .fi-sidebar-item-button .fi-sidebar-item-icon { color: #dff3e8 !important; }
    html:not(.dark) .fi-sidebar-item-button:hover { background: rgba(255,255,255,.08); }
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button { background: rgba(255,255,255,.16); }
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-label,
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-icon { color: #fff !important; font-weight: 600; }
    /* কন্টেন্ট একটু হালকা ব্যাকগ্রাউন্ড, কার্ড/টেবিল পরিষ্কার সাদা */
    html:not(.dark) .fi-main-ctn { background: #f4f7f5; }
@else
    /* Ocean থিম: গ্র্যাডিয়েন্ট সাইডবার (পুরো উচ্চতা, ধার ঘেঁষে — সাদা ফাঁক নেই) (নীল-টিল), কমলা অ্যাকসেন্ট, হালকা নীলচে ব্যাকগ্রাউন্ড */
    html:not(.dark) .fi-sidebar { background: linear-gradient(180deg, #0894ab 0%, #0a74b8 52%, #0a4fa6 100%); border-right: 0; }
    html:not(.dark) .fi-sidebar-header { background: transparent; box-shadow: none; border-bottom: 1px solid rgba(255,255,255,.18); }
    html:not(.dark) .fi-sidebar-header a, html:not(.dark) .fi-sidebar-header div { color: #fff; }
    html:not(.dark) .fi-sidebar-group-label,
    html:not(.dark) .fi-sidebar-group-button svg,
    html:not(.dark) .fi-sidebar-header button,
    html:not(.dark) .fi-sidebar-header svg { color: #fff !important; opacity: 1; }
    html:not(.dark) .fi-sidebar-group-label { font-weight: 700; }
    html:not(.dark) .fi-sidebar-item-button,
    html:not(.dark) .fi-sidebar-item-button .fi-sidebar-item-label,
    html:not(.dark) .fi-sidebar-item-button .fi-sidebar-item-icon { color: #fff !important; }
    html:not(.dark) .fi-sidebar-item-button .fi-sidebar-item-label { font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,.18); }
    html:not(.dark) .fi-sidebar-item-button:hover { background: rgba(255,255,255,.14); }
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button { background: rgba(255,255,255,.22); box-shadow: inset 3px 0 0 #ff9f1c; }
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-label,
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-button .fi-sidebar-item-icon { color: #fff !important; font-weight: 700; }
    html:not(.dark) .fi-main-ctn { background: #eef2f8; }
@endif
    /* সাইডবারের আধুনিক, পাতলা স্ক্রলবার (ডিফল্ট ধূসর বার ও তীরচিহ্ন বাদ) */
    .fi-sidebar, .fi-sidebar-nav { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.38) transparent; }
    .fi-sidebar::-webkit-scrollbar, .fi-sidebar-nav::-webkit-scrollbar { width: 6px; height: 6px; background: transparent; }
    .fi-sidebar::-webkit-scrollbar-track, .fi-sidebar-nav::-webkit-scrollbar-track { background: transparent; margin: 8px 0; }
    .fi-sidebar::-webkit-scrollbar-thumb, .fi-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.34); border-radius: 999px; border: 0; }
    .fi-sidebar::-webkit-scrollbar-thumb:hover, .fi-sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.62); }
    .fi-sidebar::-webkit-scrollbar-button, .fi-sidebar-nav::-webkit-scrollbar-button { display: none; width: 0; height: 0; }
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat { border-radius: .9rem; }
    /* মোবাইল: বাল্ক অ্যাকশন বাটনগুলো Filament এ shrink-0 থাকায় এক লাইনে চওড়া হয়ে পেজ টানত — এখন র‍্যাপ হয়ে নিচের লাইনে নামবে */
    @media (max-width: 1023px) {
        .fi-main-ctn, .fi-main, .fi-page, .fi-ta, .fi-ta-ctn { max-width: 100%; min-width: 0; }
        .fi-main-ctn { overflow-x: clip; }
        .fi-ta-header-toolbar { flex-wrap: wrap !important; row-gap: .5rem; }
        .fi-ta-header-toolbar > div:first-of-type { flex: 1 1 100% !important; min-width: 0 !important; max-width: 100%; flex-wrap: wrap; }
        .fi-ta-header-toolbar .fi-ta-actions { flex-wrap: wrap !important; flex-shrink: 1 !important; min-width: 0; max-width: 100%; row-gap: .5rem; }
        .fi-ta-header-toolbar .fi-ta-actions { justify-content: flex-start !important; }
        .fi-ta-header-toolbar .fi-ta-actions .fi-btn { flex: 0 1 auto; }
        .fi-ta-header-toolbar > div:last-of-type { margin-inline-start: 0; flex: 1 1 100%; }
        .fi-ta-content { overflow-x: auto; }
    }

    /* ড্যাশবোর্ডের স্ট্যাট কার্ড: এক সারিতে ৫টি (ছোট স্ক্রিনে ২টি/১টি) */
    .fi-wi-stats-overview-stats-ctn { display: grid !important; grid-template-columns: repeat(5, minmax(0, 1fr)) !important; gap: 1rem !important; }
    @media (max-width: 1100px) { .fi-wi-stats-overview-stats-ctn { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; } }
    @media (max-width: 520px) { .fi-wi-stats-overview-stats-ctn { grid-template-columns: 1fr !important; } }
</style>
