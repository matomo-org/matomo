<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- test in UI
- check uses:
  ./plugins/SegmentEditor/javascripts/Segmentation.js
  ./plugins/SegmentEditor/angularjs/segment-generator/segmentgenerator.directive.js
  ./plugins/PrivacyManager/angularjs/manage-gdpr/managegdpr.directive.html
  ./plugins/AdvertisingConversionExport/angularjs/manage/edit.directive.html
  ./plugins/CustomReports/angularjs/manage/edit.directive.html
- create PR
</todo>

<template>
  <div class="segment-generator" ref="root">
    <ActivityIndicator :loading="isLoading" />
    <div
      :class="`segmentRow${conditionIndex}`"
      v-for="(condition, conditionIndex) in conditions"
      :key="conditionIndex"
    >
      <div class="segment-rows">
        <div
          :class="`orCondId${orCondition.id}`"
          v-for="(orCondition, orConditionIndex) in condition.orConditions"
          :key="orConditionIndex"
        >
          <div class="segment-row">
            <a
              class="segment-close"
              @click="removeOrCondition(condition, orCondition)"
            />
            <a
              href="#"
              class="segment-loading"
              v-show="orCondition.isLoading"
            />
            <div class="segment-row-inputs valign-wrapper">
              <div class="segment-input metricListBlock valign-wrapper">
                <div style="width: 100%;">
                  <Field
                    uicontrol="expandable-select"
                    name="segments"
                    :model-value="orCondition.segment"
                    @update:model-value="orCondition.segment = $event;
                      updateAutocomplete(orCondition)"
                    :title="segments[orCondition.segment].name"
                    :full-width="true"
                    :options="segmentList"
                  >
                  </Field>
                </div>
              </div>
              <div class="segment-input metricMatchBlock valign-wrapper">
                <div style="display: inline-block">
                  <Field
                    uicontrol="select"
                    name="matchType"
                    v-model="orCondition.matches"
                    :full-width="true"
                    :options="matches.segments[orCondition.segment].type"
                  >
                  </Field>
                </div>
              </div>
              <div class="segment-input metricValueBlock valign-wrapper">
                <div
                  class="form-group row"
                  style="width: 100%;"
                >
                  <div class="input-field col s12">
                    <span
                      role="status"
                      aria-live="polite"
                      class="ui-helper-hidden-accessible"
                    />
                    <input
                      placeholder="Value"
                      type="text"
                      class="autocomplete"
                      title="Value"
                      autocomplete="off"
                      :value="orCondition.value"
                      @keydown="onKeydownOrConditionValue(orCondition, $event)"
                    />
                  </div>
                </div>
              </div>
              <div class="clear" />
            </div>
          </div>
          <div class="segment-or">{{ translate('SegmentEditor_OperatorOR') }}</div>
        </div>
        <div
          class="segment-add-or"
          @click="addNewOrCondition(condition)"
        >
          <div>
            <a v-html="$sanitize(addNewOrConditionLinkText)" />
          </div>
        </div>
      </div>
      <div class="segment-and">{{ translate('SegmentEditor_OperatorAND') }}</div>
    </div>
    <div
      class="segment-add-row initial"
      @click="addNewAndCondition()"
    >
      <div>
        <a v-html="$sanitize(addNewAndConditionLinkText)" />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { DeepReadonly, defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  ActivityIndicator,
  debounce,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import SegmentGeneratorStore from './SegmentGenerator.store';
import { SegmentAndCondition, SegmentMetadata, SegmentOrCondition } from '../types';

interface SegmentGeneratorState {
  conditions: SegmentAndCondition[];
  matches: Record<string, { key: string, value: string }[]>;
  queriedSegments: DeepReadonly<SegmentMetadata[]>;
  andConditionLabel: string;
}

function initialMatches() {
  return {
    metric: [
      {
        key: '==',
        value: translate('General_OperationEquals'),
      },
      {
        key: '!=',
        value: translate('General_OperationNotEquals'),
      },
      {
        key: '<=',
        value: translate('General_OperationAtMost'),
      },
      {
        key: '>=',
        value: translate('General_OperationAtLeast'),
      },
      {
        key: '<',
        value: translate('General_OperationLessThan'),
      },
      {
        key: '>',
        value: translate('General_OperationGreaterThan'),
      },
    ],
    dimension: [
      {
        key: '==',
        value: translate('General_OperationIs'),
      },
      {
        key: '!=',
        value: translate('General_OperationIsNot'),
      },
      {
        key: '=@',
        value: translate('General_OperationContains'),
      },
      {
        key: '!@',
        value: translate('General_OperationDoesNotContain'),
      },
      {
        key: '=^',
        value: translate('General_OperationStartsWith'),
      },
      {
        key: '=$',
        value: translate('General_OperationEndsWith'),
      },
    ],
  };
}

function generateUniqueId() {
  let id = '';
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

  for (let i = 1; i <= 10; i += 1) {
    id += chars.charAt(Math.floor(Math.random() * chars.length));
  }

  return id;
}

function findAndExplodeByMatch(metric: string) {
  const matches = ['==', '!=', '<=', '>=', '=@', '!@', '<', '>', '=^', '=$'];
  const newMetric: SegmentOrCondition = {} as unknown as SegmentOrCondition;
  let minPos = metric.length;
  let match;
  let index: number;
  let singleChar = false;

  for (let key = 0; key < matches.length; key += 1) {
    match = matches[key];
    index = metric.indexOf(match);
    if (index !== -1) {
      if (index < minPos) {
        minPos = index;
        if (match.length === 1) {
          singleChar = true;
        }
      }
    }
  }

  if (minPos < metric.length) {
    // sth found - explode
    if (singleChar === true) {
      newMetric.segment = metric.substr(0, minPos);
      newMetric.matches = metric.substr(minPos, 1);
      newMetric.value = decodeURIComponent(metric.substr(minPos + 1));
    } else {
      newMetric.segment = metric.substr(0, minPos);
      newMetric.matches = metric.substr(minPos, 2);
      newMetric.value = decodeURIComponent(metric.substr(minPos + 2));
    }

    // if value is only '' -> change to empty string
    if (newMetric.value === '""') {
      newMetric.value = '';
    }
  }

  try {
    // Decode again to deal with double-encoded segments in database
    newMetric.value = decodeURIComponent(newMetric.value);
  } catch (e) {
    // Expected if the segment was not double-encoded
  }

  return newMetric;
}

function stripTags(text?: unknown) {
  return text ? `${text}`.replace(/(<([^>]+)>)/ig, '') : text;
}

const { $ } = window;

export default defineComponent({
  props: {
    addInitialCondition: Boolean,
    visitSegmentsOnly: Boolean,
    idsite: {
      type: [String, Number],
      required: true,
    },
    modelValue: {
      type: String,
      default: '',
    },
  },
  components: {
    ActivityIndicator,
    Field,
  },
  data(): SegmentGeneratorState {
    return {
      conditions: [],
      queriedSegments: [],
      matches: initialMatches(),
      andConditionLabel: '',
    };
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue(newVal) {
      if (newVal !== this.segmentDefinition) {
        this.setSegmentString(newVal);
      }
    },
    segmentDefinition(newVal) {
      if (newVal !== this.modelValue) {
        this.$emit('update:modelValue', newVal);
      }
    },
    idsite(newVal) {
      this.reloadSegments(newVal, this.visitSegmentsOnly);
    },
  },
  created() {
    this.onKeydownOrConditionValue = debounce(this.onKeydownOrConditionValue, 50);

    // TODO: ngModel bindings
    this.matches[''] = this.matches.dimension;
    this.reloadSegments(this.idsite, this.visitSegmentsOnly);
  },
  methods: {
    reloadSegments(idsite: string|number, visitSegmentsOnly?: boolean) {
      SegmentGeneratorStore.loadSegments(idsite, visitSegmentsOnly).then((segments) => {
        this.queriedSegments = segments.map((s) => ({
          ...s,
          category: s.category || 'Others',
        }));

        if (this.addInitialCondition && this.conditions.length === 0) {
          this.addNewAndCondition();
        }
      });
    },
    addAndCondition(condition: SegmentAndCondition) {
      this.andConditionLabel = translate('SegmentEditor_OperatorAND');

      this.conditions.push(condition);
    },
    addNewOrCondition(condition: SegmentAndCondition) {
      const orCondition = {
        segment: this.firstSegment,
        matches: this.firstMatch!,
        value: '',
      };

      this.addOrCondition(condition, orCondition);
    },
    addOrCondition(condition: SegmentAndCondition, orCondition: SegmentOrCondition) {
      orCondition.isLoading = false;
      orCondition.id = generateUniqueId();

      condition.orConditions.push(orCondition);

      setTimeout(() => {
        this.updateAutocomplete(orCondition);
      });
    },
    updateAutocomplete(orCondition: SegmentOrCondition) {
      orCondition.isLoading = true;

      $(`.orCondId${orCondition.id} .metricValueBlock input`, this.$refs.root as HTMLElement)
        .autocomplete({
          source: [],
          minLength: 0,
        });

      const abortController = new AbortController();

      let resolved = false;
      AjaxHelper.fetch<string[]>(
        {
          module: 'API',
          format: 'json',
          method: 'API.getSuggestedValuesForSegment',
          segmentName: orCondition.segment,
        },
        {
          createErrorNotification: false,
          abortController,
        },
      ).then((response) => {
        orCondition.isLoading = false;
        resolved = true;

        const inputElement = $(`.orCondId${orCondition.id} .metricValueBlock input`)
          .autocomplete({
            source: response,
            minLength: 0,
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            select: (event: Event, ui: any) => {
              event.preventDefault();

              orCondition.value = ui.item.value;
            },
          })
          .off('click')
          .click(() => {
            $(inputElement).autocomplete('search', orCondition.value);
          });
      }).catch(() => {
        resolved = true;

        orCondition.isLoading = false;

        $(`.orCondId${orCondition.id} .metricValueBlock input`)
          .autocomplete({
            source: [],
            minLength: 0,
          })
          .autocomplete('search', orCondition.value);
      });

      setTimeout(() => {
        if (!resolved) {
          abortController.abort();
        }
      }, 20000);
    },
    removeOrCondition(condition: SegmentAndCondition, orCondition: SegmentOrCondition) {
      const index = condition.orConditions.indexOf(orCondition);

      if (index > -1) {
        condition.orConditions.splice(index, 1);
      }

      if (condition.orConditions.length === 0) {
        const andCondIndex = this.conditions.indexOf(condition);

        if (index > -1) {
          this.conditions.splice(andCondIndex, 1);
        }

        if (this.conditions.length === 0) {
          this.andConditionLabel = '';
        }
      }
    },
    setSegmentString(segmentStr: string) {
      let orCondition: SegmentOrCondition;
      let condition: SegmentAndCondition;

      this.conditions = [];

      if (!segmentStr) {
        return;
      }

      const blocks = segmentStr.split(';').map((b) => b.split(','));
      blocks.forEach((block) => {
        condition = { orConditions: [] };

        this.addAndCondition(condition);

        block.forEach((innerBlock) => {
          orCondition = findAndExplodeByMatch(innerBlock);
          this.addOrCondition(condition, orCondition);
        });
      });
    },
    addNewAndCondition() {
      const condition = { orConditions: [] };

      this.addAndCondition(condition);
      this.addNewOrCondition(condition);

      return condition;
    },
    onKeydownOrConditionValue(orCondition: SegmentOrCondition, event: Event) {
      orCondition.value = (event.target as HTMLInputElement).value;
    },
  },
  computed: {
    firstSegment() {
      return this.queriedSegments[0].segment;
    },
    firstMatch() {
      const segment = this.queriedSegments[0];
      if (!segment) {
        return null;
      }

      if (segment.type && this.matches[segment.type]) {
        return this.matches[segment.type][0].key;
      }

      return this.matches[''][0].key;
    },
    segments() {
      const result: Record<string, SegmentMetadata> = {};
      this.queriedSegments.forEach((s) => {
        result[s.segment] = s;
      });
      return result;
    },
    segmentList() {
      return this.queriedSegments.map((s) => ({
        group: s.category,
        key: s.segment,
        value: s.name,
        tooltip: s.acceptedValues ? stripTags(s.acceptedValues) : undefined,
      }));
    },
    segmentDefinition() {
      let segmentStr = '';

      this.conditions.forEach((conditions) => {
        let subSegmentStr = '';

        conditions.orConditions.forEach((orCondition) => {
          if (subSegmentStr !== '') {
            subSegmentStr += ','; // OR operator
          }

          // one encode for urldecode on value, one encode for urldecode on condition
          const value = encodeURIComponent(encodeURIComponent(orCondition.value));
          subSegmentStr += `${orCondition.segment}${orCondition.matches}${value}`;
        });

        if (segmentStr !== '') {
          segmentStr += ';'; // add AND operator between segment blocks
        }

        segmentStr += subSegmentStr;
      });

      return segmentStr;
    },
    addNewOrConditionLinkText() {
      return `+${translate(
        'SegmentEditor_AddANDorORCondition',
        `<span>${translate('SegmentEditor_OperatorOR')}</span>`,
      )}`;
    },
    addNewAndConditionLinkText() {
      return `+${translate('SegmentEditor_AddANDorORCondition', `<span>${this.andConditionLabel}</span>`)}`;
    },
    isLoading() {
      return SegmentGeneratorStore.state.value.isLoading;
    },
  },
});
</script>
