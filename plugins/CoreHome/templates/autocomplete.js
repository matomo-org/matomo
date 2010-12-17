/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$('.but').bind('click', function(e)
{
	if($('#websiteSearch').val() != '')
		$('#websiteSearch').autocomplete('search', $('#websiteSearch').val() + '%%%');
	return false;
});
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
		select: function(event, ui) {
			if(piwik.idSite == ui.item.id)
			{
				hide();
				$("#sitesSelectionSearch .custom_select_block").toggleClass("custom_select_block_show");
			}
			else
			{
				broadcast.propagateNewPage('idSite='+ui.item.id );
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
			widthSitesSelection = $("#sitesSelectionSearch ul").width();
			$("#sitesSelectionSearch .custom_select_main_link").removeClass("custom_select_loading");
			if(widthSitesSelection > $('#max_sitename_width').val())
			{
				$('#max_sitename_width').val(widthSitesSelection);
			}
			else
			{
				widthSitesSelection = $('#max_sitename_width').val();
			}

			$('#custom_select_container').append($('.ui-autocomplete'));
			$('.custom_select_ul_list').hide();
			$(".ui-autocomplete").show();
			$(".ui-autocomplete").css('top', '0px');
			$(".ui-autocomplete").css('left', '-6px');
			$(".ui-autocomplete").width(parseInt(widthSitesSelection));
			$(".custom_select_block_show").width(parseInt(widthSitesSelection));

		}
	}).data("autocomplete")._renderItem = function( ul, item ) {
		return $( "<li></li>" )
		.data( "item.autocomplete", item )
		.append( $( "<a></a>" ).html( item.label ) )
		.appendTo( ul );
	};

	function reset()
	{
		$('#websiteSearch').val('');
		$('.custom_select_ul_list').show();
		$(".ui-autocomplete").hide();
		$("#reset").hide();
	}
	$("#reset").click(function(e)
	{
		reset();
	});
});
