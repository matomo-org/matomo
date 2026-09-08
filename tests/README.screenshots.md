# Screenshots UI tests

Matomo contains UI tests that compare captured screenshots of URLs and UI controls with expected screenshots.
If a captured screenshot does not match its expected screenshot, the build will fail. 

For a developer documentation, refer to the following guide: https://developer.matomo.org/guides/tests-ui

## Browser

Screenshots are rendered by a fixed Chrome for Testing version, pinned in
`lib/screenshot-testing/.puppeteerrc.cjs`, so that rendering does not change with the Chrome release
the machine happens to have. Download it with `npm run install-browser` in `tests/lib/screenshot-testing`
(`ddev matomo:init:tests` does this for you). Without it, the tests warn and fall back to a system
Chrome, whose screenshots will not match the expected ones.

Bumping the pinned version changes rendering, so **all** expected screenshots -- core and plugins --
have to be re-generated in the same change. Native linux/arm64 has no Chrome for Testing build and
always uses a system browser, so expected screenshots cannot be generated there.

### Deterministic text rendering

Even on the pinned browser, Chrome may hint and subpixel-position text differently from one run to
the next, moving individual line boxes by a pixel while the glyphs render identically. A run that
does so fails every text-bearing screenshot in a spec at once.

Setting `MATOMO_UI_DETERMINISTIC_TEXT=1` launches the browser with `--font-render-hinting=none`,
`--disable-font-subpixel-positioning` and `--disable-lcd-text`, which removes that variation. It is
off by default because it changes where text lands: expected screenshots generated without it will
not match runs with it, and vice versa. Whoever turns it on re-generates the expected screenshots it
affects in the same change.

The variable is read once when the browser launches, so it applies to everything in that process --
a workflow job or a whole local run, not one spec inside a test group.
