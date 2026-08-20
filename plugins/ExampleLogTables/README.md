# ExampleLogTables

A plugin that stores its data in log tables of its own, from the tracking request that fills them
through to the subject-access export that has to include them.

Core marks custom log tables as not an official API: the interfaces below can change in any release,
and the tests listed at the foot of this page are what tell you when they did.

## Owns

- Log tables belonging to a plugin rather than to core, and the DAOs that create and write them.
- The declaration that makes such a table part of Matomo's log data — what a join declaration buys
  you, and what it commits you to.
- A write path in the tracker for rows that are not a column of an existing log table, and which
  tracking input a plugin may take on trust.
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
| `ExampleLogTables.php` | Creates and drops the tables, declares the plugin as a tracker plugin, contributes the tables to `Db.getTablesInstalled`, names the archived metric | Magic filename: the plugin class is named after its directory |
| `Dao/CustomUserLog.php` | Owns the user table: the schema, and reading and writing one row per user | Nothing — the plugin's own code calls it |
| `Dao/CustomAccountLog.php` | Owns the account table: the schema, and one row per account | Nothing — the plugin's own code calls it |
| `Tracker/LogTable/CustomUserLog.php` | Declares the user table as log data and how it joins to `log_visit` | Location: any `Tracker/` class extending `Piwik\Tracker\LogTable` |
| `Tracker/LogTable/CustomAccountLog.php` | Declares the account table and how it joins to the user table | Location: same |
| `Columns/UserAttributePlan.php` | Describes the `plan` column and gives it the `userPlan` segment | Location: any `Columns/` class extending `Dimension` |
| `Columns/AccountAttributePaying.php` | Describes `is_paying` and gives it the `accountIsPaying` segment | Location: same |
| `Tracker/UserAttributesRequestProcessor.php` | Validates the request, then writes both tables during tracking | Location: any `Tracker/` class extending `Piwik\Tracker\RequestProcessor` |
| `VisitorDetails.php` | Adds the stored attributes to the Live API payload and renders them into the visits log entry | Magic filename, extending `Live\VisitorDetailsAbstract` |
| `templates/_visitorDetails.twig` | The markup for that visits log entry | Named by `VisitorDetails.php` |
| `RecordBuilders/PayingAccountVisits.php` | Archives one metric aggregated across both tables | Location: only inside a `RecordBuilders/` directory |
| `API.php` | Reads the archived metric back out | Magic filename |
| `lang/en.json` | The English strings. Every other language comes from Weblate | Magic filename |

## Not discovered by location

Four registration steps that no amount of reading the files reveals.

- **`isTrackerPlugin()` must return `true`.** Putting a `RequestProcessor` in `Tracker/` is not
  enough. The tracker loads only the plugins it considers tracker plugins, and it auto-detects that
  from visit, action or conversion dimensions, from `Tracker.*` event subscriptions and from
  `Request.initAuthenticationObject`. This plugin has none of those, so without the override the
  class in `Tracker/` is never reached and no row is ever written — with no error anywhere. The
  resulting list is cached, so on an install that has already tracked a request the override takes
  effect on a cache clear rather than on the next request. Returning `true` also means the tracker
  loads this plugin on every tracking request, so return it because you need it, not out of habit.
- **`Db.getTablesInstalled`** is a subscription in `registerEvents()`. Without it your tables are
  missing from the list core uses to drop all tables, to convert the schema to utf8mb4, to detect an
  existing install and to report database usage. There is no diagnostic for the omission.
- **`Metrics.getDefaultMetricTranslations`** is likewise a subscription, and it takes translation
  *keys*: core translates the whole array after posting the event, so translating in the handler
  translates twice. Without the subscription the metric keeps its raw record name wherever core
  consults that map — sparklines, the single-metric view, and any renderer asked to translate column
  names. The default JSON and XML output never consults it, so the raw name shows there either way.
- **Nothing registers the tracking parameters.** `user_plan`, `user_account` and
  `user_account_is_paying` are read straight out of the request. A plugin invents its own and
  documents them; there is no list to add them to. One of the three is only accepted from an
  authenticated request — see *Privacy*.

**`install()` runs once, the first time the plugin is activated, and only then.** Bumping the version
in `plugin.json` does not re-run it, and neither does deactivating and reactivating the plugin: core
skips `install()` for any plugin already listed under `[PluginsInstalled]` in `config/config.ini.php`,
and deactivation never removes it from that list. An install that activated this plugin before it had
a working `install()` therefore has no tables and no way to get them from the user interface — the
plugin name has to come out of `[PluginsInstalled]` by hand first. A migration is the only thing that
reaches an install that already has the old schema, and where that migration lives depends on how your
plugin ships: a third-party plugin puts it in its own `Updates/`, while a plugin bundled with core
cannot, because the release checklist rejects any bundled `Updates/` file named for 3.13.0 or later —
those migrations go in `core/Updates/`. This plugin is bundled, which is why it has none.

