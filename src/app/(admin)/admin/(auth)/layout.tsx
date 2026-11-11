import Link from "next/link";
import { Suspense } from "react";
import Flash from "@/components/admin/Flash";

export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <main className="a-auth">
      <div className="a-card a-auth-card">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img className="a-auth-logo" src="/logo-mark.png" alt="Bio Fashion Hub" width={90} />
        <Suspense><Flash /></Suspense>
        {children}
        <p className="a-help center"><Link href="/">← Back to website</Link></p>
      </div>
    </main>
  );
}
