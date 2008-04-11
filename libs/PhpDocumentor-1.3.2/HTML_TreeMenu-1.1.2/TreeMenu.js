// +-----------------------------------------------------------------------+
// | Copyright (c) 2002, Richard Heyes, Harald Radi                        |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.| 
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Harald Radi <harald.radi@nme.at>                              |
// +-----------------------------------------------------------------------+
//
// $Id$


/**
* TreeMenu class
*/
	function TreeMenu(iconpath, myname, linkTarget, defaultClass, usePersistence, noTopLevelImages)
	{
		// Properties
		this.iconpath         = iconpath;
		this.myname           = myname;
		this.linkTarget       = linkTarget;
		this.defaultClass     = defaultClass;
		this.usePersistence   = usePersistence;
		this.noTopLevelImages = noTopLevelImages;
		this.n                = new Array();
	
		this.nodeRefs       = new Array();
		this.branches       = new Array();
		this.branchStatus   = new Array();
		this.layerRelations = new Array();
		this.childParents   = new Array();
		this.cookieStatuses = new Array();

		this.preloadImages();
	}

/**
* Adds a node to the tree
*/
	TreeMenu.prototype.addItem = function (newNode)
	{
		newIndex = this.n.length;
		this.n[newIndex] = newNode;
		
		return this.n[newIndex];
	}

/**
* Preload images hack for Mozilla
*/
	TreeMenu.prototype.preloadImages = function ()
	{
		var plustop    = new Image; plustop.src    = this.iconpath + '/plustop.gif';
		var plusbottom = new Image; plusbottom.src = this.iconpath + '/plusbottom.gif';
		var plus       = new Image; plus.src       = this.iconpath + '/plus.gif';
	
		var minustop    = new Image; minustop.src    = this.iconpath + '/minustop.gif';
		var minusbottom = new Image; minusbottom.src = this.iconpath + '/minusbottom.gif';
		var minus       = new Image; minus.src       = this.iconpath + '/minus.gif';
	
		var branchtop    = new Image; branchtop.src    = this.iconpath + '/branchtop.gif';
		var branchbottom = new Image; branchbottom.src = this.iconpath + '/branchbottom.gif';
		var branch       = new Image; branch.src       = this.iconpath + '/branch.gif';
	
		var linebottom = new Image; linebottom.src = this.iconpath + '/linebottom.gif';
		var line       = new Image; line.src       = this.iconpath + '/line.gif';
	}

