/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

Segmentation = (function($) {

    piwikHelper.registerShortcut('s', _pk_translate('CoreHome_ShortcutSegmentSelector'), function (event) {
        if (event.altKey) {
            return;
        }
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false; // IE
        }
        $('.segmentListContainer .segmentationContainer .title').trigger('click').focus();
    });


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
            return self.currentSegmentStr;
        };

        segmentation.prototype.setSegment = function(segmentStr){
            this.currentSegmentStr = segmentStr;
        };

        segmentation.prototype.setTooltip = function (segmentDescription) {

            var title = _pk_translate('SegmentEditor_ChooseASegment') + '.';
            title += ' '+ _pk_translate('SegmentEditor_CurrentlySelectedSegment', [segmentDescription]);

            $(this.content).attr('title', title);
        };

        segmentation.prototype.markComparedSegments = function() {
            var comparisonService = window.CoreHome.ComparisonsStoreInstance;
            var comparedSegments = comparisonService.getSegmentComparisons().map(function (comparison) {
                return comparison.params.segment;
            });

            $('div.segmentList ul li[data-definition]', this.target).removeClass('comparedSegment').filter(function () {
                var definition = $(this).attr('data-definition');
                return comparedSegments.indexOf(definition) !== -1 || comparedSegments.indexOf(decodeURIComponent(definition)) !== -1;
            }).each(function () {
                $(this).addClass('comparedSegment');
            });
        };

        segmentation.prototype.markCurrentSegment = function(){
            var current = this.getSegment();

            var segmentationTitle = $(this.content).find(".segmentationTitle");
            var title;
            if( current != "")
            {
                // this code is mad, and may drive you mad.
                // the whole segmentation editor needs to be rewritten in Vue with clean code
                var selector = 'div.segmentList ul li[data-definition="'+current+'"]';
                var foundItems = $(selector, this.target);

                if (foundItems.length === 0) {
                    try {
                        currentDecoded = piwikHelper.htmlDecode(current);
                        selector = 'div.segmentList ul li[data-definition="'+currentDecoded+'"]';
                        foundItems = $(selector, this.target);
                    } catch(e) {}
                }
                if (foundItems.length === 0) {
                    try {
                        currentDecoded = piwikHelper.htmlDecode(decodeURIComponent(current));
                        selector = 'div.segmentList ul li[data-definition="'+currentDecoded+'"]';
                        foundItems = $(selector, this.target);
                    } catch(e) {}
                }

                if (foundItems.length > 0) {
                    var idSegment = $(foundItems).first().attr('data-idsegment');
                    title = getSegmentName(getSegmentFromId(idSegment));
                } else {
                    title = _pk_translate('SegmentEditor_CustomSegment');
                }
                segmentationTitle.addClass('segment-clicked').html( title );
                this.setTooltip(title);
            }
            else {
                title = this.translations['SegmentEditor_DefaultAllVisits'];
                segmentationTitle.text(title);
                this.setTooltip(title);
            }
        };

        function handleAddNewSegment() {
            var segmentToAdd = broadcast.getValueFromHash('addSegmentAsNew') || broadcast.getValueFromUrl('addSegmentAsNew');
            if (segmentToAdd) {
                showAddNewSegmentForm({ definition: decodeURIComponent(segmentToAdd) });
            }
        }

        var getSegmentFromId = function (id) {
            if(self.availableSegments.length > 0) {
                for(var i = 0; i < self.availableSegments.length; i++)
                {
                    var segment = self.availableSegments[i];
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
                (self.currentSegmentStr == "" ? " class='segmentSelected'" : "")
                + ' data-definition=""><span class="segname" tabindex="4">' + self.translations['SegmentEditor_DefaultAllVisits']
                + ' ' + self.translations['General_DefaultAppended']
                + '</span>';
            var comparisonService = window.CoreHome.ComparisonsStoreInstance;
            if (comparisonService.isComparisonEnabled()
                || comparisonService.isComparisonEnabled() === null // may not be initialized since this code is outside of Vue
            ) {
                listHtml += '<span class="compareSegment allVisitsCompareSegment" title="' + _pk_translate('SegmentEditor_CompareThisSegment') + '"></span>';
            }
            listHtml += '</li>';

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

                    if( checkSelected == self.currentSegmentStr ||
                        checkSelected == decodeURIComponent(self.currentSegmentStr)
                    ) {
                        injClass = 'class="segmentSelected"';
                    }
                    listHtml += '<li data-idsegment="'+segment.idsegment+'" data-definition="'+ (segment.definition).replace(/"/g, '&quot;') +'" '
                        +injClass+' title="'+ getSegmentTooltipEnrichedWithUsername(segment) +'"><span class="segname" tabindex="4">'+getSegmentName(segment)+'</span>';
                    if(self.segmentAccess == "write") {
                        listHtml += '<span class="editSegment" title="'+ self.translations['General_Edit'].toLocaleLowerCase() +'"></span>';
                    }
                    if (comparisonService.isComparisonEnabled()
                        || comparisonService.isComparisonEnabled() === null // may not be initialized since this code is outside of Vue
                    ) {
                        listHtml += '<span class="compareSegment" title="' + _pk_translate('SegmentEditor_CompareThisSegment') + '"></span>';
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
        };

        var getFormHtml = function() {
            var html = self.editorTemplate.find("> .segment-element").clone();
            $(html).find(".segment-content > h3")
              .after('<div class="segment-generator-container"></div>')
              .show();
            return html;
        };

        var closeAllOpenLists = function() {
            $(".segmentationContainer", self.target).each(function() {
                if($(this).closest('.segmentEditorPanel').hasClass("expanded"))
                    $(this).trigger("click");
            });
        };

        var openEditForm = function(segment){
            addForm("edit", segment);

            $(self.form).find(".segment-content > h3 > span")
                .html( getSegmentName(segment) )
                .prop('title', getSegmentTooltipEnrichedWithUsername(segment));

            $(self.form).find('.available_segments_select').val(segment.idsegment);

            $(self.form).find('.available_segments a.dropList')
                .html( getSegmentName(segment) )
                .prop( 'title', getSegmentTooltipEnrichedWithUsername(segment));

            $(self.form).find(".metricList").each( function(){
                $(this).trigger("change", true);
            });
        };

        var displayFormAddNewSegment = function (segment) {
            closeAllOpenLists();
            addForm("new", segment);
        };

        function showAddNewSegmentForm(segment) {
            var parameters = {isAllowed: true};
            window.CoreHome.Matomo.postEvent('Segmentation.initAddSegment', parameters);
            if (parameters && !parameters.isAllowed) {
                return;
            }

            displayFormAddNewSegment(segment);
        }

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
        };

        var clearFilterSegmentList = function () {
            $(self.target).find(" .filterNoResults").remove();
            $(self.target).find(".segmentList li").each(function () {
                $(this).show();
            });
            $(self.target).find(".segmentList .segmentsVisibleToSuperUser").show();
            $(self.target).find(".segmentList .segmentsSharedWithMeBySuperUser").show();
        };

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
                        self.target.closest('.segmentEditorPanel').removeClass('expanded');
                    }
                } else {
                    // for each visible segmentationContainer -> trigger click event to close and kill scrollpane - very important !
                    closeAllOpenLists();
                    self.target.closest('.segmentEditorPanel').addClass('expanded');
                    self.target.find('.segmentFilter').val(self.translations['General_Search']).trigger('keyup');
                }
            });

            self.target.on('click', '.editSegment', function(e) {
                $(this).closest(".segmentationContainer").trigger("click");
                var target = $(this).parent("li");

                openEditFormGivenSegment(target);
                e.stopPropagation();
                e.preventDefault();
            });

            self.target.on('click', '.compareSegment', function (e) {
                e.stopPropagation();
                e.preventDefault();

                var comparisonService = window.CoreHome.ComparisonsStoreInstance;
                comparisonService.addSegmentComparison({
                    segment: $(e.target).closest('li').data('definition'),
                });

                self.markComparedSegments();

                closeAllOpenLists();
            });

            self.target.on("click", ".segmentList li", function (e) {
                if ($(e.currentTarget).hasClass("grayed") !== true) {
                    var segmentDefinition = $(this).data("definition");

                    if (!piwikHelper.isReportingPage()) {
                        // we update segment on location change success
                        self.setSegment(segmentDefinition);
                    }

                    self.markCurrentSegment();
                    self.segmentSelectMethod(segmentDefinition);
                    toggleLoadingMessage(segmentDefinition.length);
                }
            });

            self.target.on('click', '.add_new_segment', function (e) {
                e.stopPropagation();

                showAddNewSegmentForm();
            });

            // emulate a click when pressing enter on one of the segments or the add button
            self.target.on("keyup", ".segmentList li, .add_new_segment", function (event) {
                var keycode = (event.keyCode ? event.keyCode : (event.which ? event.which : event.key));
                if(keycode == '13'){
                    $(this).trigger('click');
                }
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

            self.target.on('click',  "a.editSegmentName", function (e) {
                var $h3 = $(e.currentTarget).parents("h3");
                $h3.css({'margin': '0 0 0 6px'});
                var oldName = $h3.find("span").text();
                $h3.find("span").hide();
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


            self.target.on('click', ".delete", function() {
                var segmentName = $(self.form).find(".segment-content > h3 > span").text();
                var segmentId = $(self.form).find(".available_segments_select").val();
                var params = {
                    "idsegment" : segmentId
                };
                $('#segment-delete-confirm').find('#name').text( segmentName );
                if(segmentId != ""){
                    piwikHelper.modalConfirm($('#segment-delete-confirm'), {
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
                if(e.keyCode == "27" || e.which === 27) {
                    if (self.target.find('[uicontrol="expandable-select"] .expandableList:visible').length) {
                        return;
                    }
                    if (Piwik_Popover.isOpen()) {
                        Piwik_Popover.close();
                        return;
                    }
                    $(".segmentListContainer", self.target).show();
                    closeForm();
                }
            });

            //
            // segment manipulation events
            //

        };

        var getAddOrBlockButtonHtml = function(){
            if(typeof addOrBlockButton === "undefined") {
                var addOrBlockButton = self.editorTemplate.find("div.segment-add-or").clone();
            }
            return addOrBlockButton.clone();
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
            closeForm();
            // remove any remaining forms


            self.form = getFormHtml();
            self.target.prepend(self.form);

            piwikHelper.setMarginLeftToBeInViewport(self.form);

            // if there's enough space to the left & not enough space to the right,
            // anchor the form to the right of the selector
            if (self.form.width() + self.target.offset().left > $(window).width()
                && self.form.width() < self.target.offset().left + self.target.width()
            ) {
                self.form.addClass('anchorRight');
            }

            if(mode == "edit") {
                $(self.form).find('.enable_all_users_select > option[value="' + segment.enable_all_users + '"]').prop("selected",true);

                // Replace "Visible to me" by "Visible to $login" when user is super user
                if(hasSuperUserAccessAndSegmentCreatedByAnotherUser(segment)) {
                    $(self.form).find('.enable_all_users_select > option[value="' + 0 + '"]').text(segment.login);
                }
                $(self.form).find('.visible_to_website_select > option[value="'+segment.enable_only_idsite+'"]').prop("selected",true);
                $(self.form).find('.auto_archive_select > option[value="'+segment.auto_archive+'"]').prop("selected",true);
            }

            if (segment !== undefined && segment.definition != ""){
                self.currentSegmentStr = segment.definition;
                self.form.find('.segment-generator-container').attr('model-value', JSON.stringify(segment.definition));
            }

            makeDropList(".enable_all_users" , ".enable_all_users_select");
            makeDropList(".visible_to_website" , ".visible_to_website_select");
            makeDropList(".auto_archive" , ".auto_archive_select");
            $(self.form).find(".saveAndApply").bind("click", function (e) {
                e.preventDefault();
                parseFormAndSave();
            });
            $(self.form).find(".testSegment").bind("click", function (e) {
                e.preventDefault();
                testSegment();
            });

            if(typeof mode !== "undefined" && mode == "new")
            {
                $(self.form).find(".editSegmentName").trigger('click');
            }
            $(".segmentListContainer", self.target).hide();

            self.target.closest('.segmentEditorPanel').addClass('editing');

            var segmentGeneratorContainer = $('.segment-generator-container', self.form)[0];

            var createVueApp = window.CoreHome.createVueApp;
            var SegmentGenerator = window.SegmentEditor.SegmentGenerator;

            var app = createVueApp({
              template: '<root :add-initial-condition="true" v-model="value" />',
              components: {
                root: SegmentGenerator,
              },
              watch: {
                value: function () {
                  self.currentSegmentStr = this.value;
                },
              },
              data() {
                return {
                  value: self.currentSegmentStr,
                };
              },
            });
            app.mount(segmentGeneratorContainer);

            segmentGeneratorContainer.addEventListener('matomoVueDestroy', function () {
              app.unmount();
            });
        };

        var closeForm = function () {
            self.currentSegmentStr = '';

            if (typeof self.form !== "undefined") {
              $(self.form).find('.segment-generator-container')[0].dispatchEvent(
                new CustomEvent('matomoVueDestroy'),
              );

              $(self.form).unbind().remove();
            }
            self.target.closest('.segmentEditorPanel').removeClass('editing');
        };

        var parseFormAndSave = function(){
            var segmentName = $(self.form).find(".segment-content > h3 >span").text();
            var segmentStr = self.currentSegmentStr;
            var segmentId = $(self.form).find(".available_segments_select").val() || "";
            var user = $(self.form).find(".enable_all_users_select option:selected").val();
            // if create realtime segments is disabled, the select field is not available, but we need to use autoArchive = 1
            if ($(self.form).find(".auto_archive_select").length) {
              var autoArchive = $(self.form).find(".auto_archive_select option:selected").val() || 0;
            } else {
              var autoArchive = 1;
            }
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

                if(segmentStr != getSegmentFromId(segmentId).definition && $('.segment-definition-change-confirm').data('hideMessage') != 1) {
                    var isBrowserArchivingAvailableForSegments = $('.segment-definition-change-confirm').data('segmentProcessedOnRequest');
                    var isRealTimeSegment = (autoArchive == 0);
                    var segmentNotProcessedOnRequest = !isBrowserArchivingAvailableForSegments || !isRealTimeSegment;

                    $('.process-on-request, .no-process-on-request').hide();

                    if (segmentNotProcessedOnRequest) {
                        $('.no-process-on-request').show();
                    } else {
                        $('.process-on-request').show();
                    }

                    piwikHelper.modalConfirm('.segment-definition-change-confirm', {
                        yes: function () {
                            if ($('#hideSegmentMessage:checked').length) {
                                var ajaxHandler = new ajaxHelper();
                                ajaxHandler.setLoadingElement();
                                ajaxHandler.addParams({
                                    "module": 'API',
                                    "format": 'json',
                                    "method": 'UsersManager.setUserPreference',
                                    "userLogin": piwik.userLogin,
                                    "preferenceName": "hideSegmentDefinitionChangeMessage",
                                    "preferenceValue": "1"
                                }, 'GET');
                                ajaxHandler.useCallbackInCaseOfError();
                                ajaxHandler.setCallback(function (response) {
                                    self.updateMethod(params);
                                });
                                ajaxHandler.send();
                            } else {
                                self.updateMethod(params);
                            }
                        }
                    });
                } else {
                    self.updateMethod(params);
                }
            }
        };

        var testSegment = function() {
            var segmentStr = self.currentSegmentStr;
            var encSegment = jQuery(jQuery('.segmentEditorPanel').get(0)).data('uiControlObject').uriEncodeSegmentDefinition(segmentStr);

            var url = $.param({
                date: piwik.currentDateString,
                period: piwik.period,
                idSite: piwik.idSite,
                module: 'Live',
                action: 'getLastVisitsDetails',
                segment: encSegment,
                inPopover: 1,
            });

            Piwik_Popover.createPopupAndLoadUrl(url, _pk_translate('Live_VisitsLog'));
        };

        var makeDropList = function(spanId, selectId){
            var select = $(self.form).find(selectId);
            select.hide().closest('.select-wrapper').children().hide();
            var dropList = $( '<a class="dropList dropdown">' )
                .insertAfter( select.closest('.hide-select') )
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
            var self = this;

            var html = getListHtml();

            if(typeof self.content !== "undefined"){
                this.content.html($(html).html());
            } else {
                this.target.append(html);
                this.content = this.target.find(".segmentationContainer");
            }

            // assign content to object attribute to make it easily accessible through all widget methods
            this.markCurrentSegment();
            setTimeout(function () {
                self.markComparedSegments();
            });

            // Loading message
            var segmentIsSet = this.getSegment().length;
            toggleLoadingMessage(segmentIsSet);
        };

        if (piwikHelper.isReportingPage()) {
          var watch = window.Vue.watch;
          var MatomoUrl = window.CoreHome.MatomoUrl;
          watch(() => MatomoUrl.hashParsed.value.segment, function (value) {
            var segment = value || '';

            if (self.getSegment() != segment) {
              self.setSegment(segment);
              self.initHtml();
            } else {
              setTimeout(function () {
                self.markComparedSegments();
              });
            }
          });
        }

        this.initHtml();
        bindEvents();
        handleAddNewSegment();
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

        this.uriEncodeSegmentDefinition = function (segmentDefinition) {
            segmentDefinition = cleanupSegmentDefinition(segmentDefinition);
            segmentDefinition = encodeURIComponent(segmentDefinition);
            return segmentDefinition;
        };

        this.changeSegment = function(segmentDefinition) {
            if (piwikHelper.isReportingPage()) {
                var MatomoUrl = window.CoreHome.MatomoUrl;
                var segment = MatomoUrl.hashParsed.value.segment;
                if (segmentDefinition !== segment) {
                  // eg when using back button the date might be actually already changed in the URL and we do not
                  // want to change the URL again
                  MatomoUrl.updateHash(Object.assign({}, MatomoUrl.hashParsed.value, {
                    segment: segmentDefinition.replace(/%$/, '%25').replace(/%([^\d].)/g, "%25$1"),
                  }));
                }
                return false;
            } else {
                return this.forceSegmentReload(segmentDefinition);
            }
        };

        this.forceSegmentReload = function (segmentDefinition) {
            segmentDefinition = this.uriEncodeSegmentDefinition(segmentDefinition);

            if (piwikHelper.isReportingPage()) {
                return broadcast.propagateNewPage('', true, 'addSegmentAsNew=&segment=' + segmentDefinition, ['compareSegments', 'comparePeriods', 'compareDates']);
            } else {
                // eg in case of exported dashboard
                return broadcast.propagateNewPage('segment=' + segmentDefinition, true, 'addSegmentAsNew=&segment=' + segmentDefinition, ['compareSegments', 'comparePeriods', 'compareDates']);
            }
        };

        this.changeSegmentList = function () {};

        var cleanupSegmentDefinition = function(definition) {
            definition = definition.replace(/'/g, "%27");
            definition = definition.replace(/&/g, "%26");
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

                    self.impl.markCurrentSegment();

                    self.$element.find('a.close').click();
                    self.forceSegmentReload(params.definition);

                    self.changeSegmentList(self.props.availableSegments);
                }
            });
            ajaxHandler.send();
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

                    self.impl.markCurrentSegment();

                    self.$element.find('a.close').click();
                    self.forceSegmentReload(params.definition);

                    self.changeSegmentList(self.props.availableSegments);
                }
            });
            ajaxHandler.send();
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
                    self.forceSegmentReload('');

                    $('.ui-dialog-content').dialog('close');

                    self.changeSegmentList(self.props.availableSegments);
                }
            });

            ajaxHandler.send();
        };

        function getSegmentFromRequest()
        {
            var hashStr = broadcast.getHashFromUrl();
            var segmentFromRequest;

            if (hashStr && hashStr.indexOf('segment=') !== -1) {
                // needed in case "segment = ''" in hash but set in query via 'segment=foo==bar'.
                segmentFromRequest = broadcast.getValueFromHash('segment');
            } else {
                segmentFromRequest = broadcast.getValueFromHash('segment')
                    || encodeURIComponent(self.props.selectedSegment)
                    || broadcast.getValueFromUrl('segment');
            }

            segmentFromRequest = decodeURIComponent(segmentFromRequest);

            return segmentFromRequest;
        }

        var segmentFromRequest = getSegmentFromRequest();

        var userSegmentAccess = (this.props.authorizedToCreateSegments) ? "write" : "read";

        this.impl = new Segmentation({
            "target"   : this.$element.find(".segmentListContainer"),
            "editorTemplate": $('.SegmentEditor', self.$element),
            "segmentAccess" : userSegmentAccess,
            "availableSegments" : this.props.availableSegments,
            "addMethod": addSegment,
            "updateMethod": updateSegment,
            "deleteMethod": deleteSegment,
            "segmentSelectMethod": function () { self.changeSegment.apply(self, arguments); },
            "currentSegmentStr": segmentFromRequest,
            "translations": this.props.segmentTranslations
        });

        this.onMouseUp = function(e) {
            if ($(e.target).closest('.segment-element').length === 0
                && !$(e.target).is('.ui-menu-item-wrapper')
                && !$(e.target).is('.segment-element')
                && $(e.target).hasClass("ui-corner-all") == false
                && $(e.target).hasClass("ui-icon-closethick") == false
                && $(e.target).hasClass("ui-button-text") == false
                && $(".segment-element:visible", self.$element).length == 1
            ) {
                if (Piwik_Popover.isOpen()) {
                    Piwik_Popover.close();
                } else {
                    $(".segment-element:visible a.close", self.$element).click();
                }
            }

            if ($(e.target).closest('.segmentListContainer').length === 0
                && self.$element.hasClass("expanded")
            ) {
                $(".segmentationContainer", self.$element).trigger("click");
            }
        };

        $('body').on('mouseup', this.onMouseUp);

        initTopControls();

        window.CoreHome.Matomo.postEvent('piwikSegmentationInited');
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
