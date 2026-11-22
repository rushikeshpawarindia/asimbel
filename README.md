# Asimbel website

Static single-page site (`index.html`) plus a PHP contact endpoint (`api/contact.php`) that emails info@asimbel.com.

## Deploy
Upload `index.html`, `robots.txt`, `sitemap.xml` and the `api/` folder to the web root of a PHP-capable host (cPanel, Hostinger, etc.).
Form posts to `/api/contact.php`.

Note: GitHub Pages serves static files only, so PHP will not run there. Use GitHub as source control and deploy to a PHP host
(for example via cPanel Git deployment or FTP/GitHub Actions).

## Email
`contact.php` uses PHP `mail()`. If disabled on your host, switch to PHPMailer with SMTP credentials from environment variables (never commit them).
Set SPF/DKIM for asimbel.com so mail is not marked as spam.
