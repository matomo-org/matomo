# ClickHouse query quirks (DEV-20678)

Every adjustment this branch needed to make Matomo's existing SQL work when the log
tables are read from ClickHouse instead of MySQL.

It is organised **by where the query comes from**, because that is how you meet these
problems: a report renders wrong, and you work back to the query behind it. Each entry
says what the query does, what went wrong, and what was changed.

The recurring theme is worth stating up front: **most of these are not ClickHouse being
awkward, they are MySQL being permissive.** MySQL silently coerces types, tolerates
comparisons against `''`, matches strings case insensitively, and resolves ambiguous
names in a forgiving order. Matomo's SQL grew up leaning on all of that. ClickHouse
either refuses outright (easy to find) or quietly matches nothing (dangerous). Ten of
the fifteen entries below are of that kind.

---

## 1. Archiving — `LogAggregator`

### 1.1 `WITH ROLLUP` grouping keys must be nullable in the `GROUP BY`, not just the `SELECT`

**Where:** every report built through `LogAggregator::queryVisitsByDimension()` and
friends that asks for rollup totals, most visibly `Referrers\RecordBuilders\AIReferrers`.

**Symptom:** report values came out **exactly doubled** — the AI Assistants report showed
24/16/8 visits where MySQL showed 12/8/4. No error anywhere.

**Why:** MySQL marks the extra rows `WITH ROLLUP` produces by setting the rolled-up
grouping key to `NULL`. ClickHouse instead fills them with the key type's *default*, so a
`String` key yields `''` and only a `Nullable` key yields `NULL`. Both `RankingQuery`'s
rollup `CASE` branches and the window `counterRollup` expression recognise a rollup row
solely by its key being `NULL`, so the subtotal rows were read as detail rows and added
to the detail they were summarising.

Verified directly against ClickHouse 25.8:

| Shape | Rollup row value |
| --- | --- |
| `GROUP BY x` + `SELECT toNullable(x)` | `''` — wrong |
| `GROUP BY toNullable(x)` + `SELECT toNullable(x)` | `NULL` — correct |
| `any(toNullable(x))` | a real value — worst case |

**Change:** `ClickhouseDialectTranslator::makeRollupKeysNullable()` wraps the grouping key
in `toNullable()` in the `GROUP BY` as well as the `SELECT`. Wrapping only the `SELECT`
also left the two textually different, so the later `GROUP BY` completion pass no longer
recognised the item as a grouping key and wrapped it in `any()` — an aggregate returns a
real value for the rollup group and destroys the marker just as effectively. The `GROUP BY`
entry is only wrapped when it repeats the expression; when it names the `SELECT` alias the
alias already resolves to the wrapped expression.

### 1.2 Ranking queries need window functions

**Where:** `RankingQuery`, used by every report with a row limit.

**Why:** the mainline implementation ranks rows with MySQL user variables (`@counter`),
which ClickHouse does not have.

**Change:** a window-function path was grafted onto the mainline implementation, gated on
`SchemaInterface::supportsWindowFunctions() || Db::hasAnalyticsConfigured()`. MySQL and
MariaDB return `false` deliberately: they support window functions, but switching them
over touches every archiving job and is a separate decision. Counters are a running
`SUM(CASE WHEN countable THEN 1 ELSE 0 END) OVER (...)`, **not** `ROW_NUMBER()`, because
skipped rows must not advance the counter. Rollup switches are qualified with the
`withCounter.` alias, because ClickHouse resolves a bare name to the `SELECT` alias where
MySQL resolves it to the source column.

### 1.3 The segment temp-table optimisation cannot be used

**Where:** `LogAggregator::isSegmentCacheEnabled()`.

**Why:** the optimisation creates a MySQL temporary table and joins against it. The table
does not exist in ClickHouse.

**Change:** the cache is disabled whenever an analytics database is configured, so segment
conditions are inlined as `WHERE` clauses instead.

### 1.4 Aggregations spill to disk

**Where:** the adapter's per-query settings, affecting all archiving.

