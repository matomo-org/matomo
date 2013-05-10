<div id="SegmentEditor" style="display:none;">
    <div class="segmentationContainer listHtml">
        {if $authorizedToCreateSegments}
        <span class="segmentationTitle"><b>Add segment</b></span>
        <ul class="submenu">
            <li> Select a segment of visitors:
                <div class="segmentList">
                    <ul>
                    </ul>
                </div>
            </li>
        </ul>
        <a class="add_new_segment">Add new segment</a>
        {else}
            <span class="segmentationTitle"><b>Add segment</b></span>
            <ul class="submenu">
            <li> You must be logged in to create and apply custom visitor segments.
                <br/>&rsaquo; <a href='index.php?module={$loginModule}'>{'Login_LogIn'|translate}</a></strong>
            </li>
            </ul>
        {/if}
    </div>

    <div class="initial-state-rows">{* no space here important for jquery *}<div class="segment-add-row initial"><div>
        <span>+ Drag &amp; Drop condition</span>
    </div></div>
    <div class="segment-and">AND</div>
    <div class="segment-add-row initial"><div>
        <span>+ Drag &amp; Drop condition</span>
    </div></div>
    </div>

    <div class="segment-row-inputs">
        <div class="segment-input metricListBlock">
            <select title="Choose a segment" class="metricList">
                {foreach from=$segmentsByCategory key=category item=segmentsInCategory}
                <optgroup label="{$category}">
                    {foreach from=$segmentsInCategory item=segmentInCategory}
                        <option data-type="{$segmentInCategory.type}" value="{$segmentInCategory.segment}">{$segmentInCategory.name}</option>
                    {/foreach}
                </optgroup>
                {/foreach}
            </select>
        </div>
        <div class="segment-input metricMatchBlock">
            <select title="Matches">
                <option value="==">Equals</option>
                <option value="!=">Not Equals</option>
                <option value="&lt;=">At most</option>
                <option value="&gt;=">At least</option>
                <option value="&lt;">Less</option>
                <option value="&gt;">Greater</option>
                <option value="=@">Contains</option>
                <option value="!@">Does not contain</option>
            </select>
        </div>
        <div class="segment-input metricValueBlock">
            <input type="text" title="Value">
        </div>
        <div class="clear"></div>
    </div>
    <div class="segment-rows">
        <div class="segment-row">
            <a href="#" class="segment-close"></a>
            <a href="#" class="segment-loading"></a>
        </div>
    </div>
    <div class="segment-or">OR</div>
    <div class="segment-add-or"><div>
            <a href="#"> + Add <span>OR</span> condition </a>
        </div>
    </div>
    <div class="segment-and">AND</div>
    <div class="segment-add-row"><div>
            <a href="#">+ Add <span>AND</span> condition </a>
        </div>
    </div>
    <div style="position: absolute; z-index:999; width:1040px;" class="segment-element">
        <div class="segment-nav">
            <h4 class="visits"><span id="available_segments"><strong>
                <select id="available_segments_select"></select>
            </strong></span></h4>
            <div class="scrollable">
            <ul>
            {foreach from=$segmentsByCategory key=category item=segmentsInCategory}
                <li data="visit"><a class="metric_category" href="#">{$category}</a>
                    <ul style="display:none">
                        {foreach from=$segmentsInCategory item=segmentInCategory}
                        <li data-metric="{$segmentInCategory.segment}"><a class="ddmetric" href="#">{$segmentInCategory.name}</a></li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
            </ul>
            </div>
            <div class="custom_select_search">
                <a href="#"></a>
                <input type="text" aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" class="inp ui-autocomplete-input" id="segmentSearch" value="Search" length="15">
            </div>
        </div>
        <div class="segment-content">
            {if $isSuperUser}
            <div class="segment-top">
                This segment is visible to: <span id="enabledAllUsers"><strong>
                        <select id="enabledAllUsers_select">
                            <option selected="" value="0">me</option>
                            <option value="1">All users</option>
                        </select>
                    </strong></span>

                and displayed for <span id="visible_to_website"><strong>
                        <select id="visible_to_website_select">
                            <option selected="" value="{$idSite}">this website only</option>
                            <option value="0">all websites</option>
                        </select>
                    </strong></span>
            </div>
            {/if}
            <h3>Name: <span>New segment</span> <a class="editSegmentName" href="#">edit</a></h3>
        </div>
        <div class="segment-footer">
            <a class="delete" href="#">Delete</a>
            <a class="close" href="#">Close</a>
            <button class="saveAndApply">Save & Apply</button>
        </div>
    </div>
</div>

<span id="segmentEditorPanel">
    <div id="segmentList"></div>
</span>

<div class="ui-confirm" id="confirm">
    <h2>Are you sure you want to delete this segment?</h2>
    <input role="yes" type="button" value="{'General_Yes'|translate}"/>
    <input role="no" type="button" value="{'General_No'|translate}"/>
</div>

<script type="text/javascript">
var availableSegments = {$savedSegmentsJson};
</script>
