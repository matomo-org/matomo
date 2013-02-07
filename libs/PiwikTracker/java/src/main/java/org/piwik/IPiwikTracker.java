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

import java.net.URL;

/**
 *
 * @author Martin Fochler, Klaus Pfeiffer
 * @version 1.0.1
 */
public interface IPiwikTracker {

	/**
	 * Builds the URL for the page tracking request.
	 * @param pagename 
	 * @return URL 
	 */
	URL getPageTrackURL(final String pagename);

	/**
	 * Builds the URL for the download tracking request.
	 * @param downloadurl
	 * @return URl
	 */
	URL getDownloadTrackURL(final String downloadurl);

	URL getLinkTrackURL(final String linkurl);

	URL getGoalTrackURL(final String goal);

	URL getGoalTrackURL(final String goal, final String revenue);

	/**
	 * Probably was a typo. Use getDownloadTrackURL.
	 * @param downloadurl
	 * @return URL
	 */
	@Deprecated
	URL getDownloadTackURL(final String downloadurl);

	/**
	 * Probably was a typo. Use getLinkTrackURL.
	 * @param linkurl
	 * @return URL
	 */
	@Deprecated
	URL getLinkTackURL(final String linkurl);
}
