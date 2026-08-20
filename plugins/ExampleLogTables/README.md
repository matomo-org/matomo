# ExampleLogTables

A plugin that stores its data in log tables of its own, from the tracking request that fills them
through to the subject-access export that has to include them.

Core describes custom log tables as not yet an official API, so treat the interfaces below as stable
within a major version rather than forever. Everything here is verified against the core version in
`plugin.json`.

## Owns

- Log tables belonging to a plugin rather than to core, and the DAOs that create and write them.
- The declaration that makes such a table part of Matomo's log data — what a join declaration buys
  you, and what it commits you to.
- A write path in the tracker for rows that are not a column of an existing log table.
- Segments over your own tables, including one that resolves across two joins.
- Aggregating your own tables into an archived metric.
- Showing your own data in the visits log, both halves of it: the API payload and the rendered entry.
- Data subject erasure and export for plugin-owned storage. This is the only example that owns
  storage, so it is the only one that can demonstrate the obligation.
- Naming a table and its columns so they survive being interpolated into core's SQL and cannot
  collide with a future core table.

## Read this when

- You need to store something Matomo has no table for, and you are deciding between a log table, a
  plain table of your own, and an extra column on `log_visit`.
- You want your users to be able to segment reports by data your plugin collected.
- You need to write to the database during tracking and a dimension is not the right shape for it.
- You need to know whether the data your plugin stores is reached by GDPR deletion, subject export
  and log retention — or what to do if it is not.
- You want to show your own data in the visits log or the visitor profile.

## Deliberately not here

- **Adding a column to a log table core already owns.** That is a dimension, and `ExampleTracker`
  has it. Reach for that first: a column on `log_visit` needs no table, no DAO and no join
  declaration, and everything in this README comes free with it.
- **Reports, report metadata and visualizations.** `ExampleReport` and `ExampleVisualization`. The
  metric here is archived and readable through the API, but nothing declares a report over it.
- **Pages, menus and widgets.** `ExampleUI`. The one template here belongs to the visits log entry,
  which Live renders; it is not a page of this plugin's own.
- **Settings, diagnostics and admin screens.** `ExampleSettingsPlugin`.
- **The plugin skeleton, DI configuration files and update migrations.** `ExamplePlugin`. This plugin
  has no `config/` directory at all, because it configures no services.
- **Non-log storage.** A table that is configuration rather than log data is not a log table, must
  not declare a join, and gets none of the privacy handling described below.
  `plugins/CustomDimensions/Dao/Configuration.php` is the production example of that shape: one row
  per configured dimension per site, named `custom_dimensions` without the `log_` prefix, and no log
  table class anywhere in the plugin.

## Files in dependency order

| Path | What it does | How Matomo finds it |
| --- | --- | --- |
| `plugin.json` | Name, version and the core version range the plugin works with | Magic filename |
| `ExampleLogTables.php` | Creates and drops the tables, declares the plugin as a tracker plugin, names the archived metric | Magic filename: the plugin class is named after its directory |
| `Dao/CustomUserLog.php` | Owns the user table: the schema, and reading and writing one row per user | Nothing — the plugin's own code calls it |
| `Dao/CustomAccountLog.php` | Owns the account table: the schema, and one row per account | Nothing — the plugin's own code calls it |
| `Tracker/LogTable/CustomUserLog.php` | Declares the user table as log data and how it joins to `log_visit` | Location: any `Tracker/` class extending `Piwik\Tracker\LogTable` |
| `Tracker/LogTable/CustomAccountLog.php` | Declares the account table and how it joins to the user table | Location: same |
| `Columns/UserAttributePlan.php` | Describes the `plan` column and gives it the `userPlan` segment | Location: any `Columns/` class extending `Dimension` |
| `Columns/AccountAttributePaying.php` | Describes `is_paying` and gives it the `accountIsPaying` segment | Location: same |
| `Tracker/UserAttributesRequestProcessor.php` | Writes both tables during tracking | Location: any `Tracker/` class extending `Piwik\Tracker\RequestProcessor` |
| `VisitorDetails.php` | Adds the stored attributes to the Live API payload and renders them into the visits log entry | Magic filename, extending `Live\VisitorDetailsAbstract` |
| `templates/_visitorDetails.twig` | The markup for that visits log entry | Named by `VisitorDetails.php` |
| `RecordBuilders/PayingAccountVisits.php` | Archives one metric aggregated across both tables | Location: only inside a `RecordBuilders/` directory |
| `API.php` | Reads the archived metric back out | Magic filename |
| `lang/en.json` | The English strings. Every other language comes from Weblate | Magic filename |

