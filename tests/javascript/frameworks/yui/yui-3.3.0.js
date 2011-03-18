/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
/**
 * The YUI module contains the components required for building the YUI seed
 * file.  This includes the script loading mechanism, a simple queue, and
 * the core utilities for the library.
 * @module yui
 * @submodule yui-base
 */

if (typeof YUI != 'undefined') {
    YUI._YUI = YUI;
}

/**
 * The YUI global namespace object.  If YUI is already defined, the
 * existing YUI object will not be overwritten so that defined
 * namespaces are preserved.  It is the constructor for the object
 * the end user interacts with.  As indicated below, each instance
 * has full custom event support, but only if the event system
 * is available.  This is a self-instantiable factory function.  You
 * can invoke it directly like this:
 *
 * YUI().use('*', function(Y) {
 *   // ready
 * });
 *
 * But it also works like this:
 *
 * var Y = YUI();
 *
 * @class YUI
 * @constructor
 * @global
 * @uses EventTarget
 * @param o* {object} 0..n optional configuration objects.  these values
 * are store in Y.config.  See config for the list of supported
 * properties.
 */
    /*global YUI*/
    /*global YUI_config*/
    var YUI = function() {
        var i = 0,
            Y = this,
            args = arguments,
            l = args.length,
            instanceOf = function(o, type) {
                return (o && o.hasOwnProperty && (o instanceof type));
            },
            gconf = (typeof YUI_config !== 'undefined') && YUI_config;

        if (!(instanceOf(Y, YUI))) {
            Y = new YUI();
        } else {
            // set up the core environment
            Y._init();

            // YUI.GlobalConfig is a master configuration that might span
            // multiple contexts in a non-browser environment.  It is applied
            // first to all instances in all contexts.
            if (YUI.GlobalConfig) {
                Y.applyConfig(YUI.GlobalConfig);
            }

            // YUI_Config is a page-level config.  It is applied to all
            // instances created on the page.  This is applied after
            // YUI.GlobalConfig, and before the instance level configuration
            // objects.
            if (gconf) {
                Y.applyConfig(gconf);
            }

            // bind the specified additional modules for this instance
            if (!l) {
                Y._setup();
            }
        }

        if (l) {
            // Each instance can accept one or more configuration objects.
            // These are applied after YUI.GlobalConfig and YUI_Config,
            // overriding values set in those config files if there is a '
            // matching property.
            for (; i < l; i++) {
                Y.applyConfig(args[i]);
            }

            Y._setup();
        }

        Y.instanceOf = instanceOf;

        return Y;
    };

