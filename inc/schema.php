<?php
/** Database schema and first-run content (taken from the client's flyer and application form). */

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    display_name TEXT NOT NULL DEFAULT '',
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    attempted_at INTEGER NOT NULL
);
CREATE TABLE IF NOT EXISTS settings (
    key TEXT PRIMARY KEY,
    value TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    tagline TEXT NOT NULL DEFAULT '',
    icon TEXT NOT NULL DEFAULT 'kaftan',
    image TEXT NOT NULL DEFAULT '',
    sort INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    price TEXT DEFAULT NULL,
    description TEXT NOT NULL DEFAULT '',
    details TEXT NOT NULL DEFAULT '',
    image TEXT NOT NULL DEFAULT '',
    image2 TEXT NOT NULL DEFAULT '',
    featured INTEGER NOT NULL DEFAULT 0,
    in_stock INTEGER NOT NULL DEFAULT 1,
    published INTEGER NOT NULL DEFAULT 1,
    sort INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    excerpt TEXT NOT NULL DEFAULT '',
    body TEXT NOT NULL DEFAULT '',
    image TEXT NOT NULL DEFAULT '',
    published INTEGER NOT NULL DEFAULT 1,
    published_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS testimonials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT '',
    quote TEXT NOT NULL,
    rating INTEGER NOT NULL DEFAULT 5,
    image TEXT NOT NULL DEFAULT '',
    published INTEGER NOT NULL DEFAULT 1,
    sort INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS gallery (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    image TEXT NOT NULL,
    caption TEXT NOT NULL DEFAULT '',
    sort INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS enquiries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL DEFAULT 'general',
    name TEXT NOT NULL,
    phone TEXT NOT NULL DEFAULT '',
    email TEXT NOT NULL DEFAULT '',
    occasion TEXT NOT NULL DEFAULT '',
    preferred_date TEXT NOT NULL DEFAULT '',
    message TEXT NOT NULL DEFAULT '',
    is_read INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_products_cat ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_attempts ON login_attempts(ip, attempted_at);
SQL);
}

/** Every editable setting: key => [label, default, input type, group, help]. */
function setting_fields(): array
{
    return [
        // Business
        'business_name'   => ['Business name', 'Bio Fashion Hub', 'text', 'Business details', ''],
        'tagline'         => ['Tagline', 'Look Good. Feel Confident.', 'text', 'Business details', ''],
        'whatsapp_number' => ['WhatsApp number', '054 847 3309', 'text', 'Business details', 'The number customers order on. Ghana format is fine (e.g. 054 847 3309).'],
        'phone_1'         => ['Phone 1', '054 847 3309', 'text', 'Business details', ''],
        'phone_2'         => ['Phone 2', '020 524 8123', 'text', 'Business details', ''],
        'email'           => ['Email', 'abdulaisumaila044@gmail.com', 'email', 'Business details', ''],
        'city'            => ['Town / city', 'Tamale', 'text', 'Business details', ''],
        'address'         => ['Shop address', 'Tamale, Northern Region, Ghana', 'text', 'Business details', 'Shown on the Contact page.'],
        'map_query'       => ['Map location', 'Tamale, Ghana', 'text', 'Business details', 'What to search on Google Maps — a full address, landmark or "latitude,longitude".'],
        'opening_hours'   => ['Opening hours', "Mon – Sat: 8:00am – 7:00pm\nSunday: By appointment", 'textarea', 'Business details', ''],
        'notify_email'    => ['Send new enquiries to', 'abdulaisumaila044@gmail.com', 'email', 'Business details', 'Leave blank to only see enquiries in this dashboard.'],
        // Social
        'facebook_url'    => ['Facebook link', '', 'url', 'Social media', 'Full link, e.g. https://facebook.com/yourpage'],
        'tiktok_username' => ['TikTok username', '', 'text', 'Social media', 'Without the @. Shows your TikTok feed on the home page.'],
        'instagram_url'   => ['Instagram link', '', 'url', 'Social media', ''],
        'facebook_feed'   => ['Show Facebook Page feed', '0', 'checkbox', 'Social media', 'Only works with a Facebook Page (not a personal profile).'],
        // Home
        'hero_eyebrow'    => ['Hero small heading', 'Tamale · Kaftans · Fabrics · Accessories', 'text', 'Home page', ''],
        'hero_title'      => ['Hero heading', 'Dressed with intention. Made to stand out.', 'text', 'Home page', ''],
        'hero_text'       => ['Hero text', 'Ready-to-wear kaftans, quality fabrics, classic Hausa caps and timeless watches — plus custom outfits tailored for you, your groom’s party or your big occasion.', 'textarea', 'Home page', ''],
        'hero_image'      => ['Hero image', 'assets/img/placeholder/hero.jpg', 'image', 'Home page', 'Tall photos work best.'],
        'custom_title'    => ['Custom orders heading', 'Made for your moment', 'text', 'Home page', ''],
        'custom_text'     => ['Custom orders text', 'Weddings, naming ceremonies, Eid, graduations or simply a look that is yours alone. Tell us the occasion, we’ll take your measurements and craft an outfit that fits perfectly.', 'textarea', 'Home page', ''],
        // About
        'about_intro'     => ['About — introduction', 'Bio Fashion Hub is a fashion and lifestyle brand dedicated to providing quality, stylish and affordable clothing and accessories.', 'textarea', 'About page', ''],
        'about_story'     => ['About — our story', "We believe what you wear shapes how you show up. Bio Fashion Hub was started in Tamale to help people express their identity, look their best and carry themselves with confidence.\n\nWe specialise in fabrics, traditional wear, kaftans, Hausa caps, wristwatches and customised outfits for individuals and special occasions — serving men and women, young professionals, fashion lovers and grooms who want elegance without overspending.", 'textarea', 'About page', ''],
        'about_mission'   => ['About — mission', 'To help every customer look good and feel confident through quality, affordable and personalised fashion.', 'textarea', 'About page', ''],
        'about_image'     => ['About image', 'assets/img/placeholder/navy-kaftan.jpg', 'image', 'About page', ''],
        'ceo_name'        => ['Founder name', 'Sumaila Abdulai', 'text', 'About page', ''],
        'ceo_role'        => ['Founder role', 'Founder & CEO', 'text', 'About page', ''],
        'ceo_bio'         => ['Founder message', 'Fashion is how we tell the world who we are before we say a word. At Bio Fashion Hub, my promise is simple: quality you can feel, styles you will love, and prices that make sense.', 'textarea', 'About page', ''],
        'ceo_photo'       => ['Founder photo', '', 'image', 'About page', ''],
    ];
}