## Not discovered by location

- **`isTrackerPlugin()` must return `true`.** Putting a `RequestProcessor` in `Tracker/` is not
  enough. The tracker loads only the plugins it considers tracker plugins, and it auto-detects that
  from visit, action or conversion dimensions, from `Tracker.*` event subscriptions and from
  `Request.initAuthenticationObject`. This plugin has none of those, so without the override the
  class in `Tracker/` is never reached and no row is ever written — with no error anywhere.
- **`Db.getTablesInstalled`** is a subscription in `registerEvents()`. Without it your tables are
  missing from the list core uses to drop all tables, to convert the schema to utf8mb4, to detect an
  existing install and to report database usage. There is no diagnostic for the omission.
- **`Metrics.getDefaultMetricTranslations`** is likewise a subscription, and it takes translation
  *keys*: core translates the whole array after posting the event, so translating in the handler
  translates twice. Without the subscription the metric keeps its raw record name wherever core
  consults that map -- sparklines, the single-metric view, and any renderer asked to translate column
  names. The default JSON and XML output never consults it, so the raw name shows there either way.
- **A record name's prefix is a contract, not a convention.** Everything before the first underscore
  in `ExampleLogTables_nb_visits_paying_account` is read back as the plugin name when the metric is
  requested, and an unknown or deactivated plugin there throws. Rename the plugin and miss that
  string and reads fail long after archiving succeeded.
- **A segment name is also a payload key.** Segment value suggestions are read out of the visits log
  by segment name, so the value `VisitorDetails.php` writes under `userPlan` is what lets the
  segment editor suggest values for `userPlan==`. Name the two differently and the editor offers no
  suggestions and says nothing: the visits are found, the column is not, and the empty result is
  indistinguishable from a segment nobody has data for yet. Suggestions also only look a fixed number
  of days back, so a segment over older data suggests nothing either.
- **A segment whose value is not in the visits log needs a `$suggestedValuesCallback`.** `accountIsPaying`
  describes an account rather than a visit, so no payload key can carry it and the route above cannot
  work. The callback answers instead, and it short-circuits before the visits log is queried at all.
  Nothing derives `0` and `1` from a boolean dimension on your behalf — `plugins/CoreHome/Columns/Profilable.php`
  is core's precedent for exactly this shape.
- **The sort order of a visits log block is unallocated.** `VisitorDetails.php` returns a number
  alongside its HTML, every plugin that renders into the visits log returns one, and nothing hands
  them out or warns about a clash: two blocks sharing a number are ordered arbitrarily. Survey what
  core occupies and take a gap.
- **`install()` runs once, the first time the plugin is activated, and only then.** Bumping the
  version in `plugin.json` does not re-run it, and neither does deactivating and reactivating the
  plugin: core skips `install()` for any plugin already listed under `[PluginsInstalled]` in
  `config/config.ini.php`, and deactivation never removes it from that list. An install that
  activated this plugin before it had a working `install()` therefore has no tables and no way to
  get them from the user interface — the plugin name has to come out of `[PluginsInstalled]` by
  hand first. This plugin ships no migration on purpose, because the versioning lesson belongs to
  `plugins/ExamplePlugin/Updates/`; yours needs one the first time you change your own schema,
  because a migration is the only thing that reaches an install that already has the old one.
- **Nothing registers the tracking parameters.** `user_plan`, `user_account` and
  `user_account_is_paying` are read straight out of the request. A plugin invents its own and
  documents them; there is no list to add them to.
