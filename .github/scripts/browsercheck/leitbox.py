"""Browser check steps for mod_leitbox (used by the full check before a release).

Run with the generic tool, which builds a fresh Moodle in Moodle Playground for every Moodle version,
theme and browser, installs this plugin from GitHub and calls run(ctx):

    python /c/moodle/tools/browsercheck/browsercheck.py .github/scripts/browsercheck/leitbox.py --ref main

Every step clicks a control with a real click (which fails if another element covers it) and checks
that the click had its effect: dialogs open and close, a stack starts a round, the card turns over,
each rating button moves on to the next card, and the round ends on the dashboard.
The five demo cards that every new activity gets are used as test content.
"""

REPO = 'peterpleimfeldner-design/moodle-mod_leitbox'
PLUGIN_TYPE = 'mod'
PLUGIN_NAME = 'leitbox'

# Body of a PHP closure($gen, $course); returns the page to test, relative to wwwroot.
SETUP_PHP = r"""
        $cm = $gen->create_module('leitbox', ['course' => $course->id, 'name' => 'Vocabulary']);
        return '/mod/leitbox/view.php?id=' . $cm->cmid;
"""

STATE = """() => { const A = document.querySelector('#v-app-mod-leitbox'); if (!A) { return {}; }
  const front = A.querySelector('.flip-card-front h3');
  const done = [...A.querySelectorAll('button')].some(b => b.className.includes('from-indigo-600'));
  return {dashboard: !!A.querySelector('.rc-grid'),
    info: !!document.querySelector('.rc-overlay[role="dialog"]'),
    reset: !!document.querySelector('.rc-overlay[role="alertdialog"]'),
    flipped: !!A.querySelector('.flip-container.flipped'),
    question: front ? front.textContent.trim() : '',
    hint: !!A.querySelector('.flip-card-front .bg-indigo-50.italic'),
    finished: done,
    boxes: [...A.querySelectorAll('.rc-box')].map(b => b.getAttribute('aria-label')).join(' / ')}; }"""

ACTIVE_BOX = '#v-app-mod-leitbox .rc-box--active'
FLIP_BUTTON = '#v-app-mod-leitbox .flip-card-front button[aria-expanded]'
HINT_BUTTON = '#v-app-mod-leitbox .flip-card-front .mt-8 button'
CARD = '#v-app-mod-leitbox .flip-container h3'
RATE = '#v-app-mod-leitbox .flip-card-back button >> nth={}'


def run(ctx):
    ctx.goto(ctx.landing, ACTIVE_BOX)
    f = ctx.frame
    ctx.page.wait_for_timeout(1500)

    def state():
        return f.evaluate(STATE)

    def act(name, selector, check, wait=900):
        ctx.step(name, selector, check, state, wait)

    start = state()

    # Dialogs on the dashboard (teleported to the body, outside the app container).
    act('Open "How it works"', '#v-app-mod-leitbox button.rc-btn--ghost', lambda b, a: a['info'])
    act('Close "How it works"', '.rc-overlay[role="dialog"] .rc-modal-close', lambda b, a: not a['info'])
    act('Open "Reset progress"', '#v-app-mod-leitbox button.rc-btn-reset', lambda b, a: a['reset'])
    act('Cancel "Reset progress"', '.rc-overlay[role="alertdialog"] .rc-btn--ghost', lambda b, a: not a['reset'])

    # One round through the five demo cards: every way to turn a card and every rating button.
    act('Start round', ACTIVE_BOX, lambda b, a: not a['dashboard'] and a['question'] != '', 2500)
    ratings = [('Got it', 2), ('Again', 1), ('Hard', 0), ('Got it', 2), ('Got it', 2)]
    for i, (label, idx) in enumerate(ratings, start=1):
        if not state()['question']:
            ctx.note(f'Card {i} shown', False, 'No card on screen')
            break
        if i == 1 and f.locator(HINT_BUTTON).first.is_visible():
            act('Card 1: show hint', HINT_BUTTON, lambda b, a: a['hint'] and not a['flipped'])
        if i % 2:
            act(f'Card {i}: turn over (button)', FLIP_BUTTON, lambda b, a: a['flipped'], 1200)
        else:
            act(f'Card {i}: turn over (tap the card)', CARD, lambda b, a: a['flipped'], 1200)
        last = i == len(ratings)
        act(f'Card {i}: "{label}"', RATE.format(idx),
            (lambda b, a: a['finished']) if last else (lambda b, a: a['question'] != b['question'] and not a['flipped']),
            1800)

    if state()['finished']:
        act('Back to the stacks', '#v-app-mod-leitbox button.from-indigo-600',
            lambda b, a: a['dashboard'] and a['boxes'] != '', 2500)
        after = state()
        ctx.note('Stack counts changed after the round', after['boxes'] != start['boxes'], after['boxes'])
