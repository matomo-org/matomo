/**
 * Piwik - Open source web analytics
 * 
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
 * @package PiwikTracker
 */
package org.piwik;

import java.net.HttpCookie;
import java.net.HttpURLConnection;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import javax.servlet.http.Cookie;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

/**
 * 
 * @author Martin Fochler
 */
public class ResponseData {

	/**
	 * Map to store header information.
	 */
	private Map<String, List<String>> headerData;

	/**
	 * For debug output.
	 */
	private static final Log LOGGER = LogFactory.getLog(ResponseData.class);

	/**
	 * Initialize the local header data with the header fields from the connection.
	 * Those information are needed to parse the cookie information.
	 * @param connection used to retrieve the header fields
	 */
	public ResponseData(final HttpURLConnection connection) {
		headerData = connection.getHeaderFields();
	}

	public List<Cookie> getCookies() {
		List<Cookie> cookies = new ArrayList<Cookie>();

		for (String key : headerData.keySet()) {
			List<String> headerParts = headerData.get(key);

			StringBuilder cookieInfo = new StringBuilder();
			for (String part : headerParts) {
				cookieInfo.append(part);
			}

			if (key == null && cookieInfo.toString().equals("")) {
				LOGGER.debug("No more headers, not proceeding");
				return null;
			}

			if (key == null) {
				LOGGER.debug("The header value contains the server's HTTP version, not proceeding");
			} else if (key.equals("Set-Cookie")) {
				List<HttpCookie> httpCookies = HttpCookie.parse(cookieInfo.toString());
				for (HttpCookie h : httpCookies) {
					Cookie c = new Cookie(h.getName(), h.getValue());
					c.setComment(h.getComment());
					if (h.getDomain() != null) {
						c.setDomain(h.getDomain());
					}
					c.setMaxAge(Long.valueOf(h.getMaxAge()).intValue());
					c.setPath(h.getPath());
					c.setSecure(h.getSecure());
					c.setVersion(h.getVersion());
					cookies.add(c);
				}
			} else {
				LOGGER.debug("The provided key (" + key + ") with value (" + cookieInfo
						+ ") were not processed because the key is unknown");
			}
		}
		return cookies;
	}

}
