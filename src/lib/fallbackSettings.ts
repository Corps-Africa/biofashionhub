import type { Settings } from "./types";

/**
 * Used only when the database can't be reached (or isn't connected yet), so the
 * header, footer and contact details still show the real business information.
 * Mirrors supabase/seed.sql.
 */
export const FALLBACK_SETTINGS: Settings = {
  business_name: "Bio Fashion Hub",
  tagline: "Look Good. Feel Confident.",
  whatsapp_number: "054 847 3309",
  phone_1: "054 847 3309",
  phone_2: "020 524 8123",
  email: "abdulaisumaila044@gmail.com",
  city: "Tamale",
  address: "Tamale, Northern Region, Ghana",
  map_query: "Tamale, Ghana",
  opening_hours: "Mon – Sat: 8:00am – 7:00pm\nSunday: By appointment",
  hero_eyebrow: "Tamale · Kaftans · Fabrics · Accessories",
  hero_title: "Dressed with intention. Made to stand out.",
  hero_text: "Ready-to-wear kaftans, quality fabrics, classic Hausa caps and timeless watches — plus custom outfits tailored for you, your groom’s party or your big occasion.",
  custom_title: "Made for your moment",
  custom_text: "Weddings, naming ceremonies, Eid, graduations or simply a look that is yours alone. Tell us the occasion, we’ll take your measurements and craft an outfit that fits perfectly.",
  about_intro: "Bio Fashion Hub is a fashion and lifestyle brand dedicated to providing quality, stylish and affordable clothing and accessories.",
  about_story: "We believe what you wear shapes how you show up. Bio Fashion Hub was started in Tamale to help people express their identity, look their best and carry themselves with confidence.\n\nWe specialise in fabrics, traditional wear, kaftans, Hausa caps, wristwatches and customised outfits for individuals and special occasions — serving men and women, young professionals, fashion lovers and grooms who want elegance without overspending.",
  about_mission: "To help every customer look good and feel confident through quality, affordable and personalised fashion.",
  ceo_name: "Sumaila Abdulai",
  ceo_role: "Founder & CEO",
  ceo_bio: "Fashion is how we tell the world who we are before we say a word. At Bio Fashion Hub, my promise is simple: quality you can feel, styles you will love, and prices that make sense.",
};
