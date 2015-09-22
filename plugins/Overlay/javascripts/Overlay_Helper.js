/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Overlay_Helper = {

    /** Encode the iframe url to put it behind the hash in sidebar mode */
    encodeFrameUrl: function (url) {
        // url encode + replace % with $ to make sure that browsers don't break the encoding
        return encodeURIComponent(url).replace(/%/g, '$')
    },

    /** Decode the url after reading it from the hash */
    decodeFrameUrl: function (url) {
        // reverse encodeFrameUrl()
        return decodeURIComponent(url.replace(/\$/g, '%'));
    },

    /** Get the url to launch overlay */
    getOverlayLink: function (idSite, period, date, link) {
        var url = 'index.php?module=Overlay&period=' + encodeURIComponent(period) + '&date=' + encodeURIComponent(date) + '&idSite=' + encodeURIComponent(idSite);
        if (link) {
            url += '#?l=' + Overlay_Helper.encodeFrameUrl(link);
        }
        return url;
    }

};