$(function() {
	// initialize
	var feedback = $('a#topbar-feedback');
	if (feedback.size()) {
		var iframe = $('<iframe src="' + feedback.attr('href') + '" style="width:450px !important;" width="450"></iframe>').appendTo('body');
		iframe.dialog({
			title: feedback.html(),
			bgiframe: true,
			modal: true,
			height: 480,
			width: 450,
			resizable: false,
			autoOpen: false
		});

		$('#topbar-feedback').click(function() {
			iframe.dialog('open');
			return false;
		});
	}
});
