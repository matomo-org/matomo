# DebugView — Decision Log

Design decisions and their rationale, as they stand in the current implementation.
Superseded decisions from earlier iterations have been removed.

## Data flow

1. **The stream is served straight from the plugin's own raw-request table; visit context
   loads lazily in the browser** — `DebugView.getRecentHits` reads only
   `debugview_raw_request`, so its cost is independent of site traffic and debug hits can
   never be missed on busy sites. Type/title/subtitle are derived from the raw parameters
   (`HitFlattener`), and the monotonic `idRawRequest` (BIGINT auto-increment) doubles as
   the incremental polling cursor (`minId`, strictly greater-than). There is deliberately
   no timestamp-based cursor: the id cursor has no same-second ambiguity. One stream row
   per REQUEST — a pageview and the goal it triggered share one row; ping requests appear
   as own rows (they are real requests).
2. **Visit context via `Live.getLastVisitsDetails` with a `visitId==<idVisit>` segment**,
   called directly from the browser when a hit is opened (core segment on the log_visit
   primary key, `allowAnonymous=false`). That call re-enforces view access and the
   visits-log gate server-side, so DebugView can never show more visit data than the
   Visits Log itself. If the visit is unavailable (purged, backdated request, race), the
   pane still shows the captured parameters plus a note.
