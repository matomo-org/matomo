function Jash() {
	/* location of source code (used to find css file) */
	this.jashRoot = "http://www.billyreisinger.com/jash/source/latest/";

	/* functions that take element ids or class names as pricincipal arguments */
	this.domGetElFunctions = {
		id: new Array("document.getElementById","$"),
		className: new Array("getElementsByClassName","$C")
	};

	/* output line separator for major blocks of content */
	var line = "-------------------------------------------------";

	/* this is returned by internal methods to avoid printing null output */
	var _null = "nooutput";
	var self = this;

	this.version = "1.35.7";
	this.versionDate = "2009/09/05 09:10";

	/**
	* Set environment, create HTML
	*/
	this.main = function() {
		this.browser = this.returnBrowserType();		/* User's browser type */
		this.lineNumber = 0;					/* Current output line number */
		this.mainBlock;						/* HTML element parent wrapper */
		this.output = document.getElementById("JashOutput");	/* HTML element for console output */
		this.input;						/* HTML element for user input */
		this.outputHistory = new Array();			/* All output is stored here */
		this.cssEvalFlag = false; 				/* flag: are we in CSS input mode? */
		this.innerHtmlInspection = false;
		this.accessKeyText = this.getAccessKeyText();
		this.defaultText = "Jash, v" + this.version + "\nEnter \"jash.help()\" for a list of commands.\n";
		this.cls = this.clear;					/* clear function alias */
		this.tabIndexIndex = 0;
		this.currentNode = {};
		this.triedDomInserts = 0;
		this.tips = [
			"Did you know?\nThe DOM Inspector will automatically put\n an element with an ID in the input field for you.",
			"Did you know?\nYou can tie this script into your own to jash scripts. Use 'jash.methodName' anywhere\n in your scripts, and pull\n up this window before executing to see\n the results.",
			"Did you know?\nUse jash.stopWatch.start() and jash.stopWatch.stop() to\n time execution speeds! Handy for optimization.",
			"Did you know?\nPress TAB to complete a function, method, or property name.\n If more than one match is found, a list of possible\n matches will appear.",
			"Did you know?\nYou can use jash.show() to show a list of the names\nand types of an object's members.\nOn the other hand, jash.dump will show names and\n_values_ of an object's members.",
			"Whoa ---- you can now tab-complete HTML element ids after typing document.getElementsById(' (or the '$' shorthand if using Prototype).  This also works with class names (i.e. document.getElementsByClassName)"
		]
		this.defaultText += line + "\n" + this.tips[(parseInt((Math.random()*10)%this.tips.length))] + "\n" + line + "\n";
		this.loopOnDomInserts();
	}

	this.loopOnDomInserts = function() {
		try {
			self.testDomInsert();
		} catch(e) {
			self.triedDomInserts++;
			if(self.triedDomInserts < 30) {
				window.setTimeout(self.loopOnDomInserts, 250);
			}
			return;
		}
		document.body.removeChild(document.getElementById("JashTestElement"));
		self.doDomInserts();
		self.finishInit();
	}

	this.testDomInsert = function() {
		document.body.appendChild(document.createElement("em")).id = "JashTestElement";
	}
	this.finishInit = function() {
		/* create tab complete object */
		Jash.TabComplete.prototype = this;
		this.tabComplete = new Jash.TabComplete();

		/* create new evaluation instance */
		Jash.Evaluator.prototype = this;
		this.evaluation = new Jash.Evaluator();

		/* create new history object */
		this.history = new Jash.History();
		window.setTimeout(function() {
			self.input.focus();
		},500);
	}

	/**
	 * Import stylesheet and insert dom nodes
	 */
	this.doDomInserts = function() {
/*
		if(self.returnBrowserType() != "sa") {
			self.stylesheet=document.body.appendChild(document.createElement('link'));
		} else {
			self.stylesheet = document.getElementsByTagName("head")[0].appendChild(document.createElement("link"));
		}
		self.stylesheet.type='text/css';
		self.stylesheet.rel='stylesheet';
		self.stylesheet.href=self.jashRoot +'Jash.css';
*/
		self.create();
	}

	/**
	* return string representing browser type
	*/
	this.returnBrowserType = function() {
		if(window.navigator.userAgent.toLowerCase().indexOf("opera") != -1) {
			return "op";
		}
		if(window.navigator.userAgent.toLowerCase().indexOf("msie") != -1) {
			return "ie";
		}
		if(window.navigator.userAgent.toLowerCase().indexOf("firefox") != -1) {
			return "ff";
		}
		if(window.navigator.userAgent.toLowerCase().indexOf("safari") != -1) {
			return "sa";
		}
	}
	/**
	* return string representing os
	*/
	this.returnOsType = function() {
	    	var ua = window.navigator.userAgent.toLowerCase();
		if(ua.indexOf("macintosh") != -1) {
		    return "mac";
		} else if(ua.indexOf("windows") != -1) {
		    return "win";
		} else if(ua.indexOf("linux i686") != -1) {
			return "linux";
		}
	}

	/**
	* return access key text based on what browser we're using. Access keys are
	* different for every browser, and even between the same browsers on
	* different platforms.
	*/
	this.getAccessKeyText = function() {
	    var txt;
	    var agt = this.returnOsType();
	    switch(this.browser) {
		case "ie":
			txt = "Alt";
			break;
		case "ff":
			/* FF/Win = alt/shift; FF/Mac = ctrl; FF/Linux/x86 = alt */
			if (agt == "mac") {
				txt = "Ctrl";
			} else if(agt == "linux") {
				txt = "Alt";
			} else {
				txt = "Alt-Shift";
			}
			break;
		case "op":
			txt = "Shift-Esc";
			break;
		case "sa":
			if(agt == "mac") {
				txt = "Ctrl";
			} else {
				txt = "Alt";
			}
			break;
		default:
			txt = "Alt";
			break;
	    }
	    return txt;
	}

	/**
	* Print simple output to the console
	* @param 	{string}	text			text to print
	* @param	{bool} 		clear			clear console before printing, default is false
	* @param	{bool} 		suppressLineNumbers	print line number before text, default is true
	* @param	{bool}		autoscroll		scroll output console to bottom when printing
	*/
	this.print = function(text,clear,suppressLineNumbers,autoscroll) {
		clear = (typeof clear != "undefined") ? clear : false;
		autoscroll = (typeof autoscroll != "undefined") ? autoscroll : true;

		if(this.output == null || document.getElementById("JashParent") == null) {
			this.create();
			this.output = document.getElementById("JashOutput");
			this.mainBlock = document.getElementById("JashParent");
		}
		if(clear) {
			this.clear();
		}
		if(text != "") {
			if(typeof suppressLineNumbers != "undefined" && !suppressLineNumbers) {
				this.output.value += this.lineNumber + ". ";
			}
			this.output.value += text + "\n";
			if(autoscroll) {
				this.output.scrollTop = this.output.scrollHeight;
			}
			this.lineNumber++;
		}
		return _null;
	}
	/**
	* Show terse output (name and type) of an object's members
	* @param {object}	obj	an object whose members are to be shown
	* @returns {string} 	_null
	*/
	this.show = function(obj) {
		this.print(line,false,true);
		var out = "";
		this.lineNumber = 0;

		for(var p in obj) {
			if(typeof obj[p] == "function") {
				var t = obj[p].toString();
				t = t.replace(/[\x0A\x0D]/g,"").replace(/\s+/g,"").replace(/\{.+\}/g,"{ ... }");
				t = t.replace(p,"");
				t = p + ": " + t;
			} else {
				t = p + ": " + typeof obj[p];
			}
			out += ++this.lineNumber + ". " + t + "\n";
		}
		this.print(out,false,true);
		this.print(line,false,true);
		this.output.scrollTop = this.output.scrollHeight;
		return _null;
	}

	/**
	* Dump - show verbose output of all of an object's members
	* @param 	{object}	obj			object whose members should be dumped
	* @returns						_null or other string, see above
	*/
	this.dump = function(obj) {
		if(typeof obj == "string") {
			this.print(obj);
		} else {
			this.print(line,false,true);
			var out = new Array();
			/* object */
			if(typeof obj.push == "undefined") {
				for(var th in obj) {
					out.push(++this.lineNumber + ". " + th + " = " + obj[th]);
				}
			/* array */
			} else {
				for(var i = 0; i<obj.length; i++) {
					out.push(++this.lineNumber + ". " + obj[i]);
				}
			}
			this.print(out.join("\n"),false,true);
			this.print(line,false,true);
			this.output.scrollTop = this.output.scrollHeight;
		}
		return _null;
	}
	/**
	* Clear output console
	*/
	this.clear = function() {
	    	this.outputHistory.push(this.output.value);
		this.output.value = "";
		this.input.focus();
		return _null;
	}

	/**
	* Shows everything that has gone in the output console during this session
	*/
	this.showOutputHistory = function() {
	    this.outputHistory.push(this.output.value);
	    this.dump(this.outputHistory);
	}

	/**
	* Map input keystrokes
	* @param {int} keyCode		number representing keycode of key pressed in event object
	*/
	this.assignInputKeyEvent = function(event) {
		var keyCode = event.keyCode;
		/* Enter key */
		if(keyCode == 13 && !event.shiftKey) {
			this.evaluation.evaluate(this.input.value);
			this.input.value = "";
			return false;
		/* Up key */
		} else if(keyCode == 38 && !event.shiftKey) {
			if(this.browser != "op") {
				this.input.value = this.history.getPreviousInput();
			}
			return false;
		/* Down key */
		} else if(keyCode == 40) {
			if(this.browser != "op") {
				this.input.value = this.history.getNextInput();
			}
			return false;
		/* Tab key */
		} else if(keyCode == 9) {
			this.tabComplete.tabComplete();
			return false;
		}
	}

	/**
	* Get the Y scrolling offset of the current page for whatever browser
	* @returns {int} 	Y scrolling offset of current page
	*/
	this.getXBrowserYOffset = function() {
		var y;
		if (self.pageYOffset) {
			y = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop) {
			y = document.documentElement.scrollTop;
		} else if (document.body) {
			y = document.body.scrollTop;
		}
		return y;
	}
	/**
	* Get mouse position in pixels
	* @param {object} e	event object
	* @returns {object} 	[x,y] representing mouse position on screen in px
	*/
	this.getMouseXY = function(e) {
		/*  Temporary variables to hold mouse x-y pos.s */
		var tempX = 0
		var tempY = 0

		/*  IE */
		if (window.event) {
		    /* doctype present in IE6/7 */
		    if(document.documentElement && document.documentElement.scrollTop) {
			    tempX = window.event.clientX + document.documentElement.scrollLeft;
			    tempY = window.event.clientY + document.documentElement.scrollTop;
		    } else {
			tempX = window.event.clientX + document.body.scrollLeft;
			tempY = window.event.clientY + document.body.scrollTop;
		    }
		} else {  /*  grab the x-y pos.s if browser is NS */
			tempX = e.pageX;
			tempY = e.pageY;
		}

		return {x:tempX,y:tempY};
	}
	/**
	* Get the pixel dimensions of any given HTML object
	* @param {HTML Element} el	an HTML element
	* @returns {object}		[x,y] representing object width, height
	*/
	this.getDimensions = function(el) {
		var dims = {}
		if(document.all) {
			dims.x = el.offsetWidth;
			dims.y = el.offsetHeight;
		} else {
			dims.x = parseInt(document.defaultView.getComputedStyle(el,"").getPropertyValue("width"));
			dims.y = parseInt(document.defaultView.getComputedStyle(el,"").getPropertyValue("height"));
		}
		return dims;
	}
	/**
	* Cross-browser DOM 2 event handler assignment - calls 'func' on 'eventName' in 'obj'
	* @param {HTML Element} obj	HTML Element on which to listen for eventName
	* @param {string} eventName	event name without "on", i.e., "click"
	* @param {function} func	function to assign as handler for eventName on obj
	*/
	this.addEvent = function(obj, eventName, func) {
		if(obj.addEventListener)
			return obj.addEventListener(eventName, func, true);
		else if(obj.attachEvent) {
			obj.attachEvent("on" + eventName, func);
			return true;
		}
		return false;
	}
	/**
	* Find top, left pixel offset of HTML element relative to window
	* @param {HTML Element} obj	an HTML element to calculate offset of
	* @returns {array} 		[x,y] offset of html element 'obj'
	*/
	this.findElementPosition = function(obj) {
		var curleft = 0 ;
		var curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft
			curtop = obj.offsetTop
			while (obj = obj.offsetParent) {
				curleft += obj.offsetLeft
				curtop += obj.offsetTop
			}
		}
		return [curleft,curtop];
	}

	/**
	* Create HTML necessary for Debugger, assign events to buttons and window
	*/
	this.create = function() {
		if(document.getElementsByTagName("frameset").length > 0) {
			alert("Jash currently does not support pages with frames.");
			return;
		}
		var self = this;

		/* outermost container */
		var debugParent = document.createElement("div");
		var windowScrollY = 0;

		if (document.documentElement && document.documentElement.scrollTop) {
			windowScrollY = document.documentElement.scrollTop;
		} else if (document.body) {
			windowScrollY = document.body.scrollTop
		} else {
			windowScrollY = window.scrollY;
		}
		debugParent.style.top = windowScrollY + 50 + "px";
		debugParent.id = "JashParent";

		/* close on ESC key press */
		this.addEvent(document,"keydown", function(e) {
			e = (typeof window.event != "undefined") ? window.event : e;
			if (parseInt(e.keyCode) == 27) {
			    /* in Opera, shift-esc is precursor to access key usage */
			    if(typeof e.shiftKey == "undefined" || !e.shiftKey) {
					self.close();
			    }
			}
		});

		/* WRAPPERS FOR TEXTAREAS */
		var textareaWrap = document.createElement("div");
		textareaWrap.id = "JashTextareaWrap";

		/* OUTPUT FIELD */
		var debugOutput = document.createElement("textarea");
		debugOutput.id = "JashOutput";
		debugOutput.wrap = "off";
		debugOutput.readOnly = "true";
		debugOutput.value = this.defaultText;

		/* INPUT FIELD */
		var inp = document.createElement("textarea");
		inp.id = "JashInput";
		var last = "";

		/* listen for certain keystrokes, map them */
		inp.onkeydown = function(e) {
			e = (typeof window.event != "undefined") ? window.event : e;
			return self.assignInputKeyEvent(e);
		}
		/* Supress certain keystrokes */
		inp.onkeypress = function(e) {
			e = (typeof window.event != "undefined") ? window.event : e;
			var k = e.keyCode;
			/* suppress certain key strokes */
			if(!self.evaluation.cssEvalFlag) {
				/* tab or return or up or down */
				if(k==9 || (k==13 && !e.shiftKey) || (k==38 && !e.shiftKey) || k==40) {
					if(k!=40 && this.browser != "ie") {
						return false;
					}
				}
			/* suppress tabs in css mode */
			} else if(k==9) {
				return false;
			}
		}

		/* DRAG / TITLE BAR */
		var dragBut = document.createElement("div");
		dragBut.innerHTML = "Jash";
		dragBut.id = "JashDragBar";
		dragBut.onmousedown = function(e) {
			e = (typeof window.event != "undefined") ? window.event : e;
			var xplus = (typeof e.layerX == "undefined") ? e.offsetX : e.layerX;
			var yplus = (typeof e.layerY == "undefined") ? e.offsetY : e.layerY;
			document.onmousemove = function(e) {
				var coords = self.getMouseXY(e);
				document.getElementById("JashParent").style.top = coords.y - yplus + "px";
				document.getElementById("JashParent").style.left = coords.x - xplus + "px";
			}
			return false;
		}
		document.onmouseup = function() {
			document.onmousemove = null;
		};
		/* cancel click event to prevent text selection */
		dragBut.onclick = function() { return false; }

		/**
		* BUTTONS
		*/
		/* CLOSE BUTTON (SMALL ONE) */
		var xBut = document.createElement("a");
		xBut.className = "JashXButton";
		xBut.innerHTML = "X";
		xBut.href = "#";
		xBut.onclick = function() {
		    self.close();
		    return false;
		}

		/* CLEAR BUTTON */
		var clearBut = document.createElement("a");
		clearBut.innerHTML = "Clear (" + this.accessKeyText + "-C)";
		clearBut.accessKey = "C";
		clearBut.className = "JashButton";
		clearBut.onclick = function() {
			self.clear();
			return false;
		}
		this.setCrossBrowserAccessKeyFunctionForAnchor(clearBut);

		/* EVALUATE BUTTON */
		var evalBut = document.createElement("a");
		evalBut.value = "Evaluate (" + this.accessKeyText + "-Z)";
		evalBut.innerHTML = "Evaluate (" + this.accessKeyText + "-Z)";
		evalBut.accessKey = "Z";
		evalBut.className = "JashButton";
		evalBut.title = "Evaluate current input (" + this.accessKeyText + "-Z)";
		evalBut.onclick = function() {
			self.evaluation.evaluate(inp.value);
			if(!self.evaluation.cssEvalFlag) {
				inp.value = "";
			}
			inp.focus();
			return false;
		}
		this.setCrossBrowserAccessKeyFunctionForAnchor(evalBut);

		/* HELP BUTTON */
		var helpBut = document.createElement("a");
		helpBut.innerHTML = "Help";
		helpBut.className = "JashButton";
		helpBut.title = "Help: show list of commands (or type jash.help(); )";
		helpBut.onclick = function() {
			self.help();
		}

		/* DOM BUTTON */
		var domBut = document.createElement("a");
		domBut.innerHTML = "Mouseover DOM (" + this.accessKeyText + "-X)";
		domBut.title = "Mouseover DOM: toggle to turn on/off inspection of document nodes (" + this.accessKeyText + "-X)";
		domBut.className = "JashButton";
		domBut.accessKey = "X";
		domBut.tabIndex = "4";
		this.domActive = false;
		domBut.onclick = function() {
			if(!self.domActive) {
				document.body.onmouseover = function(e) {
					if(typeof e == "undefined") { e = window.event; }
					self.showNodes(e);
				}
				self.setButtonVisualActiveState(domBut,"on");
				self.domActive = true;
			} else {
				document.body.onmouseover = function() {}
				self.domActive = false;
				self.setButtonVisualActiveState(domBut,"off");
			}
			return _null;
		}
		this.setCrossBrowserAccessKeyFunctionForAnchor(domBut);

		/* INNER HTML INSPECT BUTTON */
		var innerHtmlInspectBut = document.createElement("a");
		innerHtmlInspectBut.innerHTML = "innerHTML Dump (" + this.accessKeyText + "-A)";
		innerHtmlInspectBut.title = "innerHTML Inspect: toggle to turn on/off innerHTML inspection of document nodes (" + this.accessKeyText + "-A)";
		innerHtmlInspectBut.className = "JashButton";
		innerHtmlInspectBut.accessKey = "A";
		innerHtmlInspectBut.tabIndex = "5";
		this.innerHtmlInspection = false;
		innerHtmlInspectBut.onclick = function() {
			self.innerHtmlInspection = !self.innerHtmlInspection;
			self.setButtonVisualActiveState(innerHtmlInspectBut,self.innerHtmlInspection ? "on" : "off");
			return _null;
		}
		this.setCrossBrowserAccessKeyFunctionForAnchor(innerHtmlInspectBut);

		/* CSS BUTTON  */
		var cssBut = document.createElement("a");
		cssBut.innerHTML = "CSS Input (" + this.accessKeyText + "-S)";
		cssBut.title = "CSS Input: turn on CSS input to enter arbitrary CSS (" + this.accessKeyText + "-S)";
		cssBut.className = "JashButton";
		cssBut.accessKey = "S";
		cssBut.onclick = function() {
			if(!self.evaluation.cssEvalFlag) {
				self.setButtonVisualActiveState(cssBut,"on");
				self.evaluation.cssEvalFlag = true;
				inp.className = "cssEntry";
				if(document.getElementById("JashStyleInput") != null) {
					self.evaluation.styleInputTag.disabled = false;
				}
				inp.value = "";
			} else {
				self.setButtonVisualActiveState(cssBut,"off");
				inp.className = "";
				self.evaluation.cssEvalFlag = false;
				if(document.getElementById("JashStyleInput") != null) {
					self.evaluation.styleInputTag.disabled = true;
				}
				inp.value = "";
			}
			inp.focus();
			return _null;
		}
		this.setCrossBrowserAccessKeyFunctionForAnchor(cssBut);

		/* RESIZE BUTTON */
		var resizeBut = document.createElement("div");
		resizeBut.id = "JashResizeButton";
		this.minDims = { x:100,y:100 };
		resizeBut.onmousedown = function(e) {
			e = (typeof window.event != "undefined") ? window.event : e;
			var originalDims = self.getDimensions(textareaWrap);
			var originMouseDims = self.getMouseXY(e);
			document.onmousemove = function(e) {
				var newMouseDims = self.getMouseXY(e);
				var newWidth = originalDims.x + (newMouseDims.x - originMouseDims.x);
				if(newWidth < self.minDims.x) { newWidth = self.minDims.x; }
				textareaWrap.style.width = newWidth + "px";
				debugParent.style.width = newWidth + "px";

				var newHeight = originalDims.y + (newMouseDims.y - originMouseDims.y);
				if(newHeight < self.minDims.y) { newHeight = self.minDims.y; }
				textareaWrap.style.height = newHeight + "px";
				debugParent.style.height = newHeight + "px";
			}
			document.onmouseup = function() {
				document.onmousemove = "";
			}
		}

		var bottomBar = document.createElement("div");
		bottomBar.id = "JashBottomBar";

		/* append nodes to DOM */

		debugParent.appendChild(dragBut);
		debugParent.appendChild(xBut);

		bottomBar.appendChild(evalBut);
		bottomBar.appendChild(cssBut);
		bottomBar.appendChild(domBut);
		bottomBar.appendChild(innerHtmlInspectBut);
		bottomBar.appendChild(clearBut);
		bottomBar.appendChild(helpBut);
		debugParent.appendChild(bottomBar);

		debugParent.appendChild(resizeBut);
		document.body.appendChild(debugParent);

		/* the textareas should be last to get w/h calculated correctly */
		textareaWrap.appendChild(debugOutput);
		textareaWrap.appendChild(inp);
		debugParent.appendChild(textareaWrap);

		this.bottomBar = document.getElementById("JashBottomBar");
		this.dragBar = document.getElementById("JashDragBar")
		this.output = document.getElementById("JashOutput");
		this.input = document.getElementById("JashInput");
		this.mainBlock = debugParent;

		/* When user scrolls page, move debug window, too */
		this.addEvent(window,'scroll',function() {
			debugParent.style.top = 50 + self.getXBrowserYOffset() + 'px';
		});
	}

	/**
	* set the visual state of a button
	* @param {HTML Element} button		element to change visual state of
	* @param {string} state			"on" | "off"
	*/
	this.setButtonVisualActiveState = function(button,state) {
		if(state == "on") {
			button.style.backgroundColor = "lightgreen";
		} else {
			button.style.backgroundColor = "";
		}
	}
	/**
	* Print some useful information
	*/
	this.help = function() {
		var out = new Array();
		out.push(line);
		out.push("Jash v" + this.version + " " + this.versionDate,true);
		out.push("http://www.billyreisinger.com/jash/documentation.html");
		out.push(line);
		out.push("METHODS");
		out.push(line);
		out.push("jash.cls() - clear console");
		out.push("jash.print(str,clear) - output str to console ~~ str = string ~~ clear = true|false: clear console before output");
		out.push("jash.close() - close this console");
		out.push("jash.dump(obj) - output object and members to console");
		out.push("jash.show(obj) - print out the names and types (only) of all members of obj");
		out.push("jash.stopWatch.start() - start timer");
		out.push("jash.stopWatch.stop() - end timer and return result in ms");
		out.push("jash.kill(HTML Element) - remove an element from the page.");
		out.push("jash.getDimensions(HTML Element) - get width, height dimensions of an html element. Returns an object [x,y]");
		out.push(line);
		out.push("KEYSTROKES");
		out.push(line);
		out.push("press up arrow in input field to retrieve last input");
		out.push("press ESC to show/hide console");
		out.push("press ENTER in input field to enter a command");
		out.push("press TAB to auto-complete input");
		out.push("press " + this.accessKeyText + "-Z to evaluate input");
		out.push("press " + this.accessKeyText + "-X to activate/deactivate DOM inspector");
		out.push("press " + this.accessKeyText + "-A to activate/deactivate innerHTML dump (only works w/ DOM inspector)");
		out.push("press " + this.accessKeyText + "-C to clear output and input");
		out.push("press " + this.accessKeyText + "-S to turn on/off CSS input mode. In CSS input mode, you can enter arbitrary CSS selectors and rules, as you would normally do in a CSS stylesheet.");

		this.print(out.join("\n"));

		return _null;
	}
	/**
	* show/hide Jash
	*/
	this.close = function() {
		if(this.mainBlock.style.display == "none") {
			this.mainBlock.style.display = "block";
			this.input.focus();
		} else {
			this.mainBlock.style.display = "none";
		}
	}

	/**
	* Cross-browser access key
	* @param {HTML Element} el	element to simulate access key event on
	*/
	this.setCrossBrowserAccessKeyFunctionForAnchor = function(el) {
		var self = this;
		el.tabIndex = ++this.tabIndexIndex;
		/* IE only focuses on anchors with access keys, but FF fires click.  */
		if(this.browser == "ie") {
			el.onfocus = function() {
				/* access key is being used; fire button's click event */
				if(window.event.altKey) {
					el.onclick();
				}
				self.input.focus();
			}
		}

	}

	/**
	* Time execution in ms
	*/
	this.stopWatch = {
		t_start: 0,
		t_end: 0,
		t_total: 0,
		/**
		* Start the timer
		* @returns {int} 	epoch time in ms
		*/
		start: function() {
			t_start = new Date().getTime();
			return t_start;
		},
		/**
		* Stop the timer
		* @returns {int} 	time between start and stop in ms
		*/
		stop: function() {
			t_end = new Date().getTime();
			t_total = t_end - t_start;
			return (t_total);
		}
	}

	/**
	* DOM inspection: Show parent node structure, and possibly innerHTML, of node
	* under mouse cursor.
	* @param {object} e 	Event object
	*/
	this.showNodes = function(e) {
		if(typeof e == "undefined") e = window.event;
		var el = typeof e.target == "undefined" ? e.srcElement : e.target;
		/* store first node for later use */
		this.currentNode = el;

		/* see what first node is */
		var childMost = this.identifyNode(el,false);

		/* step through parent nodes */
		var out = "";
		var childmostTxt = "childmost..... " + childMost.txt + "\n";
		while(el = el.parentNode) {
			if(el.nodeName.toLowerCase() == "html") {
				out = "parentmost.... <html>\n" + out;
				break;
			}
			out = this.identifyNode(el).txt + "\n" + out;
		}
		out = "**** PRESS " + this.accessKeyText + "-X TO PAUSE / UNPAUSE ****\n" + out;
		out += childmostTxt;
		this.print(out,true,true,false);
		if(this.innerHtmlInspection) {
			this.print("INNER HTML");
			if(this.currentNode.innerHTML.indexOf("<") != -1) {
				this.print(Jash.Indenter.indent(this.currentNode.innerHTML),false,true,false);
			} else {
				this.print(this.currentNode.innerHTML,false,true,false);
			}
		}

		if(!this.evaluation.cssEvalFlag) {
			if(childMost.id != "") {
				if(typeof $ != "undefined") {
					this.input.value = '$("' + childMost.id + '")';
				} else {
					this.input.value = 'document.getElementById("' + childMost.id + '")';
				}
			} else {

				this.input.value = "this.currentNode";
			}
		}
	}

	/**
	* Return a string containing information about HTML element 'el' - node name, id, class, etc.
	* @param {HTML Element} el	Element to inspect
	* @param {bool} showDots	precede returned text with dots
	* @returns {object} 		{txt: string <node class="" id="">,id: string elementId}
	*/
	this.identifyNode = function(el,showDots) {
		showDots = typeof showDots == "boolean" ? showDots : true;

		var out = {
			txt: "",
			id: ""
		};

		if(showDots) out.txt += ".............. ";
		out.txt += "<" + el.nodeName.toLowerCase();
		if(el.id != "") {
			out.id = el.id;
			out.txt += ' id="' + el.id + '"';
		}
		if(el.name) {
		    out.txt += ' name="' + el.name + '"';
		}
		if(el.className !="") {
			out.txt +=  ' class="' + el.className + '"';
		}
		if(el.href) {
		    out.txt += ' href="' + el.href + '"';
		}

		out.txt += ">";

		return out;
	}
	/**
	* Remove node under cursor
	*/
	this.kill = function() {
		this.currentNode.parentNode.removeChild(this.currentNode);
	}
}
/**
* Class to evaluate input text as javascript or CSS
* @class Jash.Evaluator
* @inherits Jash
* @returns {object} 	a new copy of Evaluator
*/
Jash.Evaluator = function() {
	/* are we in CSS-edit mode? bool */
	this.cssEvalFlag = false;
	/* this is returned by internal methods to avoid printing null output */
	var _null = "nooutput";
	/**
	* Delegate evaluation of input string appropriately
	* @param {string} input 	input string to evaluate
	*/
	this.evaluate = function(input) {
		if(input == "") return false;
		this.history.add(input);
		if(this.cssEvalFlag) {
			this.evalCss(input);
			this.print(input);
		} else {
			var output = this.evalJs(input);
			if(typeof output != "undefined") {
				this.print(">> " + input);
				this.print(output);
			}
		}
	}
	/**
	* Evaluate 'input' string as javascript
	* @param {string} input		input string to evaluate as javascript
	* @returns {string} 		result of evaluation (or undefined if this.returnInsteadOfPrint is true)
	*/
	this.evalJs = function(input) {
		try {
			var result;
            if(this.browser == "ie") {
             result = eval(input);
            } else {
             result = window.eval(input);
            }
			if(result !== null && result.toString() != _null) {
				return(result.toString());
			} else {
				return "null"
			}
		} catch(e) {
			return(e.message);
		}
	}
	/**
	* evaluate 'input' string as css
	* @param {string} input		an input string to evaluate as css (selector(s) followed by rules)
	* @returns {sring} 		the input string unmodified
	*/
	this.evalCss = function(input) {
		try {
			this.insertStyleRule(input);
		} catch (e) {
			//input = e.message;
		}
		return input;
	}
	/**
	* Write style rule in stylesheet
	* @param {string} rule 		a series of selectors and rules separated by the newline character '\n'
	* @returns {string} 		empty string
	*/
	this.insertStyleRule = function(rule) {
		var lastStyleSheetIndex = document.styleSheets.length - 1;
		if(document.getElementById("JashStyleInput") == null) {
			this.styleInputTag = document.createElement("style");
			this.styleInputTag.id = "JashStyleInput";
			this.styleInputTag.type = "text/css";
			document.body.appendChild(this.styleInputTag);
		}
		if(this.browser == "ff" || this.browser == "op") {
			/* wow, I can't believe this works in FF and Opera. It shouldn't */
			this.styleInputTag.innerHTML += rule + "\n";
		} else if (this.browser == "ie" || this.browser == "sa") {
			/* in IE, stylesheets are added to the top of the stack */
			if(this.browser == "ie") {
				var i = 0;
			} else if (this.browser = "sa") {
				var i = document.styleSheets.length - 1;
			}
			/* create array of rules */
			var rulesArray = rule.split("}");
			for(var t = 0; t < rulesArray.length; t++) {
				var ruleSplit = rulesArray[t].split("{");
				/* IE wont take multiple selectors in one rule in addRule */
				var selectors = ruleSplit[0].split(",");
				for(var k = 0; k < selectors.length; k++) {
					document.styleSheets[i].addRule(selectors[k],ruleSplit[1]);
				}
			}
		}
		return "";
	}
	return this;
}

