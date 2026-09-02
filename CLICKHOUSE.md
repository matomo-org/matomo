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

### 3.2a `ILIKE` is correct and unindexable at the same time (DEV-20751)

**Where:** `log_action.name`, which is every action-scope segment component — the expensive
half of the compound segments the benchmark measures.

**Why:** `ngrambf_v1` is **never consulted for `ILIKE`**, only for `LIKE`. So §3.2's fix,
which is the right answer for correctness, is at the same time a silent performance cliff:
it turns a skip-index lookup into a scan of `log_action` (242 million rows on the POC
corpus). This is the one place where the correctness fix and the speed fix pull in opposite
directions, and it has to be resolved deliberately rather than by picking one.

**Change:** get both properties instead of trading them. The copy carries a
`name_lower String MATERIALIZED lower(name)` column with an `ngrambf_v1(4, 262144, 3, 0)`
index on it, and the translator points segment `LIKE`s at that column while lowercasing the
needle — literals during translation, bound values in
`lowercaseNeedlesForIndexedColumns()` once the binds have names to match them by. The result
is case-insensitive *and* index-eligible.

Two details that matter:

- **`strtolower()`, never `mb_strtolower()`.** The stored column is ClickHouse's `lower()`,
  which is ASCII-only. Lowering the needle further than the column was lowered would stop it
  matching.
- **The needle is lowered in PHP, not with `lower()` in the SQL.** Index usage then never
  depends on the planner constant-folding a function call.

The column is created by `ClickhouseLogTableSync` rather than only by
`bench/clickhouse-indices.sql`, so it exists wherever the adapter runs — a ClickPipes Resync
drops the hand-applied copy silently, and the rewrite must not depend on someone remembering
to re-apply it.

`n = 4` in the index means needles shorter than four characters cannot use it. They still
match; they are just not accelerated.

### 3.3 Still open: a numeric column compared with free text

**Where:** any segment on a numeric column given a non-numeric value. The GDPR tools spec
types `userfoobar` into the segment value box, producing `log_visit.idvisit = 'userfoobar'`.

**Symptom:** `Cannot convert string 'userfoobar' to type UInt64 … while executing function
equals on arguments __table1.idvisit UInt64 … 'userfoobar'_String (TYPE_MISMATCH)`. The page
renders an error box instead of the "no visits found" message, so the screenshot is *taller*
than expected.

**Why:** MySQL coerces the string operand to a number — `'userfoobar'` becomes `0`, which
matches nothing. ClickHouse refuses to compare a number with a string at all.

**Not fixed yet, and the obvious fix does not work.** Coercing the value in the dimension's
`getSqlFilterValue()` (as `countryCode` does) would also rewrite `'10%'` for the
starts-with case in the very next spec, because that hook does not receive the match type.
Doing it properly needs either match-type aware coercion in the segment layer or column type
introspection in the adapter — the latter arrives with the migration command, which reads
`system.columns` anyway.

### 3.4 Still open: equality is case sensitive too

`==` and `!=` on string columns remain case sensitive on ClickHouse. Reproducing MySQL
needs `lowerUTF8()` on both sides, which needs to know the column is a string — so it wants
type introspection from `system.columns`. It also has a real cost: wrapping a filtered
column in a function defeats ClickHouse's sparse-index pruning, which matters most for
columns in the table's sort key. Deliberately not done yet.

---

### 3.5 Still open: `Unknown table expression identifier 'log_visit'`

**Where:** Goals archiving with a segment, seen intermittently in the SegmentEditor UI job
(`PluginsArchiver.php:203`).

**Symptom:** `ClickHouse query failed: Unknown table expression identifier 'log_visit' in
scope SELECT log_conversion.idgoal …`.

**What is known:** the outer query *does* join it —
`FROM log_conversion AS log_conversion LEFT JOIN log_visit AS log_visit ON …` — and the
segment condition on `log_visit.location_country` is a long `IN ('ad', 'al', …)` list. So the
unresolved reference is somewhere the join is not in scope, presumably in a nested part of a
very large generated statement (all the visit-count and days-since-first dimensions). It is
intermittent: the job passed in rounds 7 to 11 and failed in round 12, which points at
archiving order rather than a fixed query shape.

