# Bio Fashion Hub — website (Next.js + Supabase)

Website for **Bio Fashion Hub** (Tamale, Ghana): kaftans, fabrics, Hausa caps, watches and custom outfits.
Customers order through **WhatsApp**, and the owner updates everything from an **admin dashboard**.

| Part | Technology |
|---|---|
| Website and admin | Next.js 15 (React 19, TypeScript) |
| Database | Supabase Postgres, with row-level security on every table |
| Admin sign-in | Supabase Auth (email + password, reset by email) |
| Photos | Supabase Storage (`media` bucket), resized in the browser before upload |
| Hosting | Netlify (free plan; business use allowed) |
| Enquiry emails (optional) | Resend |

> The earlier PHP + SQLite version of the site is on the `main` branch.

## What's included

**Public site:** Home, Shop (categories and search), Product pages, Custom Orders (fitting/booking form), Lookbook, About, Testimonials, Journal (blog), Contact (Google Map), and a branded 404 page.

- Every product has **"Order on WhatsApp"**, which opens a chat with the product name, price and link already typed.
- A WhatsApp button floats on every page.
- After sending a form, the visitor can also send the same message on WhatsApp.
- If social details are entered, the home page shows the TikTok feed and the Facebook Page feed.

**Admin dashboard (`/admin`):**

- **Products:** photos, price, featured, available or made to order, hidden
- **Categories:** name, description, icon, order
- **Enquiries:** inbox with one-tap reply on WhatsApp or by email
- **Lookbook:** upload many photos at once, with captions and order
- **Testimonials** and **Journal** posts
- **Site settings:** every phone number, address, social link, and the home/about text and images
- **My account:** change password, add or remove admins
- **Launch checklist** on the dashboard
- **Forgot password** link on the login page

Saved changes appear on the live site straight away.

**Security** (`supabase/migrations/…_schema.sql`):

- Visitors can only **read** published content.
- Visitors can only create enquiries through `submit_enquiry()`. It validates input and limits enquiries to 3 per phone number or email per hour, and 60 per hour across the site.
- Only users listed in `admins` can change content, read enquiries or upload photos. A signed-in user who isn't an admin can change nothing.
- Public sign-up should be **off**. Admins are created by an admin or with `npm run create-admin`.
- Forms have a hidden spam-trap field and a time check.
- Uploaded photos are re-encoded, which strips location data.

---

## Setup (about 30 minutes)

### 1. Supabase

1. Create a project at [supabase.com](https://supabase.com) and choose the region closest to Ghana that's offered.
2. **SQL Editor:** paste and run `supabase/migrations/20261111000000_schema.sql`, then `supabase/seed.sql` (the starter content from the flyer).
3. **Authentication → Sign In / Providers:** turn **off** "Allow new users to sign up". Keep Email enabled.
4. **Authentication → URL Configuration:** set **Site URL** to the live address (e.g. `https://biofashionhub.com`). Add `https://biofashionhub.com/**` and your Netlify address (`https://<name>.netlify.app/**`) to **Redirect URLs**.
5. **Authentication → Emails → SMTP:** add a custom SMTP sender, for example Resend's SMTP. Supabase's built-in email only sends to the project's own team members, so without this the owner can't receive password-reset emails.
6. Copy the **Project URL**, **anon public key** and **service_role key** from **Project Settings → API**.

### 2. First admin

On your computer, copy `.env.example` to `.env.local` and fill in the values. Then run:

```bash
npm install
```

```bash
npm run create-admin -- owner@example.com "Sumaila Abdulai"
```

This prints a temporary password. The owner signs in at `/admin/login` and changes it under **My account**.

*Without Node:* in Supabase, go to **Authentication → Users → Add user** (tick "Auto confirm"). Then run this in the SQL Editor:
`insert into admins (user_id, display_name) select id, 'Sumaila Abdulai' from auth.users where email = 'owner@example.com';`

### 3. Netlify

1. **Add new site → Import from Git**, then choose this repository and the branch to deploy.
2. Netlify detects Next.js automatically; `netlify.toml` holds the build settings.
3. **Site configuration → Environment variables:**

| Variable | Required | Notes |
|---|---|---|
| `NEXT_PUBLIC_SUPABASE_URL` | ✅ | Project URL |
| `NEXT_PUBLIC_SUPABASE_ANON_KEY` | ✅ | anon public key |
| `NEXT_PUBLIC_SITE_URL` | ✅ | e.g. `https://biofashionhub.com`, used in WhatsApp links and reset emails |
| `SUPABASE_SERVICE_ROLE_KEY` | optional | Lets admins add or remove other admins from the dashboard. **Keep it secret.** |
| `RESEND_API_KEY` | optional | Emails each new enquiry to the address in Site settings |
| `ENQUIRY_FROM_EMAIL` | optional | e.g. `Bio Fashion Hub <website@biofashionhub.com>` (domain verified in Resend) |

4. Deploy, then add the custom domain under **Domain management**. HTTPS is set up automatically.
5. If you change the domain later, update `NEXT_PUBLIC_SITE_URL` and the Supabase URL settings, then redeploy.

### Free-tier pausing

Supabase pauses free projects after about a week without database activity.
`netlify/functions/keep-alive.mts` runs once a day on Netlify and makes one small read to prevent this.
Delete it if you move to Supabase Pro.

---

## Developing locally

```bash
npm install
```

```bash
npm run dev
```

Then open http://localhost:3000. You can point `.env.local` at a Supabase project, or at a local stack (`supabase start` with the Supabase CLI) where you've applied the migration and seed.

- `npm run lint`: type-check
- `npm run build`: production build

## Project layout

```
src/app/(site)/        public pages (+ actions.ts for the enquiry form)
src/app/(admin)/admin/ admin: (auth) login/forgot, (panel) dashboard and editors, actions.ts
src/app/auth/confirm/  landing route for password-reset email links
src/components/        shared UI (admin/ = admin-only client components)
src/lib/               Supabase clients, helpers, settings definitions
src/middleware.ts      keeps admin sessions fresh, guards /admin
supabase/              schema + security rules, seed data
netlify/functions/     daily keep-alive
scripts/create-admin.mjs
public/                logo, favicon, placeholder photos cropped from the flyer
```

## Before launch: still needed from the client

- [ ] **Real product photos.** Placeholders are tagged "Sample photo" in the admin.
- [ ] **Prices,** or confirm they want "Price on request"
- [ ] **Which number is on WhatsApp.** The site uses 054 847 3309 for now.
- [ ] **Exact shop address or landmark** for the map
- [ ] **Social profile links.** The form and flyer disagree about which name is which account.
- [ ] **Real testimonials.** The two samples are stored hidden.
- [ ] Review the About text and the two starter Journal articles
- [ ] Domain name (the client's cost)
