#!/usr/bin/python
# vim: et sw=4 ts=4:
# -*- coding: utf-8 -*-
#
# Piwik - free/libre analytics platform
#
# @link http://piwik.org
# @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
# @version $Id$
#
# For more info see: http://piwik.org/log-analytics/ and http://piwik.org/docs/log-analytics-tool-how-to/
#
# Requires Python 2.6 or greater.
#

import base64
import bz2
import ConfigParser
import datetime
import fnmatch
import gzip
import hashlib
import httplib
import inspect
import itertools
import logging
import optparse
import os
import os.path
import Queue
import re
import sys
import threading
import time
import urllib
import urllib2
import urlparse
import subprocess

try:
    import json
except ImportError:
    try:
        import simplejson as json
    except ImportError:
        if sys.version_info < (2, 6):
            print >> sys.stderr, 'simplejson (http://pypi.python.org/pypi/simplejson/) is required.'
            sys.exit(1)



##
## Constants.
##

STATIC_EXTENSIONS = set((
    'gif jpg jpeg png bmp ico svg ttf eot woff class swf css js xml robots.txt'
).split())

DOWNLOAD_EXTENSIONS = set((
    '7z aac arc arj asf asx avi bin csv deb dmg doc docx exe flv gz gzip hqx '
    'jar mpg mp2 mp3 mp4 mpeg mov movie msi msp odb odf odg odp '
    'ods odt ogg ogv pdf phps ppt pptx qt qtm ra ram rar rpm sea sit tar tbz '
    'bz2 tbz tgz torrent txt wav wma wmv wpd xls xlsx xml xsd z zip '
    'azw3 epub mobi apk'
).split())

# A good source is: http://phpbb-bots.blogspot.com/
EXCLUDED_USER_AGENTS = (
    'adsbot-google',
    'ask jeeves',
    'baidubot',
    'bot-',
    'bot/',
    'ccooter/',
    'crawl',
    'curl',
    'echoping',
    'exabot',
    'feed',
    'googlebot',
    'ia_archiver',
    'java/',
    'libwww',
    'mediapartners-google',
    'msnbot',
    'netcraftsurvey',
    'panopta',
    'robot',
    'spider',
    'surveybot',
    'twiceler',
    'voilabot',
    'yahoo',
    'yandex',
)

PIWIK_MAX_ATTEMPTS = 3
PIWIK_DELAY_AFTER_FAILURE = 2

PIWIK_EXPECTED_IMAGE = base64.b64decode(
    'R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='
)

##
## Formats.
##

class BaseFormatException(Exception): pass

class BaseFormat(object):
    def __init__(self, name):
        self.name = name
        self.regex = None
        self.date_format = '%d/%b/%Y:%H:%M:%S'

    def check_format(self, file):
        line = file.readline()
        file.seek(0)
        return self.check_format_line(line)

    def check_format_line(self, line):
        return False

class JsonFormat(BaseFormat):
    def __init__(self, name):
        super(JsonFormat, self).__init__(name)
        self.json = None
        self.date_format = '%Y-%m-%dT%H:%M:%S'

    def check_format_line(self, line):
        try:
            self.json = json.loads(line)
            return True
        except:
            return False

    def match(self, line):
        try:
            self.json = json.loads(line)
            return self
        except:
            self.json = None
            return None

    def get(self, key):
        # Some ugly patchs ...
        if key == 'generation_time_milli':
            self.json[key] =  int(self.json[key] * 1000)
        # Patch date format ISO 8601
        elif key == 'date':
            tz = self.json[key][19:]
            self.json['timezone'] = tz.replace(':', '')
            self.json[key] = self.json[key][:19]

        try:
            return self.json[key]
        except KeyError:
            raise BaseFormatException()

    def get_all(self,):
        return self.json

class RegexFormat(BaseFormat):

    def __init__(self, name, regex, date_format=None):
        super(RegexFormat, self).__init__(name)
        if regex is not None:
            self.regex = re.compile(regex)
        if date_format is not None:
            self.date_format = date_format
        self.matched = None

    def check_format_line(self, line):
        return self.match(line)

    def match(self,line):
        self.matched = self.regex.match(line)
        return self.matched

    def get(self, key):
        try:
            return self.matched.group(key)
        except IndexError:
            raise BaseFormatException()

    def get_all(self,):
        return self.matched.groupdict()

class IisFormat(RegexFormat):

    def __init__(self):
        super(IisFormat, self).__init__('iis', None, '%Y-%m-%d %H:%M:%S')

    def check_format(self, file):
        line = file.readline()
        if not line.startswith('#Software: Microsoft Internet Information Services '):
            file.seek(0)
            return
        # Skip the next 2 lines.
        for i in xrange(2):
            file.readline()
        # Parse the 4th line (regex)
        full_regex = []
        line = file.readline()
        fields = {
            'date': '(?P<date>^\d+[-\d+]+',
            'time': '[\d+:]+)',
            'cs-uri-stem': '(?P<path>/\S*)',
            'cs-uri-query': '(?P<query_string>\S*)',
            'c-ip': '(?P<ip>[\d*.]*)',
            'cs(User-Agent)': '(?P<user_agent>\S+)',
            'cs(Referer)': '(?P<referrer>\S+)',
            'sc-status': '(?P<status>\d+)',
            'sc-bytes': '(?P<length>\S+)',
            'cs-host': '(?P<host>\S+)',
        }
        # Skip the 'Fields: ' prefix.
        line = line[9:]
        for field in line.split():
            try:
                regex = fields[field]
            except KeyError:
                regex = '\S+'
            full_regex.append(regex)
        self.regex = re.compile(' '.join(full_regex))

        start_pos = file.tell()
        nextline = file.readline()
        file.seek(start_pos)
        return self.check_format_line(nextline)

_HOST_PREFIX = '(?P<host>[\w\-\.]*)(?::\d+)? '
_COMMON_LOG_FORMAT = (
    '(?P<ip>\S+) \S+ \S+ \[(?P<date>.*?) (?P<timezone>.*?)\] '
    '"\S+ (?P<path>.*?) \S+" (?P<status>\S+) (?P<length>\S+)'
)
_NCSA_EXTENDED_LOG_FORMAT = (_COMMON_LOG_FORMAT +
    ' "(?P<referrer>.*?)" "(?P<user_agent>.*?)"'
)
_S3_LOG_FORMAT = (
    '\S+ (?P<host>\S+) \[(?P<date>.*?) (?P<timezone>.*?)\] (?P<ip>\S+) '
    '\S+ \S+ \S+ \S+ "\S+ (?P<path>.*?) \S+" (?P<status>\S+) \S+ (?P<length>\S+) '
    '\S+ \S+ \S+ "(?P<referrer>.*?)" "(?P<user_agent>.*?)"'
)
_ICECAST2_LOG_FORMAT = ( _NCSA_EXTENDED_LOG_FORMAT +
    ' (?P<session_time>\S+)'
)

