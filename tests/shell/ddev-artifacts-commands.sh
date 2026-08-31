#!/usr/bin/env bash

# Tests for the DDEV host commands that sync build artifacts, in .ddev/commands/host.
#
# Neither DDEV, a credential helper nor network access is needed: ddev is stubbed on PATH and git is
# pointed at a fake credential helper through an isolated configuration, so the developer's own git
# configuration and credential store stay untouched.

set -uo pipefail

REPOSITORY_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
LIBRARY="${REPOSITORY_ROOT}/.ddev/commands/host/.matomo_artifacts_lib.sh"
LOGIN_COMMAND="${REPOSITORY_ROOT}/.ddev/commands/host/matomo_artifacts_login"

# BSD mktemp, as shipped on macOS, needs a template
TEST_DIR="$(mktemp -d 2>/dev/null || mktemp -d -t matomo-artifacts)"
FAILURES=0
ASSERTIONS=0

trap 'rm -rf "${TEST_DIR}"' EXIT

setup() {
  mkdir -p "${TEST_DIR}/bin"

  cat > "${TEST_DIR}/bin/ddev" <<'STUB'
#!/usr/bin/env bash
printf '%s\n' "$*" > "${DDEV_STUB_DIR}/args"
cat > "${DDEV_STUB_DIR}/stdin"
STUB

  # git looks a helper up as git-credential-<name> on PATH, so the fake is a real one. It keeps what
  # it is given in a file, so a test can tell whether "git credential approve" actually stored
  # anything; FAKE_IGNORE_STORE makes it behave like a helper that quietly stores nothing.
  cat > "${TEST_DIR}/bin/git-credential-fake" <<'HELPER'
#!/usr/bin/env bash
case "${1}" in
  get)
    [[ -f "${FAKE_CREDENTIAL_STORE}" ]] && cat "${FAKE_CREDENTIAL_STORE}"
    ;;
  store)
    [[ -n "${FAKE_IGNORE_STORE:-}" ]] && exit 0
    grep -E '^(username|password)=' > "${FAKE_CREDENTIAL_STORE}"
    ;;
  erase)
    rm -f "${FAKE_CREDENTIAL_STORE}"
    ;;
esac
exit 0
HELPER

  chmod +x "${TEST_DIR}/bin/ddev" "${TEST_DIR}/bin/git-credential-fake"

  export PATH="${TEST_DIR}/bin:${PATH}"
  export DDEV_STUB_DIR="${TEST_DIR}"
  export GIT_CONFIG_SYSTEM=/dev/null
  export GIT_CONFIG_NOSYSTEM=1
  # without these git would read the configuration of whatever repository the suite runs in
  export GIT_CONFIG_GLOBAL="${TEST_DIR}/no-helper"
  export GIT_DIR="${TEST_DIR}/isolated.git"

  export FAKE_CREDENTIAL_STORE="${TEST_DIR}/credential-store"

  : > "${TEST_DIR}/no-helper"
  printf '[credential]\n\thelper = fake\n' > "${TEST_DIR}/with-credential"

  seed_credential
}

seed_credential() {
  printf 'username=ci-user\npassword=p@ss word\n' > "${FAKE_CREDENTIAL_STORE}"
}

run_login() {
  LOGIN_OUTPUT="$(
    export GIT_CONFIG_GLOBAL="${TEST_DIR}/${1}"
    "${LOGIN_COMMAND}" <<<"${2}
${3}" 2>&1
  )"
  LOGIN_STATUS=$?
}

fail() {
  FAILURES=$((FAILURES + 1))
  printf 'not ok - %s\n' "${1}"
  printf '  expected: %s\n  actual:   %s\n' "${2}" "${3}"
}

pass() {
  printf 'ok - %s\n' "${1}"
}

assert_equals() {
  ASSERTIONS=$((ASSERTIONS + 1))
  if [[ "${1}" == "${2}" ]]; then pass "${3}"; else fail "${3}" "${1}" "${2}"; fi
}

