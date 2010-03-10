{loadJavascriptTranslations disableOutputScriptTag=1 plugins='CoreHome'}

document.write('<link rel="stylesheet" href="{$piwikUrl}themes/default/common.css">');
document.write('<link rel="stylesheet" href="{$piwikUrl}plugins/CoreHome/templates/styles.css">');
document.write('<link rel="stylesheet" href="{$piwikUrl}plugins/CoreHome/templates/datatable.css">');
document.write('<link rel="stylesheet" href="{$piwikUrl}plugins/CoreHome/templates/cloud.css">');

document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/swfobject/swfobject.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/jquery.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/jquery-ui.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/tooltip/jquery.tooltip.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/truncate/jquery.truncate.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/javascript/sprintf.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}themes/default/common.js"></scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}plugins/CoreHome/templates/datatable.js"></scr'+'ipt>');

var content = '{$content|escape:'javascript'}';
document.write('<scr'+'ipt language="javascript">document.write(content)</scr'+'ipt>');

