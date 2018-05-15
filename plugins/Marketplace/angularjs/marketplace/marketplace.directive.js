/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-marketplace>
 */
(function () {

    angular.module('piwikApp').directive('piwikMarketplace', piwikMarketplace);

    piwikMarketplace.$inject = ['piwik', '$timeout'];

    function piwikMarketplace(piwik, $timeout){

        return {
            restrict: 'A',
            compile: function (element, attrs) {

                return function (scope, element, attrs) {

                    $timeout(function () {


                        $('.installAllPaidPlugins').click(function (event) {
                            event.preventDefault();

                            piwikHelper.modalConfirm('#installAllPaidPluginsAtOnce');
                        });

                        // Keeps the plugin descriptions the same height
                        $('.marketplace .plugin .description').dotdotdot({
                            after: 'a.more',
                            watch: 'window'
                        });

                        function syncMaxHeight2 (selector) {

                            if (!selector) {
                                return;
                            }

                            var $nodes = $(selector);

                            if (!$nodes || !$nodes.length) {
                                return;
                            }

                            var maxh3 = null;
                            var maxMeta = null;
                            var maxFooter = null;
                            var nodesToUpdate = [];
                            var lastTop = 0;
                            $nodes.each(function (index, node) {
                                var $node = $(node);
                                var top   = $node.offset().top;

                                if (lastTop !== top) {
                                    nodesToUpdate = [];
                                    lastTop = top;
                                    maxh3 = null;
                                    maxMeta = null;
                                    maxFooter = null;
                                }

                                nodesToUpdate.push($node);

                                var heightH3 = $node.find('h3').height();
                                var heightMeta = $node.find('.metadata').height();
                                var heightFooter = $node.find('.footer').height();

                                if (!maxh3) {
                                    maxh3 = heightH3;
                                } else if (maxh3 < heightH3) {
                                    maxh3 = heightH3;
                                }

                                if (!maxMeta) {
                                    maxMeta = heightMeta;
                                } else if (maxMeta < heightMeta) {
                                    maxMeta = heightMeta;
                                }

                                if (!maxFooter) {
                                    maxFooter = heightFooter;
                                } else if (maxFooter < heightFooter) {
                                    maxFooter = heightFooter;
                                }

                                $.each(nodesToUpdate, function (index, $node) {
                                    if (maxh3) {
                                        $node.find('h3').height(maxh3 + 'px');
                                    }
                                    if (maxMeta) {
                                        $node.find('.metadata').height(maxMeta + 'px');
                                    }
                                    if (maxFooter) {
                                        $node.find('.footer').height(maxFooter + 'px');
                                    }
                                });
                            });
                        }
                        syncMaxHeight2('.marketplace .plugin');

                    });
                };
            }
        };
    }
})();