FORMATS = {
    'common': RegexFormat('common', _COMMON_LOG_FORMAT),
    'common_vhost': RegexFormat('common_vhost', _HOST_PREFIX + _COMMON_LOG_FORMAT),
    'ncsa_extended': RegexFormat('ncsa_extended', _NCSA_EXTENDED_LOG_FORMAT),
    'common_complete': RegexFormat('common_complete', _HOST_PREFIX + _NCSA_EXTENDED_LOG_FORMAT),
    'iis': IisFormat(),
    's3': RegexFormat('s3', _S3_LOG_FORMAT),
    'icecast2': RegexFormat('icecast2', _ICECAST2_LOG_FORMAT),
    'nginx_json': JsonFormat('nginx_json'),
}

##
## Code.
##

class Configuration(object):
    """
    Stores all the configuration options by reading sys.argv and parsing,
    if needed, the config.inc.php.

    It has 2 attributes: options and filenames.
    """

    class Error(Exception):
        pass

    def _create_parser(self):
        """
        Initialize and return the OptionParser instance.
        """
        option_parser = optparse.OptionParser(
            usage='Usage: %prog [options] log_file [ log_file [...] ]',
            description="Import HTTP access logs to Piwik. "
                         "log_file is the path to a server access log file (uncompressed, .gz, .bz2, or specify - to read from stdin). "
                         " By default, the script will try to produce clean reports and will exclude bots, static files, discard http error and redirects, etc. This is customizable, see below.",
            epilog="About Piwik Server Log Analytics: http://piwik.org/log-analytics/ "
                   "              Found a bug? Please create a ticket in http://dev.piwik.org/ "
                   "              Please send your suggestions or successful user story to hello@piwik.org "
        )
        option_parser.add_option(
            '--debug', '-d', dest='debug', action='count', default=0,
            help="Enable debug output (specify multiple times for more verbose)",
        )
        option_parser.add_option(
            '--url', dest='piwik_url',
            help="REQUIRED Piwik base URL, eg. http://example.com/piwik/ or http://analytics.example.net",
        )
        option_parser.add_option(
            '--dry-run', dest='dry_run',
            action='store_true', default=False,
            help="Perform a trial run with no tracking data being inserted into Piwik",
        )
        option_parser.add_option(
            '--show-progress', dest='show_progress',
            action='store_true', default=os.isatty(sys.stdout.fileno()),
            help="Print a progress report X seconds (default: 1, use --show-progress-delay to override)"
        )
        option_parser.add_option(
            '--show-progress-delay', dest='show_progress_delay',
            type='int', default=1,
            help="Change the default progress delay"
        )
        option_parser.add_option(
            '--add-sites-new-hosts', dest='add_sites_new_hosts',
            action='store_true', default=False,
            help="When a hostname is found in the log file, but not matched to any website "
            "in Piwik, automatically create a new website in Piwik with this hostname to "
            "import the logs"
        )
        option_parser.add_option(
            '--idsite', dest='site_id',
            help= ("When specified, "
                   "data in the specified log files will be tracked for this Piwik site ID."
                   " The script will not auto-detect the website based on the log line hostname (new websites will not be automatically created).")
        )
        option_parser.add_option(
            '--idsite-fallback', dest='site_id_fallback',
            help="Default Piwik site ID to use if the hostname doesn't match any "
            "known Website's URL. New websites will not be automatically created. "
            "                         Used only if --add-sites-new-hosts or --idsite are not set",
        )
        default_config = os.path.abspath(
            os.path.join(os.path.dirname(__file__),
            '../../config/config.ini.php'),
        )
        option_parser.add_option(
            '--config', dest='config_file', default=default_config,
            help=(
                "This is only used when --login and --password is not used. "
                "Piwik will read the configuration file (default: %default) to "
                "fetch the Super User token_auth from the config file. "
            )
        )
        option_parser.add_option(
            '--login', dest='login',
            help="You can manually specify the Piwik Super User login"
        )
        option_parser.add_option(
            '--password', dest='password',
            help="You can manually specify the Piwik Super User password"
        )
        option_parser.add_option(
            '--token-auth', dest='piwik_token_auth',
            help="Piwik Super User token_auth, 32 characters hexadecimal string, found in Piwik > API",
        )

        option_parser.add_option(
            '--hostname', dest='hostnames', action='append', default=[],
            help="Accepted hostname (requests with other hostnames will be excluded). "
            "Can be specified multiple times"
        )
        option_parser.add_option(
            '--exclude-path', dest='excluded_paths', action='append', default=[],
            help="Any URL path matching this exclude-path will not be imported in Piwik. Can be specified multiple times"
        )
        option_parser.add_option(
            '--exclude-path-from', dest='exclude_path_from',
            help="Each line from this file is a path to exclude (see: --exclude-path)"
        )
        option_parser.add_option(
            '--include-path', dest='included_paths', action='append', default=[],
            help="Paths to include. Can be specified multiple times. If not specified, all paths are included."
        )
        option_parser.add_option(
            '--include-path-from', dest='include_path_from',
            help="Each line from this file is a path to include"
        )
        option_parser.add_option(
            '--useragent-exclude', dest='excluded_useragents',
            action='append', default=[],
            help="User agents to exclude (in addition to the standard excluded "
            "user agents). Can be specified multiple times",
        )
        option_parser.add_option(
            '--enable-static', dest='enable_static',
            action='store_true', default=False,
            help="Track static files (images, css, js, ico, ttf, etc.)"
        )
        option_parser.add_option(
            '--enable-bots', dest='enable_bots',
            action='store_true', default=False,
            help="Track bots. All bot visits will have a Custom Variable set with name='Bot' and value='$Bot_user_agent_here$'"
        )
        option_parser.add_option(
            '--enable-http-errors', dest='enable_http_errors',
            action='store_true', default=False,
            help="Track HTTP errors (status code 4xx or 5xx)"
        )
        option_parser.add_option(
            '--enable-http-redirects', dest='enable_http_redirects',
            action='store_true', default=False,
            help="Track HTTP redirects (status code 3xx except 304)"
        )
        option_parser.add_option(
            '--enable-reverse-dns', dest='reverse_dns',
            action='store_true', default=False,
            help="Enable reverse DNS, used to generate the 'Providers' report in Piwik. "
                 "Disabled by default, as it impacts performance"
        )
        option_parser.add_option(
            '--strip-query-string', dest='strip_query_string',
            action='store_true', default=False,
            help="Strip the query string from the URL"
        )
        option_parser.add_option(
            '--query-string-delimiter', dest='query_string_delimiter', default='?',
            help="The query string delimiter (default: %default)"
        )
        option_parser.add_option(
            '--log-format-name', dest='log_format_name', default=None,
            help=("Access log format to detect (supported are: %s). "
                  "When not specified, the log format will be autodetected by trying all supported log formats."
                  % ', '.join(sorted(FORMATS.iterkeys())))
        )
        option_parser.add_option(
            '--log-format-regex', dest='log_format_regex', default=None,
            help="Access log regular expression. For an example of a supported Regex, see the source code of this file. "
                 "Overrides --log-format-name"
        )
        option_parser.add_option(
            '--log-hostname', dest='log_hostname', default=None,
            help="Force this hostname for a log format that doesn't incldude it. All hits "
            "will seem to came to this host"
        )
        option_parser.add_option(
            '--skip', dest='skip', default=0, type='int',
            help="Skip the n first lines to start parsing/importing data at a given line for the specified log file",
        )
        option_parser.add_option(
            '--recorders', dest='recorders', default=1, type='int',
            help="Number of simultaneous recorders (default: %default). "
            "It should be set to the number of CPU cores in your server. "
            "You can also experiment with higher values which may increase performance until a certain point",
        )
        option_parser.add_option(
            '--recorder-max-payload-size', dest='recorder_max_payload_size', default=200, type='int',
            help="Maximum number of log entries to record in one tracking request (default: %default). "
        )
        option_parser.add_option(
            '--replay-tracking', dest='replay_tracking',
            action='store_true', default=False,
            help="Replay piwik.php requests found in custom logs (only piwik.php requests expected). \nSee http://piwik.org/faq/how-to/faq_17033/"
        )
        option_parser.add_option(
            '--output', dest='output',
            help="Redirect output (stdout and stderr) to the specified file"
        )
        option_parser.add_option(
            '--encoding', dest='encoding', default='utf8',
            help="Log files encoding (default: %default)"
        )
        option_parser.add_option(
            '--disable-bulk-tracking', dest='use_bulk_tracking',
            default=True, action='store_false',
            help="Disables use of bulk tracking so recorders record one hit at a time."
        )
        option_parser.add_option(
            '--debug-force-one-hit-every-Ns', dest='force_one_action_interval', default=False, type='float',
            help="Debug option that will force each recorder to record one hit every N secs."
        )
        option_parser.add_option(
            '--invalidate-dates', dest='invalidate_dates', default=None,
            help="Invalidate reports for the specified dates (format: YYYY-MM-DD,YYYY-MM-DD,...). "
                 "By default, all dates found in the logs will be invalidated.",
        )
        option_parser.add_option(
            '--force-lowercase-path', dest='force_lowercase_path', default=False, action='store_true',
            help="Make URL path lowercase so paths with the same letters but different cases are "
                 "treated the same."
        )
        option_parser.add_option(
            '--enable-testmode', dest='enable_testmode', default=False, action='store_true',
            help="If set, it will try to get the token_auth from the piwik_tests directory"
        )
        option_parser.add_option(
            '--download-extensions', dest='download_extensions', default=None,
            help="By default Piwik tracks as Downloads the most popular file extensions. If you set this parameter (format: pdf,doc,...) then files with an extension found in the list will be imported as Downloads, other file extensions downloads will be skipped."
        )
        return option_parser

    def _parse_args(self, option_parser):
        """
        Parse the command line args and create self.options and self.filenames.
        """
        self.options, self.filenames = option_parser.parse_args(sys.argv[1:])

        if self.options.output:
            sys.stdout = sys.stderr = open(self.options.output, 'a+', 0)

        if not self.filenames:
            print(option_parser.format_help())
            sys.exit(1)

        # Configure logging before calling logging.{debug,info}.
        logging.basicConfig(
            format='%(asctime)s: [%(levelname)s] %(message)s',
            level=logging.DEBUG if self.options.debug >= 1 else logging.INFO,
        )

        self.options.excluded_useragents = set([s.lower() for s in self.options.excluded_useragents])

        if self.options.exclude_path_from:
            paths = [path.strip() for path in open(self.options.exclude_path_from).readlines()]
            self.options.excluded_paths.extend(path for path in paths if len(path) > 0)
        if self.options.excluded_paths:
            self.options.excluded_paths = set(self.options.excluded_paths)
            logging.debug('Excluded paths: %s', ' '.join(self.options.excluded_paths))

        if self.options.include_path_from:
            paths = [path.strip() for path in open(self.options.include_path_from).readlines()]
            self.options.included_paths.extend(path for path in paths if len(path) > 0)
        if self.options.included_paths:
            self.options.included_paths = set(self.options.included_paths)
            logging.debug('Included paths: %s', ' '.join(self.options.included_paths))

        if self.options.hostnames:
            logging.debug('Accepted hostnames: %s', ', '.join(self.options.hostnames))
        else:
            logging.debug('Accepted hostnames: all')

        if self.options.log_format_regex:
            self.format = RegexFormat('custom', self.options.log_format_regex)
        elif self.options.log_format_name:
            try:
                self.format = FORMATS[self.options.log_format_name]
            except KeyError:
                fatal_error('invalid log format: %s' % self.options.log_format_name)
        else:
            self.format = None

        if not self.options.piwik_url:
            fatal_error('no URL given for Piwik')

        if not (self.options.piwik_url.startswith('http://') or self.options.piwik_url.startswith('https://')):
            self.options.piwik_url = 'http://' + self.options.piwik_url
        logging.debug('Piwik URL is: %s', self.options.piwik_url)

        if not self.options.piwik_token_auth:
            try:
                self.options.piwik_token_auth = self._get_token_auth()
            except Piwik.Error, e:
                fatal_error(e)
        logging.debug('Authentication token token_auth is: %s', self.options.piwik_token_auth)

        if self.options.recorders < 1:
            self.options.recorders = 1

        if self.options.download_extensions:
            self.options.download_extensions = set(self.options.download_extensions.split(','))
        else:
            self.options.download_extensions = DOWNLOAD_EXTENSIONS

    def __init__(self):
        self._parse_args(self._create_parser())

    def _get_token_auth(self):
        """
        If the token auth is not specified in the options, get it from Piwik.
        """
        # Get superuser login/password from the options.
        logging.debug('No token-auth specified')

        if self.options.login and self.options.password:
            piwik_login = self.options.login
            piwik_password = hashlib.md5(self.options.password).hexdigest()

            logging.debug('Using credentials: (login = %s, password = %s)', piwik_login, piwik_password)
            try:
                api_result = piwik.call_api('UsersManager.getTokenAuth',
                    userLogin=piwik_login,
                    md5Password=piwik_password,
                    _token_auth='',
                    _url=self.options.piwik_url,
                )
            except urllib2.URLError, e:
                fatal_error('error when fetching token_auth from the API: %s' % e)

            try:
                return api_result['value']
            except KeyError:
                # Happens when the credentials are invalid.
                message = api_result.get('message')
                fatal_error(
                    'error fetching authentication token token_auth%s' % (
                    ': %s' % message if message else '')
                )
        else:
            # Fallback to the given (or default) configuration file, then
            # get the token from the API.
            logging.debug(
                'No credentials specified, reading them from "%s"',
                self.options.config_file,
            )
            config_file = ConfigParser.RawConfigParser()
            success = len(config_file.read(self.options.config_file)) > 0
            if not success:
                fatal_error(
                    "the configuration file" + self.options.config_file + " could not be read. Please check permission. This file must be readable to get the authentication token"
                )

            updatetokenfile = os.path.abspath(
                os.path.join(os.path.dirname(__file__),
                    '../../misc/cron/updatetoken.php'),
            )

            phpBinary = 'php'

            is_windows = sys.platform.startswith('win')
            if is_windows:
                try:
                    processWin = subprocess.Popen('where php.exe', stdout=subprocess.PIPE, stderr=subprocess.PIPE)
                    [stdout, stderr] = processWin.communicate()
                    if processWin.returncode == 0:
                        phpBinary = stdout.strip()
                    else:
                        fatal_error("We couldn't detect PHP. It might help to add your php.exe to the path or alternatively run the importer using the --login and --password option")
                except:
                    fatal_error("We couldn't detect PHP. You can run the importer using the --login and --password option to fix this issue")

            command = [phpBinary, updatetokenfile]
            if self.options.enable_testmode:
                command.append('--testmode')

            command = subprocess.list2cmdline(command)
            process = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
            [stdout, stderr] = process.communicate()
            if process.returncode != 0:
                fatal_error("`" + command + "` failed with error: " + stderr + ".\nReponse code was: " + str(process.returncode) + ". You can alternatively run the importer using the --login and --password option")

            filename = stdout
            credentials = open(filename, 'r').readline()
            credentials = credentials.split('\t')
            return credentials[1]

    def get_resolver(self):
        if self.options.site_id:
            logging.debug('Resolver: static')
            return StaticResolver(self.options.site_id)
        else:
            logging.debug('Resolver: dynamic')
            return DynamicResolver()

