/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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
