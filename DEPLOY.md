# ডিপ্লয় (cPanel → Git Version Control)
1. GitHub-এ পুশ করুন।
2. cPanel → Git Version Control → rafamarts → "Update from Remote" → "Deploy HEAD Commit"।
`.cpanel.yml` কোড কপি করে, `php artisan migrate --force` ও `optimize:clear` চালায়।
`.env`, `storage`, `vendor`, আপনার `public_html/index.php` ও `.htaccess` কখনো বদলায় না।
