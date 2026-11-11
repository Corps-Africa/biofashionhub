import { ActionForm, SubmitButton } from "@/components/admin/ActionForm";
import ImageField from "@/components/admin/ImageField";
import { requireAdmin } from "@/lib/admin";
import { SETTING_FIELDS, type SettingField } from "@/lib/settingFields";
import { waLink } from "@/lib/util";
import { saveSettings } from "../../actions";

export const metadata = { title: "Site settings" };

export default async function SettingsAdmin() {
  const { sb } = await requireAdmin();
  const st = Object.fromEntries(((await sb.from("settings").select("key, value")).data ?? []).map((r) => [r.key, r.value]));
  const groups = SETTING_FIELDS.reduce<Record<string, SettingField[]>>((acc, f) => ((acc[f.group] ??= []).push(f), acc), {});

  return (
    <>
      <h1>Site settings</h1>
      <ActionForm action={saveSettings} className="a-form">
        {Object.entries(groups).map(([group, fields]) => (
          <section key={group} className="a-card">
            <h2>{group}</h2>
            {fields.map((f) => {
              const v = st[f.key] ?? "";
              if (f.type === "image") return <ImageField key={f.key} name={f.key} label={f.label} value={v} folder="site" help={f.help} />;
              if (f.type === "checkbox") return (
                <div key={f.key}>
                  <label className="a-check"><input type="checkbox" name={f.key} defaultChecked={v === "1"} /> {f.label}</label>
                  {f.help && <p className="a-help">{f.help}</p>}
                </div>
              );
              return (
                <div key={f.key} className="a-field">
                  <label htmlFor={f.key}>{f.label}</label>
                  {f.type === "textarea"
                    ? <textarea id={f.key} name={f.key} rows={f.key === "about_story" ? 7 : 3} defaultValue={v} />
                    : <input id={f.key} name={f.key} type={f.type === "url" ? "text" : f.type} inputMode={f.type === "url" ? "url" : undefined} placeholder={f.type === "url" ? "https://" : undefined} defaultValue={v} />}
                  {f.help && <p className="a-help">{f.help}</p>}
                </div>
              );
            })}
            {group === "Business details" && <p className="a-help">Test your WhatsApp link: <a href={waLink(st, "Test message from the website")} target="_blank" rel="noopener">{waLink(st)}</a></p>}
          </section>
        ))}
        <div className="a-actions a-sticky"><SubmitButton>Save settings</SubmitButton></div>
      </ActionForm>
    </>
  );
}
