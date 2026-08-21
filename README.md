# TMS Core Essentials

Development monorepo for [TMS Core Essentials](https://wordpress.org/plugins/tms-core-essentials): a modular toolkit for WordPress with security and performance hardening, admin utilities, custom fields, content helpers, privacy tools, social features, and a PHP/JS API for themes and custom code.

**Requirements:** WordPress **6.0+** and PHP **8.0+**. No other plugins required.

**Install:** Copy **only** the folder `tms-core-essentials/` from this repository into `wp-content/plugins/` (do not place the whole monorepo root there). The folder name under `plugins/` becomes the plugin directory name in WordPress. Activate **TMS Core Essentials** in the admin.

After activation, configure modules under **Settings → TMS Core Essentials**.

**License:** GPLv2 or later (same as declared in the plugin header and `readme.txt`).

End-user documentation and changelog live in [`tms-core-essentials/readme.txt`](tms-core-essentials/readme.txt) (WordPress.org format).

---

## Features (overview)

- **Content:** Breadcrumbs, sitemap, related content, extend search, scroll to top
- **Fields:** Body classes, subtitle, hero, featured video, featured image for terms
- **Social and contact:** Social menu, share content, chats (WhatsApp, Telegram, Messenger)
- **Privacy and forms:** Privacy consent, Google Consent Mode v2, privacy notices
- **Administration:** Disable Gutenberg / post types / taxonomies / comments, clean admin bar, list columns, drag to reorder, duplicate posts, SVG uploads
- **Login:** Custom login page, logout redirect
- **Security and performance:** Login hardening, head cleanup
- **Extras:** Frontend assets, CDN libraries (Swiper, GLightbox, Choices, Lenis), SVG icons, shortcodes
- **Developers:** PHP API and frontend JavaScript utilities (collapse, tabs)

---

## Repository layout

```
tms-core-essentials/
├── _src/                    # Source SCSS and JS (authoring)
├── prepros.config           # Prepros compile configuration
├── tms-core-essentials/     # Installable WordPress plugin (deploy this folder)
│   ├── assets/              # Compiled CSS/JS
│   ├── includes/            # PHP modules, settings, API
│   ├── languages/           # Translations
│   └── readme.txt           # WordPress.org readme
└── README.md
```

---

## Development

Monorepo layout: installable plugin under `tms-core-essentials/`; frontend and admin assets are authored under `_src/` and compiled with **[Prepros](https://prepros.io/)** using `prepros.config` at the repo root (open that folder in Prepros and use watch or manual compile). No Prepros needed to run a release or directory copy—compiled files are already in the plugin tree.