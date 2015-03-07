# vim: et sw=4 ts=4:
import functools
import os
import datetime
import re

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

def add_multiple_spaces_to_file(path):
    file = open(path)
    contents = file.read()
    file.close()

    # replace spaces that aren't between " quotes
    contents = contents.split('"')
    for i in xrange(0, len(contents), 2):
        contents[i] = re.sub(' ', "  ", contents[i])
    contents = '"'.join(contents)
    import_logs.logging.debug(contents)

    assert "  " in contents # sanity check

    file = open('tmp.log', 'w')
    file.write(contents)
    file.close()

    return 'tmp.log'

def tearDownModule():
    if os.path.exists('tmp.log'):
        os.remove('tmp.log')

def test_format_detection():
    def _test(format_name, log_file = None):
        if log_file is None:
            log_file = 'logs/%s.log' % format_name

        file = open(log_file)
        import_logs.config = Config()
        format = import_logs.Parser.detect_format(file)
        assert(format is not None)
        assert(format.name == format_name)

    def _test_junk(format_name, log_file = None):
        if log_file is None:
            log_file = 'logs/%s.log' % format_name
        
        tmp_path = add_junk_to_file(log_file)

        file = open(tmp_path)
        import_logs.config = Config()
        format = import_logs.Parser.detect_format(file)
        assert(format is not None)
        assert(format.name == format_name)

    def _test_multiple_spaces(format_name, log_file = None):
        if log_file is None:
            log_file = 'logs/%s.log' % format_name

        tmp_path = add_multiple_spaces_to_file(log_file) # TODO

        file = open(tmp_path)
        import_logs.config = Config()
        format = import_logs.Parser.detect_format(file)
        assert(format is not None)
        assert(format.name == format_name)

    for format_name in import_logs.FORMATS.iterkeys():
        # w3c extended tested by iis and netscaler log files; amazon cloudfront tested later
        if format_name == 'w3c_extended' or format_name == 'amazon_cloudfront':
            continue

        f = functools.partial(_test, format_name)
        f.description = 'Testing autodetection of format ' + format_name
        yield f

        f = functools.partial(_test_junk, format_name)
        f.description = 'Testing autodetection of format ' + format_name + ' w/ garbage at end of line'
        yield f

        f = functools.partial(_test_multiple_spaces, format_name)
        f.description = 'Testing autodetection of format ' + format_name + ' when multiple spaces separate fields'
        yield f

    # add tests for amazon cloudfront (normal web + rtmp)
    f = functools.partial(_test, 'w3c_extended', 'logs/amazon_cloudfront_web.log')
    f.description = 'Testing autodetection of amazon cloudfront (web) logs.'
    yield f

    f = functools.partial(_test_junk, 'w3c_extended', 'logs/amazon_cloudfront_web.log')
    f.description = 'Testing autodetection of amazon cloudfront (web) logs w/ garbage at end of line'
    yield f

    f = functools.partial(_test_multiple_spaces, 'w3c_extended', 'logs/amazon_cloudfront_web.log')
    f.description = 'Testing autodetection of format amazon cloudfront (web) logs when multiple spaces separate fields'
    yield f

    f = functools.partial(_test, 'amazon_cloudfront', 'logs/amazon_cloudfront_rtmp.log')
    f.description = 'Testing autodetection of amazon cloudfront (rtmp) logs.'
    yield f

    f = functools.partial(_test_junk, 'amazon_cloudfront', 'logs/amazon_cloudfront_rtmp.log')
    f.description = 'Testing autodetection of amazon cloudfront (rtmp) logs w/ garbage at end of line.'
    yield f

    f = functools.partial(_test_multiple_spaces, 'amazon_cloudfront', 'logs/amazon_cloudfront_rtmp.log')
    f.description = 'Testing autodetection of format amazon cloudfront (rtmp) logs when multiple spaces separate fields'
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
    force_lowercase_path = False
    included_paths = []
    enable_http_errors = False
    download_extensions = 'doc,pdf'
    custom_w3c_fields = {}
    dump_log_regex = False
    w3c_time_taken_in_millisecs = False
    w3c_fields = None
    w3c_field_regexes = {}
    regex_group_to_visit_cvars_map = {}
    regex_group_to_page_cvars_map = {}
    regex_groups_to_ignore = None

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
    file_ = 'logs/logs_to_tests.log'

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

    import_logs.config.options.custom_w3c_fields = {}

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
                               'status', 'length', 'host', 'userid', 'generation_time_milli',
                               '__win32_status']

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

