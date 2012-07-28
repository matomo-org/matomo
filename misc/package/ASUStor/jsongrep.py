#Original author: 	Terry Jones (http://blogs.fluidinfo.com/terry/about/)
#Source: 			http://blogs.fluidinfo.com/terry/2010/11/25/jsongrep-py-python-for-extracting-pieces-of-json-objects/

#!/usr/bin/env python

import sys
import re
import json
from pprint import pprint

def jsongrep(d, patterns):
    try:
        pattern = patterns.pop(0)
    except IndexError:
        pprint(d)
    else:
        if isinstance(d, dict):
            keys = filter(pattern.match, d.keys())
        elif isinstance(d, list):
            keys = map(int,
                       filter(pattern.match,
                              ['%d' % i for i in range(len(d))]))
        else:
            if pattern.match(str(d)):
                pprint(d)
            return
        for item in (d[key] for key in keys):
            jsongrep(item, patterns[:])

if __name__ == '__main__':
    try:
        j = json.loads(sys.stdin.read())
    except ValueError, e:
        print >>sys.stderr, 'Could not load JSON object from stdin.'
        sys.exit(1)

    jsongrep(j, map(re.compile, sys.argv[1:]))
