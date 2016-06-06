<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Hostname.php 24307 2011-07-30 02:13:14Z adamlundrigan $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Validate_Ip
 */
// require_once 'Zend/Validate/Ip.php';

/**
 * Please note there are two standalone test scripts for testing IDN characters due to problems
 * with file encoding.
 *
 * The first is tests/Zend/Validate/HostnameTestStandalone.php which is designed to be run on
 * the command line.
 *
 * The second is tests/Zend/Validate/HostnameTestForm.php which is designed to be run via HTML
 * to allow users to test entering UTF-8 characters in a form.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Hostname extends Zend_Validate_Abstract
{
    const CANNOT_DECODE_PUNYCODE  = 'hostnameCannotDecodePunycode';
    const INVALID                 = 'hostnameInvalid';
    const INVALID_DASH            = 'hostnameDashCharacter';
    const INVALID_HOSTNAME        = 'hostnameInvalidHostname';
    const INVALID_HOSTNAME_SCHEMA = 'hostnameInvalidHostnameSchema';
    const INVALID_LOCAL_NAME      = 'hostnameInvalidLocalName';
    const INVALID_URI             = 'hostnameInvalidUri';
    const IP_ADDRESS_NOT_ALLOWED  = 'hostnameIpAddressNotAllowed';
    const LOCAL_NAME_NOT_ALLOWED  = 'hostnameLocalNameNotAllowed';
    const UNDECIPHERABLE_TLD      = 'hostnameUndecipherableTld';
    const UNKNOWN_TLD             = 'hostnameUnknownTld';
    const VALID_UNICODE_DOMAIN = '/^[\p{L}\p{M}]{1,63}$/iu';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::CANNOT_DECODE_PUNYCODE  => "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded",
        self::INVALID                 => "Invalid type given. String expected",
        self::INVALID_DASH            => "'%value%' appears to be a DNS hostname but contains a dash in an invalid position",
        self::INVALID_HOSTNAME        => "'%value%' does not match the expected structure for a DNS hostname",
        self::INVALID_HOSTNAME_SCHEMA => "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        self::INVALID_LOCAL_NAME      => "'%value%' does not appear to be a valid local network name",
        self::INVALID_URI             => "'%value%' does not appear to be a valid URI hostname",
        self::IP_ADDRESS_NOT_ALLOWED  => "'%value%' appears to be an IP address, but IP addresses are not allowed",
        self::LOCAL_NAME_NOT_ALLOWED  => "'%value%' appears to be a local network name but local network names are not allowed",
        self::UNDECIPHERABLE_TLD      => "'%value%' appears to be a DNS hostname but cannot extract TLD part",
        self::UNKNOWN_TLD             => "'%value%' appears to be a DNS hostname but cannot match TLD against known list",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'tld' => '_tld'
    );

    /**
     * Allows Internet domain names (e.g., example.com)
     */
    const ALLOW_DNS   = 1;

    /**
     * Allows IP addresses
     */
    const ALLOW_IP    = 2;

    /**
     * Allows local network names (e.g., localhost, www.localdomain)
     */
    const ALLOW_LOCAL = 4;

    /**
     * Allows all types of hostnames
     */
    const ALLOW_URI = 8;

    /**
     * Allows all types of hostnames
     */
    const ALLOW_ALL = 15;

    /**
     * Array of valid top-level-domains
     *
     * @see ftp://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/ Official list of supported TLDs
     * @var array
     */
    protected $_validTlds = array(
        'aaa', 'aarp', 'abb', 'abbott', 'abogado', 'ac', 'academy', 'accenture', 'accountant', 'accountants',
        'aco', 'active', 'actor', 'ad', 'ads', 'adult', 'ae', 'aeg', 'aero', 'af', 'afl', 'ag', 'agency', 'ai',
        'aig', 'airforce', 'airtel', 'al', 'allfinanz', 'alsace', 'am', 'amica', 'amsterdam', 'analytics', 
        'android', 'ao', 'apartments', 'app', 'apple', 'aq', 'aquarelle', 'ar', 'aramco', 'archi', 'army', 'arpa', 
        'arte', 'as', 'asia', 'associates', 'at', 'attorney', 'au', 'auction', 'audi', 'audio', 'author', 'auto', 
        'autos', 'aw', 'ax', 'axa', 'az', 'azure', 'ba', 'baidu', 'band', 'bank', 'bar', 'barcelona', 'barclaycard', 
        'barclays', 'bargains', 'bauhaus', 'bayern', 'bb', 'bbc', 'bbva', 'bcn', 'bd', 'be', 'beats', 'beer', 
        'bentley', 'berlin', 'best', 'bet', 'bf', 'bg', 'bh', 'bharti', 'bi', 'bible', 'bid', 'bike', 'bing', 
        'bingo', 'bio', 'biz', 'bj', 'black', 'blackfriday', 'bloomberg', 'blue', 'bm', 'bms', 'bmw', 'bn', 'bnl', 
        'bnpparibas', 'bo', 'boats', 'boehringer', 'bom', 'bond', 'boo', 'book', 'boots', 'bosch', 'bostik', 'bot', 
        'boutique', 'br', 'bradesco', 'bridgestone', 'broadway', 'broker', 'brother', 'brussels', 'bs', 'bt', 
        'budapest', 'bugatti', 'build', 'builders', 'business', 'buy', 'buzz', 'bv', 'bw', 'by', 'bz', 'bzh', 'ca', 
        'cab', 'cafe', 'cal', 'call', 'camera', 'camp', 'cancerresearch', 'canon', 'capetown', 'capital', 'car', 
        'caravan', 'cards', 'care', 'career', 'careers', 'cars', 'cartier', 'casa', 'cash', 'casino', 'cat', 
        'catering', 'cba', 'cbn', 'cc', 'cd', 'ceb', 'center', 'ceo', 'cern', 'cf', 'cfa', 'cfd', 'cg', 'ch', 
        'chanel', 'channel', 'chat', 'cheap', 'chloe', 'christmas', 'chrome', 'church', 'ci', 'cipriani', 
        'circle', 'cisco', 'citic', 'city', 'cityeats', 'ck', 'cl', 'claims', 'cleaning', 'click', 'clinic', 
        'clinique', 'clothing', 'cloud', 'club', 'clubmed', 'cm', 'cn', 'co', 'coach', 'codes', 'coffee', 
        'college', 'cologne', 'com', 'commbank', 'community', 'company', 'computer', 'comsec', 'condos', 
        'construction', 'consulting', 'contact', 'contractors', 'cooking', 'cool', 'coop', 'corsica', 'country', 
        'coupons', 'courses', 'cr', 'credit', 'creditcard', 'creditunion', 'cricket', 'crown', 'crs', 'cruises', 
        'csc', 'cu', 'cuisinella', 'cv', 'cw', 'cx', 'cy', 'cymru', 'cyou', 'cz', 'dabur', 'dad', 'dance', 'date', 
        'dating', 'datsun', 'day', 'dclk', 'de', 'dealer', 'deals', 'degree', 'delivery', 'dell', 'delta', 'democrat', 
        'dental', 'dentist', 'desi', 'design', 'dev', 'diamonds', 'diet', 'digital', 'direct', 'directory', 
        'discount', 'dj', 'dk', 'dm', 'dnp', 'do', 'docs', 'dog', 'doha', 'domains', 'doosan', 'download', 'drive', 
        'dubai', 'durban', 'dvag', 'dz', 'earth', 'eat', 'ec', 'edu', 'education', 'ee', 'eg', 'email', 'emerck', 
        'energy', 'engineer', 'engineering', 'enterprises', 'epson', 'equipment', 'er', 'erni', 'es', 'esq', 
        'estate', 'et', 'eu', 'eurovision', 'eus', 'events', 'everbank', 'exchange', 'expert', 'exposed', 'express', 
        'fage', 'fail', 'fairwinds', 'faith', 'family', 'fan', 'fans', 'farm', 'fashion', 'fast', 'feedback', 
        'ferrero', 'fi', 'film', 'final', 'finance', 'financial', 'firestone', 'firmdale', 'fish', 'fishing', 
        'fit', 'fitness', 'fj', 'fk', 'flights', 'florist', 'flowers', 'flsmidth', 'fly', 'fm', 'fo', 'foo', 
        'football', 'ford', 'forex', 'forsale', 'forum', 'foundation', 'fox', 'fr', 'frl', 'frogans', 'fund', 
        'furniture', 'futbol', 'fyi', 'ga', 'gal', 'gallery', 'game', 'garden', 'gb', 'gbiz', 'gd', 'gdn', 'ge', 
        'gea', 'gent', 'genting', 'gf', 'gg', 'ggee', 'gh', 'gi', 'gift', 'gifts', 'gives', 'giving', 'gl', 'glass', 
        'gle', 'global', 'globo', 'gm', 'gmail', 'gmo', 'gmx', 'gn', 'gold', 'goldpoint', 'golf', 'goo', 'goog', 
        'google', 'gop', 'got', 'gov', 'gp', 'gq', 'gr', 'grainger', 'graphics', 'gratis', 'green', 'gripe', 'group', 
        'gs', 'gt', 'gu', 'gucci', 'guge', 'guide', 'guitars', 'guru', 'gw', 'gy', 'hamburg', 'hangout', 'haus', 
        'healthcare', 'help', 'here', 'hermes', 'hiphop', 'hitachi', 'hiv', 'hk', 'hm', 'hn', 'hockey', 'holdings', 
        'holiday', 'homedepot', 'homes', 'honda', 'horse', 'host', 'hosting', 'hoteles', 'hotmail', 'house', 'how', 
        'hr', 'hsbc', 'ht', 'hu', 'hyundai', 'ibm', 'icbc', 'ice', 'icu', 'id', 'ie', 'ifm', 'iinet', 'il', 'im', 
        'immo', 'immobilien', 'in', 'industries', 'infiniti', 'info', 'ing', 'ink', 'institute', 'insurance', 'insure', 
        'int', 'international', 'investments', 'io', 'ipiranga', 'iq', 'ir', 'irish', 'is', 'ist', 'istanbul', 'it', 
        'itau', 'iwc', 'jaguar', 'java', 'jcb', 'je', 'jetzt', 'jewelry', 'jlc', 'jll', 'jm', 'jmp', 'jo', 'jobs', 
        'joburg', 'jot', 'joy', 'jp', 'jprs', 'juegos', 'kaufen', 'kddi', 'ke', 'kfh', 'kg', 'kh', 'ki', 'kia', 'kim', 
        'kinder', 'kitchen', 'kiwi', 'km', 'kn', 'koeln', 'komatsu', 'kp', 'kpn', 'kr', 'krd', 'kred', 'kw', 'ky', 
        'kyoto', 'kz', 'la', 'lacaixa', 'lamborghini', 'lamer', 'lancaster', 'land', 'landrover', 'lasalle', 'lat', 
        'latrobe', 'law', 'lawyer', 'lb', 'lc', 'lds', 'lease', 'leclerc', 'legal', 'lexus', 'lgbt', 'li', 'liaison', 
        'lidl', 'life', 'lifestyle', 'lighting', 'like', 'limited', 'limo', 'lincoln', 'linde', 'link', 'live', 
        'living', 'lixil', 'lk', 'loan', 'loans', 'lol', 'london', 'lotte', 'lotto', 'love', 'lr', 'ls', 'lt', 'ltd', 
        'ltda', 'lu', 'lupin', 'luxe', 'luxury', 'lv', 'ly', 'ma', 'madrid', 'maif', 'maison', 'man', 'management', 
        'mango', 'market', 'marketing', 'markets', 'marriott', 'mba', 'mc', 'md', 'me', 'med', 'media', 'meet', 
        'melbourne', 'meme', 'memorial', 'men', 'menu', 'meo', 'mg', 'mh', 'miami', 'microsoft', 'mil', 'mini', 'mk', 
        'ml', 'mm', 'mma', 'mn', 'mo', 'mobi', 'mobily', 'moda', 'moe', 'moi', 'mom', 'monash', 'money', 'montblanc', 
        'mormon', 'mortgage', 'moscow', 'motorcycles', 'mov', 'movie', 'movistar', 'mp', 'mq', 'mr', 'ms', 'mt', 
        'mtn', 'mtpc', 'mtr', 'mu', 'museum', 'mutuelle', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nadex', 'nagoya', 
        'name', 'navy', 'nc', 'ne', 'nec', 'net', 'netbank', 'network', 'neustar', 'new', 'news', 'nexus', 'nf', 
        'ng', 'ngo', 'nhk', 'ni', 'nico', 'ninja', 'nissan', 'nl', 'no', 'nokia', 'norton', 'nowruz', 'np', 'nr', 
        'nra', 'nrw', 'ntt', 'nu', 'nyc', 'nz', 'obi', 'office', 'okinawa', 'om', 'omega', 'one', 'ong', 'onl', 
        'online', 'ooo', 'oracle', 'orange', 'org', 'organic', 'origins', 'osaka', 'otsuka', 'ovh', 'pa', 'page', 
        'panerai', 'paris', 'pars', 'partners', 'parts', 'party', 'pe', 'pet', 'pf', 'pg', 'ph', 'pharmacy', 
        'philips', 'photo', 'photography', 'photos', 'physio', 'piaget', 'pics', 'pictet', 'pictures', 'pid', 'pin', 
        'ping', 'pink', 'pizza', 'pk', 'pl', 'place', 'play', 'playstation', 'plumbing', 'plus', 'pm', 'pn', 'pohl', 
        'poker', 'porn', 'post', 'pr', 'praxi', 'press', 'pro', 'prod', 'productions', 'prof', 'promo', 'properties', 
        'property', 'protection', 'ps', 'pt', 'pub', 'pw', 'py', 'qa', 'qpon', 'quebec', 'racing', 're', 'read', 
        'realtor', 'realty', 'recipes', 'red', 'redstone', 'redumbrella', 'rehab', 'reise', 'reisen', 'reit', 'ren', 
        'rent', 'rentals', 'repair', 'report', 'republican', 'rest', 'restaurant', 'review', 'reviews', 'rexroth', 
        'rich', 'ricoh', 'rio', 'rip', 'ro', 'rocher', 'rocks', 'rodeo', 'room', 'rs', 'rsvp', 'ru', 'ruhr', 'run', 
        'rw', 'rwe', 'ryukyu', 'sa', 'saarland', 'safe', 'safety', 'sakura', 'sale', 'salon', 'samsung', 'sandvik', 
        'sandvikcoromant', 'sanofi', 'sap', 'sapo', 'sarl', 'sas', 'saxo', 'sb', 'sbs', 'sc', 'sca', 'scb', 
        'schaeffler', 'schmidt', 'scholarships', 'school', 'schule', 'schwarz', 'science', 'scor', 'scot', 'sd', 
        'se', 'seat', 'security', 'seek', 'sener', 'services', 'seven', 'sew', 'sex', 'sexy', 'sfr', 'sg', 'sh', 
        'sharp', 'shell', 'shia', 'shiksha', 'shoes', 'show', 'shriram', 'si', 'singles', 'site', 'sj', 'sk', 'ski', 
        'sky', 'skype', 'sl', 'sm', 'smile', 'sn', 'sncf', 'so', 'soccer', 'social', 'software', 'sohu', 'solar', 
        'solutions', 'sony', 'soy', 'space', 'spiegel', 'spreadbetting', 'sr', 'srl', 'st', 'stada', 'star', 'starhub', 
        'statefarm', 'statoil', 'stc', 'stcgroup', 'stockholm', 'storage', 'studio', 'study', 'style', 'su', 'sucks', 
        'supplies', 'supply', 'support', 'surf', 'surgery', 'suzuki', 'sv', 'swatch', 'swiss', 'sx', 'sy', 'sydney', 
        'symantec', 'systems', 'sz', 'tab', 'taipei', 'tatamotors', 'tatar', 'tattoo', 'tax', 'taxi', 'tc', 'tci', 
        'td', 'team', 'tech', 'technology', 'tel', 'telefonica', 'temasek', 'tennis', 'tf', 'tg', 'th', 'thd', 
        'theater', 'theatre', 'tickets', 'tienda', 'tips', 'tires', 'tirol', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 
        'today', 'tokyo', 'tools', 'top', 'toray', 'toshiba', 'tours', 'town', 'toyota', 'toys', 'tr', 'trade', 
        'trading', 'training', 'travel', 'travelers', 'travelersinsurance', 'trust', 'trv', 'tt', 'tui', 'tushu', 
        'tv', 'tw', 'tz', 'ua', 'ubs', 'ug', 'uk', 'university', 'uno', 'uol', 'us', 'uy', 'uz', 'va', 'vacations', 
        'vana', 'vc', 've', 'vegas', 'ventures', 'verisign', 'versicherung', 'vet', 'vg', 'vi', 'viajes', 'video', 
        'villas', 'vin', 'vip', 'virgin', 'vision', 'vista', 'vistaprint', 'viva', 'vlaanderen', 'vn', 'vodka', 'vote', 
        'voting', 'voto', 'voyage', 'vu', 'wales', 'walter', 'wang', 'wanggou', 'watch', 'watches', 'webcam', 'weber', 
        'website', 'wed', 'wedding', 'weir', 'wf', 'whoswho', 'wien', 'wiki', 'williamhill', 'win', 'windows', 'wine', 
        'wme', 'work', 'works', 'world', 'ws', 'wtc', 'wtf', 'xbox', 'xerox', 'xin', 'xperia', 'xxx', 'xyz', 'yachts', 'yamaxun', 'yandex', 'ye',
        'yodobashi', 'yoga', 'yokohama', 'youtube', 'yt', 'za', 'zara', 'zero', 'zip', 'zm', 'zone', 'zuerich', 'zw'
    );

    /**
     * @var string
     */
    protected $_tld;

    /**
     * Array for valid Idns
     * @see http://www.iana.org/domains/idn-tables/ Official list of supported IDN Chars
     * (.AC) Ascension Island http://www.nic.ac/pdf/AC-IDN-Policy.pdf
     * (.AR) Argentinia http://www.nic.ar/faqidn.html
     * (.AS) American Samoa http://www.nic.as/idn/chars.cfm
     * (.AT) Austria http://www.nic.at/en/service/technical_information/idn/charset_converter/
     * (.BIZ) International http://www.iana.org/domains/idn-tables/
     * (.BR) Brazil http://registro.br/faq/faq6.html
     * (.BV) Bouvett Island http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.CAT) Catalan http://www.iana.org/domains/idn-tables/tables/cat_ca_1.0.html
     * (.CH) Switzerland https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.CL) Chile http://www.iana.org/domains/idn-tables/tables/cl_latn_1.0.html
     * (.COM) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.DE) Germany http://www.denic.de/en/domains/idns/liste.html
     * (.DK) Danmark http://www.dk-hostmaster.dk/index.php?id=151
     * (.ES) Spain https://www.nic.es/media/2008-05/1210147705287.pdf
     * (.FI) Finland http://www.ficora.fi/en/index/palvelut/fiverkkotunnukset/aakkostenkaytto.html
     * (.GR) Greece https://grweb.ics.forth.gr/CharacterTable1_en.jsp
     * (.HU) Hungary http://www.domain.hu/domain/English/szabalyzat/szabalyzat.html
     * (.INFO) International http://www.nic.info/info/idn
     * (.IO) British Indian Ocean Territory http://www.nic.io/IO-IDN-Policy.pdf
     * (.IR) Iran http://www.nic.ir/Allowable_Characters_dot-iran
     * (.IS) Iceland http://www.isnic.is/domain/rules.php
     * (.KR) Korea http://www.iana.org/domains/idn-tables/tables/kr_ko-kr_1.0.html
     * (.LI) Liechtenstein https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.LT) Lithuania http://www.domreg.lt/static/doc/public/idn_symbols-en.pdf
     * (.MD) Moldova http://www.register.md/
     * (.MUSEUM) International http://www.iana.org/domains/idn-tables/tables/museum_latn_1.0.html
     * (.NET) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.NO) Norway http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.NU) Niue http://www.worldnames.net/
     * (.ORG) International http://www.pir.org/index.php?db=content/FAQs&tbl=FAQs_Registrant&id=2
     * (.PE) Peru https://www.nic.pe/nuevas_politicas_faq_2.php
     * (.PL) Poland http://www.dns.pl/IDN/allowed_character_sets.pdf
     * (.PR) Puerto Rico http://www.nic.pr/idn_rules.asp
     * (.PT) Portugal https://online.dns.pt/dns_2008/do?com=DS;8216320233;111;+PAGE(4000058)+K-CAT-CODIGO(C.125)+RCNT(100);
     * (.RU) Russia http://www.iana.org/domains/idn-tables/tables/ru_ru-ru_1.0.html
     * (.SA) Saudi Arabia http://www.iana.org/domains/idn-tables/tables/sa_ar_1.0.html
     * (.SE) Sweden http://www.iis.se/english/IDN_campaignsite.shtml?lang=en
     * (.SH) Saint Helena http://www.nic.sh/SH-IDN-Policy.pdf
     * (.SJ) Svalbard and Jan Mayen http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.TH) Thailand http://www.iana.org/domains/idn-tables/tables/th_th-th_1.0.html
     * (.TM) Turkmenistan http://www.nic.tm/TM-IDN-Policy.pdf
     * (.TR) Turkey https://www.nic.tr/index.php
     * (.VE) Venice http://www.iana.org/domains/idn-tables/tables/ve_es_1.0.html
     * (.VN) Vietnam http://www.vnnic.vn/english/5-6-300-2-2-04-20071115.htm#1.%20Introduction
     *
     * @var array
     */
    protected $_validIdns = array(
        'AC'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'),
        'AR'  => array(1 => '/^[\x{002d}0-9a-zà-ãç-êìíñ-õü]{1,63}$/iu'),
        'AS'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĸĺļľłńņňŋōŏőœŕŗřśŝşšţťŧũūŭůűųŵŷźż]{1,63}$/iu'),
        'AT'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœšž]{1,63}$/iu'),
        'BIZ' => 'Hostname/Biz.php',
        'BR'  => array(1 => '/^[\x{002d}0-9a-zà-ãçéíó-õúü]{1,63}$/iu'),
        'BV'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'CAT' => array(1 => '/^[\x{002d}0-9a-z·àç-éíïòóúü]{1,63}$/iu'),
        'CH'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'),
        'CL'  => array(1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'),
        'CN'  => 'Hostname/Cn.php',
        'COM' => 'Hostname/Com.php',
        'DE'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'DK'  => array(1 => '/^[\x{002d}0-9a-zäéöü]{1,63}$/iu'),
        'ES'  => array(1 => '/^[\x{002d}0-9a-zàáçèéíïñòóúü·]{1,63}$/iu'),
        'EU'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-zΐάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώ]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюя]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zἀ-ἇἐ-ἕἠ-ἧἰ-ἷὀ-ὅὐ-ὗὠ-ὧὰ-ώᾀ-ᾇᾐ-ᾗᾠ-ᾧᾰ-ᾴᾶᾷῂῃῄῆῇῐ-ΐῖῗῠ-ῧῲῳῴῶῷ]{1,63}$/iu'),
        'FI'  => array(1 => '/^[\x{002d}0-9a-zäåö]{1,63}$/iu'),
        'GR'  => array(1 => '/^[\x{002d}0-9a-zΆΈΉΊΌΎ-ΡΣ-ώἀ-ἕἘ-Ἕἠ-ὅὈ-Ὅὐ-ὗὙὛὝὟ-ώᾀ-ᾴᾶ-ᾼῂῃῄῆ-ῌῐ-ΐῖ-Ίῠ-Ῥῲῳῴῶ-ῼ]{1,63}$/iu'),
        'HK'  => 'Hostname/Cn.php',
        'HU'  => array(1 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu'),
        'INFO'=> array(1 => '/^[\x{002d}0-9a-zäåæéöøü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záæéíðóöúýþ]{1,63}$/iu',
            4 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            5 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'),
        'IO'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'IS'  => array(1 => '/^[\x{002d}0-9a-záéýúíóþæöð]{1,63}$/iu'),
        'JP'  => 'Hostname/Jp.php',
        'KR'  => array(1 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu'),
        'LI'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'),
        'LT'  => array(1 => '/^[\x{002d}0-9ąčęėįšųūž]{1,63}$/iu'),
        'MD'  => array(1 => '/^[\x{002d}0-9ăâîşţ]{1,63}$/iu'),
        'MUSEUM' => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćċčďđēėęěğġģħīįıķĺļľłńņňŋōőœŕŗřśşšţťŧūůűųŵŷźżžǎǐǒǔ\x{01E5}\x{01E7}\x{01E9}\x{01EF}ə\x{0292}ẁẃẅỳ]{1,63}$/iu'),
        'NET' => 'Hostname/Com.php',
        'NO'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'NU'  => 'Hostname/Com.php',
        'ORG' => array(1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            6 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            7 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu'),
        'PE'  => array(1 => '/^[\x{002d}0-9a-zñáéíóúü]{1,63}$/iu'),
        'PL'  => array(1 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            2 => '/^[\x{002d}а-ик-ш\x{0450}ѓѕјљњќџ]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9а-яё\x{04C2}]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zàáâèéêìíîòóôùúûċġħż]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zàäåæéêòóôöøü]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-zàáâãçéêíòóôõúü]{1,63}$/iu',
            9 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            10=> '/^[\x{002d}0-9a-záäéíóôúýčďĺľňŕšťž]{1,63}$/iu',
            11=> '/^[\x{002d}0-9a-zçë]{1,63}$/iu',
            12=> '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu',
            13=> '/^[\x{002d}0-9a-zćčđšž]{1,63}$/iu',
            14=> '/^[\x{002d}0-9a-zâçöûüğış]{1,63}$/iu',
            15=> '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            16=> '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu',
            17=> '/^[\x{002d}0-9a-zĉĝĥĵŝŭ]{1,63}$/iu',
            18=> '/^[\x{002d}0-9a-zâäéëîô]{1,63}$/iu',
            19=> '/^[\x{002d}0-9a-zàáâäåæçèéêëìíîïðñòôöøùúûüýćčłńřśš]{1,63}$/iu',
            20=> '/^[\x{002d}0-9a-zäåæõöøüšž]{1,63}$/iu',
            21=> '/^[\x{002d}0-9a-zàáçèéìíòóùú]{1,63}$/iu',
            22=> '/^[\x{002d}0-9a-zàáéíóöúüőű]{1,63}$/iu',
            23=> '/^[\x{002d}0-9ΐά-ώ]{1,63}$/iu',
            24=> '/^[\x{002d}0-9a-zàáâåæçèéêëðóôöøüþœ]{1,63}$/iu',
            25=> '/^[\x{002d}0-9a-záäéíóöúüýčďěňřšťůž]{1,63}$/iu',
            26=> '/^[\x{002d}0-9a-z·àçèéíïòóúü]{1,63}$/iu',
            27=> '/^[\x{002d}0-9а-ъьюя\x{0450}\x{045D}]{1,63}$/iu',
            28=> '/^[\x{002d}0-9а-яёіў]{1,63}$/iu',
            29=> '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            30=> '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            31=> '/^[\x{002d}0-9a-zàâæçèéêëîïñôùûüÿœ]{1,63}$/iu',
            32=> '/^[\x{002d}0-9а-щъыьэюяёєіїґ]{1,63}$/iu',
            33=> '/^[\x{002d}0-9א-ת]{1,63}$/iu'),
        'PR'  => array(1 => '/^[\x{002d}0-9a-záéíóúñäëïüöâêîôûàèùæçœãõ]{1,63}$/iu'),
        'PT'  => array(1 => '/^[\x{002d}0-9a-záàâãçéêíóôõú]{1,63}$/iu'),
        'RU'  => array(1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'),
        'SA'  => array(1 => '/^[\x{002d}.0-9\x{0621}-\x{063A}\x{0641}-\x{064A}\x{0660}-\x{0669}]{1,63}$/iu'),
        'SE'  => array(1 => '/^[\x{002d}0-9a-zäåéöü]{1,63}$/iu'),
        'SH'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'SJ'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'TH'  => array(1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'),
        'TM'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'),
        'TW'  => 'Hostname/Cn.php',
        'TR'  => array(1 => '/^[\x{002d}0-9a-zğıüşöç]{1,63}$/iu'),
        'VE'  => array(1 => '/^[\x{002d}0-9a-záéíóúüñ]{1,63}$/iu'),
        'VN'  => array(1 => '/^[ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚÝàáâãèéêìíòóôõùúýĂăĐđĨĩŨũƠơƯư\x{1EA0}-\x{1EF9}]{1,63}$/iu'),
        'ایران' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        '中国' => 'Hostname/Cn.php',
        '公司' => 'Hostname/Cn.php',
        '网络' => 'Hostname/Cn.php', 
        'कॉम' => array(1 => self::VALID_UNICODE_DOMAIN),
        'セール' => array(1 => self::VALID_UNICODE_DOMAIN),
        '佛山' => array(1 => self::VALID_UNICODE_DOMAIN),
        '慈善' => array(1 => self::VALID_UNICODE_DOMAIN),
        '集团' => array(1 => self::VALID_UNICODE_DOMAIN),
        '在线' => array(1 => self::VALID_UNICODE_DOMAIN),
        '한국' => array(1 => self::VALID_UNICODE_DOMAIN),
        '点看' => array(1 => self::VALID_UNICODE_DOMAIN),
        'คอม' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ভারত' => array(1 => self::VALID_UNICODE_DOMAIN),
        '八卦' => array(1 => self::VALID_UNICODE_DOMAIN),
        'موقع' => array(1 => self::VALID_UNICODE_DOMAIN),
        '公益' => array(1 => self::VALID_UNICODE_DOMAIN),
        '公司' => array(1 => self::VALID_UNICODE_DOMAIN),
        '移动' => array(1 => self::VALID_UNICODE_DOMAIN),
        '我爱你' => array(1 => self::VALID_UNICODE_DOMAIN),
        'москва' => array(1 => self::VALID_UNICODE_DOMAIN),
        'қаз' => array(1 => self::VALID_UNICODE_DOMAIN),
        'онлайн' => array(1 => self::VALID_UNICODE_DOMAIN),
        'сайт' => array(1 => self::VALID_UNICODE_DOMAIN),
        '联通' => array(1 => self::VALID_UNICODE_DOMAIN),
        'срб' => array(1 => self::VALID_UNICODE_DOMAIN),
        'бел' => array(1 => self::VALID_UNICODE_DOMAIN),
        'קום' => array(1 => self::VALID_UNICODE_DOMAIN),
        '时尚' => array(1 => self::VALID_UNICODE_DOMAIN),
        '淡马锡' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ファッション' => array(1 => self::VALID_UNICODE_DOMAIN),
        'орг' => array(1 => self::VALID_UNICODE_DOMAIN),
        'नेट' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ストア' => array(1 => self::VALID_UNICODE_DOMAIN),
        '삼성' => array(1 => self::VALID_UNICODE_DOMAIN),
        'சிங்கப்பூர்' => array(1 => self::VALID_UNICODE_DOMAIN),
        '商标' => array(1 => self::VALID_UNICODE_DOMAIN),
        '商店' => array(1 => self::VALID_UNICODE_DOMAIN),
        '商城' => array(1 => self::VALID_UNICODE_DOMAIN),
        'дети' => array(1 => self::VALID_UNICODE_DOMAIN),
        'мкд' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ею' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ポイント' => array(1 => self::VALID_UNICODE_DOMAIN),
        '新闻' => array(1 => self::VALID_UNICODE_DOMAIN),
        '工行' => array(1 => self::VALID_UNICODE_DOMAIN),
        'كوم' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中文网' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中信' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中国' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中國' => array(1 => self::VALID_UNICODE_DOMAIN),
        '娱乐' => array(1 => self::VALID_UNICODE_DOMAIN),
        '谷歌' => array(1 => self::VALID_UNICODE_DOMAIN),
        'భారత్' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ලංකා' => array(1 => self::VALID_UNICODE_DOMAIN),
        '购物' => array(1 => self::VALID_UNICODE_DOMAIN),
        'クラウド' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ભારત' => array(1 => self::VALID_UNICODE_DOMAIN),
        'भारत' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网店' => array(1 => self::VALID_UNICODE_DOMAIN),
        'संगठन' => array(1 => self::VALID_UNICODE_DOMAIN),
        '餐厅' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网络' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ком' => array(1 => self::VALID_UNICODE_DOMAIN),
        'укр' => array(1 => self::VALID_UNICODE_DOMAIN),
        '香港' => array(1 => self::VALID_UNICODE_DOMAIN),
        '诺基亚' => array(1 => self::VALID_UNICODE_DOMAIN),
        '食品' => array(1 => self::VALID_UNICODE_DOMAIN),
        '飞利浦' => array(1 => self::VALID_UNICODE_DOMAIN),
        '台湾' => array(1 => self::VALID_UNICODE_DOMAIN),
        '台灣' => array(1 => self::VALID_UNICODE_DOMAIN),
        '手表' => array(1 => self::VALID_UNICODE_DOMAIN),
        '手机' => array(1 => self::VALID_UNICODE_DOMAIN),
        'мон' => array(1 => self::VALID_UNICODE_DOMAIN),
        'الجزائر' => array(1 => self::VALID_UNICODE_DOMAIN),
        'عمان' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ارامكو' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ایران' => array(1 => self::VALID_UNICODE_DOMAIN),
        'امارات' => array(1 => self::VALID_UNICODE_DOMAIN),
        'بازار' => array(1 => self::VALID_UNICODE_DOMAIN),
        'الاردن' => array(1 => self::VALID_UNICODE_DOMAIN),
        'موبايلي' => array(1 => self::VALID_UNICODE_DOMAIN),
        'بھارت' => array(1 => self::VALID_UNICODE_DOMAIN),
        'المغرب' => array(1 => self::VALID_UNICODE_DOMAIN),
        'السعودية' => array(1 => self::VALID_UNICODE_DOMAIN),
        'سودان' => array(1 => self::VALID_UNICODE_DOMAIN),
        'همراه' => array(1 => self::VALID_UNICODE_DOMAIN),
        'عراق' => array(1 => self::VALID_UNICODE_DOMAIN),
        'مليسيا' => array(1 => self::VALID_UNICODE_DOMAIN),
        '澳門' => array(1 => self::VALID_UNICODE_DOMAIN),
        '닷컴' => array(1 => self::VALID_UNICODE_DOMAIN),
        '政府' => array(1 => self::VALID_UNICODE_DOMAIN),
        'شبكة' => array(1 => self::VALID_UNICODE_DOMAIN),
        'بيتك' => array(1 => self::VALID_UNICODE_DOMAIN),
        'გე' => array(1 => self::VALID_UNICODE_DOMAIN),
        '机构' => array(1 => self::VALID_UNICODE_DOMAIN),
        '组织机构' => array(1 => self::VALID_UNICODE_DOMAIN),
        '健康' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ไทย' => array(1 => self::VALID_UNICODE_DOMAIN),
        'سورية' => array(1 => self::VALID_UNICODE_DOMAIN),
        'рус' => array(1 => self::VALID_UNICODE_DOMAIN),
        'рф' => array(1 => self::VALID_UNICODE_DOMAIN),
        '珠宝' => array(1 => self::VALID_UNICODE_DOMAIN),
        'تونس' => array(1 => self::VALID_UNICODE_DOMAIN),
        '大拿' => array(1 => self::VALID_UNICODE_DOMAIN),
        'みんな' => array(1 => self::VALID_UNICODE_DOMAIN),
        'グーグル' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ελ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '世界' => array(1 => self::VALID_UNICODE_DOMAIN),
        '書籍' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ਭਾਰਤ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网址' => array(1 => self::VALID_UNICODE_DOMAIN),
        '닷넷' => array(1 => self::VALID_UNICODE_DOMAIN),
        'コム' => array(1 => self::VALID_UNICODE_DOMAIN),
        '游戏' => array(1 => self::VALID_UNICODE_DOMAIN),
        'VERMöGENSBERATER' => array(1 => self::VALID_UNICODE_DOMAIN),
        'VERMöGENSBERATUNG' => array(1 => self::VALID_UNICODE_DOMAIN),
        '企业' => array(1 => self::VALID_UNICODE_DOMAIN),
        '信息' => array(1 => self::VALID_UNICODE_DOMAIN),
        '嘉里大酒店' => array(1 => self::VALID_UNICODE_DOMAIN),
        'مصر' => array(1 => self::VALID_UNICODE_DOMAIN),
        'قطر' => array(1 => self::VALID_UNICODE_DOMAIN),
        '广东' => array(1 => self::VALID_UNICODE_DOMAIN),
        'இலங்கை' => array(1 => self::VALID_UNICODE_DOMAIN),
        'இந்தியா' => array(1 => self::VALID_UNICODE_DOMAIN),
        'հայ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '新加坡' => array(1 => self::VALID_UNICODE_DOMAIN),
        'فلسطين' => array(1 => self::VALID_UNICODE_DOMAIN),
        '政务' => array(1 => self::VALID_UNICODE_DOMAIN),
        '家電' => array(1 => self::VALID_UNICODE_DOMAIN),
        '微博' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ابوظبي' => array(1 => self::VALID_UNICODE_DOMAIN),
    );


    protected $_idnLength = array(
        'BIZ' => array(5 => 17, 11 => 15, 12 => 20),
        'CN'  => array(1 => 20),
        'COM' => array(3 => 17, 5 => 20),
        'HK'  => array(1 => 15),
        'INFO'=> array(4 => 17),
        'KR'  => array(1 => 17),
        'NET' => array(3 => 17, 5 => 20),
        'ORG' => array(6 => 17),
        'TW'  => array(1 => 20),
        'ایران' => array(1 => 30),
        '中国' => array(1 => 20),
        '公司' => array(1 => 20),
        '网络' => array(1 => 20),
    );

    protected $_options = array(
        'allow' => self::ALLOW_DNS,
        'idn'   => true,
        'tld'   => true,
        'ip'    => null
    );

    /**
     * Sets validator options
     *
     * @param integer          $allow       OPTIONAL Set what types of hostname to allow (default ALLOW_DNS)
     * @param boolean          $validateIdn OPTIONAL Set whether IDN domains are validated (default true)
     * @param boolean          $validateTld OPTIONAL Set whether the TLD element of a hostname is validated (default true)
     * @param Zend_Validate_Ip $ipValidator OPTIONAL
     * @return void
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['idn'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['tld'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['ip'] = array_shift($options);
            }

            $options = $temp;
        }

        $options += $this->_options;
        $this->setOptions($options);
    }

    /**
     * Returns all set options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the options for this validator
     *
     * @param array $options
     * @return Zend_Validate_Hostname
     */
    public function setOptions($options)
    {
        if (array_key_exists('allow', $options)) {
            $this->setAllow($options['allow']);
        }

        if (array_key_exists('idn', $options)) {
            $this->setValidateIdn($options['idn']);
        }

        if (array_key_exists('tld', $options)) {
            $this->setValidateTld($options['tld']);
        }

        if (array_key_exists('ip', $options)) {
            $this->setIpValidator($options['ip']);
        }

        return $this;
    }

    /**
     * Returns the set ip validator
     *
     * @return Zend_Validate_Ip
     */
    public function getIpValidator()
    {
        return $this->_options['ip'];
    }

    /**
     * @param Zend_Validate_Ip $ipValidator OPTIONAL
     * @return void;
     */
    public function setIpValidator(Zend_Validate_Ip $ipValidator = null)
    {
        if ($ipValidator === null) {
            $ipValidator = new Zend_Validate_Ip();
        }

        $this->_options['ip'] = $ipValidator;
        return $this;
    }

    /**
     * Returns the allow option
     *
     * @return integer
     */
    public function getAllow()
    {
        return $this->_options['allow'];
    }

    /**
     * Sets the allow option
     *
     * @param  integer $allow
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->_options['allow'] = $allow;
        return $this;
    }

    /**
     * Returns the set idn option
     *
     * @return boolean
     */
    public function getValidateIdn()
    {
        return $this->_options['idn'];
    }

    /**
     * Set whether IDN domains are validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $allowed Set allowed to true to validate IDNs, and false to not validate them
     */
    public function setValidateIdn ($allowed)
    {
        $this->_options['idn'] = (bool) $allowed;
        return $this;
    }

    /**
     * Returns the set tld option
     *
     * @return boolean
     */
    public function getValidateTld()
    {
        return $this->_options['tld'];
    }

    /**
     * Set whether the TLD element of a hostname is validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $allowed Set allowed to true to validate TLDs, and false to not validate them
     */
    public function setValidateTld ($allowed)
    {
        $this->_options['tld'] = (bool) $allowed;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the $value is a valid hostname with respect to the current allow option
     *
     * @param  string $value
     * @throws Zend_Validate_Exception if a fatal error occurs for validation process
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        // Check input against IP address schema
        if (preg_match('/^[0-9a-f:.]*$/i', $value) &&
            $this->_options['ip']->setTranslator($this->getTranslator())->isValid($value)) {
            if (!($this->_options['allow'] & self::ALLOW_IP)) {
                $this->_error(self::IP_ADDRESS_NOT_ALLOWED);
                return false;
            } else {
                return true;
            }
        }

        // RFC3986 3.2.2 states:
        // 
        //     The rightmost domain label of a fully qualified domain name
        //     in DNS may be followed by a single "." and should be if it is 
        //     necessary to distinguish between the complete domain name and
        //     some local domain.
        //     
        // (see ZF-6363)
        
        // Local hostnames are allowed to be partitial (ending '.')
        if ($this->_options['allow'] & self::ALLOW_LOCAL) {
            if (substr($value, -1) === '.') {
                $value = substr($value, 0, -1);
                if (substr($value, -1) === '.') {
                    // Empty hostnames (ending '..') are not allowed
                    $this->_error(self::INVALID_LOCAL_NAME);
                    return false;
                }
            }
        }

        $domainParts = explode('.', $value);

        // Prevent partitial IP V4 adresses (ending '.')
        if ((count($domainParts) == 4) && preg_match('/^[0-9.a-e:.]*$/i', $value) &&
            $this->_options['ip']->setTranslator($this->getTranslator())->isValid($value)) {
            $this->_error(self::INVALID_LOCAL_NAME);
        }

        // Check input against DNS hostname schema
        if ((count($domainParts) > 1) && (strlen($value) >= 4) && (strlen($value) <= 254)) {
            $status = false;

            $origenc = PHP_VERSION_ID < 50600
                ? iconv_get_encoding('internal_encoding')
                : ini_get('default_charset');
            if (PHP_VERSION_ID < 50600) {
                iconv_set_encoding('internal_encoding', 'UTF-8');
            } else {
                @ini_set('default_charset', 'UTF-8');
            }

            do {
                // First check TLD
                $matches = array();
                if (preg_match('/([^.]{2,63})$/i', end($domainParts), $matches) ||
                    (end($domainParts) == 'ایران') || (end($domainParts) == '中国') ||
                    (end($domainParts) == '公司') || (end($domainParts) == '网络')) {

                    reset($domainParts);

                    // Hostname characters are: *(label dot)(label dot label); max 254 chars
                    // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
                    // id-prefix: alpha / digit
                    // ldh: alpha / digit / dash

                    // Match TLD against known list
                    $this->_tld = strtolower($matches[1]);
                    if ($this->_options['tld']) {
                        if (!in_array($this->_tld, $this->_validTlds)) {
                            $this->_error(self::UNKNOWN_TLD);
                            $status = false;
                            break;
                        }
                    }

                    /**
                     * Match against IDN hostnames
                     * Note: Keep label regex short to avoid issues with long patterns when matching IDN hostnames
                     * @see Zend_Validate_Hostname_Interface
                     */
                    $regexChars = array(0 => '/^[a-z0-9\x2d]{1,63}$/i');
                    if ($this->_options['idn'] &&  isset($this->_validIdns[strtoupper($this->_tld)])) {
                        if (is_string($this->_validIdns[strtoupper($this->_tld)])) {
                            $regexChars += include(dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->_validIdns[strtoupper($this->_tld)]);
                        } else {
                            $regexChars += $this->_validIdns[strtoupper($this->_tld)];
                        }
                    }

                    // Check each hostname part
                    $check = 0;
                    foreach ($domainParts as $domainPart) {
                        // Decode Punycode domainnames to IDN
                        if (strpos($domainPart, 'xn--') === 0) {
                            $domainPart = $this->decodePunycode(substr($domainPart, 4));
                            if ($domainPart === false) {
                                return false;
                            }
                        }

                        // Check dash (-) does not start, end or appear in 3rd and 4th positions
                        if ((strpos($domainPart, '-') === 0)
                            || ((strlen($domainPart) > 2) && (strpos($domainPart, '-', 2) == 2) && (strpos($domainPart, '-', 3) == 3))
                            || (strpos($domainPart, '-') === (strlen($domainPart) - 1))) {
                                $this->_error(self::INVALID_DASH);
                            $status = false;
                            break 2;
                        }

                        // Check each domain part
                        $checked = false;
                        foreach($regexChars as $regexKey => $regexChar) {
                            $status = @preg_match($regexChar, $domainPart);
                            if ($status > 0) {
                                $length = 63;
                                if (array_key_exists(strtoupper($this->_tld), $this->_idnLength)
                                    && (array_key_exists($regexKey, $this->_idnLength[strtoupper($this->_tld)]))) {
                                    $length = $this->_idnLength[strtoupper($this->_tld)];
                                }

                                if (iconv_strlen($domainPart, 'UTF-8') > $length) {
                                    $this->_error(self::INVALID_HOSTNAME);
                                } else {
                                    $checked = true;
                                    break;
                                }
                            }
                        }

                        if ($checked) {
                            ++$check;
                        }
                    }

                    // If one of the labels doesn't match, the hostname is invalid
                    if ($check !== count($domainParts)) {
                        $this->_error(self::INVALID_HOSTNAME_SCHEMA);
                        $status = false;
                    }
                } else {
                    // Hostname not long enough
                    $this->_error(self::UNDECIPHERABLE_TLD);
                    $status = false;
                }
            } while (false);

            if (PHP_VERSION_ID < 50600) {
                iconv_set_encoding('internal_encoding', $origenc);
            } else {
                @ini_set('default_charset', $origenc);
            }

            // If the input passes as an Internet domain name, and domain names are allowed, then the hostname
            // passes validation
            if ($status && ($this->_options['allow'] & self::ALLOW_DNS)) {
                return true;
            }
        } else if ($this->_options['allow'] & self::ALLOW_DNS) {
            $this->_error(self::INVALID_HOSTNAME);
        }

        // Check for URI Syntax (RFC3986)
        if ($this->_options['allow'] & self::ALLOW_URI) {
            if (preg_match("/^([a-zA-Z0-9-._~!$&'()*+,;=]|%[[:xdigit:]]{2}){1,254}$/i", $value)) {
                return true;
            } else {
                $this->_error(self::INVALID_URI);
            }
        }

        // Check input against local network name schema; last chance to pass validation
        $regexLocal = '/^(([a-zA-Z0-9\x2d]{1,63}\x2e)*[a-zA-Z0-9\x2d]{1,63}[\x2e]{0,1}){1,254}$/';
        $status = @preg_match($regexLocal, $value);

        // If the input passes as a local network name, and local network names are allowed, then the
        // hostname passes validation
        $allowLocal = $this->_options['allow'] & self::ALLOW_LOCAL;
        if ($status && $allowLocal) {
            return true;
        }

        // If the input does not pass as a local network name, add a message
        if (!$status) {
            $this->_error(self::INVALID_LOCAL_NAME);
        }

        // If local network names are not allowed, add a message
        if ($status && !$allowLocal) {
            $this->_error(self::LOCAL_NAME_NOT_ALLOWED);
        }

        return false;
    }

    /**
     * Decodes a punycode encoded string to it's original utf8 string
     * In case of a decoding failure the original string is returned
     *
     * @param  string $encoded Punycode encoded string to decode
     * @return string
     */
    protected function decodePunycode($encoded)
    {
        $found = preg_match('/([^a-z0-9\x2d]{1,10})$/i', $encoded);
        if (empty($encoded) || ($found > 0)) {
            // no punycode encoded string, return as is
            $this->_error(self::CANNOT_DECODE_PUNYCODE);
            return false;
        }

        $separator = strrpos($encoded, '-');
        if ($separator > 0) {
            for ($x = 0; $x < $separator; ++$x) {
                // prepare decoding matrix
                $decoded[] = ord($encoded[$x]);
            }
        } else {
            $this->_error(self::CANNOT_DECODE_PUNYCODE);
            return false;
        }

        $lengthd = count($decoded);
        $lengthe = strlen($encoded);

        // decoding
        $init  = true;
        $base  = 72;
        $index = 0;
        $char  = 0x80;

        for ($indexe = ($separator) ? ($separator + 1) : 0; $indexe < $lengthe; ++$lengthd) {
            for ($old_index = $index, $pos = 1, $key = 36; 1 ; $key += 36) {
                $hex   = ord($encoded[$indexe++]);
                $digit = ($hex - 48 < 10) ? $hex - 22
                       : (($hex - 65 < 26) ? $hex - 65
                       : (($hex - 97 < 26) ? $hex - 97
                       : 36));

                $index += $digit * $pos;
                $tag    = ($key <= $base) ? 1 : (($key >= $base + 26) ? 26 : ($key - $base));
                if ($digit < $tag) {
                    break;
                }

                $pos = (int) ($pos * (36 - $tag));
            }

            $delta   = intval($init ? (($index - $old_index) / 700) : (($index - $old_index) / 2));
            $delta  += intval($delta / ($lengthd + 1));
            for ($key = 0; $delta > 910 / 2; $key += 36) {
                $delta = intval($delta / 35);
            }

            $base   = intval($key + 36 * $delta / ($delta + 38));
            $init   = false;
            $char  += (int) ($index / ($lengthd + 1));
            $index %= ($lengthd + 1);
            if ($lengthd > 0) {
                for ($i = $lengthd; $i > $index; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }

            $decoded[$index++] = $char;
        }

        // convert decoded ucs4 to utf8 string
        foreach ($decoded as $key => $value) {
            if ($value < 128) {
                $decoded[$key] = chr($value);
            } elseif ($value < (1 << 11)) {
                $decoded[$key]  = chr(192 + ($value >> 6));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 16)) {
                $decoded[$key]  = chr(224 + ($value >> 12));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 21)) {
                $decoded[$key]  = chr(240 + ($value >> 18));
                $decoded[$key] .= chr(128 + (($value >> 12) & 63));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } else {
                $this->_error(self::CANNOT_DECODE_PUNYCODE);
                return false;
            }
        }

        return implode($decoded);
    }
}