**Everything else this plugin has to teach is stated next to the code it governs**, because a second
copy in this file is a second thing to keep true:

| Lesson | Where it lives |
| --- | --- |
| A record name's prefix is read back as the plugin name, and an unknown plugin there throws | `RecordBuilders/PayingAccountVisits.php` |
| A segment name is also the Live payload key, and a mismatch fails silently | `Columns/UserAttributePlan.php`, `VisitorDetails.php` |
| A segment whose value no payload can carry needs a `$suggestedValuesCallback` | `Columns/AccountAttributePaying.php` |
| The sort order of a visits log block is unallocated, and the numbers are per sink | `VisitorDetails.php` |
| Table and column names end up in core's SQL unquoted, and an id column is not free | `Dao/CustomAccountLog.php`, `Tracker/LogTable/CustomUserLog.php` |
| Column widths, defaults for partial writes, and sanitise-before-clamp | `Dao/CustomUserLog.php`, `Tracker/UserAttributesRequestProcessor.php` |
| Which SQL shape to copy, and why one write method per attribute | `Dao/CustomUserLog.php` |
| What the FROM list of an aggregation does and does not generalise to | `RecordBuilders/PayingAccountVisits.php` |

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

**They reach your tables only while the plugin is activated.** The log table list is built from
activated and loaded plugins, so deactivating this plugin takes both tables out of it while every row
stays on disk: subject deletion, subject export, deleted-site cleanup and retention all stop reaching
personal data that is still stored, with no warning anywhere. `uninstall()` drops the tables, so the
dangerous state is deactivated-but-installed — and since deactivation never removes the plugin from
`[PluginsInstalled]`, that state is easy to reach and invisible once you are in it. A plugin holding
subject data should be uninstalled rather than deactivated.

**The trap is the join declaration.** A table core cannot find a join path for is skipped
*silently* by the export — a deliberate choice, so that one stale third-party plugin cannot break
the feature for everyone — but makes the deletion *throw*. One wrong declaration therefore produces
a subject-access export that is quietly incomplete, which is the one failure mode a compliance
feature must never have. The chain here is two hops long and has no `idvisit` column anywhere in
it, so `tests/Integration/DataSubjectLifecycleTest.php` exists to prove it resolves rather than to
assume it.

**One premise, three consequences: these tables are keyed on the subject, not on the visit.** That
choice is honest for an attribute describing a person rather than their activity, and it is also where
every rough edge below comes from.

- *Erasing one subject removes rows other subjects share.* Deleting a user takes their row in the user
  table and the account row for the account they belonged to, even when other users belong to it. Core
  deletes everything it can reach from the visits being erased, which is the right default for a
  compliance feature — it never leaves personal data behind. It does mean a table of genuinely shared
  reference data should not declare a join into the subject chain. Here it is acceptable only because
  the account row is reference data the tracker rewrites on the next request from any remaining member.
- *Rows with no `idsite` are global.* One user id shares one row across every site of the install.
  Erasing that subject on one site removes the row while their visits on another site remain; an
  account flagged by tracking on one site changes another site's archived metric; and — the direction
  easiest to miss — **one site's visits log displays attributes another site collected.** On a shared
  install that is a disclosure between tenants, not just untidy bookkeeping. It is also why the
  account-level flag is only accepted from an authenticated request; see the last paragraph here.
- *Retention is necessarily coarse.* A table with one row per subject has no notion of age, so the
  purge erases a user's whole row as soon as their *oldest* visit ages out, even if they visited
  yesterday. The tracker restores it only on their next request carrying those parameters.

If your rows describe activity rather than a person, give them `idsite` and a date column of their own
and join on `idvisit`. All three consequences go away together.

**A `user_id` join is not stable under anonymisation, and that is the sharpest edge here.** Matomo's
"anonymize previously tracked data" feature rewrites `log_visit.user_id` to a salted hash, and it only
ever touches core's own three log tables — `plugins/PrivacyManager/Dao/LogDataAnonymizer.php` names
them literally and never consults the log table list. A table joined on `user_id` therefore stops
matching the moment that job runs, and its rows become unreachable by all four features above: still
stored, no longer erasable, no longer exportable. The same happens if an administrator unsets the
`user_id` column outright. A table that must survive that has to join on `idvisit` instead. Nothing
warns you either way.

