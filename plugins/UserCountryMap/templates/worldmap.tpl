<script type="text/javascript">

{literal}

$(document).ready(function() {
	var fv = {};
	
	var params = {
		menu: "false",
		scale: "noScale",
		allowFullscreen: "true",
		allowScriptAccess: "always",
		allowNetworking: "true",
		wmode: "opaque",
		bgcolor: "#FFFFFF"
	};
	
{/literal}

	$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
	if ($.browser.chrome) $.browser.safari = false;
	
	fv.dataUrl = encodeURIComponent("{$dataUrl}");
	fv.hueMin = {$hueMin};
	fv.hueMax = {$hueMax};
	fv.satMin = {$satMin};
	fv.satMax = {$satMax};
	fv.lgtMin = {$lgtMin};
	fv.lgtMax = {$lgtMax};
	fv.iconOffset = $('#userCountryMapSelectMetrics').width()+13+($.browser.safari ? 22 : 0);
	fv.defaultMetric = "{$defaultMetric}";
	
	fv.txtLoading = encodeURIComponent("{'General_Loading_js'|translate}");
	fv.txtLoadingData = encodeURIComponent("{'General_LoadingData'|translate}");
	fv.txtToggleFullscreen = encodeURIComponent("{'UserCountryMap_toggleFullscreen'|translate}");
	fv.txtExportImage = encodeURIComponent("{'General_ExportAsImage_js'|translate}");

{literal}	
	
	var attr = { id:"UserCountryMap" };
	swfobject.embedSWF("plugins/UserCountryMap/PiwikMap.swf", "UserCountryMap_map", "100%", "300", 
		"9.0.0", "libs/swfobject/expressInstall.swf", fv, params, attr);
	
	$("#userCountryMapSelectMetrics").change(function(el) {
		$("#UserCountryMap")[0].changeMode(el.currentTarget.value);
	});
	
	$(".userCountryMapFooterIcons a.tableIcon[var=fullscreen]").click(function() {
		$("#UserCountryMap")[0].setFullscreenMode();
	});
	
	$(".userCountryMapFooterIcons a.tableIcon[var=export_png]").click(function() {
		$("#UserCountryMap")[0].exportPNG();
	});
	
	$(window).resize(function() {
		$("#UserCountryMap")[0].height = Math.round($('#UserCountryMap').width() *.55);
		$("#UserCountryMap")[0].setIconOffset($('#userCountryMapSelectMetrics').width()+13+($.browser.safari ? 22 : 0));
	});
	
	$("#UserCountryMap")[0].height = Math.round($('#widgetUserCountryMapworldMap').width() *.55);
	$("#UserCountryMap")[0].setIconOffset($('#userCountryMapSelectMetrics').width()+13);
});

{/literal}

</script>
<div id="UserCountryMap_content" style="position:relative; overflow:hidden;">
	<div id="UserCountryMap_map">{'General_RequiresFlash'|translate}</div>
	<div style="height:3px"></div>
	<select id="userCountryMapSelectMetrics" style="height: 19px; font-size: 10px; position:absolute; left: 5px; bottom: 7px;">
		{foreach from=$metrics item=metric}
			<option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
		{/foreach}
	</select>
{*<div class="dataTableFooterIcons userCountryMapFooterIcons">
	<div class="dataTableFooterWrap" var="table">
		<div class="tableIconsGroup">
			<select id="userCountryMapSelectMetrics" style="height: 19px; font-size: 10px;">
				{foreach from=$metrics item=metric}
					<option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
				{/foreach}
			</select>
		</div>
		<div class="tableIconsGroup">
			<span class="exportToFormatIcons"><a class="tableIcon" var="export_png"><img src="themes/default/images/image.png" title="{'General_ExportAsImage_js'|translate}" height="16" width="16"></a></span>
		</div>
	</div>

</div>*}
</div>