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
  metric here is archived and readable through the API but has no `Reports/` class.
- **Pages, menus and widgets.** `ExampleUI`. The one template here belongs to the visits log entry,
  which Live renders; it is not a page of this plugin's own.
- **Settings, diagnostics and admin screens.** `ExampleSettingsPlugin`.
- **The plugin skeleton, DI configuration files and update migrations.** `ExamplePlugin`. This plugin
  has no `config/` directory at all, because it configures no services.
- **Non-log storage.** A table that is configuration rather than log data — one row per site, say —
  is not a log table, must not declare a join, and gets none of the privacy handling described
  below. `plugins/CustomDimensions/Dao/Configuration.php` is the production example of that shape.

## Files in dependency order

| Path | What it does | How Matomo finds it |
| --- | --- | --- |
| `plugin.json` | Name, version and the core version range the plugin works with | Magic filename |
| `ExampleLogTables.php` | Creates and drops the tables, declares the plugin as a tracker plugin, names the archived metric | Magic filename: `<Plugin>/<Plugin>.php` |
| `Dao/CustomUserLog.php` | Owns the user table: the schema, and reading and writing one row per user | Nothing — the plugin's own code calls it |
| `Dao/CustomGroupLog.php` | Owns the group table: the schema, and one row per group | Nothing — the plugin's own code calls it |
| `Tracker/LogTable/CustomUserLog.php` | Declares the user table as log data and how it joins to `log_visit` | Location: any `Tracker/` class extending `Piwik\Tracker\LogTable` |
| `Tracker/LogTable/CustomGroupLog.php` | Declares the group table and how it joins to the user table | Location: same |
| `Columns/UserAttributeGender.php` | Describes the `gender` column and gives it the `userGender` segment | Location: any `Columns/` class extending `Dimension` |
| `Columns/GroupAttributeAdmin.php` | Describes `is_admin` and gives it the `groupIsAdmin` segment | Location: same |
| `Tracker/UserAttributesRequestProcessor.php` | Writes both tables during tracking | Location: any `Tracker/` class extending `Piwik\Tracker\RequestProcessor` |
| `VisitorDetails.php` | Adds the stored attributes to the Live API payload and renders them into the visits log entry | Magic filename, extending `Live\VisitorDetailsAbstract` |
| `templates/_visitorDetails.twig` | The markup for that visits log entry | Named by `VisitorDetails.php` |
| `RecordBuilders/AdminGroupVisits.php` | Archives one metric aggregated across both tables | Location: only inside a `RecordBuilders/` directory |
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
  translates twice. Without the subscription the archived metric renders under its raw record name.
- **A record name's prefix is a contract, not a convention.** Everything before the first underscore
  in `ExampleLogTables_nb_visits_admin_group` is read back as the plugin name when the metric is
  requested, and an unknown or deactivated plugin there throws. Rename the plugin and miss that
  string and reads fail long after archiving succeeded.
- **A segment name is also a payload key.** Segment value suggestions are read out of the visits log
  by segment name, so the value `VisitorDetails.php` writes under `userGender` is what lets the
  segment editor suggest values for `userGender==`. Name them differently and the editor reports that
  there is nothing to suggest.
- **`install()` runs on activation, once.** Bumping the version in `plugin.json` does not re-run it.
  This plugin ships no `Updates/` migration on purpose — the versioning lesson belongs to
  `ExamplePlugin`, and an install that activated this plugin before it had a working `install()` is
  fixed by deactivating and reactivating it. Your plugin will need one the first time you change your
  own table's schema, because nothing else will reach an install that already has the old one.
- **Nothing registers the tracking parameters.** `user_gender`, `user_group` and
  `user_group_is_admin` are read straight out of the request. A plugin invents its own and
  documents them; there is no list to add them to.

## Table and column naming

Both are load-bearing in ways that only show up later.

- **Prefix the table with your plugin name**, as `log_examplelogtables_user` does and as
  `plugins/BotTracking/Dao/BotRequestsDao.php` does with `log_bot_request`. A generic name such as
  `log_custom` collides with the next core table of that name. Keep the `log_` prefix so a human
  reading the schema can see it holds log data.
- **Do not name a column after a reserved word.** Your own queries can quote it; core's do not always
  — it builds `SELECT MAX(<id column>)` unquoted for every declared log table while purging raw data,
  so a table whose id column is called `group` breaks the whole site's purge with a syntax error. The
  column here is `group_name` for that reason.
- **Match core's width on a column you join on.** `user_id` is `VARCHAR(200)` because that is what
  `log_visit.user_id` is. Matomo connects with a non-strict `sql_mode`, so a shorter column truncates
  a long user id silently, and the truncated value no longer matches — leaving a row nothing can ever
  join to again.
- **Declare defaults for columns a partial write may omit.** A request that carries one attribute
  writes one column, and the others fall back to their declared default.