**Not diagnosed further.** Reproducing it needs the whole statement; the success log caps SQL
at 600 characters and the error path at 2000, and this statement is far longer than either.

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

- **Engine:** `ReplacingMergeTree(_version, is_deleted)`, matching what the CDC connectors
  create.
- **Sorted by `idsite`, a date, then the MySQL primary key.** The copies used to be sorted
  by the primary key alone — the CDC default — which meant *no* read path filtered on the
  sorting key: every archiving query is `WHERE <datetime> BETWEEN ? AND ? AND idsite IN
  (…)` (`LogAggregator::getWhereStatement()`), and the visits log filters the same way, so
  the sparse index could not skip a single granule. The primary key stays as the tail of
  the key, which is what keeps deduplication exact.
- **Only immutable columns are sorted or partitioned on.** ReplacingMergeTree deduplicates
  on the whole sorting key, so a row whose sorting key changes between versions sorts to a
  different key and `FINAL` returns *both* copies — double counting, not a slowdown. That
  rules out `log_visit.visit_last_action_time` (rewritten on every action) and
  `log_conversion.server_time` (rewritten on abandoned-cart updates); `log_visit` is sorted
  on `visit_first_action_time`, which `VisitFirstActionTime` only ever writes `onNewVisit`.
  The same reasoning applies to `PARTITION BY`, more mildly: merges only collapse versions
  *within* a partition, so a mutable partition key strands versions where no merge reaches
  them. `log_conversion` and `log_conversion_item` therefore have no partition key at all.
- **Partitioned by month** (`toYYYYMM`) where there is an immutable datetime, so raw-log
  retention is a `DROP PARTITION` rather than a row-wise delete. Two years of retention is
  24 partitions, well inside ClickHouse's guidance of a few hundred.
- **Skipping indices** cover what the sorting key cannot: `minmax` on the mutable datetimes
  (it still prunes, because rows arrive in time order and so the column correlates with
  physical position) and a `bloom_filter` on `log_visit.idvisitor` for the visitor profile.
  They are added by `ALTER` after the copy, because `CREATE TABLE … AS SELECT` takes its
  column list from the `SELECT` and cannot declare an index inline.
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
- **Sizing settings are unset by default (DEV-20751).** `max_threads`,
  `max_bytes_before_external_sort`, `max_bytes_before_external_group_by` and
  `join_algorithm` are all `[database_analytics]` keys that are **empty** in
  `global.ini.php`, and empty means the setting is not sent at all — the ClickHouse server's
  own default applies. They used to be pinned to values chosen for a 2.55 GiB ddev
  container, which is exactly wrong on production hardware: `max_threads = 2` throws away
  most of the parallel scan that is ClickHouse's whole advantage, and spilling a sort or an
  aggregation while memory is still free is pure loss. The constrained environments opt back
  in explicitly, through `CLICKHOUSE_MAX_THREADS` and friends in the ddev `web_environment`
  and the CI job env (test mode only, like every other `CLICKHOUSE_*` override). Verified
  against `system.query_log`'s `Settings` map, not by reading the code: unconfigured, none of
  the four appear; configured, all four do.
- **Row post-processing** strips qualified names, reverses the hex encoding and trims
  microseconds from `TIME`-like values, so callers see what `Db::fetchAll()` would return on
  MySQL.
- **Errors carry context.** Selects include the translated SQL and the caller chain, which is
  how nearly every problem in this document was found. Statements and the log-table copy were
  wrapped later, after 26 UI failures surfaced only as an uncaught `DatabaseException` with no
  indication of what had run.
- **`count(DISTINCT user_id)` is wrong by exactly one, invisibly (DEV-20751).** MySQL's
  `COUNT(DISTINCT)` ignores `NULL`. ClickPipes maps `user_id` to a **non-nullable** `String`,
  so every MySQL `NULL` arrives as `''` — which `uniqExact` counts as a value, adding one
  spurious unique to every day, on every site. Measured against ClickHouse 25.8 on 2 Sep
  2026, for data whose MySQL answer is 2:

  | destination column | `count(DISTINCT user_id)` | `uniqExactIf(user_id, user_id != '')` |
  |---|---|---|
  | `String` (ClickPipes) | **3** — wrong | 2 |
  | `Nullable(String)` (in-test sync) | 2 | 2 |

  The translator emits the guarded form, which is correct on **both** mappings. That matters
  more than it looks: the defect is invisible exactly where it would be tested, because the
  in-test sync produces `Nullable` columns and therefore gets the right answer, while the
  destination the POC measures against does not. The number is plausible and wrong by one, so
  no row-count check catches it.

  `uniq\w*` was also added to the translator's aggregate-detection list — without it
  `uniqExactIf()` is not recognised as an aggregate, gets wrapped in `any()` by the GROUP BY
  fixer and is rejected with `ILLEGAL_AGGREGATION`.

