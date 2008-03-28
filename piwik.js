// Web analytics by Piwik - http://piwik.org
// Copyleft 2007, All rights reversed.
var _pk_use_title_as_name = 0;
var _pk_install_tracker = 1;
var _pk_tracker_pause = 500;
var _pk_download_extensions = "7z|aac|avi|csv|doc|exe|flv|gif|gz|jpe?g|js|mp(3|4|e?g)|mov|pdf|phps|png|ppt|rar|sit|tar|torrent|txt|wma|wmv|xls|xml|zip";

// Beginning script
function _pk_plug_normal(_pk_pl) {
	if (_pk_tm.indexOf(_pk_pl) != -1 && (navigator.mimeTypes[_pk_pl].enabledPlugin != null)) 
		return '1';
	return '0';
}

function _pk_plug_ie(_pk_pl)
{
	pk_found = false;
	document.write('<SCR' + 'IPT LANGUAGE=VBScript>\n on error resume next \n pk_found = IsObject(CreateObject("' + _pk_pl + '")) </SCR' + 'IPT>\n');
	if (pk_found) return '1';
	return '0';
}

var _pk_jav = '0'; if(navigator.javaEnabled()) _pk_jav='1';
var _pk_agent = navigator.userAgent.toLowerCase();
var _pk_moz = (navigator.appName.indexOf("Netscape") != -1);
var _pk_ie = (_pk_agent.indexOf("msie") != -1);
var _pk_win = ((_pk_agent.indexOf("win") != -1) || (_pk_agent.indexOf("32bit") != -1));
var _pk_cookie = (navigator.cookieEnabled)? '1' : '0';
if((typeof (navigator.cookieEnabled) == "undefined") && (_pk_cookie == '0')) {
	document.cookie="_pk_testcookie"
	_pk_cookie=(document.cookie.indexOf("_pk_testcookie")!=-1)? '1' : '0';
}

var _pk_dir='0',_pk_fla='0',_pk_pdf='0',_pk_qt = '0',_pk_rea = '0',_pk_wma='0'; 
if (_pk_win && _pk_ie){
	_pk_dir = _pk_plug_ie("SWCtl.SWCtl.1");
	_pk_fla = _pk_plug_ie("ShockwaveFlash.ShockwaveFlash.1");
	if (_pk_plug_ie("PDF.PdfCtrl.1") == '1' || _pk_plug_ie('PDF.PdfCtrl.5') == '1' || _pk_plug_ie('PDF.PdfCtrl.6') == '1') _pk_pdf = '1';
	_pk_qt = _pk_plug_ie("Quicktime.Quicktime"); // Old : "QuickTimeCheckObject.QuickTimeCheck.1"
	_pk_rea = _pk_plug_ie("rmocx.RealPlayer G2 Control.1");
	_pk_wma = _pk_plug_ie("wmplayer.ocx"); // Old : "MediaPlayer.MediaPlayer.1"
} else {
	var _pk_tm = '';
	for (var i=0; i < navigator.mimeTypes.length; i++)
		_pk_tm += navigator.mimeTypes[i].type.toLowerCase();
	_pk_dir = _pk_plug_normal("application/x-director");
	_pk_fla = _pk_plug_normal("application/x-shockwave-flash");
	_pk_pdf = _pk_plug_normal("application/pdf");
	_pk_qt  = _pk_plug_normal("video/quicktime");
	_pk_rea = _pk_plug_normal("audio/x-pn-realaudio-plugin");
	_pk_wma = _pk_plug_normal("application/x-mplayer2");
}
	
var _pk_rtu = '';
try {
	_pk_rtu = top.document.referrer;
} catch(e1) {
	if(parent){ 
		try{ _pk_rtu = parent.document.referrer; } catch(e2) { _pk_rtu=''; }
	}
}
if(_pk_rtu == '') {
	_pk_rtu = document.referrer;
}

function _pk_escape(_pk_str){
	if(typeof(encodeURIComponent) == 'function') {
		return encodeURIComponent(_pk_str);
	} else {
		return escape(_pk_str);
	}
}
var _pk_title = '';
if (document.title && document.title!="") _pk_title = _pk_escape(document.title);

var _pk_called;

