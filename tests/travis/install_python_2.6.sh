#!/usr/bin/env bash
if [ "$SKIP_INSTALL_PYTHON_26" == "1" ]; then
    echo "Skipping Python 2.6 installation."
    exit 0;
fi

SCRIPT_DIR=$( dirname "$0" )

source "$SCRIPT_DIR/travis-helper.sh"

travis_retry sudo add-apt-repository ppa:fkrull/deadsnakes -y
travis_retry sudo apt-get update > /dev/null
travis_retry sudo apt-get install python2.6 python2.6-dev -y --force-yes > /dev/null

# Log Analytics works with Python 2.6 or 2.7 but we want to test on 2.6
echo "python2.6 --version:"
python2.6 --version

echo ""

echo "python --version:"
python --version
