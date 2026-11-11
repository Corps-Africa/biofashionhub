import type { Metadata, Viewport } from "next";
import { SITE_URL } from "./supabase/env";

export const baseMetadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  title: { default: "Bio Fashion Hub — Look Good. Feel Confident.", template: "%s | Bio Fashion Hub" },
  description: "Quality kaftans, fabrics, Hausa caps, wristwatches and custom outfits in Tamale. Order easily on WhatsApp.",
  icons: { icon: "/favicon.png", apple: "/favicon.png" },
  openGraph: { type: "website", siteName: "Bio Fashion Hub", images: ["/placeholder/hero.jpg"] },
};

export const baseViewport: Viewport = { themeColor: "#071938", width: "device-width", initialScale: 1 };
