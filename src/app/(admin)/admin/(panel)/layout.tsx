import Link from "next/link";
import { Suspense } from "react";
import AdminMenuButton from "@/components/admin/AdminMenuButton";
import Flash from "@/components/admin/Flash";
import SideNav from "@/components/admin/SideNav";
import { requireAdmin } from "@/lib/admin";
import { signOut } from "../actions";

export default async function PanelLayout({ children }: { children: React.ReactNode }) {
  const { sb, user, isAdmin } = await requireAdmin();
  if (!isAdmin) {
    return (
      <main className="a-auth">
        <div className="a-card a-auth-card">
          <h1>No access</h1>
          <p>You’re signed in as {user.email}, but this account isn’t an admin for this website.</p>
          <form action={signOut}><button className="a-btn a-btn-block">Sign out</button></form>
        </div>
      </main>
    );
  }
  const [{ count }, { data: me }] = await Promise.all([
    sb.from("enquiries").select("id", { count: "exact", head: true }).eq("is_read", false),
    sb.from("admins").select("display_name").eq("user_id", user.id).single(),
  ]);

  return (
    <>
      <header className="a-top">
        <AdminMenuButton />
        <Link className="a-brand" href="/admin">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src="/logo-mark.png" alt="" width={34} /> <span>Bio Fashion Hub</span>
        </Link>
        <a className="a-view" href="/" target="_blank" rel="noopener">View website ↗</a>
      </header>
      <div className="a-shell">
        <nav className="a-side" id="a-side" aria-label="Admin">
          <SideNav unread={count ?? 0} />
          <form action={signOut} className="a-logout">
            <p>Signed in as <strong>{me?.display_name || user.email}</strong></p>
            <button type="submit" className="a-btn a-btn-ghost a-btn-sm">Sign out</button>
          </form>
        </nav>
        <main className="a-main">
          <Suspense><Flash /></Suspense>
          {children}
        </main>
      </div>
    </>
  );
}
