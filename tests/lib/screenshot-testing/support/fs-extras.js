var fs = require('fs'),
    path = require('path');

fs.isDirectory = function (p) {
    try {
        return fs.statSync(p).isDirectory();
    } catch (e) {
        return false;
    }
};

fs.isFile = function (p) {
    try {
        return fs.statSync(p).isFile();
    } catch (e) {
        return false;
    }
};

fs.commonprefixLength = function (lists) {
    var l = 0;

    var rest = lists.slice(1);

    while (l < lists[0].length) {
        for (var i = 0; i != rest.length; ++i) {
            var list = rest[i];
            if (l == list.length
                || list[l] != lists[0][l]
            ) {
                return l;
            }
        }

        l += 1;
    }

    return l;
};

// This and the helper function above are essentially the python version ported to JavaScript
fs.relpath = function (p, start) {
    start_list = path.resolve(start).substring(1).split('/');
    path_list = path.resolve(p).substring(1).split('/');

    l = fs.commonprefixLength([start_list, path_list]);

    rel_list = []
    for (var i = 0; i < start_list.length - l; ++i) {
        rel_list.push('..');
    }

    rel_list.push.apply(rel_list, path_list.slice(l));

    if (rel_list.length == 0) {
        return '.';
    }

    return path.join.apply(null, rel_list)
};