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
# Fails CI if any get_string('key', 'mod_leitbox') call in the plugin's PHP
# source references a key that is not defined in lang/en/leitbox.php.
#
# This exists because two Moodle Plugin Directory review rounds (2026-03 and
# 2026-04) both flagged missing string definitions - once because a fix was
# only ever noted in CHANGELOG.md and never actually committed. This script
# makes that class of bug fail CI instead of only being caught by a human
# reviewer months later.
#
# @package   mod_leitbox
# @copyright 2026 Peter Pleimfeldner
# @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

set -euo pipefail

cd "$(dirname "$0")/../.."

LANGFILE="lang/en/leitbox.php"
missing=0

used_keys=$(grep -rhoE "get_string\('[A-Za-z0-9_:]+',[[:space:]]*'mod_leitbox'\)" \
    --include="*.php" . \
    | sed -E "s/get_string\('([A-Za-z0-9_:]+)'.*/\1/" \
    | sort -u)

for key in $used_keys; do
    if ! grep -qF "\$string['${key}']" "$LANGFILE"; then
        echo "Missing string definition: '${key}' (used via get_string(), not found in ${LANGFILE})"
        missing=1
    fi
done

if [ "$missing" -eq 1 ]; then
    echo ""
    echo "One or more get_string() keys used in the code are not defined in ${LANGFILE}."
    exit 1
fi

echo "All get_string('key', 'mod_leitbox') references are defined in ${LANGFILE}."
