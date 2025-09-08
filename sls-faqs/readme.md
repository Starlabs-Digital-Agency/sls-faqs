=== Starlabs FAQ Filter ===
Contributors: starlabs
Tags: faq, shortcode, custom post type, taxonomy, tabs, filter, accessibility, import, export, uninstall
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create FAQs with a custom post type + categories, display them via an accessible tabbed filter shortcode, style with an “Additional CSS” admin page, import/export JSON, and safely control uninstall behavior.

== Description ==

**Starlabs FAQ Filter** lets you manage FAQs as their own post type and organize them with a hierarchical **FAQ Categories** taxonomy. On the front-end, a single shortcode renders an **accessible tabbed UI** with instant, JS-driven category filtering and **deep-linking** to categories via `#slug`.

Admin tools include:

- **Additional CSS** page (CodeMirror editor) to override default styles.
- **Import/Export** FAQs and categories as JSON.
- **Uninstall behavior** control: optionally purge all FAQ posts and categories on uninstall.
- **Danger Zone**: one-click “Delete All FAQs Now” (nonce + confirmation).

Front-end is keyboard-navigable (WAI-ARIA tabs pattern), respects reduced motion, and shows a **category badge** on each FAQ (“None” if uncategorized).

**Highlights**
- FAQ **Custom Post Type** (`faq`) + **FAQ Categories** (`faq_category`)
- Shortcode: `[starlabs_faqs]`
- Accessible tabs (Left/Right/Home/End keys), deep-linking (`/faqs/#category-slug`)
- Category **badge** on each FAQ (primary category by alphabetical order; filter available)
- **Additional CSS** admin screen
- **Import/Export** JSON (optional upsert by slug)
- **Safe uninstall** with opt-in content purge
- Developer hooks and filters

= Accessibility =
- Tabs follow WAI-ARIA practices (`role="tablist"`, `aria-selected`, roving `tabindex`)
- Keyboard: **Left/Right** to move, **Home/End** to jump, **Enter/Space** to activate
- Honors `prefers-reduced-motion`

= Styling =
- Base CSS variables:
  `--faq-bg, --faq-bg-open, --faq-border, --faq-border-strong, --faq-text, --faq-muted, --faq-accent, --faq-radius, --faq-shadow`
- Dark mode via `prefers-color-scheme: dark`
- `.faq-badge` in the summary’s top-right; shows primary category or **None**

== Installation ==

1. Upload the `starlabs-faqs` folder to `/wp-content/plugins/` or install via **Plugins → Add New → Upload**.
2. Activate the plugin.
3. Go to **FAQs → Add New** and create FAQ posts. Assign **FAQ Categories**.
4. Add the shortcode to a page:
5. (Optional) Customize styles in **FAQs → Additional CSS**.

== Usage ==
[starlabs_faqs]
**Basic**
Displays all FAQs with tabs for all categories.
[starlabs_faqs categories="marketing,website,ecommerce"]

**Only specific categories**
Limits both the **tabs** and the **query** to those category slugs.

**Attributes**
- `categories` — comma-separated slugs (default: all)
- `per_page` — number of FAQs, `-1` for all (default: `-1`)
- `orderby` — `menu_order|title|date` (default: `menu_order`)
- `order` — `ASC|DESC` (default: `ASC`)
- `show_all_tab` — `true|false` (default: `true`)
- `update_hash` — `true|false` (default: `true`)

**Examples**
- Single category, hide the “All” tab:

[starlabs_faqs categories="marketing" show_all_tab="false"]
Optionally hide the tabs entirely via Additional CSS:


.starlabs-faqs .faq-tabs { display:none; }

- Deep-link to a category (no shortcode change):  
`https://example.com/faqs/#ecommerce` opens with **Ecommerce** selected.

**Finding slugs**
WP Admin → **FAQs → FAQ Categories** → Slug column (or Edit a category).

== Admin Tools ==

**Additional CSS**  
- **FAQs → Additional CSS**  
- Loads **after** the plugin CSS so your rules win.

**Import / Export**  
- **FAQs → Tools**  
- **Export** downloads all FAQs + categories as JSON.  
- **Import** accepts the same JSON. Optional **upsert** updates existing FAQs by slug.

