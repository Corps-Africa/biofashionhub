import { notFound } from "next/navigation";

// Sends any unknown address to the branded 404 page.
export default function Missing() {
  notFound();
}
