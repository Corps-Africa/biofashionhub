# Bio Fashion Hub — website

Website for **Bio Fashion Hub** (Tamale, Ghana): kaftans, fabrics, Hausa caps, watches and custom outfits.
Customers order through **WhatsApp**, and the owner updates everything from an **admin dashboard**.

- **Stack:** plain PHP 8.1+ with SQLite. There's no framework, no build step and no separate database server.
- **Hosting:** any cheap cPanel or shared host with PHP 8.1+ and the `pdo_sqlite` and `gd` extensions (almost all have them).
- **Brand:** taken from the client's flyer: navy `#071938`, gold `#B37C2B`, off-white `#FAF8F3`, Bodoni Moda + Montserrat + Jost.

## Pages

| Page | File | Notes |
|---|---|---|
| Home | `index.php` | Hero, categories, featured products, how ordering works, custom orders, lookbook, testimonials, TikTok feed, journal, contact strip |
| Shop | `shop.php` | Category filter and search |
| Product | `product.php?slug=…` | "Order on WhatsApp" opens a chat with the product name, price and link already typed |
| Custom Orders | `custom-orders.php` | Fitting/booking form (occasion and date), saved to the admin, then offers to send it on WhatsApp too |
| Lookbook | `lookbook.php` | Portfolio gallery with lightbox and "Ask about this" on WhatsApp |
| About | `about.php` | Story, mission, values, founder |
| Testimonials | `testimonials.php` | |
| Journal (blog) | `blog.php`, `post.php` | |
| Contact | `contact.php` | Google Map (no API key needed), phones, email, hours, form |

The floating WhatsApp button appears on every page.

## Admin dashboard (`/admin/`)

From a phone or computer, the owner can:

- **Products:** add, edit, hide, delete, mark "made to order", feature on the home page, and upload 2 photos (phone photos are resized automatically)
- **Categories:** name, description, icon and order
- **Enquiries:** read form messages, then reply on WhatsApp or by email in one tap. New ones are also emailed to the address in Settings.
- **Lookbook:** upload several photos at once, add captions, set the order
- **Testimonials** and **Journal/Blog** posts
- **Site settings:** WhatsApp number, phones, email, address, map, hours, social links, TikTok feed, all home and about-page text and images
- **My account:** change password, add or remove other admins

## Deploying (cPanel)

1. Upload everything in this folder to `public_html/` (or a subfolder).
2. Make sure `data/` and `uploads/` are writable by PHP (usually `755`; use `775` if saving fails).
3. Visit `https://yourdomain.com/admin/`. The first visit opens a one-time **setup page** where the owner creates their username and password. The page locks itself afterwards.
4. Turn on free SSL (AutoSSL / Let's Encrypt in cPanel), then uncomment the HTTPS redirect in `.htaccess`.
5. In **Admin → Site settings**, check the WhatsApp number, address, map location and social links.

The database is created and filled with starter content automatically on the first page load.
**Back up** `data/site.db` and `uploads/` regularly; that's all the site's content.

The `.htaccess` files block web access to `data/` and `inc/` and stop scripts from running in `uploads/`.
If the host runs **Nginx** instead of Apache, add equivalent `deny` rules for `/data/` and `/inc/`.

### Run locally

```bash
php -S 127.0.0.1:8080
```

Then open http://127.0.0.1:8080 (admin at `/admin/`).

## Before launch: still needed from the client

- [ ] **Real product photos.** The current images are cropped from the flyer as placeholders and are tagged "Sample photo" in the admin.
- [ ] **Prices,** or confirm they want "Price on request"
- [ ] **Which number is on WhatsApp.** The site uses 054 847 3309 for now.
- [ ] **Exact shop address or landmark** for the map
- [ ] **Social links.** The form and flyer disagree (Facebook "Abdulai Sumaila" vs "Biology", TikTok "Biology", Instagram "Abdulai Sumaila"). We need the real profile URLs.
- [ ] **Real testimonials.** Two samples are stored **hidden**, so nothing invented appears on the site.
- [ ] Review the starter About text and the two Journal articles
- [ ] A founder photo (optional)
- [ ] Domain and hosting (the client's responsibility under the campaign)

## Security notes

- Passwords hashed with `password_hash`, sessions regenerated on login, cookies HttpOnly + SameSite
- CSRF token on every form; all database queries use prepared statements; all output escaped
- Login throttled (5 failed attempts per 15 minutes per IP)
- Uploads checked by real file type and size (6 MB max), then re-encoded as JPEG, which strips any hidden content
- Forms have a honeypot field, a time check, and a per-visitor limit against spam
