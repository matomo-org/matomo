#!/bin/bash

mkdir -p tmp/xhprof-logs

cd vendor/facebook/xhprof/extension

echo "Building xhprof..."

echo "Running phpize..."
if ! phpize &> ../../../../tmp/xhprof-logs/phpize.log; then
    echo "Fatal error: phpize failed! View tmp/xhprof-logs/phpize.log for more info."
    exit 1
fi

echo "Running configure script..."
if ! ./configure &> ../../../../tmp/xhprof-logs/configure.log; then
    echo "Fatal error: configure script failed! View tmp/xhprof-logs/configure.log for more info."
    exit 2
fi

echo "Building..."
if ! make &> ../../../../tmp/xhprof-logs/make.log; then
    echo "Fatal error: could not build extension (make failed)! View tmp/xhprof-logs/make.log for more info."
    exit 3
fi

echo "Done."