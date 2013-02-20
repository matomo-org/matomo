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


class Options(object):
    """Mock config options necessary to run checkers from Parser class."""
    debug = False
    encoding = 'utf-8'
    log_hostname = 'foo'
    query_string_delimiter = '?'
    piwik_token_auth = False
    piwik_url = 'http://example.com'
    recorder_max_payload_size = 200
    replay_tracking = True
    show_progress = False
    skip = False
    hostnames = []
    excluded_paths = []
    excluded_useragents = []
    enable_bots = []


class Config(object):
    """Mock configuration."""
    options = Options()
    format = import_logs.FORMATS['ncsa_extended']


class Resolver(object):
    """Mock resolver which doesn't check connection to real piwik."""
    def check_format(self, format_):
        pass


class Recorder(object):
    """Mock recorder which collects hits but doesn't put their in database."""
    recorders = []

    @classmethod
    def add_hits(cls, hits):
        cls.recorders.extend(hits)


def test_replay_tracking_arguments():
    """Test data parsing from sample log file."""
    file_ = 'logs_to_tests.log'
    import_logs.stats = import_logs.Statistics()
    import_logs.config = Config()
    import_logs.resolver = Resolver()
    import_logs.Recorder = Recorder()
    import_logs.parser = import_logs.Parser()
    import_logs.parser.parse(file_)

    hits = [hit.args for hit in import_logs.Recorder.recorders]

    assert hits[0]['_idn'] == '0'
    assert hits[0]['ag'] == '1'
    assert hits[0]['_viewts'] == '1360047661'
    assert hits[0]['urlref'] == 'http://clearcode.cc/welcome'
    assert hits[0]['_ref'] == 'http://piwik.org/thank-you-all/'
    assert hits[0]['_idts'] == '1360047661'
    assert hits[0]['java'] == '1'
    assert hits[0]['res'] == '1680x1050'
    assert hits[0]['idsite'] == '1'
    assert hits[0]['realp'] == '0'
    assert hits[0]['wma'] == '1'
    assert hits[0]['_idvc'] == '1'
    assert hits[0]['action_name'] == 'Clearcode - Web and Mobile Development | Technology With Passion'
    assert hits[0]['cookie'] == '1'
    assert hits[0]['rec'] == '1'
    assert hits[0]['qt'] == '1'
    assert hits[0]['url'] == 'http://clearcode.cc/'
    assert hits[0]['h'] == '17'
    assert hits[0]['m'] == '31'
    assert hits[0]['s'] == '25'
    assert hits[0]['r'] == '983420'
    assert hits[0]['gears'] == '0'
    assert hits[0]['fla'] == '1'
    assert hits[0]['pdf'] == '1'
    assert hits[0]['_id'] == '1da79fc743e8bcc4'
    assert hits[0]['dir'] == '1'
    assert hits[0]['_refts'] == '1360047661'

    assert hits[1]['_idn'] == '0'
    assert hits[1]['ag'] == '1'
    assert hits[1]['_viewts'] == '1360047661'
    assert hits[1]['urlref'] == 'http://clearcode.cc/welcome'
    assert hits[1]['_ref'] == 'http://piwik.org/thank-you-all/'
    assert hits[1]['_idts'] == '1360047661'
    assert hits[1]['java'] == '1'
    assert hits[1]['res'] == '1680x1050'
    assert hits[1]['idsite'] == '1'
    assert hits[1]['realp'] == '0'
    assert hits[1]['wma'] == '1'
    assert hits[1]['_idvc'] == '1'
    assert hits[1]['action_name'] == 'AdviserBrief - Track Your Investments and Plan Financial Future | Clearcode'
    assert hits[1]['cookie'] == '1'
    assert hits[1]['rec'] == '1'
    assert hits[1]['qt'] == '1'
    assert hits[1]['url'] == 'http://clearcode.cc/case/adviserbrief-track-your-investments-and-plan-financial-future/'
    assert hits[1]['h'] == '17'
    assert hits[1]['m'] == '31'
    assert hits[1]['s'] == '40'
    assert hits[1]['r'] == '109464'
    assert hits[1]['gears'] == '0'
    assert hits[1]['fla'] == '1'
    assert hits[1]['pdf'] == '1'
    assert hits[1]['_id'] == '1da79fc743e8bcc4'
    assert hits[1]['dir'] == '1'
    assert hits[1]['_refts'] == '1360047661'

    assert hits[2]['_idn'] == '0'
    assert hits[2]['ag'] == '1'
    assert hits[2]['_viewts'] == '1360047661'
    assert hits[2]['urlref'] == 'http://clearcode.cc/welcome'
    assert hits[2]['_ref'] == 'http://piwik.org/thank-you-all/'
    assert hits[2]['_idts'] == '1360047661'
    assert hits[2]['java'] == '1'
    assert hits[2]['res'] == '1680x1050'
    assert hits[2]['idsite'] == '1'
    assert hits[2]['realp'] == '0'
    assert hits[2]['wma'] == '1'
    assert hits[2]['_idvc'] == '1'
    assert hits[2]['action_name'] == 'ATL Apps - American Tailgating League Mobile Android IOS Games | Clearcode'
    assert hits[2]['cookie'] == '1'
    assert hits[2]['rec'] == '1'
    assert hits[2]['qt'] == '1'
    assert hits[2]['url'] == 'http://clearcode.cc/case/atl-apps-mobile-android-ios-games/'
    assert hits[2]['h'] == '17'
    assert hits[2]['m'] == '31'
    assert hits[2]['s'] == '46'
    assert hits[2]['r'] == '080064'
    assert hits[2]['gears'] == '0'
    assert hits[2]['fla'] == '1'
    assert hits[2]['pdf'] == '1'
    assert hits[2]['_id'] == '1da79fc743e8bcc4'
    assert hits[2]['dir'] == '1'
    assert hits[2]['_refts'] == '1360047661'
