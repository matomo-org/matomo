
(function($){var height=$.fn.height,width=$.fn.width;$.fn.extend({height:function(){if(!this[0])error();if(this[0]==window)
if($.browser.opera||($.browser.safari&&parseInt($.browser.version)>520))
return self.innerHeight-(($(document).height()>self.innerHeight)?getScrollbarWidth():0);else if($.browser.safari)
return self.innerHeight;else
return $.boxModel&&document.documentElement.clientHeight||document.body.clientHeight;if(this[0]==document)
return Math.max(($.boxModel&&document.documentElement.scrollHeight||document.body.scrollHeight),document.body.offsetHeight);return height.apply(this,arguments);},width:function(){if(!this[0])error();if(this[0]==window)
if($.browser.opera||($.browser.safari&&parseInt($.browser.version)>520))
return self.innerWidth-(($(document).width()>self.innerWidth)?getScrollbarWidth():0);else if($.browser.safari)
return self.innerWidth;else
return $.boxModel&&document.documentElement.clientWidth||document.body.clientWidth;if(this[0]==document)
if($.browser.mozilla){var scrollLeft=self.pageXOffset;self.scrollTo(99999999,self.pageYOffset);var scrollWidth=self.pageXOffset;self.scrollTo(scrollLeft,self.pageYOffset);return document.body.offsetWidth+scrollWidth;}
else
return Math.max((($.boxModel&&!$.browser.safari)&&document.documentElement.scrollWidth||document.body.scrollWidth),document.body.offsetWidth);return width.apply(this,arguments);},innerHeight:function(){if(!this[0])error();return this[0]==window||this[0]==document?this.height():this.is(':visible')?this[0].offsetHeight-num(this,'borderTopWidth')-num(this,'borderBottomWidth'):this.height()+num(this,'paddingTop')+num(this,'paddingBottom');},innerWidth:function(){if(!this[0])error();return this[0]==window||this[0]==document?this.width():this.is(':visible')?this[0].offsetWidth-num(this,'borderLeftWidth')-num(this,'borderRightWidth'):this.width()+num(this,'paddingLeft')+num(this,'paddingRight');},outerHeight:function(options){if(!this[0])error();options=$.extend({margin:false},options||{});return this[0]==window||this[0]==document?this.height():this.is(':visible')?this[0].offsetHeight+(options.margin?(num(this,'marginTop')+num(this,'marginBottom')):0):this.height()
+num(this,'borderTopWidth')+num(this,'borderBottomWidth')
+num(this,'paddingTop')+num(this,'paddingBottom')
+(options.margin?(num(this,'marginTop')+num(this,'marginBottom')):0);},outerWidth:function(options){if(!this[0])error();options=$.extend({margin:false},options||{});return this[0]==window||this[0]==document?this.width():this.is(':visible')?this[0].offsetWidth+(options.margin?(num(this,'marginLeft')+num(this,'marginRight')):0):this.width()
+num(this,'borderLeftWidth')+num(this,'borderRightWidth')
+num(this,'paddingLeft')+num(this,'paddingRight')
+(options.margin?(num(this,'marginLeft')+num(this,'marginRight')):0);},scrollLeft:function(val){if(!this[0])error();if(val!=undefined)
return this.each(function(){if(this==window||this==document)
window.scrollTo(val,$(window).scrollTop());else
this.scrollLeft=val;});if(this[0]==window||this[0]==document)
return self.pageXOffset||$.boxModel&&document.documentElement.scrollLeft||document.body.scrollLeft;return this[0].scrollLeft;},scrollTop:function(val){if(!this[0])error();if(val!=undefined)
return this.each(function(){if(this==window||this==document)
window.scrollTo($(window).scrollLeft(),val);else
this.scrollTop=val;});if(this[0]==window||this[0]==document)
return self.pageYOffset||$.boxModel&&document.documentElement.scrollTop||document.body.scrollTop;return this[0].scrollTop;},position:function(returnObject){return this.offset({margin:false,scroll:false,relativeTo:this.offsetParent()},returnObject);},offset:function(options,returnObject){if(!this[0])error();var x=0,y=0,sl=0,st=0,elem=this[0],parent=this[0],op,parPos,elemPos=$.css(elem,'position'),mo=$.browser.mozilla,ie=$.browser.msie,oa=$.browser.opera,sf=$.browser.safari,sf3=$.browser.safari&&parseInt($.browser.version)>520,absparent=false,relparent=false,options=$.extend({margin:true,border:false,padding:false,scroll:true,lite:false,relativeTo:document.body},options||{});if(options.lite)return this.offsetLite(options,returnObject);if(options.relativeTo.jquery)options.relativeTo=options.relativeTo[0];if(elem.tagName=='BODY'){x=elem.offsetLeft;y=elem.offsetTop;if(mo){x+=num(elem,'marginLeft')+(num(elem,'borderLeftWidth')*2);y+=num(elem,'marginTop')+(num(elem,'borderTopWidth')*2);}else
if(oa){x+=num(elem,'marginLeft');y+=num(elem,'marginTop');}else
if((ie&&jQuery.boxModel)){x+=num(elem,'borderLeftWidth');y+=num(elem,'borderTopWidth');}else
if(sf3){x+=num(elem,'marginLeft')+num(elem,'borderLeftWidth');y+=num(elem,'marginTop')+num(elem,'borderTopWidth');}}else{do{parPos=$.css(parent,'position');x+=parent.offsetLeft;y+=parent.offsetTop;if((mo&&!parent.tagName.match(/^t[d|h]$/i))||ie||sf3){x+=num(parent,'borderLeftWidth');y+=num(parent,'borderTopWidth');if(mo&&parPos=='absolute')absparent=true;if(ie&&parPos=='relative')relparent=true;}
op=parent.offsetParent||document.body;if(options.scroll||mo){do{if(options.scroll){sl+=parent.scrollLeft;st+=parent.scrollTop;}
if(oa&&($.css(parent,'display')||'').match(/table-row|inline/)){sl=sl-((parent.scrollLeft==parent.offsetLeft)?parent.scrollLeft:0);st=st-((parent.scrollTop==parent.offsetTop)?parent.scrollTop:0);}
if(mo&&parent!=elem&&$.css(parent,'overflow')!='visible'){x+=num(parent,'borderLeftWidth');y+=num(parent,'borderTopWidth');}
parent=parent.parentNode;}while(parent!=op);}
parent=op;if(parent==options.relativeTo&&!(parent.tagName=='BODY'||parent.tagName=='HTML')){if(mo&&parent!=elem&&$.css(parent,'overflow')!='visible'){x+=num(parent,'borderLeftWidth');y+=num(parent,'borderTopWidth');}
if(((sf&&!sf3)||oa)&&parPos!='static'){x-=num(op,'borderLeftWidth');y-=num(op,'borderTopWidth');}
break;}
if(parent.tagName=='BODY'||parent.tagName=='HTML'){if(((sf&&!sf3)||(ie&&$.boxModel))&&elemPos!='absolute'&&elemPos!='fixed'){x+=num(parent,'marginLeft');y+=num(parent,'marginTop');}
if(sf3||(mo&&!absparent&&elemPos!='fixed')||(ie&&elemPos=='static'&&!relparent)){x+=num(parent,'borderLeftWidth');y+=num(parent,'borderTopWidth');}
break;}}while(parent);}
var returnValue=handleOffsetReturn(elem,options,x,y,sl,st);if(returnObject){$.extend(returnObject,returnValue);return this;}
else{return returnValue;}},offsetLite:function(options,returnObject){if(!this[0])error();var x=0,y=0,sl=0,st=0,parent=this[0],offsetParent,options=$.extend({margin:true,border:false,padding:false,scroll:true,relativeTo:document.body},options||{});if(options.relativeTo.jquery)options.relativeTo=options.relativeTo[0];do{x+=parent.offsetLeft;y+=parent.offsetTop;offsetParent=parent.offsetParent||document.body;if(options.scroll){do{sl+=parent.scrollLeft;st+=parent.scrollTop;parent=parent.parentNode;}while(parent!=offsetParent);}
parent=offsetParent;}while(parent&&parent.tagName!='BODY'&&parent.tagName!='HTML'&&parent!=options.relativeTo);var returnValue=handleOffsetReturn(this[0],options,x,y,sl,st);if(returnObject){$.extend(returnObject,returnValue);return this;}
else{return returnValue;}},offsetParent:function(){if(!this[0])error();var offsetParent=this[0].offsetParent;while(offsetParent&&(offsetParent.tagName!='BODY'&&$.css(offsetParent,'position')=='static'))
offsetParent=offsetParent.offsetParent;return $(offsetParent);}});var error=function(){throw"Dimensions: jQuery collection is empty";};var num=function(el,prop){return parseInt($.css(el.jquery?el[0]:el,prop))||0;};var handleOffsetReturn=function(elem,options,x,y,sl,st){if(!options.margin){x-=num(elem,'marginLeft');y-=num(elem,'marginTop');}
if(options.border&&(($.browser.safari&&parseInt($.browser.version)<520)||$.browser.opera)){x+=num(elem,'borderLeftWidth');y+=num(elem,'borderTopWidth');}else if(!options.border&&!(($.browser.safari&&parseInt($.browser.version)<520)||$.browser.opera)){x-=num(elem,'borderLeftWidth');y-=num(elem,'borderTopWidth');}
if(options.padding){x+=num(elem,'paddingLeft');y+=num(elem,'paddingTop');}
if(options.scroll&&(!$.browser.opera||elem.offsetLeft!=elem.scrollLeft&&elem.offsetTop!=elem.scrollLeft)){sl-=elem.scrollLeft;st-=elem.scrollTop;}
return options.scroll?{top:y-st,left:x-sl,scrollTop:st,scrollLeft:sl}:{top:y,left:x};};var scrollbarWidth=0;var getScrollbarWidth=function(){if(!scrollbarWidth){var testEl=$('<div>').css({width:100,height:100,overflow:'auto',position:'absolute',top:-1000,left:-1000}).appendTo('body');scrollbarWidth=100-testEl.append('<div>').find('div').css({width:'100%',height:200}).width();testEl.remove();}
return scrollbarWidth;};})(jQuery);