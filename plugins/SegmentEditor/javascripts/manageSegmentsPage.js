function initManageSegmentsPage() {
  const root = document.querySelector('[data-page="manage-segments"]');
  if (!root) {
    return;
  }
  if (typeof root.__segmentEditorManageSegmentsCleanup === 'function') {
    root.__segmentEditorManageSegmentsCleanup();
  }
  // The panel exposes its public API once the Vue selector has mounted, which
  // can happen after this inline script runs. Poll briefly until it appears
  // so the page can wire up its star/edit/delete listeners.
  const panelApi = window.matomoPluginSegmentEditor
    && window.matomoPluginSegmentEditor.panelAPI;
  if (!panelApi) {
    if (initManageSegmentsPage._retryCount === undefined) {
      initManageSegmentsPage._retryCount = 0;
    }
    if (initManageSegmentsPage._retryCount < 100) {
      initManageSegmentsPage._retryCount += 1;
      setTimeout(initManageSegmentsPage, 50);
    }
    return;
  }
  initManageSegmentsPage._retryCount = 0;
  const tbody = root.querySelector('tbody');
  const rowList = Array.from(tbody.children).reverse();
  const noResultElement = root.querySelector('.tableFooterLabel');
  let filterTimerId = null;
  let removeStarChangeListener = null;
  let removePageListeners = null;
  let isDestroyed = false;
  init();

  function init() {
    reorderSegments();
    removePageListeners = initListener();
    initHref();
    initTitles();
    loadSegmentDataSequentially();
    removeStarChangeListener = panelApi.onSegmentsStarChange(onSegmentsStarChange);
    root.__segmentEditorManageSegmentsCleanup = cleanup;
  }

  function cleanup() {
    isDestroyed = true;
    if (removeStarChangeListener) {
      removeStarChangeListener();
      removeStarChangeListener = null;
    }
    if (removePageListeners) {
      removePageListeners();
      removePageListeners = null;
    }
    delete root.__segmentEditorManageSegmentsCleanup;
  }

  function reorderSegments() {
    let currentOrder = -1;
    const lastRowByOrder = [];
    rowList.forEach(function (row) {
      const order = row.getAttribute('data-segment-order');
      if (currentOrder < order) {
        currentOrder = order;
      }
      const nextSibling = findNextSiblingByOrder(lastRowByOrder, order);
      if (nextSibling) {
        tbody.insertBefore(row, nextSibling);
      } else {
        tbody.appendChild(row);
      }
      lastRowByOrder[order] = row;
    });
  }

  function findNextSiblingByOrder(lastRowByOrder, order) {
    if (lastRowByOrder[order]) {
      return lastRowByOrder[order];
    }
    if (order <= 0) {
      return null;
    }
    return findNextSiblingByOrder(lastRowByOrder, order - 1);
  }

  function initHref() {
    rowList.forEach(function (row) {
      const definition = row.getAttribute('data-segment-definition');
      const $dashboardLink = $('.icon-show', row);
      const encodedDefinition = encodeURIComponent(definition || '');
      $dashboardLink.attr('href', window.broadcast.buildReportingUrl(`category=Dashboard_Dashboard&segment=${encodedDefinition}`));
    });
  }

  function initTitles() {
    rowList.forEach(function (row) {
      const $starButton = $('[data-star]', row);
      const $editButton = $('[data-edit-segment]', row);
      const $deleteButton = $('[data-delete-segment]', row);
      const idSegment = $starButton.attr('data-star');
      const segment = panelApi.getSegmentFromId(idSegment);
      if (segment && typeof segment.enable_only_idsite === 'string') {
        segment.enable_only_idsite = parseInt(segment.enable_only_idsite, 10) || 0;
      }
      const canEdit = panelApi.getCanUserEditSegment(segment);
      if (!canEdit) {
        $starButton.attr('data-state', 'disabled');
        $editButton.attr('data-state', 'disabled');
        $deleteButton.attr('data-state', 'disabled');
      }
      panelApi.updateStarSegmentTitle($starButton, segment);
      $editButton.attr('title', panelApi.getEditSegmentTitle(segment, canEdit));
      $deleteButton.attr('title', panelApi.getDeleteSegmentTitle(segment, canEdit));
    });
  }

  function initListener() {
    const removeDelegatedListeners = [];

    function delegate(eventName, selector, handler) {
      const listener = function (e) {
        const target = e.target.closest(selector);
        if (!target || !root.contains(target)) {
          return;
        }
        handler(e, target);
      };
      root.addEventListener(eventName, listener);
      return function unsubscribe() {
        root.removeEventListener(eventName, listener);
      };
    }

    removeDelegatedListeners.push(delegate('click', '[data-edit-segment]', function (e, button) {
      e.stopPropagation();
      e.preventDefault();
      if (button.getAttribute('data-state') === 'disabled') {
        return;
      }
      const idSegment = button.getAttribute('data-edit-segment');
      panelApi.openEditFormGivenIdSegment(idSegment);
    }));

    removeDelegatedListeners.push(delegate('click', '[data-delete-segment]', function (e, button) {
      e.stopPropagation();
      e.preventDefault();
      if (button.getAttribute('data-state') === 'disabled') {
        return;
      }
      const idSegment = button.getAttribute('data-delete-segment');
      panelApi.openEditFormGivenIdSegment(idSegment);
      panelApi.askToDeleteSegment(idSegment);
    }));

    removeDelegatedListeners.push(delegate('click', '[data-star]', function (e, button) {
      e.stopPropagation();
      e.preventDefault();
      if (button.getAttribute('data-state') === 'disabled') {
        return;
      }
      const $segment = $(button).closest('tr');
      const idSegment = button.getAttribute('data-star');
      panelApi.toggleStarredSegment($segment, idSegment);
    }));

    removeDelegatedListeners.push(delegate('click', '.createNewSegment', function (e) {
      e.stopPropagation();
      e.preventDefault();
      panelApi.openEditFormGivenIdSegment();
    }));

    removeDelegatedListeners.push(delegate('input', '#manageSegmentSearch', function (e, searchInput) {
      e.stopPropagation();
      e.preventDefault();
      if (filterTimerId) {
        clearTimeout(filterTimerId);
        filterTimerId = null;
      }
      const value = searchInput.value || '';
      if (value.length >= 2) {
        filterTimerId = setTimeout(function () {
          filterSegmentList(value);
        }, 500);
      } else {
        filterTimerId = setTimeout(clearFilterSegmentList, 500);
      }
    }));

    return function removeListeners() {
      removeDelegatedListeners.forEach(function (unsubscribe) {
        unsubscribe();
      });
    };
  }

  function loadSegmentDataSequentially() {
    const rowsToLoad = Array.from(tbody.querySelectorAll('tr[data-segment-name]')).filter(function (row) {
      return row.getAttribute('data-is-realtime') !== '1';
    });
    loadSegmentDataByIndex(rowsToLoad, 0);
  }

  function loadSegmentDataByIndex(rows, index) {
    if (isDestroyed || index >= rows.length) {
      return;
    }

    const row = rows[index];
    const segmentDefinition = row.getAttribute('data-segment-definition') || '';
    fetchSegmentData(segmentDefinition, function onSuccess(segmentData) {
      if (!isDestroyed) {
        applySegmentData(row, segmentData);
      }
      loadSegmentDataByIndex(rows, index + 1);
    }, function onError() {
      loadSegmentDataByIndex(rows, index + 1);
    });
  }

  function fetchSegmentData(segmentDefinition, onSuccess, onError) {
    const idSite = getReportingParam('idSite');
    const period = getReportingParam('period');
    const date = getReportingParam('date');
    const ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({
      module: 'API',
      method: 'SegmentEditor.getSegmentData',
      format: 'json',
      segment: segmentDefinition,
      idSite,
      period,
      date,
    }, 'GET');
    ajaxHandler.useCallbackInCaseOfError();
    ajaxHandler.setCallback(function (response) {
      if (!response || response.result === 'error') {
        if (typeof onError === 'function') {
          onError(response);
        }
        return;
      }

      if (typeof onSuccess === 'function') {
        onSuccess(response);
      }
    });
    ajaxHandler.send();
  }

  function getReportingParam(paramName) {
    return window.broadcast.getValueFromHash(paramName) || window.broadcast.getValueFromUrl(paramName);
  }

  function applySegmentData(row, data) {
    const $row = $(row);
    $row.find('[data-segment-data-field="nb_visits"]').text(data.nb_visits);
    $row.find('[data-segment-data-field="nb_actions"]').text(data.nb_actions);
    $row.find('[data-segment-data-field="evolution_visits"]').text(data.evolution_visits);

    const $evolution = $row.find('.sparklineEvolution');
    $evolution.removeClass('sparklineEvolution-positive sparklineEvolution-negative sparklineEvolution-stable');
    $evolution.addClass(`sparklineEvolution-${data.evolution_visits_direction}`);
    $evolution.find('.sparklineEvolution_icon').attr('src', data.evolution_visits_icon);
  }

  function getStarButtonFromSegmentId(segmentId) {
    return $(`[data-star="${segmentId}"]`, root);
  }

  function onSegmentsStarChange(segment, isError) {
    const $starButton = getStarButtonFromSegmentId(segment.idsegment);
    const $segment = $starButton.closest('tr');
    const $previousOrder = $segment.attr('data-segment-order');
    var tooltip = $(root).parents('.matomo-widget').tooltip('instance');
    if (tooltip) {
      tooltip.disable();
    }
    panelApi.updateStarSegmentTitle($starButton, segment);
    if (tooltip) {
      tooltip.enable();
    }
    $segment.attr('data-segment-order', $previousOrder === '2' ? 2 : segment.starred ? 1 : 0);
    reorderSegments();
    panelApi.triggerStarAnimation($segment, segment, isError);
  }

  function filterSegmentList(keyword) {
    clearFilterSegmentList();
    const search = piwikHelper.normalize(keyword || '');
    rowList.forEach(function (row) {
      const segmentSeed = piwikHelper.normalize($(row).attr('data-segment-name') || '');
      if (segmentSeed.indexOf(search) === -1) {
        $(row).hide();
      }
    });

    if ($(root).find("[data-segment-name]:visible").length === 0) {
      $(noResultElement).show();
    }
  }

  function clearFilterSegmentList() {
    rowList.forEach(function (row) {
      $(row).show();
    });
    $(noResultElement).hide();
  }
}
