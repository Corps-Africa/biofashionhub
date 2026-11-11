export default function PageHero({ eyebrow, title, text }: { eyebrow: string; title: string; text?: string }) {
  return (
    <section className="page-hero">
      <div className="wrap">
        <p className="eyebrow">{eyebrow}</p>
        <h1>{title}</h1>
        {text && <p className="lead">{text}</p>}
        <span className="rule" aria-hidden="true" />
      </div>
    </section>
  );
}