class Statistics(object):
    """
    Store statistics about parsed logs and recorded entries.
    Can optionally print statistics on standard output every second.
    """

    class Counter(object):
        """
        Simple integers cannot be used by multithreaded programs. See:
        http://stackoverflow.com/questions/6320107/are-python-ints-thread-safe
        """
        def __init__(self):
            # itertools.count's implementation in C does not release the GIL and
            # therefore is thread-safe.
            self.counter = itertools.count(1)
            self.value = 0

        def increment(self):
            self.value = self.counter.next()

        def advance(self, n):
            for i in range(n):
                self.increment()

        def __str__(self):
            return str(int(self.value))

    def __init__(self):
        self.time_start = None
        self.time_stop = None

        self.piwik_sites = set()                # sites ID
        self.piwik_sites_created = []           # (hostname, site ID)
        self.piwik_sites_ignored = set()        # hostname

        self.count_lines_parsed = self.Counter()
        self.count_lines_recorded = self.Counter()

        # Do not match the regexp.
        self.count_lines_invalid = self.Counter()
        # No site ID found by the resolver.
        self.count_lines_no_site = self.Counter()
        # Hostname filtered by config.options.hostnames
        self.count_lines_hostname_skipped = self.Counter()
        # Static files.
        self.count_lines_static = self.Counter()
        # Ignored user-agents.
        self.count_lines_skipped_user_agent = self.Counter()
        # Ignored HTTP erors.
        self.count_lines_skipped_http_errors = self.Counter()
        # Ignored HTTP redirects.
        self.count_lines_skipped_http_redirects = self.Counter()
        # Downloads
        self.count_lines_downloads = self.Counter()
        # Ignored downloads when --download-extensions is used
        self.count_lines_skipped_downloads = self.Counter()

        # Misc
        self.dates_recorded = set()
        self.monitor_stop = False

    def set_time_start(self):
        self.time_start = time.time()

    def set_time_stop(self):
        self.time_stop = time.time()

    def _compute_speed(self, value, start, end):
        delta_time = end - start
        if value == 0:
            return 0
        if delta_time == 0:
            return 'very high!'
        else:
            return value / delta_time

    def _round_value(self, value, base=100):
        return round(value * base) / base

    def _indent_text(self, lines, level=1):
        """
        Return an indented text. 'lines' can be a list of lines or a single
        line (as a string). One level of indentation is 4 spaces.
        """
        prefix = ' ' * (4 * level)
        if isinstance(lines, basestring):
            return prefix + lines
        else:
            return '\n'.join(
                prefix + line
                for line in lines
            )

    def print_summary(self):
        print '''
Logs import summary
-------------------

    %(count_lines_recorded)d requests imported successfully
    %(count_lines_downloads)d requests were downloads
    %(total_lines_ignored)d requests ignored:
        %(count_lines_skipped_http_errors)d HTTP errors
        %(count_lines_skipped_http_redirects)d HTTP redirects
        %(count_lines_invalid)d invalid log lines
        %(count_lines_no_site)d requests did not match any known site
        %(count_lines_hostname_skipped)d requests did not match any --hostname
        %(count_lines_skipped_user_agent)d requests done by bots, search engines...
        %(count_lines_static)d requests to static resources (css, js, images, ico, ttf...)
        %(count_lines_skipped_downloads)d requests to file downloads did not match any --download-extensions

Website import summary
----------------------

    %(count_lines_recorded)d requests imported to %(total_sites)d sites
        %(total_sites_existing)d sites already existed
        %(total_sites_created)d sites were created:
%(sites_created)s
    %(total_sites_ignored)d distinct hostnames did not match any existing site:
%(sites_ignored)s
%(sites_ignored_tips)s

Performance summary
-------------------

    Total time: %(total_time)d seconds
    Requests imported per second: %(speed_recording)s requests per second
''' % {

    'count_lines_recorded': self.count_lines_recorded.value,
    'count_lines_downloads': self.count_lines_downloads.value,
    'total_lines_ignored': sum([
            self.count_lines_invalid.value,
            self.count_lines_skipped_user_agent.value,
            self.count_lines_skipped_http_errors.value,
            self.count_lines_skipped_http_redirects.value,
            self.count_lines_static.value,
            self.count_lines_skipped_downloads.value,
            self.count_lines_no_site.value,
            self.count_lines_hostname_skipped.value,
        ]),
    'count_lines_invalid': self.count_lines_invalid.value,
    'count_lines_skipped_user_agent': self.count_lines_skipped_user_agent.value,
    'count_lines_skipped_http_errors': self.count_lines_skipped_http_errors.value,
    'count_lines_skipped_http_redirects': self.count_lines_skipped_http_redirects.value,
    'count_lines_static': self.count_lines_static.value,
    'count_lines_skipped_downloads': self.count_lines_skipped_downloads.value,
    'count_lines_no_site': self.count_lines_no_site.value,
    'count_lines_hostname_skipped': self.count_lines_hostname_skipped.value,
    'total_sites': len(self.piwik_sites),
    'total_sites_existing': len(self.piwik_sites - set(site_id for hostname, site_id in self.piwik_sites_created)),
    'total_sites_created': len(self.piwik_sites_created),
    'sites_created': self._indent_text(
            ['%s (ID: %d)' % (hostname, site_id) for hostname, site_id in self.piwik_sites_created],
            level=3,
        ),
    'total_sites_ignored': len(self.piwik_sites_ignored),
    'sites_ignored': self._indent_text(
            self.piwik_sites_ignored, level=3,
        ),
    'sites_ignored_tips': '''
        TIPs:
         - if one of these hosts is an alias host for one of the websites
           in Piwik, you can add this host as an "Alias URL" in Settings > Websites.
         - use --add-sites-new-hosts if you wish to automatically create
           one website for each of these hosts in Piwik rather than discarding
           these requests.
         - use --idsite-fallback to force all these log lines with a new hostname
           to be recorded in a specific idsite (for example for troubleshooting/visualizing the data)
         - use --idsite to force all lines in the specified log files
           to be all recorded in the specified idsite
         - or you can also manually create a new Website in Piwik with the URL set to this hostname
''' if self.piwik_sites_ignored else '',
    'total_time': self.time_stop - self.time_start,
    'speed_recording': self._round_value(self._compute_speed(
            self.count_lines_recorded.value,
            self.time_start, self.time_stop,
        )),
}

    ##
    ## The monitor is a thread that prints a short summary each second.
    ##

    def _monitor(self):
        latest_total_recorded = 0
        while not self.monitor_stop:
            current_total = stats.count_lines_recorded.value
            time_elapsed = time.time() - self.time_start
            print '%d lines parsed, %d lines recorded, %d records/sec (avg), %d records/sec (current)' % (
                stats.count_lines_parsed.value,
                current_total,
                current_total / time_elapsed if time_elapsed != 0 else 0,
                (current_total - latest_total_recorded) / config.options.show_progress_delay,
            )
            latest_total_recorded = current_total
            time.sleep(config.options.show_progress_delay)

    def start_monitor(self):
        t = threading.Thread(target=self._monitor)
        t.daemon = True
        t.start()

    def stop_monitor(self):
        self.monitor_stop = True

