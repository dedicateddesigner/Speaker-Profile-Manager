# Speaker Profile Manager

Version 1.4.5

Manage speaker profiles, sponsors and media partners, and display them with Elementor widgets.

## Content types
- **Speakers**: speaker name, designation, company, company logo, portrait and numeric display order.
- **Sponsors**: title, logo, optional tier label, optional website URL and display order.
- **Media Partners**: title, logo, optional website URL and display order.

## Elementor widgets
- Speaker Marquee
- Sponsor Marquee
- Media Partners Marquee

Both partner marquee widgets include direction (Right to Left / Left to Right), duration, pause-on-hover, and sizing controls. Sponsor cards include a tier footer; media partners use compact logo tiles.

## Upgrade
Install over Speaker Profile Manager 1.3.1 using the same plugin slug. Existing speaker post type and metadata keys are preserved. WordPress may ask to confirm replacement of the installed plugin.

Requires WordPress 6.0+, PHP 7.4+, and Elementor for the widgets.

### Partner widget layout controls
Sponsor Marquee includes responsive Card Height; Media Partners Marquee includes responsive Logo Tile Height. Both include a soft edge-fade width control. Set the fade width to 0 for a hard edge.


### v1.4.2
Sponsor cards use a 3:1 logo-to-tier area, configurable two-color footer gradient, and logo zoom on hover.


### v1.4.3
Fixed sponsor and media partner marquee continuity by grouping each repeated sequence and animating exactly one sequence width.


### v1.4.4
Fixes empty marquee gaps on wide viewports by rendering five equal partner sequences and moving exactly one sequence per animation cycle.


### v1.4.5
Fixes the visible join between duplicated marquee groups by including trailing group spacing in each equal animation segment. Elementor gap control now applies to both card spacing and repeated-group boundaries.
