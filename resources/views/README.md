# Interlude Coffee & Tea - Laravel Blade

Template user page aplikasi reservasi cafe menggunakan Laravel Blade, Tailwind CDN, dan vanilla JavaScript.

## Struktur

```txt
resources/views/
├── layouts/
│   └── cafe.blade.php          # Layout utama
├── components/
│   ├── head.blade.php          # Head HTML + Tailwind config
│   ├── navbar.blade.php        # Navbar reusable
│   ├── footer.blade.php        # Footer reusable
│   ├── page-hero.blade.php     # Optional reusable page hero
│   └── menu-card.blade.php     # Card menu reusable
├── index.blade.php             # Landing page
├── menu.blade.php              # Menu page
├── cart.blade.php              # Cart -> Choose Number -> Reserved
└── about.blade.php             # About page

config/
└── cafe.php                    # Config app mini + data menu

public/assets/
├── images/
│   ├── hero.png
│   └── about.png
└── js/
    └── app.js                  # Cart, filter menu, flow reservasi
```

## Route

- `/` -> Landing
- `/menu` -> Menu
- `/cart` -> Cart reservasi
- `/about` -> About

## Catatan

- Data cart dan reservasi disimpan di `localStorage` browser untuk demo front-end.
- Belum ada database dan backend submit permanen.
- Tailwind dipakai via CDN agar tetap sederhana tanpa build step.
