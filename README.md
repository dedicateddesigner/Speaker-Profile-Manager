# Speaker Profile Manager v1.3.0

Adds an Elementor **Speaker Marquee** widget while retaining the speaker CPT, metadata, Elementor Dynamic Tags, numeric display order and Quick Edit.

## Requirements
- WordPress 6.0+
- PHP 7.4+
- Elementor (free) for the widget; Elementor Pro is not required for the built-in card.

## Install
Back up the site and existing plugin folder. Replace the plugin files with this package, keeping the plugin folder name. Activate/update the plugin, then open Elementor and search for **Speaker Marquee**.

## Speaker records
Create records under Speakers. Set portrait using Featured Image; enter Speaker Name, Title/Designation, Company Name, Company Logo, and Display Order. The logo source may be 600 × 300 px.

## Elementor widget
- Built-in speaker card by default.
- Optional saved Elementor template selection.
- Number of speakers, card width, gap, speed, direction, pause on hover, background, and accent controls.
- One speaker is rendered as a centered static card with constrained width.
- Two or more speakers render as a continuous marquee, ordered by Display Order.
- For a saved card template, create an Elementor Template first, use Speaker Profile Manager Dynamic Tags for name/title/company/logo, then select it in the widget.

## Notes
The template option renders the saved Elementor template once for each speaker while setting the current post context. Verify your particular template widgets/dynamic tags on staging before publishing. Marquee animation respects reduced-motion preferences.
