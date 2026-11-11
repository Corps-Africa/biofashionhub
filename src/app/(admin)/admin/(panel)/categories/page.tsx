import { ActionForm, ConfirmButton, SubmitButton } from "@/components/admin/ActionForm";
import Icon, { ICON_CHOICES } from "@/components/Icon";
import { requireAdmin } from "@/lib/admin";
import type { Category } from "@/lib/types";
import { deleteCategory, saveCategory } from "../../actions";

export const metadata = { title: "Categories" };

function CategoryForm({ c }: { c?: Category }) {
  return (
    <ActionForm action={saveCategory}>
      <input type="hidden" name="id" value={c?.id ?? 0} />
      <div className="a-grid-cat">
        <div className="a-field"><label htmlFor={`n${c?.id ?? 0}`}>Name</label><input id={`n${c?.id ?? 0}`} name="name" required defaultValue={c?.name} /></div>
        <div className="a-field"><label htmlFor={`t${c?.id ?? 0}`}>Short description</label><input id={`t${c?.id ?? 0}`} name="tagline" defaultValue={c?.tagline} /></div>
        <div className="a-field"><label htmlFor={`i${c?.id ?? 0}`}>Icon</label>
          <select id={`i${c?.id ?? 0}`} name="icon" defaultValue={c?.icon ?? "kaftan"}>
            {Object.entries(ICON_CHOICES).map(([k, l]) => <option key={k} value={k}>{l}</option>)}
          </select></div>
        <div className="a-field"><label htmlFor={`s${c?.id ?? 0}`}>Order</label><input id={`s${c?.id ?? 0}`} name="sort" type="number" defaultValue={c?.sort ?? 0} /></div>
      </div>
      <SubmitButton className="a-btn a-btn-sm">{c ? "Save" : "Add category"}</SubmitButton>
    </ActionForm>
  );
}

export default async function CategoriesAdmin() {
  const { sb } = await requireAdmin();
  const [{ data: cats }, { data: prods }] = await Promise.all([sb.from("categories").select("*").order("sort").order("name"), sb.from("products").select("category_id")]);
  const counts = new Map<number, number>();
  (prods ?? []).forEach((p) => p.category_id && counts.set(p.category_id, (counts.get(p.category_id) ?? 0) + 1));
  return (
    <>
      <h1>Categories</h1>
      <p className="a-help">Categories group your products in the shop and appear as the “What we offer” list on the home page.</p>
      {((cats ?? []) as Category[]).map((c) => (
        <div key={c.id} className="a-card">
          <div className="a-card-head">
            <h2><Icon name={c.icon} /> {c.name} <span className="a-sub">({counts.get(c.id) ?? 0} products)</span></h2>
            <form action={deleteCategory}><input type="hidden" name="id" value={c.id} /><ConfirmButton message={`Delete the “${c.name}” category? Products in it will be kept.`}>Delete</ConfirmButton></form>
          </div>
          <CategoryForm c={c} />
        </div>
      ))}
      <div className="a-card a-card-new"><h2>Add a new category</h2><CategoryForm /></div>
    </>
  );
}