**Change:** `max_bytes_before_external_sort` and `max_bytes_before_external_group_by` are
both set to 256 MiB, along with `max_threads = 2` and `join_algorithm = grace_hash`, so a
wide aggregation on a small container spills rather than dying with
`MEMORY_LIMIT_EXCEEDED`.

> Do not read the sort setting as harmless and the group-by setting as suspicious: an
> earlier measurement appearing to show the group-by threshold making queries 5x slower
> was contaminated by other load on the same machine. A clean run showed no difference.

---

## 2. The visits log and visitor details — `Live`

All of these were invisible until M4 routed every Live read through the adapter. The
previous POC served only the visits log, with its own conversion rules **and a silent
MySQL fallback**, so a query it could not run was quietly answered by MySQL — or returned
nothing at all.

### 2.1 `STRAIGHT_JOIN`

**Where:** `Ecommerce\VisitorDetails::getSqlEcommerceConversionsLifeTimeMetricsForIdGoal()`.

**Symptom:** `Syntax error … STRAIGHT_JOIN` — 24 occurrences in one UI job.

**Why:** MySQL's hint for forcing join order. ClickHouse has no equivalent and rejects the
keyword.

**Change:** the translator maps `STRAIGHT_JOIN` to `INNER JOIN`. The join is the only part
that carries over; ClickHouse picks its own order.

### 2.2 `idvisit IN ('')` from an empty visit set

**Where:** `Actions\VisitorDetails::queryActionsForVisits()` and
`Ecommerce\VisitorDetails::queryEcommerceConversionsForVisits()`. `Goals\VisitorDetails`
already guarded this.

**Symptom:** `Attempt to read after eof: while converting '' to UInt64`. Reached whenever a
visitor log has no visits — the purged visitor log does exactly that.

**Why:** the SQL builds `IN ('" . implode("','", $idVisits) . "')`, which with an empty
array becomes `IN ('')`. MySQL coerces `''` to `0` and matches nothing; ClickHouse fails
parsing `''` as a visit id.

**Change:** both methods return early on an empty set, as Goals already did. Worth noting
this was a latent waste on MySQL too — it ran a query that could not match anything.

### 2.3 An integer bound against a `varchar` column

**Where:** `Ecommerce\VisitorDetails::queryEcommerceItemsForOrder()`.

**Symptom:** `There is no supertype for types String, UInt8 … while executing function
equals on arguments __table1.idorder String … 0_UInt8`.

**Why:** `log_conversion_item.idorder` is `varchar(100)`, and the abandoned-cart marker
`GoalManager::ITEM_IDORDER_ABANDONED_CART` is the integer `0`. MySQL coerces silently;
ClickHouse refuses to compare a `String` with a number.

**Change:** the caller binds `(string) $idOrder`, the string the column actually holds.

### 2.4 A bare column name in `WHERE` resolving to an aggregate alias

**Where:** `Live\Model::queryAdjacentVisitorId()`.

**Symptom:** `Aggregate function MAX(log_visit.visit_last_action_time) AS
visit_last_action_time is found in WHERE in query. (ILLEGAL_AGGREGATION)`.

**Why:** the `SELECT` aliases `MAX(visit_last_action_time)` to that same name, and the
`WHERE` then filtered on the bare name. MySQL resolves the bare name to the *column*;
ClickHouse resolves it to the *alias*, producing an aggregate in `WHERE`.

**Change:** the `WHERE` qualifies the column as `log_visit.visit_last_action_time`. Equally
correct on MySQL, and clearer.

### 2.5 A column selected twice ended up double-aliased

**Where:** the visits log query, which selects
`log_link_visit_action.idlink_va` both bare and again `AS pageId`.

**Symptom:** ``… AS `idlink_va` AS pageId`` — a plain syntax error.

**Why:** the translator aliases bare qualified columns to their short name so ClickHouse
does not invent a qualified output name. It did so by replacing the reference text
everywhere in the `SELECT` clause, which also appended the alias to the already-aliased
second copy.

**Change:** `aliasQualifiedSelectColumns()` rebuilds the `SELECT` list item by item, so
each item is considered on its own. The same `str_replace`-across-duplicates hazard still
exists in two other passes (`makeRollupKeysNullable`, and the `fullItem` replace in the
`GROUP BY` completion) — they have not bitten yet, but convert them the same way if they do.