(function() {

    var proto, prop,
        VERSION = '3.3.0',
        PERIOD = '.',
        BASE = 'http://yui.yahooapis.com/',
        DOC_LABEL = 'yui3-js-enabled',
        NOOP = function() {},
        SLICE = Array.prototype.slice,
        APPLY_TO_AUTH = { 'io.xdrReady': 1,   // the functions applyTo
                          'io.xdrResponse': 1,   // can call. this should
                          'SWF.eventHandler': 1 }, // be done at build time
        hasWin = (typeof window != 'undefined'),
        win = (hasWin) ? window : null,
        doc = (hasWin) ? win.document : null,
        docEl = doc && doc.documentElement,
        docClass = docEl && docEl.className,
        instances = {},
        time = new Date().getTime(),
        add = function(el, type, fn, capture) {
            if (el && el.addEventListener) {
                el.addEventListener(type, fn, capture);
            } else if (el && el.attachEvent) {
                el.attachEvent('on' + type, fn);
            }
        },
        remove = function(el, type, fn, capture) {
            if (el && el.removeEventListener) {
                // this can throw an uncaught exception in FF
                try {
                    el.removeEventListener(type, fn, capture);
                } catch (ex) {}
            } else if (el && el.detachEvent) {
                el.detachEvent('on' + type, fn);
            }
        },
        handleLoad = function() {
            YUI.Env.windowLoaded = true;
            YUI.Env.DOMReady = true;
            if (hasWin) {
                remove(window, 'load', handleLoad);
            }
        },
        getLoader = function(Y, o) {
            var loader = Y.Env._loader;
            if (loader) {
                loader.ignoreRegistered = false;
                loader.onEnd = null;
                loader.data = null;
                loader.required = [];
                loader.loadType = null;
            } else {
                loader = new Y.Loader(Y.config);
                Y.Env._loader = loader;
            }

            return loader;
        },

        clobber = function(r, s) {
            for (var i in s) {
                if (s.hasOwnProperty(i)) {
                    r[i] = s[i];
                }
            }
        },

        ALREADY_DONE = { success: true };

//  Stamp the documentElement (HTML) with a class of "yui-loaded" to
//  enable styles that need to key off of JS being enabled.
if (docEl && docClass.indexOf(DOC_LABEL) == -1) {
    if (docClass) {
        docClass += ' ';
    }
    docClass += DOC_LABEL;
    docEl.className = docClass;
}

if (VERSION.indexOf('@') > -1) {
    VERSION = '3.2.0'; // dev time hack for cdn test
}

proto = {
    /**
     * Applies a new configuration object to the YUI instance config.
     * This will merge new group/module definitions, and will also
     * update the loader cache if necessary.  Updating Y.config directly
     * will not update the cache.
     * @method applyConfig
     * @param {object} the configuration object.
     * @since 3.2.0
     */
    applyConfig: function(o) {

        o = o || NOOP;

        var attr,
            name,
            // detail,
            config = this.config,
            mods = config.modules,
            groups = config.groups,
            rls = config.rls,
            loader = this.Env._loader;

        for (name in o) {
            if (o.hasOwnProperty(name)) {
                attr = o[name];
                if (mods && name == 'modules') {
                    clobber(mods, attr);
                } else if (groups && name == 'groups') {
                    clobber(groups, attr);
                } else if (rls && name == 'rls') {
                    clobber(rls, attr);
                } else if (name == 'win') {
                    config[name] = attr.contentWindow || attr;
                    config.doc = config[name].document;
                } else if (name == '_yuid') {
                    // preserve the guid
                } else {
                    config[name] = attr;
                }
            }
        }

        if (loader) {
            loader._config(o);
        }
    },

    _config: function(o) {
        this.applyConfig(o);
    },

    /**
     * Initialize this YUI instance
     * @private
     */
    _init: function() {
        var filter,
            Y = this,
            G_ENV = YUI.Env,
            Env = Y.Env,
            prop;

        /**
         * The version number of the YUI instance.
         * @property version
         * @type string
         */
        Y.version = VERSION;

        if (!Env) {
            Y.Env = {
                mods: {}, // flat module map
                versions: {}, // version module map
                base: BASE,
                cdn: BASE + VERSION + '/build/',
                // bootstrapped: false,
                _idx: 0,
                _used: {},
                _attached: {},
                _yidx: 0,
                _uidx: 0,
                _guidp: 'y',
                _loaded: {},
                serviced: {},
                getBase: G_ENV && G_ENV.getBase ||

    function(srcPattern, comboPattern) {
        var b, nodes, i, src, match;
        // get from querystring
        nodes = (doc && doc.getElementsByTagName('script')) || [];
        for (i = 0; i < nodes.length; i = i + 1) {
            src = nodes[i].src;
            if (src) {

                match = src.match(srcPattern);
                b = match && match[1];
                if (b) {
                    // this is to set up the path to the loader.  The file
                    // filter for loader should match the yui include.
                    filter = match[2];

                    if (filter) {
                        match = filter.indexOf('js');

                        if (match > -1) {
                            filter = filter.substr(0, match);
                        }
                    }

                    // extract correct path for mixed combo urls
                    // http://yuilibrary.com/projects/yui3/ticket/2528423
                    match = src.match(comboPattern);
                    if (match && match[3]) {
                        b = match[1] + match[3];
                    }

                    break;
                }
            }
        }

        // use CDN default
        return b || Env.cdn;
    }
            };

            Env = Y.Env;

            Env._loaded[VERSION] = {};

            if (G_ENV && Y !== YUI) {
                Env._yidx = ++G_ENV._yidx;
                Env._guidp = ('yui_' + VERSION + '_' +
                             Env._yidx + '_' + time).replace(/\./g, '_');
            } else if (YUI._YUI) {

                G_ENV = YUI._YUI.Env;
                Env._yidx += G_ENV._yidx;
                Env._uidx += G_ENV._uidx;

                for (prop in G_ENV) {
                    if (!(prop in Env)) {
                        Env[prop] = G_ENV[prop];
                    }
                }

                delete YUI._YUI;
            }

            Y.id = Y.stamp(Y);
            instances[Y.id] = Y;

        }

        Y.constructor = YUI;

        // configuration defaults
        Y.config = Y.config || {
            win: win,
            doc: doc,
            debug: true,
            useBrowserConsole: true,
            throwFail: true,
            bootstrap: true,
            cacheUse: true,
            fetchCSS: true
        };

        Y.config.base = YUI.config.base ||
            Y.Env.getBase(/^(.*)yui\/yui([\.\-].*)js(\?.*)?$/,
                          /^(.*\?)(.*\&)(.*)yui\/yui[\.\-].*js(\?.*)?$/);

        if (!filter || (!('-min.-debug.').indexOf(filter))) {
            filter = '-min.';
        }

        Y.config.loaderPath = YUI.config.loaderPath ||
            'loader/loader' + (filter || '-min.') + 'js';

    },

    /**
     * Finishes the instance setup. Attaches whatever modules were defined
     * when the yui modules was registered.
     * @method _setup
     * @private
     */
    _setup: function(o) {
        var i, Y = this,
            core = [],
            mods = YUI.Env.mods,
            extras = Y.config.core || ['get',
                                        'rls',
                                        'intl-base',
                                        'loader',
                                        'yui-log',
                                        'yui-later',
                                        'yui-throttle'];

        for (i = 0; i < extras.length; i++) {
            if (mods[extras[i]]) {
                core.push(extras[i]);
            }
        }

        Y._attach(['yui-base']);
        Y._attach(core);

    },

    /**
     * Executes a method on a YUI instance with
     * the specified id if the specified method is whitelisted.
     * @method applyTo
     * @param id {string} the YUI instance id.
     * @param method {string} the name of the method to exectute.
     * Ex: 'Object.keys'.
     * @param args {Array} the arguments to apply to the method.
     * @return {object} the return value from the applied method or null.
     */
    applyTo: function(id, method, args) {
        if (!(method in APPLY_TO_AUTH)) {
            this.log(method + ': applyTo not allowed', 'warn', 'yui');
            return null;
        }

        var instance = instances[id], nest, m, i;
        if (instance) {
            nest = method.split('.');
            m = instance;
            for (i = 0; i < nest.length; i = i + 1) {
                m = m[nest[i]];
                if (!m) {
                    this.log('applyTo not found: ' + method, 'warn', 'yui');
                }
            }
            return m.apply(instance, args);
        }

        return null;
    },

    /**
     * Registers a module with the YUI global.  The easiest way to create a
     * first-class YUI module is to use the YUI component build tool.
     *
     * http://yuilibrary.com/projects/builder
     *
     * The build system will produce the YUI.add wrapper for you module, along
     * with any configuration info required for the module.
     * @method add
     * @param name {string} module name.
     * @param fn {Function} entry point into the module that
     * is used to bind module to the YUI instance.
     * @param version {string} version string.
     * @param details {object} optional config data:
     * requires: features that must be present before this module can be
     * attached.
     * optional: optional features that should be present if loadOptional
     * is defined.  Note: modules are not often loaded this way in YUI 3,
     * but this field is still useful to inform the user that certain
     * features in the component will require additional dependencies.
     * use: features that are included within this module which need to
     * be attached automatically when this module is attached.  This
     * supports the YUI 3 rollup system -- a module with submodules
     * defined will need to have the submodules listed in the 'use'
     * config.  The YUI component build tool does this for you.
     * @return {YUI} the YUI instance.
     *
     */
    add: function(name, fn, version, details) {
        details = details || {};
        var env = YUI.Env,
            mod = {
                name: name,
                fn: fn,
                version: version,
                details: details
            },
            loader,
            i, versions = env.versions;

        env.mods[name] = mod;
        versions[version] = versions[version] || {};
        versions[version][name] = mod;

        for (i in instances) {
            if (instances.hasOwnProperty(i)) {
                loader = instances[i].Env._loader;
                if (loader) {
                    if (!loader.moduleInfo[name]) {
                        loader.addModule(details, name);
                    }
                }
            }
        }

        return this;
    },

    /**
     * Executes the function associated with each required
     * module, binding the module to the YUI instance.
     * @method _attach
     * @private
     */
    _attach: function(r, fromLoader) {
        var i, name, mod, details, req, use, after,
            mods = YUI.Env.mods,
            Y = this, j,
            done = Y.Env._attached,
            len = r.length, loader;


        for (i = 0; i < len; i++) {
            if (!done[r[i]]) {
                name = r[i];
                mod = mods[name];
                if (!mod) {
                    loader = Y.Env._loader;


                    if (!loader || !loader.moduleInfo[name]) {
                        Y.message('NOT loaded: ' + name, 'warn', 'yui');
                    }
                } else {
                    done[name] = true;
                    details = mod.details;
                    req = details.requires;
                    use = details.use;
                    after = details.after;

                    if (req) {
                        for (j = 0; j < req.length; j++) {
                            if (!done[req[j]]) {
                                if (!Y._attach(req)) {
                                    return false;
                                }
                                break;
                            }
                        }
                    }

                    if (after) {
                        for (j = 0; j < after.length; j++) {
                            if (!done[after[j]]) {
                                if (!Y._attach(after)) {
                                    return false;
                                }
                                break;
                            }
                        }
                    }

                    if (use) {
                        for (j = 0; j < use.length; j++) {
                            if (!done[use[j]]) {
                                if (!Y._attach(use)) {
                                    return false;
                                }
                                break;
                            }
                        }
                    }

                    if (mod.fn) {
                        try {
                            mod.fn(Y, name);
                        } catch (e) {
                            Y.error('Attach error: ' + name, e, name);
                            return false;
                        }
                    }

                }
            }
        }

        return true;
    },

    /**
     * Attaches one or more modules to the YUI instance.  When this
     * is executed, the requirements are analyzed, and one of
     * several things can happen:
     *
     * - All requirements are available on the page --  The modules
     *   are attached to the instance.  If supplied, the use callback
     *   is executed synchronously.
     *
     * - Modules are missing, the Get utility is not available OR
     *   the 'bootstrap' config is false -- A warning is issued about
     *   the missing modules and all available modules are attached.
     *
     * - Modules are missing, the Loader is not available but the Get
     *   utility is and boostrap is not false -- The loader is bootstrapped
     *   before doing the following....
     *
     * - Modules are missing and the Loader is available -- The loader
     *   expands the dependency tree and fetches missing modules.  When
     *   the loader is finshed the callback supplied to use is executed
     *   asynchronously.
     *
     * @param modules* {string} 1-n modules to bind (uses arguments array).
     * @param *callback {function} callback function executed when
     * the instance has the required functionality.  If included, it
     * must be the last parameter.
     * <code>
     * // loads and attaches drag and drop and its dependencies
     * YUI().use('dd', function(Y) &#123;&#125);
     * // attaches all modules that are available on the page
     * YUI().use('*', function(Y) &#123;&#125);
     * // intrinsic YUI gallery support (since 3.1.0)
     * YUI().use('gallery-yql', function(Y) &#123;&#125);
     * // intrinsic YUI 2in3 support (since 3.1.0)
     * YUI().use('yui2-datatable', function(Y) &#123;&#125);.
     * </code>
     *
     * @return {YUI} the YUI instance.
     */
    use: function() {
        var args = SLICE.call(arguments, 0),
            callback = args[args.length - 1],
            Y = this,
            key;

        // The last argument supplied to use can be a load complete callback
        if (Y.Lang.isFunction(callback)) {
            args.pop();
        } else {
            callback = null;
        }

        if (Y._loading) {
            Y._useQueue = Y._useQueue || new Y.Queue();
            Y._useQueue.add([args, callback]);
        } else {
            key = args.join();

            if (Y.config.cacheUse && Y.Env.serviced[key]) {
                Y._notify(callback, ALREADY_DONE, args);
            } else {
                Y._use(args, function(Y, response) {
                    if (Y.config.cacheUse) {
                        Y.Env.serviced[key] = true;
                    }
                    Y._notify(callback, response, args);
                });
            }
        }

        return Y;
    },

    _notify: function(callback, response, args) {
        if (!response.success && this.config.loadErrorFn) {
            this.config.loadErrorFn.call(this, this, callback, response, args);
        } else if (callback) {
            try {
                callback(this, response);
            } catch (e) {
                this.error('use callback error', e, args);
            }
        }
    },

    _use: function(args, callback) {

        if (!this.Array) {
            this._attach(['yui-base']);
        }

        var len, loader, handleBoot,
            Y = this,
            G_ENV = YUI.Env,
            mods = G_ENV.mods,
            Env = Y.Env,
            used = Env._used,
            queue = G_ENV._loaderQueue,
            firstArg = args[0],
            YArray = Y.Array,
            config = Y.config,
            boot = config.bootstrap,
            missing = [],
            r = [],
            ret = true,
            fetchCSS = config.fetchCSS,
            process = function(names, skip) {

                if (!names.length) {
                    return;
                }

                YArray.each(names, function(name) {

                    // add this module to full list of things to attach
                    if (!skip) {
                        r.push(name);
                    }

                    // only attach a module once
                    if (used[name]) {
                        return;
                    }

                    var m = mods[name], req, use;

                    if (m) {
                        used[name] = true;
                        req = m.details.requires;
                        use = m.details.use;
                    } else {
                        // CSS files don't register themselves, see if it has
                        // been loaded
                        if (!G_ENV._loaded[VERSION][name]) {
                            missing.push(name);
                        } else {
                            used[name] = true; // probably css
                        }
                    }

                    // make sure requirements are attached
                    if (req && req.length) {
                        process(req);
                    }

                    // make sure we grab the submodule dependencies too
                    if (use && use.length) {
                        process(use, 1);
                    }
                });
            },

            handleLoader = function(fromLoader) {
                var response = fromLoader || {
                        success: true,
                        msg: 'not dynamic'
                    },
                    redo, origMissing,
                    ret = true,
                    data = response.data;


                Y._loading = false;

                if (data) {
                    origMissing = missing;
                    missing = [];
                    r = [];
                    process(data);
                    redo = missing.length;
                    if (redo) {
                        if (missing.sort().join() ==
                                origMissing.sort().join()) {
                            redo = false;
                        }
                    }
                }

                if (redo && data) {
                    Y._loading = false;
                    Y._use(args, function() {
                        if (Y._attach(data)) {
                            Y._notify(callback, response, data);
                        }
                    });
                } else {
                    if (data) {
                        ret = Y._attach(data);
                    }
                    if (ret) {
                        Y._notify(callback, response, args);
                    }
                }

                if (Y._useQueue && Y._useQueue.size() && !Y._loading) {
                    Y._use.apply(Y, Y._useQueue.next());
                }

            };


        // YUI().use('*'); // bind everything available
        if (firstArg === '*') {
            ret = Y._attach(Y.Object.keys(mods));
            if (ret) {
                handleLoader();
            }
            return Y;
        }


        // use loader to expand dependencies and sort the
        // requirements if it is available.
        if (boot && Y.Loader && args.length) {
            loader = getLoader(Y);
            loader.require(args);
            loader.ignoreRegistered = true;
            loader.calculate(null, (fetchCSS) ? null : 'js');
            args = loader.sorted;
        }

        // process each requirement and any additional requirements
        // the module metadata specifies
        process(args);

        len = missing.length;

        if (len) {
            missing = Y.Object.keys(YArray.hash(missing));
            len = missing.length;
        }

        // dynamic load
        if (boot && len && Y.Loader) {
            Y._loading = true;
            loader = getLoader(Y);
            loader.onEnd = handleLoader;
            loader.context = Y;
            loader.data = args;
            loader.ignoreRegistered = false;
            loader.require(args);
            loader.insert(null, (fetchCSS) ? null : 'js');
            // loader.partial(missing, (fetchCSS) ? null : 'js');

        } else if (len && Y.config.use_rls) {

            // server side loader service
            Y.Get.script(Y._rls(args), {
                onEnd: function(o) {
                    handleLoader(o);
                },
                data: args
            });

        } else if (boot && len && Y.Get && !Env.bootstrapped) {

            Y._loading = true;

            handleBoot = function() {
                Y._loading = false;
                queue.running = false;
                Env.bootstrapped = true;
                if (Y._attach(['loader'])) {
                    Y._use(args, callback);
                }
            };

            if (G_ENV._bootstrapping) {
                queue.add(handleBoot);
            } else {
                G_ENV._bootstrapping = true;
                Y.Get.script(config.base + config.loaderPath, {
                    onEnd: handleBoot
                });
            }

        } else {
            ret = Y._attach(args);
            if (ret) {
                handleLoader();
            }
        }

        return Y;
    },


    /**
     * Returns the namespace specified and creates it if it doesn't exist
     * <pre>
     * YUI.namespace("property.package");
     * YUI.namespace("YAHOO.property.package");
     * </pre>
     * Either of the above would create YUI.property, then
     * YUI.property.package (YAHOO is scrubbed out, this is
     * to remain compatible with YUI2)
     *
     * Be careful when naming packages. Reserved words may work in some browsers
     * and not others. For instance, the following will fail in Safari:
     * <pre>
     * YUI.namespace("really.long.nested.namespace");
     * </pre>
     * This fails because "long" is a future reserved word in ECMAScript
     *
     * @method namespace
     * @param  {string*} arguments 1-n namespaces to create.
     * @return {object}  A reference to the last namespace object created.
     */
    namespace: function() {
        var a = arguments, o = this, i = 0, j, d, arg;
        for (; i < a.length; i++) {
            // d = ('' + a[i]).split('.');
            arg = a[i];
            if (arg.indexOf(PERIOD)) {
                d = arg.split(PERIOD);
                for (j = (d[0] == 'YAHOO') ? 1 : 0; j < d.length; j++) {
                    o[d[j]] = o[d[j]] || {};
                    o = o[d[j]];
                }
            } else {
                o[arg] = o[arg] || {};
            }
        }
        return o;
    },

    // this is replaced if the log module is included
    log: NOOP,
    message: NOOP,

    /**
     * Report an error.  The reporting mechanism is controled by
     * the 'throwFail' configuration attribute.  If throwFail is
     * not specified, the message is written to the Logger, otherwise
     * a JS error is thrown
     * @method error
     * @param msg {string} the error message.
     * @param e {Error|string} Optional JS error that was caught, or an error string.
     * @param data Optional additional info
     * and throwFail is specified, this error will be re-thrown.
     * @return {YUI} this YUI instance.
     */
    error: function(msg, e, data) {

        var Y = this, ret;

        if (Y.config.errorFn) {
            ret = Y.config.errorFn.apply(Y, arguments);
        }

        if (Y.config.throwFail && !ret) {
            throw (e || new Error(msg));
        } else {
            Y.message(msg, 'error'); // don't scrub this one
        }

        return Y;
    },

    /**
     * Generate an id that is unique among all YUI instances
     * @method guid
     * @param pre {string} optional guid prefix.
     * @return {string} the guid.
     */
    guid: function(pre) {
        var id = this.Env._guidp + (++this.Env._uidx);
        return (pre) ? (pre + id) : id;
    },

    /**
     * Returns a guid associated with an object.  If the object
     * does not have one, a new one is created unless readOnly
     * is specified.
     * @method stamp
     * @param o The object to stamp.
     * @param readOnly {boolean} if true, a valid guid will only
     * be returned if the object has one assigned to it.
     * @return {string} The object's guid or null.
     */
    stamp: function(o, readOnly) {
        var uid;
        if (!o) {
            return o;
        }

        // IE generates its own unique ID for dom nodes
        // The uniqueID property of a document node returns a new ID
        if (o.uniqueID && o.nodeType && o.nodeType !== 9) {
            uid = o.uniqueID;
        } else {
            uid = (typeof o === 'string') ? o : o._yuid;
        }

        if (!uid) {
            uid = this.guid();
            if (!readOnly) {
                try {
                    o._yuid = uid;
                } catch (e) {
                    uid = null;
                }
            }
        }
        return uid;
    },

    /**
     * Destroys the YUI instance
     * @method destroy
     * @since 3.3.0
     */
    destroy: function() {
        var Y = this;
        if (Y.Event) {
            Y.Event._unload();
        }
        delete instances[Y.id];
        delete Y.Env;
        delete Y.config;
    }

    /**
     * instanceof check for objects that works around
     * memory leak in IE when the item tested is
     * window/document
     * @method instanceOf
     * @since 3.3.0
     */
};



    YUI.prototype = proto;

    // inheritance utilities are not available yet
    for (prop in proto) {
        if (proto.hasOwnProperty(prop)) {
            YUI[prop] = proto[prop];
        }
    }

    // set up the environment
    YUI._init();

    if (hasWin) {
        // add a window load event at load time so we can capture
        // the case where it fires before dynamic loading is
        // complete.
        add(window, 'load', handleLoad);
    } else {
        handleLoad();
    }

    YUI.Env.add = add;
    YUI.Env.remove = remove;

    /*global exports*/
    // Support the CommonJS method for exporting our single global
    if (typeof exports == 'object') {
        exports.YUI = YUI;
    }

}());


/**
 * The config object contains all of the configuration options for
 * the YUI instance.  This object is supplied by the implementer
 * when instantiating a YUI instance.  Some properties have default
 * values if they are not supplied by the implementer.  This should
 * not be updated directly because some values are cached.  Use
 * applyConfig() to update the config object on a YUI instance that
 * has already been configured.
 *
 * @class config
 * @static
 */

/**
 * Allows the YUI seed file to fetch the loader component and library
 * metadata to dynamically load additional dependencies.
 *
 * @property bootstrap
 * @type boolean
 * @default true
 */

/**
 * Log to the browser console if debug is on and the browser has a
 * supported console.
 *
 * @property useBrowserConsole
 * @type boolean
 * @default true
 */

/**
 * A hash of log sources that should be logged.  If specified, only
 * log messages from these sources will be logged.
 *
 * @property logInclude
 * @type object
 */

/**
 * A hash of log sources that should be not be logged.  If specified,
 * all sources are logged if not on this list.
 *
 * @property logExclude
 * @type object
 */

