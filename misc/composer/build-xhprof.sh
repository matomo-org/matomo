#!/bin/bash

if ! type phpize &> /dev/null; then
    echo "phpize missing, skipping build"
    echo "If you installed PHP via Aptitude, you can install phpize w/ 'sudo apt-get install php5-dev'"
    exit
fi

if ! type make &> /dev/null; then
    echo "make missing, skipping build"
    exit
fi

mkdir -p tmp/xhprof-logs

cd vendor/facebook/xhprof/extension

echo "Building xhprof..."

if ! phpize &> ../../../../tmp/xhprof-logs/phpize.log; then
    echo "Fatal error: phpize failed! View tmp/xhprof-logs/phpize.log for more info."
    exit 1
fi

if ! ./configure &> ../../../../tmp/xhprof-logs/configure.log; then
    echo "Fatal error: configure script failed! View tmp/xhprof-logs/configure.log for more info."
    exit 2
fi

if ! make &> ../../../../tmp/xhprof-logs/make.log; then
    echo "Fatal error: could not build extension (make failed)! View tmp/xhprof-logs/make.log for more info."
    exit 3
fi

echo "Done."