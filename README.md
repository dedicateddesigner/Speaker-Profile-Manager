# Speaker Profile Manager

WordPress speaker CPT with Elementor Dynamic Tags and manual display ordering.

## Version 1.2.0
- Speaker Name, Title, Company, Logo, Ring Color fields.
- Speaker Display Order field (1 is first).
- Display Order editable in the speaker editor and WordPress Quick Edit.
- Admin Order column is sortable.
- Elementor Loop Grid query ID `speaker_listing` sorts ascending by Display Order.
- Elementor Dynamic Tags for speaker text fields and company logo image.

## Update
Back up the existing plugin folder/site, then replace the plugin files. Keep the existing plugin folder name and do not delete speaker posts. Existing speaker metadata remains.

## Set ordering
In Speakers list, use Quick Edit and enter Display Order (1, 2, 3...). Or edit a speaker and set Display Order in Speaker Information. Use unique numbers for predictable order.

## Elementor Loop Grid
Set Query ID to `speaker_listing`. Set Items Per Page to 6 and pagination to Load on Click / Load More. The plugin query orders speaker cards by Display Order ascending.

## Company logo
Use the Speaker Company Logo dynamic tag on an Image widget. A 600 × 300 px source is suitable; set Object Fit to Contain.