/**
 * Set to true if the yui seed file was dynamically loaded in
 * order to bootstrap components relying on the window load event
 * and the 'domready' custom event.
 *
 * @property injected
 * @type boolean
 * @default false
 */

/**
 * If throwFail is set, Y.error will generate or re-throw a JS Error.
 * Otherwise the failure is logged.
 *
 * @property throwFail
 * @type boolean
 * @default true
 */

/**
 * The window/frame that this instance should operate in.
 *
 * @property win
 * @type Window
 * @default the window hosting YUI
 */

/**
 * The document associated with the 'win' configuration.
 *
 * @property doc
 * @type Document
 * @default the document hosting YUI
 */

/**
 * A list of modules that defines the YUI core (overrides the default).
 *
 * @property core
 * @type string[]
 */

/**
 * A list of languages in order of preference. This list is matched against
 * the list of available languages in modules that the YUI instance uses to
 * determine the best possible localization of language sensitive modules.
 * Languages are represented using BCP 47 language tags, such as "en-GB" for
 * English as used in the United Kingdom, or "zh-Hans-CN" for simplified
 * Chinese as used in China. The list can be provided as a comma-separated
 * list or as an array.
 *
 * @property lang
 * @type string|string[]
 */

/**
 * The default date format
 * @property dateFormat
 * @type string
 * @deprecated use configuration in DataType.Date.format() instead.
 */

/**
 * The default locale
 * @property locale
 * @type string
 * @deprecated use config.lang instead.
 */

/**
 * The default interval when polling in milliseconds.
 * @property pollInterval
 * @type int
 * @default 20
 */

/**
 * The number of dynamic nodes to insert by default before
 * automatically removing them.  This applies to script nodes
 * because remove the node will not make the evaluated script
 * unavailable.  Dynamic CSS is not auto purged, because removing
 * a linked style sheet will also remove the style definitions.
 * @property purgethreshold
 * @type int
 * @default 20
 */

/**
 * The default interval when polling in milliseconds.
 * @property windowResizeDelay
 * @type int
 * @default 40
 */

/**
 * Base directory for dynamic loading
 * @property base
 * @type string
 */

/*
 * The secure base dir (not implemented)
 * For dynamic loading.
 * @property secureBase
 * @type string
 */

/**
 * The YUI combo service base dir. Ex: http://yui.yahooapis.com/combo?
 * For dynamic loading.
 * @property comboBase
 * @type string
 */

/**
 * The root path to prepend to module path for the combo service.
 * Ex: 3.0.0b1/build/
 * For dynamic loading.
 * @property root
 * @type string
 */

/**
 * A filter to apply to result urls.  This filter will modify the default
 * path for all modules.  The default path for the YUI library is the
 * minified version of the files (e.g., event-min.js).  The filter property
 * can be a predefined filter or a custom filter.  The valid predefined
 * filters are:
 * <dl>
 *  <dt>DEBUG</dt>
 *  <dd>Selects the debug versions of the library (e.g., event-debug.js).
 *      This option will automatically include the Logger widget</dd>
 *  <dt>RAW</dt>
 *  <dd>Selects the non-minified version of the library (e.g., event.js).</dd>
 * </dl>
 * You can also define a custom filter, which must be an object literal
 * containing a search expression and a replace string:
 * <pre>
 *  myFilter: &#123;
 *      'searchExp': "-min\\.js",
 *      'replaceStr': "-debug.js"
 *  &#125;
 * </pre>
 *
 * For dynamic loading.
 *
 * @property filter
 * @type string|object
 */

/**
 * The 'skin' config let's you configure application level skin
 * customizations.  It contains the following attributes which
 * can be specified to override the defaults:
 *
 *      // The default skin, which is automatically applied if not
 *      // overriden by a component-specific skin definition.
 *      // Change this in to apply a different skin globally
 *      defaultSkin: 'sam',
 *
 *      // This is combined with the loader base property to get
 *      // the default root directory for a skin.
 *      base: 'assets/skins/',
 *
 *      // Any component-specific overrides can be specified here,
 *      // making it possible to load different skins for different
 *      // components.  It is possible to load more than one skin
 *      // for a given component as well.
 *      overrides: {
 *          slider: ['capsule', 'round']
 *      }
 *
 * For dynamic loading.
 *
 *  @property skin
 */

/**
 * Hash of per-component filter specification.  If specified for a given
 * component, this overrides the filter config.
 *
 * For dynamic loading.
 *
 * @property filters
 */

/**
 * Use the YUI combo service to reduce the number of http connections
 * required to load your dependencies.  Turning this off will
 * disable combo handling for YUI and all module groups configured
 * with a combo service.
 *
 * For dynamic loading.
 *
 * @property combine
 * @type boolean
 * @default true if 'base' is not supplied, false if it is.
 */

/**
 * A list of modules that should never be dynamically loaded
 *
 * @property ignore
 * @type string[]
 */

/**
 * A list of modules that should always be loaded when required, even if already
 * present on the page.
 *
 * @property force
 * @type string[]
 */

/**
 * Node or id for a node that should be used as the insertion point for new
 * nodes.  For dynamic loading.
 *
 * @property insertBefore
 * @type string
 */

/**
 * Object literal containing attributes to add to dynamically loaded script
 * nodes.
 * @property jsAttributes
 * @type string
 */

/**
 * Object literal containing attributes to add to dynamically loaded link
 * nodes.
 * @property cssAttributes
 * @type string
 */

/**
 * Number of milliseconds before a timeout occurs when dynamically
 * loading nodes. If not set, there is no timeout.
 * @property timeout
 * @type int
 */

/**
 * Callback for the 'CSSComplete' event.  When dynamically loading YUI
 * components with CSS, this property fires when the CSS is finished
 * loading but script loading is still ongoing.  This provides an
 * opportunity to enhance the presentation of a loading page a little
 * bit before the entire loading process is done.
 *
 * @property onCSS
 * @type function
 */

/**
 * A hash of module definitions to add to the list of YUI components.
 * These components can then be dynamically loaded side by side with
 * YUI via the use() method. This is a hash, the key is the module
 * name, and the value is an object literal specifying the metdata
 * for the module.  * See Loader.addModule for the supported module
 * metadata fields.  Also @see groups, which provides a way to
 * configure the base and combo spec for a set of modules.
 * <code>
 * modules: {
 * &nbsp; mymod1: {
 * &nbsp;   requires: ['node'],
 * &nbsp;   fullpath: 'http://myserver.mydomain.com/mymod1/mymod1.js'
 * &nbsp; },
 * &nbsp; mymod2: {
 * &nbsp;   requires: ['mymod1'],
 * &nbsp;   fullpath: 'http://myserver.mydomain.com/mymod2/mymod2.js'
 * &nbsp; }
 * }
 * </code>
 *
 * @property modules
 * @type object
 */

/**
 * A hash of module group definitions.  It for each group you
 * can specify a list of modules and the base path and
 * combo spec to use when dynamically loading the modules.  @see
 * @see modules for the details about the modules part of the
 * group definition.
 * <code>
 * &nbsp; groups: {
 * &nbsp;     yui2: {
 * &nbsp;         // specify whether or not this group has a combo service
 * &nbsp;         combine: true,
 * &nbsp;
 * &nbsp;         // the base path for non-combo paths
 * &nbsp;         base: 'http://yui.yahooapis.com/2.8.0r4/build/',
 * &nbsp;
 * &nbsp;         // the path to the combo service
 * &nbsp;         comboBase: 'http://yui.yahooapis.com/combo?',
 * &nbsp;
 * &nbsp;         // a fragment to prepend to the path attribute when
 * &nbsp;         // when building combo urls
 * &nbsp;         root: '2.8.0r4/build/',
 * &nbsp;
 * &nbsp;         // the module definitions
 * &nbsp;         modules:  {
 * &nbsp;             yui2_yde: {
 * &nbsp;                 path: "yahoo-dom-event/yahoo-dom-event.js"
 * &nbsp;             },
 * &nbsp;             yui2_anim: {
 * &nbsp;                 path: "animation/animation.js",
 * &nbsp;                 requires: ['yui2_yde']
 * &nbsp;             }
 * &nbsp;         }
 * &nbsp;     }
 * &nbsp; }
 * </code>
 * @property modules
 * @type object
 */

/**
 * The loader 'path' attribute to the loader itself.  This is combined
 * with the 'base' attribute to dynamically load the loader component
 * when boostrapping with the get utility alone.
 *
 * @property loaderPath
 * @type string
 * @default loader/loader-min.js
 */

/**
 * Specifies whether or not YUI().use(...) will attempt to load CSS
 * resources at all.  Any truthy value will cause CSS dependencies
 * to load when fetching script.  The special value 'force' will
 * cause CSS dependencies to be loaded even if no script is needed.
 *
 * @property fetchCSS
 * @type boolean|string
 * @default true
 */

/**
 * The default gallery version to build gallery module urls
 * @property gallery
 * @type string
 * @since 3.1.0
 */

/**
 * The default YUI 2 version to build yui2 module urls.  This is for
 * intrinsic YUI 2 support via the 2in3 project.  Also @see the '2in3'
 * config for pulling different revisions of the wrapped YUI 2
 * modules.
 * @since 3.1.0
 * @property yui2
 * @type string
 * @default 2.8.1
 */

/**
 * The 2in3 project is a deployment of the various versions of YUI 2
 * deployed as first-class YUI 3 modules.  Eventually, the wrapper
 * for the modules will change (but the underlying YUI 2 code will
 * be the same), and you can select a particular version of
 * the wrapper modules via this config.
 * @since 3.1.0
 * @property 2in3
 * @type string
 * @default 1
 */

/**
 * Alternative console log function for use in environments without
 * a supported native console.  The function is executed in the
 * YUI instance context.
 * @since 3.1.0
 * @property logFn
 * @type Function
 */

/**
 * A callback to execute when Y.error is called.  It receives the
 * error message and an javascript error object if Y.error was
 * executed because a javascript error was caught.  The function
 * is executed in the YUI instance context.
 *
 * @since 3.2.0
 * @property errorFn
 * @type Function
 */

/**
 * A callback to execute when the loader fails to load one or
 * more resource.  This could be because of a script load
 * failure.  It can also fail if a javascript module fails
 * to register itself, but only when the 'requireRegistration'
 * is true.  If this function is defined, the use() callback will
 * only be called when the loader succeeds, otherwise it always
 * executes unless there was a javascript error when attaching
 * a module.
 *
 * @since 3.3.0
 * @property loadErrorFn
 * @type Function
 */

/**
 * When set to true, the YUI loader will expect that all modules
 * it is responsible for loading will be first-class YUI modules
 * that register themselves with the YUI global.  If this is
 * set to true, loader will fail if the module registration fails
 * to happen after the script is loaded.
 *
 * @since 3.3.0
 * @property requireRegistration
 * @type boolean
 * @default false
 */

/**
 * Cache serviced use() requests.
 * @since 3.3.0
 * @property cacheUse
 * @type boolean
 * @default true
 */

/**
 * The parameter defaults for the remote loader service.
 * Requires the rls submodule.  The properties that are
 * supported:
 * <pre>
 * m: comma separated list of module requirements.  This
 *    must be the param name even for custom implemetations.
 * v: the version of YUI to load.  Defaults to the version
 *    of YUI that is being used.
 * gv: the version of the gallery to load (@see the gallery config)
 * env: comma separated list of modules already on the page.
 *      this must be the param name even for custom implemetations.
 * lang: the languages supported on the page (@see the lang config)
 * '2in3v':  the version of the 2in3 wrapper to use (@see the 2in3 config).
 * '2v': the version of yui2 to use in the yui 2in3 wrappers
 *       (@see the yui2 config)
 * filt: a filter def to apply to the urls (@see the filter config).
 * filts: a list of custom filters to apply per module
 *        (@see the filters config).
 * tests: this is a map of conditional module test function id keys
 * with the values of 1 if the test passes, 0 if not.  This must be
 * the name of the querystring param in custom templates.
 *</pre>
 *
 * @since 3.2.0
 * @property rls
 */

/**
 * The base path to the remote loader service
 *
 * @since 3.2.0
 * @property rls_base
 */

/**
 * The template to use for building the querystring portion
 * of the remote loader service url.  The default is determined
 * by the rls config -- each property that has a value will be
 * represented.
 *
 * ex: m={m}&v={v}&env={env}&lang={lang}&filt={filt}&tests={tests}
 *
 *
 * @since 3.2.0
 * @property rls_tmpl
 */

/**
 * Configure the instance to use a remote loader service instead of
 * the client loader.
 *
 * @since 3.2.0
 * @property use_rls
 */
