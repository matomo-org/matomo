#!/usr/bin/env bash

set -euo pipefail

MATOMO_APP_ROOT="${DDEV_APPROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)}"
MATOMO_PLUGINS_DIR="${MATOMO_APP_ROOT}/plugins"
MATOMO_GENERATED_PLUGIN_MOUNTS="${MATOMO_APP_ROOT}/.ddev/docker-compose.local-plugins.yaml"
MATOMO_PLUGINS_LAST_CHANGE=0
MATOMO_PLUGINS_DDEV_PHP_VERSION=""
MATOMO_PLUGINS_DDEV_PHP_VERSION_READY=0
MATOMO_PLUGINS_DDEV_PHP_VERSION_UNAVAILABLE=0
MATOMO_PLUGINS_PHP_CHECK_RESULT="ok"
MATOMO_PLUGINS_MAP_SEPARATOR=$'\t'

matomo_plugins::map_get() {
  local map_name="${1}"
  local wanted_key="${2}"
  local entry=""
  local entry_key=""
  local entry_value=""

  eval "for entry in \"\${${map_name}[@]+\"\${${map_name}[@]}\"}\"; do
    entry_key=\${entry%%\"\${MATOMO_PLUGINS_MAP_SEPARATOR}\"*}
    if [[ \"\${entry}\" == *\"\${MATOMO_PLUGINS_MAP_SEPARATOR}\"* ]]; then
      entry_value=\${entry#*\"\${MATOMO_PLUGINS_MAP_SEPARATOR}\"}
    else
      entry_value=''
    fi
    if [[ \"\${entry_key}\" == \"\${wanted_key}\" ]]; then
      printf '%s\n' \"\${entry_value}\"
      break
    fi
  done"
}

matomo_plugins::map_set() {
  local map_name="${1}"
  local wanted_key="${2}"
  local wanted_value="${3}"
  local entry=""
  local entry_key=""
  local -a updated_entries=()

  eval "for entry in \"\${${map_name}[@]+\"\${${map_name}[@]}\"}\"; do
    entry_key=\${entry%%\"\${MATOMO_PLUGINS_MAP_SEPARATOR}\"*}
    if [[ \"\${entry_key}\" != \"\${wanted_key}\" ]]; then
      updated_entries+=(\"\${entry}\")
    fi
  done"

  updated_entries+=("${wanted_key}${MATOMO_PLUGINS_MAP_SEPARATOR}${wanted_value}")
  eval "${map_name}=(\"\${updated_entries[@]}\")"
}

matomo_plugins::map_unset() {
  local map_name="${1}"
  local wanted_key="${2}"
  local entry=""
  local entry_key=""
  local -a updated_entries=()

  eval "for entry in \"\${${map_name}[@]+\"\${${map_name}[@]}\"}\"; do
    entry_key=\${entry%%\"\${MATOMO_PLUGINS_MAP_SEPARATOR}\"*}
    if [[ \"\${entry_key}\" != \"\${wanted_key}\" ]]; then
      updated_entries+=(\"\${entry}\")
    fi
  done"

  eval "${map_name}=(\"\${updated_entries[@]}\")"
}

matomo_plugins::map_keys_sorted() {
  local map_name="${1}"
  local entry=""
  local entry_key=""

  eval "for entry in \"\${${map_name}[@]+\"\${${map_name}[@]}\"}\"; do
    entry_key=\${entry%%\"\${MATOMO_PLUGINS_MAP_SEPARATOR}\"*}
    printf '%s\n' \"\${entry_key}\"
  done" | sort
}

matomo_plugins::map_size() {
  local map_name="${1}"

  eval "printf '%s\n' \"\${#${map_name}[@]}\""
}

matomo_plugins::map_is_empty() {
  local map_name="${1}"
  local size

  size="$(matomo_plugins::map_size "${map_name}")"
  [[ "${size}" -eq 0 ]]
}

matomo_plugins::app_root() {
  printf '%s\n' "${MATOMO_APP_ROOT}"
}

matomo_plugins::generated_config() {
  printf '%s\n' "${MATOMO_GENERATED_PLUGIN_MOUNTS}"
}

matomo_plugins::normalize_path() {
  local target_path="${1}"
  if [[ ! -d "${target_path}" ]]; then
    return 1
  fi

  (cd "${target_path}" && pwd)
}

matomo_plugins::extract_name_from_plugin_json() {
  local plugin_json="${1}"

  python3 - "${plugin_json}" <<'PY'
import json
import sys

plugin_json = sys.argv[1]

try:
    with open(plugin_json, "r", encoding="utf-8") as handle:
        data = json.load(handle)
except Exception:
    sys.exit(0)

name = data.get("name")
if isinstance(name, str):
    print(name.strip())
PY
}

matomo_plugins::extract_php_requirement_from_plugin_json() {
  local plugin_json="${1}"

  python3 - "${plugin_json}" <<'PY'
import json
import sys

plugin_json = sys.argv[1]

try:
    with open(plugin_json, "r", encoding="utf-8") as handle:
        data = json.load(handle)
except Exception:
    sys.exit(0)

require = data.get("require")
if isinstance(require, dict):
    php_requirement = require.get("php")
    if isinstance(php_requirement, str):
        print(php_requirement.strip())
PY
}

matomo_plugins::infer_name_from_path() {
  local candidate_dir="${1}"
  local base_name

  base_name="$(basename "${candidate_dir}")"

  if [[ "${base_name}" == plugin-* ]]; then
    printf '%s\n' "${base_name#plugin-}"
    return 0
  fi

  return 1
}

matomo_plugins::resolve_name() {
  local candidate_dir="${1}"
  local plugin_json="${candidate_dir}/plugin.json"
  local plugin_name=""

  if [[ -f "${plugin_json}" ]]; then
    plugin_name="$(matomo_plugins::extract_name_from_plugin_json "${plugin_json}")"
  fi

  if [[ -n "${plugin_name}" ]]; then
    printf '%s\n' "${plugin_name}"
    return 0
  fi

  matomo_plugins::infer_name_from_path "${candidate_dir}"
}

matomo_plugins::find_candidate_dirs() {
  local scan_root="${1}"

  find "${scan_root}" \
    \( -name .git -o -name node_modules -o -name vendor -o -name tmp -o -name '.*' -o -path '*/plugins/*' \) -prune -o \
    \( -type d -name 'plugin-*' -print \) \
    2>/dev/null | sort -u
}

matomo_plugins::regex_matches() {
  local pattern="${1}"
  local subject="${2}"

  if printf '%s\n' "${subject}" | grep -Eq -- "${pattern}"; then
    return 0
  fi

  local grep_status=$?
  if [[ ${grep_status} -eq 2 ]]; then
    echo "Invalid regex: ${pattern}" >&2
    exit 1
  fi

  return 1
}

matomo_plugins::load_mounts() {
  local map_name="${1}"
  local config_file
  local line
  local mount_path
  local plugin_name
  config_file="$(matomo_plugins::generated_config)"
  if [[ ! -f "${config_file}" ]]; then
    return 0
  fi

  while IFS= read -r line; do
    if [[ "${line}" =~ ^[[:space:]]*-[[:space:]]*\"([^\"]+):/var/www/html/plugins/([^\"]+)\"[[:space:]]*$ ]]; then
      mount_path="${BASH_REMATCH[1]}"
      plugin_name="${BASH_REMATCH[2]}"
      matomo_plugins::map_set "${map_name}" "${plugin_name}" "${mount_path}"
    fi
  done < "${config_file}"
}

matomo_plugins::get_ddev_php_version() {
  if [[ "${MATOMO_PLUGINS_DDEV_PHP_VERSION_READY}" == "1" ]]; then
    printf '%s\n' "${MATOMO_PLUGINS_DDEV_PHP_VERSION}"
    return 0
  fi

  if [[ "${MATOMO_PLUGINS_DDEV_PHP_VERSION_UNAVAILABLE}" == "1" ]]; then
    return 1
  fi

  MATOMO_PLUGINS_DDEV_PHP_VERSION="$(ddev exec php -r 'echo PHP_VERSION;' </dev/null 2>/dev/null | tr -d '\r\n')"

  if [[ -n "${MATOMO_PLUGINS_DDEV_PHP_VERSION}" ]]; then
    MATOMO_PLUGINS_DDEV_PHP_VERSION_READY=1
    printf '%s\n' "${MATOMO_PLUGINS_DDEV_PHP_VERSION}"
    return 0
  fi

  MATOMO_PLUGINS_DDEV_PHP_VERSION_UNAVAILABLE=1
  echo "Warning: could not determine the active DDEV PHP version. PHP requirement checks will be skipped." >&2
  return 1
}

matomo_plugins::evaluate_php_requirement() {
  local actual_php_version="${1}"
  local php_requirement="${2}"

  php -r '
$version = trim($argv[1]);
$constraint = trim($argv[2]);

if ($constraint === "") {
    echo "none";
    exit(0);
}

if (preg_match("/[\\^~*|]/", $constraint)) {
    echo "complex";
    exit(0);
}

$normalized = str_replace(",", " ", $constraint);
$pattern = "/(>=|<=|!=|==|=|>|<)\\s*([0-9A-Za-z._+-]+)/";
$matched = preg_match_all($pattern, $normalized, $matches, PREG_SET_ORDER);

if (!$matched) {
    echo "complex";
    exit(0);
}

$leftovers = preg_replace([$pattern, "/[\\s,]+/"], "", $normalized);
if ($leftovers !== "") {
    echo "complex";
    exit(0);
}

foreach ($matches as $match) {
    $operator = $match[1] === "=" ? "==" : $match[1];
    if (!version_compare($version, $match[2], $operator)) {
        echo "simple_incompatible";
        exit(0);
    }
}

echo "simple_compatible";
' -- "${actual_php_version}" "${php_requirement}"
}

matomo_plugins::check_php_requirement() {
  local plugin_name="${1}"
  local plugin_path="${2}"
  local plugin_json="${plugin_path}/plugin.json"
  local php_requirement=""
  local ddev_php_version=""
  local requirement_status=""

  MATOMO_PLUGINS_PHP_CHECK_RESULT="ok"

  if [[ ! -f "${plugin_json}" ]]; then
    return 0
  fi

  php_requirement="$(matomo_plugins::extract_php_requirement_from_plugin_json "${plugin_json}")"
  if [[ -z "${php_requirement}" ]]; then
    return 0
  fi

  if ! ddev_php_version="$(matomo_plugins::get_ddev_php_version)"; then
    echo "Warning: mounting ${plugin_name} from ${plugin_path} without enforcing PHP requirement ${php_requirement}." >&2
    return 0
  fi

  requirement_status="$(matomo_plugins::evaluate_php_requirement "${ddev_php_version}" "${php_requirement}")"

  case "${requirement_status}" in
    simple_compatible)
      echo "PHP requirement OK for ${plugin_name}: ${php_requirement} (DDEV PHP ${ddev_php_version})."
      return 0
      ;;
    simple_incompatible)
      echo "Warning: skipping ${plugin_name} from ${plugin_path}; requires PHP ${php_requirement}, current DDEV PHP is ${ddev_php_version}." >&2
      MATOMO_PLUGINS_PHP_CHECK_RESULT="skip"
      return 0
      ;;
    complex)
      echo "Warning: could not strictly validate PHP requirement ${php_requirement} for ${plugin_name} against DDEV PHP ${ddev_php_version}. Mounting anyway." >&2
      return 0
      ;;
  esac

  echo "Warning: unexpected PHP requirement evaluation result for ${plugin_name}. Mounting anyway." >&2
  return 0
}

matomo_plugins::write_mounts_to_temp() {
  local map_name="${1}"
  local tmp_file="${2}"
  local plugin_name
  local plugin_path

  {
    echo "#ddev-generated"
    echo "# Generated by ddev matomo:plugins:mount / ddev matomo:plugins:unmount"
    echo "services:"
    echo "  web:"
    echo "    volumes:"

    while IFS= read -r plugin_name; do
      plugin_path="$(matomo_plugins::map_get "${map_name}" "${plugin_name}")"
      printf '      - "%s:/var/www/html/plugins/%s"\n' "${plugin_path}" "${plugin_name}"
    done < <(matomo_plugins::map_keys_sorted "${map_name}")
  } > "${tmp_file}"
}

matomo_plugins::apply_mounts() {
  local map_name="${1}"
  local action_label="${2}"
  local config_file
  local tmp_file
  local mount_count=0

  MATOMO_PLUGINS_LAST_CHANGE=0

  config_file="$(matomo_plugins::generated_config)"
  mount_count="$(matomo_plugins::map_size "${map_name}")"

  if [[ "${mount_count}" -eq 0 ]]; then
    if [[ -f "${config_file}" ]]; then
      rm -f "${config_file}"
      echo "Removed ${config_file}."
      MATOMO_PLUGINS_LAST_CHANGE=1
    else
      echo "No managed plugin mounts remain."
    fi
    return 0
  fi

  tmp_file="$(mktemp)"
  trap 'rm -f "${tmp_file}"' RETURN

  matomo_plugins::write_mounts_to_temp "${map_name}" "${tmp_file}"

  if [[ -f "${config_file}" ]] && cmp -s "${tmp_file}" "${config_file}"; then
    echo "Managed plugin mounts already up to date."
    return 0
  fi

  mv "${tmp_file}" "${config_file}"
  trap - RETURN
  echo "${action_label}: wrote ${config_file} with ${mount_count} managed mount(s)."
  MATOMO_PLUGINS_LAST_CHANGE=1
}

matomo_plugins::restart_ddev_if_needed() {
  if [[ "${MATOMO_PLUGINS_LAST_CHANGE}" != "1" ]]; then
    echo "No DDEV restart needed."
    return 0
  fi

  echo "Restarting DDEV to apply plugin mount changes ..."
  ddev restart
}

matomo_plugins::record_mount() {
  local map_name="${1}"
  local plugin_name="${2}"
  local plugin_path="${3}"
  local source_label="${4}"
  local existing_path=""

  existing_path="$(matomo_plugins::map_get "${map_name}" "${plugin_name}")"

  if [[ -n "${existing_path}" ]]; then
    if [[ "${existing_path}" == "${plugin_path}" ]]; then
      echo "Keeping ${plugin_name} mounted from ${plugin_path} (${source_label})."
      return 0
    fi

    echo "Replacing ${plugin_name}: ${existing_path} -> ${plugin_path} (${source_label})."
  else
    echo "Mounting ${plugin_name} from ${plugin_path} (${source_label})."
  fi

  matomo_plugins::map_set "${map_name}" "${plugin_name}" "${plugin_path}"
}

matomo_plugins::scan_explicit_dir() {
  local map_name="${1}"
  local explicit_dir="${2}"
  local candidate_dir
  local plugin_name
  local normalized_dir
  local source_label="path:${explicit_dir}"

  normalized_dir="$(matomo_plugins::normalize_path "${explicit_dir}")"

  if plugin_name="$(matomo_plugins::resolve_name "${normalized_dir}")"; then
    if [[ -n "${plugin_name}" ]]; then
      matomo_plugins::check_php_requirement "${plugin_name}" "${normalized_dir}"
      if [[ "${MATOMO_PLUGINS_PHP_CHECK_RESULT}" != "skip" ]]; then
        matomo_plugins::record_mount "${map_name}" "${plugin_name}" "${normalized_dir}" "${source_label}"
      fi
    fi
  fi

  while IFS= read -r candidate_dir; do
    if [[ "${candidate_dir}" == "${normalized_dir}" ]]; then
      continue
    fi

    if plugin_name="$(matomo_plugins::resolve_name "${candidate_dir}")"; then
      if [[ -n "${plugin_name}" ]]; then
        matomo_plugins::check_php_requirement "${plugin_name}" "${candidate_dir}"
        if [[ "${MATOMO_PLUGINS_PHP_CHECK_RESULT}" != "skip" ]]; then
          matomo_plugins::record_mount "${map_name}" "${plugin_name}" "${candidate_dir}" "${source_label}"
        fi
      fi
    fi
  done < <(matomo_plugins::find_candidate_dirs "${normalized_dir}")

  return 0
}

matomo_plugins::scan_regex() {
  local map_name="${1}"
  local pattern="${2}"
  shift 2
  local root
  local candidate_dir
  local plugin_name
  local matched_any=0

  for root in "$@"; do
    while IFS= read -r candidate_dir; do
      if ! matomo_plugins::regex_matches "${pattern}" "${candidate_dir}"; then
        continue
      fi

      if plugin_name="$(matomo_plugins::resolve_name "${candidate_dir}")"; then
        if [[ -n "${plugin_name}" ]]; then
          matched_any=1
          matomo_plugins::check_php_requirement "${plugin_name}" "${candidate_dir}"
          if [[ "${MATOMO_PLUGINS_PHP_CHECK_RESULT}" != "skip" ]]; then
            matomo_plugins::record_mount "${map_name}" "${plugin_name}" "${candidate_dir}" "regex:${pattern}"
          fi
        fi
      fi
    done < <(matomo_plugins::find_candidate_dirs "${root}")
  done

  if [[ "${matched_any}" -eq 0 ]]; then
    echo "Regex matched no plugin candidates: ${pattern}"
  fi

  return 0
}
