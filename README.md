# Featured Cases Technical Exercise

## Overview

This project is a small WordPress implementation for a law firm website to manage and display **Featured Cases**.

## Approach

This solution separates content structure from presentation:

- Plugin: the `Featured Cases` plugin owns the `featured_case` post type, admin field UI, validation, and data saving. This keeps the content model portable and independent from the active theme.
- Theme: the `Law Firm Base` theme owns the page template that queries and renders the Featured Cases. This keeps rendering logic in the theme layer where it belongs without adding an unnecessary theme layer for the exercise.
- Native WordPress APIs: the implementation uses core WordPress APIs instead of ACF or another field library. For this exercise, native APIs keep the solution smaller, transparent, and easier to review.

### Case Type Note

The brief mentions custom fields, but `Case Type` is intentionally handled as a taxonomy-backed select inside the meta box instead of a free-text meta input.

This was a deliberate usability choice:

- it gives admins a controlled list of reusable options
- it avoids inconsistent values caused by typing variations
- it makes the editor experience faster and clearer
- it still uses native WordPress functionality only

`Settlement Amount` remains standard post meta.

## Setup

1. Copy the `featured-cases` plugin folder to the `wp-content/plugins/` folder.
2. Copy the `law-firm-base` theme folder to the `wp-content/themes/` folder.
3. In WordPress admin, go to `Plugins` and activate the `Featured Cases` plugin.
4. In WordPress admin, go to `Appearance > Themes` and activate the `Law Firm Base` theme.
5. In WordPress admin, create a new page and assign the `Featured Cases` page template.
6. In WordPress admin, add some `Case Types`, for example: `Car Accident`, `Medical Malpractice`, and `Work Injury`.

## Create Demo Content

1. Go to `Featured Cases` in WordPress admin.
2. Create at least three Featured Case posts.
3. Select a `Case Type` and enter a `Settlement Amount` for each post.
4. Publish the posts.
5. Visit the page that uses the `Featured Cases` template.

## Implementation Notes

- CPT registered with `register_post_type()`
- `Case Type` managed with a native custom taxonomy and selected in the custom meta box
- `Settlement Amount` stored as native post meta
- nonce verification, capability checks, autosave/revision guards, sanitization, and escaped output are included
- prefixed function names and meta keys are used to reduce naming conflicts

## File Structure

```text
wp-content/
|-- plugins/
|   `-- featured-cases/
|       `-- featured-cases.php
`-- themes/
    `-- law-firm-base/
        `-- page-featured-cases.php
```
