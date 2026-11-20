#!/bin/bash

# Matomo - free/libre analytics platform
#
# End-to-end verification for the DebugView plugin. Repeatable; safe to run twice.
#
# Required environment:
#   MATOMO_URL        base URL of a running Matomo (default http://apache.matomo)
#   IDSITE            idSite to track/test against (default 36)
#   TOKEN_ROOT        token_auth of a super user
#   TOKEN_NOACCESS    token_auth of a user with NO access to IDSITE
#   ADMIN_LOGIN/ADMIN_PASSWORD    web login of a super user
#   VIEWER_LOGIN/VIEWER_PASSWORD  web login of a user with view access to IDSITE
#
# Exit code 0 = all checks passed.

set -u

MATOMO_URL="${MATOMO_URL:-http://apache.matomo}"
IDSITE="${IDSITE:-36}"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
MATOMO_ROOT="$(cd "$SCRIPT_DIR/../../../.." && pwd)"
RUN="e2e$(date +%s)"
JAR="$(mktemp)"
PASS=0
FAIL=0

for var in TOKEN_ROOT TOKEN_NOACCESS ADMIN_LOGIN ADMIN_PASSWORD VIEWER_LOGIN VIEWER_PASSWORD; do
    if [ -z "${!var:-}" ]; then echo "ERROR: $var is not set"; exit 2; fi
done

report() { # report <ok:0|1> <description>
    if [ "$1" -eq 0 ]; then PASS=$((PASS+1)); echo "  PASS: $2";
    else FAIL=$((FAIL+1)); echo "  FAIL: $2"; fi
}

contains() { # contains <haystack-file> <needle> <description>
    grep -qF -- "$2" "$1"; report $? "$3"
}

not_contains() { # not_contains <haystack-file> <needle> <description>
    if grep -qF -- "$2" "$1"; then report 1 "$3"; else report 0 "$3"; fi
}

api() { # api <token> <extra-params>  -> stdout json
    curl -s "$MATOMO_URL/index.php" --data "module=API&method=DebugView.getRecentHits&idSite=$IDSITE&format=json&token_auth=$1&$2"
}

track() { # track <query-string>
    curl -s -o /dev/null -w "%{http_code}" "$MATOMO_URL/matomo.php?idsite=$IDSITE&rec=1&$1"
}

login() { # login <user> <password>  (fills cookie jar)
    rm -f "$JAR"
    local nonce
    nonce=$(curl -s -c "$JAR" "$MATOMO_URL/index.php?module=Login&action=index" \
        | sed -n 's/.*name="form_nonce"[^>]*value="\([^"]*\)".*/\1/p' | head -1)
    curl -s -b "$JAR" -c "$JAR" -o /dev/null \
        --data-urlencode "form_login=$1" \
        --data-urlencode "form_password=$2" \
        --data-urlencode "form_nonce=$nonce" \
        "$MATOMO_URL/index.php?module=Login&action=index"
}

fetch_page() { # fetch_page <outfile> -> echoes http code
    curl -s -b "$JAR" -o "$1" -w "%{http_code}" \
        "$MATOMO_URL/index.php?module=DebugView&action=index&idSite=$IDSITE&period=day&date=today"
}

fetch_admin_home() { # fetch_admin_home <outfile>; menu checks run against a page that is NOT DebugView itself
    curl -s -b "$JAR" -o "$1" -w "%{http_code}" \
        "$MATOMO_URL/index.php?module=CoreAdminHome&action=home&idSite=$IDSITE&period=day&date=today"
}

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP" "$JAR"' EXIT

echo "== 1. activate plugin =="
cd "$MATOMO_ROOT"
OUT=$(./console plugin:activate DebugView 2>&1)
echo "$OUT" | grep -qE "Activated plugin DebugView|already activated" ; report $? "plugin DebugView is active ($(echo "$OUT" | tail -1))"
php "$SCRIPT_DIR/set-visitor-log.php" enabled >/dev/null

# one poll activates raw-parameter capturing before the hits are fired
api "$TOKEN_ROOT" "" >/dev/null
sleep 1