- **PIPE CONFIGURATION REQUIREMENT: `idaction_url_ref` / `idaction_name_ref` must be
  replicated as `Nullable` (DEV-20751).** ClickPipes maps them to plain `UInt32`, so every
  MySQL `NULL` arrives as `0`. Matomo's Transitions query branches on
  `idaction_url_ref IS NULL` — "site search referrers are logged with `url_ref = NULL`; when
  we find one we have to join on `name_ref`" — and against that destination the branch can
  never fire.

  **This one cannot be repaired in the adapter, and the attempt is instructive.** Rewriting
  `IS NULL` to `(IS NULL OR = 0)` looks obviously right and is wrong: `0` and `NULL` are
  *both* meaningful in this column and they mean different things — `0` is "this action had
  no referring action at all", `NULL` is "the referrer was a site search". Treating them
  alike moves site searches into the wrong bucket; measured 2 Sep 2026, it added a spurious
  `previousSiteSearches` row to three of the seven Transitions system tests. The destination
  has genuinely lost information, so the fix belongs in the pipe, not here.

- **`log_action` must never be joined unrestricted (DEV-20751).** ClickHouse builds a join
  hash table from the **right** side, and in Transitions the right side is `log_action`: 242
  million rows on the POC corpus. Joining it whole is not slow, it is fatal — the first
  attempt died with `MEMORY_LIMIT_EXCEEDED` at 14.40 GiB.
  `LogAggregator::getActionRestrictionSubQuery()` therefore replaces the table reference with
  a sub-select restricted to the ids the report can actually reach, which turns the join into
  a primary-key lookup because `idaction` **is** `log_action`'s sorting key.
  `JoinGenerator` grew a `tableSubQuery` key to carry it; the entry keeps its `table` name so
  table sorting and join discovery are unaffected.

  Three deliberate properties:

  - **It applies to the analytics database only.** On MySQL the unrestricted join is both
    correct and the better plan, so the helper returns `null` there.
  - **The restriction is a superset and omits the segment.** The join is on an
    already-segment-filtered expression, so the answer is identical either way — and leaving
    the segment out means the expensive part of a segmented query runs once rather than
    twice. On the POC corpus that took segmented Transitions from 16.06s to 6.48s with
    byte-identical output.
  - **The site and date bounds are inlined, not bound.** A sub-select in `FROM` is emitted
    ahead of the `WHERE`, so binding them would mean splicing values into the middle of a
    positional bind list that `Segment::getSelectQuery()` also contributes to, and a bind
    list in the wrong order is a silently wrong answer rather than an error. The values are
    internally generated (formatted `Date` objects, integer site ids), never request input,
    and the helper bails out rather than guessing if the caller's extra `WHERE` carries
    placeholders of its own.

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

---

## 8. Switching between MySQL and the analytics database (DEV-20751)

`[database_analytics] enabled` is a **three-state** switch, so one value flips a whole
install without anyone editing connection details between the two legs of an A/B run:

| `enabled` | Behaviour |
|---|---|
| *(empty, the default)* | Infer from `host`: set means ClickHouse, unset means MySQL. This is the pre-6.0 rule, so nothing changes for an install that has a host configured today. |
| `1` | Assert ClickHouse. A missing host **throws**. |
| `0` | Assert MySQL, even with a fully populated host block — the MySQL leg of an A/B, with the ClickHouse credentials left in place. |

Two things about this are deliberate, and both are about failure modes rather than
convenience:

- **`enabled = 1` with no host is fatal, not a fallback.** Under the old rule "not
  configured" and "use MySQL" were the same state, so a typo in the host key gave you a full
  MySQL run that looked exactly like a successful ClickHouse run. On a benchmark that is the
  worst failure available. This is the existing no-fallback rule on routed reads, applied one
  level up.
