$(function () {
    function hasPageAMenu()
    {
        return $('.Menu--dashboard').size();
    }

    if (hasPageAMenu()) {
        piwikMenu = new menu();
        piwikMenu.init();
        piwikMenu.loadFirstSection();
        broadcast.init();
    } else {
        broadcast.init(true);
    }
});