/**
* Main function that draws the menu and assigns it
* to the layer (or document.write()s it)
*/
	TreeMenu.prototype.drawMenu = function ()// OPTIONAL ARGS: nodes = [], level = [], prepend = '', expanded = false, visbility = 'inline', parentLayerID = null
	{
		/**
	    * Necessary variables
	    */
		var output        = '';
		var modifier      = '';
		var layerID       = '';
		var parentLayerID = '';
	
		/**
	    * Parse any optional arguments
	    */
		var nodes         = arguments[0] ? arguments[0] : this.n
		var level         = arguments[1] ? arguments[1] : [];
		var prepend       = arguments[2] ? arguments[2] : '';
		var expanded      = arguments[3] ? arguments[3] : false;
		var visibility    = arguments[4] ? arguments[4] : 'inline';
		var parentLayerID = arguments[5] ? arguments[5] : null;
	
		var currentlevel  = level.length;
	
		for (var i=0; i<nodes.length; i++) {
		
			level[currentlevel] = i+1;
			layerID = this.myname + '_' + 'node_' + this.implode('_', level);
	
			/**
            * Store this object in the nodeRefs array
            */
			this.nodeRefs[layerID] = nodes[i];

			/**
	        * Store the child/parent relationship
	        */
			this.childParents[layerID] = parentLayerID;
	
			/**
	        * Gif modifier
	        */
			if (i == 0 && parentLayerID == null) {
				modifier = nodes.length > 1 ? "top" : 'single';
			} else if(i == (nodes.length-1)) {
				modifier = "bottom";
			} else {
				modifier = "";
			}
	
			/**
	        * Single root branch is always expanded
	        */
			if (!this.doesMenu() || (parentLayerID == null && (nodes.length == 1 || this.noTopLevelImages))) {
				expanded = true;
	
			} else if (nodes[i].expanded) {
				expanded = true;
	
			} else {
				expanded = false;
			}
	
			/**
	        * Make sure visibility is correct based on parent status
	        */
			visibility =  this.checkParentVisibility(layerID) ? visibility : 'none';
	
			/**
	        * Setup branch status and build an indexed array
			* of branch layer ids
	        */
			if (nodes[i].n.length > 0) {
				this.branchStatus[layerID] = expanded;
				this.branches[this.branches.length] = layerID;
			}
	
			/**
	        * Setup toggle relationship
	        */
			if (!this.layerRelations[parentLayerID]) {
				this.layerRelations[parentLayerID] = new Array();
			}
			this.layerRelations[parentLayerID][this.layerRelations[parentLayerID].length] = layerID;
	
			/**
	        * Branch images
	        */
			var gifname = nodes[i].n.length && this.doesMenu() && nodes[i].isDynamic ? (expanded ? 'minus' : 'plus') : 'branch';
			var iconimg = nodes[i].icon ? this.stringFormat('<img src="{0}/{1}" width="20" height="20" align="top">', this.iconpath, nodes[i].icon) : '';
			
			/**
			* Add event handlers
			*/
			var eventHandlers = "";
			for (j in nodes[i].events) {
				eventHandlers += this.stringFormat('{0}="{1}" ', j, nodes[i].events[j]);
			}

			/**
	        * Build the html to write to the document
			* IMPORTANT:
			* document.write()ing the string: '<div style="display:...' will screw up nn4.x
	        */
			var layerTag  = this.doesMenu() ? this.stringFormat('<div id="{0}" style="display: {1}" class="{2}">', layerID, visibility, (nodes[i].cssClass ? nodes[i].cssClass : this.defaultClass)) : this.stringFormat('<div class="{0}">', '');
			var onMDown   = this.doesMenu() && nodes[i].n.length  && nodes[i].isDynamic ? this.stringFormat('onmousedown="{0}.toggleBranch(\'{1}\', true)" style="cursor: pointer; cursor: hand"', this.myname, layerID) : '';
			var imgTag    = this.stringFormat('<img src="{0}/{1}{2}.gif" width="20" height="20" align="top" border="0" name="img_{3}" {4}>', this.iconpath, gifname, modifier, layerID, onMDown);
			var linkStart = nodes[i].link ? this.stringFormat('<a href="{0}" target="{1}">', nodes[i].link, this.linkTarget) : '';
			var linkEnd   = nodes[i].link ? '</a>' : '';

			output = this.stringFormat('{0}<nobr>{1}{2}{3}{4}<span {5}>{6}</span>{7}</nobr><br></div>',
			                  layerTag,
							  prepend,
			                  parentLayerID == null && (nodes.length == 1 || this.noTopLevelImages) ? '' : imgTag,
							  iconimg,
							  linkStart,
							  eventHandlers,
							  nodes[i].title,
							  linkEnd);
	
			/**
	        * Write out the HTML. Uses document.write for speed over layers and
			* innerHTML. This however means no dynamic adding/removing nodes on
			* the client side. This could be conditional I guess if dynamic
			* adding/removing is required.
	        */
			document.write(output + "\r\n");

			/**
	        * Traverse sub nodes ?
	        */
			if (nodes[i].n.length) {
				/**
	            * Determine what to prepend. If there is only one root
				* node then the prepend to pass to children is nothing.
				* Otherwise it depends on where we are in the tree.
	            */
				if (parentLayerID == null && (nodes.length == 1 || this.noTopLevelImages)) {
					var newPrepend = '';
	
				} else if (i < (nodes.length - 1)) {
					var newPrepend = prepend + this.stringFormat('<img src="{0}/line.gif" width="20" height="20" align="top">', this.iconpath);
	
				} else {
					var newPrepend = prepend + this.stringFormat('<img src="{0}/linebottom.gif" width="20" height="20" align="top">', this.iconpath);
				}
	
				this.drawMenu(nodes[i].n,
				              level,
				              newPrepend,
				              nodes[i].expanded,
				              expanded ? 'inline' : 'none',
				              layerID);
			}
		}
	}

