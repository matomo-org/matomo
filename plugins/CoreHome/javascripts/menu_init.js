$(function () {
    if ($('.Menu--dashboard').size()) {
        piwikMenu = new menu();
        piwikMenu.init();
        piwikMenu.loadFirstSection();
        broadcast.init();
    }
});
