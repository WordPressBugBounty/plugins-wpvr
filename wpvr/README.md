# WPVR
WP VR - 360 Panorama and virtual tour creator for WordPress is a customized panorama & virtual builder tool for WordPress websites.

## Changelog

### 9.0.2 (2026-08-28)
- **Fix:** Resolved `wpvrhotspot is not defined` error when caching and JavaScript deferral plugins (such as Autoptimize, WP Rocket, LiteSpeed Cache) are active.
- **Fix:** Ensured frontend scripts and styles are reliably enqueued across Gutenberg blocks and page builder widgets.
- **Fix:** Resolved fatal error on `update.php` during plugin updates.
- **Fix:** Resolved mobile touch dragging and swipe navigation dropping or getting interrupted on mobile browsers.
- **Fix:** Ensured tour navigation controls (mouse drag, zoom, and keyboard movement) remain properly enabled by default on the Free tier.
- **Fix:** Corrected keyboard movement control setting mapping in the tour editor.

### 9.0.1 (2026-08-20)
- **Fix:** Fixed mouse drag & drop issue.
- **Fix:** Fixed unexpected variable warning.

### 9.0.0 (2026-08-20)
- **New:** Introducing the new WPVR user interface for a faster and more intuitive tour-building experience.
- **New:** Added keyboard accessibility to improve tour navigation and interaction.
- **New:** Added an imported tour indicator for easier identification of imported tours.
- **New:** Added smart warning diagnostics to help identify and resolve errors.
- **Fix:** Prevented hotspots from overlapping panorama controls.
- **Fix:** Fixed hotspot link types not opening configured destinations.
- **Fix:** Improved image-switching performance in the preview section.
- **Fix:** Fixed layout and alignment issues in the desktop tour builder.
- **Fix:** Removed unexpected warnings from WordPress admin screens.
- **Fix:** Fixed access and rendering issues for password-protected scenes.