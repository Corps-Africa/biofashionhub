import { ActionForm, ConfirmButton, SubmitButton } from "@/components/admin/ActionForm";
import { requireAdmin, serviceClient } from "@/lib/admin";
import { addAdmin, changePassword, removeAdmin, updateName } from "../../actions";

export const metadata = { title: "My account" };

export default async function AccountAdmin() {
  const { sb, user } = await requireAdmin();
  const { data: admins } = await sb.from("admins").select("user_id, display_name, created_at").order("created_at");
  const svc = serviceClient();
  const emails = new Map<string, string>();
  if (svc) (await svc.auth.admin.listUsers({ perPage: 200 })).data?.users.forEach((u) => emails.set(u.id, u.email ?? ""));
  const me = admins?.find((a) => a.user_id === user.id);

  return (
    <>
      <h1>My account</h1>
      <form action={updateName} className="a-card a-form">
        <h2>Your name</h2>
        <div className="a-field"><label htmlFor="display_name">Display name</label><input id="display_name" name="display_name" defaultValue={me?.display_name} /></div>
        <p className="a-help">Signed in as {user.email}</p>
        <SubmitButton className="a-btn a-btn-sm">Save</SubmitButton>
      </form>

      <ActionForm action={changePassword} className="a-card a-form">
        <h2>Change password</h2>
        <input type="text" name="username" value={user.email} autoComplete="username" hidden readOnly />
        <div className="a-field"><label htmlFor="current">Current password</label><input id="current" name="current" type="password" autoComplete="current-password" required /></div>
        <div className="a-grid-2">
          <div className="a-field"><label htmlFor="new">New password (10+ characters)</label><input id="new" name="new" type="password" autoComplete="new-password" minLength={10} required /></div>
          <div className="a-field"><label htmlFor="new2">Repeat new password</label><input id="new2" name="new2" type="password" autoComplete="new-password" minLength={10} required /></div>
        </div>
        <SubmitButton className="a-btn a-btn-sm">Change password</SubmitButton>
      </ActionForm>

      <section className="a-card">
        <h2>Who can sign in</h2>
        <ul className="a-items a-items-flat">
          {(admins ?? []).map((a) => (
            <li key={a.user_id} className="a-item">
              <div className="a-item-main">
                <span className="a-item-title">{a.display_name || emails.get(a.user_id) || "Admin"}</span>
                {emails.get(a.user_id) && <span className="a-sub">{emails.get(a.user_id)}</span>}
              </div>
              {a.user_id === user.id ? <span className="a-tag">You</span> : svc && (
                <form action={removeAdmin}><input type="hidden" name="user_id" value={a.user_id} /><ConfirmButton message="Remove this person’s access?">Remove</ConfirmButton></form>
              )}
            </li>
          ))}
        </ul>
        {svc ? (
          <details className="a-details">
            <summary>Add another person</summary>
            <ActionForm action={addAdmin} className="a-form">
              <div className="a-grid-2">
                <div className="a-field"><label htmlFor="dn">Their name</label><input id="dn" name="display_name" /></div>
                <div className="a-field"><label htmlFor="ne">Their email</label><input id="ne" name="email" type="email" required /></div>
              </div>
              <div className="a-field"><label htmlFor="np">Temporary password (10+ characters)</label><input id="np" name="password" type="password" autoComplete="new-password" minLength={10} required />
                <p className="a-help">Share it with them privately. They can change it here, or use “Forgot password”.</p></div>
              <SubmitButton className="a-btn a-btn-sm">Add admin</SubmitButton>
            </ActionForm>
          </details>
        ) : <p className="a-help">To add or remove admins here, set <code>SUPABASE_SERVICE_ROLE_KEY</code> in Netlify. Otherwise use <code>npm run create-admin</code> (see the README).</p>}
      </section>
    </>
  );
}
