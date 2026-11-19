<?php

namespace App\Support;

/**
 * অ্যাডমিনের লেখা রিচ-টেক্সট (প্রোডাক্ট বর্ণনা, রিটার্ন পলিসি) সাইটে দেখানোর আগে পরিষ্কার করে:
 * অনুমোদিত ট্যাগ ছাড়া সব বাদ, onclick-জাতীয় অ্যাট্রিবিউট ও javascript: লিংক মুছে ফেলে।
 * পুরোনো সাধারণ (ট্যাগহীন) লেখা হলে নতুন লাইন বজায় রেখে দেখায়।
 */
class SafeHtml
{
    private const ALLOWED = '<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><ul><ol><li><a><img><blockquote><table><thead><tbody><tr><th><td><span><div><hr><figure><figcaption>';

    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        // ট্যাগহীন সাধারণ লেখা
        if ($html === strip_tags($html)) {
            return nl2br(e($html));
        }

        $html = strip_tags($html, self::ALLOWED);
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*(javascript|vbscript|data:text)[^"\'>\s]*\2/i', '$1="#"', $html) ?? '';

        return $html;
    }
}