class Piwik(object):
    """
    Make requests to Piwik.
    """

    class Error(Exception):
        pass

    @staticmethod
    def _call(path, args, headers=None, url=None, data=None):
        """
        Make a request to the Piwik site. It is up to the caller to format
        arguments, to embed authentication, etc.
        """
        if url is None:
            url = config.options.piwik_url
        headers = headers or {}

        if data is None:
            # If Content-Type isn't defined, PHP do not parse the request's body.
            headers['Content-type'] = 'application/x-www-form-urlencoded'
            data = urllib.urlencode(args)
        elif not isinstance(data, basestring) and headers['Content-type'] == 'application/json':
            data = json.dumps(data)

        headers['User-Agent'] = 'Piwik/LogImport'
        request = urllib2.Request(url + path, data, headers)
        response = urllib2.urlopen(request)
        result = response.read()
        response.close()
        return result

    @staticmethod
    def _call_api(method, **kwargs):
        """
        Make a request to the Piwik API taking care of authentication, body
        formatting, etc.
        """
        args = {
            'module' : 'API',
            'format' : 'json2',
            'method' : method,
        }
        # token_auth, by default, is taken from config.
        token_auth = kwargs.pop('_token_auth', None)
        if token_auth is None:
            token_auth = config.options.piwik_token_auth
        if token_auth:
            args['token_auth'] = token_auth

        url = kwargs.pop('_url', None)

        if kwargs:
            args.update(kwargs)

        # Convert lists into appropriate format.
        # See: http://developer.piwik.org/api-reference/reporting-api#passing-an-array-of-data-as-a-parameter
        # Warning: we have to pass the parameters in order: foo[0], foo[1], foo[2]
        # and not foo[1], foo[0], foo[2] (it will break Piwik otherwise.)
        final_args = []
        for key, value in args.iteritems():
            if isinstance(value, (list, tuple)):
                for index, obj in enumerate(value):
                    final_args.append(('%s[%d]' % (key, index), obj))
            else:
                final_args.append((key, value))
        res = Piwik._call('/', final_args, url=url)
        try:
            return json.loads(res)
        except ValueError:
            truncate_after = 4000
            raise urllib2.URLError('Piwik returned an invalid response: ' + res[:truncate_after])

    @staticmethod
    def _call_wrapper(func, expected_response, on_failure, *args, **kwargs):
        """
        Try to make requests to Piwik at most PIWIK_FAILURE_MAX_RETRY times.
        """
        errors = 0
        while True:
            try:
                response = func(*args, **kwargs)
                if expected_response is not None and response != expected_response:
                    if on_failure is not None:
                        error_message = on_failure(response, kwargs.get('data'))
                    else:
                        truncate_after = 4000
                        truncated_response = (response[:truncate_after] + '..') if len(response) > truncate_after else response
                        error_message = "didn't receive the expected response. Response was %s " % truncated_response

                    raise urllib2.URLError(error_message)
                return response
            except (urllib2.URLError, httplib.HTTPException, ValueError), e:
                logging.debug('Error when connecting to Piwik: %s', e)
                errors += 1
                if errors == PIWIK_MAX_ATTEMPTS:
                    if isinstance(e, urllib2.HTTPError):
                        # See Python issue 13211.
                        message = e.msg
                    elif isinstance(e, urllib2.URLError):
                        message = e.reason
                    else:
                        message = str(e)
                    raise Piwik.Error(message)
                else:
                    time.sleep(PIWIK_DELAY_AFTER_FAILURE)

    @classmethod
    def call(cls, path, args, expected_content=None, headers=None, data=None, on_failure=None):
        return cls._call_wrapper(cls._call, expected_content, on_failure, path, args, headers,
                                    data=data)

    @classmethod
    def call_api(cls, method, **kwargs):
        return cls._call_wrapper(cls._call_api, None, None, method, **kwargs)

