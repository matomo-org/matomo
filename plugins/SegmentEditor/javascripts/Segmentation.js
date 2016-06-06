/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

Segmentation = (function($) {

    function preselectFirstMetricMatch(rowNode)
    {
        var matchValue = $(rowNode).find('.metricMatchBlock option:first').attr('value');
        $(rowNode).find('.metricMatchBlock select').val(matchValue);
    }

    var segmentation = function segmentation(config) {
        if (!config.target) {
            throw new Error("target property must be set in config to segment editor control element");
        }

        var self = this;

        self.currentSegmentStr = "";
        self.segmentAccess = "read";
        self.availableSegments = [];

        for (var item in config) {
            self[item] = config[item];
        }

        self.editorTemplate = self.editorTemplate.detach();

        self.timer = ""; // variable for further use in timing events
        self.searchAllowed = true;
        self.filterTimer = "";
        self.filterAllowed = true;

        self.availableMatches = [];
        self.availableMatches["metric"] = [];
        self.availableMatches["metric"]["=="] = self.translations['General_OperationEquals'];
        self.availableMatches["metric"]["!="] = self.translations['General_OperationNotEquals'];
        self.availableMatches["metric"]["<="] = self.translations['General_OperationAtMost'];
        self.availableMatches["metric"][">="] = self.translations['General_OperationAtLeast'];
        self.availableMatches["metric"]["<"] = self.translations['General_OperationLessThan'];
        self.availableMatches["metric"][">"] = self.translations['General_OperationGreaterThan'];

        self.availableMatches["dimension"] = [];
        self.availableMatches["dimension"]["=="] = self.translations['General_OperationIs'];
        self.availableMatches["dimension"]["!="] = self.translations['General_OperationIsNot'];
        self.availableMatches["dimension"]["=@"] = self.translations['General_OperationContains'];
        self.availableMatches["dimension"]["!@"] = self.translations['General_OperationDoesNotContain'];
        self.availableMatches["dimension"]["=^"] = self.translations['General_OperationStartsWith'];
        self.availableMatches["dimension"]["=$"] = self.translations['General_OperationEndsWith'];

        segmentation.prototype.setAvailableSegments = function (segments) {
            this.availableSegments = segments;
        };

        segmentation.prototype.getSegment = function(){
            var self = this;
            if($.browser.mozilla) {
                return self.currentSegmentStr;
            }
            return decodeURIComponent(self.currentSegmentStr);
        };

        segmentation.prototype.setSegment = function(segmentStr){
            if(!$.browser.mozilla) {
                segmentStr = encodeURIComponent(segmentStr);
            }
            this.currentSegmentStr = segmentStr;
        };

        segmentation.prototype.markCurrentSegment = function(){
            var current = this.getSegment();

            var segmentationTitle = $(this.content).find(".segmentationTitle");
            if( current != "")
            {
                var currentDecoded = piwikHelper.htmlDecode(current);
                var selector = 'div.segmentList ul li[data-definition="'+currentDecoded+'"]';
                var foundItems = $(selector, this.target);

                if( foundItems.length > 0) {
                    var idSegment = $(foundItems).first().attr('data-idsegment');
                    var title = getSegmentName( getSegmentFromId(idSegment));
                } else {
                    title = _pk_translate('SegmentEditor_CustomSegment');
                }
                segmentationTitle.addClass('segment-clicked').html( title );
            }
            else {
                $(this.content).find(".segmentationTitle").text(this.translations['SegmentEditor_DefaultAllVisits']);
            }
        };

        var getAndDiv = function(){
            if(typeof andDiv === "undefined"){
                var andDiv = self.editorTemplate.find("> div.segment-and").clone();
            }
            return andDiv.clone();
        };

        var getOrDiv = function(){
            if(typeof orDiv === "undefined"){
                var orDiv = self.editorTemplate.find("> div.segment-or").clone();
            }
            return orDiv.clone();
        };

        var getMockedInputSet = function(){
            var mockedInputSet = self.editorTemplate.find("div.segment-row-inputs").clone();
            var clonedInput    = mockedInputSet.clone();
            preselectFirstMetricMatch(clonedInput);

            return clonedInput;
        };

        var getMockedInputRowHtml = function(){
            var mockedInputRow = '<div class="segment-row"><a class="segment-close" href="#"></a><div class="segment-row-inputs">'+getMockedInputSet().html()+'</div></div>';
            return mockedInputRow;
        };

        var getMockedFormRow = function(){
            var mockedFormRow = self.editorTemplate.find("div.segment-rows").clone();
            $(mockedFormRow).find(".segment-row").append(getMockedInputSet()).after(getAddOrBlockButtonHtml).after(getOrDiv());
            var clonedRow = mockedFormRow.clone();
            preselectFirstMetricMatch(clonedRow);

            return clonedRow;
        };

        var getInitialStateRowsHtml = function(){
            if(typeof initialStateRows === "undefined"){
                var content = self.editorTemplate.find("div.initial-state-rows").html();
                var initialStateRows = $(content).clone();
            }
            return initialStateRows;
        };

        var revokeInitialStateRows = function(){
            $(self.form).find(".segment-add-row").remove();
            $(self.form).find(".segment-and").remove();
        };

        var appendSpecifiedRowHtml= function(metric) {
            var mockedRow = getMockedFormRow();
            $(self.form).find(".segment-content > h3").after(mockedRow);
            $(self.form).find(".segment-content").append(getAndDiv());
            $(self.form).find(".segment-content").append(getAddNewBlockButtonHtml());
            doDragDropBindings();
            $(self.form).find(".metricList").val(metric).trigger("change");
            preselectFirstMetricMatch(mockedRow);
        };

        var appendComplexRowHtml = function(block){
            var key;
            var newRow = getMockedFormRow();

            var x = $(newRow).find(".metricMatchBlock select");
            $(newRow).find(".metricListBlock select").val(block[0].metric);
            $(newRow).find(".metricMatchBlock select").val(block[0].match);
            $(newRow).find(".metricValueBlock input").val(block[0].value);

            if(block.length > 1) {
                $(newRow).find(".segment-add-or").remove();
                for(key = 1; key < block.length;key++) {
                    var newSubRow = $(getMockedInputRowHtml()).clone();
                    $(newSubRow).find(".metricListBlock select").val(block[key].metric);
                    $(newSubRow).find(".metricMatchBlock select").val(block[key].match);
                    $(newSubRow).find(".metricValueBlock input").val(block[key].value);
                    $(newRow).append(newSubRow).append(getOrDiv());
                }
                $(newRow).append(getAddOrBlockButtonHtml());
            }
            $(self.form).find(".segment-content").append(newRow).append(getAndDiv());
        };

        var applyInitialStateModification = function(){
            $(self.form).find(".segment-add-row").remove();
            $(self.form).find(".segment-content").append(getInitialStateRowsHtml());
            doDragDropBindings();
        };

        var getSegmentFromId = function (id) {
            if(self.availableSegments.length > 0) {
                for(var i = 0; i < self.availableSegments.length; i++)
                {
                    segment = self.availableSegments[i];
                    if(segment.idsegment == id) {
                        return segment;
                    }
                }
            }
            return false;
        };

        var getListHtml = function() {
            var html = self.editorTemplate.find("> .listHtml").clone();
            var segment, injClass;
            var listHtml = '<li data-idsegment="" ' +
                (self.currentSegmentStr == "" ? " class='segmentSelected' " : "")
                + ' data-definition=""><span class="segname">' + self.translations['SegmentEditor_DefaultAllVisits']
                + ' ' + self.translations['General_DefaultAppended']
                + '</span></li> ';

            var isVisibleToSuperUserNoticeAlreadyDisplayedOnce = false;
            var isVisibleToSuperUserNoticeShouldBeClosed = false;

            var isSharedWithMeBySuperUserNoticeAlreadyDisplayedOnce = false;
            var isSharedWithMeBySuperUserNoticeShouldBeClosed = false;

            if(self.availableSegments.length > 0) {

                for(var i = 0; i < self.availableSegments.length; i++)
                {
                    segment = self.availableSegments[i];

                    if(isSegmentSharedWithMeBySuperUser(segment) && !isSharedWithMeBySuperUserNoticeAlreadyDisplayedOnce) {
                        isSharedWithMeBySuperUserNoticeAlreadyDisplayedOnce = true;
                        isSharedWithMeBySuperUserNoticeShouldBeClosed = true;
                        listHtml += '<span class="segmentsSharedWithMeBySuperUser"><hr> ' + _pk_translate('SegmentEditor_SharedWithYou') + ':<br/><br/>';
                    }

                    if(isSegmentVisibleToSuperUserOnly(segment) && !isVisibleToSuperUserNoticeAlreadyDisplayedOnce) {
                        // close <span class="segmentsSharedWithMeBySuperUser">
                        if(isSharedWithMeBySuperUserNoticeShouldBeClosed) {
                            isSharedWithMeBySuperUserNoticeShouldBeClosed = false;
                            listHtml += '</span>';
                        }

                        isVisibleToSuperUserNoticeAlreadyDisplayedOnce = true;
                        isVisibleToSuperUserNoticeShouldBeClosed = true;
                        listHtml += '<span class="segmentsVisibleToSuperUser"><hr> ' + _pk_translate('SegmentEditor_VisibleToSuperUser') + ':<br/><br/>';
                    }


                    injClass = "";
                    var checkSelected = segment.definition;
                    if(!$.browser.mozilla) {
                        checkSelected = encodeURIComponent(checkSelected);
                    }

                    if( checkSelected == self.currentSegmentStr){
                        injClass = 'class="segmentSelected"';
                    }
                    listHtml += '<li data-idsegment="'+segment.idsegment+'" data-definition="'+ (segment.definition).replace(/"/g, '&quot;') +'" '
                        +injClass+' title="'+ getSegmentTooltipEnrichedWithUsername(segment) +'"><span class="segname">'+getSegmentName(segment)+'</span>';
                    if(self.segmentAccess == "write") {
                        listHtml += '<span class="editSegment" title="'+ self.translations['General_Edit'].toLocaleLowerCase() +'"></span>';
                    }
                    listHtml += '</li>';
                }

                if(isVisibleToSuperUserNoticeShouldBeClosed) {
                    listHtml += '</span>';
                }

                if(isSharedWithMeBySuperUserNoticeShouldBeClosed) {
                    listHtml += '</span>';
                }

                $(html).find(".segmentList > ul").append(listHtml);
                if(self.segmentAccess === "write"){
                    $(html).find(".add_new_segment").html(self.translations['SegmentEditor_AddNewSegment']);
                }
                else {
                    $(html).find(".add_new_segment").hide();
                }
            }
            else
            {
                $(html).find(".segmentList > ul").append(listHtml);
            }
            return html;
        };

        var isSegmentVisibleToSuperUserOnly = function(segment) {
            return hasSuperUserAccessAndSegmentCreatedByAnotherUser(segment)
                && segment.enable_all_users == 0;
        };

        var isSegmentSharedWithMeBySuperUser = function(segment) {
            return segment.login != piwik.userLogin
                && segment.enable_all_users == 1;
        };

        var hasSuperUserAccessAndSegmentCreatedByAnotherUser = function(segment) {
            return piwik.hasSuperUserAccess && segment.login != piwik.userLogin;
        };

        var getSegmentTooltipEnrichedWithUsername = function(segment) {
            var segmentName = segment.name;
            if(hasSuperUserAccessAndSegmentCreatedByAnotherUser(segment)) {
                segmentName += ' (';
                segmentName += _pk_translate('General_CreatedByUser', [segment.login]);

                if(segment.enable_all_users == 0) {
                    segmentName += ', ' + _pk_translate('SegmentEditor_VisibleToSuperUser');
                }

                segmentName += ')';
            }
            return sanitiseSegmentName(segmentName);
        };

        var getSegmentName = function(segment) {
            return sanitiseSegmentName(segment.name);
        };

        var sanitiseSegmentName = function(segment) {
            segment = piwikHelper.escape(segment);
            return segment;
        }

        var getFormHtml = function() {
            var html = self.editorTemplate.find("> .segment-element").clone();
            // set left margin to center form
            var segmentsDropdown = $(html).find(".available_segments_select");
            var segment, newOption;
            newOption = '<option data-idsegment="" data-definition="" title="'
                + self.translations['SegmentEditor_AddNewSegment']
                + '">' + self.translations['SegmentEditor_AddNewSegment']
                + '</option>';
            segmentsDropdown.append(newOption);
            for(var i = 0; i < self.availableSegments.length; i++)
            {
                segment = self.availableSegments[i];
                newOption = '<option data-idsegment="'+segment.idsegment+'" data-definition="'+(segment.definition).replace(/"/g, '&quot;')+'" title="'+getSegmentTooltipEnrichedWithUsername(segment)+'">'+getSegmentName(segment)+'</option>';
                segmentsDropdown.append(newOption);
            }
            $(html).find(".segment-content > h3").after(getInitialStateRowsHtml()).show();
            return html;
        };

        var closeAllOpenLists = function() {
            $(".segmentationContainer", self.target).each(function() {
                if($(this).closest('.segmentEditorPanel').hasClass("expanded"))
                    $(this).trigger("click");
            });
        };

        var findAndExplodeByMatch = function(metric){
            var matches = ["==" , "!=" , "<=", ">=", "=@" , "!@","<",">", "=^", "=$"];
            var newMetric = {};
            var minPos = metric.length;
            var match, index;
            var singleChar = false;

            for(var key=0; key < matches.length; key++)
            {
                match = matches[key];
                index = metric.indexOf(match);
                if( index != -1){
                    if(index < minPos){
                        minPos = index;
                        if(match.length == 1){
                            singleChar = true;
                        }
                    }
                }
            }

            if(minPos < metric.length){
                // sth found - explode
                if(singleChar == true){
                    newMetric.metric = metric.substr(0,minPos);
                    newMetric.match = metric.substr(minPos,1);
                    newMetric.value = metric.substr(minPos+1);
                } else {
                    newMetric.metric = metric.substr(0,minPos);
                    newMetric.match = metric.substr(minPos,2);
                    newMetric.value = metric.substr(minPos+2);
                }
                // if value is only "" -> change to empty string
                if(newMetric.value == '""')
                {
                    newMetric.value = "";
                }
            }

            newMetric.value = decodeURIComponent(newMetric.value);
            return newMetric;
        };

        var parseSegmentStr = function(segmentStr)
        {
            var blocks;
            blocks = segmentStr.split(";");
            for(var key in blocks){
                blocks[key] = blocks[key].split(",");
                for(var innerkey = 0; innerkey < blocks[key].length; innerkey++){
                    blocks[key][innerkey] = findAndExplodeByMatch(blocks[key][innerkey]);
                }
            }
            return blocks;
        };

        var openEditForm = function(segment){
            addForm("edit", segment);

            $(self.form).find(".segment-content > h3 > span")
                .html( getSegmentName(segment) )
                .prop('title', getSegmentTooltipEnrichedWithUsername(segment));

            $(self.form).find('.available_segments_select > option[data-idsegment="'+segment.idsegment+'"]').prop("selected",true);

            $(self.form).find('.available_segments a.dropList')
                .html( getSegmentName(segment) )
                .prop( 'title', getSegmentTooltipEnrichedWithUsername(segment));

            if(segment.definition != ""){
                revokeInitialStateRows();
                var blocks = parseSegmentStr(segment.definition);
                for(var key in blocks){
                    appendComplexRowHtml(blocks[key]);
                }
                $(self.form).find(".segment-content").append(getAddNewBlockButtonHtml());
            }
            $(self.form).find(".metricList").each( function(){
                $(this).trigger("change", true);
            });
            doDragDropBindings();
        };

        var displayFormAddNewSegment = function (e) {
            closeAllOpenLists();
            addForm("new");
            doDragDropBindings();
        };

        var filterSegmentList = function (keyword) {
            var curTitle;
            clearFilterSegmentList();
            $(self.target).find(" .filterNoResults").remove();

            $(self.target).find(".segmentList li").each(function () {
                curTitle = $(this).prop('title');
                $(this).hide();
                if (curTitle.toLowerCase().indexOf(keyword.toLowerCase()) !== -1) {
                    $(this).show();
                }
            });

            if ($(self.target).find(".segmentList li:visible").length == 0) {
                $(self.target).find(".segmentList li:first")
                    .before("<li class=\"filterNoResults grayed\">" + self.translations['General_SearchNoResults'] + "</li>");
            }

            if ($(self.target).find(".segmentList .segmentsVisibleToSuperUser li:visible").length == 0) {
                $(self.target).find(".segmentList .segmentsVisibleToSuperUser").hide();
            }
            if ($(self.target).find(".segmentList .segmentsSharedWithMeBySuperUser li:visible").length == 0) {
                $(self.target).find(".segmentList .segmentsSharedWithMeBySuperUser").hide();
            }
        }

        var clearFilterSegmentList = function () {
            $(self.target).find(" .filterNoResults").remove();
            $(self.target).find(".segmentList li").each(function () {
                $(this).show();
            });
            $(self.target).find(".segmentList .segmentsVisibleToSuperUser").show();
            $(self.target).find(".segmentList .segmentsSharedWithMeBySuperUser").show();
        }

        var bindEvents = function () {
            self.target.on('click', '.segmentationContainer', function (e) {
                // hide all other modals connected with this widget
                if (self.content.closest('.segmentEditorPanel').hasClass("expanded")) {
                    if ($(e.target).hasClass("jspDrag") === true
                        || $(e.target).hasClass("segmentFilterContainer") === true
                        || $(e.target).parents().hasClass("segmentFilterContainer") === true
                        || $(e.target).hasClass("filterNoResults")) {
                        e.stopPropagation();
                    } else {
                        if (self.jscroll) {
                            self.jscroll.destroy();
                        }
                        self.target.closest('.segmentEditorPanel').removeClass('expanded');
                    }
                } else {
                    // for each visible segmentationContainer -> trigger click event to close and kill scrollpane - very important !
                    closeAllOpenLists();
                    self.target.closest('.segmentEditorPanel').addClass('expanded');
                    self.target.find('.segmentFilter').val(self.translations['General_Search']).trigger('keyup');
                    self.jscroll = self.target.find(".segmentList").jScrollPane({
                        autoReinitialise: true,
                        showArrows:true
                    }).data().jsp;
                }
            });

            self.target.on('click', '.editSegment', function(e) {
                $(this).closest(".segmentationContainer").trigger("click");
                var target = $(this).parent("li");

                openEditFormGivenSegment(target);
                e.stopPropagation();
                e.preventDefault();
            });

            self.target.on("click", ".segmentList li", function (e) {
                if ($(e.currentTarget).hasClass("grayed") !== true) {
                    var idsegment = $(this).attr("data-idsegment");
                    segmentDefinition = $(this).data("definition");

                    self.setSegment(segmentDefinition);
                    self.markCurrentSegment();
                    self.segmentSelectMethod( segmentDefinition );
                    toggleLoadingMessage(segmentDefinition.length);
                }
            });

            self.target.on('click', '.add_new_segment', function (e) {
                e.stopPropagation();
                displayFormAddNewSegment(e);
            });

            self.target.on('change', "select.metricList", function (e, persist) {
                if (typeof persist === "undefined") {
                    persist = false;
                }
                alterMatchesList(this, true);

                doDragDropBindings();

                autoSuggestValues(this, persist);
            });

            // attach event that will clear segment list filtering input after clicking x
            self.target.on('click', ".segmentFilterContainer span", function (e) {
                $(e.target).parent().find(".segmentFilter").val(self.translations['General_Search']).trigger('keyup');
            });

            self.target.on('blur', ".segmentFilter", function (e) {
                if ($(e.target).parent().find(".segmentFilter").val() == "") {
                    $(e.target).parent().find(".segmentFilter").val(self.translations['General_Search'])
                }
            });

            self.target.on('click', ".segmentFilter", function (e) {
                if ($(e.target).val() == self.translations['General_Search']) {
                    $(e.target).val("");
                }
            });

            self.target.on('keyup', ".segmentFilter", function (e) {
                var search = $(e.currentTarget).val();
                if (search == self.translations['General_Search']) {
                    search = "";
                }

                if (search.length >= 2) {
                    clearTimeout(self.filterTimer);
                    self.filterAllowed = true;
                    self.filterTimer = setTimeout(function () {
                        filterSegmentList(search);
                    }, 500);
                }
                else {
                    self.filterTimer = false;
                    clearFilterSegmentList();
                }
            });

            //
            // segment editor form events
            //

            self.target.on('click', ".segment-element a:not(.crowdfundingLink)", function (e) {
                e.preventDefault();
            });

            self.target.on('click',  "a.editSegmentName", function (e) {
                var oldName = $(e.currentTarget).parents("h3").find("span").text();
                $(e.currentTarget).parents("h3").find("span").hide();
                $(e.currentTarget).hide();
                $(e.currentTarget).before('<input class="edit_segment_name" type="text"/>');
                $(e.currentTarget).siblings(".edit_segment_name").focus().val(oldName);
            });

            self.target.on("click", ".segmentName", function(e) {
                $(self.form).find("a.editSegmentName").trigger('click');
            });

            self.target.on('blur', "input.edit_segment_name", function (e) {
                var newName = $(this).val();
                var segmentNameNode = $(e.currentTarget).parents("h3").find("span");

                if(newName.trim()) {
                    segmentNameNode.text(newName);
                } else {
                    $(this).val(segmentNameNode.text());
                }
            });

            self.target.on('click', '.segment-element', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            self.target.on('change', '.available_segments_select', function (e) {
                var option = $(e.currentTarget).find('option:selected');
                openEditFormGivenSegment(option);
            });

            // attach event that shows/hides child elements of each metric category
            self.target.on('click', '.segment-nav a.metric_category', function (e) {
                $(e.currentTarget).siblings("ul").toggle();
            });

            self.target.on('click', ".custom_select_search a", function (e) {
                $(self.form).find(".segmentSearch").val("").trigger("keyup").val(self.translations['General_Search']);
            });

            // attach event that will clear search input upon focus if its content is default
            self.target.on('focus', '.segmentSearch', function (e) {
                var search = $(e.currentTarget).val();
                if(search == self.translations['General_Search'])
                    $(e.currentTarget).val("");
            });

            // attach event that will set search input value upon blur if its content is not null
            self.target.on('blur', '.segmentSearch', function (e) {
                var search = $(e.currentTarget).val();
                if(search == ""){
                    clearSearchMetricHighlight();
                    $(e.currentTarget).val(self.translations['General_Search']);
                }
            });

            // bind search action triggering - only when input text is longer than 2 chars
            self.target.on('keyup', '.segmentSearch', function (e) {
                var search = $(e.currentTarget).val();
                if( search.length >= 2)
                {
                    clearTimeout(self.timer);
                    self.searchAllowed = true;
                    self.timer = setTimeout(function(){
                        searchSegments(search);
                    }, 500);
                }
                else{
                    self.searchAllowed = false;
                    clearSearchMetricHighlight();
                }
            });

            self.target.on('click', ".delete", function() {
                var segmentName = $(self.form).find(".segment-content > h3 > span").text();
                var segmentId = $(self.form).find(".available_segments_select option:selected").attr("data-idsegment");
                var params = {
                    "idsegment" : segmentId
                };
                $('.segment-delete-confirm', self.target).find('#name').text( segmentName );
                if(segmentId != ""){
                    piwikHelper.modalConfirm(self.target.find('.segment-delete-confirm'), {
                        yes: function(){
                            self.deleteMethod(params);
                        }
                    });
                }
            });

            self.target.on("click", "a.close", function (e) {
                $(".segmentListContainer", self.target).show();
                closeForm();
            });

            $("body").on("keyup", function (e) {
                if(e.keyCode == "27"){
                    $(".segmentListContainer", self.target).show();
                    closeForm();
                }
            });

            //
            // segment manipulation events
            //

            // upon clicking - add new segment block, then bind 'x' action to newly added row
            self.target.on('click', ".segment-add-row a", function(event, data){
                var mockedRow = getMockedFormRow();
                $(self.form).find(".segment-and:last").after(getAndDiv()).after(mockedRow);
                if(typeof data !== "undefined"){
                    $(self.form).find(".metricList:last").val(data);
                }
                $(self.form).find(".metricList:last").trigger('change');
                preselectFirstMetricMatch(mockedRow);
                doDragDropBindings();
            });

            self.target.on("click", ".segment-add-row span", function(event, data){
                if(typeof data !== "undefined") {
                    var mockedRow = getMockedFormRow();
                    $(self.form).find(".segment-and:last").after(getAndDiv()).after(mockedRow);
                    preselectFirstMetricMatch(mockedRow);
                    $(self.form).find(".metricList:last").val(data).trigger('change');
                    doDragDropBindings();
                }
            });

            // add new OR block
            self.target.on("click", ".segment-add-or a", function(event, data){
                var parentRows = $(event.currentTarget).parents(".segment-rows");

                parentRows.find(".segment-or:last").after(getOrDiv()).after(getMockedInputRowHtml());
                if(typeof data !== "undefined"){
                    parentRows.find(".metricList:last").val(data);
                }
                parentRows.find(".metricList:last").trigger('change');

                var addedRow = parentRows.find('.segment-row:last');
                preselectFirstMetricMatch(addedRow);
                doDragDropBindings();
            });

            self.target.on("click", ".segment-close",  function (e) {
                var target = e.currentTarget;
                var rowCnt = $(target).parents(".segment-rows").find(".segment-row").length;
                var globalRowCnt = $(self.form).find(".segment-close").length;
                if(rowCnt > 1){
                    $(target).parents(".segment-row").next().remove();
                    $(target).parents(".segment-row").remove();
                }
                else if(rowCnt == 1){
                    $(target).parents(".segment-rows").next().remove();
                    $(target).parents(".segment-rows").remove();
                    if(globalRowCnt == 1){
                        applyInitialStateModification();
                    }
                }
            });
        };

        // Request auto-suggest values
        var autoSuggestValues = function(select, persist) {
            var type = $(select).find("option:selected").attr("value");
            if(!persist) {
                var parents = $(select).parents('.segment-row');
                var loadingElement = parents.find(".segment-loading");
                loadingElement.show();
                var inputElement = parents.find(".metricValueBlock input");
                var segmentName = $('option:selected',select).attr('value');

                var ajaxHandler = new ajaxHelper();
                ajaxHandler.addParams({
                    module: 'API',
                    format: 'json',
                    method: 'API.getSuggestedValuesForSegment',
                    segmentName: segmentName
                }, 'GET');
                ajaxHandler.useRegularCallbackInCaseOfError = true;
                ajaxHandler.setTimeout(20000);
                ajaxHandler.setErrorCallback(function(response) {
                    loadingElement.hide();
                    inputElement.autocomplete({
                        source: [],
                        minLength: 0
                    });
                    $(inputElement).autocomplete('search', $(inputElement).val());
                });
                ajaxHandler.setCallback(function(response) {
                    loadingElement.hide();

                    if (response && response.result != 'error') {

                        inputElement.autocomplete({
                            source: response,
                            minLength: 0,
                            select: function(event, ui){
                                event.preventDefault();
                                $(inputElement).val(ui.item.value);
                            }
                        });
                    }

                    inputElement.click(function (e) {
                        $(inputElement).autocomplete('search', $(inputElement).val());
                    });
                });
                ajaxHandler.send();
            }
        };

        var alterMatchesList = function(select, persist){
            var oldMatch;
            var type = $(select).find("option:selected").attr("data-type");
            var matchSelector = $(select).parents(".segment-input").siblings(".metricMatchBlock").find("select");
            if(persist === true){
                oldMatch = matchSelector.find("option:selected").val();
            } else {
                oldMatch = "";
            }

            if(type === "dimension" || type === "metric"){
                matchSelector.empty();
                var optionsHtml = "";
                for(var key in self.availableMatches[type]){
                    optionsHtml += '<option value="'+key+'">'+self.availableMatches[type][key]+'</option>';
                }
            }

            matchSelector.append(optionsHtml);

            if (matchSelector.find('option[value="' + oldMatch + '"]').length) {
                matchSelector.val(oldMatch);
            } else {
                preselectFirstMetricMatch(matchSelector.parent());
            }
        };

        var getAddNewBlockButtonHtml = function()
        {
            if(typeof addNewBlockButton === "undefined")
            {
                var addNewBlockButton = self.editorTemplate.find("> div.segment-add-row").clone();
            }
            return addNewBlockButton.clone();

        };

        var getAddOrBlockButtonHtml = function(){
            if(typeof addOrBlockButton === "undefined") {
                var addOrBlockButton = self.editorTemplate.find("div.segment-add-or").clone();
            }
            return addOrBlockButton.clone();
        };

        var placeSegmentationFormControls = function(){
            doDragDropBindings();
            $(self.form).find(".scrollable").jScrollPane({
                showArrows: true,
                autoReinitialise: true,
                verticalArrowPositions: 'os',
                horizontalArrowPositions: 'os'
            });
        };

        function openEditFormGivenSegment(option) {
            var idsegment = option.attr("data-idsegment");

            if(idsegment.length == 0) {
                displayFormAddNewSegment();
            } else {
                var segment = getSegmentFromId(idsegment);
                segment.definition = option.data("definition");
                openEditForm(segment);
            }
        }

        var doDragDropBindings = function(){
            $(self.form).find(".segment-nav div > ul > li > ul > li").sortable({
                cursor: 'move',
                revert: 10,
                revertDuration: 0,
                snap: false,
                helper: 'clone',
                appendTo: 'body'
            });

            $(self.form).find(".metricListBlock").droppable({
                hoverClass: "hovered",
                drop: function( event, ui ) {
                    $(this).find("select").val(ui.draggable.parent().attr("data-metric")).trigger("change");
                }
            });

            $(self.form).find(".segment-add-row > div").droppable({
                hoverClass: "hovered",
                drop: function( event, ui ) {
                    $(this).find("a").trigger("click", [ui.draggable.parent().attr("data-metric")]);
                    if($(this).find("a > span").length == 0){
                        revokeInitialStateRows();
                        appendSpecifiedRowHtml([ui.draggable.parent().attr("data-metric")]);
                    }
                }
            });

            $(self.form).find(".segment-add-or > div").droppable({
                hoverClass: "hovered",
                drop: function( event, ui ) {
                    $(this).find("a").trigger("click", [ui.draggable.parent().attr("data-metric")]);
                }
            });
        };

        var searchSegments = function(search){
            // pre-process search string to normalized form
            search = normalizeSearchString(search);

            // ---
            // clear all previous search highlights and hide all categories
            // to allow further showing only matching ones, while others remain invisible
            clearSearchMetricHighlight();
            $(self.form).find('.segment-nav div > ul > li').hide();
            var curStr = "";
            var curMetric = "";

            // 1 - do most obvious selection -> mark whole categories matching search string
            // also expand whole category
            $(self.form).find('.segment-nav div > ul > li').each( function(){
                    curStr = normalizeSearchString($(this).find("a.metric_category").text());
                    if(curStr.indexOf(search) > -1) {
                        $(this).addClass("searchFound");
                        $(this).find("ul").show();
                        $(this).find("li").show();
                        $(this).show();
                    }
                }
            );

            // 2 - among all unselected categories find metrics which match and mark parent as search result
            $(self.form).find(".segment-nav div > ul > li:not(.searchFound)").each(function(){
                var parent = this;
                $(this).find("li").each( function(){
                    var curStr = normalizeSearchString($(this).text());
                    var curMetric = normalizeSearchString($(this).attr("data-metric"));
                    $(this).hide();
                    if(curStr.indexOf(search) > -1 || curMetric.indexOf(search) > -1){
                        $(this).show();
                        $(parent).find("ul").show();
                        $(parent).addClass("searchFound").show();
                    }
                });
            });

            if( $(self.form).find("li.searchFound").length == 0)
            {
                $(self.form).find("div > ul").prepend('<li class="no_results"><a>'+self.translations['General_SearchNoResults']+'</a></li>').show();
            }
            // check if search allow flag was revoked - then clear all search results
            if(self.searchAllowed == false)
            {
                clearSearchMetricHighlight();
                self.searchAllowed = true;
            }

        };

        var clearSearchMetricHighlight = function(){
            $(self.form).find('.no_results').remove();
            $(self.form).find('.segment-nav div > ul > li').removeClass("searchFound").show();
            $(self.form).find('.segment-nav div > ul > li').removeClass("others").show();
            $(self.form).find('.segment-nav div > ul > li > ul > li').show();
            $(self.form).find('.segment-nav div > ul > li > ul').hide();
        };

        var normalizeSearchString = function(search){
            search = search.replace(/^\s+|\s+$/g, ''); // trim
            search = search.toLowerCase();
            // remove accents, swap ñ for n, etc
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";
            for (var i=0, l=from.length ; i<l ; i++) {
                search = search.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            search = search.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '_') // collapse whitespace and replace by underscore
                .replace(/-+/g, '-'); // collapse dashes
            return search;
        };

        // Mode = 'new' or 'edit'
        var addForm = function(mode, segment){

            self.target.find(".segment-element:visible").unbind().remove();
            if (typeof self.form !== "undefined") {
                closeForm();
            }
            // remove any remaining forms

            self.form = getFormHtml();
            self.target.prepend(self.form);

            // if there's enough space to the left & not enough space to the right,
            // anchor the form to the right of the selector
            if (self.form.width() + self.target.offset().left > $(window).width()
                && self.form.width() < self.target.offset().left + self.target.width()
            ) {
                self.form.addClass('anchorRight');
            }

            placeSegmentationFormControls();

            if(mode == "edit") {
                var userSelector = $(self.form).find('.enable_all_users_select > option[value="' + segment.enable_all_users + '"]').prop("selected",true);

                // Replace "Visible to me" by "Visible to $login" when user is super user
                if(hasSuperUserAccessAndSegmentCreatedByAnotherUser(segment)) {
                    $(self.form).find('.enable_all_users_select > option[value="' + 0 + '"]').text(segment.login);
                }
                $(self.form).find('.visible_to_website_select > option[value="'+segment.enable_only_idsite+'"]').prop("selected",true);
                $(self.form).find('.auto_archive_select > option[value="'+segment.auto_archive+'"]').prop("selected",true);

            }

            makeDropList(".enable_all_users" , ".enable_all_users_select");
            makeDropList(".visible_to_website" , ".visible_to_website_select");
            makeDropList(".auto_archive" , ".auto_archive_select");
            makeDropList(".available_segments" , ".available_segments_select");
            $(self.form).find(".saveAndApply").bind("click", function (e) {
                e.preventDefault();
                parseFormAndSave();
            });

            $(self.form).find('.segment-footer').hover( function() {
                $('.segmentFooterNote', self.target).fadeIn();
            }, function() {
                $('.segmentFooterNote', self.target).fadeOut();
            });

            if(typeof mode !== "undefined" && mode == "new")
            {
                $(self.form).find(".editSegmentName").trigger('click');
            }
            $(".segmentListContainer", self.target).hide();

            self.target.closest('.segmentEditorPanel').addClass('editing');

            piwikHelper.compileAngularComponents(self.target);
        };

        var closeForm = function () {
            $(self.form).unbind().remove();
            self.target.closest('.segmentEditorPanel').removeClass('editing');
        };

        var parseForm = function(){
            var segmentStr = "";
            $(self.form).find(".segment-rows").each( function(){
                var subSegmentStr = "";

                $(this).find(".segment-row").each( function(){
                    if(subSegmentStr != ""){
                        subSegmentStr += ","; // OR operator
                    }
                    $(this).find(".segment-row-inputs").each( function(){
                        var metric = $(this).find(".metricList option:selected").val();
                        var match = $(this).find(".metricMatchBlock > select option:selected").val();
                        var value = $(this).find(".segment-input input").val();
                        subSegmentStr += metric + match + encodeURIComponent(value);
                    });
                });
                if(segmentStr != "")
                {
                    segmentStr += ";"; // add AND operator between segment blocks
                }
                segmentStr += subSegmentStr;
            });
            return segmentStr
        };

        var parseFormAndSave = function(){
            var segmentName = $(self.form).find(".segment-content > h3 >span").text();
            var segmentStr = parseForm();
            var segmentId = $(self.form).find('.available_segments_select > option:selected').attr("data-idsegment");
            var user = $(self.form).find(".enable_all_users_select option:selected").val();
            var autoArchive = $(self.form).find(".auto_archive_select option:selected").val() || 0;
            var params = {
                "name": segmentName,
                "definition": segmentStr,
                "enabledAllUsers": user,
                "autoArchive": autoArchive,
                "idSite":  $(self.form).find(".visible_to_website_select option:selected").val()
            };

            // determine if save or update should be performed
            if(segmentId === ""){
                self.addMethod(params);
            }
            else{
                jQuery.extend(params, {
                    "idSegment": segmentId
                });
                self.updateMethod(params);
            }
        };

        var makeDropList = function(spanId, selectId){
            var select = $(self.form).find(selectId).hide();
            var dropList = $( '<a class="dropList dropdown">' )
                .insertAfter( select )
                .text( select.children(':selected').text() )
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    appendTo: "body",
                    source: function( request, response ) {
                        response( select.children( "option" ).map(function() {
                            var text = $( this ).text();
                            return {
                                label: text,
                                value: this.value,
                                option: this
                            };
                        }) );
                    },
                    select: function( event, ui ) {
                        event.preventDefault();
                        ui.item.option.selected = true;
                        // Mark original select>option
                        $(spanId + ' option[value="' + ui.item.value + '"]', self.editorTemplate).prop('selected', true);
                        dropList.text(ui.item.label);
                        $(self.form).find(selectId).trigger("change");
                    }
                })
                .click(function() {
                    // close all other droplists made by this form
                    $("a.dropList").autocomplete("close");
                    //                 close if already visible
                    if ( $(this).autocomplete( "widget" ).is(":visible") ) {
                        $(this).autocomplete("close");
                        return;
                    }
                    // pass empty string as value to search for, displaying all results
                    $(this).autocomplete( "search", "" );

                });
            $('body').on('mouseup',function (e) {
                if (!$(e.target).parents(spanId).length
                    && !$(e.target).is(spanId)
                    && !$(e.target).parents(spanId).length
                    && !$(e.target).parents(".ui-autocomplete").length
                    && !$(e.target).is(".ui-autocomplete")
                    && !$(e.target).parents(".ui-autocomplete").length
                ) {
                    dropList.autocomplete().autocomplete("close");
                }
            });
        };

        function toggleLoadingMessage(segmentIsSet) {
            if (segmentIsSet) {
                $('#ajaxLoadingDiv').find('.loadingSegment').show();
            } else {
                $('#ajaxLoadingDiv').find('.loadingSegment').hide();
            }
        }

        this.initHtml = function() {
            var html = getListHtml();

            if(typeof self.content !== "undefined"){
                this.content.html($(html).html());
            } else {
                this.target.append(html);
                this.content = this.target.find(".segmentationContainer");
            }

            // assign content to object attribute to make it easil accesible through all widget methods
            this.markCurrentSegment();

            // Loading message
            var segmentIsSet = this.getSegment().length;
            toggleLoadingMessage(segmentIsSet);
        };

        this.initHtml();
        bindEvents();
    };

    return segmentation;
})(jQuery);

$(document).ready(function() {
    var exports = require('piwik/UI');
    var UIControl = exports.UIControl;

    /**
     * Sets up and handles events for the segment selector & editor control.
     *
     * @param {Element} element The HTML element generated by the SegmentSelectorControl PHP class. Should
     *                          have the CSS class 'segmentEditorPanel'.
     * @constructor
     */
    var SegmentSelectorControl = function (element) {
        UIControl.call(this, element);

        if ((typeof this.props.isSegmentNotAppliedBecauseBrowserArchivingIsDisabled != "undefined")
            && this.props.isSegmentNotAppliedBecauseBrowserArchivingIsDisabled
        ) {
            piwikHelper.modalConfirm($('.pleaseChangeBrowserAchivingDisabledSetting', this.$element), {
                yes: function () {}
            });
        }

        var self = this;
        this.changeSegment = function(segmentDefinition) {
            segmentDefinition = cleanupSegmentDefinition(segmentDefinition);
            segmentDefinition = encodeURIComponent(segmentDefinition);
            return broadcast.propagateNewPage('segment=' + segmentDefinition, true);
        };

        this.changeSegmentList = function () {};

        var cleanupSegmentDefinition = function(definition) {
            definition = definition.replace("'", "%27");
            definition = definition.replace("&", "%26");
            return definition;
        };

        var addSegment = function(params){
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.setLoadingElement();
            params.definition = cleanupSegmentDefinition(params.definition);

            ajaxHandler.addParams($.extend({}, params, {
                "module": 'API',
                "format": 'json',
                "method": 'SegmentEditor.add'
            }), 'GET');
            ajaxHandler.useCallbackInCaseOfError();
            ajaxHandler.setCallback(function (response) {
                if (response && response.result == 'error') {
                    alert(response.message);
                } else {
                    params.idsegment = response.value;
                    self.props.availableSegments.push(params);
                    self.rebuild();

                    self.impl.setSegment(params.definition);
                    self.impl.markCurrentSegment();

                    self.$element.find('a.close').click();
                    self.changeSegment(params.definition);

                    self.changeSegmentList(self.props.availableSegments);
                }
            });
            ajaxHandler.send(true);
        };

        var updateSegment = function(params){
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.setLoadingElement();
            params.definition = cleanupSegmentDefinition(params.definition);

            ajaxHandler.addParams($.extend({}, params, {
                "module": 'API',
                "format": 'json',
                "method": 'SegmentEditor.update'
            }), 'GET');
            ajaxHandler.useCallbackInCaseOfError();
            ajaxHandler.setCallback(function (response) {
                if (response && response.result == 'error') {
                    alert(response.message);
                } else {
                    params.idsegment = params.idSegment;

                    var idx = null;
                    for (idx in self.props.availableSegments) {
                        if (self.props.availableSegments[idx].idsegment == params.idSegment) {
                            break;
                        }
                    }

                    $.extend( self.props.availableSegments[idx], params);
                    self.rebuild();

                    self.impl.setSegment(params.definition);
                    self.impl.markCurrentSegment();

                    self.$element.find('a.close').click();
                    self.changeSegment(params.definition);

                    self.changeSegmentList(self.props.availableSegments);
                }
            });
            ajaxHandler.send(true);
        };

        var deleteSegment = function(params){
            var ajaxHandler = new ajaxHelper();
            ajaxHandler.addParams({
                module: 'API',
                format: 'json',
                method: 'SegmentEditor.delete'
            }, 'GET');
            ajaxHandler.addParams({
                idSegment: params.idsegment
            }, 'POST');
            ajaxHandler.setLoadingElement();
            ajaxHandler.useCallbackInCaseOfError();
            ajaxHandler.setCallback(function (response) {
                if (response && response.result == 'error') {
                    alert(response.message);
                } else {
                    self.impl.setSegment('');
                    self.impl.markCurrentSegment();

                    var idx = null;
                    for (idx in self.props.availableSegments) {
                        if (self.props.availableSegments[idx].idsegment == params.idsegment) {
                            break;
                        }
                    }

                    self.props.availableSegments.splice(idx, 1);
                    self.rebuild();

                    self.$element.find('a.close').click();
                    self.changeSegment('');
                    $('.ui-dialog-content').dialog('close');

                    self.changeSegmentList(self.props.availableSegments);
                }
            });

            ajaxHandler.send(true);
        };

        var segmentFromRequest = encodeURIComponent(self.props.selectedSegment)
            || broadcast.getValueFromHash('segment')
            || broadcast.getValueFromUrl('segment');
        if($.browser.mozilla) {
            segmentFromRequest = decodeURIComponent(segmentFromRequest);
        }

        var userSegmentAccess = (this.props.authorizedToCreateSegments) ? "write" : "read";

        this.impl = new Segmentation({
            "target"   : this.$element.find(".segmentListContainer"),
            "editorTemplate": $('.SegmentEditor', self.$element),
            "segmentAccess" : userSegmentAccess,
            "availableSegments" : this.props.availableSegments,
            "addMethod": addSegment,
            "updateMethod": updateSegment,
            "deleteMethod": deleteSegment,
            "segmentSelectMethod": function () { self.changeSegment.apply(this, arguments); },
            "currentSegmentStr": segmentFromRequest,
            "translations": this.props.segmentTranslations
        });

        this.onMouseUp = function(e) {
            if ($(e.target).closest('.segment-element').length === 0
                && !$(e.target).is('.segment-element')
                && $(e.target).hasClass("ui-corner-all") == false
                && $(e.target).hasClass("ddmetric") == false
                && $(".segment-element:visible", self.$element).length == 1
            ) {
                $(".segment-element:visible a.close", self.$element).click();
            }

            if ($(e.target).closest('.segmentListContainer').length === 0
                && self.$element.hasClass("expanded")
            ) {
                $(".segmentationContainer", self.$element).trigger("click");
            }
        };

        $('body').on('mouseup', this.onMouseUp);

        initTopControls();
    };

    /**
     * Initializes all elements w/ the .segmentEditorPanel CSS class as SegmentSelectorControls,
     * if the element has not already been initialized.
     */
    SegmentSelectorControl.initElements = function () {
        UIControl.initElements(this, '.segmentEditorPanel');
    };

    $.extend(SegmentSelectorControl.prototype, UIControl.prototype, {
        getSegment: function () {
            return this.impl.getSegment();
        },

        setSegment: function (segment) {
            return this.impl.setSegment(segment);
        },

        rebuild: function () {
            this.impl.setAvailableSegments(this.props.availableSegments);
            this.impl.initHtml();
        },

        _destroy: function () {
            UIControl.prototype._destroy.call(this);

            $('body').off('mouseup', null, this.onMouseUp);
        }
    });

    exports.SegmentSelectorControl = SegmentSelectorControl;
});