echo "== 2. fire tracking requests =="
CODE=$(track "url=http%3A%2F%2Fdebugview-e2e.example%2Fpage-$RUN&action_name=E2E%20Page%20$RUN&debug=1&_id=e2e00000000000aa")
[ "$CODE" = "200" ] || [ "$CODE" = "204" ]; report $? "pageview tracked (HTTP $CODE)"
CODE=$(track "e_c=E2ECat$RUN&e_a=click&e_n=cta&e_v=3&debug=1&_id=e2e00000000000aa")
[ "$CODE" = "200" ] || [ "$CODE" = "204" ]; report $? "event tracked (HTTP $CODE)"
CODE=$(track "search=needle$RUN&search_cat=e2ecat&search_count=7&debug=1&_id=e2e00000000000aa")
[ "$CODE" = "200" ] || [ "$CODE" = "204" ]; report $? "site search tracked (HTTP $CODE)"
CODE=$(track "download=http%3A%2F%2Fdebugview-e2e.example%2Ffile-$RUN.zip&debug=1&_id=e2e00000000000aa")
[ "$CODE" = "200" ] || [ "$CODE" = "204" ]; report $? "download tracked (HTTP $CODE)"
CODE=$(track "link=http%3A%2F%2Fexternal.example%2Fout-$RUN&debug=1&_id=e2e00000000000aa")
[ "$CODE" = "200" ] || [ "$CODE" = "204" ]; report $? "outlink tracked (HTTP $CODE)"
CODE=$(track "url=http%3A%2F%2Fdebugview-e2e.example%2Fnodebug-$RUN&action_name=E2E%20NoDebug%20$RUN&_id=e2e00000000000aa")
[ "$CODE" = "200" ] || [ "$CODE" = "204" ]; report $? "pageview without debug=1 tracked (HTTP $CODE)"
sleep 2

echo "== 3a. API as super user: all hits with correct types =="
api "$TOKEN_ROOT" "" > "$TMP/root.json"
php -r '
$d = json_decode(file_get_contents($argv[1]), true);
$run = $argv[2];
if (!is_array($d) || isset($d["result"])) { fwrite(STDERR, "api error: " . substr(json_encode($d), 0, 300)); exit(1); }
$found = [];
$noDebugHit = null;
foreach ($d["hits"] as $h) {
    if (strpos($h["title"], "E2E NoDebug $run") !== false) { $noDebugHit = $h; continue; }
    if (strpos(json_encode($h), $run) !== false) { $found[$h["type"]] = $h; }
}
$expected = ["pageview", "event", "search", "download", "outlink"];
$missing = array_diff($expected, array_keys($found));
if ($missing) { fwrite(STDERR, "missing types: " . implode(",", $missing)); exit(1); }
if ($found["pageview"]["title"] !== "E2E Page $run") { fwrite(STDERR, "bad pageview title"); exit(1); }
if (($found["event"]["trackingParams"]["e_c"] ?? "") !== "E2ECat$run") { fwrite(STDERR, "bad event category"); exit(1); }
if (empty($found["pageview"]["idVisit"]) || empty($found["pageview"]["idLinkVa"])) { fwrite(STDERR, "lazy-load references missing"); exit(1); }
if ($found["search"]["title"] !== "needle$run") { fwrite(STDERR, "bad search keyword"); exit(1); }
if (strpos($found["download"]["subtitle"], "file-$run.zip") === false) { fwrite(STDERR, "bad download url"); exit(1); }
if (strpos($found["outlink"]["subtitle"], "out-$run") === false) { fwrite(STDERR, "bad outlink url"); exit(1); }
if (!is_array($found["pageview"]["trackingParams"] ?? null)) { fwrite(STDERR, "pageview has no raw tracking params"); exit(1); }
if (($found["pageview"]["trackingParams"]["action_name"] ?? "") !== "E2E Page $run") { fwrite(STDERR, "raw action_name mismatch"); exit(1); }
if (($found["pageview"]["trackingParams"]["debug"] ?? "") !== "1") { fwrite(STDERR, "raw debug param missing"); exit(1); }
if (($found["event"]["trackingParams"]["e_c"] ?? "") !== "E2ECat$run") { fwrite(STDERR, "raw e_c mismatch"); exit(1); }
if ($noDebugHit !== null) { fwrite(STDERR, "no-debug hit must NOT be visualised in the stream"); exit(1); }
$defaults = $found["pageview"]["trackingParamsDefaults"] ?? null;
if (!is_array($defaults)) { fwrite(STDERR, "default parameters missing"); exit(1); }
if (($defaults["userAgent"] ?? "") === "") { fwrite(STDERR, "default userAgent missing"); exit(1); }
if (!is_numeric($defaults["serverTimeReceived"] ?? null)) { fwrite(STDERR, "serverTimeReceived missing"); exit(1); }
$other = $found["pageview"]["trackingParamsOther"] ?? null;
if (!is_array($other) || !array_key_exists("isAuthenticated", $other)) { fwrite(STDERR, "other/isAuthenticated missing"); exit(1); }
if ($other["isAuthenticated"] !== false) { fwrite(STDERR, "plain curl request must not be authenticated"); exit(1); }
foreach ($d["hits"] as $h) { if (empty($h["idRawRequest"]) || empty($h["timestamp"]) || empty($h["timePretty"])) { fwrite(STDERR, "hit missing id/timestamp/timePretty"); exit(1); } }
$ts = array_column($d["hits"], "timestamp");
$sorted = $ts; sort($sorted);
if ($ts !== $sorted) { fwrite(STDERR, "hits not sorted"); exit(1); }
$ids = array_map("intval", array_column($d["hits"], "idRawRequest"));
file_put_contents($argv[3], (string) max($ids));
' "$TMP/root.json" "$RUN" "$TMP/maxid" 2>"$TMP/err3a"
report $? "5 debug hits visualised with raw+default params, no-debug hit hidden ($(cat "$TMP/err3a" 2>/dev/null))"

