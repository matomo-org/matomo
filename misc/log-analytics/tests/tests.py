import functools

import import_logs


def test_format_detection():
    def _test(format_name):
        file = open('logs/%s.log' % format_name)
        assert(import_logs.Parser.detect_format(file).name == format_name)

    for format_name in import_logs.FORMATS.iterkeys():
        f = functools.partial(_test, format_name)
        f.description = 'Testing autodetection of format ' + format_name
        yield f