- **Your table and column names end up inside core's SQL, unquoted.** Nothing validates them. Core
  builds `SELECT MAX(<id column>)` for every declared log table while purging unused log actions and
  does not quote the column, so declaring an id column named after a reserved word breaks the whole
  site's raw-log purge with a syntax error — on a path no test in your plugin exercises. `group`,
  `order`, `rank` and `key` are the ones that look like natural column names; neither name here is
  reserved, which is the point of picking names that never need checking. Both tables also carry the
  plugin name so they cannot collide with the next core table called `log_custom`. Declaring an id
  column at all enrols
  the table in that query and in the table lock the same purge step takes, so declare one because the
  table has one, not out of habit. The remaining schema rules — matching the width of a column you
  join on, and declaring a default for every column a partial write may omit — are visible in the DAOs
  that own the schema, along with the write path's own sanitise-then-clamp: values that arrive in a
  tracking request are not yours to assume the length of, and the request API hands them to you raw, so
  sanitising is a step you take rather than one you inherit. Order matters — encoding expands a value, so
  clamping first can still overflow the column.
- **Your own SQL has one shape here, and it is core's.** Backtick a table name you interpolate, leave
  column names bare unless they are reserved words, and write the column list and the
  `ON DUPLICATE KEY UPDATE` clause out literally. Building a column list at runtime is for code that
  cannot know its columns — schema migrations, not a DAO for one table — and
  `core/Updater/Migration/Db/Insert.php` is the shape to copy if you ever need it, backticks included.
  This plugin keeps one write method per attribute for that reason: partial writes have no upsert
  syntax in core to copy, so the choice of statement expresses them instead of a generated clause.

## Privacy

**What is stored.** One row per identified user in the user table, holding attributes the site sends
with its tracking requests, and one row per account in the account table. The user id is personal data,
and the attributes are personal data about that user. Nothing is stored for visits without a user id.
The plan dimension sets `$allowAnonymous = false`, so segmenting by it needs a logged-in user;
the paying flag leaves the default, because an account is not a person. Core sets that flag on its five
visitor-identifying dimensions -- user id, visitor id, visit id, IP and fingerprint -- and leaves it
alone elsewhere, including on identifiers like `idorder` that identify a thing rather than a person.
The question the flag answers is not "is this column an identifier" but "does this value describe a
person", and an attribute attached to one is as personal as the id itself.

**How it is erased and exported — and the point of this section.** This plugin subscribes to none
of the `PrivacyManager` events and contains no erasure code. Subject deletion, subject export,
deleted-site cleanup and log retention all reach these tables anyway, because the two classes in
`Tracker/LogTable/` declare them as log data and `PrivacyManager` drives all four off that one
list. **The privacy features are a consequence of using the platform's storage API, not a feature
you implement.**

**The trap is the join declaration.** A table core cannot find a join path for is skipped
*silently* by the export — a deliberate choice, so that one stale third-party plugin cannot break
the feature for everyone — but makes the deletion *throw*. One wrong declaration therefore produces
a subject-access export that is quietly incomplete, which is the one failure mode a compliance
feature must never have. The chain here is two hops long and has no `idvisit` column anywhere in
it, so `tests/Integration/DataSubjectLifecycleTest.php` exists to prove it resolves rather than to
assume it.

**Deleting a subject also deletes rows they share.** Erasing a user removes their row in the user
table and the account row for the account they belonged to, even when other users belong to that account.
Core deletes everything it can reach from the visits being erased, which is the right default for a
compliance feature — it never leaves personal data behind. It does mean a table whose rows are
shared between subjects should not declare a join into the subject chain. Here it is acceptable
because the account row is reference data the tracker rewrites on the next request from any remaining
member of that account.

**A `user_id` join is not stable under anonymisation, and that is the sharpest edge here.** Matomo's
"anonymize previously tracked data" feature rewrites `log_visit.user_id` to a salted hash, and it only
ever touches core's own three log tables — `plugins/PrivacyManager/Dao/LogDataAnonymizer.php` names
them literally and never consults the log table list. A table joined on `user_id` therefore stops
matching the moment that job runs, and its rows become unreachable by all four features above: still
stored, no longer erasable, no longer exportable. The same happens if an administrator unsets the
`user_id` column outright. A table that must survive that has to join on `idvisit` instead. Nothing
warns you either way.

**Rows with no `idsite` are global.** Neither table has one, which is honest for attributes that
describe a person rather than their activity on one site — but it means one user id shares a row
across every site, erasing that subject on one site removes the row while their visits on another
site remain, and a paying flagged by tracking on one site changes another site's archived metric. If
your rows describe activity rather than a person, give them `idsite` and join on `idvisit`.

