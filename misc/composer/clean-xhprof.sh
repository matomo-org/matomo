#!/bin/bash

# if xhprof exists but points to a non-master branch, checkout master so composer won't fail

if [ -d "vendor/lox/xhprof/extension" ]; then
    cd vendor/lox/xhprof/extension

    GIT_BRANCH=$(git symbolic-ref HEAD 2>/dev/null)

    git reset --hard &> /dev/null
    git checkout master &> /dev/null
fi