##
## Resolvers.
##
## A resolver is a class that turns a hostname into a Piwik site ID.
##

class StaticResolver(object):
    """
    Always return the same site ID, specified in the configuration.
    """

    def __init__(self, site_id):
        self.site_id = site_id
        # Go get the main URL
        site = piwik.call_api(
            'SitesManager.getSiteFromId', idSite=self.site_id
        )
        if site.get('result') == 'error':
            fatal_error(
                "cannot get the main URL of this site: %s" % site.get('message')
            )
        self._main_url = site['main_url']
        stats.piwik_sites.add(self.site_id)

    def resolve(self, hit):
        return (self.site_id, self._main_url)

    def check_format(self, format):
        pass

class DynamicResolver(object):
    """
    Use Piwik API to determine the site ID.
    """

    _add_site_lock = threading.Lock()

    def __init__(self):
        self._cache = {}
        if config.options.replay_tracking:
            # get existing sites
            self._cache['sites'] = piwik.call_api('SitesManager.getAllSites')

    def _get_site_id_from_hit_host(self, hit):
        main_url = 'http://' + hit.host
        return piwik.call_api(
            'SitesManager.getSitesIdFromSiteUrl',
            url=main_url,
        )

    def _add_site(self, hit):
        main_url = 'http://' + hit.host
        DynamicResolver._add_site_lock.acquire()

        try:
            # After we obtain the lock, make sure the site hasn't already been created.
            res = self._get_site_id_from_hit_host(hit)
            if res:
                return res[0]['idsite']

            # The site doesn't exist.
            logging.debug('No Piwik site found for the hostname: %s', hit.host)
            if config.options.site_id_fallback is not None:
                logging.debug('Using default site for hostname: %s', hit.host)
                return config.options.site_id_fallback
            elif config.options.add_sites_new_hosts:
                if config.options.dry_run:
                    # Let's just return a fake ID.
                    return 0
                logging.debug('Creating a Piwik site for hostname %s', hit.host)
                result = piwik.call_api(
                    'SitesManager.addSite',
                    siteName=hit.host,
                    urls=[main_url],
                )
                if result.get('result') == 'error':
                    logging.error("Couldn't create a Piwik site for host %s: %s",
                        hit.host, result.get('message'),
                    )
                    return None
                else:
                    site_id = result['value']
                    stats.piwik_sites_created.append((hit.host, site_id))
                    return site_id
            else:
                # The site doesn't exist, we don't want to create new sites and
                # there's no default site ID. We thus have to ignore this hit.
                return None
        finally:
            DynamicResolver._add_site_lock.release()

    def _resolve(self, hit):
        res = self._get_site_id_from_hit_host(hit)
        if res:
            # The site already exists.
            site_id = res[0]['idsite']
        else:
            site_id = self._add_site(hit)
        if site_id is not None:
            stats.piwik_sites.add(site_id)
        return site_id

    def _resolve_when_replay_tracking(self, hit):
        """
        If parsed site ID found in the _cache['sites'] return site ID and main_url,
        otherwise return (None, None) tuple.
        """
        site_id = hit.args['idsite']
        if site_id in self._cache['sites']:
            stats.piwik_sites.add(site_id)
            return (site_id, self._cache['sites'][site_id]['main_url'])
        else:
            return (None, None)

    def _resolve_by_host(self, hit):
        """
        Returns the site ID and site URL for a hit based on the hostname.
        """
        try:
            site_id = self._cache[hit.host]
        except KeyError:
            logging.debug(
                'Site ID for hostname %s not in cache', hit.host
            )
            site_id = self._resolve(hit)
            logging.debug('Site ID for hostname %s: %s', hit.host, site_id)
            self._cache[hit.host] = site_id
        return (site_id, 'http://' + hit.host)

    def resolve(self, hit):
        """
        Return the site ID from the cache if found, otherwise call _resolve.
        If replay_tracking option is enabled, call _resolve_when_replay_tracking.
        """
        if config.options.replay_tracking:
            # We only consider requests with piwik.php which don't need host to be imported
            return self._resolve_when_replay_tracking(hit)
        else:
            return self._resolve_by_host(hit)

    def check_format(self, format):
        if config.options.replay_tracking:
            pass
        elif 'host' not in format.regex.groupindex and not config.options.log_hostname:
            fatal_error(
                "the selected log format doesn't include the hostname: you must "
                "specify the Piwik site ID with the --idsite argument"
            )

