/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function() {
	function toggleBlock(id, value) {
		$('#' + id).toggle(value == 1);
	}
	
	// reloads purged database size estimate
	var currentRequest;
	function reloadDbStats() {
		if (currentRequest) {
			currentRequest.abort();
		}
		
		$('#deleteDataEstimate').html('');
		$('#deleteDataEstimateSect .loadingPiwik').show();
		
		currentRequest = $.ajax({
			type: 'GET',
			url: 'index.php?module=PrivacyManager&action=getDatabaseSize',
			dataType: 'html',
			async: true,
			error: piwikHelper.ajaxHandleError,		// Callback when the request fails
			data: $('#formDeleteSettings').serialize(),
			success: function(data) {
				currentRequest = undefined;
				$('#deleteDataEstimateSect .loadingPiwik').hide();
				$('#deleteDataEstimate').html(data);
			}
		});
	}
	
	// make sure certain sections only display if their corresponding features are enabled
	$('input[name=anonymizeIPEnable]').click(function() {
		toggleBlock("anonymizeIPenabled", $(this).val());
	});

	$('input[name=deleteEnable]').click(function() {
		toggleBlock("deleteLogSettings", $(this).val());
	}).change(reloadDbStats);
	
	$('input[name=deleteReportsEnable]').click(function() {
		toggleBlock("deleteReportsSettings", $(this).val());
	}).change(reloadDbStats);
	
	// initial toggling calls
	$(function() {
		toggleBlock("deleteLogSettings", $("input[name=deleteEnable]:checked").val());
		toggleBlock("anonymizeIPenabled", $("input[name=anonymizeIPEnable]:checked").val());
		toggleBlock("deleteReportsSettings", $("input[name=deleteReportsEnable]:checked").val());
	});
	
	// make sure the DB size estimate is reloaded every time a delete logs/reports setting is changed
	$('#formDeleteSettings input[type=text]').each(function() {
		$(this).change(reloadDbStats);
	});
	$('#formDeleteSettings input[type=checkbox]').each(function() {
		$(this).click(reloadDbStats);
	});
	
	// make sure when the delete log/report settings are submitted, a confirmation popup is
	// displayed first
	$('#deleteLogSettingsSubmit').click(function(e) {
		var deletingLogs = $("input[name=deleteEnable]:checked").val() == 1,
			deletingReports = $("input[name=deleteReportsEnable]:checked").val() == 1,
			confirm_id;
		
		// hide all confirmation texts, then show the correct one based on what
		// type of deletion is enabled.
		$('#confirmDeleteSettings>h2').each(function() {
			$(this).hide();
		});
		
		if (deletingLogs)
		{
			confirm_id = deletingReports ? "deleteBothConfirm" : "deleteLogsConfirm";
		}
		else if (deletingReports)
		{
			confirm_id = "deleteReportsConfirm";
		}
		
		if (confirm_id)
		{
			$("#" + confirm_id).show();
			e.preventDefault();
			
			piwikHelper.modalConfirm('#confirmDeleteSettings', {
				yes: function() {
					$('#formDeleteSettings').submit();
				}
			});
		}
		else
		{
			$('#formDeleteSettings').submit();
		}
	});
	
	// execute purge now link click
	$('#purgeDataNowLink').click(function(e) {
		e.preventDefault();
		
		var link = this;
		
		// if any option has been modified, abort purging and instruct user to save first
		var modified = false;
		$('#formDeleteSettings input').each(function() {
			if (this.type === 'checkbox' || this.type === 'radio') {
			  modified |= this.defaultChecked !== this.checked;
			} else {
			  modified |= this.defaultValue !== this.value;
			}
		});
		
		if (modified) {
			piwikHelper.modalConfirm('#saveSettingsBeforePurge', {yes: function() {}});
			return;
		}
		
		// ask user if they really want to delete their old data
		piwikHelper.modalConfirm('#confirmPurgeNow', {
			yes: function() {
				$(link).hide();
				$('#deleteSchedulingSettings .loadingPiwik').show();
		
				// execute a data purge
				$.ajax({
					type: 'POST',
					url: 'index.php?module=PrivacyManager&action=executeDataPurge',
					dataType: 'html',
					async: true,
					error: piwikHelper.ajaxHandleError,		// Callback when the request fails
					success: function(data) { // ajax request will return new database estimate
						$('#deleteSchedulingSettings .loadingPiwik').hide();
						$(link).show();
						$('#deleteDataEstimate').html(data);
					}
				});
			}
		});
	});
});
