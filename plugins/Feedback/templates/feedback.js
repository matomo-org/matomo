$(function() {
	var feedback = $('a#topbar-feedback');
	if (feedback.size()) {
		var fbDiv = $('<div id="feedback-dialog"></div>').appendTo('body');

		$.get(feedback.attr('href'), function(data) {
			fbDiv.html(data);
		});

		fbDiv.dialog({
			title: feedback.html(),
			bgiframe: true,
			modal: true,
			height: 480,
			width: 500,
			resizable: false,
			autoOpen: false
		});

		$('#topbar-feedback').click(function() {
			$('#feedback-faq').show();
			$('#feedback-form').hide();
			$('#feedback-sent').hide().empty();
			fbDiv.dialog('open');
			return false;
		});
	}
});