YUI.add('yui-base', function(Y) {

/*
 * YUI stub
 * @module yui
 * @submodule yui-base
 */
/**
 * The YUI module contains the components required for building the YUI
 * seed file.  This includes the script loading mechanism, a simple queue,
 * and the core utilities for the library.
 * @module yui
 * @submodule yui-base
 */

/**
 * Provides the language utilites and extensions used by the library
 * @class Lang
 * @static
 */
Y.Lang = Y.Lang || {};

var L = Y.Lang,

ARRAY = 'array',
BOOLEAN = 'boolean',
DATE = 'date',
ERROR = 'error',
FUNCTION = 'function',
NUMBER = 'number',
NULL = 'null',
OBJECT = 'object',
REGEX = 'regexp',
STRING = 'string',
STRING_PROTO = String.prototype,
TOSTRING = Object.prototype.toString,
UNDEFINED = 'undefined',

TYPES = {
    'undefined' : UNDEFINED,
    'number' : NUMBER,
    'boolean' : BOOLEAN,
    'string' : STRING,
    '[object Function]' : FUNCTION,
    '[object RegExp]' : REGEX,
    '[object Array]' : ARRAY,
    '[object Date]' : DATE,
    '[object Error]' : ERROR
},

TRIMREGEX = /^\s+|\s+$/g,
EMPTYSTRING = '',
SUBREGEX = /\{\s*([^\|\}]+?)\s*(?:\|([^\}]*))?\s*\}/g;

/**
 * Determines whether or not the provided item is an array.
 * Returns false for array-like collections such as the
 * function arguments collection or HTMLElement collection
 * will return false.  Use <code>Y.Array.test</code> if you
 * want to test for an array-like collection.
 * @method isArray
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is an array.
 */
// L.isArray = Array.isArray || function(o) {
//     return L.type(o) === ARRAY;
// };

L.isArray = function(o) {
    return L.type(o) === ARRAY;
};

/**
 * Determines whether or not the provided item is a boolean.
 * @method isBoolean
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is a boolean.
 */
L.isBoolean = function(o) {
    return typeof o === BOOLEAN;
};

/**
 * <p>
 * Determines whether or not the provided item is a function.
 * Note: Internet Explorer thinks certain functions are objects:
 * </p>
 *
 * <pre>
 * var obj = document.createElement("object");
 * Y.Lang.isFunction(obj.getAttribute) // reports false in IE
 * &nbsp;
 * var input = document.createElement("input"); // append to body
 * Y.Lang.isFunction(input.focus) // reports false in IE
 * </pre>
 *
 * <p>
 * You will have to implement additional tests if these functions
 * matter to you.
 * </p>
 *
 * @method isFunction
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is a function.
 */
L.isFunction = function(o) {
    return L.type(o) === FUNCTION;
};

/**
 * Determines whether or not the supplied item is a date instance.
 * @method isDate
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is a date.
 */
L.isDate = function(o) {
    // return o instanceof Date;
    return L.type(o) === DATE && o.toString() !== 'Invalid Date' && !isNaN(o);
};

/**
 * Determines whether or not the provided item is null.
 * @method isNull
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is null.
 */
L.isNull = function(o) {
    return o === null;
};

/**
 * Determines whether or not the provided item is a legal number.
 * @method isNumber
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is a number.
 */
L.isNumber = function(o) {
    return typeof o === NUMBER && isFinite(o);
};

/**
 * Determines whether or not the provided item is of type object
 * or function. Note that arrays are also objects, so
 * <code>Y.Lang.isObject([]) === true</code>.
 * @method isObject
 * @static
 * @param o The object to test.
 * @param failfn {boolean} fail if the input is a function.
 * @return {boolean} true if o is an object.
 */
L.isObject = function(o, failfn) {
    var t = typeof o;
    return (o && (t === OBJECT ||
        (!failfn && (t === FUNCTION || L.isFunction(o))))) || false;
};

/**
 * Determines whether or not the provided item is a string.
 * @method isString
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is a string.
 */
L.isString = function(o) {
    return typeof o === STRING;
};

/**
 * Determines whether or not the provided item is undefined.
 * @method isUndefined
 * @static
 * @param o The object to test.
 * @return {boolean} true if o is undefined.
 */
L.isUndefined = function(o) {
    return typeof o === UNDEFINED;
};

/**
 * Returns a string without any leading or trailing whitespace.  If
 * the input is not a string, the input will be returned untouched.
 * @method trim
 * @static
 * @param s {string} the string to trim.
 * @return {string} the trimmed string.
 */
L.trim = STRING_PROTO.trim ? function(s) {
    return (s && s.trim) ? s.trim() : s;
} : function (s) {
    try {
        return s.replace(TRIMREGEX, EMPTYSTRING);
    } catch (e) {
        return s;
    }
};

/**
 * Returns a string without any leading whitespace.
 * @method trimLeft
 * @static
 * @param s {string} the string to trim.
 * @return {string} the trimmed string.
 */
L.trimLeft = STRING_PROTO.trimLeft ? function (s) {
    return s.trimLeft();
} : function (s) {
    return s.replace(/^\s+/, '');
};

/**
 * Returns a string without any trailing whitespace.
 * @method trimRight
 * @static
 * @param s {string} the string to trim.
 * @return {string} the trimmed string.
 */
L.trimRight = STRING_PROTO.trimRight ? function (s) {
    return s.trimRight();
} : function (s) {
    return s.replace(/\s+$/, '');
};

/**
 * A convenience method for detecting a legitimate non-null value.
 * Returns false for null/undefined/NaN, true for other values,
 * including 0/false/''
 * @method isValue
 * @static
 * @param o The item to test.
 * @return {boolean} true if it is not null/undefined/NaN || false.
 */
L.isValue = function(o) {
    var t = L.type(o);
    switch (t) {
        case NUMBER:
            return isFinite(o);
        case NULL:
        case UNDEFINED:
            return false;
        default:
            return !!(t);
    }
};

/**
 * <p>
 * Returns a string representing the type of the item passed in.
 * </p>
 *
 * <p>
 * Known issues:
 * </p>
 *
 * <ul>
 *   <li>
 *     <code>typeof HTMLElementCollection</code> returns function in Safari, but
 *     <code>Y.type()</code> reports object, which could be a good thing --
 *     but it actually caused the logic in <code>Y.Lang.isObject</code> to fail.
 *   </li>
 * </ul>
 *
 * @method type
 * @param o the item to test.
 * @return {string} the detected type.
 * @static
 */
L.type = function(o) {
    return TYPES[typeof o] || TYPES[TOSTRING.call(o)] || (o ? OBJECT : NULL);
};

/**
 * Lightweight version of <code>Y.substitute</code>. Uses the same template
 * structure as <code>Y.substitute</code>, but doesn't support recursion,
 * auto-object coersion, or formats.
 * @method sub
 * @param {string} s String to be modified.
 * @param {object} o Object containing replacement values.
 * @return {string} the substitute result.
 * @static
 * @since 3.2.0
 */
L.sub = function(s, o) {
    return ((s.replace) ? s.replace(SUBREGEX, function(match, key) {
        return (!L.isUndefined(o[key])) ? o[key] : match;
    }) : s);
};

/**
 * Returns the current time in milliseconds.
 * @method now
 * @return {int} the current date
 * @since 3.3.0
 */
L.now = Date.now || function () {
  return new Date().getTime();
};

/**
 * The YUI module contains the components required for building the YUI seed
 * file.  This includes the script loading mechanism, a simple queue, and the
 * core utilities for the library.
 * @module yui
 * @submodule yui-base
 */


var Native = Array.prototype, LENGTH = 'length',

/**
 * Adds the following array utilities to the YUI instance.  Additional
 * array helpers can be found in the collection component.
 * @class Array
 */

/**
 * Y.Array(o) returns an array:
 * - Arrays are return unmodified unless the start position is specified.
 * - "Array-like" collections (@see Array.test) are converted to arrays
 * - For everything else, a new array is created with the input as the sole
 *   item.
 * - The start position is used if the input is or is like an array to return
 *   a subset of the collection.
 *
 *   @todo this will not automatically convert elements that are also
 *   collections such as forms and selects.  Passing true as the third
 *   param will force a conversion.
 *
 * @method ()
 * @static
 *   @param {object} o the item to arrayify.
 *   @param {int} startIdx if an array or array-like, this is the start index.
 *   @param {boolean} arraylike if true, it forces the array-like fork.  This
 *   can be used to avoid multiple Array.test calls.
 *   @return {Array} the resulting array.
 */
YArray = function(o, startIdx, arraylike) {
    var t = (arraylike) ? 2 : YArray.test(o),
        l, a, start = startIdx || 0;

    if (t) {
        // IE errors when trying to slice HTMLElement collections
        try {
            return Native.slice.call(o, start);
        } catch (e) {
            a = [];
            l = o.length;
            for (; start < l; start++) {
                a.push(o[start]);
            }
            return a;
        }
    } else {
        return [o];
    }
};

Y.Array = YArray;

/**
 * Evaluates the input to determine if it is an array, array-like, or
 * something else.  This is used to handle the arguments collection
 * available within functions, and HTMLElement collections
 *
 * @method test
 * @static
 *
 * @todo current implementation (intenionally) will not implicitly
 * handle html elements that are array-like (forms, selects, etc).
 *
 * @param {object} o the object to test.
 *
 * @return {int} a number indicating the results:
 * 0: Not an array or an array-like collection
 * 1: A real array.
 * 2: array-like collection.
 */
YArray.test = function(o) {
    var r = 0;
    if (Y.Lang.isObject(o)) {
        if (Y.Lang.isArray(o)) {
            r = 1;
        } else {
            try {
                // indexed, but no tagName (element) or alert (window),
                // or functions without apply/call (Safari
                // HTMLElementCollection bug).
                if ((LENGTH in o) && !o.tagName && !o.alert && !o.apply) {
                    r = 2;
                }

            } catch (e) {}
        }
    }
    return r;
};

/**
 * Executes the supplied function on each item in the array.
 * @method each
 * @param {Array} a the array to iterate.
 * @param {Function} f the function to execute on each item.  The
 * function receives three arguments: the value, the index, the full array.
 * @param {object} o Optional context object.
 * @static
 * @return {YUI} the YUI instance.
 */
YArray.each = (Native.forEach) ?
    function(a, f, o) {
        Native.forEach.call(a || [], f, o || Y);
        return Y;
    } :
    function(a, f, o) {
        var l = (a && a.length) || 0, i;
        for (i = 0; i < l; i = i + 1) {
            f.call(o || Y, a[i], i, a);
        }
        return Y;
    };

/**
 * Returns an object using the first array as keys, and
 * the second as values.  If the second array is not
 * provided the value is set to true for each.
 * @method hash
 * @static
 * @param {Array} k keyset.
 * @param {Array} v optional valueset.
 * @return {object} the hash.
 */
YArray.hash = function(k, v) {
    var o = {}, l = k.length, vl = v && v.length, i;
    for (i = 0; i < l; i = i + 1) {
        o[k[i]] = (vl && vl > i) ? v[i] : true;
    }

    return o;
};

/**
 * Returns the index of the first item in the array
 * that contains the specified value, -1 if the
 * value isn't found.
 * @method indexOf
 * @static
 * @param {Array} a the array to search.
 * @param {any} val the value to search for.
 * @return {int} the index of the item that contains the value or -1.
 */
YArray.indexOf = (Native.indexOf) ?
    function(a, val) {
        return Native.indexOf.call(a, val);
    } :
    function(a, val) {
        for (var i = 0; i < a.length; i = i + 1) {
            if (a[i] === val) {
                return i;
            }
        }

        return -1;
    };

/**
 * Numeric sort convenience function.
 * Y.ArrayAssert.itemsAreEqual([1,2,3], [3,1,2].sort(Y.Array.numericSort));
 * @method numericSort
 * @static
 * @param {number} a a number.
 * @param {number} b a number.
 */
YArray.numericSort = function(a, b) {
    return (a - b);
};

/**
 * Executes the supplied function on each item in the array.
 * Returning true from the processing function will stop the
 * processing of the remaining items.
 * @method some
 * @param {Array} a the array to iterate.
 * @param {Function} f the function to execute on each item. The function
 * receives three arguments: the value, the index, the full array.
 * @param {object} o Optional context object.
 * @static
 * @return {boolean} true if the function returns true on
 * any of the items in the array.
 */
YArray.some = (Native.some) ?
    function(a, f, o) {
        return Native.some.call(a, f, o);
    } :
    function(a, f, o) {
        var l = a.length, i;
        for (i = 0; i < l; i = i + 1) {
            if (f.call(o, a[i], i, a)) {
                return true;
            }
        }
        return false;
    };

/**
 * The YUI module contains the components required for building the YUI
 * seed file.  This includes the script loading mechanism, a simple queue,
 * and the core utilities for the library.
 * @module yui
 * @submodule yui-base
 */

/**
 * A simple FIFO queue.  Items are added to the Queue with add(1..n items) and
 * removed using next().
 *
 * @class Queue
 * @constructor
 * @param {MIXED} item* 0..n items to seed the queue.
 */
function Queue() {
    this._init();
    this.add.apply(this, arguments);
}

Queue.prototype = {
    /**
     * Initialize the queue
     *
     * @method _init
     * @protected
     */
    _init: function() {
        /**
         * The collection of enqueued items
         *
         * @property _q
         * @type Array
         * @protected
         */
        this._q = [];
    },

    /**
     * Get the next item in the queue. FIFO support
     *
     * @method next
     * @return {MIXED} the next item in the queue.
     */
    next: function() {
        return this._q.shift();
    },

    /**
     * Get the last in the queue. LIFO support.
     *
     * @method last
     * @return {MIXED} the last item in the queue.
     */
    last: function() {
        return this._q.pop();
    },

    /**
     * Add 0..n items to the end of the queue.
     *
     * @method add
     * @param {MIXED} item* 0..n items.
     * @return {object} this queue.
     */
    add: function() {
        this._q.push.apply(this._q, arguments);

        return this;
    },

    /**
     * Returns the current number of queued items.
     *
     * @method size
     * @return {Number} The size.
     */
    size: function() {
        return this._q.length;
    }
};

