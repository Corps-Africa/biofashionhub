import type { Metadata } from "next";
import "@/styles/admin.css";
import Document from "@/components/Document";
import { baseViewport } from "@/lib/meta";
import { SITE_URL } from "@/lib/supabase/env";

export const metadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  title: { default: "Admin · Bio Fashion Hub", template: "%s · Bio Fashion Hub admin" },
  robots: { index: false, follow: false },
  icons: { icon: "/favicon.png" },
};
export const viewport = baseViewport;
export const dynamic = "force-dynamic";

export default function AdminRoot({ children }: { children: React.ReactNode }) {
  return <Document bodyClass="admin">{children}</Document>;
}
