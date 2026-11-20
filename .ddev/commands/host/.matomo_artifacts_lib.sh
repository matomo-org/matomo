#!/usr/bin/env bash

set -euo pipefail

MATOMO_ARTIFACTS_HOST="builds-artifacts.matomo.org"

matomo_artifacts::require_git() {
  if ! command -v git >/dev/null 2>&1; then
    echo "git is required to read the artifacts server credentials from your credential store." >&2
    return 1
  fi
}

# The credential is whatever git resolves for the artifacts server, through whichever helper the
# developer has configured. Which helper that is stays their business, exactly as it is for git
# itself; what this library guarantees is that it never writes the password anywhere, and hands it
# to the console over STDIN rather than on a command line.
matomo_artifacts::fill_credential() {
  local credential=""

  # GIT_TERMINAL_PROMPT=0 so a missing credential fails cleanly instead of waiting on a terminal
  if ! credential="$(printf 'protocol=https\nhost=%s\n\n' "${MATOMO_ARTIFACTS_HOST}" \
    | GIT_TERMINAL_PROMPT=0 git credential fill 2>/dev/null)"; then
    return 1
  fi

  printf '%s\n' "${credential}"
}

matomo_artifacts::credential_field() {
  local credential="${1}"
  local field="${2}"

  printf '%s\n' "${credential}" | sed -n "s/^${field}=//p"
}

# every helper that could answer for this host: the general ones and any configured for this host
# alone. --get-urlmatch would collapse them to a single value, which names the wrong helper when
# more than one is configured. git config exits non-zero when nothing matches, which under `set -e`
# would abort the caller mid-assignment and swallow the explanation entirely.
matomo_artifacts::configured_helpers() {
  {
    git config --get-all credential.helper 2>/dev/null || true
    git config --get-all "credential.https://${MATOMO_ARTIFACTS_HOST}.helper" 2>/dev/null || true
  } | sed '/^[[:space:]]*$/d'
}

matomo_artifacts::explain_credential_helpers() {
  local helpers
  helpers="$(matomo_artifacts::configured_helpers)"

  # saying "no helper configured" when one is configured sends the reader after the wrong
  # problem, so report which one answered and that it kept nothing
  if [[ -n "${helpers}" ]]; then
    {
      echo "git has a credential helper configured, so the password was handed to one and not"
      echo "given back. git does not report which one answered; these could have:"
      echo
      printf '  %s\n' "${helpers}"
      cat <<'MESSAGE'

That is a helper's own problem rather than missing configuration, so check each can reach your
credential store. On Linux, git-credential-libsecret silently switches to a file backend and
stops returning passwords whenever SNAP is set, as it is in terminals started by a snap-packaged
editor.

Either fix the helper, or configure one that keeps the password in your operating system's
credential store rather than in a file:
MESSAGE
      matomo_artifacts::credential_helper_options
    } >&2
    return
  fi

  {
    cat <<'MESSAGE'
git has no credential helper configured, so there is nowhere to keep the password. Configure one
that keeps it in your operating system's credential store, rather than in a file:
MESSAGE
    matomo_artifacts::credential_helper_options
  } >&2
}

matomo_artifacts::credential_helper_options() {
  cat <<MESSAGE

  macOS    git config --global credential.helper osxkeychain
  Windows  git config --global credential.helper manager
  Linux    git config --global credential.helper libsecret

Git ships the helpers for macOS and Windows, so nothing needs installing there. On Debian and
Ubuntu the libsecret helper ships as source in /usr/share/doc/git/contrib/credential/libsecret
and has to be built once. Inside WSL2, point git at the Windows credential manager instead.
MESSAGE
}

matomo_artifacts::repository_from_args() {
  local repository=""
  local expect_value=0
  local argument=""
  local cluster=""
  local character=""
  local rest=""
  local index=0

  for argument in "$@"; do
    if [[ "${expect_value}" -eq 1 ]]; then
      repository="${argument}"
      expect_value=0
      continue
    fi

    if [[ "${argument}" == '--' ]]; then
      break
    fi

    case "${argument}" in
      --repository=*)
        repository="${argument#--repository=}"
        ;;
      --repository)
        expect_value=1
        ;;
      --*)
        ;;
      -?*)
        cluster="${argument#-}"
        index=0

        while [[ "${index}" -lt "${#cluster}" ]]; do
          character="${cluster:index:1}"
          rest="${cluster:index+1}"

          case "${character}" in
            r)
              if [[ -n "${rest}" ]]; then repository="${rest}"; else expect_value=1; fi
              break
              ;;
            p)
              # -p takes a value too, so it swallows the rest of the token
              break
              ;;
            *)
              index=$((index + 1))
              ;;
          esac
        done
        ;;
    esac
  done

  printf '%s\n' "${repository}"
}

matomo_artifacts::sync() {
  local console_command="${1}"
  shift

  local repository=""
  local credential=""
  local username=""
  local password=""
  local argument=""

  repository="$(matomo_artifacts::repository_from_args "$@")"

  # only core's own artifacts are public: matomo-org plugin repositories sit behind the same HTTP
  # auth as the premium ones
  if [[ -z "${repository}" || "${repository}" == 'matomo-org/matomo' ]]; then
    ddev matomo:console "${console_command}" "$@"
    return
  fi

  # only the credential lookup needs git, so it is not required for a public sync
  matomo_artifacts::require_git || return 1

  credential="$(matomo_artifacts::fill_credential)" || true
  username="$(matomo_artifacts::credential_field "${credential}" username)"
  password="$(matomo_artifacts::credential_field "${credential}" password)"

  if [[ -z "${username}" || -z "${password}" ]]; then
    echo "No credentials are stored for ${MATOMO_ARTIFACTS_HOST}. Run: ddev matomo:artifacts:login" >&2
    return 1
  fi

  # the credentials go before any "--", or Symfony would read them as positional arguments
  local -a before=()
  local -a after=()
  local terminator=0

  for argument in "$@"; do
    if [[ "${terminator}" -eq 0 && "${argument}" == '--' ]]; then
      terminator=1
      continue
    fi

    if [[ "${terminator}" -eq 1 ]]; then
      after+=("${argument}")
    else
      before+=("${argument}")
    fi
  done

  if [[ "${terminator}" -eq 1 ]]; then
    printf '%s' "${password}" | ddev matomo:console "${console_command}" \
      ${before[@]+"${before[@]}"} --http-user="${username}" --http-password-stdin \
      -- ${after[@]+"${after[@]}"}
    return
  fi

  printf '%s' "${password}" | ddev matomo:console "${console_command}" \
    ${before[@]+"${before[@]}"} --http-user="${username}" --http-password-stdin
}