function seed(PDO $pdo): void
{
    $ins = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)');
    foreach (setting_fields() as $k => $f) $ins->execute([$k, $f[1]]);

    $cats = [
        ['Kaftan Wears', 'kaftan-wears', 'Ready-to-wear kaftans for every occasion.', 'kaftan', 'assets/img/placeholder/white-kaftan.jpg'],
        ['Quality Fabrics', 'quality-fabrics', 'Carefully selected for comfort and style.', 'fabric', 'assets/img/placeholder/fabrics.jpg'],
        ['Hats & Accessories', 'hats-accessories', 'Classic hats and more to complete your look.', 'hat', 'assets/img/placeholder/hausa-cap.jpg'],
        ['Wristwatches', 'wristwatches', 'Timeless pieces to finish a sharp outfit.', 'watch', 'assets/img/placeholder/watches.jpg'],
    ];
    $c = $pdo->prepare('INSERT INTO categories (name, slug, tagline, icon, image, sort) VALUES (?,?,?,?,?,?)');
    foreach ($cats as $i => $cat) $c->execute([...$cat, $i]);
    $catId = fn($slug) => (int)$pdo->query("SELECT id FROM categories WHERE slug = " . $pdo->quote($slug))->fetchColumn();

    $products = [
        ['kaftan-wears', 'Navy Embroidered Kaftan', 'Deep navy kaftan with gold-thread embroidery down the placket and a clean mandarin collar. Elegant for Jumu’ah, weddings and evening events.', "Colour: Navy with gold embroidery\nSizes: S – XXL (custom sizing available)\nIncludes: Top and trousers", 'assets/img/placeholder/navy-kaftan.jpg', 1],
        ['kaftan-wears', 'White Kaftan with Woven Pocket', 'Crisp white kaftan finished with fine contrast stitching and a patterned woven pocket — a fresh, classic look for any celebration.', "Colour: White with navy & gold pocket detail\nSizes: S – XXL (custom sizing available)\nIncludes: Top and trousers", 'assets/img/placeholder/white-kaftan.jpg', 1],
        ['quality-fabrics', 'Premium Suiting Fabric Bundle', 'Soft, durable fabrics in navy, sky blue, beige and charcoal — ideal for kaftans and senator wear. Buy the fabric alone or have us sew it for you.', "Colours: Navy, sky blue, beige, charcoal\nSold per yard or per outfit length", 'assets/img/placeholder/fabrics.jpg', 1],
        ['hats-accessories', 'Embroidered Hausa Cap', 'Hand-finished Hausa cap with rich navy, white and gold embroidery. The perfect crown for your kaftan.', "Colours: Navy / gold / white\nSizes: Small, medium, large", 'assets/img/placeholder/hausa-cap.jpg', 1],
        ['hats-accessories', 'Striped Kufi Caps', 'Light, comfortable striped caps in black-and-white and white-and-gold. Easy everyday style.', "Colours: Black & white, white & gold", 'assets/img/placeholder/kufi-caps.jpg', 0],
        ['wristwatches', 'Classic Leather & Steel Watches', 'Gold-tone dress watches with black leather or two-tone steel straps. Understated, sharp and made to match traditional or modern outfits.', "Options: Black dial / leather strap, white dial / two-tone steel", 'assets/img/placeholder/watches.jpg', 1],
    ];
    $p = $pdo->prepare('INSERT INTO products (category_id, name, slug, description, details, image, featured, sort) VALUES (?,?,?,?,?,?,?,?)');
    foreach ($products as $i => [$cat, $name, $desc, $details, $img, $feat]) {
        $p->execute([$catId($cat), $name, slugify($name), $desc, $details, $img, $feat, $i]);
    }

    $posts = [
        ['How to Choose the Right Kaftan for Your Occasion', 'From Jumu’ah to weddings, a quick guide to picking colour, fabric and fit.',
            "A kaftan is one of the most versatile outfits a man can own — but the right one depends on where you are going.\n\n**For Jumu’ah and everyday wear**, choose lighter fabrics and calm colours such as white, sky blue or beige. Keep embroidery simple.\n\n**For weddings and celebrations**, go richer: navy, deep green or wine with gold or silver embroidery stands out in photos.\n\n**For grooms and their party**, coordinate colours across the group and add matching caps for a finished look.\n\nNot sure what suits you? Send us a message on WhatsApp and we will help you choose.",
            'assets/img/placeholder/navy-kaftan.jpg'],
        ['Completing Your Look: Caps, Watches & Details', 'Small accessories make a big difference. Here is how to pair them with your outfit.',
            "The outfit gets attention, but accessories complete the story.\n\n**Caps:** match the embroidery colour of your cap to the thread on your kaftan for a polished look.\n\n**Watches:** a gold-tone watch pairs beautifully with navy and white; silver works with grey and charcoal.\n\n**Fabrics:** if you are having an outfit made, choose your cap and watch first — then pick a fabric that ties them together.\n\nVisit us in Tamale or browse the shop to put your full look together.",
            'assets/img/placeholder/watches.jpg'],
    ];
    $b = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, body, image) VALUES (?,?,?,?,?)');
    foreach ($posts as [$t, $ex, $body, $img]) $b->execute([$t, slugify($t), $ex, $body, $img]);

    // Sample testimonials are saved HIDDEN so nothing invented is shown publicly.
    // The client replaces them with real customer words and ticks "Show on website".
    $t = $pdo->prepare('INSERT INTO testimonials (name, role, quote, rating, published, sort) VALUES (?,?,?,?,0,?)');
    $t->execute(['SAMPLE — Customer name', 'Groom, Tamale', 'Replace this with a real customer’s words about their outfit or experience.', 5, 0]);
    $t->execute(['SAMPLE — Customer name', 'Young professional', 'Replace this with a real customer’s words. Ask happy customers on WhatsApp if you can share their message.', 5, 1]);

    $g = $pdo->prepare('INSERT INTO gallery (image, caption, sort) VALUES (?,?,?)');
    foreach ([['navy-kaftan', 'Navy embroidered kaftan'], ['white-kaftan', 'White kaftan with woven pocket'], ['hausa-cap', 'Embroidered Hausa cap'], ['fabrics', 'Quality fabrics'], ['watches', 'Classic watches'], ['kufi-caps', 'Striped kufi caps']] as $i => [$f, $cap]) {
        $g->execute(["assets/img/placeholder/$f.jpg", $cap, $i]);
    }
}
