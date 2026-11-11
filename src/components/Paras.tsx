import { Fragment } from "react";

/** Plain text → paragraphs. Blank line = new paragraph, single newline = line break, **bold**. Never renders HTML. */
export default function Paras({ text, className }: { text: string | null | undefined; className?: string }) {
  const blocks = (text ?? "").replace(/\r\n/g, "\n").trim().split(/\n{2,}/).filter(Boolean);
  if (!blocks.length) return null;
  return (
    <div className={className}>
      {blocks.map((block, i) => (
        <p key={i}>
          {block.split("\n").map((line, j) => (
            <Fragment key={j}>
              {j > 0 && <br />}
              {line.split(/(\*\*[^*]+\*\*)/g).map((part, k) =>
                part.startsWith("**") && part.endsWith("**") && part.length > 4
                  ? <strong key={k}>{part.slice(2, -2)}</strong>
                  : <Fragment key={k}>{part}</Fragment>)}
            </Fragment>
          ))}
        </p>
      ))}
    </div>
  );
}