### 2.6 Visitor details for conversions join a configuration table

**Where:** `Goals\VisitorDetails::queryGoalConversionsForVisits()`.

**Symptom:** `Unknown table expression identifier 'goal'`.

**Why:** the query joins the log tables to `goal`, which is configuration, not log data,
and so has no copy in ClickHouse. Of the 21 `VisitorDetails` implementations this is the
only one that reads a table outside the log set.

**Change:** that one query reads from MySQL, which holds the configuration alongside the
log tables.

> **Carry this forward:** it assumes the MySQL log tables are still present. If they are
> ever pruned in favour of the analytics database, this query has to be split into a
> conversions read plus a goal-name lookup — preserving the `LEFT JOIN … AND goal.deleted
> = 0` semantics (a deleted goal yields `goalName`/`goalId` of `NULL`) and the current
> output field order, which system test expectations depend on.

---

## 3. Segments

### 3.1 Country codes only matched by accident

**Where:** any report with a `countryCode` segment. Caught by the ecommerce log with
`segment=countryCode==CN`.

**Symptom:** the report rendered *"There is no data for this report."* with no error at
all. 45 queries in one UI job filtered `location_country = 'CN'`.

**Why:** country codes are stored **lower case**, and the segment passed the user's text
straight into the comparison. MySQL's collation matched `'CN'` against `'cn'` for free, so
nothing ever depended on normalising it — and an upper case code is rare enough in tests
that this went unnoticed.

**Change:** `UserCountry\Columns\Country::getSqlFilterValue()` lower-cases the value, so
matching no longer depends on the connection's collation. The `countryName` segment
alongside it already resolved to a lower case code. Segment **hashes** come from the
definition string rather than the SQL, so stored archives are unaffected.

### 3.2 `LIKE` is case sensitive on ClickHouse

**Where:** every `=@` (contains), `=^` (starts with) and `=$` (ends with) segment — the
bulk of free-text segment matching.

**Why:** MySQL matches `LIKE` case insensitively under its default collation. ClickHouse's
`LIKE` is case sensitive, so the same segment matched fewer rows.

**Change:** the translator emits `ILIKE`, ClickHouse's case-insensitive equivalent, in the
same pass that wraps non-string arguments in `toString()` (MySQL allows `LIKE` on numeric
columns; the GDPR data subject search does `idvisit LIKE '10%'`). The pattern anchors on an
identifier followed by the operator, so the word "like" inside a string literal is left
alone. On the hex-encoded binary columns `ILIKE` is marginally more permissive than MySQL's
case sensitive binary comparison, but those values are always written lower case.

### 3.3 Still open: equality is case sensitive too

`==` and `!=` on string columns remain case sensitive on ClickHouse. Reproducing MySQL
needs `lowerUTF8()` on both sides, which needs to know the column is a string — so it wants
type introspection from `system.columns`. It also has a real cost: wrapping a filtered
column in a function defeats ClickHouse's sparse-index pruning, which matters most for
columns in the table's sort key. Deliberately not done yet.

---

## 4. The archiving decision stays on MySQL

**Where:** `RawLogDao::hasSiteVisitsBetweenTimeframe()`, called from the archiving loader.

**Change:** deliberately **not** routed to the analytics database, unlike the aggregation
queries around it.

**Why:** this check decides whether a period is archived at all. The analytics database is
a replica that can lag behind the tracker's writes, so a "no visits" answer from a copy
that has not caught up would make Matomo store an *empty archive* for a period that does
have visits — losing that data silently, with nothing in the logs. MySQL is the write path
and cannot be stale, and the check is a single indexed existence lookup.

Symptom while it was routed: the SegmentEditor tests lost the *"configuration prevents a
rearchiving for this period"* notice, because Matomo took the nothing-to-archive path.

---

## 5. Schema and the data copy

- **Engine:** `ReplacingMergeTree(_version, is_deleted)` ordered by the MySQL primary key,
  so the copies are compatible with the CDC sink.
