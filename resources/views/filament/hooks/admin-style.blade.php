<style>
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
    html:not(.dark) .fi-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.25); }
    /* কন্টেন্ট একটু হালকা ব্যাকগ্রাউন্ড, কার্ড/টেবিল পরিষ্কার সাদা */
    html:not(.dark) .fi-main-ctn { background: #f4f7f5; }
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
</style>
