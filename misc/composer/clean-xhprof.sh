#!/bin/bash

# if xhprof exists but points to a non-master branch, checkout master so composer won't fail

if [ -d "vendor/facebook/xhprof/extension" ]; then
    cd vendor/facebook/xhprof/extension

    GIT_BRANCH=$(git symbolic-ref HEAD 2>/dev/null)

    git reset --hard &> /dev/null
    git checkout master &> /dev/null
fi