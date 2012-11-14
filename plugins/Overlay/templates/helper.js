/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Overlay_Helper = {
	
	/** Encode the iframe url to put it behind the hash in sidebar mode */
	encodeFrameUrl: function(url) {
		// make sure there's only one hash in the resulting url
		return url.replace(/#/g, '%23')
	},
	
	/** Decode the url after reading it from the hash */
	decodeFrameUrl: function(url) {
		return url.replace(/%23/g, '#');
	},
	
	/** Get the url to launch overlay */
	getOverlayLink: function(idSite, period, date, link) {
		var url = 'index.php?module=Overlay&period=' + period + '&date=' + date + '&idSite=' + idSite;
		if (link) {
			url += '#' + Overlay_Helper.encodeFrameUrl(link);
		}
		return url;
	}
	
};