def check_icecast2_groups(groups):
    check_ncsa_extended_groups(groups)

    assert groups['session_time'] == '1807'

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
        # w3c extended tested by IIS and netscaler logs; amazon cloudfront tested individually
        if format_name == 'w3c_extended' or format_name == 'amazon_cloudfront':
            continue

        f = functools.partial(_test, format_name, 'logs/' + format_name + '.log')
        f.description = 'Testing parsing of format "%s"' % format_name
        yield f

        f = functools.partial(_test_with_junk, format_name, 'logs/' + format_name + '.log')
        f.description = 'Testing parsing of format "%s" with junk appended to path' % format_name
        yield f

    f = functools.partial(_test, 'common', 'logs/ncsa_extended.log')
    f.description = 'Testing parsing of format "common" with ncsa_extended log'
    yield f

def test_iis_custom_format():
    """test IIS custom format name parsing."""

    file_ = 'logs/iis_custom.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {
        'date-local': 'date',
        'time-local': 'time',
        'cs(Host)': 'cs-host',
        'TimeTakenMS': 'time-taken'
    }
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    # import_logs.config.options.w3c_time_taken_in_millisecs = True test that even w/o this, we get the right values
    import_logs.parser.parse(file_)

    hits = [hit.__dict__ for hit in Recorder.recorders]

    assert hits[0]['status'] == '200'
    assert hits[0]['is_error'] == False
    assert hits[0]['extension'] == u'/products/theproduct'
    assert hits[0]['is_download'] == False
    assert hits[0]['referrer'] == u'http://example.com/Search/SearchResults.pg?informationRecipient.languageCode.c=en'
    assert hits[0]['args'] == {}
    assert hits[0]['generation_time_milli'] == 109
    assert hits[0]['host'] == 'foo'
    assert hits[0]['filename'] == 'logs/iis_custom.log'
    assert hits[0]['is_redirect'] == False
    assert hits[0]['date'] == datetime.datetime(2012, 8, 15, 17, 0)
    assert hits[0]['lineno'] == 4
    assert hits[0]['ip'] == u'70.95.0.0'
    assert hits[0]['query_string'] == ''
    assert hits[0]['path'] == u'/Products/theProduct'
    assert hits[0]['is_robot'] == False
    assert hits[0]['full_path'] == u'/Products/theProduct'
    assert hits[0]['user_agent'] == u'Mozilla/5.0 (Linux; Android 4.4.4; SM-G900V Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.59 Mobile Safari/537.36'

    assert hits[1]['status'] == u'301'
    assert hits[1]['is_error'] == False
    assert hits[1]['extension'] == u'/topic/hw43061'
    assert hits[1]['is_download'] == False
    assert hits[1]['referrer'] == ''
    assert hits[1]['args'] == {}
    assert hits[1]['generation_time_milli'] == 0
    assert hits[1]['host'] == 'foo'
    assert hits[1]['filename'] == 'logs/iis_custom.log'
    assert hits[1]['is_redirect'] == True
    assert hits[1]['date'] == datetime.datetime(2012, 8, 15, 17, 0)
    assert hits[1]['lineno'] == 5
    assert hits[1]['ip'] == '70.95.32.0'
    assert hits[1]['query_string'] == ''
    assert hits[1]['path'] == u'/Topic/hw43061'
    assert hits[1]['is_robot'] == False
    assert hits[1]['full_path'] == u'/Topic/hw43061'
    assert hits[1]['user_agent'] == u'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36'

    assert hits[2]['status'] == u'404'
    assert hits[2]['is_error'] == True
    assert hits[2]['extension'] == u'/hello/world/6,681965'
    assert hits[2]['is_download'] == False
    assert hits[2]['referrer'] == ''
    assert hits[2]['args'] == {}
    assert hits[2]['generation_time_milli'] == 359
    assert hits[2]['host'] == 'foo'
    assert hits[2]['filename'] == 'logs/iis_custom.log'
    assert hits[2]['is_redirect'] == False
    assert hits[2]['date'] == datetime.datetime(2012, 8, 15, 17, 0)
    assert hits[2]['lineno'] == 6
    assert hits[2]['ip'] == u'173.5.0.0'
    assert hits[2]['query_string'] == ''
    assert hits[2]['path'] == u'/hello/world/6,681965'
    assert hits[2]['is_robot'] == False
    assert hits[2]['full_path'] == u'/hello/world/6,681965'
    assert hits[2]['user_agent'] == u'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36'

