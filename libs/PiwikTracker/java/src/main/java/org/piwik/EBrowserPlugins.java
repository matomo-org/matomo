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

/**
 *
 * @author Martin Fochler
 * @version 1.0.1
 */
public enum EBrowserPlugins {

	/**
	 *  Browserplugins.
	 */
	FLASH("fla"), JAVA("java"), DIRECTOR("dir"), QUICKTIME("qt"),
	REALPLAYER("realp"), PDF("pdf"), WINDOWSMEDIA("wma"), GEARS("gears"),
	SILVERLIGHT("ag");

	/**
	 * The short URL.
	 */
	private String urlshort;

	/**
	 * Constructor that sets the short URL.
	 * @param urlshort 
	 */
	EBrowserPlugins(final String urlshort) {
		this.urlshort = urlshort;
	}

	@Override
	public String toString() {
		return this.urlshort + "=true";
	}
}
