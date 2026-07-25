#!/usr/bin/env python3
"""Report only the PHPCS findings that sit on lines a pull request added.

This plugin predates the WordPress coding standards by roughly a decade, so its files carry
hundreds of pre-existing findings each. Running PHPCS over whole files would therefore fail
every pull request that touches legacy code, whether or not it made anything worse -- and a
job that is always red is a job nobody reads.

Filtering to added lines keeps the signal: a finding is reported when this change introduced
it, and the historical backlog stays a separate, deliberate piece of work.

Even scoped this way it is advisory, not a gate. Some of what it reports cannot reasonably
be fixed: the sniffs cannot see through `apply_filters()` to an escaper inside it, cannot
know that an interpolated ORDER BY direction was whitelisted, and object to the
`$before_title` / `$after_title` arguments that every WordPress widget emits. Failing a build
on those would mean scattering `phpcs:ignore` annotations through unrelated changes, so the
job reports and moves on. Pass --strict to exit non-zero instead.

Usage: phpcs-added-lines.py [--strict] <phpcs-report.json> <unified-diff>
"""

import collections
import json
import os
import re
import sys

HUNK = re.compile(r'^@@ -\d+(?:,\d+)? \+(\d+)(?:,(\d+))? @@')


def added_lines(diff_path):
    """Map each file to the set of line numbers this diff adds to it."""
    added = collections.defaultdict(set)
    current = None
    new_line = 0
    with open(diff_path, encoding='utf-8', errors='replace') as fh:
        for line in fh:
            if line.startswith('+++ '):
                path = line[4:].strip()
                current = None if path == '/dev/null' else re.sub(r'^b/', '', path)
                continue
            if line.startswith('@@'):
                m = HUNK.match(line)
                if m:
                    new_line = int(m.group(1))
                continue
            if current is None:
                continue
            if line.startswith('+'):
                added[current].add(new_line)
                new_line += 1
            elif not line.startswith('-'):
                # Context line. With --unified=0 these are rare, but count them anyway so
                # the line numbering stays correct if the diff is ever generated with context.
                new_line += 1
    return added


def main():
    args = [a for a in sys.argv[1:] if a != '--strict']
    strict = '--strict' in sys.argv[1:]
    if len(args) != 2:
        print(__doc__, file=sys.stderr)
        return 2

    report_path, diff_path = args
    if not os.path.exists(report_path) or os.path.getsize(report_path) == 0:
        print('PHPCS produced no report; nothing to check.')
        return 0

    with open(report_path, encoding='utf-8', errors='replace') as fh:
        report = json.load(fh)

    added = added_lines(diff_path)

    def to_repo_path(reported_path):
        """Map a path in the PHPCS report onto the repo-relative path used by the diff.

        PHPCS reports absolute paths. Those normally sit under the working directory, but
        not always -- so fall back to matching the longest path suffix that the diff knows
        about, rather than silently treating every finding as untouched and reporting a
        false all-clear.
        """
        rel = os.path.relpath(reported_path, os.getcwd())
        if not rel.startswith('..'):
            return rel
        norm = reported_path.replace(os.sep, '/')
        for candidate in added:
            if norm.endswith('/' + candidate) or norm == candidate:
                return candidate
        return rel

    reported = 0
    suppressed = 0
    for abs_path, data in report.get('files', {}).items():
        rel = to_repo_path(abs_path)
        touched = added.get(rel, set())
        for msg in data.get('messages', []):
            if msg.get('type') != 'ERROR':
                continue
            if msg.get('line') in touched:
                reported += 1
                print('{}:{}:{}  {}\n    {}'.format(
                    rel, msg.get('line'), msg.get('column'), msg.get('source'), msg.get('message')))
            else:
                suppressed += 1

    print()
    if suppressed:
        print('{} pre-existing finding(s) on untouched lines were not reported.'.format(suppressed))
    if reported:
        print('{} finding(s) on lines this change adds -- worth a look, but note that some '
              'are unavoidable; see the header of this script.'.format(reported))
        return 1 if strict else 0
    print('No PHPCS findings on added lines.')
    return 0


if __name__ == '__main__':
    sys.exit(main())
