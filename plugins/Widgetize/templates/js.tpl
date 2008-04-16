{loadJavascriptTranslations noHtml=1 modules='Home'}

document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/jquery.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}themes/default/common.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/jquery.dimensions.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/tooltip/jquery.tooltip.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/truncate/jquery.truncate.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/swfobject/swfobject.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}plugins/Home/templates/datatable.js"><\/scr'+'ipt>');

document.write('<scr'+'ipt language="javascript" src="{$piwikUrl}libs/jquery/ui.mouse.js"><\/scr'+'ipt>');

document.write('<link rel="stylesheet" href="{$piwikUrl}plugins/Home/templates/datatable.css">');

var content = '{$content|escape:'javascript'}';
document.write(content);