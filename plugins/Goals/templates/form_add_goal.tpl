{literal}
<style>
.goalInlineHelp{
color:#9B9B9B;
}
</style>
{/literal}
<span id='GoalForm' style="display:none;">
<form>
	<table class="tableForm">	
		<tr>
			<td>Goal Name </td>
			<td><input type="text" name="name" value="" id="goal_name" /></td>
		</tr>
		<tr>
			<td>Goal is triggered when visitors:</td>
			<td>
				<input type="radio" id="match_attribute_url" value="url" name="match_attribute"/>
				<label for="match_attribute_url">Visit a given URL (page or group of pages)</label>
				<br>
				<input type="radio" id="match_attribute_file" value="file" name="match_attribute"/>
				<label for="match_attribute_file">Download a file</label>
				<br>
				<input type="radio" id="match_attribute_external_website" value="external_website" name="match_attribute"/>
				<label for="match_attribute_external_website">Click on a Link to an external website </label>
			</td>
		</tr>
		<tr>
			<td>where the <span id="match_attribute_name"></span></td>
			<td>
				<select name="pattern_type">
					<option value="contains">contains</option>
					<option value="exact">is exactly</option>
					<option value="regex">matches the expression</option>
				</select>
				
				<input type="text" name="pattern" value=""/>
				<br>
				<div id="examples_pattern" class="goalInlineHelp"></div>
				<br>
				<span style="float:right">
				(optional) <input type="checkbox" id="case_sensitive"/>
				<label for="case_sensitive">Case sensitive match</label>
				</span>
			</td>
		</tr>
		<tr>
			<td>(optional) Goal default value is </td>
			<td>{$currency} <input type="text" name="revenue" size="1" value="0"/>
			<div class="goalInlineHelp"> 
			For example, a Contact Form submitted by a visitor <br>
			may be worth $10 on average. Piwik will help you understand <br>
			how well your visitors segments are performing.</div>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="border:0">
			<div class="submit">	
				<input type="hidden" name="methodGoalAPI" value="">	
				<input type="hidden" name="goalIdUpdate" value="">
				<input type="submit" value="Add Goal" name="submit" id="goal_submit" class="submit" />
			</div>
			</td>
		</tr>
	</table>
</form>
</span>