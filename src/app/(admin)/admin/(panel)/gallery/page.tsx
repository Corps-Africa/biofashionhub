import { ConfirmButton } from "@/components/admin/ActionForm";
import GalleryUploader from "@/components/admin/GalleryUploader";
import { requireAdmin } from "@/lib/admin";
import type { GalleryItem } from "@/lib/types";
import { imgUrl } from "@/lib/util";
import { deleteGalleryPhoto, saveGallery } from "../../actions";

export const metadata = { title: "Lookbook" };

export default async function GalleryAdmin() {
  const { sb } = await requireAdmin();
  const items = ((await sb.from("gallery").select("*").order("sort").order("id", { ascending: false })).data ?? []) as GalleryItem[];
  return (
    <>
      <h1>Lookbook photos</h1>
      <GalleryUploader />
      {items.length > 0 && (
        <>
          <form id="captions" action={saveGallery} />
          <div className="a-gallery">
            {items.map((g) => (
              <div key={g.id} className="a-gal-item">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={imgUrl(g.image)} alt="" />
                <input type="hidden" form="captions" name="id" value={g.id} />
                <label className="sr-only" htmlFor={`cap-${g.id}`}>Caption</label>
                <input form="captions" id={`cap-${g.id}`} name={`caption_${g.id}`} defaultValue={g.caption} placeholder="Caption" />
                <div className="a-gal-row">
                  <label className="a-sub" htmlFor={`sort-${g.id}`}>Order</label>
                  <input form="captions" id={`sort-${g.id}`} className="a-sort" type="number" name={`sort_${g.id}`} defaultValue={g.sort} />
                  <form action={deleteGalleryPhoto}><input type="hidden" name="id" value={g.id} /><ConfirmButton message="Remove this photo from the lookbook?">Remove</ConfirmButton></form>
                </div>
              </div>
            ))}
          </div>
          <div className="a-actions a-sticky"><button className="a-btn" type="submit" form="captions">Save captions &amp; order</button></div>
        </>
      )}
    </>
  );
}