- **The environment override can only ever *disable*.** `MATOMO_ANALYTICS_DB_DISABLED=1`
  forces `enabled = 0`, and it is the one `CLICKHOUSE_*`-family override that is **not**
  gated on `PIWIK_TEST_MODE` — safe precisely because of that asymmetry. An env var that
  could *enable* would reintroduce the update-flow bug in §7: the second, plain Matomo those
  specs install inherits the CI job environment and has no ClickHouse copy of its database.
  A switch that can only turn things off cannot route anything anywhere new.

The no-fallback rule on routed reads is unchanged. When the analytics database is on and a
query cannot be served, it fails loudly.

---

## 9. Emitted SQL versus the tuned benchmark SQL (DEV-20751)

The published ClickHouse timings were measured by running **hand-written** SQL
(`bench/queries/*.clickhouse.sql`) through `bench/bench.py`. Matomo itself has never produced
those queries. `bench/tools/dump-emitted-clickhouse-sql.php` captures what the adapter
actually emits for each of the 12 benchmark shapes so the gap can be read rather than
guessed.

**Closed** — the emitted SQL now carries these:

| Rule | Where it lives | Why there |
|---|---|---|
| Restrict `log_action` before joining it | `LogAggregator::getActionRestrictionSubQuery()` + `JoinGenerator` | Structural, and a plugin can reach this path with SQL no regex would match. Also needs the unbound `WHERE`, which only exists upstream. |
| `LIKE` on `name_lower`, not `ILIKE` on `name` | translator + adapter | Purely a ClickHouse dialect/index concern. The bind half has to be in the adapter because that is the first point where SQL and values are both known. |
| `uniqExactIf`, not `uniqExact` | translator | Same: a property of the destination column type, not of Matomo. |
| Window functions instead of `@counter` | `RankingQuery` (already present) | Structural, and MySQL 8 wants it too. |

**Still open**, and deliberately not attempted here:

- **The semi-join (`idvisit IN (SELECT ...)` instead of `LEFT JOIN` + `GROUP BY`).** This is
  the single largest remaining gap and the one the benchmark README calls "the whole rewrite;
  everything else is bookkeeping". The emitted Visits Log still fans a visit out to one row
  per matching action and collapses it with `LIMIT 1 BY idvisit`, which is *correct* but is a
  different shape from the tuned SQL and keeps the fan-out cost.

  It belongs upstream in `LogQueryBuilder`, not in the translator — it is far too structural
  for a textual rewrite. The hazard to design around is bind ordering: moving a joined table
  into a `WHERE ... IN (SELECT ...)` moves its placeholders relative to the ones
  `Segment::getSelectQuery()` contributes, and a positional bind list in the wrong order is a
  silently wrong answer rather than an error. The three rules that travel with it are not
  optional — both action-scope components in the *same* subquery, the site and date filter
  repeated inside every subquery, and one day of slack on the action lower bound (bounding it
  to exactly the window dropped 6 of 68,804 visits when checked on 27 Aug 2026).

- **`FINAL` on `log_visit` only.** The adapter sets `final = 1` connection-wide, so FINAL
  applies to every table in every statement. Only `log_visit` needs it: Matomo `UPDATE`s that
  table on every action, while `log_action` and `log_link_visit_action` are insert-only and
  `IN` collapses duplicates anyway. **This needs a p200 measurement before a decision, and
  that measurement is not reproducible on a laptop** — a ddev fixture is a few thousand rows.
  The setting is deliberately left alone rather than changed on principle. Note the reason it
  was chosen over the `FINAL` keyword in the first place — the setting reaches subqueries
  where the keyword does not — has to be *solved*, not forgotten, if it is ever made
  selective.

### `RankingQuery` agrees with the benchmark (verified, not read)

The benchmark writes `row_number() OVER (PARTITION BY action_partition, is_self ORDER BY ...)`;
the adapter emits a running conditional count over
`ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW`, partitioned by `action_partition` alone
and counting only rows where `is_self = 0`.