/**
* Toggles a branches visible status. Called from resetBranches()
* and also when a +/- graphic is clicked.
*/
	TreeMenu.prototype.toggleBranch = function (layerID, updateStatus) // OPTIONAL ARGS: fireEvents = true
	{
		var currentDisplay = this.getLayer(layerID).style.display;
		var newDisplay     = (this.branchStatus[layerID] && currentDisplay == 'inline') ? 'none' : 'inline';
		var fireEvents     = arguments[2] != null ? arguments[2] : true;
	
		for (var i=0; i<this.layerRelations[layerID].length; i++) {
	
			if (this.branchStatus[this.layerRelations[layerID][i]]) {
				this.toggleBranch(this.layerRelations[layerID][i], false);
			}
	
			this.getLayer(this.layerRelations[layerID][i]).style.display = newDisplay;
		}
	
		if (updateStatus) {
			this.branchStatus[layerID] = !this.branchStatus[layerID];
	
			/**
	        * Persistence
	        */
			if (this.doesPersistence() && !arguments[2] && this.usePersistence) {
				this.setExpandedStatusForCookie(layerID, this.branchStatus[layerID]);
			}

			/**
			* Fire custom events
			*/
			if (fireEvents) {
				nodeObject = this.nodeRefs[layerID];
	
				if (nodeObject.ontoggle != null) {
					eval(nodeObject.ontoggle);
				}
				
				if (newDisplay == 'none' && nodeObject.oncollapse != null) {
					eval(nodeObject.oncollapse);
				} else if (newDisplay == 'inline' && nodeObject.onexpand != null){
					eval(nodeObject.onexpand);
				}
			}

			// Swap image
			this.swapImage(layerID);
		}
	}

/**
* Swaps the plus/minus branch images
*/
	TreeMenu.prototype.swapImage = function (layerID)
	{
		imgSrc = document.images['img_' + layerID].src;
	
		re = /^(.*)(plus|minus)(bottom|top|single)?.gif$/
		if (matches = imgSrc.match(re)) {
	
			document.images['img_' + layerID].src = this.stringFormat('{0}{1}{2}{3}',
			                                                matches[1],
															matches[2] == 'plus' ? 'minus' : 'plus',
															matches[3] ? matches[3] : '',
															'.gif');
		}
	}

/**
* Can the browser handle the dynamic menu?
*/
	TreeMenu.prototype.doesMenu = function ()
	{
		return (is_ie4up || is_nav6up || is_gecko);
	}

/**
* Can the browser handle save the branch status
*/
	TreeMenu.prototype.doesPersistence = function ()
	{
		return (is_ie4up || is_gecko || is_nav6up);
	}

/**
* Returns the appropriate layer accessor
*/
	TreeMenu.prototype.getLayer = function (layerID)
	{
		if (is_ie4) {
			return document.all(layerID);
	
		} else if (document.getElementById(layerID)) {
			return document.getElementById(layerID);
	
		} else if (document.all(layerID)) {
			return document.all(layerID);
		}
	}

/**
* Save the status of the layer
*/
	TreeMenu.prototype.setExpandedStatusForCookie = function (layerID, expanded)
	{
		this.cookieStatuses[layerID] = expanded;
		this.saveCookie();
	}

/**
* Load the status of the layer
*/
	TreeMenu.prototype.getExpandedStatusFromCookie = function (layerID)
	{
		if (this.cookieStatuses[layerID]) {
			return this.cookieStatuses[layerID];
		}

		return false;
	}

