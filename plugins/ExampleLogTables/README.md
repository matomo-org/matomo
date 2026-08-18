# ExampleLogTables

A plugin that stores its data in log tables of its own, from the tracking request that fills them
through to the subject-access export that has to include them.

## Owns

- Log tables belonging to a plugin rather than to core, and the DAOs that create and write them.
- The declaration that makes such a table part of Matomo's log data — what a join declaration buys
  you, and what it commits you to.
- A write path in the tracker for rows that are not a column of an existing log table.
- Segments over your own tables, including one that resolves across two joins.
- Aggregating your own tables into an archived metric.
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
- **Pages, menus and widgets.** `ExampleUI`.
- **Settings, diagnostics and admin screens.** `ExampleSettingsPlugin`.
- **The plugin skeleton, DI configuration files and update migrations.** `ExamplePlugin`.
- **Non-log storage.** A table that is configuration rather than log data — one row per site, say —
  is not a log table, must not declare a join, and gets none of the privacy handling described
  below. `CustomDimensions/Dao/` is the production example of that shape.

## Files in dependency order

| Path | What it does | How Matomo finds it |
| --- | --- | --- |
| `plugin.json` | Name, version and the core version range the plugin works with | Magic filename |
| `ExampleLogTables.php` | Creates and drops the tables, declares the plugin as a tracker plugin, names the archived metric | Magic filename: `<Plugin>/<Plugin>.php` |
| `Dao/CustomUserLog.php` | Owns `log_custom`: the schema, and reading and writing one row per user | Nothing — the plugin's own code calls it |
| `Dao/CustomGroupLog.php` | Owns `log_group`: the schema, and one row per group | Nothing — the plugin's own code calls it |
| `Tracker/LogTable/CustomUserLog.php` | Declares `log_custom` as log data and how it joins to `log_visit` | Location: any `Tracker/` class extending `Piwik\Tracker\LogTable` |
| `Tracker/LogTable/CustomGroupLog.php` | Declares `log_group` and how it joins to `log_custom` | Location: same |
| `Columns/UserAttributeGender.php` | Describes the `gender` column and gives it the `attrgender` segment | Location: any `Columns/` class extending `Dimension` |
| `Columns/GroupAttributeAdmin.php` | Describes `is_admin` and gives it the `isadmin` segment | Location: same |
| `Tracker/UserAttributesRequestProcessor.php` | Writes both tables during tracking | Location: any `Tracker/` class extending `Piwik\Tracker\RequestProcessor` |
| `VisitorDetails.php` | Adds the stored attributes to the visits log and visitor profile | Magic filename, extending `Live\VisitorDetailsAbstract` |
| `RecordBuilders/AdminGroupVisits.php` | Archives one metric aggregated across both tables | Location: only inside a `RecordBuilders/` directory |
| `API.php` | Reads the archived metric back out | Magic filename |
| `lang/en.json` | The English strings. Every other language comes from Weblate | Magic filename |

## Not discovered by location

- **`isTrackerPlugin()` must return `true`.** Putting a `RequestProcessor` in `Tracker/` is not
  enough. The tracker loads only the plugins it considers tracker plugins, and it auto-detects that
  from visit, action or conversion dimensions and from `Tracker.*` event subscriptions. This plugin
  has neither, so without the override the class in `Tracker/` is never reached and no row is ever
  written — with no error anywhere.
- **`Db.getTablesInstalled`** is a subscription in `registerEvents()`. Without it Matomo does not
  know the tables belong to it, and the system check reports them as unexpected.
- **`Metrics.getDefaultMetricTranslations`** is likewise a subscription. Without it the archived
  metric renders under its raw record name.
- **Nothing registers the tracking parameters.** `user_gender`, `user_group` and
  `user_group_is_admin` are read straight out of the request. A plugin invents its own and
  documents them; there is no list to add them to.

## Privacy

**What is stored.** One row per identified user in `log_custom`, holding attributes the site sends
with its tracking requests, and one row per group in `log_group`. The user id is personal data, and
the attributes are personal data about that user. Nothing is stored for visits without a user id.

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

**Deleting a subject also deletes rows they share.** Erasing a user removes their `log_custom` row
and the `log_group` row for the group they belonged to, even when other users belong to that group.
Core deletes everything it can reach from the visits being erased, which is the right default for a
compliance feature — it never leaves personal data behind. It does mean a table whose rows are
shared between subjects should not declare a join into the subject chain. Here it is acceptable
because the group row is reference data the tracker rewrites on the next request from any remaining
member of that group.

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
| `tests/Integration/DataSubjectLifecycleTest.php` | A data subject's rows appear in the export and are gone after the deletion, in both tables |

```
ddev exec ./console tests:run --options="--filter=CustomLogTablesTest"
ddev exec ./console tests:run --options="--filter=DataSubjectLifecycleTest"
```

## Production references

| Concept | Where core does it at scale |
| --- | --- |
| Plugin-owned tables and DAOs | `plugins/CustomDimensions/Dao/`, `plugins/TagManager/Dao/` |
| A `RequestProcessor` in a small plugin | `plugins/Heartbeat/Tracker/PingRequestProcessor.php` |
| Subject export and deletion across log tables | `plugins/PrivacyManager/Model/DataSubjects.php` |
| Handling privacy for storage outside the log table API | `plugins/BotTracking/BotTracking.php` |
| Log table declarations for the core tables | `plugins/CoreHome/Tracker/LogTable/` |
