/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function switchSite(id, name, showAjaxLoading, idCanBeAll) {
    var $mainLink = $('.custom_select_main_link').attr('data-loading', 1);

    if (id == 'all'
        && !idCanBeAll
    ) {
        broadcast.propagateNewPage('module=MultiSites&action=index');
    }
    else {
        $('.sites_autocomplete input').val(id);
        $mainLink
            .find('span')
            .text(name);
        broadcast.propagateNewPage('segment=&idSite=' + id, showAjaxLoading);
    }
    return false;
}

$(function () {

    var reset = function (selector) {
        $('.websiteSearch', selector).val('');
        $('.custom_select_ul_list', selector).show();
        $(".siteSelect.ui-autocomplete,.reset", selector).hide();
    };

    // sets up every un-inited site selector widget
    piwik.initSiteSelectors = function () {
        function getUrlForWebsiteId(idSite) {
            var idSiteParam = 'idSite=' + idSite;
            var newParameters = 'segment=&' + idSiteParam;
            var hash = broadcast.isHashExists() ? broadcast.getHashFromUrl() : "";
            return piwikHelper.getCurrentQueryStringWithParametersModified(newParameters)
                    + '#' + piwikHelper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
        }

        $('.sites_autocomplete').each(function () {
            var selector = $(this);

            if (selector.attr('data-inited') == 1) {
                return;
            }

            selector.attr('data-inited', 1);

            var websiteSearch = $('.websiteSearch', selector);

            // when the search input is clicked, clear the input
            websiteSearch.click(function () {
                $(this).val('');
            });

            // when a key is released over the search input when empty, reset the selector
            //
            websiteSearch.keyup(function (e) {
                if (e.keyCode == 27) {
                    $('.custom_select_block', selector).removeClass('custom_select_block_show');
                    return false;
                }

                if (!$(this).val()) {
                    reset(selector);
                }
            });

            // setup the autocompleter
            websiteSearch.autocomplete({
                minLength: 1,
                source: '?module=SitesManager&action=getSitesForAutocompleter',
                appendTo: $('.custom_select_container', selector),
                select: function (event, ui) {
                    event.preventDefault();
                    if (ui.item.id > 0) {
                        // autocomplete.js allows item names to be HTML, so we have to entity the site name in PHP.
                        // to avoid double encoding, we decode before setting text.
                        // note: use of $.html() would not be future-proof.
                        ui.item.name = piwikHelper.htmlDecode(ui.item.name);

                        // set attributes of selected site display (what shows in the box)
                        $('.custom_select_main_link', selector)
                            .attr('data-siteid', ui.item.id)
                            .html($('<span/>').text(ui.item.name));

                        // hide the dropdown
                        $('.custom_select_block', selector).removeClass('custom_select_block_show');

                        // fire the site selected event
                        selector.trigger('piwik:siteSelected', ui.item);
                    }
                    else {
                        reset(selector);
                    }

                    return false;
                },
                focus: function (event, ui) {
                    $('.websiteSearch', selector).val(ui.item.name);
                    return false;
                },
                search: function (event, ui) {
                    $('.reset', selector).show();
                    $('.custom_select_main_link', selector).attr('data-loading', 1);
                },
                open: function (event, ui) {
                    var widthSitesSelection = +$('.custom_select_ul_list', selector).width();

                    $('.custom_select_main_link', selector).attr('data-loading', 0);

                    var maxSitenameWidth = $('.max_sitename_width', selector);
                    if (widthSitesSelection > maxSitenameWidth.val()) {
                        maxSitenameWidth.val(widthSitesSelection);
                    }
                    else {
                        maxSitenameWidth = +maxSitenameWidth.val(); // convert to int
                    }

                    $('.custom_select_ul_list', selector).hide();

                    // customize jquery-ui's autocomplete positioning
                    var cssToRemove = {float: 'none', position: 'static'};
                    $('.siteSelect.ui-autocomplete', selector)
                        .show().width(widthSitesSelection).css(cssToRemove)
                        .find('li,a').each(function () {
                            $(this).css(cssToRemove);
                        });

                    $('.custom_select_block_show', selector).width(widthSitesSelection);
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                $(ul).addClass('siteSelect');
                var linkUrl = getUrlForWebsiteId(item.id);
                var link = $("<a></a>").html(item.label).attr('href', linkUrl),
                    listItem = $('<li></li>');

                listItem.data("item.ui-autocomplete", item)
                    .append(link)
                    .appendTo(ul);

                return listItem;
            };

            // when the reset button is clicked, reset the site selector
            $('.reset', selector).click(reset);

            // when mouse button is released on body, check if it is not over the site selector, and if not
            // close it
            $('body').on('mouseup', function (e) {
                var closestSelector = $(e.target).closest('.sites_autocomplete');
                if (!closestSelector.length || !closestSelector.is(selector)) {
                    if ($('.custom_select_block', selector).hasClass('custom_select_block_show')) {
                        reset(selector);
                        $('.custom_select_block', selector).removeClass('custom_select_block_show');
                    }
                }
            });

            // set event handling code for non-jquery-autocomplete parts of widget
            if ($('li', selector).length > 1) {

                // event handler for when site selector is clicked. shows dropdown w/ first X sites
                $(".custom_select", selector).click(function(e) {
                    if(!$(e.target).parents('.custom_select_block').length) {
                        $(".custom_select_block", selector).toggleClass("custom_select_block_show");
                        $(".websiteSearch", selector).val("").focus();
                    }
                    return false;
                });

                $('.custom_select_block', selector).on('mouseenter', function() {
                    $('.custom_select_ul_list > li > a', selector).each(function() {
                        var idSite = $(this).attr('data-siteid');
                        var linkUrl = getUrlForWebsiteId(idSite);
                        $(this).attr('href', linkUrl);
                    });
                });

                // change selection. fire's site selector's on select event and modifies the attributes
                // of the selected link
                $('.custom_select_ul_list li a', selector).each(function() {
                    $(this).click(function (e) {
                        var idsite = $(this).attr('data-siteid'),
                            name = $(this).text(),
                            mainLinkElem = $(".custom_select_main_link", selector),
                            mainLinkSpan = $('span', mainLinkElem),
                            oldName = mainLinkSpan.text();

                        mainLinkElem.attr('data-siteid', idsite);
                        mainLinkSpan.text(name);
                        $(this).text(oldName);

                        selector.trigger('piwik:siteSelected', {id: idsite, name: name});

                        // close the dropdown
                        $(".custom_select_block", selector).removeClass("custom_select_block_show");

                        e.preventDefault();
                    });
                });
            }

            // handle multi-sites link click (triggers site selected event w/ id=all)
            $('.custom_select_all', selector).click(function () {
                $(".custom_select_block", selector).toggleClass("custom_select_block_show");

                selector.trigger('piwik:siteSelected', {id: 'all', name: $('.custom_select_all>a', selector).text()});
            });

            // handle submit button click
            $('.but', selector).on('click', function (e) {
                if (websiteSearch.val() != '') {
                    websiteSearch.autocomplete('search', websiteSearch.val() + '%%%');
                }
                return false;
            });

            // if the data-switch-site-on-select attribute is set to 1 on the selector, set
            // a default handler for piwik:siteSelected that switches the current site
            // otherwise only update the input
            selector.bind('piwik:siteSelected', function (e, site) {
                if (1 == $(this).attr('data-switch-site-on-select')) {
                    if (piwik.idSite !== site.id) {
                        switchSite(site.id, site.name);
                    }
                } else {
                    $('input', this).val(site.id);
                }
            });
        });
    };
});