**Uninstall behavior**  
- **FAQs → Tools**  
- Toggle **“On uninstall, also delete all FAQs and FAQ Categories”**.  
WordPress can’t show a plugin-specific dialog on delete; enabling this instructs `uninstall.php` to purge content.

**Danger Zone (Delete All FAQs Now)**  
- **FAQs → Tools**  
- Permanently deletes *all* FAQ posts and *all* FAQ categories. Protected by nonce + confirmation.

== Frequently Asked Questions ==

= How do I show only FAQs from certain categories? =
Use the `categories` attribute with slugs:


[starlabs_faqs categories="marketing,website"]

= Can I hide the “All” tab? =
Yes:

[starlabs_faqs show_all_tab="false"]

= How can I link users directly to a category? =
Share a URL with the category slug as a hash:  
`/faqs/#ecommerce`

= The tabs don’t filter. What should I check? =
1) Ensure CSS/JS loaded (DevTools → Network).  
2) Hard refresh; some caching/optimizer plugins defer scripts (the plugin handles async/defer, but caching may require a clear).  
3) Confirm each `.faq-item` has category classes (based on slugs).  
4) Ensure `.faq-item.is-hidden { display:none }` exists (it’s in the base CSS).

= Does uninstall remove all FAQs automatically? =
Only if you enable **“On uninstall, also delete all FAQs and FAQ Categories”** in **FAQs → Tools**. Otherwise, uninstall removes plugin options only.

== Developer Hooks ==

- `starlabs_faq_force_assets` (bool)  
  Force assets to load when shortcode detection might miss (e.g., builder templates):
  ```php
  add_filter('starlabs_faq_force_assets', '__return_true');


starlabs_faq_disable_default_css (bool)
Dequeue base CSS entirely (use with your own or Additional CSS):

php
Copy
Edit
add_filter('starlabs_faq_disable_default_css', '__return_true');
starlabs_faq_all_label (string)
Change the “All” tab label:

php
Copy
Edit
add_filter('starlabs_faq_all_label', fn() => 'Everything');
starlabs_faq_query_args (array)
Modify the WP_Query used by the shortcode:

php
Copy
Edit
add_filter('starlabs_faq_query_args', function($args){ $args['orderby']='date'; return $args; }, 10, 2);
starlabs_faq_render_item (string, int)
Override FAQ item markup before output:

php
Copy
Edit
add_filter('starlabs_faq_render_item', function($html, $post_id){
  return str_replace('<div class="faq-content">','<div class="faq-content"><div class="my-wrap">',$html);
}, 10, 2);
starlabs_faq_primary_term (WP_Term, array)
Choose which term becomes the “primary” (badge + first class):

php
Copy
Edit
add_filter('starlabs_faq_primary_term', function($primary, $terms){
  // Example: pick deepest term instead of alphabetical
  usort($terms, fn($a,$b)=> $a->parent <=> $b->parent);
  return end($terms);
}, 10, 2);
== Screenshots ==

Front-end tabbed FAQs with category badge.

Tools page with Export/Import, uninstall setting, and Danger Zone.

Additional CSS editor with CodeMirror.

== Changelog ==

= 1.3.0 =

New: Tools page with Import/Export (JSON), uninstall setting, and Danger Zone “Delete All FAQs Now”.

New: Category badge on each FAQ; shows primary category or “None”.

Dev: Modularized codebase (includes/ classes + helpers).

Dev: Hardened asset enqueues (shortcode + conditional) for page builders.

UX: Improved error handling during import/export.

= 1.2.0 =

Robust JS bootstrap for async/defer loaders.

Added plugin row quick link.

= 1.1.0 =

Added Additional CSS page with CodeMirror.

Improved default CSS (focus states, dark mode, reduced motion, print).

= 1.0.0 =

Initial release: FAQ CPT + taxonomy, shortcode with accessible tabs and filtering.

== Upgrade Notice ==

= 1.3.0 =
Adds Tools (Import/Export/Uninstall control) and category badges. Review FAQs → Tools after upgrading.

== Privacy ==
This plugin does not collect or transmit personal data. JSON export includes only FAQ content and associated categories from your site.

== Roadmap ==

Optional Gutenberg block wrapper for the shortcode.

Settings UI for brand color / spacing variables.

Analytics hook (opt-in) to see most-opened questions.

= 1.3.2 =
* Rename CPT menu label to **SLS FAQs**.
* Add **Help** submenu with quick-start usage guide.