/**
* Saves the cookie that holds which branches are expanded.
* Only saves the details of the branches which are expanded.
*/
	TreeMenu.prototype.saveCookie = function ()
	{
		var cookieString = new Array();

		for (var i in this.cookieStatuses) {
			if (this.cookieStatuses[i] == true) {
				cookieString[cookieString.length] = i;
			}
		}
		
		document.cookie = 'TreeMenuBranchStatus=' + cookieString.join(':');
	}

/**
* Reads cookie parses it for status info and
* stores that info in the class member.
*/
	TreeMenu.prototype.loadCookie = function ()
	{
		var cookie = document.cookie.split('; ');

		for (var i=0; i < cookie.length; i++) {
			var crumb = cookie[i].split('=');
			if ('TreeMenuBranchStatus' == crumb[0] && crumb[1]) {
				var expandedBranches = crumb[1].split(':');
				for (var j=0; j<expandedBranches.length; j++) {
					this.cookieStatuses[expandedBranches[j]] = true;
				}
			}
		}
	}

/**
* Reset branch status
*/
	TreeMenu.prototype.resetBranches = function ()
	{
		if (!this.doesPersistence()) {
			return false;
		}

		this.loadCookie();

		for (var i=0; i<this.branches.length; i++) {
			var status = this.getExpandedStatusFromCookie(this.branches[i]);
			// Only update if it's supposed to be expanded and it's not already
			if (status == true && this.branchStatus[this.branches[i]] != true) {
				if (this.checkParentVisibility(this.branches[i])) {
					this.toggleBranch(this.branches[i], true, false);
				} else {
					this.branchStatus[this.branches[i]] = true;
					this.swapImage(this.branches[i]);
				}
			}
		}
	}

/**
* Checks whether a branch should be open 
* or not based on its parents' status
*/
	TreeMenu.prototype.checkParentVisibility = function (layerID)
	{
		if (this.in_array(this.childParents[layerID], this.branches)
		    && this.branchStatus[this.childParents[layerID]]
			&& this.checkParentVisibility(this.childParents[layerID]) ) {
			
			return true;
	
		} else if (this.childParents[layerID] == null) {
			return true;
		}
		
		return false;
	}

/**
* New C# style string formatter
*/
	TreeMenu.prototype.stringFormat = function (strInput)
	{
		var idx = 0;
	
		for (var i=1; i<arguments.length; i++) {
			while ((idx = strInput.indexOf('{' + (i - 1) + '}', idx)) != -1) {
				strInput = strInput.substring(0, idx) + arguments[i] + strInput.substr(idx + 3);
			}
		}
		
		return strInput;
	}

/**
* Also much adored, the PHP implode() function
*/
	TreeMenu.prototype.implode = function (seperator, input)
	{
		var output = '';
	
		for (var i=0; i<input.length; i++) {
			if (i == 0) {
				output += input[i];
			} else {
				output += seperator + input[i];
			}
		}
		
		return output;
	}

/**
* Aah, all the old favourites are coming out...
*/
	TreeMenu.prototype.in_array = function (item, arr)
	{
		for (var i=0; i<arr.length; i++) {
			if (arr[i] == item) {
				return true;
			}
		}
	
		return false;
	}

/**
* TreeNode Class
*/
	function TreeNode(title, icon, link, expanded, isDynamic, cssClass)
	{
		this.title      = title;
		this.icon       = icon;
		this.link       = link;
		this.expanded   = expanded;
		this.isDynamic  = isDynamic;
		this.cssClass   = cssClass;
		this.n          = new Array();
		this.events     = new Array();
		this.handlers   = null;
		this.oncollapse = null;
		this.onexpand   = null;
		this.ontoggle   = null;
	}

/**
* Adds a node to an already existing node
*/
	TreeNode.prototype.addItem = function (newNode)
	{
		newIndex = this.n.length;
		this.n[newIndex] = newNode;
		
		return this.n[newIndex];
	}