/**
* Store input for later retrieval.  Provide methods for retrieving input in a
* linear fashion.
* @class	Jash.History
*/
Jash.History = function() {
	/* Array where entries will be stored */
	this.entries = new Array('');
	this.position = 0;
}
Jash.History.prototype = {
	/**
	* Add input string to history array
	* @param {string} input		input to add to history
	*/
	add: function(input) {
		this.entries.push(input);
		this.position = this.entries.length - 1;
	},
	/**
	* Find the previous input in history relative to current position
	* @returns {string} 	blank if no history value, or string
	*/
	getPreviousInput: function() {
		if(this.position < 0) {
			return '';
		}
		var entry = typeof this.entries[this.position] != "undefined" ? this.entries[this.position] : '';
		if(this.position > 0) {
			this.position--;
		}
		return entry;
	},
	/**
	* Get the next input string in history relative to the current position
	* @returns {string} 	blank if no history value, or string
	*/
	getNextInput: function() {
		if(this.position < this.entries.length) {
			var entry = typeof this.entries[this.position] != "undefined" ? this.entries[this.position] : '';
			if(this.entries.length <= this.position++) {
				this.position++;
			}
			return entry;
		} else return '';
	}
}
/**
* Indent, add line breaks, and close tags in an HTML string
* Example usage:
* <pre>
* Jash.Indenter.indent(document.getElementById("someDiv").innerHTML);
* </pre>
*
* @class Jash.Indenter
*/
Jash.Indenter = {
	indentChar: "\t",
	nodesCommonlyUnclosed: new Array("link ", "img ", "meta ", "!DOCTYPE ", "input ", "param", "hr", "br"),
	/**
	* repeat stringToRepeat times times and return concatenated string with no separator
	* @param {string} stringToRepeat	a string that should be repeated times times
	* @param {int} times 			number of times to repeat string
	* @returns {string}			string repeated times times
	*/
	stringRepeat: function(stringToRepeat,times) {
		var string = new Array();
		for(var i = 0; i < times; i++) {
			string.push(stringToRepeat);
		}
		return string.join('');
	},
	/**
	* Find unclosed tags (a list of which is in this.nodesCommonlyUnclosed) in str and
	* close them.
	* @param {string} str 	string representing one node
	* @returns {str} 		string with tag(s) closed
	*/
	closeUnclosedNode: function(str) {
 		for(var k=0;k<this.nodesCommonlyUnclosed.length;k++) {
			var reg = new RegExp("^" + this.nodesCommonlyUnclosed[k].toLowerCase());
			if(str.toLowerCase().match(reg)) {
				return str.replace(">","/>");
			}
		}
		return str;
 	},
	/**
	* Indent a text string level times and add it to arr
	* @param {int} level		number of times to indent string
	* @param {string} string	string to indent
	* @param {Array} arr 		array of indented strings (i.e., result set)
	* @returns {Array} 			array "arr" with new entry
	*/
	indentAndAdd: function(level,string,arr) {
		var indents = this.stringRepeat(this.indentChar,level);
		arr.push(indents + string);
		return arr;
	},
	/**
	* indent string source and return indented result
	* @param {string} source	a string representing unformatted HTML
	* @returns {string} 		prettified HTML
	*/
	indent: function(source) {
		var source = source;
		var arr = new Array();

		/* remove new lines and tabs */
		source = source.replace(/[\n\r\t]/g, '');
		/* remove spaces before and after html tags */
		source = source.replace(/>\s+/g, ">");
		source = source.replace(/\s+</g, "<");

		/* Close some nodes */
		var splitsrc = source.split("<");
		for(i=0;i<splitsrc.length;i++) {
			splitsrc[i] = this.closeUnclosedNode(splitsrc[i]);
		}
		source = splitsrc.join("<");

		/* indent code */
		var level = 0;
		var sourceLength = source.length;
		var position = 0;
		while (position < sourceLength) {
			if (source.charAt(position) == '<') {
				var startedAt = position;
				var tagLevel = 1;
				if (source.charAt(position+1) == '/') {
					tagLevel = -1;
				}
				if (source.charAt(position+1) == '!') {
					tagLevel = 0;
				}
				while (source.charAt(position) != '>') {
					position++;
				}
				if (source.charAt(position-1) == '/') {
					tagLevel = 0;
				}
				var tagLength = position+1-startedAt;
				if (tagLevel === -1) {
					level--;
				}
				arr = this.indentAndAdd(level,source.substr(startedAt,tagLength),arr);
				if (tagLevel === 1) {
					level++;
				}
			}
			if ((position+1) < sourceLength) {
				if (source.charAt(position+1) !== '<') {
					startedAt = position+1;
					while (source.charAt(position) !== '<' && position < sourceLength) {
						position++;
					}
					if (source.charAt(position) === '<') {
						tagLength = position-startedAt;
						arr = this.indentAndAdd(level,source.substr(startedAt,tagLength),arr);
					}
				} else {
					position++;
				}
			} else {
				break;
			}
		}
		return arr.join("\n");
	}
}

