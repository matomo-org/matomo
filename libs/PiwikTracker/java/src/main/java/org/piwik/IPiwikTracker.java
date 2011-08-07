/**
 * Piwik - Open source web analytics
 * 
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version $Id$
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
 * @package PiwikTracker
 */
package org.piwik;

import java.net.URL;

/**
 *
 * @author Martin Fochler
 * @version 1.0.0
 */
public interface IPiwikTracker {

    URL getPageTrackURL(final String pagename);

    URL getDownloadTackURL(final String downloadurl);

    URL getLinkTackURL(final String linkurl);

    URL getGoalTrackURL(final String goal);

    URL getGoalTrackURL(final String goal, final String revenue);
}
