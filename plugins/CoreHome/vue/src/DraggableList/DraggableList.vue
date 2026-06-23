<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul
    class="draggableList"
    :class="{
      isDragging: draggedId !== null,
      isDisabled: disabled,
    }"
  >
    <li
      v-for="(orderedItem, index) in orderedItems"
      :key="orderedItem.id"
      class="draggableListItem"
      :class="{ isDragged: orderedItem.id === placeholderId }"
      :data-item-id="orderedItem.id"
      :draggable="canDrag"
      :aria-grabbed="orderedItem.id === draggedId"
      @dragstart="onDragStartForIndex($event, index)"
      @dragover="onDragOverForIndex($event, index)"
      @drop="onDrop"
      @dragend="onDragEnd"
    >
      <slot
        :item="orderedItem.item"
        :index="orderedItem.sourceIndex"
      />
    </li>
  </ul>
</template>

<script setup lang="ts">
import {
  computed,
  ref,
  watch,
} from 'vue';

type ItemKey = string | number;
type ItemKeyGetter = string | ((item: unknown, index: number) => ItemKey);
type DropPosition = 'before' | 'after';
const SORT_TRIGGER_OFFSET = 0.1;

interface OrderedItem {
  id: string;
  item: unknown;
  sourceIndex: number;
}

interface Props {
  items: unknown[];
  itemKey: ItemKeyGetter;
  disabled?: boolean;
  handle?: string;
  axis?: string;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  handle: '',
  axis: 'y',
});

const emit = defineEmits<{(e: 'reorder', order: string[]): void;}>();

const orderedItems = ref<OrderedItem[]>([]);
const draggedId = ref<string | null>(null);
const dragTargetId = ref<string | null>(null);
const placeholderId = ref<string | null>(null);
const dropSucceeded = ref(false);

const canDrag = computed(() => !props.disabled && props.items.length > 1);

function getItemKey(item: unknown, index: number): ItemKey {
  if (typeof props.itemKey === 'function') return props.itemKey(item, index);
  if (!item || typeof item !== 'object') return index;

  const value = (item as Record<string, unknown>)[props.itemKey];

  if (typeof value === 'string' || typeof value === 'number') return value;
  return index;
}

const sourceItems = computed<OrderedItem[]>(() => props.items.map((item, index) => ({
  id: String(getItemKey(item, index)),
  item,
  sourceIndex: index,
})));

const itemKeySignature = computed(() => sourceItems.value.map((entry) => entry.id).join('\u0000'));

function syncOrderedItems() {
  orderedItems.value = sourceItems.value.slice();
}

function clearDragVisualState() {
  draggedId.value = null;
  dragTargetId.value = null;
  placeholderId.value = null;
}

function resetDragState(shouldSync = false) {
  clearDragVisualState();
  dropSucceeded.value = false;

  if (shouldSync) syncOrderedItems();
}

function matchesHandle(target: EventTarget | null, currentTarget: HTMLElement) {
  if (!props.handle) return true;
  if (!(target instanceof Element)) return false;

  const handleElement = target.closest(props.handle);
  return !!handleElement && currentTarget.contains(handleElement);
}

function getOrderedIndex(itemId: string) {
  return orderedItems.value.findIndex((entry) => entry.id === itemId);
}

function getOrderedItemAt(index: number | string | symbol) {
  return orderedItems.value[typeof index === 'number' ? index : Number(index)];
}

function getDropPosition(event: DragEvent, element: HTMLElement): DropPosition {
  const rect = element.getBoundingClientRect();
  const draggedIndex = draggedId.value ? getOrderedIndex(draggedId.value) : -1;
  const hoveredIndex = dragTargetId.value ? getOrderedIndex(dragTargetId.value) : -1;
  const isMovingForward = draggedIndex !== -1 && hoveredIndex !== -1 && draggedIndex < hoveredIndex;
  const triggerOffset = isMovingForward ? SORT_TRIGGER_OFFSET : (1 - SORT_TRIGGER_OFFSET);

  if (props.axis === 'x') {
    return event.clientX < rect.left + (rect.width * triggerOffset) ? 'before' : 'after';
  }

  return event.clientY < rect.top + (rect.height * triggerOffset) ? 'before' : 'after';
}

function moveDraggedItem(targetId: string, position: DropPosition) {
  if (!draggedId.value || draggedId.value === targetId) return;

  const currentIndex = getOrderedIndex(draggedId.value);
  const targetIndex = getOrderedIndex(targetId);

  if (currentIndex === -1 || targetIndex === -1) return;

  let insertionIndex = targetIndex + (position === 'after' ? 1 : 0);
  if (currentIndex < insertionIndex) {
    insertionIndex -= 1;
  }

  if (insertionIndex === currentIndex) return;

  const nextItems = orderedItems.value.slice();
  const [movedItem] = nextItems.splice(currentIndex, 1);
  nextItems.splice(insertionIndex, 0, movedItem);
  orderedItems.value = nextItems;
}

function onDragStart(event: DragEvent, itemId: string) {
  const itemElement = event.currentTarget as HTMLElement | null;

  if (!itemElement || !canDrag.value || !matchesHandle(event.target, itemElement)) {
    event.preventDefault();
    return;
  }

  draggedId.value = itemId;
  dragTargetId.value = itemId;
  dropSucceeded.value = false;
  placeholderId.value = null;

  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', itemId);
  }

  // Delay the placeholder styling so the browser creates the native drag ghost
  // from the original item instead of the emptied placeholder
  window.setTimeout(() => {
    if (draggedId.value === itemId) placeholderId.value = itemId;
  }, 0);
}

function onDragStartForIndex(event: DragEvent, index: number | string | symbol) {
  const orderedItem = getOrderedItemAt(index);
  if (!orderedItem) {
    event.preventDefault();
    return;
  }

  onDragStart(event, orderedItem.id);
}

function onDragOver(event: DragEvent, itemId: string) {
  if (!draggedId.value || !canDrag.value) return;

  event.preventDefault();

  const itemElement = event.currentTarget as HTMLElement | null;
  if (!itemElement) return;

  dragTargetId.value = itemId;
  moveDraggedItem(itemId, getDropPosition(event, itemElement));

  if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
}

function onDragOverForIndex(event: DragEvent, index: number | string | symbol) {
  const orderedItem = getOrderedItemAt(index);
  if (!orderedItem) return;

  onDragOver(event, orderedItem.id);
}

function onDrop(event: DragEvent) {
  if (!draggedId.value) return;

  event.preventDefault();

  const reorderedIds = orderedItems.value.map((entry) => entry.id);
  if (reorderedIds && reorderedIds.join('\u0000') !== itemKeySignature.value) {
    dropSucceeded.value = true;
    emit('reorder', reorderedIds);
  }

  clearDragVisualState();
}

function onDragEnd() {
  if (dropSucceeded.value) {
    dropSucceeded.value = false;
    return;
  }

  resetDragState(true);
}

// Refresh the local list if the parent sends new items
watch([sourceItems, () => props.disabled], () => resetDragState(true), { immediate: true });
</script>