def test_netscaler_parsing():
    """test parsing of netscaler logs (which use extended W3C log format)"""

    file_ = 'logs/netscaler.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {}
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    import_logs.config.options.w3c_time_taken_in_millisecs = False
    import_logs.parser.parse(file_)

    hits = [hit.__dict__ for hit in Recorder.recorders]

    assert hits[0]['status'] == u'302'
    assert hits[0]['userid'] == None
    assert hits[0]['is_error'] == False
    assert hits[0]['extension'] == u'jsp'
    assert hits[0]['is_download'] == False
    assert hits[0]['referrer'] == ''
    assert hits[0]['args'] == {}
    assert hits[0]['generation_time_milli'] == 1000
    assert hits[0]['host'] == 'foo'
    assert hits[0]['filename'] == 'logs/netscaler.log'
    assert hits[0]['is_redirect'] == True
    assert hits[0]['date'] == datetime.datetime(2012, 8, 16, 11, 55, 13)
    assert hits[0]['lineno'] == 4
    assert hits[0]['ip'] == u'172.20.1.0'
    assert hits[0]['query_string'] == ''
    assert hits[0]['path'] == u'/Citrix/XenApp/Wan/auth/login.jsp'
    assert hits[0]['is_robot'] == False
    assert hits[0]['full_path'] == u'/Citrix/XenApp/Wan/auth/login.jsp'
    assert hits[0]['user_agent'] == u'Mozilla/4.0+(compatible;+MSIE+7.0;+Windows+NT+5.1;+Trident/4.0;+.NET+CLR+1.1.4322;+.NET+CLR+2.0.50727;+.NET+CLR+3.0.04506.648;+.NET+CLR+3.5.21022)'

def test_amazon_cloudfront_web_parsing():
    """test parsing of amazon cloudfront logs (which use extended W3C log format)"""

    file_ = 'logs/amazon_cloudfront_web.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {}
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    import_logs.config.options.w3c_time_taken_in_millisecs = False
    import_logs.parser.parse(file_)

    hits = [hit.__dict__ for hit in Recorder.recorders]

    assert hits[0]['status'] == u'200'
    assert hits[0]['userid'] == None
    assert hits[0]['is_error'] == False
    assert hits[0]['extension'] == u'html'
    assert hits[0]['is_download'] == False
    assert hits[0]['referrer'] == u'www.displaymyfiles.com'
    assert hits[0]['args'] == {}
    assert hits[0]['generation_time_milli'] == 1.0
    assert hits[0]['host'] == 'foo'
    assert hits[0]['filename'] == 'logs/amazon_cloudfront_web.log'
    assert hits[0]['is_redirect'] == False
    assert hits[0]['date'] == datetime.datetime(2014, 5, 23, 1, 13, 11)
    assert hits[0]['lineno'] == 2
    assert hits[0]['ip'] == u'192.0.2.10'
    assert hits[0]['query_string'] == ''
    assert hits[0]['path'] == u'/view/my/file.html'
    assert hits[0]['is_robot'] == False
    assert hits[0]['full_path'] == u'/view/my/file.html'
    assert hits[0]['user_agent'] == u'Mozilla/4.0%20(compatible;%20MSIE%205.0b1;%20Mac_PowerPC)'

    assert len(hits) == 1

