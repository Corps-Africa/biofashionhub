export type Settings = Record<string, string>;
export type Category = { id: number; name: string; slug: string; tagline: string; icon: string; sort: number };
export type Product = {
  id: number; category_id: number | null; name: string; slug: string; price: number | null;
  description: string; details: string; image: string; image2: string;
  featured: boolean; in_stock: boolean; published: boolean; sort: number; created_at: string;
  categories?: { name: string; slug: string } | null;
};
export type Post = { id: number; title: string; slug: string; excerpt: string; body: string; image: string; published: boolean; published_at: string };
export type Testimonial = { id: number; name: string; role: string; quote: string; rating: number; image: string; published: boolean; sort: number };
export type GalleryItem = { id: number; image: string; caption: string; sort: number };
export type Enquiry = {
  id: number; type: "contact" | "custom"; name: string; phone: string; email: string; occasion: string;
  preferred_date: string | null; message: string; is_read: boolean; created_at: string;
};
