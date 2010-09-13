<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package DataFiles
 */

/**
 * Search Engine database
 *
 * ======================================
 * HOW TO ADD A SEARCH ENGINE TO THE LIST
 * ======================================
 * If you want to add a new entry, please email us the information + icon at
 * hello at piwik.org
 *
 * See also: http://piwik.org/faq/general/#faq_39
 *
 * Detail of a line:
 * Url => array( SearchEngineName, KeywordParameter, [path containing the keyword], [charset used by the search engine])
 *
 * The main search engine URL has to be at the top of the list for the given
 * search Engine.  This serves as the master record so additional URLs
 * don't have to duplicate all the information, but can override when needed.
 * 
 * The URL, "example.com", will match "example.com", "m.example.com",
 * "www.example.com", and "search.example.com".
 *
 * For region-specific search engines, the URL, "{}.example.com" will match
 * any ISO3166-1 alpha2 country code against "{}".  Similarly, "example.{}"
 * will match against valid country TLDs, but should be used sparingly to
 * avoid false positives.
 *
 * You can add new search engines icons by adding the icon in the
 * plugins/Referers/images/searchEngines directory using the format
 * 'mainSearchEngineUrl.png'. Example: www.google.com.png
 *
 * To help Piwik link directly the search engine result page for the keyword,
 * specify the third entry in the array using the macro {k} that will
 * automatically be replaced by the keyword.
 *
 * A simple example is:
 *  'www.google.com'		=> array('Google', 'q', 'search?q={k}'),
 *
 * A more complicated example, with an array of possible variable names, and a custom charset:
 *  'www.baidu.com'			=> array('Baidu', array('wd', 'word', 'kw'), 's?wd={k}', 'gb2312'),
 *
 * Another example using a regular expression to parse the path for keywords:
 *  'infospace.com'         => array('InfoSpace', array('/dir1\/(pattern)\/dir2/'), '/dir1/{k}/dir2/stuff/'),
 */
