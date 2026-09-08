# WPVR
WP VR - 360 Panorama and virtual tour creator for WordPress is a customized panorama & virtual builder tool for WordPress websites.

## Changelog

### 9.1.1 (2026-09-08)
- **Fix:** Resolved issue where tours created with the new UI failed to render when embedded.
- **Fix:** Improved tour type detection and state synchronization across legacy and REST API services.
- **Fix:** Prevented WordPress `wptexturize` and content filters from corrupting tour inline scripts.
- **Fix:** Resolved container ID conflicts and script errors when embedding multiple tour instances on the same page.
- **Fix:** Resolved hotspot click and modal popup display issues across different content formats.
- **Fix:** Added safe fallbacks to prevent PHP undefined key warnings in scene and hotspot data validation.

### 9.1.0 (2026-09-03)
- **New:** Added Breakdance page builder integration with dedicated WPVR block support.
- **New:** Added 360-degree video tour support with Video.js and VideoJS-VR integration in the modern tour editor and frontend.
- **Fix:** Resolved self-hosted 360-degree video dragging and touch navigation issues with robust deferred initialization.
- **Fix:** Resolved Gutenberg block inline script corruption caused by wptexturize modifying operators in the content filter.
- **Fix:** Improved video tour metadata handling, state synchronization, and toggle logic between UI modes and editor.

### 9.0.3 (2026-09-01)
- **Security Fix:** Enforced object-level authorization (BOLA) checks on tour REST API and AJAX endpoints so users can no longer read, edit, or delete tours they don't own.
- **Security Fix:** Restricted the UI mode toggle endpoint to administrators only.
- **Security Fix:** Sanitized hotspot content (including FluentForm hotspots) on both server and client sides to prevent stored XSS.

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