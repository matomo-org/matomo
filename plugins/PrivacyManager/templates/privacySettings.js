/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function toggleBlock(id, value) {
	$('#' + id).toggle(value == 1);
}

$(document).ready(function() {
	$(function() {
		toggleBlock("deleteLogSettings", $("input[name=deleteEnable]:checked").val());
		toggleBlock("anonymizeIPenabled", $("input[name=anonymizeIPEnable]:checked").val());
	});

	$('input[name=anonymizeIPEnable]').click(function() {
		toggleBlock("anonymizeIPenabled", $(this).val());
	});

	$('input[name=deleteEnable]').click(function() {
		toggleBlock("deleteLogSettings", $(this).val());
	});
});
