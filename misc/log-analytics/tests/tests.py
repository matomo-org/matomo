import functools

import import_logs


def test_format_detection():
    def _test(format):
        line = open('logs/%s.log' % format).readlines()[0]
        assert(import_logs.Parser.detect_format(line) == format)

    for format in import_logs.FORMATS.iterkeys():
        f = functools.partial(_test, format)
        f.description = 'Testing autodetection of format ' + format
        yield f