Y.Queue = Queue;

YUI.Env._loaderQueue = YUI.Env._loaderQueue || new Queue();

/**
 * The YUI module contains the components required for building the YUI
 * seed file.  This includes the script loading mechanism, a simple queue,
 * and the core utilities for the library.
 * @module yui
 * @submodule yui-base
 */

var CACHED_DELIMITER = '__',

/*
 * IE will not enumerate native functions in a derived object even if the
 * function was overridden.  This is a workaround for specific functions
 * we care about on the Object prototype.
 * @property _iefix
 * @for YUI
 * @param {Function} r  the object to receive the augmentation
 * @param {Function} s  the object that supplies the properties to augment
 * @private
 */
_iefix = function(r, s) {
    var fn = s.toString;
    if (Y.Lang.isFunction(fn) && fn != Object.prototype.toString) {
        r.toString = fn;
    }
};


/**
 * Returns a new object containing all of the properties of
 * all the supplied objects.  The properties from later objects
 * will overwrite those in earlier objects.  Passing in a
 * single object will create a shallow copy of it.  For a deep
 * copy, use clone.
 * @method merge
 * @for YUI
 * @param arguments {Object*} the objects to merge.
 * @return {object} the new merged object.
 */
Y.merge = function() {
    var a = arguments, o = {}, i, l = a.length;
    for (i = 0; i < l; i = i + 1) {
        Y.mix(o, a[i], true);
    }
    return o;
};

/**
 * Applies the supplier's properties to the receiver.  By default
 * all prototype and static propertes on the supplier are applied
 * to the corresponding spot on the receiver.  By default all
 * properties are applied, and a property that is already on the
 * reciever will not be overwritten.  The default behavior can
 * be modified by supplying the appropriate parameters.
 *
 * @todo add constants for the modes
 *
 * @method mix
 * @param {Function} r  the object to receive the augmentation.
 * @param {Function} s  the object that supplies the properties to augment.
 * @param ov {boolean} if true, properties already on the receiver
 * will be overwritten if found on the supplier.
 * @param wl {string[]} a whitelist.  If supplied, only properties in
 * this list will be applied to the receiver.
 * @param {int} mode what should be copies, and to where
 *        default(0): object to object
 *        1: prototype to prototype (old augment)
 *        2: prototype to prototype and object props (new augment)
 *        3: prototype to object
 *        4: object to prototype.
 * @param merge {boolean/int} merge objects instead of overwriting/ignoring.
 * A value of 2 will skip array merge
 * Used by Y.aggregate.
 * @return {object} the augmented object.
 */
Y.mix = function(r, s, ov, wl, mode, merge) {

    if (!s || !r) {
        return r || Y;
    }

    if (mode) {
        switch (mode) {
            case 1: // proto to proto
                return Y.mix(r.prototype, s.prototype, ov, wl, 0, merge);
            case 2: // object to object and proto to proto
                Y.mix(r.prototype, s.prototype, ov, wl, 0, merge);
                break; // pass through
            case 3: // proto to static
                return Y.mix(r, s.prototype, ov, wl, 0, merge);
            case 4: // static to proto
                return Y.mix(r.prototype, s, ov, wl, 0, merge);
            default:  // object to object is what happens below
        }
    }

    // Maybe don't even need this wl && wl.length check anymore??
    var i, l, p, type;

    if (wl && wl.length) {
        for (i = 0, l = wl.length; i < l; ++i) {
            p = wl[i];
            type = Y.Lang.type(r[p]);
            if (s.hasOwnProperty(p)) {
                if (merge && type == 'object') {
                    Y.mix(r[p], s[p]);
                } else if (ov || !(p in r)) {
                    r[p] = s[p];
                }
            }
        }
    } else {
        for (i in s) {
            // if (s.hasOwnProperty(i) && !(i in FROZEN)) {
            if (s.hasOwnProperty(i)) {
                // check white list if it was supplied
                // if the receiver has this property, it is an object,
                // and merge is specified, merge the two objects.
                if (merge && Y.Lang.isObject(r[i], true)) {
                    Y.mix(r[i], s[i], ov, wl, 0, true); // recursive
                // otherwise apply the property only if overwrite
                // is specified or the receiver doesn't have one.
                } else if (ov || !(i in r)) {
                    r[i] = s[i];
                }
                // if merge is specified and the receiver is an array,
                // append the array item
                // } else if (arr) {
                    // r.push(s[i]);
                // }
            }
        }

        if (Y.UA.ie) {
            _iefix(r, s);
        }
    }

    return r;
};

/**
 * Returns a wrapper for a function which caches the
 * return value of that function, keyed off of the combined
 * argument values.
 * @method cached
 * @param source {function} the function to memoize.
 * @param cache an optional cache seed.
 * @param refetch if supplied, this value is tested against the cached
 * value.  If the values are equal, the wrapped function is executed again.
 * @return {Function} the wrapped function.
 */
Y.cached = function(source, cache, refetch) {
    cache = cache || {};

    return function(arg1) {

        var k = (arguments.length > 1) ?
            Array.prototype.join.call(arguments, CACHED_DELIMITER) : arg1;

        if (!(k in cache) || (refetch && cache[k] == refetch)) {
            cache[k] = source.apply(source, arguments);
        }

        return cache[k];
    };

};

/**
 * The YUI module contains the components required for building the YUI
 * seed file.  This includes the script loading mechanism, a simple queue,
 * and the core utilities for the library.
 * @module yui
 * @submodule yui-base
 */

/**
 * Adds the following Object utilities to the YUI instance
 * @class Object
 */

/**
 * Y.Object(o) returns a new object based upon the supplied object.
 * @method ()
 * @static
 * @param o the supplier object.
 * @return {Object} the new object.
 */
var F = function() {},

// O = Object.create || function(o) {
//     F.prototype = o;
//     return new F();
// },

O = function(o) {
    F.prototype = o;
    return new F();
},

owns = function(o, k) {
    return o && o.hasOwnProperty && o.hasOwnProperty(k);
    // return Object.prototype.hasOwnProperty.call(o, k);
},

UNDEF,

/**
 * Extracts the keys, values, or size from an object
 *
 * @method _extract
 * @param o the object.
 * @param what what to extract (0: keys, 1: values, 2: size).
 * @return {boolean|Array} the extracted info.
 * @static
 * @private
 */
_extract = function(o, what) {
    var count = (what === 2), out = (count) ? 0 : [], i;

    for (i in o) {
        if (owns(o, i)) {
            if (count) {
                out++;
            } else {
                out.push((what) ? o[i] : i);
            }
        }
    }

    return out;
};

Y.Object = O;

/**
 * Returns an array containing the object's keys
 * @method keys
 * @static
 * @param o an object.
 * @return {string[]} the keys.
 */
// O.keys = Object.keys || function(o) {
//     return _extract(o);
// };

O.keys = function(o) {
    return _extract(o);
};

/**
 * Returns an array containing the object's values
 * @method values
 * @static
 * @param o an object.
 * @return {Array} the values.
 */
// O.values = Object.values || function(o) {
//     return _extract(o, 1);
// };

O.values = function(o) {
    return _extract(o, 1);
};

/**
 * Returns the size of an object
 * @method size
 * @static
 * @param o an object.
 * @return {int} the size.
 */
O.size = Object.size || function(o) {
    return _extract(o, 2);
};

/**
 * Returns true if the object contains a given key
 * @method hasKey
 * @static
 * @param o an object.
 * @param k the key to query.
 * @return {boolean} true if the object contains the key.
 */
O.hasKey = owns;
/**
 * Returns true if the object contains a given value
 * @method hasValue
 * @static
 * @param o an object.
 * @param v the value to query.
 * @return {boolean} true if the object contains the value.
 */
O.hasValue = function(o, v) {
    return (Y.Array.indexOf(O.values(o), v) > -1);
};

/**
 * Determines whether or not the property was added
 * to the object instance.  Returns false if the property is not present
 * in the object, or was inherited from the prototype.
 *
 * @method owns
 * @static
 * @param o {any} The object being testing.
 * @param p {string} the property to look for.
 * @return {boolean} true if the object has the property on the instance.
 */
O.owns = owns;

/**
 * Executes a function on each item. The function
 * receives the value, the key, and the object
 * as parameters (in that order).
 * @method each
 * @static
 * @param o the object to iterate.
 * @param f {Function} the function to execute on each item. The function
 * receives three arguments: the value, the the key, the full object.
 * @param c the execution context.
 * @param proto {boolean} include proto.
 * @return {YUI} the YUI instance.
 */
O.each = function(o, f, c, proto) {
    var s = c || Y, i;

    for (i in o) {
        if (proto || owns(o, i)) {
            f.call(s, o[i], i, o);
        }
    }
    return Y;
};

/**
 * Executes a function on each item, but halts if the
 * function returns true.  The function
 * receives the value, the key, and the object
 * as paramters (in that order).
 * @method some
 * @static
 * @param o the object to iterate.
 * @param f {Function} the function to execute on each item. The function
 * receives three arguments: the value, the the key, the full object.
 * @param c the execution context.
 * @param proto {boolean} include proto.
 * @return {boolean} true if any execution of the function returns true,
 * false otherwise.
 */
O.some = function(o, f, c, proto) {
    var s = c || Y, i;

    for (i in o) {
        if (proto || owns(o, i)) {
            if (f.call(s, o[i], i, o)) {
                return true;
            }
        }
    }
    return false;
};

/**
 * Retrieves the sub value at the provided path,
 * from the value object provided.
 *
 * @method getValue
 * @static
 * @param o The object from which to extract the property value.
 * @param path {Array} A path array, specifying the object traversal path
 * from which to obtain the sub value.
 * @return {Any} The value stored in the path, undefined if not found,
 * undefined if the source is not an object.  Returns the source object
 * if an empty path is provided.
 */
O.getValue = function(o, path) {
    if (!Y.Lang.isObject(o)) {
        return UNDEF;
    }

    var i,
        p = Y.Array(path),
        l = p.length;

    for (i = 0; o !== UNDEF && i < l; i++) {
        o = o[p[i]];
    }

    return o;
};

/**
 * Sets the sub-attribute value at the provided path on the
 * value object.  Returns the modified value object, or
 * undefined if the path is invalid.
 *
 * @method setValue
 * @static
 * @param o             The object on which to set the sub value.
 * @param path {Array}  A path array, specifying the object traversal path
 *                      at which to set the sub value.
 * @param val {Any}     The new value for the sub-attribute.
 * @return {Object}     The modified object, with the new sub value set, or
 *                      undefined, if the path was invalid.
 */
O.setValue = function(o, path, val) {
    var i,
        p = Y.Array(path),
        leafIdx = p.length - 1,
        ref = o;

    if (leafIdx >= 0) {
        for (i = 0; ref !== UNDEF && i < leafIdx; i++) {
            ref = ref[p[i]];
        }

        if (ref !== UNDEF) {
            ref[p[i]] = val;
        } else {
            return UNDEF;
        }
    }

    return o;
};

/**
 * Returns true if the object has no properties of its own
 * @method isEmpty
 * @static
 * @return {boolean} true if the object is empty.
 * @since 3.2.0
 */
O.isEmpty = function(o) {
    for (var i in o) {
        if (owns(o, i)) {
            return false;
        }
    }
    return true;
};
/**
 * The YUI module contains the components required for building the YUI seed
 * file.  This includes the script loading mechanism, a simple queue, and the
 * core utilities for the library.
 * @module yui
 * @submodule yui-base
 */

/**
 * YUI user agent detection.
 * Do not fork for a browser if it can be avoided.  Use feature detection when
 * you can.  Use the user agent as a last resort.  UA stores a version
 * number for the browser engine, 0 otherwise.  This value may or may not map
 * to the version number of the browser using the engine.  The value is
 * presented as a float so that it can easily be used for boolean evaluation
 * as well as for looking for a particular range of versions.  Because of this,
 * some of the granularity of the version info may be lost (e.g., Gecko 1.8.0.9
 * reports 1.8).
 * @class UA
 * @static
 */
