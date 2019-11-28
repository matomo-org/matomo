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

    // Ask the parent window to send us initial state of the optout cookie so that we can display the form correctly
    var numAttempts = 0;
    initializationTimer = setInterval(function() {
        var message = {maq_loaded: true};
        parent.postMessage(JSON.stringify(message), '*');
        numAttempts++;
        // 10 times per second * 1200 = 2 minutes
        // If the tracker JS hasn't finished loading by now, it ain't gonna, so let's stop trying
        if (numAttempts > 1200) {
            clearInterval(initializationTimer);
        }
    }, 100);
});

// Listener for initialization message from parent window
// This will tell us the initial state the form should be in
// based on the first-party cookie value (which we can't access directly)
window.addEventListener('message', function(e) {
    try {
        var data = JSON.parse(e.data);
    } catch (e) {
        return;
    }

    if (typeof data.maq_opted_in == 'undefined') {
        return;
    }

    updateText(data.maq_opted_in);
    // Cancel the interval so that we don't keep sending requests to the parent
    clearInterval(initializationTimer);
});