- **Nullability is mirrored from MySQL exactly**, by introspection rather than a hand-written
  list. Getting this wrong breaks every `IS NULL` filter — an earlier attempt had all Actions
  reports reading zero.
- **Binary columns** (`idvisitor`, `config_id`, `location_ip`) are stored hex-encoded as
  `String`; the adapter hexes matching binds on the way in and reverses it on the way out.
  The bind heuristic is width-and-printability based, so a false positive is possible.
- **Old-schema fixtures**: the sort-key and binary-column lists are intersected with the
  columns that actually exist, because the update-flow tests load ancient dumps whose
  `log_conversion` predates `buster` and whose `log_visit` predates `idvisitor`.
- `log_bot_request` is a sixth log table, created only when the BotTracking plugin has run.

---

## 6. Adapter and transport

- **`final = 1`** is set as a query setting rather than injecting `FINAL` after each table
  name: the copies are `ReplacingMergeTree`, so reads must collapse row versions.
- **`session_timezone = 'UTC'`** is pinned so a server in another timezone cannot shift
  `toDate()`/`toHour()` results.
- **Row post-processing** strips qualified names, reverses the hex encoding and trims
  microseconds from `TIME`-like values, so callers see what `Db::fetchAll()` would return on
  MySQL.
- **Errors carry context.** Selects include the translated SQL and the caller chain, which is
  how nearly every problem in this document was found. Statements and the log-table copy were
  wrapped later, after 26 UI failures surfaced only as an uncaught `DatabaseException` with no
  indication of what had run.
- **Known accepted drift:** `DECIMAL` trailing zeros — MySQL returns `'0.0620'` where
  ClickHouse returns `0.062`. Visible only in ClickHouse-routed system tests run locally, and
  rounded away in the UI.
- **Per-query connection overhead is not fixed.** The client library opens a new TCP
  connection for every query: its `keepAlive()` sets the `Connection: Keep-Alive` header and
  *also* sets curl's `CURLOPT_FORBID_REUSE`, which closes the connection after the transfer.
  Measured at 5.74 ms per query versus 3.38 ms with a reused handle — about 41%, or roughly a
  minute across the ~25,000 queries a UI job issues. It cannot be overridden from our side:
  `keepAlive()` runs after client-level curl options are applied, and the request builder is
  private. Fixing it means either an upstream change or a small transport of our own.

---

## 7. Test infrastructure

- **Reload before saving the testing environment.** Fixture `setUp()` implementations persist
  their own `TestingEnvironmentVariables` instance — `SqlDump` rewrites `configOverride` with
  the dump's table prefix — so saving an instance created before `setUp()` ran silently
  reverted them. This is what made the whole CoreUpdater update-flow suite fail 0/9.
- **`CLICKHOUSE_*` environment variables apply in test mode only.** The update-flow specs
  install a second, plain Matomo and serve it from the same php-fpm process, which inherits
  the CI job environment but has no ClickHouse copy of its database. Honouring the variables
  there routed that install to ClickHouse and failed every log query with
  `UNKNOWN_DATABASE`.
- **`sync_mysql_host` must be propagated too**, not just host and port. A web request that has
  to re-copy the log tables runs the copy from inside the ClickHouse server, where
  `127.0.0.1` is that container rather than the machine running MySQL. Only reproducible in
  CI: locally the ClickHouse service reaches MySQL on the same hostname the application uses.
- **The freshness fingerprint is throttled** to once per 250 ms per process. Fingerprinting
  MySQL costs about 2 ms for the `information_schema` lookup plus a `CHECKSUM TABLE` scan of
  every log table, and one report page issues thousands of queries, so doing it per query cost
  more than the queries. Cross-process freshness is unaffected: a new process always
  fingerprints on its first query.
- **Expected screenshots must come from CI**, never regenerated locally. A local browser is
  not the Chrome for Testing build CI uses; regenerating
  `UIIntegrationTest_admin_diagnostics_configfile.png` locally produced a 6.4 million pixel
  diff of pure rendering difference. Take the `processed` image from the build artifacts
  instead.
