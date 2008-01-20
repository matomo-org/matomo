document.write('<scr'+'ipt language="javascript" src="libs/jquery/jquery.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="themes/default/common.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="libs/jquery/jquery.dimensions.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="libs/jquery/tooltip/jquery.tooltip.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="libs/jquery/truncate/jquery.truncate.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="libs/swfobject/swfobject.js"><\/scr'+'ipt>');
document.write('<scr'+'ipt language="javascript" src="plugins/Home/templates/datatable.js"><\/scr'+'ipt>');
document.write('<link rel="stylesheet" href="libs/jquery/tooltip/jquery.tooltip.css">');
document.write('<link rel="stylesheet" href="plugins/Home/templates/datatable.css">');

var content = '{$content|escape:'javascript'}';
document.write(content);