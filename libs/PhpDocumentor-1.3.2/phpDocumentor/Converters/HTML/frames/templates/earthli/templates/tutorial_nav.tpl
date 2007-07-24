<table class="tutorial-nav-box">
	<tr>
		<td style="width: 30%">
			{if $prev}
				<a href="{$prev}"><img src="{$subdir}media/images/previous_button.png" alt="Previous"></a>
			{else}
				<span class="disabled"><img src="{$subdir}media/images/previous_button_disabled.png" alt="Previous"></span>
			{/if}
		</td>
		<td style="text-align: center">
			{if $up}
				<a href="{$up}"><img src="{$subdir}media/images/up_button.png" alt="Up"></a>
			{/if}
		</td>
		<td style="text-align: right; width: 30%">
			{if $next}
				<a href="{$next}"><img src="{$subdir}media/images/next_button.png" alt="Next"></a>
			{else}
				<span class="disabled"><img src="{$subdir}media/images/next_button_disabled.png" alt="Next"></span>
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
	