if(!isset($GLOBALS['Piwik_SearchEngines'] ))
{
	$GLOBALS['Piwik_SearchEngines'] = array(
		// 1
		'1.cz'						=> array('1.cz', 'q', 'index.php?q={k}', 'iso-8859-2'),

		// 123people
		'www.123people.com'			=> array('123people', '/s\/([^\/]+)/', 's/{k}'),
		'www.123people.de'			=> array('123people'),
		'www.123people.es'			=> array('123people'),
		'www.123people.fr'			=> array('123people'),
		'www.123people.it'			=> array('123people'),

		// 1und1
		'search.1und1.de'			=> array('1und1', 'su', 'search/web/?su={k}'),

		// Abacho
		'www.abacho.de'				=> array('Abacho', 'q', 'suche?q={k}'),
		'www.abacho.com'			=> array('Abacho'),
		'www.abacho.co.uk'			=> array('Abacho'),
		'www.se.abacho.com'			=> array('Abacho'),
		'www.tr.abacho.com'			=> array('Abacho'),
		'www.abacho.at'				=> array('Abacho'),
		'www.abacho.fr'				=> array('Abacho'),
		'www.abacho.es'				=> array('Abacho'),
		'www.abacho.ch'				=> array('Abacho'),
		'www.abacho.it'				=> array('Abacho'),
	
		// ABCsøk
		'abcsok.no'					=> array('ABCsøk', 'q', '?q={k}'),

		// about
		'search.about.com'			=> array('About', 'terms', '?terms={k}'),

		// Acoon
		'www.acoon.de'				=> array('Acoon', 'begriff', 'cgi-bin/search.exe?begriff={k}'),

		// Alexa
		'alexa.com'				=> array('Alexa', 'q', 'search?q={k}'),

		// Alice Adsl
		'rechercher.aliceadsl.fr'	=> array('Alice Adsl', 'qs', 'google.pl?qs={k}'),

		// Allesklar
		'www.allesklar.de'			=> array('Allesklar', 'words', '?words={k}'),
		'www.allesklar.at'			=> array('Allesklar'),
		'www.allesklar.ch'			=> array('Allesklar'),

		// AllTheWeb
		'www.alltheweb.com'			=> array('AllTheWeb', 'q', 'search?q={k}'),

		// all.by
		'all.by'					=> array('All.by', 'query', 'cgi-bin/search.cgi?mode=by&query={k}'),

		// Altavista
		'www.altavista.com'			=> array('AltaVista', 'q', 'web/results?q={k}'),
		'search.altavista.com'		=> array('AltaVista'),
		'listings.altavista.com'	=> array('AltaVista'),
		'altavista.de'				=> array('AltaVista'),
		'altavista.fr'				=> array('AltaVista'),
		'{}.altavista.com'			=> array('AltaVista'),
		'be-nl.altavista.com'		=> array('AltaVista'),
		'be-fr.altavista.com'		=> array('AltaVista'),

		// Apollo Latvia
		'apollo.lv/portal/search/'	=> array('Apollo lv', 'q', '?cof=FORID%3A11&q={k}&search_where=www'),

		// APOLLO7
		'apollo7.de'			=> array('Apollo7', 'query', 'a7db/index.php?query={k}&de_sharelook=true&de_bing=true&de_witch=true&de_google=true&de_yahoo=true&de_lycos=true'),

		// AOL
		'search.aol.com'			=> array('AOL', array('query', 'q'), 'aol/search?q={k}'),
		'aolsearch.aol.com'			=> array('AOL'),
		'www.aolrecherche.aol.fr'	=> array('AOL'),
		'www.aolrecherches.aol.fr'	=> array('AOL'),
		'www.aolimages.aol.fr'		=> array('AOL'),
		'aim.search.aol.com'		=> array('AOL'),
		'www.recherche.aol.fr'		=> array('AOL'),
		'find.web.aol.com'			=> array('AOL'),
		'recherche.aol.ca'			=> array('AOL'),
		'aolsearch.aol.co.uk'		=> array('AOL'),
		'search.aol.co.uk'			=> array('AOL'),
		'aolrecherche.aol.fr'		=> array('AOL'),
		'sucheaol.aol.de'			=> array('AOL'),
		'suche.aol.de'				=> array('AOL'),
		'suche.aolsvc.de'			=> array('AOL'),
		'aolbusqueda.aol.com.mx'	=> array('AOL'),
		'alicesuchet.aol.de'		=> array('AOL'),
		'suche.aolsvc.de'			=> array('AOL'),
		'suche.aol.de'				=> array('AOL'),
		'alicesuche.aol.de'			=> array('AOL'),
		'suchet2.aol.de'			=> array('AOL'),
		'search.hp.my.aol.com.au'	=> array('AOL'),
		'search.hp.my.aol.de'		=> array('AOL'),
		'search.hp.my.aol.it'		=> array('AOL'),
		'search-intl.netscape.com'	=> array('AOL'),

		// Aport
		'sm.aport.ru'				=> array('Aport', 'r', 'search?r={k}'),

		// arama
		'arama.com'					=> array('Arama', 'q', 'search.php3?q={k}'),
	
		// Arcor
		'www.arcor.de'				=> array('Arcor', 'Keywords', 'content/searchresult.jsp?Keywords={k}'),

		// Arianna (Libero.it)
		'arianna.libero.it'			=> array('Arianna', 'query', 'search/abin/integrata.cgi?query={k}'),

		// Ask
		'www.ask.com'				=> array('Ask', array('ask', 'q'), 'web?q={k}'),
		'web.ask.com'				=> array('Ask'),
		'images.ask.com'			=> array('Ask'),
		'ask.reference.com'			=> array('Ask'),
		'iwon.ask.com'				=> array('Ask'),
		'www.ask.co.uk'				=> array('Ask'),
		'{}.ask.com'				=> array('Ask'),

		// Atlas
		'searchatlas.centrum.cz'	=> array('Atlas', 'q', '?q={k}'),

		// Austronaut
		'www2.austronaut.at'		=> array('Austronaut', 'q'),
		'www1.austronaut.at'		=> array('Austronaut'),
	
		// Babylon (Enhanced by Google)
		'search.babylon.com'		=> array('Babylon', 'q', '?q={k}'),

		// Baidu
		'www.baidu.com'				=> array('Baidu', array('wd', 'word', 'kw'), 's?wd={k}', 'gb2312'),
		'www1.baidu.com'			=> array('Baidu'),
		'zhidao.baidu.com'			=> array('Baidu'),
		'tieba.baidu.com'			=> array('Baidu'),
		'news.baidu.com'			=> array('Baidu'),
		'web.gougou.com'			=> array('Baidu', 'search', 'search?search={k}'), // uses baidu search
	
		// Bellnet
		'www.suchmaschine.com'		=> array('Bellnet', 'suchstr', 'cgi-bin/bellnet.cgi?suchstr={k}'),

		// Biglobe
		'cgi.search.biglobe.ne.jp'	=> array('Biglobe', 'q', 'cgi-bin/search-st?q={k}'),

		// Bing
		'www.bing.com'				=> array('Bing', array('q', 'Q'), 'search?q={k}'),
		'm.bing.com'				=> array('Bing'),

		// Bing Images
		'www.bing.com/images/search'=> array('Bing Images', array('q', 'Q'), '?q={k}'),

		// Blogdigger
		'www.blogdigger.com'		=> array('Blogdigger', 'q'),

		// Blogpulse
		'www.blogpulse.com'			=> array('Blogpulse', 'query', 'search?query={k}'),

		// Bluewin
		'search.bluewin.ch'			=> array('Bluewin', 'searchTerm', '?searchTerm={k}'),

		// canoe.ca
		'web.canoe.ca'				=> array('Canoe.ca', 'q', 'search?q={k}'),

		// Centrum
		'search.centrum.cz'			=> array('Centrum', 'q', '?q={k}'),
		'morfeo.centrum.cz'			=> array('Centrum'),

		// Charter
		'www.charter.net'			=> array('Charter', 'q', 'search/index.php?q={k}'),

		// Clix (Enhanced by Google)
		'pesquisa.clix.pt'			=> array('Clix', 'question', 'resultado.html?in=Mundial&question={k}'),

		// Conduit
		'search.conduit.com'		=> array('Conduit.com', 'q', 'Results.aspx?q={k}'),

		// Comcast
		'search.comcast.net'		=> array('Comcast', 'q', '?q={k}'),

		// Compuserve
		'websearch.cs.com'			=> array('Compuserve.com (Enhanced by Google)', 'query', 'cs/search?query={k}'),

		// Cuil
		'www.cuil.com'				=> array('Cuil', 'q', 'search?q={k}'),

		// Daemon search
		'daemon-search.com'		=> array('Daemon search', 'q', 'explore/web?q={k}'),

		// DasOertliche
		'www.dasoertliche.de'		=> array('DasOertliche', 'kw'),

		// DasTelefonbuch
		'www1.dastelefonbuch.de'	=> array('DasTelefonbuch', 'kw'),

		// Delfi Latvia
		'smart.delfi.lv'			=> array('Delfi lv', 'q', 'find?q={k}'),

		// Delfi
		'otsing.delfi.ee'			=> array('Delfi EE', 'q', 'find?q={k}'),

		// Digg
		'digg.com'					=> array('Digg', 's', 'search?s={k}'),

		// dir.com
		'fr.dir.com'				=> array('dir.com', 'req'),

		// dmoz
		'dmoz.org'					=> array('dmoz', 'search'),
		'editors.dmoz.org'			=> array('dmoz'),

		// DuckDuckGo
		'duckduckgo.com'			=> array('DuckDuckGo', 'q', '?q={k}'),

		// earthlink
		'search.earthlink.net'		=> array('Earthlink', 'q', 'search?q={k}'),

		// Ecosia (powered by Bing)
		'ecosia.org'				=> array('Ecosia', 'q', 'search.php?q={k}'),

		// Eniro
		'www.eniro.se'				=> array('Eniro', array('q', 'search_word'), 'query?q={k}'),

		// Eudip
		'www.eudip.com'				=> array('Eudip', ''),

		// Eurip
		'www.eurip.com'				=> array('Eurip', 'q', 'search/?q={k}'),

		// Euroseek
		'www.euroseek.com'			=> array('Euroseek', 'string', 'system/search.cgi?string={k}'),

		// Everyclick
		'www.everyclick.com'		=> array('Everyclick', 'keyword'),

		// Excite
		'search.excite.it'			=> array('Excite', 'q', 'web/?q={k}'),
		'search.excite.fr'			=> array('Excite'),
		'search.excite.de'			=> array('Excite'),
		'search.excite.co.uk'		=> array('Excite'),
		'search.excite.es'			=> array('Excite'),
		'search.excite.nl'			=> array('Excite'),
		'msxml.excite.com'			=> array('Excite', '/\/[^\/]+\/ws\/results\/[^\/]+\/([^\/]+)/'),
		'www.excite.co.jp'			=> array('Excite', 'search', 'search.gw?search={k}', 'SHIFT_JIS'),

		// Exalead
		'www.exalead.fr'			=> array('Exalead', 'q', 'search/results?q={k}'),
		'www.exalead.com'			=> array('Exalead'),

		// eo
		'eo.st'						=> array('eo', 'x_query', 'cgi-bin/eolost.cgi?x_query={k}'),

		// Facebook
		'www.facebook.com'			=> array('Facebook', 'q', 'search/?q={k}'),

		// Fast Browser Search
		'www.fastbrowsersearch.com'	=> array('Fast Browser Search', 'q', 'results/results.aspx?q={k}'),

		// Francite
		'recherche.francite.com'	=> array('Francite', 'name'),

		// Fireball
		'www.fireball.de'			=> array('Fireball', 'q', 'ajax.asp?q={k}'),

		// Firstfind
		'www.firstsfind.com'		=> array('Firstsfind', 'qry'),

		// Fixsuche
		'www.fixsuche.de'			=> array('Fixsuche', 'q'),

		// Flix
		'www.flix.de'				=> array('Flix.de', 'keyword'),

		// Forestle
		'{}.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),

		// Free
		'search.free.fr'			=> array('Free', 'q'),
		'search1-2.free.fr'			=> array('Free'),
		'search1-1.free.fr'			=> array('Free'),

		// Freecause
		'search.freecause.com'		=> array('FreeCause', 'p', '?p={k}'),

		// Freenet
		'suche.freenet.de'			=> array('Freenet', 'query', 'suche/?query={k}'),

		// FriendFeed
		'friendfeed.com'			=> array('FriendFeed', 'q', 'search?q={k}'),

		// GAIS
		'gais.cs.ccu.edu.tw'		=> array('GAIS', 'q', 'search.php?q={k}'),

		// Geona 
		'geona.net'					=> array('Geona', 'q', 'search?q={k}'),

		// Gigablast
		'www.gigablast.com'			=> array('Gigablast', 'q', 'search?q={k}'),
		'dir.gigablast.com'			=> array('Gigablast (Directory)', 'q'),

		// GMX
		'suche.gmx.net'				=> array('GMX', 'su', 'search/web/?su={k}'),

		// Gnadenmeer
		'www.gnadenmeer.de'			=> array('Gnadenmeer', 'keyword'),

		// goo
		'search.goo.ne.jp'			=> array('goo', 'MT', 'web.jsp?MT={k}'),
		'ocnsearch.goo.ne.jp'		=> array('goo'),

		// Google
		'www.google.com'			=> array('Google', 'q', 'search?q={k}'),
		'www2.google.com'			=> array('Google'),
		'ipv6.google.com'			=> array('Google'),
		'w.google.com'				=> array('Google'),
		'ww.google.com'				=> array('Google'),
		'wwwgoogle.com'				=> array('Google'),
		'www.goggle.com'			=> array('Google'),
		'www.gogole.com'			=> array('Google'),
		'www.gppgle.com'			=> array('Google'),
		'go.google.com'				=> array('Google'),
		'www.google.ad'				=> array('Google'),
		'www.google.ae'				=> array('Google'),
		'www.google.am'				=> array('Google'),
		'www.google.it.ao'			=> array('Google'),
		'www.google.as'				=> array('Google'),
		'www.google.at'				=> array('Google'),
		'wwwgoogle.at'				=> array('Google'),
		'ww.google.at'				=> array('Google'),
		'w.google.at'				=> array('Google'),
		'www.google.az'				=> array('Google'),
		'www.google.ba'				=> array('Google'),
		'www.google.be'				=> array('Google'),
		'www.google.bf'				=> array('Google'),
		'www.google.bg'				=> array('Google'),
		'google.bg'					=> array('Google'),
		'www.google.bi'				=> array('Google'),
		'www.google.bj'				=> array('Google'),
		'www.google.bs'				=> array('Google'),
		'www.google.ca'				=> array('Google'),
		'ww.google.ca'				=> array('Google'),
		'w.google.ca'				=> array('Google'),
		'www.google.cat'			=> array('Google'),
		'www.google.cc'				=> array('Google'),
		'www.google.cd'				=> array('Google'),
		'google.cf'					=> array('Google'),
		'www.google.cg'				=> array('Google'),
		'www.google.ch'				=> array('Google'),
		'ww.google.ch'				=> array('Google'),
		'w.google.ch'				=> array('Google'),
		'www.google.ci'				=> array('Google'),
		'google.co.ck'				=> array('Google'),
		'www.google.cl'				=> array('Google'),
		'www.google.cn'				=> array('Google'),
		'google.cm'					=> array('Google'),
		'www.google.co'				=> array('Google'),
		'www.google.cz'				=> array('Google'),
		'wwwgoogle.cz'				=> array('Google'),
		'www.google.de'				=> array('Google'),
		'ww.google.de'				=> array('Google'),
		'w.google.de'				=> array('Google'),
		'wwwgoogle.de'				=> array('Google'),
		'google.dm'					=> array('Google'),
		'google.dz'					=> array('Google'),
		'www.google.ee'				=> array('Google'),
		'www.google.dj'				=> array('Google'),
		'www.google.dk'				=> array('Google'),
		'www.google.es'				=> array('Google'),
		'www.google.fi'				=> array('Google'),
		'www.googel.fi'				=> array('Google'),
		'www.google.fm'				=> array('Google'),
		'gogole.fr'					=> array('Google'),
		'www.gogole.fr'				=> array('Google'),
		'wwwgoogle.fr'				=> array('Google'),
		'ww.google.fr'				=> array('Google'),
		'w.google.fr'				=> array('Google'),
		'www.google.fr'				=> array('Google'),
		'www.google.fr.'			=> array('Google'),
		'google.fr'					=> array('Google'),
		'www.google.ga'				=> array('Google'),
		'google.ge'					=> array('Google'),
		'w.google.ge'				=> array('Google'),
		'ww.google.ge'				=> array('Google'),
		'www.google.ge'				=> array('Google'),
		'www.google.gg'				=> array('Google'),
		'google.gr'					=> array('Google'),
		'www.google.gl'				=> array('Google'),
		'www.google.gm'				=> array('Google'),
		'www.google.gp'				=> array('Google'),
		'www.google.gr'				=> array('Google'),
		'www.google.gy'				=> array('Google'),
		'www.google.hn'				=> array('Google'),
		'www.google.hr'				=> array('Google'),
		'www.google.ht'				=> array('Google'),
		'www.google.hu'				=> array('Google'),
		'www.google.ie'				=> array('Google'),
		'www.google.im'				=> array('Google'),
		'www.google.is'				=> array('Google'),
		'www.google.it'				=> array('Google'),
		'www.google.je'				=> array('Google'),
		'www.google.jo'				=> array('Google'),
		'www.google.ki'				=> array('Google'),
		'www.google.kg'				=> array('Google'),
		'www.google.kz'				=> array('Google'),
		'www.google.la'				=> array('Google'),
		'www.google.li'				=> array('Google'),
		'www.google.lk'				=> array('Google'),
		'www.google.lt'				=> array('Google'),
		'www.google.lu'				=> array('Google'),
		'www.google.lv'				=> array('Google'),
		'www.google.md'				=> array('Google'),
		'www.google.me'				=> array('Google'),
		'www.google.mg'				=> array('Google'),
		'www.google.mk'				=> array('Google'),
		'www.google.ml'				=> array('Google'),
		'www.google.mn'				=> array('Google'),
		'www.google.ms'				=> array('Google'),
		'www.google.mu'				=> array('Google'),
		'www.google.mv'				=> array('Google'),
		'www.google.mw'				=> array('Google'),
		'www.google.ne'				=> array('Google'),
		'www.google.nl'				=> array('Google'),
		'www.google.no'				=> array('Google'),
		'www.google.nr'				=> array('Google'),
		'www.google.nu'				=> array('Google'),
		'www.google.ps'				=> array('Google'),
		'www.google.pl'				=> array('Google'),
		'www.google.pn'				=> array('Google'),
		'www.google.pt'				=> array('Google'),
		'www.google.ro'				=> array('Google'),
		'www.google.rs'				=> array('Google'),
		'www.google.ru'				=> array('Google'),
		'www.google.rw'				=> array('Google'),
		'www.google.sc'				=> array('Google'),
		'www.google.se'				=> array('Google'),
		'www.google.sh'				=> array('Google'),
		'www.google.si'				=> array('Google'),
		'www.google.sk'				=> array('Google'),
		'www.google.sm'				=> array('Google'),
		'www.google.sn'				=> array('Google'),
		'www.google.st'				=> array('Google'),
		'www.google.td'				=> array('Google'),
		'www.google.tg'				=> array('Google'),
		'www.google.tk'				=> array('Google'),
		'www.google.tl'				=> array('Google'),
		'www.google.tm'				=> array('Google'),
		'www.google.to'				=> array('Google'),
		'www.google.tt'				=> array('Google'),
		'www.google.uz'				=> array('Google'),
		'www.google.vu'				=> array('Google'),
		'www.google.vg'				=> array('Google'),
		'www.google.ws'				=> array('Google'),
		'www.google.co.bw'			=> array('Google'),
		'www.google.co.cr'			=> array('Google'),
		'www.google.co.gg'			=> array('Google'),
		'www.google.co.hu'			=> array('Google'),
		'www.google.co.id'			=> array('Google'),
		'www.google.co.il'			=> array('Google'),
		'www.google.co.in'			=> array('Google'),
		'www.google.co.je'			=> array('Google'),
		'www.google.co.jp'			=> array('Google'),
		'www.google.co.ls'			=> array('Google'),
		'www.google.co.ke'			=> array('Google'),
		'www.google.co.kr'			=> array('Google'),
		'www.google.co.ma'			=> array('Google'),
		'www.google.co.mz'			=> array('Google'),
		'www.google.co.nz'			=> array('Google'),
		'www.google.co.th'			=> array('Google'),
		'www.google.co.tz'			=> array('Google'),
		'www.google.co.ug'			=> array('Google'),
		'www.google.co.uk'			=> array('Google'),
		'www.google.co.uz'			=> array('Google'),
		'www.google.co.vi'			=> array('Google'),
		'www.google.co.ve'			=> array('Google'),
		'www.google.co.za'			=> array('Google'),
		'www.google.co.zm'			=> array('Google'),
		'www.google.co.zw'			=> array('Google'),
		'www.google.com.af'			=> array('Google'),
		'www.google.com.ag'			=> array('Google'),
		'www.google.com.ai'			=> array('Google'),
		'www.google.com.ar'			=> array('Google'),
		'www.google.com.au'			=> array('Google'),
		'www.google.com.bd'			=> array('Google'),
		'www.google.com.bh'			=> array('Google'),
		'www.google.com.bn'			=> array('Google'),
		'www.google.com.bo'			=> array('Google'),
		'www.google.com.br'			=> array('Google'),
		'www.google.com.by'			=> array('Google'),
		'www.google.com.bz'			=> array('Google'),
		'www.google.com.co'			=> array('Google'),
		'www.google.com.cu'			=> array('Google'),
		'www.google.com.do'			=> array('Google'),
		'www.google.com.ec'			=> array('Google'),
		'www.google.com.eg'			=> array('Google'),
		'www.google.com.et'			=> array('Google'),
		'www.google.com.fj'			=> array('Google'),
		'www.google.com.gh'			=> array('Google'),
		'www.google.com.gi'			=> array('Google'),
		'www.google.com.gr'			=> array('Google'),
		'www.google.com.gt'			=> array('Google'),
		'www.google.com.hk'			=> array('Google'),
		'www.google.com.jm'			=> array('Google'),
		'www.google.com.kh'			=> array('Google'),
		'www.google.com.kw'			=> array('Google'),
		'www.google.com.lb'			=> array('Google'),
		'www.google.com.ly'			=> array('Google'),
		'www.google.com.mt'			=> array('Google'),
		'www.google.com.mx'			=> array('Google'),
		'www.google.com.my'			=> array('Google'),
		'www.google.com.na'			=> array('Google'),
		'www.google.com.nf'			=> array('Google'),
		'www.google.com.ng'			=> array('Google'),
		'www.google.com.ni'			=> array('Google'),
		'www.google.com.np'			=> array('Google'),
		'www.google.com.om'			=> array('Google'),
		'www.google.com.pa'			=> array('Google'),
		'www.google.com.pe'			=> array('Google'),
		'www.google.com.ph'			=> array('Google'),
		'www.google.com.pk'			=> array('Google'),
		'www.google.com.pl'			=> array('Google'),
		'www.google.com.pr'			=> array('Google'),
		'www.google.com.py'			=> array('Google'),
		'www.google.com.qa'			=> array('Google'),
		'www.google.com.ru'			=> array('Google'),
		'www.google.com.sa'			=> array('Google'),
		'www.google.com.sb'			=> array('Google'),
		'www.google.com.sg'			=> array('Google'),
		'www.google.com.sl'			=> array('Google'),
		'www.google.com.sv'			=> array('Google'),
		'www.google.com.tj'			=> array('Google'),
		'www.google.com.tr'			=> array('Google'),
		'www.google.com.tw'			=> array('Google'),
		'www.google.com.ua'			=> array('Google'),
		'www.google.com.uy'			=> array('Google'),
		'www.google.com.vc'			=> array('Google'),
		'www.google.com.vn'			=> array('Google'),

		// Powered by Google
		'verden.abcsok.no'			=> array('Google'),
		'search.incredimail.com'	=> array('Google'),
		'search1.incredimail.com'	=> array('Google'),
		'search2.incredimail.com'	=> array('Google'),
		'search3.incredimail.com'	=> array('Google'),
		'search4.incredimail.com'	=> array('Google'),
		'search.sweetim.com'		=> array('Google'),
		'darkoogle.com'				=> array('Google'),
		'search.darkoogle.com'		=> array('Google'),
		'search.hiyo.com'			=> array('Google'),

		// Google Earth
		'www.googleearth.de'		=> array('Google'),
		'www.googleearth.fr'		=> array('Google'),

		// Google Cache
		'webcache.googleusercontent.com'=> array('Google', '/\/search\?q=cache:[A-Za-z0-9]+:[^+]+([^&]+)/', 'search?q={k}'),

		// Google SSL 
		'encrypted.google.com'		=> array('Google SSL', 'q', 'search?q={k}'), 

		// Google Blogsearch
		'blogsearch.google.com'		=> array('Google Blogsearch', 'q', 'blogsearch?q={k}'),
		'blogsearch.google.net'		=> array('Google Blogsearch'),
		'blogsearch.google.at'		=> array('Google Blogsearch'),
		'blogsearch.google.be'		=> array('Google Blogsearch'),
		'blogsearch.google.ch'		=> array('Google Blogsearch'),
		'blogsearch.google.de'		=> array('Google Blogsearch'),
		'blogsearch.google.es'		=> array('Google Blogsearch'),
		'blogsearch.google.fr'		=> array('Google Blogsearch'),
		'blogsearch.google.it'		=> array('Google Blogsearch'),
		'blogsearch.google.nl'		=> array('Google Blogsearch'),
		'blogsearch.google.pl'		=> array('Google Blogsearch'),
		'blogsearch.google.ru'		=> array('Google Blogsearch'),
		'blogsearch.google.co.in'	=> array('Google Blogsearch'),
		'blogsearch.google.co.uk'	=> array('Google Blogsearch'),

		// Google Custom Search
		'www.google.com/cse'		=> array('Google Custom Search', 'q'),

		// Google translation
		'translate.google.com'		=> array('Google Translations', 'q'),

		// Google Images
		'images.google.com'			=> array('Google Images', 'q', 'images?q={k}'),
		'images.google.at'			=> array('Google Images'),
		'images.google.be'			=> array('Google Images'),
		'images.google.bg'			=> array('Google Images'),
		'images.google.ca'			=> array('Google Images'),
		'images.google.ch'			=> array('Google Images'),
		'images.google.ci'			=> array('Google Images'),
		'images.google.cz'			=> array('Google Images'),
		'images.google.de'			=> array('Google Images'),
		'images.google.dk'			=> array('Google Images'),
		'images.google.ee'			=> array('Google Images'),
		'images.google.es'			=> array('Google Images'),
		'images.google.fi'			=> array('Google Images'),
		'images.google.fr'			=> array('Google Images'),
		'images.google.gg'			=> array('Google Images'),
		'images.google.gr'			=> array('Google Images'),
		'images.google.hr'			=> array('Google Images'),
		'images.google.hu'			=> array('Google Images'),
		'images.google.it'			=> array('Google Images'),
		'images.google.lt'			=> array('Google Images'),
		'images.google.ms'			=> array('Google Images'),
		'images.google.nl'			=> array('Google Images'),
		'images.google.no'			=> array('Google Images'),
		'images.google.pl'			=> array('Google Images'),
		'images.google.pt'			=> array('Google Images'),
		'images.google.ro'			=> array('Google Images'),
		'images.google.ru'			=> array('Google Images'),
		'images.google.se'			=> array('Google Images'),
		'images.google.sk'			=> array('Google Images'),
		'images.google.co.id'		=> array('Google Images'),
		'images.google.co.il'		=> array('Google Images'),
		'images.google.co.in'		=> array('Google Images'),
		'images.google.co.jp'		=> array('Google Images'),
		'images.google.co.hu'		=> array('Google Images'),
		'images.google.co.kr'		=> array('Google Images'),
		'images.google.co.nz'		=> array('Google Images'),
		'images.google.co.th'		=> array('Google Images'),
		'images.google.co.tw'		=> array('Google Images'),
		'images.google.co.uk'		=> array('Google Images'),
		'images.google.co.ve'		=> array('Google Images'),
		'images.google.co.za'		=> array('Google Images'),
		'images.google.com.ar'		=> array('Google Images'),
		'images.google.com.au'		=> array('Google Images'),
		'images.google.com.br'		=> array('Google Images'),
		'images.google.com.cu'		=> array('Google Images'),
		'images.google.com.do'		=> array('Google Images'),
		'images.google.com.gr'		=> array('Google Images'),
		'images.google.com.hk'		=> array('Google Images'),
		'images.google.com.kw'		=> array('Google Images'),
		'images.google.com.mx'		=> array('Google Images'),
		'images.google.com.my'		=> array('Google Images'),
		'images.google.com.pe'		=> array('Google Images'),
		'images.google.com.sa'		=> array('Google Images'),
		'images.google.com.tr'		=> array('Google Images'),
		'images.google.com.tw'		=> array('Google Images'),
		'images.google.com.ua'		=> array('Google Images'),
		'images.google.com.vn'		=> array('Google Images'),

		// Google News
		'news.google.com'			=> array('Google News', 'q'),
		'news.google.at'			=> array('Google News'),
		'news.google.ca'			=> array('Google News'),
		'news.google.ch'			=> array('Google News'),
		'news.google.cl'			=> array('Google News'),
		'news.google.de'			=> array('Google News'),
		'news.google.es'			=> array('Google News'),
		'news.google.fr'			=> array('Google News'),
		'news.google.ie'			=> array('Google News'),
		'news.google.it'			=> array('Google News'),
		'news.google.lt'			=> array('Google News'),
		'news.google.lu'			=> array('Google News'),
		'news.google.se'			=> array('Google News'),
		'news.google.sm'			=> array('Google News'),
		'news.google.co.in'			=> array('Google News'),
		'news.google.co.jp'			=> array('Google News'),
		'news.google.co.uk'			=> array('Google News'),
		'news.google.co.ve'			=> array('Google News'),
		'news.google.com.ar'		=> array('Google News'),
		'news.google.com.au'		=> array('Google News'),
		'news.google.com.co'		=> array('Google News'),
		'news.google.com.hk'		=> array('Google News'),
		'news.google.com.ly'		=> array('Google News'),
		'news.google.com.mx'		=> array('Google News'),
		'news.google.com.pe'		=> array('Google News'),
		'news.google.com.tw'		=> array('Google News'),

		// Google product search
		'froogle.google.com'		=> array('Google Product search', 'q'),
		'froogle.google.de'			=> array('Google Product search'),
		'froogle.google.co.uk'		=> array('Google Product search'),

		// Google syndicated search
		'googlesyndicatedsearch.com'=> array('Google syndicated search', 'q'),

		// Goyellow.de
		'www.goyellow.de'			=> array('GoYellow.de', 'MDN'),

		// Gule Sider
		'www.gulesider.no'			=> array('Gule Sider', 'q'),

		// HighBeam
		'www.highbeam.com'			=> array('HighBeam', 'q', 'Search.aspx?q={k}'),

		// Hit-Parade
		'req.hit-parade.com'		=> array('Hit-Parade', 'p7', 'general/recherche.asp?p7={k}'),
		'class.hit-parade.com'		=> array('Hit-Parade'),
		'www.hit-parade.com'		=> array('Hit-Parade'),

		// Holmes.ge
		'holmes.ge'					=> array('Holmes', 'q', 'search.htm?q={k}'),

		// Hooseek.com
		'www.hooseek.com'			=> array('Hooseek', 'recherche', 'web?recherche={k}'),

		// Hotbot
		'www.hotbot.com'			=> array('Hotbot', 'query'),

		// IAC Search & Media (qbyrd.com & search-results.com)
		'www.qbyrd.com'				=> array('IAC', 'q', 'web?q={k}'),
		'{}.qbyrd.com'				=> array('IAC'),
		'www.search-results.com'	=> array('IAC'),
		'{}.search-results.com'		=> array('IAC'),

		// Icerocket
		'blogs.icerocket.com'		=> array('Icerocket', 'q', 'search?q={k}'),

		// ICQ
		'www.icq.com'				=> array('ICQ', 'q', 'search/results.php?q={k}'),
		'search.icq.com'			=> array('ICQ'),

		// Ilse
		'www.ilse.nl'				=> array('Ilse NL', 'search_for', '?search_for={k}'),

		// InfoSpace (and related web properties)
		'infospace.com'				=> array('InfoSpace', '/\/[^\/]+\/ws\/results\/[^\/]+\/([^\/]+)/', 'ispace/ws/results/Web/{k}/1/1/content-top-left/Relevance/'),
		'dogpile.com'				=> array('InfoSpace'),
		'nbci.dogpile.com'			=> array('InfoSpace'),
		'search.nation.com'			=> array('InfoSpace'),
		'search.go2net.com'			=> array('InfoSpace'),
		'metacrawler.com'			=> array('InfoSpace'),
		'webfetch.com'				=> array('InfoSpace'),
		'webcrawler.com'			=> array('InfoSpace'),
		'search.dogreatgood.com'	=> array('InfoSpace'),
	
		/*
		 * Infospace powered metasearches are handled in Piwik_Common::extractSearchEngineInformationFromUrl()
		 * That includes:
		 * - search.kiwee.com
		 * - ws.copernic.com
		 * - result.iminent.com
		 */

		// Interia
		'www.google.interia.pl'		=> array('Interia', 'q', 'szukaj?q={k}'),

		// Ixquick
		'ixquick.com'				=> array('Ixquick', 'query'),
		'www.eu.ixquick.com'		=> array('Ixquick'),
		'ixquick.de'				=> array('Ixquick'),
		'www.ixquick.de'			=> array('Ixquick'),
		'us.ixquick.com'			=> array('Ixquick'),
		's1.us.ixquick.com'			=> array('Ixquick'),
		's2.us.ixquick.com'			=> array('Ixquick'),
		's3.us.ixquick.com'			=> array('Ixquick'),
		's4.us.ixquick.com'			=> array('Ixquick'),
		's5.us.ixquick.com'			=> array('Ixquick'),
		'eu.ixquick.com'			=> array('Ixquick'),
		's8-eu.ixquick.com'			=> array('Ixquick'),
		's1-eu.ixquick.de'			=> array('Ixquick'),

		// Jyxo
		'jyxo.1188.cz'				=> array('Jyxo', 'q', 's?q={k}'),

		// Jungle Spider
		'www.jungle-spider.de'		=> array('Jungle Spider', 'q'),

		// Kataweb
		'www.kataweb.it'			=> array('Kataweb', 'q'),

		// Kvasir
		'www.kvasir.no'				=> array('Kvasir', 'q', 'alle?q={k}'),

		// Latne
		'www.latne.lv'				=> array('Latne', 'q', 'siets.php?q={k}'),

		// La Toile Du Québec via Google
		'www.toile.com'				=> array('La Toile Du Québec (Google)', 'q', 'search?q={k}'),
		'web.toile.com'				=> array('La Toile Du Québec (Google)'),

		// Looksmart
		'www.looksmart.com'			=> array('Looksmart', 'key'),

		// Lo.st (Enhanced by Google)
		'lo.st'						=> array('Lo.st', 'x_query', 'cgi-bin/eolost.cgi?x_query={k}'),

		// Lycos
		'search.lycos.com'			=> array('Lycos', 'query', '?query={k}'),
		'search.lycos.com.au'		=> array('Lycos'),
		'search.lycos.com.ar'		=> array('Lycos'),
		'search.lycos.com.br'		=> array('Lycos'),
		'search.lycos.com.co'		=> array('Lycos'),
		'search.lycos.at'			=> array('Lycos'),
		'search.lycos.be'			=> array('Lycos'),
		'search.lycos.ca'			=> array('Lycos'),
		'search.lycos.cl'			=> array('Lycos'),
		'search.lycos.dk'			=> array('Lycos'),
		'search.lycos.fi'			=> array('Lycos'),
		'search.lycos.fr'			=> array('Lycos'),
		'search.lycos.de'			=> array('Lycos'),
		'search.lycos.in'			=> array('Lycos'),
		'search.lycos.it'			=> array('Lycos'),
		'search.lycos.co.jp'		=> array('Lycos'),
		'search.lycos.co.kr'		=> array('Lycos'),
		'search.lycos.mx'			=> array('Lycos'),
		'search.lycos.nl'			=> array('Lycos'),
		'search.lycos.co.nz'		=> array('Lycos'),
		'search.lycos.com.pe'		=> array('Lycos'),
		'search.lycos.es'			=> array('Lycos'),
		'search.lycos.se'			=> array('Lycos'),
		'search.lycos.ch'			=> array('Lycos'),
		'search.lycos.co.uk'		=> array('Lycos'),
		'search.lycos.com.ve'		=> array('Lycos'),
	
		// maailm.com
		'www.maailm.com'			=> array('maailm.com', 'tekst'),

		// Mail.ru
		'go.mail.ru'				=> array('Mailru', 'q', 'search?q={k}', 'windows-1251'),

		// Mamma
		'www.mamma.com'				=> array('Mamma', 'query', 'result.php?q={k}'),
		'mamma75.mamma.com'			=> array('Mamma'),

		// Meta
		'meta.ua'					=> array('Meta.ua', 'q', 'search.asp?q={k}'),

		// MetaCrawler.de
		's1.metacrawler.de'			=> array('MetaCrawler DE', 'qry', '?qry={k}'),
		's2.metacrawler.de'			=> array('MetaCrawler DE'),
		's3.metacrawler.de'			=> array('MetaCrawler DE'),

		// Metager
		'meta.rrzn.uni-hannover.de'	=> array('Metager', 'eingabe', 'meta/cgi-bin/meta.ger1?eingabe={k}'),
		'www.metager.de'			=> array('Metager'),

		// Metager2
		'metager2.de'			=> array('Metager2', 'q', 'search/index.php?q={k}'),

		// Meinestadt
		'www.meinestadt.de'			=> array('Meinestadt.de', 'words'),

		// Mister Wong
		'www.mister-wong.com'		=> array('Mister Wong', 'keywords', 'search/?keywords={k}'),
		'www.mister-wong.de'		=> array('Mister Wong'),

		// Monstercrawler
		'www.monstercrawler.com'	=> array('Monstercrawler', 'qry'),

		// Mozbot
		'www.mozbot.fr'				=> array('mozbot', 'q', 'results.php?q={k}'),
		'www.mozbot.co.uk'			=> array('mozbot'),
		'www.mozbot.com'			=> array('mozbot'),

		// El Mundo
		'ariadna.elmundo.es'		=> array('El Mundo', 'q'),

		// MySpace
		'searchservice.myspace.com'	=> array('MySpace', 'qry', 'index.cfm?fuseaction=sitesearch.results&type=Web&qry={k}'),

		// MySearch / MyWay / MyWebSearch (default: powered by Ask.com)
		'www.mysearch.com'			=> array('MyWebSearch', 'searchfor', 'search/Ajmain.jhtml?searchfor={k}'),
		'ms114.mysearch.com'		=> array('MyWebSearch'),
		'ms146.mysearch.com'		=> array('MyWebSearch'),
		'kf.mysearch.myway.com'		=> array('MyWebSearch'),
		'ki.mysearch.myway.com'		=> array('MyWebSearch'),
		'search.myway.com'			=> array('MyWebSearch'),
		'search.mywebsearch.com'	=> array('MyWebSearch'),


		// Najdi
		'www.najdi.si'				=> array('Najdi.si', 'q', 'search.jsp?q={k}'),

		// Naver
		'search.naver.com'			=> array('Naver', 'query', 'search.naver?query={k}', 'x-windows-949'),

		// Needtofind
		'ko.search.need2find.com'	=> array('Needtofind', 'searchfor', 'search/AJmain.jhtml?searchfor={k}'),

		// Neti
		'www.neti.ee'				=> array('Neti', 'query', 'cgi-bin/otsing?query={k}', 'iso-8859-1'),

		// Netster
		'www.ireit.com'				=> array('Netster', 'search', 'netstercom/?search={k}'),

		// Nifty
		'search.nifty.com'			=> array('Nifty', 'q', 'websearch/search?q={k}'),

		// Nigma
		'nigma.ru'					=> array('Nigma', 's', 'index.php?s={k}'),

		// Onet
		'szukaj.onet.pl'			=> array('Onet.pl', 'qt', 'query.html?qt={k}'),

		// Online.no
		'online.no'				=> array('Online.no', 'q', 'google/index.jsp?q={k}'),

		// Opplysningen 1881
		'www.1881.no'				=> array('Opplysningen 1881', 'Query', 'Multi/?Query={k}'),

		// Orange
		'busca.orange.es'			=> array('Orange', 'q', 'search?q={k}'),
	
		// Paperball
		'www.paperball.de'			=> array('Paperball', 'q', 'suche/s/?q={k}'),

		// PeoplePC
		'search.peoplepc.com'		=> array('PeoplePC', 'q', 'search?q={k}'),

		// Picsearch
		'www.picsearch.com'			=> array('Picsearch', 'q', 'index.cgi?q={k}'),

		// Plazoo
		'www.plazoo.com'			=> array('Plazoo', 'q'),

		// Poisk.Ru
		'poisk.ru'					=> array('Poisk.Ru', 'text', 'cgi-bin/poisk?text={k}', 'windows-1251'),

		// qip
		'search.qip.ru'				=> array('qip.ru', 'query', 'search?query={k}'),

		// Qualigo
		'www.qualigo.at'			=> array('Qualigo', 'q'),
		'www.qualigo.ch'			=> array('Qualigo'),
		'www.qualigo.de'			=> array('Qualigo'),
		'www.qualigo.nl'			=> array('Qualigo'),

		// Rakuten
		'websearch.rakuten.co.jp'	=> array('Rakuten', 'qt', 'WebIS?qt={k}'),

		// Rambler
		'nova.rambler.ru'			=> array('Rambler', array('query', 'words'), 'search?query={k}'),

		// RPMFind
		'www.rpmfind.net'			=> array('rpmfind', 'query', 'linux/rpm2html/search.php?query={k}'),
		'rpmfind.net'				=> array('rpmfind'),
		'fr2.rpmfind.net'			=> array('rpmfind'),

		// Road Runner Search
		'search.rr.com'				=> array('Road Runner', 'q', '?q={k}'),

		// Sapo
		'pesquisa.sapo.pt'			=> array('Sapo', 'q', '?q={k}'),

		// scroogle.org
		'www.scroogle.org'			=> array('Scroogle', ''),
	
		// Search.com
		'www.search.com'			=> array('Search.com', 'q', 'search?q={k}'),

		// Search.ch
		'www.search.ch'				=> array('Search.ch', 'q', '?q={k}'),

		// Searchalot
		'searchalot.com'			=> array('Searchalot', 'q', '?q={k}'),

		// SearchCanvas
		'www.searchcanvas.com'		=> array('SearchCanvas', 'q', 'web?q={k}'),

		// Seek
		'www.seek.fr'				=> array('Seek.fr', ''),

		// Searchy
		'www.searchy.co.uk'			=> array('Searchy', 'q', 'index.html?q={k}'),

		// Setooz
		'bg.setooz.com'				=> array('Setooz', 'query', 'search?query={k}'),
		'da.setooz.com'				=> array('Setooz'),
		'el.setooz.com'				=> array('Setooz'),
		'fa.setooz.com'				=> array('Setooz'),
		'ur.setooz.com'				=> array('Setooz'),
		'{}.setooz.com'				=> array('Setooz'),

		// Seznam
		'search.seznam.cz'			=> array('Seznam', 'q', '?q={k}'),

		// Sharelook
		'www.sharelook.fr'			=> array('Sharelook', 'keyword'),

		// Skynet
		'www.skynet.be'				=> array('Skynet', 'q', 'services/recherche/google?q={k}'),

		// Sogou
		'www.sogou.com'				=> array('Sogou', 'query', 'web?query={k}'),

		// soso.com
		'www.soso.com'				=> array('Soso', 'w', 'q?w={k}', 'gb2312'),

		// Startpagina
		'startgoogle.startpagina.nl'=> array('Startpagina (Google)', 'q', '?q={k}'),

		// suche.info
		'suche.info'				=> array('Suche.info', 'Keywords', 'suche.php?Keywords={k}'),
	
		// Suchmaschine.com
		'www.suchmaschine.com'		=> array('Suchmaschine.com', 'suchstr', 'cgi-bin/wo.cgi?suchstr={k}'),

		// Suchnase
		'www.suchnase.de'			=> array('Suchnase', 'q'),

		// Technorati
		'technorati.com'			=> array('Technorati', 'q', 'search?return=sites&authority=all&q={k}'),

		// Teoma
		'www.teoma.com'				=> array('Teoma', 'q', 'web?q={k}'),

		// Terra -- referer does not contain search phrase (keywords)
		'buscador.terra.es'			=> array('Terra'),
		'buscador.terra.cl'			=> array('Terra'),
		'buscador.terra.com.br'		=> array('Terra'),

		// Tiscali
		'search.tiscali.it'			=> array('Tiscali', 'q', '?q={k}'),
		'search-dyn.tiscali.it'		=> array('Tiscali'),
		'hledani.tiscali.cz'		=> array('Tiscali', 'query', false, 'windows-1250'),

		// Tixuma
		'www.tixuma.de'				=> array('Tixuma', 'sc', 'index.php?mp=search&stp=&sc={k}&tg=0'),

		// T-Online
		'suche.t-online.de'			=> array('T-Online', 'q', 'fast-cgi/tsc?mandant=toi&context=internet-tab&q={k}'),
		'brisbane.t-online.de'		=> array('T-Online'),
		'navigationshilfe.t-online.de'=> array('T-Online', 'q', 'dtag/dns/results?mode=search_top&q={k}'),

		// Trouvez.com
		'www.trouvez.com'			=> array('Trouvez.com', 'query'),

		// TrovaRapido
		'www.trovarapido.com'		=> array('TrovaRapido', 'q', 'result.php?q={k}'),
	
		// Trusted-Search
		'www.trusted--search.com'	=> array('Trusted Search', 'w', 'search?w={k}'),

		// Twingly
		'www.twingly.com'			=> array('Twingly', 'q', 'search?q={k}'),

		// Vinden
		'www.vinden.nl'				=> array('Vinden', 'q', '?q={k}'),

		// Vindex
		'www.vindex.nl'				=> array('Vindex', 'search_for', '/web?search_for={k}'),
		'search.vindex.nl'			=> array('Vindex'),

		// Virgilio
		'ricerca.virgilio.it'		=> array('Virgilio', 'qs', 'ricerca?qs={k}'),
		'ricercaimmagini.virgilio.it'=> array('Virgilio'),
		'ricercavideo.virgilio.it'	=> array('Virgilio'),
		'ricercanews.virgilio.it'	=> array('Virgilio'),

		// Voila
		'search.ke.voila.fr'		=> array('Voila', 'rdata', 'S/voila?rdata={k}'),
		'www.lemoteur.fr'			=> array('Voila'), // uses voila search

		// Volny
		'web.volny.cz'				=> array('Volny', 'search', 'fulltext/?search={k}', 'windows-1250'),

		// Walhello 
		'www.walhello.info'			=> array('Walhello', 'key', 'search?key={k}'),
		'www.walhello.com'			=> array('Walhello'),
		'www.walhello.de'			=> array('Walhello'),
		'www.walhello.nl'			=> array('Walhello'),

		// Web.de
		'suche.web.de'				=> array('Web.de', 'su', 'search/web/?su={k}'),

		// Web.nl
		'www.web.nl'				=> array('Web.nl', 'zoekwoord'),

		// Weborama
		'www.weborama.fr'			=> array('weborama', 'QUERY'),

		// WebSearch
		'www.websearch.com'			=> array('WebSearch', array('qkw', 'q'), 'search/results2.aspx?q={k}'), 

		// Wedoo
		'fr.wedoo.com'				=> array('Wedoo', 'keyword'),

		// Witch
		'www.witch.de'				=> array('Witch', 'search', 'search-result.php?cn=0&search={k}'),

		// WWW
		'search.www.ee'				=> array('www värav', 'query'),

		// X-recherche
		'www.x-recherche.com'		=> array('X-Recherche', 'MOTS', 'cgi-bin/websearch?MOTS={k}'),

		// Yahoo
		'search.yahoo.com'			=> array('Yahoo!', 'p', 'search?p={k}'),
		'search.yahoo.co.jp'		=> array('Yahoo!'),
		'ar.search.yahoo.com'		=> array('Yahoo!'),
		'au.search.yahoo.com'		=> array('Yahoo!'),
		'br.search.yahoo.com'		=> array('Yahoo!'),
		'ch.search.yahoo.com'		=> array('Yahoo!'),
		'ca.search.yahoo.com'		=> array('Yahoo!'),
		'cade.search.yahoo.com'		=> array('Yahoo!'),
		'cf.search.yahoo.com'		=> array('Yahoo!'),
		'de.search.yahoo.com'		=> array('Yahoo!'),
		'es.search.yahoo.com'		=> array('Yahoo!'),
		'espanol.search.yahoo.com'	=> array('Yahoo!'),
		'fi.search.yahoo.com'		=> array('Yahoo!'),
		'fr.search.yahoo.com'		=> array('Yahoo!'),
		'hk.search.yahoo.com'		=> array('Yahoo!'),
		'id.search.yahoo.com'		=> array('Yahoo!'),
		'it.search.yahoo.com'		=> array('Yahoo!'),
		'in.search.yahoo.com'		=> array('Yahoo!'),
		'kr.search.yahoo.com'		=> array('Yahoo!'),
		'mx.search.yahoo.com'		=> array('Yahoo!'),
		'nl.search.yahoo.com'		=> array('Yahoo!'),
		'qc.search.yahoo.com'		=> array('Yahoo!'),
		'ru.search.yahoo.com'		=> array('Yahoo!'),
		'se.search.yahoo.com'		=> array('Yahoo!'),
		'tw.search.yahoo.com'		=> array('Yahoo!'),
		'uk.search.yahoo.com'		=> array('Yahoo!'),
		'us.search.yahoo.com'		=> array('Yahoo!'),
		'm.yahoo.com'				=> array('Yahoo!'),
		'ar.m.yahoo.com'			=> array('Yahoo!'),
		'au.m.yahoo.com'			=> array('Yahoo!'),
		'br.m.yahoo.com'			=> array('Yahoo!'),
		'ch.m.yahoo.com'			=> array('Yahoo!'),
		'ca.m.yahoo.com'			=> array('Yahoo!'),
		'cade.m.yahoo.com'			=> array('Yahoo!'),
		'cf.m.yahoo.com'			=> array('Yahoo!'),
		'de.m.yahoo.com'			=> array('Yahoo!'),
		'es.m.yahoo.com'			=> array('Yahoo!'),
		'espanol.m.yahoo.com'		=> array('Yahoo!'),
		'fi.m.yahoo.com'			=> array('Yahoo!'),
		'fr.m.yahoo.com'			=> array('Yahoo!'),
		'hk.m.yahoo.com'			=> array('Yahoo!'),
		'id.m.yahoo.com'			=> array('Yahoo!'),
		'it.m.yahoo.com'			=> array('Yahoo!'),
		'in.m.yahoo.com'			=> array('Yahoo!'),
		'kr.m.yahoo.com'			=> array('Yahoo!'),
		'mx.m.yahoo.com'			=> array('Yahoo!'),
		'nl.m.yahoo.com'			=> array('Yahoo!'),
		'qc.m.yahoo.com'			=> array('Yahoo!'),
		'ru.m.yahoo.com'			=> array('Yahoo!'),
		'se.m.yahoo.com'			=> array('Yahoo!'),
		'tw.m.yahoo.com'			=> array('Yahoo!'),
		'uk.m.yahoo.com'			=> array('Yahoo!'),
		'us.m.yahoo.com'			=> array('Yahoo!'),
		'us.yhs.search.yahoo.com'	=> array('Yahoo!'),
		'it.yhs.search.yahoo.com '	=> array('Yahoo!'),
		'search.cn.yahoo.com'		=> array('Yahoo!'),
		'one.cn.yahoo.com'			=> array('Yahoo!'),
		'siteexplorer.search.yahoo.com'	=> array('Yahoo!'),
		'de.dir.yahoo.com'			=> array('Yahoo! Webverzeichnis', ''),
		'cf.dir.yahoo.com'			=> array('Yahoo! Directory', ''),
		'fr.dir.yahoo.com'			=> array('Yahoo! Directory', ''),

		// Yahoo! Images
		'images.search.yahoo.com'	=> array('Yahoo! Images', 'p', 'search/images?p={k}'),
		'ar.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'au.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'br.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'ch.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'ca.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'cade.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'cf.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'de.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'es.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'espanol.images.search.yahoo.com'=> array('Yahoo! Images'),
		'fi.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'fr.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'hk.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'id.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'it.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'kr.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'mx.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'nl.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'qc.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'ru.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'se.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'tw.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'uk.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'us.images.search.yahoo.com'	=> array('Yahoo! Images'),
		'images.m.yahoo.com'			=> array('Yahoo! Images'),
		'ar.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'au.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'br.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'ch.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'ca.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'cade.images.m.yahoo.com'		=> array('Yahoo! Images'),
		'cf.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'de.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'es.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'espanol.images.m.yahoo.com'	=> array('Yahoo! Images'),
		'fi.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'fr.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'hk.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'id.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'it.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'kr.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'mx.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'nl.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'qc.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'ru.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'se.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'tw.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'uk.images.m.yahoo.com'			=> array('Yahoo! Images'),
		'us.images.m.yahoo.com'			=> array('Yahoo! Images'),
	
		// Yandex
		'yandex.ru'					=> array('Yandex', 'text', 'yandsearch?text={k}'),
		'yandex.ua'					=> array('Yandex'),

		// Yandex Images
		'images.yandex.ru'			=> array('Yandex Images', 'text', 'yandsearch?text={k}'),
		'images.yandex.ua'			=> array('Yandex Images'),

		// Yasni
		'www.yasni.de'				=> array('Yasni', 'query'),
		'www.yasni.com'				=> array('Yasni'),
		'www.yasni.co.uk'			=> array('Yasni'),
		'www.yasni.ch'				=> array('Yasni'),
		'www.yasni.at'				=> array('Yasni'),

		// Yellowmap
		'yellowmap.de'				=> array('Yellowmap', ' '),

		// Yippy
		'search.yippy.com'			=> array('Yippy', 'query', 'search?query={k}'),

		// Zoek
		'www3.zoek.nl'				=> array('Zoek', 'q'),

		// Zhongsou
		'p.zhongsou.com'			=> array('Zhongsou', 'w', 'p?w={k}'),

		// Zoeken
		'www.zoeken.nl'				=> array('Zoeken', 'q', '?q={k}'),

		// Zoohoo
		'zoohoo.cz'					=> array('Zoohoo', 'q', '?q={k}', 'windows-1250'),

		// Zoznam
		'www.zoznam.sk'				=> array('Zoznam', 's', 'hladaj.fcgi?s={k}&co=svet'),
	);

	$GLOBALS['Piwik_SearchEngines_NameToUrl'] = array();
	foreach($GLOBALS['Piwik_SearchEngines'] as $url => $info)
	{
		if(!isset($GLOBALS['Piwik_SearchEngines_NameToUrl'][$info[0]]))
		{
			$GLOBALS['Piwik_SearchEngines_NameToUrl'][$info[0]] = $url;
		}
	}
}
