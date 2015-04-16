/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

 /**
 * Jquery script to auto-expire user session after N days of inactivity.
 *
 * Fix for issue: https://github.com/piwik/piwik/issues/7316
 */

(function ($) {

    $(document).ready(function () {
    	var idleTimer = null,
		    idleState = false,
            /**
             *  idleWait -  number of milliseconds after which idle user session should be destroyed 
             *  by redirecting automatically to logout page
             *
             *  => Sample Values (Give appropriate value as per Piwik's collective desicion)
             *  idleWait = 86400000;  // no. of milliseconds in 1 day
             *  idleWait = 172800000; // no. of milliseconds in 2 day
             *  idleWait = 259200000; // no. of milliseconds in 3 day
             *  idleWait = 345600000; // no. of milliseconds in 4 day
             *  idleWait = 432000000; // no. of milliseconds in 5 day
            */
		    idleWait = 259200000; // user idle session expiry time is set at 3 day

        // when none of these events occurs after the prolonged auto-expiry time has passed, 
        //we redirect to logout page. Add any missing events to 'bind' if any.    
        $('*').bind('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function () {
            // clear idleTimer
            clearTimeout(idleTimer);
                    
            if (idleState == true) {
                // Reactivated event. Do nothing
            }
            
            idleState = false;
            
            idleTimer = setTimeout(function () {
                // Idle Event
                var protocol = window.location.protocol;
               	var host = window.location.hostname;
                var loginRedirectUrl = protocol + '//' + host + '/piwik/index.php?module=Login&action=logout';
                /*
                 *  This alert is for developers willing to test the functionality 
                 *  by setting idleTime=5000 (5 millisecond) or so. Uncomment alert and change idleTime to test.
                */ 
               	//alert('You are idle for a prolonged period of time and so redirected back to login page!');
               	window.location.replace(loginRedirectUrl);
                idleState = true; }, idleWait);
        });
        
        $("body").trigger("mousemove");
    
    });
}) (jQuery)