assert_contains() {
  ASSERTIONS=$((ASSERTIONS + 1))
  if [[ "${1}" == *"${2}"* ]]; then pass "${3}"; else fail "${3}" "containing '${2}'" "${1}"; fi
}

assert_not_contains() {
  ASSERTIONS=$((ASSERTIONS + 1))
  if [[ "${1}" != *"${2}"* ]]; then pass "${3}"; else fail "${3}" "not containing '${2}'" "${1}"; fi
}

# Runs matomo_artifacts::sync with the given git configuration, and records what reached the ddev
# stub. Sets SYNC_STATUS, SYNC_OUTPUT, DDEV_ARGS and DDEV_STDIN.
run_sync() {
  local configuration="${1}"
  shift

  rm -f "${TEST_DIR}/args" "${TEST_DIR}/stdin"

  # both redirections belong inside the substitution: a redirection on an assignment-only
  # command resets $? to 0, and stderr is where the commands report why they stopped
  SYNC_OUTPUT="$(
    export GIT_CONFIG_GLOBAL="${TEST_DIR}/${configuration}"
    source "${LIBRARY}"
    matomo_artifacts::sync tests:sync-ui-screenshots "$@" 2>&1 </dev/null
  )"
  SYNC_STATUS=$?

  DDEV_ARGS="$(cat "${TEST_DIR}/args" 2>/dev/null || true)"
  DDEV_STDIN="$(cat "${TEST_DIR}/stdin" 2>/dev/null || true)"
}

test_public_repository_needs_no_credentials() {
  run_sync no-helper 12345 'Marketplace_.*'

  assert_equals 0 "${SYNC_STATUS}" 'a public sync works without any credential'
  assert_equals 'matomo:console tests:sync-ui-screenshots 12345 Marketplace_.*' "${DDEV_ARGS}" \
    'a public sync forwards the arguments unchanged'
  assert_not_contains "${DDEV_ARGS}" '--http-user' 'a public sync sends no credentials'

  run_sync no-helper 12345 -r matomo-org/matomo
  assert_equals 0 "${SYNC_STATUS}" 'an explicit matomo-org repository needs no credentials'
}

test_premium_repository_sends_the_credentials() {
  seed_credential

  run_sync with-credential 12345 -r innocraft/plugin-FormAnalytics

  assert_equals 0 "${SYNC_STATUS}" 'a premium sync succeeds when a credential is stored'
  assert_contains "${DDEV_ARGS}" '--http-user=ci-user' 'the stored username is passed'
  assert_contains "${DDEV_ARGS}" '--http-password-stdin' 'the console is asked to read STDIN'
  assert_equals 'p@ss word' "${DDEV_STDIN}" 'the password reaches the console over STDIN intact'
  assert_not_contains "${DDEV_ARGS}" 'p@ss word' 'the password never appears in the arguments'
}

test_premium_repository_stops_without_a_credential() {
  run_sync no-helper 12345 -r innocraft/plugin-FormAnalytics

  assert_equals 1 "${SYNC_STATUS}" 'a premium sync fails when nothing is stored'
  assert_equals '' "${DDEV_ARGS}" 'a premium sync without a credential never runs ddev'
  assert_contains "${SYNC_OUTPUT}" 'matomo:artifacts:login' 'the failure points at the login command'
}

test_every_repository_option_spelling_is_recognised() {
  local spelling=''

  seed_credential

  for spelling in '-r innocraft/plugin-Funnels' '-rinnocraft/plugin-Funnels' \
    '--repository innocraft/plugin-Funnels' '--repository=innocraft/plugin-Funnels' \
    '-er innocraft/plugin-Funnels' '-erinnocraft/plugin-Funnels'; do
    # shellcheck disable=SC2086
    run_sync with-credential 12345 ${spelling}

    assert_contains "${DDEV_ARGS}" '--http-password-stdin' "'${spelling}' is treated as a premium repository"
  done

  # -p takes a value, so its value must not be mistaken for a repository even when it contains an "r"
  for spelling in '-p FormAnalytics' '-pFormAnalytics' '-e'; do
    # shellcheck disable=SC2086
    run_sync no-helper 12345 ${spelling}

    assert_equals 0 "${SYNC_STATUS}" "'${spelling}' alone stays a public sync"
  done
}

