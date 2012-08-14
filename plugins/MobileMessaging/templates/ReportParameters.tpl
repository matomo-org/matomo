<script>
	$(function() {ldelim}
		resetReportParametersFunctions ['{$reportType}'] =
				function () {ldelim}

					var	reportParameters = {ldelim}
							'phoneNumbers' : [],
						{rdelim};

					updateReportParametersFunctions['{$reportType}'](reportParameters);
				{rdelim};

		updateReportParametersFunctions['{$reportType}'] =
				function (reportParameters) {ldelim}

					if(reportParameters == null) return;

					$('[name=phoneNumbers]').removeProp('checked');
					$(reportParameters.phoneNumbers).each(function(index, phoneNumber) {ldelim}
						$('#\\'+phoneNumber).prop('checked','checked');
					{rdelim});
				{rdelim};

		getReportParametersFunctions['{$reportType}'] =
				function () {ldelim}

					var parameters = Object();

					var selectedPhoneNumbers =
						$.map(
							$('[name=phoneNumbers]:checked'),
							function (phoneNumber) {ldelim}
								return $(phoneNumber).attr('id');
							{rdelim}
						);

					// returning [''] when no phone numbers are selected avoids the "please provide a value for 'parameters'" error message
					parameters.phoneNumbers =
							selectedPhoneNumbers.length > 0 ? selectedPhoneNumbers : [''];

					return parameters;
				{rdelim};
	{rdelim});
</script>

<tr class='{$reportType}'>
	<td class="first">
		{'MobileMessaging_MobileReport_PhoneNumbers'|translate}
	</td>
	<td>
		{if $phoneNumbers|@count eq 0}
			<div class="entityInlineHelp">
			{'MobileMessaging_MobileReport_NoPhoneNumbers'|translate}
		{else}
			{foreach from=$phoneNumbers item=phoneNumber}
				<label><input name='phoneNumbers' type='checkbox' id='{$phoneNumber}'/>{$phoneNumber}</label><br/>
			{/foreach}
			<div class="entityInlineHelp">
				{'MobileMessaging_MobileReport_AdditionalPhoneNumbers'|translate}
		{/if}
			<a href='{url module="MobileMessaging" updated=null}'>{'MobileMessaging_MobileReport_MobileMessagingSettingsLink'|translate}</a>
			</div>
	</td>
</tr>
