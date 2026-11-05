"""Touch check for the LeitBox learner view (real touch emulation, not mouse clicks).

Opens an activity as a learner on a phone (390x844) and a tablet (820x1180) with Playwright
(has_touch, is_mobile) and checks every gesture by its effect:
  - tap a stack starts a round, tap the card turns it over exactly once (no double flip from the
    touch event plus the emulated mouse click), tap the turn-over button, tap the hint,
    tap each rating button moves on exactly one card, swipe across the card changes nothing,
  - the "How it works" and "Reset progress" dialogs open and close by tap.

Usage (Git Bash, local test site):
    python .github/scripts/touchcheck/touchcheck.py --base http://localhost:81 --cmid 175 \
        --user lb_student --password-file docs-intern/TEST-ZUGAENGE-LOKAL.md

The password file is a local, git-ignored list ("- <user> (<role>): <password>").
Exit code 1 if any check fails. Screenshots go to --out.
"""
import argparse
import pathlib
import re
import sys

from playwright.sync_api import sync_playwright

STATE = """() => { const A = document.querySelector('#v-app-mod-leitbox'); if (!A) { return {}; }
  const front = A.querySelector('.flip-card-front h3');
  const badge = [...A.querySelectorAll('div')].find(d => /\\d+\\s*\\S+\\s*\\d+$/.test(d.textContent.trim()) && d.className.includes('rounded-full'));
  return {dashboard: !!A.querySelector('.rc-grid'),
    info: !!document.querySelector('.rc-overlay[role="dialog"]'),
    reset: !!document.querySelector('.rc-overlay[role="alertdialog"]'),
    flipped: !!A.querySelector('.flip-container.flipped'),
    question: front ? front.textContent.trim() : '',
    hint: !!A.querySelector('.flip-card-front .bg-indigo-50.italic'),
    finished: [...A.querySelectorAll('button')].some(b => b.className.includes('from-indigo-600')),
    counter: badge ? badge.textContent.replace(/\\s+/g, ' ').trim() : ''}; }"""


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--base', default='http://localhost:81')
    ap.add_argument('--cmid', type=int, default=175)
    ap.add_argument('--user', default='lb_student')
    ap.add_argument('--password-file', required=True)
    ap.add_argument('--out', default='touchcheck-ergebnis')
    a = ap.parse_args()
    m = re.search(r'^- ' + re.escape(a.user) + r' \([^)]*\): (\S+)', pathlib.Path(a.password_file).read_text(encoding='utf-8'), re.M)
    if not m:
        sys.exit('No password for ' + a.user)
    pw = m.group(1)
    out = pathlib.Path(a.out)
    out.mkdir(parents=True, exist_ok=True)
    rows = []

    def check(label, name, ok, detail=''):
        rows.append((label, name, bool(ok), detail))
        print(('ok    ' if ok else 'FAIL  ') + f'{label} | {name}' + (f' | {detail}' if detail and not ok else ''))

    with sync_playwright() as p:
        for label, (w, h) in [('phone 390x844', (390, 844)), ('tablet 820x1180', (820, 1180))]:
            br = p.chromium.launch(channel='chrome')
            ctx = br.new_context(viewport={'width': w, 'height': h}, has_touch=True, is_mobile=True,
                                 device_scale_factor=2)
            pg = ctx.new_page()
            errors = []
            pg.on('pageerror', lambda e: errors.append(str(e)))
            pg.goto(a.base + '/login/index.php')
            pg.fill('#username', a.user)
            pg.fill('#password', pw)
            pg.click('#loginbtn')
            pg.goto(f'{a.base}/mod/leitbox/view.php?id={a.cmid}')
            pg.wait_for_selector('#v-app-mod-leitbox .rc-grid', timeout=30000)
            pg.wait_for_timeout(1500)

            def st():
                return pg.evaluate(STATE)

            def tap(sel, wait=900):
                pg.locator(sel).first.scroll_into_view_if_needed()
                pg.locator(sel).first.tap()
                pg.wait_for_timeout(wait)

            def swipe(sel, dx):
                box = pg.locator(sel).first.bounding_box()
                cdp = ctx.new_cdp_session(pg)
                x, y = box['x'] + box['width'] / 2, box['y'] + box['height'] / 2
                cdp.send('Input.dispatchTouchEvent', {'type': 'touchStart', 'touchPoints': [{'x': x, 'y': y}]})
                for i in range(1, 9):
                    cdp.send('Input.dispatchTouchEvent', {'type': 'touchMove', 'touchPoints': [{'x': x + dx * i / 8, 'y': y}]})
                cdp.send('Input.dispatchTouchEvent', {'type': 'touchEnd', 'touchPoints': []})
                pg.wait_for_timeout(900)

            tap('#v-app-mod-leitbox button.rc-btn--ghost')
            check(label, 'Tap "How it works" opens the dialog', st()['info'])
            tap('.rc-overlay[role="dialog"] .rc-modal-close')
            check(label, 'Tap close shuts the dialog', not st()['info'])
            tap('#v-app-mod-leitbox button.rc-btn-reset')
            check(label, 'Tap "Reset" opens the confirmation', st()['reset'])
            tap('.rc-overlay[role="alertdialog"] .rc-btn--ghost')
            check(label, 'Tap "Cancel" shuts it, nothing reset', not st()['reset'] and st()['dashboard'])

            tap('#v-app-mod-leitbox .rc-box--active', 2500)
            s = st()
            check(label, 'Tap a stack starts a round', not s['dashboard'] and s['question'], s)
            n = 0
            while st()['question'] and not st()['finished'] and n < 6:
                n += 1
                before = st()
                if n == 1 and pg.locator('#v-app-mod-leitbox .flip-card-front .mt-8 button').first.is_visible():
                    tap('#v-app-mod-leitbox .flip-card-front .mt-8 button')
                    s = st()
                    check(label, 'Tap hint shows it without turning the card', s['hint'] and not s['flipped'])
                if n == 1:
                    swipe('#v-app-mod-leitbox .flip-container', -150)
                    s = st()
                    check(label, 'Swipe across the card changes nothing', s['question'] == before['question'] and not s['flipped'], s)
                if n % 2:
                    tap('#v-app-mod-leitbox .flip-container h3', 1300)
                    check(label, f'Card {n}: tap the card turns it over once', st()['flipped'])
                else:
                    tap('#v-app-mod-leitbox .flip-card-front button[aria-expanded]', 1300)
                    check(label, f'Card {n}: tap the turn-over button turns it once', st()['flipped'])
                pg.wait_for_timeout(600)
                check(label, f'Card {n}: card stays turned (no double flip)', st()['flipped'])
                idx = [2, 1, 0][(n - 1) % 3]
                tap(f'#v-app-mod-leitbox .flip-card-back button >> nth={idx}', 1800)
                after = st()
                nb, na = re.findall(r'\d+', before['counter']), re.findall(r'\d+', after['counter'])
                one = after['finished'] or (nb and na and int(na[0]) == int(nb[0]) + 1 and not after['flipped'])
                check(label, f'Card {n}: tap rating {idx} moves on exactly one card ({before["counter"]} -> '
                      + (after['counter'] if not after['finished'] else 'end of round') + ')', one)
            if st()['finished']:
                tap('#v-app-mod-leitbox button.from-indigo-600', 2500)
                check(label, 'Tap "back" returns to the stacks', st()['dashboard'])
            check(label, 'No JavaScript errors', not errors, '; '.join(errors)[:300])
            pg.screenshot(path=str(out / f"{label.split()[0]}.png"))
            br.close()
    bad = [r for r in rows if not r[2]]
    print(f'\n{len(rows) - len(bad)}/{len(rows)} checks ok')
    sys.exit(1 if bad else 0)


if __name__ == '__main__':
    main()
