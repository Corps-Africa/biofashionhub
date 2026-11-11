import { SITE_URL, SUPABASE_URL } from "./supabase/env";
import type { Product, Settings } from "./types";

export const DEFAULTS: Settings = {
  business_name: "Bio Fashion Hub",
  tagline: "Look Good. Feel Confident.",
  whatsapp_number: "054 847 3309",
  city: "Tamale",
  map_query: "Tamale, Ghana",
  hero_image: "/placeholder/hero.jpg",
  custom_image: "/placeholder/white-kaftan.jpg",
  about_image: "/placeholder/navy-kaftan.jpg",
};

/** Look up a setting, falling back to a default when empty. */
export function s(settings: Settings, key: string, fallback = ""): string {
  const v = settings[key];
  return v !== undefined && v !== "" ? v : DEFAULTS[key] ?? fallback;
}

/** Stored image path → URL. Paths starting with "/" are bundled files; others live in Supabase Storage. */
export function imgUrl(path: string | null | undefined, fallback = "/placeholder/hero.jpg"): string {
  if (!path) return fallback;
  if (path.startsWith("/") || /^https?:\/\//.test(path)) return path;
  return `${SUPABASE_URL}/storage/v1/object/public/media/${path.split("/").map(encodeURIComponent).join("/")}`;
}

export function isStoragePath(path: string | null | undefined): path is string {
  return !!path && !path.startsWith("/") && !/^https?:\/\//.test(path);
}

export function money(price: number | string | null | undefined): string {
  if (price === null || price === undefined || price === "") return "Price on request";
  const n = Number(price);
  return "GH₵ " + n.toLocaleString("en-GH", { minimumFractionDigits: n % 1 ? 2 : 0, maximumFractionDigits: 2 });
}

/** Ghana number like "054 847 3309" → "233548473309". */
export function waNumber(raw: string): string {
  let d = (raw || "").replace(/\D+/g, "");
  if (d.startsWith("0")) d = "233" + d.slice(1);
  return d;
}

export function waLink(settings: Settings, message = ""): string {
  return `https://wa.me/${waNumber(s(settings, "whatsapp_number"))}${message ? "?text=" + encodeURIComponent(message) : ""}`;
}

export function productOrderMessage(p: Pick<Product, "name" | "price" | "slug">): string {
  return `Hello Bio Fashion Hub, I'd like to order:\n*${p.name}*${p.price !== null ? ` (${money(p.price)})` : ""}\n${SITE_URL}/product/${p.slug}`;
}

export function telHref(phone: string): string {
  return "tel:" + phone.replace(/\s+/g, "");
}

export function slugify(text: string): string {
  const slug = text.toLowerCase().normalize("NFKD").replace(/[^\w\s-]/g, "").trim().replace(/[\s_-]+/g, "-").replace(/^-+|-+$/g, "");
  return slug || "item";
}

export function formatDate(iso: string, opts: Intl.DateTimeFormatOptions = { day: "numeric", month: "short", year: "numeric" }): string {
  return new Date(iso).toLocaleDateString("en-GB", { timeZone: "Africa/Accra", ...opts });
}

export function initials(name: string): string {
  return name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
}