**Retention reaches the rows but not the estimate.** Purging old raw data deletes visits, and deleting
a visit routes through the very same code as deleting a data subject — `LogDataPurger` calls
`LogDeleter`, which calls the subject deletion in `PrivacyManager\Model\DataSubjects` — so these tables
are reached for the same reason and nothing is left behind. The purge *estimate* shown in the
administration UI, and the table optimisation that follows a purge, work off a different and shorter
list: only log tables that declare a column to join on `idvisit`, which neither of these does. So the
rows go and the site owner is never shown that they will.

**If you store personal data outside the log table API**, none of the above applies and you must
subscribe to `PrivacyManager.deleteDataSubjects`, `PrivacyManager.deleteDataSubjectsForDeletedSites`,
`PrivacyManager.exportDataSubjects` and `PrivacyManager.deleteLogsOlderThan` by hand — only the ones
your storage actually needs. `plugins/BotTracking/BotTracking.php` is the in-core precedent, and it
implements two of the four because its rows are not bound to a visit. Adding any of those
subscriptions to *this* plugin would teach the opposite of the lesson.

**A shared row needs an authenticated request; a row about the sender does not.** Anyone can send any
`uid`, so a row keyed on one is exactly as trustworthy as `log_visit.user_id` itself and no gate would
improve it — the plan and the account name are accepted from any request. The paying flag is not: it
lands on a row every member of that account is read through, on every site, so one forged request would
change what other people see and what every site's archived metric says, for subjects who never sent a
request. `processRequestParams()` therefore rejects it unless the request carries a valid `token_auth`,
which is how core gates `cty`, `cip` and `cdt`. It rejects in that phase rather than at the write so
the request is refused before anything is stored.

`is_paying` is a segmentation attribute describing an account and never an authorisation signal; no
access decision anywhere may read it. And the user id written here is the one Matomo persisted, not the
raw `uid` parameter, so the site's "disable user id" setting and pseudonymisation are already applied
and this table never holds an identifier that `log_visit` does not.

## Tests that pin this

| File | What it proves |
| --- | --- |
| `tests/System/CustomLogTablesTest.php` | Both segments resolve across the joins for every API in the tree, and the archived metric reconciles with `UserId.getUsers` — 13 visits on the pro plan plus 33 on the free plan are the 46 the metric reports |
| `tests/Integration/CustomLogWritePathTest.php` | A request that mentions only some attributes leaves the others alone, values are clamped to their column width without leaving a half-cut HTML entity, an unauthenticated request cannot assert the paying flag, and nothing is stored without a user id |
| `tests/Integration/VisitorDetailsTest.php` | The attributes reach the Live API payload *and* the rendered visits log entry, encoded exactly once |
| `tests/Integration/SegmentAndMetricNamesTest.php` | Both segments suggest values — one from the visits log payload, one from its callback — and the archived metric carries a name rather than its record name |
| `tests/Integration/DataSubjectLifecycleTest.php` | Tracking fills both tables; a subject's rows appear in the export and are gone after the deletion; retention and deleted-site cleanup clear both without breaking on their column names; and a row no visit points at survives all of it |

Name the file rather than filtering on the class name: a filter matches by pattern across the whole
suite, so a short class name pulls in unrelated classes and re-runs their fixtures.

```
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/System/CustomLogTablesTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/CustomLogWritePathTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/VisitorDetailsTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/SegmentAndMetricNamesTest.php
ddev matomo:console tests:run --file=plugins/ExampleLogTables/tests/Integration/DataSubjectLifecycleTest.php
```

## Production references

| Concept | Where core does it at scale |
| --- | --- |
| Plugin-owned tables and DAOs | `plugins/CustomDimensions/Dao/`, `plugins/TagManager/Dao/` |
| A plugin-owned table with one row per event, its own `idsite` and its own date column — deliberately **not** declared as a log table, which is why the same plugin subscribes to the `PrivacyManager` events | `plugins/BotTracking/Dao/BotRequestsDao.php` |
| Aggregating your own table on its own date column instead of through the visit join | `plugins/BotTracking/RecordBuilders/AIChatbotReports.php` |
| A `RequestProcessor` in a small plugin | `plugins/Heartbeat/Tracker/PingRequestProcessor.php` |
| Rendering your own data into the visits log | `plugins/CustomDimensions/VisitorDetails.php` and `plugins/CustomDimensions/templates/_visitorDetails.twig` |
| Suggesting segment values a visits log payload cannot carry | `plugins/CoreHome/Columns/Profilable.php` |
| Subject export and deletion across log tables | `plugins/PrivacyManager/Model/DataSubjects.php` |
| Handling privacy for storage outside the log table API | `plugins/BotTracking/BotTracking.php` |
| Log table declarations for the core tables, including ones that do have `idvisit` | `plugins/CoreHome/Tracker/LogTable/` |
