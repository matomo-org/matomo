$(function () {
    var isPageHasMenu = $('#secondNavBar').size();
    var isPageIsAdmin = $('#content.admin').size();

    if (isPageHasMenu) {
        piwikMenu = new menu();
        piwikMenu.init();
        if (isPageIsAdmin) {
            piwikMenu.activateMenu(broadcast.getValueFromUrl('module'), broadcast.getValueFromUrl('action'), '');
        } else {
            piwikMenu.loadFirstSection();
        }
    } else if (!isPageIsAdmin) {
        // eg multisites
        initTopControls();
    }

    if (isPageIsAdmin) {
        // don't use broadcast in admin page
        initTopControls();
        return;
    }

    if (isPageHasMenu) {
        broadcast.init();
    } else {
        broadcast.init(true);
    }
});
