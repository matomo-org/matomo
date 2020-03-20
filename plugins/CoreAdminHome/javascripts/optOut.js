// Strips off protocol and trailing path and URL params
function getDomain(url)
{
    return url.replace(/^http[s]?:\/\//, '').replace(/\/.*/, '');
}

function addEventListener(element, eventType, eventHandler) {
    if (element.addEventListener) {
        element.addEventListener(eventType, eventHandler, false);

        return true;
    }

    if (element.attachEvent) {
        return element.attachEvent('on' + eventType, eventHandler);
    }

    element['on' + eventType] = eventHandler;
}

// Strips off protocol and trailing path and URL params
function getHostName(url)
{
    // scheme : // [username [: password] @] hostame [: port] [/ [path] [? query] [# fragment]]
    var e = new RegExp('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)'),
        matches = e.exec(url);

    return matches ? matches[1] : url;
}

function isOptIn() {
    return document.getElementById('trackVisits').checked;
}

/**
 * Check for common error conditions (cases where we know the optout will not work) and warning conditions (cases
 * where the optout may not work on some browsers).  Displays a message if warning/error conditions are encountered,
 * and also hides the checkbox in the case of an error condition.
 * @param obj message The message as received from the tracking JS
 * @returns {boolean} Whether we should display the checkbox and the optin/optout text.  Returns false if an error
 * condition was encountered, otherwise true.
 */
function showWarningIfHttp()
{
    var optOutUrl = window.location.href;
    var isHttp = optOutUrl && optOutUrl.indexOf('http:') === 0;

    if (isHttp) {
        var errorPara = document.getElementById('textError_https');
        if (errorPara) {
            errorPara.style.display = 'block';
        }
    }
}

function getDataIfMessageIsForThisFrame(e){
    if (!e || !e.data) {
        return false;
    }

    try {
        var data = JSON.parse(e.data);
    } catch (e) {
        return false;
    }

    if (!data || !data.maq_url) {
        return false;
    }

    var originHost = getHostName(data.maq_url);
    if (originHost !== getHostName(window.location.href)) {
        // just to double check it really is for this optOut script...
        return false;
    }

    return data;
}


function submitForm(e, form) {
    // Find out whether checkbox is turned on
    var optedIn = isOptIn();
    var hasOptOutChangeWorkedThroughPostMessage = null;

    // Send a message to the parent window so that it can set a first-party cookie (a fallback in case
    // third-party cookies are not permitted by the browser).
    if (typeof parent === 'object' && typeof parent.postMessage !== 'undefined') {
        addEventListener(window, 'message', function(e) {
            var data = getDataIfMessageIsForThisFrame(e);
            if (!data || typeof data.maq_confirm_opted_in === 'undefined') {
                return;
            }

            var optedIn = isOptIn(); // need to get value again as otherwise might be changed
            hasOptOutChangeWorkedThroughPostMessage = optedIn == data.maq_confirm_opted_in;
            if (!hasOptOutChangeWorkedThroughPostMessage) {
                // looks like opt out or opt in did maybe not work...
                // this might be IF eg the Matomo instance trackerUrl on the page does not match the Matomo instance optOut url...
                showWarningIfHttp();
            }
        });

        var optOutStatus = {maq_opted_in: optedIn};
        parent.postMessage(JSON.stringify(optOutStatus), "*");
    }

    // Update the text on the form
    updateText(optedIn);

    // Fire off a request to Matomo in the background, which will try to set the third-party cookie.
    // We have the first-party cookie but it's nice to set this too if we can, since it will respect the
    // user's wishes across multiple sites.
    var now = Date.now ? Date.now() : (+(new Date())); // Date.now does not exist in < IE8
    var openedWindow = window.open(form.action + '&time=' + now);

    if (openedWindow) {
        var checkWindowClosedInterval;
        checkWindowClosedInterval = setInterval(function() {
            if (openedWindow.closed) {
                clearInterval(checkWindowClosedInterval);
                checkWindowClosedInterval = null;
                if (!hasOptOutChangeWorkedThroughPostMessage) {
                    // this is not always 100% correct but better show a warning if post message hasn't completed by now.
                    // Technically, the postMessage should finish before the window.open but this might not always be the case
                    showWarningIfHttp();
                }
            }
        }, 200);
    } else {
        var errorPara = document.getElementById('textError_popupBlocker');
        if (errorPara) {
            errorPara.style.display = 'block';
        }
    }

    return false;
}

function updateText(optedIn) {
    var optInPara = document.getElementById('textOptIn');
    var optOutPara = document.getElementById('textOptOut');

    var optInLabel = document.getElementById('labelOptIn');
    var optOutLabel = document.getElementById('labelOptOut');

    var checkbox = document.getElementById('trackVisits');

    if (optedIn) {
        optInPara.style.display = 'none';
        optOutPara.style.display = 'block';
        optInLabel.style.display = 'none';
        optOutLabel.style.display = 'inline';
        checkbox.checked = true;
    } else {
        optOutPara.style.display = 'none';
        optInPara.style.display = 'block';
        optOutLabel.style.display = 'none';
        optInLabel.style.display = 'inline';
        checkbox.checked = false;
    }
}

function showWarningIfCookiesDisabled() {
    if (navigator && !navigator.cookieEnabled) {
        // Error condition: cookies disabled and Matomo not configured to opt the user out by default = they can't opt out
        var errorPara = document.getElementById('textError_cookies');
        if (errorPara) {
            errorPara.style.display = 'block';
        }

        var checkbox = document.getElementById('trackVisits');
        var optInPara = document.getElementById('textOptIn');
        var optOutPara = document.getElementById('textOptOut');
        var optInLabel = document.getElementById('labelOptIn');
        var optOutLabel = document.getElementById('labelOptOut');

        // Hide the checkbox
        checkbox.style.display = 'none';
        optInPara.style.display = 'none';
        optOutPara.style.display = 'none';
        optInLabel.style.display = 'none';
        optOutLabel.style.display = 'none';
    }
}

var initializationTimer = null;

addEventListener(document, 'DOMContentLoaded', function() {
    showWarningIfCookiesDisabled();
    
    var trackVisitsCheckbox = document.getElementById('trackVisits');
    if (trackVisitsCheckbox && typeof parent === 'object') {
        var initiallyChecked = trackVisitsCheckbox.checked;

        // Ask the parent window to send us initial state of the optout cookie so that we can display the form correctly
        var numAttempts = 0;
        function checkParentTrackerLoaded() {
            var message = {maq_initial_value: initiallyChecked};
            parent.postMessage(JSON.stringify(message), '*');
            numAttempts++;
            // 0.15 times per second * 1200 = 3 minutes
            // If the tracker JS hasn't finished loading by now, it ain't gonna, so let's stop trying
            if (numAttempts > 1200) {
                clearInterval(initializationTimer);
                initializationTimer = null;
            }
        }

        initializationTimer = setInterval(checkParentTrackerLoaded, 150);
    }
});

// Listener for initialization message from parent window
// This will tell us the initial state the form should be in
// based on the first-party cookie value (which we can't access directly)
addEventListener(window, 'message', function(e) {
    var data = getDataIfMessageIsForThisFrame(e);
    if (!data) {
        return;
    }

    if (typeof data.maq_opted_in !== 'undefined'
        && typeof data.maq_url !== 'undefined'
        && typeof data.maq_optout_by_default !== 'undefined'
    ) {
        // Cancel the interval so that we don't keep sending requests to the parent
        if (initializationTimer) {
            clearInterval(initializationTimer);
        }

        updateText(data.maq_opted_in);
    }
});