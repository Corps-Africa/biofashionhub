import { ActionForm, SubmitButton } from "@/components/admin/ActionForm";
import { setNewPassword } from "../../actions";

export const metadata = { title: "Choose a new password" };

export default function ResetPage() {
  return (
    <>
      <h1>Choose a new password</h1>
      <ActionForm action={setNewPassword} className="a-card a-form">
        <div className="a-field"><label htmlFor="password">New password (at least 10 characters)</label><input id="password" name="password" type="password" autoComplete="new-password" minLength={10} required /></div>
        <div className="a-field"><label htmlFor="password2">Repeat new password</label><input id="password2" name="password2" type="password" autoComplete="new-password" minLength={10} required /></div>
        <SubmitButton>Save new password</SubmitButton>
      </ActionForm>
    </>
  );
}
