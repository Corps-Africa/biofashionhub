/** Shared <html> shell for the two root layouts (public site and admin). */
export default function Document({ children, bodyClass }: { children: React.ReactNode; bodyClass?: string }) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        {/* eslint-disable-next-line @next/next/no-page-custom-font */}
        <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..600&family=Montserrat:wght@500;600;700&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
      </head>
      <body className={bodyClass}>{children}</body>
    </html>
  );
}
