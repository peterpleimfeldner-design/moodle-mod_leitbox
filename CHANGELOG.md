# Changelog

All notable changes to this project are documented in this file.
A German version is kept in the repository as `CHANGELOG.de.md` (not part of the release package).

## [1.6.1] - 2026-10-01 (Moodle Plugins directory review, round 3)

### Security
- **Card deletion could remove learner progress in other activities** (GitHub issue #17): the
  single-card delete action in `manage.php` deleted all `leitbox_progress` rows for the submitted
  card id before checking that the card belongs to the current activity. A teacher who could manage
  one LeitBox activity could therefore erase the learning progress of a card in another course. The
  card is now loaded together with the activity id first (`MUST_EXIST`); the card and its progress
  are deleted only afterwards, through the new `leitbox_delete_cards()`, which the bulk deletion
  uses as well. Card ids from other activities are ignored.
- All card management actions in `manage.php` now use `require_sesskey()`.

### Fixed
- **Privacy API deletion and course reset failed on MySQL** (#18): the queries deleted from
  `leitbox_progress` with a subquery on the same table, which MySQL rejects (error 1093). They now
  filter by card id with a subquery on `leitbox_cards` only. This affected
  `delete_data_for_all_users_in_context()`, `delete_data_for_user()`, `delete_data_for_users()` and
  `leitbox_reset_userdata()`.
- **JavaScript error on every activity view** (#23): `view.php` called `init()` on `core/ajax`,
  which has no such function, so the page kept a pending JavaScript marker. The call is removed.
  The learner view now calls the web services through Moodle's `core/ajax` module instead of Axios,
  so Moodle handles the sesskey, session expiry and errors. Axios is no longer bundled (bundle size
  163 KB to 124 KB).
- **`styles.css` affected other activities** (#21): the rules for the completion settings form
  applied to every activity type on the site. They are removed. All remaining rules are limited to
  LeitBox pages and use no `!important`.
- The Vue learner view contained hard-coded fallback texts (partly German). All texts now come from
  the language files; the error shown when resetting progress fails is a new language string.
- The learner view now starts only after Moodle's AMD loader has provided `core/ajax`, so the
  language strings are always available on the first render.
- The text of demo card 4 promised that a card rated "Again" is shown again in the same session.
  The plugin does not do this, so the sentence was removed (English and German).
- Privacy export: dates are exported with `transform::datetime()`, card texts are formatted in the
  module context.

### Fixed (found by our own full review before resubmission)
- **Course restore with user data failed:** the restore step stored the card id mapping under a
  different name than the one it read back, so every progress record was restored with an empty
  card id and the restore stopped with a database error as soon as any learner had progress. Links
  in card texts were not rewritten to the restored activity either. Both fixed and covered by a new
  backup and restore test.
- **Activity settings form crashed on Moodle 4.1 and 4.2:** `mod_form.php` called `get_suffix()`,
  which only exists from Moodle 4.3. The form now works on every supported version; a new Behat
  test opens and saves it.
- **Completion "minimum cards answered correctly"** also counted new cards that were only rated
  "Hard" (they move to box 1). It now counts only cards answered correctly at least once, as its
  help text says.
- **Learner view:** an error appeared in the browser console after the last card of a session; the
  activity name was escaped twice ("Q&amp;A"); the description was shown twice on Moodle 4.0 and
  later; Tailwind utility classes such as `.block` restyled Moodle's own blocks on the page (all
  utility classes are now limited to the app container).
- **Keyboard and screen reader use of the learner view:** the card can now be turned over with the
  keyboard ("Tap to flip" is a real button), the keyboard focus moves to the rating buttons and then
  to the next card, the hidden side of the card is inert, the dialogs take the focus, close with
  Escape and lie above Moodle's navigation bar, and invalid landmark and list roles were removed.
- **A failed save of an answer is now shown to the learner** instead of being lost silently;
  messages that used `alert()` are now shown on the page.
- "Display description on course page" now works; the course page shows the activity purpose
  colour (`FEATURE_MOD_PURPOSE`); `index.php` triggers the instance list viewed event and shows
  section names; the viewed event maps its object id for restored logs.
- Privacy API: the `status` field of the progress table is declared and exported, the export
  includes the hint and resolves the demo card texts.
- Texts that promised behaviour the plugin does not have ("spaced repetition", cards "queried less
  frequently") now describe the Leitner box method as it works: learners choose the deck.

### Changed
- **New capability `mod/leitbox:managecards`** (#22): adding, editing, importing, exporting and
  deleting cards is controlled by this capability (editing teachers and managers by default)
  instead of `moodle/course:manageactivities`.
- **Plugin icons** (#19): new single-colour vector icon `pix/monologo.svg` (3 KB). The 4.5 MB
  `pix/icon.svg` (embedded PNG images) and `pix/monologo.png` are removed, `pix/icon.png` is now
  64 x 64 pixels and `pix/logo.png` is resized to twice its display size. The release package
  shrinks from 6.4 MB to well under 1 MB. The header logo is loaded through Moodle's theme image
  URL, so browsers fetch the new file after an update.
- **Source code in the release package** (#20): the Vue source (`frontend/`, without
  `node_modules`) and the tests are now part of the release ZIP. The README documents the build.
- **English change log** (#24): this file is now in English.
- Card management page: form labels are linked to their fields, the selection check boxes have
  accessible names, inline styles moved to `styles.css`, Bootstrap 4-only utility classes replaced.
- Language files are sorted alphabetically.
- Frontend build tools updated (Vite 6); `npm audit` reports no vulnerabilities.
- README: the description of the "Again" and "Hard" buttons now matches what the plugin does.

### Tests and continuous integration
- New PHPUnit tests for the privacy provider (all functions, with a second activity and a second
  learner that must stay untouched), the course reset, card deletion across activities and the new
  capability.
- New Behat tests for the learner view and for card management.
- CI now runs on PostgreSQL, MySQL 8.4 and MariaDB, on Moodle 4.1, 4.4, 4.5, 5.0 and 5.1. Code
  checker and PHPDoc warnings fail the build, all Grunt tasks run (including Stylelint and Gherkin
  lint), and a new check verifies the release package (size, required source files, no internal
  files, English text outside the German language pack). The language string check now also covers
  the strings used by the Vue frontend.

## [1.6.0] - 2026-09-19 (Live UI test round: card loading, course reset, design, security)

A complete manual test of the activity in a real local Moodle site (driven in the browser, not only
a static code review), including bulk import, backup and restore, the Privacy API, permissions and
an XSS hardening test. Several functional bugs were found and fixed and verified live.

### Fixed (functionality)
- **Cards never loaded:** `get_cards_by_box` returned `question`, `answer` and `hint` without
  cleaning them, although the return definition requires `PARAM_CLEANHTML`. Moodle's strict return
  value validation (`clean_returnvalue()`) rejected every formatted card (`<b>`, `<br>`) with
  `invalidresponse`. The values are now normalised with `clean_param(..., PARAM_CLEANHTML)`.
- **Demo tutorial shown in random order:** the five introduction cards (`##demo_q1##` to
  `##demo_q5##`) were shuffled; only one card was forced to the front. Demo cards are now excluded
  from shuffling and always come first, sorted by id.
- **Export did not resolve demo markers:** the export in `manage.php` wrote the raw placeholders
  (`##demo_q1##`) instead of the demo text.
- **Grammar "1 Cards" instead of "1 Card"** in the box counts (German and English) fixed.
- **Permission check confirmed:** learners can view cards but not manage them (the check used
  `moodle/course:manageactivities`). No change needed; verified with real test accounts.
- **Course reset not supported:** `leitbox_reset_userdata()` was missing, so the learning progress
  of the previous cohort stayed in the database when a course was reused. Now implemented, with its
  own section in the course reset form ("LeitBox": delete the learning progress of all participants).

### Fixed (frontend stability)
- **JavaScript errors on every LeitBox page** (`Y.NodeList is not a constructor`): the Vue bundle
  was not wrapped in an IIFE, so a minified internal Vue function `Y()` overwrote Moodle's global
  YUI namespace `window.Y`. Vite now builds with `format: 'iife'`. In addition, Tailwind's global
  CSS reset (Preflight) was disabled because it affected the whole Moodle page instead of only the
  plugin's own component.
- **Header broke apart with long course titles** (buttons moved around, logo looked misplaced):
  logo, title and buttons now stay on one line; long titles are shortened with an ellipsis (full
  text as tooltip).
- **Empty box when starting a session** (for example after editing in a second tab or device) now
  shows a clear message instead of an empty "Card 0 of 0" state.

### Changed
- The dashboard title now shows the activity name instead of a fixed phrase; the LeitBox logo was
  made smaller so it repeats Moodle's own header less.
- The progress bar now shows the weighted learning progress across all six Leitner boxes (box 0 =
  0 %, box 5 = 100 %, proportional in between), independent of any completion conditions. Before,
  only fully mastered cards counted.
- `pix/monologo.png` recreated with a transparent background instead of its own square background,
  which created a double frame together with Moodle's icon border.

### Security (verified, no change needed)
- XSS hardening test with `<script>` and `<img onerror>` payloads in card fields: both are cleaned
  reliably in the management view and in the learner view (script tag removed, dangerous attributes
  stripped); no code execution possible.
- The 200-card limit shown in the interface is enforced on the server (single card and bulk
  import), not only in the frontend.

## [1.5.13] - 2026-09-18 (Review fixes: issues #10 to #12, #16, plus security audit)

All four GitHub issues still open since the second Plugins directory submission were fixed and
verified against a real Moodle 4.x test site (PHP CLI, real database), not only checked statically.
A full review against the Plugins directory guidelines (structure, security, code quality, APIs,
frontend and third-party code) found several further problems that had not been reported.

### Fixed (open GitHub issues)
- **Issue #16, missing language string definitions:** `import_placeholder` and `frontendnotfound`
  had never actually been defined in `lang/en/leitbox.php` and `lang/de/leitbox.php`, although the
  change log entry for 1.5.6 said so. Both strings added and verified with live `get_string()`
  calls.
- **Issue #11, PARAM_RAW security risk:** the actual problem was not the `PARAM_RAW` for the raw
  bulk import text (needed for the plugin's own Q:/A:/H: parser), but that
  `classes/import_handler.php::parse_text()` (formerly `classes/import.php`) did no cleaning, and
  `manage.php` stored the parsed fields unfiltered with `insert_record()`, unlike single add and
  edit, which already used `PARAM_CLEANHTML`. Every imported field (`question`, `answer`, `hint`)
  is now cleaned with `clean_param(..., PARAM_CLEANHTML)` before it is saved. Tested with a real
  `<script>` payload: removed reliably.
- **Issue #12, missing file boilerplate headers:** the full "This file is part of Moodle" GPL
  header was missing in `styles.css`, `frontend/src/style.css`, `templates/manage.mustache` and all
  frontend build configuration files; now added. The debug scripts `test_completion.php` and
  `test_completion_db.php` (the latter with hard-coded local Windows paths) were removed from the
  repository.
- **Issue #10, invalid or stale AMD build artifact:** `amd/build/manage.min.js` was a byte-identical
  copy of `amd/src/manage.js`. It is now really minified with Terser (4170 to 2039 bytes), with a
  source map. The CI pipeline runs `moodle-plugin-ci grunt --tasks=amd`, so an outdated build fails
  automatically.

### Fixed (further findings of the full review)
- **`classes/import.php` renamed to `classes/import_handler.php`:** the file contained the class
  `\mod_leitbox\import_handler`, which breaks Moodle's autoloader convention. It only worked because
  `manage.php` included the file manually. The manual `require_once` was removed.
- **`view.php`:** the Vue frontend assets were printed as raw `<script>` and `<link>` tags. They are
  now loaded through `$PAGE->requires->js()` and `css()`.
- **`thirdpartylibs.xml`:** Axios version corrected to the exact bundled version `1.13.5`.
- **`frontend/src/components/Dashboard.vue`:** two hard-coded German strings replaced by
  `getString('close')` and `getString('cancel')`; the JavaScript fallback texts switched to English.
- **`styles.css`:** German word removed from a comment.
- **`classes/external.php`:** `submit_answer()` now validates the `rating` parameter (0 to 2).
- **`db/services.php`:** the optional `capabilities` field added for all four external functions.
- **`templates/manage.mustache`:** unused context value `strdidacticnotice` removed; missing
  `jsconfig` added to the example context.
- **`.gitattributes`:** `LICENSE` is no longer excluded from the release ZIP.
- **`user-logo/` folder removed:** unused duplicates of the `pix/` files (about 6 MB).
- **CI:** new step `.github/scripts/check-lang-strings.sh`, which checks every
  `get_string('key', 'mod_leitbox')` call against `lang/en/leitbox.php`.

### Verification
All fixes were verified against a real local Moodle 4.x test site (PHP 8.2, MariaDB) via CLI:
plugin detection, all language string keys, class autoloading, Mustache rendering, backup and
restore file structure, database schema, external function registration and an XSS payload test
against the new import cleaning. `php -l` on all changed files and a fresh `npm run build` passed.

## [1.5.12] - 2026-03-31 (Fix: js_call_amd 1024 character limit)

### Fixed
- **Moodle warning "Too much data passed as arguments to js_call_amd":** the AMD call for
  `mod_leitbox/manage` moved from `$PAGE->requires->js_call_amd()` into a `{{#js}}` block in the
  Mustache template, because the prompt templates exceeded the 1024 character limit for AMD
  arguments. The data is provided through `json_encode()` in `$templatedata['jsconfig']`.
- Files: `manage.php`, `templates/manage.mustache`.

## [1.5.11] - 2026-03-31 (Review compliance: PARAM_RAW, comments, CI)

### Fixed
- **Issue #4, PARAM_RAW (follow-up in external.php):** `question`, `answer` and `hint` in
  `classes/external.php` changed from `PARAM_RAW` to `PARAM_CLEANHTML`, `category` to `PARAM_TEXT`.
- **Issue #7, non-English comments (follow-up):** remaining German comments in
  `classes/external.php` and `classes/completion/custom_completion.php` translated.
- **Issue #8, thirdpartylibs.xml (follow-up):** Axios (MIT) added, as it was bundled in
  `dist/assets/index.js`.

### Added
- **Issue #1, GitHub Actions CI:** `.github/workflows/ci.yml` with `moodlehq/moodle-plugin-ci`,
  testing Moodle 4.1, 4.4 and 4.5 with PHP 8.1 to 8.3 (PostgreSQL).

## [1.5.10] - 2026-03-18 (Backup file names)

### Fixed
- Backup and restore task files renamed from `.php` to `.class.php`, so Moodle finds the task
  classes. Courses could not be backed up before ("Class 'backup_leitbox_activity_task' not found").

## [1.5.9] - 2026-03-18

### Fixed
- Preparatory version for the backup fix.

## [1.5.8] - 2026-03-11 (Delete dialog)

### Fixed
- **Delete confirmation flickered:** `Notification.confirm` was replaced by `window.confirm()`,
  which stays open until the user confirms or cancels. The dependency on `core/notification` in the
  AMD module was removed.

## [1.5.7] - 2026-03-11 (Bug fixes after testing)

### Fixed
- **Missing AMD build file:** `amd/build/manage.min.js` recreated. Without it the module did not
  load in production mode, so neither the AI prompt selector nor the delete confirmations worked.
- **AMD guard:** `amd/src/manage.js` checks `params` and `params.prompts` before use.
- **AMD initialisation:** the prompt preview shows the selected option right after page load.
- **Mustache boolean:** `'selected' => true` instead of `1` in `manage.php`.

### Checked (no change needed)
- Review of all files changed in 1.5.6 (events, PARAM types, Output API, backup, template, CI).

## [1.5.6] - 2026-03-11 (Plugins directory compliance fixes)

### Fixed
- **Issue #9, hard-coded language strings:** user-visible strings in `manage.php` and `view.php`
  replaced by `get_string()` calls.
- **Issue #8, missing thirdpartylibs.xml:** file created, documenting Vue.js 3.5.29 (MIT) bundled
  in `dist/assets/`.
- **Issue #7, non-English comments:** German comments in `lib.php` and
  `classes/completion/custom_completion.php` translated.
- **Issue #6, templates, Output API and AMD modules:** `manage.php` moved to the Output API with
  the new template `templates/manage.mustache` and the AMD module `amd/src/manage.js`.
- **Issue #5, missing events:** new event class `classes/event/course_module_viewed.php`, triggered
  by `view.php`.
- **Issue #4, PARAM_RAW:** `question`, `answer` and `hint` in `manage.php` changed to
  `PARAM_CLEANHTML`; `importdata` stays `PARAM_RAW` for the import parser.
- **Issue #3, backup and restore:** all four backup files checked; no change needed.
- **Issue #2, repository name:** renamed to `moodle-mod_leitbox`.
- **Issue #1, CI:** new workflow `.github/workflows/ci.yml`.

## [1.5.5] - 2026-03-01 (Multilingual demo cards)

### Fixed
- Demo cards are stored with language-neutral markers (`##demo_q1##`) and shown in the user's
  current language (`classes/external.php` resolves them with `get_string()`).

## [1.5.4] - 2026-03-01 (dist folder in git)

### Added
- The `dist/` folder with the built frontend is now part of the repository, so the assets do not
  have to be built during installation.

## [1.5.3] - 2026-03-01 (Icon)

### Changed
- `pix/icon.svg`: placeholder icon replaced by the LeitBox icon.

## [1.5.2] - 2026-03-01 (Line endings and icon)

### Fixed
- `.gitattributes` enforces LF line endings for PHP, XML, CSS, JS and Markdown files; all files
  renormalised. The Plugins directory validator could not parse files with CRLF line endings.

### Added
- `pix/icon.svg`.

## [1.5.1] - 2026-03-01

### Added
- `index.php`, required for activity modules: lists all LeitBox activities of a course.

## [1.5.0] - 2026-03-01 (First public release)

### Changed
- Version 1.5.0 for the first submission to the Moodle Plugins directory.
- **Activity completion** reworked for Moodle 4.x: only active completion rules are written to
  `customdata`; `update_state` is called with `COMPLETION_UNKNOWN`; `get_cm()` always receives the
  course module id; help icons in the completion settings aligned.
- **Language:** English and German strings use inclusive wording ("learners").
- **Completion logic:** `completion_min_cards` counts cards with `box_number >= 1` (answered
  correctly at least once).

## [1.4.33] - 2026-03-01 (Language clean-up and completion audit)

### Changed
- German `modulename_help` uses inclusive wording.
- Unused `completion_min_correct` strings removed (covered by `completion_min_cards`).

## [1.4.32] - 2026-03-01

### Fixed
- `mod_form.php`: the third completion rule (`completion_all_mastered`) uses `addGroup` like rules
  1 and 2, so its help button is placed consistently.

## [1.4.31] - 2026-03-01

### Changed
- `styles.css`: selectors for completion help icons extended to group and single elements.

## [1.4.30] - 2026-03-01 (Completion strings)

### Changed
- Completion help texts improved in German and English, including a warning that the threshold
  must not exceed the number of cards. `styles.css` aligns the completion help icons.

## [1.4.29] - 2026-03-01 (Completion: correct cards)

### Changed
- `completion_min_cards` counts only cards with `box_number >= 1` (answered correctly at least
  once), not every card with a progress record. Strings updated accordingly.

## [1.4.28] - 2026-03-01

### Changed
- English completion strings reworded.

## [1.4.27] - 2026-03-01

### Changed
- German completion strings reworded.

## [1.4.26] - 2026-03-01 (Completion state)

### Fixed
- `classes/external.php` sends `COMPLETION_UNKNOWN` instead of `COMPLETION_COMPLETE` to
  `update_state`, so Moodle re-evaluates completion after every answer (also after a reset).

## [1.4.25] - 2026-03-01

### Fixed
- `classes/external.php` passed the instance id instead of the course module id to
  `$modinfo->get_cm()`, which aborted saving progress.

## [1.4.24] - 2026-03-01

### Changed
- `custom_completion.php` uses only the `customdata` array filled in `lib.php`; redundant database
  queries removed.

## [1.4.23] - 2026-03-01

### Fixed
- `lib.php` uses `leitbox_get_coursemodule_info` to fill `cached_cm_info` with the completion
  `customdata`; `classes/external.php` uses `get_fast_modinfo()` to pass proper `cm_info` objects to
  `update_state`.

## [1.4.22] - 2026-03-01

### Fixed
- Completion handling for Moodle 4.x reworked (`customdata`, form pre- and post-processing with
  `get_suffix()`).

## [1.4.21] - 2026-03-01

### Fixed
- Disabled completion conditions no longer count as complete, so a new activity is not marked
  complete immediately.
- The old Moodle 3.x function `leitbox_get_completion_state()` removed.
- `view.php` checks `is_enabled()` before marking the activity as viewed.

## [1.4.20] - 2026-03-01

### Fixed
- Completion check boxes keep their values in Moodle 4.3+ (form field suffixes for bulk editing),
  following the approach of `mod_quiz`.

## [1.4.19] - 2026-03-01

### Fixed
- Completion form: numeric rules are active when a number greater than 0 is entered.

## [1.4.18] - 2026-03-01

### Fixed
- Completion API for Moodle 4: new class `mod_leitbox\completion\custom_completion`.

## [1.4.17] - 2026-03-01

### Fixed
- `mod_form.php`: missing `data_preprocessing()` added, so custom completion conditions are loaded
  correctly when the activity is edited.

## [1.4.16] - 2026-03-01

### Added
- Support email address (`leitbox.moodle@gmail.com`) added to the project documents.

## [1.4.15] - 2026-03-01

### Changed
- English text of the "How does this work?" dialogue corrected.

## [1.4.14] - 2026-03-01

### Changed
- "How does this work?" dialogue: the method is attributed to Sebastian Leitner (1972).

## [1.4.13] - 2026-03-01

### Fixed
- Duplicate, outdated language strings removed.

### Added
- Help buttons for the three completion conditions in the activity settings.

## [1.4.12] - 2026-03-01

### Changed
- Automatic deletion of the demo cards checks whether own (non-demo) cards exist.

### Added
- PHPUnit tests for invalid box numbers and for counting unique cards in completion tracking.

## [1.4.11] - 2026-03-01 (Code review)

### Fixed
- Backup: invalid `annotate_ids` call removed.
- `print_error()` replaced by `throw new \moodle_exception()`.
- Demo cards are identified by the category `demo` instead of text comparisons.
- `get_cards_by_box` validates the box number (0 to 5).
- Upgrade step version numbers corrected.

## [1.4.10] - 2026-03-01

### Fixed
- `mod_form.php`: the "All cards mastered" check box is saved correctly.

## [1.4.9] - 2026-03-01

### Changed
- Completion SQL queries use `DISTINCT` to avoid counting cards twice.

## [1.4.8] - 2026-03-01

### Changed
- "Minimum cards" completion query uses `COUNT(DISTINCT cardid)`.

## [1.4.7] - 2026-03-01

### Fixed
- Completion state no longer assumes success by default.

## [1.4.6] - 2026-03-01

### Fixed
- Completion was set to complete after the first answered card; Moodle's evaluation is now called
  with `COMPLETION_UNKNOWN`.
- "Minimum cards" counts unique cards, not clicks.

## [1.4.5] - 2026-03-01

### Fixed
- PHP parse error in `view.php`.

## [1.4.4] - 2026-03-01

### Fixed
- `leitbox_get_custom_completion_rules()` added, required with `FEATURE_COMPLETION_HAS_RULES`.

## [1.4.3] - 2026-03-01

### Fixed
- Invalid SQL query in `lib.php` replaced by `count_records`.

## [1.4.2] - 2026-03-01

### Fixed
- `view.php` loads `completionlib.php` before using the completion API.

## [1.4.1] - 2026-03-01

### Fixed
- Completion is updated after every answered card; `view.php` marks the activity as viewed.

## [1.4.0] - 2026-03-01 (Rebranding and custom completion)

### Changed
- Plugin renamed from "Recall" to "LeitBox".
- New transparent logos (`logo.png`, `icon.png`).

### Added
- New completion condition "All cards mastered" (all cards in box 5).

## [1.3.1] - 2026-02-28 (Performance)

### Changed
- New web service `mod_leitbox_get_box_counts` returns the six box counts instead of six full card
  lists.

### Fixed
- Fallback texts for missing frontend strings.
- Backup and restore: `LEITBOXINDEX` decode rules added.

## [1.2.0] - 2026-02-28 (Card management and AI prompts)

### Added
- Six AI prompt templates (standard, true/false, vocabulary, cloze, Jeopardy, transfer).
- Bulk selection and deletion of cards.
- Card export as a text file in the `===CARD===` format (can be imported again).
- Limit of 200 cards per activity, enforced on the server.
- Session feedback texts and progress bar texts in English and German.

### Changed
- Plugin name "LeitBox"; neutral reset button; header layout; vendor-neutral AI prompt text.

### Fixed
- PHP warning on bulk deletion without selection; missing `cancel` string; two hard-coded strings.

## [1.1.0] - 2026-02-28 (Feedback and onboarding)

### Added
- Session feedback in four levels and a special message when all cards are in the Expert box.
- Statistics of "Got it", "Again" and "Hard" after each session.

### Changed
- The welcome demo card always comes first; texts revised; logo updated.

## [1.0.0] - 2026-02-27 (Stable release)

### Added
- Reset of the own learning progress (web service and dialogue with warning).
- Accessibility work in the Vue frontend (ARIA attributes, keyboard focus, semantic HTML).
- Logos in the header and activity icon.

### Changed
- Renamed from `mod_smartcards` to `mod_leitbox`.
- Box names instead of numbers ("Beginner" to "Expert").
- "Hard" moves a card back one box instead of to the start.
- Dashboard redesign; maturity set to `MATURITY_STABLE`.

## [0.1.0] - Initial implementation (beta)

### Added
- Moodle activity module with database tables.
- Vue.js and Tailwind CSS frontend.
- Leitner system logic.
- Text import for AI-generated flashcards.
- Backup and restore and Privacy API.
