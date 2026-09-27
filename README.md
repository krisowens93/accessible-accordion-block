# Accessible Accordion Block

An ACF Pro block that builds accordions from native `<details>` and `<summary>` elements. No JavaScript.

Use it for FAQs, or any content that opens and closes.

## Why native `<details>`

- **No JavaScript.** Opening, closing, and keyboard support come from the browser.
- **Content is always in the page,** even when a section is closed.
- **Nothing to break.** If CSS fails to load, it still works as a plain disclosure.

WordPress core's Accordion block uses buttons and the Interactivity API. This block takes the native route instead.

## Features

- **Structured content.** Editors fill in Summary and Body fields for each item, so the markup stays consistent.
- **Optional headings.** Choose None, or H2 to H6, for the summaries. Headings keep the same look as plain labels, because heading level is structure, not styling.
- **One open at a time.** Uses the native `name` attribute. Browsers without support simply let several stay open.
- **Open by default,** per item.
- **Three preset styles:** Default (dividers), Boxed (cards), and Minimal (no lines).
- **Theme-aware style options.** Label and body color, size, and weight, plus a card background for Boxed. The choices come from the active theme's own color palette and font sizes, so the block follows the theme if its colors change.
- **Block supports** for background color, wide and full alignment, and an HTML anchor. Padding, top and bottom margin, and line height also appear when the active theme enables them. Block themes do by default.

## Requirements

| | Minimum | Tested with |
|---|---|---|
| WordPress | 6.3 | 7.1.2 |
| ACF Pro | 6.2 | 6.8.10 |
| PHP | 7.4 | 8.3 |

Versions between the minimum and the tested version haven't been tested yet.

The field group ships in `acf-json/`, so ACF loads it automatically. There's nothing to import.

## Installation

1. Download or clone this repository into `wp-content/plugins/accessible-accordion-block`.
2. Make sure ACF Pro is active.
3. Activate **Accessible Accordion Block** under **Plugins**.
4. In the block editor, search for "Accessible Accordion" (or "faq").

## Styling for theme developers

The block's CSS is built on custom properties, and the block's wrapper selector is wrapped in `:where()`, so your theme's styles win without `!important`.

Set any of these on `.wp-block-accessible-accordion-accordion`:

| Property | Default | Controls |
|---|---|---|
| `--aab-border-color` | `currentColor` | Divider and card border color |
| `--aab-border-width` | `1px` | Divider and card border width |
| `--aab-radius` | `0.5rem` | Card corner radius (Boxed) |
| `--aab-gap` | `0.75rem` | Space between cards (Boxed) |
| `--aab-space` | `1rem` | Padding inside items |
| `--aab-marker-size` | `0.5em` | Chevron size |

Example:

```css
.wp-block-accessible-accordion-accordion {
	--aab-border-color: var(--wp--preset--color--primary);
	--aab-radius: 0;
}
```

The Style fields in the editor set these per block: `--aab-label-color`, `--aab-label-size`, `--aab-label-weight`, `--aab-body-color`, `--aab-body-size`, and `--aab-item-bg`.

## File structure

```
accessible-accordion-block/
├── accessible-accordion-block.php   Registers the block, Local JSON paths, style field choices
├── acf-json/                        The block's field group
└── blocks/accordion/
    ├── block.json                   Block settings and supports
    ├── render.php                   Markup
    └── style.css                    Front end and editor styles
```

## Roadmap

- A shortcode for synced patterns, so accordions can appear in the Classic Editor, widgets, and page builders.
- An optional FAQ schema toggle.
- Custom colors beyond the theme palette.

## Project Notes

Created as a practical deep-dive into custom ACF Pro block development. Created with technical assistance from Claude Code to explore block architecture, rapid prototyping, and custom field workflows.

## License

GPL-2.0-or-later. Built by [Kris Owens](https://krisowensdev.com).
