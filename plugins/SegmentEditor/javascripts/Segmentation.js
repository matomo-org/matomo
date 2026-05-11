/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

Segmentation = (function($) {
    const FORM_MODE_EDIT = 'edit';
    const FORM_MODE_NEW = 'new';
    const SINGLETON_WARNING_MESSAGE = 'Segmentation is initialized more than once on this page. Only one segment selector control per page is supported.';
    let activeSegmentationInstance = null;

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

        if (activeSegmentationInstance) {
            throw new Error(SINGLETON_WARNING_MESSAGE);
        }

        self.currentSegmentStr = "";
        self.segmentAccess = "read";
        self.availableSegments = [];

        for (var item in config) {
            self[item] = config[item];
        }

        self.editorTemplate = self.editorTemplate.detach();

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

        // SegmentSelectorStore is singleton by design and backs the single supported
        // segment selector control on a page. Pages that render more than one
        // segment selector are unsupported and should not initialize another instance.
        var segmentSelectorStore = window.SegmentEditor && window.SegmentEditor.SegmentSelectorStore;
        if (!segmentSelectorStore) {
            throw new Error('SegmentSelectorStore must be available before Segmentation initializes');
        }

        segmentation.prototype.setAvailableSegments = function (segments) {
            this.availableSegments = segments;
            segmentSelectorStore.setAvailableSegments(segments);
        };

        segmentation.prototype.getSegment = function(){
            var self = this;
            return self.currentSegmentStr;
        };

        segmentation.prototype.setSegment = function(segmentStr){
            this.currentSegmentStr = segmentStr;
            segmentSelectorStore.setCurrentSegment(segmentStr);
        };

        segmentation.prototype.setTooltip = function () {};

        // We will listen to changes in the Segment Comparison Store
        // so we can mark compared segments properly. This will now include deletion of compared segments.
        piwik.on('piwikComparisonsChanged', function () {
          self.markComparedSegments();
        });

        segmentation.prototype.markComparedSegments = function() {
            segmentSelectorStore.notifyChange();
        };
        segmentation.prototype.checkIfComparedSegmentsHasReachedLimit = function() {
            segmentSelectorStore.notifyChange();
            return false;
        };

        segmentation.prototype.markCurrentSegment = function(){
            segmentSelectorStore.setCurrentSegment(self.getSegment());
            // MatomoUrl.updatePageTitle() reads the active segment label by
            // querying .segmentEditorPanel .segmentationTitle from the DOM.
            // The Vue panel re-renders that text on the next tick after the
            // store mutation, so wait for the render before reading it,
            // otherwise the page title sticks on the previous segment label.
            window.Vue.nextTick(function () {
                window.CoreHome.MatomoUrl.updatePageTitle();
            });
        };

        function handleAddNewSegment() {
            var segmentToAdd = broadcast.getValueFromHash('addSegmentAsNew') || broadcast.getValueFromUrl('addSegmentAsNew');
            if (segmentToAdd) {
                openAddSegmentForm({ definition: decodeURIComponent(segmentToAdd) });
            }
        }

        var getSegmentFromId = function (id) {
            return segmentSelectorStore.getSegmentFromId(id);
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

        var getSegmentTooltipText = function(segment) {
            var segmentName = piwikHelper.htmlDecode(segment.name);
            if(hasSuperUserAccessAndSegmentCreatedByAnotherUser(segment)) {
                segmentName += ' (';
                segmentName += _pk_translate('General_CreatedByUser', [segment.login]);

                if(segment.enable_all_users == 0) {
                    segmentName += ', ' + _pk_translate('SegmentEditor_VisibleToSuperUser');
                }

                segmentName += ')';
            }
            return segmentName;
        };

        var getSegmentName = function(segment) {
            return sanitiseSegmentName(segment.name);
        };

        var getPlainSegmentName = function(segment) {
            return piwikHelper.htmlDecode(segment.name);
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

        var openEditForm = function(segment){
            closePanel();
            addForm(FORM_MODE_EDIT, segment);

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

        function openAddSegmentForm(segment) {
            var parameters = {isAllowed: true};
            window.CoreHome.Matomo.postEvent('Segmentation.initAddSegment', parameters);
            if (parameters && !parameters.isAllowed) {
                return;
            }

            closePanel();
            addForm(FORM_MODE_NEW, segment);
        }

        function togglePanel() {
          if (self.target.closest('.segmentEditorPanel').hasClass("expanded")) {
            closePanel();
          } else {
            openPanel();
          }
        }

        function openPanel() {
          self.target.closest('.segmentEditorPanel').addClass('expanded');
          segmentSelectorStore.setPanelExpanded(true);
          self.target[0].dispatchEvent(new CustomEvent('SegmentEditor.resetFilter'));
        }

        function closePanel() {
          self.target.closest('.segmentEditorPanel').removeClass('expanded');
          segmentSelectorStore.setPanelExpanded(false);
        }

        function askToDeleteSegment(idSegment) {
          if (!idSegment) {
            return;
          }

          const segment = getSegmentFromId(idSegment);
          if (!segment) {
            return;
          }

          const label = _pk_translate('SegmentEditor_AreYouSureDeleteSegment', [getSegmentName(segment)]);
          $('#segment-delete-confirm').find('h2').text(label);
          piwikHelper.modalConfirm($('#segment-delete-confirm'), {
            yes: function(){
              self.deleteMethod({
                "idsegment" : idSegment
              });
            }
          });
        }

        function toggleStarredSegment($segment, idSegment) {
          segmentSelectorStore.toggleStarredSegmentById(idSegment);
        }

        function onSegmentsStarChange(callback) {
          return segmentSelectorStore.onStarChange(callback);
        }

        var bindEvents = function () {
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
                var segmentId = $(self.form).find(".available_segments_select").val();
                modalService.deleteSegment(segmentId);
            });

            self.target.on("click", "a.close", function (e) {
                $(".segmentListContainer", self.target).show();
                modalService.closeEditModal();
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
                    modalService.closeEditModal();
                }
            });

        };

        function getCanUserEditSegment(segment) {
          return segmentSelectorStore.getCanUserEditSegment(segment);
        }

        function getDeleteSegmentTitle(segment, canEdit) {
          if (!segment) {
            return '';
          }

          return segmentSelectorStore.getDeleteSegmentTitle(segment, canEdit);
        }

        function getEditSegmentTitle(segment, canEdit) {
          if (!segment) {
            return '';
          }

          return segmentSelectorStore.getEditSegmentTitle(segment, canEdit);
        }

        function getStarSegmentTitle(segment, canEdit) {
          if (!segment) {
            return '';
          }

          return segmentSelectorStore.getStarSegmentTitle(segment, canEdit);
        }

        function updateStarSegmentTitle($starButton, segment) {
          if (!segment) {
            $starButton.attr('title', '');
            if (typeof $starButton.data('ui-tooltip-title') !== 'undefined') {
              $starButton.data('ui-tooltip-title', '');
            }
            return;
          }

          const canEdit = getCanUserEditSegment(segment);
          const title = getStarSegmentTitle(segment, canEdit);
          $starButton.attr('title', title);
          if (typeof $starButton.data('ui-tooltip-title') !== 'undefined') {
            $starButton.data('ui-tooltip-title', title);
          }
        }

        function triggerStarAnimation($segment, segment, isError = false) {
          $segment.toggleClass('segmentStarred', segment.starred);
          if (!$segment.is(":visible")) {
            return;
          }
          $segment.one('animationend', function avoidAnimationRepetition() {
            $segment.removeClass('segmentStarAnimation');
            $segment.removeClass('segmentStarErrorAnimation');
          });
          $segment.toggleClass('segmentStarAnimation', !isError);
          $segment.toggleClass('segmentStarErrorAnimation', isError);
        }

        function openEditFormGivenSegment(option) {
            var idsegment = option.attr("data-idsegment") || '';

            if (idsegment.length == 0) {
                modalService.openAddSegment();
            } else {
                var segment = getSegmentFromId(idsegment);
                if (segment) {
                    segment.definition = option.data("definition");
                    modalService.openEditSegment(segment);
                }
            }
        }

        function openEditFormGivenIdSegment(idSegment) {
            if (!idSegment) {
                modalService.openAddSegment();
                return;
            }

            const segment = getSegmentFromId(idSegment);
            if (segment) {
                modalService.openEditSegment(segment);
            }
        }

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

            if (mode === FORM_MODE_EDIT) {
                $(self.form).find('.enable_all_users_select > option[value="' + segment.enable_all_users + '"]').prop("selected",true);

                // Replace "Visible to me" by "Visible to $login" when user is super user
                if (hasSuperUserAccessAndSegmentCreatedByAnotherUser(segment)) {
                    $(self.form).find('.enable_all_users_select > option[value="' + 0 + '"]').text(segment.login);
                }
                $(self.form).find('.visible_to_website_select > option[value="'+segment.enable_only_idsite+'"]').prop("selected",true);
                $(self.form).find('.auto_archive_select > option[value="'+segment.auto_archive+'"]').prop("selected",true);
                $(self.form).find('.segment-footer > .delete').show();
            } else {
                $(self.form).find(".editSegmentName").trigger('click');
                $(self.form).find('.segment-footer > .delete').hide();
            }

            if (segment !== undefined && segment.definition != "") {
                self.currentSegmentStr = segment.definition;
                self.form.find('.segment-generator-container').attr('model-value', JSON.stringify(segment.definition));
            }

            makeDropList(".enable_all_users" , ".enable_all_users_select");
            makeDropList(".visible_to_website" , ".visible_to_website_select");
            makeDropList(".auto_archive" , ".auto_archive_select");
            $(self.form).find(".saveAndApply").bind("click", function (e) {
                e.preventDefault();
                modalService.saveSegment();
            });
            $(self.form).find(".testSegment").bind("click", function (e) {
                e.preventDefault();
                modalService.testSegment();
            });

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
            if (segmentId === "") {
                self.addMethod(params);
            } else {
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

        const modalService = {
            openAddSegment(segment) {
                openAddSegmentForm(segment);
            },
            openEditSegment(segment) {
                openEditForm(segment);
            },
            closeEditModal() {
                closeForm();
            },
            deleteSegment(idSegment) {
                askToDeleteSegment(idSegment);
            },
            saveSegment() {
                parseFormAndSave();
            },
            testSegment(segmentDefinition) {
                if (typeof segmentDefinition !== 'undefined') {
                    self.currentSegmentStr = segmentDefinition;
                }
                testSegment();
            }
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

        function getComparedSegmentDefinitions() {
            const comparisonService = window.CoreHome.ComparisonsStoreInstance;
            return comparisonService.getSegmentComparisons().map(function (comparison) {
                return comparison.params.segment;
            });
        }

        function isSegmentCompared(definition, comparedSegments) {
            return comparedSegments.indexOf(definition) !== -1 || comparedSegments.indexOf(decodeURIComponent(definition)) !== -1;
        }

        function selectSegment(segmentDefinition) {
            if (!piwikHelper.isReportingPage()) {
                self.setSegment(segmentDefinition);
            }

            self.markCurrentSegment();
            self.segmentSelectMethod(segmentDefinition);
            toggleLoadingMessage(segmentDefinition.length);
            closePanel();
        }

        function toggleComparisonByDefinition(segmentDefinition) {
            const comparisonService = window.CoreHome.ComparisonsStoreInstance;
            const comparedSegments = getComparedSegmentDefinitions();
            if (isSegmentCompared(segmentDefinition, comparedSegments)) {
                comparisonService.removeSegmentComparisonByDefinition(segmentDefinition);
            } else {
                comparisonService.addSegmentComparison({
                    segment: segmentDefinition,
                });
            }
            closePanel();
        }

        function openEditSegmentById(idSegment) {
            const segment = getSegmentFromId(idSegment);
            if (segment) {
                closePanel();
                modalService.openEditSegment(segment);
            }
        }

        const removeSelectorEventListeners = [];
        function bindSelectorEvent(eventName, handler) {
            const listener = function (event) {
                handler(event.detail || {});
            };
            self.target[0].addEventListener(eventName, listener);
            removeSelectorEventListeners.push(function () {
                self.target[0].removeEventListener(eventName, listener);
            });
        }

        bindSelectorEvent('SegmentEditor:toggle-panel', function () {
            togglePanel();
        });
        bindSelectorEvent('SegmentEditor:close-panel', function () {
            closePanel();
        });
        bindSelectorEvent('SegmentEditor:select-segment', function (detail) {
            if (typeof detail.definition === 'undefined') {
                return;
            }
            selectSegment(detail.definition);
        });
        bindSelectorEvent('SegmentEditor:open-add-segment', function () {
            modalService.openAddSegment();
        });
        bindSelectorEvent('SegmentEditor:open-edit-segment', function (detail) {
            openEditSegmentById(detail.idSegment);
        });
        bindSelectorEvent('SegmentEditor:request-delete-segment', function (detail) {
            askToDeleteSegment(detail.idSegment);
        });
        bindSelectorEvent('SegmentEditor:toggle-comparison', function (detail) {
            if (typeof detail.definition === 'undefined') {
                return;
            }
            toggleComparisonByDefinition(detail.definition);
        });

        this.closePanel = closePanel;

        segmentSelectorStore.init({
            availableSegments: self.availableSegments,
            currentSegment: self.currentSegmentStr,
            isUserAnonymous: !!self.isUserAnonymous,
            loginUrl: self.loginUrl,
            manageSegmentsUrl: self.manageSegmentsUrl,
            segmentAccess: self.segmentAccess,
            translations: self.translations,
            userContext: {
                hasSuperUserAccess: !!piwik.hasSuperUserAccess,
                isAnonymous: !!self.isUserAnonymous,
                login: piwik.userLogin,
            },
        });
        this.initHtml = function() {
            this.markCurrentSegment();
            setTimeout(function () {
                self.markComparedSegments();
            });

            // Loading message
            var segmentIsSet = this.getSegment().length;
            toggleLoadingMessage(segmentIsSet);

            $(self.target).tooltip({
              track: true,
              show: { delay: 700, duration: 200 }, // default from Tooltips.js
              hide: false,
            });

            segmentSelectorStore.setPanelExpanded(self.target.closest('.segmentEditorPanel').hasClass('expanded'));
            segmentSelectorStore.notifyChange();
        };

        let removeHashWatcher = null;
        if (piwikHelper.isReportingPage()) {
          var watch = window.Vue.watch;
          var MatomoUrl = window.CoreHome.MatomoUrl;
          removeHashWatcher = watch(() => MatomoUrl.hashParsed.value.segment, function (value) {
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

        window.matomoPluginSegmentEditor = window.matomoPluginSegmentEditor || {};
        window.matomoPluginSegmentEditor.panelAPI = {
          askToDeleteSegment,
          closePanel,
          getDeleteSegmentTitle,
          getEditSegmentTitle,
          getCanUserEditSegment,
          getSegmentFromId,
          onSegmentsStarChange,
          openPanel,
          openEditFormGivenIdSegment,
          togglePanel,
          toggleStarredSegment,
          triggerStarAnimation,
          updateStarSegmentTitle,
        };

        this.destroy = function () {
          if (removeHashWatcher) {
            removeHashWatcher();
            removeHashWatcher = null;
          }
          removeSelectorEventListeners.forEach(function (removeListener) {
            removeListener();
          });
          if (activeSegmentationInstance === self) {
            activeSegmentationInstance = null;
          }
        };

        this.initHtml();
        bindEvents();
        handleAddNewSegment();
        activeSegmentationInstance = self;
    };

    return segmentation;
})(jQuery);

$(document).ready(function() {
    var exports = require('piwik/UI');
    var UIControl = exports.UIControl;

    /**
     * Sets up and handles events for the segment selector & editor control.
     *
     * @param {Element} element The HTML element generated by the SegmentSelectorControl PHP class.
     *                          Should have the CSS class 'segmentEditorPanel'.
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

                    params.name = piwikHelper.htmlEntities(params.name);
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

        var isAuthorizedToCreateSegments = this.props.authorizedToCreateSegments === true
            || this.props.authorizedToCreateSegments === 'true'
            || this.props.authorizedToCreateSegments === 1
            || this.props.authorizedToCreateSegments === '1';
        var userSegmentAccess = isAuthorizedToCreateSegments ? "write" : "read";
        var segmentSelectorVueRoot = this.$element.find('[vue-entry="SegmentEditor.SegmentSelector"]').first();

        this.impl = new Segmentation({
            "target"   : this.$element.find(".segmentListContainer"),
            "editorTemplate": $('.SegmentEditor', self.$element),
            "segmentAccess" : userSegmentAccess,
            "availableSegments" : this.props.availableSegments,
            "addMethod": addSegment,
            "updateMethod": updateSegment,
            "deleteMethod": deleteSegment,
            "isUserAnonymous": segmentSelectorVueRoot.attr('is-user-anonymous') === 'true',
            "loginUrl": segmentSelectorVueRoot.attr('login-url') || '',
            "manageSegmentsUrl": segmentSelectorVueRoot.attr('manage-segments-url') || '',
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
                self.impl.closePanel();
            }
        };

        $('body').on('mouseup', this.onMouseUp);

        initTopControls();

        window.CoreHome.Matomo.postEvent('piwikSegmentationInited');
    };

    /**
     * Initialize the first element w/ the .segmentEditorPanel CSS class as SegmentSelectorControl,
     * if the element has not already been initialized.
     */
    SegmentSelectorControl.initElements = function () {
      // Enforce the page-level singleton contract for Segmentation.
      // This legacy bridge is being phased out in favor of shared Vue store state,
      // so we only allow one Segment Editor control instance per document.

      UIControl.initElements(this, '.segmentEditorPanel:first');
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
            if (this.impl && typeof this.impl.destroy === 'function') {
                this.impl.destroy();
            }
        }
    });

    exports.SegmentSelectorControl = SegmentSelectorControl;
});
