=== nera-voluntary-code-plugin ===
Contributors: Nera
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 9.0
Stable tag: 1.0.0
License: GPLv2 or later

UK Voluntary Code public-disclosure page and footer badge for prize draw operators.

== Description ==

Auto-creates an editable **Our commitment to player protection** page and surfaces
a sitewide footer badge, fulfilling clause 3.4 of the UK Voluntary Code of Good
Practice for Prize Draw Operators (Public Disclosure).

= Admin (CMS) =
Theme Settings → **Nera Features** → **Voluntary Code**:

* **Intro Copy** — wysiwyg statement shown at the top of the commitment page.
* **Footer Badge** toggle — enable or disable the sitewide footer badge.
* **Footer Text** — badge label text (defaults to "We follow the Voluntary Code
  for Prize Draw Operators").
* **GOV.UK URL** — link to the official Voluntary Code publication (pre-filled).
* **ADR Provider** — Alternative Dispute Resolution provider details (optional).

= 2 surfaces =

1. **Our commitment to player protection page** — auto-created on activation,
   rendered by a dynamic block (`nera/voluntary-code-commitment`) or shortcode
   (`[nera_voluntary_code]`). Lists Player Protection, Transparency and
   Accountability measures. Self-heals if trashed.

2. **Footer badge** — slim adherence statement on every page (via wp_footer),
   linking to the commitment page.

== Notes ==

* Pure standalone plugin — no WooCommerce dependency for core functionality.
* Works on any WordPress site (6.0+).
* HPOS-compatible (no order meta accessed).
* ACF Pro recommended for admin settings UI. All getters return safe defaults
  when ACF is absent.

== Changelog ==

= 1.0.0 =
* Initial release.