/**
* Static method for parsing the UA string. Defaults to assigning it's value to Y.UA
* @static
* @method Env.parseUA
* @param {String} subUA Parse this UA string instead of navigator.userAgent
* @returns {Object} The Y.UA object
*/
YUI.Env.parseUA = function(subUA) {
    
    var numberify = function(s) {
            var c = 0;
            return parseFloat(s.replace(/\./g, function() {
                return (c++ == 1) ? '' : '.';
            }));
        },

        win = Y.config.win,

        nav = win && win.navigator,

        o = {

        /**
         * Internet Explorer version number or 0.  Example: 6
         * @property ie
         * @type float
         * @static
         */
        ie: 0,

        /**
         * Opera version number or 0.  Example: 9.2
         * @property opera
         * @type float
         * @static
         */
        opera: 0,

        /**
         * Gecko engine revision number.  Will evaluate to 1 if Gecko
         * is detected but the revision could not be found. Other browsers
         * will be 0.  Example: 1.8
         * <pre>
         * Firefox 1.0.0.4: 1.7.8   <-- Reports 1.7
         * Firefox 1.5.0.9: 1.8.0.9 <-- 1.8
         * Firefox 2.0.0.3: 1.8.1.3 <-- 1.81
         * Firefox 3.0   <-- 1.9
         * Firefox 3.5   <-- 1.91
         * </pre>
         * @property gecko
         * @type float
         * @static
         */
        gecko: 0,

        /**
         * AppleWebKit version.  KHTML browsers that are not WebKit browsers
         * will evaluate to 1, other browsers 0.  Example: 418.9
         * <pre>
         * Safari 1.3.2 (312.6): 312.8.1 <-- Reports 312.8 -- currently the
         *                                   latest available for Mac OSX 10.3.
         * Safari 2.0.2:         416     <-- hasOwnProperty introduced
         * Safari 2.0.4:         418     <-- preventDefault fixed
         * Safari 2.0.4 (419.3): 418.9.1 <-- One version of Safari may run
         *                                   different versions of webkit
         * Safari 2.0.4 (419.3): 419     <-- Tiger installations that have been
         *                                   updated, but not updated
         *                                   to the latest patch.
         * Webkit 212 nightly:   522+    <-- Safari 3.0 precursor (with native
         * SVG and many major issues fixed).
         * Safari 3.0.4 (523.12) 523.12  <-- First Tiger release - automatic
         * update from 2.x via the 10.4.11 OS patch.
         * Webkit nightly 1/2008:525+    <-- Supports DOMContentLoaded event.
         *                                   yahoo.com user agent hack removed.
         * </pre>
         * http://en.wikipedia.org/wiki/Safari_version_history
         * @property webkit
         * @type float
         * @static
         */
        webkit: 0,

        /**
         * Chrome will be detected as webkit, but this property will also
         * be populated with the Chrome version number
         * @property chrome
         * @type float
         * @static
         */
        chrome: 0,

        /**
         * The mobile property will be set to a string containing any relevant
         * user agent information when a modern mobile browser is detected.
         * Currently limited to Safari on the iPhone/iPod Touch, Nokia N-series
         * devices with the WebKit-based browser, and Opera Mini.
         * @property mobile
         * @type string
         * @static
         */
        mobile: null,

        /**
         * Adobe AIR version number or 0.  Only populated if webkit is detected.
         * Example: 1.0
         * @property air
         * @type float
         */
        air: 0,
        /**
         * Detects Apple iPad's OS version
         * @property ipad
         * @type float
         * @static
         */
        ipad: 0,
        /**
         * Detects Apple iPhone's OS version
         * @property iphone
         * @type float
         * @static
         */
        iphone: 0,
        /**
         * Detects Apples iPod's OS version
         * @property ipod
         * @type float
         * @static
         */
        ipod: 0,
        /**
         * General truthy check for iPad, iPhone or iPod
         * @property ios
         * @type float
         * @static
         */
        ios: null,
        /**
         * Detects Googles Android OS version
         * @property android
         * @type float
         * @static
         */
        android: 0,
        /**
         * Detects Palms WebOS version
         * @property webos
         * @type float
         * @static
         */
        webos: 0,

        /**
         * Google Caja version number or 0.
         * @property caja
         * @type float
         */
        caja: nav && nav.cajaVersion,

        /**
         * Set to true if the page appears to be in SSL
         * @property secure
         * @type boolean
         * @static
         */
        secure: false,

        /**
         * The operating system.  Currently only detecting windows or macintosh
         * @property os
         * @type string
         * @static
         */
        os: null

    },

    ua = subUA || nav && nav.userAgent,

    loc = win && win.location,

    href = loc && loc.href,

    m;

    o.secure = href && (href.toLowerCase().indexOf('https') === 0);

    if (ua) {

        if ((/windows|win32/i).test(ua)) {
            o.os = 'windows';
        } else if ((/macintosh/i).test(ua)) {
            o.os = 'macintosh';
        } else if ((/rhino/i).test(ua)) {
            o.os = 'rhino';
        }

        // Modern KHTML browsers should qualify as Safari X-Grade
        if ((/KHTML/).test(ua)) {
            o.webkit = 1;
        }
        // Modern WebKit browsers are at least X-Grade
        m = ua.match(/AppleWebKit\/([^\s]*)/);
        if (m && m[1]) {
            o.webkit = numberify(m[1]);

            // Mobile browser check
            if (/ Mobile\//.test(ua)) {
                o.mobile = 'Apple'; // iPhone or iPod Touch

                m = ua.match(/OS ([^\s]*)/);
                if (m && m[1]) {
                    m = numberify(m[1].replace('_', '.'));
                }
                o.ios = m;
                o.ipad = o.ipod = o.iphone = 0;

                m = ua.match(/iPad|iPod|iPhone/);
                if (m && m[0]) {
                    o[m[0].toLowerCase()] = o.ios;
                }
            } else {
                m = ua.match(/NokiaN[^\/]*|Android \d\.\d|webOS\/\d\.\d/);
                if (m) {
                    // Nokia N-series, Android, webOS, ex: NokiaN95
                    o.mobile = m[0];
                }
                if (/webOS/.test(ua)) {
                    o.mobile = 'WebOS';
                    m = ua.match(/webOS\/([^\s]*);/);
                    if (m && m[1]) {
                        o.webos = numberify(m[1]);
                    }
                }
                if (/ Android/.test(ua)) {
                    o.mobile = 'Android';
                    m = ua.match(/Android ([^\s]*);/);
                    if (m && m[1]) {
                        o.android = numberify(m[1]);
                    }

                }
            }

            m = ua.match(/Chrome\/([^\s]*)/);
            if (m && m[1]) {
                o.chrome = numberify(m[1]); // Chrome
            } else {
                m = ua.match(/AdobeAIR\/([^\s]*)/);
                if (m) {
                    o.air = m[0]; // Adobe AIR 1.0 or better
                }
            }
        }

        if (!o.webkit) { // not webkit
// @todo check Opera/8.01 (J2ME/MIDP; Opera Mini/2.0.4509/1316; fi; U; ssr)
            m = ua.match(/Opera[\s\/]([^\s]*)/);
            if (m && m[1]) {
                o.opera = numberify(m[1]);
                m = ua.match(/Opera Mini[^;]*/);
                if (m) {
                    o.mobile = m[0]; // ex: Opera Mini/2.0.4509/1316
                }
            } else { // not opera or webkit
                m = ua.match(/MSIE\s([^;]*)/);
                if (m && m[1]) {
                    o.ie = numberify(m[1]);
                } else { // not opera, webkit, or ie
                    m = ua.match(/Gecko\/([^\s]*)/);
                    if (m) {
                        o.gecko = 1; // Gecko detected, look for revision
                        m = ua.match(/rv:([^\s\)]*)/);
                        if (m && m[1]) {
                            o.gecko = numberify(m[1]);
                        }
                    }
                }
            }
        }
    }

    YUI.Env.UA = o;

    return o;
};


