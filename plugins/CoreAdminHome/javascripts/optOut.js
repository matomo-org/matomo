function submitForm(e, form) {
    if (e.preventDefault) { // IE8 and below do not support preventDefault
        e.preventDefault();
    }

    var now = Date.now ? Date.now() : (+(new Date())), // Date.now does not exist in < IE8
    newWindow = window.open(form.action + '&time=' + now);

    var interval = setInterval(function () {
        if (newWindow.closed) {
            window.location.reload(true);
            clearInterval(interval);
        }
    }, 1000);
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    var trackVisitsCheckbox = document.getElementById('trackVisits');
    if (typeof trackVisitsCheckbox === "undefined") trackVisitsCheckbox.addEventListener('click', function(event) { submitForm(event, this.form); });
});