3. **Stored `server_time` is the RECEIPT time, not the event time** — the stream promises
   "requests as they arrive", but `getCurrentTimestamp()` honours `cdt`, so a queued or
   backdated authenticated request arriving now would otherwise never appear in the
   real-time window. Capture stores `time()` for retention, streaming and window purposes;
   the event timestamp stays visible as the `cdt` parameter. Accepted consequence: for
   backdated requests the lazily loaded visit context may not be found. Display strings
   (`timePretty`, minute-rail labels) use the site timezone (server-side via
   `Date::getLocalized`, client-side via `Intl.DateTimeFormat` with the API's `timezone`).

## Capture gating

4. **Two gates must both hold: an active viewer AND `debug=1` on the request** (where only debug_mode traffic reaches DebugView). Normal production traffic is
   never stored, even while someone has Debug View open; the visits log itself still
   records everything. The empty-state hint and the API docs explain both gates — the
   first `getRecentHits` call arms capturing, so requests sent before it are not in the
   stream. Arming stays a side effect of the read on purpose: the only sensible
   keep-alive for "someone is watching" is the poll itself, and a separate
   startCapturing method would still need the poll to re-arm it.
5. **Arming state is one option row per site**
   (`DebugView.rawRequestActiveSite.<idSite>`, value = active-until UTC timestamp,
   ~3 minutes, rewritten only about once a minute per active site). One row per site
   means arming never reads or writes another site's state — no lost-update race between
   concurrent viewers of different sites. Expired rows are removed by the hourly task,
   the site's row on site deletion (`SitesManager.deleteSite.end`), and all of them on
   uninstall.
6. **The tracker reads the arming flag straight from the option table, never via the
   tracker cache** — on large installations the general tracker cache is deliberately
   long-lived (e.g. ~75 minutes on the cloud service), so invalidating it on re-arm would
   be a real performance hazard. The processor checks the free `debug=1` string
   comparison FIRST; only explicitly flagged debug traffic pays the single direct indexed
   SELECT on the option table (bypassing the Option class and all caches — `Db` routes to
   the tracker connection automatically during tracking). The reader side polls the same
   direct SELECT because `Option::get()` would load all options on every 5-second poll.
   Writes go through `Option::set` for correct upsert/cache semantics, and arming never
   invalidates anything (covered by a regression test).
   Trap for future work: a plugin is only loaded during tracking if
   `Plugin\Manager::isTrackerPlugin()` classifies it as one — which checks dimensions and
   registered `Tracker.*` events, NOT whether a `Tracker/` directory exists. DebugView
   explicitly overrides `isTrackerPlugin()` to return true.

## Capture & storage

7. **Capture is a `Tracker\RequestProcessor`** (`Tracker/RawRequestProcessor`,
   auto-discovered, running after core processors in plugin load order so the recorded
   action's `idlink_va` is available), with the shared gating/redaction/storage logic in
   `Tracker/RequestCapture`. Every processor body is wrapped in try/catch — debug capture
   must never break tracking.
8. **Bot-path requests are captured too** — requests carrying `recMode=1/2` with a
   detected bot user agent take the tracker's bot path (`Piwik\Tracker\BotRequest`),
   where the normal recordLogs stage never runs. A dedicated `Tracker\BotRequestProcessor`
   (core extension point) applies the same gates via `RequestCapture` and stores the
   request with a `bot` group (name from the device-detector bot parser — the tracker's
   own detector instance discards bot details) and without idvisit/idlink_va. Its
   `handleRequest()` returns false: the tracker ORs all bot processors' results, so other
   processors still count as having handled the request — we only avoid adding an archive
   invalidation trigger of our own.
9. **Stored parameter hygiene** — `token_auth` is redacted (`__redacted__`) before
   storage; single values are truncated at 1,000 characters (multibyte-safe `mb_substr`;
   a byte cut could split UTF-8 and make the row unencodable), recursively for nested
   values like client hints, ending in a `...` marker (plain dots — a word would need
   translating); payloads over 64 KB are dropped entirely. This bounds what oversized
   payloads (e.g. Heatmap & Session Recording) or attackers can store per value.
10. **Stored format** — one JSON column with the groups `query` (raw tracking
    parameters), `defaults` (passively received request data: user agent, browser
    language, client hints, receipt time), `other` (state derived while tracking,
    currently `isAuthenticated`), `actionType` (the tracker's Action subclass type) and
    optionally `bot`. Rows stored before a group existed decode with that group as null.
11. **The 500-rows-per-site cap is a hard operational bound, not just a UI limit** — the
    reader query fetches at most the newest 500 matching rows (single limited query,
    chronological result), and every stream poll trims the polled site back to the cap
    (two small indexed queries paid by the watching viewer — capture only happens for
    watched sites, so this bounds growth exactly where it can occur, with zero cost on
    the tracker path). The hourly task (`Tasks::trimRawRequests`) remains the safety net:
    it purges rows older than the longest displayable window (`API::MAX_LAST_MINUTES`
    = 60 minutes), caps every site, and drops expired arming options. Per-site (not
    global), so one busy site cannot evict another site's captures.

## API

12. **`DebugView.getRecentHits(idSite, lastMinutes = 30, minId = 0)`** returns
    `{ hits, serverTime, timezone }`; `lastMinutes` is clamped to 1–60. Each hit carries
    `idRawRequest` (named to match other APIs and to distinguish it from `idVisit` and
    `idLinkVa`), the visit references for the lazy Live lookup, receipt `timestamp` and
    `timePretty`, `type`, `title`, `subtitle`, the three parameter groups, and
    `isBot`/`botName`. The docblock documents every field including the redaction and
    truncation caveats — plugin API docblocks are public contract.
13. **Ids stay decimal strings in the browser** — BIGINT UNSIGNED can in principle exceed
    JavaScript's safe integer range, and plain string comparison orders "9" after "10".
    The UI never converts ids to Number: the cursor is a decimal string and comparisons
    go through a length-then-lexical decimal comparator.
14. **Stored parameters are treated as hostile input end to end** — PHP parses e.g.
    `e_c[]=poison` into an array, and such requests reach the capture (especially via the
    bot path, which skips action validation). `HitFlattener` funnels every value used for
    type/title/subtitle derivation through a scalar-string extraction helper
    (arrays/objects become ''), and the API isolates each row in a try/catch — one
    malformed stored row must never break the stream. Raw array values are still stored
    and shown as nested rows in the Parameters tab.

## Typing & the details pane

15. **Other plugins' request kinds are typed via the tracker's own Action classes** — the
    Action subclass chosen for the request (`getActionType()`) is stored with the row;
    `deriveType` prefers it via a hard-coded map of the stable public constants
    (ActionMedia 94 → media, ActionForm 95 → form, ActionHsr 96 → sessionRecording,
    ActionCrash 110 → crash). No marker-parameter sniffing and no new API surface.
    Several plugins null the recorded action in their processors, so the capture
    re-derives it via `Action::factory` purely to learn the kind (it records nothing).
    A form payload piggybacked on a pageview (`fa_pv=1`) keeps the pageview Action and
    stays typed pageview — matching the tracker.
16. **Icons are exactly what the visits log shows** — hardcoded to the same assets
    (`plugins/Morpheus/images/action|event|goal|download|link|search|ecommerceOrder|
    ecommerceAbandonedCart|contentinteraction|contentimpression.svg`, content following
    the visits log's interaction/impression distinction via `c_i`; MediaAnalytics
    audio/video.png by `ma_mt`, FormAnalytics form.png, CrashAnalytics crash.png).
    Types without a visits-log image (ping, session recordings, unknown) use icon-font
    glyphs.
17. **Per-type matching of processed details in the pane** — media, form and crash
    requests have no log_link_visit_action row, so the Processed tab matches Live's
    enriched actions with type-specific keys: media by resource (`ma_re` ↔ the media
    action's `url`), crash by error-message prefix (stored values are truncated), form by
    pageview id (`pv_id` ↔ `idpageview`) with the numeric form id as fallback; core
    types match via `idLinkVa` ↔ `pageId`/`goalPageId`. Heatmap & Session Recording
    requests are deliberately not matched (Live has no per-action entries) — the pane
    says "Cannot be shown for this type of request." Bot hits skip the Live lookup
    entirely (there is no visit) and the pane explains why.
18. **Known capture gaps are disclosed in the UI** — excluded visits are not captured
    (the pipeline aborts before the capture stage), and Heatmap & Session Recording
    requests may not be captured at all (their processor can stop the pipeline early).
    The page description states both.

## UI

19. **Tabs: Parameters (input) and Processed (output)** — Parameters shows the raw
    tracking request parameters, the "Default Parameters" group and the "Other" group;
    Processed shows the matched "Processed hit details" and "Processed Visit details".
    Media/form hits show a hint that multiple requests update the same processed details
    over time.
20. **Site selector in the page header, full page reload on switch** — like the scheduled
    reports page, both index.twig and disabled.twig render
    `@CoreHome/_siteSelectHeader.twig` in the `topcontrols` block; changing the site
    updates the URL's idSite (a full reload on an admin page). There is no in-page
    selector and no SPA-style site switching.
21. **The Diagnostics menu item is always visible** to logged-in users with view access
    (order 20), even when the Live plugin or the visits log is disabled — the page itself
    then explains why Debug View cannot be used (including that changing the setting may
    require an administrator, since the visits log can be disabled per site or
    system-wide). Better than silently hiding that the feature exists.
22. **The controller requires an explicit idSite** (`$this->idSite` +
    `checkSitePermission()`) — no fallback to the first accessible site. Every real entry
    point carries an idSite; a missing one indicates a hand-crafted URL, and an explicit
    error beats silently showing another site's data.
23. **Pause buffers instead of stopping** — while paused, polling continues and new hits
    accumulate in a client-side buffer counted by the "N new" circle;
    resume flushes it. The circle stays visible while live ("0 new"). Minute-rail counts are tracked separately from the rendered stream
    (capped at 500 rows), so the rail stays accurate when old rows leave the DOM.
    Every poll is tagged with a stream generation so a stale in-flight response can never
    pollute the stream after a reset.
24. **No "Top events" pane** — per spec, explicitly excluded; asserted in the E2E script.
25. **Styles follow the component-sibling convention** — one .less file per Vue component
    (registered individually), shared blocks (hit icon circle, bot badge) in the
    plugin-level stylesheet, BEM `--modifier` classes for states, flex keywords, and
    `safe center` for flex centering.

## Tracker SDK

26. **JS tracker public API `enableDebugMode` / `disableDebugMode` / `isDebugModeEnabled`**
    (js/piwik.js; `enableDebugMode` is in the `applyFirst` list so it takes effect before
    queued tracking calls) — the supported way to flag traffic for Debug View
    (`_paq.push(['enableDebugMode'])`). The built matomo.js/piwik.js in this branch
    include the change. Other tracker SDKs and Tag Manager preview mode are follow-up
    work.

## Plumbing & lifecycle

27. **Dao/Model split, matching other plugins** — all SQL lives in `Dao\RawRequestLog`
    (schema, insert, limited read, trims, direct option reads); the business logic
    (arming, gating, encoding/decoding, truncation, trim policy) lives in
    `Model\DebugRequests`, injected via DI into API, tracker processors and the task.
    No static methods, no private methods on plugin classes; the dependency-free
    `HitFlattener` holds the pure derivation logic with unit-tested public methods.
28. **Install/uninstall** — the table is created with CREATE TABLE IF NOT EXISTS via
    `install()` (fresh installs and existing activated installations alike) and
    registered through `Db.getTablesInstalled`; uninstall drops the table and deletes all
    arming options.

## Testing

29. **The fixture covers every core tracking type across four sites** — site 1: pageview,
    ping, event, site search, outlink, download, content interaction, goal conversion
    (goal created in the fixture), ecommerce order, one unflagged control request and one
    bot-path request; site 2: a different small set; site 3: no data; site 4: visits log
    disabled per site (exercising the friendly disabled page). Paid plugins' tracking
    types are deliberately excluded. System tests pin type derivation, titles and
    per-site isolation. Integration tests use a UTC+12 site (Pacific/Auckland) so any
    timezone regression fails loudly instead of passing accidentally on UTC.
30. **UI screenshot tests normalise instead of waiting for stable state** — clock-derived
    text is rewritten to fixed values, volatile parameter values are replaced via a key
    list plus a date/number pattern, animations are frozen, the pulsing live dot and the
    second-boundary-dependent gap rows are hidden via injected CSS (re-render-proof), the
    minutes rail is hidden and pinned to a fixed height, notification banners are
    excluded (environment noise), and a 0.003% comparison threshold absorbs rasterisation
    jitter while still failing on any real content change. Rare devtools-protocol stalls
    are retried instead of failing the suite.
31. **The fixture defends itself against stale test-runner state** — it removes the web
    tracker's test cache files directly (CLI cache clears can resolve a different tmp
    path than the web server), runs the core updater when log_visit is missing dimension
    columns, and sends one unrecorded warm-up request (`rec=0`) so the tracker bootstraps
    before the first asserted request without shifting any ids pinned in expected output.
32. **E2E runs against a dedicated local site and users** (`debugview_viewer` with view
    access, `debugview_noaccess` without); credentials/tokens are passed to
    `tests/e2e/run.sh` via environment variables, never committed.
