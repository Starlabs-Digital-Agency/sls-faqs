# SLS FAQs — Install & Use Guide (v1.3.2)
SLS FAQs is a free, lightweight WordPress plugin for creating and displaying FAQs with clean, accessible, category-based tabs—no page builder required. It adds a simple FAQ post type and taxonomy, and lets you drop everything on a page with one shortcode.


**Last updated:** 2025-09-08

---

## 1) Requirements
- WordPress 6.0+
- PHP 7.4+
- Admin access to your WordPress dashboard

---

## 2) Download & Install

### Method A — Upload via Dashboard (recommended)
1. In WordPress, go to **Plugins → Add New → Upload Plugin**.
2. Click **Choose File** and select the ZIP: `starlabs-faqs-1.3.2-sls-help.zip`.
3. Click **Install Now**, then **Activate**.

### Method B — SFTP/FTP
1. Extract the ZIP locally.
2. Upload the plugin folder to `/wp-content/plugins/`.
3. In **Plugins**, click **Activate** next to **SLS FAQs**.

> Tip: If category/taxonomy URLs behave oddly after activation, visit **Settings → Permalinks** and click **Save** once to flush rewrite rules.

---

## 3) Quick Start (3 Steps)

### Step 1 — Create categories
Go to **SLS FAQs → FAQ Categories** and add categories like *General*, *Billing*, *Technical Support*.

### Step 2 — Add FAQs
Go to **SLS FAQs → Add New**.
- **Title** = the question
- **Content** = the answer (you can use headings, lists, images)
- (Optional) **Excerpt** = a short summary
- Assign one or more **FAQ Categories**
- (Optional) Set **Order** in Page Attributes to control item ordering

### Step 3 — Display on a page
Create or edit a page (e.g., *Help Center*) and add the shortcode:
```
[starlabs_faqs]
```

**Examples with options:**
```
[starlabs_faqs categories="general,billing" per_page="10" orderby="title" order="ASC"]

[starlabs_faqs categories="general" show_all_tab="false"]
```
- Passing a **single** category slug auto-hides tabs for a clean, single list.
- Tabs + filtering are accessible and keyboard-navigable.

---

## 4) Shortcode Reference

| Attribute       | Type  | Default       | Description |
|----------------|-------|---------------|-------------|
| `categories`   | csv   | `""`          | Comma-separated category slugs to include. |
| `per_page`     | int   | `-1`          | FAQs per query; `-1` shows all. |
| `orderby`      | text  | `menu_order`  | `menu_order` \| `title` \| `date`. |
| `order`        | text  | `ASC`         | `ASC` or `DESC`. |
| `show_all_tab` | bool  | `true`        | Show the **All** tab when multiple categories render. |
| `update_hash`  | bool  | `true`        | Update `location.hash` when users switch tabs. |

---

## 5) Styling

### Additional CSS (easy)
Go to **SLS FAQs → Additional CSS** and paste overrides. They load **after** the base CSS.

**Example:**
```css
/* Accent color */
:root { --faq-accent: #8b5cf6; }

/* Card look */
.starlabs-faqs .faq-item {
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,.06);
}
```

### Replace base CSS (advanced)
- Programmatically disable plugin CSS and ship your own:
```php
add_filter('starlabs_faq_disable_default_css', '__return_true');
// or in wp-config.php:
define('STARLABS_FAQ_DISABLE_DEFAULT_CSS', true);
```

---

## 6) Tools (Import/Export/Delete)

Go to **SLS FAQs → Tools**.

### Export
- Click **Export** to download a JSON of all FAQs + categories.

### Import
- Choose a valid export JSON and click **Import**.
- (Optional) **Upsert** updates existing FAQs by slug.

### Delete all (Danger Zone)
- Deletes **all** FAQs and FAQ categories. Irreversible.

> Note: The **Uninstall behavior** setting is present for future use. In v1.3.2, use **Delete all** before uninstalling for a clean database.

---

## 7) Compatibility & Tips
- Works with **Gutenberg**, **Classic Editor**, and major **page builders** (use their Shortcode block/widget).
- For heavily cached builders, you can force assets everywhere:
```php
add_filter('starlabs_faq_force_assets', '__return_true');
```

---

## 8) Troubleshooting
- **Unstyled output:** Force assets (see tip above).  
- **Tabs don’t filter:** Ensure posts are assigned to real **FAQ Categories** and slugs in `categories="..."` match.  
- **Permalinks:** Visit **Settings → Permalinks** and click **Save** to flush rules.  
- **Import errors:** Re-export from source site; confirm JSON and WordPress nonces.

---

## 9) Need help?
Open **SLS FAQs → Help** in your dashboard for a built-in quick start, or reach out to the team for customizations.
