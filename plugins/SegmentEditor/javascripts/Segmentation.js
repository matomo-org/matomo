/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

Segmentation = (function($) {

    var segmentation = function segmentation(config) {
        
        var self = this;
        // set defaults for widget
        self.currentSegmentStr = "";
        self.targetId = "segmentEditorPanel";
        self.segmentAccess = "read";
        self.segmentList = [];
        // -----------
        for(var item in config)
        {
            self[item] = config[item];
        }

        self.timer = ""; // variable for further use in timing events
        self.searchAllowed = true;
        //----------

        self.availableMatches = [];
        self.availableMatches["metric"] = [];
        self.availableMatches["metric"]["=="] = "Equals";
        self.availableMatches["metric"]["!="] = "Not Equals";
        self.availableMatches["metric"]["<="] = "At most";
        self.availableMatches["metric"][">="] = "At least";
        self.availableMatches["metric"]["<"] = "Less than";
        self.availableMatches["metric"][">"] = "Greater than";
        
        self.availableMatches["dimension"] = [];
        self.availableMatches["dimension"]["=="] = "Is";
        self.availableMatches["dimension"]["!="] = "Is not";
        self.availableMatches["dimension"]["=@"] = "Contains";
        self.availableMatches["dimension"]["!@"] = "Does not contain";

        segmentation.prototype.getSegment = function(){
            var self = this;
            return self.currentSegmentStr;
        }

        var setSegment = function(segmentStr){
            self.currentSegmentStr = segmentStr;
        }

        segmentation.prototype.shortenSegmentName = function(name, length){

            if(typeof length === "undefined") length = 30;
            if(typeof name === "undefined") name = "";
            var i;
            
            if(name.length > length)
            {
                for(i = length; i > 0; i--){
                    if(name[i] === " "){
                        break;
                    }
                }
                if(i == 0){ 
                    i = length-3;
                }
                
                return name.slice(0,i)+"...";
            }
            return name;
        }

        var markCurrentSegment = function(){
            var current = self.getSegment();
//            window.setTimeout(function(){
                var segmentationTitle = $(self.content).find(".segmentationTitle");
                if( current != "")
                {
                    var foundItems = $(self.content).find('div.segmentList > ul > li[data-definition="'+current+'"]');
                    if( foundItems.length > 0)
                    {
                        var name = $(foundItems).first().find("span.segname").text();
                        segmentationTitle.html("<b>"+name+"</b>");
                    }
                    else{
                        segmentationTitle.html("<b>Custom Segment</b>");
                    }
                }
                else {
                    $(self.content).find(".segmentationTitle").text("All visits");
                }
//            }, 20);
        }

        var getAndDiv = function(){
            if(typeof andDiv === "undefined"){
                var andDiv = $("#SegmentEditor > div.segment-and").clone();
            }
            return andDiv.clone();
        }

        var getOrDiv = function(){
            if(typeof orDiv === "undefined"){
                var orDiv = $("#SegmentEditor > div.segment-or").clone();
            }
            return orDiv.clone();
        }

        var getMockedInputSet = function(){
            if(typeof mockedInputSet === "undefined"){
                var mockedInputSet = $("#SegmentEditor div.segment-row-inputs").clone();
            }
            return mockedInputSet.clone();
        }

        var getMockedInputRowHtml = function(){
            if(typeof mockedInputRow === "undefined"){
                var mockedInputRow = '<div class="segment-row"><a class="segment-close" href="#"></a><div class="segment-row-inputs">'+getMockedInputSet().html()+'</div></div>';
            }
            return mockedInputRow;
        }

        var getMockedFormRow = function(){
            if(typeof mockedFormRow === "undefined")
            {
                var mockedFormRow = $("#SegmentEditor div.segment-rows").clone();
                $(mockedFormRow).find(".segment-row").append(getMockedInputSet()).after(getAddOrBlockButtonHtml).after(getOrDiv());
            }
            return mockedFormRow.clone();
        }

        var getInitialStateRowsHtml = function(){
            if(typeof initialStateRows === "undefined"){
                var content = $("#SegmentEditor div.initial-state-rows").html();
                var initialStateRows = $(content).clone();
            }
            return initialStateRows;
        }

        var revokeInitialStateRows = function(){
            $(self.form).find(".segment-add-row").remove();
            $(self.form).find(".segment-and").remove();
        }

        var appendSpecifiedRowHtml= function(metric) {
            $(self.form).find(".segment-content > h3").after(getMockedFormRow());
            $(self.form).find(".segment-content").append(getAndDiv());
            $(self.form).find(".segment-content").append(getAddNewBlockButtonHtml());
            doDragDropBindings();
            $(self.form).find(".metricList").val(metric).trigger("change");
        }

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
        }

        var applyInitialStateModification = function(){
            $(self.form).find(".segment-add-row").remove();
            $(self.form).find(".segment-content").append(getInitialStateRowsHtml());
            doDragDropBindings();
        }
        
        var getListHtml = function() {
            var html = $("#SegmentEditor > .listHtml").clone();
            var segment, injClass;

            var listHtml = '<li data-idsegment="" ' +
                            (self.currentSegmentsGlobal == "" ? " class='segmentSelected' " : "")
                            + ' data-definition=""><span class="segname">All Visits (default)</span></li> ';
            if(self.segmentList.length > 0) {
                for(var key in self.segmentList)
                {
                    segment = self.segmentList[key];
                    injClass = "";
                    if( segment.definition == self.currentSegmentsGlobal){
                        injClass = 'class="segmentSelected"';
                    }
                    listHtml += '<li data-idsegment="'+segment.idsegment+'" data-definition=\''+segment.definition+'\' '
                                + injClass +' title="'+segment.name+'"><span class="segname">'
                                + self.shortenSegmentName(segment.name)+'</span>';
                    if(self.segmentAccess == "write") {
                        listHtml += '<span class="editSegment">[edit]</span>';
                    }
                    listHtml += '</li>';
                }
                $(html).find(".segmentList > ul").append(listHtml);
                if(self.segmentAccess === "write"){
                    $(html).find(".add_new_segment").html(_pk_translate('General_AddNewSegment_js'));
                }
                else{
                    $(html).find(".add_new_segment").hide();;
                }
            }
            else
            {
                $(html).find(".segmentList > ul").append(listHtml);
            }
            return html;
        }

        var getFormHtml = function() {
            var html = $("#SegmentEditor > .segment-element").clone();
            // set left margin to center form
            //$("body").append(html);
            var segmentsDropdown = $(html).find("#available_segments_select");
            var segment, newOption;
            newOption = '<option data-idsegment="" data-definition="" >New segment</option>';
            segmentsDropdown.append(newOption);
            for(var key in self.segmentList)
            {
                segment = self.segmentList[key];
                newOption = '<option data-idsegment="'+segment.idsegment+'" data-definition=\''+segment.definition+'\' title="'+segment.name+'">'+self.shortenSegmentName(segment.name)+'</option>';
                segmentsDropdown.append(newOption);
            }
            $(html).find(".segment-content > h3").after(getInitialStateRowsHtml()).show();
            return html;
        }

        var doListBindings = function()
        {
            self.jscroll = self.content.find(".segmentList").jScrollPane({
                autoReinitialise: true,
                showArrows:true
            }).data().jsp;

            self.content.find(".add_new_segment").unbind().on("click", function(event){
                event.stopPropagation();
                closeAllOpenLists();
                addForm("new");
                doDragDropBindings();
            });

        }

        var closeAllOpenLists = function() {
            $(".segmentationContainer").each(function() {
                if($(this).hasClass("visible"))
                    $(this).trigger("click");
            });
        }


        var findAndExplodeByMatch = function(metric){
            var matches = ["==" , "!=" , "<=", ">=", "=@" , "!@","<",">"];
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
                        if(match == ">" || match == "<"){
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

                }
                else{
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
            return newMetric;
        }

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
        }

        var openEditForm = function(segment){
            addForm();
            $(self.form).find(".segment-content > h3 > span").text(segment.name);
            $(self.form).find('#available_segments_select > option[data-idsegment="'+segment.idsegment+'"]').prop("selected",true);
            $(self.form).find('#available_segments a.dropList').html(self.shortenSegmentName(segment.name, 16));
            
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
        }

        var bindListEvents = function(){
            $(self.content).off("click").on("click", function(event){
                // hide all other modals connected with this widget
                if(self.content.hasClass("visible")){
                    if($(event.target).hasClass("jspDrag") === true)
                    {
                        event.stopPropagation();
                    }
                    else{
                        self.jscroll.destroy();
                        self.content.removeClass("visible");
                    }
                }
                else{
                    // for each visible segmentationContainer -> trigger click event to close and kill scrollpane - very important !
                    closeAllOpenLists();
                    self.content.addClass("visible");
                    doListBindings();
                }
            });

            $(self.content).off("click",".editSegment").on("click", ".editSegment", function(e){
                $(this).parents(".segmentationContainer").trigger("click");
                var target = $(this).parent("li");
                var segment = {};
                segment.idsegment = target.attr("data-idsegment");
                segment.definition = target.attr("data-definition");
                segment.name = target.attr("title");
                openEditForm(segment);
                e.stopPropagation();
                e.preventDefault();
            });

            $(self.content).off("click", ".segmentList li").on("click", ".segmentList li", function(e){
                if($(e.currentTarget).hasClass("grayed") !== true){
                    var segment = {};
                    segment.idsegment = $(this).attr("data-idsegment");
                    segment.definition = $(this).attr("data-definition");
                    segment.name = $(this).attr("title");
                    self.segmentSelectMethod(segment);
                    setSegment(segment.definition);
                    markCurrentSegment();
                }
            });
        };

        var bindChangeMetricSelectEvent = function()
        {
            $(".segment-content").off("change","select.metricList").on("change", "select.metricList", function(e, persist){
                if(typeof persist === "undefined"){
                    persist = false;
                }
                alterMatchesList(this, persist);
                doDragDropBindings();
                autoSuggestValues(this, persist);
            } );
        }

        var autoSuggestValues = function(select, persist) {
            var type = $(select).find("option:selected").attr("value");
            if(!persist) {
                var parents = $(select).parents('.segment-row');
                var loadingElement = parents.find(".segment-loading");
                loadingElement.show();
                var inputElement = parents.find(".metricValueBlock input");
                var segmentName = $('option:selected',select).attr('value');

                // Request auto-suggest values
                var ajaxHandler = new ajaxHelper();
                ajaxHandler.addParams({
                    module: 'API',
                    format: 'json',
                    method: 'API.getSuggestedValuesForSegment',
                    segmentName: segmentName,
                    idSite: piwik.idSite
                }, 'GET');
                ajaxHandler.setCallback(function(response) {
                    loadingElement.hide();

                    inputElement.autocomplete({
                        source: response,
                        minLength: 0,
                        select: function(event, ui){
                            event.preventDefault();
                            $(inputElement).val(ui.item.value);
                        }
                    });

                    inputElement.click(function(e){ inputElement.keydown() });
                });
                ajaxHandler.send(true);
            }
        }

        var alterMatchesList = function(select, persist){
            var oldMatch;
            var type = $(select).find("option:selected").attr("data-type");
            var matchSelector = $(select).parents(".segment-input").siblings(".metricMatchBlock").find("select");
            if(persist === true){
                oldMatch = matchSelector.find("option:selected").val();
            }
            else{
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
            matchSelector.val(oldMatch);
        }

        var getAddNewBlockButtonHtml = function()
        {
            if(typeof addNewBlockButton === "undefined")
            {
                var addNewBlockButton = $("#SegmentEditor > div.segment-add-row").clone();
            }
            return addNewBlockButton.clone();

        }

        var getAddOrBlockButtonHtml = function(){
            if(typeof addOrBlockButton === "undefined")
            {
                var addOrBlockButton = $("#SegmentEditor div.segment-add-or").clone();
            }
            return addOrBlockButton.clone();
        }

        var placeSegmentationFormControls = function(){
            doDragDropBindings();
            $(self.form).find(".scrollable").jScrollPane({
                showArrows: true,
                autoReinitialise: true,
                verticalArrowPositions: 'os',
                horizontalArrowPositions: 'os'
            });
        }

        var bindFormEvents = function(){

            $(self.form).on("click", "a", function(e){
                e.preventDefault();
            });

            $('#closeSegmentationForm').on("click", function() {
                $("#segmentList").show();
                self.form.remove();
            });

            $(self.form).off("click", "a.editSegmentName").on("click", "a.editSegmentName", function(e){
                var oldName = $(e.currentTarget).parents("h3").find("span").text();
                $(e.currentTarget).parents("h3").find("span").hide();
                $(e.currentTarget).hide();
                $(e.currentTarget).before('<input id="edit_segment_name"/>');
                $(e.currentTarget).siblings("#edit_segment_name").focus().val(oldName);
            });

            $(self.form).off("blur", "input#edit_segment_name").on("blur", "input#edit_segment_name", function(e){
                var newName = $(this).val();
                $(e.currentTarget).parents("h3").find("span").text(newName).show();
                $(self.form).find("a.editSegmentName").show();
                $(this).remove();
            });

            $(self.form).on("click", '.segment-element', function(event) {
                event.stopPropagation();
                event.preventDefault();
            });

            $(self.form).find("#available_segments_select").bind("change", function(e){
                var option = $(e.currentTarget).find('option:selected');
                var segment = {};
                segment.idsegment = option.attr("data-idsegment");
                segment.name = option.attr("title");
                segment.definition = option.attr("data-definition");
                openEditForm(segment);

            });

            // attach event that shows/hides child elements of each metric category
            $(self.form).find(".segment-nav > div > ul > li > a").each( function(){
                $(this).on("click", function(e){
                    $(e.currentTarget).siblings("ul").toggle();
                });
            });

            $(self.form).off("click", ".custom_select_search a").on("click", ".custom_select_search a", function(e){
                $(self.form).find("#segmentSearch").val("").trigger("keyup").val("Search");
            });

            // attach event that will clear search input upon focus if its content is default
            $(self.form).find("#segmentSearch").on("focus", function(e){
                var search = $(e.currentTarget).val();
                if(search == "Search")
                    $(e.currentTarget).val("");
            });


            // attach event that will set search input value upon blur if its content is not null
            $(self.form).find("#segmentSearch").on("blur", function(e){
                var search = $(e.currentTarget).val();
                if(search == ""){
                    clearSearchMetricHighlight();
                    $(e.currentTarget).val("Search");
                }
            });

            // bind search action triggering - only when input text is longer than 2 chars
            $(self.form).find("#segmentSearch").on("keyup", function(e){
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


            $(self.form).on("click", ".delete", function(){

                var segmentName = $(self.form).find(".segment-content > h3 > span").text();
                var segmentId = $(self.form).find("#available_segments_select option:selected").attr("data-idsegment")
                var params = {
                    "idsegment" : segmentId
                };
                $('#confirm').find('#name').text( segmentName );
                if(segmentId != ""){
                    piwikHelper.modalConfirm( '#confirm', {
                        yes: function(){
                            self.deleteMethod(params);
                        }
                    });
                }
            });

            $(self.form).on("click", "a.close", function(e){
                $("#segmentList").show();
                self.form.unbind().remove();
            });

            $("body").on("keyup", function(e){
                if(e.keyCode == "27"){
                    $("#segmentList").show();
                    $(self.form).remove();
                }
            });

            bindChangeMetricSelectEvent();

            placeSegmentationFormControls();

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


        }

        var searchSegments = function(search){
            // pre-proces search string to normalized form
            search = normalizeSearchString(search);
            // ---
            // clear all previous search highlights and hide all categories
            // to allow further showing only matching ones, while others remain invisible
            clearSearchMetricHighlight();
            $(self.form).find('.segment-nav div > ul > li').hide();
            var curStr = "";
            // 1 - do most obvious selection -> mark whole categories matching search string
            // also expand whole category
            $(self.form).find('.segment-nav div > ul > li').each( function(){
                curStr = normalizeSearchString($(this).text())
                if(curStr.indexOf(search) > -1){
                    $(this).addClass("searchFound");
                    $(this).find("ul").show();
                    $(this).find("li").show();
                    $(this).show();
                }
            });

            var curMetric = "";
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

            //                if(curStr.indexOf(search) > -1 || curMetric.indexOf(search) > -1)
            //                {
            //                    $(this).addClass("searchFound");
            //                    $(this).find('li').hide();
            //                    $(this).find('li[data*="'+search+'"]').show();
            //                }
            });

            if( $(self.form).find("li.searchFound").length == 0)
            {
                $(self.form).find("div > ul").prepend('<li class="no_results"><a>No results</a></li>').show();

            }
            // check if search allow flag was revoked - then clear all search results
            if(self.searchAllowed == false)
            {
                clearSearchMetricHighlight();
                self.searchAllowed = true;
            }

        }

        var clearSearchMetricHighlight = function(){
            $(self.form).find('.no_results').remove();
            $(self.form).find('.segment-nav div > ul > li').removeClass("searchFound").show();
            $(self.form).find('.segment-nav div > ul > li').removeClass("others").show();
            $(self.form).find('.segment-nav div > ul > li > ul > li').show();
            $(self.form).find('.segment-nav div > ul > li > ul').hide();
        }

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
        }

        var bindSegmentManipulationEvents = function(){
            // upon clicking - add new segment block, then bind 'x' action to newly added row
            $(self.form).on("click", ".segment-add-row a", function(event, data){
                $(self.form).find(".segment-and:last").after(getAndDiv()).after(getMockedFormRow());
                if(typeof data !== "undefined"){
                    $(self.form).find(".metricList:last").val(data);
                }
                $(self.form).find(".metricList:last").trigger('change')
                doDragDropBindings();
            });

            $(self.form).on("click", ".segment-add-row span", function(event, data){
                if(typeof data !== "undefined") {
                    $(self.form).find(".segment-and:last").after(getAndDiv()).after(getMockedFormRow());
                    $(self.form).find(".metricList:last").val(data).trigger('change');
                    doDragDropBindings();
                }
            });

            //   add new OR block
            $(self.form).on("click", ".segment-add-or  a", function(event, data){
                $(event.currentTarget).parents(".segment-rows").find(".segment-or:last").after(getOrDiv()).after(getMockedInputRowHtml());
                if(typeof data !== "undefined"){
                    $(event.currentTarget).parents(".segment-rows").find(".metricList:last").val(data);
                }
                $(event.currentTarget).parents(".segment-rows").find(".metricList:last").trigger('change');
                doDragDropBindings();
            });

            $(self.form).on("click", ".segment-close",  function(e){
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
        }

        var addForm = function(mode){

            $("#segmentEditorPanel").find(".segment-element:visible").unbind().remove();
            if(typeof self.form !== "undefined")
            {
                self.form.unbind().remove();
            }
            // remove any remaining forms

            self.form = getFormHtml();
            $("#segmentEditorPanel").prepend(self.form);

            setLeftMargin('#segmentEditorPanel > .segment-element');
            bindFormEvents();
            bindSegmentManipulationEvents();
            makeDropList("#enabledAllUsers" , "#enabledAllUsers_select");
            makeDropList("#visible_to_website" , "#visible_to_website_select");
            makeDropList("#available_segments" , "#available_segments_select");
            $(self.form).find(".saveAndApply").bind("click", function(e){
                e.preventDefault();
                parseFormAndSave();
            });

            if(typeof mode !== "undefined" && mode == "new")
            {
                $(self.form).find(".editSegmentName").trigger('click');
                $(self.form).find("#edit_segment_name").val("");
            }
            $("#segmentList").hide();

        }

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
                        /*if(value == ""){
                            value= '';
                        }*/
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
        }

        var parseFormAndSave = function(){
            var segmentName = $(self.form).find(".segment-content > h3 >span").text();
            var segmentStr = parseForm();
            var segmentId = $(self.form).find('#available_segments_select > option:selected').attr("data-idsegment");
            var user = $(self.form).find("#enabledAllUsers_select option:selected").val();
            var params = {
                "name": segmentName,
                "definition": segmentStr,
                "enabledAllUsers": user,
                "idSite": $('#visible_to_website option:selected').attr('value')
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
        }

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
                    if(ui.item.value) {
                        dropList.text(ui.item.label);
                        $(self.form).find(selectId).trigger("change");
                    }
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
            $('body').on('mouseup',function(e){
                if(!$(e.target).parents(spanId).length && !$(e.target).is(spanId) && !$(e.target).parents(spanId).length
                    && !$(e.target).parents(".ui-autocomplete").length && !$(e.target).is(".ui-autocomplete") && !$(e.target).parents(".ui-autocomplete").length
                    ) {
                    dropList.autocomplete().autocomplete("close");
                }
            });
        }

        var setLeftMargin = function(selector) {
//            setTimeout( function() {
                $(selector).css({left: Math.max($('#periodString')[0].offsetWidth) + 10});
//            }, 500);
        }

        var initHtml = function() {
            if(typeof self.content !== "undefined"){
                self.content.unbind();
            }
            var html = getListHtml();

            if(typeof self.content !== "undefined"){
                self.content.html($(html).html());
            } else {
                $("#"+self.targetId).append(html);
                self.content = $("#"+self.targetId).find(".segmentationContainer");
            }
            setLeftMargin('.segmentationContainer');

            // assign content to object attribute to make it easil accesible through all widget methods
            bindListEvents();
            markCurrentSegment();
        }
        initHtml();
    };

    return segmentation;
})(jQuery);


