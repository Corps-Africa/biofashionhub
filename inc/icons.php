<?php
/** Inline line icons in the flyer's outline style. */
function icon(string $name, string $class = 'icon'): string
{
    $p = [
        'kaftan'   => '<path d="M9 3h6l1.5 2.5L21 8l-2 5-2.5-1V21h-9v-9L5 13 3 8l4.5-2.5z"/><path d="M12 5v9"/><circle cx="12" cy="8" r=".6"/><circle cx="12" cy="11" r=".6"/><path d="M13.6 14.5h2"/>',
        'fabric'   => '<path d="M4 9a4 4 0 1 1 8 0v7a4 4 0 1 1-8 0z"/><circle cx="8" cy="9" r="1.6"/><path d="M12 16h7.5L21 20H8"/>',
        'hat'      => '<ellipse cx="12" cy="8" rx="8" ry="2.6"/><path d="M4 8v8c0 1.6 3.6 2.8 8 2.8s8-1.2 8-2.8V8"/><path d="m5 14 2-2 2 2 2-2 2 2 2-2 2 2 2-2"/><circle cx="12" cy="5" r=".9"/>',
        'watch'    => '<circle cx="12" cy="12" r="6"/><path d="M12 9v3l2 1.5"/><path d="M9 6.8 9.6 2h4.8l.6 4.8M9 17.2l.6 4.8h4.8l.6-4.8"/>',
        'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.1 15.9M14.5 14.5 20 20M8.1 8.1 12 12"/>',
        'whatsapp' => '<path d="M3.5 20.5l1.3-4.2A8.5 8.5 0 1 1 8 19.3z"/><path d="M9 8.6c0 3 2.4 5.9 5.9 6.6l1.2-1.4-1.8-1-1 .8c-1-.4-2-1.4-2.4-2.4l.8-1-1-1.8z" fill="currentColor" stroke="none"/>',
        'phone'    => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'pin'      => '<path d="M12 21s-7-6.2-7-12a7 7 0 1 1 14 0c0 5.8-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z"/>',
        'tiktok'   => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 3c.4 2.8 2.3 4.6 5 4.8"/>',
        'instagram'=> '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'star'     => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z" fill="currentColor"/>',
        'check'    => '<path d="m5 12 5 5L20 7"/>',
        'ruler'    => '<rect x="2" y="8" width="20" height="8" rx="1"/><path d="M6 8v3M10 8v4M14 8v3M18 8v4"/>',
        'chat'     => '<path d="M4 5h16v11H8l-4 4z"/><path d="M8 9h8M8 12h5"/>',
        'bag'      => '<path d="M5 8h14l-1 13H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
    ][$name] ?? '';
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}
