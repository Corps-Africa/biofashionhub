-- Bio Fashion Hub — database schema, security rules and storage.
-- Run once in Supabase → SQL Editor (or `supabase db push`). Then run seed.sql.

-- ─────────────────────────────── Admins ───────────────────────────────
create table public.admins (
  user_id uuid primary key references auth.users (id) on delete cascade,
  display_name text not null default '',
  created_at timestamptz not null default now()
);

-- True when the signed-in user is in the admins table.
-- SECURITY DEFINER so it can read admins without granting read access to everyone.
create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (select 1 from public.admins where user_id = auth.uid());
$$;
revoke all on function public.is_admin() from public;
grant execute on function public.is_admin() to anon, authenticated;

-- ─────────────────────────────── Content ───────────────────────────────
create table public.settings (
  key text primary key,
  value text not null default ''
);

create table public.categories (
  id bigint generated always as identity primary key,
  name text not null check (char_length(name) between 1 and 80),
  slug text not null unique,
  tagline text not null default '',
  icon text not null default 'kaftan',
  sort int not null default 0
);

create table public.products (
  id bigint generated always as identity primary key,
  category_id bigint references public.categories (id) on delete set null,
  name text not null check (char_length(name) between 1 and 140),
  slug text not null unique,
  price numeric(10, 2) check (price is null or price >= 0),
  description text not null default '',
  details text not null default '',
  image text not null default '',
  image2 text not null default '',
  featured boolean not null default false,
  in_stock boolean not null default true,
  published boolean not null default true,
  sort int not null default 0,
  created_at timestamptz not null default now()
);
create index products_category_idx on public.products (category_id);

create table public.posts (
  id bigint generated always as identity primary key,
  title text not null check (char_length(title) between 1 and 160),
  slug text not null unique,
  excerpt text not null default '',
  body text not null default '',
  image text not null default '',
  published boolean not null default true,
  published_at timestamptz not null default now()
);

create table public.testimonials (
  id bigint generated always as identity primary key,
  name text not null,
  role text not null default '',
  quote text not null,
  rating int not null default 5 check (rating between 1 and 5),
  image text not null default '',
  published boolean not null default true,
  sort int not null default 0
);

create table public.gallery (
  id bigint generated always as identity primary key,
  image text not null,
  caption text not null default '',
  sort int not null default 0,
  created_at timestamptz not null default now()
);

create table public.enquiries (
  id bigint generated always as identity primary key,
  type text not null default 'contact' check (type in ('contact', 'custom')),
  name text not null,
  phone text not null default '',
  email text not null default '',
  occasion text not null default '',
  preferred_date date,
  message text not null default '',
  is_read boolean not null default false,
  created_at timestamptz not null default now()
);
create index enquiries_created_idx on public.enquiries (created_at desc);

-- ─────────────────────────── Row Level Security ───────────────────────────
-- Public visitors (anon) can only READ what is published. Only admins can change anything.
alter table public.admins enable row level security;
alter table public.settings enable row level security;
alter table public.categories enable row level security;
alter table public.products enable row level security;
alter table public.posts enable row level security;
alter table public.testimonials enable row level security;
alter table public.gallery enable row level security;
alter table public.enquiries enable row level security;

create policy "admins see admins" on public.admins for select to authenticated using (public.is_admin());
create policy "admins edit own name" on public.admins for update to authenticated
  using (user_id = auth.uid()) with check (user_id = auth.uid());

create policy "public read" on public.settings for select to anon, authenticated using (true);
create policy "public read" on public.categories for select to anon, authenticated using (true);
create policy "public read" on public.gallery for select to anon, authenticated using (true);
create policy "public read published" on public.products for select to anon, authenticated using (published or public.is_admin());
create policy "public read published" on public.posts for select to anon, authenticated using ((published and published_at <= now()) or public.is_admin());
create policy "public read published" on public.testimonials for select to anon, authenticated using (published or public.is_admin());

create policy "admin write" on public.settings for all to authenticated using (public.is_admin()) with check (public.is_admin());
create policy "admin write" on public.categories for all to authenticated using (public.is_admin()) with check (public.is_admin());
create policy "admin write" on public.products for all to authenticated using (public.is_admin()) with check (public.is_admin());
create policy "admin write" on public.posts for all to authenticated using (public.is_admin()) with check (public.is_admin());
create policy "admin write" on public.testimonials for all to authenticated using (public.is_admin()) with check (public.is_admin());
create policy "admin write" on public.gallery for all to authenticated using (public.is_admin()) with check (public.is_admin());

-- Enquiries: nobody but admins can read them. Visitors submit through submit_enquiry() below.
create policy "admin manage" on public.enquiries for all to authenticated using (public.is_admin()) with check (public.is_admin());

-- ─────────────────────────── Enquiry submission ───────────────────────────
-- The only way for the public to create an enquiry. Validates input and caps volume
-- so the public API key cannot be used to flood the inbox.
create or replace function public.submit_enquiry(
  p_type text, p_name text, p_phone text, p_email text,
  p_occasion text, p_preferred_date date, p_message text
)
returns bigint
language plpgsql
security definer
set search_path = ''
as $$
declare
  new_id bigint;
begin
  p_name := left(btrim(coalesce(p_name, '')), 120);
  p_phone := left(btrim(coalesce(p_phone, '')), 40);
  p_email := left(btrim(coalesce(p_email, '')), 160);
  p_occasion := left(btrim(coalesce(p_occasion, '')), 120);
  p_message := left(btrim(coalesce(p_message, '')), 3000);

  if p_type not in ('contact', 'custom') then raise exception 'invalid type'; end if;
  if p_name = '' or p_message = '' or (p_phone = '' and p_email = '') then
    raise exception 'missing required fields';
  end if;
  if p_email <> '' and p_email !~* '^[^@\s]+@[^@\s]+\.[^@\s]+$' then raise exception 'invalid email'; end if;
  if p_phone <> '' and p_phone !~ '^[0-9+\s()\-]{9,20}$' then raise exception 'invalid phone'; end if;

  -- Same contact details: max 3 per hour. Whole site: max 60 per hour.
  if (select count(*) from public.enquiries
      where created_at > now() - interval '1 hour'
        and ((p_phone <> '' and phone = p_phone) or (p_email <> '' and email = p_email))) >= 3
     or (select count(*) from public.enquiries where created_at > now() - interval '1 hour') >= 60 then
    raise exception 'rate limited' using errcode = 'P0429';
  end if;

  insert into public.enquiries (type, name, phone, email, occasion, preferred_date, message)
  values (p_type, p_name, p_phone, p_email, p_occasion, p_preferred_date, p_message)
  returning id into new_id;
  return new_id;
end;
$$;
revoke all on function public.submit_enquiry(text, text, text, text, text, date, text) from public;
grant execute on function public.submit_enquiry(text, text, text, text, text, date, text) to anon, authenticated;

-- ─────────────────────────────── Storage ───────────────────────────────
-- One public bucket for product, lookbook, blog and site photos. Only admins can upload or delete.
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values ('media', 'media', true, 6291456, array['image/jpeg', 'image/png', 'image/webp'])
on conflict (id) do nothing;

create policy "media public read" on storage.objects for select to anon, authenticated using (bucket_id = 'media');
create policy "media admin insert" on storage.objects for insert to authenticated with check (bucket_id = 'media' and public.is_admin());
create policy "media admin update" on storage.objects for update to authenticated using (bucket_id = 'media' and public.is_admin());
create policy "media admin delete" on storage.objects for delete to authenticated using (bucket_id = 'media' and public.is_admin());
