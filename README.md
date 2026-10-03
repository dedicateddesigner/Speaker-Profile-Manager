# Speaker Profile Manager

A lightweight WordPress plugin for managing conference speaker profiles
and displaying them through Elementor Loop Grid.

## Features

-   Registers a **Speakers** custom post type.
-   Uses the WordPress Featured Image for each speaker portrait.
-   Stores speaker name, title/designation, company name, company logo,
    and ring color.
-   Uses the WordPress Media Library for company logos.
-   Exposes registered speaker metadata through the WordPress REST API.
-   Designed to work with Elementor Loop Grid and dynamic content.
-   Adds speaker information columns to the WordPress admin list.

## Requirements

-   WordPress 6.0 or later (recommended)
-   PHP 7.4 or later
-   Elementor Pro for Loop Grid templates and dynamic content

## Installation

1.  Download or clone this repository.
2.  Copy the `speaker-profile-manager` directory into
    `wp-content/plugins/`.
3.  In WordPress Admin, open **Plugins** and activate **Speaker Profile
    Manager**.
4.  Open **Speakers → Add Speaker** to create a profile.
5.  Set the speaker portrait using the **Featured Image** panel.
6.  Fill in the speaker fields and publish.

## Speaker fields

  -------------------------------------------------------------------------
  Field                   Meta key                  Notes
  ----------------------- ------------------------- -----------------------
  Speaker Name            `_speaker_name`           Display name

  Title / Designation     `_speaker_title`          Role, title, or
                                                    description

  Company Name            `_speaker_company`        Organization name

  Company Logo            `_speaker_company_logo`   WordPress attachment ID

  Ring Color              `_speaker_ring_color`     Hex color; defaults to
                                                    `#A4D600`

  Portrait                Featured Image            WordPress featured
                                                    image
  -------------------------------------------------------------------------

## Elementor Loop Grid

1.  Create an Elementor **Loop Item** template.
2.  Add an Image widget and set its dynamic source to **Featured
    Image**.
3.  Add text widgets and select **Post Custom Field** as the dynamic
    source.
4.  Enter the corresponding meta key, such as `_speaker_name`,
    `_speaker_title`, or `_speaker_company`.
5.  Add a Loop Grid and select the **Speakers** post type as its source.

**Note:** Elementor's handling of image attachment IDs stored in custom
fields can vary. The company logo field currently stores an attachment
ID; if your Elementor setup does not render it as an image, add a
dedicated Elementor dynamic tag or image URL integration.

The ring color is stored as metadata. Applying it automatically to a
Loop Item's CSS border requires a CSS-variable or Elementor dynamic-tag
integration; the current plugin provides the stored value but does not
inject a per-item CSS variable.

## Development

The plugin's main file is `speaker-profile-manager.php`. Admin media
selection is handled by `assets/admin.js`.

After changing the custom post type rewrite slug, visit **Settings →
Permalinks** and click **Save Changes** to refresh rewrite rules.

## Security

The plugin uses WordPress nonces, capability checks, sanitization, and
output escaping for its admin fields.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
