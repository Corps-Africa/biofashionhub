import Link from "next/link";
import { ActionForm, SubmitButton } from "@/components/admin/ActionForm";
import { sendReset } from "../../actions";

export const metadata = { title: "Reset password" };

export default function ForgotPage() {
  return (
    <>
      <h1>Reset your password</h1>
      <p className="a-help">Enter your admin email and we’ll send you a link to choose a new password.</p>
      <ActionForm action={sendReset}>
        <div className="a-field"><label htmlFor="email">Email</label><input id="email" name="email" type="email" autoComplete="username" required /></div>
        <SubmitButton className="a-btn a-btn-block" pendingText="Sending…">Send reset link</SubmitButton>
      </ActionForm>
      <p className="a-help center"><Link href="/admin/login">Back to sign in</Link></p>
    </>
  );
}
