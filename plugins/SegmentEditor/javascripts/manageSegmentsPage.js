function initManageSegmentsPage() {
  const root = document.querySelector('[data-page="manage-segments"]');
  if (!root) {
    return;
  }
  const panelApi = window.matomoPluginSegmentEditor
    && window.matomoPluginSegmentEditor.panelAPI;
  if (!panelApi) {
    return;
  }
  const tbody = root.querySelector('tbody');
  const rowList = Array.from(tbody.children).reverse();
  const noResultElement = root.querySelector('.tableFooterLabel');
  let filterTimerId = null;
  init();

  function init() {
    reorderSegments();
    initListener();
    initHref();
    initTitles();
    panelApi.onSegmentsStarChange(onSegmentsStarChange);
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
    function delegate(eventName, selector, handler) {
      root.addEventListener(eventName, function (e) {
        const target = e.target.closest(selector);
        if (!target || !root.contains(target)) {
          return;
        }
        handler(e, target);
      });
    }

    delegate('click', '[data-edit-segment]', function (e, button) {
      e.stopPropagation();
      e.preventDefault();
      if (button.getAttribute('data-state') === 'disabled') {
        return;
      }
      const idSegment = button.getAttribute('data-edit-segment');
      panelApi.openEditFormGivenIdSegment(idSegment);
    });

    delegate('click', '[data-delete-segment]', function (e, button) {
      e.stopPropagation();
      e.preventDefault();
      if (button.getAttribute('data-state') === 'disabled') {
        return;
      }
      const idSegment = button.getAttribute('data-delete-segment');
      panelApi.openEditFormGivenIdSegment(idSegment);
      panelApi.askToDeleteSegment(idSegment);
    });

    delegate('click', '[data-star]', function (e, button) {
      e.stopPropagation();
      e.preventDefault();
      if (button.getAttribute('data-state') === 'disabled') {
        return;
      }
      const $segment = $(button).closest('tr');
      const idSegment = button.getAttribute('data-star');
      panelApi.toggleStarredSegment($segment, idSegment);
    });

    delegate('click', '.createNewSegment', function (e) {
      e.stopPropagation();
      e.preventDefault();
      panelApi.openEditFormGivenIdSegment();
    });

    delegate('input', '#manageSegmentSearch', function (e, searchInput) {
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
    });
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
    const search = panelApi.normalizeSearchString(keyword);
    rowList.forEach(function (row) {
      const segmentSeed = panelApi.normalizeSearchString($(row).attr('data-segment-name'));
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