class Recorder(object):
    """
    A Recorder fetches hits from the Queue and inserts them into Piwik using
    the API.
    """

    recorders = []

    def __init__(self):
        self.queue = Queue.Queue(maxsize=2)

        # if bulk tracking disabled, make sure we can store hits outside of the Queue
        if not config.options.use_bulk_tracking:
            self.unrecorded_hits = []

    @classmethod
    def launch(cls, recorder_count):
        """
        Launch a bunch of Recorder objects in a separate thread.
        """
        for i in xrange(recorder_count):
            recorder = Recorder()
            cls.recorders.append(recorder)

            run = recorder._run_bulk if config.options.use_bulk_tracking else recorder._run_single
            t = threading.Thread(target=run)

            t.daemon = True
            t.start()
            logging.debug('Launched recorder')

    @classmethod
    def add_hits(cls, all_hits):
        """
        Add a set of hits to the recorders queue.
        """
        # Organize hits so that one client IP will always use the same queue.
        # We have to do this so visits from the same IP will be added in the right order.
        hits_by_client = [[] for r in cls.recorders]
        for hit in all_hits:
            hits_by_client[abs(hash(hit.ip)) % len(cls.recorders)].append(hit)

        for i, recorder in enumerate(cls.recorders):
            recorder.queue.put(hits_by_client[i])

    @classmethod
    def wait_empty(cls):
        """
        Wait until all recorders have an empty queue.
        """
        for recorder in cls.recorders:
            recorder._wait_empty()

    def _run_bulk(self):
        while True:
            hits = self.queue.get()
            if len(hits) > 0:
                try:
                    self._record_hits(hits)
                except Piwik.Error, e:
                    fatal_error(e, hits[0].filename, hits[0].lineno) # approximate location of error
            self.queue.task_done()

    def _run_single(self):
        while True:
            if config.options.force_one_action_interval != False:
                time.sleep(config.options.force_one_action_interval)

            if len(self.unrecorded_hits) > 0:
                hit = self.unrecorded_hits.pop(0)

                try:
                    self._record_hits([hit])
                except Piwik.Error, e:
                    fatal_error(e, hit.filename, hit.lineno)
            else:
                self.unrecorded_hits = self.queue.get()
                self.queue.task_done()

    def _wait_empty(self):
        """
        Wait until the queue is empty.
        """
        while True:
            if self.queue.empty():
                # We still have to wait for the last queue item being processed
                # (queue.empty() returns True before queue.task_done() is
                # called).
                self.queue.join()
                return
            time.sleep(1)

    def date_to_piwik(self, date):
        date, time = date.isoformat(sep=' ').split()
        return '%s %s' % (date, time.replace('-', ':'))

    def _get_hit_args(self, hit):
        """
        Returns the args used in tracking a hit, without the token_auth.
        """
        site_id, main_url = resolver.resolve(hit)
        if site_id is None:
            # This hit doesn't match any known Piwik site.
            if config.options.replay_tracking:
                stats.piwik_sites_ignored.add('unrecognized site ID %s' % hit.args.get('idsite'))
            else:
                stats.piwik_sites_ignored.add(hit.host)
            stats.count_lines_no_site.increment()
            return

        stats.dates_recorded.add(hit.date.date())

        path = hit.path
        if hit.query_string and not config.options.strip_query_string:
            path += config.options.query_string_delimiter + hit.query_string

        # only prepend main url if it's a path
        url = (main_url if path.startswith('/') else '') + path[:1024]

        args = {
            'rec': '1',
            'apiv': '1',
            'url': url.encode('utf8'),
            'urlref': hit.referrer[:1024].encode('utf8'),
            'cip': hit.ip,
            'cdt': self.date_to_piwik(hit.date),
            'idsite': site_id,
            'dp': '0' if config.options.reverse_dns else '1',
            'ua': hit.user_agent.encode('utf8'),
        }
        if config.options.replay_tracking:
            # prevent request to be force recorded when option replay-tracking
            args['rec'] = '0'

        args.update(hit.args)

        if hit.is_download:
            args['download'] = args['url']

        if config.options.enable_bots:
            args['bots'] = '1'
            if hit.is_robot:
                args['_cvar'] = '{"1":["Bot","%s"]}' % hit.user_agent
            else:
                args['_cvar'] = '{"1":["Not-Bot","%s"]}' % hit.user_agent

        # do not overwrite custom variables if it's already set (eg. when replaying ecommerce logs)
        if 'cvar' not in args:
            args['cvar'] = '{"1":["HTTP-code","%s"]}' % hit.status

        if hit.is_error or hit.is_redirect:
			args['action_name'] = '%s/URL = %s%s' % (
				hit.status,
				urllib.quote(args['url'], ''),
				("/From = %s" % urllib.quote(args['urlref'], '') if args['urlref'] != ''  else '')
			)

        if hit.generation_time_milli > 0:
            args['gt_ms'] = hit.generation_time_milli
        return args

    def _record_hits(self, hits):
        """
        Inserts several hits into Piwik.
        """
        if not config.options.dry_run:
            data = {
                'token_auth': config.options.piwik_token_auth,
                'requests': [self._get_hit_args(hit) for hit in hits]
            }
            piwik.call(
                '/piwik.php', args={},
                expected_content=None,
                headers={'Content-type': 'application/json'},
                data=data,
                on_failure=self._on_tracking_failure
            )
        stats.count_lines_recorded.advance(len(hits))

    def _on_tracking_failure(self, response, data):
        """
        Removes the successfully tracked hits from the request payload so
        they are not logged twice.
        """
        try:
            response = json.loads(response)
        except:
            # the response should be in JSON, but in case it can't be parsed just try another attempt
            logging.debug("cannot parse tracker response, should be valid JSON")
            return response

        # remove the successfully tracked hits from payload
        tracked = response['tracked']
        data['requests'] = data['requests'][tracked:]

        return response['message']

    @staticmethod
    def invalidate_reports():
        if config.options.dry_run or not stats.dates_recorded:
            return

        if config.options.invalidate_dates is not None:
            dates = [date for date in config.options.invalidate_dates.split(',') if date]
        else:
            dates = [date.strftime('%Y-%m-%d') for date in stats.dates_recorded]
        if dates:
            print '\nPurging Piwik archives for dates: ' + ' '.join(dates)
            result = piwik.call_api(
                'CoreAdminHome.invalidateArchivedReports',
                dates=','.join(dates),
                idSites=','.join(str(site_id) for site_id in stats.piwik_sites),
            )
            print('\nTo re-process these reports with your newly imported data, execute the following command: \n'
                  '$ /path/to/piwik/console core:archive --url=http://example/piwik --force-all-websites --force-all-periods=315576000 --force-date-last-n=1000'
                  '\nReference: http://piwik.org/docs/setup-auto-archiving/ ')

