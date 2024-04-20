/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    getOverlayLink: function (idSite, period, date, segment, link) {
        var url = 'index.php?module=Overlay&period=' + encodeURIComponent(period) + '&date=' + encodeURIComponent(date) + '&idSite=' + encodeURIComponent(idSite);

        if (segment) {
            url += '&segment=' + encodeURIComponent(segment);
        }

        var token_auth = piwik.broadcast.getValueFromUrl("token_auth");
        if (token_auth.length && piwik.shouldPropagateTokenAuth) {
            if (!piwik.broadcast.isWidgetizeRequestWithoutSession()) {
                url += '&force_api_session=1';
            }
            url += '&token_auth='  + encodeURIComponent(token_auth);
        }

        if (link) {
            url += '#?l=' + Overlay_Helper.encodeFrameUrl(link);
        }

        return url;
    }

};
