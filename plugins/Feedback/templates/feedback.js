$(function() {
	var feedback = $('a#topmenu-feedback');
	if (feedback.size()) {
		var fbDiv = $('<div id="feedback-dialog"></div>').appendTo('body');

		$('a#topmenu-feedback').click(function() {
			if(fbDiv.html() == '') {
				fbDiv.html('<div id="feedback-loading"><img alt="" src="themes/default/images/loading-blue.gif"> '+ _pk_translate('General_Loading_js') +'</div>');
			}
			if($('#feedback-loading' ,fbDiv).length) {
				$.get(feedback.attr('href'), function(data) {
					fbDiv.html(data);
				});

				fbDiv.dialog({
					title: feedback.html(),
					bgiframe: true,
					modal: true,
					width: 650,
					position: ['center', 90],
					resizable: false,
					autoOpen: false
				});
			}
			$('#feedback-faq').show();
			$('#feedback-form').hide();
			$('#feedback-sent').hide().empty();
			fbDiv.dialog('open');
			return false;
		});
	}
	
});