**Retention reaches the rows but not the estimate.** Purging old raw data deletes visits, and deleting
a visit routes through the very same code as deleting a data subject — `LogDataPurger` calls
`LogDeleter`, which calls the subject deletion in `PrivacyManager\Model\DataSubjects` — so these tables
are reached for the same reason and nothing is left behind. The purge *estimate* shown in the
administration UI, and the table optimisation that follows a purge, work off a different and shorter
list: only log tables that declare a column to join on `idvisit`, which neither of these does. So the
rows go and the site owner is never shown that they will.

**Retention deletes more than it looks like it should, for the same reason.** The purge erases visits
older than the window and everything reachable from them, and what is reachable from a visit here is
the user's whole row, not the part of it that belongs to that visit. A user whose oldest visit ages
out loses their stored attributes even if they visited yesterday, and the tracker restores them only
on their next request carrying those parameters. A table with one row per subject is a table with no
notion of age, so any retention rule applied through the visit join is necessarily coarse. Give rows
`idsite` and a date of their own, and join on `idvisit`, if that matters to you.

**If you store personal data outside the log table API**, none of the above applies and you must
subscribe to `PrivacyManager.deleteDataSubjects`, `PrivacyManager.deleteDataSubjectsForDeletedSites`,
`PrivacyManager.exportDataSubjects` and `PrivacyManager.deleteLogsOlderThan` by hand — only the ones
your storage actually needs. `plugins/BotTracking/BotTracking.php` is the in-core precedent, and it
implements two of the four because its rows are not bound to a visit. Adding any of those
subscriptions to *this* plugin would teach the opposite of the lesson.

**Collect only what the report needs.** `is_paying` arrives in a tracking request, which anyone can
send. It is a segmentation attribute describing an account, never an authorisation signal, and no
access decision anywhere may read it. The user id written here is the one Matomo persisted, not the
raw `uid` parameter, so the site's "disable user id" setting and pseudonymisation are already
applied and this table never holds an identifier that `log_visit` does not.

## Tests that pin this

| File | What it proves |
| --- | --- |
| `tests/System/CustomLogTablesTest.php` | Tracking fills both tables, both segments resolve across the joins, and the archived metric agrees with them |
| `tests/Integration/CustomLogWritePathTest.php` | A request that mentions only some attributes leaves the others alone, values are clamped to their column width, and nothing is stored without a user id |
| `tests/Integration/VisitorDetailsTest.php` | The attributes reach the Live API payload *and* the rendered visits log entry, encoded exactly once |
| `tests/Integration/SegmentAndMetricNamesTest.php` | Both segments suggest values — one from the visits log payload, one from its callback — and the archived metric carries a name rather than its record name |
| `tests/Integration/DataSubjectLifecycleTest.php` | A data subject's rows appear in the export and are gone after the deletion, in both tables, and the retention purge clears both without breaking on their column names |

Name the file rather than filtering on the class name: a filter matches every test class of that
name in the tree, and `VisitorDetailsTest` is a name several plugins have reason to use.

```
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/System/CustomLogTablesTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/CustomLogWritePathTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/VisitorDetailsTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/DataSubjectLifecycleTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/SegmentAndMetricNamesTest.php
```

## Production references

| Concept | Where core does it at scale |
| --- | --- |
| Plugin-owned tables and DAOs | `plugins/CustomDimensions/Dao/`, `plugins/TagManager/Dao/` |
| A log table with one row per event, its own `idsite` and its own date column | `plugins/BotTracking/Dao/BotRequestsDao.php` |
| Aggregating your own table on its own date column instead of through the visit join | `plugins/BotTracking/RecordBuilders/AIChatbotReports.php` |
| A `RequestProcessor` in a small plugin | `plugins/Heartbeat/Tracker/PingRequestProcessor.php` |
| Rendering your own data into the visits log | `plugins/CustomDimensions/VisitorDetails.php` and `plugins/CustomDimensions/templates/_visitorDetails.twig` |
| Suggesting segment values a visits log payload cannot carry | `plugins/CoreHome/Columns/Profilable.php` |
| Subject export and deletion across log tables | `plugins/PrivacyManager/Model/DataSubjects.php` |
| Handling privacy for storage outside the log table API | `plugins/BotTracking/BotTracking.php` |
| Log table declarations for the core tables | `plugins/CoreHome/Tracker/LogTable/` |
