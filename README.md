#### LeitBox – Leitner Flashcards for Moodle

> **Named after Sebastian Leitner** – the Austrian scientist who invented the flashcard box learning
> system in 1972. LeitBox brings his method natively into Moodle.

LeitBox is a native Moodle activity plugin that turns passive course content into flashcard decks
learners can practise on their own. Built on the proven **Leitner box method**, it runs directly
inside Moodle – no extra login, no external tools, no data leaving the institution's own server.

---

## Screenshots

*Coming soon*

---

## Features

### Six-Box Learning System
Cards are organised into **6 boxes** (New → Beginner → Learner → Advanced → Experienced → Expert).
Three answer options give learners full control:

| Answer | Effect |
|--------|--------|
| **Got it** | Card advances one box |
| **Again** | Card stays in its current box, repeated later in the same session – no penalty |
| **Hard** | Card falls back exactly **one box** (never all the way to the start) |

A single mistake never erases all progress. Learners choose when and how often to practise – LeitBox
does not schedule reminders or due dates.

### Fast Card Creation, AI-Ready
Built-in prompt templates let teachers generate entire card decks from any existing material in
minutes. Six didactic categories are available from a dropdown:

- Standard Q&A (factual knowledge)
- True or False (with explanation)
- Vocabulary & Terms (translations, definitions)
- Fill in the Blank (cloze-style)
- Jeopardy (answer first, guess the question)
- Transfer & Real-World Application (highest didactic level)

The output can be pasted directly into the bulk import field – no file upload, no encoding errors,
no CSV headaches.

### Moodle-Native Completion Tracking
Three granular, combinable completion conditions:
- User interacted with X unique cards
- User promoted X cards to Expert level (box 5)
- User promoted **all** cards to Expert level

Full integration with Moodle's Activity Completion system.

### GDPR / Privacy by Design
Full Moodle Privacy API integration. All learner data stays on the institution's own server and can
be exported or deleted via standard Moodle Data Privacy tools.

### Moodle-Native & Admin-Friendly
- Full Backup & Restore API support
- Integrates with Moodle's standard Course Reset tool, so reusing a course for a new term cleanly
  clears the previous cohort's learning progress
- No CSS bleeding, no external dependencies
- Uses only Moodle's built-in role, permission and session management
- Compatible with all standard Moodle themes

### Calm by Design
No ranking, no leaderboards, no game points, no images on cards – LeitBox is built for focus and
speed rather than competition.

---

## Requirements

- **Moodle:** 4.1 LTS or later (tested up to 4.5)
- **PHP:** 8.1 to 8.3
- **Database:** MySQL, MariaDB, or PostgreSQL
- No additional PHP extensions or server dependencies required

---

## Installation

1. Download the latest release ZIP: `mod_leitbox_vX.Y.Z.zip`
2. In Moodle: **Site administration → Plugins → Install plugins**
3. Upload the ZIP and follow the on-screen instructions

**Manual installation:**
Extract the ZIP into your Moodle `/mod/leitbox/` directory, then visit `/admin/index.php` to trigger
the database upgrade.

> **Important:** The ZIP root must contain exactly one directory named `leitbox`. Do not rename or
> restructure it.

---

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.x, Moodle Core API |
| Frontend | Vue.js 3, Tailwind CSS, Vite |
| Database | Moodle XMLDB (MySQL / MariaDB / PostgreSQL) |
| Security | CSRF/XSS protection via Moodle Core Services |
| Maturity | `MATURITY_STABLE` |
| License | GNU GPL v3 |

---

## Versioning

This plugin follows [Semantic Versioning](https://semver.org/):

- **MAJOR** – Architectural rewrites or dropping Moodle LTS support
- **MINOR** – New features or database schema changes
- **PATCH** – Bug fixes, translation corrections, CSS tweaks

All changes are documented in [CHANGELOG.md](CHANGELOG.md).

---

## Privacy & GDPR

LeitBox supports Moodle's native Privacy API. All learner interaction data is strictly linked to
their Moodle user ID and can be fully exported or purged via **Site administration → Privacy and
policies → Data requests**.

---

## Backup & Restore

Full support for Moodle's native Backup and Restore API. Course backups include all flashcard decks
and, optionally, individual student progress data.

---

## Support & Contact

- **Bug reports & feature requests:** [GitHub Issues](https://github.com/peterpleimfeldner-design/moodle-mod_leitbox/issues)
- **Email:** leitbox.moodle@gmail.com

---

## License

This program is free software: you can redistribute it and/or modify it under the terms of the
**GNU General Public License** as published by the Free Software Foundation, either version 3 of
the License, or (at your option) any later version.

See [LICENSE](LICENSE) for full details.
