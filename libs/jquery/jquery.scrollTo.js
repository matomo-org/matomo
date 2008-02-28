;(function($){$.scrollTo=function(target,duration,settings){$($.browser.safari?'body':'html').scrollTo(target,duration,settings);};$.scrollTo.defaults={axis:'y',duration:1};$.fn.scrollTo=function(target,duration,settings){if(typeof duration=='object'){settings=duration;duration=0;}
settings=$.extend({},$.scrollTo.defaults,settings);if(!duration)
duration=settings.speed||settings.duration;settings.queue=settings.queue&&settings.axis.length==2;if(settings.queue)
duration=Math.ceil(duration/2);if(typeof settings.offset=='number')
settings.offset={left:settings.offset,top:settings.offset};return this.each(function(){var elem=this,$elem=$(elem),t=target,toff,attr={},win=$elem.is('html,body');switch(typeof t){case'number':case'string':if(/^([+-]=)?\d+(px)?$/.test(t)){t={top:t,left:t};break;}
t=$(t,this);case'object':if(t.is||t.style)
toff=(t=$(t)).offset();}
$.each(settings.axis.split(''),parse);animate(settings.onAfter);function parse(i,axis){var Pos=axis=='x'?'Left':'Top',pos=Pos.toLowerCase(),key='scroll'+Pos,act=elem[key];if(toff){attr[key]=toff[pos]+(win?0:act-$elem.offset()[pos]);if(settings.margin){attr[key]-=parseInt(t.css('margin'+Pos))||0;attr[key]-=parseInt(t.css('border'+Pos+'Width'))||0;}
if(settings.offset&&settings.offset[pos])
attr[key]+=settings.offset[pos];}else{attr[key]=t[pos];}
if(/^\d+$/.test(attr[key]))
attr[key]=attr[key]<=0?0:Math.min(attr[key],max(axis));if(!i&&settings.queue){if(act!=attr[key])
animate(settings.onAfterFirst);delete attr[key];}};function animate(callback){$elem.animate(attr,duration,settings.easing,function(){if(callback)
callback.call(this,$elem,attr,t);});};function max(axis){var el=win?$.browser.opera?document.body:document.documentElement:elem,Dim=axis=='x'?'Width':'Height';return el['scroll'+Dim]-el['client'+Dim];};});};})(jQuery);