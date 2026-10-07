=== TMS Core Essentials ===
Contributors: entumas
Tags: security, performance, privacy, administration, frontend functionalities
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Core utilities and shared essentials for TMS WordPress projects.


== Description ==

TMS Core Essentials is a modular toolkit for WordPress sites: security and performance hardening, admin utilities, custom fields, content helpers, privacy tools, social features, and a PHP/JS API for themes and custom code.

Configure everything from **Settings → TMS Core Essentials**. Enable only what you need.

**Included areas:**
- Content (breadcrumbs, sitemap, related content, extend search, scroll to top)
- Fields (body classes, subtitle, hero, featured video, term images)
- Social and contact (social menu, share buttons, chats)
- Privacy and forms (consent, Google Consent Mode v2, privacy notices)
- Administration (Gutenberg, lists, comments, admin bar, duplicate posts, SVG uploads)
- Login (custom login page, logout redirect)
- Security and performance (login hardening, head cleanup)
- Extras (frontend assets, bundled libraries, SVG icons, shortcodes)
- Developers reference (PHP API and frontend JavaScript utilities)

**Bundled translations:**
- Catalan (`ca`)
- English (US) (`en_US`)
- French (France) (`fr_FR`)
- German (`de_DE`)
- Italian (`it_IT`)
- Portuguese (Portugal) (`pt_PT`)
- Spanish (Spain) (`es_ES`)

WordPress loads the matching file when the site language uses the same locale.


== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Activate it from the Plugins section in WordPress.
3. Open **Settings → TMS Core Essentials** to configure the plugin.


== Source Code ==

JavaScript and CSS shipped in this plugin are compiled from human-readable source files.

* **Public repository:** https://github.com/entumas/tms-core-essentials
* **Source authoring:** `_src/` at the repository root (JavaScript modules and SCSS)
* **Build tool:** [Prepros](https://prepros.io/) using `prepros.config` at the repository root
* **Compiled output in this plugin:** `assets/js/` and `assets/css/`
* **PHP:** included as readable source under `includes/`

To build from source: clone the repository, open the project root in Prepros, then compile the configured `_src/` entry files. The installable plugin directory is `tms-core-essentials/`.


== Changelog ==

= 1.0.0 =
* NEW: PHP API: tcres_option_get(), tcres_field_get(), tcres_tax_field_get(), tcres_post_meta_update(), tcres_term_meta_update(), tcres_post_types_get_included(), tcres_taxonomies_get_included(), tcres_option_get_for_post_types(), tcres_option_get_for_taxonomies(), tcres_option_get_for_post_types_diff(), tcres_slug_convert_to_id(), tcres_svg_icon_get(), tcres_date_format(), tcres_image_validate_size(), tcres_template_get_info(), and tcres_image_size_get_registered().
* NEW: JavaScript API: window.tcresCollapseInit() and window.tcresTabsInit() for frontend collapse/accordion and tabs markup.
* NEW: JavaScript API: window.tcresModalInit(), window.tcresShowModal(), and window.tcresHideModal() for frontend modal dialogs.
* NEW: Security: hide login error messages, block user enumeration, block author archives, disable XML-RPC, and block proxy visits.
* NEW: Performance: remove WordPress version, DNS prefetch, RSD, WLW manifest, shortlink, REST API link, oEmbed discovery, emoji scripts, and the wp-embed script.
* NEW: Disable Gutenberg: turn off the block editor per post type and use the classic editor instead.
* NEW: Disable post types: hide selected post type admin screens and redirect their frontend URLs to the homepage.
* NEW: Disable taxonomies: hide selected taxonomy admin screens and redirect their frontend archives to the homepage.
* NEW: Disable comments: remove comments from the admin, admin bar, and frontend for all post types.
* NEW: Clean admin bar: hide selected admin bar nodes per user role, including custom node IDs.
* NEW: Featured image column: add an image column to selected post type list tables.
* NEW: Sortable columns: make author and taxonomy columns sortable per post type.
* NEW: Drag to reorder (Posts list): reorder posts by menu_order with a drag handle on selected post type lists.
* NEW: Drag to reorder (Terms list): reorder terms with a drag handle and an Order field on the term edit screen.
* NEW: Duplicate posts: duplicate selected post types from the admin list (status, taxonomies, meta, featured image).
* NEW: Allow SVG uploads: allow sanitized SVG uploads in the Media Library for selected roles.
* NEW: Custom login page: use the Site Icon as the login logo and link it to the site home and site name.
* NEW: Logout redirect: after logout, always redirect to home, login, a WooCommerce page, or a custom URL.
* NEW: Privacy consent: required privacy policy checkbox on comments, registration, and WooCommerce checkout.
* NEW: Google Consent Mode v2: default Google consent signals to denied and update them with GDPR Cookie Compliance when statistics or marketing cookies are accepted.
* NEW: Privacy notice: collapsible privacy notices for contact, subscribe, comments, register, and checkout (CF7, WPForms, WooCommerce).
* NEW: Body classes: add custom CSS classes to the frontend body on singular posts and taxonomy archives.
* NEW: Subtitle: WYSIWYG subtitle field for posts and terms, available via tcres_subtitle_get().
* NEW: Hero: hero block with title, image or featured video background, subtitle, description, and buttons via tcres_hero_get().
* NEW: Featured video: featured video field for posts and terms, available via tcres_featured_video_get().
* NEW: Featured image for terms: featured image field on term screens, available via tcres_term_image_get().
* NEW: Breadcrumbs: configurable breadcrumbs with tcres_breadcrumb_get() and [tcres-breadcrumb].
* NEW: Sitemap: configurable HTML sitemap with tcres_sitemap_get() and [tcres-sitemap].
* NEW: Related content: related posts metabox plus automatic taxonomy picks via tcres_related_content_get() / [tcres-related-content].
* NEW: Extend search: extend frontend search by post type, custom fields, and optional taxonomy terms.
* NEW: Scroll to top: floating button that scrolls the page back to the top.
* NEW: Social menu: Social menu theme location with SVG icons via tcres_social_menu_get() / [tcres-social-menu].
* NEW: Share content: social share buttons via tcres_share_content() / tcres_share_content_get() / [tcres-share-content].
* NEW: Chats: floating WhatsApp, Telegram, and/or Messenger buttons in the footer.
* NEW: Frontend assets: options to disable the plugin frontend CSS and JS.
* NEW: External scripts: optional Swiper, GLightbox, Choices, and Smooth scroll (Lenis), bundled with the plugin.
* NEW: SVG icons: configurable admin/frontend sprite URLs for tcres_svg_icon_get().
* NEW: Shortcodes: [tcres-field], [tcres-tax-field], [tcres-option], [tcres-button], [tcres-highlighted], [tcres-svgicon], [tcres-youtube], and [tcres-vimeo].