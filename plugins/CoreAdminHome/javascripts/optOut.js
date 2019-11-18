function submitForm(e, form) {
    // Find out whether checkbox is turned on
    let optedIn = document.getElementById('trackVisits').checked;

    // Send a message to the parent window so that it can set a first-party cookie (a fallback in case
    // third-party cookies are not permitted by the browser).
    let optOutStatus = {opted_in: optedIn};
    parent.postMessage(JSON.stringify(optOutStatus), "*");

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
    debugger;
    let optInPara = document.getElementById('optedIn');
    let optOutPara = document.getElementById('optedOut');

    let optInLabel = document.getElementById('labelOptIn');
    let optOutLabel = document.getElementById('labelOptOut');

    if (optedIn) {
        optInPara.style.display = 'none';
        optOutPara.style.display = 'block';
        optInLabel.style.display = 'none';
        optOutLabel.style.display = 'inline';
    } else {
        optOutPara.style.display = 'none';
        optInPara.style.display = 'block';
        optOutLabel.style.display = 'none';
        optInLabel.style.display = 'inline';
    }


}

document.addEventListener('DOMContentLoaded', function() {
    var trackVisitsCheckbox = document.getElementById('trackVisits');
    if (typeof trackVisitsCheckbox === "undefined") trackVisitsCheckbox.addEventListener('click', function(event) { submitForm(event, this.form); });
});

window.addEventListener('message', function(e) {
    debugger;
    let data = JSON.parse(e.data);
    if (typeof data.opted_in == 'undefined') {
        return;
    }

    updateText(data.opted_in);
});