/**
* Time exectuion of a given function.  Store results and report average
* resuls.  Allow single or multiple-pass execution using a variety of
* loop styles.
* Example usage:
* <pre>
* var profile = new Jash.Profiler(function() {
*	document.getElementById("something");
* });
* profile.multiPass(1000);
* </pre>
*
* @class 	Jash.Profiler
* @param 	{function} 	func	Function to profile
* @param	{function} 	func	(optional) callback function to fire when profiler is done
* @returns	{object}		an instance of this object
*/
Jash.Profiler = function(func,onFinish) {
	/* function to profile */
	this.func = func;
	this.time = 0;
	/* set a default callback */
	this.defaultOnFinish = function() {};
	/* array where all result sets will be stored */
	this.results = new Array();
	this.onFinish = typeof onFinish != "function" ? this.defaultOnFinish : onFinish;
	var self = this;

	/**
	* Do this.func 'reps' times in a reverse while loop
	* @param {int} reps	Amount of times to execute this.func
	* @returns {int} 	Time, in milliseconds, it took to perform loop
	*/
	this.reverseWhile = function(reps) {
		this.stopWatch.start();
		while(reps > 0) {
			this.func();
			reps--;
		}
		return this.stopWatch.stop();
	}
	/**
	* Do this.func reps times in a for loop
	* @param {int} reps 	Amount of times to execute this.func
	* @returns {int} 	Time, in milliseconds, it took to perform loop
	*/
	this.forLoop = function(reps) {
		this.stopWatch.start();
		for(i=0;i<reps;i++) {
			this.func();
		}
		return this.stopWatch.stop();
	}
	/**
	* Controller for loop types - run loop 'kind' with 'reps' iterations
	* Store the results of each loop type in its own array, i.e. results.reverseWhile.100
	* or results.forLoop.100 or results.reverseWhile.200
	* @param {str}	kind	Kind of loop to perform.  "reverseWhile" | "forLoop"
	* @param {int} 	reps	Number of iterations in the loop.
	*/
	this.loop = function(kind,reps) {
		if(!this.results[kind]) {
			this.results[kind] = new Array();
		}
		var repsMemberName = "r_" + reps;
		if(!this.results[kind][repsMemberName]) {
			this.results[kind][repsMemberName] = new Array();
		}

		var time = this[kind](reps);
		this.results[kind][repsMemberName].push(time);
	}
	/**
	* Run this.func only one time, store resulting time in milliseconds in
	* this.results.runOnce[]
	*/
	this.runOnce = function() {
		if(!this.results.runOnce) {
			this.results.runOnce = new Array();
		}
		this.stopWatch.start();
		func();
		this.results.runOnce.push(this.stopWatch.stop());
	}
	/**
	* Simple stop watch to time something in milliseconds
	*/
	this.stopWatch = {
		t_start: 0,
		t_end: 0,
		t_total: 0,
		start: function() {
			t_start = new Date().getTime();
			return t_start;
		},
		stop: function() {
			t_end = new Date().getTime();
			t_total = t_end - t_start;
			self.time = t_total;
			return t_total;
		}
	}
	/**
	* Get the average of all of the numbers in arr
	* @param {array} arr	Array of integers to average
	* @returns {int} 	Average of numbers in arr
	*/
	this.average = function(arr) {
		var sum = 0;
		for(i=0;i<arr.length;i++) {
			sum += arr[i];
		}
		return sum / arr.length
	}
	/**
	* run func() passes times in type manner (if type is a loop type, do reps iterations)
	* @param {int} passes	number of times to execute func
	* @param {str} type	"runOnce" or "forLoop" or "reverseWhile" (optional, defaults to runOnce)
	* @param {int} reps	number of times to loop if loop type is used (optional)
	*/
	this.multiPass  = function(passes,type,reps) {
		if(typeof type == "undefined") {
			type = "runOnce";
		} else if(typeof this[type] == "undefined") {
			jash.print("Error: the loop type '" + type + "' does not exist");
			return false;
		}

		var self = this;
		if(type == "runOnce") {
			if(passes < 1) {
				self.reportProfile(Math.round(this.average(this.results.runOnce)),type,reps);
			} else {
				window.setTimeout(function() {
					self.runOnce();
					self.multiPass(--passes,type);
				},50);
			}

		} else {

		    if(passes < 1) {
			    var repsMemberName = "r_" + reps;
			    self.reportProfile(Math.round(this.average(this.results[type][repsMemberName])),type,reps);
		    } else {
			    window.setTimeout(function() {
				    self.loop(type,reps);
				    self.multiPass(--passes,type,reps);
			    },50);
		    }
		}
	}
	/**
	* Create output for user to see results
	* @param {int} avgMs	Average milliseconds it took to do type reps times
	* @param {str} type	Type of function profile. If not "runOnce", then profile type is considered a loop.
	* @param {int} reps	(optional, only if type is loopy) Number of repetitions of loop
	*/
	this.reportProfile = function(avgMs,type,reps) {
		var line = "-------PROFILER----------------------------------------------";
		var str = line + "\n" + this.func + "\n" + line + "\n";
		str += "Type of profile: " + type + "\n";

		if(typeof reps != "undefined") {
			str += "Loop iterations: " + reps + "\n";
		}

		str += "Average execution time: " + avgMs + "ms" + "\n";

		if(type == "runOnce") {
		    howManyTimes = this.results.runOnce.length;
		} else {
		    repsMemberName = "r_" + reps;
		    howManyTimes = this.results[type][repsMemberName].length;
		}
		str += "Average calculated from " + howManyTimes + " pass(es)\n";
		str += line + "\n";
		jash.print(str);
	}
}
/**
* Tab completion of javascript objects, HTML Element ids, and HTML Element
* class names.
* @class 	Jash.TabComplete
* @returns	{object}	an object that is a new instance of Jash.TabComplete class
*/
Jash.TabComplete = function() {
	/***
	* Begin completion process by delegating event based on what is found to
	* be the context of the request.
	* @param {object}	e	Event object
	* @returns {boolean}		False if tab delegated to a id or class name completion function, null if not
	***/
	this.tabComplete = function(e) {
		e = (typeof window.event != "undefined") ? window.event : e;
		var inputText = this.input.value;

		/* see if input is a dom selector function */
		var match = null;
		if(match = this.searchInputForDomGetElFunctions(inputText)) {
			this.tabCompleteIdOrClassInJavascript(match.match[0], match.type);
			this.focusCaretAtEndOfInput();
			return false;
		} else if(this.evaluation.cssEvalFlag) {
			this.tabCompleteIdOrClassInCss(inputText);
			this.focusCaretAtEndOfInput();
			return false;
		} else {
		    this.tabCompleteJavascript(e,inputText);
		    this.focusCaretAtEndOfInput();
		}

	}
	this.focusCaretAtEndOfInput = function() {
		this.input.selectionEnd = this.input.selectionStart = this.input.value.length;
	}
	/**
	* Try to complete a javscript object or function name
	* @param	{object} 	e 		Event object
	* @param 	{string}	inputText	Text to run completion on
	* @returns	{boolean}			false
	**/
	this.tabCompleteJavascript = function(e,inputText) {
		/*get last word of input */
		var words = inputText.split(/\s+/);
		var lastWord = words[(words.length - 1)];

		var numOpeningParens = lastWord.split("(").length - 1;
		var numClosingParens = lastWord.split(")").length - 1;

		var scope;
		var sentinel = 0;

		var diff = numOpeningParens - numClosingParens;

		if(diff > 0) {
			/*how many )'s are after the last ( ?*/
			numClosingParens = lastWord.split("(")[numOpeningParens].split(")").length - 1;
			/*now we can figure out how many )'s we care about*/
			var numRealDanglers = numOpeningParens - numClosingParens;
			scope = lastWord.split("(").slice(numRealDanglers).join("(");
		} else if (diff < 0) {
			this.print("error: too many closing parentheses");
			return false;
		} else 	{
			scope = lastWord;
		}

		scope = scope.split(".");
		var fragment = scope.pop();
		scope = scope.join(".");

		if(scope == "") scope = "window";

		var members = this.getMembers(scope);
		var results = this.findTextMatchesInArray(members,fragment);

		/*no match was found*/
		if(results == false) {
			/*no match*/
		/*several matches have been found*/
		} else if(typeof results != "string") {
		    this.dump(results);
		    var bestMatch = this.findBestStringMatch(fragment,results);
		    if(fragment != '') {
				fragReg = new RegExp(fragment + "$");
				this.input.value = this.input.value.replace(fragReg,bestMatch);
		    } else {
				this.input.value += bestMatch;
		    }
		/*one match was found*/
		} else {
			var reggie = new RegExp(fragment + "$");
			this.input.value = this.input.value.replace(reggie,results);
		}

		return false;
	}
	/**
	* Return true if all characters in an array of strings at a certain position
	* are the same
	*
	* @param 	{int}	index 	0 start int position of character to look at
	* @param 	{array}	arr 	array of strings to test
	* @returns	{boolean}	True if all characters match at position 'index', false if not
	**/
	this.doAllStringsInArrayHaveSameCharacterAtIndex = function(index,arr) {
	    var matched = 0;
	    if(!arr[0].charAt(index)) return false;
	    var character = arr[0].charAt(index);
	    for(var i = 1; i < arr.length; i++) {
		if(!arr[i].charAt(index) || arr[i].charAt(index) != character) {
		    return false;
		}
	    }
	    return true;
	}

	/**
	* Try to find the longest possible match in an array of strings starting from the
	* left
	*
	* @param 	{str}	str	String to look for
	* @param	{array}	arr	Array of strings to look through
	* @returns	{str}		Longest match, starting from left, of all strings in arr
	*/
	this.findBestStringMatch = function(str,arr) {
	    var fragLength = str.length;
	    var matches = this.doAllStringsInArrayHaveSameCharacterAtIndex(fragLength,arr);
	    while(matches) {
		fragLength++;
		matches = this.doAllStringsInArrayHaveSameCharacterAtIndex(fragLength,arr);
	    }
	    return arr[0].substr(0,fragLength);
	}
	/**
	* Attempt to complete an element id or class name based on what is available in all
	* elements in the current DOM; assume the input text is a javascript function call containing (" before
	* the string in question.
	* @param	{string}	inputText	Text to try to complete
	* @param	{string} 	type		"id" | "class" : element id or class name completion
	**/
	this.tabCompleteIdOrClassInJavascript = function(inputText,type) {

	    /*parse out query*/
	    var query = inputText.split("(");
	    query = query[query.length - 1].replace(/\W/g,'');

	    /*loop through dom to find els that match query*/
	    var matches = new Array();

	    var els = document.getElementsByTagName("*");
	    if(type == "id") {
			for(var i = 0; i<els.length; i++) {
				if(els[i].id && els[i].id.indexOf(query) == 0) {
					matches.push(els[i].id);
				}
			}

	    } else if (type == "class") {
			for(var i = 0; i<els.length; i++) {
				if(els[i].className && els[i].className != '') {
					/* tokenize classes into array */
					var classes = els[i].className.split(/\s/);
					for(var ii = 0; ii < classes.length; ii++) {
						if(classes[ii].indexOf(query) == 0 || query == '') {
							/* prevent duplicate entries */
							if(matches.join("***").indexOf(classes[ii]) == -1) {
								matches.push(classes[ii]);
							}
						}
					}
				}
			}
	    }
	    if(matches.length == 1) {
			this.input.value += matches[0].split(query)[1];
	    } else if (matches.length == 0) {
			this.print("no match");
	    } else {
			this.dump(matches.sort());
			var bestMatch = this.findBestStringMatch(query,matches);
			if(query != '') {
				/* do the same string splitting operation that
				was used to find the query text in the first place */
				var replacement = inputText.split("(");
				replacement[replacement.length - 1] = replacement[replacement.length - 1].replace(query,bestMatch);
				this.input.value = this.input.value.replace(inputText,replacement.join("("));
			} else {
			    this.input.value += bestMatch;
			}
	    }
	}

	/**
	* Attempt to complete an element id or class name based on what is available in all
	* elements in the current DOM; assume the input text is a css-style selector, i.e. ".someth" or "#someth"
	* @param	{string}	inputText	Text to try to complete
	**/
	this.tabCompleteIdOrClassInCss = function(inputText) {
		/* tokenize selectors in input */
		var selectors = inputText.replace(/(\.|#)/g,' $1').split(/\s+/);
		var lastSelector = selectors[selectors.length-1];
		var els = document.getElementsByTagName("*");
		var matches = new Array();

		/* class name */
		if(lastSelector.match(/^\./)) {
			for(var i = 0; i<els.length; i++) {
				if(els[i].className && els[i].className != '') {
					/* tokenize classes into array */
					var classes = els[i].className.split(/\s/);
					for(var ii = 0; ii < classes.length; ii++) {
						if(classes[ii].indexOf(lastSelector.substr(1)) == 0 || lastSelector == ".") {
							/* prevent duplicate entries */
							if(matches.join("***").indexOf(classes[ii]) == -1) {
								matches.push("." + classes[ii]);
							}
						}
					}
				}
			}
		/* id */
		} else if (lastSelector.match(/^#/)) {
			for(var i = 0; i<els.length; i++) {
				if(els[i].id && els[i].id.indexOf(lastSelector.substr(1)) == 0) {
					matches.push("#" + els[i].id);
				}
			}
		}
		if(matches.length == 1) {
			this.input.value += matches[0].split(lastSelector)[1];
	    } else if (matches.length == 0) {
			this.print("no match");
	    } else {
			this.dump(matches.sort());
			var bestMatch = this.findBestStringMatch(lastSelector,matches);
			if(lastSelector != '') {
			    this.input.value = this.input.value.replace(lastSelector,bestMatch);
			} else {
			    this.input.value += bestMatch;
			}
	    }
	}

	/**
	* scan inputText to determine if a dom get el fct was typed in.  If so, return match
	* and type of match (class or id)
	* @param 	{str}	inputText	Text to scan for getEl function
	* @returns 	{object}		{ match: "matching text", type: "class" | "id" }
	**/
	this.searchInputForDomGetElFunctions = function(inputText) {
		for(var i = 0; i<this.domGetElFunctions.id.length; i++) {
			var selfct = new RegExp(this.domGetElFunctions.id[i].replace("\$","\\\$") + "\\\(['\"]\\w*$");
			if(inputText.match(selfct)) {
			    return { match: inputText.match(selfct), type: "id"};
			}
		}
		for(var i = 0; i<this.domGetElFunctions.className.length; i++) {
			var selfct = new RegExp(this.domGetElFunctions.className[i].replace("\$","\\\$") + "\\\(['\"]\\w*$");
			if(inputText.match(selfct)) {
			    return { match: inputText.match(selfct), type: "class"};
			}
		}
	}
	/**
	* Look through an array of strings, return strings that match 'findMe'
	* @param {array} arrayToTest	array of strings to match against
	* @param {string} findMe	string to look for in array
	* @returns {array}	array of matches (if matches > 1)
	* @returns {str}	string match (if matches == 1)
	* @returns {boolean}	false (if matches == 0)
	**/
	this.findTextMatchesInArray = function(arrayToTest,findMe) {
		var resultsArray = new Array();
		var tester = new RegExp("^" + findMe);

		for(var i=0;i<arrayToTest.length;i++) {
			if(tester.test(arrayToTest[i])) {
				resultsArray.push(arrayToTest[i]);
			}
		}
		if(resultsArray.length > 1) {
			resultsArray.sort();
			return resultsArray;
		} else if (resultsArray.length == 1) {
			return resultsArray[0];
		} else {
			return false;
		}
	}
	/**
	* Scan an object and return just the member names
	* @param {string} 	context		name of object to scan
	**/
	this.getMembers = function(context) {
		var members = new Array();
		for(memberName in eval(context)) {
			members.push(memberName);
		}
		return members;
	}
	return this;
}
/**
* Anonymous function to create new instance of Jash
*/
new function() {
	if("jash" in window) {
		/* toggle display of jash */
		window.jash.close();
	} else {
		window.jash = new Jash();
		window.jash.main();
	}
}
