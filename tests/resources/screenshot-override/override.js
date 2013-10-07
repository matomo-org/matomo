(function ($) {

    var DEBUG_LOGGING = true;
    
    if (DEBUG_LOGGING) {
        var log = function(message) {
            console.log(message);
        };
    } else {
        var log = function() {};
    }

    var triggerRenderInsane = function () {
        console.log("__AJAX_DONE__");
    };
    
    var triggerRender = function () {
        if (window.globalAjaxQueue.active === 0) { // sanity check
            triggerRenderInsane();
        }
    };

    var triggerRenderIfNoAjax = function () {
        setTimeout(function () { // allow other javascript to execute in case they execute ajax/add images/set the src of images
            if (window.globalAjaxQueue.active === 0) {
                $('body').waitForImages({
                    waitForAll: true,
                    finished: function () {
                        // wait some more to make sure other javascript is executed & the last image is rendered
                        setTimeout(triggerRender, 10000);
                    },
                });
            }
        }, 1);
    };

    window.piwik = window.piwik || {};
    window.piwik.ajaxRequestFinished = triggerRenderIfNoAjax;
    window.piwik._triggerRenderInsane = triggerRenderInsane;

}(jQuery));