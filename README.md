# Moodle Activity Module: LeitBox

LeitBox is a modern, single-page application (SPA) flashcard activity plugin for Moodle that utilizes the proven Leitner system of spaced repetition.

## Description

The LeitBox activity allows students to practice flashcards using a smart spaced repetition system. It provides a modern, fast, and responsive user interface built as a Vue.js 3 single-page application integrated directly into Moodle.

Teachers can easily manage flashcards manually or import them in bulk using AI-generated text. The plugin tracks student progress, supports custom activity completion rules based on cards mastered, and helps students retain knowledge efficiently.

## Core Features & Benefits

- **Smart Leitner System:** Cards are organized into 6 learning boxes (New, Beginner, Learner, Advanced, Experienced, Expert). The system dynamically adjusts review intervals based on user feedback.
- **Pedagogically Optimized:** When a card is forgotten (marked as "Hard"), it does not frustratingly reset to the beginning but shifts back by just one box, maintaining student motivation. A didactic limit of 200 cards per set ensures focused, effective learning.
- **Premium UI/UX:** Built with Vue.js 3 and Tailwind CSS. Features psychologically aligned pastel colors, micro-animations, and a completely seamless, reload-free experience.
- **AI Prompt Templates:** 6 built-in prompt templates (Standard Q&A, True/False, Vocabulary, Fill-in-the-blank, Jeopardy, Transfer & Everyday Context) for generating flashcard decks with any AI. Select, copy, and paste.
- **Bulk Card Management:** Checkbox-powered multi-select for bulk deletion, plus a one-click export of all cards as a re-importable `.txt` file.
- **WCAG 2.1 AA Compliant:** Fully accessible interface with comprehensive screen reader support, keyboard navigation, and high-contrast fallbacks.
- **Full Internationalization:** Complete German and English language packs with no hardcoded strings. All UI elements, prompts, feedback messages, and explanations adapt to the user's Moodle language.
- **Learning Progress Reset:** A built-in user-facing reset feature allows students to safely restart their learning journey before exams.
- **Session Feedback:** Dynamic performance feedback after completing a deck, with 5 graduated levels from "Review recommended" to "Masterpiece".

## Requirements

- Moodle 4.1 or later (LTS compliant).
- No additional PHP extensions required.

## Installation

1. Log in to your Moodle site as an administrator.
2. Go to **Site administration** > **Plugins** > **Install plugins**.
3. Upload the `leitbox_v1.zip` file and follow the onscreen instructions.
4. Alternatively, extract the zip file directly into the `mod/leitbox` directory of your Moodle installation and trigger the upgrade via the browser (`/admin/index.php`) or CLI.

## Privacy & GDPR

LeitBox supports Moodle's native Privacy API. All student interaction data (progress, flashcard states) is strictly linked to their Moodle ID and can be exported or purged via the standard Data Privacy tools in Moodle.

## Backup & Restore

Full support for Moodle's native Backup and Restore API is included. Course backups will seamlessly include all flashcard decks and, optionally, student progress data.

## Links

- **Documentation:** [GitHub Repository](https://github.com/peterpleimfeldner-design/leitbox)
- **Bug tracker:** [GitHub Issues](https://github.com/peterpleimfeldner-design/leitbox/issues)

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