class Hit(object):
    """
    It's a simple container.
    """
    def __init__(self, **kwargs):
        for key, value in kwargs.iteritems():
            setattr(self, key, value)
        super(Hit, self).__init__()

        if config.options.force_lowercase_path:
            self.full_path = self.full_path.lower()

class Parser(object):
    """
    The Parser parses the lines in a specified file and inserts them into
    a Queue.
    """

    def __init__(self):
        self.check_methods = [method for name, method
                              in inspect.getmembers(self, predicate=inspect.ismethod)
                              if name.startswith('check_')]

    ## All check_* methods are called for each hit and must return True if the
    ## hit can be imported, False otherwise.

    def check_hostname(self, hit):
        # Check against config.hostnames.
        if not hasattr(hit, 'host') or not config.options.hostnames:
            return True

        # Accept the hostname only if it matches one pattern in the list.
        result = any(
            fnmatch.fnmatch(hit.host, pattern)
            for pattern in config.options.hostnames
        )
        if not result:
            stats.count_lines_hostname_skipped.increment()
        return result

    def check_static(self, hit):
        if hit.extension in STATIC_EXTENSIONS:
            if config.options.enable_static:
                hit.is_download = True
                return True
            else:
                stats.count_lines_static.increment()
                return False
        return True

    def check_download(self, hit):
        if hit.extension in config.options.download_extensions:
            stats.count_lines_downloads.increment()
            hit.is_download = True
            return True
        # the file is not in the white-listed downloads
        # if it's a know download file, we shall skip it
        elif hit.extension in DOWNLOAD_EXTENSIONS:
            stats.count_lines_skipped_downloads.increment()
            return False
        return True

    def check_user_agent(self, hit):
        user_agent = hit.user_agent.lower()
        for s in itertools.chain(EXCLUDED_USER_AGENTS, config.options.excluded_useragents):
            if s in user_agent:
                if config.options.enable_bots:
                    hit.is_robot = True
                    return True
                else:
                    stats.count_lines_skipped_user_agent.increment()
                    return False
        return True

    def check_http_error(self, hit):
        if hit.status[0] in ('4', '5'):
            if config.options.enable_http_errors:
                hit.is_error = True
                return True
            else:
                stats.count_lines_skipped_http_errors.increment()
                return False
        return True

    def check_http_redirect(self, hit):
        if hit.status[0] == '3' and hit.status != '304':
            if config.options.enable_http_redirects:
                hit.is_redirect = True
                return True
            else:
                stats.count_lines_skipped_http_redirects.increment()
                return False
        return True

    def check_path(self, hit):
        for excluded_path in config.options.excluded_paths:
            if fnmatch.fnmatch(hit.path, excluded_path):
                return False
        # By default, all paths are included.
        if config.options.included_paths:
           for included_path in config.options.included_paths:
               if fnmatch.fnmatch(hit.path, included_path):
                   return True
           return False
        return True

    @staticmethod
    def check_format(lineOrFile):
        format = False
        format_groups = 0
        for name, candidate_format in FORMATS.iteritems():
            logging.debug("Check format %s", name)

            match = None
            try:
                if isinstance(lineOrFile, basestring):
                    match = candidate_format.check_format_line(lineOrFile)
                else:
                    match = candidate_format.check_format(lineOrFile)
            except:
                pass

            if match:
                logging.debug('Format %s matches', name)

                # compare format groups if this *BaseFormat has groups() method
                try:
                    # if there's more info in this match, use this format
                    match_groups = len(match.groups())
                    if format_groups < match_groups:
                        format = candidate_format
                        format_groups = match_groups
                except AttributeError:
                    format = candidate_format

            else:
                logging.debug('Format %s does not match', name)

        return format

    @staticmethod
    def detect_format(file):
        """
        Return the best matching format for this file, or None if none was found.
        """
        logging.debug('Detecting the log format')

        format = False

        # check the format using the file (for formats like the IIS one)
        format = Parser.check_format(file)

        # check the format using the first N lines (to avoid irregular ones)
        lineno = 0
        limit = 100000
        while not format and lineno < limit:
            line = file.readline()
            lineno = lineno + 1

            logging.debug("Detecting format against line %i" % lineno)
            format = Parser.check_format(line)

        try:
            file.seek(0)
        except IOError:
            pass

        if not format:
            fatal_error("cannot automatically determine the log format using the first %d lines of the log file. " % limit +
                        "\nMaybe try specifying the format with the --log-format-name command line argument." )
            return

        logging.debug('Format %s is the best match', format.name)
        return format

    def parse(self, filename):
        """
        Parse the specified filename and insert hits in the queue.
        """
        def invalid_line(line, reason):
            stats.count_lines_invalid.increment()
            if config.options.debug >= 2:
                logging.debug('Invalid line detected (%s): %s' % (reason, line))

        if filename == '-':
            filename = '(stdin)'
            file = sys.stdin
        else:
            if not os.path.exists(filename):
                print >> sys.stderr, 'File %s does not exist' % filename
                return
            else:
                if filename.endswith('.bz2'):
                    open_func = bz2.BZ2File
                elif filename.endswith('.gz'):
                    open_func = gzip.open
                else:
                    open_func = open
                file = open_func(filename, 'r')

        if config.options.show_progress:
            print 'Parsing log %s...' % filename

        if config.format:
            # The format was explicitely specified.
            format = config.format
        else:
            # If the file is empty, don't bother.
            data = file.read(100)
            if len(data.strip()) == 0:
                return
            try:
                file.seek(0)
            except IOError:
                pass

            format = self.detect_format(file)
            if format is None:
                return fatal_error(
                    'Cannot guess the logs format. Please give one using '
                    'either the --log-format-name or --log-format-regex option'
                )
        # Make sure the format is compatible with the resolver.
        resolver.check_format(format)

        hits = []
        for lineno, line in enumerate(file):
            try:
                line = line.decode(config.options.encoding)
            except UnicodeDecodeError:
                invalid_line(line, 'invalid encoding')
                continue

            stats.count_lines_parsed.increment()
            if stats.count_lines_parsed.value <= config.options.skip:
                continue

            match = format.match(line)
            if not match:
                invalid_line(line, 'line did not match')
                continue

            hit = Hit(
                filename=filename,
                lineno=lineno,
                status=format.get('status'),
                full_path=format.get('path'),
                is_download=False,
                is_robot=False,
                is_error=False,
                is_redirect=False,
                args={},
            )

            try:
                hit.query_string = format.get('query_string')
                hit.path = hit.full_path
            except BaseFormatException:
                hit.path, _, hit.query_string = hit.full_path.partition(config.options.query_string_delimiter)

            hit.extension = hit.path.rsplit('.')[-1].lower()

            try:
                hit.referrer = format.get('referrer')
            except BaseFormatException:
                hit.referrer = ''
            if hit.referrer == '-':
                hit.referrer = ''

            try:
                hit.user_agent = format.get('user_agent')
            except BaseFormatException:
                hit.user_agent = ''

            hit.ip = format.get('ip')
            try:
                hit.length = int(format.get('length'))
            except (ValueError, BaseFormatException):
                # Some lines or formats don't have a length (e.g. 304 redirects, IIS logs)
                hit.length = 0

            try:
                hit.generation_time_milli = int(format.get('generation_time_milli'))
            except BaseFormatException:
                try:
                    hit.generation_time_milli = int(format.get('generation_time_micro')) / 1000
                except BaseFormatException:
                    hit.generation_time_milli = 0

            if config.options.log_hostname:
                hit.host = config.options.log_hostname
            else:
                try:
                    hit.host = format.get('host').lower().strip('.')
                except BaseFormatException:
                    # Some formats have no host.
                    pass

            # Check if the hit must be excluded.
            if not all((method(hit) for method in self.check_methods)):
                continue

            # Parse date.
            # We parse it after calling check_methods as it's quite CPU hungry, and
            # we want to avoid that cost for excluded hits.
            date_string = format.get('date')
            try:
                hit.date = datetime.datetime.strptime(date_string, format.date_format)
            except ValueError:
                invalid_line(line, 'invalid date')
                continue

            # Parse timezone and substract its value from the date
            try:
                timezone = float(format.get('timezone'))
            except BaseFormatException:
                timezone = 0
            except ValueError:
                invalid_line(line, 'invalid timezone')
                continue

            if timezone:
                hit.date -= datetime.timedelta(hours=timezone/100)

            if config.options.replay_tracking:
                # we need a query string and we only consider requests with piwik.php
                if not hit.query_string or not hit.path.lower().endswith('piwik.php'):
                    invalid_line(line, 'no query string, or ' + hit.path.lower() + ' does not end with piwik.php')
                    continue

                query_arguments = urlparse.parse_qs(hit.query_string)
                if not "idsite" in query_arguments:
                    invalid_line(line, 'missing idsite')
                    continue

                try:
                    hit.args.update((k, v.pop().encode('raw_unicode_escape').decode(config.options.encoding)) for k, v in query_arguments.iteritems())
                except UnicodeDecodeError:
                    invalid_line(line, 'invalid encoding')
                    continue

            hits.append(hit)

            if len(hits) >= config.options.recorder_max_payload_size * len(Recorder.recorders):
                Recorder.add_hits(hits)
                hits = []

        # add last chunk of hits
        if len(hits) > 0:
            Recorder.add_hits(hits)

def main():
    """
    Start the importing process.
    """
    stats.set_time_start()

    if config.options.show_progress:
        stats.start_monitor()

    recorders = Recorder.launch(config.options.recorders)

    try:
        for filename in config.filenames:
            parser.parse(filename)

        Recorder.wait_empty()
    except KeyboardInterrupt:
        pass

    stats.set_time_stop()

    if config.options.show_progress:
        stats.stop_monitor()

    try:
        Recorder.invalidate_reports()
    except Piwik.Error, e:
        pass
    stats.print_summary()

def fatal_error(error, filename=None, lineno=None):
    print >> sys.stderr, 'Fatal error: %s' % error
    if filename and lineno is not None:
        print >> sys.stderr, (
            'You can restart the import of "%s" from the point it failed by '
            'specifying --skip=%d on the command line.\n' % (filename, lineno)
        )
    os._exit(1)

if __name__ == '__main__':
    try:
        piwik = Piwik()
        config = Configuration()
        stats = Statistics()
        resolver = config.get_resolver()
        parser = Parser()
        main()
        sys.exit(0)
    except KeyboardInterrupt:
        pass