$(document).ready( function(){
    // ie. admin screens
    if(typeof availableSegments == "undefined") {
        return;
    }

    var changeSegment = function(params){
        $('#segmentEditorPanel a.close').click();

        return broadcast.propagateNewPage('segment=' + params.definition, true, true);
    };

    var addSegment = function(params){
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.setLoadingElement();
        jQuery.extend(params, {
            "module": 'API',
            "format": 'json',
            "method": 'SegmentEditor.add'
        });
        ajaxHandler.addParams(params, 'GET');
        ajaxHandler.useCallbackInCaseOfError();
        ajaxHandler.setCallback(function (response) {
            if (response && response.result == 'error') {
                alert(response.message);
            } else {
                changeSegment(params);
            }
        });
        ajaxHandler.send(true);
    };

    var updateSegment = function(params){
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.setLoadingElement();
        jQuery.extend(params, {
            "module": 'API',
            "format": 'json',
            "method": 'SegmentEditor.update'
        });
        ajaxHandler.addParams(params, 'GET');
        ajaxHandler.useCallbackInCaseOfError();
        ajaxHandler.setCallback(function (response) {
            if (response && response.result == 'error') {
                alert(response.message);
            } else {
                changeSegment(params);
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
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement();
        ajaxHandler.send(true);
    };

    var testSegment = function(segmentStr){
        console.log(segmentStr);
    }

    var segmentationFtw = new Segmentation({
        "targetId"   : "segmentList",
        "segmentAccess" : "write",
        "segmentList" : availableSegments,
        "addMethod": addSegment,
        "updateMethod": updateSegment,
        "deleteMethod": deleteSegment,
        "segmentSelectMethod": changeSegment,
        "testSegmentMethod": testSegment,
        "currentSegmentStr": broadcast.getValueFromHash('segment'),
        "currentSegmentsGlobal": broadcast.getValueFromHash('segment')
    });

    $('body').on('mouseup',function(e){
        if($(e.target).parents('.segment-element').length === 0 && !$(e.target).is('.segment-element') && $(e.target).hasClass("ui-corner-all") == false
            && $(e.target).hasClass("ddmetric") == false  && $(".segment-element:visible").length == 1 ) {
            $(".segment-element:visible").unbind().remove();
            $("#segmentList").show();
        }

        if($(e.target).parents('.segmentList').length === 0 && $(".segmentationContainer").hasClass("visible")){

            $(".segmentationContainer").trigger("click");
        }

    });
});