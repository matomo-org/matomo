/*
 * Ext JS Library 2.3.0
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */


Ext={version:'2.3.0'};window["undefined"]=window["undefined"];Ext.apply=function(o,c,defaults){if(defaults){Ext.apply(o,defaults);}
if(o&&c&&typeof c=='object'){for(var p in c){o[p]=c[p];}}
return o;};(function(){var idSeed=0;var ua=navigator.userAgent.toLowerCase(),check=function(r){return r.test(ua);},isStrict=document.compatMode=="CSS1Compat",isOpera=check(/opera/),isChrome=check(/chrome/),isWebKit=check(/webkit/),isSafari=!isChrome&&check(/safari/),isSafari2=isSafari&&check(/applewebkit\/4/),isSafari3=isSafari&&check(/version\/3/),isSafari4=isSafari&&check(/version\/4/),isIE=!isOpera&&check(/msie/),isIE7=isIE&&check(/msie 7/),isIE8=isIE&&check(/msie 8/),isIE6=isIE&&!isIE7&&!isIE8,isGecko=!isWebKit&&check(/gecko/),isGecko2=isGecko&&check(/rv:1\.8/),isGecko3=isGecko&&check(/rv:1\.9/),isBorderBox=isIE&&!isStrict,isWindows=check(/windows|win32/),isMac=check(/macintosh|mac os x/),isAir=check(/adobeair/),isLinux=check(/linux/),isSecure=/^https/i.test(window.location.protocol);if(isIE6){try{document.execCommand("BackgroundImageCache",false,true);}catch(e){}}
Ext.apply(Ext,{isStrict:isStrict,isSecure:isSecure,isReady:false,enableGarbageCollector:true,enableListenerCollection:false,SSL_SECURE_URL:"javascript:false",BLANK_IMAGE_URL:"http:/"+"/extjs.com/s.gif",emptyFn:function(){},applyIf:function(o,c){if(o&&c){for(var p in c){if(typeof o[p]=="undefined"){o[p]=c[p];}}}
return o;},addBehaviors:function(o){if(!Ext.isReady){Ext.onReady(function(){Ext.addBehaviors(o);});return;}
var cache={};for(var b in o){var parts=b.split('@');if(parts[1]){var s=parts[0];if(!cache[s]){cache[s]=Ext.select(s);}
cache[s].on(parts[1],o[b]);}}
cache=null;},id:function(el,prefix){prefix=prefix||"ext-gen";el=Ext.getDom(el);var id=prefix+(++idSeed);return el?(el.id?el.id:(el.id=id)):id;},extend:function(){var io=function(o){for(var m in o){this[m]=o[m];}};var oc=Object.prototype.constructor;return function(sb,sp,overrides){if(typeof sp=='object'){overrides=sp;sp=sb;sb=overrides.constructor!=oc?overrides.constructor:function(){sp.apply(this,arguments);};}
var F=function(){},sbp,spp=sp.prototype;F.prototype=spp;sbp=sb.prototype=new F();sbp.constructor=sb;sb.superclass=spp;if(spp.constructor==oc){spp.constructor=sp;}
sb.override=function(o){Ext.override(sb,o);};sbp.override=io;Ext.override(sb,overrides);sb.extend=function(o){Ext.extend(sb,o);};return sb;};}(),override:function(origclass,overrides){if(overrides){var p=origclass.prototype;for(var method in overrides){p[method]=overrides[method];}
if(Ext.isIE&&overrides.toString!=origclass.toString){p.toString=overrides.toString;}}},namespace:function(){var a=arguments,o=null,i,j,d,rt;for(i=0;i<a.length;++i){d=a[i].split(".");rt=d[0];eval('if (typeof '+rt+' == "undefined"){'+rt+' = {};} o = '+rt+';');for(j=1;j<d.length;++j){o[d[j]]=o[d[j]]||{};o=o[d[j]];}}},urlEncode:function(o){if(!o){return"";}
var buf=[];for(var key in o){var ov=o[key],k=encodeURIComponent(key);var type=typeof ov;if(type=='undefined'){buf.push(k,"=&");}else if(type!="function"&&type!="object"){buf.push(k,"=",encodeURIComponent(ov),"&");}else if(Ext.isDate(ov)){var s=Ext.encode(ov).replace(/"/g,'');buf.push(k,"=",s,"&");}else if(Ext.isArray(ov)){if(ov.length){for(var i=0,len=ov.length;i<len;i++){buf.push(k,"=",encodeURIComponent(ov[i]===undefined?'':ov[i]),"&");}}else{buf.push(k,"=&");}}}
buf.pop();return buf.join("");},urlDecode:function(string,overwrite){if(!string||!string.length){return{};}
var obj={};var pairs=string.split('&');var pair,name,value;for(var i=0,len=pairs.length;i<len;i++){pair=pairs[i].split('=');name=decodeURIComponent(pair[0]);value=decodeURIComponent(pair[1]);if(overwrite!==true){if(typeof obj[name]=="undefined"){obj[name]=value;}else if(typeof obj[name]=="string"){obj[name]=[obj[name]];obj[name].push(value);}else{obj[name].push(value);}}else{obj[name]=value;}}
return obj;},each:function(array,fn,scope){if(typeof array.length=="undefined"||typeof array=="string"){array=[array];}
for(var i=0,len=array.length;i<len;i++){if(fn.call(scope||array[i],array[i],i,array)===false){return i;};}},combine:function(){var as=arguments,l=as.length,r=[];for(var i=0;i<l;i++){var a=as[i];if(Ext.isArray(a)){r=r.concat(a);}else if(a.length!==undefined&&!a.substr){r=r.concat(Array.prototype.slice.call(a,0));}else{r.push(a);}}
return r;},escapeRe:function(s){return s.replace(/([.*+?^${}()|[\]\/\\])/g,"\\$1");},callback:function(cb,scope,args,delay){if(typeof cb=="function"){if(delay){cb.defer(delay,scope,args||[]);}else{cb.apply(scope,args||[]);}}},getDom:function(el){if(!el||!document){return null;}
return el.dom?el.dom:(typeof el=='string'?document.getElementById(el):el);},getDoc:function(){return Ext.get(document);},getBody:function(){return Ext.get(document.body||document.documentElement);},getCmp:function(id){return Ext.ComponentMgr.get(id);},num:function(v,defaultValue){v=Number(v==null||typeof v=='boolean'?NaN:v);return isNaN(v)?defaultValue:v;},destroy:function(){for(var i=0,a=arguments,len=a.length;i<len;i++){var as=a[i];if(as){if(typeof as.destroy=='function'){as.destroy();}
else if(as.dom){as.removeAllListeners();as.remove();}}}},removeNode:isIE?function(){var d;return function(n){if(n&&n.tagName!='BODY'){d=d||document.createElement('div');d.appendChild(n);d.innerHTML='';}}}():function(n){if(n&&n.parentNode&&n.tagName!='BODY'){n.parentNode.removeChild(n);}},type:function(o){if(o===undefined||o===null){return false;}
if(o.htmlElement){return'element';}
var t=typeof o;if(t=='object'&&o.nodeName){switch(o.nodeType){case 1:return'element';case 3:return(/\S/).test(o.nodeValue)?'textnode':'whitespace';}}
if(t=='object'||t=='function'){switch(o.constructor){case Array:return'array';case RegExp:return'regexp';case Date:return'date';}
if(typeof o.length=='number'&&typeof o.item=='function'){return'nodelist';}}
return t;},isEmpty:function(v,allowBlank){return v===null||v===undefined||(!allowBlank?v==='':false);},value:function(v,defaultValue,allowBlank){return Ext.isEmpty(v,allowBlank)?defaultValue:v;},isArray:function(v){return v&&typeof v.length=='number'&&typeof v.splice=='function';},isDate:function(v){return v&&typeof v.getFullYear=='function';},isOpera:isOpera,isWebKit:isWebKit,isChrome:isChrome,isSafari:isSafari,isSafari4:isSafari4,isSafari3:isSafari3,isSafari2:isSafari2,isIE:isIE,isIE6:isIE6,isIE7:isIE7,isIE8:isIE8,isGecko:isGecko,isGecko2:isGecko2,isGecko3:isGecko3,isBorderBox:isBorderBox,isLinux:isLinux,isWindows:isWindows,isMac:isMac,isAir:isAir,useShims:((isIE&&!(isIE7||isIE8))||(isMac&&isGecko&&!isGecko3))});Ext.ns=Ext.namespace;})();Ext.ns("Ext","Ext.util","Ext.grid","Ext.dd","Ext.tree","Ext.data","Ext.form","Ext.menu","Ext.state","Ext.lib","Ext.layout","Ext.app","Ext.ux");Ext.apply(Function.prototype,{createCallback:function(){var args=arguments;var method=this;return function(){return method.apply(window,args);};},createDelegate:function(obj,args,appendArgs){var method=this;return function(){var callArgs=args||arguments;if(appendArgs===true){callArgs=Array.prototype.slice.call(arguments,0);callArgs=callArgs.concat(args);}else if(typeof appendArgs=="number"){callArgs=Array.prototype.slice.call(arguments,0);var applyArgs=[appendArgs,0].concat(args);Array.prototype.splice.apply(callArgs,applyArgs);}
return method.apply(obj||window,callArgs);};},defer:function(millis,obj,args,appendArgs){var fn=this.createDelegate(obj,args,appendArgs);if(millis){return setTimeout(fn,millis);}
fn();return 0;},createSequence:function(fcn,scope){if(typeof fcn!="function"){return this;}
var method=this;return function(){var retval=method.apply(this||window,arguments);fcn.apply(scope||this||window,arguments);return retval;};},createInterceptor:function(fcn,scope){if(typeof fcn!="function"){return this;}
var method=this;return function(){fcn.target=this;fcn.method=method;if(fcn.apply(scope||this||window,arguments)===false){return;}
return method.apply(this||window,arguments);};}});Ext.applyIf(String,{escape:function(string){return string.replace(/('|\\)/g,"\\$1");},leftPad:function(val,size,ch){var result=new String(val);if(!ch){ch=" ";}
while(result.length<size){result=ch+result;}
return result.toString();},format:function(format){var args=Array.prototype.slice.call(arguments,1);return format.replace(/\{(\d+)\}/g,function(m,i){return args[i];});}});String.prototype.toggle=function(value,other){return this==value?other:value;};String.prototype.trim=function(){var re=/^\s+|\s+$/g;return function(){return this.replace(re,"");};}();Ext.applyIf(Number.prototype,{constrain:function(min,max){return Math.min(Math.max(this,min),max);}});Ext.applyIf(Array.prototype,{indexOf:function(o){for(var i=0,len=this.length;i<len;i++){if(this[i]==o)return i;}
return-1;},remove:function(o){var index=this.indexOf(o);if(index!=-1){this.splice(index,1);}
return this;}});Date.prototype.getElapsed=function(date){return Math.abs((date||new Date()).getTime()-this.getTime());};

(function(){var libFlyweight;Ext.lib.Dom={getViewWidth:function(full){return full?this.getDocumentWidth():this.getViewportWidth();},getViewHeight:function(full){return full?this.getDocumentHeight():this.getViewportHeight();},getDocumentHeight:function(){var scrollHeight=(document.compatMode!="CSS1Compat")?document.body.scrollHeight:document.documentElement.scrollHeight;return Math.max(scrollHeight,this.getViewportHeight());},getDocumentWidth:function(){var scrollWidth=(document.compatMode!="CSS1Compat")?document.body.scrollWidth:document.documentElement.scrollWidth;return Math.max(scrollWidth,this.getViewportWidth());},getViewportHeight:function(){if(Ext.isIE){return Ext.isStrict?document.documentElement.clientHeight:document.body.clientHeight;}else{return self.innerHeight;}},getViewportWidth:function(){if(Ext.isIE){return Ext.isStrict?document.documentElement.clientWidth:document.body.clientWidth;}else{return self.innerWidth;}},isAncestor:function(p,c){p=Ext.getDom(p);c=Ext.getDom(c);if(!p||!c){return false;}
if(p.contains&&!Ext.isWebKit){return p.contains(c);}else if(p.compareDocumentPosition){return!!(p.compareDocumentPosition(c)&16);}else{var parent=c.parentNode;while(parent){if(parent==p){return true;}
else if(!parent.tagName||parent.tagName.toUpperCase()=="HTML"){return false;}
parent=parent.parentNode;}
return false;}},getRegion:function(el){return Ext.lib.Region.getRegion(el);},getY:function(el){return this.getXY(el)[1];},getX:function(el){return this.getXY(el)[0];},getXY:function(el){var p,pe,b,scroll,bd=(document.body||document.documentElement);el=Ext.getDom(el);if(el==bd){return[0,0];}
if(el.getBoundingClientRect){b=el.getBoundingClientRect();scroll=fly(document).getScroll();return[b.left+scroll.left,b.top+scroll.top];}
var x=0,y=0;p=el;var hasAbsolute=fly(el).getStyle("position")=="absolute";while(p){x+=p.offsetLeft;y+=p.offsetTop;if(!hasAbsolute&&fly(p).getStyle("position")=="absolute"){hasAbsolute=true;}
if(Ext.isGecko){pe=fly(p);var bt=parseInt(pe.getStyle("borderTopWidth"),10)||0;var bl=parseInt(pe.getStyle("borderLeftWidth"),10)||0;x+=bl;y+=bt;if(p!=el&&pe.getStyle('overflow')!='visible'){x+=bl;y+=bt;}}
p=p.offsetParent;}
if(Ext.isWebKit&&hasAbsolute){x-=bd.offsetLeft;y-=bd.offsetTop;}
if(Ext.isGecko&&!hasAbsolute){var dbd=fly(bd);x+=parseInt(dbd.getStyle("borderLeftWidth"),10)||0;y+=parseInt(dbd.getStyle("borderTopWidth"),10)||0;}
p=el.parentNode;while(p&&p!=bd){if(!Ext.isOpera||(p.tagName!='TR'&&fly(p).getStyle("display")!="inline")){x-=p.scrollLeft;y-=p.scrollTop;}
p=p.parentNode;}
return[x,y];},setXY:function(el,xy){el=Ext.fly(el,'_setXY');el.position();var pts=el.translatePoints(xy);if(xy[0]!==false){el.dom.style.left=pts.left+"px";}
if(xy[1]!==false){el.dom.style.top=pts.top+"px";}},setX:function(el,x){this.setXY(el,[x,false]);},setY:function(el,y){this.setXY(el,[false,y]);}};Ext.lib.Event=function(){var loadComplete=false;var listeners=[];var unloadListeners=[];var retryCount=0;var onAvailStack=[];var counter=0;var lastError=null;return{POLL_RETRYS:200,POLL_INTERVAL:20,EL:0,TYPE:1,FN:2,WFN:3,OBJ:3,ADJ_SCOPE:4,_interval:null,startInterval:function(){if(!this._interval){var self=this;var callback=function(){self._tryPreloadAttach();};this._interval=setInterval(callback,this.POLL_INTERVAL);}},onAvailable:function(p_id,p_fn,p_obj,p_override){onAvailStack.push({id:p_id,fn:p_fn,obj:p_obj,override:p_override,checkReady:false});retryCount=this.POLL_RETRYS;this.startInterval();},addListener:function(el,eventName,fn){el=Ext.getDom(el);if(!el||!fn){return false;}
if("unload"==eventName){unloadListeners[unloadListeners.length]=[el,eventName,fn];return true;}
var wrappedFn=function(e){return typeof Ext!='undefined'?fn(Ext.lib.Event.getEvent(e)):false;};var li=[el,eventName,fn,wrappedFn];var index=listeners.length;listeners[index]=li;this.doAdd(el,eventName,wrappedFn,false);return true;},removeListener:function(el,eventName,fn){var i,len;el=Ext.getDom(el);if(!fn){return this.purgeElement(el,false,eventName);}
if("unload"==eventName){for(i=0,len=unloadListeners.length;i<len;i++){var li=unloadListeners[i];if(li&&li[0]==el&&li[1]==eventName&&li[2]==fn){unloadListeners.splice(i,1);return true;}}
return false;}
var cacheItem=null;var index=arguments[3];if("undefined"==typeof index){index=this._getCacheIndex(el,eventName,fn);}
if(index>=0){cacheItem=listeners[index];}
if(!el||!cacheItem){return false;}
this.doRemove(el,eventName,cacheItem[this.WFN],false);delete listeners[index][this.WFN];delete listeners[index][this.FN];listeners.splice(index,1);return true;},getTarget:function(ev,resolveTextNode){ev=ev.browserEvent||ev;var t=ev.target||ev.srcElement;return this.resolveTextNode(t);},resolveTextNode:function(node){if(Ext.isWebKit&&node&&3==node.nodeType){return node.parentNode;}else{return node;}},getPageX:function(ev){ev=ev.browserEvent||ev;var x=ev.pageX;if(!x&&0!==x){x=ev.clientX||0;if(Ext.isIE){x+=this.getScroll()[1];}}
return x;},getPageY:function(ev){ev=ev.browserEvent||ev;var y=ev.pageY;if(!y&&0!==y){y=ev.clientY||0;if(Ext.isIE){y+=this.getScroll()[0];}}
return y;},getXY:function(ev){ev=ev.browserEvent||ev;return[this.getPageX(ev),this.getPageY(ev)];},getRelatedTarget:function(ev){ev=ev.browserEvent||ev;var t=ev.relatedTarget;if(!t){if(ev.type=="mouseout"){t=ev.toElement;}else if(ev.type=="mouseover"){t=ev.fromElement;}}
return this.resolveTextNode(t);},getTime:function(ev){ev=ev.browserEvent||ev;if(!ev.time){var t=new Date().getTime();try{ev.time=t;}catch(ex){this.lastError=ex;return t;}}
return ev.time;},stopEvent:function(ev){this.stopPropagation(ev);this.preventDefault(ev);},stopPropagation:function(ev){ev=ev.browserEvent||ev;if(ev.stopPropagation){ev.stopPropagation();}else{ev.cancelBubble=true;}},preventDefault:function(ev){ev=ev.browserEvent||ev;if(ev.preventDefault){ev.preventDefault();}else{ev.returnValue=false;}},getEvent:function(e){var ev=e||window.event;if(!ev){var c=this.getEvent.caller;while(c){ev=c.arguments[0];if(ev&&Event==ev.constructor){break;}
c=c.caller;}}
return ev;},getCharCode:function(ev){ev=ev.browserEvent||ev;return ev.charCode||ev.keyCode||0;},_getCacheIndex:function(el,eventName,fn){for(var i=0,len=listeners.length;i<len;++i){var li=listeners[i];if(li&&li[this.FN]==fn&&li[this.EL]==el&&li[this.TYPE]==eventName){return i;}}
return-1;},elCache:{},getEl:function(id){return document.getElementById(id);},clearCache:function(){},_load:function(e){loadComplete=true;var EU=Ext.lib.Event;if(Ext.isIE){EU.doRemove(window,"load",EU._load);}},_tryPreloadAttach:function(){if(this.locked){return false;}
this.locked=true;var tryAgain=!loadComplete;if(!tryAgain){tryAgain=(retryCount>0);}
var notAvail=[];for(var i=0,len=onAvailStack.length;i<len;++i){var item=onAvailStack[i];if(item){var el=this.getEl(item.id);if(el){if(!item.checkReady||loadComplete||el.nextSibling||(document&&document.body)){var scope=el;if(item.override){if(item.override===true){scope=item.obj;}else{scope=item.override;}}
item.fn.call(scope,item.obj);onAvailStack[i]=null;}}else{notAvail.push(item);}}}
retryCount=(notAvail.length===0)?0:retryCount-1;if(tryAgain){this.startInterval();}else{clearInterval(this._interval);this._interval=null;}
this.locked=false;return true;},purgeElement:function(el,recurse,eventName){var elListeners=this.getListeners(el,eventName);if(elListeners){for(var i=0,len=elListeners.length;i<len;++i){var l=elListeners[i];this.removeListener(el,l.type,l.fn);}}
if(recurse&&el&&el.childNodes){for(i=0,len=el.childNodes.length;i<len;++i){this.purgeElement(el.childNodes[i],recurse,eventName);}}},getListeners:function(el,eventName){var results=[],searchLists;if(!eventName){searchLists=[listeners,unloadListeners];}else if(eventName=="unload"){searchLists=[unloadListeners];}else{searchLists=[listeners];}
for(var j=0;j<searchLists.length;++j){var searchList=searchLists[j];if(searchList&&searchList.length>0){for(var i=0,len=searchList.length;i<len;++i){var l=searchList[i];if(l&&l[this.EL]===el&&(!eventName||eventName===l[this.TYPE])){results.push({type:l[this.TYPE],fn:l[this.FN],obj:l[this.OBJ],adjust:l[this.ADJ_SCOPE],index:i});}}}}
return(results.length)?results:null;},_unload:function(e){var EU=Ext.lib.Event,i,j,l,len,index;for(i=0,len=unloadListeners.length;i<len;++i){l=unloadListeners[i];if(l){var scope=window;if(l[EU.ADJ_SCOPE]){if(l[EU.ADJ_SCOPE]===true){scope=l[EU.OBJ];}else{scope=l[EU.ADJ_SCOPE];}}
l[EU.FN].call(scope,EU.getEvent(e),l[EU.OBJ]);unloadListeners[i]=null;l=null;scope=null;}}
unloadListeners=null;if(listeners&&listeners.length>0){j=listeners.length;while(j){index=j-1;l=listeners[index];if(l){EU.removeListener(l[EU.EL],l[EU.TYPE],l[EU.FN],index);}
j=j-1;}
l=null;EU.clearCache();}
EU.doRemove(window,"unload",EU._unload);},getScroll:function(){var dd=document.documentElement,db=document.body;if(dd&&(dd.scrollTop||dd.scrollLeft)){return[dd.scrollTop,dd.scrollLeft];}else if(db){return[db.scrollTop,db.scrollLeft];}else{return[0,0];}},doAdd:function(){if(window.addEventListener){return function(el,eventName,fn,capture){el.addEventListener(eventName,fn,(capture));};}else if(window.attachEvent){return function(el,eventName,fn,capture){el.attachEvent("on"+eventName,fn);};}else{return function(){};}}(),doRemove:function(){if(window.removeEventListener){return function(el,eventName,fn,capture){el.removeEventListener(eventName,fn,(capture));};}else if(window.detachEvent){return function(el,eventName,fn){el.detachEvent("on"+eventName,fn);};}else{return function(){};}}()};}();var E=Ext.lib.Event;E.on=E.addListener;E.un=E.removeListener;if(document&&document.body){E._load();}else{E.doAdd(window,"load",E._load);}
E.doAdd(window,"unload",E._unload);E._tryPreloadAttach();Ext.lib.Ajax={request:function(method,uri,cb,data,options){if(options){var hs=options.headers;if(hs){for(var h in hs){if(hs.hasOwnProperty(h)){this.initHeader(h,hs[h],false);}}}
if(options.xmlData){if(!hs||!hs['Content-Type']){this.initHeader('Content-Type','text/xml',false);}
method=(method?method:(options.method?options.method:'POST'));data=options.xmlData;}else if(options.jsonData){if(!hs||!hs['Content-Type']){this.initHeader('Content-Type','application/json',false);}
method=(method?method:(options.method?options.method:'POST'));data=typeof options.jsonData=='object'?Ext.encode(options.jsonData):options.jsonData;}}
return this.asyncRequest(method,uri,cb,data);},serializeForm:function(form){if(typeof form=='string'){form=(document.getElementById(form)||document.forms[form]);}
var el,name,val,disabled,data='',hasSubmit=false;for(var i=0;i<form.elements.length;i++){el=form.elements[i];disabled=form.elements[i].disabled;name=form.elements[i].name;val=form.elements[i].value;if(!disabled&&name){switch(el.type)
{case'select-one':case'select-multiple':for(var j=0;j<el.options.length;j++){if(el.options[j].selected){var opt=el.options[j],sel=(opt.hasAttribute?opt.hasAttribute('value'):opt.getAttributeNode('value').specified)?opt.value:opt.text;data+=encodeURIComponent(name)+'='+encodeURIComponent(sel)+'&';}}
break;case'radio':case'checkbox':if(el.checked){data+=encodeURIComponent(name)+'='+encodeURIComponent(val)+'&';}
break;case'file':case undefined:case'reset':case'button':break;case'submit':if(hasSubmit==false){data+=encodeURIComponent(name)+'='+encodeURIComponent(val)+'&';hasSubmit=true;}
break;default:data+=encodeURIComponent(name)+'='+encodeURIComponent(val)+'&';break;}}}
data=data.substr(0,data.length-1);return data;},headers:{},hasHeaders:false,useDefaultHeader:true,defaultPostHeader:'application/x-www-form-urlencoded; charset=UTF-8',useDefaultXhrHeader:true,defaultXhrHeader:'XMLHttpRequest',hasDefaultHeaders:true,defaultHeaders:{},poll:{},timeout:{},pollInterval:50,transactionId:0,setProgId:function(id)
{this.activeX.unshift(id);},setDefaultPostHeader:function(b)
{this.useDefaultHeader=b;},setDefaultXhrHeader:function(b)
{this.useDefaultXhrHeader=b;},setPollingInterval:function(i)
{if(typeof i=='number'&&isFinite(i)){this.pollInterval=i;}},createXhrObject:function(transactionId)
{var obj,http;try
{http=new XMLHttpRequest();obj={conn:http,tId:transactionId};}
catch(e)
{for(var i=0;i<this.activeX.length;++i){try
{http=new ActiveXObject(this.activeX[i]);obj={conn:http,tId:transactionId};break;}
catch(e){}}}
finally
{return obj;}},getConnectionObject:function()
{var o;var tId=this.transactionId;try
{o=this.createXhrObject(tId);if(o){this.transactionId++;}}
catch(e){}
finally
{return o;}},asyncRequest:function(method,uri,callback,postData)
{var o=this.getConnectionObject();if(!o){return null;}
else{o.conn.open(method,uri,true);if(this.useDefaultXhrHeader){if(!this.defaultHeaders['X-Requested-With']){this.initHeader('X-Requested-With',this.defaultXhrHeader,true);}}
if(postData&&this.useDefaultHeader&&(!this.hasHeaders||!this.headers['Content-Type'])){this.initHeader('Content-Type',this.defaultPostHeader);}
if(this.hasDefaultHeaders||this.hasHeaders){this.setHeader(o);}
this.handleReadyState(o,callback);o.conn.send(postData||null);return o;}},handleReadyState:function(o,callback)
{var oConn=this;if(callback&&callback.timeout){this.timeout[o.tId]=window.setTimeout(function(){oConn.abort(o,callback,true);},callback.timeout);}
this.poll[o.tId]=window.setInterval(function(){if(o.conn&&o.conn.readyState==4){window.clearInterval(oConn.poll[o.tId]);delete oConn.poll[o.tId];if(callback&&callback.timeout){window.clearTimeout(oConn.timeout[o.tId]);delete oConn.timeout[o.tId];}
oConn.handleTransactionResponse(o,callback);}},this.pollInterval);},handleTransactionResponse:function(o,callback,isAbort)
{if(!callback){this.releaseObject(o);return;}
var httpStatus,responseObject;try
{if(o.conn.status!==undefined&&o.conn.status!=0){httpStatus=o.conn.status;}
else{httpStatus=13030;}}
catch(e){httpStatus=13030;}
if((httpStatus>=200&&httpStatus<300)||(Ext.isIE&&httpStatus==1223)){responseObject=this.createResponseObject(o,callback.argument);if(callback.success){if(!callback.scope){callback.success(responseObject);}
else{callback.success.apply(callback.scope,[responseObject]);}}}
else{switch(httpStatus){case 12002:case 12029:case 12030:case 12031:case 12152:case 13030:responseObject=this.createExceptionObject(o.tId,callback.argument,(isAbort?isAbort:false));if(callback.failure){if(!callback.scope){callback.failure(responseObject);}
else{callback.failure.apply(callback.scope,[responseObject]);}}
break;default:responseObject=this.createResponseObject(o,callback.argument);if(callback.failure){if(!callback.scope){callback.failure(responseObject);}
else{callback.failure.apply(callback.scope,[responseObject]);}}}}
this.releaseObject(o);responseObject=null;},createResponseObject:function(o,callbackArg)
{var obj={};var headerObj={};try
{var headerStr=o.conn.getAllResponseHeaders();var header=headerStr.split('\n');for(var i=0;i<header.length;i++){var delimitPos=header[i].indexOf(':');if(delimitPos!=-1){headerObj[header[i].substring(0,delimitPos)]=header[i].substring(delimitPos+2);}}}
catch(e){}
obj.tId=o.tId;obj.status=o.conn.status;obj.statusText=o.conn.statusText;obj.getResponseHeader=function(header){return headerObj[header];};obj.getAllResponseHeaders=function(){return headerStr};obj.responseText=o.conn.responseText;obj.responseXML=o.conn.responseXML;if(typeof callbackArg!==undefined){obj.argument=callbackArg;}
return obj;},createExceptionObject:function(tId,callbackArg,isAbort)
{var COMM_CODE=0;var COMM_ERROR='communication failure';var ABORT_CODE=-1;var ABORT_ERROR='transaction aborted';var obj={};obj.tId=tId;if(isAbort){obj.status=ABORT_CODE;obj.statusText=ABORT_ERROR;}
else{obj.status=COMM_CODE;obj.statusText=COMM_ERROR;}
if(callbackArg){obj.argument=callbackArg;}
return obj;},initHeader:function(label,value,isDefault)
{var headerObj=(isDefault)?this.defaultHeaders:this.headers;if(headerObj[label]===undefined){headerObj[label]=value;}
else{headerObj[label]=value+","+headerObj[label];}
if(isDefault){this.hasDefaultHeaders=true;}
else{this.hasHeaders=true;}},setHeader:function(o)
{if(this.hasDefaultHeaders){for(var prop in this.defaultHeaders){if(this.defaultHeaders.hasOwnProperty(prop)){o.conn.setRequestHeader(prop,this.defaultHeaders[prop]);}}}
if(this.hasHeaders){for(var prop in this.headers){if(this.headers.hasOwnProperty(prop)){o.conn.setRequestHeader(prop,this.headers[prop]);}}
this.headers={};this.hasHeaders=false;}},resetDefaultHeaders:function(){delete this.defaultHeaders;this.defaultHeaders={};this.hasDefaultHeaders=false;},abort:function(o,callback,isTimeout)
{if(this.isCallInProgress(o)){o.conn.abort();window.clearInterval(this.poll[o.tId]);delete this.poll[o.tId];if(isTimeout){delete this.timeout[o.tId];}
this.handleTransactionResponse(o,callback,true);return true;}
else{return false;}},isCallInProgress:function(o)
{if(o.conn){return o.conn.readyState!=4&&o.conn.readyState!=0;}
else{return false;}},releaseObject:function(o)
{o.conn=null;o=null;},activeX:['MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP','Microsoft.XMLHTTP']};Ext.lib.Region=function(t,r,b,l){this.top=t;this[1]=t;this.right=r;this.bottom=b;this.left=l;this[0]=l;};Ext.lib.Region.prototype={contains:function(region){return(region.left>=this.left&&region.right<=this.right&&region.top>=this.top&&region.bottom<=this.bottom);},getArea:function(){return((this.bottom-this.top)*(this.right-this.left));},intersect:function(region){var t=Math.max(this.top,region.top);var r=Math.min(this.right,region.right);var b=Math.min(this.bottom,region.bottom);var l=Math.max(this.left,region.left);if(b>=t&&r>=l){return new Ext.lib.Region(t,r,b,l);}else{return null;}},union:function(region){var t=Math.min(this.top,region.top);var r=Math.max(this.right,region.right);var b=Math.max(this.bottom,region.bottom);var l=Math.min(this.left,region.left);return new Ext.lib.Region(t,r,b,l);},constrainTo:function(r){this.top=this.top.constrain(r.top,r.bottom);this.bottom=this.bottom.constrain(r.top,r.bottom);this.left=this.left.constrain(r.left,r.right);this.right=this.right.constrain(r.left,r.right);return this;},adjust:function(t,l,b,r){this.top+=t;this.left+=l;this.right+=r;this.bottom+=b;return this;}};Ext.lib.Region.getRegion=function(el){var p=Ext.lib.Dom.getXY(el);var t=p[1];var r=p[0]+el.offsetWidth;var b=p[1]+el.offsetHeight;var l=p[0];return new Ext.lib.Region(t,r,b,l);};Ext.lib.Point=function(x,y){if(Ext.isArray(x)){y=x[1];x=x[0];}
this.x=this.right=this.left=this[0]=x;this.y=this.top=this.bottom=this[1]=y;};Ext.lib.Point.prototype=new Ext.lib.Region();Ext.lib.Anim={scroll:function(el,args,duration,easing,cb,scope){return this.run(el,args,duration,easing,cb,scope,Ext.lib.Scroll);},motion:function(el,args,duration,easing,cb,scope){return this.run(el,args,duration,easing,cb,scope,Ext.lib.Motion);},color:function(el,args,duration,easing,cb,scope){return this.run(el,args,duration,easing,cb,scope,Ext.lib.ColorAnim);},run:function(el,args,duration,easing,cb,scope,type){type=type||Ext.lib.AnimBase;if(typeof easing=="string"){easing=Ext.lib.Easing[easing];}
var anim=new type(el,args,duration,easing);anim.animateX(function(){Ext.callback(cb,scope);});return anim;}};function fly(el){if(!libFlyweight){libFlyweight=new Ext.Element.Flyweight();}
libFlyweight.dom=el;return libFlyweight;}
if(Ext.isIE){function fnCleanUp(){var p=Function.prototype;delete p.createSequence;delete p.defer;delete p.createDelegate;delete p.createCallback;delete p.createInterceptor;window.detachEvent("onunload",fnCleanUp);}
window.attachEvent("onunload",fnCleanUp);}
Ext.lib.AnimBase=function(el,attributes,duration,method){if(el){this.init(el,attributes,duration,method);}};Ext.lib.AnimBase.prototype={toString:function(){var el=this.getEl();var id=el.id||el.tagName;return("Anim "+id);},patterns:{noNegatives:/width|height|opacity|padding/i,offsetAttribute:/^((width|height)|(top|left))$/,defaultUnit:/width|height|top$|bottom$|left$|right$/i,offsetUnit:/\d+(em|%|en|ex|pt|in|cm|mm|pc)$/i},doMethod:function(attr,start,end){return this.method(this.currentFrame,start,end-start,this.totalFrames);},setAttribute:function(attr,val,unit){if(this.patterns.noNegatives.test(attr)){val=(val>0)?val:0;}
Ext.fly(this.getEl(),'_anim').setStyle(attr,val+unit);},getAttribute:function(attr){var el=this.getEl();var val=fly(el).getStyle(attr);if(val!=='auto'&&!this.patterns.offsetUnit.test(val)){return parseFloat(val);}
var a=this.patterns.offsetAttribute.exec(attr)||[];var pos=!!(a[3]);var box=!!(a[2]);if(box||(fly(el).getStyle('position')=='absolute'&&pos)){val=el['offset'+a[0].charAt(0).toUpperCase()+a[0].substr(1)];}else{val=0;}
return val;},getDefaultUnit:function(attr){if(this.patterns.defaultUnit.test(attr)){return'px';}
return'';},animateX:function(callback,scope){var f=function(){this.onComplete.removeListener(f);if(typeof callback=="function"){callback.call(scope||this,this);}};this.onComplete.addListener(f,this);this.animate();},setRuntimeAttribute:function(attr){var start;var end;var attributes=this.attributes;this.runtimeAttributes[attr]={};var isset=function(prop){return(typeof prop!=='undefined');};if(!isset(attributes[attr]['to'])&&!isset(attributes[attr]['by'])){return false;}
start=(isset(attributes[attr]['from']))?attributes[attr]['from']:this.getAttribute(attr);if(isset(attributes[attr]['to'])){end=attributes[attr]['to'];}else if(isset(attributes[attr]['by'])){if(start.constructor==Array){end=[];for(var i=0,len=start.length;i<len;++i){end[i]=start[i]+attributes[attr]['by'][i];}}else{end=start+attributes[attr]['by'];}}
this.runtimeAttributes[attr].start=start;this.runtimeAttributes[attr].end=end;this.runtimeAttributes[attr].unit=(isset(attributes[attr].unit))?attributes[attr]['unit']:this.getDefaultUnit(attr);},init:function(el,attributes,duration,method){var isAnimated=false;var startTime=null;var actualFrames=0;el=Ext.getDom(el);this.attributes=attributes||{};this.duration=duration||1;this.method=method||Ext.lib.Easing.easeNone;this.useSeconds=true;this.currentFrame=0;this.totalFrames=Ext.lib.AnimMgr.fps;this.getEl=function(){return el;};this.isAnimated=function(){return isAnimated;};this.getStartTime=function(){return startTime;};this.runtimeAttributes={};this.animate=function(){if(this.isAnimated()){return false;}
this.currentFrame=0;this.totalFrames=(this.useSeconds)?Math.ceil(Ext.lib.AnimMgr.fps*this.duration):this.duration;Ext.lib.AnimMgr.registerElement(this);};this.stop=function(finish){if(finish){this.currentFrame=this.totalFrames;this._onTween.fire();}
Ext.lib.AnimMgr.stop(this);};var onStart=function(){this.onStart.fire();this.runtimeAttributes={};for(var attr in this.attributes){this.setRuntimeAttribute(attr);}
isAnimated=true;actualFrames=0;startTime=new Date();};var onTween=function(){var data={duration:new Date()-this.getStartTime(),currentFrame:this.currentFrame};data.toString=function(){return('duration: '+data.duration+', currentFrame: '+data.currentFrame);};this.onTween.fire(data);var runtimeAttributes=this.runtimeAttributes;for(var attr in runtimeAttributes){this.setAttribute(attr,this.doMethod(attr,runtimeAttributes[attr].start,runtimeAttributes[attr].end),runtimeAttributes[attr].unit);}
actualFrames+=1;};var onComplete=function(){var actual_duration=(new Date()-startTime)/1000;var data={duration:actual_duration,frames:actualFrames,fps:actualFrames/actual_duration};data.toString=function(){return('duration: '+data.duration+', frames: '+data.frames+', fps: '+data.fps);};isAnimated=false;actualFrames=0;this.onComplete.fire(data);};this._onStart=new Ext.util.Event(this);this.onStart=new Ext.util.Event(this);this.onTween=new Ext.util.Event(this);this._onTween=new Ext.util.Event(this);this.onComplete=new Ext.util.Event(this);this._onComplete=new Ext.util.Event(this);this._onStart.addListener(onStart);this._onTween.addListener(onTween);this._onComplete.addListener(onComplete);}};Ext.lib.AnimMgr=new function(){var thread=null;var queue=[];var tweenCount=0;this.fps=1000;this.delay=1;this.registerElement=function(tween){queue[queue.length]=tween;tweenCount+=1;tween._onStart.fire();this.start();};this.unRegister=function(tween,index){tween._onComplete.fire();index=index||getIndex(tween);if(index!=-1){queue.splice(index,1);}
tweenCount-=1;if(tweenCount<=0){this.stop();}};this.start=function(){if(thread===null){thread=setInterval(this.run,this.delay);}};this.stop=function(tween){if(!tween){clearInterval(thread);for(var i=0,len=queue.length;i<len;++i){if(queue[0].isAnimated()){this.unRegister(queue[0],0);}}
queue=[];thread=null;tweenCount=0;}
else{this.unRegister(tween);}};this.run=function(){for(var i=0,len=queue.length;i<len;++i){var tween=queue[i];if(!tween||!tween.isAnimated()){continue;}
if(tween.currentFrame<tween.totalFrames||tween.totalFrames===null)
{tween.currentFrame+=1;if(tween.useSeconds){correctFrame(tween);}
tween._onTween.fire();}
else{Ext.lib.AnimMgr.stop(tween,i);}}};var getIndex=function(anim){for(var i=0,len=queue.length;i<len;++i){if(queue[i]==anim){return i;}}
return-1;};var correctFrame=function(tween){var frames=tween.totalFrames;var frame=tween.currentFrame;var expected=(tween.currentFrame*tween.duration*1000/tween.totalFrames);var elapsed=(new Date()-tween.getStartTime());var tweak=0;if(elapsed<tween.duration*1000){tweak=Math.round((elapsed/expected-1)*tween.currentFrame);}else{tweak=frames-(frame+1);}
if(tweak>0&&isFinite(tweak)){if(tween.currentFrame+tweak>=frames){tweak=frames-(frame+1);}
tween.currentFrame+=tweak;}};};Ext.lib.Bezier=new function(){this.getPosition=function(points,t){var n=points.length;var tmp=[];for(var i=0;i<n;++i){tmp[i]=[points[i][0],points[i][1]];}
for(var j=1;j<n;++j){for(i=0;i<n-j;++i){tmp[i][0]=(1-t)*tmp[i][0]+t*tmp[parseInt(i+1,10)][0];tmp[i][1]=(1-t)*tmp[i][1]+t*tmp[parseInt(i+1,10)][1];}}
return[tmp[0][0],tmp[0][1]];};};(function(){Ext.lib.ColorAnim=function(el,attributes,duration,method){Ext.lib.ColorAnim.superclass.constructor.call(this,el,attributes,duration,method);};Ext.extend(Ext.lib.ColorAnim,Ext.lib.AnimBase);var Y=Ext.lib;var superclass=Y.ColorAnim.superclass;var proto=Y.ColorAnim.prototype;proto.toString=function(){var el=this.getEl();var id=el.id||el.tagName;return("ColorAnim "+id);};proto.patterns.color=/color$/i;proto.patterns.rgb=/^rgb\(([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\)$/i;proto.patterns.hex=/^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i;proto.patterns.hex3=/^#?([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})$/i;proto.patterns.transparent=/^transparent|rgba\(0, 0, 0, 0\)$/;proto.parseColor=function(s){if(s.length==3){return s;}
var c=this.patterns.hex.exec(s);if(c&&c.length==4){return[parseInt(c[1],16),parseInt(c[2],16),parseInt(c[3],16)];}
c=this.patterns.rgb.exec(s);if(c&&c.length==4){return[parseInt(c[1],10),parseInt(c[2],10),parseInt(c[3],10)];}
c=this.patterns.hex3.exec(s);if(c&&c.length==4){return[parseInt(c[1]+c[1],16),parseInt(c[2]+c[2],16),parseInt(c[3]+c[3],16)];}
return null;};proto.getAttribute=function(attr){var el=this.getEl();if(this.patterns.color.test(attr)){var val=fly(el).getStyle(attr);if(this.patterns.transparent.test(val)){var parent=el.parentNode;val=fly(parent).getStyle(attr);while(parent&&this.patterns.transparent.test(val)){parent=parent.parentNode;val=fly(parent).getStyle(attr);if(parent.tagName.toUpperCase()=='HTML'){val='#fff';}}}}else{val=superclass.getAttribute.call(this,attr);}
return val;};proto.doMethod=function(attr,start,end){var val;if(this.patterns.color.test(attr)){val=[];for(var i=0,len=start.length;i<len;++i){val[i]=superclass.doMethod.call(this,attr,start[i],end[i]);}
val='rgb('+Math.floor(val[0])+','+Math.floor(val[1])+','+Math.floor(val[2])+')';}
else{val=superclass.doMethod.call(this,attr,start,end);}
return val;};proto.setRuntimeAttribute=function(attr){superclass.setRuntimeAttribute.call(this,attr);if(this.patterns.color.test(attr)){var attributes=this.attributes;var start=this.parseColor(this.runtimeAttributes[attr].start);var end=this.parseColor(this.runtimeAttributes[attr].end);if(typeof attributes[attr]['to']==='undefined'&&typeof attributes[attr]['by']!=='undefined'){end=this.parseColor(attributes[attr].by);for(var i=0,len=start.length;i<len;++i){end[i]=start[i]+end[i];}}
this.runtimeAttributes[attr].start=start;this.runtimeAttributes[attr].end=end;}};})();Ext.lib.Easing={easeNone:function(t,b,c,d){return c*t/d+b;},easeIn:function(t,b,c,d){return c*(t/=d)*t+b;},easeOut:function(t,b,c,d){return-c*(t/=d)*(t-2)+b;},easeBoth:function(t,b,c,d){if((t/=d/2)<1){return c/2*t*t+b;}
return-c/2*((--t)*(t-2)-1)+b;},easeInStrong:function(t,b,c,d){return c*(t/=d)*t*t*t+b;},easeOutStrong:function(t,b,c,d){return-c*((t=t/d-1)*t*t*t-1)+b;},easeBothStrong:function(t,b,c,d){if((t/=d/2)<1){return c/2*t*t*t*t+b;}
return-c/2*((t-=2)*t*t*t-2)+b;},elasticIn:function(t,b,c,d,a,p){if(t==0){return b;}
if((t/=d)==1){return b+c;}
if(!p){p=d*.3;}
if(!a||a<Math.abs(c)){a=c;var s=p/4;}
else{var s=p/(2*Math.PI)*Math.asin(c/a);}
return-(a*Math.pow(2,10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p))+b;},elasticOut:function(t,b,c,d,a,p){if(t==0){return b;}
if((t/=d)==1){return b+c;}
if(!p){p=d*.3;}
if(!a||a<Math.abs(c)){a=c;var s=p/4;}
else{var s=p/(2*Math.PI)*Math.asin(c/a);}
return a*Math.pow(2,-10*t)*Math.sin((t*d-s)*(2*Math.PI)/p)+c+b;},elasticBoth:function(t,b,c,d,a,p){if(t==0){return b;}
if((t/=d/2)==2){return b+c;}
if(!p){p=d*(.3*1.5);}
if(!a||a<Math.abs(c)){a=c;var s=p/4;}
else{var s=p/(2*Math.PI)*Math.asin(c/a);}
if(t<1){return-.5*(a*Math.pow(2,10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p))+b;}
return a*Math.pow(2,-10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p)*.5+c+b;},backIn:function(t,b,c,d,s){if(typeof s=='undefined'){s=1.70158;}
return c*(t/=d)*t*((s+1)*t-s)+b;},backOut:function(t,b,c,d,s){if(typeof s=='undefined'){s=1.70158;}
return c*((t=t/d-1)*t*((s+1)*t+s)+1)+b;},backBoth:function(t,b,c,d,s){if(typeof s=='undefined'){s=1.70158;}
if((t/=d/2)<1){return c/2*(t*t*(((s*=(1.525))+1)*t-s))+b;}
return c/2*((t-=2)*t*(((s*=(1.525))+1)*t+s)+2)+b;},bounceIn:function(t,b,c,d){return c-Ext.lib.Easing.bounceOut(d-t,0,c,d)+b;},bounceOut:function(t,b,c,d){if((t/=d)<(1/2.75)){return c*(7.5625*t*t)+b;}else if(t<(2/2.75)){return c*(7.5625*(t-=(1.5/2.75))*t+.75)+b;}else if(t<(2.5/2.75)){return c*(7.5625*(t-=(2.25/2.75))*t+.9375)+b;}
return c*(7.5625*(t-=(2.625/2.75))*t+.984375)+b;},bounceBoth:function(t,b,c,d){if(t<d/2){return Ext.lib.Easing.bounceIn(t*2,0,c,d)*.5+b;}
return Ext.lib.Easing.bounceOut(t*2-d,0,c,d)*.5+c*.5+b;}};(function(){Ext.lib.Motion=function(el,attributes,duration,method){if(el){Ext.lib.Motion.superclass.constructor.call(this,el,attributes,duration,method);}};Ext.extend(Ext.lib.Motion,Ext.lib.ColorAnim);var Y=Ext.lib;var superclass=Y.Motion.superclass;var proto=Y.Motion.prototype;proto.toString=function(){var el=this.getEl();var id=el.id||el.tagName;return("Motion "+id);};proto.patterns.points=/^points$/i;proto.setAttribute=function(attr,val,unit){if(this.patterns.points.test(attr)){unit=unit||'px';superclass.setAttribute.call(this,'left',val[0],unit);superclass.setAttribute.call(this,'top',val[1],unit);}else{superclass.setAttribute.call(this,attr,val,unit);}};proto.getAttribute=function(attr){if(this.patterns.points.test(attr)){var val=[superclass.getAttribute.call(this,'left'),superclass.getAttribute.call(this,'top')];}else{val=superclass.getAttribute.call(this,attr);}
return val;};proto.doMethod=function(attr,start,end){var val=null;if(this.patterns.points.test(attr)){var t=this.method(this.currentFrame,0,100,this.totalFrames)/100;val=Y.Bezier.getPosition(this.runtimeAttributes[attr],t);}else{val=superclass.doMethod.call(this,attr,start,end);}
return val;};proto.setRuntimeAttribute=function(attr){if(this.patterns.points.test(attr)){var el=this.getEl();var attributes=this.attributes;var start;var control=attributes['points']['control']||[];var end;var i,len;if(control.length>0&&!Ext.isArray(control[0])){control=[control];}else{var tmp=[];for(i=0,len=control.length;i<len;++i){tmp[i]=control[i];}
control=tmp;}
Ext.fly(el,'_anim').position();if(isset(attributes['points']['from'])){Ext.lib.Dom.setXY(el,attributes['points']['from']);}
else{Ext.lib.Dom.setXY(el,Ext.lib.Dom.getXY(el));}
start=this.getAttribute('points');if(isset(attributes['points']['to'])){end=translateValues.call(this,attributes['points']['to'],start);var pageXY=Ext.lib.Dom.getXY(this.getEl());for(i=0,len=control.length;i<len;++i){control[i]=translateValues.call(this,control[i],start);}}else if(isset(attributes['points']['by'])){end=[start[0]+attributes['points']['by'][0],start[1]+attributes['points']['by'][1]];for(i=0,len=control.length;i<len;++i){control[i]=[start[0]+control[i][0],start[1]+control[i][1]];}}
this.runtimeAttributes[attr]=[start];if(control.length>0){this.runtimeAttributes[attr]=this.runtimeAttributes[attr].concat(control);}
this.runtimeAttributes[attr][this.runtimeAttributes[attr].length]=end;}
else{superclass.setRuntimeAttribute.call(this,attr);}};var translateValues=function(val,start){var pageXY=Ext.lib.Dom.getXY(this.getEl());val=[val[0]-pageXY[0]+start[0],val[1]-pageXY[1]+start[1]];return val;};var isset=function(prop){return(typeof prop!=='undefined');};})();(function(){Ext.lib.Scroll=function(el,attributes,duration,method){if(el){Ext.lib.Scroll.superclass.constructor.call(this,el,attributes,duration,method);}};Ext.extend(Ext.lib.Scroll,Ext.lib.ColorAnim);var Y=Ext.lib;var superclass=Y.Scroll.superclass;var proto=Y.Scroll.prototype;proto.toString=function(){var el=this.getEl();var id=el.id||el.tagName;return("Scroll "+id);};proto.doMethod=function(attr,start,end){var val=null;if(attr=='scroll'){val=[this.method(this.currentFrame,start[0],end[0]-start[0],this.totalFrames),this.method(this.currentFrame,start[1],end[1]-start[1],this.totalFrames)];}else{val=superclass.doMethod.call(this,attr,start,end);}
return val;};proto.getAttribute=function(attr){var val=null;var el=this.getEl();if(attr=='scroll'){val=[el.scrollLeft,el.scrollTop];}else{val=superclass.getAttribute.call(this,attr);}
return val;};proto.setAttribute=function(attr,val,unit){var el=this.getEl();if(attr=='scroll'){el.scrollLeft=val[0];el.scrollTop=val[1];}else{superclass.setAttribute.call(this,attr,val,unit);}};})();})();
