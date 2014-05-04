# Prevent random build failures by retrying 3 times
# Source: https://github.com/travis-ci/travis-build/blob/fc4ae8a2ffa1f2b3a2f62533bbc4f8a9be19a8ae/lib/travis/build/script/templates/header.sh#L104
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

  [ $count -eq 3 ] && {
    echo "\n${RED}The command \"$@\" failed 3 times.${RESET}\n" >&2
  }

  return $result
}

DIR=`dirname $0`
cd $DIR
travis_retry sudo apt-get -qq install python-software-properties
travis_retry sudo apt-add-repository -y ppa:chris-lea/node.js > /dev/null
travis_retry sudo apt-get -qq update
travis_retry sudo apt-get -qq install nodejs
travis_retry sudo apt-get -qq install npm
cd ..
sudo npm config set loglevel error
sudo npm install .