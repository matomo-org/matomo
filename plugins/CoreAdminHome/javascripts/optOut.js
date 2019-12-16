function submitForm(e, form) {
    // Find out whether checkbox is turned on
    var optedIn = document.getElementById('trackVisits').checked;

    // Send a message to the parent window so that it can set a first-party cookie (a fallback in case
    // third-party cookies are not permitted by the browser).
    if (typeof parent.postMessage !== 'undefined') {
        var optOutStatus = {maq_opted_in: optedIn};
        parent.postMessage(JSON.stringify(optOutStatus), "*");
    }

    // Update the text on the form
    updateText(optedIn);

    // Fire off a request to Matomo in the background, which will try to set the third-party cookie.
    // We have the first-party cookie but it's nice to set this too if we can, since it will respect the
    // user's wishes across multiple sites.
    var now = Date.now ? Date.now() : (+(new Date())); // Date.now does not exist in < IE8
    window.open(form.action + '&time=' + now);

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

var initializationTimer = null;

document.addEventListener('DOMContentLoaded', function() {
    var trackVisitsCheckbox = document.getElementById('trackVisits');
    if (typeof trackVisitsCheckbox === "undefined") trackVisitsCheckbox.addEventListener('click', function(event) { submitForm(event, this.form); });
    var initiallyChecked = trackVisitsCheckbox.checked;

    // Ask the parent window to send us initial state of the optout cookie so that we can display the form correctly
    var numAttempts = 0;
    initializationTimer = setInterval(function() {
        var message = {maq_initial_value: initiallyChecked};
        parent.postMessage(JSON.stringify(message), '*');
        numAttempts++;
        // 10 times per second * 1200 = 2 minutes
        // If the tracker JS hasn't finished loading by now, it ain't gonna, so let's stop trying
        if (numAttempts > 1200) {
            clearInterval(initializationTimer);
        }
    }, 100);
});

/**
 * Check for common error conditions (cases where we know the optout will not work) and warning conditions (cases
 * where the optout may not work on some browsers).  Displays a message if warning/error conditions are encountered,
 * and also hides the checkbox in the case of an error condition.
 * @param obj message The message as received from the tracking JS
 * @returns {boolean} Whether we should display the checkbox and the optin/optout text.  Returns false if an error
 * condition was encountered, otherwise true.
 */
function checkForWarnings(message)
{
    var optOutUrl = window.location.href;
    var isHttps = optOutUrl.startsWith('https') && message.maq_url.startsWith('https');
    var optOutDomain = getDomain(optOutUrl);
    var matomoDomain = getDomain(message.maq_url);

    var errorMessage = null;
    var isError = false;
    if ((!navigator.cookieEnabled) && (!message.maq_optout_by_default)) {
        // Error condition: cookies disabled and Matomo not configured to opt the user out by default = they can't opt out
        errorMessage = 'The tracking opt-out feature requires cookies to be enabled.';
        isError = true;
    } else if (!isHttps) {
        // Warning condition: not on HTTPS. On some browsers the third-party opt-out cookie won't work.
        errorMessage = 'The tracking opt-out feature may not work because this site was not loaded over HTTPS.';
    } else if (optOutDomain != matomoDomain) {
        // Warning condition: mismatched domains for optout and Matomo JS scripts. Cookies may not work as expected.
        errorMessage = 'The tracking Opt-out feature may not work because it was embedded incorrectly.';
    }

    var optInPara = document.getElementById('textOptIn');
    var optOutPara = document.getElementById('textOptOut');
    var errorPara = document.getElementById('textError');

    var optInLabel = document.getElementById('labelOptIn');
    var optOutLabel = document.getElementById('labelOptOut');

    var checkbox = document.getElementById('trackVisits');

    if (isError) {
        // Hide the checkbox
        checkbox.style.display = 'none';
        optInPara.style.display = 'none';
        optOutPara.style.display = 'none';
        optInLabel.style.display = 'none';
        optOutLabel.style.display = 'none';
    }

    if (errorMessage != null) {
        errorPara.innerText = errorMessage;
        errorPara.style.display = 'block';
    }
    
    return !isError;
}

// Strips off protocol and trailing path and URL params
function getDomain(url)
{
    return url.replace(/^http[s]?:\/\//, '').replace(/\/.*/, '');
}

// Listener for initialization message from parent window
// This will tell us the initial state the form should be in
// based on the first-party cookie value (which we can't access directly)
window.addEventListener('message', function(e) {
    try {
        var data = JSON.parse(e.data);
    } catch (e) {
        return;
    }

    if (typeof data.maq_opted_in == 'undefined' 
        || typeof data.maq_url == 'undefined'
        || typeof data.maq_optout_by_default == 'undefined'
    ) {
        return;
    }

    var okToDisplay = checkForWarnings(data);

    if (okToDisplay) {
        updateText(data.maq_opted_in);
    }

    // Cancel the interval so that we don't keep sending requests to the parent
    clearInterval(initializationTimer);
});