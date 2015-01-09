#!/bin/bash

# Sourced from https://github.com/travis-ci/travis-build/blob/master/lib/travis/build/script/templates/header.sh
# + Tweaked to display output and not show the status line
travis_wait() {
  local timeout=40
  local cmd="$@"
  local log_file=travis_wait_$$.log

  $cmd &
  local cmd_pid=$!

  travis_jigger $! $timeout $cmd &
  local jigger_pid=$!
  local result

  {
    wait $cmd_pid 2>/dev/null
    result=$?
    ps -p$jigger_pid &>/dev/null && kill $jigger_pid
  } || return 1

  if [ $result -eq 0 ]; then
echo -e "\n${GREEN}The command \"${TRAVIS_CMD}\" exited with $result.${RESET}"
  else
echo -e "\n${RED}The command \"${TRAVIS_CMD}\" exited with $result.${RESET}"
  fi

echo -e "\n${GREEN}Log:${RESET}\n"

  return $result
}

travis_jigger() {
  # helper method for travis_wait()
  local cmd_pid=$1
  shift
local timeout=40
  shift
local count=0

  # clear the line
  echo -e "\n"

  while [ $count -lt $timeout ]; do
count=$(($count + 1))
    #echo -ne "Still running ($count of $timeout): $@\r"

    # print invisible character
    echo -ne "\xE2\x80\x8B"
    sleep 60
  done

echo -e "\n${RED}Timeout (${timeout} minutes) reached. Terminating \"$@\"${RESET}\n"
  kill -9 $cmd_pid
}

travis_retry() {
  local result=0
  local count=1
  while [ $count -le 3 ]; do
    [ $result -ne 0 ] && {
      echo -e "\n${RED}The command \"$@\" failed. Retrying, $count of 3.${RESET}\n" >&2
    }
    "$@"
    result=$?
    [ $result -eq 0 ] && break
count=$(($count + 1))
    sleep 1
  done

  [ $count -gt 3 ] && {
    echo "\n${RED}The command \"$@\" failed 3 times.${RESET}\n" >&2
  }

  return $result
}