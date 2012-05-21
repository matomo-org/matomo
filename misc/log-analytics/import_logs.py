#!/usr/bin/python
# -*- coding: utf-8 -*-
#
# Piwik - Open source web analytics
#
# @link http://piwik.org
# @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
# @version $Id$
#
# For more info see: http://piwik.org/log-analytics/

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

STATIC_EXTENSIONS = (
    'gif jpg jpeg png bmp ico svg ttf eot woff class swf css js xml robots.txt'
).split()


DOWNLOAD_EXTENSIONS = (
    '7z aac arc arj asf asx avi bin csv deb dmg doc exe flv gz gzip hqx '
    'jar mpg mp2 mp3 mp4 mpeg mov movie msi msp odb odf odg odp '
    'ods odt ogg ogv pdf phps ppt qt qtm ra ram rar rpm sea sit tar tbz '
    'bz2 tbz tgz torrent txt wav wma wmv wpd xls xml z zip'
).split()


# A good source is: http://phpbb-bots.blogspot.com/
EXCLUDED_USER_AGENTS = (
    'adsbot-google',
    'ask jeeves',
    'baiduspider+(',
    'bot-',
    'bot/',
    'ccooter/',
    'crawl',
    'exabot',
    'feed',
    'googlebot',
    'ia_archiver',
    'java/',
    'mediapartners-google',
    'msnbot',
    'robot',
    'sosospider+',
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

class RegexFormat(object):

    def __init__(self, name, regex, date_format='%d/%b/%Y:%H:%M:%S'):
        self.name = name
        self.regex = re.compile(regex)
        self.date_format = date_format

    def check_format(self, file):
        line = file.readline()
        file.seek(0)
        if re.match(self.regex, line):
            return self


class IisFormat(object):

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
        return RegexFormat('iis', ' '.join(full_regex), '%Y-%m-%d %H:%M:%S')



_HOST_PREFIX = '(?P<host>[\w\-\.]*)(?::\d+)? '
_COMMON_LOG_FORMAT = (
    '(?P<ip>\S+) \S+ \S+ \[(?P<date>.*?) (?P<timezone>.*?)\] '
    '"\S+ (?P<path>.*?) \S+" (?P<status>\S+) (?P<length>\S+)'
)
_NCSA_EXTENDED_LOG_FORMAT = (_COMMON_LOG_FORMAT +
    ' "(?P<referrer>.*?)" "(?P<user_agent>.*?)"'
)

FORMATS = {
    'common': RegexFormat('common', _COMMON_LOG_FORMAT),
    'common_vhost': RegexFormat('common_vhost', _HOST_PREFIX + _COMMON_LOG_FORMAT),
    'ncsa_extended': RegexFormat('ncsa_extended', _NCSA_EXTENDED_LOG_FORMAT),
    'common_complete': RegexFormat('common_complete', _HOST_PREFIX + _NCSA_EXTENDED_LOG_FORMAT),
    'iis': IisFormat(),
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

    def __init__(self):
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
            help="Print a progress report every second"
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
            help="Paths to exclude. Can be specified multiple times"
        )
        option_parser.add_option(
            '--exclude-path-from', dest='exclude_path_from',
            help="Each line from this file is a path to exclude"
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
            help="Track static files (images, css, js, etc.)"
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
                  % ', '.join(sorted(FORMATS.iterkeys()))
        ))
        option_parser.add_option(
            '--log-format-regex', dest='log_format_regex', default=None,
            help="Access log regular expression. For an example of a supported Regex, see the source code of this file. "
                 "Overrides --log-format-name"
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
            '--output', dest='output',
            help="Redirect output (stdout and stderr) to the specified file"
        )
        option_parser.add_option(
            '--encoding', dest='encoding', default='utf8',
            help="Log files encoding (default: %default)"
        )


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

        self.options.excluded_useragents = [s.lower() for s in self.options.excluded_useragents]

        if self.options.exclude_path_from:
            paths = [path.strip() for path in open(self.options.exclude_path_from).readlines()]
            self.options.excluded_paths.extend(path for path in paths if len(path) > 0)
        if self.options.excluded_paths:
            logging.debug('Excluded paths: %s', ' '.join(self.options.excluded_paths))

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


    def _get_token_auth(self):
        """
        If the token auth is not specified in the options, get it from Piwik.
        """
        # Get superuser login/password from the options.
        logging.debug('No token-auth specified')

        if self.options.login and self.options.password:
            piwik_login = self.options.login
            piwik_password = hashlib.md5(self.options.password).hexdigest()
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
                    "couldn't open the configuration file, "
                    "required to get the authentication token"
                )
            piwik_login = config_file.get('superuser', 'login').strip('"')
            piwik_password = config_file.get('superuser', 'password').strip('"')

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
        %(count_lines_invalid)d invalid log lines
        %(count_lines_skipped_user_agent)d requests done by bots, search engines, ...
        %(count_lines_skipped_http_errors)d HTTP errors
        %(count_lines_skipped_http_redirects)d HTTP redirects
        %(count_lines_static)d requests to static resources (css, js, ...)
        %(count_lines_no_site)d requests did not match any known site
        %(count_lines_hostname_skipped)d requests did not match any requested hostname

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
            self.count_lines_no_site.value,
            self.count_lines_hostname_skipped.value,
        ]),
    'count_lines_invalid': self.count_lines_invalid.value,
    'count_lines_skipped_user_agent': self.count_lines_skipped_user_agent.value,
    'count_lines_skipped_http_errors': self.count_lines_skipped_http_errors.value,
    'count_lines_skipped_http_redirects': self.count_lines_skipped_http_redirects.value,
    'count_lines_static': self.count_lines_static.value,
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
            print '%d lines parsed, %d lines recorded, %d records/sec' % (
                stats.count_lines_parsed.value,
                current_total,
                current_total - latest_total_recorded,
            )
            latest_total_recorded = current_total
            time.sleep(1)

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
    def _call(path, args, headers=None, url=None):
        """
        Make a request to the Piwik site. It is up to the caller to format
        arguments, to embed authentication, etc.
        """
        if url is None:
            url = config.options.piwik_url
        headers = headers or {}
        # If Content-Type isn't defined, PHP do not parse the request's body.
        headers['Content-type'] = 'application/x-www-form-urlencoded'
        data = urllib.urlencode(args)
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
            'format' : 'json',
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
        # See: http://dev.piwik.org/trac/wiki/API/Reference#PassinganArrayParameter
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
            raise urllib2.URLError('Piwik returned an invalid response: ' + res[:300])


    def _call_wrapper(self, func, expected_response, *args, **kwargs):
        """
        Try to make requests to Piwik at most PIWIK_FAILURE_MAX_RETRY times.
        """
        errors = 0
        while True:
            try:
                response = func(*args, **kwargs)
                if expected_response is not None and response != expected_response:
                    raise urllib2.URLError("didn't receive the expected response. Response was %s "
                    % ((response[:200] + '..') if len(response) > 200 else response))
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

    def call(self, path, args, expected_content=None, headers=None):
        return self._call_wrapper(self._call, expected_content, path, args, headers)

    def call_api(self, method, **kwargs):
        return self._call_wrapper(self._call_api, None, method, **kwargs)



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
        sites = piwik.call_api(
            'SitesManager.getSiteFromId', idSite=self.site_id
        )
        try:
            site = sites[0]
        except (IndexError, KeyError):
            fatal_error(
                "cannot get the main URL of this site: invalid site ID: %s" % site_id
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

    def __init__(self):
        self._cache = {}

    def _resolve(self, hit):
        main_url = 'http://' + hit.host
        res = piwik.call_api(
            'SitesManager.getSitesIdFromSiteUrl',
            url=main_url,
        )
        if res:
            # The site already exists.
            site_id = res[0]['idsite']
        else:
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
                else:
                    site_id = result['value']
                    stats.piwik_sites_created.append((hit.host, site_id))
            else:
                # The site doesn't exist, we don't want to create new sites and
                # there's no default site ID. We thus have to ignore this hit.
                site_id = None
        if site_id is not None:
            stats.piwik_sites.add(site_id)
        return site_id

    def resolve(self, hit):
        """
        Return the site ID from the cache if found, otherwise call _resolve.
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


    def check_format(self, format):
        if 'host' not in format.regex.groupindex:
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
        self.queue = Queue.Queue(maxsize=10000)

    @staticmethod
    def launch(recorder_count):
        """
        Launch a bunch of Recorder objects in a separate thread.
        """
        for i in xrange(recorder_count):
            recorder = Recorder()
            Recorder.recorders.append(recorder)
            t = threading.Thread(target=recorder._run)
            t.daemon = True
            t.start()
            logging.debug('Launched recorder')

    @staticmethod
    def add_hit(hit):
        """
        Add a hit in one of the recorders queue.
        """
        # Get a queue so that one client IP will always use the same queue.
        recorders = Recorder.recorders
        queue = recorders[abs(hash(hit.ip)) % len(recorders)].queue
        queue.put(hit)

    @staticmethod
    def wait_empty():
        """
        Wait until all recorders have an empty queue.
        """
        for recorder in Recorder.recorders:
            recorder._wait_empty()


    def _run(self):
        while True:
            hit = self.queue.get()
            try:
                self._record_hit(hit)
            except Piwik.Error, e:
                fatal_error(e, hit.filename, hit.lineno)
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

    def _record_hit(self, hit):
        """
        Insert the hit into Piwik.
        """
        site_id, main_url = resolver.resolve(hit)
        if site_id is None:
            # This hit doesn't match any known Piwik site.
            stats.piwik_sites_ignored.add(hit.host)
            stats.count_lines_no_site.increment()
            return

        stats.dates_recorded.add(hit.date.date())

        path = hit.path
        if hit.query_string and not config.options.strip_query_string:
            path += config.options.query_string_delimiter + hit.query_string

        args = {
            'rec': '1',
            'apiv': '1',
            'url': (main_url + path[:1024]).encode('utf8'),
            'urlref': hit.referrer[:1024].encode('utf8'),
            'cip': hit.ip,
            'cdt': self.date_to_piwik(hit.date),
            'idsite': site_id,
            'dp': '0' if config.options.reverse_dns else '1',
            'token_auth': config.options.piwik_token_auth,
        }
        if hit.is_download:
            args['download'] = args['url']
        if hit.is_robot:
            args['_cvar'] = '{"1":["Bot","%s"]}' % hit.user_agent
        elif config.options.enable_bots:
            args['_cvar'] = '{"1":["Not-Bot","%s"]}' % hit.user_agent
        if hit.is_error or hit.is_redirect:
            args['_cvar'] = '{"2":["HTTP-code","%s"]}' % hit.status
            args['action_name'] = '%s/URL = %s%s' % (
                hit.status,
                urllib.quote(args['url'], ''),
                ("/From = %s" % urllib.quote(args['urlref'], '') if args['urlref'] != ''  else '')
            )

        if not config.options.dry_run:
            piwik.call(
                '/piwik.php', args,
                expected_content=PIWIK_EXPECTED_IMAGE,
                headers={'User-Agent' : hit.user_agent},
            )
        stats.count_lines_recorded.increment()


    @staticmethod
    def invalidate_reports():
        if config.options.dry_run or not stats.dates_recorded:
            return

        dates = [date.strftime('%Y-%m-%d') for date in stats.dates_recorded]
        print 'Purging Piwik archives for dates: ' + ' '.join(dates)
        result = piwik.call_api(
            'CoreAdminHome.invalidateArchivedReports',
            dates=','.join(dates),
            idSites=','.join(str(site_id) for site_id in stats.piwik_sites),
        )




class Hit(object):
    """
    It's a simple container.
    """
    def __init__(self, **kwargs):
        for key, value in kwargs.iteritems():
            setattr(self, key, value)
        super(Hit, self).__init__()


class Parser(object):
    """
    The Parser parses the lines in a specified file and inserts them into
    a Queue.
    """

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
        for extension in STATIC_EXTENSIONS:
            if hit.path.lower().endswith(extension):
                if config.options.enable_static:
                    hit.is_download = True
                    return True
                else:
                    stats.count_lines_static.increment()
                    return False
        return True

    def check_download(self, hit):
        for extension in DOWNLOAD_EXTENSIONS:
            if hit.path.lower().endswith(extension):
                stats.count_lines_downloads.increment()
                hit.is_download = True
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
        if hit.status.startswith('4') or hit.status.startswith('5'):
            if config.options.enable_http_errors:
                hit.is_error = True
                return True
            else:
                stats.count_lines_skipped_http_errors.increment()
                return False
        return True

    def check_http_redirect(self, hit):
        if hit.status.startswith('3') and hit.status != '304':
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
        return True

    @staticmethod
    def detect_format(file):
        """
        Return the format matching this file, or None if none was found.
        """
        logging.debug('Detecting the log format')
        for name, candidate_format in FORMATS.iteritems():
            format = candidate_format.check_format(file)
            if format:
                logging.debug('Format %s matches', name)
                return format
            else:
                logging.debug('Format %s does not match', name)

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
            format = self.detect_format(file)
            if format is None:
                return fatal_error(
                    'Cannot guess the logs format. Please give one using '
                    'either the --log-format-name or --log-format-regex option'
                )
        # Make sure the format is compatible with the resolver.
        resolver.check_format(format)

        for lineno, line in enumerate(file):
            try:
                line = line.decode(config.options.encoding)
            except UnicodeDecodeError:
                invalid_line(line, 'invalid encoding')
                continue

            stats.count_lines_parsed.increment()
            if stats.count_lines_parsed.value <= config.options.skip:
                continue

            match = format.regex.match(line)
            if not match:
                invalid_line(line, 'line did not match')
                continue

            hit = Hit(
                filename=filename,
                lineno=lineno,
                status=match.group('status'),
                full_path=match.group('path'),
                is_download=False,
                is_robot=False,
                is_error=False,
                is_redirect=False,
            )

            try:
                hit.query_string = match.group('query_string')
                hit.path = hit.full_path
            except IndexError:
                hit.path, _, hit.query_string = hit.full_path.partition(config.options.query_string_delimiter)

            # Parse date
            date_string = match.group('date')
            try:
                hit.date = datetime.datetime.strptime(date_string, format.date_format)
            except ValueError:
                invalid_line(line, 'invalid date')
                continue

            # Parse timezone and substract its value from the date
            try:
                timezone = float(match.group('timezone'))
            except IndexError:
                timezone = 0
            except ValueError:
                invalid_line(line, 'invalid timezone')
                continue

            if timezone:
                hit.date -= datetime.timedelta(hours=timezone/100)

            try:
                hit.referrer = match.group('referrer')
            except IndexError:
                hit.referrer = ''
            if hit.referrer == '-':
                hit.referrer = ''

            try:
                hit.user_agent = match.group('user_agent')
            except IndexError:
                hit.user_agent = ''

            hit.ip = match.group('ip')
            try:
                hit.length = int(match.group('length'))
            except (ValueError, IndexError):
                # Some lines or formats don't have a length (e.g. 304 redirects, IIS logs)
                hit.length = 0
            try:
                hit.host = match.group('host')
            except IndexError:
                # Some formats have no host.
                pass

            # Check if the hit must be excluded.
            check_methods = inspect.getmembers(self, predicate=inspect.ismethod)
            if all((method(hit) for name, method in check_methods if name.startswith('check_'))):
                Recorder.add_hit(hit)




def main():
    """
    Start the importing process.
    """
    if config.options.show_progress:
        stats.start_monitor()

    stats.set_time_start()

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
