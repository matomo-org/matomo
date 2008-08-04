<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: SearchEngines.php 569 2008-07-22 23:12:58Z matt $
 * 
 * @package Piwik_Referers
 */
/**
 * Search Engine database
 * 
 * ======================================
 * HOW TO ADD A SEARCH ENGINE TO THE LIST
 * ======================================
 * If you want to add a new entry, please email us the information + icon at hello at piwik.org
 * 
 * Detail of a line:
 * Url => array( SearchEngineName, VariableKeyword, [charset used by the search engine])
 * 
 * The main search engine URL has to be at the top of the list for the given search Engine.
 * 
 * You can add new search engines icons by adding the icon 
 * in the plugins/Referers/images/SearchEngines directory 
 * using the format "mainSearchEngineUrl.png". Example: www.google.com.png
 *  
 * 
 */
if(!isset($GLOBALS['Piwik_SearchEngines'] ))
{
	$GLOBALS['Piwik_SearchEngines'] = array(
	
		//" "		=> array(" ", " " [, " "]),
		
		// 1
		"1.cz" 						=> array("1.cz", "q", "iso-8859-2"),
		"www.1.cz" 					=> array("1.cz", "q", "iso-8859-2"),
		
		// 1und1
		"portal.1und1.de"			=> array("1und1", "search"),
		
		// 3271
		"nmsearch.3721.com"			=> array("3271", "p"),
		"seek.3721.com"				=> array("3271", "p"),
		
		// A9
		"www.a9.com"				=> array("A9", ""),
		"a9.com"					=> array("A9", ""),
		
		// Abacho
		"search.abacho.com"		=> array("Abacho", "q"),
		
		// about
		"search.about.com"		=> array("About", "terms"),
		
		//Acoon
		"www.acoon.de"			=> array("Acoon", "begriff"),
		
		//Acont
		"acont.de"			=> array("Acont", "query"),
		
		//Alexa
		"www.alexa.com"		        => array("Alexa", "q"),
		"alexa.com"		        => array("Alexa", "q"),
		
		//Alice Adsl
		"rechercher.aliceadsl.fr"	=> array("Alice Adsl", "qs"),
		"search.alice.it"		      => array("Alice (Virgilio)", "qt"),
		
		//Allesklar
		"www.allesklar.de"		=> array("Allesklar", "words"),
		
		// AllTheWeb 
		"www.alltheweb.com"		    => array("AllTheWeb", "q"),
		
		// all.by
		"all.by"			=> array("All.by", "query"),
		
		// Altavista
		"www.altavista.com"		=> array("AltaVista", "q"),
		"listings.altavista.com"        => array("AltaVista", "q"),
		"www.altavista.de"		=> array("AltaVista", "q"),
		"altavista.fr"			=> array("AltaVista", "q"),
		"de.altavista.com"		=> array("AltaVista", "q"),
		"fr.altavista.com"		=> array("AltaVista", "q"),
		"es.altavista.com"		=> array("AltaVista", "q"),
		"www.altavista.fr"		=> array("AltaVista", "q"),
		"search.altavista.com"		=> array("AltaVista", "q"),
		"search.fr.altavista.com"	=> array("AltaVista", "q"),
		"se.altavista.com"		=> array("AltaVista", "q"),
		"be-nl.altavista.com" 		=> array("AltaVista", "q"),
		"be-fr.altavista.com" 		=> array("AltaVista", "q"),
		"it.altavista.com" 		=> array("AltaVista", "q"),
		"us.altavista.com" 		=> array("AltaVista", "q"),
		"nl.altavista.com" 		=> array("Altavista", "q"),
		"ch.altavista.com" 		=> array("AltaVista", "q"),
		
		// APOLLO7
		"www.apollo7.de"		=> array("Apollo7", "query"),
		"apollo7.de"			=> array("Apollo7", "query"),
		
		// AOL
		"search.aol.com"		=> array("AOL", "query"),
		"aolsearch.aol.com"		=> array("AOL", "query"),
		"www.aolrecherche.aol.fr"	=> array("AOL", "q"),
		"www.aolrecherches.aol.fr" 	=> array("AOL", "query"),
		"www.aolimages.aol.fr"   	=> array("AOL", "query"),
		"www.recherche.aol.fr"		=> array("AOL", "q"),
		"aolsearcht.aol.com"		=> array("AOL", "query"),
		"find.web.aol.com"		=> array("AOL", "query"),
		"recherche.aol.ca"		=> array("AOL", "query"),
		"aolsearch.aol.co.uk"		=> array("AOL", "query"),
		"search.aol.co.uk"		=> array("AOL", "query"),
		"aolrecherche.aol.fr"		=> array("AOL", "q"),
		"sucheaol.aol.de"		=> array("AOL", "q"),
		"suche.aol.de"			=> array("AOL", "q"),
		"suche.aolsvc.de"		=> array("AOL", "q"),
		
		"aolbusqueda.aol.com.mx"	=> array("AOL", "query"),
		
		// Aport
		"sm.aport.ru"			=> array("Aport", "r"),
		
		// Arcor
		"www.arcor.de"			=> array("Arcor", "Keywords"),
		
		// Arianna (Libero.it)
		"arianna.libero.it" 		=> array("Arianna", "query"),
		
		// Ask
		"www.ask.com"			=> array("Ask", "ask"),
		"web.ask.com"			=> array("Ask", "ask"),
		"www.ask.co.uk"			=> array("Ask", "q"),
		"uk.ask.com"			=> array("Ask", "q"),
		"fr.ask.com"			=> array("Ask", "q"),
		"de.ask.com"			=> array("Ask", "q"),
		"es.ask.com"			=> array("Ask", "q"),
		"it.ask.com"			=> array("Ask", "q"),
		"nl.ask.com"			=> array("Ask", "q"),
		"ask.jp"			=> array("Ask", "q"),
		
		// Atlas
		"search.atlas.cz" 		=> array("Atlas", "q", "windows-1250"),
		
		// Austronaut
		"www2.austronaut.at"		=> array("Austronaut", "begriff"),
		
		// Baidu
		"www.baidu.com"			=> array("Baidu", "wd"),
		"www1.baidu.com"		=> array("Baidu", "wd"),
		
		// BBC
		"search.bbc.co.uk"	        => array("BBC", "q"),
		
		// Bellnet
		"www.suchmaschine.com"		 => array("Bellnet", "suchstr"),
		
		// Biglobe
		"cgi.search.biglobe.ne.jp"	=> array("Biglobe", "q"),
		
		// Bild
		"www.bild.t-online.de"	        => array("Bild.de (enhanced by Google)", "query"),
		
		//Blogdigger
		"www.blogdigger.com"		=> array("Blogdigger","q"),
		
		//Bloglines
		"www.bloglines.com"		=> array("Bloglines","q"),
		
		//Blogpulse
		"www.blogpulse.com"		=> array("Blogpulse","query"),
		
		//Bluewin
		"search.bluewin.ch"		=> array("Bluewin","query"),
		
		// Caloweb
		"www.caloweb.de"		=> array("Caloweb", "q"),
		
		// Cegetel (Google)
		"www.cegetel.net" 		=> array("Cegetel (Google)", "q"),
		
		// Centrum
		"fulltext.centrum.cz" 		=> array("Centrum", "q", "windows-1250"),
		"morfeo.centrum.cz" 		=> array("Centrum", "q", "windows-1250"),
		"search.centrum.cz" 		=> array("Centrum", "q", "windows-1250"),
		
		// Chello
		"www.chello.fr"		 	=> array("Chello", "q1"),
		
		// Club Internet
		"recherche.club-internet.fr"    => array("Club Internet", "q"),
		
		// Comcast
		"www.comcast.net" 		=> array("Comcast", "query"),
		
		// Comet systems
		"search.cometsystems.com"	=> array("CometSystems", "q"),
		
		// Compuserve
		"suche.compuserve.de"	        => array("Compuserve.de (Powered by Google)", "q"),
		"websearch.cs.com"		     => array("Compuserve.com (Enhanced by Google)", "query"),
		
		// Copernic
		"metaresults.copernic.com"	=> array("Copernic", " "),
		
		// Crossbot
		"www.crossbot.de"		=> array("Crossbot", "q"),
	
		// DasOertliche
		"www.dasoertliche.de"	        => array("DasOertliche", "kw"),
		
		// DasTelefonbuch
		"www.4call.dastelefonbuch.de"	=> array("DasTelefonbuch", "kw"),
		
		// Defind.de
		"suche.defind.de"	        => array("Defind.de", "search"),
		
		// Deskfeeds
		"www.deskfeeds.com"	        => array("Deskfeeds", "sx"),
		
		// Dino
		"www.dino-online.de"		=> array("Dino", "query"),
		
		// dir.com
		"fr.dir.com" 			=> array("dir.com", "req"),
		
		// dmoz
		"dmoz.org"			=> array("dmoz", "search"),
		"editors.dmoz.org"		=> array("dmoz", "search"),
		"search.dmoz.org"		=> array("dmoz", "search"),
		"www.dmoz.org"			=> array("dmoz", "search"),
		
		// Dogpile
		"search.dogpile.com"		=> array("Dogpile", "q"),
		"nbci.dogpile.com"		=> array("Dogpile", "q"),
		
		// earthlink
		"search.earthlink.net"		=> array("Earthlink", "q"),
		
		// Eniro
		"www.eniro.se" 			=> array("Eniro", "q"),
		
		// Espotting 
		"affiliate.espotting.fr"	=> array("Espotting", "keyword"),
		
		// Eudip
		"www.eudip.com"			=> array("Eudip", " "),
		
		// Eurip
		"www.eurip.com"			=> array("Eurip", "q"),
		
		// Euroseek
		"www.euroseek.com"		=> array("Euroseek", "string"),
		
		// Excite
		"www.excite.it" 		=> array("Excite", "q"),
		"msxml.excite.com"		=> array("Excite", "qkw"),
		"www.excite.fr"			=> array("Excite", "search"),
		
		// Exalead
		"www.exalead.fr"		=> array("Exalead", "q"),
		"www.exalead.com"		=> array("Exalead", "q"),
		
		// eo
		"eo.st"				=> array("eo", "q"),
		
		// Feedminer
		"www.feedminer.com"		=> array("Feedminer", "q"),
		
		// Feedster
		"www.feedster.com"		=> array("Feedster", ""),
		
		// Francite
		"recherche.francite.com"	=> array("Francite", "name"),
		"antisearch.francite.com"	=> array("Francite", "KEYWORDS"),
		
		// Fireball
		"suche.fireball.de"		=> array("Fireball", "query"),
		
		
		// Firstfind
		"www.firstsfind.com"		=> array("Firstsfind", "qry"),
		
		// Fixsuche
		"www.fixsuche.de"		=> array("Fixsuche", "q"),
		
		// Flix
		"www.flix.de"			=> array("Flix.de", "keyword"),
		
		// Free
		"search.free.fr"		=> array("Free", "q"),
		"search1-2.free.fr"		=> array("Free", "q"),
		"search1-1.free.fr"		=> array("Free", "q"),
		
		// Freenet
		"suche.freenet.de"		=> array("Freenet", "query"),
		
		//Froogle
		"froogle.google.com" 		=> array("Google (Froogle)", "q"),
		"froogle.google.de" 		=> array("Google (Froogle)", "q"),
		"froogle.google.co.uk" 		=> array("Google (Froogle)", "q"),
		
		//GAIS
		"gais.cs.ccu.edu.tw" 		=> array("GAIS)", "query"),
		
		// Gigablast
		"www.gigablast.com" 		=> array("Gigablast", "q"),
		"blogs.gigablast.com" 		=> array("Gigablast (Blogs)", "q"),
		"travel.gigablast.com" 		=> array("Gigablast (Travel)", "q"),
		"dir.gigablast.com" 		=> array("Gigablast (Directory)", "q"),
		"gov.gigablast.com" 		=> array("Gigablast (Gov)", "q"),
		
		// GMX
		"suche.gmx.net"			=> array("GMX", "su"),
		"www.gmx.net"			=> array("GMX", "su"),
		
		// goo
		"search.goo.ne.jp"		=> array("goo", "mt"),
		"ocnsearch.goo.ne.jp"		=> array("goo", "mt"),
		
		
		// Google
		"www.google.com"		=> array("Google", "q"),
		"gogole.fr"				=> array("Google", "q"),
		"www.gogole.fr"			=> array("Google", "q"),
		"wwwgoogle.fr"			=> array("Google", "q"),
		"ww.google.fr"			=> array("Google", "q"),
		"w.google.fr"			=> array("Google", "q"),
		"www.google.fr"			=> array("Google", "q"),
		"www.google.fr."		=> array("Google", "q"),
		"google.fr"				=> array("Google", "q"),
		"www2.google.com"		=> array("Google", "q"),
		"w.google.com"			=> array("Google", "q"),
		"ww.google.com"			=> array("Google", "q"),
		"wwwgoogle.com"		    => array("Google", "q"),
		"www.gogole.com"		=> array("Google", "q"),
		"www.gppgle.com"		=> array("Google", "q"),
		"go.google.com"			=> array("Google", "q"),
		"www.google.ae"			=> array("Google", "q"),
		"www.google.as"			=> array("Google", "q"),
		"www.google.at"			=> array("Google", "q"),
		"wwwgoogle.at"			=> array("Google", "q"),
		"ww.google.at"			=> array("Google", "q"),
		"w.google.at"			=> array("Google", "q"),
		"www.google.az"			=> array("Google", "q"),
		"www.google.be"			=> array("Google", "q"),
		"www.google.bg"			=> array("Google", "q"),
		"www.google.ba"			=> array("Google", "q"),
		"google.bg"				=> array("Google", "q"),
		"www.google.bi"			=> array("Google", "q"),
		"www.google.ca"			=> array("Google", "q"),
		"ww.google.ca"			=> array("Google", "q"),
		"w.google.ca"			=> array("Google", "q"),
		"www.google.cc"			=> array("Google", "q"),
		"www.google.cd"			=> array("Google", "q"),
		"www.google.cg"			=> array("Google", "q"),
		"www.google.ch"			=> array("Google", "q"),
		"ww.google.ch"			=> array("Google", "q"),
		"w.google.ch"			=> array("Google", "q"),
		"www.google.ci"			=> array("Google", "q"),
		"www.google.cl"			=> array("Google", "q"),
		"www.google.cn"			=> array("Google", "q"),
		"www.google.co"			=> array("Google", "q"),
		"www.google.cz"			=> array("Google", "q"),
		"wwwgoogle.cz"			=> array("Google", "q"),
		"www.google.de"			=> array("Google", "q"),
		"ww.google.de"			=> array("Google", "q"),
		"w.google.de"			=> array("Google", "q"),
		"wwwgoogle.de" 			=> array("Google", "q"),
		"www.googleearth.de" 	=> array("Google", "q"),
		"googleearth.de"		=> array("Google", "q"),
		"google.gr"				=> array("Google", "q"),
		"google.hr"				=> array("Google", "q"),
		"www.google.dj"			=> array("Google", "q"),
		"www.google.dk"			=> array("Google", "q"),
		"www.google.es"			=> array("Google", "q"),
		"www.google.fi"			=> array("Google", "q"),
		"www.google.fm"			=> array("Google", "q"),
		"www.google.gg"			=> array("Google", "q"),
		"www.googel.fi"			=> array("Google", "q"),
		"www.googleearth.fr"	=> array("Google", "q"),
		"www.google.gl"			=> array("Google", "q"),
		"www.google.gm"			=> array("Google", "q"),
		"www.google.gr"			=> array("Google", "q"),
		"www.google.hn"			=> array("Google", "q"),
		"www.google.hr"			=> array("Google", "q"),
		"www.google.hu"			=> array("Google", "q"),
		"www.google.ie"			=> array("Google", "q"),
		"www.google.is"			=> array("Google", "q"),
		"www.google.it"			=> array("Google", "q"),
		"www.google.jo"			=> array("Google", "q"),
		"www.google.kz"			=> array("Google", "q"),
		"www.google.li"			=> array("Google", "q"),
		"www.google.lt"			=> array("Google", "q"),
		"www.google.lu"			=> array("Google", "q"),
		"www.google.lv"			=> array("Google", "q"),
		"www.google.ms"			=> array("Google", "q"),
		"www.google.mu"			=> array("Google", "q"),
		"www.google.mw"			=> array("Google", "q"),
		"www.google.md"			=> array("Google", "q"),
		"www.google.nl"			=> array("Google", "q"),
		"www.google.no"			=> array("Google", "q"),
		"www.google.pl"			=> array("Google", "q"),
		"www.google.sk" 		=> array("Google", "q"),
		"www.google.pn"			=> array("Google", "q"),
		"www.google.pt"			=> array("Google", "q"),
		"www.google.dk"			=> array("Google", "q"),
		"www.google.ro"			=> array("Google", "q"),
		"www.google.ru"			=> array("Google", "q"),
		"www.google.rw"			=> array("Google", "q"),
		"www.google.se"			=> array("Google", "q"),
		"www.google.sn"			=> array("Google", "q"),
		"www.google.sh"			=> array("Google", "q"),
		"www.google.si"			=> array("Google", "q"),
		"www.google.sm" 		=> array("Google", "q"),
		"www.google.td"			=> array("Google", "q"),
		"www.google.tt"			=> array("Google", "q"),
		"www.google.uz"			=> array("Google", "q"),
		"www.google.vg"			=> array("Google", "q"),
		"www.google.com.ar"		=> array("Google", "q"),
		"www.google.com.au"		=> array("Google", "q"),
		"www.google.com.bo"		=> array("Google", "q"),
		"www.google.com.br"		=> array("Google", "q"),
		"www.google.com.co"		=> array("Google", "q"),
		"www.google.com.cu"		=> array("Google", "q"),
		"www.google.com.ec"		=> array("Google", "q"),
		"www.google.com.eg"		=> array("Google", "q"),
		"www.google.com.do"		=> array("Google", "q"),
		"www.google.com.fj"		=> array("Google", "q"),
		"www.google.com.gr" 	=> array("Google", "q"),
		"www.google.com.gt" 	=> array("Google", "q"),
		"www.google.com.hk"		=> array("Google", "q"),
		"www.google.com.ly"		=> array("Google", "q"),
		"www.google.com.mt"		=> array("Google", "q"),
		"www.google.com.mx"		=> array("Google", "q"),
		"www.google.com.my"		=> array("Google", "q"),
		"www.google.com.nf"		=> array("Google", "q"),
		"www.google.com.ni"		=> array("Google", "q"),
		"www.google.com.np"		=> array("Google", "q"),
		"www.google.com.pa"		=> array("Google", "q"),
		"www.google.com.pe" 	=> array("Google", "q"),
		"www.google.com.ph"		=> array("Google", "q"),
		"www.google.com.pk"		=> array("Google", "q"),
		"www.google.com.pl"		=> array("Google", "q"),
		"www.google.com.pr"		=> array("Google", "q"),
		"www.google.com.py"		=> array("Google", "q"),
		"www.google.com.qa"		=> array("Google", "q"),
		"www.google.com.om"		=> array("Google", "q"),
		"www.google.com.ru"		=> array("Google", "q"),
		"www.google.com.sg"		=> array("Google", "q"),
		"www.google.com.sa"		=> array("Google", "q"),
		"www.google.com.sv"		=> array("Google", "q"),
		"www.google.com.tr"		=> array("Google", "q"),
		"www.google.com.tw"		=> array("Google", "q"),
		"www.google.com.ua"		=> array("Google", "q"),
		"www.google.com.uy"		=> array("Google", "q"),
		"www.google.com.vc"		=> array("Google", "q"),
		"www.google.com.vn"		=> array("Google", "q"),
		"www.google.co.cr"		=> array("Google", "q"),
		"www.google.co.gg"		=> array("Google", "q"),
		"www.google.co.hu"		=> array("Google", "q"),
		"www.google.co.id"		=> array("Google", "q"),
		"www.google.co.il"		=> array("Google", "q"),
		"www.google.co.in" 		=> array("Google", "q"),
		"www.google.co.je"		=> array("Google", "q"),
		"www.google.co.jp"		=> array("Google", "q"),
		"www.google.co.ls"		=> array("Google", "q"),
		"www.google.co.ke" 		=> array("Google", "q"),
		"www.google.co.kr"		=> array("Google", "q"),
		"www.google.co.nz"		=> array("Google", "q"),
		"www.google.co.th"		=> array("Google", "q"),
		"www.google.co.uk"		=> array("Google", "q"),
		"www.google.co.ve"		=> array("Google", "q"),
		"www.google.co.za" 		=> array("Google", "q"),
		"www.google.co.ma"		=> array("Google", "q"),
		"www.goggle.com"		=> array("Google", "q"),
		
		
		// Powered by Google 
		"www.charter.net" 		=> array("Google", "q"),
		"brisbane.t-online.de" 	        => array("Google", "q"),
		"miportal.bellsouth.net"        => array("Google", "string"),
		"home.bellsouth.net"	        => array("Google", "string"),
		"pesquisa.clix.pt" 		=> array("Google", "q"),
		"google.startsiden.no" 	        => array("Google", "q"),
		"google.startpagina.nl"		=> array("Google", "q"),
		"search.peoplepc.com" 	        => array("Google", "q"),
		"www.google.interia.pl"		=> array("Google", "q"),
		"buscador.terra.es" 	        => array("Google", "query"),
		"buscador.terra.cl" 	        => array("Google", "query"),
		"buscador.terra.com.br"		=> array("Google", "query"),
		"www.icq.com" 			=> array("Google", "q"),
		"www.adelphia.net" 		=> array("Google", "q"),
		"so.qq.com" 			=> array("Google", "word"),
		"misc.skynet.be" 		=> array("Google", "keywords"),
		"www.start.no" 			=> array("Google", "q"),
		"verden.abcsok.no"		=> array("Google", "q"),
		"search.sweetim.com"	        => array("Google", "q"),
		
		
		//Google Blogsearch
		"blogsearch.google.com"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.de"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.fr"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.co.uk"	=> array("Google Blogsearch", "q"),
		"blogsearch.google.it"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.net"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.es"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.ru"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.be"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.nl"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.at"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.ch"		=> array("Google Blogsearch", "q"),
		"blogsearch.google.pl"		=> array("Google Blogsearch", "q"),
		
		
		// Google translation
		"translate.google.com"		=> array("Google Translations", "q"),
		
		// Google Directory
		"directory.google.com"		=> array("Google Directory", " "),
		
		// Google Images
		"images.google.fr"		=> array("Google Images", "q"),
		"images.google.be" 		=> array("Google Images", "q"),
		"images.google.ca" 		=> array("Google Images", "q"),
		"images.google.co.uk"		=> array("Google Images", "q"),
		"images.google.de" 		=> array("Google Images", "q"),
		"images.google.it"    		=> array("Google Images", "q"),
		"images.google.at"		=> array("Google Images", "q"),
		"images.google.bg"		=> array("Google Images", "q"),
		"images.google.ch"		=> array("Google Images", "q"),
		"images.google.ci"		=> array("Google Images", "q"),
		"images.google.com.au"		=> array("Google Images", "q"),
		"images.google.com.cu"		=> array("Google Images", "q"),
		"images.google.co.id"		=> array("Google Images", "q"),
		"images.google.co.il"		=> array("Google Images", "q"),
		"images.google.co.in"		=> array("Google Images", "q"),
		"images.google.co.jp"		=> array("Google Images", "q"),
		"images.google.co.hu"		=> array("Google Images", "q"),
		"images.google.co.kr"		=> array("Google Images", "q"),
		"images.google.co.nz"		=> array("Google Images", "q"),
		"images.google.co.th"		=> array("Google Images", "q"),
		"images.google.co.tw"		=> array("Google Images", "q"),
		"images.google.co.ve"		=> array("Google Images", "q"),
		"images.google.com.ar"		=> array("Google Images", "q"),
		"images.google.com.br"		=> array("Google Images", "q"),
		"images.google.com.cu"		=> array("Google Images", "q"),
		"images.google.com.do"		=> array("Google Images", "q"),
		"images.google.com.gr"		=> array("Google Images", "q"),
		"images.google.com.hk"		=> array("Google Images", "q"),
		"images.google.com.mx"		=> array("Google Images", "q"),
		"images.google.com.my"		=> array("Google Images", "q"),
		"images.google.com.pe"		=> array("Google Images", "q"),
		"images.google.com.tr"		=> array("Google Images", "q"),
		"images.google.com.tw"		=> array("Google Images", "q"),
		"images.google.com.ua"		=> array("Google Images", "q"),
		"images.google.com.vn"		=> array("Google Images", "q"),
		"images.google.dk"		=> array("Google Images", "q"),
		"images.google.es"		=> array("Google Images", "q"),
		"images.google.fi"		=> array("Google Images", "q"),
		"images.google.gg"		=> array("Google Images", "q"),
		"images.google.gr"		=> array("Google Images", "q"),
		"images.google.it"		=> array("Google Images", "q"),
		"images.google.ms"		=> array("Google Images", "q"),
		"images.google.nl"		=> array("Google Images", "q"),
		"images.google.no"		=> array("Google Images", "q"),
		"images.google.pl"		=> array("Google Images", "q"),
		"images.google.pt"		=> array("Google Images", "q"),
		"images.google.ro"		=> array("Google Images", "q"),
		"images.google.ru"		=> array("Google Images", "q"),
		"images.google.se"		=> array("Google Images", "q"),
		"images.google.sk"		=> array("Google Images", "q"),
		"images.google.com"		=> array("Google Images", "q"),
		
		// Google News
		"news.google.com" 		=> array("Google News", "q"),
		"news.google.se" 		=> array("Google News", "q"),
		"news.google.com" 		=> array("Google News", "q"),
		"news.google.es" 		=> array("Google News", "q"),
		"news.google.ch" 		=> array("Google News", "q"),
		"news.google.lt" 		=> array("Google News", "q"),
		"news.google.ie" 		=> array("Google News", "q"),
		"news.google.de" 		=> array("Google News", "q"),
		"news.google.cl" 		=> array("Google News", "q"),
		"news.google.com.ar" 		=> array("Google News", "q"),
		"news.google.fr" 		=> array("Google News", "q"),
		"news.google.ca" 		=> array("Google News", "q"),
		"news.google.co.uk" 		=> array("Google News", "q"),
		"news.google.co.jp" 		=> array("Google News", "q"),
		"news.google.com.pe" 		=> array("Google News", "q"),
		"news.google.com.au" 		=> array("Google News", "q"),
		"news.google.com.mx" 		=> array("Google News", "q"),
		"news.google.com.hk" 		=> array("Google News", "q"),
		"news.google.co.in" 		=> array("Google News", "q"),
		"news.google.at" 		=> array("Google News", "q"),
		"news.google.com.tw" 		=> array("Google News", "q"),
		"news.google.com.co" 		=> array("Google News", "q"),
		"news.google.co.ve" 		=> array("Google News", "q"),
		"news.google.lu" 		=> array("Google News", "q"),
		"news.google.com.ly" 		=> array("Google News", "q"),
		"news.google.it" 		=> array("Google News", "q"),
		"news.google.sm" 		=> array("Google News", "q"),
		
		// Goyellow.de
		"www.goyellow.de"	        => array("GoYellow.de", "MDN"),
		
		// HighBeam
		"www.highbeam.com"	        => array("HighBeam", "Q"),
		
		// Hit-Parade
		"recherche.hit-parade.com"	=> array("Hit-Parade", "p7"),
		"class.hit-parade.com"		=> array("Hit-Parade", "p7"),
		
		// Hotbot via Lycos
		"hotbot.lycos.com"		=> array("Hotbot (Lycos)", "query"),
		"search.hotbot.de"		=> array("Hotbot", "query"),
		"search.hotbot.fr"		=> array("Hotbot", "query"),
		"www.hotbot.com"		=> array("Hotbot", "query"),
		
		// 1stekeuze
		"zoek.1stekeuze.nl" 		=> array("1stekeuze", "terms"),
		
		// Infoseek
		"search.www.infoseek.co.jp"     => array("Infoseek", "qt"),
		
		// Icerocket
		"blogs.icerocket.com"		  => array("Icerocket", "qt"),
		
		// ICQ
		"www.icq.com"			=> array("ICQ", "q"),
		"search.icq.com"		=> array("ICQ", "q"),
		
		// Ilse
		"spsearch.ilse.nl" 		=> array("Startpagina", "search_for"),
		"be.ilse.nl" 			=> array("Ilse BE", "query"),
		"search.ilse.nl" 		=> array("Ilse NL", "search_for"),
		
		// Iwon
		"search.iwon.com"		=> array("Iwon", "searchfor"),
		
		// Ixquick
		"ixquick.com"			=> array("Ixquick", "query"),
		"www.eu.ixquick.com"		=> array("Ixquick", "query"),
		"us.ixquick.com"		=> array("Ixquick", "query"),
		"s1.us.ixquick.com"		=> array("Ixquick", "query"),
		"s2.us.ixquick.com"		=> array("Ixquick", "query"),
		"s3.us.ixquick.com"		=> array("Ixquick", "query"),
		"s4.us.ixquick.com"		=> array("Ixquick", "query"),
		"s5.us.ixquick.com"		=> array("Ixquick", "query"),
		"eu.ixquick.com" 		=> array("Ixquick","query"),
		
		// Jyxo
		"jyxo.cz" 			=> array("Jyxo", "q"),
		
		// Jungle Spider
		"www.jungle-spider.de"		=> array("Jungle Spider", "qry"),
		
		// Kartoo
		"kartoo.com"			=> array("Kartoo", ""),
		"kartoo.de"			=> array("Kartoo", ""),
		"kartoo.fr"			=> array("Kartoo", ""),
		
		
		// Kataweb
		"www.kataweb.it" 		=> array("Kataweb", "q"),
		
		// Klug suchen
		"www.klug-suchen.de"		   => array("Klug suchen!", "query"),
		
		// La Toile Du Québec via Google
		"google.canoe.com"		=> array("La Toile Du Québec (Google)", "q"),
		"www.toile.com"			=> array("La Toile Du Québec (Google)", "q"),	
		"web.toile.com"			=> array("La Toile Du Québec (Google)", "q"),
		
		// La Toile Du Québec 
		"recherche.toile.qc.ca"		=> array("La Toile Du Québec", "query"),
		
		// Live.com
		"www.live.com"			=> array("Live", "q"),
		"beta.search.live.com"	=> array("Live", "q"),
		"search.live.com"		=> array("Live", "q"),
		"g.msn.com"		        => array("Live", " "),
		
		// Looksmart
		"www.looksmart.com"		=> array("Looksmart", "key"),
		
		// Lycos
		"search.lycos.com"		=> array("Lycos", "query"),
		"vachercher.lycos.fr"		=> array("Lycos", "query"),
		"www.lycos.fr"			=> array("Lycos", "query"),
		"suche.lycos.de"		=> array("Lycos", "query"),
		"search.lycos.de"		=> array("Lycos", "query"),
		"sidesearch.lycos.com"		=> array("Lycos", "query"),
		"www.multimania.lycos.fr" 	=> array("Lycos", "query"),
		"buscador.lycos.es" 	=> array("Lycos", "query"),
		
		// Mail.ru
		"go.mail.ru"			=> array("Mailru", "q"),
		
		// Mamma
		"mamma.com"			=> array("Mamma", "query"),
		"mamma75.mamma.com"		=> array("Mamma", "query"),
		"www.mamma.com"			=> array("Mamma", "query"),
		
		// Meceoo
		"www.meceoo.fr" 		=> array("Meceoo", "kw"),
		
		// Mediaset
		"servizi.mediaset.it" 		=> array("Mediaset", "searchword"),
		
		// Metacrawler
		"search.metacrawler.com"	=> array("Metacrawler", "general"),
		
		// Metager
		"mserv.rrzn.uni-hannover.de"	=> array("Metager", "eingabe"),
		"www.metager.de"		=> array("Metager", "eingabe"),
		
		// Metager2
		"www.metager2.de"	        => array("Metager2", "q"),
		"metager2.de"			       => array("Metager2", "q"),
		
		// Meinestadt
		"www.meinestadt.de"	        => array("Meinestadt.de", "words"),
		
		// Monstercrawler
		"www.monstercrawler.com" 	=> array("Monstercrawler", "qry"),
		
		// Mozbot
		"www.mozbot.fr"			=> array("mozbot", "q"),
		"www.mozbot.co.uk" 		=> array("mozbot", "q"),
		"www.mozbot.com"		=> array("mozbot", "q"),
		
		// MSN
		"search.msn.com"		=> array("MSN", "q"),
		"beta.search.msn.fr"		=> array("MSN", "q"),
		"search.msn.fr"			=> array("MSN", "q"),
		"search.msn.es"			=> array("MSN", "q"),
		"search.msn.se"			=> array("MSN", "q"),
		"search.latam.msn.com"		=> array("MSN", "q"),
		"search.msn.nl" 		=> array("MSN", "q"),
		"leguide.fr.msn.com"		=> array("MSN", "s"),
		"leguide.msn.fr"		=> array("MSN", "s"),
		"search.msn.co.jp"		=> array("MSN", "q"),
		"search.msn.no"			=> array("MSN", "q"),
		"search.msn.at"			=> array("MSN", "q"),
		"search.msn.com.hk"		=> array("MSN", "q"),
		"search.t1msn.com.mx"		=> array("MSN", "q"),
		"fr.ca.search.msn.com"		=> array("MSN", "q"),
		"search.msn.be" 		=> array("MSN", "q"),
		"search.fr.msn.be" 		=> array("MSN", "q"),
		"search.msn.it" 		=> array("MSN", "q"),
		"sea.search.msn.it" 		=> array("MSN", "q"),
		"sea.search.msn.fr" 		=> array("MSN", "q"),
		"sea.search.msn.de" 		=> array("MSN", "q"),
		"sea.search.msn.com" 		=> array("MSN", "q"),
		"sea.search.fr.msn.be" 		=> array("MSN", "q"),
		"search.msn.com.tw" 		=> array("MSN", "q"),
		"search.msn.de" 		=> array("MSN", "q"),
		"search.msn.co.uk" 		=> array("MSN", "q"),
		"search.msn.co.za"		=> array("MSN", "q"),
		"search.msn.ch" 		=> array("MSN", "q"),
		"search.msn.es" 		=> array("MSN", "q"),
		"search.msn.com.br"		=> array("MSN", "q"),
		"search.ninemsn.com.au"		=> array("MSN", "q"),
		"search.msn.dk"			=> array("MSN", "q"),
		"search.arabia.msn.com"		=> array("MSN", "q"),
		"search.prodigy.msn.com"	=> array("MSN", "q"),
		
		// El Mundo
		"ariadna.elmundo.es" 	=> array("El Mundo", "q"),
		
		// MyWebSearch
		"kf.mysearch.myway.com" 	=> array("MyWebSearch", "searchfor"),
		"ms114.mysearch.com" 		=> array("MyWebSearch", "searchfor"),
		"ms146.mysearch.com"	 	=> array("MyWebSearch", "searchfor"),
		"mysearch.myway.com"		=> array("MyWebSearch", "searchfor"),
		"searchfr.myway.com"		=> array("MyWebSearch", "searchfor"),
		"ki.mysearch.myway.com" 	=> array("MyWebSearch", "searchfor"),
		"search.mywebsearch.com"	=> array("MyWebSearch", "searchfor"),
		"www.mywebsearch.com"		=> array("MyWebSearch", "searchfor"),
		
		// Najdi
		"www.najdi.si" 			=> array("Najdi.si", "q"),
		
		// Needtofind
		"ko.search.need2find.com"	=> array("Needtofind", "searchfor"),
		
		// Netster
		"www.netster.com"		=> array("Netster", "keywords"),
		
		// Netscape
		"search-intl.netscape.com"	=> array("Netscape", "search"),
		"www.netscape.fr"		=> array("Netscape", "q"),
		"suche.netscape.de"		=> array("Netscape", "q"),
		"search.netscape.com"		=> array("Netscape", "query"),
		
		// Nomade
		"ie4.nomade.fr"			=> array("Nomade", "s"),
		"rechercher.nomade.aliceadsl.fr"=> array("Nomade (AliceADSL)", "s"),
		"rechercher.nomade.fr"		=> array("Nomade", "s"),
		
		// Northern Light
		"www.northernlight.com"		=> array("Northern Light", "qr"),
		
		// Numéricable
		"www.numericable.fr" 		=> array("Numéricable", "query"),
		
		// Onet
		"szukaj.onet.pl" 		=> array("Onet.pl", "qt"),
		
		// Opera
		"search.opera.com" 		=> array("Opera", "search"),
		
		// Openfind
		"wps.openfind.com.tw" 		=> array("Openfind (Websearch)", "query"),
		"bbs2.openfind.com.tw" 		=> array("Openfind (BBS)", "query"),
		"news.openfind.com.tw" 		=> array("Openfind (News)", "query"),
		
		// Overture
		"www.overture.com"		=> array("Overture", "Keywords"),
		"www.fr.overture.com"		=> array("Overture", "Keywords"),
		
		// Paperball
		"suche.paperball.de" 		=> array("Paperball", "query"),
		
		// Picsearch
		"www.picsearch.com" 		=> array("Picsearch", "q"),
		
		// Plazoo
		"www.plazoo.com" 		=> array("Plazoo", "q"),
		
		// Postami
		"www.postami.com" 		=> array("Postami", "query"),
		
		// Quick searches
		"data.quicksearches.net"	=> array("QuickSearches", "q"),
		
		// Qualigo
		"www.qualigo.de"	        => array("Qualigo", "q"),
		"www.qualigo.ch"	        => array("Qualigo", "q"),
		"www.qualigo.at"	        => array("Qualigo", "q"),
		"www.qualigo.nl"	        => array("Qualigo", "q"),
		
		// Rambler
		"search.rambler.ru" 		=> array("Rambler", "words"),
		
		// Reacteur.com
		"www.reacteur.com"		=> array("Reacteur", "kw"),
		
		// Sapo
		"pesquisa.sapo.pt" 		=> array("Sapo","q"),
		
		// Search.com
		"www.search.com"		=> array("Search.com", "q"),
		
		// Search.ch
		"www.search.ch"			=> array("Search.ch", "q"),
		
		// Search a lot
		"www.searchalot.com"		=> array("Searchalot", "query"),
		
		// Seek
		"www.seek.fr"			=> array("Searchalot", "qry_str"),
		
		// Seekport
		"www.seekport.de"		=> array("Seekport", "query"),
		"www.seekport.co.uk"		=> array("Seekport", "query"),
		"www.seekport.fr"		=> array("Seekport", "query"),
		"www.seekport.at"		=> array("Seekport", "query"),
		"www.seekport.es"		=> array("Seekport", "query"),
		"www.seekport.it"		=> array("Seekport", "query"),
		
		// Seekport (blogs)
		"blogs.seekport.de"		=> array("Seekport (Blogs)", "query"),
		"blogs.seekport.co.uk"		=> array("Seekport (Blogs)", "query"),
		"blogs.seekport.fr"		=> array("Seekport (Blogs)", "query"),
		"blogs.seekport.at"		=> array("Seekport (Blogs)", "query"),
		"blogs.seekport.es"		=> array("Seekport (Blogs)", "query"),
		"blogs.seekport.it"		=> array("Seekport (Blogs)", "query"),
		
		// Seekport (news)
		"news.seekport.de"		=> array("Seekport (News)", "query"),
		"news.seekport.co.uk"		=> array("Seekport (News)", "query"),
		"news.seekport.fr"		=> array("Seekport (News)", "query"),
		"news.seekport.at"		=> array("Seekport (News)", "query"),
		"news.seekport.es"		=> array("Seekport (News)", "query"),
		"news.seekport.it"		=> array("Seekport (News)", "query"),
		
		// Searchscout
		"www.searchscout.com"		=> array("Search Scout", "gt_keywords"),
		
		// Searchy
		"www.searchy.co.uk"		=> array("Searchy", "search_term"),
		
		// Seznam
		"search1.seznam.cz" 		=> array("Seznam", "q"),
		"search2.seznam.cz" 		=> array("Seznam", "q"),
		"search.seznam.cz" 		=> array("Seznam", "q"),
		
		// Sharelook
		"www.sharelook.fr"		=> array("Sharelook", "keyword"),
		"www.sharelook.de"		=> array("Sharelook", "keyword"),
		
		// Skynet
		"search.skynet.be" 		=> array("Skynet", "keywords"),
		
		// Sphere
		"www.sphere.com" 		=> array("Sphere", "q"),
		
		// Startpagina
		"startgoogle.startpagina.nl" 	=> array("Startpagina (Google)", "q"),
		
		// Suchnase
		"www.suchnase.de" 		=> array("Suchnase", "qkw"),
		
		// Supereva
		"search.supereva.com" 		=> array("Supereva", "q"),
		
		// Sympatico
		"search.sympatico.msn.ca"	=> array("Sympatico", "q"),
		"search.sli.sympatico.ca"       => array("Sympatico", "q"),
		"search.fr.sympatico.msn.ca"    => array("Sympatico", "q"),
		"sea.search.fr.sympatico.msn.ca"=> array("Sympatico", "q"),
		
		// Suchmaschine.com
		"www.suchmaschine.com"		=> array("Suchmaschine.com", "suchstr"),
		
		//Technorati
		"www.technorati.com"		=> array("Technorati", " "),
		
		// Teoma
		"www.teoma.com"			=> array("Teoma", "t"),
		
		// Tiscali
		"rechercher.nomade.tiscali.fr"  => array("Tiscali", "s"),
		"search-dyn.tiscali.it" 	=> array("Tiscali", "key"),
		"www.tiscali.co.uk"		=> array("Tiscali", "query"),
		"search-dyn.tiscali.de"		=> array("Tiscali", "key"),
		"hledani.tiscali.cz" 		=> array("Tiscali", "query", "windows-1250"),
		
		// T-Online
		"suche.t-online.de"		=> array("T-Online", "q"),
		
		// Trouvez.com
		"www.trouvez.com"		=> array("Trouvez.com", "query"),
		
		// Trusted-Search
		
		"www.trusted--search.com"       => array("Trusted Search", "w"),
		 
		// Vinden
		"zoek.vinden.nl" 		=> array("Vinden", "query"),
		
		// Vindex
		"www.vindex.nl" 		=> array("Vindex","search_for"),
		
		// Virgilio
		"search.virgilio.it"		=> array("Virgilio", "qs"),
		
		// Voila
		"search.voila.com"		=> array("Voila", "kw"),
		"search.ke.voila.fr"		=> array("Voila", "rdata"),
		"moteur.voila.fr"		=> array("Voila", "kw"),
		"search.voila.fr"		=> array("Voila", "kw"),
		"beta.voila.fr"			=> array("Voila", "kw"),
		
		// Volny
		"web.volny.cz" 			=> array("Volny", "search", "windows-1250"),
		
		// Wanadoo
		"search.ke.wanadoo.fr"		=> array("Wanadoo", "kw"),
		"busca.wanadoo.es"		=> array("Wanadoo", "buscar"),
		
		// Web.de
		"suche.web.de"			=> array("Web.de (Websuche)", "su"),
		"dir.web.de"			=> array("Web.de (Directory)", "su"),
		
		// Webtip
		"www.webtip.de"			=> array("Webtip", "keyword"),
		
		// X-recherche
		"www.x-recherche.com" 		=> array("X-Recherche", "mots"),
		
		// Yahoo
		"search.yahoo.com"		=> array("Yahoo!", "p"),
		"ink.yahoo.com"			=> array("Yahoo!", "p"),
		"ink.yahoo.fr"			=> array("Yahoo!", "p"),
		"fr.ink.yahoo.com"		=> array("Yahoo!", "p"),
		"search.yahoo.co.jp" 		=> array("Yahoo!", "p"),
		"search.yahoo.fr"		=> array("Yahoo!", "p"),
		"ar.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"br.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"ch.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"de.search.yahoo.com"		=> array("Yahoo!", "p"),
		"ca.search.yahoo.com"		=> array("Yahoo!", "p"),
		"cf.search.yahoo.com"		=> array("Yahoo!", "p"),
		"fr.search.yahoo.com"		=> array("Yahoo!", "p"),
		"espanol.search.yahoo.com"	=> array("Yahoo!", "p"),
		"es.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"id.search.yahoo.com"		  => array("Yahoo!", "p"),
		"it.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"kr.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"mx.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"nl.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"uk.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"cade.search.yahoo.com"		=> array("Yahoo!", "p"),
		"tw.search.yahoo.com" 		=> array("Yahoo!", "p"),
		"www.yahoo.com.cn" 		=> array("Yahoo!", "p"),
		
		"de.dir.yahoo.com"		     => array("Yahoo! Webverzeichnis", ""),   
		"cf.dir.yahoo.com"		=> array("Yahoo! Directory", ""),
		"fr.dir.yahoo.com"		=> array("Yahoo! Directory", ""),
		
		// Yandex
		"www.yandex.ru" 		=> array("Yandex", "text"),
		"yandex.ru" 			=> array("Yandex", "text"),
		"search.yaca.yandex.ru" 	=> array("Yandex", "text"),
		"ya.ru" 			=> array("Yandex", "text"),
		"www.ya.ru" 			=> array("Yandex", "text"),
		"images.yandex.ru"		=> array("Yandex Images","text"),
		
		//Yellowmap
		
		"www.yellowmap.de"	        => array("Yellowmap", " "),
		"yellowmap.de"			       => array("Yellowmap", " "),
		
		// Wanadoo
		"search.ke.wanadoo.fr"		=> array("Wanadoo", "kw"),
		"busca.wanadoo.es"		=> array("Wanadoo", "buscar"),
		
		// Wedoo
		"fr.wedoo.com"			=> array("Wedoo", "keyword"),
		
		// Web.nl
		"www.web.nl" 			=> array("Web.nl","query"),
		
		// Weborama
		"www.weborama.fr"		=> array("weborama", "query"),
		
		// WebSearch
		"is1.websearch.com"		=> array("WebSearch", "qkw"),
		"www.websearch.com"		=> array("WebSearch", "qkw"),
		"websearch.cs.com"		=> array("WebSearch", "query"),
		
		// Witch
		"www.witch.de"		        => array("Witch", "search"),
		
		// WXS
		"wxsl.nl" 			=> array("Planet Internet","q"),
		
		// Zoek
		"www3.zoek.nl" 			=> array("Zoek","q"),
		
		// Zhongsou
		"p.zhongsou.com" 		=> array("Zhongsou","w"),
		
		// Zoeken
		"www.zoeken.nl" 		=> array("Zoeken","query"),
		
		// Zoohoo
		"zoohoo.cz" 			=> array("Zoohoo", "q", "windows-1250"),
		"www.zoohoo.cz" 		=> array("Zoohoo", "q", "windows-1250"),
		
		// Zoznam
		"www.zoznam.sk" 		=> array("Zoznam", "s"),
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

