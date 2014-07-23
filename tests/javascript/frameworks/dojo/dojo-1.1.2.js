/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/

/*
	This is a compiled version of Dojo, built for deployment and not for
	development. To get an editable version, please visit:

		http://dojotoolkit.org

	for documentation and information on getting the source.
*/

;(function(){

	/*
	dojo, dijit, and dojox must always be the first three, and in that order.
	djConfig.scopeMap = [
		["dojo", "fojo"],
		["dijit", "fijit"],
		["dojox", "fojox"]

	]
	*/

	/**Build will replace this comment with a scoped djConfig **/

	//The null below can be relaced by a build-time value used instead of djConfig.scopeMap.
	var sMap = null;

	//See if new scopes need to be defined.
	if((sMap || (typeof djConfig != "undefined" && djConfig.scopeMap)) && (typeof window != "undefined")){
		var scopeDef = "", scopePrefix = "", scopeSuffix = "", scopeMap = {}, scopeMapRev = {};
		sMap = sMap || djConfig.scopeMap;
		for(var i = 0; i < sMap.length; i++){
			//Make local variables, then global variables that use the locals.
			var newScope = sMap[i];
			scopeDef += "var " + newScope[0] + " = {}; " + newScope[1] + " = " + newScope[0] + ";" + newScope[1] + "._scopeName = '" + newScope[1] + "';";
			scopePrefix += (i == 0 ? "" : ",") + newScope[0];
			scopeSuffix += (i == 0 ? "" : ",") + newScope[1];
			scopeMap[newScope[0]] = newScope[1];
			scopeMapRev[newScope[1]] = newScope[0];
		}

		eval(scopeDef + "dojo._scopeArgs = [" + scopeSuffix + "];");

		dojo._scopePrefixArgs = scopePrefix;
		dojo._scopePrefix = "(function(" + scopePrefix + "){";
		dojo._scopeSuffix = "})(" + scopeSuffix + ")";
		dojo._scopeMap = scopeMap;
		dojo._scopeMapRev = scopeMapRev;
	}

/*=====
// note:
//		'djConfig' does not exist under 'dojo.*' so that it can be set before the
//		'dojo' variable exists.
// note:
//		Setting any of these variables *after* the library has loaded does
//		nothing at all.

djConfig = {
	// summary:
	//		Application code can set the global 'djConfig' prior to loading
	//		the library to override certain global settings for how dojo works.
	//
	// isDebug: Boolean
	//		Defaults to `false`. If set to `true`, ensures that Dojo provides
	//		extende debugging feedback via Firebug. If Firebug is not available
	//		on your platform, setting `isDebug` to `true` will force Dojo to
	//		pull in (and display) the version of Firebug Lite which is
	//		integrated into the Dojo distribution, thereby always providing a
	//		debugging/logging console when `isDebug` is enabled. Note that
	//		Firebug's `console.*` methods are ALWAYS defined by Dojo. If
	//		`isDebug` is false and you are on a platform without Firebug, these
	//		methods will be defined as no-ops.
	isDebug: false,
	// debugAtAllCosts: Boolean
	//		Defaults to `false`. If set to `true`, this triggers an alternate
	//		mode of the package system in which dependencies are detected and
	//		only then are resources evaluated in dependency order via
	//		`<script>` tag inclusion. This may double-request resources and
	//		cause problems with scripts which expect `dojo.require()` to
	//		preform synchronously. `debugAtAllCosts` can be an invaluable
	//		debugging aid, but when using it, ensure that all code which
	//		depends on Dojo modules is wrapped in `dojo.addOnLoad()` handlers.
	//		Due to the somewhat unpredictable side-effects of using
	//		`debugAtAllCosts`, it is strongly recommended that you enable this
	//		flag as a last resort. `debugAtAllCosts` has no effect when loading
	//		resources across domains. For usage information, see the
	//		[Dojo Book](http://dojotoolkit.org/book/book-dojo/part-4-meta-dojo-making-your-dojo-code-run-faster-and-better/debugging-facilities/deb)
	debugAtAllCosts: false,
	// locale: String
	//		The locale to assume for loading localized resources in this page,
	//		specified according to [RFC 3066](http://www.ietf.org/rfc/rfc3066.txt).
	//		Must be specified entirely in lowercase, e.g. `en-us` and `zh-cn`.
	//		See the documentation for `dojo.i18n` and `dojo.requireLocalization`
	//		for details on loading localized resources. If no locale is specified,
	//		Dojo assumes the locale of the user agent, according to `navigator.userLanguage`
	//		or `navigator.language` properties.
	locale: undefined,
	// extraLocale: Array
	//		No default value. Specifies additional locales whose
	//		resources should also be loaded alongside the default locale when
	//		calls to `dojo.requireLocalization()` are processed.
	extraLocale: undefined,
	// baseUrl: String
	//		The directory in which `dojo.js` is located. Under normal
	//		conditions, Dojo auto-detects the correct location from which it
	//		was loaded. You may need to manually configure `baseUrl` in cases
	//		where you have renamed `dojo.js` or in which `<base>` tags confuse
	//		some browsers (e.g., IE 6). The variable `dojo.baseUrl` is assigned
	//		either the value of `djConfig.baseUrl` if one is provided or the
	//		auto-detected root if not. Other modules are located relative to
	//		this path.
	baseUrl: undefined,
	// modulePaths: Object
	//		A map of module names to paths relative to `dojo.baseUrl`. The
	//		key/value pairs correspond directly to the arguments which
	//		`dojo.registerModulePath` accepts. Specifiying
	//		`djConfig.modulePaths = { "foo": "../../bar" }` is the equivalent
	//		of calling `dojo.registerModulePath("foo", "../../bar");`. Multiple
	//		modules may be configured via `djConfig.modulePaths`.
	modulePaths: {},
}
=====*/

(function(){
	// firebug stubs

	// if((!this["console"])||(!console["firebug"])){

	if(!this["console"]){
		this.console = {
			log: function(){} // no-op
		};
	}

	var cn = [
		"assert", "count", "debug", "dir", "dirxml", "error", "group",
		"groupEnd", "info", "profile", "profileEnd", "time", "timeEnd",
		"trace", "warn", "log"
	];
	var i=0, tn;
	while((tn=cn[i++])){
		if(!console[tn]){
			(function(){
				var tcn = tn+"";
				console[tcn] = function(){
					var a = Array.apply({}, arguments);
					a.unshift(tcn+":");
					console.log(a.join(" "));
				}
			})();
		}
	}

	//TODOC:  HOW TO DOC THIS?
	// dojo is the root variable of (almost all) our public symbols -- make sure it is defined.
	if(typeof dojo == "undefined"){
		this.dojo = {
			_scopeName: "dojo",
			_scopePrefix: "",
			_scopePrefixArgs: "",
			_scopeSuffix: "",
			_scopeMap: {},
			_scopeMapRev: {}
		};
	}

	var d = dojo;

	//Need placeholders for dijit and dojox for scoping code.
	if(typeof dijit == "undefined"){
		this.dijit = {_scopeName: "dijit"};
	}
	if(typeof dojox == "undefined"){
		this.dojox = {_scopeName: "dojox"};
	}

	if(!d._scopeArgs){
		d._scopeArgs = [dojo, dijit, dojox];
	}

/*=====
dojo.global = {
	//	summary:
	//		Alias for the global scope
	//		(e.g. the window object in a browser).
	//	description:
	//		Refer to 'dojo.global' rather than referring to window to ensure your
	//		code runs correctly in contexts other than web browsers (e.g. Rhino on a server).
}
=====*/
	d.global = this;

	d.config =/*===== djConfig = =====*/{
		isDebug: false,
		debugAtAllCosts: false
	};

	if(typeof djConfig != "undefined"){
		for(var opt in djConfig){
			d.config[opt] = djConfig[opt];
		}
	}

	var _platforms = ["Browser", "Rhino", "Spidermonkey", "Mobile"];
	var t;
	while((t=_platforms.shift())){
		d["is"+t] = false;
	}

/*=====
	// Override locale setting, if specified
	dojo.locale = {
		// summary: the locale as defined by Dojo (read-only)
	};
=====*/
	dojo.locale = d.config.locale;

	var rev = "$Rev: 21532 $".match(/\d+/);

	dojo.version = {
		// summary:
		//		version number of dojo
		//	major: Integer
		//		Major version. If total version is "1.2.0beta1", will be 1
		//	minor: Integer
		//		Minor version. If total version is "1.2.0beta1", will be 2
		//	patch: Integer
		//		Patch version. If total version is "1.2.0beta1", will be 0
		//	flag: String
		//		Descriptor flag. If total version is "1.2.0beta1", will be "beta1"
		//	revision: Number
		//		The SVN rev from which dojo was pulled
		major: 1, minor: 1, patch: 2, flag: "",
		revision: rev ? +rev[0] : 999999, //FIXME: use NaN?
		toString: function(){
			with(d.version){
				return major + "." + minor + "." + patch + flag + " (" + revision + ")";	// String
			}
		}
	}

	// Register with the OpenAjax hub
	if(typeof OpenAjax != "undefined"){
		OpenAjax.hub.registerLibrary(dojo._scopeName, "http://dojotoolkit.org", d.version.toString());
	}

	dojo._mixin = function(/*Object*/ obj, /*Object*/ props){
		// summary:
		//		Adds all properties and methods of props to obj. This addition
		//		is "prototype extension safe", so that instances of objects
		//		will not pass along prototype defaults.
		var tobj = {};
		for(var x in props){
			// the "tobj" condition avoid copying properties in "props"
			// inherited from Object.prototype.  For example, if obj has a custom
			// toString() method, don't overwrite it with the toString() method
			// that props inherited from Object.prototype
			if(tobj[x] === undefined || tobj[x] != props[x]){
				obj[x] = props[x];
			}
		}
		// IE doesn't recognize custom toStrings in for..in
		if(d["isIE"] && props){
			var p = props.toString;
			if(typeof p == "function" && p != obj.toString && p != tobj.toString &&
				p != "\nfunction toString() {\n    [native code]\n}\n"){
					obj.toString = props.toString;
			}
		}
		return obj; // Object
	}

	dojo.mixin = function(/*Object*/obj, /*Object...*/props){
		// summary:
		//		Adds all properties and methods of props to obj and returns the
		//		(now modified) obj.
		//	description:
		//		`dojo.mixin` can mix multiple source objects into a
		//		destionation object which is then returned. Unlike regular
		//		`for...in` iteration, `dojo.mixin` is also smart about avoiding
		//		extensions which other toolkits may unwisely add to the root
		//		object prototype
		//	obj:
		//		The object to mix properties into. Also the return value.
		//	props:
		//		One or more objects whose values are successively copied into
		//		obj. If more than one of these objects contain the same value,
		//		the one specified last in the function call will "win".
		//	example:
		//		make a shallow copy of an object
		//	|	var copy = dojo.mixin({}, source);
		//	example:
		//		many class constructors often take an object which specifies
		//		values to be configured on the object. In this case, it is
		//		often simplest to call `dojo.mixin` on the `this` object:
		//	|	dojo.declare("acme.Base", null, {
		//	|		constructor: function(properties){
		//	|			// property configuration:
		//	|			dojo.mixin(this, properties);
		//	|
		//	|			console.debug(this.quip);
		//	|			//  ...
		//	|		},
		//	|		quip: "I wasn't born yesterday, you know - I've seen movies.",
		//	|		// ...
		//	|	});
		//	|
		//	|	// create an instance of the class and configure it
		//	|	var b = new acme.Base({quip: "That's what it does!" });
		//	example:
		//		copy in properties from multiple objects
		//	|	var flattened = dojo.mixin(
		//	|		{
		//	|			name: "Frylock",
		//	|			braces: true,
		//	|		}
		//	|		{
		//	|			name: "Carl Brutanananadilewski"
		//	|		}
		//	|	);
		//	|
		//	|	// will print "Carl Brutanananadilewski"
		//	|	console.debug(flattened.name);
		//	|	// will print "true"
		//	|	console.debug(flattened.braces);
		for(var i=1, l=arguments.length; i<l; i++){
			d._mixin(obj, arguments[i]);
		}
		return obj; // Object
	}

	dojo._getProp = function(/*Array*/parts, /*Boolean*/create, /*Object*/context){
		var obj=context || d.global;
		for(var i=0, p; obj && (p=parts[i]); i++){
			if(i == 0 && this._scopeMap[p]){
				p = this._scopeMap[p];
			}
			obj = (p in obj ? obj[p] : (create ? obj[p]={} : undefined));
		}
		return obj; // mixed
	}

	dojo.setObject = function(/*String*/name, /*Object*/value, /*Object?*/context){
		// summary:
		//		Set a property from a dot-separated string, such as "A.B.C"
		//	description:
		//		Useful for longer api chains where you have to test each object in
		//		the chain, or when you have an object reference in string format.
		//		Objects are created as needed along `path`. Returns the passed
		//		value if setting is successful or `undefined` if not.
		//	name:
		//		Path to a property, in the form "A.B.C".
		//	context:
		//		Optional. Object to use as root of path. Defaults to
		//		`dojo.global`.
		//	example:
		//		set the value of `foo.bar.baz`, regardless of whether
		//		intermediate objects already exist:
		//	|	dojo.setObject("foo.bar.baz", value);
		//	example:
		//		without `dojo.setObject`, we often see code like this:
		//	|	// ensure that intermediate objects are available
		//	|	if(!obj["parent"]){ obj.parent = {}; }
		//	|	if(!obj.parent["child"]){ obj.parent.child= {}; }
		//	|	// now we can safely set the property
		//	|	obj.parent.child.prop = "some value";
		//		wheras with `dojo.setObject`, we can shorten that to:
		//	|	dojo.setObject("parent.child.prop", "some value", obj);
		var parts=name.split("."), p=parts.pop(), obj=d._getProp(parts, true, context);
		return obj && p ? (obj[p]=value) : undefined; // Object
	}

	dojo.getObject = function(/*String*/name, /*Boolean*/create, /*Object*/context){
		// summary:
		//		Get a property from a dot-separated string, such as "A.B.C"
		//	description:
		//		Useful for longer api chains where you have to test each object in
		//		the chain, or when you have an object reference in string format.
		//	name:
		//		Path to an property, in the form "A.B.C".
		//	context:
		//		Optional. Object to use as root of path. Defaults to
		//		'dojo.global'. Null may be passed.
		//	create:
		//		Optional. Defaults to `false`. If `true`, Objects will be
		//		created at any point along the 'path' that is undefined.
		return d._getProp(name.split("."), create, context); // Object
	}

	dojo.exists = function(/*String*/name, /*Object?*/obj){
		//	summary:
		//		determine if an object supports a given method
		//	description:
		//		useful for longer api chains where you have to test each object in
		//		the chain
		//	name:
		//		Path to an object, in the form "A.B.C".
		//	obj:
		//		Object to use as root of path. Defaults to
		//		'dojo.global'. Null may be passed.
		//	example:
		//	|	// define an object
		//	|	var foo = {
		//	|		bar: { }
		//	|	};
		//	|
		//	|	// search the global scope
		//	|	dojo.exists("foo.bar"); // true
		//	|	dojo.exists("foo.bar.baz"); // false
		//	|
		//	|	// search from a particular scope
		//	|	dojo.exists("bar", foo); // true
		//	|	dojo.exists("bar.baz", foo); // false
		return !!d.getObject(name, false, obj); // Boolean
	}

	dojo["eval"] = function(/*String*/ scriptFragment){
		//	summary:
		//		Perform an evaluation in the global scope. Use this rather than
		//		calling 'eval()' directly.
		//	description:
		//		Placed in a separate function to minimize size of trapped
		//		exceptions. Calling eval() directly from some other scope may
		//		complicate tracebacks on some platforms.
		//	return:
		//		The result of the evaluation. Often `undefined`

		// note:
		//	 - JSC eval() takes an optional second argument which can be 'unsafe'.
		//	 - Mozilla/SpiderMonkey eval() takes an optional second argument which is the
		//  	 scope object for new symbols.

		// FIXME: investigate Joseph Smarr's technique for IE:
		//		http://josephsmarr.com/2007/01/31/fixing-eval-to-use-global-scope-in-ie/
		//	see also:
		// 		http://trac.dojotoolkit.org/ticket/744
		return d.global.eval ? d.global.eval(scriptFragment) : eval(scriptFragment); 	// Object
	}

	/*=====
		dojo.deprecated = function(behaviour, extra, removal){
			//	summary:
			//		Log a debug message to indicate that a behavior has been
			//		deprecated.
			//	behaviour: String
			//		The API or behavior being deprecated. Usually in the form
			//		of "myApp.someFunction()".
			//	extra: String?
			//		Text to append to the message. Often provides advice on a
			//		new function or facility to achieve the same goal during
			//		the deprecation period.
			//	removal: String?
			//		Text to indicate when in the future the behavior will be
			//		removed. Usually a version number.
			//	example:
			//	|	dojo.deprecated("myApp.getTemp()", "use myApp.getLocaleTemp() instead", "1.0");
		}

		dojo.experimental = function(moduleName, extra){
			//	summary: Marks code as experimental.
			//	description:
			//	 	This can be used to mark a function, file, or module as
			//	 	experimental.  Experimental code is not ready to be used, and the
			//	 	APIs are subject to change without notice.  Experimental code may be
			//	 	completed deleted without going through the normal deprecation
			//	 	process.
			//	moduleName: String
			//	 	The name of a module, or the name of a module file or a specific
			//	 	function
			//	extra: String?
			//	 	some additional message for the user
			//	example:
			//	|	dojo.experimental("dojo.data.Result");
			//	example:
			//	|	dojo.experimental("dojo.weather.toKelvin()", "PENDING approval from NOAA");
		}
	=====*/

	//Real functions declared in dojo._firebug.firebug.
	d.deprecated = d.experimental = function(){};

})();
// vim:ai:ts=4:noet

/*
 * loader.js - A bootstrap module.  Runs before the hostenv_*.js file. Contains
 * all of the package loading methods.
 */

(function(){
	var d = dojo;

	d.mixin(d, {
		_loadedModules: {},
		_inFlightCount: 0,
		_hasResource: {},

		_modulePrefixes: {
			dojo: 	{	name: "dojo", value: "." },
			// dojox: 	{	name: "dojox", value: "../dojox" },
			// dijit: 	{	name: "dijit", value: "../dijit" },
			doh: 	{	name: "doh", value: "../util/doh" },
			tests: 	{	name: "tests", value: "tests" }
		},

		_moduleHasPrefix: function(/*String*/module){
			// summary: checks to see if module has been established
			var mp = this._modulePrefixes;
			return !!(mp[module] && mp[module].value); // Boolean
		},

		_getModulePrefix: function(/*String*/module){
			// summary: gets the prefix associated with module
			var mp = this._modulePrefixes;
			if(this._moduleHasPrefix(module)){
				return mp[module].value; // String
			}
			return module; // String
		},

		_loadedUrls: [],

		//WARNING:
		//		This variable is referenced by packages outside of bootstrap:
		//		FloatingPane.js and undo/browser.js
		_postLoad: false,

		//Egad! Lots of test files push on this directly instead of using dojo.addOnLoad.
		_loaders: [],
		_unloaders: [],
		_loadNotifying: false
	});

		dojo._loadPath = function(/*String*/relpath, /*String?*/module, /*Function?*/cb){
		// 	summary:
		//		Load a Javascript module given a relative path
		//
		//	description:
		//		Loads and interprets the script located at relpath, which is
		//		relative to the script root directory.  If the script is found but
		//		its interpretation causes a runtime exception, that exception is
		//		not caught by us, so the caller will see it.  We return a true
		//		value if and only if the script is found.
		//
		// relpath:
		//		A relative path to a script (no leading '/', and typically ending
		//		in '.js').
		// module:
		//		A module whose existance to check for after loading a path.  Can be
		//		used to determine success or failure of the load.
		// cb:
		//		a callback function to pass the result of evaluating the script

		var uri = ((relpath.charAt(0) == '/' || relpath.match(/^\w+:/)) ? "" : this.baseUrl) + relpath;
		try{
			return !module ? this._loadUri(uri, cb) : this._loadUriAndCheck(uri, module, cb); // Boolean
		}catch(e){
			console.error(e);
			return false; // Boolean
		}
	}

	dojo._loadUri = function(/*String*/uri, /*Function?*/cb){
		//	summary:
		//		Loads JavaScript from a URI
		//	description:
		//		Reads the contents of the URI, and evaluates the contents.  This is
		//		used to load modules as well as resource bundles. Returns true if
		//		it succeeded. Returns false if the URI reading failed.  Throws if
		//		the evaluation throws.
		//	uri: a uri which points at the script to be loaded
		//	cb:
		//		a callback function to process the result of evaluating the script
		//		as an expression, typically used by the resource bundle loader to
		//		load JSON-style resources

		if(this._loadedUrls[uri]){
			return true; // Boolean
		}
		var contents = this._getText(uri, true);
		if(!contents){ return false; } // Boolean
		this._loadedUrls[uri] = true;
		this._loadedUrls.push(uri);
		if(cb){
			contents = '('+contents+')';
		}else{
			//Only do the scoping if no callback. If a callback is specified,
			//it is most likely the i18n bundle stuff.
			contents = this._scopePrefix + contents + this._scopeSuffix;
		}
		if(d.isMoz){ contents += "\r\n//@ sourceURL=" + uri; } // debugging assist for Firebug
		var value = d["eval"](contents);
		if(cb){ cb(value); }
		return true; // Boolean
	}

	// FIXME: probably need to add logging to this method
	dojo._loadUriAndCheck = function(/*String*/uri, /*String*/moduleName, /*Function?*/cb){
		// summary: calls loadUri then findModule and returns true if both succeed
		var ok = false;
		try{
			ok = this._loadUri(uri, cb);
		}catch(e){
			console.error("failed loading " + uri + " with error: " + e);
		}
		return !!(ok && this._loadedModules[moduleName]); // Boolean
	}

	dojo.loaded = function(){
		// summary:
		//		signal fired when initial environment and package loading is
		//		complete. You may use dojo.addOnLoad() or dojo.connect() to
		//		this method in order to handle initialization tasks that
		//		require the environment to be initialized. In a browser host,
		//		declarative widgets will be constructed when this function
		//		finishes runing.
		this._loadNotifying = true;
		this._postLoad = true;
		var mll = d._loaders;

		//Clear listeners so new ones can be added
		//For other xdomain package loads after the initial load.
		this._loaders = [];

		for(var x = 0; x < mll.length; x++){
			try{
				mll[x]();
			}catch(e){
				throw e;
				console.error("dojo.addOnLoad callback failed: " + e, e); /* let other load events fire, like the parser, but report the error */
			}
		}

		this._loadNotifying = false;

		//Make sure nothing else got added to the onload queue
		//after this first run. If something did, and we are not waiting for any
		//more inflight resources, run again.
		if(d._postLoad && d._inFlightCount == 0 && mll.length){
			d._callLoaded();
		}
	}

	dojo.unloaded = function(){
		// summary:
		//		signal fired by impending environment destruction. You may use
		//		dojo.addOnUnload() or dojo.connect() to this method to perform
		//		page/application cleanup methods.
		var mll = this._unloaders;
		while(mll.length){
			(mll.pop())();
		}
	}

	var onto = function(arr, obj, fn){
		if(!fn){
			arr.push(obj);
		}else if(fn){
			var func = (typeof fn == "string") ? obj[fn] : fn;
			arr.push(function(){ func.call(obj); });
		}
	}

	dojo.addOnLoad = function(/*Object?*/obj, /*String|Function*/functionName){
		// summary:
		//		Registers a function to be triggered after the DOM has finished
		//		loading and widgets declared in markup have been instantiated.
		//		Images and CSS files may or may not have finished downloading when
		//		the specified function is called.  (Note that widgets' CSS and HTML
		//		code is guaranteed to be downloaded before said widgets are
		//		instantiated.)
		// example:
		//	|	dojo.addOnLoad(functionPointer);
		//	|	dojo.addOnLoad(object, "functionName");
		//	|	dojo.addOnLoad(object, function(){ /* ... */});

		onto(d._loaders, obj, functionName);

		//Added for xdomain loading. dojo.addOnLoad is used to
		//indicate callbacks after doing some dojo.require() statements.
		//In the xdomain case, if all the requires are loaded (after initial
		//page load), then immediately call any listeners.
		if(d._postLoad && d._inFlightCount == 0 && !d._loadNotifying){
			d._callLoaded();
		}
	}

	dojo.addOnUnload = function(/*Object?*/obj, /*String|Function?*/functionName){
		// summary:
		//		registers a function to be triggered when the page unloads
		// example:
		//	|	dojo.addOnUnload(functionPointer)
		//	|	dojo.addOnUnload(object, "functionName")
		//	|	dojo.addOnUnload(object, function(){ /* ... */});

		onto(d._unloaders, obj, functionName);
	}

	dojo._modulesLoaded = function(){
		if(d._postLoad){ return; }
		if(d._inFlightCount > 0){
			console.warn("files still in flight!");
			return;
		}
		d._callLoaded();
	}

	dojo._callLoaded = function(){

		// The "object" check is for IE, and the other opera check fixes an
		// issue in Opera where it could not find the body element in some
		// widget test cases.  For 0.9, maybe route all browsers through the
		// setTimeout (need protection still for non-browser environments
		// though). This might also help the issue with FF 2.0 and freezing
		// issues where we try to do sync xhr while background css images are
		// being loaded (trac #2572)? Consider for 0.9.
		if(typeof setTimeout == "object" || (dojo.config.useXDomain && d.isOpera)){
			if(dojo.isAIR){
				setTimeout(function(){dojo.loaded();}, 0);
			}else{
				setTimeout(dojo._scopeName + ".loaded();", 0);
			}
		}else{
			d.loaded();
		}
	}

	dojo._getModuleSymbols = function(/*String*/modulename){
		// summary:
		//		Converts a module name in dotted JS notation to an array
		//		representing the path in the source tree
		var syms = modulename.split(".");
		for(var i = syms.length; i>0; i--){
			var parentModule = syms.slice(0, i).join(".");
			if((i==1) && !this._moduleHasPrefix(parentModule)){
				// Support default module directory (sibling of dojo) for top-level modules
				syms[0] = "../" + syms[0];
			}else{
				var parentModulePath = this._getModulePrefix(parentModule);
				if(parentModulePath != parentModule){
					syms.splice(0, i, parentModulePath);
					break;
				}
			}
		}
		// console.debug(syms);
		return syms; // Array
	}

	dojo._global_omit_module_check = false;

	dojo._loadModule = dojo.require = function(/*String*/moduleName, /*Boolean?*/omitModuleCheck){
		//	summary:
		//		loads a Javascript module from the appropriate URI
		//	moduleName:
		//		module name to load. Module paths are de-referenced by dojo's
		//		internal mapping of locations to names and are disambiguated by
		//		longest prefix. See `dojo.registerModulePath()` for details on
		//		registering new modules.
		//	omitModuleCheck:
		//		if `true`, omitModuleCheck skips the step of ensuring that the
		//		loaded file actually defines the symbol it is referenced by.
		//		For example if it called as `dojo._loadModule("a.b.c")` and the
		//		file located at `a/b/c.js` does not define an object `a.b.c`,
		//		and exception will be throws whereas no exception is raised
		//		when called as `dojo._loadModule("a.b.c", true)`
		//	description:
		//		`dojo._loadModule("A.B")` first checks to see if symbol A.B is
		//		defined. If it is, it is simply returned (nothing to do).
		//
		//		If it is not defined, it will look for `A/B.js` in the script root
		//		directory.
		//
		//		`dojo._loadModule` throws an excpetion if it cannot find a file
		//		to load, or if the symbol `A.B` is not defined after loading.
		//
		//		It returns the object `A.B`.
		//
		//		`dojo._loadModule()` does nothing about importing symbols into
		//		the current namespace.  It is presumed that the caller will
		//		take care of that. For example, to import all symbols into a
		//		local block, you might write:
		//
		//		|	with (dojo._loadModule("A.B")) {
		//		|		...
		//		|	}
		//
		//		And to import just the leaf symbol to a local variable:
		//
		//		|	var B = dojo._loadModule("A.B");
		//	   	|	...
		//	returns: the required namespace object
		omitModuleCheck = this._global_omit_module_check || omitModuleCheck;

		//Check if it is already loaded.
		var module = this._loadedModules[moduleName];
		if(module){
			return module;
		}

		// convert periods to slashes
		var relpath = this._getModuleSymbols(moduleName).join("/") + '.js';

		var modArg = (!omitModuleCheck) ? moduleName : null;
		var ok = this._loadPath(relpath, modArg);

		if(!ok && !omitModuleCheck){
			throw new Error("Could not load '" + moduleName + "'; last tried '" + relpath + "'");
		}

		// check that the symbol was defined
		// Don't bother if we're doing xdomain (asynchronous) loading.
		if(!omitModuleCheck && !this._isXDomain){
			// pass in false so we can give better error
			module = this._loadedModules[moduleName];
			if(!module){
				throw new Error("symbol '" + moduleName + "' is not defined after loading '" + relpath + "'");
			}
		}

		return module;
	}

	dojo.provide = function(/*String*/ resourceName){
		//	summary:
		//		Each javascript source file must have at least one
		//		`dojo.provide()` call at the top of the file, corresponding to
		//		the file name.  For example, `js/dojo/foo.js` must have
		//		`dojo.provide("dojo.foo");` before any calls to
		//		`dojo.require()` are made.
		//	description:
		//		Each javascript source file is called a resource.  When a
		//		resource is loaded by the browser, `dojo.provide()` registers
		//		that it has been loaded.
		//
		//		For backwards compatibility reasons, in addition to registering
		//		the resource, `dojo.provide()` also ensures that the javascript
		//		object for the module exists.  For example,
		//		`dojo.provide("dojox.data.FlickrStore")`, in addition to
		//		registering that `FlickrStore.js` is a resource for the
		//		`dojox.data` module, will ensure that the `dojox.data`
		//		javascript object exists, so that calls like
		//		`dojo.data.foo = function(){ ... }` don't fail.
		//
		//		In the case of a build where multiple javascript source files
		//		are combined into one bigger file (similar to a .lib or .jar
		//		file), that file may contain multiple dojo.provide() calls, to
		//		note that it includes multiple resources.

		//Make sure we have a string.
		resourceName = resourceName + "";
		return (d._loadedModules[resourceName] = d.getObject(resourceName, true)); // Object
	}

	//Start of old bootstrap2:

	dojo.platformRequire = function(/*Object*/modMap){
		//	summary:
		//		require one or more modules based on which host environment
		//		Dojo is currently operating in
		//	description:
		//		This method takes a "map" of arrays which one can use to
		//		optionally load dojo modules. The map is indexed by the
		//		possible dojo.name_ values, with two additional values:
		//		"default" and "common". The items in the "default" array will
		//		be loaded if none of the other items have been choosen based on
		//		dojo.name_, set by your host environment. The items in the
		//		"common" array will *always* be loaded, regardless of which
		//		list is chosen.
		//	example:
		//		|	dojo.platformRequire({
		//		|		browser: [
		//		|			"foo.sample", // simple module
		//		|			"foo.test",
		//		|			["foo.bar.baz", true] // skip object check in _loadModule
		//		|		],
		//		|		default: [ "foo.sample._base" ],
		//		|		common: [ "important.module.common" ]
		//		|	});

		var common = modMap.common || [];
		var result = common.concat(modMap[d._name] || modMap["default"] || []);

		for(var x=0; x<result.length; x++){
			var curr = result[x];
			if(curr.constructor == Array){
				d._loadModule.apply(d, curr);
			}else{
				d._loadModule(curr);
			}
		}
	}

	dojo.requireIf = function(/*Boolean*/ condition, /*String*/ resourceName){
		// summary:
		//		If the condition is true then call dojo.require() for the specified
		//		resource
		if(condition === true){
			// FIXME: why do we support chained require()'s here? does the build system?
			var args = [];
			for(var i = 1; i < arguments.length; i++){
				args.push(arguments[i]);
			}
			d.require.apply(d, args);
		}
	}

	dojo.requireAfterIf = d.requireIf;

	dojo.registerModulePath = function(/*String*/module, /*String*/prefix){
		//	summary:
		//		maps a module name to a path
		//	description:
		//		An unregistered module is given the default path of ../[module],
		//		relative to Dojo root. For example, module acme is mapped to
		//		../acme.  If you want to use a different module name, use
		//		dojo.registerModulePath.
		//	example:
		//		If your dojo.js is located at this location in the web root:
		//	|	/myapp/js/dojo/dojo/dojo.js
		//		and your modules are located at:
		//	|	/myapp/js/foo/bar.js
		//	|	/myapp/js/foo/baz.js
		//	|	/myapp/js/foo/thud/xyzzy.js
		//		Your application can tell Dojo to locate the "foo" namespace by calling:
		//	|	dojo.registerModulePath("foo", "../../foo");
		//		At which point you can then use dojo.require() to load the
		//		modules (assuming they provide() the same things which are
		//		required). The full code might be:
		//	|	<script type="text/javascript"
		//	|		src="/myapp/js/dojo/dojo/dojo.js"></script>
		//	|	<script type="text/javascript">
		//	|		dojo.registerModulePath("foo", "../../foo");
		//	|		dojo.require("foo.bar");
		//	|		dojo.require("foo.baz");
		//	|		dojo.require("foo.thud.xyzzy");
		//	|	</script>
		d._modulePrefixes[module] = { name: module, value: prefix };
	}

	dojo.requireLocalization = function(/*String*/moduleName, /*String*/bundleName, /*String?*/locale, /*String?*/availableFlatLocales){
		// summary:
		//		Declares translated resources and loads them if necessary, in the
		//		same style as dojo.require.  Contents of the resource bundle are
		//		typically strings, but may be any name/value pair, represented in
		//		JSON format.  See also dojo.i18n.getLocalization.
		// moduleName:
		//		name of the package containing the "nls" directory in which the
		//		bundle is found
		// bundleName:
		//		bundle name, i.e. the filename without the '.js' suffix
		// locale:
		//		the locale to load (optional)  By default, the browser's user
		//		locale as defined by dojo.locale
		// availableFlatLocales:
		//		A comma-separated list of the available, flattened locales for this
		//		bundle. This argument should only be set by the build process.
		// description:
		//		Load translated resource bundles provided underneath the "nls"
		//		directory within a package.  Translated resources may be located in
		//		different packages throughout the source tree.  For example, a
		//		particular widget may define one or more resource bundles,
		//		structured in a program as follows, where moduleName is
		//		mycode.mywidget and bundleNames available include bundleone and
		//		bundletwo:
		//
		//	|		...
		//	|		mycode/
		//	|		 mywidget/
		//	|		  nls/
		//	|		   bundleone.js (the fallback translation, English in this example)
		//	|		   bundletwo.js (also a fallback translation)
		//	|		   de/
		//	|		    bundleone.js
		//	|		    bundletwo.js
		//	|		   de-at/
		//	|		    bundleone.js
		//	|		   en/
		//	|		    (empty; use the fallback translation)
		//	|		   en-us/
		//	|		    bundleone.js
		//	|		   en-gb/
		//	|		    bundleone.js
		//	|		   es/
		//	|		    bundleone.js
		//	|		    bundletwo.js
		//	|		  ...etc
		//	|		...
		//
		//		Each directory is named for a locale as specified by RFC 3066,
		//		(http://www.ietf.org/rfc/rfc3066.txt), normalized in lowercase.
		//		Note that the two bundles in the example do not define all the
		//		same variants.  For a given locale, bundles will be loaded for
		//		that locale and all more general locales above it, including a
		//		fallback at the root directory.  For example, a declaration for
		//		the "de-at" locale will first load `nls/de-at/bundleone.js`,
		//		then `nls/de/bundleone.js` and finally `nls/bundleone.js`.  The
		//		data will be flattened into a single Object so that lookups
		//		will follow this cascading pattern.  An optional build step can
		//		preload the bundles to avoid data redundancy and the multiple
		//		network hits normally required to load these resources.

		d.require("dojo.i18n");
		d.i18n._requireLocalization.apply(d.hostenv, arguments);
	};

	var ore = new RegExp("^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\\?([^#]*))?(#(.*))?$");
	var ire = new RegExp("^((([^:]+:)?([^@]+))@)?([^:]*)(:([0-9]+))?$");

	dojo._Url = function(/*dojo._Url||String...*/){
		// summary:
		//		Constructor to create an object representing a URL.
		//		It is marked as private, since we might consider removing
		//		or simplifying it.
		// description:
		//		Each argument is evaluated in order relative to the next until
		//		a canonical uri is produced. To get an absolute Uri relative to
		//		the current document use:
		//      	new dojo._Url(document.baseURI, url)

		var n = null;

		// TODO: support for IPv6, see RFC 2732
		var _a = arguments;
		var uri = [_a[0]];
		// resolve uri components relative to each other
		for(var i = 1; i<_a.length; i++){
			if(!_a[i]){ continue; }

			// Safari doesn't support this.constructor so we have to be explicit
			// FIXME: Tracked (and fixed) in Webkit bug 3537.
			//		http://bugs.webkit.org/show_bug.cgi?id=3537
			var relobj = new d._Url(_a[i]+"");
			var uriobj = new d._Url(uri[0]+"");

			if(
				relobj.path == "" &&
				!relobj.scheme &&
				!relobj.authority &&
				!relobj.query
			){
				if(relobj.fragment != n){
					uriobj.fragment = relobj.fragment;
				}
				relobj = uriobj;
			}else if(!relobj.scheme){
				relobj.scheme = uriobj.scheme;

				if(!relobj.authority){
					relobj.authority = uriobj.authority;

					if(relobj.path.charAt(0) != "/"){
						var path = uriobj.path.substring(0,
							uriobj.path.lastIndexOf("/") + 1) + relobj.path;

						var segs = path.split("/");
						for(var j = 0; j < segs.length; j++){
							if(segs[j] == "."){
								// flatten "./" references
								if(j == segs.length - 1){
									segs[j] = "";
								}else{
									segs.splice(j, 1);
									j--;
								}
							}else if(j > 0 && !(j == 1 && segs[0] == "") &&
								segs[j] == ".." && segs[j-1] != ".."){
								// flatten "../" references
								if(j == (segs.length - 1)){
									segs.splice(j, 1);
									segs[j - 1] = "";
								}else{
									segs.splice(j - 1, 2);
									j -= 2;
								}
							}
						}
						relobj.path = segs.join("/");
					}
				}
			}

			uri = [];
			if(relobj.scheme){
				uri.push(relobj.scheme, ":");
			}
			if(relobj.authority){
				uri.push("//", relobj.authority);
			}
			uri.push(relobj.path);
			if(relobj.query){
				uri.push("?", relobj.query);
			}
			if(relobj.fragment){
				uri.push("#", relobj.fragment);
			}
		}

		this.uri = uri.join("");

		// break the uri into its main components
		var r = this.uri.match(ore);

		this.scheme = r[2] || (r[1] ? "" : n);
		this.authority = r[4] || (r[3] ? "" : n);
		this.path = r[5]; // can never be undefined
		this.query = r[7] || (r[6] ? "" : n);
		this.fragment  = r[9] || (r[8] ? "" : n);

		if(this.authority != n){
			// server based naming authority
			r = this.authority.match(ire);

			this.user = r[3] || n;
			this.password = r[4] || n;
			this.host = r[5];
			this.port = r[7] || n;
		}
	}

	dojo._Url.prototype.toString = function(){ return this.uri; };

	dojo.moduleUrl = function(/*String*/module, /*dojo._Url||String*/url){
		//	summary:
		//		Returns a `dojo._Url` object relative to a module.
		//	example:
		//	|	var pngPath = dojo.moduleUrl("acme","images/small.png");
		//	|	console.dir(pngPath); // list the object properties
		//	|	// create an image and set it's source to pngPath's value:
		//	|	var img = document.createElement("img");
		// 	|	// NOTE: we assign the string representation of the url object
		//	|	img.src = pngPath.toString();
		//	|	// add our image to the document
		//	|	dojo.body().appendChild(img);
		//	example:
		//		you may de-reference as far as you like down the package
		//		hierarchy.  This is sometimes handy to avoid lenghty relative
		//		urls or for building portable sub-packages. In this example,
		//		the `acme.widget` and `acme.util` directories may be located
		//		under different roots (see `dojo.registerModulePath`) but the
		//		the modules which reference them can be unaware of their
		//		relative locations on the filesystem:
		//	|	// somewhere in a configuration block
		//	|	dojo.registerModulePath("acme.widget", "../../acme/widget");
		//	|	dojo.registerModulePath("acme.util", "../../util");
		//	|
		//	|	// ...
		//	|
		//	|	// code in a module using acme resources
		//	|	var tmpltPath = dojo.moduleUrl("acme.widget","templates/template.html");
		//	|	var dataPath = dojo.moduleUrl("acme.util","resources/data.json");

		var loc = d._getModuleSymbols(module).join('/');
		if(!loc){ return null; }
		if(loc.lastIndexOf("/") != loc.length-1){
			loc += "/";
		}

		//If the path is an absolute path (starts with a / or is on another
		//domain/xdomain) then don't add the baseUrl.
		var colonIndex = loc.indexOf(":");
		if(loc.charAt(0) != "/" && (colonIndex == -1 || colonIndex > loc.indexOf("/"))){
			loc = d.baseUrl + loc;
		}

		return new d._Url(loc, url); // String
	}
})();

/*=====
dojo.isBrowser = {
	//	example:
	//	|	if(dojo.isBrowser){ ... }
};

dojo.isFF = {
	//	example:
	//	|	if(dojo.isFF > 1){ ... }
};

dojo.isIE = {
	// example:
	//	|	if(dojo.isIE > 6){
	//	|		// we are IE7
	// 	|	}
};

dojo.isSafari = {
	//	example:
	//	|	if(dojo.isSafari){ ... }
	//	example:
	//		Detect iPhone:
	//	|	if(dojo.isSafari && (navigator.userAgent.indexOf("iPhone") < 0)){
	//	|		// we are iPhone. iPod touch reports "iPod" above
	//	|	}
};

dojo = {
	// isBrowser: Boolean
	//		True if the client is a web-browser
	isBrowser: true,
	//	isFF: Number
	//		Greater than zero if client is FireFox. 0 otherwise. Corresponds to
	//		major detected FireFox version (1.5, 2, 3, etc.)
	isFF: 2,
	//	isIE: Number
	//		Greater than zero if client is MSIE(PC). 0 otherwise. Corresponds to
	//		major detected IE version (6, 7, 8, etc.)
	isIE: 6,
	//	isKhtml: Number
	//		Greater than zero if client is a KTHML-derived browser (Konqueror,
	//		Safari, etc.). 0 otherwise. Corresponds to major detected version.
	isKhtml: 0,
	//	isMozilla: Number
	//		Greater than zero if client is a Mozilla-based browser (Firefox,
	//		SeaMonkey). 0 otherwise. Corresponds to major detected version.
	isMozilla: 0,
	//	isOpera: Number
	//		Greater than zero if client is Opera. 0 otherwise. Corresponds to
	//		major detected version.
	isOpera: 0,
	//	isSafari: Number
	//		Greater than zero if client is Safari or iPhone. 0 otherwise.
	isSafari: 0
}
=====*/

if(typeof window != 'undefined'){
	dojo.isBrowser = true;
	dojo._name = "browser";

	// attempt to figure out the path to dojo if it isn't set in the config
	(function(){
		var d = dojo;
		// this is a scope protection closure. We set browser versions and grab
		// the URL we were loaded from here.

		// grab the node we were loaded from
		if(document && document.getElementsByTagName){
			var scripts = document.getElementsByTagName("script");
			var rePkg = /dojo(\.xd)?\.js(\W|$)/i;
			for(var i = 0; i < scripts.length; i++){
				var src = scripts[i].getAttribute("src");
				if(!src){ continue; }
				var m = src.match(rePkg);
				if(m){
					// find out where we came from
					if(!d.config.baseUrl){
						d.config.baseUrl = src.substring(0, m.index);
					}
					// and find out if we need to modify our behavior
					var cfg = scripts[i].getAttribute("djConfig");
					if(cfg){
						var cfgo = eval("({ "+cfg+" })");
						for(var x in cfgo){
							dojo.config[x] = cfgo[x];
						}
					}
					break; // "first Dojo wins"
				}
			}
		}
		d.baseUrl = d.config.baseUrl;

		// fill in the rendering support information in dojo.render.*
		var n = navigator;
		var dua = n.userAgent;
		var dav = n.appVersion;
		var tv = parseFloat(dav);

		d.isOpera = (dua.indexOf("Opera") >= 0) ? tv : 0;
		// safari detection derived from:
		//		http://developer.apple.com/internet/safari/faq.html#anchor2
		//		http://developer.apple.com/internet/safari/uamatrix.html
		var idx = Math.max(dav.indexOf("WebKit"), dav.indexOf("Safari"), 0);
		if(idx){
			// try to grab the explicit Safari version first. If we don't get
			// one, look for 419.3+ as the indication that we're on something
			// "Safari 3-ish". Lastly, default to "Safari 2" handling.
			d.isSafari = parseFloat(dav.split("Version/")[1]) || ( ( parseFloat(dav.substr(idx+7)) >= 419.3 ) ? 3 : 2 ) || 2;
		}
		d.isAIR = (dua.indexOf("AdobeAIR") >= 0) ? 1 : 0;
		d.isKhtml = (dav.indexOf("Konqueror") >= 0 || d.isSafari) ? tv : 0;
		d.isMozilla = d.isMoz = (dua.indexOf("Gecko") >= 0 && !d.isKhtml) ? tv : 0;
		d.isFF = d.isIE = 0;
		if(d.isMoz){
			d.isFF = parseFloat(dua.split("Firefox/")[1]) || 0;
		}
		if(document.all && !d.isOpera){
			d.isIE = parseFloat(dav.split("MSIE ")[1]) || 0;
		}

		//Workaround to get local file loads of dojo to work on IE 7
		//by forcing to not use native xhr.
		if(dojo.isIE && window.location.protocol === "file:"){
			dojo.config.ieForceActiveXXhr=true;
		}

		var cm = document.compatMode;
		d.isQuirks = cm == "BackCompat" || cm == "QuirksMode" || d.isIE < 6;

		// TODO: is the HTML LANG attribute relevant?
		d.locale = dojo.config.locale || (d.isIE ? n.userLanguage : n.language).toLowerCase();

		// These are in order of decreasing likelihood; this will change in time.
		d._XMLHTTP_PROGIDS = ['Msxml2.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.4.0'];

		d._xhrObj = function(){
			// summary:
			//		does the work of portably generating a new XMLHTTPRequest
			//		object.
			var http = null;
			var last_e = null;
			if(!dojo.isIE || !dojo.config.ieForceActiveXXhr){
				try{ http = new XMLHttpRequest(); }catch(e){}
			}
			if(!http){
				for(var i=0; i<3; ++i){
					var progid = d._XMLHTTP_PROGIDS[i];
					try{
						http = new ActiveXObject(progid);
					}catch(e){
						last_e = e;
					}

					if(http){
						d._XMLHTTP_PROGIDS = [progid];  // so faster next time
						break;
					}
				}
			}

			if(!http){
				throw new Error("XMLHTTP not available: "+last_e);
			}

			return http; // XMLHTTPRequest instance
		}

		d._isDocumentOk = function(http){
			var stat = http.status || 0;
			return (stat >= 200 && stat < 300) || 	// Boolean
				stat == 304 || 						// allow any 2XX response code
				stat == 1223 || 						// get it out of the cache
				(!stat && (location.protocol=="file:" || location.protocol=="chrome:") ); // Internet Explorer mangled the status code
		}

		//See if base tag is in use.
		//This is to fix http://trac.dojotoolkit.org/ticket/3973,
		//but really, we need to find out how to get rid of the dojo._Url reference
		//below and still have DOH work with the dojo.i18n test following some other
		//test that uses the test frame to load a document (trac #2757).
		//Opera still has problems, but perhaps a larger issue of base tag support
		//with XHR requests (hasBase is true, but the request is still made to document
		//path, not base path).
		var owloc = window.location+"";
		var base = document.getElementsByTagName("base");
		var hasBase = (base && base.length > 0);

		d._getText = function(/*URI*/ uri, /*Boolean*/ fail_ok){
			// summary: Read the contents of the specified uri and return those contents.
			// uri:
			//		A relative or absolute uri. If absolute, it still must be in
			//		the same "domain" as we are.
			// fail_ok:
			//		Default false. If fail_ok and loading fails, return null
			//		instead of throwing.
			// returns: The response text. null is returned when there is a
			//		failure and failure is okay (an exception otherwise)

			// alert("_getText: " + uri);

			// NOTE: must be declared before scope switches ie. this._xhrObj()
			var http = this._xhrObj();

			if(!hasBase && dojo._Url){
				uri = (new dojo._Url(owloc, uri)).toString();
			}
			/*
			console.debug("_getText:", uri);
			console.debug(window.location+"");
			alert(uri);
			*/

			if(d.config.cacheBust){
				uri += (uri.indexOf("?") == -1 ? "?" : "&") + String(d.config.cacheBust).replace(/\W+/g,"");
			}

			http.open('GET', uri, false);
			try{
				http.send(null);
				// alert(http);
				if(!d._isDocumentOk(http)){
					var err = Error("Unable to load "+uri+" status:"+ http.status);
					err.status = http.status;
					err.responseText = http.responseText;
					throw err;
				}
			}catch(e){
				if(fail_ok){ return null; } // null
				// rethrow the exception
				throw e;
			}
			return http.responseText; // String
		}
	})();

	dojo._initFired = false;
	//	BEGIN DOMContentLoaded, from Dean Edwards (http://dean.edwards.name/weblog/2006/06/again/)
	dojo._loadInit = function(e){
		dojo._initFired = true;
		// allow multiple calls, only first one will take effect
		// A bug in khtml calls events callbacks for document for event which isnt supported
		// for example a created contextmenu event calls DOMContentLoaded, workaround
		var type = (e && e.type) ? e.type.toLowerCase() : "load";
		if(arguments.callee.initialized || (type != "domcontentloaded" && type != "load")){ return; }
		arguments.callee.initialized = true;
		if("_khtmlTimer" in dojo){
			clearInterval(dojo._khtmlTimer);
			delete dojo._khtmlTimer;
		}

		if(dojo._inFlightCount == 0){
			dojo._modulesLoaded();
		}
	}

	dojo._fakeLoadInit = function(){
		dojo._loadInit({type: "load"});
	}

	if(!dojo.config.afterOnLoad){
		//	START DOMContentLoaded
		// Mozilla and Opera 9 expose the event we could use
		if(document.addEventListener){
			// NOTE:
			//		due to a threading issue in Firefox 2.0, we can't enable
			//		DOMContentLoaded on that platform. For more information, see:
			//		http://trac.dojotoolkit.org/ticket/1704
			if(dojo.isOpera || dojo.isFF >= 3 || (dojo.isMoz && dojo.config.enableMozDomContentLoaded === true)){
				document.addEventListener("DOMContentLoaded", dojo._loadInit, null);
			}

			//	mainly for Opera 8.5, won't be fired if DOMContentLoaded fired already.
			//  also used for Mozilla because of trac #1640
			window.addEventListener("load", dojo._loadInit, null);
		}

		if(dojo.isAIR){
			window.addEventListener("load", dojo._loadInit, null);
		}else if(/(WebKit|khtml)/i.test(navigator.userAgent)){ // sniff
			dojo._khtmlTimer = setInterval(function(){
				if(/loaded|complete/.test(document.readyState)){
					dojo._loadInit(); // call the onload handler
				}
			}, 10);
		}
		//	END DOMContentLoaded
	}

	(function(){
		var _w = window;
		var _handleNodeEvent = function(/*String*/evtName, /*Function*/fp){
			// summary:
			//		non-destructively adds the specified function to the node's
			//		evtName handler.
			// evtName: should be in the form "onclick" for "onclick" handlers.
			// Make sure you pass in the "on" part.
			var oldHandler = _w[evtName] || function(){};
			_w[evtName] = function(){
				fp.apply(_w, arguments);
				oldHandler.apply(_w, arguments);
			};
		};

		if(dojo.isIE){
			// 	for Internet Explorer. readyState will not be achieved on init
			// 	call, but dojo doesn't need it however, we'll include it
			// 	because we don't know if there are other functions added that
			// 	might.  Note that this has changed because the build process
			// 	strips all comments -- including conditional ones.
			if(!dojo.config.afterOnLoad){
				document.write('<scr'+'ipt defer src="//:" '
					+ 'onreadystatechange="if(this.readyState==\'complete\'){' + dojo._scopeName + '._loadInit();}">'
					+ '</scr'+'ipt>'
				);
			}

			// IE WebControl hosted in an application can fire "beforeunload" and "unload"
			// events when control visibility changes, causing Dojo to unload too soon. The
			// following code fixes the problem
			// Reference: http://support.microsoft.com/default.aspx?scid=kb;en-us;199155
			var _unloading = true;
			_handleNodeEvent("onbeforeunload", function(){
				_w.setTimeout(function(){ _unloading = false; }, 0);
			});
			_handleNodeEvent("onunload", function(){
				if(_unloading){ dojo.unloaded(); }
			});

			try{
				document.namespaces.add("v","urn:schemas-microsoft-com:vml");
				document.createStyleSheet().addRule("v\\:*", "behavior:url(#default#VML)");
			}catch(e){}
		}else{
			// FIXME: dojo.unloaded requires dojo scope, so using anon function wrapper.
			_handleNodeEvent("onbeforeunload", function() { dojo.unloaded(); });
		}

	})();

	/*
	OpenAjax.subscribe("OpenAjax", "onload", function(){
		if(dojo._inFlightCount == 0){
			dojo._modulesLoaded();
		}
	});

	OpenAjax.subscribe("OpenAjax", "onunload", function(){
		dojo.unloaded();
	});
	*/
} //if (typeof window != 'undefined')

//Register any module paths set up in djConfig. Need to do this
//in the hostenvs since hostenv_browser can read djConfig from a
//script tag's attribute.
(function(){
	var mp = dojo.config["modulePaths"];
	if(mp){
		for(var param in mp){
			dojo.registerModulePath(param, mp[param]);
		}
	}
})();

//Load debug code if necessary.
if(dojo.config.isDebug){
	dojo.require("dojo._firebug.firebug");
}

if(dojo.config.debugAtAllCosts){
	dojo.config.useXDomain = true;
	dojo.require("dojo._base._loader.loader_xd");
	dojo.require("dojo._base._loader.loader_debug");
	dojo.require("dojo.i18n");
}

if(!dojo._hasResource["dojo._base.lang"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.lang"] = true;
dojo.provide("dojo._base.lang");

// Crockford (ish) functions

dojo.isString = function(/*anything*/ it){
	//	summary:
	//		Return true if it is a String
	return !!arguments.length && it != null && (typeof it == "string" || it instanceof String); // Boolean
}

dojo.isArray = function(/*anything*/ it){
	//	summary:
	//		Return true if it is an Array
	return it && (it instanceof Array || typeof it == "array"); // Boolean
}

/*=====
dojo.isFunction = function(it){
	// summary: Return true if it is a Function
	// it: anything
	//	return: Boolean
}
=====*/

dojo.isFunction = (function(){
	var _isFunction = function(/*anything*/ it){
		return it && (typeof it == "function" || it instanceof Function); // Boolean
	};

	return dojo.isSafari ?
		// only slow this down w/ gratuitious casting in Safari since it's what's b0rken
		function(/*anything*/ it){
			if(typeof it == "function" && it == "[object NodeList]"){ return false; }
			return _isFunction(it); // Boolean
		} : _isFunction;
})();

dojo.isObject = function(/*anything*/ it){
	// summary:
	//		Returns true if it is a JavaScript object (or an Array, a Function
	//		or null)
	return it !== undefined &&
		(it === null || typeof it == "object" || dojo.isArray(it) || dojo.isFunction(it)); // Boolean
}

dojo.isArrayLike = function(/*anything*/ it){
	//	summary:
	//		similar to dojo.isArray() but more permissive
	//	description:
	//		Doesn't strongly test for "arrayness".  Instead, settles for "isn't
	//		a string or number and has a length property". Arguments objects
	//		and DOM collections will return true when passed to
	//		dojo.isArrayLike(), but will return false when passed to
	//		dojo.isArray().
	//	return:
	//		If it walks like a duck and quicks like a duck, return `true`
	var d = dojo;
	return it && it !== undefined &&
		// keep out built-in constructors (Number, String, ...) which have length
		// properties
		!d.isString(it) && !d.isFunction(it) &&
		!(it.tagName && it.tagName.toLowerCase() == 'form') &&
		(d.isArray(it) || isFinite(it.length)); // Boolean
}

dojo.isAlien = function(/*anything*/ it){
	// summary:
	//		Returns true if it is a built-in function or some other kind of
	//		oddball that *should* report as a function but doesn't
	return it && !dojo.isFunction(it) && /\{\s*\[native code\]\s*\}/.test(String(it)); // Boolean
}

dojo.extend = function(/*Object*/ constructor, /*Object...*/ props){
	// summary:
	//		Adds all properties and methods of props to constructor's
	//		prototype, making them available to all instances created with
	//		constructor.
	for(var i=1, l=arguments.length; i<l; i++){
		dojo._mixin(constructor.prototype, arguments[i]);
	}
	return constructor; // Object
}

dojo._hitchArgs = function(scope, method /*,...*/){
	var pre = dojo._toArray(arguments, 2);
	var named = dojo.isString(method);
	return function(){
		// arrayify arguments
		var args = dojo._toArray(arguments);
		// locate our method
		var f = named ? (scope||dojo.global)[method] : method;
		// invoke with collected args
		return f && f.apply(scope || this, pre.concat(args)); // mixed
 	} // Function
}

dojo.hitch = function(/*Object*/scope, /*Function|String*/method /*,...*/){
	//	summary:
	//		Returns a function that will only ever execute in the a given scope.
	//		This allows for easy use of object member functions
	//		in callbacks and other places in which the "this" keyword may
	//		otherwise not reference the expected scope.
	//		Any number of default positional arguments may be passed as parameters
	//		beyond "method".
	//		Each of these values will be used to "placehold" (similar to curry)
	//		for the hitched function.
	//	scope:
	//		The scope to use when method executes. If method is a string,
	//		scope is also the object containing method.
	//	method:
	//		A function to be hitched to scope, or the name of the method in
	//		scope to be hitched.
	//	example:
	//	|	dojo.hitch(foo, "bar")();
	//		runs foo.bar() in the scope of foo
	//	example:
	//	|	dojo.hitch(foo, myFunction);
	//		returns a function that runs myFunction in the scope of foo
	if(arguments.length > 2){
		return dojo._hitchArgs.apply(dojo, arguments); // Function
	}
	if(!method){
		method = scope;
		scope = null;
	}
	if(dojo.isString(method)){
		scope = scope || dojo.global;
		if(!scope[method]){ throw(['dojo.hitch: scope["', method, '"] is null (scope="', scope, '")'].join('')); }
		return function(){ return scope[method].apply(scope, arguments || []); }; // Function
	}
	return !scope ? method : function(){ return method.apply(scope, arguments || []); }; // Function
}

/*=====
dojo.delegate = function(obj, props){
	//	summary:
	//		returns a new object which "looks" to obj for properties which it
	//		does not have a value for. Optionally takes a bag of properties to
	//		seed the returned object with initially.
	//	description:
	//		This is a small implementaton of the Boodman/Crockford delegation
	//		pattern in JavaScript. An intermediate object constructor mediates
	//		the prototype chain for the returned object, using it to delegate
	//		down to obj for property lookup when object-local lookup fails.
	//		This can be thought of similarly to ES4's "wrap", save that it does
	//		not act on types but rather on pure objects.
	//	obj:
	//		The object to delegate to for properties not found directly on the
	//		return object or in props.
	//	props:
	//		an object containing properties to assign to the returned object
	//	returns:
	//		an Object of anonymous type
	//	example:
	//	|	var foo = { bar: "baz" };
	//	|	var thinger = dojo.delegate(foo, { thud: "xyzzy"});
	//	|	thinger.bar == "baz"; // delegated to foo
	//	|	foo.thud == undefined; // by definition
	//	|	thinger.thud == "xyzzy"; // mixed in from props
	//	|	foo.bar = "thonk";
	//	|	thinger.bar == "thonk"; // still delegated to foo's bar
}
=====*/

dojo.delegate = dojo._delegate = function(obj, props){

	// boodman/crockford delegation
	function TMP(){};
	TMP.prototype = obj;
	var tmp = new TMP();
	if(props){
		dojo.mixin(tmp, props);
	}
	return tmp; // Object
}

dojo.partial = function(/*Function|String*/method /*, ...*/){
	//	summary:
	//		similar to hitch() except that the scope object is left to be
	//		whatever the execution context eventually becomes.
	//	description:
	//		Calling dojo.partial is the functional equivalent of calling:
	//		|	dojo.hitch(null, funcName, ...);
	var arr = [ null ];
	return dojo.hitch.apply(dojo, arr.concat(dojo._toArray(arguments))); // Function
}

dojo._toArray = function(/*Object*/obj, /*Number?*/offset, /*Array?*/ startWith){
	//	summary:
	//		Converts an array-like object (i.e. arguments, DOMCollection) to an
	//		array. Returns a new Array with the elements of obj.
	//	obj:
	//		the object to "arrayify". We expect the object to have, at a
	//		minimum, a length property which corresponds to integer-indexed
	//		properties.
	//	offset:
	//		the location in obj to start iterating from. Defaults to 0.
	//		Optional.
	//	startWith:
	//		An array to pack with the properties of obj. If provided,
	//		properties in obj are appended at the end of startWith and
	//		startWith is the returned array.
	var arr = startWith||[];
	for(var x = offset || 0; x < obj.length; x++){
		arr.push(obj[x]);
	}
	return arr; // Array
}

dojo.clone = function(/*anything*/ o){
	// summary:
	//		Clones objects (including DOM nodes) and all children.
	//		Warning: do not clone cyclic structures.
	if(!o){ return o; }
	if(dojo.isArray(o)){
		var r = [];
		for(var i = 0; i < o.length; ++i){
			r.push(dojo.clone(o[i]));
		}
		return r; // Array
	}
	if(!dojo.isObject(o)){
		return o;	/*anything*/
	}
	if(o.nodeType && o.cloneNode){ // isNode
		return o.cloneNode(true); // Node
	}
	if(o instanceof Date){
		return new Date(o.getTime());	// Date
	}
	// Generic objects
	var r = new o.constructor(); // specific to dojo.declare()'d classes!
	for(var i in o){
		if(!(i in r) || r[i] != o[i]){
			r[i] = dojo.clone(o[i]);
		}
	}
	return r; // Object
}

dojo.trim = function(/*String*/ str){
	// summary:
	//		trims whitespaces from both sides of the string
	// description:
	//		This version of trim() was selected for inclusion into the base due
	//		to its compact size and relatively good performance (see Steven
	//		Levithan's blog:
	//		http://blog.stevenlevithan.com/archives/faster-trim-javascript).
	//		The fastest but longest version of this function is located at
	//		dojo.string.trim()
	return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');	// String
}

}

if(!dojo._hasResource["dojo._base.declare"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.declare"] = true;
dojo.provide("dojo._base.declare");

// this file courtesy of the TurboAjax group, licensed under a Dojo CLA

dojo.declare = function(/*String*/ className, /*Function|Function[]*/ superclass, /*Object*/ props){
	//	summary:
	//		Create a feature-rich constructor from compact notation
	//	className:
	//		The name of the constructor (loosely, a "class")
	//		stored in the "declaredClass" property in the created prototype
	//	superclass:
	//		May be null, a Function, or an Array of Functions. If an array,
	//		the first element is used as the prototypical ancestor and
	//		any following Functions become mixin ancestors.
	//	props:
	//		An object whose properties are copied to the
	//		created prototype.
	//		Add an instance-initialization function by making it a property
	//		named "constructor".
	//	description:
	//		Create a constructor using a compact notation for inheritance and
	//		prototype extension.
	//
	//		All superclasses (including mixins) must be Functions (not simple Objects).
	//
	//		Mixin ancestors provide a type of multiple inheritance. Prototypes of mixin
	//		ancestors are copied to the new class: changes to mixin prototypes will
	//		not affect classes to which they have been mixed in.
	//
	//		"className" is cached in "declaredClass" property of the new class.
	//
	//	example:
	//	|	dojo.declare("my.classes.bar", my.classes.foo, {
	//	|		// properties to be added to the class prototype
	//	|		someValue: 2,
	//	|		// initialization function
	//	|		constructor: function(){
	//	|			this.myComplicatedObject = new ReallyComplicatedObject();
	//	|		},
	//	|		// other functions
	//	|		someMethod: function(){
	//	|			doStuff();
	//	|		}
	//	|	);

	// process superclass argument
	// var dd=dojo.declare, mixins=null;
	var dd = arguments.callee, mixins;
	if(dojo.isArray(superclass)){
		mixins = superclass;
		superclass = mixins.shift();
	}
	// construct intermediate classes for mixins
	if(mixins){
		dojo.forEach(mixins, function(m){
			if(!m){ throw(className + ": mixin #" + i + " is null"); } // It's likely a required module is not loaded
			superclass = dd._delegate(superclass, m);
		});
	}
	// prepare values
	var init = (props||0).constructor, ctor = dd._delegate(superclass), fn;
	// name methods (experimental)
	for(var i in props){ if(dojo.isFunction(fn = props[i]) && !0[i]){fn.nom = i;} } // 0[i] checks Object.prototype
	// decorate prototype
	dojo.extend(ctor, {declaredClass: className, _constructor: init, preamble: null}, props || 0);
	// special help for IE
	ctor.prototype.constructor = ctor;
	// create named reference
	return dojo.setObject(className, ctor); // Function
};

dojo.mixin(dojo.declare, {
	_delegate: function(base, mixin){
		var bp = (base||0).prototype, mp = (mixin||0).prototype;
		// fresh constructor, fresh prototype
		var ctor = dojo.declare._makeCtor();
		// cache ancestry
		dojo.mixin(ctor, {superclass: bp, mixin: mp, extend: dojo.declare._extend});
		// chain prototypes
		if(base){ctor.prototype = dojo._delegate(bp);}
		// add mixin and core
		dojo.extend(ctor, dojo.declare._core, mp||0, {_constructor: null, preamble: null});
		// special help for IE
		ctor.prototype.constructor = ctor;
		// name this class for debugging
		ctor.prototype.declaredClass = (bp||0).declaredClass + '_' + (mp||0).declaredClass;
		return ctor;
	},
	_extend: function(props){
		for(var i in props){ if(dojo.isFunction(fn=props[i]) && !0[i]){fn.nom=i;} }
		dojo.extend(this, props);
	},
	_makeCtor: function(){
		// we have to make a function, but don't want to close over anything
		return function(){ this._construct(arguments); };
	},
	_core: {
		_construct: function(args){
			var c=args.callee, s=c.superclass, ct=s&&s.constructor, m=c.mixin, mct=m&&m.constructor, a=args, ii, fn;
			// side-effect of = used on purpose here, lint may complain, don't try this at home
			if(a[0]){
				// FIXME: preambles for each mixin should be allowed
				// FIXME:
				//		should we allow the preamble here NOT to modify the
				//		default args, but instead to act on each mixin
				//		independently of the class instance being constructed
				//		(for impedence matching)?

				// allow any first argument w/ a "preamble" property to act as a
				// class preamble (not exclusive of the prototype preamble)
				if(/*dojo.isFunction*/((fn = a[0].preamble))){
					a = fn.apply(this, a) || a;
				}
			}
			// prototype preamble
			if((fn = c.prototype.preamble)){a = fn.apply(this, a) || a;}
			// FIXME:
			//		need to provide an optional prototype-settable
			//		"_explicitSuper" property which disables this
			// initialize superclass
			if(ct&&ct.apply){ct.apply(this, a);}
			// initialize mixin
			if(mct&&mct.apply){mct.apply(this, a);}
			// initialize self
			if((ii=c.prototype._constructor)){ii.apply(this, args);}
			// post construction
			if(this.constructor.prototype==c.prototype && (ct=this.postscript)){ ct.apply(this, args); }
		},
		_findMixin: function(mixin){
			var c = this.constructor, p, m;
			while(c){
				p = c.superclass;
				m = c.mixin;
				if(m==mixin || (m instanceof mixin.constructor)){return p;}
				if(m && (m=m._findMixin(mixin))){return m;}
				c = p && p.constructor;
			}
		},
		_findMethod: function(name, method, ptype, has){
			// consciously trading readability for bytes and speed in this low-level method
			var p=ptype, c, m, f;
			do{
				c = p.constructor;
				m = c.mixin;
				// find method by name in our mixin ancestor
				if(m && (m=this._findMethod(name, method, m, has))){return m;}
				// if we found a named method that either exactly-is or exactly-is-not 'method'
				if((f=p[name])&&(has==(f==method))){return p;}
				// ascend chain
				p = c.superclass;
			}while(p);
			// if we couldn't find an ancestor in our primary chain, try a mixin chain
			return !has && (p=this._findMixin(ptype)) && this._findMethod(name, method, p, has);
		},
		inherited: function(name, args, newArgs){
			// optionalize name argument (experimental)
			var a = arguments;
			if(!dojo.isString(a[0])){newArgs=args; args=name; name=args.callee.nom;}
			a = newArgs||args;
			var c = args.callee, p = this.constructor.prototype, fn, mp;
			// if not an instance override
			if(this[name] != c || p[name] == c){
				mp = this._findMethod(name, c, p, true);
				if(!mp){throw(this.declaredClass + ': inherited method "' + name + '" mismatch');}
				p = this._findMethod(name, c, mp, false);
			}
			fn = p && p[name];
			if(!fn){throw(mp.declaredClass + ': inherited method "' + name + '" not found');}
			// if the function exists, invoke it in our scope
			return fn.apply(this, a);
		}
	}
});

}

if(!dojo._hasResource["dojo._base.connect"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.connect"] = true;
dojo.provide("dojo._base.connect");

// this file courtesy of the TurboAjax Group, licensed under a Dojo CLA

// low-level delegation machinery
dojo._listener = {
	// create a dispatcher function
	getDispatcher: function(){
		// following comments pulled out-of-line to prevent cloning them
		// in the returned function.
		// - indices (i) that are really in the array of listeners (ls) will
		//   not be in Array.prototype. This is the 'sparse array' trick
		//   that keeps us safe from libs that take liberties with built-in
		//   objects
		// - listener is invoked with current scope (this)
		return function(){
			var ap=Array.prototype, c=arguments.callee, ls=c._listeners, t=c.target;
			// return value comes from original target function
			var r=t && t.apply(this, arguments);
			// invoke listeners after target function
			for(var i in ls){
				if(!(i in ap)){
					ls[i].apply(this, arguments);
				}
			}
			// return value comes from original target function
			return r;
		}
	},
	// add a listener to an object
	add: function(/*Object*/ source, /*String*/ method, /*Function*/ listener){
		// Whenever 'method' is invoked, 'listener' will have the same scope.
		// Trying to supporting a context object for the listener led to
		// complexity.
		// Non trivial to provide 'once' functionality here
		// because listener could be the result of a dojo.hitch call,
		// in which case two references to the same hitch target would not
		// be equivalent.
		source = source || dojo.global;
		// The source method is either null, a dispatcher, or some other function
		var f = source[method];
		// Ensure a dispatcher
		if(!f||!f._listeners){
			var d = dojo._listener.getDispatcher();
			// original target function is special
			d.target = f;
			// dispatcher holds a list of listeners
			d._listeners = [];
			// redirect source to dispatcher
			f = source[method] = d;
		}
		// The contract is that a handle is returned that can
		// identify this listener for disconnect.
		//
		// The type of the handle is private. Here is it implemented as Integer.
		// DOM event code has this same contract but handle is Function
		// in non-IE browsers.
		//
		// We could have separate lists of before and after listeners.
		return f._listeners.push(listener) ; /*Handle*/
	},
	// remove a listener from an object
	remove: function(/*Object*/ source, /*String*/ method, /*Handle*/ handle){
		var f = (source||dojo.global)[method];
		// remember that handle is the index+1 (0 is not a valid handle)
		if(f && f._listeners && handle--){
			delete f._listeners[handle];
		}
	}
};

// Multiple delegation for arbitrary methods.

// This unit knows nothing about DOM,
// but we include DOM aware
// documentation and dontFix
// argument here to help the autodocs.
// Actual DOM aware code is in event.js.

dojo.connect = function(/*Object|null*/ obj,
						/*String*/ event,
						/*Object|null*/ context,
						/*String|Function*/ method,
						/*Boolean*/ dontFix){
	// summary:
	//		Create a link that calls one function when another executes.
	//
	// description:
	//		Connects method to event, so that after event fires, method
	//		does too. All connected functions are passed the same arguments as
	//		the event function was initially called with. You may connect as
	//		many methods to event as needed.
	//
	//		event must be a string. If obj is null, dojo.global is used.
	//
	//		null arguments may simply be omitted.
	//
	//		obj[event] can resolve to a function or undefined (null).
	//		If obj[event] is null, it is assigned a function.
	//
	//		The return value is a handle that is needed to
	//		remove this connection with dojo.disconnect.
	//
	// obj:
	//		The source object for the event function.
	//		Defaults to dojo.global if null.
	//		If obj is a DOM node, the connection is delegated
	//		to the DOM event manager (unless dontFix is true).
	//
	// event:
	//		String name of the event function in obj.
	//		I.e. identifies a property obj[event].
	//
	// context:
	//		The object that method will receive as "this".
	//
	//		If context is null and method is a function, then method
	//		inherits the context of event.
	//
	//		If method is a string then context must be the source
	//		object object for method (context[method]). If context is null,
	//		dojo.global is used.
	//
	// method:
	//		A function reference, or name of a function in context.
	//		The function identified by method fires after event does.
	//		method receives the same arguments as the event.
	//		See context argument comments for information on method's scope.
	//
	// dontFix:
	//		If obj is a DOM node, set dontFix to true to prevent delegation
	//		of this connection to the DOM event manager.
	//
	// example:
	//		When obj.onchange(), do ui.update():
	//	|	dojo.connect(obj, "onchange", ui, "update");
	//	|	dojo.connect(obj, "onchange", ui, ui.update); // same
	//
	// example:
	//		Using return value for disconnect:
	//	|	var link = dojo.connect(obj, "onchange", ui, "update");
	//	|	...
	//	|	dojo.disconnect(link);
	//
	// example:
	//		When onglobalevent executes, watcher.handler is invoked:
	//	|	dojo.connect(null, "onglobalevent", watcher, "handler");
	//
	// example:
	//		When ob.onCustomEvent executes, customEventHandler is invoked:
	//	|	dojo.connect(ob, "onCustomEvent", null, "customEventHandler");
	//	|	dojo.connect(ob, "onCustomEvent", "customEventHandler"); // same
	//
	// example:
	//		When ob.onCustomEvent executes, customEventHandler is invoked
	//		with the same scope (this):
	//	|	dojo.connect(ob, "onCustomEvent", null, customEventHandler);
	//	|	dojo.connect(ob, "onCustomEvent", customEventHandler); // same
	//
	// example:
	//		When globalEvent executes, globalHandler is invoked
	//		with the same scope (this):
	//	|	dojo.connect(null, "globalEvent", null, globalHandler);
	//	|	dojo.connect("globalEvent", globalHandler); // same

	// normalize arguments
	var a=arguments, args=[], i=0;
	// if a[0] is a String, obj was ommited
	args.push(dojo.isString(a[0]) ? null : a[i++], a[i++]);
	// if the arg-after-next is a String or Function, context was NOT omitted
	var a1 = a[i+1];
	args.push(dojo.isString(a1)||dojo.isFunction(a1) ? a[i++] : null, a[i++]);
	// absorb any additional arguments
	for(var l=a.length; i<l; i++){	args.push(a[i]); }
	// do the actual work
	return dojo._connect.apply(this, args); /*Handle*/
}

// used by non-browser hostenvs. always overriden by event.js
dojo._connect = function(obj, event, context, method){
	var l=dojo._listener, h=l.add(obj, event, dojo.hitch(context, method));
	return [obj, event, h, l]; // Handle
}

dojo.disconnect = function(/*Handle*/ handle){
	// summary:
	//		Remove a link created by dojo.connect.
	// description:
	//		Removes the connection between event and the method referenced by handle.
	// handle:
	//		the return value of the dojo.connect call that created the connection.
	if(handle && handle[0] !== undefined){
		dojo._disconnect.apply(this, handle);
		// let's not keep this reference
		delete handle[0];
	}
}

dojo._disconnect = function(obj, event, handle, listener){
	listener.remove(obj, event, handle);
}

// topic publish/subscribe

dojo._topics = {};

dojo.subscribe = function(/*String*/ topic, /*Object|null*/ context, /*String|Function*/ method){
	//	summary:
	//		Attach a listener to a named topic. The listener function is invoked whenever the
	//		named topic is published (see: dojo.publish).
	//		Returns a handle which is needed to unsubscribe this listener.
	//	context:
	//		Scope in which method will be invoked, or null for default scope.
	//	method:
	//		The name of a function in context, or a function reference. This is the function that
	//		is invoked when topic is published.
	//	example:
	//	|	dojo.subscribe("alerts", null, function(caption, message){ alert(caption + "\n" + message); };
	//	|	dojo.publish("alerts", [ "read this", "hello world" ]);

	// support for 2 argument invocation (omitting context) depends on hitch
	return [topic, dojo._listener.add(dojo._topics, topic, dojo.hitch(context, method))]; /*Handle*/
}

dojo.unsubscribe = function(/*Handle*/ handle){
	//	summary:
	//	 	Remove a topic listener.
	//	handle:
	//	 	The handle returned from a call to subscribe.
	//	example:
	//	|	var alerter = dojo.subscribe("alerts", null, function(caption, message){ alert(caption + "\n" + message); };
	//	|	...
	//	|	dojo.unsubscribe(alerter);
	if(handle){
		dojo._listener.remove(dojo._topics, handle[0], handle[1]);
	}
}

dojo.publish = function(/*String*/ topic, /*Array*/ args){
	//	summary:
	//	 	Invoke all listener method subscribed to topic.
	//	topic:
	//	 	The name of the topic to publish.
	//	args:
	//	 	An array of arguments. The arguments will be applied
	//	 	to each topic subscriber (as first class parameters, via apply).
	//	example:
	//	|	dojo.subscribe("alerts", null, function(caption, message){ alert(caption + "\n" + message); };
	//	|	dojo.publish("alerts", [ "read this", "hello world" ]);

	// Note that args is an array, which is more efficient vs variable length
	// argument list.  Ideally, var args would be implemented via Array
	// throughout the APIs.
	var f = dojo._topics[topic];
	if(f){
		f.apply(this, args||[]);
	}
}

dojo.connectPublisher = function(	/*String*/ topic,
									/*Object|null*/ obj,
									/*String*/ event){
	//	summary:
	//	 	Ensure that everytime obj.event() is called, a message is published
	//	 	on the topic. Returns a handle which can be passed to
	//	 	dojo.disconnect() to disable subsequent automatic publication on
	//	 	the topic.
	//	topic:
	//	 	The name of the topic to publish.
	//	obj:
	//	 	The source object for the event function. Defaults to dojo.global
	//	 	if null.
	//	event:
	//	 	The name of the event function in obj.
	//	 	I.e. identifies a property obj[event].
	//	example:
	//	|	dojo.connectPublisher("/ajax/start", dojo, "xhrGet"};
	var pf = function(){ dojo.publish(topic, arguments); }
	return (event) ? dojo.connect(obj, event, pf) : dojo.connect(obj, pf); //Handle
};

}

if(!dojo._hasResource["dojo._base.Deferred"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.Deferred"] = true;
dojo.provide("dojo._base.Deferred");

dojo.Deferred = function(/*Function?*/ canceller){
	// summary:
	//		Encapsulates a sequence of callbacks in response to a value that
	//		may not yet be available.  This is modeled after the Deferred class
	//		from Twisted <http://twistedmatrix.com>.
	// description:
	//		JavaScript has no threads, and even if it did, threads are hard.
	//		Deferreds are a way of abstracting non-blocking events, such as the
	//		final response to an XMLHttpRequest. Deferreds create a promise to
	//		return a response a some point in the future and an easy way to
	//		register your interest in receiving that response.
	//
	//		The most important methods for Deffered users are:
	//
	//			* addCallback(handler)
	//			* addErrback(handler)
	//			* callback(result)
	//			* errback(result)
	//
	//		In general, when a function returns a Deferred, users then "fill
	//		in" the second half of the contract by registering callbacks and
	//		error handlers. You may register as many callback and errback
	//		handlers as you like and they will be executed in the order
	//		registered when a result is provided. Usually this result is
	//		provided as the result of an asynchronous operation. The code
	//		"managing" the Deferred (the code that made the promise to provide
	//		an answer later) will use the callback() and errback() methods to
	//		communicate with registered listeners about the result of the
	//		operation. At this time, all registered result handlers are called
	//		*with the most recent result value*.
	//
	//		Deferred callback handlers are treated as a chain, and each item in
	//		the chain is required to return a value that will be fed into
	//		successive handlers. The most minimal callback may be registered
	//		like this:
	//
	//		|	var d = new dojo.Deferred();
	//		|	d.addCallback(function(result){ return result; });
	//
	//		Perhaps the most common mistake when first using Deferreds is to
	//		forget to return a value (in most cases, the value you were
	//		passed).
	//
	//		The sequence of callbacks is internally represented as a list of
	//		2-tuples containing the callback/errback pair.  For example, the
	//		following call sequence:
	//
	//		|	var d = new dojo.Deferred();
	//		|	d.addCallback(myCallback);
	//		|	d.addErrback(myErrback);
	//		|	d.addBoth(myBoth);
	//		|	d.addCallbacks(myCallback, myErrback);
	//
	//		is translated into a Deferred with the following internal
	//		representation:
	//
	//		|	[
	//		|		[myCallback, null],
	//		|		[null, myErrback],
	//		|		[myBoth, myBoth],
	//		|		[myCallback, myErrback]
	//		|	]
	//
	//		The Deferred also keeps track of its current status (fired).  Its
	//		status may be one of three things:
	//
	//			* -1: no value yet (initial condition)
	//			* 0: success
	//			* 1: error
	//
	//		A Deferred will be in the error state if one of the following three
	//		conditions are met:
	//
	//			1. The result given to callback or errback is "instanceof" Error
	//			2. The previous callback or errback raised an exception while
	//			   executing
	//			3. The previous callback or errback returned a value
	//			   "instanceof" Error
	//
	//		Otherwise, the Deferred will be in the success state. The state of
	//		the Deferred determines the next element in the callback sequence
	//		to run.
	//
	//		When a callback or errback occurs with the example deferred chain,
	//		something equivalent to the following will happen (imagine
	//		that exceptions are caught and returned):
	//
	//		|	// d.callback(result) or d.errback(result)
	//		|	if(!(result instanceof Error)){
	//		|		result = myCallback(result);
	//		|	}
	//		|	if(result instanceof Error){
	//		|		result = myErrback(result);
	//		|	}
	//		|	result = myBoth(result);
	//		|	if(result instanceof Error){
	//		|		result = myErrback(result);
	//		|	}else{
	//		|		result = myCallback(result);
	//		|	}
	//
	//		The result is then stored away in case another step is added to the
	//		callback sequence.	Since the Deferred already has a value
	//		available, any new callbacks added will be called immediately.
	//
	//		There are two other "advanced" details about this implementation
	//		that are useful:
	//
	//		Callbacks are allowed to return Deferred instances themselves, so
	//		you can build complicated sequences of events with ease.
	//
	//		The creator of the Deferred may specify a canceller.  The canceller
	//		is a function that will be called if Deferred.cancel is called
	//		before the Deferred fires. You can use this to implement clean
	//		aborting of an XMLHttpRequest, etc. Note that cancel will fire the
	//		deferred with a CancelledError (unless your canceller returns
	//		another kind of error), so the errbacks should be prepared to
	//		handle that error for cancellable Deferreds.
	// example:
	//	|	var deferred = new dojo.Deferred();
	//	|	setTimeout(function(){ deferred.callback({success: true}); }, 1000);
	//	|	return deferred;
	// example:
	//		Deferred objects are often used when making code asynchronous. It
	//		may be easiest to write functions in a synchronous manner and then
	//		split code using a deferred to trigger a response to a long-lived
	//		operation. For example, instead of register a callback function to
	//		denote when a rendering operation completes, the function can
	//		simply return a deferred:
	//
	//		|	// callback style:
	//		|	function renderLotsOfData(data, callback){
	//		|		var success = false
	//		|		try{
	//		|			for(var x in data){
	//		|				renderDataitem(data[x]);
	//		|			}
	//		|			success = true;
	//		|		}catch(e){ }
	//		|		if(callback){
	//		|			callback(success);
	//		|		}
	//		|	}
	//
	//		|	// using callback style
	//		|	renderLotsOfData(someDataObj, function(success){
	//		|		// handles success or failure
	//		|		if(!success){
	//		|			promptUserToRecover();
	//		|		}
	//		|	});
	//		|	// NOTE: no way to add another callback here!!
	// example:
	//		Using a Deferred doesn't simplify the sending code any, but it
	//		provides a standard interface for callers and senders alike,
	//		providing both with a simple way to service multiple callbacks for
	//		an operation and freeing both sides from worrying about details
	//		such as "did this get called already?". With Deferreds, new
	//		callbacks can be added at any time.
	//
	//		|	// Deferred style:
	//		|	function renderLotsOfData(data){
	//		|		var d = new dojo.Deferred();
	//		|		try{
	//		|			for(var x in data){
	//		|				renderDataitem(data[x]);
	//		|			}
	//		|			d.callback(true);
	//		|		}catch(e){
	//		|			d.errback(new Error("rendering failed"));
	//		|		}
	//		|		return d;
	//		|	}
	//
	//		|	// using Deferred style
	//		|	renderLotsOfData(someDataObj).addErrback(function(){
	//		|		promptUserToRecover();
	//		|	});
	//		|	// NOTE: addErrback and addCallback both return the Deferred
	//		|	// again, so we could chain adding callbacks or save the
	//		|	// deferred for later should we need to be notified again.
	// example:
	//		In this example, renderLotsOfData is syncrhonous and so both
	//		versions are pretty artificial. Putting the data display on a
	//		timeout helps show why Deferreds rock:
	//
	//		|	// Deferred style and async func
	//		|	function renderLotsOfData(data){
	//		|		var d = new dojo.Deferred();
	//		|		setTimeout(function(){
	//		|			try{
	//		|				for(var x in data){
	//		|					renderDataitem(data[x]);
	//		|				}
	//		|				d.callback(true);
	//		|			}catch(e){
	//		|				d.errback(new Error("rendering failed"));
	//		|			}
	//		|		}, 100);
	//		|		return d;
	//		|	}
	//
	//		|	// using Deferred style
	//		|	renderLotsOfData(someDataObj).addErrback(function(){
	//		|		promptUserToRecover();
	//		|	});
	//
	//		Note that the caller doesn't have to change his code at all to
	//		handle the asynchronous case.

	this.chain = [];
	this.id = this._nextId();
	this.fired = -1;
	this.paused = 0;
	this.results = [null, null];
	this.canceller = canceller;
	this.silentlyCancelled = false;
};

dojo.extend(dojo.Deferred, {
	/*
	makeCalled: function(){
		// summary:
		//		returns a new, empty deferred, which is already in the called
		//		state. Calling callback() or errback() on this deferred will
		//		yeild an error and adding new handlers to it will result in
		//		them being called immediately.
		var deferred = new dojo.Deferred();
		deferred.callback();
		return deferred;
	},

	toString: function(){
		var state;
		if(this.fired == -1){
			state = 'unfired';
		}else{
			state = this.fired ? 'success' : 'error';
		}
		return 'Deferred(' + this.id + ', ' + state + ')';
	},
	*/

	_nextId: (function(){
		var n = 1;
		return function(){ return n++; };
	})(),

	cancel: function(){
		// summary:
		//		Cancels a Deferred that has not yet received a value, or is
		//		waiting on another Deferred as its value.
		// description:
		//		If a canceller is defined, the canceller is called. If the
		//		canceller did not return an error, or there was no canceller,
		//		then the errback chain is started.
		var err;
		if(this.fired == -1){
			if(this.canceller){
				err = this.canceller(this);
			}else{
				this.silentlyCancelled = true;
			}
			if(this.fired == -1){
				if(!(err instanceof Error)){
					var res = err;
					err = new Error("Deferred Cancelled");
					err.dojoType = "cancel";
					err.cancelResult = res;
				}
				this.errback(err);
			}
		}else if(	(this.fired == 0) &&
					(this.results[0] instanceof dojo.Deferred)
		){
			this.results[0].cancel();
		}
	},

	_resback: function(res){
		// summary:
		//		The private primitive that means either callback or errback
		this.fired = ((res instanceof Error) ? 1 : 0);
		this.results[this.fired] = res;
		this._fire();
	},

	_check: function(){
		if(this.fired != -1){
			if(!this.silentlyCancelled){
				throw new Error("already called!");
			}
			this.silentlyCancelled = false;
			return;
		}
	},

	callback: function(res){
		//	summary:
		//		Begin the callback sequence with a non-error value.

		/*
		callback or errback should only be called once on a given
		Deferred.
		*/
		this._check();
		this._resback(res);
	},

	errback: function(/*Error*/res){
		//	summary:
		//		Begin the callback sequence with an error result.
		this._check();
		if(!(res instanceof Error)){
			res = new Error(res);
		}
		this._resback(res);
	},

	addBoth: function(/*Function|Object*/cb, /*String?*/cbfn){
		//	summary:
		//		Add the same function as both a callback and an errback as the
		//		next element on the callback sequence.This is useful for code
		//		that you want to guarantee to run, e.g. a finalizer.
		var enclosed = dojo.hitch.apply(dojo, arguments);
		return this.addCallbacks(enclosed, enclosed);
	},

	addCallback: function(/*Function|Object*/cb, /*String?*/cbfn /*...*/){
		//	summary:
		//		Add a single callback to the end of the callback sequence.
		return this.addCallbacks(dojo.hitch.apply(dojo, arguments));
	},

	addErrback: function(cb, cbfn){
		//	summary:
		//		Add a single callback to the end of the callback sequence.
		return this.addCallbacks(null, dojo.hitch.apply(dojo, arguments));
	},

	addCallbacks: function(cb, eb){
		// summary:
		//		Add separate callback and errback to the end of the callback
		//		sequence.
		this.chain.push([cb, eb])
		if(this.fired >= 0){
			this._fire();
		}
		return this;
	},

	_fire: function(){
		// summary:
		//		Used internally to exhaust the callback sequence when a result
		//		is available.
		var chain = this.chain;
		var fired = this.fired;
		var res = this.results[fired];
		var self = this;
		var cb = null;
		while(
			(chain.length > 0) &&
			(this.paused == 0)
		){
			// Array
			var f = chain.shift()[fired];
			if(!f){ continue; }
			try{
				res = f(res);
				fired = ((res instanceof Error) ? 1 : 0);
				if(res instanceof dojo.Deferred){
					cb = function(res){
						self._resback(res);
						// inlined from _pause()
						self.paused--;
						if(
							(self.paused == 0) &&
							(self.fired >= 0)
						){
							self._fire();
						}
					}
					// inlined from _unpause
					this.paused++;
				}
			}catch(err){
				console.debug(err);
				fired = 1;
				res = err;
			}
		}
		this.fired = fired;
		this.results[fired] = res;
		if((cb)&&(this.paused)){
			// this is for "tail recursion" in case the dependent
			// deferred is already fired
			res.addBoth(cb);
		}
	}
});

}

if(!dojo._hasResource["dojo._base.json"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.json"] = true;
dojo.provide("dojo._base.json");

dojo.fromJson = function(/*String*/ json){
	// summary:
	// 		Parses a [JSON](http://json.org) string to return a JavaScript object.
	// json:
	//		a string literal of a JSON item, for instance:
	//			`'{ "foo": [ "bar", 1, { "baz": "thud" } ] }'`

	return eval("(" + json + ")"); // Object
}

dojo._escapeString = function(/*String*/str){
	//summary:
	//		Adds escape sequences for non-visual characters, double quote and
	//		backslash and surrounds with double quotes to form a valid string
	//		literal.
	return ('"' + str.replace(/(["\\])/g, '\\$1') + '"').
		replace(/[\f]/g, "\\f").replace(/[\b]/g, "\\b").replace(/[\n]/g, "\\n").
		replace(/[\t]/g, "\\t").replace(/[\r]/g, "\\r"); // string
}

dojo.toJsonIndentStr = "\t";
dojo.toJson = function(/*Object*/ it, /*Boolean?*/ prettyPrint, /*String?*/ _indentStr){
	// summary:
	//		Returns a [JSON](http://json.org) serialization of an object.
	//
	// description:
	//		Returns a [JSON](http://json.org) serialization of an object.
	//		Note that this doesn't check for infinite recursion, so don't do that!
	//
	// it:
	//		an object to be serialized. Objects may define their own
	//		serialization via a special "__json__" or "json" function
	//		property. If a specialized serializer has been defined, it will
	//		be used as a fallback.
	//
	// prettyPrint:
	//		if true, we indent objects and arrays to make the output prettier.
	//		The variable dojo.toJsonIndentStr is used as the indent string
	//		-- to use something other than the default (tab),
	//		change that variable before calling dojo.toJson().
	//
	// _indentStr:
	//		private variable for recursive calls when pretty printing, do not use.

	if(it === undefined){
		return "undefined";
	}
	var objtype = typeof it;
	if(objtype == "number" || objtype == "boolean"){
		return it + "";
	}
	if(it === null){
		return "null";
	}
	if(dojo.isString(it)){
		return dojo._escapeString(it);
	}
	if(it.nodeType && it.cloneNode){ // isNode
		return ""; // FIXME: would something like outerHTML be better here?
	}
	// recurse
	var recurse = arguments.callee;
	// short-circuit for objects that support "json" serialization
	// if they return "self" then just pass-through...
	var newObj;
	_indentStr = _indentStr || "";
	var nextIndent = prettyPrint ? _indentStr + dojo.toJsonIndentStr : "";
	if(typeof it.__json__ == "function"){
		newObj = it.__json__();
		if(it !== newObj){
			return recurse(newObj, prettyPrint, nextIndent);
		}
	}
	if(typeof it.json == "function"){
		newObj = it.json();
		if(it !== newObj){
			return recurse(newObj, prettyPrint, nextIndent);
		}
	}

	var sep = prettyPrint ? " " : "";
	var newLine = prettyPrint ? "\n" : "";

	// array
	if(dojo.isArray(it)){
		var res = dojo.map(it, function(obj){
			var val = recurse(obj, prettyPrint, nextIndent);
			if(typeof val != "string"){
				val = "undefined";
			}
			return newLine + nextIndent + val;
		});
		return "[" + res.join("," + sep) + newLine + _indentStr + "]";
	}
	/*
	// look in the registry
	try {
		window.o = it;
		newObj = dojo.json.jsonRegistry.match(it);
		return recurse(newObj, prettyPrint, nextIndent);
	}catch(e){
		// console.debug(e);
	}
	// it's a function with no adapter, skip it
	*/
	if(objtype == "function"){
		return null; // null
	}
	// generic object code path
	var output = [];
	for(var key in it){
		var keyStr;
		if(typeof key == "number"){
			keyStr = '"' + key + '"';
		}else if(typeof key == "string"){
			keyStr = dojo._escapeString(key);
		}else{
			// skip non-string or number keys
			continue;
		}
		val = recurse(it[key], prettyPrint, nextIndent);
		if(typeof val != "string"){
			// skip non-serializable values
			continue;
		}
		// FIXME: use += on Moz!!
		//	 MOW NOTE: using += is a pain because you have to account for the dangling comma...
		output.push(newLine + nextIndent + keyStr + ":" + sep + val);
	}
	return "{" + output.join("," + sep) + newLine + _indentStr + "}"; // String
}

}

if(!dojo._hasResource["dojo._base.array"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.array"] = true;

dojo.provide("dojo._base.array");

(function(){
	var _getParts = function(arr, obj, cb){
		return [
			dojo.isString(arr) ? arr.split("") : arr,
			obj || dojo.global,
			// FIXME: cache the anonymous functions we create here?
			dojo.isString(cb) ? new Function("item", "index", "array", cb) : cb
		];
	};

	dojo.mixin(dojo, {
		indexOf: function(	/*Array*/		array,
							/*Object*/		value,
							/*Integer?*/	fromIndex,
							/*Boolean?*/	findLast){
			// summary:
			//		locates the first index of the provided value in the
			//		passed array. If the value is not found, -1 is returned.
			// description:
			//		For details on this method, see:
			// 			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:indexOf>

			var step = 1, end = array.length || 0, i = 0;
			if(findLast){
				i = end - 1;
				step = end = -1;
			}
			if(fromIndex != undefined){ i = fromIndex; }
			if((findLast && i > end) || i < end){
				for(; i != end; i += step){
					if(array[i] == value){ return i; }
				}
			}
			return -1;	// Number
		},

		lastIndexOf: function(/*Array*/array, /*Object*/value, /*Integer?*/fromIndex){
			// summary:
			//		locates the last index of the provided value in the passed array.
			//		If the value is not found, -1 is returned.
			// description:
			//		For details on this method, see:
			// 			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:lastIndexOf>
			return dojo.indexOf(array, value, fromIndex, true); // Number
		},

		forEach: function(/*Array|String*/arr, /*Function|String*/callback, /*Object?*/thisObject){
			// summary:
			//		for every item in arr, callback is invoked.  Return values are ignored.
			// arr: the array to iterate on.  If a string, operates on individual characters.
			// callback: a function is invoked with three arguments: item, index, and array
			// thisObject: may be used to scope the call to callback
			// description:
			//		This function corresponds to the JavaScript 1.6 Array.forEach() method.
			//		In environments that support JavaScript 1.6, this function is a passthrough to the built-in method.
			//		For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:forEach>

			// match the behavior of the built-in forEach WRT empty arrs
			if(!arr || !arr.length){ return; }

			// FIXME: there are several ways of handilng thisObject. Is
			// dojo.global always the default context?
			var _p = _getParts(arr, thisObject, callback); arr = _p[0];
			for(var i=0,l=_p[0].length; i<l; i++){
				_p[2].call(_p[1], arr[i], i, arr);
			}
		},

		_everyOrSome: function(/*Boolean*/every, /*Array|String*/arr, /*Function|String*/callback, /*Object?*/thisObject){
			var _p = _getParts(arr, thisObject, callback); arr = _p[0];
			for(var i = 0, l = arr.length; i < l; i++){
				var result = !!_p[2].call(_p[1], arr[i], i, arr);
				if(every ^ result){
					return result; // Boolean
				}
			}
			return every; // Boolean
		},

		every: function(/*Array|String*/arr, /*Function|String*/callback, /*Object?*/thisObject){
			// summary:
			//		Determines whether or not every item in arr satisfies the
			//		condition implemented by callback.
			// arr: the array to iterate on.  If a string, operates on individual characters.
			// callback: a function is invoked with three arguments: item, index, and array and returns true
			//		if the condition is met.
			// thisObject: may be used to scope the call to callback
			// description:
			//		This function corresponds to the JavaScript 1.6 Array.every() method.
			//		In environments that support JavaScript 1.6, this function is a passthrough to the built-in method.
			//		For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:every>
			// example:
			//	|	dojo.every([1, 2, 3, 4], function(item){ return item>1; });
			//		returns false
			// example:
			//	|	dojo.every([1, 2, 3, 4], function(item){ return item>0; });
			//		returns true
			return this._everyOrSome(true, arr, callback, thisObject); // Boolean
		},

		some: function(/*Array|String*/arr, /*Function|String*/callback, /*Object?*/thisObject){
			// summary:
			//		Determines whether or not any item in arr satisfies the
			//		condition implemented by callback.
			// arr: the array to iterate on.  If a string, operates on individual characters.
			// callback: a function is invoked with three arguments: item, index, and array and returns true
			//		if the condition is met.
			// thisObject: may be used to scope the call to callback
			// description:
			//		This function corresponds to the JavaScript 1.6 Array.some() method.
			//		In environments that support JavaScript 1.6, this function is a passthrough to the built-in method.
			//		For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:some>
			// example:
			//	|	dojo.some([1, 2, 3, 4], function(item){ return item>1; });
			//		returns true
			// example:
			//	|	dojo.some([1, 2, 3, 4], function(item){ return item<1; });
			//		returns false
			return this._everyOrSome(false, arr, callback, thisObject); // Boolean
		},

		map: function(/*Array|String*/arr, /*Function|String*/callback, /*Function?*/thisObject){
			// summary:
			//		applies callback to each element of arr and returns
			//		an Array with the results
			// arr: the array to iterate on.  If a string, operates on individual characters.
			// callback: a function is invoked with three arguments: item, index, and array and returns a value
			// thisObject: may be used to scope the call to callback
			// description:
			//		This function corresponds to the JavaScript 1.6 Array.map() method.
			//		In environments that support JavaScript 1.6, this function is a passthrough to the built-in method.
			//		For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:map>
			// example:
			//	|	dojo.map([1, 2, 3, 4], function(item){ return item+1 });
			//		returns [2, 3, 4, 5]
			var _p = _getParts(arr, thisObject, callback); arr = _p[0];
			var outArr = (arguments[3] ? (new arguments[3]()) : []);
			for(var i=0;i<arr.length;++i){
				outArr.push(_p[2].call(_p[1], arr[i], i, arr));
			}
			return outArr; // Array
		},

		filter: function(/*Array*/arr, /*Function|String*/callback, /*Object?*/thisObject){
			// summary:
			//		Returns a new Array with those items from arr that match the
			//		condition implemented by callback.
			// arr: the array to iterate on.  If a string, operates on individual characters.
			// callback: a function is invoked with three arguments: item, index, and array and returns true
			//		if the condition is met.
			// thisObject: may be used to scope the call to callback
			// description:
			//		This function corresponds to the JavaScript 1.6 Array.filter() method.
			//		In environments that support JavaScript 1.6, this function is a passthrough to the built-in method.
			//		For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:filter>
			// example:
			//	|	dojo.filter([1, 2, 3, 4], function(item){ return item>1; });
			//		returns [2, 3, 4]

			var _p = _getParts(arr, thisObject, callback); arr = _p[0];
			var outArr = [];
			for(var i = 0; i < arr.length; i++){
				if(_p[2].call(_p[1], arr[i], i, arr)){
					outArr.push(arr[i]);
				}
			}
			return outArr; // Array
		}
	});
})();

}

if(!dojo._hasResource["dojo._base.Color"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.Color"] = true;
dojo.provide("dojo._base.Color");

dojo.Color = function(/*Array|String|Object*/ color){
	// summary:
	//		takes a named string, hex string, array of rgb or rgba values,
	//		an object with r, g, b, and a properties, or another dojo.Color object
	if(color){ this.setColor(color); }
};

// FIXME: there's got to be a more space-efficient way to encode or discover these!!  Use hex?
dojo.Color.named = {
	black:      [0,0,0],
	silver:     [192,192,192],
	gray:       [128,128,128],
	white:      [255,255,255],
	maroon:		[128,0,0],
	red:        [255,0,0],
	purple:		[128,0,128],
	fuchsia:	[255,0,255],
	green:	    [0,128,0],
	lime:	    [0,255,0],
	olive:		[128,128,0],
	yellow:		[255,255,0],
	navy:       [0,0,128],
	blue:       [0,0,255],
	teal:		[0,128,128],
	aqua:		[0,255,255]
};

dojo.extend(dojo.Color, {
	r: 255, g: 255, b: 255, a: 1,
	_set: function(r, g, b, a){
		var t = this; t.r = r; t.g = g; t.b = b; t.a = a;
	},
	setColor: function(/*Array|String|Object*/ color){
		// summary:
		//		takes a named string, hex string, array of rgb or rgba values,
		//		an object with r, g, b, and a properties, or another dojo.Color object
		var d = dojo;
		if(d.isString(color)){
			d.colorFromString(color, this);
		}else if(d.isArray(color)){
			d.colorFromArray(color, this);
		}else{
			this._set(color.r, color.g, color.b, color.a);
			if(!(color instanceof d.Color)){ this.sanitize(); }
		}
		return this;	// dojo.Color
	},
	sanitize: function(){
		// summary:
		//		makes sure that the object has correct attributes
		// description:
		//		the default implementation does nothing, include dojo.colors to
		//		augment it to real checks
		return this;	// dojo.Color
	},
	toRgb: function(){
		// summary: returns 3 component array of rgb values
		var t = this;
		return [t.r, t.g, t.b];	// Array
	},
	toRgba: function(){
		// summary: returns a 4 component array of rgba values
		var t = this;
		return [t.r, t.g, t.b, t.a];	// Array
	},
	toHex: function(){
		// summary: returns a css color string in hexadecimal representation
		var arr = dojo.map(["r", "g", "b"], function(x){
			var s = this[x].toString(16);
			return s.length < 2 ? "0" + s : s;
		}, this);
		return "#" + arr.join("");	// String
	},
	toCss: function(/*Boolean?*/ includeAlpha){
		// summary: returns a css color string in rgb(a) representation
		var t = this, rgb = t.r + ", " + t.g + ", " + t.b;
		return (includeAlpha ? "rgba(" + rgb + ", " + t.a : "rgb(" + rgb) + ")";	// String
	},
	toString: function(){
		// summary: returns a visual representation of the color
		return this.toCss(true); // String
	}
});

dojo.blendColors = function(
	/*dojo.Color*/ start,
	/*dojo.Color*/ end,
	/*Number*/ weight,
	/*dojo.Color?*/ obj
){
	// summary:
	//		blend colors end and start with weight from 0 to 1, 0.5 being a 50/50 blend,
	//		can reuse a previously allocated dojo.Color object for the result
	var d = dojo, t = obj || new dojo.Color();
	d.forEach(["r", "g", "b", "a"], function(x){
		t[x] = start[x] + (end[x] - start[x]) * weight;
		if(x != "a"){ t[x] = Math.round(t[x]); }
	});
	return t.sanitize();	// dojo.Color
};

dojo.colorFromRgb = function(/*String*/ color, /*dojo.Color?*/ obj){
	// summary: get rgb(a) array from css-style color declarations
	var m = color.toLowerCase().match(/^rgba?\(([\s\.,0-9]+)\)/);
	return m && dojo.colorFromArray(m[1].split(/\s*,\s*/), obj);	// dojo.Color
};

dojo.colorFromHex = function(/*String*/ color, /*dojo.Color?*/ obj){
	// summary: converts a hex string with a '#' prefix to a color object.
	//	Supports 12-bit #rgb shorthand.
	var d = dojo, t = obj || new d.Color(),
		bits = (color.length == 4) ? 4 : 8,
		mask = (1 << bits) - 1;
	color = Number("0x" + color.substr(1));
	if(isNaN(color)){
		return null; // dojo.Color
	}
	d.forEach(["b", "g", "r"], function(x){
		var c = color & mask;
		color >>= bits;
		t[x] = bits == 4 ? 17 * c : c;
	});
	t.a = 1;
	return t;	// dojo.Color
};

dojo.colorFromArray = function(/*Array*/ a, /*dojo.Color?*/ obj){
	// summary: builds a color from 1, 2, 3, or 4 element array
	var t = obj || new dojo.Color();
	t._set(Number(a[0]), Number(a[1]), Number(a[2]), Number(a[3]));
	if(isNaN(t.a)){ t.a = 1; }
	return t.sanitize();	// dojo.Color
};

dojo.colorFromString = function(/*String*/ str, /*dojo.Color?*/ obj){
	//	summary:
	//		parses str for a color value.
	//	description:
	//		Acceptable input values for str may include arrays of any form
	//		accepted by dojo.colorFromArray, hex strings such as "#aaaaaa", or
	//		rgb or rgba strings such as "rgb(133, 200, 16)" or "rgba(10, 10,
	//		10, 50)"
	//	returns:
	//		a dojo.Color object. If obj is passed, it will be the return value.
	var a = dojo.Color.named[str];
	return a && dojo.colorFromArray(a, obj) || dojo.colorFromRgb(str, obj) || dojo.colorFromHex(str, obj);
};

}

if(!dojo._hasResource["dojo._base"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base"] = true;
dojo.provide("dojo._base");

}

if(!dojo._hasResource["dojo._base.window"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.window"] = true;
dojo.provide("dojo._base.window");

dojo._gearsObject = function(){
	// summary:
	//		factory method to get a Google Gears plugin instance to
	//		expose in the browser runtime environment, if present
	var factory;
	var results;

	var gearsObj = dojo.getObject("google.gears");
	if(gearsObj){ return gearsObj; } // already defined elsewhere

	if(typeof GearsFactory != "undefined"){ // Firefox
		factory = new GearsFactory();
	}else{
		if(dojo.isIE){
			// IE
			try{
				factory = new ActiveXObject("Gears.Factory");
			}catch(e){
				// ok to squelch; there's no gears factory.  move on.
			}
		}else if(navigator.mimeTypes["application/x-googlegears"]){
			// Safari?
			factory = document.createElement("object");
			factory.setAttribute("type", "application/x-googlegears");
			factory.setAttribute("width", 0);
			factory.setAttribute("height", 0);
			factory.style.display = "none";
			document.documentElement.appendChild(factory);
		}
	}

	// still nothing?
	if(!factory){ return null; }

	// define the global objects now; don't overwrite them though if they
	// were somehow set internally by the Gears plugin, which is on their
	// dev roadmap for the future
	dojo.setObject("google.gears.factory", factory);
	return dojo.getObject("google.gears");
};

/*=====
dojo.isGears = {
	// summary: True if client is using Google Gears
};
=====*/
// see if we have Google Gears installed, and if
// so, make it available in the runtime environment
// and in the Google standard 'google.gears' global object
dojo.isGears = (!!dojo._gearsObject())||0;

/*=====
dojo.doc = {
	// summary:
	//		Alias for the current document. 'dojo.doc' can be modified
	//		for temporary context shifting. Also see dojo.withDoc().
	// description:
	//    Refer to dojo.doc rather
	//    than referring to 'window.document' to ensure your code runs
	//    correctly in managed contexts.
	// example:
	// 	|	n.appendChild(dojo.doc.createElement('div'));
}
=====*/
dojo.doc = window["document"] || null;

dojo.body = function(){
	// summary:
	//		Return the body element of the document
	//		return the body object associated with dojo.doc
	// example:
	// 	|	dojo.body().appendChild(dojo.doc.createElement('div'));

	// Note: document.body is not defined for a strict xhtml document
	// Would like to memoize this, but dojo.doc can change vi dojo.withDoc().
	return dojo.doc.body || dojo.doc.getElementsByTagName("body")[0]; // Node
}

dojo.setContext = function(/*Object*/globalObject, /*DocumentElement*/globalDocument){
	// summary:
	//		changes the behavior of many core Dojo functions that deal with
	//		namespace and DOM lookup, changing them to work in a new global
	//		context (e.g., an iframe). The varibles dojo.global and dojo.doc
	//		are modified as a result of calling this function and the result of
	//		`dojo.body()` likewise differs.
	dojo.global = globalObject;
	dojo.doc = globalDocument;
};

dojo._fireCallback = function(callback, context, cbArguments){
	if(context && dojo.isString(callback)){
		callback = context[callback];
	}
	return callback.apply(context, cbArguments || [ ]);
}

dojo.withGlobal = function(	/*Object*/globalObject,
							/*Function*/callback,
							/*Object?*/thisObject,
							/*Array?*/cbArguments){
	// summary:
	//		Call callback with globalObject as dojo.global and
	//		globalObject.document as dojo.doc. If provided, globalObject
	//		will be executed in the context of object thisObject
	// description:
	//		When callback() returns or throws an error, the dojo.global
	//		and dojo.doc will be restored to its previous state.
	var rval;
	var oldGlob = dojo.global;
	var oldDoc = dojo.doc;
	try{
		dojo.setContext(globalObject, globalObject.document);
		rval = dojo._fireCallback(callback, thisObject, cbArguments);
	}finally{
		dojo.setContext(oldGlob, oldDoc);
	}
	return rval;
}

dojo.withDoc = function(	/*Object*/documentObject,
							/*Function*/callback,
							/*Object?*/thisObject,
							/*Array?*/cbArguments){
	// summary:
	//		Call callback with documentObject as dojo.doc. If provided,
	//		callback will be executed in the context of object thisObject
	// description:
	//		When callback() returns or throws an error, the dojo.doc will
	//		be restored to its previous state.
	var rval;
	var oldDoc = dojo.doc;
	try{
		dojo.doc = documentObject;
		rval = dojo._fireCallback(callback, thisObject, cbArguments);
	}finally{
		dojo.doc = oldDoc;
	}
	return rval;
};

}

if(!dojo._hasResource["dojo._base.event"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.event"] = true;
dojo.provide("dojo._base.event");

// this file courtesy of the TurboAjax Group, licensed under a Dojo CLA

(function(){
	// DOM event listener machinery
	var del = (dojo._event_listener = {
		add: function(/*DOMNode*/node, /*String*/name, /*Function*/fp){
			if(!node){return;}
			name = del._normalizeEventName(name);
			fp = del._fixCallback(name, fp);
			var oname = name;
			if(!dojo.isIE && (name == "mouseenter" || name == "mouseleave")){
				var ofp = fp;
				//oname = name;
				name = (name == "mouseenter") ? "mouseover" : "mouseout";
				fp = function(e){
					// thanks ben!
					if(!dojo.isDescendant(e.relatedTarget, node)){
						// e.type = oname; // FIXME: doesn't take? SJM: event.type is generally immutable.
						return ofp.call(this, e);
					}
				}
			}
			node.addEventListener(name, fp, false);
			return fp; /*Handle*/
		},
		remove: function(/*DOMNode*/node, /*String*/event, /*Handle*/handle){
			// summary:
			//		clobbers the listener from the node
			// node:
			//		DOM node to attach the event to
			// event:
			//		the name of the handler to remove the function from
			// handle:
			//		the handle returned from add
			if (node){
				node.removeEventListener(del._normalizeEventName(event), handle, false);
			}
		},
		_normalizeEventName: function(/*String*/name){
			// Generally, name should be lower case, unless it is special
			// somehow (e.g. a Mozilla DOM event).
			// Remove 'on'.
			return name.slice(0,2) =="on" ? name.slice(2) : name;
		},
		_fixCallback: function(/*String*/name, fp){
			// By default, we only invoke _fixEvent for 'keypress'
			// If code is added to _fixEvent for other events, we have
			// to revisit this optimization.
			// This also applies to _fixEvent overrides for Safari and Opera
			// below.
			return name != "keypress" ? fp : function(e){ return fp.call(this, del._fixEvent(e, this)); };
		},
		_fixEvent: function(evt, sender){
			// _fixCallback only attaches us to keypress.
			// Switch on evt.type anyway because we might
			// be called directly from dojo.fixEvent.
			switch(evt.type){
				case "keypress":
					del._setKeyChar(evt);
					break;
			}
			return evt;
		},
		_setKeyChar: function(evt){
			evt.keyChar = evt.charCode ? String.fromCharCode(evt.charCode) : '';
		}
	});

	// DOM events

	dojo.fixEvent = function(/*Event*/evt, /*DOMNode*/sender){
		// summary:
		//		normalizes properties on the event object including event
		//		bubbling methods, keystroke normalization, and x/y positions
		// evt: Event
		//		native event object
		// sender: DOMNode
		//		node to treat as "currentTarget"
		return del._fixEvent(evt, sender);
	}

	dojo.stopEvent = function(/*Event*/evt){
		// summary:
		//		prevents propagation and clobbers the default action of the
		//		passed event
		// evt: Event
		//		The event object. If omitted, window.event is used on IE.
		evt.preventDefault();
		evt.stopPropagation();
		// NOTE: below, this method is overridden for IE
	}

	// the default listener to use on dontFix nodes, overriden for IE
	var node_listener = dojo._listener;

	// Unify connect and event listeners
	dojo._connect = function(obj, event, context, method, dontFix){
		// FIXME: need a more strict test
		var isNode = obj && (obj.nodeType||obj.attachEvent||obj.addEventListener);
		// choose one of three listener options: raw (connect.js), DOM event on a Node, custom event on a Node
		// we need the third option to provide leak prevention on broken browsers (IE)
		var lid = !isNode ? 0 : (!dontFix ? 1 : 2), l = [dojo._listener, del, node_listener][lid];
		// create a listener
		var h = l.add(obj, event, dojo.hitch(context, method));
		// formerly, the disconnect package contained "l" directly, but if client code
		// leaks the disconnect package (by connecting it to a node), referencing "l"
		// compounds the problem.
		// instead we return a listener id, which requires custom _disconnect below.
		// return disconnect package
		return [ obj, event, h, lid ];
	}

	dojo._disconnect = function(obj, event, handle, listener){
		([dojo._listener, del, node_listener][listener]).remove(obj, event, handle);
	}

	// Constants

	// Public: client code should test
	// keyCode against these named constants, as the
	// actual codes can vary by browser.
	dojo.keys = {
		// summary: definitions for common key values
		BACKSPACE: 8,
		TAB: 9,
		CLEAR: 12,
		ENTER: 13,
		SHIFT: 16,
		CTRL: 17,
		ALT: 18,
		PAUSE: 19,
		CAPS_LOCK: 20,
		ESCAPE: 27,
		SPACE: 32,
		PAGE_UP: 33,
		PAGE_DOWN: 34,
		END: 35,
		HOME: 36,
		LEFT_ARROW: 37,
		UP_ARROW: 38,
		RIGHT_ARROW: 39,
		DOWN_ARROW: 40,
		INSERT: 45,
		DELETE: 46,
		HELP: 47,
		LEFT_WINDOW: 91,
		RIGHT_WINDOW: 92,
		SELECT: 93,
		NUMPAD_0: 96,
		NUMPAD_1: 97,
		NUMPAD_2: 98,
		NUMPAD_3: 99,
		NUMPAD_4: 100,
		NUMPAD_5: 101,
		NUMPAD_6: 102,
		NUMPAD_7: 103,
		NUMPAD_8: 104,
		NUMPAD_9: 105,
		NUMPAD_MULTIPLY: 106,
		NUMPAD_PLUS: 107,
		NUMPAD_ENTER: 108,
		NUMPAD_MINUS: 109,
		NUMPAD_PERIOD: 110,
		NUMPAD_DIVIDE: 111,
		F1: 112,
		F2: 113,
		F3: 114,
		F4: 115,
		F5: 116,
		F6: 117,
		F7: 118,
		F8: 119,
		F9: 120,
		F10: 121,
		F11: 122,
		F12: 123,
		F13: 124,
		F14: 125,
		F15: 126,
		NUM_LOCK: 144,
		SCROLL_LOCK: 145
	};

	// IE event normalization
	if(dojo.isIE){
		var _trySetKeyCode = function(e, code){
			try{
				// squelch errors when keyCode is read-only
				// (e.g. if keyCode is ctrl or shift)
				return (e.keyCode = code);
			}catch(e){
				return 0;
			}
		}

		// by default, use the standard listener
		var iel = dojo._listener;
		// dispatcher tracking property
		if(!dojo.config._allow_leaks){
			// custom listener that handles leak protection for DOM events
			node_listener = iel = dojo._ie_listener = {
				// support handler indirection: event handler functions are
				// referenced here. Event dispatchers hold only indices.
				handlers: [],
				// add a listener to an object
				add: function(/*Object*/ source, /*String*/ method, /*Function*/ listener){
					source = source || dojo.global;
					var f = source[method];
					if(!f||!f._listeners){
						var d = dojo._getIeDispatcher();
						// original target function is special
						d.target = f && (ieh.push(f) - 1);
						// dispatcher holds a list of indices into handlers table
						d._listeners = [];
						// redirect source to dispatcher
						f = source[method] = d;
					}
					return f._listeners.push(ieh.push(listener) - 1) ; /*Handle*/
				},
				// remove a listener from an object
				remove: function(/*Object*/ source, /*String*/ method, /*Handle*/ handle){
					var f = (source||dojo.global)[method], l = f && f._listeners;
					if(f && l && handle--){
						delete ieh[l[handle]];
						delete l[handle];
					}
				}
			};
			// alias used above
			var ieh = iel.handlers;
		}

		dojo.mixin(del, {
			add: function(/*DOMNode*/node, /*String*/event, /*Function*/fp){
				if(!node){return;} // undefined
				event = del._normalizeEventName(event);
				if(event=="onkeypress"){
					// we need to listen to onkeydown to synthesize
					// keypress events that otherwise won't fire
					// on IE
					var kd = node.onkeydown;
					if(!kd || !kd._listeners || !kd._stealthKeydownHandle){
						var h = del.add(node, "onkeydown", del._stealthKeyDown);
						kd = node.onkeydown;
						kd._stealthKeydownHandle = h;
						kd._stealthKeydownRefs = 1;
					}else{
						kd._stealthKeydownRefs++;
					}
				}
				return iel.add(node, event, del._fixCallback(fp));
			},
			remove: function(/*DOMNode*/node, /*String*/event, /*Handle*/handle){
				event = del._normalizeEventName(event);
				iel.remove(node, event, handle);
				if(event=="onkeypress"){
					var kd = node.onkeydown;
					if(--kd._stealthKeydownRefs <= 0){
						iel.remove(node, "onkeydown", kd._stealthKeydownHandle);
						delete kd._stealthKeydownHandle;
					}
				}
			},
			_normalizeEventName: function(/*String*/eventName){
				// Generally, eventName should be lower case, unless it is
				// special somehow (e.g. a Mozilla event)
				// ensure 'on'
				return eventName.slice(0,2) != "on" ? "on" + eventName : eventName;
			},
			_nop: function(){},
			_fixEvent: function(/*Event*/evt, /*DOMNode*/sender){
				// summary:
				//		normalizes properties on the event object including event
				//		bubbling methods, keystroke normalization, and x/y positions
				// evt: native event object
				// sender: node to treat as "currentTarget"
				if(!evt){
					var w = sender && (sender.ownerDocument || sender.document || sender).parentWindow || window;
					evt = w.event;
				}
				if(!evt){return(evt);}
				evt.target = evt.srcElement;
				evt.currentTarget = (sender || evt.srcElement);
				evt.layerX = evt.offsetX;
				evt.layerY = evt.offsetY;
				// FIXME: scroll position query is duped from dojo.html to
				// avoid dependency on that entire module. Now that HTML is in
				// Base, we should convert back to something similar there.
				var se = evt.srcElement, doc = (se && se.ownerDocument) || document;
				// DO NOT replace the following to use dojo.body(), in IE, document.documentElement should be used
				// here rather than document.body
				var docBody = ((dojo.isIE < 6) || (doc["compatMode"] == "BackCompat")) ? doc.body : doc.documentElement;
				var offset = dojo._getIeDocumentElementOffset();
				evt.pageX = evt.clientX + dojo._fixIeBiDiScrollLeft(docBody.scrollLeft || 0) - offset.x;
				evt.pageY = evt.clientY + (docBody.scrollTop || 0) - offset.y;
				if(evt.type == "mouseover"){
					evt.relatedTarget = evt.fromElement;
				}
				if(evt.type == "mouseout"){
					evt.relatedTarget = evt.toElement;
				}
				evt.stopPropagation = del._stopPropagation;
				evt.preventDefault = del._preventDefault;
				return del._fixKeys(evt);
			},
			_fixKeys: function(evt){
				switch(evt.type){
					case "keypress":
						var c = ("charCode" in evt ? evt.charCode : evt.keyCode);
						if (c==10){
							// CTRL-ENTER is CTRL-ASCII(10) on IE, but CTRL-ENTER on Mozilla
							c=0;
							evt.keyCode = 13;
						}else if(c==13||c==27){
							c=0; // Mozilla considers ENTER and ESC non-printable
						}else if(c==3){
							c=99; // Mozilla maps CTRL-BREAK to CTRL-c
						}
						// Mozilla sets keyCode to 0 when there is a charCode
						// but that stops the event on IE.
						evt.charCode = c;
						del._setKeyChar(evt);
						break;
				}
				return evt;
			},
			// some ctrl-key combinations (mostly w/punctuation) do not emit a char code in IE
			// we map those virtual key codes to ascii here
			// not valid for all (non-US) keyboards, so maybe we shouldn't bother
			_punctMap: {
				106:42,
				111:47,
				186:59,
				187:43,
				188:44,
				189:45,
				190:46,
				191:47,
				192:96,
				219:91,
				220:92,
				221:93,
				222:39
			},
			_stealthKeyDown: function(evt){
				// IE doesn't fire keypress for most non-printable characters.
				// other browsers do, we simulate it here.
				var kp = evt.currentTarget.onkeypress;
				// only works if kp exists and is a dispatcher
				if(!kp || !kp._listeners){ return; }
				// munge key/charCode
				var k=evt.keyCode;
				// These are Windows Virtual Key Codes
				// http://msdn.microsoft.com/library/default.asp?url=/library/en-us/winui/WinUI/WindowsUserInterface/UserInput/VirtualKeyCodes.asp
				var unprintable = (k!=13)&&(k!=32)&&(k!=27)&&(k<48||k>90)&&(k<96||k>111)&&(k<186||k>192)&&(k<219||k>222);
				// synthesize keypress for most unprintables and CTRL-keys
				if(unprintable||evt.ctrlKey){
					var c = unprintable ? 0 : k;
					if(evt.ctrlKey){
						if(k==3 || k==13){
							return; // IE will post CTRL-BREAK, CTRL-ENTER as keypress natively
						}else if(c>95 && c<106){
							c -= 48; // map CTRL-[numpad 0-9] to ASCII
						}else if((!evt.shiftKey)&&(c>=65&&c<=90)){
							c += 32; // map CTRL-[A-Z] to lowercase
						}else{
							c = del._punctMap[c] || c; // map other problematic CTRL combinations to ASCII
						}
					}
					// simulate a keypress event
					var faux = del._synthesizeEvent(evt, {type: 'keypress', faux: true, charCode: c});
					kp.call(evt.currentTarget, faux);
					evt.cancelBubble = faux.cancelBubble;
					evt.returnValue = faux.returnValue;
					_trySetKeyCode(evt, faux.keyCode);
				}
			},
			// Called in Event scope
			_stopPropagation: function(){
				this.cancelBubble = true;
			},
			_preventDefault: function(){
				// Setting keyCode to 0 is the only way to prevent certain keypresses (namely
				// ctrl-combinations that correspond to menu accelerator keys).
				// Otoh, it prevents upstream listeners from getting this information
				// Try to split the difference here by clobbering keyCode only for ctrl
				// combinations. If you still need to access the key upstream, bubbledKeyCode is
				// provided as a workaround.
				this.bubbledKeyCode = this.keyCode;
				if(this.ctrlKey){_trySetKeyCode(this, 0);}
				this.returnValue = false;
			}
		});

		// override stopEvent for IE
		dojo.stopEvent = function(evt){
			evt = evt || window.event;
			del._stopPropagation.call(evt);
			del._preventDefault.call(evt);
		}
	}

	del._synthesizeEvent = function(evt, props){
			var faux = dojo.mixin({}, evt, props);
			del._setKeyChar(faux);
			// FIXME: would prefer to use dojo.hitch: dojo.hitch(evt, evt.preventDefault);
			// but it throws an error when preventDefault is invoked on Safari
			// does Event.preventDefault not support "apply" on Safari?
			faux.preventDefault = function(){ evt.preventDefault(); };
			faux.stopPropagation = function(){ evt.stopPropagation(); };
			return faux;
	}

	// Opera event normalization
	if(dojo.isOpera){
		dojo.mixin(del, {
			_fixEvent: function(evt, sender){
				switch(evt.type){
					case "keypress":
						var c = evt.which;
						if(c==3){
							c=99; // Mozilla maps CTRL-BREAK to CTRL-c
						}
						// can't trap some keys at all, like INSERT and DELETE
						// there is no differentiating info between DELETE and ".", or INSERT and "-"
						c = ((c<41)&&(!evt.shiftKey) ? 0 : c);
						if((evt.ctrlKey)&&(!evt.shiftKey)&&(c>=65)&&(c<=90)){
							// lowercase CTRL-[A-Z] keys
							c += 32;
						}
						return del._synthesizeEvent(evt, { charCode: c });
				}
				return evt;
			}
		});
	}

	// Safari event normalization
	if(dojo.isSafari){
		dojo.mixin(del, {
			_fixEvent: function(evt, sender){
				switch(evt.type){
					case "keypress":
						var c = evt.charCode, s = evt.shiftKey, k = evt.keyCode;
						// FIXME: This is a hack, suggest we rethink keyboard strategy.
						// Arrow and page keys have 0 "keyCode" in keypress events.on Safari for Windows
						k = k || identifierMap[evt.keyIdentifier] || 0;
						if(evt.keyIdentifier=="Enter"){
							c = 0; // differentiate Enter from CTRL-m (both code 13)
						}else if((evt.ctrlKey)&&(c>0)&&(c<27)){
							c += 96; // map CTRL-[A-Z] codes to ASCII
						} else if (c==dojo.keys.SHIFT_TAB) {
							c = dojo.keys.TAB; // morph SHIFT_TAB into TAB + shiftKey: true
							s = true;
						} else {
							c = (c>=32 && c<63232 ? c : 0); // avoid generating keyChar for non-printables
						}
						return del._synthesizeEvent(evt, {charCode: c, shiftKey: s, keyCode: k});
				}
				return evt;
			}
		});

		dojo.mixin(dojo.keys, {
			SHIFT_TAB: 25,
			UP_ARROW: 63232,
			DOWN_ARROW: 63233,
			LEFT_ARROW: 63234,
			RIGHT_ARROW: 63235,
			F1: 63236,
			F2: 63237,
			F3: 63238,
			F4: 63239,
			F5: 63240,
			F6: 63241,
			F7: 63242,
			F8: 63243,
			F9: 63244,
			F10: 63245,
			F11: 63246,
			F12: 63247,
			PAUSE: 63250,
			DELETE: 63272,
			HOME: 63273,
			END: 63275,
			PAGE_UP: 63276,
			PAGE_DOWN: 63277,
			INSERT: 63302,
			PRINT_SCREEN: 63248,
			SCROLL_LOCK: 63249,
			NUM_LOCK: 63289
		});
		var dk = dojo.keys, identifierMap = { "Up": dk.UP_ARROW, "Down": dk.DOWN_ARROW, "Left": dk.LEFT_ARROW, "Right": dk.RIGHT_ARROW, "PageUp": dk.PAGE_UP, "PageDown": dk.PAGE_DOWN };
	}
})();

if(dojo.isIE){
	// keep this out of the closure
	// closing over 'iel' or 'ieh' b0rks leak prevention
	// ls[i] is an index into the master handler array
	dojo._ieDispatcher = function(args, sender){
		var ap=Array.prototype, h=dojo._ie_listener.handlers, c=args.callee, ls=c._listeners, t=h[c.target];
		// return value comes from original target function
		var r = t && t.apply(sender, args);
		// invoke listeners after target function
		for(var i in ls){
			if(!(i in ap)){
				h[ls[i]].apply(sender, args);
			}
		}
		return r;
	}
	dojo._getIeDispatcher = function(){
		// ensure the returned function closes over nothing
		return new Function(dojo._scopeName + "._ieDispatcher(arguments, this)"); // function
	}
	// keep this out of the closure to reduce RAM allocation
	dojo._event_listener._fixCallback = function(fp){
		var f = dojo._event_listener._fixEvent;
		return function(e){ return fp.call(this, f(e, this)); };
	}
}

}

if(!dojo._hasResource["dojo._base.html"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.html"] = true;

dojo.provide("dojo._base.html");

// FIXME: need to add unit tests for all the semi-public methods

try{
	document.execCommand("BackgroundImageCache", false, true);
}catch(e){
	// sane browsers don't have cache "issues"
}

// =============================
// DOM Functions
// =============================

/*=====
dojo.byId = function(id, doc){
	//	summary:
	//		Returns DOM node with matching `id` attribute or `null`
	//		if not found, similar to "$" function in another library.
	//		If `id` is a DomNode, this function is a no-op.
	//
	//	id: String|DOMNode
	//	 	A string to match an HTML id attribute or a reference to a DOM Node
	//
	//	doc: Document?
	//		Document to work in. Defaults to the current value of
	//		dojo.doc.  Can be used to retrieve
	//		node references from other documents.
=====*/
if(dojo.isIE || dojo.isOpera){
	dojo.byId = function(id, doc){
		if(dojo.isString(id)){
			var _d = doc || dojo.doc;
			var te = _d.getElementById(id);
			// attributes.id.value is better than just id in case the
			// user has a name=id inside a form
			if(te && te.attributes.id.value == id){
				return te;
			}else{
				var eles = _d.all[id];
				if(!eles || !eles.length){ return eles; }
				// if more than 1, choose first with the correct id
				var i=0;
				while((te=eles[i++])){
					if(te.attributes.id.value == id){ return te; }
				}
			}
		}else{
			return id; // DomNode
		}
	}
}else{
	dojo.byId = function(id, doc){
		return dojo.isString(id) ? (doc || dojo.doc).getElementById(id) : id; // DomNode
	}
}
/*=====
}
=====*/

(function(){
	/*
	dojo.createElement = function(obj, parent, position){
		// TODO: need to finish this!
	}
	*/

	var d = dojo;

	var _destroyContainer = null;
	dojo.addOnUnload(function(){
		_destroyContainer=null; //prevent IE leak
	});
	dojo._destroyElement = function(/*String||DomNode*/node){
		// summary:
		//		removes node from its parent, clobbers it and all of its
		//		children.
		//	node:
		//		the element to be destroyed, either as an ID or a reference

		node = d.byId(node);
		try{
			if(!_destroyContainer){
				_destroyContainer = document.createElement("div");
			}
			_destroyContainer.appendChild(node.parentNode ? node.parentNode.removeChild(node) : node);
			// NOTE: see http://trac.dojotoolkit.org/ticket/2931. This may be a bug and not a feature
			_destroyContainer.innerHTML = "";
		}catch(e){
			/* squelch */
		}
	};

	dojo.isDescendant = function(/*DomNode|String*/node, /*DomNode|String*/ancestor){
		//	summary:
		//		Returns true if node is a descendant of ancestor
		//	node: id or node reference to test
		//	ancestor: id or node reference of potential parent to test against
		try{
			node = d.byId(node);
			ancestor = d.byId(ancestor);
			while(node){
				if(node === ancestor){
					return true; // Boolean
				}
				node = node.parentNode;
			}
		}catch(e){ /* squelch, return false */ }
		return false; // Boolean
	};

	dojo.setSelectable = function(/*DomNode|String*/node, /*Boolean*/selectable){
		//	summary: enable or disable selection on a node
		//	node:
		//		id or reference to node
		//	selectable:
		node = d.byId(node);
		if(d.isMozilla){
			node.style.MozUserSelect = selectable ? "" : "none";
		}else if(d.isKhtml){
			node.style.KhtmlUserSelect = selectable ? "auto" : "none";
		}else if(d.isIE){
			node.unselectable = selectable ? "" : "on";
			d.query("*", node).forEach(function(descendant){
				descendant.unselectable = selectable ? "" : "on";
			});
		}
		//FIXME: else?  Opera?
	};

	var _insertBefore = function(/*Node*/node, /*Node*/ref){
		ref.parentNode.insertBefore(node, ref);
		return true;	//	boolean
	}

	var _insertAfter = function(/*Node*/node, /*Node*/ref){
		//	summary:
		//		Try to insert node after ref
		var pn = ref.parentNode;
		if(ref == pn.lastChild){
			pn.appendChild(node);
		}else{
			return _insertBefore(node, ref.nextSibling);	//	boolean
		}
		return true;	//	boolean
	}

	dojo.place = function(/*String|DomNode*/node, /*String|DomNode*/refNode, /*String|Number*/position){
		//	summary:
		//		Attempt to insert node into the DOM, choosing from various positioning options.
		//		Returns true if successful, false otherwise.
		//	node:
		//		id or node reference to place relative to refNode
		//	refNode:
		//		id or node reference to use as basis for placement
		//	position:
		//		string noting the position of node relative to refNode or a
		//		number indicating the location in the childNodes collection of
		//		refNode. Accepted string values are:
		//
		//		* before
		//		* after
		//		* first
		//		* last
		//
		//		"first" and "last" indicate positions as children of refNode.

		// FIXME: need to write tests for this!!!!
		if(!node || !refNode || position === undefined){
			return false;	//	boolean
		}
		node = d.byId(node);
		refNode = d.byId(refNode);
		if(typeof position == "number"){
			var cn = refNode.childNodes;
			if((position == 0 && cn.length == 0) ||
				cn.length == position){
				refNode.appendChild(node); return true;
			}
			if(position == 0){
				return _insertBefore(node, refNode.firstChild);
			}
			return _insertAfter(node, cn[position-1]);
		}
		switch(position.toLowerCase()){
			case "before":
				return _insertBefore(node, refNode);	//	boolean
			case "after":
				return _insertAfter(node, refNode);		//	boolean
			case "first":
				if(refNode.firstChild){
					return _insertBefore(node, refNode.firstChild);	//	boolean
				}
				// else fallthrough...
			default: // aka: last
				refNode.appendChild(node);
				return true;	//	boolean
		}
	}

	// Box functions will assume this model.
	// On IE/Opera, BORDER_BOX will be set if the primary document is in quirks mode.
	// Can be set to change behavior of box setters.

	// can be either:
	//	"border-box"
	//	"content-box" (default)
	dojo.boxModel = "content-box";

	// We punt per-node box mode testing completely.
	// If anybody cares, we can provide an additional (optional) unit
	// that overrides existing code to include per-node box sensitivity.

	// Opera documentation claims that Opera 9 uses border-box in BackCompat mode.
	// but experiments (Opera 9.10.8679 on Windows Vista) indicate that it actually continues to use content-box.
	// IIRC, earlier versions of Opera did in fact use border-box.
	// Opera guys, this is really confusing. Opera being broken in quirks mode is not our fault.

	if(d.isIE /*|| dojo.isOpera*/){
		var _dcm = document.compatMode;
		// client code may have to adjust if compatMode varies across iframes
		d.boxModel = _dcm == "BackCompat" || _dcm == "QuirksMode" || d.isIE<6 ? "border-box" : "content-box"; // FIXME: remove IE < 6 support?
	}

	// =============================
	// Style Functions
	// =============================

	// getComputedStyle drives most of the style code.
	// Wherever possible, reuse the returned object.
	//
	// API functions below that need to access computed styles accept an
	// optional computedStyle parameter.
	// If this parameter is omitted, the functions will call getComputedStyle themselves.
	// This way, calling code can access computedStyle once, and then pass the reference to
	// multiple API functions.

/*=====
	dojo.getComputedStyle = function(node){
		//	summary:
		//		Returns a "computed style" object.
		//
		//	description:
		//		Gets a "computed style" object which can be used to gather
		//		information about the current state of the rendered node.
		//
		//		Note that this may behave differently on different browsers.
		//		Values may have different formats and value encodings across
		//		browsers.
		//
		//		Note also that this method is expensive.  Wherever possible,
		//		reuse the returned object.
		//
		//		Use the dojo.style() method for more consistent (pixelized)
		//		return values.
		//
		//	node: DOMNode
		//		A reference to a DOM node. Does NOT support taking an
		//		ID string for speed reasons.
		//	example:
		//	|	dojo.getComputedStyle(dojo.byId('foo')).borderWidth;
		return; // CSS2Properties
	}
=====*/

	var gcs, dv = document.defaultView;
	if(d.isSafari){
		gcs = function(/*DomNode*/node){
			var s = dv.getComputedStyle(node, null);
			if(!s && node.style){
				node.style.display = "";
				s = dv.getComputedStyle(node, null);
			}
			return s || {};
		};
	}else if(d.isIE){
		gcs = function(node){
			return node.currentStyle;
		};
	}else{
		gcs = function(node){
			return dv.getComputedStyle(node, null);
		};
	}
	dojo.getComputedStyle = gcs;

	if(!d.isIE){
		dojo._toPixelValue = function(element, value){
			// style values can be floats, client code may want
			// to round for integer pixels.
			return parseFloat(value) || 0;
		}
	}else{
		dojo._toPixelValue = function(element, avalue){
			if(!avalue){ return 0; }
			// on IE7, medium is usually 4 pixels
			if(avalue=="medium"){ return 4; }
			// style values can be floats, client code may
			// want to round this value for integer pixels.
			if(avalue.slice && (avalue.slice(-2)=='px')){ return parseFloat(avalue); }
			with(element){
				var sLeft = style.left;
				var rsLeft = runtimeStyle.left;
				runtimeStyle.left = currentStyle.left;
				try{
					// 'avalue' may be incompatible with style.left, which can cause IE to throw
					// this has been observed for border widths using "thin", "medium", "thick" constants
					// those particular constants could be trapped by a lookup
					// but perhaps there are more
					style.left = avalue;
					avalue = style.pixelLeft;
				}catch(e){
					avalue = 0;
				}
				style.left = sLeft;
				runtimeStyle.left = rsLeft;
			}
			return avalue;
		}
	}
	var px = d._toPixelValue;

	// FIXME: there opacity quirks on FF that we haven't ported over. Hrm.
	/*=====
	dojo._getOpacity = function(node){
			//	summary:
			//		Returns the current opacity of the passed node as a
			//		floating-point value between 0 and 1.
			//	node: DomNode
			//		a reference to a DOM node. Does NOT support taking an
			//		ID string for speed reasons.
			//	return: Number between 0 and 1
	}
	=====*/

	dojo._getOpacity = d.isIE ? function(node){
		try{
			return node.filters.alpha.opacity / 100; // Number
		}catch(e){
			return 1; // Number
		}
	} : function(node){
		return gcs(node).opacity;
	};

	/*=====
	dojo._setOpacity = function(node, opacity){
			//	summary:
			//		set the opacity of the passed node portably. Returns the
			//		new opacity of the node.
			//	node: DOMNode
			//		a reference to a DOM node. Does NOT support taking an
			//		ID string for performance reasons.
			//	opacity: Number
			//		A Number between 0 and 1. 0 specifies transparent.
			//	return: Number between 0 and 1
	}
	=====*/

	dojo._setOpacity = d.isIE ? function(/*DomNode*/node, /*Number*/opacity){
		if(opacity == 1){
			// on IE7 Alpha(Filter opacity=100) makes text look fuzzy so remove it altogether (bug #2661)
			var filterRE = /FILTER:[^;]*;?/i;
			node.style.cssText = node.style.cssText.replace(filterRE, "");
			if(node.nodeName.toLowerCase() == "tr"){
				d.query("> td", node).forEach(function(i){
					i.style.cssText = i.style.cssText.replace(filterRE, "");
				});
			}
		}else{
			var o = "Alpha(Opacity="+ opacity * 100 +")";
			node.style.filter = o;
		}
		if(node.nodeName.toLowerCase() == "tr"){
			d.query("> td", node).forEach(function(i){
				i.style.filter = o;
			});
		}
		return opacity;
	} : function(node, opacity){
		return node.style.opacity = opacity;
	};

	var _pixelNamesCache = {
		left: true, top: true
	};
	var _pixelRegExp = /margin|padding|width|height|max|min|offset/;  // |border
	var _toStyleValue = function(node, type, value){
		type = type.toLowerCase();
		if(d.isIE && value == "auto"){
			if(type == "height"){ return node.offsetHeight; }
			if(type == "width"){ return node.offsetWidth; }
		}
		if(!(type in _pixelNamesCache)){
			//	if(dojo.isOpera && type == "cssText"){
			// 		FIXME: add workaround for #2855 here
			//	}
			_pixelNamesCache[type] = _pixelRegExp.test(type);
		}
		return _pixelNamesCache[type] ? px(node, value) : value;
	}

	var _floatStyle = d.isIE ? "styleFloat" : "cssFloat";
	var _floatAliases = { "cssFloat": _floatStyle, "styleFloat": _floatStyle, "float": _floatStyle };

	// public API

	dojo.style = function(	/*DomNode|String*/ node,
							/*String?|Object?*/ style,
							/*String?*/ value){
		//	summary:
		//		Accesses styles on a node. If 2 arguments are
		//		passed, acts as a getter. If 3 arguments are passed, acts
		//		as a setter.
		//	node:
		//		id or reference to node to get/set style for
		//	style:
		//		the style property to set in DOM-accessor format
		//		("borderWidth", not "border-width") or an object with key/value
		//		pairs suitable for setting each property.
		//	value:
		//		If passed, sets value on the node for style, handling
		//		cross-browser concerns.
		//	example:
		//		Passing only an ID or node returns the computed style object of
		//		the node:
		//	|	dojo.style("thinger");
		//	example:
		//		Passing a node and a style property returns the current
		//		normalized, computed value for that property:
		//	|	dojo.style("thinger", "opacity"); // 1 by default
		//
		//	example:
		//		Passing a node, a style property, and a value changes the
		//		current display of the node and returns the new computed value
		//	|	dojo.style("thinger", "opacity", 0.5); // == 0.5
		//
		//	example:
		//		Passing a node, an object-style style property sets each of the values in turn and returns the computed style object of the node:
		//	|	dojo.style("thinger", {
		//	|		"opacity": 0.5,
		//	|		"border": "3px solid black",
		//	|		"height": 300
		//	|	});
		//
		// 	example:
		//		When the CSS style property is hyphenated, the JavaScript property is camelCased.
		//		font-size becomes fontSize, and so on.
		//	|	dojo.style("thinger",{
		//	|		fontSize:"14pt",
		//	|		letterSpacing:"1.2em"
		//	|	});
		//
		//	example:
		//		dojo.NodeList implements .style() using the same syntax, omitting the "node" parameter, calling
		//		dojo.style() on every element of the list. See: dojo.query and dojo.NodeList
		//	|	dojo.query(".someClassName").style("visibility","hidden");
		//	|	// or
		//	|	dojo.query("#baz > div").style({
		//	|		opacity:0.75,
		//	|		fontSize:"13pt"
		//	|	});

		var n = d.byId(node), args = arguments.length, op = (style=="opacity");
		style = _floatAliases[style] || style;
		if(args == 3){
			return op ? d._setOpacity(n, value) : n.style[style] = value; /*Number*/
		}
		if(args == 2 && op){
			return d._getOpacity(n);
		}
		var s = gcs(n);
		if(args == 2 && !d.isString(style)){
			for(var x in style){
				d.style(node, x, style[x]);
			}
			return s;
		}
		return (args == 1) ? s : _toStyleValue(n, style, s[style]); /* CSS2Properties||String||Number */
	}

	// =============================
	// Box Functions
	// =============================

	dojo._getPadExtents = function(/*DomNode*/n, /*Object*/computedStyle){
		//	summary:
		// 		Returns object with special values specifically useful for node
		// 		fitting.
		//
		// 		* l/t = left/top padding (respectively)
		// 		* w = the total of the left and right padding
		// 		* h = the total of the top and bottom padding
		//
		//		If 'node' has position, l/t forms the origin for child nodes.
		//		The w/h are used for calculating boxes.
		//		Normally application code will not need to invoke this
		//		directly, and will use the ...box... functions instead.
		var
			s = computedStyle||gcs(n),
			l = px(n, s.paddingLeft),
			t = px(n, s.paddingTop);
		return {
			l: l,
			t: t,
			w: l+px(n, s.paddingRight),
			h: t+px(n, s.paddingBottom)
		};
	}

	dojo._getBorderExtents = function(/*DomNode*/n, /*Object*/computedStyle){
		//	summary:
		//		returns an object with properties useful for noting the border
		//		dimensions.
		//
		// 		* l/t = the sum of left/top border (respectively)
		//		* w = the sum of the left and right border
		//		* h = the sum of the top and bottom border
		//
		//		The w/h are used for calculating boxes.
		//		Normally application code will not need to invoke this
		//		directly, and will use the ...box... functions instead.
		var
			ne = "none",
			s = computedStyle||gcs(n),
			bl = (s.borderLeftStyle != ne ? px(n, s.borderLeftWidth) : 0),
			bt = (s.borderTopStyle != ne ? px(n, s.borderTopWidth) : 0);
		return {
			l: bl,
			t: bt,
			w: bl + (s.borderRightStyle!=ne ? px(n, s.borderRightWidth) : 0),
			h: bt + (s.borderBottomStyle!=ne ? px(n, s.borderBottomWidth) : 0)
		};
	}

	dojo._getPadBorderExtents = function(/*DomNode*/n, /*Object*/computedStyle){
		//	summary:
		//		returns object with properties useful for box fitting with
		//		regards to padding.
		//
		//		* l/t = the sum of left/top padding and left/top border (respectively)
		//		* w = the sum of the left and right padding and border
		//		* h = the sum of the top and bottom padding and border
		//
		//		The w/h are used for calculating boxes.
		//		Normally application code will not need to invoke this
		//		directly, and will use the ...box... functions instead.
		var
			s = computedStyle||gcs(n),
			p = d._getPadExtents(n, s),
			b = d._getBorderExtents(n, s);
		return {
			l: p.l + b.l,
			t: p.t + b.t,
			w: p.w + b.w,
			h: p.h + b.h
		};
	}

	dojo._getMarginExtents = function(n, computedStyle){
		//	summary:
		//		returns object with properties useful for box fitting with
		//		regards to box margins (i.e., the outer-box).
		//
		//		* l/t = marginLeft, marginTop, respectively
		//		* w = total width, margin inclusive
		//		* h = total height, margin inclusive
		//
		//		The w/h are used for calculating boxes.
		//		Normally application code will not need to invoke this
		//		directly, and will use the ...box... functions instead.
		var
			s = computedStyle||gcs(n),
			l = px(n, s.marginLeft),
			t = px(n, s.marginTop),
			r = px(n, s.marginRight),
			b = px(n, s.marginBottom);
		if(d.isSafari && (s.position != "absolute")){
			// FIXME: Safari's version of the computed right margin
			// is the space between our right edge and the right edge
			// of our offsetParent.
			// What we are looking for is the actual margin value as
			// determined by CSS.
			// Hack solution is to assume left/right margins are the same.
			r = l;
		}
		return {
			l: l,
			t: t,
			w: l+r,
			h: t+b
		};
	}

	// Box getters work in any box context because offsetWidth/clientWidth
	// are invariant wrt box context
	//
	// They do *not* work for display: inline objects that have padding styles
	// because the user agent ignores padding (it's bogus styling in any case)
	//
	// Be careful with IMGs because they are inline or block depending on
	// browser and browser mode.

	// Although it would be easier to read, there are not separate versions of
	// _getMarginBox for each browser because:
	// 1. the branching is not expensive
	// 2. factoring the shared code wastes cycles (function call overhead)
	// 3. duplicating the shared code wastes bytes

	dojo._getMarginBox = function(/*DomNode*/node, /*Object*/computedStyle){
		// summary:
		//		returns an object that encodes the width, height, left and top
		//		positions of the node's margin box.
		var s = computedStyle||gcs(node), me = d._getMarginExtents(node, s);
		var	l = node.offsetLeft - me.l,	t = node.offsetTop - me.t;
		if(d.isMoz){
			// Mozilla:
			// If offsetParent has a computed overflow != visible, the offsetLeft is decreased
			// by the parent's border.
			// We don't want to compute the parent's style, so instead we examine node's
			// computed left/top which is more stable.
			var sl = parseFloat(s.left), st = parseFloat(s.top);
			if(!isNaN(sl) && !isNaN(st)){
				l = sl, t = st;
			}else{
				// If child's computed left/top are not parseable as a number (e.g. "auto"), we
				// have no choice but to examine the parent's computed style.
				var p = node.parentNode;
				if(p && p.style){
					var pcs = gcs(p);
					if(pcs.overflow != "visible"){
						var be = d._getBorderExtents(p, pcs);
						l += be.l, t += be.t;
					}
				}
			}
		}else if(d.isOpera){
			// On Opera, offsetLeft includes the parent's border
			var p = node.parentNode;
			if(p){
				var be = d._getBorderExtents(p);
				l -= be.l, t -= be.t;
			}
		}
		return {
			l: l,
			t: t,
			w: node.offsetWidth + me.w,
			h: node.offsetHeight + me.h
		};
	}

	dojo._getContentBox = function(node, computedStyle){
		// summary:
		//		Returns an object that encodes the width, height, left and top
		//		positions of the node's content box, irrespective of the
		//		current box model.

		// clientWidth/Height are important since the automatically account for scrollbars
		// fallback to offsetWidth/Height for special cases (see #3378)
		var s=computedStyle||gcs(node), pe=d._getPadExtents(node, s), be=d._getBorderExtents(node, s), w=node.clientWidth, h;
		if(!w){
			w=node.offsetWidth, h=node.offsetHeight;
		}else{
			h=node.clientHeight, be.w = be.h = 0;
		}
		// On Opera, offsetLeft includes the parent's border
		if(d.isOpera){ pe.l += be.l; pe.t += be.t; };
		return {
			l: pe.l,
			t: pe.t,
			w: w - pe.w - be.w,
			h: h - pe.h - be.h
		};
	}

	dojo._getBorderBox = function(node, computedStyle){
		var s=computedStyle||gcs(node), pe=d._getPadExtents(node, s), cb=d._getContentBox(node, s);
		return {
			l: cb.l - pe.l,
			t: cb.t - pe.t,
			w: cb.w + pe.w,
			h: cb.h + pe.h
		};
	}

	// Box setters depend on box context because interpretation of width/height styles
	// vary wrt box context.
	//
	// The value of dojo.boxModel is used to determine box context.
	// dojo.boxModel can be set directly to change behavior.
	//
	// Beware of display: inline objects that have padding styles
	// because the user agent ignores padding (it's a bogus setup anyway)
	//
	// Be careful with IMGs because they are inline or block depending on
	// browser and browser mode.
	//
	// Elements other than DIV may have special quirks, like built-in
	// margins or padding, or values not detectable via computedStyle.
	// In particular, margins on TABLE do not seems to appear
	// at all in computedStyle on Mozilla.

	dojo._setBox = function(/*DomNode*/node, /*Number?*/l, /*Number?*/t, /*Number?*/w, /*Number?*/h, /*String?*/u){
		//	summary:
		//		sets width/height/left/top in the current (native) box-model
		//		dimentions. Uses the unit passed in u.
		//	node: DOM Node reference. Id string not supported for performance reasons.
		//	l: optional. left offset from parent.
		//	t: optional. top offset from parent.
		//	w: optional. width in current box model.
		//	h: optional. width in current box model.
		//	u: optional. unit measure to use for other measures. Defaults to "px".
		u = u || "px";
		var s = node.style;
		if(!isNaN(l)){ s.left = l+u; }
		if(!isNaN(t)){ s.top = t+u; }
		if(w>=0){ s.width = w+u; }
		if(h>=0){ s.height = h+u; }
	}

	dojo._usesBorderBox = function(/*DomNode*/node){
		//	summary:
		//		True if the node uses border-box layout.

		// We could test the computed style of node to see if a particular box
		// has been specified, but there are details and we choose not to bother.
		var n = node.tagName;
		// For whatever reason, TABLE and BUTTON are always border-box by default.
		// If you have assigned a different box to either one via CSS then
		// box functions will break.
		return d.boxModel=="border-box" || n=="TABLE" || n=="BUTTON"; // boolean
	}

	dojo._setContentSize = function(/*DomNode*/node, /*Number*/widthPx, /*Number*/heightPx, /*Object*/computedStyle){
		//	summary:
		//		Sets the size of the node's contents, irrespective of margins,
		//		padding, or borders.
		if(d._usesBorderBox(node)){
			var pb = d._getPadBorderExtents(node, computedStyle);
			if(widthPx >= 0){ widthPx += pb.w; }
			if(heightPx >= 0){ heightPx += pb.h; }
		}
		d._setBox(node, NaN, NaN, widthPx, heightPx);
	}

	dojo._setMarginBox = function(/*DomNode*/node, 	/*Number?*/leftPx, /*Number?*/topPx,
													/*Number?*/widthPx, /*Number?*/heightPx,
													/*Object*/computedStyle){
		//	summary:
		//		sets the size of the node's margin box and placement
		//		(left/top), irrespective of box model. Think of it as a
		//		passthrough to dojo._setBox that handles box-model vagaries for
		//		you.

		var s = computedStyle||gcs(node);
		// Some elements have special padding, margin, and box-model settings.
		// To use box functions you may need to set padding, margin explicitly.
		// Controlling box-model is harder, in a pinch you might set dojo.boxModel.
		var bb=d._usesBorderBox(node),
				pb=bb ? _nilExtents : d._getPadBorderExtents(node, s),
				mb=d._getMarginExtents(node, s);
		if(widthPx>=0){	widthPx = Math.max(widthPx - pb.w - mb.w, 0); }
		if(heightPx>=0){ heightPx = Math.max(heightPx - pb.h - mb.h, 0); }
		d._setBox(node, leftPx, topPx, widthPx, heightPx);
	}

	var _nilExtents = { l:0, t:0, w:0, h:0 };

	// public API

	dojo.marginBox = function(/*DomNode|String*/node, /*Object?*/box){
		//	summary:
		//		Getter/setter for the margin-box of node.
		//	description:
		//		Returns an object in the expected format of box (regardless
		//		if box is passed). The object might look like:
		//			`{ l: 50, t: 200, w: 300: h: 150 }`
		//		for a node offset from its parent 50px to the left, 200px from
		//		the top with a margin width of 300px and a margin-height of
		//		150px.
		//	node:
		//		id or reference to DOM Node to get/set box for
		//	box:
		//		If passed, denotes that dojo.marginBox() should
		//		update/set the margin box for node. Box is an object in the
		//		above format. All properties are optional if passed.
		var n=d.byId(node), s=gcs(n), b=box;
		return !b ? d._getMarginBox(n, s) : d._setMarginBox(n, b.l, b.t, b.w, b.h, s); // Object
	}

	dojo.contentBox = function(/*DomNode|String*/node, /*Object?*/box){
		//	summary:
		//		Getter/setter for the content-box of node.
		//	description:
		//		Returns an object in the expected format of box (regardless if box is passed).
		//		The object might look like:
		//			`{ l: 50, t: 200, w: 300: h: 150 }`
		//		for a node offset from its parent 50px to the left, 200px from
		//		the top with a content width of 300px and a content-height of
		//		150px. Note that the content box may have a much larger border
		//		or margin box, depending on the box model currently in use and
		//		CSS values set/inherited for node.
		//	node:
		//		id or reference to DOM Node to get/set box for
		//	box:
		//		If passed, denotes that dojo.contentBox() should
		//		update/set the content box for node. Box is an object in the
		//		above format. All properties are optional if passed.
		var n=dojo.byId(node), s=gcs(n), b=box;
		return !b ? d._getContentBox(n, s) : d._setContentSize(n, b.w, b.h, s); // Object
	}

	// =============================
	// Positioning
	// =============================

	var _sumAncestorProperties = function(node, prop){
		if(!(node = (node||0).parentNode)){return 0};
		var val, retVal = 0, _b = d.body();
		while(node && node.style){
			if(gcs(node).position == "fixed"){
				return 0;
			}
			val = node[prop];
			if(val){
				retVal += val - 0;
				// opera and khtml #body & #html has the same values, we only
				// need one value
				if(node == _b){ break; }
			}
			node = node.parentNode;
		}
		return retVal;	//	integer
	}

	dojo._docScroll = function(){
		var
			_b = d.body(),
			_w = d.global,
			de = d.doc.documentElement;
		return {
			y: (_w.pageYOffset || de.scrollTop || _b.scrollTop || 0),
			x: (_w.pageXOffset || d._fixIeBiDiScrollLeft(de.scrollLeft) || _b.scrollLeft || 0)
		};
	};

	dojo._isBodyLtr = function(){
		//FIXME: could check html and body tags directly instead of computed style?  need to ignore case, accept empty values
		return !("_bodyLtr" in d) ?
			d._bodyLtr = gcs(d.body()).direction == "ltr" :
			d._bodyLtr; // Boolean
	}

	dojo._getIeDocumentElementOffset = function(){
		// summary
		// The following values in IE contain an offset:
		//     event.clientX
		//     event.clientY
		//     node.getBoundingClientRect().left
		//     node.getBoundingClientRect().top
		// But other position related values do not contain this offset, such as
		// node.offsetLeft, node.offsetTop, node.style.left and node.style.top.
		// The offset is always (2, 2) in LTR direction. When the body is in RTL
		// direction, the offset counts the width of left scroll bar's width.
		// This function computes the actual offset.

		//NOTE: assumes we're being called in an IE browser

		var de = d.doc.documentElement;
		//FIXME: use this instead?			var de = d.compatMode == "BackCompat" ? d.body : d.documentElement;

		return (d.isIE >= 7) ?
			{x: de.getBoundingClientRect().left, y: de.getBoundingClientRect().top}
		:
			// IE 6.0
			{x: d._isBodyLtr() || window.parent == window ?
				de.clientLeft : de.offsetWidth - de.clientWidth - de.clientLeft,
				y: de.clientTop}; // Object
	};

	dojo._fixIeBiDiScrollLeft = function(/*Integer*/ scrollLeft){
		// In RTL direction, scrollLeft should be a negative value, but IE
		// returns a positive one. All codes using documentElement.scrollLeft
		// must call this function to fix this error, otherwise the position
		// will offset to right when there is a horizontal scrollbar.
		var dd = d.doc;
		if(d.isIE && !dojo._isBodyLtr()){
			var de = dd.compatMode == "BackCompat" ? dd.body : dd.documentElement;
			return scrollLeft + de.clientWidth - de.scrollWidth; // Integer
		}
		return scrollLeft; // Integer
	}

	dojo._abs = function(/*DomNode*/node, /*Boolean?*/includeScroll){
		//	summary:
		//		Gets the position of the passed element relative to
		//		the viewport (if includeScroll==false), or relative to the
		//		document root (if includeScroll==true).
		//
		//		Returns an object of the form:
		//			{ x: 100, y: 300 }
		//		if includeScroll is passed, the x and y values will include any
		//		document offsets that may affect the position relative to the
		//		viewport.

		// FIXME: need to decide in the brave-new-world if we're going to be
		// margin-box or border-box.
		var ownerDocument = node.ownerDocument;
		var ret = {
			x: 0,
			y: 0
		};

		// targetBoxType == "border-box"
		var db = d.body();
		if(d.isIE || (d.isFF >= 3)){
			var client = node.getBoundingClientRect();
			var offset = (d.isIE) ? d._getIeDocumentElementOffset() : { x: 0, y: 0};
			ret.x = client.left - offset.x;
			ret.y = client.top - offset.y;
		}else if(ownerDocument["getBoxObjectFor"]){
			// mozilla
			var bo = ownerDocument.getBoxObjectFor(node),
				b = d._getBorderExtents(node);
			ret.x = bo.x - b.l - _sumAncestorProperties(node, "scrollLeft");
			ret.y = bo.y - b.t - _sumAncestorProperties(node, "scrollTop");
		}else{
			if(node["offsetParent"]){
				var endNode;
				// in Safari, if the node is an absolutely positioned child of
				// the body and the body has a margin the offset of the child
				// and the body contain the body's margins, so we need to end
				// at the body
				// FIXME: getting contrary results to the above in latest WebKit.
				if(d.isSafari &&
					//(node.style.getPropertyValue("position") == "absolute") &&
					(gcs(node).position == "absolute") &&
					(node.parentNode == db)){
					endNode = db;
				}else{
					endNode = db.parentNode;
				}
				if(node.parentNode != db){
					var nd = node;
					if(d.isOpera){ nd = db; }
					ret.x -= _sumAncestorProperties(nd, "scrollLeft");
					ret.y -= _sumAncestorProperties(nd, "scrollTop");
				}
				var curnode = node;
				do{
					var n = curnode.offsetLeft;
					//FIXME: ugly hack to workaround the submenu in
					//popupmenu2 does not shown up correctly in opera.
					//Someone have a better workaround?
					if(!d.isOpera || n > 0){
						ret.x += isNaN(n) ? 0 : n;
					}
					var t = curnode.offsetTop;
					ret.y += isNaN(t) ? 0 : t;
					if(d.isSafari && curnode != node){
						var cs = gcs(curnode);
						ret.x += px(curnode, cs.borderLeftWidth);
						ret.y += px(curnode, cs.borderTopWidth);
					}
					curnode = curnode.offsetParent;
				}while((curnode != endNode) && curnode);
			}else if(node.x && node.y){
				ret.x += isNaN(node.x) ? 0 : node.x;
				ret.y += isNaN(node.y) ? 0 : node.y;
			}
		}
		// account for document scrolling
		// if offsetParent is used, ret value already includes scroll position
		// so we may have to actually remove that value if !includeScroll
		if(includeScroll){
			var scroll = d._docScroll();
			ret.y += scroll.y;
			ret.x += scroll.x;
		}

		return ret; // object
	}

	// FIXME: need a setter for coords or a moveTo!!
	dojo.coords = function(/*DomNode|String*/node, /*Boolean?*/includeScroll){
		//	summary:
		//		Returns an object that measures margin box width/height and
		//		absolute positioning data from dojo._abs().
		//
		//	description:
		//		Returns an object that measures margin box width/height and
		//		absolute positioning data from dojo._abs().
		//		Return value will be in the form:
		//			`{ l: 50, t: 200, w: 300: h: 150, x: 100, y: 300 }`
		//		Does not act as a setter. If includeScroll is passed, the x and
		//		y params are affected as one would expect in dojo._abs().
		var n=d.byId(node), s=gcs(n), mb=d._getMarginBox(n, s);
		var abs = d._abs(n, includeScroll);
		mb.x = abs.x;
		mb.y = abs.y;
		return mb;
	}

	// =============================
	// Element attribute Functions
	// =============================

	var _fixAttrName = function(/*String*/name){
		switch(name.toLowerCase()){
			case "tabindex":
				// Internet Explorer will only set or remove tabindex
				// if it is spelled "tabIndex"
				// console.debug((dojo.isIE && dojo.isIE < 8)? "tabIndex" : "tabindex");
				return (d.isIE && d.isIE < 8) ? "tabIndex" : "tabindex";
			default:
				return name;
		}
	}

	// non-deprecated HTML4 attributes with default values
	// http://www.w3.org/TR/html401/index/attributes.html
	// FF and Safari will return the default values if you
	// access the attributes via a property but not
	// via getAttribute()
	var _attrProps = {
		colspan: "colSpan",
		enctype: "enctype",
		frameborder: "frameborder",
		method: "method",
		rowspan: "rowSpan",
		scrolling: "scrolling",
		shape: "shape",
		span: "span",
		type: "type",
		valuetype: "valueType"
	}

	dojo.hasAttr = function(/*DomNode|String*/node, /*String*/name){
		//	summary:
		//		Returns true if the requested attribute is specified on the
		//		given element, and false otherwise.
		//	node:
		//		id or reference to the element to check
		//	name:
		//		the name of the attribute
		//	returns:
		//		true if the requested attribute is specified on the
		//		given element, and false otherwise
		var attr = d.byId(node).getAttributeNode(_fixAttrName(name));
		return attr ? attr.specified : false; // Boolean
	}

	var _evtHdlrMap = {

	}

	var _ctr = 0;
	var _attrId = dojo._scopeName + "attrid";

	dojo.attr = function(/*DomNode|String*/node, /*String|Object*/name, /*String?*/value){
		//	summary:
		//		Gets or sets an attribute on an HTML element.
		//	description:
		//		Handles normalized getting and setting of attributes on DOM
		//		Nodes. If 2 arguments are passed, and a the second argumnt is a
		//		string, acts as a getter.
		//
		//		If a third argument is passed, or if the second argumnt is a
		//		map of attributes, acts as a setter.
		//
		//		When passing functions as values, note that they will not be
		//		directly assigned to slots on the node, but rather the default
		//		behavior will be removed and the new behavior will be added
		//		using `dojo.connect()`, meaning that event handler properties
		//		will be normalized and that some caveats with regards to
		//		non-standard behaviors for onsubmit apply. Namely that you
		//		should cancel form submission using `dojo.stopEvent()` on the
		//		passed event object instead of returning a boolean value from
		//		the handler itself.
		//	node:
		//		id or reference to the element to get or set the attribute on
		//	name:
		//		the name of the attribute to get or set.
		//	value:
		//		The value to set for the attribute
		//	returns:
		//		when used as a getter, the value of the requested attribute
		//		or null if that attribute does not have a specified or
		//		default value;
		//
		//		when user as a setter, undefined
		//	example:
		//	|	// get the current value of the "foo" attribute on a node
		//	|	dojo.attr(dojo.byId("nodeId"), "foo");
		//	|
		//	|	// we can just pass the id:
		//	|	dojo.attr("nodeId", "foo");
		//	|
		//	|	// use attr() to set the tab index
		//	|	dojo.attr("nodeId", "tabindex", 3);
		//	|
		//	|	// set multiple values at once, including event handlers:
		//	|	dojo.attr("formId", {
		//	|		"foo": "bar",
		//	|		"tabindex": -1,
		//	|		"method": "POST",
		//	|		"onsubmit": function(e){
		//	|			// stop submitting the form. Note that the IE behavior
		//	|			// of returning true or false will have no effect here
		//	|			// since our handler is connect()ed to the built-in
		//	|			// onsubmit behavior and so we need to use
		//	|			// dojo.stopEvent() to ensure that the submission
		//	|			// doesn't proceed.
		//	|			dojo.stopEvent(e);
		//	|
		//	|			// submit the form with Ajax
		//	|			dojo.xhrPost({ form: "formId" });
		//	|		}
		//	|	});

		var args = arguments.length;
		if(args == 2 && !d.isString(name)){
			for(var x in name){ d.attr(node, x, name[x]); }
			return;
		}
		node = d.byId(node);
		name = _fixAttrName(name);
		if(args == 3){
			if(d.isFunction(value)){
				// clobber if we can
				var attrId = d.attr(node, _attrId);
				if(!attrId){
					attrId = _ctr++;
					d.attr(node, _attrId, attrId);
				}
				if(!_evtHdlrMap[attrId]){
					_evtHdlrMap[attrId] = {};
				}
				var h = _evtHdlrMap[attrId][name];
				if(h){
					d.disconnect(h);
				}else{
					try{
						delete node[name];
					}catch(e){}
				}

				// ensure that event objects are normalized, etc.
				_evtHdlrMap[attrId][name] = d.connect(node, name, value);

			}else if(typeof value == "boolean"){ // e.g. onsubmit, disabled
				// if a function, we should normalize the event object here!!!
				node[name] = value;
			}else{
				node.setAttribute(name, value);
			}
			return;
		}else{
			// should we access this attribute via a property or
			// via getAttribute()?
			var prop = _attrProps[name.toLowerCase()];
			if(prop){
				return node[prop];
			}else{
				var value = node[name];
				return (typeof value == 'boolean' || typeof value == 'function') ? value : (d.hasAttr(node, name) ? node.getAttribute(name) : null);
			}
		}
	}

	dojo.removeAttr = function(/*DomNode|String*/node, /*String*/name){
		//	summary:
		//		Removes an attribute from an HTML element.
		//	node:
		//		id or reference to the element to remove the attribute from
		//	name:
		//		the name of the attribute to remove
		d.byId(node).removeAttribute(_fixAttrName(name));
	}
})();

// =============================
// (CSS) Class Functions
// =============================

dojo.hasClass = function(/*DomNode|String*/node, /*String*/classStr){
	//	summary:
	//		Returns whether or not the specified classes are a portion of the
	//		class list currently applied to the node.
	return ((" "+dojo.byId(node).className+" ").indexOf(" "+classStr+" ") >= 0);  // Boolean
};

dojo.addClass = function(/*DomNode|String*/node, /*String*/classStr){
	//	summary:
	//		Adds the specified classes to the end of the class list on the
	//		passed node.
	node = dojo.byId(node);
	var cls = node.className;
	if((" "+cls+" ").indexOf(" "+classStr+" ") < 0){
		node.className = cls + (cls ? ' ' : '') + classStr;
	}
};

dojo.removeClass = function(/*DomNode|String*/node, /*String*/classStr){
	// summary: Removes the specified classes from node.
	node = dojo.byId(node);
	var t = dojo.trim((" " + node.className + " ").replace(" " + classStr + " ", " "));
	if(node.className != t){ node.className = t; }
};

dojo.toggleClass = function(/*DomNode|String*/node, /*String*/classStr, /*Boolean?*/condition){
	//	summary:
	//		Adds a class to node if not present, or removes if present.
	//		Pass a boolean condition if you want to explicitly add or remove.
	//	condition:
	//		If passed, true means to add the class, false means to remove.
	if(condition === undefined){
		condition = !dojo.hasClass(node, classStr);
	}
	dojo[condition ? "addClass" : "removeClass"](node, classStr);
};

}

if(!dojo._hasResource["dojo._base.NodeList"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.NodeList"] = true;
dojo.provide("dojo._base.NodeList");

(function(){

	var d = dojo;

	var tnl = function(arr){
		// decorate an array to make it look like a NodeList
		arr.constructor = dojo.NodeList;
		dojo._mixin(arr, dojo.NodeList.prototype);
		return arr;
	}

	var _mapIntoDojo = function(func, alwaysThis){
		// returns a function which, when executed in the scope of its caller,
		// applies the passed arguments to a particular dojo.* function (named
		// in func) and aggregates the returns. if alwaysThis is true, it
		// always returns the scope object and not the collected returns from
		// the Dojo method
		return function(){
			var _a = arguments;
			var aa = d._toArray(_a, 0, [null]);
			var s = this.map(function(i){
				aa[0] = i;
				return d[func].apply(d, aa);
			});
			return (alwaysThis || ( (_a.length > 1) || !d.isString(_a[0]) )) ? this : s; // String||dojo.NodeList
		}
	};

	dojo.NodeList = function(){
		//	summary:
		//		dojo.NodeList is as subclass of Array which adds syntactic
		//		sugar for chaining, common iteration operations, animation,
		//		and node manipulation. NodeLists are most often returned as
		//		the result of dojo.query() calls.
		//	example:
		//		create a node list from a node
		//		|	new dojo.NodeList(dojo.byId("foo"));

		return tnl(Array.apply(null, arguments));
	}

	dojo.NodeList._wrap = tnl;

	dojo.extend(dojo.NodeList, {
		// http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array#Methods

		// FIXME: handle return values for #3244
		//		http://trac.dojotoolkit.org/ticket/3244

		// FIXME:
		//		need to wrap or implement:
		//			join (perhaps w/ innerHTML/outerHTML overload for toString() of items?)
		//			reduce
		//			reduceRight

		slice: function(/*===== begin, end =====*/){
			// summary:
			//		Returns a new NodeList, maintaining this one in place
			// description:
			//		This method behaves exactly like the Array.slice method
			//		with the caveat that it returns a dojo.NodeList and not a
			//		raw Array. For more details, see:
			//			http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:slice
			// begin: Integer
			//		Can be a positive or negative integer, with positive
			//		integers noting the offset to begin at, and negative
			//		integers denoting an offset from the end (i.e., to the left
			//		of the end)
			// end: Integer?
			//		Optional parameter to describe what position relative to
			//		the NodeList's zero index to end the slice at. Like begin,
			//		can be positive or negative.
			var a = dojo._toArray(arguments);
			return tnl(a.slice.apply(this, a));
		},

		splice: function(/*===== index, howmany, item =====*/){
			// summary:
			//		Returns a new NodeList, manipulating this NodeList based on
			//		the arguments passed, potentially splicing in new elements
			//		at an offset, optionally deleting elements
			// description:
			//		This method behaves exactly like the Array.splice method
			//		with the caveat that it returns a dojo.NodeList and not a
			//		raw Array. For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:splice>
			// index: Integer
			//		begin can be a positive or negative integer, with positive
			//		integers noting the offset to begin at, and negative
			//		integers denoting an offset from the end (i.e., to the left
			//		of the end)
			// howmany: Integer?
			//		Optional parameter to describe what position relative to
			//		the NodeList's zero index to end the slice at. Like begin,
			//		can be positive or negative.
			// item: Object...?
			//		Any number of optional parameters may be passed in to be
			//		spliced into the NodeList
			// returns:
			//		dojo.NodeList
			var a = dojo._toArray(arguments);
			return tnl(a.splice.apply(this, a));
		},

		concat: function(/*===== item =====*/){
			// summary:
			//		Returns a new NodeList comprised of items in this NodeList
			//		as well as items passed in as parameters
			// description:
			//		This method behaves exactly like the Array.concat method
			//		with the caveat that it returns a dojo.NodeList and not a
			//		raw Array. For more details, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:concat>
			// item: Object...?
			//		Any number of optional parameters may be passed in to be
			//		spliced into the NodeList
			// returns:
			//		dojo.NodeList
			var a = dojo._toArray(arguments, 0, [this]);
			return tnl(a.concat.apply([], a));
		},

		indexOf: function(/*Object*/ value, /*Integer?*/ fromIndex){
			//	summary:
			//		see dojo.indexOf(). The primary difference is that the acted-on
			//		array is implicitly this NodeList
			// value:
			//		The value to search for.
			// fromIndex:
			//		The loction to start searching from. Optional. Defaults to 0.
			//	description:
			//		For more details on the behavior of indexOf, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:indexOf>
			//	returns:
			//		Positive Integer or 0 for a match, -1 of not found.
			return d.indexOf(this, value, fromIndex); // Integer
		},

		lastIndexOf: function(/*===== value, fromIndex =====*/){
			// summary:
			//		see dojo.lastIndexOf(). The primary difference is that the
			//		acted-on array is implicitly this NodeList
			//	description:
			//		For more details on the behavior of lastIndexOf, see:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:lastIndexOf>
			// value: Object
			//		The value to search for.
			// fromIndex: Integer?
			//		The loction to start searching from. Optional. Defaults to 0.
			// returns:
			//		Positive Integer or 0 for a match, -1 of not found.
			return d.lastIndexOf.apply(d, d._toArray(arguments, 0, [this])); // Integer
		},

		every: function(/*Function*/callback, /*Object?*/thisObject){
			//	summary:
			//		see `dojo.every()` and:
			//			<http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:every>
			//		Takes the same structure of arguments and returns as
			//		dojo.every() with the caveat that the passed array is
			//		implicitly this NodeList
			return d.every(this, callback, thisObject); // Boolean
		},

		some: function(/*Function*/callback, /*Object?*/thisObject){
			//	summary:
			//		see dojo.some() and:
			//			http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:some
			//		Takes the same structure of arguments and returns as
			//		dojo.some() with the caveat that the passed array is
			//		implicitly this NodeList
			return d.some(this, callback, thisObject); // Boolean
		},

		map: function(/*Function*/ func, /*Function?*/ obj){
			//	summary:
			//		see dojo.map(). The primary difference is that the acted-on
			//		array is implicitly this NodeList and the return is a
			//		dojo.NodeList (a subclass of Array)

			return d.map(this, func, obj, d.NodeList); // dojo.NodeList
		},

		forEach: function(callback, thisObj){
			//	summary:
			//		see dojo.forEach(). The primary difference is that the acted-on
			//		array is implicitly this NodeList

			d.forEach(this, callback, thisObj);
			// non-standard return to allow easier chaining
			return this; // dojo.NodeList
		},

		// custom methods

		coords: function(){
			//	summary:
			// 		Returns the box objects all elements in a node list as
			// 		an Array (*not* a NodeList)

			return d.map(this, d.coords); // Array
		},

		/*=====
		attr: function(property, value){
			//	summary:
			//		gets or sets the DOM attribute for every element in the
			//		NodeList
			//	property: String
			//		the attribute to get/set
			//	value: String?
			//		optional. The value to set the property to
			//	return:
			//		if no value is passed, the result is an array of attribute values
			//		If a value is passed, the return is this NodeList
		},

		style: function(property, value){
			//	summary:
			//		gets or sets the CSS property for every element in the NodeList
			//	property: String
			//		the CSS property to get/set, in JavaScript notation
			//		("lineHieght" instead of "line-height")
			//	value: String?
			//		optional. The value to set the property to
			//	return:
			//		if no value is passed, the result is an array of strings.
			//		If a value is passed, the return is this NodeList
		},

		addClass: function(className){
			//	summary:
			//		adds the specified class to every node in the list
			//	className: String
			//		the CSS class to add
			//	return:
			//		dojo.NodeList, this list
		},

		removeClass: function(className){
			//	summary:
			//		removes the specified class from every node in the list
			//	className: String
			//		the CSS class to add
			//	return:
			//		dojo.NodeList, this list
		},

		toggleClass: function(className, condition){
			//	summary:
			//		Adds a class to node if not present, or removes if present.
			//		Pass a boolean condition if you want to explicitly add or remove.
			//	condition: Boolean?
			//		If passed, true means to add the class, false means to remove.
			//	className: String
			//		the CSS class to add
			//	return: dojo.NodeList
			//		this list
		},

		connect: function(methodName, objOrFunc, funcName){
			//	summary:
			//		attach event handlers to every item of the NodeList. Uses dojo.connect()
			//		so event properties are normalized
			//	methodName: String
			//		the name of the method to attach to. For DOM events, this should be
			//		the lower-case name of the event
			//	objOrFunc: Object|Function|String
			//		if 2 arguments are passed (methodName, objOrFunc), objOrFunc should
			//		reference a function or be the name of the function in the global
			//		namespace to attach. If 3 arguments are provided
			//		(methodName, objOrFunc, funcName), objOrFunc must be the scope to
			//		locate the bound function in
			//	funcName: String?
			//		optional. A string naming the function in objOrFunc to bind to the
			//		event. May also be a function reference.
			//	example:
			//		add an onclick handler to every button on the page
			//		|	dojo.query("div:nth-child(odd)").connect("onclick", function(e){
			//		|		console.debug("clicked!");
			//		|	});
			// example:
			//		attach foo.bar() to every odd div's onmouseover
			//		|	dojo.query("div:nth-child(odd)").connect("onmouseover", foo, "bar");
		},
		=====*/
		attr: _mapIntoDojo("attr"),
		style: _mapIntoDojo("style"),
		addClass: _mapIntoDojo("addClass", true),
		removeClass: _mapIntoDojo("removeClass", true),
		toggleClass: _mapIntoDojo("toggleClass", true),
		connect: _mapIntoDojo("connect", true),

		// FIXME: connectPublisher()? connectRunOnce()?

		place: function(/*String||Node*/ queryOrNode, /*String*/ position){
			//	summary:
			//		places elements of this node list relative to the first element matched
			//		by queryOrNode. Returns the original NodeList.
			//	queryOrNode:
			//		may be a string representing any valid CSS3 selector or a DOM node.
			//		In the selector case, only the first matching element will be used
			//		for relative positioning.
			//	position:
			//		can be one of:
			//			* "last"||"end" (default)
			//			* "first||"start"
			//			* "before"
			//			* "after"
			// 		or an offset in the childNodes property
			var item = d.query(queryOrNode)[0];
			return this.forEach(function(i){ d.place(i, item, (position||"last")); }); // dojo.NodeList
		},

		orphan: function(/*String?*/ simpleFilter){
			//	summary:
			//		removes elements in this list that match the simple
			//		filter from their parents and returns them as a new
			//		NodeList.
			//	simpleFilter:
			//		single-expression CSS filter
			//	return:
			//		`dojo.NodeList` the orpahned elements
			var orphans = simpleFilter ? d._filterQueryResult(this, simpleFilter) : this;
			orphans.forEach(function(item){
				if(item.parentNode){
					item.parentNode.removeChild(item);
				}
			});
			return orphans; // dojo.NodeList
		},

		adopt: function(/*String||Array||DomNode*/ queryOrListOrNode, /*String?*/ position){
			//	summary:
			//		places any/all elements in queryOrListOrNode at a
			//		position relative to the first element in this list.
			//		Returns a dojo.NodeList of the adopted elements.
			//	queryOrListOrNode:
			//		a DOM node or a query string or a query result.
			//		Represents the nodes to be adopted relative to the
			//		first element of this NodeList.
			//	position:
			//		can be one of:
			//			* "last"||"end" (default)
			//			* "first||"start"
			//			* "before"
			//			* "after"
			// 		or an offset in the childNodes property
			var item = this[0];
			return d.query(queryOrListOrNode).forEach(function(ai){ d.place(ai, item, position || "last"); }); // dojo.NodeList
		},

		// FIXME: do we need this?
		query: function(/*String*/ queryStr){
			//	summary:
			//		Returns a new, flattened NodeList. Elements of the new list
			//		satisfy the passed query but use elements of the
			//		current NodeList as query roots.

			if(!queryStr){ return this; }

			// FIXME: probably slow
			// FIXME: use map?
			var ret = d.NodeList();
			this.forEach(function(item){
				d.query(queryStr, item).forEach(function(subItem){
					if(subItem !== undefined){
						ret.push(subItem);
					}
				});
			});
			return ret; // dojo.NodeList
		},

		filter: function(/*String*/ simpleQuery){
			//	summary:
			// 		"masks" the built-in javascript filter() method to support
			//		passing a simple string filter in addition to supporting
			//		filtering function objects.
			//	example:
			//		"regular" JS filter syntax as exposed in dojo.filter:
			//		|	dojo.query("*").filter(function(item){
			//		|		// highlight every paragraph
			//		|		return (item.nodeName == "p");
			//		|	}).styles("backgroundColor", "yellow");
			// example:
			//		the same filtering using a CSS selector
			//		|	dojo.query("*").filter("p").styles("backgroundColor", "yellow");

			var items = this;
			var _a = arguments;
			var r = d.NodeList();
			var rp = function(t){
				if(t !== undefined){
					r.push(t);
				}
			}
			if(d.isString(simpleQuery)){
				items = d._filterQueryResult(this, _a[0]);
				if(_a.length == 1){
					// if we only got a string query, pass back the filtered results
					return items; // dojo.NodeList
				}
				// if we got a callback, run it over the filtered items
				_a.shift();
			}
			// handle the (callback, [thisObject]) case
			d.forEach(d.filter(items, _a[0], _a[1]), rp);
			return r; // dojo.NodeList
		},

		/*
		// FIXME: should this be "copyTo" and include parenting info?
		clone: function(){
			// summary:
			//		creates node clones of each element of this list
			//		and returns a new list containing the clones
		},
		*/

		addContent: function(/*String*/ content, /*String||Integer?*/ position){
			//	summary:
			//		add a node or some HTML as a string to every item in the list.
			//		Returns the original list.
			//	description:
			//		a copy of the HTML content is added to each item in the
			//		list, with an optional position argument. If no position
			//		argument is provided, the content is appended to the end of
			//		each item.
			//	content:
			//		the HTML in string format to add at position to every item
			//	position:
			//		can be one of:
			//			* "last"||"end" (default)
			//			* "first||"start"
			//			* "before"
			//			* "after"
			// 		or an offset in the childNodes property
			//	example:
			//		appends content to the end if the position is ommitted
			//	|	dojo.query("h3 > p").addContent("hey there!");
			//	example:
			//		add something to the front of each element that has a "thinger" property:
			//	|	dojo.query("[thinger]").addContent("...", "first");
			//	example:
			//		adds a header before each element of the list
			//	|	dojo.query(".note").addContent("<h4>NOTE:</h4>", "before");
			var ta = d.doc.createElement("span");
			if(d.isString(content)){
				ta.innerHTML = content;
			}else{
				ta.appendChild(content);
			}
			if(position === undefined){
				position = "last";
			}
			var ct = (position == "first" || position == "after") ? "lastChild" : "firstChild";
			this.forEach(function(item){
				var tn = ta.cloneNode(true);
				while(tn[ct]){
					d.place(tn[ct], item, position);
				}
			});
			return this; // dojo.NodeList
		},

		empty: function(){
			//	summary:
			//		clears all content from each node in the list
			return this.forEach("item.innerHTML='';"); // dojo.NodeList

			// FIXME: should we be checking for and/or disposing of widgets below these nodes?
		},

		instantiate: function(/*String|Object*/ declaredClass, /*Object?*/ properties){
			//	summary:
			//		Create a new instance of a specified class, using the
			//		specified properties and each node in the nodeList as a
			//		srcNodeRef
			//
			var c = d.isFunction(declaredClass) ? declaredClass : d.getObject(declaredClass);
			return this.forEach(function(i){
				new c(properties||{},i);
			}) // dojo.NodeList
		}

	});

	// syntactic sugar for DOM events
	d.forEach([
		"blur", "focus", "click", "keydown", "keypress", "keyup", "mousedown",
		"mouseenter", "mouseleave", "mousemove", "mouseout", "mouseover",
		"mouseup"
		], function(evt){
			var _oe = "on"+evt;
			dojo.NodeList.prototype[_oe] = function(a, b){
				return this.connect(_oe, a, b);
			}
				// FIXME: should these events trigger publishes?
				/*
				return (a ? this.connect(_oe, a, b) :
							this.forEach(function(n){
								// FIXME:
								//		listeners get buried by
								//		addEventListener and can't be dug back
								//		out to be triggered externally.
								// see:
								//		http://developer.mozilla.org/en/docs/DOM:element

								console.debug(n, evt, _oe);

								// FIXME: need synthetic event support!
								var _e = { target: n, faux: true, type: evt };
								// dojo._event_listener._synthesizeEvent({}, { target: n, faux: true, type: evt });
								try{ n[evt](_e); }catch(e){ console.debug(e); }
								try{ n[_oe](_e); }catch(e){ console.debug(e); }
							})
				);
			}
			*/
		}
	);

})();

}

if(!dojo._hasResource["dojo._base.query"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.query"] = true;
dojo.provide("dojo._base.query");

/*
	dojo.query() architectural overview:

		dojo.query is a relatively full-featured CSS3 query library. It is
		designed to take any valid CSS3 selector and return the nodes matching
		the selector. To do this quickly, it processes queries in several
		steps, applying caching where profitable.

		The steps (roughly in reverse order of the way they appear in the code):
			1.) check to see if we already have a "query dispatcher"
				- if so, use that with the given parameterization. Skip to step 4.
			2.) attempt to determine which branch to dispatch the query to:
				- JS (optimized DOM iteration)
				- xpath (for browsers that support it and where it's fast)
				- native (not available in any browser yet)
			3.) tokenize and convert to executable "query dispatcher"
				- this is where the lion's share of the complexity in the
				  system lies. In the DOM version, the query dispatcher is
				  assembled as a chain of "yes/no" test functions pertaining to
				  a section of a simple query statement (".blah:nth-child(odd)"
				  but not "div div", which is 2 simple statements). Individual
				  statement dispatchers are cached (to prevent re-definition)
				  as are entire dispatch chains (to make re-execution of the
				  same query fast)
				- in the xpath path, tokenization yeilds a concatenation of
				  parameterized xpath selectors. As with the DOM version, both
				  simple selector blocks and overall evaluators are cached to
				  prevent re-defintion
			4.) the resulting query dispatcher is called in the passed scope (by default the top-level document)
				- for DOM queries, this results in a recursive, top-down
				  evaluation of nodes based on each simple query section
				- xpath queries can, thankfully, be executed in one shot
			5.) matched nodes are pruned to ensure they are unique
*/

;(function(){
	// define everything in a closure for compressability reasons. "d" is an
	// alias to "dojo" since it's so frequently used. This seems a
	// transformation that the build system could perform on a per-file basis.

	////////////////////////////////////////////////////////////////////////
	// Utility code
	////////////////////////////////////////////////////////////////////////

	var d = dojo;
	var childNodesName = dojo.isIE ? "children" : "childNodes";
	var caseSensitive = false;

	var getQueryParts = function(query){
		// summary: state machine for query tokenization
		if(">~+".indexOf(query.charAt(query.length-1)) >= 0){
			query += " *"
		}
		query += " "; // ensure that we terminate the state machine

		var ts = function(s, e){
			return d.trim(query.slice(s, e));
		}

		// the overall data graph of the full query, as represented by queryPart objects
		var qparts = [];
		// state keeping vars
		var inBrackets = -1;
		var inParens = -1;
		var inMatchFor = -1;
		var inPseudo = -1;
		var inClass = -1;
		var inId = -1;
		var inTag = -1;
		var lc = ""; // the last character
		var cc = ""; // the current character
		var pStart;
		// iteration vars
		var x = 0; // index in the query
		var ql = query.length;
		var currentPart = null; // data structure representing the entire clause
		var _cp = null; // the current pseudo or attr matcher

		var endTag = function(){
			if(inTag >= 0){
				var tv = (inTag == x) ? null : ts(inTag, x).toLowerCase();
				currentPart[ (">~+".indexOf(tv) < 0) ? "tag" : "oper" ] = tv;
				inTag = -1;
			}
		}

		var endId = function(){
			if(inId >= 0){
				currentPart.id = ts(inId, x).replace(/\\/g, "");
				inId = -1;
			}
		}

		var endClass = function(){
			if(inClass >= 0){
				currentPart.classes.push(ts(inClass+1, x).replace(/\\/g, ""));
				inClass = -1;
			}
		}

		var endAll = function(){
			endId(); endTag(); endClass();
		}

		for(; lc=cc, cc=query.charAt(x),x<ql; x++){
			if(lc == "\\"){ continue; }
			if(!currentPart){
				// NOTE: I hate all this alloc, but it's shorter than writing tons of if's
				pStart = x;
				currentPart = {
					query: null,
					pseudos: [],
					attrs: [],
					classes: [],
					tag: null,
					oper: null,
					id: null
				};
				inTag = x;
			}

			if(inBrackets >= 0){
				// look for a the close first
				if(cc == "]"){
					if(!_cp.attr){
						_cp.attr = ts(inBrackets+1, x);
					}else{
						_cp.matchFor = ts((inMatchFor||inBrackets+1), x);
					}
					var cmf = _cp.matchFor;
					if(cmf){
						if(	(cmf.charAt(0) == '"') || (cmf.charAt(0)  == "'") ){
							_cp.matchFor = cmf.substring(1, cmf.length-1);
						}
					}
					currentPart.attrs.push(_cp);
					_cp = null; // necessaray?
					inBrackets = inMatchFor = -1;
				}else if(cc == "="){
					var addToCc = ("|~^$*".indexOf(lc) >=0 ) ? lc : "";
					_cp.type = addToCc+cc;
					_cp.attr = ts(inBrackets+1, x-addToCc.length);
					inMatchFor = x+1;
				}
				// now look for other clause parts
			}else if(inParens >= 0){
				if(cc == ")"){
					if(inPseudo >= 0){
						_cp.value = ts(inParens+1, x);
					}
					inPseudo = inParens = -1;
				}
			}else if(cc == "#"){
				endAll();
				inId = x+1;
			}else if(cc == "."){
				endAll();
				inClass = x;
			}else if(cc == ":"){
				endAll();
				inPseudo = x;
			}else if(cc == "["){
				endAll();
				inBrackets = x;
				_cp = {
					/*=====
					attr: null, type: null, matchFor: null
					=====*/
				};
			}else if(cc == "("){
				if(inPseudo >= 0){
					_cp = {
						name: ts(inPseudo+1, x),
						value: null
					}
					currentPart.pseudos.push(_cp);
				}
				inParens = x;
			}else if(cc == " " && lc != cc){
				// note that we expect the string to be " " terminated
				endAll();
				if(inPseudo >= 0){
					currentPart.pseudos.push({ name: ts(inPseudo+1, x) });
				}
				currentPart.hasLoops = (
						currentPart.pseudos.length ||
						currentPart.attrs.length ||
						currentPart.classes.length	);
				currentPart.query = ts(pStart, x);
				currentPart.tag = (currentPart["oper"]) ? null : (currentPart.tag || "*");
				qparts.push(currentPart);
				currentPart = null;
			}
		}
		return qparts;
	};

	////////////////////////////////////////////////////////////////////////
	// XPath query code
	////////////////////////////////////////////////////////////////////////

	// this array is a lookup used to generate an attribute matching function.
	// There is a similar lookup/generator list for the DOM branch with similar
	// calling semantics.
	var xPathAttrs = {
		"*=": function(attr, value){
			return "[contains(@"+attr+", '"+ value +"')]";
		},
		"^=": function(attr, value){
			return "[starts-with(@"+attr+", '"+ value +"')]";
		},
		"$=": function(attr, value){
			return "[substring(@"+attr+", string-length(@"+attr+")-"+(value.length-1)+")='"+value+"']";
		},
		"~=": function(attr, value){
			return "[contains(concat(' ',@"+attr+",' '), ' "+ value +" ')]";
		},
		"|=": function(attr, value){
			return "[contains(concat(' ',@"+attr+",' '), ' "+ value +"-')]";
		},
		"=": function(attr, value){
			return "[@"+attr+"='"+ value +"']";
		}
	};

	// takes a list of attribute searches, the overall query, a function to
	// generate a default matcher, and a closure-bound method for providing a
	// matching function that generates whatever type of yes/no distinguisher
	// the query method needs. The method is a bit tortured and hard to read
	// because it needs to be used in both the XPath and DOM branches.
	var handleAttrs = function(	attrList,
								query,
								getDefault,
								handleMatch){
		d.forEach(query.attrs, function(attr){
			var matcher;
			// type, attr, matchFor
			if(attr.type && attrList[attr.type]){
				matcher = attrList[attr.type](attr.attr, attr.matchFor);
			}else if(attr.attr.length){
				matcher = getDefault(attr.attr);
			}
			if(matcher){ handleMatch(matcher); }
		});
	}

	var buildPath = function(query){
		var xpath = ".";
		var qparts = getQueryParts(d.trim(query));
		while(qparts.length){
			var tqp = qparts.shift();
			var prefix;
			var postfix = "";
			if(tqp.oper == ">"){
				prefix = "/";
				// prefix = "/child::*";
				tqp = qparts.shift();
			}else if(tqp.oper == "~"){
				prefix = "/following-sibling::"; // get element following siblings
				tqp = qparts.shift();
			}else if(tqp.oper == "+"){
				// FIXME:
				//		fails when selecting subsequent siblings by node type
				//		because the position() checks the position in the list
				//		of matching elements and not the localized siblings
				prefix = "/following-sibling::";
				postfix = "[position()=1]";
				tqp = qparts.shift();
			}else{
				prefix = "//";
				// prefix = "/descendant::*"
			}

			// get the tag name (if any)

			xpath += prefix + tqp.tag + postfix;

			// check to see if it's got an id. Needs to come first in xpath.
			if(tqp.id){
				xpath += "[@id='"+tqp.id+"'][1]";
			}

			d.forEach(tqp.classes, function(cn){
				var cnl = cn.length;
				var padding = " ";
				if(cn.charAt(cnl-1) == "*"){
					padding = ""; cn = cn.substr(0, cnl-1);
				}
				xpath +=
					"[contains(concat(' ',@class,' '), ' "+
					cn + padding + "')]";
			});

			handleAttrs(xPathAttrs, tqp,
				function(condition){
						return "[@"+condition+"]";
				},
				function(matcher){
					xpath += matcher;
				}
			);

			// FIXME: need to implement pseudo-class checks!!
		};
		return xpath;
	};

	var _xpathFuncCache = {};
	var getXPathFunc = function(path){
		if(_xpathFuncCache[path]){
			return _xpathFuncCache[path];
		}

		var doc = d.doc;
		// don't need to memoize. The closure scope handles it for us.
		var xpath = buildPath(path);

		var tf = function(parent){
			// XPath query strings are memoized.
			var ret = [];
			var xpathResult;
			try{
				xpathResult = doc.evaluate(xpath, parent, null,
												// XPathResult.UNORDERED_NODE_ITERATOR_TYPE, null);
												XPathResult.ANY_TYPE, null);
			}catch(e){
				console.debug("failure in exprssion:", xpath, "under:", parent);
				console.debug(e);
			}
			var result = xpathResult.iterateNext();
			while(result){
				ret.push(result);
				result = xpathResult.iterateNext();
			}
			return ret;
		}
		return _xpathFuncCache[path] = tf;
	};

	/*
	d.xPathMatch = function(query){
		// XPath based DOM query system. Handles a small subset of CSS
		// selectors, subset is identical to the non-XPath version of this
		// function.

		return getXPathFunc(query)();
	}
	*/

	////////////////////////////////////////////////////////////////////////
	// DOM query code
	////////////////////////////////////////////////////////////////////////

	var _filtersCache = {};
	var _simpleFiltersCache = {};

	// the basic building block of the yes/no chaining system. agree(f1, f2)
	// generates a new function which returns the boolean results of both of
	// the passed functions to a single logical-anded result.
	var agree = function(first, second){
		if(!first){ return second; }
		if(!second){ return first; }

		return function(){
			return first.apply(window, arguments) && second.apply(window, arguments);
		}
	}

	var _childElements = function(root){
		var ret = [];
		var te, x=0, tret = root[childNodesName];
		while(te=tret[x++]){
			if(te.nodeType == 1){ ret.push(te); }
		}
		return ret;
	}

	var _nextSiblings = function(root, single){
		var ret = [];
		var te = root;
		while(te = te.nextSibling){
			if(te.nodeType == 1){
				ret.push(te);
				if(single){ break; }
			}
		}
		return ret;
	}

	var _filterDown = function(element, queryParts, matchArr, idx){
		// NOTE:
		//		in the fast path! this function is called recursively and for
		//		every run of a query.
		var nidx = idx+1;
		var isFinal = (queryParts.length == nidx);
		var tqp = queryParts[idx];

		// see if we can constrain our next level to direct children
		if(tqp.oper){
			var ecn = (tqp.oper == ">") ?
				_childElements(element) :
				_nextSiblings(element, (tqp.oper == "+"));

			if(!ecn || !ecn.length){
				return;
			}
			nidx++;
			isFinal = (queryParts.length == nidx);
			// kinda janky, too much array alloc
			var tf = getFilterFunc(queryParts[idx+1]);
			// for(var x=ecn.length-1, te; x>=0, te=ecn[x]; x--){
			for(var x=0, ecnl=ecn.length, te; x<ecnl, te=ecn[x]; x++){
				if(tf(te)){
					if(isFinal){
						matchArr.push(te);
					}else{
						_filterDown(te, queryParts, matchArr, nidx);
					}
				}
				/*
				if(x==0){
					break;
				}
				*/
			}
		}

		// otherwise, keep going down, unless we'er at the end
		var candidates = getElementsFunc(tqp)(element);
		if(isFinal){
			while(candidates.length){
				matchArr.push(candidates.shift());
			}
			/*
			candidates.unshift(0, matchArr.length-1);
			matchArr.splice.apply(matchArr, candidates);
			*/
		}else{
			// if we're not yet at the bottom, keep going!
			while(candidates.length){
				_filterDown(candidates.shift(), queryParts, matchArr, nidx);
			}
		}
	}

	var filterDown = function(elements, queryParts){
		var ret = [];

		// for every root, get the elements that match the descendant selector
		// for(var x=elements.length-1, te; x>=0, te=elements[x]; x--){
		var x = elements.length - 1, te;
		while(te = elements[x--]){
			_filterDown(te, queryParts, ret, 0);
		}
		return ret;
	}

	var getFilterFunc = function(q){
		// note: query can't have spaces!
		if(_filtersCache[q.query]){
			return _filtersCache[q.query];
		}
		var ff = null;

		// does it have a tagName component?
		if(q.tag){
			if(q.tag == "*"){
				ff = agree(ff,
					function(elem){
						return (elem.nodeType == 1);
					}
				);
			}else{
				// tag name match
				ff = agree(ff,
					function(elem){
						return (
							(elem.nodeType == 1) &&
							(q.tag == elem.tagName.toLowerCase())
						);
						// return isTn;
					}
				);
			}
		}

		// does the node have an ID?
		if(q.id){
			ff = agree(ff,
				function(elem){
					return (
						(elem.nodeType == 1) &&
						(elem.id == q.id)
					);
				}
			);
		}

		if(q.hasLoops){
			// if we have other query param parts, make sure we add them to the
			// filter chain
			ff = agree(ff, getSimpleFilterFunc(q));
		}

		return _filtersCache[q.query] = ff;
	}

	var getNodeIndex = function(node){
		// NOTE:
		//		we could have a more accurate caching mechanism by invalidating
		//		caches after the query has finished, but I think that'd lead to
		//		significantly more cache churn than the cache would provide
		//		value for in the common case. Generally, we're more
		//		conservative (and therefore, more accurate) than jQuery and
		//		DomQuery WRT node node indexes, but there may be corner cases
		//		in which we fall down.  How much we care about them is TBD.

		var pn = node.parentNode;
		var pnc = pn.childNodes;

		// check to see if we can trust the cache. If not, re-key the whole
		// thing and return our node match from that.

		var nidx = -1;
		var child = pn.firstChild;
		if(!child){
			return nidx;
		}

		var ci = node["__cachedIndex"];
		var cl = pn["__cachedLength"];

		// only handle cache building if we've gone out of sync
		if(((typeof cl == "number")&&(cl != pnc.length))||(typeof ci != "number")){
			// rip though the whole set, building cache indexes as we go
			pn["__cachedLength"] = pnc.length;
			var idx = 1;
			do{
				// we only assign indexes for nodes with nodeType == 1, as per:
				//		http://www.w3.org/TR/css3-selectors/#nth-child-pseudo
				// only elements are counted in the search order, and they
				// begin at 1 for the first child's index

				if(child === node){
					nidx = idx;
				}
				if(child.nodeType == 1){
					child["__cachedIndex"] = idx;
					idx++;
				}
				child = child.nextSibling;
			}while(child);
		}else{
			// NOTE:
			//		could be incorrect in some cases (node swaps involving the
			//		passed node, etc.), but we ignore those due to the relative
			//		unlikelihood of that occuring
			nidx = ci;
		}
		return nidx;
	}

	var firedCount = 0;

	var blank = "";
	var _getAttr = function(elem, attr){
		if(attr == "class"){
			return elem.className || blank;
		}
		if(attr == "for"){
			return elem.htmlFor || blank;
		}
		return elem.getAttribute(attr, 2) || blank;
	}

	var attrs = {
		"*=": function(attr, value){
			return function(elem){
				// E[foo*="bar"]
				//		an E element whose "foo" attribute value contains
				//		the substring "bar"
				return (_getAttr(elem, attr).indexOf(value)>=0);
			}
		},
		"^=": function(attr, value){
			// E[foo^="bar"]
			//		an E element whose "foo" attribute value begins exactly
			//		with the string "bar"
			return function(elem){
				return (_getAttr(elem, attr).indexOf(value)==0);
			}
		},
		"$=": function(attr, value){
			// E[foo$="bar"]
			//		an E element whose "foo" attribute value ends exactly
			//		with the string "bar"
			var tval = " "+value;
			return function(elem){
				var ea = " "+_getAttr(elem, attr);
				return (ea.lastIndexOf(value)==(ea.length-value.length));
			}
		},
		"~=": function(attr, value){
			// E[foo~="bar"]
			//		an E element whose "foo" attribute value is a list of
			//		space-separated values, one of which is exactly equal
			//		to "bar"

			// return "[contains(concat(' ',@"+attr+",' '), ' "+ value +" ')]";
			var tval = " "+value+" ";
			return function(elem){
				var ea = " "+_getAttr(elem, attr)+" ";
				return (ea.indexOf(tval)>=0);
			}
		},
		"|=": function(attr, value){
			// E[hreflang|="en"]
			//		an E element whose "hreflang" attribute has a
			//		hyphen-separated list of values beginning (from the
			//		left) with "en"
			var valueDash = " "+value+"-";
			return function(elem){
				var ea = " "+(elem.getAttribute(attr, 2) || "");
				return (
					(ea == value) ||
					(ea.indexOf(valueDash)==0)
				);
			}
		},
		"=": function(attr, value){
			return function(elem){
				return (_getAttr(elem, attr) == value);
			}
		}
	};

	var pseudos = {
		"first-child": function(name, condition){
			return function(elem){
				if(elem.nodeType != 1){ return false; }
				// check to see if any of the previous siblings are elements
				var fc = elem.previousSibling;
				while(fc && (fc.nodeType != 1)){
					fc = fc.previousSibling;
				}
				return (!fc);
			}
		},
		"last-child": function(name, condition){
			return function(elem){
				if(elem.nodeType != 1){ return false; }
				// check to see if any of the next siblings are elements
				var nc = elem.nextSibling;
				while(nc && (nc.nodeType != 1)){
					nc = nc.nextSibling;
				}
				return (!nc);
			}
		},
		"empty": function(name, condition){
			return function(elem){
				// DomQuery and jQuery get this wrong, oddly enough.
				// The CSS 3 selectors spec is pretty explicit about
				// it, too.
				var cn = elem.childNodes;
				var cnl = elem.childNodes.length;
				// if(!cnl){ return true; }
				for(var x=cnl-1; x >= 0; x--){
					var nt = cn[x].nodeType;
					if((nt == 1)||(nt == 3)){ return false; }
				}
				return true;
			}
		},
		"contains": function(name, condition){
			return function(elem){
				// FIXME: I dislike this version of "contains", as
				// whimsical attribute could set it off. An inner-text
				// based version might be more accurate, but since
				// jQuery and DomQuery also potentially get this wrong,
				// I'm leaving it for now.
				return (elem.innerHTML.indexOf(condition) >= 0);
			}
		},
		"not": function(name, condition){
			var ntf = getFilterFunc(getQueryParts(condition)[0]);
			return function(elem){
				return (!ntf(elem));
			}
		},
		"nth-child": function(name, condition){
			var pi = parseInt;
			if(condition == "odd"){
				return function(elem){
					return (
						((getNodeIndex(elem)) % 2) == 1
					);
				}
			}else if((condition == "2n")||
				(condition == "even")){
				return function(elem){
					return ((getNodeIndex(elem) % 2) == 0);
				}
			}else if(condition.indexOf("0n+") == 0){
				var ncount = pi(condition.substr(3));
				return function(elem){
					return (elem.parentNode[childNodesName][ncount-1] === elem);
				}
			}else if(	(condition.indexOf("n+") > 0) &&
						(condition.length > 3) ){
				var tparts = condition.split("n+", 2);
				var pred = pi(tparts[0]);
				var idx = pi(tparts[1]);
				return function(elem){
					return ((getNodeIndex(elem) % pred) == idx);
				}
			}else if(condition.indexOf("n") == -1){
				var ncount = pi(condition);
				return function(elem){
					return (getNodeIndex(elem) == ncount);
				}
			}
		}
	};

	var defaultGetter = (d.isIE) ? function(cond){
		var clc = cond.toLowerCase();
		return function(elem){
			return elem[cond]||elem[clc];
		}
	} : function(cond){
		return function(elem){
			return (elem && elem.getAttribute && elem.hasAttribute(cond));
		}
	};

	var getSimpleFilterFunc = function(query){

		var fcHit = (_simpleFiltersCache[query.query]||_filtersCache[query.query]);
		if(fcHit){ return fcHit; }

		var ff = null;

		// the only case where we'll need the tag name is if we came from an ID query
		if(query.id){ // do we have an ID component?
			if(query.tag != "*"){
				ff = agree(ff, function(elem){
					return (elem.tagName.toLowerCase() == query.tag);
				});
			}
		}

		// if there's a class in our query, generate a match function for it
		d.forEach(query.classes, function(cname, idx, arr){
			// get the class name
			var isWildcard = cname.charAt(cname.length-1) == "*";
			if(isWildcard){
				cname = cname.substr(0, cname.length-1);
			}
			// I dislike the regex thing, even if memozied in a cache, but it's VERY short
			var re = new RegExp("(?:^|\\s)" + cname + (isWildcard ? ".*" : "") + "(?:\\s|$)");
			ff = agree(ff, function(elem){
				return re.test(elem.className);
			});
			ff.count = idx;
		});

		d.forEach(query.pseudos, function(pseudo){
			if(pseudos[pseudo.name]){
				ff = agree(ff, pseudos[pseudo.name](pseudo.name, pseudo.value));
			}
		});

		handleAttrs(attrs, query, defaultGetter,
			function(tmatcher){ ff = agree(ff, tmatcher); }
		);
		if(!ff){
			ff = function(){ return true; };
		}
		return _simpleFiltersCache[query.query] = ff;
	}

	var _getElementsFuncCache = { };

	var getElementsFunc = function(query, root){
		var fHit = _getElementsFuncCache[query.query];
		if(fHit){ return fHit; }

		// NOTE: this function is in the fast path! not memoized!!!

		// the query doesn't contain any spaces, so there's only so many
		// things it could be

		if(query.id && !query.hasLoops && !query.tag){
			// ID-only query. Easy.
			return _getElementsFuncCache[query.query] = function(root){
				// FIXME: if root != document, check for parenting!
				return [ d.byId(query.id) ];
			}
		}

		var filterFunc = getSimpleFilterFunc(query);

		var retFunc;
		if(query.tag && query.id && !query.hasLoops){
			// we got a filtered ID search (e.g., "h4#thinger")
			retFunc = function(root){
				var te = d.byId(query.id);
				if(filterFunc(te)){
					return [ te ];
				}
			}
		}else{
			var tret;

			if(!query.hasLoops){
				// it's just a plain-ol elements-by-tag-name query from the root
				retFunc = function(root){
					var ret = [];
					var te, x=0, tret = root.getElementsByTagName(query.tag);
					while(te=tret[x++]){
						ret.push(te);
					}
					return ret;
				}
			}else{
				retFunc = function(root){
					var ret = [];
					var te, x=0, tret = root.getElementsByTagName(query.tag);
					while(te=tret[x++]){
						if(filterFunc(te)){
							ret.push(te);
						}
					}
					return ret;
				}
			}
		}
		return _getElementsFuncCache[query.query] = retFunc;
	}

	var _partsCache = {};

	////////////////////////////////////////////////////////////////////////
	// the query runner
	////////////////////////////////////////////////////////////////////////

	// this is the second level of spliting, from full-length queries (e.g.,
	// "div.foo .bar") into simple query expressions (e.g., ["div.foo",
	// ".bar"])
	var _queryFuncCache = {
		"*": d.isIE ?
			function(root){
					return root.all;
			} :
			function(root){
				 return root.getElementsByTagName("*");
			},
		"~": _nextSiblings,
		"+": function(root){ return _nextSiblings(root, true); },
		">": _childElements
	};

	var getStepQueryFunc = function(query){
		// if it's trivial, get a fast-path dispatcher
		var qparts = getQueryParts(d.trim(query));
		// if(query[query.length-1] == ">"){ query += " *"; }
		if(qparts.length == 1){
			var tt = getElementsFunc(qparts[0]);
			tt.nozip = true;
			return tt;
		}

		// otherwise, break it up and return a runner that iterates over the parts recursively
		var sqf = function(root){
			var localQueryParts = qparts.slice(0); // clone the src arr
			var candidates;
			if(localQueryParts[0].oper == ">"){ // FIXME: what if it's + or ~?
				candidates = [ root ];
				// root = document;
			}else{
				candidates = getElementsFunc(localQueryParts.shift())(root);
			}
			return filterDown(candidates, localQueryParts);
		}
		return sqf;
	}

	// a specialized method that implements our primoridal "query optimizer".
	// This allows us to dispatch queries to the fastest subsystem we can get.
	var _getQueryFunc = (
		// NOTE:
		//		XPath on the Webkit nighlies is slower than it's DOM iteration
		//		for most test cases
		// FIXME:
		//		we should try to capture some runtime speed data for each query
		//		function to determine on the fly if we should stick w/ the
		//		potentially optimized variant or if we should try something
		//		new.
		(document["evaluate"] && !d.isSafari) ?
		function(query){
			// has xpath support that's faster than DOM
			var qparts = query.split(" ");
			// can we handle it?
			if(	(document["evaluate"])&&
				(query.indexOf(":") == -1)&&
				(query.indexOf("+") == -1) // skip direct sibling matches. See line ~344
			){
				// dojo.debug(query);
				// should we handle it?

				// kind of a lame heuristic, but it works
				if(
					// a "div div div" style query
					((qparts.length > 2)&&(query.indexOf(">") == -1))||
					// or something else with moderate complexity. kinda janky
					(qparts.length > 3)||
					(query.indexOf("[")>=0)||
					// or if it's a ".thinger" query
					((1 == qparts.length)&&(0 <= query.indexOf(".")))

				){
					// use get and cache a xpath runner for this selector
					return getXPathFunc(query);
				}
			}

			// fallthrough
			return getStepQueryFunc(query);
		} : getStepQueryFunc
	);
	// uncomment to disable XPath for testing and tuning the DOM path
	// _getQueryFunc = getStepQueryFunc;

	// FIXME: we've got problems w/ the NodeList query()/filter() functions if we go XPath for everything

	// uncomment to disable DOM queries for testing and tuning XPath
	// _getQueryFunc = getXPathFunc;

	// this is the primary caching for full-query results. The query dispatcher
	// functions are generated here and then pickled for hash lookup in the
	// future
	var getQueryFunc = function(query){
		// return a cached version if one is available
		if(_queryFuncCache[query]){ return _queryFuncCache[query]; }
		if(0 > query.indexOf(",")){
			// if it's not a compound query (e.g., ".foo, .bar"), cache and return a dispatcher
			return _queryFuncCache[query] = _getQueryFunc(query);
		}else{
			// if it's a complex query, break it up into it's constituent parts
			// and return a dispatcher that will merge the parts when run

			// var parts = query.split(", ");
			var parts = query.split(/\s*,\s*/);
			var tf = function(root){
				var pindex = 0; // avoid array alloc for every invocation
				var ret = [];
				var tp;
				while(tp = parts[pindex++]){
					ret = ret.concat(_getQueryFunc(tp, tp.indexOf(" "))(root));
				}
				return ret;
			}
			// ...cache and return
			return _queryFuncCache[query] = tf;
		}
	}

	// FIXME:
	//		Dean's Base2 uses a system whereby queries themselves note if
	//		they'll need duplicate filtering. We need to get on that plan!!

	// attempt to efficiently determine if an item in a list is a dupe,
	// returning a list of "uniques", hopefully in doucment order
	var _zipIdx = 0;
	var _zip = function(arr){
		if(arr && arr.nozip){ return d.NodeList._wrap(arr); }
		var ret = new d.NodeList();
		if(!arr){ return ret; }
		if(arr[0]){
			ret.push(arr[0]);
		}
		if(arr.length < 2){ return ret; }
		_zipIdx++;
		arr[0]["_zipIdx"] = _zipIdx;
		for(var x=1, te; te = arr[x]; x++){
			if(arr[x]["_zipIdx"] != _zipIdx){
				ret.push(te);
			}
			te["_zipIdx"] = _zipIdx;
		}
		// FIXME: should we consider stripping these properties?
		return ret;
	}

	// the main executor
	d.query = function(/*String*/ query, /*String|DOMNode?*/ root){
		//	summary:
		//		Returns nodes which match the given CSS3 selector, searching the
		//		entire document by default but optionally taking a node to scope
		//		the search by. Returns an instance of dojo.NodeList.
		//	description:
		//		dojo.query() is the swiss army knife of DOM node manipulation in
		//		Dojo. Much like Prototype's "$$" (bling-bling) function or JQuery's
		//		"$" function, dojo.query provides robust, high-performance
		//		CSS-based node selector support with the option of scoping searches
		//		to a particular sub-tree of a document.
		//
		//		Supported Selectors:
		//		--------------------
		//
		//		dojo.query() supports a rich set of CSS3 selectors, including:
		//
		//			* class selectors (e.g., `.foo`)
		//			* node type selectors like `span`
		//			* ` ` descendant selectors
		//			* `>` child element selectors
		//			* `#foo` style ID selectors
		//			* `*` universal selector
		//			* `~`, the immediately preceeded-by sibling selector
		//			* `+`, the preceeded-by sibling selector
		//			* attribute queries:
		//			|	* `[foo]` attribute presence selector
		//			|	* `[foo='bar']` attribute value exact match
		//			|	* `[foo~='bar']` attribute value list item match
		//			|	* `[foo^='bar']` attribute start match
		//			|	* `[foo$='bar']` attribute end match
		//			|	* `[foo*='bar']` attribute substring match
		//			* `:first-child`, `:last-child` positional selectors
		//			* `:empty` content emtpy selector
		//			* `:empty` content emtpy selector
		//			* `:nth-child(n)`, `:nth-child(2n+1)` style positional calculations
		//			* `:nth-child(even)`, `:nth-child(odd)` positional selectors
		//			* `:not(...)` negation pseudo selectors
		//
		//		Any legal combination of these selectors will work with
		//		`dojo.query()`, including compound selectors ("," delimited).
		//		Very complex and useful searches can be constructed with this
		//		palette of selectors and when combined with functions for
		//		maniplation presented by dojo.NodeList, many types of DOM
		//		manipulation operations become very straightforward.
		//
		//		Unsupported Selectors:
		//		----------------------
		//
		//		While dojo.query handles many CSS3 selectors, some fall outside of
		//		what's resaonable for a programmatic node querying engine to
		//		handle. Currently unsupported selectors include:
		//
		//			* namespace-differentiated selectors of any form
		//			* all `::` pseduo-element selectors
		//			* certain pseduo-selectors which don't get a lot of day-to-day use:
		//			|	* `:root`, `:lang()`, `:target`, `:focus`
		//			* all visual and state selectors:
		//			|	* `:root`, `:active`, `:hover`, `:visisted`, `:link`,
		//				  `:enabled`, `:disabled`, `:checked`
		//			* `:*-of-type` pseudo selectors
		//
		//		dojo.query and XML Documents:
		//		-----------------------------
		//
		//		`dojo.query` currently only supports searching XML documents
		//		whose tags and attributes are 100% lower-case. This is a known
		//		limitation and will [be addressed soon](http://trac.dojotoolkit.org/ticket/3866)
		//		Non-selector Queries:
		//		---------------------
		//
		//		If something other than a String is passed for the query,
		//		`dojo.query` will return a new `dojo.NodeList` constructed from
		//		that parameter alone and all further processing will stop. This
		//		means that if you have a reference to a node or NodeList, you
		//		can quickly construct a new NodeList from the original by
		//		calling `dojo.query(node)` or `dojo.query(list)`.
		//
		//	query:
		//		The CSS3 expression to match against. For details on the syntax of
		//		CSS3 selectors, see <http://www.w3.org/TR/css3-selectors/#selectors>
		//	root:
		//		A DOMNode (or node id) to scope the search from. Optional.
		//	returns: dojo.NodeList
		//		An instance of `dojo.NodeList`. Many methods are available on
		//		NodeLists for searching, iterating, manipulating, and handling
		//		events on the matched nodes in the returned list.
		//	example:
		//		search the entire document for elements with the class "foo":
		//	|	dojo.query(".foo");
		//		these elements will match:
		//	|	<span class="foo"></span>
		//	|	<span class="foo bar"></span>
		//	|	<p class="thud foo"></p>
		//	example:
		//		search the entire document for elements with the classes "foo" *and* "bar":
		//	|	dojo.query(".foo.bar");
		//		these elements will match:
		//	|	<span class="foo bar"></span>
		//		while these will not:
		//	|	<span class="foo"></span>
		//	|	<p class="thud foo"></p>
		//	example:
		//		find `<span>` elements which are descendants of paragraphs and
		//		which have a "highlighted" class:
		//	|	dojo.query("p span.highlighted");
		//		the innermost span in this fragment matches:
		//	|	<p class="foo">
		//	|		<span>...
		//	|			<span class="highlighted foo bar">...</span>
		//	|		</span>
		//	|	</p>
		//	example:
		//		set an "odd" class on all odd table rows inside of the table
		//		`#tabular_data`, using the `>` (direct child) selector to avoid
		//		affecting any nested tables:
		//	|	dojo.query("#tabular_data > tbody > tr:nth-child(odd)").addClass("odd");
		//	example:
		//		remove all elements with the class "error" from the document
		//		and store them in a list:
		//	|	var errors = dojo.query(".error").orphan();
		//	example:
		//		add an onclick handler to every submit button in the document
		//		which causes the form to be sent via Ajax instead:
		//	|	dojo.query("input[type='submit']").onclick(function(e){
		//	|		dojo.stopEvent(e); // prevent sending the form
		//	|		var btn = e.target;
		//	|		dojo.xhrPost({
		//	|			form: btn.form,
		//	|			load: function(data){
		//	|				// replace the form with the response
		//	|				var div = dojo.doc.createElement("div");
		//	|				dojo.place(div, btn.form, "after");
		//	|				div.innerHTML = data;
		//	|				dojo.style(btn.form, "display", "none");
		//	|			}
		//	|		});
		//	|	});

		// NOTE: elementsById is not currently supported
		// NOTE: ignores xpath-ish queries for now

		if(query.constructor == d.NodeList){
			return query;
		}
		if(!d.isString(query)){
			return new d.NodeList(query); // dojo.NodeList
		}
		if(d.isString(root)){
			root = d.byId(root);
		}

		return _zip(getQueryFunc(query)(root||d.doc)); // dojo.NodeList
	}

	/*
	// exposing this was a mistake
	d.query.attrs = attrs;
	*/
	// exposing this because new pseudo matches are only executed through the
	// DOM query path (never through the xpath optimizing branch)
	d.query.pseudos = pseudos;

	// one-off function for filtering a NodeList based on a simple selector
	d._filterQueryResult = function(nodeList, simpleFilter){
		var tnl = new d.NodeList();
		var ff = (simpleFilter) ? getFilterFunc(getQueryParts(simpleFilter)[0]) : function(){ return true; };
		for(var x=0, te; te = nodeList[x]; x++){
			if(ff(te)){ tnl.push(te); }
		}
		return tnl;
	}
})();

}

if(!dojo._hasResource["dojo._base.xhr"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.xhr"] = true;
dojo.provide("dojo._base.xhr");

(function(){
	var _d = dojo;
	function setValue(/*Object*/obj, /*String*/name, /*String*/value){
		//summary:
		//		For the nameed property in object, set the value. If a value
		//		already exists and it is a string, convert the value to be an
		//		array of values.
		var val = obj[name];
		if(_d.isString(val)){
			obj[name] = [val, value];
		}else if(_d.isArray(val)){
			val.push(value);
		}else{
			obj[name] = value;
		}
	}

	dojo.formToObject = function(/*DOMNode||String*/ formNode){
		// summary:
		//		dojo.formToObject returns the values encoded in an HTML form as
		//		string properties in an object which it then returns. Disabled form
		//		elements, buttons, and other non-value form elements are skipped.
		//		Multi-select elements are returned as an array of string values.
		// description:
		//		This form:
		//
		//		|	<form id="test_form">
		//		|		<input type="text" name="blah" value="blah">
		//		|		<input type="text" name="no_value" value="blah" disabled>
		//		|		<input type="button" name="no_value2" value="blah">
		//		|		<select type="select" multiple name="multi" size="5">
		//		|			<option value="blah">blah</option>
		//		|			<option value="thud" selected>thud</option>
		//		|			<option value="thonk" selected>thonk</option>
		//		|		</select>
		//		|	</form>
		//
		//		yields this object structure as the result of a call to
		//		formToObject():
		//
		//		|	{
		//		|		blah: "blah",
		//		|		multi: [
		//		|			"thud",
		//		|			"thonk"
		//		|		]
		//		|	};

		var ret = {};
		var iq = "input:not([type=file]):not([type=submit]):not([type=image]):not([type=reset]):not([type=button]), select, textarea";
		_d.query(iq, formNode).filter(function(node){
			return !node.disabled && node.name;
		}).forEach(function(item){
			var _in = item.name;
			var type = (item.type||"").toLowerCase();
			if(type == "radio" || type == "checkbox"){
				if(item.checked){ setValue(ret, _in, item.value); }
			}else if(item.multiple){
				ret[_in] = [];
				_d.query("option", item).forEach(function(opt){
					if(opt.selected){
						setValue(ret, _in, opt.value);
					}
				});
			}else{
				setValue(ret, _in, item.value);
				if(type == "image"){
					ret[_in+".x"] = ret[_in+".y"] = ret[_in].x = ret[_in].y = 0;
				}
			}
		});
		return ret; // Object
	}

	dojo.objectToQuery = function(/*Object*/ map){
		//	summary:
		//		takes a name/value mapping object and returns a string representing
		//		a URL-encoded version of that object.
		//	example:
		//		this object:
		//
		//		|	{
		//		|		blah: "blah",
		//		|		multi: [
		//		|			"thud",
		//		|			"thonk"
		//		|		]
		//		|	};
		//
		//	yields the following query string:
		//
		//	|	"blah=blah&multi=thud&multi=thonk"

		// FIXME: need to implement encodeAscii!!
		var enc = encodeURIComponent;
		var pairs = [];
		var backstop = {};
		for(var name in map){
			var value = map[name];
			if(value != backstop[name]){
				var assign = enc(name) + "=";
				if(_d.isArray(value)){
					for(var i=0; i < value.length; i++){
						pairs.push(assign + enc(value[i]));
					}
				}else{
					pairs.push(assign + enc(value));
				}
			}
		}
		return pairs.join("&"); // String
	}

	dojo.formToQuery = function(/*DOMNode||String*/ formNode){
		// summary:
		//		Returns a URL-encoded string representing the form passed as either a
		//		node or string ID identifying the form to serialize
		return _d.objectToQuery(_d.formToObject(formNode)); // String
	}

	dojo.formToJson = function(/*DOMNode||String*/ formNode, /*Boolean?*/prettyPrint){
		// summary:
		//		return a serialized JSON string from a form node or string
		//		ID identifying the form to serialize
		return _d.toJson(_d.formToObject(formNode), prettyPrint); // String
	}

	dojo.queryToObject = function(/*String*/ str){
		// summary:
		//		returns an object representing a de-serialized query section of a
		//		URL. Query keys with multiple values are returned in an array.
		// description:
		//		This string:
		//
		//	|		"foo=bar&foo=baz&thinger=%20spaces%20=blah&zonk=blarg&"
		//
		//		results in this object structure:
		//
		//	|		{
		//	|			foo: [ "bar", "baz" ],
		//	|			thinger: " spaces =blah",
		//	|			zonk: "blarg"
		//	|		}
		//
		//		Note that spaces and other urlencoded entities are correctly
		//		handled.

		// FIXME: should we grab the URL string if we're not passed one?
		var ret = {};
		var qp = str.split("&");
		var dec = decodeURIComponent;
		_d.forEach(qp, function(item){
			if(item.length){
				var parts = item.split("=");
				var name = dec(parts.shift());
				var val = dec(parts.join("="));
				if(_d.isString(ret[name])){
					ret[name] = [ret[name]];
				}
				if(_d.isArray(ret[name])){
					ret[name].push(val);
				}else{
					ret[name] = val;
				}
			}
		});
		return ret; // Object
	}

	/*
		from refactor.txt:

		all bind() replacement APIs take the following argument structure:

			{
				url: "blah.html",

				// all below are optional, but must be supported in some form by
				// every IO API
				timeout: 1000, // milliseconds
				handleAs: "text", // replaces the always-wrong "mimetype"
				content: {
					key: "value"
				},

				// browser-specific, MAY be unsupported
				sync: true, // defaults to false
				form: dojo.byId("someForm")
			}
	*/

	// need to block async callbacks from snatching this thread as the result
	// of an async callback might call another sync XHR, this hangs khtml forever
	// must checked by watchInFlight()

	dojo._blockAsync = false;

	dojo._contentHandlers = {
		"text": function(xhr){ return xhr.responseText; },
		"json": function(xhr){
			if(!dojo.config.usePlainJson){
				console.warn("Consider using mimetype:text/json-comment-filtered"
					+ " to avoid potential security issues with JSON endpoints"
					+ " (use djConfig.usePlainJson=true to turn off this message)");
			}
			return (xhr.status == 204) ? undefined : _d.fromJson(xhr.responseText);
		},
		"json-comment-filtered": function(xhr){
			// NOTE: we provide the json-comment-filtered option as one solution to
			// the "JavaScript Hijacking" issue noted by Fortify and others. It is
			// not appropriate for all circumstances.

			var value = xhr.responseText;
			var cStartIdx = value.indexOf("\/*");
			var cEndIdx = value.lastIndexOf("*\/");
			if(cStartIdx == -1 || cEndIdx == -1){
				throw new Error("JSON was not comment filtered");
			}
			return (xhr.status == 204) ? undefined :
				_d.fromJson(value.substring(cStartIdx+2, cEndIdx));
		},
		"javascript": function(xhr){
			// FIXME: try Moz and IE specific eval variants?
			return _d.eval(xhr.responseText);
		},
		"xml": function(xhr){
			var result = xhr.responseXML;
			if(_d.isIE && (!result || window.location.protocol == "file:")){
				_d.forEach(["MSXML2", "Microsoft", "MSXML", "MSXML3"], function(prefix){
					try{
						var dom = new ActiveXObject(prefix + ".XMLDOM");
						dom.async = false;
						dom.loadXML(xhr.responseText);
						result = dom;
					}catch(e){ /* Not available. Squelch and try next one. */ }
				});
			}
			return result; // DOMDocument
		}
	};

	dojo._contentHandlers["json-comment-optional"] = function(xhr){
		var handlers = _d._contentHandlers;
		try{
			return handlers["json-comment-filtered"](xhr);
		}catch(e){
			return handlers["json"](xhr);
		}
	};

	/*=====
	dojo.__IoArgs = function(){
		//	url: String
		//		URL to server endpoint.
		//	content: Object?
		//		Contains properties with string values. These
		//		properties will be serialized as name1=value2 and
		//		passed in the request.
		//	timeout: Integer?
		//		Milliseconds to wait for the response. If this time
		//		passes, the then error callbacks are called.
		//	form: DOMNode?
		//		DOM node for a form. Used to extract the form values
		//		and send to the server.
		//	preventCache: Boolean?
		//		Default is false. If true, then a
		//		"dojo.preventCache" parameter is sent in the request
		//		with a value that changes with each request
		//		(timestamp). Useful only with GET-type requests.
		//	handleAs: String?
		//		Acceptable values depend on the type of IO
		//		transport (see specific IO calls for more information).
		//	load: Function?
		//		function(response, ioArgs){}. response is an Object, ioArgs
		//		is of type dojo.__IoCallbackArgs. The load function will be
		//		called on a successful response.
		//	error: Function?
		//		function(response, ioArgs){}. response is an Object, ioArgs
		//		is of type dojo.__IoCallbackArgs. The error function will
		//		be called in an error case.
		//	handle: Function?
		//		function(response, ioArgs){}. response is an Object, ioArgs
		//		is of type dojo.__IoCallbackArgs. The handle function will
		//		be called in either the successful or error case.  For
		//		the load, error and handle functions, the ioArgs object
		//		will contain the following properties:
		this.url = url;
		this.content = content;
		this.timeout = timeout;
		this.form = form;
		this.preventCache = preventCache;
		this.handleAs = handleAs;
		this.load = load;
		this.error = error;
		this.handle = handle;
	}
	=====*/

	/*=====
	dojo.__IoCallbackArgs = function(args, xhr, url, query, handleAs, id, canDelete, json){
		//	args: Object
		//		the original object argument to the IO call.
		//	xhr: XMLHttpRequest
		//		For XMLHttpRequest calls only, the
		//		XMLHttpRequest object that was used for the
		//		request.
		//	url: String
		//		The final URL used for the call. Many times it
		//		will be different than the original args.url
		//		value.
		//	query: String
		//		For non-GET requests, the
		//		name1=value1&name2=value2 parameters sent up in
		//		the request.
		//	handleAs: String
		//		The final indicator on how the response will be
		//		handled.
		//	id: String
		//		For dojo.io.script calls only, the internal
		//		script ID used for the request.
		//	canDelete: Boolean
		//		For dojo.io.script calls only, indicates
		//		whether the script tag that represents the
		//		request can be deleted after callbacks have
		//		been called. Used internally to know when
		//		cleanup can happen on JSONP-type requests.
		//	json: Object
		//		For dojo.io.script calls only: holds the JSON
		//		response for JSONP-type requests. Used
		//		internally to hold on to the JSON responses.
		//		You should not need to access it directly --
		//		the same object should be passed to the success
		//		callbacks directly.
		this.args = args;
		this.xhr = xhr;
		this.url = url;
		this.query = query;
		this.handleAs = handleAs;
		this.id = id;
		this.canDelete = canDelete;
		this.json = json;
	}
	=====*/

	dojo._ioSetArgs = function(/*dojo.__IoArgs*/args,
			/*Function*/canceller,
			/*Function*/okHandler,
			/*Function*/errHandler){
		//	summary:
		//		sets up the Deferred and ioArgs property on the Deferred so it
		//		can be used in an io call.
		//	args:
		//		The args object passed into the public io call. Recognized properties on
		//		the args object are:
		//	canceller:
		//		The canceller function used for the Deferred object. The function
		//		will receive one argument, the Deferred object that is related to the
		//		canceller.
		//	okHandler:
		//		The first OK callback to be registered with Deferred. It has the opportunity
		//		to transform the OK response. It will receive one argument -- the Deferred
		//		object returned from this function.
		//	errHandler:
		//		The first error callback to be registered with Deferred. It has the opportunity
		//		to do cleanup on an error. It will receive two arguments: error (the
		//		Error object) and dfd, the Deferred object returned from this function.

		var ioArgs = {args: args, url: args.url};

		//Get values from form if requestd.
		var formObject = null;
		if(args.form){
			var form = _d.byId(args.form);
			//IE requires going through getAttributeNode instead of just getAttribute in some form cases,
			//so use it for all.  See #2844
			var actnNode = form.getAttributeNode("action");
			ioArgs.url = ioArgs.url || (actnNode ? actnNode.value : null);
			formObject = _d.formToObject(form);
		}

		// set up the query params
		var miArgs = [{}];

		if(formObject){
			// potentially over-ride url-provided params w/ form values
			miArgs.push(formObject);
		}
		if(args.content){
			// stuff in content over-rides what's set by form
			miArgs.push(args.content);
		}
		if(args.preventCache){
			miArgs.push({"dojo.preventCache": new Date().valueOf()});
		}
		ioArgs.query = _d.objectToQuery(_d.mixin.apply(null, miArgs));

		// .. and the real work of getting the deferred in order, etc.
		ioArgs.handleAs = args.handleAs || "text";
		var d = new _d.Deferred(canceller);
		d.addCallbacks(okHandler, function(error){
			return errHandler(error, d);
		});

		//Support specifying load, error and handle callback functions from the args.
		//For those callbacks, the "this" object will be the args object.
		//The callbacks will get the deferred result value as the
		//first argument and the ioArgs object as the second argument.
		var ld = args.load;
		if(ld && _d.isFunction(ld)){
			d.addCallback(function(value){
				return ld.call(args, value, ioArgs);
			});
		}
		var err = args.error;
		if(err && _d.isFunction(err)){
			d.addErrback(function(value){
				return err.call(args, value, ioArgs);
			});
		}
		var handle = args.handle;
		if(handle && _d.isFunction(handle)){
			d.addBoth(function(value){
				return handle.call(args, value, ioArgs);
			});
		}

		d.ioArgs = ioArgs;

		// FIXME: need to wire up the xhr object's abort method to something
		// analagous in the Deferred
		return d;
	}

	var _deferredCancel = function(/*Deferred*/dfd){
		//summary: canceller function for dojo._ioSetArgs call.

		dfd.canceled = true;
		var xhr = dfd.ioArgs.xhr;
		var _at = typeof xhr.abort;
		if(_at == "function" || _at == "unknown"){
			xhr.abort();
		}
		var err = new Error("xhr cancelled");
		err.dojoType = "cancel";
		return err;
	}
	var _deferredOk = function(/*Deferred*/dfd){
		//summary: okHandler function for dojo._ioSetArgs call.

		return _d._contentHandlers[dfd.ioArgs.handleAs](dfd.ioArgs.xhr);
	}
	var _deferError = function(/*Error*/error, /*Deferred*/dfd){
		//summary: errHandler function for dojo._ioSetArgs call.

		// console.debug("xhr error in:", dfd.ioArgs.xhr);
		console.debug(error);
		return error;
	}

	var _makeXhrDeferred = function(/*dojo.__XhrArgs*/args){
		//summary: makes the Deferred object for this xhr request.
		var dfd = _d._ioSetArgs(args, _deferredCancel, _deferredOk, _deferError);
		//Pass the args to _xhrObj, to allow xhr iframe proxy interceptions.
		dfd.ioArgs.xhr = _d._xhrObj(dfd.ioArgs.args);
		return dfd;
	}

	// avoid setting a timer per request. It degrades performance on IE
	// something fierece if we don't use unified loops.
	var _inFlightIntvl = null;
	var _inFlight = [];
	var _watchInFlight = function(){
		//summary:
		//		internal method that checks each inflight XMLHttpRequest to see
		//		if it has completed or if the timeout situation applies.

		var now = (new Date()).getTime();
		// make sure sync calls stay thread safe, if this callback is called
		// during a sync call and this results in another sync call before the
		// first sync call ends the browser hangs
		if(!_d._blockAsync){
			// we need manual loop because we often modify _inFlight (and therefore 'i') while iterating
			// note: the second clause is an assigment on purpose, lint may complain
			for(var i = 0, tif; i < _inFlight.length && (tif = _inFlight[i]); i++){
				var dfd = tif.dfd;
				try{
					if(!dfd || dfd.canceled || !tif.validCheck(dfd)){
						_inFlight.splice(i--, 1);
					}else if(tif.ioCheck(dfd)){
						_inFlight.splice(i--, 1);
						tif.resHandle(dfd);
					}else if(dfd.startTime){
						//did we timeout?
						if(dfd.startTime + (dfd.ioArgs.args.timeout || 0) < now){
							_inFlight.splice(i--, 1);
							var err = new Error("timeout exceeded");
							err.dojoType = "timeout";
							dfd.errback(err);
							//Cancel the request so the io module can do appropriate cleanup.
							dfd.cancel();
						}
					}
				}catch(e){
					// FIXME: make sure we errback! (fixed?  remove console.debug?)
					console.debug(e);
					dfd.errback(new Error("_watchInFlightError!"));
				}
			}
		}

		if(!_inFlight.length){
			clearInterval(_inFlightIntvl);
			_inFlightIntvl = null;
			return;
		}

	}

	dojo._ioCancelAll = function(){
		//summary: Cancels all pending IO requests, regardless of IO type
		//(xhr, script, iframe).
		try{
			_d.forEach(_inFlight, function(i){
				i.dfd.cancel();
			});
		}catch(e){/*squelch*/}
	}

	//Automatically call cancel all io calls on unload
	//in IE for trac issue #2357.
	if(_d.isIE){
		_d.addOnUnload(_d._ioCancelAll);
	}

	_d._ioWatch = function(/*Deferred*/dfd,
		/*Function*/validCheck,
		/*Function*/ioCheck,
		/*Function*/resHandle){
		//summary: watches the io request represented by dfd to see if it completes.
		//dfd:
		//		The Deferred object to watch.
		//validCheck:
		//		Function used to check if the IO request is still valid. Gets the dfd
		//		object as its only argument.
		//ioCheck:
		//		Function used to check if basic IO call worked. Gets the dfd
		//		object as its only argument.
		//resHandle:
		//		Function used to process response. Gets the dfd
		//		object as its only argument.
		if(dfd.ioArgs.args.timeout){
			dfd.startTime = (new Date()).getTime();
		}
		_inFlight.push({dfd: dfd, validCheck: validCheck, ioCheck: ioCheck, resHandle: resHandle});
		if(!_inFlightIntvl){
			_inFlightIntvl = setInterval(_watchInFlight, 50);
		}
		_watchInFlight(); // handle sync requests
	}

	var _defaultContentType = "application/x-www-form-urlencoded";

	var _validCheck = function(/*Deferred*/dfd){
		return dfd.ioArgs.xhr.readyState; //boolean
	}
	var _ioCheck = function(/*Deferred*/dfd){
		return 4 == dfd.ioArgs.xhr.readyState; //boolean
	}
	var _resHandle = function(/*Deferred*/dfd){
		var xhr = dfd.ioArgs.xhr;
		if(_d._isDocumentOk(xhr)){
			dfd.callback(dfd);
		}else{
			var err = new Error("Unable to load " + dfd.ioArgs.url + " status:" + xhr.status);
			err.status = xhr.status;
			err.responseText = xhr.responseText;
			dfd.errback(err);
		}
	}

	var _doIt = function(/*String*/type, /*Deferred*/dfd){
		// IE 6 is a steaming pile. It won't let you call apply() on the native function (xhr.open).
		// workaround for IE6's apply() "issues"
		var ioArgs = dfd.ioArgs;
		var args = ioArgs.args;
		var xhr = ioArgs.xhr;
		xhr.open(type, ioArgs.url, args.sync !== true, args.user || undefined, args.password || undefined);
		if(args.headers){
			for(var hdr in args.headers){
				if(hdr.toLowerCase() === "content-type" && !args.contentType){
					args.contentType = args.headers[hdr];
				}else{
					xhr.setRequestHeader(hdr, args.headers[hdr]);
				}
			}
		}
		// FIXME: is this appropriate for all content types?
		xhr.setRequestHeader("Content-Type", args.contentType || _defaultContentType);
		if(!args.headers || !args.headers["X-Requested-With"]){
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		}
		// FIXME: set other headers here!
		try{
			xhr.send(ioArgs.query);
		}catch(e){
			dfd.cancel();
		}
		_d._ioWatch(dfd, _validCheck, _ioCheck, _resHandle);
		xhr = null;
		return dfd; //Deferred
	}

	dojo._ioAddQueryToUrl = function(/*dojo.__IoCallbackArgs*/ioArgs){
		//summary: Adds query params discovered by the io deferred construction to the URL.
		//Only use this for operations which are fundamentally GET-type operations.
		if(ioArgs.query.length){
			ioArgs.url += (ioArgs.url.indexOf("?") == -1 ? "?" : "&") + ioArgs.query;
			ioArgs.query = null;
		}
	}

	/*=====
	dojo.declare("dojo.__XhrArgs", dojo.__IoArgs, {
		constructor: function(){
			//	summary:
			//		In addition to the properties listed for the dojo._IoArgs type,
			//		the following properties are allowed for dojo.xhr* methods.
			//	handleAs: String?
			//		Acceptable values are: text (default), json, json-comment-optional,
			//		json-comment-filtered, javascript, xml
			//	sync: Boolean?
			//		false is default. Indicates whether the request should
			//		be a synchronous (blocking) request.
			//	headers: Object?
			//		Additional HTTP headers to send in the request.
			this.handleAs = handleAs;
			this.sync = sync;
			this.headers = headers;
		}
	});
	=====*/

	dojo.xhr = function(/*String*/ method, /*dojo.__XhrArgs*/ args, /*Boolean?*/ hasBody){
		//	summary:
		//		Sends an HTTP request with the given method. If the request has an
		//		HTTP body, then pass true for hasBody. The method argument should be uppercase.
		//		Also look at dojo.xhrGet(), xhrPost(), xhrPut() and dojo.xhrDelete() for shortcuts
		//		for those HTTP methods. There are also methods for "raw" PUT and POST methods
		//		via dojo.rawXhrPut() and dojo.rawXhrPost() respectively.
		var dfd = _makeXhrDeferred(args);
		if(!hasBody){
			_d._ioAddQueryToUrl(dfd.ioArgs);
		}
		return _doIt(method, dfd); // dojo.Deferred
	}

	dojo.xhrGet = function(/*dojo.__XhrArgs*/ args){
		//	summary:
		//		Sends an HTTP GET request to the server.
		return _d.xhr("GET", args); //dojo.Deferred
	}

	dojo.xhrPost = function(/*dojo.__XhrArgs*/ args){
		//summary:
		//		Sends an HTTP POST request to the server.
		return _d.xhr("POST", args, true); // dojo.Deferred
	}

	dojo.rawXhrPost = function(/*dojo.__XhrArgs*/ args){
		//	summary:
		//		Sends an HTTP POST request to the server. In addtion to the properties
		//		listed for the dojo.__XhrArgs type, the following property is allowed:
		//	postData:
		//		String. The raw data to send in the body of the POST request.
		var dfd = _makeXhrDeferred(args);
		dfd.ioArgs.query = args.postData;
		return _doIt("POST", dfd); // dojo.Deferred
	}

	dojo.xhrPut = function(/*dojo.__XhrArgs*/ args){
		//	summary:
		//		Sends an HTTP PUT request to the server.
		return _d.xhr("PUT", args, true); // dojo.Deferred
	}

	dojo.rawXhrPut = function(/*dojo.__XhrArgs*/ args){
		//	summary:
		//		Sends an HTTP PUT request to the server. In addtion to the properties
		//		listed for the dojo.__XhrArgs type, the following property is allowed:
		//	putData:
		//		String. The raw data to send in the body of the PUT request.
		var dfd = _makeXhrDeferred(args);
		var ioArgs = dfd.ioArgs;
		if(args.putData){
			ioArgs.query = args.putData;
			args.putData = null;
		}
		return _doIt("PUT", dfd); // dojo.Deferred
	}

	dojo.xhrDelete = function(/*dojo.__XhrArgs*/ args){
		//	summary:
		//		Sends an HTTP DELETE request to the server.
		return _d.xhr("DELETE", args); //dojo.Deferred
	}

	/*
	dojo.wrapForm = function(formNode){
		//summary:
		//		A replacement for FormBind, but not implemented yet.

		// FIXME: need to think harder about what extensions to this we might
		// want. What should we allow folks to do w/ this? What events to
		// set/send?
		throw new Error("dojo.wrapForm not yet implemented");
	}
	*/
})();

}

if(!dojo._hasResource["dojo._base.fx"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.fx"] = true;
dojo.provide("dojo._base.fx");

/*
	Animation losely package based on Dan Pupius' work, contributed under CLA:
		http://pupius.co.uk/js/Toolkit.Drawing.js
*/
(function(){

	var d = dojo;

	dojo._Line = function(/*int*/ start, /*int*/ end){
		//	summary:
		//		dojo._Line is the object used to generate values from a start value
		//		to an end value
		//	start: int
		//		Beginning value for range
		//	end: int
		//		Ending value for range
		this.start = start;
		this.end = end;
		this.getValue = function(/*float*/ n){
			//	summary: returns the point on the line
			//	n: a floating point number greater than 0 and less than 1
			return ((this.end - this.start) * n) + this.start; // Decimal
		}
	}

	d.declare("dojo._Animation", null, {
		//	summary
		//		A generic animation class that fires callbacks into its handlers
		//		object at various states. Nearly all dojo animation functions
		//		return an instance of this method, usually without calling the
		//		.play() method beforehand. Therefore, you will likely need to
		//		call .play() on instances of dojo._Animation when one is
		//		returned.
		constructor: function(/*Object*/ args){
			d.mixin(this, args);
			if(d.isArray(this.curve)){
				/* curve: Array
					pId: a */
				this.curve = new d._Line(this.curve[0], this.curve[1]);
			}
		},

		// duration: Integer
		//	The time in milliseonds the animation will take to run
		duration: 350,

	/*=====
		// curve: dojo._Line||Array
		//	A two element array of start and end values, or a dojo._Line instance to be
		//	used in the Animation.
		curve: null,

		// easing: Function
		//	A Function to adjust the acceleration (or deceleration) of the progress
		//	across a dojo._Line
		easing: null,
	=====*/

		// repeat: Integer
		//	The number of times to loop the animation
		repeat: 0,

		// rate: Integer
		//	the time in milliseconds to wait before advancing to next frame
		//	(used as a fps timer: rate/1000 = fps)
		rate: 10 /* 100 fps */,

	/*=====
		// delay: Integer
		// 	The time in milliseconds to wait before starting animation after it has been .play()'ed
		delay: null,

		// events
		//
		// beforeBegin: Event
		//	Synthetic event fired before a dojo._Animation begins playing (synchronous)
		beforeBegin: null,

		// onBegin: Event
		//	Synthetic event fired as a dojo._Animation begins playing (useful?)
		onBegin: null,

		// onAnimate: Event
		//	Synthetic event fired at each interval of a dojo._Animation
		onAnimate: null,

		// onEnd: Event
		//	Synthetic event fired after the final frame of a dojo._Animation
		onEnd: null,

		// onPlay: Event
		//	Synthetic event fired any time a dojo._Animation is play()'ed
		onPlay: null,

		// onPause: Event
		//	Synthetic event fired when a dojo._Animation is paused
		onPause: null,

		// onStop: Event
		//	Synthetic event fires when a dojo._Animation is stopped
		onStop: null,

	=====*/

		_percent: 0,
		_startRepeatCount: 0,

		_fire: function(/*Event*/ evt, /*Array?*/ args){
			//	summary:
			//		Convenience function.  Fire event "evt" and pass it the
			//		arguments specified in "args".
			//	evt:
			//		The event to fire.
			//	args:
			//		The arguments to pass to the event.
			try{
				if(this[evt]){
					this[evt].apply(this, args||[]);
				}
			}catch(e){
				// squelch and log because we shouldn't allow exceptions in
				// synthetic event handlers to cause the internal timer to run
				// amuck, potentially pegging the CPU. I'm not a fan of this
				// squelch, but hopefully logging will make it clear what's
				// going on
				console.error("exception in animation handler for:", evt);
				console.error(e);
			}
			return this; // dojo._Animation
		},

		play: function(/*int?*/ delay, /*Boolean?*/ gotoStart){
			// summary:
			//		Start the animation.
			// delay:
			//		How many milliseconds to delay before starting.
			// gotoStart:
			//		If true, starts the animation from the beginning; otherwise,
			//		starts it from its current position.
			var _t = this;
			if(gotoStart){
				_t._stopTimer();
				_t._active = _t._paused = false;
				_t._percent = 0;
			}else if(_t._active && !_t._paused){
				return _t; // dojo._Animation
			}

			_t._fire("beforeBegin");

			var de = delay||_t.delay;
			var _p = dojo.hitch(_t, "_play", gotoStart);
			if(de > 0){
				setTimeout(_p, de);
				return _t; // dojo._Animation
			}
			_p();
			return _t;
		},

		_play: function(gotoStart){
			var _t = this;
			_t._startTime = new Date().valueOf();
			if(_t._paused){
				_t._startTime -= _t.duration * _t._percent;
			}
			_t._endTime = _t._startTime + _t.duration;

			_t._active = true;
			_t._paused = false;

			var value = _t.curve.getValue(_t._percent);
			if(!_t._percent){
				if(!_t._startRepeatCount){
					_t._startRepeatCount = _t.repeat;
				}
				_t._fire("onBegin", [value]);
			}

			_t._fire("onPlay", [value]);

			_t._cycle();
			return _t; // dojo._Animation
		},

		pause: function(){
			// summary: Pauses a running animation.
			this._stopTimer();
			if(!this._active){ return this; /*dojo._Animation*/ }
			this._paused = true;
			this._fire("onPause", [this.curve.getValue(this._percent)]);
			return this; // dojo._Animation
		},

		gotoPercent: function(/*Decimal*/ percent, /*Boolean?*/ andPlay){
			//	summary:
			//		Sets the progress of the animation.
			//	percent:
			//		A percentage in decimal notation (between and including 0.0 and 1.0).
			//	andPlay:
			//		If true, play the animation after setting the progress.
			this._stopTimer();
			this._active = this._paused = true;
			this._percent = percent;
			if(andPlay){ this.play(); }
			return this; // dojo._Animation
		},

		stop: function(/*boolean?*/ gotoEnd){
			// summary: Stops a running animation.
			// gotoEnd: If true, the animation will end.
			if(!this._timer){ return this; /* dojo._Animation */ }
			this._stopTimer();
			if(gotoEnd){
				this._percent = 1;
			}
			this._fire("onStop", [this.curve.getValue(this._percent)]);
			this._active = this._paused = false;
			return this; // dojo._Animation
		},

		status: function(){
			// summary: Returns a string token representation of the status of
			//			the animation, one of: "paused", "playing", "stopped"
			if(this._active){
				return this._paused ? "paused" : "playing"; // String
			}
			return "stopped"; // String
		},

		_cycle: function(){
			var _t = this;
			if(_t._active){
				var curr = new Date().valueOf();
				var step = (curr - _t._startTime) / (_t._endTime - _t._startTime);

				if(step >= 1){
					step = 1;
				}
				_t._percent = step;

				// Perform easing
				if(_t.easing){
					step = _t.easing(step);
				}

				_t._fire("onAnimate", [_t.curve.getValue(step)]);

				if(_t._percent < 1){
					_t._startTimer();
				}else{
					_t._active = false;

					if(_t.repeat > 0){
						_t.repeat--;
						_t.play(null, true);
					}else if(_t.repeat == -1){
						_t.play(null, true);
					}else{
						if(_t._startRepeatCount){
							_t.repeat = _t._startRepeatCount;
							_t._startRepeatCount = 0;
						}
					}
					_t._percent = 0;
					_t._fire("onEnd");
					_t._stopTimer();
				}
			}
			return _t; // dojo._Animation
		}
	});

	var ctr = 0;
	var _globalTimerList = [];
	var runner = {
		run: function(){ }
	};
	var timer = null;
	dojo._Animation.prototype._startTimer = function(){
		// this._timer = setTimeout(dojo.hitch(this, "_cycle"), this.rate);
		if(!this._timer){
			this._timer = d.connect(runner, "run", this, "_cycle");
			ctr++;
		}
		if(!timer){
			timer = setInterval(d.hitch(runner, "run"), this.rate);
		}
	};

	dojo._Animation.prototype._stopTimer = function(){
		if(this._timer){
			d.disconnect(this._timer);
			this._timer = null;
			ctr--;
		}
		if(ctr <= 0){
			clearInterval(timer);
			timer = null;
			ctr = 0;
		}
	};

	var _makeFadeable = (d.isIE) ? function(node){
		// only set the zoom if the "tickle" value would be the same as the
		// default
		var ns = node.style;
		if(!ns.zoom.length && d.style(node, "zoom") == "normal"){
			// make sure the node "hasLayout"
			// NOTE: this has been tested with larger and smaller user-set text
			// sizes and works fine
			ns.zoom = "1";
			// node.style.zoom = "normal";
		}
		// don't set the width to auto if it didn't already cascade that way.
		// We don't want to f anyones designs
		if(!ns.width.length && d.style(node, "width") == "auto"){
			ns.width = "auto";
		}
	} : function(){};

	dojo._fade = function(/*Object*/ args){
		//	summary:
		//		Returns an animation that will fade the node defined by
		//		args.node from the start to end values passed (args.start
		//		args.end) (end is mandatory, start is optional)

		args.node = d.byId(args.node);
		var fArgs = d.mixin({ properties: {} }, args);
		var props = (fArgs.properties.opacity = {});
		props.start = !("start" in fArgs) ?
			function(){
				return Number(d.style(fArgs.node, "opacity"));
			} : fArgs.start;
		props.end = fArgs.end;

		var anim = d.animateProperty(fArgs);
		d.connect(anim, "beforeBegin", d.partial(_makeFadeable, fArgs.node));

		return anim; // dojo._Animation
	}

	/*=====
	dojo.__FadeArgs = function(node, duration, easing){
		// 	node: DOMNode|String
		//		The node referenced in the animation
		//	duration: Integer?
		//		Duration of the animation in milliseconds.
		//	easing: Function?
		//		An easing function.
		this.node = node;
		this.duration = duration;
		this.easing = easing;
	}
	=====*/

	dojo.fadeIn = function(/*dojo.__FadeArgs*/ args){
		// summary:
		//		Returns an animation that will fade node defined in 'args' from
		//		its current opacity to fully opaque.
		return d._fade(d.mixin({ end: 1 }, args)); // dojo._Animation
	}

	dojo.fadeOut = function(/*dojo.__FadeArgs*/  args){
		// summary:
		//		Returns an animation that will fade node defined in 'args'
		//		from its current opacity to fully transparent.
		return d._fade(d.mixin({ end: 0 }, args)); // dojo._Animation
	}

	dojo._defaultEasing = function(/*Decimal?*/ n){
		// summary: The default easing function for dojo._Animation(s)
		return 0.5 + ((Math.sin((n + 1.5) * Math.PI))/2);
	}

	var PropLine = function(properties){
		// PropLine is an internal class which is used to model the values of
		// an a group of CSS properties across an animation lifecycle. In
		// particular, the "getValue" function handles getting interpolated
		// values between start and end for a particular CSS value.
		this._properties = properties;
		for(var p in properties){
			var prop = properties[p];
			if(prop.start instanceof d.Color){
				// create a reusable temp color object to keep intermediate results
				prop.tempColor = new d.Color();
			}
		}
		this.getValue = function(r){
			var ret = {};
			for(var p in this._properties){
				var prop = this._properties[p];
				var start = prop.start;
				if(start instanceof d.Color){
					ret[p] = d.blendColors(start, prop.end, r, prop.tempColor).toCss();
				}else if(!d.isArray(start)){
					ret[p] = ((prop.end - start) * r) + start + (p != "opacity" ? prop.units||"px" : "");
				}
			}
			return ret;
		}
	}

	/*=====
	dojo.declare("dojo.__AnimArgs", [dojo.__FadeArgs], {
		// Properties: Object?
		//	A hash map of style properties to Objects describing the transition,
		//	such as the properties of dojo._Line with an additional 'unit' property
		properties: {}

		//TODOC: add event callbacks
	});
	=====*/

	dojo.animateProperty = function(/*dojo.__AnimArgs*/ args){
		//	summary:
		//		Returns an animation that will transition the properties of
		//		node defined in 'args' depending how they are defined in
		//		'args.properties'
		//
		// description:
		//		dojo.animateProperty is the foundation of most dojo.fx
		//		animations. It takes an object of "properties" corresponding to
		//		style properties, and animates them in parallel over a set
		//		duration.
		//
		// 	example:
		//		A simple animation that changes the width of the specified node.
		//	|	dojo.animateProperty({
		//	|		node: "nodeId",
		//	|		properties: { width: 400 },
		//	|	}).play();
		//		Dojo figures out the start value for the width and converts the
		//		integer specified for the width to the more expressive but
		//		verbose form `{ width: { end: '400', units: 'px' } }` which you
		//		can also specify directly
		// 	example:
		//		animate width, height, and padding over 2 seconds...the
		//		pedantic way:
		//	|	dojo.animateProperty({ node: node, duration:2000,
		//	|		properties: {
		//	|			width: { start: '200', end: '400', unit:"px" },
		//	|			height: { start:'200', end: '400', unit:"px" },
		//	|			paddingTop: { start:'5', end:'50', unit:"px" }
		//	|		}
		//	|	}).play();
		//
		// 	example:
		//		plug in a different easing function and register a callback for
		//		when the animation ends. Easing functions accept values between
		//		zero and one and return a value on that basis. In this case, an
		//		exponential-in curve.
		//	|	dojo.animateProperty({
		//	|		node: "nodeId",
		//	|		// dojo figures out the start value
		//	|		properties: { width: { end: 400 } },
		//	|		easing: function(n){
		//	|			return (n==0) ? 0 : Math.pow(2, 10 * (n - 1));
		//	|		},
		//	|		onEnd: function(){
		//	|			// called when the animation finishes
		//	|		}
		//	|	}).play(500); // delay playing half a second

		args.node = d.byId(args.node);
		if(!args.easing){ args.easing = d._defaultEasing; }

		var anim = new d._Animation(args);
		d.connect(anim, "beforeBegin", anim, function(){
			var pm = {};
			for(var p in this.properties){
				// Make shallow copy of properties into pm because we overwrite
				// some values below. In particular if start/end are functions
				// we don't want to overwrite them or the functions won't be
				// called if the animation is reused.
				if(p == "width" || p == "height"){
					this.node.display = "block";
				}
				var prop = this.properties[p];
				prop = pm[p] = d.mixin({}, (d.isObject(prop) ? prop: { end: prop }));

				if(d.isFunction(prop.start)){
					prop.start = prop.start();
				}
				if(d.isFunction(prop.end)){
					prop.end = prop.end();
				}
				var isColor = (p.toLowerCase().indexOf("color") >= 0);
				function getStyle(node, p){
					// dojo.style(node, "height") can return "auto" or "" on IE; this is more reliable:
					var v = ({height: node.offsetHeight, width: node.offsetWidth})[p];
					if(v !== undefined){ return v; }
					v = d.style(node, p);
					return (p=="opacity") ? Number(v) : (isColor ? v : parseFloat(v));
				}
				if(!("end" in prop)){
					prop.end = getStyle(this.node, p);
				}else if(!("start" in prop)){
					prop.start = getStyle(this.node, p);
				}

				if(isColor){
					prop.start = new d.Color(prop.start);
					prop.end = new d.Color(prop.end);
				}else{
					prop.start = (p == "opacity") ? Number(prop.start) : parseFloat(prop.start);
				}
			}
			this.curve = new PropLine(pm);
		});
		d.connect(anim, "onAnimate", anim, function(propValues){
			// try{
			for(var s in propValues){
				d.style(this.node, s, propValues[s]);
				// this.node.style[s] = propValues[s];
			}
		});
		return anim; // dojo._Animation
	}

	dojo.anim = function(	/*DOMNode|String*/ 	node,
							/*Object*/ 			properties,
							/*Integer?*/		duration,
							/*Function?*/		easing,
							/*Function?*/		onEnd,
							/*Integer?*/		delay){
		//	summary:
		//		A simpler interface to `dojo.animateProperty()`, also returns
		//		an instance of `dojo._Animation` but begins the animation
		//		immediately, unlike nearly every other Dojo animation API.
		//	description:
		//		`dojo.anim` is a simpler (but somewhat less powerful) version
		//		of `dojo.animateProperty`.  It uses defaults for many basic properties
		//		and allows for positional parameters to be used in place of the
		//		packed "property bag" which is used for other Dojo animation
		//		methods.
		//
		//		The `dojo._Animation` object returned from `dojo.anim` will be
		//		already playing when it is returned from this function, so
		//		calling play() on it again is (usually) a no-op.
		//	node:
		//		a DOM node or the id of a node to animate CSS properties on
		//	duration:
		//		The number of milliseconds over which the animation
		//		should run. Defaults to the global animation default duration
		//		(350ms).
		//	easing:
		//		An easing function over which to calculate acceleration
		//		and deceleration of the animation through its duration.
		//		A default easing algorithm is provided, but you may
		//		plug in any you wish. A large selection of easing algorithms
		//		are available in `dojox.fx.easing`.
		//	onEnd:
		//		A function to be called when the animation finishes
		//		running.
		//	delay:
		//		The number of milliseconds to delay beginning the
		//		animation by. The default is 0.
		//	example:
		//		Fade out a node
		//	|	dojo.anim("id", { opacity: 0 });
		//	example:
		//		Fade out a node over a full second
		//	|	dojo.anim("id", { opacity: 0 }, 1000);
		return d.animateProperty({
			node: node,
			duration: duration||d._Animation.prototype.duration,
			properties: properties,
			easing: easing,
			onEnd: onEnd
		}).play(delay||0);
	}
})();

}

if(!dojo._hasResource["dojo._base.browser"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojo._base.browser"] = true;
dojo.provide("dojo._base.browser");

//Need this to be the last code segment in base, so do not place any
//dojo.requireIf calls in this file. Otherwise, due to how the build system
//puts all requireIf dependencies after the current file, the require calls
//could be called before all of base is defined.
if(dojo.config.require){
	dojo.forEach(dojo.config.require, "dojo['require'](item);");
}

}

	if(dojo.config.afterOnLoad && dojo.isBrowser){
		//Dojo is being added to the page after page load, so just trigger
		//the init sequence after a timeout. Using a timeout so the rest of this
		//script gets evaluated properly. This work needs to happen after the
		//dojo.config.require work done in dojo._base.
		window.setTimeout(dojo._fakeLoadInit, 1000);
	}

})();