echo "== 3a2. hourly trim task caps rows at 500 per site =="
./console scheduled-tasks:run "Piwik\\Plugins\\DebugView\\Tasks.trimRawRequests" >/dev/null 2>&1
report $? "trim scheduled task executed"
RAWCOUNT=$(php "$SCRIPT_DIR/raw-request-count.php" "$IDSITE" 2>/dev/null | tail -1)
[ -n "$RAWCOUNT" ] && [ "$RAWCOUNT" -le 500 ]; report $? "debugview_raw_request holds $RAWCOUNT rows for site $IDSITE after trim (<= 500)"

echo "== 3b. API as user without access: rejected =="
api "$TOKEN_NOACCESS" "" > "$TMP/noaccess.json"
php -r '$d = json_decode(file_get_contents($argv[1]), true);
exit(isset($d["result"]) && $d["result"] === "error" ? 0 : 1);' "$TMP/noaccess.json"
report $? "no-access token receives an error result"

echo "== 3c. incremental polling: only newer hits =="
MAXID=$(cat "$TMP/maxid")
sleep 1
CODE=$(track "url=http%3A%2F%2Fdebugview-e2e.example%2Flate-$RUN&action_name=E2E%20Late%20$RUN&debug=1&_id=e2e00000000000aa")
sleep 2
api "$TOKEN_ROOT" "minId=$MAXID" > "$TMP/incr.json"
php -r '
$d = json_decode(file_get_contents($argv[1]), true); $run = $argv[2]; $min = (int) $argv[3];
$titles = array_column($d["hits"], "title");
if (!in_array("E2E Late $run", $titles, true)) { fwrite(STDERR, "late hit missing"); exit(1); }
if (in_array("E2E Page $run", $titles, true)) { fwrite(STDERR, "old hit re-delivered"); exit(1); }
foreach ($d["hits"] as $h) { if ((int) $h["idRawRequest"] <= $min) { fwrite(STDERR, "hit id not greater than cursor"); exit(1); } }
' "$TMP/incr.json" "$RUN" "$MAXID" 2>"$TMP/err3c"
report $? "minId call returns only newer hits ($(cat "$TMP/err3c" 2>/dev/null))"

