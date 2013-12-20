;(function($) {
    /*
    * ui.dropdownchecklist
    *
    * Copyright (c) 2008-2009 Adrian Tosca
    * Dual licensed under the MIT (MIT-LICENSE.txt)
    * and GPL (GPL-LICENSE.txt) licenses.
    *
    */
    // The dropdown check list jQuery plugin transforms a regular select html element into a dropdown check list.
    $.widget("ui.dropdownchecklist", {
        // Creates the drop container that keeps the items and appends it to the document
        _appendDropContainer: function() {
            var wrapper = $("<div/>");
            // the container is wrapped in a div
            wrapper.addClass("ui-dropdownchecklist-dropcontainer-wrapper");
            // initially hidden
            wrapper.css({
                position: 'absolute',
                left: "-33000",
                top: "-33000px",
                width: '3000px',
                height: '30000px'
            });
            var container = $("<div/>"); // the actual container
            container.addClass("ui-dropdownchecklist-dropcontainer")
            .css("overflow-y", "auto");
            wrapper.append(container);
            $(document.body).append(wrapper);
            //wrapper.insertAfter(this.sourceSelect);
            // flag that tells if the drop container is shown or not
            wrapper.drop = false;
            return wrapper;
        },
        _isDropDownKeyShortcut: function(e) {
            return e.altKey && ($.ui.keyCode.DOWN == (e.keyCode || e.which));// Alt + Down Arrow
        },
        _isDroDownCloseKey: function(e) {
            return $.ui.keyCode.ESCAPE == (e.keyCode || e.which);
        },
        _handleKeyboard: function(e) {
            var self = this;
            if (self._isDropDownKeyShortcut(e)) {
                e.stopPropagation();
                self._toggleDropContainer();
                self.dropWrapper.find("input:first").focus();
            } else if (self.dropWrapper.drop && self._isDroDownCloseKey(e)) {
                self._toggleDropContainer();
            }
        },
        // Creates the control that will replace the source select and appends it to the document
        // The control resembles a regular select with single selection
        _appendControl: function() {
            var self = this, sourceSelect = this.sourceSelect;

            // the controls is wrapped in a span with inline-block display
            var wrapper = $("<span/>");
            wrapper.addClass("ui-dropdownchecklist-wrapper");
            wrapper.css({
                display: "inline-block",
                cursor: "default"
            });

            // the actual control, can be styled to set the border and drop right image
            var control = $("<span/>");
            control.addClass("ui-dropdownchecklist");
            control.css({
                display: "inline-block"
            });
            control.attr("tabIndex", 0);
            control.keyup(function(e) {
                self._handleKeyboard(e)
                });
            wrapper.append(control);

            // the text container keeps the control text that is build from the selected (checked) items
            var textContainer = $("<span/>");
            textContainer.addClass("ui-dropdownchecklist-text")
            textContainer.css({
                display: "inline-block",
                overflow: "hidden"
            });
            control.append(textContainer);

            // add the hover styles to the control
            wrapper.hover(function() {
                if (!self.disabled) {
                    control.toggleClass("ui-dropdownchecklist-hover")
                }
            }, function() {
                if (!self.disabled) {
                    control.toggleClass("ui-dropdownchecklist-hover")
                }
            });

            // clicking on the control toggles the drop container
            wrapper.click(function(event) {
                if (!self.disabled) {
                    event.stopPropagation();
                    self._toggleDropContainer();
                }
            })

            wrapper.insertAfter(sourceSelect);

            $(window).resize(function() {
                if (!self.disabled && self.dropWrapper.drop) {
                    self._toggleDropContainer();
                }
            })
            
            return wrapper;
        },
        // Creates a drop item that coresponds to an option element in the source select
        _createDropItem: function(index, value, text, checked, disabled, indent) {
            var self = this;
            // the item contains a div that contains a checkbox input and a span for text
            // the div
            var item = $("<div/>");
            item.addClass("ui-dropdownchecklist-item");
            item.css({
                whiteSpace: "nowrap"
            });
            var checkedString = checked ? ' checked="checked"' : '';
            var disabledString = disabled ? ' disabled="disabled"' : '';
            var idBase = (self.sourceSelect.attr("id") || "ddcl");
            var id = idBase + index;
            var checkBox;
            if (self.initialMultiple) { // the checkbox
                checkBox = $('<input type="checkbox" id="' + id + '"' + checkedString + disabledString + '/>');
            } else { // the radiobutton
                checkBox = $('<input type="radio" id="' + id + '" name="' + idBase + '"' + checkedString + disabledString + '/>');
            }
            checkBox = checkBox.attr("index", index).val(value);
            item.append(checkBox);
            // the text
            var label = $("<label for=" + id + "/>");
            label.addClass("ui-dropdownchecklist-text")
            .css({
                cursor: "default",
                width: "100%"
            })
            .text(text);
            if (indent) {
                item.addClass("ui-dropdownchecklist-indent");
            }
            if (disabled) {
                item.addClass("ui-dropdownchecklist-item-disabled");
            }
            item.append(label);
            item.hover(function() {
                item.addClass("ui-dropdownchecklist-item-hover")
            }, function() {
                item.removeClass("ui-dropdownchecklist-item-hover")
            });
            // clicking on the checkbox synchronizes the source select
            checkBox.click(function(e) {
                e.stopPropagation();
                if (!disabled) {
                    self._syncSelected($(this));
                    self.sourceSelect.trigger("change", 'ddcl_internal');
                }
            });
            // check/uncheck the item on clicks on the entire item div
            var checkItem = function(e) {
                e.stopPropagation();
                if (!disabled) {
                    var checked = checkBox.attr("checked");
                    checkBox.attr("checked", !checked)
                    self._syncSelected(checkBox);
                    self.sourceSelect.trigger("change", 'ddcl_internal');
                }
            }
            label.click(function(e) {
                e.stopPropagation()
                });
            item.click(checkItem);
            item.keyup(function(e) {
                self._handleKeyboard(e)
                });
            return item;
        },
        _createGroupItem: function(text) {
            var group = $("<div />")
            group.addClass("ui-dropdownchecklist-group");
            group.css({
                whiteSpace: "nowrap"
            });
            var label = $("<span/>");
            label.addClass("ui-dropdownchecklist-text")
            .css({
                cursor: "default",
                width: "100%"
            })
            .text(text);
            group.append(label);
            return group;
        },
        // Creates the drop items and appends them to the drop container
        // Also calculates the size needed by the drop container and returns it
        _appendItems: function() {
            var self = this, sourceSelect = this.sourceSelect, dropWrapper = this.dropWrapper;
            var dropContainerDiv = dropWrapper.find(".ui-dropdownchecklist-dropcontainer");
            dropContainerDiv.css({
                "float": "left"
            }); // to allow getting the actual width of the container
            sourceSelect.children().each(function(index) { // when the select has groups
                var opt = $(this);
                if (opt.is("option")) {
                    self._appendOption(opt, dropContainerDiv, index, false);
                } else {
                    var text = opt.attr("label");
                    var group = self._createGroupItem(text);
                    dropContainerDiv.append(group);
                    self._appendOptions(opt, dropContainerDiv, index, true);
                }
            });
            //self._appendOptions(sourceSelect, dropContainerDiv, false); // when no groups
            var divWidth = dropContainerDiv.outerWidth();
            var divHeight = dropContainerDiv.outerHeight();
            dropContainerDiv.css({
                "float": ""
            }); // set it back
            return {
                width: divWidth,
                height: divHeight
            };
        },
        _appendOptions: function(parent, container, parentIndex, indent) {
            var self = this;
            parent.children("option").each(function(index) {
                var option = $(this);
                var childIndex = (parentIndex + "." + index);
                self._appendOption(option, container, childIndex, indent);
            })
        },
        _appendOption: function(option, container, index, indent) {
            var self = this;
            var text = option.text();
            var value = option.val();
            var selected = option.attr("selected");
            var disabled = option.attr("disabled");
            var item = self._createDropItem(index, value, text, selected, disabled, indent);
            container.append(item);
        },
        // Synchronizes the items checked and the source select
        // When firstItemChecksAll option is active also synchronizes the checked items
        // senderCheckbox parameters is the checkbox input that generated the synchronization
        _syncSelected: function(senderCheckbox) {
            var self = this, options = this.options, sourceSelect = this.sourceSelect, dropWrapper = this.dropWrapper;
            var allEnabledCheckboxes = dropWrapper.find("input:not([disabled])");
            if (options.firstItemChecksAll) {
                // if firstItemChecksAll is true, check all checkboxes if the first one is checked
                if (senderCheckbox.attr("index") == 0) {
                    allEnabledCheckboxes.attr("checked", senderCheckbox.attr("checked"));
                } else {
                    // check the first checkbox if all the other checkboxes are checked
                    var allChecked;
                    allChecked = true;
                    allEnabledCheckboxes.each(function(index) {
                        if (index > 0) {
                            var checked = $(this).attr("checked");
                            if (!checked) allChecked = false;
                        }
                    });
                    var firstCheckbox = allEnabledCheckboxes.filter(":first");
                    firstCheckbox.attr("checked", false);
                    if (allChecked) {
                        firstCheckbox.attr("checked", true);
                    }
                }
            }

            var allCheckboxes = dropWrapper.find("input");
            // do the actual synch with the source select
            var selectOptions = sourceSelect.get(0).options;
            allCheckboxes.each(function(index) {
                $(selectOptions[index]).attr("selected", $(this).attr("checked"));
            });

            // update the text shown in the control
            self._updateControlText();
        },
        _sourceSelectChangeHandler: function(event) {
            var self = this, dropWrapper = this.dropWrapper;
            dropWrapper.find("input").val(self.sourceSelect.val());

            // update the text shown in the control
            self._updateControlText();
        },
        // Updates the text shown in the control depending on the checked (selected) items
        _updateControlText: function() {
            var self = this, sourceSelect = this.sourceSelect, options = this.options, controlWrapper = this.controlWrapper;
            var firstSelect = sourceSelect.find("option:first");
            var allSelected = null != firstSelect && firstSelect.attr("selected");
            var selectOptions = sourceSelect.find("option");
            var text = self._formatText(selectOptions, options.firstItemChecksAll, allSelected);
            var controlLabel = controlWrapper.find(".ui-dropdownchecklist-text");
            controlLabel.text(text);
            controlLabel.attr("title", text);
        },
        // Formats the text that is shown in the control
        _formatText: function(selectOptions, firstItemChecksAll, allSelected) {
            var text;
            if (null != this.options.textFormatFunction) {
                return this.options.textFormatFunction(selectOptions);
            } else if (firstItemChecksAll && allSelected) {
                // just set the text from the first item
                text = selectOptions.filter(":first").text();
            } else {
                // concatenate the text from the checked items
                text = "";
                selectOptions.each(function() {
                    if ($(this).attr("selected")) {
                        text += $(this).text() + ", ";
                    }
                });
                if (text.length > 0) {
                    text = text.substring(0, text.length - 2);
                }
            }
            if (text == "") text = this.options.emptyText;
            return text;
        },
        // Shows and hides the drop container
        _toggleDropContainer: function() {
            var self = this, dropWrapper = this.dropWrapper, controlWrapper = this.controlWrapper;
            // hides the last shown drop container
            var hide = function() {
                var instance = $.ui.dropdownchecklist.drop;
                if (null != instance) {
                    instance.dropWrapper.css({
                        top: "-33000px",
                        left: "-33000px"
                    });
                    instance.controlWrapper.find(".ui-dropdownchecklist").toggleClass("ui-dropdownchecklist-active");
                    instance.dropWrapper.find("input").attr("tabIndex", -1);
                    instance.dropWrapper.drop = false;
                    $.ui.dropdownchecklist.drop = null;
                    $(document).unbind("click", hide);
                    self.sourceSelect.trigger("blur");
                }
            }
            // shows the given drop container instance
            var show = function(instance) {
                if (null != $.ui.dropdownchecklist.drop) {
                    hide();
                }
                instance.dropWrapper.css({
                    top: instance.controlWrapper.offset().top + instance.controlWrapper.outerHeight() + "px",
                    left: instance.controlWrapper.offset().left + "px"
                })
                var ancestorsZIndexes = controlWrapper.parents().map(
                    function() {
                        var zIndex = $(this).css("z-index");
                        return isNaN(zIndex) ? 0 : zIndex
                        }
                    ).get();
                var parentZIndex = Math.max.apply(Math, ancestorsZIndexes);
                if (parentZIndex > 0) {
                    instance.dropWrapper.css({
                        zIndex: (parentZIndex+1)
                    })
                }
                instance.controlWrapper.find(".ui-dropdownchecklist").toggleClass("ui-dropdownchecklist-active");
                instance.dropWrapper.find("input").attr("tabIndex", 0);
                instance.dropWrapper.drop = true;
                $.ui.dropdownchecklist.drop = instance;
                $(document).bind("click", hide);
                self.sourceSelect.trigger("focus");
            }
            if (dropWrapper.drop) {
                hide(self);
            } else {
                show(self);
            }
        },
        // Set the size of the control and of the drop container
        _setSize: function(dropCalculatedSize) {
            var options = this.options, dropWrapper = this.dropWrapper, controlWrapper = this.controlWrapper;

            var controlWidth;
            // use the width from options if set, otherwise set the same width as the drop container
            if (options.width) {
                controlWidth = parseInt(options.width);
            } else {
                controlWidth = dropCalculatedSize.width;
                var minWidth = options.minWidth;
                // if the width is to small (usually when there are no items) set a minimum width
                if (controlWidth < minWidth) {
                    controlWidth = minWidth;
                }
            }
            controlWrapper.find(".ui-dropdownchecklist-text").css({
                width: controlWidth + "px"
            });

            // for the drop container get the actual (outer) width of the control.
            // this can be different than the set one depening on paddings, borders etc set on the control
            var controlOuterWidth = controlWrapper.outerWidth();

            // the drop container height can be set from options
            var dropHeight = options.maxDropHeight ? parseInt(options.maxDropHeight) : dropCalculatedSize.height;
            // ensure the drop container is not less than the control width (would be ugly)
            var dropWidth = dropCalculatedSize.width < controlOuterWidth ? controlOuterWidth : dropCalculatedSize.width;

            $(dropWrapper).css({
                width: dropWidth + "px",
                height: dropHeight + "px"
            });

            dropWrapper.find(".ui-dropdownchecklist-dropcontainer").css({
                height: dropHeight + "px"
            });
        },
        // Initializes the plugin
        _init: function() {
            var self = this, options = this.options;

            // sourceSelect is the select on which the plugin is applied
            var sourceSelect = self.element;
            self.initialDisplay = sourceSelect.css("display");
            sourceSelect.css("display", "none");
            self.initialMultiple = sourceSelect.attr("multiple");
            sourceSelect.attr("multiple", "multiple");
            self.sourceSelect = sourceSelect;

            // create the drop container where the items are shown
            var dropWrapper = self._appendDropContainer();
            self.dropWrapper = dropWrapper;

            // append the items from the source select element
            var dropCalculatedSize = self._appendItems();

            // append the control that resembles a single selection select
            var controlWrapper = self._appendControl();
            self.controlWrapper = controlWrapper;

            // updates the text shown in the control
            self._updateControlText(controlWrapper, dropWrapper, sourceSelect);

            // set the sizes of control and drop container
            self._setSize(dropCalculatedSize);

            // BGIFrame for IE6
            if (options.bgiframe && typeof self.dropWrapper.bgiframe == "function") {
                self.dropWrapper.bgiframe();
            }

            // listen for change events on the source select element
            // ensure we avoid processing internally triggered changes
            self.sourceSelect.change(function(event, eventName) {
                if (eventName != 'ddcl_internal') {
                    self._sourceSelectChangeHandler(event);
                }
            });
        },
        enable: function() {
            this.controlWrapper.find(".ui-dropdownchecklist").removeClass("ui-dropdownchecklist-disabled");
            this.disabled = false;
        },
        disable: function() {
            this.controlWrapper.find(".ui-dropdownchecklist").addClass("ui-dropdownchecklist-disabled");
            this.disabled = true;
        },
        destroy: function() {
            $.widget.prototype.destroy.apply(this, arguments);
            this.sourceSelect.css("display", this.initialDisplay);
            this.sourceSelect.attr("multiple", this.initialMultiple);
            this.controlWrapper.unbind().remove();
            this.dropWrapper.remove();
        }
    });

    $.extend($.ui.dropdownchecklist, {
        defaults: {
            width: null,
            maxDropHeight: null,
            firstItemChecksAll: false,
            minWidth: 50,
            bgiframe: false,
            emptyText: "",
            textFormatFunction: null
        }
    });

})(jQuery);