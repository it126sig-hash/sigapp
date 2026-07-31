---
name: sigapp-ui-acuan
description: Use this skill when working on SIGAPP UI in CodeIgniter views, Bootstrap modals, tabs, dividers, upload/file previews, or siteplan/admin interfaces. It preserves the project UI baseline: Bootstrap primary blue #2057a3, modal detail as the modal styling reference, divider-left section headers, FileAccessService-backed file URLs, and one-line horizontally scrollable mobile tabs.
---

# SIGAPP UI Acuan

## Core Rules

- Use `#2057a3` as SIGAPP's primary Bootstrap blue. Keep related hover/focus colors in the same family and avoid reintroducing old purple primary values.
- Treat `#modal_detail` in `app/Views/siteplan/master.php` as the modal styling baseline unless the user gives a different reference.
- Use `divider divider-left` for section dividers. Avoid adding plain `divider` in new or refactored UI sections.
- File display, download, preview, and upload-result URLs must come from `FileAccessService` or backend fields produced by it.
- On mobile, tab navigation must stay in one row and scroll horizontally.

## Modal Baseline

When creating or restyling SIGAPP modals, follow the detail modal pattern:

- Dialog: wide enough for dense admin data, with responsive max width.
- Content: light border, 8-10px radius, no decorative heavy shadow.
- Header: white background, compact spacing, clear title, close button aligned.
- Body: light gray background, scrollable when content is tall.
- Cards: 8px radius or less, thin `#e5e7eb` style borders, compact body padding.
- Labels: small, muted, bold enough for scanning.
- Data rows: use compact bordered rows or cards, not oversized marketing-style blocks.
- File tiles/previews: use the existing detail modal visual language where possible.

Before changing other modals, inspect the current `#modal_detail` CSS and markup in `app/Views/siteplan/master.php` because this file evolves often.

## Dividers

Use this shape for new or updated section headings:

```html
<div class="divider divider-left">
    <div class="divider-text">Judul Section</div>
</div>
```

If editing old markup that uses only `divider`, migrate it to `divider divider-left` when it is inside the touched UI surface.

## File Access

Do not build direct public file URLs in views or JavaScript, such as:

```php
base_url('uploads/...') // avoid for dynamic uploaded files
```

Prefer one of these patterns:

- Backend service/controller injects `FileAccessService` and returns `access_url`, `download_url`, or `thumbnail_url`.
- For legacy logical paths, use `FileAccessService::pathUrl()` or the existing path-based gateway helpers.
- Views consume the URL fields returned by the backend.
- JavaScript preview logic should prefer existing `*_access_url` fields for stored files and use `URL.createObjectURL(file)` only for newly selected local files.

Static public assets such as CSS, JS, logos, and bundled images can remain public. This rule is for uploaded or user/business document files.

## Mobile Tabs

For Bootstrap tabs/pills in modals or dense admin screens, keep mobile navigation one line:

```css
@media (max-width: 767.98px) {
    .your-scope .nav-tabs,
    .your-scope .nav-pills {
        flex-direction: row !important;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: .25rem;
    }

    .your-scope .nav-tabs .nav-link,
    .your-scope .nav-pills .nav-link {
        white-space: nowrap;
    }
}
```

Scope selectors to the modal/page being touched unless the user asks for a global cleanup.

## Implementation Checklist

1. Start from the named file or screen, then widen only if shared styling or data flow requires it.
2. Preserve existing endpoint names, request fields, response shapes, and modal IDs unless the user asks otherwise.
3. Apply `#2057a3` consistently to Bootstrap primary states and modal-specific primary accents.
4. Match the `#modal_detail` spacing, cards, headers, labels, tab behavior, and file tiles for new modal work.
5. Replace touched plain section dividers with `divider divider-left`.
6. Verify file links are gateway URLs from `FileAccessService`, not direct `public/uploads` paths.
7. Check desktop and mobile behavior for tab overflow, modal body scrolling, and text wrapping.
8. Run targeted syntax checks for edited PHP files and `git diff --check` before finishing when practical.
