#!/usr/bin/env bash
# Upgrade check on the local test site (see README.md). Backs up the database first.
#
# Usage: bash run-local.sh [previous tag] [ref to install] [work dir]
#   previous tag    release that must be installed on the site now (default: v1.6.1)
#   ref to install  git ref whose code is installed and upgraded to (default: HEAD);
#                   "worktree" installs the working tree including uncommitted changes
#                   to tracked files (untracked files are left out, as in a release)
#   work dir        where before.json and after.json are written (default: a temp dir)
#
# Paths can be changed with the environment variables MOODLE (Moodle code), PHP, MYSQLDUMP
# and BACKUPDIR. The defaults are the local test site of this project on Windows (Git Bash).
#
# Expects the site to have the previous release installed already (step 1 of README.md); this
# script backs up the database, seeds data, takes the snapshots, installs the new code and
# upgrades. Do not run it while a Behat run uses the same Moodle code. The site keeps the test
# course and the upgraded plugin: restore the backup afterwards, e.g.
#   /c/moodle/server/mysql/bin/mysql.exe -u root moodle < /c/moodle/backups/<file>.sql
# and copy the previous plugin code back into mod/leitbox.
set -euo pipefail

previous="${1:-v1.6.1}"
ref="${2:-HEAD}"
work="${3:-$(mktemp -d)}"
moodle="${MOODLE:-/c/moodle/server/moodle}"
php="${PHP:-/c/moodle/server/php/php.exe}"
mysqldump="${MYSQLDUMP:-/c/moodle/server/mysql/bin/mysqldump.exe}"
backupdir="${BACKUPDIR:-/c/moodle/backups}"
repo="$(cd "$(dirname "$0")/../../.." && pwd)"
here="$repo/.github/scripts/upgrade-check"
mkdir -p "$work"

# PHP for Windows needs Windows paths; cygpath exists in Git Bash only.
winpath() {
    if command -v cygpath > /dev/null; then
        cygpath -m "$1"
    else
        echo "$1"
    fi
}
config="$(winpath "$moodle/config.php")"

installed=$("$php" -r 'define("CLI_SCRIPT", true); require($argv[1]); echo get_config("mod_leitbox", "version");' \
    -- "$config")
expected=$(git -C "$repo" show "$previous:version.php" | sed -n 's/.*plugin->version *= *\([0-9]*\).*/\1/p')
if [ "$installed" != "$expected" ]; then
    echo "Installed mod_leitbox version $installed is not $previous ($expected). Install $previous first." >&2
    exit 1
fi
dirroot=$("$php" -r 'define("CLI_SCRIPT", true); require($argv[1]); echo $CFG->dirroot;' -- "$config")
dbname=$("$php" -r 'define("CLI_SCRIPT", true); require($argv[1]); echo $CFG->dbname;' -- "$config")
plugindir="$(cygpath -u "$dirroot" 2> /dev/null || echo "$dirroot")/mod/leitbox"

if [ "$ref" = "worktree" ]; then
    # A commit object of the working tree that changes nothing in the repository (no stash entry).
    ref=$(git -C "$repo" stash create)
    ref="${ref:-HEAD}"
fi

mkdir -p "$backupdir"
backup="$backupdir/moodle_vor_leitbox_upgradecheck_$(date +%Y%m%d_%H%M).sql"
echo "Backing up database $dbname to $backup"
"$mysqldump" -u root --single-transaction "$dbname" > "$backup"

"$php" "$(winpath "$here/seed.php")" "$config"
"$php" "$(winpath "$here/snapshot.php")" "$config" "$(winpath "$work/before.json")"

# Replace the plugin code (exactly what a release ZIP contains).
rm -rf "$plugindir"
mkdir -p "$plugindir"
git -C "$repo" archive "$ref" | tar -x -C "$plugindir"
"$php" "$(winpath "$moodle/admin/cli/upgrade.php")" --non-interactive
"$php" "$(winpath "$moodle/admin/cli/purge_caches.php")"

"$php" "$(winpath "$here/snapshot.php")" "$config" "$(winpath "$work/after.json")"
"$php" "$(winpath "$here/compare.php")" "$(winpath "$work/before.json")" "$(winpath "$work/after.json")"
echo "Backup of the database before the check: $backup"
