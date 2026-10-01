#!/usr/bin/env bash
#
# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# Checks the release package exactly as the release workflow builds it
# (git archive, honouring export-ignore in .gitattributes).
#
# Each check exists because a Moodle Plugins directory review found the
# problem in an earlier version (review round 3, 2026-09):
# - the package was 6.4 MB, almost all of it oversized icons;
# - the Vue source (frontend/) and the tests were left out of the package;
# - the shipped CHANGELOG.md was written in German.
#
# @package   mod_leitbox
# @copyright 2026 Peter Pleimfeldner
# @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

set -euo pipefail

cd "$(dirname "$0")/../.."

MAX_PACKAGE_BYTES=$((1024 * 1024))   # 1 MB for the whole (uncompressed) package.
MAX_FILE_BYTES=$((300 * 1024))       # 300 KB for any single file.
failed=0

tmpdir=$(mktemp -d)
trap 'rm -rf "$tmpdir"' EXIT
git archive --format=tar HEAD | tar -x -C "$tmpdir"

total=$(find "$tmpdir" -type f -printf '%s\n' | awk '{s += $1} END {print s + 0}')
echo "Package size (uncompressed): ${total} bytes"
if [ "$total" -gt "$MAX_PACKAGE_BYTES" ]; then
    echo "The package is larger than ${MAX_PACKAGE_BYTES} bytes."
    failed=1
fi

while IFS= read -r line; do
    echo "File larger than ${MAX_FILE_BYTES} bytes: ${line}"
    failed=1
done < <(cd "$tmpdir" && find . -type f -size +"${MAX_FILE_BYTES}"c -printf '%P (%s bytes)\n')

# Files that must be shipped.
for required in version.php CHANGELOG.md README.md LICENSE thirdpartylibs.xml \
        pix/monologo.svg dist/assets/index.js \
        frontend/package.json frontend/package-lock.json frontend/vite.config.js frontend/src/main.js \
        tests/lib_test.php tests/privacy/provider_test.php; do
    if [ ! -f "$tmpdir/$required" ]; then
        echo "Missing from the package: ${required}"
        failed=1
    fi
done

# Files and folders that must not be shipped.
for forbidden in node_modules docs-intern docs .github CLAUDE.md CHANGELOG.de.md; do
    if [ -n "$(find "$tmpdir" -name "$forbidden" -print -quit)" ]; then
        echo "Must not be in the package: ${forbidden}"
        failed=1
    fi
done

# Everything except the German language pack must be in English. German
# umlauts and sharp s are a reliable sign of German text.
while IFS= read -r line; do
    echo "Non-English text (German characters) in: ${line}"
    failed=1
done < <(cd "$tmpdir" && grep -rlI '[äöüÄÖÜß]' . | grep -v '^\./lang/de/' | sed 's|^\./||' || true)

if [ "$failed" -eq 1 ]; then
    echo ""
    echo "Release package check failed (see above)."
    exit 1
fi

echo "Release package check passed."