These are the same function. For an `is_self = 0` row the adapter's running count is its rank
among `is_self = 0` rows in the partition, which is exactly what partitioning on `is_self`
gives the benchmark; `is_self != 0` rows take the `-is_self` sentinel in both and consume no
rank in either, which is what the MySQL original does by short-circuiting the `CASE` before
the counter increments. The caps agree too (`>= 1001` against `> 1000`).

Confirmed by output on 2 Sep 2026: the seven Transitions system tests pass against ClickHouse,
including the top-N boundary where a one-row shift is the whole symptom. **The adapter's form
is the faithful one and the benchmark should be read as agreeing with it**, not the reverse.

## 10. Measuring both engines: `clickhouse:benchmark` (DEV-20751)

`./console clickhouse:benchmark --date=2026-08-03` times the same report APIs and the same
archiving runs on MySQL and on the analytics database, and prints a comparison.

The point of it is that §9 lists gaps between the SQL Matomo *emits* and the SQL the standalone
benchmark measured. Those gaps can only be closed against numbers produced by Matomo itself, so
nothing here reimplements archiving or the API dispatcher — every measurement is a child process
running Matomo's own CLI:

| | |
|---|---|
| archive cases | `core:invalidate-report-data`, then the `CoreAdminHome.archiveReports` request `core:archive` would have issued (`--archive-driver=request`, default) or `core:archive` itself (`--archive-driver=cron`) |
| API cases | `climulti:request` |

Case ids match the standalone benchmark's file names — `v1`/`v1s`/`v1n`/`v1c`/`v1e` for the
Visits Log, `a1`/`a1s`/… for archiving, `t1`/`t1s` for Transitions — so a number from here is
directly comparable to a number from there, and a divergence is a finding about the adapter
rather than a mystery.

### The engine switch is checked, not trusted

The two legs differ by one environment variable, `MATOMO_ANALYTICS_DB_DISABLED` (§8). Before
measuring anything the command starts one child per leg with `--report-engine` and asks it which
engine it actually routes to. **A mismatch is fatal**, because the failure it prevents is silent:
an environment variable that did not take effect produces a full run of plausible numbers under
the wrong column heading, and nothing in the table would show it. `--allow-engine-mismatch`
overrides, and then the output is explicitly not an A/B.

The same check is why `--archive-driver=cron` refuses to run when `CliMulti` cannot use CLI
processes: HTTP archiving goes to php-fpm, which does not inherit the environment, so the engine
selection would not reach the archiver.

### Where the archiving numbers come from

From the `archiving_metrics` table, not from the wall clock. The ArchivingMetrics plugin records
one row per archive with `total_time` and `total_time_exclusive`, which is the archive build
alone; the child's wall clock also contains bootstrap and the invalidation scan. Both are kept,
and the calibration line at the end of a run reports what a child that does no work costs, so the
gap between them is accounted for rather than argued about.

Two consequences worth knowing before reading a table:

- **`total_time` is in milliseconds.** `Timer::complete()` takes a difference of
  `microtime(true)` values — seconds — and multiplies by 1000. The local variable is named
  `$totalTimeMs` *before* that multiplication, which reads like it is already milliseconds.
- **Only all-plugin archives get a row.** `Timer::isApplicableForTiming()` skips done flags
  containing a `.`, so `--archive-plugin=VisitsSummary` produces no row and that case falls back
  to the wall clock. The table names its source per cell and the notes call out any fallback.

### Invalidation, not deletion

Each archive iteration runs `core:invalidate-report-data` first. Without it the second iteration
finds a usable archive, reports `wasCached`, writes no `archiving_metrics` row, and lands in the
results as a suspiciously fast leg. For `week`/`month`/`year`, add `--cascade` — otherwise the
run aggregates existing day archives and measures almost no log queries.

### Segments, and why `--archive-driver=cron` needs setup

The default `request` driver archives an ad-hoc segment directly, so it needs no setup. The
`cron` driver runs the real `core:archive`, which works from `archive_invalidations` and resolves
a done-flag hash back to a definition through the **stored** segment list
(`QueueConsumer::findSegmentForArchive`); an unstored segment is logged as unresolvable and
skipped, so the case would pass having archived nothing. `--setup-segments` stores them.

