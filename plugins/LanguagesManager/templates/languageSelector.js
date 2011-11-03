/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function() {
	// no Language sector on the page
	if($("#languageSelection").size() == 0) return false;
	
    $("#languageSelection input").hide();
    var select = $("#language").hide();
    var langSelect = $( "<a>" )
    .insertAfter( select )
    .text( select.children(':selected').text() )
    .autocomplete({
        delay: 0,
        minLength: 0,
        appendTo: '#languageSelection',
        source: function( request, response ) {
            response( select.children( "option" ).map(function() {
                var text = $( this ).text();
                    return {
                        label: text,
                        value: this.value,
                        title: $(this).attr('title'),
                        href: $(this).attr('href'),
                        option: this
                    };
            }) );
        },
        select: function( event, ui ) {
            ui.item.option.selected = true;
            if(ui.item.value) {
                langSelect.text(ui.item.label);
                $('#languageSelection form').submit();
            } else if(ui.item.href) {
                window.open(ui.item.href);
            }
        }
    })
    .click(function() {
        // close if already visible
        if ( $(this).autocomplete( "widget" ).is(":visible") ) {
            $(this).autocomplete("close");
            return;
        }

        // pass empty string as value to search for, displaying all results
        $(this).autocomplete( "search", "" );
    });

    langSelect.data( "autocomplete" )._renderItem = function( ul, item ) {
        $(ul).attr('id', 'languageSelect');
        return $( "<li></li>" )
            .data( "item.autocomplete", item )
            .append( "<a title=\"" + item.title + "\" href=\"" + $('#languageSelection form').attr('action') + "&language=" + item.value + "\">" + item.label + "</a>" )
            .appendTo( ul );
    };

    $('body').on('mouseup',function(e){ 
        if(!$(e.target).parents('#languageSelection').length && !$(e.target).is('#languageSelection') && !$(e.target).parents('#languageSelect').length) {
            langSelect.autocomplete("close");
        }
    });
});