test_option_parsing_stops_at_the_terminator() {
  run_sync no-helper 12345 -- -r innocraft/plugin-FormAnalytics

  assert_equals 0 "${SYNC_STATUS}" 'an option after -- is not read as a repository'
  assert_not_contains "${DDEV_ARGS}" '--http-user' 'nothing is appended after the terminator'
}

test_credentials_are_inserted_before_the_terminator() {
  seed_credential

  run_sync with-credential 12345 -r innocraft/plugin-FormAnalytics -- --passed-through

  assert_contains "${DDEV_ARGS}" '--http-password-stdin -- --passed-through' \
    'the credentials are placed before -- so Symfony still reads them as options'
  assert_equals 'p@ss word' "${DDEV_STDIN}" 'the password is still piped when -- is used'
}

test_login_stores_the_credentials() {
  rm -f "${FAKE_CREDENTIAL_STORE}"

  run_login with-credential 'ci-user' 'p@ss word'

  assert_equals 0 "${LOGIN_STATUS}" 'login succeeds when the helper stores the password'
  assert_contains "${LOGIN_OUTPUT}" 'Stored the credentials' 'login confirms what it did'
  assert_contains "$(cat "${FAKE_CREDENTIAL_STORE}" 2>/dev/null)" 'password=p@ss word' \
    'the password reached the credential helper'
  assert_contains "$(cat "${FAKE_CREDENTIAL_STORE}" 2>/dev/null)" 'username=ci-user' \
    'the username reached the credential helper'
  assert_not_contains "${LOGIN_OUTPUT}" 'p@ss word' 'login never echoes the password'
}

test_login_reports_when_nothing_was_stored() {
  # git accepts "credential approve" without a helper and stores nothing, silently
  run_login no-helper 'ci-user' 'p@ss word'

  assert_equals 1 "${LOGIN_STATUS}" 'login fails when nothing was actually stored'
  assert_contains "${LOGIN_OUTPUT}" 'not stored' 'login says the credentials were not stored'
  assert_contains "${LOGIN_OUTPUT}" 'credential.helper' 'login explains how to configure a helper'
  assert_not_contains "${LOGIN_OUTPUT}" 'p@ss word' 'login never echoes the password'
}

test_login_reports_a_helper_that_silently_ignores_the_store() {
  rm -f "${FAKE_CREDENTIAL_STORE}"

  FAKE_IGNORE_STORE=1 run_login with-credential 'ci-user' 'p@ss word'

  assert_equals 1 "${LOGIN_STATUS}" 'login fails when the helper accepts but stores nothing'
}

test_login_reports_credentials_that_came_back_different() {
  # another helper answering first would return someone else's credentials for this host
  seed_credential

  FAKE_IGNORE_STORE=1 run_login with-credential 'other-user' 'other-password'

  assert_equals 1 "${LOGIN_STATUS}" 'login fails when git returns different credentials'
  assert_contains "${LOGIN_OUTPUT}" 'does not return them' 'login explains what it read back'
}

setup

test_public_repository_needs_no_credentials
test_premium_repository_sends_the_credentials
test_premium_repository_stops_without_a_credential
test_every_repository_option_spelling_is_recognised
test_option_parsing_stops_at_the_terminator
test_credentials_are_inserted_before_the_terminator
test_login_stores_the_credentials
test_login_reports_when_nothing_was_stored
test_login_reports_a_helper_that_silently_ignores_the_store
test_login_reports_credentials_that_came_back_different

printf '\n%s assertions, %s failures\n' "${ASSERTIONS}" "${FAILURES}"

[[ "${FAILURES}" -eq 0 ]]