function _pk_getUrlLog( _pk_action_name, _pk_site, _pk_pkurl, _pk_custom_vars )
{
	var _pk_custom_vars_str = '';
	if(typeof _pk_custom_vars == "undefined"){
		_pk_custom_vars = false;
	}
	if (_pk_custom_vars) {
		for (var i in _pk_custom_vars){
			if (!Array.prototype[i]){
				_pk_custom_vars_str = _pk_custom_vars_str + '&vars['+ escape(i) + ']' + "=" + escape(_pk_custom_vars[i]);
			}
		}
	}
	
	var _pk_url = document.location.href;
	var _pk_da = new Date();
	var _pk_src = _pk_pkurl
		+'?url='+_pk_escape(document.location.href)
		+'&action_name='+_pk_escape(_pk_action_name)
		+'&idsite='+_pk_site
		+'&res='+screen.width+'x'+screen.height	+'&col='+screen.colorDepth
		+'&h='+_pk_da.getHours()+'&m='+_pk_da.getMinutes()+'&s='+_pk_da.getSeconds()
		+'&fla='+_pk_fla+'&dir='+_pk_dir+'&qt='+_pk_qt+'&realp='+_pk_rea+'&pdf='+_pk_pdf
		+'&wma='+_pk_wma+'&java='+_pk_jav+'&cookie='+_pk_cookie
		+'&title='+_pk_title
		+'&urlref='+_pk_escape(_pk_rtu)
		+_pk_custom_vars_str;
	return _pk_src;
}

function piwik_log( _pk_action_name, _pk_site, _pk_pkurl, _pk_custom_vars )
{
	if(_pk_called && (!_pk_action_name || _pk_action_name=="")) return;
	var _pk_src = _pk_getUrlLog(_pk_action_name, _pk_site, _pk_pkurl, _pk_custom_vars );
	document.writeln('<img src="'+_pk_src+'" alt="Piwik" style="border:0" />');
	if(!_pk_action_name || _pk_action_name=="") _pk_called=1;
	
  _pk_init_tracker(_pk_site, _pk_pkurl);
}

function _pk_add_event(elm, evType, fn, useCapture) 
{
	if (elm.addEventListener) { 
		elm.addEventListener(evType, fn, useCapture); 
		return true; 
	} else if (elm.attachEvent) { 
		var r = elm.attachEvent('on' + evType, fn); 
		return r; 
	} else {
		elm['on' + evType] = fn;
	}
}

var _pk_tracker_site, _pk_tracker_url;

function _pk_init_tracker(_pk_site, _pk_pkurl) 
{
	if( typeof(piwik_install_tracker) != "undefined" )
		_pk_install_tracker = piwik_install_tracker;
	if( typeof(piwik_tracker_pause) != "undefined" )
		_pk_tracker_pause = piwik_tracker_pause;
	if( typeof(piwik_download_extensions) != "undefined" )
		_pk_download_extensions = piwik_download_extensions;

	_pk_hosts_alias = ( typeof(piwik_hosts_alias) != "undefined" ? piwik_hosts_alias : new Array())
	_pk_hosts_alias.push(window.location.hostname);

	if( !_pk_install_tracker )
		return;

	_pk_tracker_site = _pk_site;
	_pk_tracker_url = _pk_pkurl;

	if (document.getElementsByTagName) {
		linksElements = document.getElementsByTagName('a')
		for (var i = 0; i < linksElements.length; i++) {
		if( linksElements[i].className != 'piwik_ignore' )
			_pk_add_event(linksElements[i], 'mousedown', _pk_click, false);
		}
	}
}

function _pk_dummy() { return true; }

function _pk_pause(_pk_time_msec) {
	var _pk_now = new Date();
	var _pk_expire = _pk_now.getTime() + _pk_time_msec;
	while(_pk_now.getTime() < _pk_expire)
		_pk_now = new Date();
}

// _pk_type only 'download' and 'link' types supported
function piwik_track(url, _pk_site, _pk_url, _pk_type) 
{
	var _pk_image = new Image();
	_pk_image.onLoad = function() { _pk_dummy(); };
	_pk_image.src = _pk_url + '?idsite=' + _pk_site + '&' + _pk_type + '=' + escape(url) + '&rand=' + Math.random() + '&redirect=0';
	_pk_pause(_pk_tracker_pause);
}

function _pk_is_site_hostname(_pk_hostname) {
	for(i = 0; i < _pk_hosts_alias.length; i++)
		if( _pk_hostname == _pk_hosts_alias[i] ) 
			return true;
	return false;
}

function _pk_click(e)
{
	var source;

	if (typeof e == 'undefined')
		var e = window.event;

	if (typeof e.target != 'undefined') 
		source = e.target;
	else if (typeof e.srcElement != 'undefined')
		source = e.srcElement;
	else return true;

	while( source.tagName != "A" )
		source = source.parentNode;

	if( typeof source.href == 'undefined' )
		return true;

	var _pk_download = new RegExp('\\.(' + _pk_download_extensions + ')$', 'i');
	var _pk_link_type;
	var _pk_not_site_hostname = !_pk_is_site_hostname(source.hostname);

	if( source.className == "piwik_download" )
		_pk_link_type = 'download';
	else if( source.className == "piwik_link" ) {
		_pk_link_type = 'link';
		_pk_not_site_hostname = 1;
	}
	else _pk_link_type = (_pk_download.test(source.href) ? 'download' : 'link');

	if( _pk_not_site_hostname || _pk_link_type == 'download' ) 
		piwik_track(source.href, _pk_tracker_site, _pk_tracker_url, _pk_link_type);

	return true;
}
