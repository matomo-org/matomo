/**
* <pre>
* Jash - JavaScript Shell
* Copyright: 2007, Billy Reisinger
* Documentation: http://www.billyreisinger.com/jash/
* License: GNU General Public License - http://www.gnu.org/licenses/gpl.html
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
* </pre>
* @class 	Jash
* @returns	{object}	an object that is a new instance of Jash class
*/
function Jash(){this.jashRoot="http://www.billyreisinger.com/jash/source/latest/";this.domGetElFunctions={id:new Array("document.getElementById","$"),className:new Array("getElementsByClassName","$C")};var line="-------------------------------------------------";var _null="nooutput";var self=this;this.version="1.35.7";this.versionDate="2009/09/05 09:10";this.main=function(){this.browser=this.returnBrowserType();this.lineNumber=0;this.mainBlock;this.output=document.getElementById("JashOutput");this.input;this.outputHistory=new Array();this.cssEvalFlag=false;this.innerHtmlInspection=false;this.accessKeyText=this.getAccessKeyText();this.defaultText="Jash, v"+this.version+"\nEnter \"jash.help()\" for a list of commands.\n";this.cls=this.clear;this.tabIndexIndex=0;this.currentNode={};this.triedDomInserts=0;this.tips=["Did you know?\nThe DOM Inspector will automatically put\n an element with an ID in the input field for you.","Did you know?\nYou can tie this script into your own to jash scripts. Use 'jash.methodName' anywhere\n in your scripts, and pull\n up this window before executing to see\n the results.","Did you know?\nUse jash.stopWatch.start() and jash.stopWatch.stop() to\n time execution speeds! Handy for optimization.","Did you know?\nPress TAB to complete a function, method, or property name.\n If more than one match is found, a list of possible\n matches will appear.","Did you know?\nYou can use jash.show() to show a list of the names\nand types of an object's members.\nOn the other hand, jash.dump will show names and\n_values_ of an object's members.","Whoa ---- you can now tab-complete HTML element ids after typing document.getElementsById(' (or the '$' shorthand if using Prototype).  This also works with class names (i.e. document.getElementsByClassName)"]
this.defaultText+=line+"\n"+this.tips[(parseInt((Math.random()*10)%this.tips.length))]+"\n"+line+"\n";this.loopOnDomInserts();}
this.loopOnDomInserts=function(){try{self.testDomInsert();}catch(e){self.triedDomInserts++;if(self.triedDomInserts<30){window.setTimeout(self.loopOnDomInserts,250);}
return;}
document.body.removeChild(document.getElementById("JashTestElement"));self.doDomInserts();self.finishInit();}
this.testDomInsert=function(){document.body.appendChild(document.createElement("em")).id="JashTestElement";}
this.finishInit=function(){Jash.TabComplete.prototype=this;this.tabComplete=new Jash.TabComplete();Jash.Evaluator.prototype=this;this.evaluation=new Jash.Evaluator();this.history=new Jash.History();window.setTimeout(function(){self.input.focus();},500);}
this.doDomInserts=function(){if(self.returnBrowserType()!="sa"){self.stylesheet=document.body.appendChild(document.createElement('link'));}else{self.stylesheet=document.getElementsByTagName("head")[0].appendChild(document.createElement("link"));}
self.stylesheet.type='text/css';self.stylesheet.rel='stylesheet';self.stylesheet.href=self.jashRoot+'Jash.css';self.create();}
this.returnBrowserType=function(){if(window.navigator.userAgent.toLowerCase().indexOf("opera")!=-1){return"op";}
if(window.navigator.userAgent.toLowerCase().indexOf("msie")!=-1){return"ie";}
if(window.navigator.userAgent.toLowerCase().indexOf("firefox")!=-1){return"ff";}
if(window.navigator.userAgent.toLowerCase().indexOf("safari")!=-1){return"sa";}}
this.returnOsType=function(){var ua=window.navigator.userAgent.toLowerCase();if(ua.indexOf("macintosh")!=-1){return"mac";}else if(ua.indexOf("windows")!=-1){return"win";}else if(ua.indexOf("linux i686")!=-1){return"linux";}}
this.getAccessKeyText=function(){var txt;var agt=this.returnOsType();switch(this.browser){case"ie":txt="Alt";break;case"ff":if(agt=="mac"){txt="Ctrl";}else if(agt=="linux"){txt="Alt";}else{txt="Alt-Shift";}
break;case"op":txt="Shift-Esc";break;case"sa":if(agt=="mac"){txt="Ctrl";}else{txt="Alt";}
break;default:txt="Alt";break;}
return txt;}
this.print=function(text,clear,suppressLineNumbers,autoscroll){clear=(typeof clear!="undefined")?clear:false;autoscroll=(typeof autoscroll!="undefined")?autoscroll:true;if(this.output==null||document.getElementById("JashParent")==null){this.create();this.output=document.getElementById("JashOutput");this.mainBlock=document.getElementById("JashParent");}
if(clear){this.clear();}
if(text!=""){if(typeof suppressLineNumbers!="undefined"&&!suppressLineNumbers){this.output.value+=this.lineNumber+". ";}
this.output.value+=text+"\n";if(autoscroll){this.output.scrollTop=this.output.scrollHeight;}
this.lineNumber++;}
return _null;}
this.show=function(obj){this.print(line,false,true);var out="";this.lineNumber=0;for(var p in obj){if(typeof obj[p]=="function"){var t=obj[p].toString();t=t.replace(/[\x0A\x0D]/g,"").replace(/\s+/g,"").replace(/\{.+\}/g,"{ ... }");t=t.replace(p,"");t=p+": "+t;}else{t=p+": "+typeof obj[p];}
out+=++this.lineNumber+". "+t+"\n";}
this.print(out,false,true);this.print(line,false,true);this.output.scrollTop=this.output.scrollHeight;return _null;}
this.dump=function(obj){if(typeof obj=="string"){this.print(obj);}else{this.print(line,false,true);var out=new Array();if(typeof obj.push=="undefined"){for(var th in obj){out.push(++this.lineNumber+". "+th+" = "+obj[th]);}}else{for(var i=0;i<obj.length;i++){out.push(++this.lineNumber+". "+obj[i]);}}
this.print(out.join("\n"),false,true);this.print(line,false,true);this.output.scrollTop=this.output.scrollHeight;}
return _null;}
this.clear=function(){this.outputHistory.push(this.output.value);this.output.value="";this.input.focus();return _null;}
this.showOutputHistory=function(){this.outputHistory.push(this.output.value);this.dump(this.outputHistory);}
this.assignInputKeyEvent=function(event){var keyCode=event.keyCode;if(keyCode==13&&!event.shiftKey){this.evaluation.evaluate(this.input.value);this.input.value="";return false;}else if(keyCode==38&&!event.shiftKey){if(this.browser!="op"){this.input.value=this.history.getPreviousInput();}
return false;}else if(keyCode==40){if(this.browser!="op"){this.input.value=this.history.getNextInput();}
return false;}else if(keyCode==9){this.tabComplete.tabComplete();return false;}}
this.getXBrowserYOffset=function(){var y;if(self.pageYOffset){y=self.pageYOffset;}else if(document.documentElement&&document.documentElement.scrollTop){y=document.documentElement.scrollTop;}else if(document.body){y=document.body.scrollTop;}
return y;}
this.getMouseXY=function(e){var tempX=0
var tempY=0
if(window.event){if(document.documentElement&&document.documentElement.scrollTop){tempX=window.event.clientX+document.documentElement.scrollLeft;tempY=window.event.clientY+document.documentElement.scrollTop;}else{tempX=window.event.clientX+document.body.scrollLeft;tempY=window.event.clientY+document.body.scrollTop;}}else{tempX=e.pageX;tempY=e.pageY;}
return{x:tempX,y:tempY};}
this.getDimensions=function(el){var dims={}
if(document.all){dims.x=el.offsetWidth;dims.y=el.offsetHeight;}else{dims.x=parseInt(document.defaultView.getComputedStyle(el,"").getPropertyValue("width"));dims.y=parseInt(document.defaultView.getComputedStyle(el,"").getPropertyValue("height"));}
return dims;}
this.addEvent=function(obj,eventName,func){if(obj.addEventListener)
return obj.addEventListener(eventName,func,true);else if(obj.attachEvent){obj.attachEvent("on"+eventName,func);return true;}
return false;}
this.findElementPosition=function(obj){var curleft=0;var curtop=0;if(obj.offsetParent){curleft=obj.offsetLeft
curtop=obj.offsetTop
while(obj=obj.offsetParent){curleft+=obj.offsetLeft
curtop+=obj.offsetTop}}
return[curleft,curtop];}
this.create=function(){if(document.getElementsByTagName("frameset").length>0){alert("Jash currently does not support pages with frames.");return;}
var self=this;var debugParent=document.createElement("div");var windowScrollY=0;if(document.documentElement&&document.documentElement.scrollTop){windowScrollY=document.documentElement.scrollTop;}else if(document.body){windowScrollY=document.body.scrollTop}else{windowScrollY=window.scrollY;}
debugParent.style.top=windowScrollY+50+"px";debugParent.id="JashParent";this.addEvent(document,"keydown",function(e){e=(typeof window.event!="undefined")?window.event:e;if(parseInt(e.keyCode)==27){if(typeof e.shiftKey=="undefined"||!e.shiftKey){self.close();}}});var textareaWrap=document.createElement("div");textareaWrap.id="JashTextareaWrap";var debugOutput=document.createElement("textarea");debugOutput.id="JashOutput";debugOutput.wrap="off";debugOutput.readOnly="true";debugOutput.value=this.defaultText;var inp=document.createElement("textarea");inp.id="JashInput";var last="";inp.onkeydown=function(e){e=(typeof window.event!="undefined")?window.event:e;return self.assignInputKeyEvent(e);}
inp.onkeypress=function(e){e=(typeof window.event!="undefined")?window.event:e;var k=e.keyCode;if(!self.evaluation.cssEvalFlag){if(k==9||(k==13&&!e.shiftKey)||(k==38&&!e.shiftKey)||k==40){if(k!=40&&this.browser!="ie"){return false;}}}else if(k==9){return false;}}
var dragBut=document.createElement("div");dragBut.innerHTML="Jash";dragBut.id="JashDragBar";dragBut.onmousedown=function(e){e=(typeof window.event!="undefined")?window.event:e;var xplus=(typeof e.layerX=="undefined")?e.offsetX:e.layerX;var yplus=(typeof e.layerY=="undefined")?e.offsetY:e.layerY;document.onmousemove=function(e){var coords=self.getMouseXY(e);document.getElementById("JashParent").style.top=coords.y-yplus+"px";document.getElementById("JashParent").style.left=coords.x-xplus+"px";}
return false;}
document.onmouseup=function(){document.onmousemove=null;};dragBut.onclick=function(){return false;}
var xBut=document.createElement("a");xBut.className="JashXButton";xBut.innerHTML="X";xBut.href="#";xBut.onclick=function(){self.close();return false;}
var clearBut=document.createElement("a");clearBut.innerHTML="Clear ("+this.accessKeyText+"-C)";clearBut.accessKey="C";clearBut.className="JashButton";clearBut.onclick=function(){self.clear();return false;}
this.setCrossBrowserAccessKeyFunctionForAnchor(clearBut);var evalBut=document.createElement("a");evalBut.value="Evaluate ("+this.accessKeyText+"-Z)";evalBut.innerHTML="Evaluate ("+this.accessKeyText+"-Z)";evalBut.accessKey="Z";evalBut.className="JashButton";evalBut.title="Evaluate current input ("+this.accessKeyText+"-Z)";evalBut.onclick=function(){self.evaluation.evaluate(inp.value);if(!self.evaluation.cssEvalFlag){inp.value="";}
inp.focus();return false;}
this.setCrossBrowserAccessKeyFunctionForAnchor(evalBut);var helpBut=document.createElement("a");helpBut.innerHTML="Help";helpBut.className="JashButton";helpBut.title="Help: show list of commands (or type jash.help(); )";helpBut.onclick=function(){self.help();}
var domBut=document.createElement("a");domBut.innerHTML="Mouseover DOM ("+this.accessKeyText+"-X)";domBut.title="Mouseover DOM: toggle to turn on/off inspection of document nodes ("+this.accessKeyText+"-X)";domBut.className="JashButton";domBut.accessKey="X";domBut.tabIndex="4";this.domActive=false;domBut.onclick=function(){if(!self.domActive){document.body.onmouseover=function(e){if(typeof e=="undefined"){e=window.event;}
self.showNodes(e);}
self.setButtonVisualActiveState(domBut,"on");self.domActive=true;}else{document.body.onmouseover=function(){}
self.domActive=false;self.setButtonVisualActiveState(domBut,"off");}
return _null;}
this.setCrossBrowserAccessKeyFunctionForAnchor(domBut);var innerHtmlInspectBut=document.createElement("a");innerHtmlInspectBut.innerHTML="innerHTML Dump ("+this.accessKeyText+"-A)";innerHtmlInspectBut.title="innerHTML Inspect: toggle to turn on/off innerHTML inspection of document nodes ("+this.accessKeyText+"-A)";innerHtmlInspectBut.className="JashButton";innerHtmlInspectBut.accessKey="A";innerHtmlInspectBut.tabIndex="5";this.innerHtmlInspection=false;innerHtmlInspectBut.onclick=function(){self.innerHtmlInspection=!self.innerHtmlInspection;self.setButtonVisualActiveState(innerHtmlInspectBut,self.innerHtmlInspection?"on":"off");return _null;}
this.setCrossBrowserAccessKeyFunctionForAnchor(innerHtmlInspectBut);var cssBut=document.createElement("a");cssBut.innerHTML="CSS Input ("+this.accessKeyText+"-S)";cssBut.title="CSS Input: turn on CSS input to enter arbitrary CSS ("+this.accessKeyText+"-S)";cssBut.className="JashButton";cssBut.accessKey="S";cssBut.onclick=function(){if(!self.evaluation.cssEvalFlag){self.setButtonVisualActiveState(cssBut,"on");self.evaluation.cssEvalFlag=true;inp.className="cssEntry";if(document.getElementById("JashStyleInput")!=null){self.evaluation.styleInputTag.disabled=false;}
inp.value="";}else{self.setButtonVisualActiveState(cssBut,"off");inp.className="";self.evaluation.cssEvalFlag=false;if(document.getElementById("JashStyleInput")!=null){self.evaluation.styleInputTag.disabled=true;}
inp.value="";}
inp.focus();return _null;}
this.setCrossBrowserAccessKeyFunctionForAnchor(cssBut);var resizeBut=document.createElement("div");resizeBut.id="JashResizeButton";this.minDims={x:100,y:100};resizeBut.onmousedown=function(e){e=(typeof window.event!="undefined")?window.event:e;var originalDims=self.getDimensions(textareaWrap);var originMouseDims=self.getMouseXY(e);document.onmousemove=function(e){var newMouseDims=self.getMouseXY(e);var newWidth=originalDims.x+(newMouseDims.x-originMouseDims.x);if(newWidth<self.minDims.x){newWidth=self.minDims.x;}
textareaWrap.style.width=newWidth+"px";debugParent.style.width=newWidth+"px";var newHeight=originalDims.y+(newMouseDims.y-originMouseDims.y);if(newHeight<self.minDims.y){newHeight=self.minDims.y;}
textareaWrap.style.height=newHeight+"px";debugParent.style.height=newHeight+"px";}
document.onmouseup=function(){document.onmousemove="";}}
var bottomBar=document.createElement("div");bottomBar.id="JashBottomBar";debugParent.appendChild(dragBut);debugParent.appendChild(xBut);bottomBar.appendChild(evalBut);bottomBar.appendChild(cssBut);bottomBar.appendChild(domBut);bottomBar.appendChild(innerHtmlInspectBut);bottomBar.appendChild(clearBut);bottomBar.appendChild(helpBut);debugParent.appendChild(bottomBar);debugParent.appendChild(resizeBut);document.body.appendChild(debugParent);textareaWrap.appendChild(debugOutput);textareaWrap.appendChild(inp);debugParent.appendChild(textareaWrap);this.bottomBar=document.getElementById("JashBottomBar");this.dragBar=document.getElementById("JashDragBar")
this.output=document.getElementById("JashOutput");this.input=document.getElementById("JashInput");this.mainBlock=debugParent;this.addEvent(window,'scroll',function(){debugParent.style.top=50+self.getXBrowserYOffset()+'px';});}
this.setButtonVisualActiveState=function(button,state){if(state=="on"){button.style.backgroundColor="lightgreen";}else{button.style.backgroundColor="";}}
this.help=function(){var out=new Array();out.push(line);out.push("Jash v"+this.version+" "+this.versionDate,true);out.push("http://www.billyreisinger.com/jash/documentation.html");out.push(line);out.push("METHODS");out.push(line);out.push("jash.cls() - clear console");out.push("jash.print(str,clear) - output str to console ~~ str = string ~~ clear = true|false: clear console before output");out.push("jash.close() - close this console");out.push("jash.dump(obj) - output object and members to console");out.push("jash.show(obj) - print out the names and types (only) of all members of obj");out.push("jash.stopWatch.start() - start timer");out.push("jash.stopWatch.stop() - end timer and return result in ms");out.push("jash.kill(HTML Element) - remove an element from the page.");out.push("jash.getDimensions(HTML Element) - get width, height dimensions of an html element. Returns an object [x,y]");out.push(line);out.push("KEYSTROKES");out.push(line);out.push("press up arrow in input field to retrieve last input");out.push("press ESC to show/hide console");out.push("press ENTER in input field to enter a command");out.push("press TAB to auto-complete input");out.push("press "+this.accessKeyText+"-Z to evaluate input");out.push("press "+this.accessKeyText+"-X to activate/deactivate DOM inspector");out.push("press "+this.accessKeyText+"-A to activate/deactivate innerHTML dump (only works w/ DOM inspector)");out.push("press "+this.accessKeyText+"-C to clear output and input");out.push("press "+this.accessKeyText+"-S to turn on/off CSS input mode. In CSS input mode, you can enter arbitrary CSS selectors and rules, as you would normally do in a CSS stylesheet.");this.print(out.join("\n"));return _null;}
this.close=function(){if(this.mainBlock.style.display=="none"){this.mainBlock.style.display="block";this.input.focus();}else{this.mainBlock.style.display="none";}}
this.setCrossBrowserAccessKeyFunctionForAnchor=function(el){var self=this;el.tabIndex=++this.tabIndexIndex;if(this.browser=="ie"){el.onfocus=function(){if(window.event.altKey){el.onclick();}
self.input.focus();}}}
this.stopWatch={t_start:0,t_end:0,t_total:0,start:function(){t_start=new Date().getTime();return t_start;},stop:function(){t_end=new Date().getTime();t_total=t_end-t_start;return(t_total);}}
this.showNodes=function(e){if(typeof e=="undefined")e=window.event;var el=typeof e.target=="undefined"?e.srcElement:e.target;this.currentNode=el;var childMost=this.identifyNode(el,false);var out="";var childmostTxt="childmost..... "+childMost.txt+"\n";while(el=el.parentNode){if(el.nodeName.toLowerCase()=="html"){out="parentmost.... <html>\n"+out;break;}
out=this.identifyNode(el).txt+"\n"+out;}
out="**** PRESS "+this.accessKeyText+"-X TO PAUSE / UNPAUSE ****\n"+out;out+=childmostTxt;this.print(out,true,true,false);if(this.innerHtmlInspection){this.print("INNER HTML");if(this.currentNode.innerHTML.indexOf("<")!=-1){this.print(Jash.Indenter.indent(this.currentNode.innerHTML),false,true,false);}else{this.print(this.currentNode.innerHTML,false,true,false);}}
if(!this.evaluation.cssEvalFlag){if(childMost.id!=""){if(typeof $!="undefined"){this.input.value='$("'+childMost.id+'")';}else{this.input.value='document.getElementById("'+childMost.id+'")';}}else{this.input.value="this.currentNode";}}}
this.identifyNode=function(el,showDots){showDots=typeof showDots=="boolean"?showDots:true;var out={txt:"",id:""};if(showDots)out.txt+=".............. ";out.txt+="<"+el.nodeName.toLowerCase();if(el.id!=""){out.id=el.id;out.txt+=' id="'+el.id+'"';}
if(el.name){out.txt+=' name="'+el.name+'"';}
if(el.className!=""){out.txt+=' class="'+el.className+'"';}
if(el.href){out.txt+=' href="'+el.href+'"';}
out.txt+=">";return out;}
this.kill=function(){this.currentNode.parentNode.removeChild(this.currentNode);}}
Jash.Evaluator=function(){this.cssEvalFlag=false;var _null="nooutput";this.evaluate=function(input){if(input=="")return false;this.history.add(input);if(this.cssEvalFlag){this.evalCss(input);this.print(input);}else{var output=this.evalJs(input);if(typeof output!="undefined"){this.print(">> "+input);this.print(output);}}}
this.evalJs=function(input){try{var result;if(this.browser=="ie"){result=eval(input);}else{result=window.eval(input);}
if(result!==null&&result.toString()!=_null){return(result.toString());}else{return"null"}}catch(e){return(e.message);}}
this.evalCss=function(input){try{this.insertStyleRule(input);}catch(e){}
return input;}
this.insertStyleRule=function(rule){var lastStyleSheetIndex=document.styleSheets.length-1;if(document.getElementById("JashStyleInput")==null){this.styleInputTag=document.createElement("style");this.styleInputTag.id="JashStyleInput";this.styleInputTag.type="text/css";document.body.appendChild(this.styleInputTag);}
if(this.browser=="ff"||this.browser=="op"){this.styleInputTag.innerHTML+=rule+"\n";}else if(this.browser=="ie"||this.browser=="sa"){if(this.browser=="ie"){var i=0;}else if(this.browser="sa"){var i=document.styleSheets.length-1;}
var rulesArray=rule.split("}");for(var t=0;t<rulesArray.length;t++){var ruleSplit=rulesArray[t].split("{");var selectors=ruleSplit[0].split(",");for(var k=0;k<selectors.length;k++){document.styleSheets[i].addRule(selectors[k],ruleSplit[1]);}}}
return"";}
return this;}
Jash.History=function(){this.entries=new Array('');this.position=0;}
Jash.History.prototype={add:function(input){this.entries.push(input);this.position=this.entries.length-1;},getPreviousInput:function(){if(this.position<0){return'';}
var entry=typeof this.entries[this.position]!="undefined"?this.entries[this.position]:'';if(this.position>0){this.position--;}
return entry;},getNextInput:function(){if(this.position<this.entries.length){var entry=typeof this.entries[this.position]!="undefined"?this.entries[this.position]:'';if(this.entries.length<=this.position++){this.position++;}
return entry;}else return'';}}
Jash.Indenter={indentChar:"\t",nodesCommonlyUnclosed:new Array("link ","img ","meta ","!DOCTYPE ","input ","param","hr","br"),stringRepeat:function(stringToRepeat,times){var string=new Array();for(var i=0;i<times;i++){string.push(stringToRepeat);}
return string.join('');},closeUnclosedNode:function(str){for(var k=0;k<this.nodesCommonlyUnclosed.length;k++){var reg=new RegExp("^"+this.nodesCommonlyUnclosed[k].toLowerCase());if(str.toLowerCase().match(reg)){return str.replace(">","/>");}}
return str;},indentAndAdd:function(level,string,arr){var indents=this.stringRepeat(this.indentChar,level);arr.push(indents+string);return arr;},indent:function(source){var source=source;var arr=new Array();source=source.replace(/[\n\r\t]/g,'');source=source.replace(/>\s+/g,">");source=source.replace(/\s+</g,"<");var splitsrc=source.split("<");for(i=0;i<splitsrc.length;i++){splitsrc[i]=this.closeUnclosedNode(splitsrc[i]);}
source=splitsrc.join("<");var level=0;var sourceLength=source.length;var position=0;while(position<sourceLength){if(source.charAt(position)=='<'){var startedAt=position;var tagLevel=1;if(source.charAt(position+1)=='/'){tagLevel=-1;}
if(source.charAt(position+1)=='!'){tagLevel=0;}
while(source.charAt(position)!='>'){position++;}
if(source.charAt(position-1)=='/'){tagLevel=0;}
var tagLength=position+1-startedAt;if(tagLevel===-1){level--;}
arr=this.indentAndAdd(level,source.substr(startedAt,tagLength),arr);if(tagLevel===1){level++;}}
if((position+1)<sourceLength){if(source.charAt(position+1)!=='<'){startedAt=position+1;while(source.charAt(position)!=='<'&&position<sourceLength){position++;}
if(source.charAt(position)==='<'){tagLength=position-startedAt;arr=this.indentAndAdd(level,source.substr(startedAt,tagLength),arr);}}else{position++;}}else{break;}}
return arr.join("\n");}}
Jash.Profiler=function(func,onFinish){this.func=func;this.time=0;this.defaultOnFinish=function(){};this.results=new Array();this.onFinish=typeof onFinish!="function"?this.defaultOnFinish:onFinish;var self=this;this.reverseWhile=function(reps){this.stopWatch.start();while(reps>0){this.func();reps--;}
return this.stopWatch.stop();}
this.forLoop=function(reps){this.stopWatch.start();for(i=0;i<reps;i++){this.func();}
return this.stopWatch.stop();}
this.loop=function(kind,reps){if(!this.results[kind]){this.results[kind]=new Array();}
var repsMemberName="r_"+reps;if(!this.results[kind][repsMemberName]){this.results[kind][repsMemberName]=new Array();}
var time=this[kind](reps);this.results[kind][repsMemberName].push(time);}
this.runOnce=function(){if(!this.results.runOnce){this.results.runOnce=new Array();}
this.stopWatch.start();func();this.results.runOnce.push(this.stopWatch.stop());}
this.stopWatch={t_start:0,t_end:0,t_total:0,start:function(){t_start=new Date().getTime();return t_start;},stop:function(){t_end=new Date().getTime();t_total=t_end-t_start;self.time=t_total;return t_total;}}
this.average=function(arr){var sum=0;for(i=0;i<arr.length;i++){sum+=arr[i];}
return sum/arr.length}
this.multiPass=function(passes,type,reps){if(typeof type=="undefined"){type="runOnce";}else if(typeof this[type]=="undefined"){jash.print("Error: the loop type '"+type+"' does not exist");return false;}
var self=this;if(type=="runOnce"){if(passes<1){self.reportProfile(Math.round(this.average(this.results.runOnce)),type,reps);}else{window.setTimeout(function(){self.runOnce();self.multiPass(--passes,type);},50);}}else{if(passes<1){var repsMemberName="r_"+reps;self.reportProfile(Math.round(this.average(this.results[type][repsMemberName])),type,reps);}else{window.setTimeout(function(){self.loop(type,reps);self.multiPass(--passes,type,reps);},50);}}}
this.reportProfile=function(avgMs,type,reps){var line="-------PROFILER----------------------------------------------";var str=line+"\n"+this.func+"\n"+line+"\n";str+="Type of profile: "+type+"\n";if(typeof reps!="undefined"){str+="Loop iterations: "+reps+"\n";}
str+="Average execution time: "+avgMs+"ms"+"\n";if(type=="runOnce"){howManyTimes=this.results.runOnce.length;}else{repsMemberName="r_"+reps;howManyTimes=this.results[type][repsMemberName].length;}
str+="Average calculated from "+howManyTimes+" pass(es)\n";str+=line+"\n";jash.print(str);}}
Jash.TabComplete=function(){this.tabComplete=function(e){e=(typeof window.event!="undefined")?window.event:e;var inputText=this.input.value;var match=null;if(match=this.searchInputForDomGetElFunctions(inputText)){this.tabCompleteIdOrClassInJavascript(match.match[0],match.type);this.focusCaretAtEndOfInput();return false;}else if(this.evaluation.cssEvalFlag){this.tabCompleteIdOrClassInCss(inputText);this.focusCaretAtEndOfInput();return false;}else{this.tabCompleteJavascript(e,inputText);this.focusCaretAtEndOfInput();}}
this.focusCaretAtEndOfInput=function(){this.input.selectionEnd=this.input.selectionStart=this.input.value.length;}
this.tabCompleteJavascript=function(e,inputText){var words=inputText.split(/\s+/);var lastWord=words[(words.length-1)];var numOpeningParens=lastWord.split("(").length-1;var numClosingParens=lastWord.split(")").length-1;var scope;var sentinel=0;var diff=numOpeningParens-numClosingParens;if(diff>0){numClosingParens=lastWord.split("(")[numOpeningParens].split(")").length-1;var numRealDanglers=numOpeningParens-numClosingParens;scope=lastWord.split("(").slice(numRealDanglers).join("(");}else if(diff<0){this.print("error: too many closing parentheses");return false;}else{scope=lastWord;}
scope=scope.split(".");var fragment=scope.pop();scope=scope.join(".");if(scope=="")scope="window";var members=this.getMembers(scope);var results=this.findTextMatchesInArray(members,fragment);if(results==false){}else if(typeof results!="string"){this.dump(results);var bestMatch=this.findBestStringMatch(fragment,results);if(fragment!=''){fragReg=new RegExp(fragment+"$");this.input.value=this.input.value.replace(fragReg,bestMatch);}else{this.input.value+=bestMatch;}}else{var reggie=new RegExp(fragment+"$");this.input.value=this.input.value.replace(reggie,results);}
return false;}
this.doAllStringsInArrayHaveSameCharacterAtIndex=function(index,arr){var matched=0;if(!arr[0].charAt(index))return false;var character=arr[0].charAt(index);for(var i=1;i<arr.length;i++){if(!arr[i].charAt(index)||arr[i].charAt(index)!=character){return false;}}
return true;}
this.findBestStringMatch=function(str,arr){var fragLength=str.length;var matches=this.doAllStringsInArrayHaveSameCharacterAtIndex(fragLength,arr);while(matches){fragLength++;matches=this.doAllStringsInArrayHaveSameCharacterAtIndex(fragLength,arr);}
return arr[0].substr(0,fragLength);}
this.tabCompleteIdOrClassInJavascript=function(inputText,type){var query=inputText.split("(");query=query[query.length-1].replace(/\W/g,'');var matches=new Array();var els=document.getElementsByTagName("*");if(type=="id"){for(var i=0;i<els.length;i++){if(els[i].id&&els[i].id.indexOf(query)==0){matches.push(els[i].id);}}}else if(type=="class"){for(var i=0;i<els.length;i++){if(els[i].className&&els[i].className!=''){var classes=els[i].className.split(/\s/);for(var ii=0;ii<classes.length;ii++){if(classes[ii].indexOf(query)==0||query==''){if(matches.join("***").indexOf(classes[ii])==-1){matches.push(classes[ii]);}}}}}}
if(matches.length==1){this.input.value+=matches[0].split(query)[1];}else if(matches.length==0){this.print("no match");}else{this.dump(matches.sort());var bestMatch=this.findBestStringMatch(query,matches);if(query!=''){var replacement=inputText.split("(");replacement[replacement.length-1]=replacement[replacement.length-1].replace(query,bestMatch);this.input.value=this.input.value.replace(inputText,replacement.join("("));}else{this.input.value+=bestMatch;}}}
this.tabCompleteIdOrClassInCss=function(inputText){var selectors=inputText.replace(/(\.|#)/g,' $1').split(/\s+/);var lastSelector=selectors[selectors.length-1];var els=document.getElementsByTagName("*");var matches=new Array();if(lastSelector.match(/^\./)){for(var i=0;i<els.length;i++){if(els[i].className&&els[i].className!=''){var classes=els[i].className.split(/\s/);for(var ii=0;ii<classes.length;ii++){if(classes[ii].indexOf(lastSelector.substr(1))==0||lastSelector=="."){if(matches.join("***").indexOf(classes[ii])==-1){matches.push("."+classes[ii]);}}}}}}else if(lastSelector.match(/^#/)){for(var i=0;i<els.length;i++){if(els[i].id&&els[i].id.indexOf(lastSelector.substr(1))==0){matches.push("#"+els[i].id);}}}
if(matches.length==1){this.input.value+=matches[0].split(lastSelector)[1];}else if(matches.length==0){this.print("no match");}else{this.dump(matches.sort());var bestMatch=this.findBestStringMatch(lastSelector,matches);if(lastSelector!=''){this.input.value=this.input.value.replace(lastSelector,bestMatch);}else{this.input.value+=bestMatch;}}}
this.searchInputForDomGetElFunctions=function(inputText){for(var i=0;i<this.domGetElFunctions.id.length;i++){var selfct=new RegExp(this.domGetElFunctions.id[i].replace("\$","\\\$")+"\\\(['\"]\\w*$");if(inputText.match(selfct)){return{match:inputText.match(selfct),type:"id"};}}
for(var i=0;i<this.domGetElFunctions.className.length;i++){var selfct=new RegExp(this.domGetElFunctions.className[i].replace("\$","\\\$")+"\\\(['\"]\\w*$");if(inputText.match(selfct)){return{match:inputText.match(selfct),type:"class"};}}}
this.findTextMatchesInArray=function(arrayToTest,findMe){var resultsArray=new Array();var tester=new RegExp("^"+findMe);for(var i=0;i<arrayToTest.length;i++){if(tester.test(arrayToTest[i])){resultsArray.push(arrayToTest[i]);}}
if(resultsArray.length>1){resultsArray.sort();return resultsArray;}else if(resultsArray.length==1){return resultsArray[0];}else{return false;}}
this.getMembers=function(context){var members=new Array();for(memberName in eval(context)){members.push(memberName);}
return members;}
return this;}
new function(){if("jash"in window){window.jash.close();}else{window.jash=new Jash();window.jash.main();}}