Y.UA = YUI.Env.UA || YUI.Env.parseUA();


}, '3.3.0' );
YUI.add('get', function(Y) {


/**
 * Provides a mechanism to fetch remote resources and
 * insert them into a document.
 * @module yui
 * @submodule get
 */

var ua = Y.UA,
    L = Y.Lang,
    TYPE_JS = 'text/javascript',
    TYPE_CSS = 'text/css',
    STYLESHEET = 'stylesheet';

/**
 * Fetches and inserts one or more script or link nodes into the document
 * @class Get
 * @static
 */
Y.Get = function() {

    /**
     * hash of queues to manage multiple requests
     * @property queues
     * @private
     */
    var _get, _purge, _track,

    queues = {},

    /**
     * queue index used to generate transaction ids
     * @property qidx
     * @type int
     * @private
     */
    qidx = 0,

    /**
     * interal property used to prevent multiple simultaneous purge
     * processes
     * @property purging
     * @type boolean
     * @private
     */
    purging,


    /**
     * Generates an HTML element, this is not appended to a document
     * @method _node
     * @param {string} type the type of element.
     * @param {string} attr the attributes.
     * @param {Window} win optional window to create the element in.
     * @return {HTMLElement} the generated node.
     * @private
     */
    _node = function(type, attr, win) {
        var w = win || Y.config.win,
            d = w.document,
            n = d.createElement(type),
            i;

        for (i in attr) {
            if (attr[i] && attr.hasOwnProperty(i)) {
                n.setAttribute(i, attr[i]);
            }
        }

        return n;
    },

    /**
     * Generates a link node
     * @method _linkNode
     * @param {string} url the url for the css file.
     * @param {Window} win optional window to create the node in.
     * @param {object} attributes optional attributes collection to apply to the
     * new node.
     * @return {HTMLElement} the generated node.
     * @private
     */
    _linkNode = function(url, win, attributes) {
        var o = {
            id: Y.guid(),
            type: TYPE_CSS,
            rel: STYLESHEET,
            href: url
        };
        if (attributes) {
            Y.mix(o, attributes);
        }
        return _node('link', o, win);
    },

    /**
     * Generates a script node
     * @method _scriptNode
     * @param {string} url the url for the script file.
     * @param {Window} win optional window to create the node in.
     * @param {object} attributes optional attributes collection to apply to the
     * new node.
     * @return {HTMLElement} the generated node.
     * @private
     */
    _scriptNode = function(url, win, attributes) {
        var o = {
            id: Y.guid(),
            type: TYPE_JS
        };

        if (attributes) {
            Y.mix(o, attributes);
        }

        o.src = url;

        return _node('script', o, win);
    },

    /**
     * Returns the data payload for callback functions.
     * @method _returnData
     * @param {object} q the queue.
     * @param {string} msg the result message.
     * @param {string} result the status message from the request.
     * @return {object} the state data from the request.
     * @private
     */
    _returnData = function(q, msg, result) {
        return {
                tId: q.tId,
                win: q.win,
                data: q.data,
                nodes: q.nodes,
                msg: msg,
                statusText: result,
                purge: function() {
                    _purge(this.tId);
                }
            };
    },

    /**
     * The transaction is finished
     * @method _end
     * @param {string} id the id of the request.
     * @param {string} msg the result message.
     * @param {string} result the status message from the request.
     * @private
     */
    _end = function(id, msg, result) {
        var q = queues[id], sc;
        if (q && q.onEnd) {
            sc = q.context || q;
            q.onEnd.call(sc, _returnData(q, msg, result));
        }
    },

    /*
     * The request failed, execute fail handler with whatever
     * was accomplished.  There isn't a failure case at the
     * moment unless you count aborted transactions
     * @method _fail
     * @param {string} id the id of the request
     * @private
     */
    _fail = function(id, msg) {

        var q = queues[id], sc;
        if (q.timer) {
            // q.timer.cancel();
            clearTimeout(q.timer);
        }

        // execute failure callback
        if (q.onFailure) {
            sc = q.context || q;
            q.onFailure.call(sc, _returnData(q, msg));
        }

        _end(id, msg, 'failure');
    },

    /**
     * The request is complete, so executing the requester's callback
     * @method _finish
     * @param {string} id the id of the request.
     * @private
     */
    _finish = function(id) {
        var q = queues[id], msg, sc;
        if (q.timer) {
            // q.timer.cancel();
            clearTimeout(q.timer);
        }
        q.finished = true;

        if (q.aborted) {
            msg = 'transaction ' + id + ' was aborted';
            _fail(id, msg);
            return;
        }

        // execute success callback
        if (q.onSuccess) {
            sc = q.context || q;
            q.onSuccess.call(sc, _returnData(q));
        }

        _end(id, msg, 'OK');
    },

    /**
     * Timeout detected
     * @method _timeout
     * @param {string} id the id of the request.
     * @private
     */
    _timeout = function(id) {
        var q = queues[id], sc;
        if (q.onTimeout) {
            sc = q.context || q;
            q.onTimeout.call(sc, _returnData(q));
        }

        _end(id, 'timeout', 'timeout');
    },


    /**
     * Loads the next item for a given request
     * @method _next
     * @param {string} id the id of the request.
     * @param {string} loaded the url that was just loaded, if any.
     * @return {string} the result.
     * @private
     */
    _next = function(id, loaded) {
        var q = queues[id], msg, w, d, h, n, url, s,
            insertBefore;

        if (q.timer) {
            // q.timer.cancel();
            clearTimeout(q.timer);
        }

        if (q.aborted) {
            msg = 'transaction ' + id + ' was aborted';
            _fail(id, msg);
            return;
        }

        if (loaded) {
            q.url.shift();
            if (q.varName) {
                q.varName.shift();
            }
        } else {
            // This is the first pass: make sure the url is an array
            q.url = (L.isString(q.url)) ? [q.url] : q.url;
            if (q.varName) {
                q.varName = (L.isString(q.varName)) ? [q.varName] : q.varName;
            }
        }

        w = q.win;
        d = w.document;
        h = d.getElementsByTagName('head')[0];

        if (q.url.length === 0) {
            _finish(id);
            return;
        }

        url = q.url[0];

        // if the url is undefined, this is probably a trailing comma
        // problem in IE.
        if (!url) {
            q.url.shift();
            return _next(id);
        }


        if (q.timeout) {
            // q.timer = L.later(q.timeout, q, _timeout, id);
            q.timer = setTimeout(function() {
                _timeout(id);
            }, q.timeout);
        }

        if (q.type === 'script') {
            n = _scriptNode(url, w, q.attributes);
        } else {
            n = _linkNode(url, w, q.attributes);
        }

        // track this node's load progress
        _track(q.type, n, id, url, w, q.url.length);

        // add the node to the queue so we can return it to the user supplied
        // callback
        q.nodes.push(n);

        // add it to the head or insert it before 'insertBefore'.  Work around
        // IE bug if there is a base tag.
        insertBefore = q.insertBefore ||
                       d.getElementsByTagName('base')[0];

        if (insertBefore) {
            s = _get(insertBefore, id);
            if (s) {
                s.parentNode.insertBefore(n, s);
            }
        } else {
            h.appendChild(n);
        }


        // FireFox does not support the onload event for link nodes, so
        // there is no way to make the css requests synchronous. This means
        // that the css rules in multiple files could be applied out of order
        // in this browser if a later request returns before an earlier one.
        // Safari too.
        if ((ua.webkit || ua.gecko) && q.type === 'css') {
            _next(id, url);
        }
    },

    /**
     * Removes processed queues and corresponding nodes
     * @method _autoPurge
     * @private
     */
    _autoPurge = function() {
        if (purging) {
            return;
        }
        purging = true;

        var i, q;

        for (i in queues) {
            if (queues.hasOwnProperty(i)) {
                q = queues[i];
                if (q.autopurge && q.finished) {
                    _purge(q.tId);
                    delete queues[i];
                }
            }
        }

        purging = false;
    },

    /**
     * Saves the state for the request and begins loading
     * the requested urls
     * @method queue
     * @param {string} type the type of node to insert.
     * @param {string} url the url to load.
     * @param {object} opts the hash of options for this request.
     * @return {object} transaction object.
     * @private
     */
    _queue = function(type, url, opts) {
        opts = opts || {};

        var id = 'q' + (qidx++), q,
            thresh = opts.purgethreshold || Y.Get.PURGE_THRESH;

        if (qidx % thresh === 0) {
            _autoPurge();
        }

        queues[id] = Y.merge(opts, {
            tId: id,
            type: type,
            url: url,
            finished: false,
            nodes: []
        });

        q = queues[id];
        q.win = q.win || Y.config.win;
        q.context = q.context || q;
        q.autopurge = ('autopurge' in q) ? q.autopurge :
                      (type === 'script') ? true : false;

        q.attributes = q.attributes || {};
        q.attributes.charset = opts.charset || q.attributes.charset || 'utf-8';

        _next(id);

        return {
            tId: id
        };
    };

    /**
     * Detects when a node has been loaded.  In the case of
     * script nodes, this does not guarantee that contained
     * script is ready to use.
     * @method _track
     * @param {string} type the type of node to track.
     * @param {HTMLElement} n the node to track.
     * @param {string} id the id of the request.
     * @param {string} url the url that is being loaded.
     * @param {Window} win the targeted window.
     * @param {int} qlength the number of remaining items in the queue,
     * including this one.
     * @param {Function} trackfn function to execute when finished
     * the default is _next.
     * @private
     */
    _track = function(type, n, id, url, win, qlength, trackfn) {
        var f = trackfn || _next;

        // IE supports the readystatechange event for script and css nodes
        // Opera only for script nodes.  Opera support onload for script
        // nodes, but this doesn't fire when there is a load failure.
        // The onreadystatechange appears to be a better way to respond
        // to both success and failure.
        if (ua.ie) {
            n.onreadystatechange = function() {
                var rs = this.readyState;
                if ('loaded' === rs || 'complete' === rs) {
                    n.onreadystatechange = null;
                    f(id, url);
                }
            };

        // webkit prior to 3.x is no longer supported
        } else if (ua.webkit) {
            if (type === 'script') {
                // Safari 3.x supports the load event for script nodes (DOM2)
                n.addEventListener('load', function() {
                    f(id, url);
                });
            }

        // FireFox and Opera support onload (but not DOM2 in FF) handlers for
        // script nodes.  Opera, but not FF, supports the onload event for link
        // nodes.
        } else {
            n.onload = function() {
                f(id, url);
            };

            n.onerror = function(e) {
                _fail(id, e + ': ' + url);
            };
        }
    };

    _get = function(nId, tId) {
        var q = queues[tId],
            n = (L.isString(nId)) ? q.win.document.getElementById(nId) : nId;
        if (!n) {
            _fail(tId, 'target node not found: ' + nId);
        }

        return n;
    };

    /**
     * Removes the nodes for the specified queue
     * @method _purge
     * @param {string} tId the transaction id.
     * @private
     */
    _purge = function(tId) {
        var n, l, d, h, s, i, node, attr, insertBefore,
            q = queues[tId];

        if (q) {
            n = q.nodes;
            l = n.length;
            d = q.win.document;
            h = d.getElementsByTagName('head')[0];

            insertBefore = q.insertBefore ||
                           d.getElementsByTagName('base')[0];

            if (insertBefore) {
                s = _get(insertBefore, tId);
                if (s) {
                    h = s.parentNode;
                }
            }

            for (i = 0; i < l; i = i + 1) {
                node = n[i];
                if (node.clearAttributes) {
                    node.clearAttributes();
                } else {
                    for (attr in node) {
                        if (node.hasOwnProperty(attr)) {
                            delete node[attr];
                        }
                    }
                }

                h.removeChild(node);
            }
        }
        q.nodes = [];
    };

    return {

        /**
         * The number of request required before an automatic purge.
         * Can be configured via the 'purgethreshold' config
         * property PURGE_THRESH
         * @static
         * @type int
         * @default 20
         * @private
         */
        PURGE_THRESH: 20,

        /**
         * Called by the the helper for detecting script load in Safari
         * @method _finalize
         * @static
         * @param {string} id the transaction id.
         * @private
         */
        _finalize: function(id) {
            setTimeout(function() {
                _finish(id);
            }, 0);
        },

        /**
         * Abort a transaction
         * @method abort
         * @static
         * @param {string|object} o Either the tId or the object returned from
         * script() or css().
         */
        abort: function(o) {
            var id = (L.isString(o)) ? o : o.tId,
                q = queues[id];
            if (q) {
                q.aborted = true;
            }
        },

        /**
         * Fetches and inserts one or more script nodes into the head
         * of the current document or the document in a specified window.
         *
         * @method script
         * @static
         * @param {string|string[]} url the url or urls to the script(s).
         * @param {object} opts Options:
         * <dl>
         * <dt>onSuccess</dt>
         * <dd>
         * callback to execute when the script(s) are finished loading
         * The callback receives an object back with the following
         * data:
         * <dl>
         * <dt>win</dt>
         * <dd>the window the script(s) were inserted into</dd>
         * <dt>data</dt>
         * <dd>the data object passed in when the request was made</dd>
         * <dt>nodes</dt>
         * <dd>An array containing references to the nodes that were
         * inserted</dd>
         * <dt>purge</dt>
         * <dd>A function that, when executed, will remove the nodes
         * that were inserted</dd>
         * <dt>
         * </dl>
         * </dd>
         * <dt>onTimeout</dt>
         * <dd>
         * callback to execute when a timeout occurs.
         * The callback receives an object back with the following
         * data:
         * <dl>
         * <dt>win</dt>
         * <dd>the window the script(s) were inserted into</dd>
         * <dt>data</dt>
         * <dd>the data object passed in when the request was made</dd>
         * <dt>nodes</dt>
         * <dd>An array containing references to the nodes that were
         * inserted</dd>
         * <dt>purge</dt>
         * <dd>A function that, when executed, will remove the nodes
         * that were inserted</dd>
         * <dt>
         * </dl>
         * </dd>
         * <dt>onEnd</dt>
         * <dd>a function that executes when the transaction finishes,
         * regardless of the exit path</dd>
         * <dt>onFailure</dt>
         * <dd>
         * callback to execute when the script load operation fails
         * The callback receives an object back with the following
         * data:
         * <dl>
         * <dt>win</dt>
         * <dd>the window the script(s) were inserted into</dd>
         * <dt>data</dt>
         * <dd>the data object passed in when the request was made</dd>
         * <dt>nodes</dt>
         * <dd>An array containing references to the nodes that were
         * inserted successfully</dd>
         * <dt>purge</dt>
         * <dd>A function that, when executed, will remove any nodes
         * that were inserted</dd>
         * <dt>
         * </dl>
         * </dd>
         * <dt>context</dt>
         * <dd>the execution context for the callbacks</dd>
         * <dt>win</dt>
         * <dd>a window other than the one the utility occupies</dd>
         * <dt>autopurge</dt>
         * <dd>
         * setting to true will let the utilities cleanup routine purge
         * the script once loaded
         * </dd>
         * <dt>purgethreshold</dt>
         * <dd>
         * The number of transaction before autopurge should be initiated
         * </dd>
         * <dt>data</dt>
         * <dd>
         * data that is supplied to the callback when the script(s) are
         * loaded.
         * </dd>
         * <dt>insertBefore</dt>
         * <dd>node or node id that will become the new node's nextSibling.
         * If this is not specified, nodes will be inserted before a base
         * tag should it exist.  Otherwise, the nodes will be appended to the
         * end of the document head.</dd>
         * </dl>
         * <dt>charset</dt>
         * <dd>Node charset, default utf-8 (deprecated, use the attributes
         * config)</dd>
         * <dt>attributes</dt>
         * <dd>An object literal containing additional attributes to add to
         * the link tags</dd>
         * <dt>timeout</dt>
         * <dd>Number of milliseconds to wait before aborting and firing
         * the timeout event</dd>
         * <pre>
         * &nbsp; Y.Get.script(
         * &nbsp; ["http://yui.yahooapis.com/2.5.2/build/yahoo/yahoo-min.js",
         * &nbsp;  "http://yui.yahooapis.com/2.5.2/build/event/event-min.js"],
         * &nbsp; &#123;
         * &nbsp;   onSuccess: function(o) &#123;
         * &nbsp;     this.log("won't cause error because Y is the context");
         * &nbsp;                   // immediately
         * &nbsp;   &#125;,
         * &nbsp;   onFailure: function(o) &#123;
         * &nbsp;   &#125;,
         * &nbsp;   onTimeout: function(o) &#123;
         * &nbsp;   &#125;,
         * &nbsp;   data: "foo",
         * &nbsp;   timeout: 10000, // 10 second timeout
         * &nbsp;   context: Y, // make the YUI instance
         * &nbsp;   // win: otherframe // target another window/frame
         * &nbsp;   autopurge: true // allow the utility to choose when to
         * &nbsp;                   // remove the nodes
         * &nbsp;   purgetheshold: 1 // purge previous transaction before
         * &nbsp;                    // next transaction
         * &nbsp; &#125;);.
         * </pre>
         * @return {tId: string} an object containing info about the
         * transaction.
         */
        script: function(url, opts) {
            return _queue('script', url, opts);
        },

        /**
         * Fetches and inserts one or more css link nodes into the
         * head of the current document or the document in a specified
         * window.
         * @method css
         * @static
         * @param {string} url the url or urls to the css file(s).
         * @param {object} opts Options:
         * <dl>
         * <dt>onSuccess</dt>
         * <dd>
         * callback to execute when the css file(s) are finished loading
         * The callback receives an object back with the following
         * data:
         * <dl>win</dl>
         * <dd>the window the link nodes(s) were inserted into</dd>
         * <dt>data</dt>
         * <dd>the data object passed in when the request was made</dd>
         * <dt>nodes</dt>
         * <dd>An array containing references to the nodes that were
         * inserted</dd>
         * <dt>purge</dt>
         * <dd>A function that, when executed, will remove the nodes
         * that were inserted</dd>
         * <dt>
         * </dl>
         * </dd>
         * <dt>context</dt>
         * <dd>the execution context for the callbacks</dd>
         * <dt>win</dt>
         * <dd>a window other than the one the utility occupies</dd>
         * <dt>data</dt>
         * <dd>
         * data that is supplied to the callbacks when the nodes(s) are
         * loaded.
         * </dd>
         * <dt>insertBefore</dt>
         * <dd>node or node id that will become the new node's nextSibling</dd>
         * <dt>charset</dt>
         * <dd>Node charset, default utf-8 (deprecated, use the attributes
         * config)</dd>
         * <dt>attributes</dt>
         * <dd>An object literal containing additional attributes to add to
         * the link tags</dd>
         * </dl>
         * <pre>
         * Y.Get.css("http://localhost/css/menu.css");
         * </pre>
         * <pre>
         * &nbsp; Y.Get.css(
         * &nbsp; ["http://localhost/css/menu.css",
         * &nbsp;   insertBefore: 'custom-styles' // nodes will be inserted
         * &nbsp;                                 // before the specified node
         * &nbsp; &#125;);.
         * </pre>
         * @return {tId: string} an object containing info about the
         * transaction.
         */
        css: function(url, opts) {
            return _queue('css', url, opts);
        }
    };
}();



}, '3.3.0' ,{requires:['yui-base']});
YUI.add('features', function(Y) {

var feature_tests = {};

Y.mix(Y.namespace('Features'), {

    tests: feature_tests,

    add: function(cat, name, o) {
        feature_tests[cat] = feature_tests[cat] || {};
        feature_tests[cat][name] = o;
    },

    all: function(cat, args) {
        var cat_o = feature_tests[cat],
            // results = {};
            result = '';
        if (cat_o) {
            Y.Object.each(cat_o, function(v, k) {
                // results[k] = Y.Features.test(cat, k, args);
                result += k + ':' +
                       (Y.Features.test(cat, k, args) ? 1 : 0) + ';';
            });
        }

        return result;
    },

    test: function(cat, name, args) {
        args = args || [];
        var result, ua, test,
            cat_o = feature_tests[cat],
            feature = cat_o && cat_o[name];

        if (!feature) {
        } else {

            result = feature.result;

            if (Y.Lang.isUndefined(result)) {

                ua = feature.ua;
                if (ua) {
                    result = (Y.UA[ua]);
                }

                test = feature.test;
                if (test && ((!ua) || result)) {
                    result = test.apply(Y, args);
                }

                feature.result = result;
            }
        }

        return result;
    }
});

// Y.Features.add("load", "1", {});
// Y.Features.test("load", "1");
// caps=1:1;2:0;3:1;

/* This file is auto-generated by src/loader/meta_join.py */
var add = Y.Features.add;
// autocomplete-list-keys-sniff.js
add('load', '0', {
    "test": function (Y) {
    // Only add keyboard support to autocomplete-list if this doesn't appear to
    // be an iOS or Android-based mobile device.
    //
    // There's currently no feasible way to actually detect whether a device has
    // a hardware keyboard, so this sniff will have to do. It can easily be
    // overridden by manually loading the autocomplete-list-keys module.
    //
    // Worth noting: even though iOS supports bluetooth keyboards, Mobile Safari
    // doesn't fire the keyboard events used by AutoCompleteList, so there's
    // no point loading the -keys module even when a bluetooth keyboard may be
    // available.
    return !(Y.UA.ios || Y.UA.android);
}, 
    "trigger": "autocomplete-list"
});
// ie-style-test.js
add('load', '1', {
    "test": function (Y) {

    var testFeature = Y.Features.test,
        addFeature = Y.Features.add,
        WINDOW = Y.config.win,
        DOCUMENT = Y.config.doc,
        DOCUMENT_ELEMENT = 'documentElement',
        ret = false;

    addFeature('style', 'computedStyle', {
        test: function() {
            return WINDOW && 'getComputedStyle' in WINDOW;
        }
    });

    addFeature('style', 'opacity', {
        test: function() {
            return DOCUMENT && 'opacity' in DOCUMENT[DOCUMENT_ELEMENT].style;
        }
    });

    ret =  (!testFeature('style', 'opacity') &&
            !testFeature('style', 'computedStyle'));

    return ret;
}, 
    "trigger": "dom-style"
});
// 0
add('load', '2', {
    "trigger": "widget-base", 
    "ua": "ie"
});
// ie-base-test.js
add('load', '3', {
    "test": function(Y) {
    var imp = Y.config.doc && Y.config.doc.implementation;
    return (imp && (!imp.hasFeature('Events', '2.0')));
}, 
    "trigger": "node-base"
});
// dd-gestures-test.js
add('load', '4', {
    "test": function(Y) {
    return (Y.config.win && ('ontouchstart' in Y.config.win && !Y.UA.chrome));
}, 
    "trigger": "dd-drag"
});
// history-hash-ie-test.js
add('load', '5', {
    "test": function (Y) {
    var docMode = Y.config.doc.documentMode;

    return Y.UA.ie && (!('onhashchange' in Y.config.win) ||
            !docMode || docMode < 8);
}, 
    "trigger": "history-hash"
});


}, '3.3.0' ,{requires:['yui-base']});
YUI.add('rls', function(Y) {

/**
 * Implentation for building the remote loader service url.
 * @method _rls
 * @param {Array} what the requested modules.
 * @since 3.2.0
 * @return {string} the url for the remote loader service call.
 */
Y._rls = function(what) {

    var config = Y.config,

        // the configuration
        rls = config.rls || {
            m: 1, // required in the template
            v: Y.version,
            gv: config.gallery,
            env: 1, // required in the template
            lang: config.lang,
            '2in3v': config['2in3'],
            '2v': config.yui2,
            filt: config.filter,
            filts: config.filters,
            tests: 1 // required in the template
        },

        // The rls base path
        rls_base = config.rls_base || 'load?',

        // the template
        rls_tmpl = config.rls_tmpl || function() {
            var s = '', param;
            for (param in rls) {
                if (param in rls && rls[param]) {
                    s += param + '={' + param + '}&';
                }
            }
            // console.log('rls_tmpl: ' + s);
            return s;
        }(),

        url;

    // update the request
    rls.m = what;
    rls.env = Y.Object.keys(YUI.Env.mods);
    rls.tests = Y.Features.all('load', [Y]);

    url = Y.Lang.sub(rls_base + rls_tmpl, rls);

    config.rls = rls;
    config.rls_tmpl = rls_tmpl;

    // console.log(url);
    return url;
};



}, '3.3.0' ,{requires:['get','features']});
YUI.add('intl-base', function(Y) {

/**
 * The Intl utility provides a central location for managing sets of
 * localized resources (strings and formatting patterns).
 *
 * @class Intl
 * @uses EventTarget
 * @static
 */

var SPLIT_REGEX = /[, ]/;

Y.mix(Y.namespace('Intl'), {

 /**
    * Returns the language among those available that
    * best matches the preferred language list, using the Lookup
    * algorithm of BCP 47.
    * If none of the available languages meets the user's preferences,
    * then "" is returned.
    * Extended language ranges are not supported.
    *
    * @method lookupBestLang
    * @param {String[] | String} preferredLanguages The list of preferred
    * languages in descending preference order, represented as BCP 47
    * language tags. A string array or a comma-separated list.
    * @param {String[]} availableLanguages The list of languages
    * that the application supports, represented as BCP 47 language
    * tags.
    *
    * @return {String} The available language that best matches the
    * preferred language list, or "".
    * @since 3.1.0
    */
    lookupBestLang: function(preferredLanguages, availableLanguages) {

        var i, language, result, index;

        // check whether the list of available languages contains language;
        // if so return it
        function scan(language) {
            var i;
            for (i = 0; i < availableLanguages.length; i += 1) {
                if (language.toLowerCase() ===
                            availableLanguages[i].toLowerCase()) {
                    return availableLanguages[i];
                }
            }
        }

        if (Y.Lang.isString(preferredLanguages)) {
            preferredLanguages = preferredLanguages.split(SPLIT_REGEX);
        }

        for (i = 0; i < preferredLanguages.length; i += 1) {
            language = preferredLanguages[i];
            if (!language || language === '*') {
                continue;
            }
            // check the fallback sequence for one language
            while (language.length > 0) {
                result = scan(language);
                if (result) {
                    return result;
                } else {
                    index = language.lastIndexOf('-');
                    if (index >= 0) {
                        language = language.substring(0, index);
                        // one-character subtags get cut along with the
                        // following subtag
                        if (index >= 2 && language.charAt(index - 2) === '-') {
                            language = language.substring(0, index - 2);
                        }
                    } else {
                        // nothing available for this language
                        break;
                    }
                }
            }
        }

        return '';
    }
});


}, '3.3.0' ,{requires:['yui-base']});
YUI.add('yui-log', function(Y) {

/**
 * Provides console log capability and exposes a custom event for
 * console implementations.
 * @module yui
 * @submodule yui-log
 */

var INSTANCE = Y,
    LOGEVENT = 'yui:log',
    UNDEFINED = 'undefined',
    LEVELS = { debug: 1,
               info: 1,
               warn: 1,
               error: 1 };

/**
 * If the 'debug' config is true, a 'yui:log' event will be
 * dispatched, which the Console widget and anything else
 * can consume.  If the 'useBrowserConsole' config is true, it will
 * write to the browser console if available.  YUI-specific log
 * messages will only be present in the -debug versions of the
 * JS files.  The build system is supposed to remove log statements
 * from the raw and minified versions of the files.
 *
 * @method log
 * @for YUI
 * @param  {String}  msg  The message to log.
 * @param  {String}  cat  The log category for the message.  Default
 *                        categories are "info", "warn", "error", time".
 *                        Custom categories can be used as well. (opt).
 * @param  {String}  src  The source of the the message (opt).
 * @param  {boolean} silent If true, the log event won't fire.
 * @return {YUI}      YUI instance.
 */
INSTANCE.log = function(msg, cat, src, silent) {
    var bail, excl, incl, m, f,
        Y = INSTANCE,
        c = Y.config,
        publisher = (Y.fire) ? Y : YUI.Env.globalEvents;
    // suppress log message if the config is off or the event stack
    // or the event call stack contains a consumer of the yui:log event
    if (c.debug) {
        // apply source filters
        if (src) {
            excl = c.logExclude;
            incl = c.logInclude;
            if (incl && !(src in incl)) {
                bail = 1;
            } else if (excl && (src in excl)) {
                bail = 1;
            }
        }
        if (!bail) {
            if (c.useBrowserConsole) {
                m = (src) ? src + ': ' + msg : msg;
                if (Y.Lang.isFunction(c.logFn)) {
                    c.logFn.call(Y, msg, cat, src);
                } else if (typeof console != UNDEFINED && console.log) {
                    f = (cat && console[cat] && (cat in LEVELS)) ? cat : 'log';
                    console[f](m);
                } else if (typeof opera != UNDEFINED) {
                    opera.postError(m);
                }
            }

            if (publisher && !silent) {

                if (publisher == Y && (!publisher.getEvent(LOGEVENT))) {
                    publisher.publish(LOGEVENT, {
                        broadcast: 2
                    });
                }

                publisher.fire(LOGEVENT, {
                    msg: msg,
                    cat: cat,
                    src: src
                });
            }
        }
    }

    return Y;
};

/**
 * Write a system message.  This message will be preserved in the
 * minified and raw versions of the YUI files, unlike log statements.
 * @method message
 * @for YUI
 * @param  {String}  msg  The message to log.
 * @param  {String}  cat  The log category for the message.  Default
 *                        categories are "info", "warn", "error", time".
 *                        Custom categories can be used as well. (opt).
 * @param  {String}  src  The source of the the message (opt).
 * @param  {boolean} silent If true, the log event won't fire.
 * @return {YUI}      YUI instance.
 */
INSTANCE.message = function() {
    return INSTANCE.log.apply(INSTANCE, arguments);
};


}, '3.3.0' ,{requires:['yui-base']});
YUI.add('yui-later', function(Y) {

/**
 * Provides a setTimeout/setInterval wrapper
 * @module yui
 * @submodule yui-later
 */

/**
 * Executes the supplied function in the context of the supplied
 * object 'when' milliseconds later.  Executes the function a
 * single time unless periodic is set to true.
 * @method later
 * @for YUI
 * @param when {int} the number of milliseconds to wait until the fn
 * is executed.
 * @param o the context object.
 * @param fn {Function|String} the function to execute or the name of
 * the method in the 'o' object to execute.
 * @param data [Array] data that is provided to the function.  This
 * accepts either a single item or an array.  If an array is provided,
 * the function is executed with one parameter for each array item.
 * If you need to pass a single array parameter, it needs to be wrapped
 * in an array [myarray].
 * @param periodic {boolean} if true, executes continuously at supplied
 * interval until canceled.
 * @return {object} a timer object. Call the cancel() method on this
 * object to stop the timer.
 */
Y.later = function(when, o, fn, data, periodic) {
    when = when || 0;

    var m = fn, f, id;

    if (o && Y.Lang.isString(fn)) {
        m = o[fn];
    }

    f = !Y.Lang.isUndefined(data) ? function() {
        m.apply(o, Y.Array(data));
    } : function() {
        m.call(o);
    };

    id = (periodic) ? setInterval(f, when) : setTimeout(f, when);

    return {
        id: id,
        interval: periodic,
        cancel: function() {
            if (this.interval) {
                clearInterval(id);
            } else {
                clearTimeout(id);
            }
        }
    };
};

Y.Lang.later = Y.later;



}, '3.3.0' ,{requires:['yui-base']});
YUI.add('yui-throttle', function(Y) {

/**
 * Provides a throttle/limiter for function calls
 * @module yui
 * @submodule yui-throttle
 */

/*! Based on work by Simon Willison: http://gist.github.com/292562 */
/**
 * Throttles a call to a method based on the time between calls.
 * @method throttle
 * @for YUI
 * @param fn {function} The function call to throttle.
 * @param ms {int} The number of milliseconds to throttle the method call.
 * Can set globally with Y.config.throttleTime or by call. Passing a -1 will
 * disable the throttle. Defaults to 150.
 * @return {function} Returns a wrapped function that calls fn throttled.
 * @since 3.1.0
 */
Y.throttle = function(fn, ms) {
    ms = (ms) ? ms : (Y.config.throttleTime || 150);

    if (ms === -1) {
        return (function() {
            fn.apply(null, arguments);
        });
    }

    var last = Y.Lang.now();

    return (function() {
        var now = Y.Lang.now();
        if (now - last > ms) {
            last = now;
            fn.apply(null, arguments);
        }
    });
};


}, '3.3.0' ,{requires:['yui-base']});


YUI.add('yui', function(Y){}, '3.3.0' ,{use:['yui-base','get','features','rls','intl-base','yui-log','yui-later','yui-throttle']});

