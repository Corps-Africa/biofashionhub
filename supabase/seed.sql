-- Bio Fashion Hub — starter content, taken from the client's flyer and application form.
-- Safe to run once after the schema migration. Photos point to placeholders bundled with the site.

insert into public.settings (key, value) values
  ('business_name', 'Bio Fashion Hub'),
  ('tagline', 'Look Good. Feel Confident.'),
  ('whatsapp_number', '054 847 3309'),
  ('phone_1', '054 847 3309'),
  ('phone_2', '020 524 8123'),
  ('email', 'abdulaisumaila044@gmail.com'),
  ('city', 'Tamale'),
  ('address', 'Tamale, Northern Region, Ghana'),
  ('map_query', 'Tamale, Ghana'),
  ('opening_hours', E'Mon – Sat: 8:00am – 7:00pm\nSunday: By appointment'),
  ('notify_email', 'abdulaisumaila044@gmail.com'),
  ('facebook_url', ''),
  ('tiktok_username', ''),
  ('instagram_url', ''),
  ('facebook_feed', '0'),
  ('hero_eyebrow', 'Tamale · Kaftans · Fabrics · Accessories'),
  ('hero_title', 'Dressed with intention. Made to stand out.'),
  ('hero_text', 'Ready-to-wear kaftans, quality fabrics, classic Hausa caps and timeless watches — plus custom outfits tailored for you, your groom’s party or your big occasion.'),
  ('hero_image', '/placeholder/hero.jpg'),
  ('custom_title', 'Made for your moment'),
  ('custom_text', 'Weddings, naming ceremonies, Eid, graduations or simply a look that is yours alone. Tell us the occasion, we’ll take your measurements and craft an outfit that fits perfectly.'),
  ('custom_image', '/placeholder/white-kaftan.jpg'),
  ('about_intro', 'Bio Fashion Hub is a fashion and lifestyle brand dedicated to providing quality, stylish and affordable clothing and accessories.'),
  ('about_story', E'We believe what you wear shapes how you show up. Bio Fashion Hub was started in Tamale to help people express their identity, look their best and carry themselves with confidence.\n\nWe specialise in fabrics, traditional wear, kaftans, Hausa caps, wristwatches and customised outfits for individuals and special occasions — serving men and women, young professionals, fashion lovers and grooms who want elegance without overspending.'),
  ('about_mission', 'To help every customer look good and feel confident through quality, affordable and personalised fashion.'),
  ('about_image', '/placeholder/navy-kaftan.jpg'),
  ('ceo_name', 'Sumaila Abdulai'),
  ('ceo_role', 'Founder & CEO'),
  ('ceo_bio', 'Fashion is how we tell the world who we are before we say a word. At Bio Fashion Hub, my promise is simple: quality you can feel, styles you will love, and prices that make sense.'),
  ('ceo_photo', '')
on conflict (key) do nothing;

insert into public.categories (name, slug, tagline, icon, sort) values
  ('Kaftan Wears', 'kaftan-wears', 'Ready-to-wear kaftans for every occasion.', 'kaftan', 0),
  ('Quality Fabrics', 'quality-fabrics', 'Carefully selected for comfort and style.', 'fabric', 1),
  ('Hats & Accessories', 'hats-accessories', 'Classic hats and more to complete your look.', 'hat', 2),
  ('Wristwatches', 'wristwatches', 'Timeless pieces to finish a sharp outfit.', 'watch', 3)
on conflict (slug) do nothing;

