<table class="tutorial-nav-box">
	<tr>
		<td style="width: 30%">
			{if $prev}
				<a href="{$prev}" class="nav-button">Previous</a>
			{else}
				<span class="nav-button-disabled">Previous</span>
			{/if}
		</td>
		<td style="text-align: center">
			{if $up}
				<a href="{$up}" class="nav-button">Up</a>
			{/if}
		</td>
		<td style="text-align: right; width: 30%">
			{if $next}
				<a href="{$next}" class="nav-button">Next</a>
			{else}
				<span class="nav-button-disabled">Next</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td style="width: 30%">
			{if $prevtitle}
				<span class="detail">{$prevtitle}</span>
			{/if}
		</td>
		<td style="text-align: center">
			{if $uptitle}
				<span class="detail">{$uptitle}</span>
			{/if}
		</td>
		<td style="text-align: right; width: 30%">
			{if $nexttitle}
				<span class="detail">{$nexttitle}</span>
			{/if}
		</td>
	</tr>
</table>
	