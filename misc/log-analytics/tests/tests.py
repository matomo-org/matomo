# vim: et sw=4 ts=4:
import functools
import os

import import_logs

# utility functions
def add_junk_to_file(path):
    file = open(path)
    contents = file.read()
    file.close()
    
    file = open('tmp.log', 'w')
    file.write(contents + ' junk')
    file.close()
    
    return 'tmp.log'

def tearDownModule():
    if os.path.exists('tmp.log'):
        os.remove('tmp.log')

def test_format_detection():
    def _test(format_name):
        file = open('logs/%s.log' % format_name)
        format = import_logs.Parser.detect_format(file)
        assert(format is not None)
        assert(format.name == format_name)
    
    def _test_junk(format_name):
        tmp_path = add_junk_to_file('logs/%s.log' % format_name)
        
        file = open(tmp_path)
        format = import_logs.Parser.detect_format(file)
        assert(format is not None)
        assert(format.name == format_name)

    for format_name in import_logs.FORMATS.iterkeys():
        f = functools.partial(_test, format_name)
        f.description = 'Testing autodetection of format ' + format_name
        yield f
        
        f = functools.partial(_test_junk, format_name)
        f.description = 'Testing autodetection of format ' + format_name + ' w/ garbage at end of line'
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

def parse_log_file_line(format_name, file_):
    format = import_logs.FORMATS[format_name]
    
    file = open(file_)
    match = format.check_format(file)
    file.close()
    
    return format.get_all()

# check parsing groups
def check_common_groups(groups):
    assert groups['ip'] == '1.2.3.4'
    assert groups['date'] == '10/Feb/2012:16:42:07'
    assert groups['timezone'] == '-0500'
    assert groups['path'] == '/'
    assert groups['status'] == '301'
    assert groups['length'] == '368'

def check_ncsa_extended_groups(groups):
    check_common_groups(groups)
    
    assert groups['referrer'] == '-'
    assert groups['user_agent'] == 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11'

def check_common_vhost_groups(groups):
    check_common_groups(groups)
    
    assert groups['host'] == 'www.example.com'

def check_common_complete_groups(groups):
    check_ncsa_extended_groups(groups)
    
    assert groups['host'] == 'www.example.com'

def check_iis_groups(groups):
    assert groups['date'] == '2012-04-01 00:00:13'
    assert groups['path'] == '/foo/bar'
    assert groups['query_string'] == 'topCat1=divinity&submit=Search'
    assert groups['ip'] == '5.6.7.8'
    assert groups['referrer'] == '-'
    assert groups['user_agent'] == 'Mozilla/5.0+(X11;+U;+Linux+i686;+en-US;+rv:1.9.2.7)+Gecko/20100722+Firefox/3.6.7'
    assert groups['status'] == '200'
    assert groups['length'] == '27028'
    assert groups['host'] == 'example.com'
    
    expected_hit_properties = ['date', 'path', 'query_string', 'ip', 'referrer', 'user_agent',
    						   'status', 'length', 'host']
    for property_name in groups.keys():
        assert property_name in expected_hit_properties
    
def check_s3_groups(groups):
    assert groups['host'] == 'www.example.com'
    assert groups['date'] == '10/Feb/2012:16:42:07'
    assert groups['timezone'] == '-0500'
    assert groups['ip'] == '1.2.3.4'
    assert groups['path'] == '/index'
    assert groups['status'] == '200'
    assert groups['length'] == '368'
    assert groups['referrer'] == '-'
    assert groups['user_agent'] == 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11'

def check_nginx_json_groups(groups):
    assert groups['host'] == 'www.piwik.org'
    assert groups['status'] == '200'
    assert groups['ip'] == '203.38.78.246'
    assert groups['length'] == 192
    assert groups['user_agent'] == 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17'
    assert groups['date'] == '2013-10-10T16:52:00+02:00'
    
def check_match_groups(format_name, groups):
    symbols = globals()
    check_function = symbols['check_' + format_name + '_groups']
    return check_function(groups)
    
# parsing tests
def test_format_parsing():
    # test format regex parses correctly
    def _test(format_name, path):
        groups = parse_log_file_line(format_name, path)
        check_match_groups(format_name, groups)
    
    # test format regex parses correctly when there's added junk at the end of the line
    def _test_with_junk(format_name, path):
        tmp_path = add_junk_to_file(path)
        _test(format_name, tmp_path)
    
    for format_name in import_logs.FORMATS.iterkeys():
        f = functools.partial(_test, format_name, 'logs/' + format_name + '.log')
        f.description = 'Testing parsing of format "%s"' % format_name
        yield f
        
        f = functools.partial(_test_with_junk, format_name, 'logs/' + format_name + '.log')
        f.description = 'Testing parsin of format "%s" with junk appended to path' % format_name
        yield f
    
    f = functools.partial(_test, 'common', 'logs/ncsa_extended.log')
    f.description = 'Testing parsing of format "common" with ncsa_extended log'
    yield f

