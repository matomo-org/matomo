$(document).ready(function () {
    if ($('.nav').size()) {
        piwikMenu = new menu();
        piwikMenu.init();
        piwikMenu.loadFirstSection();
        broadcast.init();
    }
});