def test_amazon_cloudfront_rtmp_parsing():
    """test parsing of amazon cloudfront rtmp logs (which use extended W3C log format w/ custom fields for event info)"""

    file_ = 'logs/amazon_cloudfront_rtmp.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {}
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    import_logs.config.options.w3c_time_taken_in_millisecs = False
    import_logs.parser.parse(file_)

    hits = [hit.__dict__ for hit in Recorder.recorders]

    assert hits[0]['is_download'] == False
    assert hits[0]['ip'] == u'192.0.2.147'
    assert hits[0]['is_redirect'] == False
    assert hits[0]['filename'] == 'logs/amazon_cloudfront_rtmp.log'
    assert hits[0]['event_category'] == 'cloudfront_rtmp'
    assert hits[0]['event_action'] == u'connect'
    assert hits[0]['lineno'] == 2
    assert hits[0]['status'] == '200'
    assert hits[0]['is_error'] == False
    assert hits[0]['event_name'] == None
    assert hits[0]['args'] == {}
    assert hits[0]['host'] == 'foo'
    assert hits[0]['date'] == datetime.datetime(2010, 3, 12, 23, 51, 20)
    assert hits[0]['path'] == u'/shqshne4jdp4b6.cloudfront.net/cfx/st\u200b'
    assert hits[0]['extension'] == u'net/cfx/st\u200b'
    assert hits[0]['referrer'] == ''
    assert hits[0]['userid'] == None
    assert hits[0]['user_agent'] == u'LNX%2010,0,32,18'
    assert hits[0]['generation_time_milli'] == 0
    assert hits[0]['query_string'] == u'key=value'
    assert hits[0]['is_robot'] == False
    assert hits[0]['full_path'] == u'/shqshne4jdp4b6.cloudfront.net/cfx/st\u200b'

    assert hits[1]['is_download'] == False
    assert hits[1]['ip'] == u'192.0.2.222'
    assert hits[1]['is_redirect'] == False
    assert hits[1]['filename'] == 'logs/amazon_cloudfront_rtmp.log'
    assert hits[1]['event_category'] == 'cloudfront_rtmp'
    assert hits[1]['event_action'] == u'play'
    assert hits[1]['lineno'] == 3
    assert hits[1]['status'] == '200'
    assert hits[1]['is_error'] == False
    assert hits[1]['event_name'] == u'myvideo'
    assert hits[1]['args'] == {}
    assert hits[1]['host'] == 'foo'
    assert hits[1]['date'] == datetime.datetime(2010, 3, 12, 23, 51, 21)
    assert hits[1]['path'] == u'/shqshne4jdp4b6.cloudfront.net/cfx/st\u200b'
    assert hits[1]['extension'] == u'net/cfx/st\u200b'
    assert hits[1]['referrer'] == ''
    assert hits[1]['userid'] == None
    assert hits[1]['length'] == 3914
    assert hits[1]['user_agent'] == u'LNX%2010,0,32,18'
    assert hits[1]['generation_time_milli'] == 0
    assert hits[1]['query_string'] == u'key=value'
    assert hits[1]['is_robot'] == False
    assert hits[1]['full_path'] == u'/shqshne4jdp4b6.cloudfront.net/cfx/st\u200b'

    assert len(hits) == 2

def test_ignore_groups_option_removes_groups():
    """Test that the --ignore-groups option removes groups so they do not appear in hits."""

    file_ = 'logs/iis.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {}
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    import_logs.config.options.w3c_time_taken_in_millisecs = True
    import_logs.config.options.regex_groups_to_ignore = set(['userid','generation_time_milli'])
    import_logs.parser.parse(file_)

    hits = [hit.__dict__ for hit in Recorder.recorders]

    assert hits[0]['userid'] == None
    assert hits[0]['generation_time_milli'] == 0

def test_regex_group_to_custom_var_options():
    """Test that the --regex-group-to-visit-cvar and --regex-group-to-page-cvar track regex groups to custom vars."""

    file_ = 'logs/iis.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {}
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    import_logs.config.options.w3c_time_taken_in_millisecs = True
    import_logs.config.options.regex_groups_to_ignore = set()
    import_logs.config.options.regex_group_to_visit_cvars_map = {
        'userid': "User Name",
        'date': "The Date"
    }
    import_logs.config.options.regex_group_to_page_cvars_map = {
        'generation_time_milli': 'Geneartion Time',
        'referrer': 'The Referrer'
    }
    import_logs.parser.parse(file_)

    hits = [hit.__dict__ for hit in Recorder.recorders]

    assert hits[0]['args']['_cvar'] == {1: ['The Date', '2012-04-01 00:00:13'], 2: ['User Name', 'theuser']} # check visit custom vars
    assert hits[0]['args']['cvar'] == {1: ['Geneartion Time', '1687']} # check page custom vars

    assert hits[0]['userid'] == 'theuser'
    assert hits[0]['date'] == datetime.datetime(2012, 4, 1, 0, 0, 13)
    assert hits[0]['generation_time_milli'] == 1687
    assert hits[0]['referrer'] == ''

def test_w3c_custom_field_regex_option():
    """Test that --w3c-field-regex can be used to match custom W3C log fields."""

    file_ = 'logs/iis.log'

    # have to override previous globals override for this test
    import_logs.config.options.custom_w3c_fields = {}
    Recorder.recorders = []
    import_logs.parser = import_logs.Parser()
    import_logs.config.format = None
    import_logs.config.options.enable_http_redirects = True
    import_logs.config.options.enable_http_errors = True
    import_logs.config.options.replay_tracking = False
    import_logs.config.options.w3c_time_taken_in_millisecs = True
    import_logs.config.options.w3c_field_regexes = {
        'sc-substatus': '(?P<substatus>\S+)',
        'sc-win32-status': '(?P<win32_status>\S+)'
    }

    format = import_logs.W3cExtendedFormat()

    file_handle = open(file_)
    format.check_format(file_handle)
    match = None
    while not match:
        line = file_handle.readline()
        if not line:
            break
        match = format.match(line)
    file_handle.close()

    assert match is not None
    assert format.get('substatus') == '654'
    assert format.get('win32_status') == '456'
