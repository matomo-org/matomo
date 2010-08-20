/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/*global Piwik getToken */

Piwik.addPlugin('testPlugin', {
	/*
	 * called when tracker instantiated
	 * - function or string to be eval()'d
	 */
	run: function (registerHookCallback) {
		registerHookCallback('test', '{ _isSiteHostName : isSiteHostName, _getClassesRegExp : getClassesRegExp, _hasCookies : hasCookies, _getCookie : getCookie, _setCookie : setCookie, _escape : escapeWrapper, _unescape : unescapeWrapper, _getLinkType : getLinkType, _beforeUnloadHandler : beforeUnloadHandler, _stringify : stringify }');
	},

	/*
	 * called when DOM ready
	 */
	load: function () { },

	/*
	 * function called on trackPageView
	 * - returns URL components to be appended to tracker URL
	 */
	log: function () {
		return '';
	},

	/*
	 * function called on trackLink() or click event
	 * - returns URL components to be appended to tracker URL
	 */
	click: function () {
		return '&data=' + encodeURIComponent('{"token":"' + getToken() + '"}');
	},

	/*
	 * function called on trackGoal()
	 * - returns URL components to be appended to tracker URL
	 */
	goal: function () {
		return '&data=' + encodeURIComponent('{"token":"' + getToken() + '"}');
	},

	/*
	 * called before page is unloaded
	 */
	unload: function () { }
});