That step refuses to run while `[General] process_new_segments_from = "beginning_of_time"` (the
default) unless `--allow-full-rearchive` is passed: creating an auto-archived segment schedules
re-archiving of the whole history, which the next `core:archive` starts working through. On a
corpus of any size that is days of archiving, none of it the benchmark, and nothing in the
benchmark's output would explain it.

### Results are compared, not just timings

Two engines that disagree about the answer are not two engines to compare timings between. The
comparison is deliberately narrow, because the engines disagree about the *formatting* of most
columns and always will (§5): the fingerprint is the ordered list of `idVisit` values for a
Visits Log, or the archived visit count for an archiving run. Anything else is digested whole and
marked weak, and a weak mismatch is reported as `unverified` rather than as a disagreement.

### Tideways

On by default. The children get `-d tideways.enable_cli=1` (without it nothing on the CLI is
traced at all) and `-d tideways.sample_rate=100` (at a production sample rate the slowest case is
as likely as any other to be the one that missed), plus `TIDEWAYS_SERVICE=<service>-<engine>` so
the two legs are separable in the UI instead of averaged together. `--tideways-ini` passes
anything else a particular install needs. With `--archive-driver=cron` the ini flags are
forwarded through `core:archive --php-cli-options`, because `CliMulti` grandchildren inherit the
environment but not this process's `-d` flags — and those grandchildren are where the archiving
actually happens.

### Reading a run

`--dry-run` prints every command a case would run without running it. `--json` writes every
iteration, including the exact command lines, so a published number can be traced back to what
produced it. A `spread` above 2x means the iterations did not converge — on ClickHouse Cloud that
is usually cold marks and the fix is more warmups, not a footnote; the notes at the end of a run
say so explicitly rather than leaving the cell looking measured.

## 11. `log_action` is never joined whole — enforced centrally (DEV-20751)

ClickHouse builds a join hash table from the **right** side, so `JOIN log_action ON <expr> =
log_action.idaction` materialises the entire action dictionary however selective the left side
is. On the POC corpus that is 242M rows, and it does not run slowly — it dies in
`FillingRightJoinSide` with `MEMORY_LIMIT_EXCEEDED`.

This was first fixed locally, in `LogAggregator::queryActionsByDimension()`. That turned out to
cover one of at least three paths that build such a join:

| path | builds the join in |
|---|---|
| Transitions, dimension queries | `LogAggregator::queryActionsByDimension()` |
| Visits Log action enrichment | `Actions\VisitorDetails::queryActionsForVisits()` + the joins plugins contribute via `Actions.getCustomActionDimensionFieldsAndJoins` |
| day archiving | `Actions\RecordBuilders\ActionReports::archiveDayQueryProcess()` |

The property is a property of ClickHouse, not of any one report, so it is now enforced in the
adapter for every query: `ClickhouseDialectTranslator::restrictLogActionJoins()` rewrites

```sql
JOIN log_action AS a ON t.idaction_url = a.idaction
-- into
JOIN (SELECT * FROM log_action
       WHERE idaction IN (SELECT t.idaction_url FROM <driving table> WHERE <bounds>)) AS a ON …
```

which turns the join into a primary-key lookup, because `idaction` **is** `log_action`'s sorting
key.

Three things about it are load-bearing:

- **It runs after positional binds become named ones.** The restriction repeats the driving
  query's WHERE. Repeating `?` would desynchronise the bind list — silently binding values to the
  wrong columns; repeating `:chBind007` is free, because a named parameter may appear any number
  of times.
- **Only conjuncts referencing the driving table are carried over**
  (`keepConjunctsReferencingOnly()`). The restriction subquery has one table in scope, so a
  conjunct naming a joined alias cannot resolve there. Dropping conjuncts from an AND-chain only
  widens the id set, and the restriction is only required to be a **superset** of what the join
  needs. The same rule now guards the older `LogAggregator` restriction, which previously copied
  the caller's extra WHERE wholesale and produced `Unknown expression or function identifier
  log_action1.type` — that had been failing all 15 Actions system tests on ClickHouse.
- **Anything it cannot read with confidence is left alone**: a non-trivial ON expression, a right
  side that is already a subquery, a driving side that is itself a subquery. A missed rewrite
  costs speed; a wrong one costs rows.

Verified against ClickHouse: Transitions 7/7 and Actions 15/15 system tests, both by expected
output, plus the same suites unchanged on MySQL.