insert into public.products (category_id, name, slug, description, details, image, featured, sort)
select c.id, p.name, p.slug, p.description, p.details, p.image, p.featured, p.sort
from (values
  ('kaftan-wears', 'Navy Embroidered Kaftan', 'navy-embroidered-kaftan',
   'Deep navy kaftan with gold-thread embroidery down the placket and a clean mandarin collar. Elegant for Jumu’ah, weddings and evening events.',
   E'Colour: Navy with gold embroidery\nSizes: S – XXL (custom sizing available)\nIncludes: Top and trousers', '/placeholder/navy-kaftan.jpg', true, 0),
  ('kaftan-wears', 'White Kaftan with Woven Pocket', 'white-kaftan-with-woven-pocket',
   'Crisp white kaftan finished with fine contrast stitching and a patterned woven pocket — a fresh, classic look for any celebration.',
   E'Colour: White with navy & gold pocket detail\nSizes: S – XXL (custom sizing available)\nIncludes: Top and trousers', '/placeholder/white-kaftan.jpg', true, 1),
  ('quality-fabrics', 'Premium Suiting Fabric Bundle', 'premium-suiting-fabric-bundle',
   'Soft, durable fabrics in navy, sky blue, beige and charcoal — ideal for kaftans and senator wear. Buy the fabric alone or have us sew it for you.',
   E'Colours: Navy, sky blue, beige, charcoal\nSold per yard or per outfit length', '/placeholder/fabrics.jpg', true, 2),
  ('hats-accessories', 'Embroidered Hausa Cap', 'embroidered-hausa-cap',
   'Hand-finished Hausa cap with rich navy, white and gold embroidery. The perfect crown for your kaftan.',
   E'Colours: Navy / gold / white\nSizes: Small, medium, large', '/placeholder/hausa-cap.jpg', true, 3),
  ('hats-accessories', 'Striped Kufi Caps', 'striped-kufi-caps',
   'Light, comfortable striped caps in black-and-white and white-and-gold. Easy everyday style.',
   'Colours: Black & white, white & gold', '/placeholder/kufi-caps.jpg', false, 4),
  ('wristwatches', 'Classic Leather & Steel Watches', 'classic-leather-steel-watches',
   'Gold-tone dress watches with black leather or two-tone steel straps. Understated, sharp and made to match traditional or modern outfits.',
   'Options: Black dial / leather strap, white dial / two-tone steel', '/placeholder/watches.jpg', true, 5)
) as p(cat, name, slug, description, details, image, featured, sort)
join public.categories c on c.slug = p.cat
on conflict (slug) do nothing;

insert into public.posts (title, slug, excerpt, body, image) values
  ('How to Choose the Right Kaftan for Your Occasion', 'how-to-choose-the-right-kaftan-for-your-occasion',
   'From Jumu’ah to weddings, a quick guide to picking colour, fabric and fit.',
   E'A kaftan is one of the most versatile outfits a man can own — but the right one depends on where you are going.\n\n**For Jumu’ah and everyday wear**, choose lighter fabrics and calm colours such as white, sky blue or beige. Keep embroidery simple.\n\n**For weddings and celebrations**, go richer: navy, deep green or wine with gold or silver embroidery stands out in photos.\n\n**For grooms and their party**, coordinate colours across the group and add matching caps for a finished look.\n\nNot sure what suits you? Send us a message on WhatsApp and we will help you choose.',
   '/placeholder/navy-kaftan.jpg'),
  ('Completing Your Look: Caps, Watches & Details', 'completing-your-look-caps-watches-details',
   'Small accessories make a big difference. Here is how to pair them with your outfit.',
   E'The outfit gets attention, but accessories complete the story.\n\n**Caps:** match the embroidery colour of your cap to the thread on your kaftan for a polished look.\n\n**Watches:** a gold-tone watch pairs beautifully with navy and white; silver works with grey and charcoal.\n\n**Fabrics:** if you are having an outfit made, choose your cap and watch first — then pick a fabric that ties them together.\n\nVisit us in Tamale or browse the shop to put your full look together.',
   '/placeholder/watches.jpg')
on conflict (slug) do nothing;

-- Sample testimonials are HIDDEN so nothing invented appears publicly. Replace with real customer words.
insert into public.testimonials (name, role, quote, rating, published, sort) values
  ('SAMPLE — Customer name', 'Groom, Tamale', 'Replace this with a real customer’s words about their outfit or experience.', 5, false, 0),
  ('SAMPLE — Customer name', 'Young professional', 'Replace this with a real customer’s words. Ask happy customers on WhatsApp if you can share their message.', 5, false, 1);

insert into public.gallery (image, caption, sort) values
  ('/placeholder/navy-kaftan.jpg', 'Navy embroidered kaftan', 0),
  ('/placeholder/white-kaftan.jpg', 'White kaftan with woven pocket', 1),
  ('/placeholder/hausa-cap.jpg', 'Embroidered Hausa cap', 2),
  ('/placeholder/fabrics.jpg', 'Quality fabrics', 3),
  ('/placeholder/watches.jpg', 'Classic watches', 4),
  ('/placeholder/kufi-caps.jpg', 'Striped kufi caps', 5);
