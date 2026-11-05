# Upgrade check

Proves that upgrading from the previous release keeps all learner data. Run it before every release.

1. Install the previous release (e.g. tag `v1.6.1`) on a test site.
2. `php seed.php <moodle>/config.php` creates the course "LeitBox upgrade check" with one activity per
   card order and completion rule (minimum cards, minimum mastered, all mastered with "view", a
   combination, manual completion), own cards with HTML, hints and categories next to the five demo
   cards, and four students with progress in different boxes. The students answer through the web
   service function `\mod_leitbox\external::submit_answer()`, so progress and completion are
   calculated as in the learning view. One manual and one automatic completion state are set by a
   teacher.
3. `php snapshot.php <moodle>/config.php before.json` stores every LeitBox related row of the site
   (all `leitbox*` tables, course modules, completion states and views, plugin version).
4. Replace the plugin code with the new version and run `php admin/cli/upgrade.php --non-interactive`.
5. `php snapshot.php <moodle>/config.php after.json`
6. `php compare.php before.json after.json` fails on any lost or changed row. The changes an upgrade
   is allowed to make are listed, with reasons, in `expected-changes.php`; update it for every
   release that changes the database.

The CI job "Upgrade check" runs these steps on Moodle 4.1 (PostgreSQL) and 5.1 (MariaDB).

Locally, `run-local.sh` runs steps 2 to 6 on the local test site and backs up the database first.
It changes the site (new course, users, upgraded plugin): restore the backup afterwards.