## Privacy

**What is stored.** One row per identified user in the user table, holding attributes the site sends
with its tracking requests, and one row per group in the group table. The user id is personal data,
and the attributes are personal data about that user. Nothing is stored for visits without a user id.
The gender dimension sets `$allowAnonymous = false`, as core does on every dimension over an
identifier, so anonymous visitors cannot segment by it; the group flag does not, because a group is
not a person.

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
table and the group row for the group they belonged to, even when other users belong to that group.
Core deletes everything it can reach from the visits being erased, which is the right default for a
compliance feature — it never leaves personal data behind. It does mean a table whose rows are
shared between subjects should not declare a join into the subject chain. Here it is acceptable
because the group row is reference data the tracker rewrites on the next request from any remaining
member of that group.

**A `user_id` join is not stable under anonymisation, and that is the sharpest edge here.** Matomo's
"anonymize previously tracked data" feature rewrites `log_visit.user_id` to a hash, and it only ever
touches core's own three log tables — `plugins/PrivacyManager/Model/LogDataAnonymizations.php` never
consults the log table list. A table joined on `user_id` therefore stops matching the moment that job
runs, and its rows become unreachable by all four features above: still stored, no longer erasable,
no longer exportable. The same happens if an administrator unsets the `user_id` column outright. A
table that must survive that has to join on `idvisit` instead. Nothing warns you either way.

**Rows with no `idsite` are global.** Neither table has one, which is honest for attributes that
describe a person rather than their activity on one site — but it means one user id shares a row
across every site, erasing that subject on one site removes the row while their visits on another
site remain, and a group flagged by tracking on one site changes another site's archived metric. If
your rows describe activity rather than a person, give them `idsite` and join on `idvisit`.

**Retention reaches the rows but not the estimate.** Purging old raw data deletes from these tables
through the same code as subject deletion, so nothing is left behind. The purge estimate shown in the
administration UI, and the table optimisation that follows a purge, both skip tables that do not
declare an `idvisit` column — so these two never appear there.

**If you store personal data outside the log table API**, none of the above applies and you must
subscribe to `PrivacyManager.deleteDataSubjects`, `PrivacyManager.deleteDataSubjectsForDeletedSites`,
`PrivacyManager.exportDataSubjects` and `PrivacyManager.deleteLogsOlderThan` by hand — only the ones
your storage actually needs. `plugins/BotTracking/BotTracking.php` is the in-core precedent, and it
implements two of the four because its rows are not bound to a visit. Adding any of those
subscriptions to *this* plugin would teach the opposite of the lesson.

**Collect only what the report needs.** `is_admin` arrives in a tracking request, which anyone can
send. It is a segmentation attribute describing a group, never an authorisation signal, and no
access decision anywhere may read it. The user id written here is the one Matomo persisted, not the
raw `uid` parameter, so the site's "disable user id" setting and pseudonymisation are already
applied and this table never holds an identifier that `log_visit` does not.

## Tests that pin this

| File | What it proves |
| --- | --- |
| `tests/System/CustomLogTablesTest.php` | Tracking fills both tables, both segments resolve across the joins, and the archived metric agrees with them |
| `tests/Integration/CustomLogWritePathTest.php` | A request that mentions only some attributes leaves the others alone, and nothing is stored without a user id |
| `tests/Integration/VisitorDetailsTest.php` | The attributes reach the Live API payload *and* the rendered visits log entry |
| `tests/Integration/DataSubjectLifecycleTest.php` | A data subject's rows appear in the export and are gone after the deletion, in both tables |

```
ddev exec ./console tests:run --options="--filter=CustomLogTablesTest"
ddev exec ./console tests:run --options="--filter=CustomLogWritePathTest"
ddev exec ./console tests:run --options="--filter=VisitorDetailsTest"
ddev exec ./console tests:run --options="--filter=DataSubjectLifecycleTest"
```

## Production references

| Concept | Where core does it at scale |
| --- | --- |
| Plugin-owned tables and DAOs | `plugins/CustomDimensions/Dao/`, `plugins/TagManager/Dao/` |
| A log table with one row per event, `idsite` and indexes | `plugins/BotTracking/Dao/BotRequestsDao.php` |
| Aggregating your own table without joining `log_visit` | `plugins/BotTracking/RecordBuilders/AIChatbotReports.php` |
| A `RequestProcessor` in a small plugin | `plugins/Heartbeat/Tracker/PingRequestProcessor.php` |
| Rendering your own data into the visits log | `plugins/CustomDimensions/VisitorDetails.php` |
| Subject export and deletion across log tables | `plugins/PrivacyManager/Model/DataSubjects.php` |
| Handling privacy for storage outside the log table API | `plugins/BotTracking/BotTracking.php` |
| Log table declarations for the core tables | `plugins/CoreHome/Tracker/LogTable/` |
