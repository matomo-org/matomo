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
