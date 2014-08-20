#!/bin/bash

cd vendor/facebook/xhprof/extension

echo "Building xhprof..."

git fetch origin pull/33/head:33_pull_request
git merge 33_pull_request

if ! phpize; then
    echo "Fatal error: phpize failed!"
    exit 1
fi

if ! ./configure; then
    echo "Fatal error: configure script failed!"
    exit 2
fi

if ! make; then
    echo "Fatal error: could not build extension (make failed)!"
    exit 3
fi
