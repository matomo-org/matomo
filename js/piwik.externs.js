/**
 * @fileoverview Public API of JavaScript tracking client
 * Matomo - free/libre analytics platform
 *
 * @externs
 * @suppress {checkTypes}
 */

/*
 ************************************************************
 * Global data and methods used by Matomo
 ************************************************************
 */

/**
 * @param {string} [id]
 * @param {Array} [dependencies]
 * @param {Function} [factory]
 */
window.define = function(id, dependencies, factory) {};

window.define.amd = {};

window.AnalyticsTracker;

window.Matomo_Overlay_Client;

window.Matomo_Overlay_Client.initialize;

/*
 ************************************************************
 * Matomo: Public data and methods
 ************************************************************
 */

var Matomo = {}

/**
 * @deprecated
 */
window.Piwik = Matomo;

Matomo.initialized;

Matomo.JSON = function () {};

/**
 * DOM Document related methods
 */
Matomo.DOM = {};

/**
 * Adds an event listener to the given element.
 * @param element
 * @param eventType
 * @param eventHandler
 * @param useCapture  Optional
 */
Matomo.DOM.addEventListener = function (element, eventType, eventHandler, useCapture) {};

/**
 * Specify a function to execute when the DOM is fully loaded.
 *
 * If the DOM is already loaded, the function will be executed immediately.
 *
 * @param {Function} callback
 */
Matomo.DOM.onLoad = function (callback) {};

/**
 * Specify a function to execute when the DOM is ready.
 *
 * If the DOM is already ready, the function will be executed immediately.
 *
 * @param {Function} callback
 */
Matomo.DOM.onReady = function (callback) {};

/**
 * Detect whether a node is visible right now.
 */
Matomo.DOM.isNodeVisible = function () {};

/**
 * Detect whether a node has been visible at some point
 */
Matomo.DOM.isOrWasNodeVisible = function () {};

/**
 * Listen to an event and invoke the handler when a the event is triggered.
 *
 * @param {string} event
 * @param {Function} handler
 */
Matomo.on = function (event, handler) {};

/**
 * Remove a handler to no longer listen to the event. Must pass the same handler that was used when
 * attaching the event via ".on".
 * @param {string} event
 * @param {Function} handler
 */
Matomo.off = function (event, handler) {};

/**
 * Triggers the given event and passes the parameters to all handlers.
 *
 * @param {string} event
 * @param {Array} extraParameters
 * @param {Object} context  If given the handler will be executed in this context
 */
Matomo.trigger = function (event, extraParameters, context) {};

/**
 * Add plugin
 *
 * @param {string} pluginName
 * @param {Object} pluginObj
 */
Matomo.addPlugin = function (pluginName, pluginObj) {};

/**
 * Get Tracker (factory method)
 *
 * @param {string} matomoUrl
 * @param {int|string} siteId
 * @returns {Tracker}
 */
Matomo.getTracker = function (matomoUrl, siteId) {};

/**
 * Get all created async trackers
 *
 * @returns {Array<Tracker>}
 */
Matomo.getAsyncTrackers = function () {};

/**
 * Adds a new tracker. All sent requests will be also sent to the given siteId and matomoUrl.
 * If matomoUrl is not set, current url will be used.
 *
 * @param {null|string} matomoUrl  If null, will reuse the same tracker URL of the current tracker instance
 * @param {int|string} siteId
 * @returns {Tracker}
 */
Matomo.addTracker = function (matomoUrl, siteId) {};

/**
 * Get internal asynchronous tracker object.
 *
 * If no parameters are given, it returns the internal asynchronous tracker object. If a matomoUrl and idSite
 * is given, it will try to find an optional
 *
 * @param {string} matomoUrl
 * @param {int|string} siteId
 * @returns {Tracker|undefined}
 */
Matomo.getAsyncTracker = function (matomoUrl, siteId) {};

/**
 * When calling plugin methods via "_paq.push(['...'])" and the plugin is loaded separately because
 * matomo.js is not writable then there is a chance that first matomo.js is loaded and later the plugin.
 * In this case we would have already executed all "_paq.push" methods and they would not have succeeded
 * because the plugin will be loaded only later. In this case, once a plugin is loaded, it should call
 * "Matomo.retryMissedPluginCalls()" so they will be executed after all.
 */
Matomo.retryMissedPluginCalls = function () {};

/*
 ************************************************************
 * Deprecated functionality below
 * Legacy piwik.js compatibility ftw
 ************************************************************
 */

/**
 * Track page visit
 * @deprecated
 *
 * @param {string} documentTitle
 * @param {int|string} siteId
 * @param {string} matomoUrl
 * @param {*} customData
 */
window.piwik_log = function(documentTitle, siteId, matomoUrl, customData) {};

/**
 * Track click manually (function is defined below)
 * @deprecated
 *
 * @param {string} sourceUrl
 * @param {int|string} siteId
 * @param {string} matomoUrl
 * @param {string} linkType
 */
window.piwik_track = function(sourceUrl, siteId, matomoUrl, linkType) {};
