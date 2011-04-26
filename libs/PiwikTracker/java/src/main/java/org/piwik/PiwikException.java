/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

package org.piwik;

/**
 *
 * @author Martin Fochler
 * @version 1.0
 */
public class PiwikException extends Exception {

    public PiwikException(final String message) {
        super(message);
    }

    public PiwikException(final String message, final Throwable e) {
        super(message,e);
    }
}
