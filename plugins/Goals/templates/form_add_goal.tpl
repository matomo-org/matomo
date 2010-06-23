<span id='GoalForm' style="display:none;">
<form>
<table class="tableForm tableFormGoals">
	<tbody>
		<tr>
            <td>{'Goals_GoalName'|translate} </td>
			<td><input type="text" name="name" value="" id="goal_name" /></td>
		</tr>
		<tr>
			<td style='width:240px;'>{'Goals_GoalIsTriggered'|translate}
				<select name="trigger_type">
					<option value="visitors">{'Goals_WhenVisitors'|translate}</option>
					<option value="manually">{'Goals_Manually'|translate}</option>
				</select>
			</td>
			<td>
				<input type="radio" id="match_attribute_url" value="url" name="match_attribute" />
                <label for="match_attribute_url">{'Goals_VisitUrl'|translate}</label>
				<br />
				<input type="radio" id="match_attribute_file" value="file" name="match_attribute" />
				<label for="match_attribute_file">{'Goals_Download'|translate}</label>
				<br />
				<input type="radio" id="match_attribute_external_website" value="external_website" name="match_attribute" />
				<label for="match_attribute_external_website">{'Goals_ClickOutlink'|translate}</label>
			</td>
			</tr>
	</tbody>
	<tbody id="match_attribute_section">
		<tr>
			<td>{'Goals_WhereThe'|translate} <span id="match_attribute_name"></span></td>
			<td>
				<select name="pattern_type">
                    <option value="contains">{'Goals_Contains'|translate:""}</option>
                    <option value="exact">{'Goals_IsExactly'|translate:""}</option>
                    <option value="regex">{'Goals_MatchesExpression'|translate:""}</option>
				</select>
			
				<input type="text" name="pattern" value="" />
				<br />
				<div id="examples_pattern" class="goalInlineHelp"></div>
				<br />
				<span style="float:right">
				{'Goals_Optional'|translate} <input type="checkbox" id="case_sensitive" />
                <label for="case_sensitive">{'Goals_CaseSensitive'|translate}</label>
				</span>
			</td>
		</tr>
	</tbody>
	<tbody id="manual_trigger_section" style="display:none">
		<tr><td colspan="2">
				{'Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore'|translate:"<a target='_blank' href='misc/redirectToUrl.php?url=http://piwik.org/docs/javascript-tracking/'>":"</a>"}
		</td></tr>
	</tbody>
	<tbody>
		<tr>
            <td>(optional) {'Goals_DefaultRevenue'|translate}</td>
			<td>{'<input type="text" name="revenue" size="1" value="0" />'|money:$idSite}
            <div class="goalInlineHelp"> {'Goals_DefaultRevenueHelp'|translate} </div>
			</td>
		</tr>
		<tr>
		<tr>
			<td colspan="2" style="border:0">
				<input type="hidden" name="methodGoalAPI" value="" />	
				<input type="hidden" name="goalIdUpdate" value="" />
				<center>
	            <input type="submit" value="" name="submit" id="goal_submit" class="submit" />
	            </center>
			</td>
		</tr>
	</tbody>
</table>
</form>
</span>
