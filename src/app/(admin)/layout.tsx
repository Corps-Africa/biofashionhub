import type { Metadata } from "next";
import "@/styles/admin.css";
import Document from "@/components/Document";
import { baseViewport } from "@/lib/meta";
import { SITE_URL, SUPABASE_CONFIGURED } from "@/lib/supabase/env";

export const metadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  title: { default: "Admin · Bio Fashion Hub", template: "%s · Bio Fashion Hub admin" },
  robots: { index: false, follow: false },
  icons: { icon: "/favicon.png" },
};
export const viewport = baseViewport;
export const dynamic = "force-dynamic";

export default function AdminRoot({ children }: { children: React.ReactNode }) {
  if (!SUPABASE_CONFIGURED) {
    return (
      <Document bodyClass="admin">
        <main className="a-auth">
          <div className="a-card a-auth-card">
            <h1>Admin isn’t connected yet</h1>
            <p>The website hasn’t been connected to its Supabase database.</p>
            <p className="a-help">In Netlify, add <code>NEXT_PUBLIC_SUPABASE_URL</code> and <code>NEXT_PUBLIC_SUPABASE_ANON_KEY</code> under Site configuration → Environment variables, then redeploy. See the README for details.</p>
          </div>
        </main>
      </Document>
    );
  }
  return <Document bodyClass="admin">{children}</Document>;
}
