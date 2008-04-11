/*
ua.js revision 0.200 2001-12-03

Contributor(s): Bob Clary, Netscape Communications, Copyright 2001

Netscape grants you a royalty free license to use, modify and 
distribute this software provided that this copyright notice 
appears on all copies.  This software is provided "AS IS," 
without a warranty of any kind.
*/

function xbDetectBrowser()
{
  var oldOnError = window.onerror;
  var element = null;

  window.onerror = null;
  
  // work around bug in xpcdom Mozilla 0.9.1
  window.saveNavigator = window.navigator;

  navigator.OS    = '';
  navigator.version  = parseFloat(navigator.appVersion);
  navigator.org    = '';
  navigator.family  = '';

  var platform;
  if (typeof(window.navigator.platform) != 'undefined')
  {
    platform = window.navigator.platform.toLowerCase();
    if (platform.indexOf('win') != -1)
      navigator.OS = 'win';
    else if (platform.indexOf('mac') != -1)
      navigator.OS = 'mac';
    else if (platform.indexOf('unix') != -1 || platform.indexOf('linux') != -1 || platform.indexOf('sun') != -1)
      navigator.OS = 'nix';
  }

  var i = 0;
  var ua = window.navigator.userAgent.toLowerCase();
  if (ua.indexOf('opera') != -1)
  {
    i = ua.indexOf('opera');
    navigator.family  = 'opera';
    navigator.org    = 'opera';
    navigator.version  = parseFloat('0' + ua.substr(i+6), 10);
  }
  else if ((i = ua.indexOf('msie')) != -1)
  {
    navigator.org    = 'microsoft';
    navigator.version  = parseFloat('0' + ua.substr(i+5), 10);
    
    if (navigator.version < 4)
      navigator.family = 'ie3';
    else
      navigator.family = 'ie4'
  }
  else if (ua.indexOf('gecko') != -1)
  {
    navigator.family = 'gecko';
    var rvStart = navigator.userAgent.indexOf('rv:') + 3;
    var rvEnd = navigator.userAgent.indexOf(')', rvStart);
    var rv = navigator.userAgent.substring(rvStart, rvEnd);
    var decIndex = rv.indexOf('.');
    if (decIndex != -1)
    {
      rv = rv.replace(/\./g, '')
      rv = rv.substring(0, decIndex-1) + '.' + rv.substr(decIndex)
    }
    navigator.version = parseFloat(rv);

    if (ua.indexOf('netscape') != -1)
      navigator.org = 'netscape';
    else if (ua.indexOf('compuserve') != -1)
      navigator.org = 'compuserve';
    else
      navigator.org = 'mozilla';
  }
  else if ((ua.indexOf('mozilla') !=-1) && (ua.indexOf('spoofer')==-1) && (ua.indexOf('compatible') == -1) && (ua.indexOf('opera')==-1)&& (ua.indexOf('webtv')==-1) && (ua.indexOf('hotjava')==-1))
  {
    var is_major = parseFloat(navigator.appVersion);
    
    if (is_major < 4)
      navigator.version = is_major;
    else
    {
      i = ua.lastIndexOf('/')
      navigator.version = parseFloat('0' + ua.substr(i+1), 10);
    }
    navigator.org = 'netscape';
    navigator.family = 'nn' + parseInt(navigator.appVersion);
  }
  else if ((i = ua.indexOf('aol')) != -1 )
  {
    // aol
    navigator.family  = 'aol';
    navigator.org    = 'aol';
    navigator.version  = parseFloat('0' + ua.substr(i+4), 10);
  }
  else if ((i = ua.indexOf('hotjava')) != -1 )
  {
    // hotjava
    navigator.family  = 'hotjava';
    navigator.org    = 'sun';
    navigator.version  = parseFloat(navigator.appVersion);
  }

  window.onerror = oldOnError;
}

xbDetectBrowser();
