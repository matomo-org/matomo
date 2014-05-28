/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    var languageSelector = $("#languageSelection");

    // no Language sector on the page
    if (languageSelector.size() == 0) return;

    languageSelector.find("input").hide();
    var select = $("#language").hide();
    var langSelect = $("<a>")
        .insertAfter(select)
        .text(select.children(':selected').text())
        .autocomplete({
            delay: 0,
            position: { my : "right top", at: "right bottom" },
            minLength: 0,
            appendTo: '#languageSelection',
            source: function (request, response) {
                response(select.children("option").map(function () {
                    var text = $(this).text();
                    return {
                        label: text,
                        value: this.value,
                        title: $(this).attr('title'),
                        href: $(this).attr('href'),
                        option: this
                    };
                }));
            },
            select: function (event, ui) {
                event.preventDefault();
                ui.item.option.selected = true;
                if (ui.item.value) {
                    langSelect.text(ui.item.label);
                    $('#languageSelection').find('form').submit();
                } else if (ui.item.href) {
                    window.open(ui.item.href);
                }
            }
        })
        .click(function () {
            // close if already visible
            if ($(this).autocomplete("widget").is(":visible")) {
                $(this).autocomplete("close");
                return;
            }

            // pass empty string as value to search for, displaying all results
            $(this).autocomplete("search", "");
        });

    langSelect.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        $(ul).attr('id', 'languageSelect');
        return $( "<li></li>" )
            .data( "item.ui-autocomplete", item )
            .append( "<a title=\"" + item.title + "\" href=\"javascript:;\">" + item.label + "</a>" )
            .appendTo( ul );
    };

    $('body').on('mouseup', function (e) {
        if (!$(e.target).parents('#languageSelection').length && !$(e.target).is('#languageSelection') && !$(e.target).parents('#languageSelect').length) {
            langSelect.autocomplete("close");
        }
    });
});
