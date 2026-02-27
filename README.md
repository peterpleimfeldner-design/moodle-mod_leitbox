# Moodle Activity Module: Recall

Recall is a modern, single-page application (SPA) flashcard activity plugin for Moodle that utilizes the proven Leitner system of spaced repetition.

## Description

The Recall activity allows students to practice flashcards using a smart spaced repetition system. It provides a modern, fast, and responsive user interface built as a Vue.js 3 single-page application integrated directly into Moodle.

Teachers can easily manage flashcards manually or import them in bulk using AI-generated text. The plugin tracks student progress, supports custom activity completion rules based on cards mastered, and helps students retain knowledge efficiently.

## Core Features & Benefits

- **Smart Leitner System:** Cards are organized into 5 learning boxes (Einsteiger, Lernender, Fortgeschritten, Erfahren, Experte). The system dynamically adjusts review intervals based on user feedback.
- **Pedagogically Optimized:** When a card is forgotten (marked as "Hard"), it does not frustratingly reset to the beginning but shifts back by just one box, maintaining student motivation.
- **Premium UI/UX:** Built with Vue.js 3 and Tailwind CSS. Features psycholoigcally aligned pastel colors, micro-animations, and a completely seamless, reload-free experience.
- **WCAG 2.1 AA Compliant:** Fully accessible interface with comprehensive screen reader support, keyboard navigation, and high-contrast fallbacks.
- **Learning Progress Reset:** A built-in user-facing reset feature allows students to safely restart their learning journey before exams.
- **AI Bulk Import:** Teachers can paste simple text structures (e.g., from ChatGPT) to instantly generate complete flashcard decks.

## Requirements

- Moodle 4.1 or later (LTS compliant).
- No additional PHP extensions required.

## Installation

1. Log in to your Moodle site as an administrator.
2. Go to **Site administration** > **Plugins** > **Install plugins**.
3. Upload the `recall_v1.zip` file and follow the onscreen instructions.
4. Alternatively, extract the zip file directly into the `mod/recall` directory of your Moodle installation and trigger the upgrade via the browser (`/admin/index.php`) or CLI.

## Privacy & GDPR

Recall supports Moodle's native Privacy API. All student interaction data (progress, flashcard states) is strictly linked to their Moodle ID and can be exported or purged via the standard Data Privacy tools in Moodle.

## Backup & Restore

Full support for Moodle's native Backup and Restore API is included. Course backups will seamlessly include all flashcard decks and, optionally, student progress data.

## Links

- **Documentation:** [GitHub Repository](https://github.com/peterpleimfeldner-design/recall)
- **Bug tracker:** [GitHub Issues](https://github.com/peterpleimfeldner-design/recall/issues)

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