echo "== 4a. admin page as super user =="
login "$ADMIN_LOGIN" "$ADMIN_PASSWORD"
CODE=$(fetch_page "$TMP/page_root.html")
[ "$CODE" = "200" ]; report $? "page returns HTTP 200 (got $CODE)"
contains "$TMP/page_root.html" 'vue-entry="DebugView.DebugViewPage"' "DebugView container markup present"
fetch_admin_home "$TMP/home_root.html" >/dev/null
contains "$TMP/home_root.html" 'module=DebugView' "menu entry present for super user"

echo "== 4b. admin page as view-only user =="
login "$VIEWER_LOGIN" "$VIEWER_PASSWORD"
CODE=$(fetch_page "$TMP/page_viewer.html")
[ "$CODE" = "200" ]; report $? "page returns HTTP 200 for view user (got $CODE)"
contains "$TMP/page_viewer.html" 'vue-entry="DebugView.DebugViewPage"' "container markup present for view user"
fetch_admin_home "$TMP/home_viewer.html" >/dev/null
contains "$TMP/home_viewer.html" 'module=DebugView' "menu entry present for view user"

echo "== 4c. visits log disabled: menu stays, notice shown, API fails =="
php "$SCRIPT_DIR/set-visitor-log.php" disabled >/dev/null
CODE=$(fetch_page "$TMP/page_disabled.html")
[ "$CODE" = "200" ]; report $? "disabled page still renders friendly (HTTP $CODE)"
not_contains "$TMP/page_disabled.html" 'vue-entry="DebugView.DebugViewPage"' "stream UI not rendered while disabled"
contains "$TMP/page_disabled.html" 'Debug View streams data from the Visits Log' "friendly notice shown"
fetch_admin_home "$TMP/home_disabled.html" >/dev/null
contains "$TMP/home_disabled.html" 'module=DebugView' "menu entry still present while disabled"
api "$TOKEN_ROOT" "" > "$TMP/disabled.json"
php -r '$d = json_decode(file_get_contents($argv[1]), true); exit(isset($d["result"]) && $d["result"] === "error" ? 0 : 1);' "$TMP/disabled.json"
report $? "API fails cleanly while visits log disabled"

echo "== 4d. re-enable: everything back =="
php "$SCRIPT_DIR/set-visitor-log.php" enabled >/dev/null
CODE=$(fetch_page "$TMP/page_reenabled.html")
[ "$CODE" = "200" ]; report $? "page renders again (HTTP $CODE)"
contains "$TMP/page_reenabled.html" 'vue-entry="DebugView.DebugViewPage"' "stream UI rendered again"
fetch_admin_home "$TMP/home_reenabled.html" >/dev/null
contains "$TMP/home_reenabled.html" 'module=DebugView' "menu entry visible again"

echo "== 5. Live plugin deactivated: clean failure everywhere =="
./console plugin:deactivate Live >/dev/null 2>&1
api "$TOKEN_ROOT" "" > "$TMP/nolive.json"
php -r '$d = json_decode(file_get_contents($argv[1]), true); exit(isset($d["result"]) && $d["result"] === "error" ? 0 : 1);' "$TMP/nolive.json"
report $? "API fails cleanly with Live deactivated"
CODE=$(fetch_page "$TMP/page_nolive.html")
[ "$CODE" = "200" ]; report $? "page still renders friendly with Live deactivated (HTTP $CODE)"
not_contains "$TMP/page_nolive.html" 'vue-entry="DebugView.DebugViewPage"' "stream UI not rendered without Live"
fetch_admin_home "$TMP/home_nolive.html" >/dev/null
contains "$TMP/home_nolive.html" 'module=DebugView' "menu entry still present without Live"
./console plugin:activate Live >/dev/null 2>&1
report $? "Live plugin re-activated"

echo "== 6. no 'Top events' pane anywhere =="
not_contains "$TMP/page_root.html" 'Top events' "no Top-events pane in rendered page"
if ls "$MATOMO_ROOT/plugins/DebugView/vue/src" >/dev/null 2>&1; then
    if grep -riq "top events" "$MATOMO_ROOT/plugins/DebugView/vue/src"; then report 1 "no Top-events code in vue src"; else report 0 "no Top-events code in vue src"; fi
fi

echo
echo "==============================="
echo "PASS: $PASS  FAIL: $FAIL"
echo "==============================="
[ "$FAIL" -eq 0 ]
