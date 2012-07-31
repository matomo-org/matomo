/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$('.but').on('click', function(e)
{
	if($('#websiteSearch').val() != '')
		$('#websiteSearch').autocomplete('search', $('#websiteSearch').val() + '%%%');
	return false;
});

function switchSite(id, name, showAjaxLoading)
{
	$('.sites_autocomplete input').val(id);
    $('.custom_select_main_link').text(name);
    $('.custom_select_main_link').addClass('custom_select_loading');
    broadcast.propagateNewPage('idSite='+id, showAjaxLoading);
    return false;
}

// global function that is executed when the user selects a new site.
// can be overridden to customize behavior (see UsersManager)
window.autocompleteOnNewSiteSelect = function(siteId, siteName)
{
    if (siteId == 'all')
    {
    	broadcast.propagateNewPage('module=MultiSites&action=index');
    }
    else
    {
		switchSite(siteId, siteName);
    }
};

$(function() {
	if($('#websiteSearch').length == 0)
	{
		return;
	}

	$('#websiteSearch').click(function(e)
	{
		$(this).val('');
	});
	$('#websiteSearch').keyup(function(e)
	{
		if(parseInt($(this).val().length) == 0)
		{
			reset();
		}
	});
	$('#websiteSearch').autocomplete({
		minLength: 1,
		source: '?module=SitesManager&action=getSitesForAutocompleter',
		appendTo: '#custom_select_container',
		select: function(event, ui) {
			if(piwik.idSite == ui.item.id)
			{
				$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
			}
			else
			{
				if(ui.item.id > 0) {
					// set attributes of selected site display (what shows in the box)
					$("#sitesSelectionSearch .custom_select_main_link")
                		.attr('siteid', ui.item.id)
                		.text(ui.item.name);
                	// hide the dropdown
        			$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
        			// fire the site selected event
					window.autocompleteOnNewSiteSelect(ui.item.id, ui.item.name);
				} else {
					reset();
				}
			}
			return false;
		},
		focus: function(event, ui) {
			$('#websiteSearch').val(ui.item.name);
			return false;
		},
		search: function(event, ui) {
			$("#reset").show();
			$("#sitesSelectionSearch .custom_select_main_link").addClass("custom_select_loading");
		},
		open: function(event, ui) {
			var widthSitesSelection = +$("#sitesSelectionSearch ul").width(); // convert to int
			$("#sitesSelectionSearch .custom_select_main_link").removeClass("custom_select_loading");
			if(widthSitesSelection > $('#max_sitename_width').val())
			{
				$('#max_sitename_width').val(widthSitesSelection);
			}
			else
			{
				widthSitesSelection = +$('#max_sitename_width').val(); // convert to int
			}
			
			$('.custom_select_ul_list').hide();
			
			// customize jquery-ui's autocomplete positioning
			var cssToRemove = {float: 'none', position: 'static'};
			$("#siteSelect.ui-autocomplete")
				.show().width(widthSitesSelection).css(cssToRemove)
				.find('li,a').each(function () {
					$(this).css(cssToRemove);
				});
			
			$(".custom_select_block_show").width(widthSitesSelection);
		}
	}).data("autocomplete")._renderItem = function( ul, item ) {
		$(ul).attr('id', 'siteSelect');
		return $( "<li></li>" )
		.data( "item.autocomplete", item )
		.append( $( "<a></a>" ).html( item.label )
					.attr('href', piwikHelper.getCurrentQueryStringWithParametersModified('idSite='+item.id) 
									+ (broadcast.isHashExists()
												? broadcast.getHashFromUrl().replace(/idSite=[0-9]+/, 'idSite='+item.id) 
												: ""
									) ) )
		.appendTo( ul );
	};

	$('body').on('mouseup',function(e){ 
		if(!$(e.target).parents('#sitesSelectionSearch').length && !$(e.target).is('#sitesSelectionSearch') && !$(e.target).parents('#siteSelect.ui-autocomplete').length) {
			reset();
			$('#sitesSelectionSearch .custom_select_block').removeClass('custom_select_block_show');
		}
	});

	function reset()
	{
		$('#websiteSearch').val('');
		$('.custom_select_ul_list').show();
		$("#siteSelect.ui-autocomplete").hide();
		$("#reset").hide();
	}
	$("#reset").click(reset);

	// set event handling code for non-jquery-autocomplete parts of widget
    if($('.custom_select_ul_list li').length > 1) {
    	// event handler for when site selector is clicked. shows dropdown w/ first X sites
        $("#sitesSelectionSearch .custom_select_main_link").click(function(){
    		$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
    		$('.custom_select_ul_list').show();
    		$('#websiteSearch').val('').focus();
    		return false;
    	});
        $('#sitesSelectionSearch .custom_select_block').on('mouseenter', function(){
            $('.custom_select_ul_list li a').each(function(){
                var hash = broadcast.getHashFromUrl();
                hash = hash ? hash.replace(/idSite=[0-9]+/, 'idSite='+$(this).attr('siteid')) : "";
                
                var queryString = piwikHelper.getCurrentQueryStringWithParametersModified(
                	'idSite=' + $(this).attr('siteid'));
                $(this).attr('href', queryString + hash);
            });
        });

        // change selection. fire's site selector's on select event and modifies the attributes
        // of the selected link
		$('.custom_select_ul_list li a').each(function(){
            $(this).click(function (e) {
            	var idsite = $(this).attr('siteid'), name = $(this).text();
            	window.autocompleteOnNewSiteSelect(idsite, name);
            	
            	$("#sitesSelectionSearch .custom_select_main_link")
            		.attr('siteid', idsite)
            		.text(name);
            	
            	// close the dropdown
    			$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
    			
    			e.preventDefault();
            });
        });
        
        var inlinePaddingWidth = 22, staticPaddingWidth = 34;
        if($(".custom_select_block ul")[0]){
            var widthSitesSelection = Math.max($(".custom_select_block ul").width()+inlinePaddingWidth, $(".custom_select_main_link").width()+staticPaddingWidth);
            $(".custom_select_block").css('width', widthSitesSelection);
        }
    } else {
        $('.custom_select_main_link').addClass('noselect');
    }
    
    // handle multi-sites link click
    $('.custom_select_all').click(function () {
		$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
    	window.autocompleteOnNewSiteSelect('all', $('.custom_select_all>a').text());
    });
});
