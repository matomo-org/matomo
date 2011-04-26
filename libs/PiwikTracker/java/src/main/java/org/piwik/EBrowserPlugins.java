/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package org.piwik;

/**
 *
 * @author Martin Fochler
 * @version 1.0.0
 */
public enum EBrowserPlugins {

    FLASH("fla"), JAVA("java"), DIRECTOR("dir"), QUICKTIME("qt"),
    REALPLAYER("realp"), PDF("pdf"), WINDOWSMEDIA("wma"), GEARS("gears"),
    SILVERLIGHT("ag");
    private String urlshort;

    EBrowserPlugins(final String urlshort) {
        this.urlshort = urlshort;
    }

    @Override
    public String toString() {
        return this.urlshort + "=true";
    }
}
