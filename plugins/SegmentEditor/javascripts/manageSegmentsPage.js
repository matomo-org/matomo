function initManageSegmentsPage() {
  const root = document.querySelector('[data-page="manage-segments"]');
  if (!root) {
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
    initTitles();
    window.SegmentEditorPanel.onSegmentsStarChange(onSegmentsStarChange);
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

  function initTitles() {
    rowList.forEach(function (row) {
      const $starButton = $('[data-star]', row);
      const $editButton = $('[data-edit-segment]', row);
      const $deleteButton = $('[data-delete-segment]', row);
      const idSegment = $starButton.attr('data-star');
      const segment = window.SegmentEditorPanel.getSegmentFromId(idSegment);
      const canEdit = window.SegmentEditorPanel.getCanUserEditSegment(segment);
      if (!canEdit) {
        $starButton.attr('data-state', 'disabled');
        $editButton.attr('data-state', 'disabled');
        $deleteButton.attr('data-state', 'disabled');
      }
      window.SegmentEditorPanel.updateStarSegmentTitle($starButton, segment);
      $editButton.attr('title', window.SegmentEditorPanel.getEditSegmentTitle(segment, canEdit));
      $deleteButton.attr('title', window.SegmentEditorPanel.getDeleteSegmentTitle(segment, canEdit));
    });
  }

  function initListener() {
    $(root).on('click', '[data-edit-segment]', function (e) {
      e.stopPropagation();
      e.preventDefault();
      const $button = $(this);
      if ($button.attr('data-state') === 'disabled') {
        return false;
      }
      const idSegment = $button.attr('data-edit-segment');
      SegmentEditorPanel.openEditFormGivenIdSegment(idSegment);
    });
    $(root).on('click', '[data-delete-segment]', function (e) {
      e.stopPropagation();
      e.preventDefault();
      const $button = $(this);
      if ($button.attr('data-state') === 'disabled') {
        return false;
      }
      const idSegment = $button.attr('data-delete-segment');
      SegmentEditorPanel.openEditFormGivenIdSegment(idSegment);
      SegmentEditorPanel.askToDeleteSegment(idSegment);
    });
    $(root).on('click', '[data-star]', function (e) {
      e.stopPropagation();
      e.preventDefault();
      const $button = $(this);
      if ($button.attr('data-state') === 'disabled') {
        return false;
      }
      const $segment = $button.closest('tr');
      const idSegment = $button.attr('data-star');
      window.SegmentEditorPanel.toggleStarredSegment($segment, idSegment);
    });
    $(root).on('input', '#manageSegmentSearch', function (e) {
      e.stopPropagation();
      e.preventDefault();

      if (filterTimerId) {
        clearTimeout(filterTimerId);
        filterTimerId = null;
      }
      const value = $(this).val();
      if (value.length >= 2) {
        filterTimerId = setTimeout(function () {
          filterSegmentList(value);
        }, 500);
      } else {
        filterTimerId = setTimeout(clearFilterSegmentList, 500);
      }
    });
    $(root).on('click', '.createNewSegment', function (e) {
      e.stopPropagation();
      e.preventDefault();
      window.SegmentEditorPanel.openEditFormGivenIdSegment();
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
    window.SegmentEditorPanel.updateStarSegmentTitle($starButton, segment);
    if (tooltip) {
      tooltip.enable();
    }
    $segment.attr('data-segment-order', $previousOrder === '2' ? 2 : segment.starred ? 1 : 0);
    reorderSegments();
    window.SegmentEditorPanel.triggerStarAnimation($segment, segment, isError);
  }

  function filterSegmentList(keyword) {
    clearFilterSegmentList();
    const search = window.SegmentEditorPanel.normalizeSearchString(keyword);
    rowList.forEach(function (row) {
      const segmentSeed = window.SegmentEditorPanel.normalizeSearchString($(row).attr('data-segment-name'));
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
