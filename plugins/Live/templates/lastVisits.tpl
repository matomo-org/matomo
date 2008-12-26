<div id="visits">
{foreach from=$visitors item=visitor}
	<div class="visit{if $visitor.idVisit % 2} alt{/if}">
		<div style="display:none" class="idvisit">{$visitor.idVisit}</div>
		<div class="datetime">{$visitor.serverDatePretty}<br/>{$visitor.serverTimePretty}</div>
		<div class="country"><img src="{$visitor.countryFlag}" title="{$visitor.country}, Provider {$visitor.provider}"></div>
		<div class="referer">{if $visitor.refererType != 'directEntry'}from <a href="{$visitor.refererUrl}">{$visitor.refererName}</a> {if !empty($visitor.keywords)}"{$visitor.keywords}"{/if}{/if}</div>
		<div class="settings">
			<img src="{$visitor.browserIcon}" title="{$visitor.browser} with plugins {$visitor.plugins} enabled">
			<img src="{$visitor.operatingSystemIcon}" title="{$visitor.operatingSystem}, {$visitor.resolution}">
		</div>
		<div class="returning">{if $visitor.isVisitorReturning}<img src="plugins/Live/templates/images/returningVisitor.gif" title="Returning Visitor">{/if}</div>
	</div>
{/foreach}
</div>
