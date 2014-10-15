/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, piwik) {

    var annotationsApi = {

        // calls Annotations.getAnnotationManager
        getAnnotationManager: function (idSite, date, period, lastN, callback) {
            var ajaxParams =
            {
                module: 'Annotations',
                action: 'getAnnotationManager',
                idSite: idSite,
                date: date,
                period: period
            };
            if (lastN) {
                ajaxParams.lastN = lastN;
            }

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams(ajaxParams, 'get');
            ajaxRequest.setCallback(callback);
            ajaxRequest.setFormat('html');
            ajaxRequest.send(false);
        },

        // calls Annotations.addAnnotation
        addAnnotation: function (idSite, managerDate, managerPeriod, date, note, callback) {
            var ajaxParams =
            {
                module: 'Annotations',
                action: 'addAnnotation',
                idSite: idSite,
                date: date,
                managerDate: managerDate,
                managerPeriod: managerPeriod,
                note: note
            };

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams(ajaxParams, 'get');
            ajaxRequest.setCallback(callback);
            ajaxRequest.setFormat('html');
            ajaxRequest.send(false);
        },

        // calls Annotations.saveAnnotation
        saveAnnotation: function (idSite, idNote, date, noteData, callback) {
            var ajaxParams =
            {
                module: 'Annotations',
                action: 'saveAnnotation',
                idSite: idSite,
                idNote: idNote,
                date: date
            };

            for (var key in noteData) {
                ajaxParams[key] = noteData[key];
            }

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams(ajaxParams, 'get');
            ajaxRequest.setCallback(callback);
            ajaxRequest.setFormat('html');
            ajaxRequest.send(false);
        },

        // calls Annotations.deleteAnnotation
        deleteAnnotation: function (idSite, idNote, managerDate, managerPeriod, callback) {
            var ajaxParams =
            {
                module: 'Annotations',
                action: 'deleteAnnotation',
                idSite: idSite,
                idNote: idNote,
                date: managerDate,
                period: managerPeriod
            };

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams(ajaxParams, 'get');
            ajaxRequest.setCallback(callback);
            ajaxRequest.setFormat('html');
            ajaxRequest.send(false);
        },

        // calls Annotations.getEvolutionIcons
        getEvolutionIcons: function (idSite, date, period, lastN, callback) {
            var ajaxParams =
            {
                module: 'Annotations',
                action: 'getEvolutionIcons',
                idSite: idSite,
                date: date,
                period: period
            };
            if (lastN) {
                ajaxParams.lastN = lastN;
            }

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams(ajaxParams, 'get');
            ajaxRequest.setFormat('html');
            ajaxRequest.setCallback(callback);
            ajaxRequest.send(false);
        }
    };

    var today = new Date();

    /**
     * Returns options to configure an annotation's datepicker shown in edit mode.
     *
     * @param {Element} annotation The annotation element.
     */
    var getDatePickerOptions = function (annotation) {
        var annotationDateStr = annotation.attr('data-date'),
            parts = annotationDateStr.split('-'),
            annotationDate = new Date(parts[0], parts[1] - 1, parts[2]);

        var result = piwik.getBaseDatePickerOptions(annotationDate);
        result.showButtonPanel = true;
        result.currentText = _pk_translate('General_Today');

        // make sure days before site start & after today cannot be selected
        var piwikMinDate = result.minDate;
        result.beforeShowDay = function (date) {
            var valid = true;

            // if date is after today or before date of site creation, it cannot be selected
            if (date > today
                || date < piwikMinDate) {
                valid = false;
            }

            return [valid, ''];
        };

        // on select a date, change the text of the edit date link
        result.onSelect = function (dateText) {
            $('.annotation-period-edit>a', annotation).text(dateText);
            $('.datepicker', annotation).hide();
        };

        return result;
    };

    /**
     * Switches the current mode of an annotation between the view/edit modes.
     *
     * @param {Element} inAnnotationElement An element within the annotation to toggle the mode of.
     *                                      Should be two levels nested in the .annotation-value
     *                                      element.
     * @return {Element} The .annotation-value element.
     */
    var toggleAnnotationMode = function (inAnnotationElement) {
        var annotation = $(inAnnotationElement).closest('.annotation');
        annotation.toggleClass('edit')
        $('.annotation-period,.annotation-period-edit,.delete-annotation,' +
            '.annotation-edit-mode,.annotation-view-mode', annotation).toggle();

        return $(inAnnotationElement).find('.annotation-value');
    };

    /**
     * Creates the datepicker for an annotation element.
     *
     * @param {Element} annotation The annotation element.
     */
    var createDatePicker = function (annotation) {
        $('.datepicker', annotation).datepicker(getDatePickerOptions(annotation)).hide();
    };

    /**
     * Creates datepickers for every period edit in an annotation manager.
     *
     * @param {Element} manager The annotation manager element.
     */
    var createDatePickers = function (manager) {
        $('.annotation-period-edit', manager).each(function () {
            createDatePicker($(this).parent().parent());
        });
    };

    /**
     * Replaces the HTML of an annotation manager element, and resets date/period
     * attributes.
     *
     * @param {Element} manager The annotation manager.
     * @param {string} html The HTML of the new annotation manager.
     */
    var replaceAnnotationManager = function (manager, html) {
        var newManager = $(html);
        manager.html(newManager.html())
            .attr('data-date', newManager.attr('data-date'))
            .attr('data-period', newManager.attr('data-period'));
        createDatePickers(manager);
    };

    /**
     * Returns true if an annotation element is starred, false if otherwise.
     *
     * @param {Element} annotation The annotation element.
     * @return {boolean}
     */
    var isAnnotationStarred = function (annotation) {
        return !!(+$('.annotation-star', annotation).attr('data-starred') == 1);
    };

    /**
     * Replaces the HTML of an annotation element with HTML returned from Piwik, and
     * makes sure the data attributes are correct.
     *
     * @param {Element} annotation The annotation element.
     * @param {string} html The replacement HTML (or alternatively, the replacement
     *                      element/jQuery object).
     */
    var replaceAnnotationHtml = function (annotation, html) {
        var newHtml = $(html);
        annotation.html(newHtml.html()).attr('data-date', newHtml.attr('data-date'));
        createDatePicker(annotation);
    };

    /**
     * Binds events to an annotation manager element.
     *
     * @param {Element} manager The annotation manager.
     * @param {int} idSite The site ID the manager is showing annotations for.
     * @param {function} onAnnotationCountChange Callback that is called when there is a change
     *                                           in the number of annotations and/or starred annotations,
     *                                           eg, when a user adds a new one or deletes an existing one.
     */
    var bindAnnotationManagerEvents = function (manager, idSite, onAnnotationCountChange) {
        if (!onAnnotationCountChange) {
            onAnnotationCountChange = function () {};
        }

        // show new annotation row if create new annotation link is clicked
        manager.on('click', '.add-annotation', function (e) {
            e.preventDefault();

            var $newRow = $('.new-annotation-row', manager);
            $newRow.show();
            $(this).hide();

            return false;
        });

        // hide new annotation row if cancel button clicked
        manager.on('click', '.new-annotation-cancel', function () {
            var newAnnotationRow = $(this).parent().parent();
            newAnnotationRow.hide();

            $('.add-annotation', newAnnotationRow.closest('.annotation-manager')).show();
        });

        // save new annotation when new annotation row save is clicked
        manager.on('click', '.new-annotation-save', function () {
            var addRow = $(this).parent().parent(),
                addNoteInput = addRow.find('.new-annotation-edit'),
                noteDate = addRow.find('.annotation-period-edit>a').text();

            // do nothing if input is empty
            if (!addNoteInput.val()) {
                return;
            }

            // disable input & link
            addNoteInput.attr('disabled', 'disabled');
            $(this).attr('disabled', 'disabled');

            // add a new annotation for the site, date & period
            annotationsApi.addAnnotation(
                idSite,
                manager.attr('data-date'),
                manager.attr('data-period'),
                noteDate,
                addNoteInput.val(),
                function (response) {
                    replaceAnnotationManager(manager, response);

                    // increment annotation count for this date
                    onAnnotationCountChange(noteDate, 1, 0);
                }
            );
        });

        // add new annotation when enter key pressed on new annotation input
        manager.on('keypress', '.new-annotation-edit', function (e) {
            if (e.which == 13) {
                $(this).parent().find('.new-annotation-save').click();
            }
        });

        // show annotation editor if edit link, annotation text or period text is clicked
        manager.on('click', '.annotation-enter-edit-mode', function (e) {
            e.preventDefault();

            var annotationContent = toggleAnnotationMode(this);
            annotationContent.find('.annotation-edit').focus();

            return false;
        });

        // hide annotation editor if cancel button is clicked
        manager.on('click', '.annotation-cancel', function () {
            toggleAnnotationMode(this);
        });

        // save annotation if save button clicked
        manager.on('click', '.annotation-edit-mode .annotation-save', function () {
            var annotation = $(this).parent().parent().parent(),
                input = $('.annotation-edit', annotation),
                dateEditText = $('.annotation-period-edit>a', annotation).text();

            // if annotation value/date has not changed, just show the view mode instead of edit
            if (input[0].defaultValue == input.val()
                && dateEditText == annotation.attr('data-date')) {
                toggleAnnotationMode(this);
                return;
            }

            // disable input while ajax is happening
            input.attr('disabled', 'disabled');
            $(this).attr('disabled', 'disabled');

            // save the note w/ the new note text & date
            annotationsApi.saveAnnotation(
                idSite,
                annotation.attr('data-id'),
                dateEditText,
                {
                    note: input.val()
                },
                function (response) {
                    response = $(response);

                    var newDate = response.attr('data-date'),
                        isStarred = isAnnotationStarred(response),
                        originalDate = annotation.attr('data-date');

                    replaceAnnotationHtml(annotation, response);

                    // if the date has been changed, update the evolution icon counts to reflect the change
                    if (originalDate != newDate) {
                        // reduce count for original date
                        onAnnotationCountChange(originalDate, -1, isStarred ? -1 : 0);

                        // increase count for new date
                        onAnnotationCountChange(newDate, 1, isStarred ? 1 : 0);
                    }
                }
            );
        });

        // save annotation if 'enter' pressed on input
        manager.on('keypress', '.annotation-value input', function (e) {
            if (e.which == 13) {
                $(this).parent().find('.annotation-save').click();
            }
        });

        // delete annotation if delete link clicked
        manager.on('click', '.delete-annotation', function (e) {
            e.preventDefault();

            var annotation = $(this).parent().parent();
            $(this).attr('disabled', 'disabled');

            // delete annotation by ajax
            annotationsApi.deleteAnnotation(
                idSite,
                annotation.attr('data-id'),
                manager.attr('data-date'),
                manager.attr('data-period'),
                function (response) {
                    replaceAnnotationManager(manager, response);

                    // update evolution icons
                    var isStarred = isAnnotationStarred(annotation);
                    onAnnotationCountChange(annotation.attr('data-date'), -1, isStarred ? -1 : 0);
                }
            );

            return false;
        });

        // star/unstar annotation if star clicked
        manager.on('click', '.annotation-star-changeable', function (e) {
            var annotation = $(this).parent().parent(),
                newStarredVal = $(this).attr('data-starred') == 0 ? 1 : 0 // flip existing 'starred' value
                ;

            // perform ajax request to star annotation
            annotationsApi.saveAnnotation(
                idSite,
                annotation.attr('data-id'),
                annotation.attr('data-date'),
                {
                    starred: newStarredVal
                },
                function (response) {
                    replaceAnnotationHtml(annotation, response);

                    // change starred count for this annotation in evolution graph based on what we're
                    // changing the starred value to
                    onAnnotationCountChange(annotation.attr('data-date'), 0, newStarredVal == 0 ? -1 : 1);
                }
            );
        });

        // when period edit is clicked, show datepicker
        manager.on('click', '.annotation-period-edit>a', function (e) {
            e.preventDefault();
            $('.datepicker', $(this).parent()).toggle();
            return false;
        });

        // make sure datepicker popups are closed if someone clicks elsewhere
        $('body').on('mouseup', function (e) {
            var container = $('.annotation-period-edit>.datepicker:visible').parent();

            if (!container.has(e.target).length) {
                container.find('.datepicker').hide();
            }
        });
    };

// used in below function
    var loadingAnnotationManager = false;

    /**
     * Shows an annotation manager under a report for a specific site & date range.
     *
     * @param {Element} domElem The element of the report to show the annotation manger
     *                          under.
     * @param {int} idSite The ID of the site to show the annotations of.
     * @param {string} date The start date of the period.
     * @param {string} period The period type.
     * @param {int} lastN Whether to include the last N periods in the date range or not. Can
     *              be undefined.
     * @param {function} [callback]
     */
    var showAnnotationViewer = function (domElem, idSite, date, period, lastN, callback) {
        var addToAnnotationCount = function (date, amt, starAmt) {
            if (date.indexOf(',') != -1) {
                date = date.split(',')[0];
            }

            $('.evolution-annotations>span', domElem).each(function () {
                if ($(this).attr('data-date') == date) {
                    // get counts from attributes (and convert them to ints)
                    var starredCount = +$(this).attr('data-starred'),
                        annotationCount = +$(this).attr('data-count');

                    // modify the starred count & make sure the correct image is used
                    var newStarCount = starredCount + starAmt;
                    if (newStarCount > 0) {
                        var newImg = 'plugins/Morpheus/images/annotations_starred.png';
                    } else {
                        var newImg = 'plugins/Morpheus/images/annotations.png';
                    }
                    $(this).attr('data-starred', newStarCount).find('img').attr('src', newImg);

                    // modify the annotation count & hide/show based on new count
                    var newCount = annotationCount + amt;
                    $(this).attr('data-count', newCount).css('opacity', newCount > 0 ? 1 : 0);

                    return false;
                }
            });
        };

        var manager = $('.annotation-manager', domElem);
        if (manager.length) {
            // if annotations for the requested date + period are already loaded, then just toggle the
            // visibility of the annotation viewer. otherwise, we reload the annotations.
            if (manager.attr('data-date') == date
                && manager.attr('data-period') == period) {
                // toggle manager view
                if (manager.is(':hidden')) {
                    manager.slideDown('slow', function () { if (callback) callback(manager) });
                }
                else {
                    manager.slideUp('slow', function () { if (callback) callback(manager) });
                }
            }
            else {
                // show nothing but the loading gif
                $('.annotations', manager).html('');
                $('.loadingPiwik', manager).show();

                // reload annotation manager for new date/period
                annotationsApi.getAnnotationManager(idSite, date, period, lastN, function (response) {
                    replaceAnnotationManager(manager, response);

                    createDatePickers(manager);

                    // show if hidden
                    if (manager.is(':hidden')) {
                        manager.slideDown('slow', function () { if (callback) callback(manager) });
                    }
                    else {
                        if (callback) {
                            callback(manager);
                        }
                    }
                });
            }
        }
        else {
            // if we are already loading the annotation manager, don't load it again
            if (loadingAnnotationManager) {
                return;
            }

            loadingAnnotationManager = true;

            $('.loadingPiwikBelow', domElem).insertAfter($('.evolution-annotations', domElem));

            var loading = $('.loadingPiwikBelow', domElem).css({display: 'block'});

            // the annotations for this report have not been retrieved yet, so do an ajax request
            // & show the result
            annotationsApi.getAnnotationManager(idSite, date, period, lastN, function (response) {
                var manager = $(response).hide();

                // if an error occurred (and response does not contain the annotation manager), do nothing
                if (!manager.hasClass('annotation-manager')) {
                    return;
                }

                // create datepickers for each shown annotation
                createDatePickers(manager);

                bindAnnotationManagerEvents(manager, idSite, addToAnnotationCount);

                loading.css('visibility', 'hidden');

                // add & show annotation manager
                manager.insertAfter($('.evolution-annotations', domElem));

                manager.slideDown('slow', function () {
                    loading.hide().css('visibility', 'visible');
                    loadingAnnotationManager = false;

                    if (callback) callback(manager)
                });
            });
        }
    };

    /**
     * Determines the x-coordinates of a set of evolution annotation icons.
     *
     * @param {Element} annotations The '.evolution-annotations' element.
     * @param {Element} graphElem The evolution graph's datatable element.
     */
    var placeEvolutionIcons = function (annotations, graphElem) {
        var canvases = $('.piwik-graph .jqplot-xaxis canvas', graphElem),
            noteSize = 16;

        // if no graph available, hide all icons
        if (!canvases || canvases.length == 0) {
            $('span', annotations).hide();
            return true;
        }

        // set position of each individual icon
        $('span', annotations).each(function (i) {
            var canvas = $(canvases[i]),
                canvasCenterX = canvas.position().left + (canvas.width() / 2);
            $(this).css({
                left: canvasCenterX - noteSize / 2,
                // show if there are annotations for this x-axis tick
                opacity: +$(this).attr('data-count') > 0 ? 1 : 0
            });
        });
    };

// make showAnnotationViewer, placeEvolutionIcons & annotationsApi globally accessible
    piwik.annotations = {
        showAnnotationViewer: showAnnotationViewer,
        placeEvolutionIcons: placeEvolutionIcons,
        api: annotationsApi
    };

}(jQuery, piwik));
