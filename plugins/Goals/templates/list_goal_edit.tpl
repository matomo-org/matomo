<span id='EditGoals' style="display:none;">
	<table class="tableForm">
	<thead style="font-weight:bold">
		<td>Id</td>
		<td>Goal Name</td>
		<td>Goal is Triggered when</td>
		<td>Revenue</td>
		<td>Edit</td>
		<td>Delete</td>
	</thead>
	{foreach from=$goals item=goal}
	<tr>
		<td>{$goal.idgoal}</td>
		<td>{$goal.name}</td>
		<td>{$goal.match_attribute}  <br>Pattern {$goal.pattern_type}: {$goal.pattern}</b></td>
		<td>{if $goal.revenue==0}-{else}{$currency}{$goal.revenue}{/if}</td>
		<td><a href='#' name="linkEditGoal" id="{$goal.idgoal}"><img src='plugins/UsersManager/images/edit.png' border=0> Edit</a></td>
		<td><a href='#' name="linkDeleteGoal" id="{$goal.idgoal}"><img src='plugins/UsersManager/images/remove.png' border=0> Delete</a></td>
	</tr>
	{/foreach}
	</table>
</span>