/**
* Sets an event for this particular node
*/
	TreeNode.prototype.setEvent = function (eventName, eventHandler)
	{
		switch (eventName.toLowerCase()) {
			case 'onexpand':
				this.onexpand = eventHandler;
				break;

			case 'oncollapse':
				this.oncollapse = eventHandler;
				break;

			case 'ontoggle':
				this.ontoggle = eventHandler;
				break;

			default:
				this.events[eventName] = eventHandler;
		}
	}

/**
* That's the end of the tree classes. What follows is
* the browser detection code.
*/
	

//<!--
// Ultimate client-side JavaScript client sniff. Version 3.03
// (C) Netscape Communications 1999-2001.  Permission granted to reuse and distribute.
// Revised 17 May 99 to add is_nav5up and is_ie5up (see below).
// Revised 20 Dec 00 to add is_gecko and change is_nav5up to is_nav6up
//                      also added support for IE5.5 Opera4&5 HotJava3 AOLTV
// Revised 22 Feb 01 to correct Javascript Detection for IE 5.x, Opera 4, 
//                      correct Opera 5 detection
//                      add support for winME and win2k
//                      synch with browser-type-oo.js
// Revised 26 Mar 01 to correct Opera detection
// Revised 02 Oct 01 to add IE6 detection

// Everything you always wanted to know about your JavaScript client
// but were afraid to ask. Creates "is_" variables indicating:
// (1) browser vendor:
//     is_nav, is_ie, is_opera, is_hotjava, is_webtv, is_TVNavigator, is_AOLTV
// (2) browser version number:
//     is_major (integer indicating major version number: 2, 3, 4 ...)
//     is_minor (float   indicating full  version number: 2.02, 3.01, 4.04 ...)
// (3) browser vendor AND major version number
//     is_nav2, is_nav3, is_nav4, is_nav4up, is_nav6, is_nav6up, is_gecko, is_ie3,
//     is_ie4, is_ie4up, is_ie5, is_ie5up, is_ie5_5, is_ie5_5up, is_ie6, is_ie6up, is_hotjava3, is_hotjava3up,
//     is_opera2, is_opera3, is_opera4, is_opera5, is_opera5up
// (4) JavaScript version number:
//     is_js (float indicating full JavaScript version number: 1, 1.1, 1.2 ...)
// (5) OS platform and version:
//     is_win, is_win16, is_win32, is_win31, is_win95, is_winnt, is_win98, is_winme, is_win2k
//     is_os2
//     is_mac, is_mac68k, is_macppc
//     is_unix
//     is_sun, is_sun4, is_sun5, is_suni86
//     is_irix, is_irix5, is_irix6
//     is_hpux, is_hpux9, is_hpux10
//     is_aix, is_aix1, is_aix2, is_aix3, is_aix4
//     is_linux, is_sco, is_unixware, is_mpras, is_reliant
//     is_dec, is_sinix, is_freebsd, is_bsd
//     is_vms
//
// See http://www.it97.de/JavaScript/JS_tutorial/bstat/navobj.html and
// http://www.it97.de/JavaScript/JS_tutorial/bstat/Browseraol.html
// for detailed lists of userAgent strings.
//
// Note: you don't want your Nav4 or IE4 code to "turn off" or
// stop working when new versions of browsers are released, so
// in conditional code forks, use is_ie5up ("IE 5.0 or greater") 
// is_opera5up ("Opera 5.0 or greater") instead of is_ie5 or is_opera5
// to check version in code which you want to work on future
// versions.

/**
* Severly curtailed all this as only certain elements
* are required by TreeMenu, specifically:
*  o is_ie4up
*  o is_nav6up
*  o is_gecko
*/

    // convert all characters to lowercase to simplify testing
    var agt=navigator.userAgent.toLowerCase();

    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is_ie5up to detect IE5.
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
    var is_nav6up = (is_nav && (is_major >= 5));
    var is_gecko = (agt.indexOf('gecko') != -1);


    var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
    var is_ie4    = (is_ie && (is_major == 4) && (agt.indexOf("msie 4")!=-1) );
    var is_ie4up  = (is_ie && (is_major >= 4));
//--> end hide JavaScript
