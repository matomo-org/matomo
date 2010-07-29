<div class='entityAddContainer' style="display:none;">
<form>
<table class="dataTable entityTable">
	<thead>
		<tr class="first">
			<th colspan="2">Create a Goal</th>
		<tr>
	</thead>
	<tbody>
		<tr>
            <td class="first">{'Goals_GoalName'|translate} </th>
			<td><input type="text" name="name" value="" size="28" id="goal_name" class="inp" /></td>
		</tr>
		<tr>
			<td style='width:240px;' class="first">{'Goals_GoalIsTriggered'|translate}
				<select name="trigger_type" class="inp">
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
			<td class="first">{'Goals_WhereThe'|translate} <span id="match_attribute_name"></span></td>
			<td>
				<select name="pattern_type" class="inp">
                    <option value="contains">{'Goals_Contains'|translate:""}</option>
                    <option value="exact">{'Goals_IsExactly'|translate:""}</option>
                    <option value="regex">{'Goals_MatchesExpression'|translate:""}</option>
				</select>
			
				<input type="text" name="pattern" value="" size="16" class="inp" />
				<br />
				<div id="examples_pattern" class="entityInlineHelp"></div>
				<br />
				<span style="float:right">
				{'Goals_Optional'|translate} <input type="checkbox" id="case_sensitive" />
                <label for="case_sensitive">{'Goals_CaseSensitive'|translate}</label>
				</span>
			</td>
		</tr>
	</tbody>
	<tbody id="manual_trigger_section" style="display:none">
		<tr><td colspan="2" class="first">
				{'Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore'|translate:"<a target='_blank' href='misc/redirectToUrl.php?url=http://piwik.org/docs/javascript-tracking/'>":"</a>"}
		</td></tr>
	</tbody>
	<tbody>
		<tr>
            <td class="first">(optional) {'Goals_DefaultRevenue'|translate}</td>
			<td>{' <input type="text" name="revenue" size="2" value="0" class="inp" /> '|money:$idSite}
            <div class="entityInlineHelp"> {'Goals_DefaultRevenueHelp'|translate} </div>
			</td>
		</tr>
		<tr>
	</tbody>
</table>
        <input type="hidden" name="methodGoalAPI" value="" />	
        <input type="hidden" name="goalIdUpdate" value="" />
        <input type="submit" value="" name="submit" id="goal_submit" class="submit" />
</form>
</div>
