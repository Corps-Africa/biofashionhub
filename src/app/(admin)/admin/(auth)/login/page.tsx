import Link from "next/link";
import { ActionForm, SubmitButton } from "@/components/admin/ActionForm";
import { signIn } from "../../actions";

export const metadata = { title: "Sign in" };

export default function LoginPage() {
  return (
    <>
      <h1>Admin sign in</h1>
      <ActionForm action={signIn}>
        <div className="a-field"><label htmlFor="email">Email</label><input id="email" name="email" type="email" autoComplete="username" required autoFocus /></div>
        <div className="a-field"><label htmlFor="password">Password</label><input id="password" name="password" type="password" autoComplete="current-password" required /></div>
        <SubmitButton className="a-btn a-btn-block" pendingText="Signing in…">Sign in</SubmitButton>
      </ActionForm>
      <p className="a-help center"><Link href="/admin/forgot">Forgot your password?</Link></p>
    </>
  );
}
