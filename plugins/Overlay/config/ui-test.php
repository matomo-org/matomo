<?php

return array(

    // Overlay needs the full URLs in order to find the links in the embedded page (otherwise the %
    // tooltips don't show up)
    'tests.ui.url_normalizer_blacklist.api' => DI\add(array(
        'Overlay.getFollowingPages',
    )),
    'tests.ui.url_normalizer_blacklist.controller' => DI\add(array(
        'Overlay.renderSidebar',
    )),

);