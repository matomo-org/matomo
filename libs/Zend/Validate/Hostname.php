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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
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

    const ALLOW_DNS   = 1;  // Allows Internet domain names (e.g., example.com)
    const ALLOW_IP    = 2;  // Allows IP addresses
    const ALLOW_LOCAL = 4;  // Allows local network names (e.g., localhost, www.localdomain)
    const ALLOW_URI   = 8;  // Allows URI hostnames
    const ALLOW_ALL   = 15;  // Allows all types of hostnames

    /**
     * Array of valid top-level-domains
     *
     * IanaVersion 2018013101 -- with manual additions!
     *
     * @see http://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/ Official list of supported TLDs
     * @var array
     */
    protected $_validTlds = array(
        'aaa',
        'aarp',
        'abarth',
        'abb',
        'abbott',
        'abbvie',
        'abc',
        'able',
        'abogado',
        'abudhabi',
        'ac',
        'academy',
        'accenture',
        'accountant',
        'accountants',
        'aco',
        'active',
        'actor',
        'ad',
        'adac',
        'ads',
        'adult',
        'ae',
        'aeg',
        'aero',
        'aetna',
        'af',
        'afamilycompany',
        'afl',
        'africa',
        'ag',
        'agakhan',
        'agency',
        'ai',
        'aig',
        'aigo',
        'airbus',
        'airforce',
        'airtel',
        'akdn',
        'al',
        'alfaromeo',
        'alibaba',
        'alipay',
        'allfinanz',
        'allstate',
        'ally',
        'alsace',
        'alstom',
        'am',
        'americanexpress',
        'americanfamily',
        'amex',
        'amfam',
        'amica',
        'amsterdam',
        'analytics',
        'android',
        'anquan',
        'anz',
        'ao',
        'aol',
        'apartments',
        'app',
        'apple',
        'aq',
        'aquarelle',
        'ar',
        'arab',
        'aramco',
        'archi',
        'army',
        'arpa',
        'art',
        'arte',
        'as',
        'asda',
        'asia',
        'associates',
        'at',
        'athleta',
        'attorney',
        'au',
        'auction',
        'audi',
        'audible',
        'audio',
        'auspost',
        'author',
        'auto',
        'autos',
        'avianca',
        'aw',
        'aws',
        'ax',
        'axa',
        'az',
        'azure',
        'ba',
        'baby',
        'baidu',
        'banamex',
        'bananarepublic',
        'band',
        'bank',
        'bar',
        'barcelona',
        'barclaycard',
        'barclays',
        'barefoot',
        'bargains',
        'baseball',
        'basketball',
        'bauhaus',
        'bayern',
        'bb',
        'bbc',
        'bbt',
        'bbva',
        'bcg',
        'bcn',
        'bd',
        'be',
        'beats',
        'beauty',
        'beer',
        'bentley',
        'berlin',
        'best',
        'bestbuy',
        'bet',
        'bf',
        'bg',
        'bh',
        'bharti',
        'bi',
        'bible',
        'bid',
        'bike',
        'bing',
        'bingo',
        'bio',
        'biz',
        'bj',
        'black',
        'blackfriday',
        'blanco',
        'blockbuster',
        'blog',
        'bloomberg',
        'blue',
        'bm',
        'bms',
        'bmw',
        'bn',
        'bnl',
        'bnpparibas',
        'bo',
        'boats',
        'boehringer',
        'bofa',
        'bom',
        'bond',
        'boo',
        'book',
        'booking',
        'boots',
        'bosch',
        'bostik',
        'boston',
        'bot',
        'boutique',
        'box',
        'br',
        'bradesco',
        'bridgestone',
        'broadway',
        'broker',
        'brother',
        'brussels',
        'bs',
        'bt',
        'budapest',
        'bugatti',
        'build',
        'builders',
        'business',
        'buy',
        'buzz',
        'bv',
        'bw',
        'by',
        'bz',
        'bzh',
        'ca',
        'cab',
        'cafe',
        'cal',
        'call',
        'calvinklein',
        'cam',
        'camera',
        'camp',
        'cancerresearch',
        'canon',
        'capetown',
        'capital',
        'capitalone',
        'car',
        'caravan',
        'cards',
        'care',
        'career',
        'careers',
        'cars',
        'cartier',
        'casa',
        'case',
        'caseih',
        'cash',
        'casino',
        'cat',
        'catering',
        'catholic',
        'cba',
        'cbn',
        'cbre',
        'cbs',
        'cc',
        'cd',
        'ceb',
        'center',
        'ceo',
        'cern',
        'cf',
        'cfa',
        'cfd',
        'cg',
        'ch',
        'chanel',
        'channel',
        'chase',
        'chat',
        'cheap',
        'chintai',
        'christmas',
        'chrome',
        'chrysler',
        'church',
        'ci',
        'cipriani',
        'circle',
        'cisco',
        'citadel',
        'citi',
        'citic',
        'city',
        'cityeats',
        'ck',
        'cl',
        'claims',
        'cleaning',
        'click',
        'clinic',
        'clinique',
        'clothing',
        'cloud',
        'club',
        'clubmed',
        'cm',
        'cn',
        'co',
        'coach',
        'codes',
        'coffee',
        'college',
        'cologne',
        'com',
        'comcast',
        'commbank',
        'community',
        'company',
        'compare',
        'computer',
        'comsec',
        'condos',
        'construction',
        'consulting',
        'contact',
        'contractors',
        'cooking',
        'cookingchannel',
        'cool',
        'coop',
        'corsica',
        'country',
        'coupon',
        'coupons',
        'courses',
        'cr',
        'credit',
        'creditcard',
        'creditunion',
        'cricket',
        'crown',
        'crs',
        'cruise',
        'cruises',
        'csc',
        'cu',
        'cuisinella',
        'cv',
        'cw',
        'cx',
        'cy',
        'cymru',
        'cyou',
        'cz',
        'dabur',
        'dad',
        'dance',
        'data',
        'date',
        'dating',
        'datsun',
        'day',
        'dclk',
        'dds',
        'de',
        'deal',
        'dealer',
        'deals',
        'degree',
        'delivery',
        'dell',
        'deloitte',
        'delta',
        'democrat',
        'dental',
        'dentist',
        'desi',
        'design',
        'dev',
        'dhl',
        'diamonds',
        'diet',
        'digital',
        'direct',
        'directory',
        'discount',
        'discover',
        'dish',
        'diy',
        'dj',
        'dk',
        'dm',
        'dnp',
        'do',
        'docs',
        'doctor',
        'dodge',
        'dog',
        'doha',
        'domains',
        'dot',
        'download',
        'drive',
        'dtv',
        'dubai',
        'duck',
        'dunlop',
        'duns',
        'dupont',
        'durban',
        'dvag',
        'dvr',
        'dz',
        'earth',
        'eat',
        'ec',
        'eco',
        'edeka',
        'edu',
        'education',
        'ee',
        'eg',
        'email',
        'emerck',
        'energy',
        'engineer',
        'engineering',
        'enterprises',
        'epost',
        'epson',
        'equipment',
        'er',
        'ericsson',
        'erni',
        'es',
        'esq',
        'estate',
        'esurance',
        'et',
        'etisalat',
        'eu',
        'eurovision',
        'eus',
        'events',
        'everbank',
        'exchange',
        'expert',
        'exposed',
        'express',
        'extraspace',
        'fage',
        'fail',
        'fairwinds',
        'faith',
        'family',
        'fan',
        'fans',
        'farm',
        'farmers',
        'fashion',
        'fast',
        'fedex',
        'feedback',
        'ferrari',
        'ferrero',
        'fi',
        'fiat',
        'fidelity',
        'fido',
        'film',
        'final',
        'finance',
        'financial',
        'fire',
        'firestone',
        'firmdale',
        'fish',
        'fishing',
        'fit',
        'fitness',
        'fj',
        'fk',
        'flickr',
        'flights',
        'flir',
        'florist',
        'flowers',
        'fly',
        'fm',
        'fo',
        'foo',
        'food',
        'foodnetwork',
        'football',
        'ford',
        'forex',
        'forsale',
        'forum',
        'foundation',
        'fox',
        'fr',
        'free',
        'fresenius',
        'frl',
        'frogans',
        'frontdoor',
        'frontier',
        'ftr',
        'fujitsu',
        'fujixerox',
        'fun',
        'fund',
        'furniture',
        'futbol',
        'fyi',
        'ga',
        'gal',
        'gallery',
        'gallo',
        'gallup',
        'game',
        'games',
        'gap',
        'garden',
        'gb',
        'gbiz',
        'gd',
        'gdn',
        'ge',
        'gea',
        'gent',
        'genting',
        'george',
        'gf',
        'gg',
        'ggee',
        'gh',
        'gi',
        'gift',
        'gifts',
        'gives',
        'giving',
        'gl',
        'glade',
        'glass',
        'gle',
        'global',
        'globo',
        'gm',
        'gmail',
        'gmbh',
        'gmo',
        'gmx',
        'gn',
        'godaddy',
        'gold',
        'goldpoint',
        'golf',
        'goo',
        'goodhands',
        'goodyear',
        'goog',
        'google',
        'gop',
        'got',
        'gov',
        'gp',
        'gq',
        'gr',
        'grainger',
        'graphics',
        'gratis',
        'green',
        'gripe',
        'grocery',
        'group',
        'gs',
        'gt',
        'gu',
        'guardian',
        'gucci',
        'guge',
        'guide',
        'guitars',
        'guru',
        'gw',
        'gy',
        'hair',
        'hamburg',
        'hangout',
        'haus',
        'hbo',
        'hdfc',
        'hdfcbank',
        'health',
        'healthcare',
        'help',
        'helsinki',
        'here',
        'hermes',
        'hgtv',
        'hiphop',
        'hisamitsu',
        'hitachi',
        'hiv',
        'hk',
        'hkt',
        'hm',
        'hn',
        'hockey',
        'holdings',
        'holiday',
        'homedepot',
        'homegoods',
        'homes',
        'homesense',
        'honda',
        'honeywell',
        'horse',
        'hospital',
        'host',
        'hosting',
        'hot',
        'hoteles',
        'hotels',
        'hotmail',
        'house',
        'how',
        'hr',
        'hsbc',
        'ht',
        'hu',
        'hughes',
        'hyatt',
        'hyundai',
        'ibm',
        'icbc',
        'ice',
        'icu',
        'id',
        'ie',
        'ieee',
        'ifm',
        'ikano',
        'il',
        'im',
        'imamat',
        'imdb',
        'immo',
        'immobilien',
        'in',
        'industries',
        'infiniti',
        'info',
        'ing',
        'ink',
        'institute',
        'insurance',
        'insure',
        'int',
        'intel',
        'international',
        'intuit',
        'investments',
        'io',
        'ipiranga',
        'iq',
        'ir',
        'irish',
        'is',
        'iselect',
        'ismaili',
        'ist',
        'istanbul',
        'it',
        'itau',
        'itv',
        'iveco',
        'iwc',
        'jaguar',
        'java',
        'jcb',
        'jcp',
        'je',
        'jeep',
        'jetzt',
        'jewelry',
        'jio',
        'jlc',
        'jll',
        'jm',
        'jmp',
        'jnj',
        'jo',
        'jobs',
        'joburg',
        'jot',
        'joy',
        'jp',
        'jpmorgan',
        'jprs',
        'juegos',
        'juniper',
        'kaufen',
        'kddi',
        'ke',
        'kerryhotels',
        'kerrylogistics',
        'kerryproperties',
        'kfh',
        'kg',
        'kh',
        'ki',
        'kia',
        'kim',
        'kinder',
        'kindle',
        'kitchen',
        'kiwi',
        'km',
        'kn',
        'koeln',
        'komatsu',
        'kosher',
        'kp',
        'kpmg',
        'kpn',
        'kr',
        'krd',
        'kred',
        'kuokgroup',
        'kw',
        'ky',
        'kyoto',
        'kz',
        'la',
        'lacaixa',
        'ladbrokes',
        'lamborghini',
        'lamer',
        'lancaster',
        'lancia',
        'lancome',
        'land',
        'landrover',
        'lanxess',
        'lasalle',
        'lat',
        'latino',
        'latrobe',
        'law',
        'lawyer',
        'lb',
        'lc',
        'lds',
        'lease',
        'leclerc',
        'lefrak',
        'legal',
        'lego',
        'lexus',
        'lgbt',
        'li',
        'liaison',
        'lidl',
        'life',
        'lifeinsurance',
        'lifestyle',
        'lighting',
        'like',
        'lilly',
        'limited',
        'limo',
        'lincoln',
        'linde',
        'link',
        'lipsy',
        'live',
        'living',
        'lixil',
        'lk',
        'loan',
        'loans',
        'locker',
        'locus',
        'loft',
        'lol',
        'london',
        'lotte',
        'lotto',
        'love',
        'lpl',
        'lplfinancial',
        'lr',
        'ls',
        'lt',
        'ltd',
        'ltda',
        'lu',
        'lundbeck',
        'lupin',
        'luxe',
        'luxury',
        'lv',
        'ly',
        'ma',
        'macys',
        'madrid',
        'maif',
        'maison',
        'makeup',
        'man',
        'management',
        'mango',
        'map',
        'market',
        'marketing',
        'markets',
        'marriott',
        'marshalls',
        'maserati',
        'mattel',
        'mba',
        'mc',
        'mckinsey',
        'md',
        'me',
        'med',
        'media',
        'meet',
        'melbourne',
        'meme',
        'memorial',
        'men',
        'menu',
        'meo',
        'merckmsd',
        'metlife',
        'mg',
        'mh',
        'miami',
        'microsoft',
        'mil',
        'mini',
        'mint',
        'mit',
        'mitsubishi',
        'mk',
        'ml',
        'mlb',
        'mls',
        'mm',
        'mma',
        'mn',
        'mo',
        'mobi',
        'mobile',
        'mobily',
        'moda',
        'moe',
        'moi',
        'mom',
        'monash',
        'money',
        'monster',
        'mopar',
        'mormon',
        'mortgage',
        'moscow',
        'moto',
        'motorcycles',
        'mov',
        'movie',
        'movistar',
        'mp',
        'mq',
        'mr',
        'ms',
        'msd',
        'mt',
        'mtn',
        'mtr',
        'mu',
        'museum',
        'mutual',
        'mv',
        'mw',
        'mx',
        'my',
        'mz',
        'na',
        'nab',
        'nadex',
        'nagoya',
        'name',
        'nationwide',
        'natura',
        'navy',
        'nba',
        'nc',
        'ne',
        'nec',
        'net',
        'netbank',
        'netflix',
        'network',
        'neustar',
        'new',
        'newholland',
        'news',
        'next',
        'nextdirect',
        'nexus',
        'nf',
        'nfl',
        'ng',
        'ngo',
        'nhk',
        'ni',
        'nico',
        'nike',
        'nikon',
        'ninja',
        'nissan',
        'nissay',
        'nl',
        'no',
        'nokia',
        'northwesternmutual',
        'norton',
        'now',
        'nowruz',
        'nowtv',
        'np',
        'nr',
        'nra',
        'nrw',
        'ntt',
        'nu',
        'nyc',
        'nz',
        'obi',
        'observer',
        'off',
        'office',
        'okinawa',
        'olayan',
        'olayangroup',
        'oldnavy',
        'ollo',
        'om',
        'omega',
        'one',
        'ong',
        'onl',
        'online',
        'onyourside',
        'ooo',
        'open',
        'oracle',
        'orange',
        'org',
        'organic',
        'origins',
        'osaka',
        'otsuka',
        'ott',
        'ovh',
        'pa',
        'page',
        'panasonic',
        'panerai',
        'paris',
        'pars',
        'partners',
        'parts',
        'party',
        'passagens',
        'pay',
        'pccw',
        'pe',
        'pet',
        'pf',
        'pfizer',
        'pg',
        'ph',
        'pharmacy',
        'phd',
        'philips',
        'phone',
        'photo',
        'photography',
        'photos',
        'physio',
        'piaget',
        'pics',
        'pictet',
        'pictures',
        'pid',
        'pin',
        'ping',
        'pink',
        'pioneer',
        'pizza',
        'pk',
        'pl',
        'place',
        'play',
        'playstation',
        'plumbing',
        'plus',
        'pm',
        'pn',
        'pnc',
        'pohl',
        'poker',
        'politie',
        'porn',
        'post',
        'pr',
        'pramerica',
        'praxi',
        'press',
        'prime',
        'pro',
        'prod',
        'productions',
        'prof',
        'progressive',
        'promo',
        'properties',
        'property',
        'protection',
        'pru',
        'prudential',
        'ps',
        'pt',
        'pub',
        'pw',
        'pwc',
        'py',
        'qa',
        'qpon',
        'quebec',
        'quest',
        'qvc',
        'racing',
        'radio',
        'raid',
        're',
        'read',
        'realestate',
        'realtor',
        'realty',
        'recipes',
        'red',
        'redstone',
        'redumbrella',
        'rehab',
        'reise',
        'reisen',
        'reit',
        'reliance',
        'ren',
        'rent',
        'rentals',
        'repair',
        'report',
        'republican',
        'rest',
        'restaurant',
        'review',
        'reviews',
        'rexroth',
        'rich',
        'richardli',
        'ricoh',
        'rightathome',
        'ril',
        'rio',
        'rip',
        'rmit',
        'ro',
        'rocher',
        'rocks',
        'rodeo',
        'rogers',
        'room',
        'rs',
        'rsvp',
        'ru',
        'rugby',
        'ruhr',
        'run',
        'rw',
        'rwe',
        'ryukyu',
        'sa',
        'saarland',
        'safe',
        'safety',
        'sakura',
        'sale',
        'salon',
        'samsclub',
        'samsung',
        'sandvik',
        'sandvikcoromant',
        'sanofi',
        'sap',
        'sapo',
        'sarl',
        'sas',
        'save',
        'saxo',
        'sb',
        'sbi',
        'sbs',
        'sc',
        'sca',
        'scb',
        'schaeffler',
        'schmidt',
        'scholarships',
        'school',
        'schule',
        'schwarz',
        'science',
        'scjohnson',
        'scor',
        'scot',
        'sd',
        'se',
        'search',
        'seat',
        'secure',
        'security',
        'seek',
        'select',
        'sener',
        'services',
        'ses',
        'seven',
        'sew',
        'sex',
        'sexy',
        'sfr',
        'sg',
        'sh',
        'shangrila',
        'sharp',
        'shaw',
        'shell',
        'shia',
        'shiksha',
        'shoes',
        'shop',
        'shopping',
        'shouji',
        'show',
        'showtime',
        'shriram',
        'si',
        'silk',
        'sina',
        'singles',
        'site',
        'sj',
        'sk',
        'ski',
        'skin',
        'sky',
        'skype',
        'sl',
        'sling',
        'sm',
        'smart',
        'smile',
        'sn',
        'sncf',
        'so',
        'soccer',
        'social',
        'softbank',
        'software',
        'sohu',
        'solar',
        'solutions',
        'song',
        'sony',
        'soy',
        'space',
        'spiegel',
        'sport',
        'spot',
        'spreadbetting',
        'sr',
        'srl',
        'srt',
        'st',
        'stada',
        'staples',
        'star',
        'starhub',
        'statebank',
        'statefarm',
        'statoil',
        'stc',
        'stcgroup',
        'stockholm',
        'storage',
        'store',
        'stream',
        'studio',
        'study',
        'style',
        'su',
        'sucks',
        'supplies',
        'supply',
        'support',
        'surf',
        'surgery',
        'suzuki',
        'sv',
        'swatch',
        'swiftcover',
        'swiss',
        'sx',
        'sy',
        'sydney',
        'symantec',
        'systems',
        'sz',
        'tab',
        'taipei',
        'talk',
        'taobao',
        'target',
        'tatamotors',
        'tatar',
        'tattoo',
        'tax',
        'taxi',
        'tc',
        'tci',
        'td',
        'tdk',
        'team',
        'tech',
        'technology',
        'tel',
        'telecity',
        'telefonica',
        'temasek',
        'tennis',
        'teva',
        'tf',
        'tg',
        'th',
        'thd',
        'theater',
        'theatre',
        'tiaa',
        'tickets',
        'tienda',
        'tiffany',
        'tips',
        'tires',
        'tirol',
        'tj',
        'tjmaxx',
        'tjx',
        'tk',
        'tkmaxx',
        'tl',
        'tm',
        'tmall',
        'tn',
        'to',
        'today',
        'tokyo',
        'tools',
        'top',
        'toray',
        'toshiba',
        'total',
        'tours',
        'town',
        'toyota',
        'toys',
        'tr',
        'trade',
        'trading',
        'training',
        'travel',
        'travelchannel',
        'travelers',
        'travelersinsurance',
        'trust',
        'trv',
        'tt',
        'tube',
        'tui',
        'tunes',
        'tushu',
        'tv',
        'tvs',
        'tw',
        'tz',
        'ua',
        'ubank',
        'ubs',
        'uconnect',
        'ug',
        'uk',
        'unicom',
        'university',
        'uno',
        'uol',
        'ups',
        'us',
        'uy',
        'uz',
        'va',
        'vacations',
        'vana',
        'vanguard',
        'vc',
        've',
        'vegas',
        'ventures',
        'verisign',
        'versicherung',
        'vet',
        'vg',
        'vi',
        'viajes',
        'video',
        'vig',
        'viking',
        'villas',
        'vin',
        'vip',
        'virgin',
        'visa',
        'vision',
        'vista',
        'vistaprint',
        'viva',
        'vivo',
        'vlaanderen',
        'vn',
        'vodka',
        'volkswagen',
        'volvo',
        'vote',
        'voting',
        'voto',
        'voyage',
        'vu',
        'vuelos',
        'wales',
        'walmart',
        'walter',
        'wang',
        'wanggou',
        'warman',
        'watch',
        'watches',
        'weather',
        'weatherchannel',
        'webcam',
        'weber',
        'website',
        'wed',
        'wedding',
        'weibo',
        'weir',
        'wf',
        'whoswho',
        'wien',
        'wiki',
        'williamhill',
        'win',
        'windows',
        'wine',
        'winners',
        'wme',
        'wolterskluwer',
        'woodside',
        'work',
        'works',
        'world',
        'wow',
        'ws',
        'wtc',
        'wtf',
        'xbox',
        'xerox',
        'xfinity',
        'xihuan',
        'xin',
        'कॉम',
        'セール',
        '佛山',
        'ಭಾರತ',
        '慈善',
        '集团',
        '在线',
        '한국',
        'ଭାରତ',
        '大众汽车',
        '点看',
        'คอม',
        'ভাৰত',
        'ভারত',
        '八卦',
        'موقع',
        'বাংলা',
        '公益',
        '公司',
        '香格里拉',
        '网站',
        '移动',
        '我爱你',
        'москва',
        'қаз',
        'католик',
        'онлайн',
        'сайт',
        '联通',
        'срб',
        'бг',
        'бел',
        'קום',
        '时尚',
        '微博',
        '淡马锡',
        'ファッション',
        'орг',
        'नेट',
        'ストア',
        '삼성',
        'சிங்கப்பூர்',
        '商标',
        '商店',
        '商城',
        'дети',
        'мкд',
        'ею',
        'ポイント',
        '新闻',
        '工行',
        '家電',
        'كوم',
        '中文网',
        '中信',
        '中国',
        '中國',
        '娱乐',
        '谷歌',
        'భారత్',
        'ලංකා',
        '電訊盈科',
        '购物',
        'クラウド',
        'ભારત',
        '通販',
        'भारतम्',
        'भारत',
        'भारोत',
        '网店',
        'संगठन',
        '餐厅',
        '网络',
        'ком',
        'укр',
        '香港',
        '诺基亚',
        '食品',
        '飞利浦',
        '台湾',
        '台灣',
        '手表',
        '手机',
        'мон',
        'الجزائر',
        'عمان',
        'ارامكو',
        'ایران',
        'العليان',
        'اتصالات',
        'امارات',
        'بازار',
        'پاکستان',
        'الاردن',
        'موبايلي',
        'بارت',
        'بھارت',
        'المغرب',
        'ابوظبي',
        'السعودية',
        'ڀارت',
        'كاثوليك',
        'سودان',
        'همراه',
        'عراق',
        'مليسيا',
        '澳門',
        '닷컴',
        '政府',
        'شبكة',
        'بيتك',
        'عرب',
        'გე',
        '机构',
        '组织机构',
        '健康',
        'ไทย',
        'سورية',
        '招聘',
        'рус',
        'рф',
        '珠宝',
        'تونس',
        '大拿',
        'みんな',
        'グーグル',
        'ελ',
        '世界',
        '書籍',
        'ഭാരതം',
        'ਭਾਰਤ',
        '网址',
        '닷넷',
        'コム',
        '天主教',
        '游戏',
        'vermögensberater',
        'vermögensberatung',
        '企业',
        '信息',
        '嘉里大酒店',
        '嘉里',
        'مصر',
        'قطر',
        '广东',
        'இலங்கை',
        'இந்தியா',
        'հայ',
        '新加坡',
        'فلسطين',
        '政务',
        'xperia',
        'xxx',
        'xyz',
        'yachts',
        'yahoo',
        'yamaxun',
        'yandex',
        'ye',
        'yodobashi',
        'yoga',
        'yokohama',
        'you',
        'youtube',
        'yt',
        'yun',
        'za',
        'zappos',
        'zara',
        'zero',
        'zip',
        'zippo',
        'zm',
        'zone',
        'zuerich',
        'zw',

        // Not assigned
        'bl',
        'bq',
        'chloe',
        'eh',
        'htc',
        'mcd',
        'mcdonalds',
        'mf',
        'montblanc',
        'pamperedchef',
        'ss',
        'um',
        'موريتانيا',

        // Test
        '测试',
        'परीक्षा',
        'испытание',
        '테스트',
        'טעסט',
        '測試',
        'آزمایشی',
        'பரிட்சை',
        'δοκιμή',
        'إختبار',
        'テスト',

        // Retired
        'an',
        'doosan',
        'flsmidth',
        'iinet',
        'mtpc',
        'mutuelle',
        'orientexpress',
        'tp',
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
     * (.CA) Canada http://www.iana.org/domains/idn-tables/tables/ca_fr_1.0.html
     * (.CAT) Catalan http://www.iana.org/domains/idn-tables/tables/cat_ca_1.0.html
     * (.CH) Switzerland https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.CL) Chile http://www.iana.org/domains/idn-tables/tables/cl_latn_1.0.html
     * (.COM) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.DE) Germany http://www.denic.de/en/domains/idns/liste.html
     * (.DK) Danmark http://www.dk-hostmaster.dk/index.php?id=151
     * (.EE) Estonia https://www.iana.org/domains/idn-tables/tables/pl_et-pl_1.0.html
     * (.ES) Spain https://www.nic.es/media/2008-05/1210147705287.pdf
     * (.FI) Finland http://www.ficora.fi/en/index/palvelut/fiverkkotunnukset/aakkostenkaytto.html
     * (.GR) Greece https://grweb.ics.forth.gr/CharacterTable1_en.jsp
     * (.HR) Croatia https://www.dns.hr/en/portal/files/Odluka-1,2alfanum-dijak.pdf
     * (.HU) Hungary http://www.domain.hu/domain/English/szabalyzat/szabalyzat.html
     * (.INFO) International http://www.nic.info/info/idn
     * (.IL) Israel http://www.isoc.org.il/domains/il-domain-rules.html
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
     * (.RS) Serbia http://www.iana.org/domains/idn-tables/tables/rs_sr-rs_1.0.pdf
     * (.SA) Saudi Arabia http://www.iana.org/domains/idn-tables/tables/sa_ar_1.0.html
     * (.SE) Sweden http://www.iis.se/english/IDN_campaignsite.shtml?lang=en
     * (.SH) Saint Helena http://www.nic.sh/SH-IDN-Policy.pdf
     * (.SJ) Svalbard and Jan Mayen http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.TH) Thailand http://www.iana.org/domains/idn-tables/tables/th_th-th_1.0.html
     * (.TM) Turkmenistan http://www.nic.tm/TM-IDN-Policy.pdf
     * (.TR) Turkey https://www.nic.tr/index.php
     * (.UA) Ukraine http://www.iana.org/domains/idn-tables/tables/ua_cyrl_1.2.html
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
        'CA'  => array(1 => '/^[\x{002d}0-9a-zàâæçéèêëîïôœùûüÿ\x{00E0}\x{00E2}\x{00E7}\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{00EE}\x{00EF}\x{00F4}\x{00F9}\x{00FB}\x{00FC}\x{00E6}\x{0153}\x{00FF}]{1,63}$/iu'),
        'CAT' => array(1 => '/^[\x{002d}0-9a-z·àç-éíïòóúü]{1,63}$/iu'),
        'CH'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'),
        'CL'  => array(1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'),
        'CN'  => 'Hostname/Cn.php',
        'COM' => 'Hostname/Com.php',
        'DE'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťßţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'DK'  => array(1 => '/^[\x{002d}0-9a-zäåæéöøü]{1,63}$/iu'),
        'EE'  => array(1 => '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu'),
        'ES'  => array(1 => '/^[\x{002d}0-9a-zàáçèéíïñòóúü·]{1,63}$/iu'),
        'EU'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-zΐάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώ]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюя]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zἀ-ἇἐ-ἕἠ-ἧἰ-ἷὀ-ὅὐ-ὗὠ-ὧὰ-ὼώᾀ-ᾇᾐ-ᾗᾠ-ᾧᾰ-ᾴᾶᾷῂῃῄῆῇῐ-ῒΐῖῗῠ-ῧῲῳῴῶῷ]{1,63}$/iu'),
        'FI'  => array(1 => '/^[\x{002d}0-9a-zäåö]{1,63}$/iu'),
        'GR'  => array(1 => '/^[\x{002d}0-9a-zΆΈΉΊΌΎ-ΡΣ-ώἀ-ἕἘ-Ἕἠ-ὅὈ-Ὅὐ-ὗὙὛὝὟ-ώᾀ-ᾴᾶ-ᾼῂῃῄῆ-ῌῐ-ΐῖ-Ίῠ-Ῥῲῳῴῶ-ῼ]{1,63}$/iu'),
        'HK'  => 'Hostname/Cn.php',
        'HR'  => array(1 => '/^[\x{002d}0-9a-zžćčđš]{1,63}$/iu'),
        'HU'  => array(1 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu'),
        'IL'  => array(1 => '/^[\x{002d}0-9\x{05D0}-\x{05EA}]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-z]{1,63}$/i'),
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
        'IT'  => array(1 => '/^[\x{002d}0-9a-zàâäèéêëìîïòôöùûüæœçÿß-]{1,63}$/iu'),
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
            10 => '/^[\x{002d}0-9a-záäéíóôúýčďĺľňŕšťž]{1,63}$/iu',
            11 => '/^[\x{002d}0-9a-zçë]{1,63}$/iu',
            12 => '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu',
            13 => '/^[\x{002d}0-9a-zćčđšž]{1,63}$/iu',
            14 => '/^[\x{002d}0-9a-zâçöûüğış]{1,63}$/iu',
            15 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            16 => '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu',
            17 => '/^[\x{002d}0-9a-zĉĝĥĵŝŭ]{1,63}$/iu',
            18 => '/^[\x{002d}0-9a-zâäéëîô]{1,63}$/iu',
            19 => '/^[\x{002d}0-9a-zàáâäåæçèéêëìíîïðñòôöøùúûüýćčłńřśš]{1,63}$/iu',
            20 => '/^[\x{002d}0-9a-zäåæõöøüšž]{1,63}$/iu',
            21 => '/^[\x{002d}0-9a-zàáçèéìíòóùú]{1,63}$/iu',
            22 => '/^[\x{002d}0-9a-zàáéíóöúüőű]{1,63}$/iu',
            23 => '/^[\x{002d}0-9ΐά-ώ]{1,63}$/iu',
            24 => '/^[\x{002d}0-9a-zàáâåæçèéêëðóôöøüþœ]{1,63}$/iu',
            25 => '/^[\x{002d}0-9a-záäéíóöúüýčďěňřšťůž]{1,63}$/iu',
            26 => '/^[\x{002d}0-9a-z·àçèéíïòóúü]{1,63}$/iu',
            27 => '/^[\x{002d}0-9а-ъьюя\x{0450}\x{045D}]{1,63}$/iu',
            28 => '/^[\x{002d}0-9а-яёіў]{1,63}$/iu',
            29 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            30 => '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            31 => '/^[\x{002d}0-9a-zàâæçèéêëîïñôùûüÿœ]{1,63}$/iu',
            32 => '/^[\x{002d}0-9а-щъыьэюяёєіїґ]{1,63}$/iu',
            33 => '/^[\x{002d}0-9א-ת]{1,63}$/iu'),
        'PR'  => array(1 => '/^[\x{002d}0-9a-záéíóúñäëïüöâêîôûàèùæçœãõ]{1,63}$/iu'),
        'PT'  => array(1 => '/^[\x{002d}0-9a-záàâãçéêíóôõú]{1,63}$/iu'),
        'RS'  => array(1 => '/^[\x{002D}\x{0030}-\x{0039}\x{0061}-\x{007A}\x{0107}\x{010D}\x{0111}\x{0161}\x{017E}]{1,63}$/iu)'),
        'RU'  => array(1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'),
        'SA'  => array(1 => '/^[\x{002d}.0-9\x{0621}-\x{063A}\x{0641}-\x{064A}\x{0660}-\x{0669}]{1,63}$/iu'),
        'SE'  => array(1 => '/^[\x{002d}0-9a-zäåéöü]{1,63}$/iu'),
        'SH'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'SI'  => array(
            1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu'),
        'SJ'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'TH'  => array(1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'),
        'TM'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'),
        'TW'  => 'Hostname/Cn.php',
        'TR'  => array(1 => '/^[\x{002d}0-9a-zğıüşöç]{1,63}$/iu'),
        'UA'  => array(1 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюяѐёђѓєѕіїјљњћќѝўџґӂʼ]{1,63}$/iu'),
        'VE'  => array(1 => '/^[\x{002d}0-9a-záéíóúüñ]{1,63}$/iu'),
        'VN'  => array(1 => '/^[ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚÝàáâãèéêìíòóôõùúýĂăĐđĨĩŨũƠơƯư\x{1EA0}-\x{1EF9}]{1,63}$/iu'),
        'कॉम' => array(1 => self::VALID_UNICODE_DOMAIN),
        'セール' => array(1 => self::VALID_UNICODE_DOMAIN),
        '佛山' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ಭಾರತ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '慈善' => array(1 => self::VALID_UNICODE_DOMAIN),
        '集团' => array(1 => self::VALID_UNICODE_DOMAIN),
        '在线' => array(1 => self::VALID_UNICODE_DOMAIN),
        '한국' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ଭାରତ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '大众汽车' => array(1 => self::VALID_UNICODE_DOMAIN),
        '点看' => array(1 => self::VALID_UNICODE_DOMAIN),
        'คอม' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ভাৰত' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ভারত' => array(1 => self::VALID_UNICODE_DOMAIN),
        '八卦' => array(1 => self::VALID_UNICODE_DOMAIN),
        'موقع' => array(1 => self::VALID_UNICODE_DOMAIN),
        'বাংলা' => array(1 => self::VALID_UNICODE_DOMAIN),
        '公益' => array(1 => self::VALID_UNICODE_DOMAIN),
        '公司' => array(1 => self::VALID_UNICODE_DOMAIN),
        '香格里拉' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网站' => array(1 => self::VALID_UNICODE_DOMAIN),
        '移动' => array(1 => self::VALID_UNICODE_DOMAIN),
        '我爱你' => array(1 => self::VALID_UNICODE_DOMAIN),
        'москва' => array(1 => self::VALID_UNICODE_DOMAIN),
        'қаз' => array(1 => self::VALID_UNICODE_DOMAIN),
        'католик' => array(1 => self::VALID_UNICODE_DOMAIN),
        'онлайн' => array(1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'),
        'сайт' => array(1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'),
        '联通' => array(1 => self::VALID_UNICODE_DOMAIN),
        'срб' => array(1 => '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu'),
        'бг' => array(1 => self::VALID_UNICODE_DOMAIN),
        'бел' => array(1 => self::VALID_UNICODE_DOMAIN),
        'קום' => array(1 => self::VALID_UNICODE_DOMAIN),
        '时尚' => array(1 => self::VALID_UNICODE_DOMAIN),
        '微博' => array(1 => self::VALID_UNICODE_DOMAIN),
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
        '家電' => array(1 => self::VALID_UNICODE_DOMAIN),
        'كوم' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中文网' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中信' => array(1 => self::VALID_UNICODE_DOMAIN),
        '中国' => 'Hostname/Cn.php',
        '中國' => 'Hostname/Cn.php',
        '娱乐' => array(1 => self::VALID_UNICODE_DOMAIN),
        '谷歌' => array(1 => self::VALID_UNICODE_DOMAIN),
        'భారత్' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ලංකා' => array(1 => '/^[\x{0d80}-\x{0dff}]{1,63}$/iu'),
        '電訊盈科' => array(1 => self::VALID_UNICODE_DOMAIN),
        '购物' => array(1 => self::VALID_UNICODE_DOMAIN),
        'クラウド' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ભારત' => array(1 => self::VALID_UNICODE_DOMAIN),
        '通販' => array(1 => self::VALID_UNICODE_DOMAIN),
        'भारतम्' => array(1 => self::VALID_UNICODE_DOMAIN),
        'भारत' => array(1 => self::VALID_UNICODE_DOMAIN),
        'भारोत' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网店' => array(1 => self::VALID_UNICODE_DOMAIN),
        'संगठन' => array(1 => self::VALID_UNICODE_DOMAIN),
        '餐厅' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网络' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ком' => array(1 => self::VALID_UNICODE_DOMAIN),
        'укр' => array(1 => self::VALID_UNICODE_DOMAIN),
        '香港' => 'Hostname/Cn.php',
        '诺基亚' => array(1 => self::VALID_UNICODE_DOMAIN),
        '食品' => array(1 => self::VALID_UNICODE_DOMAIN),
        '飞利浦' => array(1 => self::VALID_UNICODE_DOMAIN),
        '台湾' => 'Hostname/Cn.php',
        '台灣' => 'Hostname/Cn.php',
        '手表' => array(1 => self::VALID_UNICODE_DOMAIN),
        '手机' => array(1 => self::VALID_UNICODE_DOMAIN),
        'мон' => array(1 => '/^[\x{002d}0-9\x{0430}-\x{044F}]{1,63}$/iu'),
        'الجزائر' => array(1 => self::VALID_UNICODE_DOMAIN),
        'عمان' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ارامكو' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ایران' => array(1 => self::VALID_UNICODE_DOMAIN),
        'العليان' => array(1 => self::VALID_UNICODE_DOMAIN),
        'اتصالات' => array(1 => self::VALID_UNICODE_DOMAIN),
        'امارات' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'بازار' => array(1 => self::VALID_UNICODE_DOMAIN),
        'پاکستان' => array(1 => self::VALID_UNICODE_DOMAIN),
        'الاردن' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'موبايلي' => array(1 => self::VALID_UNICODE_DOMAIN),
        'بارت' => array(1 => self::VALID_UNICODE_DOMAIN),
        'بھارت' => array(1 => self::VALID_UNICODE_DOMAIN),
        'المغرب' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ابوظبي' => array(1 => self::VALID_UNICODE_DOMAIN),
        'السعودية' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'ڀارت' => array(1 => self::VALID_UNICODE_DOMAIN),
        'كاثوليك' => array(1 => self::VALID_UNICODE_DOMAIN),
        'سودان' => array(1 => self::VALID_UNICODE_DOMAIN),
        'همراه' => array(1 => self::VALID_UNICODE_DOMAIN),
        'عراق' => array(1 => self::VALID_UNICODE_DOMAIN),
        'مليسيا' => array(1 => self::VALID_UNICODE_DOMAIN),
        '澳門' => array(1 => self::VALID_UNICODE_DOMAIN),
        '닷컴' => array(1 => self::VALID_UNICODE_DOMAIN),
        '政府' => array(1 => self::VALID_UNICODE_DOMAIN),
        'شبكة' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'بيتك' => array(1 => self::VALID_UNICODE_DOMAIN),
        'عرب' => array(1 => self::VALID_UNICODE_DOMAIN),
        'გე' => array(1 => self::VALID_UNICODE_DOMAIN),
        '机构' => array(1 => self::VALID_UNICODE_DOMAIN),
        '组织机构' => array(1 => self::VALID_UNICODE_DOMAIN),
        '健康' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ไทย' => array(1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'),
        'سورية' => array(1 => self::VALID_UNICODE_DOMAIN),
        '招聘' => array(1 => self::VALID_UNICODE_DOMAIN),
        'рус' => array(1 => self::VALID_UNICODE_DOMAIN),
        'рф' => array(1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'),
        '珠宝' => array(1 => self::VALID_UNICODE_DOMAIN),
        'تونس' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        '大拿' => array(1 => self::VALID_UNICODE_DOMAIN),
        'みんな' => array(1 => self::VALID_UNICODE_DOMAIN),
        'グーグル' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ελ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '世界' => array(1 => self::VALID_UNICODE_DOMAIN),
        '書籍' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ഭാരതം' => array(1 => self::VALID_UNICODE_DOMAIN),
        'ਭਾਰਤ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '网址' => array(1 => self::VALID_UNICODE_DOMAIN),
        '닷넷' => array(1 => self::VALID_UNICODE_DOMAIN),
        'コム' => array(1 => self::VALID_UNICODE_DOMAIN),
        '天主教' => array(1 => self::VALID_UNICODE_DOMAIN),
        '游戏' => array(1 => self::VALID_UNICODE_DOMAIN),
        'VERMöGENSBERATER' => array(1 => self::VALID_UNICODE_DOMAIN),
        'VERMöGENSBERATUNG' => array(1 => self::VALID_UNICODE_DOMAIN),
        '企业' => array(1 => self::VALID_UNICODE_DOMAIN),
        '信息' => array(1 => self::VALID_UNICODE_DOMAIN),
        '嘉里大酒店' => array(1 => self::VALID_UNICODE_DOMAIN),
        '嘉里' => array(1 => self::VALID_UNICODE_DOMAIN),
        'مصر' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'قطر' => array(1 => self::VALID_UNICODE_DOMAIN),
        '广东' => array(1 => self::VALID_UNICODE_DOMAIN),
        'இலங்கை' => array(1 => '/^[\x{0b80}-\x{0bff}]{1,63}$/iu'),
        'இந்தியா' => array(1 => self::VALID_UNICODE_DOMAIN),
        'հայ' => array(1 => self::VALID_UNICODE_DOMAIN),
        '新加坡' => array(1 => self::VALID_UNICODE_DOMAIN),
        'فلسطين' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        '政务' => array(1 => self::VALID_UNICODE_DOMAIN),
    );

    protected $_idnLength = array(
        'BIZ' => [5 => 17, 11 => 15, 12 => 20],
        'CN'  => [1 => 20],
        'COM' => [3 => 17, 5 => 20],
        'HK'  => [1 => 15],
        'INFO' => [4 => 17],
        'KR'  => [1 => 17],
        'NET' => [3 => 17, 5 => 20],
        'ORG' => [6 => 17],
        'TW'  => [1 => 20],
        'امارات' => [1 => 30],
        'الاردن' => [1 => 30],
        'السعودية' => [1 => 30],
        'تونس' => [1 => 30],
        'مصر' => [1 => 30],
        'فلسطين' => [1 => 30],
        'شبكة' => [1 => 30],
        '中国' => [1 => 20],
        '中國' => [1 => 20],
        '香港' => [1 => 20],
        '台湾' => [1 => 20],
        '台灣' => [1 => 20],
    );

    /**
     * Options for the hostname validator
     *
     * @var array
     */
    protected $_options = array(
        'allow' => self::ALLOW_DNS, // Allow these hostnames
        'idn'   => true,  // Check IDN domains
        'tld'   => true,  // Check TLD elements
        'ip'    => null,  // IP validator to use
    );

    /**
     * Sets validator options
     *
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     * @param array $options Validator options
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
     * @return Zend_Validate_Hostname
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
     * @return $this
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
     * @return $this
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
        if (((preg_match('/^[0-9.]*$/', $value) && strpos($value, '.') !== false)
                || (preg_match('/^[0-9a-f:.]*$/i', $value) !== false))
            && $this->_options['ip']->setTranslator($this->getTranslator())->isValid($value)
        ) {
            if (!($this->_options['allow'] & self::ALLOW_IP)) {
                $this->_error(self::IP_ADDRESS_NOT_ALLOWED);
                return false;
            }

            return true;
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
                if (preg_match('/([^.]{2,63})$/iu', end($domainParts), $matches)
                    || (array_key_exists(end($domainParts), $this->_validIdns))) {
                    reset($domainParts);

                    // Hostname characters are: *(label dot)(label dot label); max 254 chars
                    // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
                    // id-prefix: alpha / digit
                    // ldh: alpha / digit / dash

                    // Match TLD against known list
                    $this->_tld = $matches[1];
                    // Decode Punycode TLD to IDN
                    if (strpos($this->_tld, 'xn--') === 0) {
                        $this->_tld = $this->decodePunycode(substr($this->_tld, 4));
                        if ($this->_tld === false) {
                            return false;
                        }
                    } else {
                        $this->_tld = strtoupper($this->_tld);
                    }

                    if ($this->_options['tld']) {
                        if (!in_array(strtolower($this->_tld), $this->_validTlds)
                            && !in_array($this->_tld, $this->_validTlds)) {
                            $this->_error(self::UNKNOWN_TLD);
                            $status = false;
                            break;
                        }
                        // We have already validated that the TLD is fine. We don't want it to go through the below
                        // checks as new UTF-8 TLDs will incorrectly fail if there is no IDN regex for it.
                        array_pop($domainParts);
                    }

                    /**
                     * Match against IDN hostnames
                     * Note: Keep label regex short to avoid issues with long patterns when matching IDN hostnames
                     * @see Zend_Validate_Hostname_Interface
                     */
                    $regexChars = array(0 => '/^[a-z0-9\x2d]{1,63}$/i');
                    if ($this->_options['idn'] &&  isset($this->_validIdns[$this->_tld])) {
                        if (is_string($this->_validIdns[$this->_tld])) {
                            $regexChars += include(__DIR__ . DIRECTORY_SEPARATOR . $this->_validIdns[$this->_tld]);
                        } else {
                            $regexChars += $this->_validIdns[$this->_tld];
                        }
                    }

                    // Check each hostname part
                    $check = 0;
                    foreach ($domainParts as $domainPart) {
                        // If some domain part is empty (i.e. zend..com), it's invalid
                        if (empty($domainPart) && $domainPart !== '0') {
                            $this->_error(self::INVALID_HOSTNAME);
                            return false;
                        }

                        // Decode Punycode domain names to IDN
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
                            // workaround for: preg_match(): Compilation failed: regular expression is too large
                            $pattern = strlen($regexChar) < 256 ? $regexChar : self::VALID_UNICODE_DOMAIN;
                            $status = preg_match($pattern, $domainPart);
                            if ($status > 0) {
                                $length = 63;
                                if (array_key_exists($this->_tld, $this->_idnLength)
                                    && (array_key_exists($regexKey, $this->_idnLength[$this->_tld]))) {
                                    $length = $this->_idnLength[$this->_tld];
                                }

                                if (iconv_strlen($domainPart, 'UTF-8') > $length) {
                                    $this->_error(self::INVALID_HOSTNAME);
                                    $status = false;
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
            if (preg_match("/^([a-zA-Z0-9-._~!$&\'()*+,;=]|%[[:xdigit:]]{2}){1,254}$/i", $value)) {
                return true;
            }

            $this->_error(self::INVALID_URI);
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
     * Returns false in case of a decoding failure.
     *
     * @param  string $encoded Punycode encoded string to decode
     * @return string|false
     */
    protected function decodePunycode($encoded)
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $encoded)) {
            // no punycode encoded string
            $this->_error(self::CANNOT_DECODE_PUNYCODE);
            return false;
        }

        $decoded = array();
        $separator = strrpos($encoded, '-');
        if ($separator > 0) {
            for ($x = 0; $x < $separator; ++$x) {
                // prepare decoding matrix
                $decoded[] = ord($encoded[$x]);
            }
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
