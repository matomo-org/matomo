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
 * If you want to add a new entry, please email us the information + icon at hello at piwik.org
 *
 * See also: http://piwik.org/faq/general/#faq_39
 *
 * Detail of a line:
 * Url => array( SearchEngineName, KeywordParameter, [path containing the keyword], [charset used by the search engine])
 *
 * The main search engine URL has to be at the top of the list for the given search Engine.
 * You can add new search engines icons by adding the icon in the plugins/Referers/images/SearchEngines directory
 * using the format 'mainSearchEngineUrl.png'. Example: www.google.com.png
 * To help Piwik link directly the search engine result page for the keyword, specify the third entry in the array
 * using the macro {k} that will automatically be replaced by the keyword.
 *
 *  A simple example is:
 *  'www.google.com'		=> array('Google', 'q', 'search?q={k}'),
 *
 *  A more complicated example, with an array of possible variable names, and a custom charset:
 *  'www.baidu.com'			=> array('Baidu', array('wd', 'word', 'kw'), 's?wd={k}', 'gb2312'),
 *
 *  Another example using a regular expression to parse the path for keywords:
 *  'infospace.com'         => array('InfoSpace', array('/dir1\/(pattern)\/dir2/'), '/dir1/{k}/dir2/stuff/'),
 */
if(!isset($GLOBALS['Piwik_SearchEngines'] ))
{
	$GLOBALS['Piwik_SearchEngines'] = array(
		// 1
		'1.cz'						=> array('1.cz', 'q', 'index.php?q={k}', 'iso-8859-2'),
		'www.1.cz'					=> array('1.cz', 'q', false, 'iso-8859-2'),

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
		'www.abacho.com'			=> array('Abacho', 'q', '?q={k}&id=87'),
		'www.abacho.co.uk'			=> array('Abacho', 'q', '?q={k}&id=94'),
		'www.se.abacho.com'			=> array('Abacho', 'q', '?q={k}&id=98'),
		'www.tr.abacho.com'			=> array('Abacho', 'q', '?q={k}&id=114'),
		'www.abacho.at'				=> array('Abacho', 'q', 'suche?q={k}'),
		'www.abacho.fr'				=> array('Abacho', 'q', '?q={k}&id=102'),
		'www.abacho.es'				=> array('Abacho', 'q', '?q={k}&id=106'),
		'www.abacho.ch'				=> array('Abacho', 'q', 'suche?q={k}'),
		'www.abacho.it'				=> array('Abacho', 'q', '?q={k}&id=110'),
	
		// ABCsøk
		'abcsok.no'					=> array('ABCsøk', 'q', '?q={k}'),
		'www.abcsok.no'				=> array('ABCsøk', 'q'),

		// about
		'search.about.com'			=> array('About', 'terms', '?terms={k}'),

		// Acoon
		'www.acoon.de'				=> array('Acoon', 'begriff', 'cgi-bin/search.exe?begriff={k}'),

		// Alexa
		'www.alexa.com'				=> array('Alexa', 'q', 'search?q={k}'),
		'alexa.com'					=> array('Alexa', 'q'),

		// Alice Adsl
		'rechercher.aliceadsl.fr'	=> array('Alice Adsl', 'qs', 'google.pl?qs={k}'),

		// Allesklar
		'www.allesklar.de'			=> array('Allesklar', 'words', '?words={k}'),
		'www.allesklar.at'			=> array('Allesklar', 'words'),
		'www.allesklar.ch'			=> array('Allesklar', 'words'),

		// AllTheWeb
		'www.alltheweb.com'			=> array('AllTheWeb', 'q', 'search?q={k}'),

		// all.by
		'all.by'					=> array('All.by', 'query', 'cgi-bin/search.cgi?mode=by&query={k}'),

		// Altavista
		'www.altavista.com'			=> array('AltaVista', 'q', 'web/results?q={k}'),
		'listings.altavista.com'	=> array('AltaVista', 'q'),
		'www.altavista.de'			=> array('AltaVista', 'q'),
		'altavista.fr'				=> array('AltaVista', 'q'),
		'de.altavista.com'			=> array('AltaVista', 'q'),
		'fr.altavista.com'			=> array('AltaVista', 'q'),
		'es.altavista.com'			=> array('AltaVista', 'q'),
		'www.altavista.fr'			=> array('AltaVista', 'q'),
		'search.altavista.com'		=> array('AltaVista', 'q'),
		'search.fr.altavista.com'	=> array('AltaVista', 'q'),
		'se.altavista.com'			=> array('AltaVista', 'q'),
		'be-nl.altavista.com'		=> array('AltaVista', 'q'),
		'be-fr.altavista.com'		=> array('AltaVista', 'q'),
		'it.altavista.com'			=> array('AltaVista', 'q'),
		'us.altavista.com'			=> array('AltaVista', 'q'),
		'nl.altavista.com'			=> array('Altavista', 'q'),
		'ch.altavista.com'			=> array('AltaVista', 'q'),

		// Apollo Latvia
		'apollo.lv/portal/search/'	=> array('Apollo lv', 'q', '?cof=FORID%3A11&q={k}&search_where=www'),

		// APOLLO7
		'www.apollo7.de'			=> array('Apollo7', 'query', 'a7db/index.php?query={k}&de_sharelook=true&de_bing=true&de_witch=true&de_google=true&de_yahoo=true&de_lycos=true'),
		'apollo7.de'				=> array('Apollo7', 'query'),

		// AOL
		'search.aol.com'			=> array('AOL', array('query', 'q'), 'aol/search?q={k}'),
		'aolsearch.aol.com'			=> array('AOL', array('query', 'q')),
		'www.aolrecherche.aol.fr'	=> array('AOL', array('query', 'q')),
		'www.aolrecherches.aol.fr'	=> array('AOL', array('query', 'q')),
		'www.aolimages.aol.fr'		=> array('AOL', array('query', 'q')),
		'aim.search.aol.com'		=> array('AOL', array('query', 'q')),
		'www.recherche.aol.fr'		=> array('AOL', array('query', 'q')),
		'find.web.aol.com'			=> array('AOL', array('query', 'q')),
		'recherche.aol.ca'			=> array('AOL', array('query', 'q')),
		'aolsearch.aol.co.uk'		=> array('AOL', array('query', 'q')),
		'search.aol.co.uk'			=> array('AOL', array('query', 'q')),
		'aolrecherche.aol.fr'		=> array('AOL', array('query', 'q')),
		'sucheaol.aol.de'			=> array('AOL', array('query', 'q')),
		'suche.aol.de'				=> array('AOL', array('query', 'q')),
		'suche.aolsvc.de'			=> array('AOL', array('query', 'q')),
		'aolbusqueda.aol.com.mx'	=> array('AOL', array('query', 'q')),
		'alicesuchet.aol.de'		=> array('AOL', array('query', 'q')),
		'suche.aolsvc.de'			=> array('AOL', array('query', 'q')),
		'suche.aol.de'				=> array('AOL', array('query', 'q')),
		'alicesuche.aol.de'			=> array('AOL', array('query', 'q')),
		'suchet2.aol.de'			=> array('AOL', array('query', 'q')),
		'search.hp.my.aol.com.au'	=> array('AOL', array('query', 'q')),
		'search.hp.my.aol.de'		=> array('AOL', array('query', 'q')),
		'search.hp.my.aol.it'		=> array('AOL', array('query', 'q')),
		'search-intl.netscape.com'	=> array('AOL', array('query', 'q')),

		// Aport
		'sm.aport.ru'				=> array('Aport', 'r', 'search?r={k}'),

		// arama
		'arama.com'					=> array('Arama', 'q', 'search.php3?q={k}'),
		'www.arama.com'				=> array('Arama', 'q'),
	
		// Arcor
		'www.arcor.de'				=> array('Arcor', 'Keywords', 'content/searchresult.jsp?Keywords={k}'),

		// Arianna (Libero.it)
		'arianna.libero.it'			=> array('Arianna', 'query', 'search/abin/integrata.cgi?query={k}'),

		// Ask
		'www.ask.com'				=> array('Ask', array('ask', 'q'), 'web?q={k}'),
		'web.ask.com'				=> array('Ask', array('ask', 'q')),
		'images.ask.com'			=> array('Ask', 'q'),
		'ask.reference.com'			=> array('Ask', 'q'),
		'www.ask.co.uk'				=> array('Ask', 'q'),
		'uk.ask.com'				=> array('Ask', 'q'),
		'fr.ask.com'				=> array('Ask', 'q'),
		'de.ask.com'				=> array('Ask', 'q'),
		'es.ask.com'				=> array('Ask', 'q'),
		'it.ask.com'				=> array('Ask', 'q'),
		'nl.ask.com'				=> array('Ask', 'q'),
		'jp.ask.com'				=> array('Ask', 'q'),

		// Atlas
		'searchatlas.centrum.cz'	=> array('Atlas', 'q', '?q={k}'),

		// Austronaut
		'www2.austronaut.at'		=> array('Austronaut', 'q'),
		'www1.austronaut.at'		=> array('Austronaut', 'q'),
	
		// Babylon
		'search.babylon.com'		=> array('Babylon (Powered by Google)', 'q', '?q={k}'),

		// Baidu
		'www.baidu.com'				=> array('Baidu', array('wd', 'word', 'kw'), 's?wd={k}', 'gb2312'),
		'www1.baidu.com'			=> array('Baidu', array('wd', 'word', 'kw'), false, 'gb2312'),
		'zhidao.baidu.com'			=> array('Baidu', array('wd', 'word', 'kw'), false, 'gb2312'),
		'tieba.baidu.com'			=> array('Baidu', array('wd', 'word', 'kw'), false, 'gb2312'),
		'news.baidu.com'			=> array('Baidu', array('wd', 'word', 'kw'), false, 'gb2312'),
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
		'morfeo.centrum.cz'			=> array('Centrum', 'q', false),

		// Conduit
		'search.conduit.com'		=> array('Conduit.com', 'q', 'Results.aspx?q={k}'),

		// Comcast
		'search.comcast.net'		=> array('Comcast', 'q', '?q={k}'),

		// Compuserve
		'websearch.cs.com'			=> array('Compuserve.com (Enhanced by Google)', 'query', 'cs/search?query={k}'),

		// Cuil
		'www.cuil.com'				=> array('Cuil', 'q', 'search?q={k}'),

		// Daemon search
		'www.daemon-search.com'		=> array('Daemon search', 'q', 'explore/web?q={k}'),
		'daemon-search.com'			=> array('Daemon search', 'q', false),

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
		'editors.dmoz.org'			=> array('dmoz', 'search'),
		'search.dmoz.org'			=> array('dmoz', 'search'),
		'www.dmoz.org'				=> array('dmoz', 'search'),

		// DuckDuckGo
		'duckduckgo.com'			=> array('DuckDuckGo', 'q', '?q={k}'),

		// earthlink
		'search.earthlink.net'		=> array('Earthlink', 'q', 'search?q={k}'),

		// Ecosia (powered by Bing)
		'ecosia.org'				=> array('Ecosia', 'q', 'search.php?q={k}'),
		'www.ecosia.org'			=> array('Ecosia', 'q'),

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
		'msxml.excite.com'			=> array('Excite', '/\/[^\/]+\/ws\/results\/[^\/]+\/([^\/]+)/'),
		'search.excite.fr'			=> array('Excite', 'q', 'web/?q={k}'),
		'search.excite.de'			=> array('Excite', 'q', 'web/?q={k}'),
		'search.excite.co.uk'		=> array('Excite', 'q', 'web/?q={k}'),
		'search.excite.es'			=> array('Excite', 'q', 'web/?q={k}'),
		'search.excite.nl'			=> array('Excite', 'q', 'web/?q={k}'),
		'www.excite.co.jp'			=> array('Excite', 'search', 'search.gw?search={k}', 'SHIFT_JIS'),

		// Exalead
		'www.exalead.fr'			=> array('Exalead', 'q', 'search/results?q={k}'),
		'www.exalead.com'			=> array('Exalead', 'q'),

		// eo
		'eo.st'						=> array('eo', 'x_query', 'cgi-bin/eolost.cgi?x_query={k}'),

		// Facebook
		'www.facebook.com'			=> array('Facebook', 'q', 'search/?q={k}'),

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
		'de.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'at.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'ch.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'us.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'fr.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'ar.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'au.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'ca.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'cl.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'co.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'cz.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'dk.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'fi.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'hu.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'in.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'id.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'it.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'jp.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'kr.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'my.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'mx.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'nl.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'nz.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'pe.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'ph.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'ro.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'ru.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'sg.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'es.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'se.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'th.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'uk.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		've.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),
		'vn.forestle.org'			=> array('Forestle', 'q', 'search.php?q={k}'),

		// Free
		'search.free.fr'			=> array('Free', 'q'),
		'search1-2.free.fr'			=> array('Free', 'q'),
		'search1-1.free.fr'			=> array('Free', 'q'),

		// Freecause
		'search.freecause.com'		=> array('FreeCause', 'q', '?p={k}'),

		// Freenet
		'suche.freenet.de'			=> array('Freenet', 'query', 'suche/?query={k}'),

		// FriendFeed
		'friendfeed.com'			=> array('FriendFeed', 'q', 'search?q={k}'),

		// GAIS
		'gais.cs.ccu.edu.tw'		=> array('GAIS', 'q', 'search.php?q={k}'),

		// Geona 
		'geona.net'					=> array('Geona', 'q', 'search?q={k}'),
		'www.geona.net'				=> array('Geona', 'q', 'search?q={k}'),

		// Gigablast
		'www.gigablast.com'			=> array('Gigablast', 'q', 'search?q={k}'),
		'dir.gigablast.com'			=> array('Gigablast (Directory)', 'q'),

		// GMX
		'suche.gmx.net'				=> array('GMX', 'su', 'search/web/?su={k}'),

		// Gnadenmeer
		'www.gnadenmeer.de'			=> array('Gnadenmeer', 'keyword'),

		// goo
		'search.goo.ne.jp'			=> array('goo', 'MT', 'web.jsp?MT={k}'),
		'ocnsearch.goo.ne.jp'		=> array('goo', 'MT'),

		// Google
		'www.google.com'			=> array('Google', 'q', 'search?q={k}'),
		'www2.google.com'			=> array('Google', 'q'),
		'ipv6.google.com'			=> array('Google', 'q'),
		'w.google.com'				=> array('Google', 'q'),
		'ww.google.com'				=> array('Google', 'q'),
		'wwwgoogle.com'				=> array('Google', 'q'),
		'www.goggle.com'			=> array('Google', 'q'),
		'www.gogole.com'			=> array('Google', 'q'),
		'www.gppgle.com'			=> array('Google', 'q'),
		'go.google.com'				=> array('Google', 'q'),
		'www.google.ad'				=> array('Google', 'q'),
		'www.google.ae'				=> array('Google', 'q'),
		'www.google.am'				=> array('Google', 'q'),
		'www.google.it.ao'			=> array('Google', 'q'),
		'www.google.as'				=> array('Google', 'q'),
		'www.google.at'				=> array('Google', 'q'),
		'wwwgoogle.at'				=> array('Google', 'q'),
		'ww.google.at'				=> array('Google', 'q'),
		'w.google.at'				=> array('Google', 'q'),
		'www.google.az'				=> array('Google', 'q'),
		'www.google.ba'				=> array('Google', 'q'),
		'www.google.be'				=> array('Google', 'q'),
		'www.google.bf'				=> array('Google', 'q'),
		'www.google.bg'				=> array('Google', 'q'),
		'google.bg'					=> array('Google', 'q'),
		'www.google.bi'				=> array('Google', 'q'),
		'www.google.bj'				=> array('Google', 'q'),
		'www.google.bs'				=> array('Google', 'q'),
		'www.google.ca'				=> array('Google', 'q'),
		'ww.google.ca'				=> array('Google', 'q'),
		'w.google.ca'				=> array('Google', 'q'),
		'www.google.cat'			=> array('Google', 'q'),
		'www.google.cc'				=> array('Google', 'q'),
		'www.google.cd'				=> array('Google', 'q'),
		'google.cf'					=> array('Google', 'q'),
		'www.google.cg'				=> array('Google', 'q'),
		'www.google.ch'				=> array('Google', 'q'),
		'ww.google.ch'				=> array('Google', 'q'),
		'w.google.ch'				=> array('Google', 'q'),
		'www.google.ci'				=> array('Google', 'q'),
		'google.co.ck'				=> array('Google', 'q'),
		'www.google.cl'				=> array('Google', 'q'),
		'www.google.cn'				=> array('Google', 'q'),
		'google.cm'					=> array('Google', 'q'),
		'www.google.co'				=> array('Google', 'q'),
		'www.google.cz'				=> array('Google', 'q'),
		'wwwgoogle.cz'				=> array('Google', 'q'),
		'www.google.de'				=> array('Google', 'q'),
		'ww.google.de'				=> array('Google', 'q'),
		'w.google.de'				=> array('Google', 'q'),
		'wwwgoogle.de'				=> array('Google', 'q'),
		'google.dm'					=> array('Google', 'q'),
		'google.dz'					=> array('Google', 'q'),
		'www.google.ee'				=> array('Google', 'q'),
		'www.google.dj'				=> array('Google', 'q'),
		'www.google.dk'				=> array('Google', 'q'),
		'www.google.es'				=> array('Google', 'q'),
		'www.google.fi'				=> array('Google', 'q'),
		'www.googel.fi'				=> array('Google', 'q'),
		'www.google.fm'				=> array('Google', 'q'),
		'gogole.fr'					=> array('Google', 'q'),
		'www.gogole.fr'				=> array('Google', 'q'),
		'wwwgoogle.fr'				=> array('Google', 'q'),
		'ww.google.fr'				=> array('Google', 'q'),
		'w.google.fr'				=> array('Google', 'q'),
		'www.google.fr'				=> array('Google', 'q'),
		'www.google.fr.'			=> array('Google', 'q'),
		'google.fr'					=> array('Google', 'q'),
		'www.google.ga'				=> array('Google', 'q'),
		'google.ge'					=> array('Google', 'q'),
		'w.google.ge'				=> array('Google', 'q'),
		'ww.google.ge'				=> array('Google', 'q'),
		'www.google.ge'				=> array('Google', 'q'),
		'www.google.gg'				=> array('Google', 'q'),
		'google.gr'					=> array('Google', 'q'),
		'www.google.gl'				=> array('Google', 'q'),
		'www.google.gm'				=> array('Google', 'q'),
		'www.google.gp'				=> array('Google', 'q'),
		'www.google.gr'				=> array('Google', 'q'),
		'www.google.gy'				=> array('Google', 'q'),
		'www.google.hn'				=> array('Google', 'q'),
		'www.google.hr'				=> array('Google', 'q'),
		'www.google.ht'				=> array('Google', 'q'),
		'www.google.hu'				=> array('Google', 'q'),
		'www.google.ie'				=> array('Google', 'q'),
		'www.google.im'				=> array('Google', 'q'),
		'www.google.is'				=> array('Google', 'q'),
		'www.google.it'				=> array('Google', 'q'),
		'www.google.je'				=> array('Google', 'q'),
		'www.google.jo'				=> array('Google', 'q'),
		'www.google.ki'				=> array('Google', 'q'),
		'www.google.kg'				=> array('Google', 'q'),
		'www.google.kz'				=> array('Google', 'q'),
		'www.google.la'				=> array('Google', 'q'),
		'www.google.li'				=> array('Google', 'q'),
		'www.google.lk'				=> array('Google', 'q'),
		'www.google.lt'				=> array('Google', 'q'),
		'www.google.lu'				=> array('Google', 'q'),
		'www.google.lv'				=> array('Google', 'q'),
		'www.google.md'				=> array('Google', 'q'),
		'www.google.me'				=> array('Google', 'q'),
		'www.google.mg'				=> array('Google', 'q'),
		'www.google.mk'				=> array('Google', 'q'),
		'www.google.ml'				=> array('Google', 'q'),
		'www.google.mn'				=> array('Google', 'q'),
		'www.google.ms'				=> array('Google', 'q'),
		'www.google.mu'				=> array('Google', 'q'),
		'www.google.mv'				=> array('Google', 'q'),
		'www.google.mw'				=> array('Google', 'q'),
		'www.google.ne'				=> array('Google', 'q'),
		'www.google.nl'				=> array('Google', 'q'),
		'www.google.no'				=> array('Google', 'q'),
		'www.google.nr'				=> array('Google', 'q'),
		'www.google.nu'				=> array('Google', 'q'),
		'www.google.ps'				=> array('Google', 'q'),
		'www.google.pl'				=> array('Google', 'q'),
		'www.google.pn'				=> array('Google', 'q'),
		'www.google.pt'				=> array('Google', 'q'),
		'www.google.ro'				=> array('Google', 'q'),
		'www.google.rs'				=> array('Google', 'q'),
		'www.google.ru'				=> array('Google', 'q'),
		'www.google.rw'				=> array('Google', 'q'),
		'www.google.sc'				=> array('Google', 'q'),
		'www.google.se'				=> array('Google', 'q'),
		'www.google.sh'				=> array('Google', 'q'),
		'www.google.si'				=> array('Google', 'q'),
		'www.google.sk'				=> array('Google', 'q'),
		'www.google.sm'				=> array('Google', 'q'),
		'www.google.sn'				=> array('Google', 'q'),
		'www.google.st'				=> array('Google', 'q'),
		'www.google.td'				=> array('Google', 'q'),
		'www.google.tg'				=> array('Google', 'q'),
		'www.google.tk'				=> array('Google', 'q'),
		'www.google.tl'				=> array('Google', 'q'),
		'www.google.tm'				=> array('Google', 'q'),
		'www.google.to'				=> array('Google', 'q'),
		'www.google.tt'				=> array('Google', 'q'),
		'www.google.uz'				=> array('Google', 'q'),
		'www.google.vu'				=> array('Google', 'q'),
		'www.google.vg'				=> array('Google', 'q'),
		'www.google.ws'				=> array('Google', 'q'),
		'www.google.co.bw'			=> array('Google', 'q'),
		'www.google.co.cr'			=> array('Google', 'q'),
		'www.google.co.gg'			=> array('Google', 'q'),
		'www.google.co.hu'			=> array('Google', 'q'),
		'www.google.co.id'			=> array('Google', 'q'),
		'www.google.co.il'			=> array('Google', 'q'),
		'www.google.co.in'			=> array('Google', 'q'),
		'www.google.co.je'			=> array('Google', 'q'),
		'www.google.co.jp'			=> array('Google', 'q'),
		'www.google.co.ls'			=> array('Google', 'q'),
		'www.google.co.ke'			=> array('Google', 'q'),
		'www.google.co.kr'			=> array('Google', 'q'),
		'www.google.co.ma'			=> array('Google', 'q'),
		'www.google.co.mz'			=> array('Google', 'q'),
		'www.google.co.nz'			=> array('Google', 'q'),
		'www.google.co.th'			=> array('Google', 'q'),
		'www.google.co.tz'			=> array('Google', 'q'),
		'www.google.co.ug'			=> array('Google', 'q'),
		'www.google.co.uk'			=> array('Google', 'q'),
		'www.google.co.uz'			=> array('Google', 'q'),
		'www.google.co.vi'			=> array('Google', 'q'),
		'www.google.co.ve'			=> array('Google', 'q'),
		'www.google.co.za'			=> array('Google', 'q'),
		'www.google.co.zm'			=> array('Google', 'q'),
		'www.google.co.zw'			=> array('Google', 'q'),
		'www.google.com.af'			=> array('Google', 'q'),
		'www.google.com.ag'			=> array('Google', 'q'),
		'www.google.com.ai'			=> array('Google', 'q'),
		'www.google.com.ar'			=> array('Google', 'q'),
		'www.google.com.au'			=> array('Google', 'q'),
		'www.google.com.bd'			=> array('Google', 'q'),
		'www.google.com.bh'			=> array('Google', 'q'),
		'www.google.com.bn'			=> array('Google', 'q'),
		'www.google.com.bo'			=> array('Google', 'q'),
		'www.google.com.br'			=> array('Google', 'q'),
		'www.google.com.by'			=> array('Google', 'q'),
		'www.google.com.bz'			=> array('Google', 'q'),
		'www.google.com.co'			=> array('Google', 'q'),
		'www.google.com.cu'			=> array('Google', 'q'),
		'www.google.com.do'			=> array('Google', 'q'),
		'www.google.com.ec'			=> array('Google', 'q'),
		'www.google.com.eg'			=> array('Google', 'q'),
		'www.google.com.et'			=> array('Google', 'q'),
		'www.google.com.fj'			=> array('Google', 'q'),
		'www.google.com.gh'			=> array('Google', 'q'),
		'www.google.com.gi'			=> array('Google', 'q'),
		'www.google.com.gr'			=> array('Google', 'q'),
		'www.google.com.gt'			=> array('Google', 'q'),
		'www.google.com.hk'			=> array('Google', 'q'),
		'www.google.com.jm'			=> array('Google', 'q'),
		'www.google.com.kh'			=> array('Google', 'q'),
		'www.google.com.kw'			=> array('Google', 'q'),
		'www.google.com.lb'			=> array('Google', 'q'),
		'www.google.com.ly'			=> array('Google', 'q'),
		'www.google.com.mt'			=> array('Google', 'q'),
		'www.google.com.mx'			=> array('Google', 'q'),
		'www.google.com.my'			=> array('Google', 'q'),
		'www.google.com.na'			=> array('Google', 'q'),
		'www.google.com.nf'			=> array('Google', 'q'),
		'www.google.com.ng'			=> array('Google', 'q'),
		'www.google.com.ni'			=> array('Google', 'q'),
		'www.google.com.np'			=> array('Google', 'q'),
		'www.google.com.om'			=> array('Google', 'q'),
		'www.google.com.pa'			=> array('Google', 'q'),
		'www.google.com.pe'			=> array('Google', 'q'),
		'www.google.com.ph'			=> array('Google', 'q'),
		'www.google.com.pk'			=> array('Google', 'q'),
		'www.google.com.pl'			=> array('Google', 'q'),
		'www.google.com.pr'			=> array('Google', 'q'),
		'www.google.com.py'			=> array('Google', 'q'),
		'www.google.com.qa'			=> array('Google', 'q'),
		'www.google.com.ru'			=> array('Google', 'q'),
		'www.google.com.sa'			=> array('Google', 'q'),
		'www.google.com.sb'			=> array('Google', 'q'),
		'www.google.com.sg'			=> array('Google', 'q'),
		'www.google.com.sl'			=> array('Google', 'q'),
		'www.google.com.sv'			=> array('Google', 'q'),
		'www.google.com.tj'			=> array('Google', 'q'),
		'www.google.com.tr'			=> array('Google', 'q'),
		'www.google.com.tw'			=> array('Google', 'q'),
		'www.google.com.ua'			=> array('Google', 'q'),
		'www.google.com.uy'			=> array('Google', 'q'),
		'www.google.com.vc'			=> array('Google', 'q'),
		'www.google.com.vn'			=> array('Google', 'q'),

		// Powered by Google
		'www.charter.net'			=> array('Google', 'q'),
		'brisbane.t-online.de'		=> array('Google', 'q'),
		'miportal.bellsouth.net'	=> array('Google', 'string'),
		'home.bellsouth.net'		=> array('Google', 'string'),
		'pesquisa.clix.pt'			=> array('Google', 'q'),
		'google.startsiden.no'		=> array('Google', 'q'),
		'google.startpagina.nl'		=> array('Google', 'q'),
		'search.peoplepc.com'		=> array('Google', 'q'),
		'www.google.interia.pl'		=> array('Google', 'q'),
		'buscador.terra.es'			=> array('Google', 'query'),
		'buscador.terra.cl'			=> array('Google', 'query'),
		'buscador.terra.com.br'		=> array('Google', 'query'),
		'www.adelphia.net'			=> array('Google', 'q'),
		'so.qq.com'					=> array('Google', 'word'),
		'misc.skynet.be'			=> array('Google', 'keywords'),
		'verden.abcsok.no'			=> array('Google', 'q'),
		'search3.incredimail.com'	=> array('Google', 'q'),
		'search.incredimail.com'	=> array('Google', 'q'),
		'search.sweetim.com'		=> array('Google', 'q'),
		'www.darkoogle.com'			=> array('Google', 'q'),
		'darkoogle.com'				=> array('Google', 'q'),

		// Google Earth
		'www.googleearth.de'		=> array('Google', 'q'),
		'www.googleearth.fr'		=> array('Google', 'q'),

		// Google Cache
		'webcache.googleusercontent.com'=> array('Google', '/\/search\?q=cache:[A-Za-z0-9]+:[^+]+([^&]+)/', 'search?q={k}'),

		// Google SSL 
		'encrypted.google.com'		=> array('Google SSL', 'q', 'search?q={k}'), 

		// Google Blogsearch
		'blogsearch.google.com'		=> array('Google Blogsearch', 'q', 'blogsearch?q={k}'),
		'blogsearch.google.net'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.at'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.be'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.ch'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.de'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.es'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.fr'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.it'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.nl'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.pl'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.ru'		=> array('Google Blogsearch', 'q'),
		'blogsearch.google.co.in'	=> array('Google Blogsearch', 'q'),
		'blogsearch.google.co.uk'	=> array('Google Blogsearch', 'q'),

		// Google Custom Search
		'www.google.com/cse'		=> array('Google Custom Search', 'q'),

		// Google translation
		'translate.google.com'		=> array('Google Translations', 'q'),

		// Google Images
		'images.google.com'			=> array('Google Images', 'q', 'images?q={k}'),
		'images.google.at'			=> array('Google Images', 'q'),
		'images.google.be'			=> array('Google Images', 'q'),
		'images.google.bg'			=> array('Google Images', 'q'),
		'images.google.ca'			=> array('Google Images', 'q'),
		'images.google.ch'			=> array('Google Images', 'q'),
		'images.google.ci'			=> array('Google Images', 'q'),
		'images.google.cz'			=> array('Google Images', 'q'),
		'images.google.de'			=> array('Google Images', 'q'),
		'images.google.dk'			=> array('Google Images', 'q'),
		'images.google.ee'			=> array('Google Images', 'q'),
		'images.google.es'			=> array('Google Images', 'q'),
		'images.google.fi'			=> array('Google Images', 'q'),
		'images.google.fr'			=> array('Google Images', 'q'),
		'images.google.gg'			=> array('Google Images', 'q'),
		'images.google.gr'			=> array('Google Images', 'q'),
		'images.google.hr'			=> array('Google Images', 'q'),
		'images.google.hu'			=> array('Google Images', 'q'),
		'images.google.it'			=> array('Google Images', 'q'),
		'images.google.lt'			=> array('Google Images', 'q'),
		'images.google.ms'			=> array('Google Images', 'q'),
		'images.google.nl'			=> array('Google Images', 'q'),
		'images.google.no'			=> array('Google Images', 'q'),
		'images.google.pl'			=> array('Google Images', 'q'),
		'images.google.pt'			=> array('Google Images', 'q'),
		'images.google.ro'			=> array('Google Images', 'q'),
		'images.google.ru'			=> array('Google Images', 'q'),
		'images.google.se'			=> array('Google Images', 'q'),
		'images.google.sk'			=> array('Google Images', 'q'),
		'images.google.co.id'		=> array('Google Images', 'q'),
		'images.google.co.il'		=> array('Google Images', 'q'),
		'images.google.co.in'		=> array('Google Images', 'q'),
		'images.google.co.jp'		=> array('Google Images', 'q'),
		'images.google.co.hu'		=> array('Google Images', 'q'),
		'images.google.co.kr'		=> array('Google Images', 'q'),
		'images.google.co.nz'		=> array('Google Images', 'q'),
		'images.google.co.th'		=> array('Google Images', 'q'),
		'images.google.co.tw'		=> array('Google Images', 'q'),
		'images.google.co.uk'		=> array('Google Images', 'q'),
		'images.google.co.ve'		=> array('Google Images', 'q'),
		'images.google.co.za'		=> array('Google Images', 'q'),
		'images.google.com.ar'		=> array('Google Images', 'q'),
		'images.google.com.au'		=> array('Google Images', 'q'),
		'images.google.com.br'		=> array('Google Images', 'q'),
		'images.google.com.cu'		=> array('Google Images', 'q'),
		'images.google.com.do'		=> array('Google Images', 'q'),
		'images.google.com.gr'		=> array('Google Images', 'q'),
		'images.google.com.hk'		=> array('Google Images', 'q'),
		'images.google.com.kw'		=> array('Google Images', 'q'),
		'images.google.com.mx'		=> array('Google Images', 'q'),
		'images.google.com.my'		=> array('Google Images', 'q'),
		'images.google.com.pe'		=> array('Google Images', 'q'),
		'images.google.com.sa'		=> array('Google Images', 'q'),
		'images.google.com.tr'		=> array('Google Images', 'q'),
		'images.google.com.tw'		=> array('Google Images', 'q'),
		'images.google.com.ua'		=> array('Google Images', 'q'),
		'images.google.com.vn'		=> array('Google Images', 'q'),

		// Google News
		'news.google.com'			=> array('Google News', 'q'),
		'news.google.at'			=> array('Google News', 'q'),
		'news.google.ca'			=> array('Google News', 'q'),
		'news.google.ch'			=> array('Google News', 'q'),
		'news.google.cl'			=> array('Google News', 'q'),
		'news.google.de'			=> array('Google News', 'q'),
		'news.google.es'			=> array('Google News', 'q'),
		'news.google.fr'			=> array('Google News', 'q'),
		'news.google.ie'			=> array('Google News', 'q'),
		'news.google.it'			=> array('Google News', 'q'),
		'news.google.lt'			=> array('Google News', 'q'),
		'news.google.lu'			=> array('Google News', 'q'),
		'news.google.se'			=> array('Google News', 'q'),
		'news.google.sm'			=> array('Google News', 'q'),
		'news.google.co.in'			=> array('Google News', 'q'),
		'news.google.co.jp'			=> array('Google News', 'q'),
		'news.google.co.uk'			=> array('Google News', 'q'),
		'news.google.co.ve'			=> array('Google News', 'q'),
		'news.google.com.ar'		=> array('Google News', 'q'),
		'news.google.com.au'		=> array('Google News', 'q'),
		'news.google.com.co'		=> array('Google News', 'q'),
		'news.google.com.hk'		=> array('Google News', 'q'),
		'news.google.com.ly'		=> array('Google News', 'q'),
		'news.google.com.mx'		=> array('Google News', 'q'),
		'news.google.com.pe'		=> array('Google News', 'q'),
		'news.google.com.tw'		=> array('Google News', 'q'),

		// Googe product search
		'froogle.google.com'		=> array('Google Product search', 'q'),
		'froogle.google.de'			=> array('Google Product search', 'q'),
		'froogle.google.co.uk'		=> array('Google Product search', 'q'),

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
		'class.hit-parade.com'		=> array('Hit-Parade', 'p7'),
		'www.hit-parade.com'		=> array('Hit-Parade', 'p7'),

		// Holmes.ge
		'holmes.ge'					=> array('Holmes', 'q', 'search.htm?q={k}'),
		'www.holmes.ge'				=> array('Holmes', 'q'),

		// Hooseek.com
		'www.hooseek.com'			=> array('Hooseek', 'recherche', 'web?recherche={k}'),

		// Hotbot
		'www.hotbot.com'			=> array('Hotbot', 'query'),

		// IAC Search & Media (qbyrd.com & search-results.com)
		'www.qbyrd.com'				=> array('IAC', 'q', 'web?q={k}'),
		'de.qbyrd.com'				=> array('IAC', 'q'),
		'es.qbyrd.com'				=> array('IAC', 'q'),
		'fr.qbyrd.com'				=> array('IAC', 'q'),
		'it.qbyrd.com'				=> array('IAC', 'q'),
		'www.search-results.com'	=> array('IAC', 'q', 'web?q={k}'),
		'de.search-results.com'		=> array('IAC', 'q'),
		'es.search-results.com'		=> array('IAC', 'q'),
		'eu.search-results.com'		=> array('IAC', 'q'),
		'fr.search-results.com'		=> array('IAC', 'q'),
		'it.search-results.com'		=> array('IAC', 'q'),

		// Icerocket
		'blogs.icerocket.com'		=> array('Icerocket', 'q', 'search?q={k}'),

		// ICQ
		'www.icq.com'				=> array('ICQ', 'q', 'search/results.php?q={k}'),
		'search.icq.com'			=> array('ICQ', 'q'),

		// Ilse
		'www.ilse.nl'				=> array('Ilse NL', 'search_for', '?search_for={k}'),

		// InfoSpace (and related web properties)
		'infospace.com'				=> array('InfoSpace', '/\/[^\/]+\/ws\/results\/[^\/]+\/([^\/]+)/', 'ispace/ws/results/Web/{k}/1/1/content-top-left/Relevance/'),
		'search.infospace.com'		=> array('InfoSpace'),
		'dogpile.com'				=> array('InfoSpace'),
		'search.dogpile.com'		=> array('InfoSpace'),
		'nbci.dogpile.com'			=> array('InfoSpace'),
		'search.nation.com'			=> array('InfoSpace'),
		'search.go2net.com'			=> array('InfoSpace'),
		'metacrawler.com'			=> array('InfoSpace'),
		'search.metacrawler.com'	=> array('InfoSpace'),
		'webfetch.com'				=> array('InfoSpace'),
		'search.webfetch.com'		=> array('InfoSpace'),
		'webcrawler.com'			=> array('InfoSpace'),
		'search.webcrawler.com'		=> array('InfoSpace'),
		'search.dogreatgood.com'	=> array('InfoSpace'),
	
		/*
		 * Infospace powered metasearches are handled in Piwik_Common::extractSearchEngineInformationFromUrl()
		 * That includes:
		 * - search.kiwee.com
		 * - ws.copernic.com
		 */

		// Iwon
		'iwon.ask.com'				=> array('Iwon', 'q', 'web?q={k}'),

		// Ixquick
		'ixquick.com'				=> array('Ixquick', 'query'),
		'www.eu.ixquick.com'		=> array('Ixquick', 'query'),
		'ixquick.de'				=> array('Ixquick', 'query'),
		'www.ixquick.de'			=> array('Ixquick', 'query'),
		'us.ixquick.com'			=> array('Ixquick', 'query'),
		's1.us.ixquick.com'			=> array('Ixquick', 'query'),
		's2.us.ixquick.com'			=> array('Ixquick', 'query'),
		's3.us.ixquick.com'			=> array('Ixquick', 'query'),
		's4.us.ixquick.com'			=> array('Ixquick', 'query'),
		's5.us.ixquick.com'			=> array('Ixquick', 'query'),
		'eu.ixquick.com'			=> array('Ixquick', 'query'),
		's8-eu.ixquick.com'			=> array('Ixquick', 'query'),
		's1-eu.ixquick.de'			=> array('Ixquick', 'query'),

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
		'web.toile.com'				=> array('La Toile Du Québec (Google)', 'q'),

		// Looksmart
		'www.looksmart.com'			=> array('Looksmart', 'key'),

		// Lo.st
		'lo.st'						=> array('Lo.st (Powered by Google)', 'x_query', 'cgi-bin/eolost.cgi?x_query={k}'),

		// Lycos
		'search.lycos.com'			=> array('Lycos', 'query', '?query={k}'),
		'search.lycos.com.au'		=> array('Lycos', 'query'),
		'search.lycos.com.ar'		=> array('Lycos', 'query'),
		'search.lycos.com.br'		=> array('Lycos', 'query'),
		'search.lycos.com.co'		=> array('Lycos', 'query'),
		'search.lycos.at'			=> array('Lycos', 'query'),
		'search.lycos.be'			=> array('Lycos', 'query'),
		'search.lycos.ca'			=> array('Lycos', 'query'),
		'search.lycos.cl'			=> array('Lycos', 'query'),
		'search.lycos.dk'			=> array('Lycos', 'query'),
		'search.lycos.fi'			=> array('Lycos', 'query'),
		'search.lycos.fr'			=> array('Lycos', 'query'),
		'search.lycos.de'			=> array('Lycos', 'query'),
		'search.lycos.in'			=> array('Lycos', 'query'),
		'search.lycos.it'			=> array('Lycos', 'query'),
		'search.lycos.co.jp'		=> array('Lycos', 'query'),
		'search.lycos.co.kr'		=> array('Lycos', 'query'),
		'search.lycos.mx'			=> array('Lycos', 'query'),
		'search.lycos.nl'			=> array('Lycos', 'query'),
		'search.lycos.co.nz'		=> array('Lycos', 'query'),
		'search.lycos.com.pe'		=> array('Lycos', 'query'),
		'search.lycos.es'			=> array('Lycos', 'query'),
		'search.lycos.se'			=> array('Lycos', 'query'),
		'search.lycos.ch'			=> array('Lycos', 'query'),
		'search.lycos.co.uk'		=> array('Lycos', 'query'),
		'search.lycos.com.ve'		=> array('Lycos', 'query'),
	
		// maailm.com
		'www.maailm.com'			=> array('maailm.com', 'tekst'),

		// Mail.ru
		'go.mail.ru'				=> array('Mailru', 'q', 'search?q={k}', 'windows-1251'),

		// Mamma
		'www.mamma.com'				=> array('Mamma', 'query', 'result.php?q={k}'),
		'mamma75.mamma.com'			=> array('Mamma', 'query'),

		// Meta
		'meta.ua'					=> array('Meta.ua', 'q', 'search.asp?q={k}'),
		'www.meta.ua'				=> array('Meta.ua', 'q'),

		// MetaCrawler.de
		's1.metacrawler.de'			=> array('MetaCrawler DE', 'qry', '?qry={k}'),
		's2.metacrawler.de'			=> array('MetaCrawler DE', 'qry'),
		's3.metacrawler.de'			=> array('MetaCrawler DE', 'qry'),

		// Metager
		'meta.rrzn.uni-hannover.de'	=> array('Metager', 'eingabe', 'meta/cgi-bin/meta.ger1?eingabe={k}'),
		'www.metager.de'			=> array('Metager', 'eingabe'),

		// Metager2
		'www.metager2.de'			=> array('Metager2', 'q', 'search/index.php?q={k}'),
		'metager2.de'				=> array('Metager2', 'q'),

		// Meinestadt
		'www.meinestadt.de'			=> array('Meinestadt.de', 'words'),

		// Mister Wong
		'www.mister-wong.com'		=> array('Mister Wong', 'keywords', 'search/?keywords={k}'),
		'www.mister-wong.de'		=> array('Mister Wong', 'keywords'),

		// Monstercrawler
		'www.monstercrawler.com'	=> array('Monstercrawler', 'qry'),

		// Mozbot
		'www.mozbot.fr'				=> array('mozbot', 'q', 'results.php?q={k}'),
		'www.mozbot.co.uk'			=> array('mozbot', 'q'),
		'www.mozbot.com'			=> array('mozbot', 'q'),

		// El Mundo
		'ariadna.elmundo.es'		=> array('El Mundo', 'q'),

		// MySpace
		'searchservice.myspace.com'	=> array('MySpace', 'qry', 'index.cfm?fuseaction=sitesearch.results&type=Web&qry={k}'),

		// MySearch / MyWay / MyWebSearch (default: powered by Ask.com)
		'www.mysearch.com'			=> array('MyWebSearch', 'searchfor', 'search/Ajmain.jhtml?searchfor={k}'),
		'ms114.mysearch.com'		=> array('MyWebSearch', 'searchfor'),
		'ms146.mysearch.com'		=> array('MyWebSearch', 'searchfor'),
		'kf.mysearch.myway.com'		=> array('MyWebSearch', 'searchfor'),
		'ki.mysearch.myway.com'		=> array('MyWebSearch', 'searchfor'),
		'search.myway.com'			=> array('MyWebSearch', 'searchfor'),
		'search.mywebsearch.com'	=> array('MyWebSearch', 'searchfor', 'mywebsearch/Ajmain.jhtml?searchfor={k}'),


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
		'www.nigma.ru'				=> array('Nigma', 's', 'index.php?s={k}'),
		'nigma.ru'					=> array('Nigma', 's'),

		// Onet
		'szukaj.onet.pl'			=> array('Onet.pl', 'qt', 'query.html?qt={k}'),

		// Online.no
		'www.online.no'				=> array('Online.no', 'q', 'google/index.jsp?q={k}'),
		'online.no'					=> array('Online.no', 'q'),

		// Opplysningen 1881
		'www.1881.no'				=> array('Opplysningen 1881', 'Query', 'Multi/?Query={k}'),

		// Orange
		'busca.orange.es'			=> array('Orange', 'q', 'search?q={k}'),
	
		// Paperball
		'www.paperball.de'			=> array('Paperball', 'q', 'suche/s/?q={k}'),

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
		'www.qualigo.ch'			=> array('Qualigo', 'q'),
		'www.qualigo.de'			=> array('Qualigo', 'q'),
		'www.qualigo.nl'			=> array('Qualigo', 'q'),

		// Rakuten
		'websearch.rakuten.co.jp'	=> array('Rakuten', 'qt', 'WebIS?qt={k}'),

		// Rambler
		'nova.rambler.ru'			=> array('Rambler', array('query', 'words'), 'search?query={k}'),

		// RPMFind
		'www.rpmfind.net'			=> array('rpmfind', 'query', 'linux/rpm2html/search.php?query={k}'),
		'rpmfind.net'				=> array('rpmfind', 'query'),
		'fr2.rpmfind.net'			=> array('rpmfind', 'query'),

		// Sapo
		'pesquisa.sapo.pt'			=> array('Sapo', 'q', '?q={k}'),

		// scroogle.org
		'www.scroogle.org'			=> array('Scroogle', ''),
	
		// Search.com
		'www.search.com'			=> array('Search.com', 'q', 'search?q={k}'),

		// Search.ch
		'www.search.ch'				=> array('Search.ch', 'q', '?q={k}'),

		// Searchalot
		'www.searchalot.com'		=> array('Searchalot', 'q', '?q={k}'),
		'searchalot.com'			=> array('Searchalot', 'q'),

		// Seek
		'www.seek.fr'				=> array('Seek.fr', ''),

		// Searchy
		'www.searchy.co.uk'			=> array('Searchy', 'q', 'index.html?q={k}'),

		// Setooz
		'bg.setooz.com'				=> array('Setooz', 'query', 'search?query={k}'),
		'el.setooz.com'				=> array('Setooz', 'query'),
		'et.setooz.com'				=> array('Setooz', 'query'),
		'fi.setooz.com'				=> array('Setooz', 'query'),
		'hu.setooz.com'				=> array('Setooz', 'query'),
		'lt.setooz.com'				=> array('Setooz', 'query'),
		'lv.setooz.com'				=> array('Setooz', 'query'),
		'no.setooz.com'				=> array('Setooz', 'query'),
		'pl.setooz.com'				=> array('Setooz', 'query'),
		'sk.setooz.com'				=> array('Setooz', 'query'),
		'sv.setooz.com'				=> array('Setooz', 'query'),
		'tr.setooz.com'				=> array('Setooz', 'query'),
		'uk.setooz.com'				=> array('Setooz', 'query'),
		'ar.setooz.com'				=> array('Setooz', 'query'),
		'bs.setooz.com'				=> array('Setooz', 'query'),
		'cs.setooz.com'				=> array('Setooz', 'query'),
		'da.setooz.com'				=> array('Setooz', 'query'),
		'hr.setooz.com'				=> array('Setooz', 'query'),
		'nl.setooz.com'				=> array('Setooz', 'query'),
		'fa.setooz.com'				=> array('Setooz', 'query'),
		'ro.setooz.com'				=> array('Setooz', 'query'),
		'sr.setooz.com'				=> array('Setooz', 'query'),
		'ur.setooz.com'				=> array('Setooz', 'query'),

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
		'www.suche.info'			=> array('Suche.info', 'Keywords'),
	
		// Suchmaschine.com
		'www.suchmaschine.com'		=> array('Suchmaschine.com', 'suchstr', 'cgi-bin/wo.cgi?suchstr={k}'),

		// Suchnase
		'www.suchnase.de'			=> array('Suchnase', 'q'),

		// Technorati
		'technorati.com'			=> array('Technorati', 'q', 'search?return=sites&authority=all&q={k}'),

		// Teoma
		'www.teoma.com'				=> array('Teoma', 'q', 'web?q={k}'),

		// Tiscali
		'search.tiscali.it'			=> array('Tiscali', 'q', '?q={k}'),
		'search-dyn.tiscali.it'		=> array('Tiscali', 'q'),
		'hledani.tiscali.cz'		=> array('Tiscali', 'query', false, 'windows-1250'),

		// Tixuma
		'www.tixuma.de'				=> array('Tixuma', 'sc', 'index.php?mp=search&stp=&sc={k}&tg=0'),

		// T-Online
		'suche.t-online.de'			=> array('T-Online', 'q'),
		'navigationshilfe.t-online.de'=> array('T-Online', 'q', 'dtag/dns/results?mode=search_top&q={k}'),

		// Trouvez.com
		'www.trouvez.com'			=> array('Trouvez.com', 'query'),

		// Trusted-Search
		'www.trusted--search.com'	=> array('Trusted Search', 'w', 'search?w={k}'),

		// Twingly
		'www.twingly.com'			=> array('Twingly', 'q', 'search?q={k}'),

		// Vinden
		'www.vinden.nl'				=> array('Vinden', 'q', '?q={k}'),

		// Vindex
		'www.vindex.nl'				=> array('Vindex', 'search_for', '/web?search_for={k}'),
		'search.vindex.nl'			=> array('Vindex', 'search_for'),

		// Virgilio
		'ricerca.virgilio.it'		=> array('Virgilio', 'qs', 'ricerca?qs={k}'),

		// Voila
		'search.ke.voila.fr'		=> array('Voila', 'rdata', 'S/voila?rdata={k}'),
		'www.lemoteur.fr'			=> array('Voila', 'rdata', 'S/voila?rdata={k}'), // uses voila search

		// Volny
		'web.volny.cz'				=> array('Volny', 'search', 'fulltext/?search={k}', 'windows-1250'),

		// Walhello 
		'www.walhello.info'			=> array('Walhello', 'key', 'search?key={k}'),
		'www.walhello.com'			=> array('Walhello', 'key', 'search?key={k}'),
		'www.walhello.de'			=> array('Walhello', 'key', 'search?key={k}'),
		'www.walhello.nl'			=> array('Walhello', 'key', 'search?key={k}'),

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
		'search.yahoo.co.jp'		=> array('Yahoo!', 'p'),
		'ar.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'au.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'br.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'ch.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'ca.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'cade.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'cf.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'de.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'es.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'espanol.search.yahoo.com'	=> array('Yahoo!', 'p'),
		'fi.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'fr.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'hk.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'id.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'it.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'in.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'kr.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'mx.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'nl.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'qc.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'ru.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'se.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'tw.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'uk.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'us.search.yahoo.com'		=> array('Yahoo!', 'p'),
		'm.yahoo.com'				=> array('Yahoo!', 'p'),
		'ar.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'au.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'br.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'ch.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'ca.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'cade.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'cf.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'de.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'es.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'espanol.m.yahoo.com'		=> array('Yahoo!', 'p'),
		'fi.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'fr.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'hk.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'id.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'it.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'in.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'kr.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'mx.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'nl.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'qc.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'ru.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'se.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'tw.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'uk.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'us.m.yahoo.com'			=> array('Yahoo!', 'p'),
		'us.yhs.search.yahoo.com'	=> array('Yahoo!', 'p'),
		'it.yhs.search.yahoo.com '	=> array('Yahoo!', 'p'),
		'search.cn.yahoo.com'		=> array('Yahoo!', 'p'),
		'one.cn.yahoo.com'			=> array('Yahoo!', 'p'),
		'siteexplorer.search.yahoo.com'	=> array('Yahoo!', 'p'),
		'de.dir.yahoo.com'			=> array('Yahoo! Webverzeichnis', ''),
		'cf.dir.yahoo.com'			=> array('Yahoo! Directory', ''),
		'fr.dir.yahoo.com'			=> array('Yahoo! Directory', ''),

		// Yahoo! Images
		'images.search.yahoo.com'	=> array('Yahoo! Images', 'p', 'search/images?p={k}'),
		'ar.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'au.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'br.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'ch.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'ca.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'cade.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'cf.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'de.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'es.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'espanol.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'fi.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'fr.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'hk.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'id.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'it.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'kr.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'mx.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'nl.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'qc.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'ru.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'se.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'tw.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'uk.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'us.images.search.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'images.m.yahoo.com'			=> array('Yahoo! Images', 'p', 'search/images?p={k}'),
		'ar.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'au.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'br.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'ch.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'ca.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'cade.images.m.yahoo.com'		=> array('Yahoo! Images', 'p'),
		'cf.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'de.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'es.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'espanol.images.m.yahoo.com'	=> array('Yahoo! Images', 'p'),
		'fi.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'fr.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'hk.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'id.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'it.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'kr.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'mx.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'nl.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'qc.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'ru.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'se.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'tw.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'uk.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
		'us.images.m.yahoo.com'			=> array('Yahoo! Images', 'p'),
	
		// Yandex
		'yandex.ru'					=> array('Yandex', 'text', 'yandsearch?text={k}'),
		'yandex.ua'					=> array('Yandex', 'text'),
		'www.yandex.ru'				=> array('Yandex', 'text'),

		// Yandex Images
		'images.yandex.ru'			=> array('Yandex Images', 'text', 'yandsearch?text={k}'),
		'images.yandex.ua'			=> array('Yandex Images', 'text'),

		// Yasni
		'www.yasni.de'				=> array('Yasni', 'query'),
		'www.yasni.com'				=> array('Yasni', 'query'),
		'www.yasni.co.uk'			=> array('Yasni', 'query'),
		'www.yasni.ch'				=> array('Yasni', 'query'),
		'www.yasni.at'				=> array('Yasni', 'query'),

		// Yellowmap
		'www.yellowmap.de'			=> array('Yellowmap', ' '),
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
		'www.zoohoo.cz'				=> array('Zoohoo', 'q', false, 'windows-1250'),

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
