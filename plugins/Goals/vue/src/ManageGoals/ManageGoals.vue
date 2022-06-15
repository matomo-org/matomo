<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="manageGoals">
    <!-- v-show required until funnels/multiattribution are using vue and not angularjs -->
    <div v-show="!onlyShowAddNewGoal">
      <div
        id='entityEditContainer'
        feature="true"
        v-show="showGoalList"
        class="managegoals"
      >
        <ContentBlock :content-title="translate('Goals_ManageGoals')">
          <ActivityIndicator :loading="isLoading"/>

          <div class="contentHelp">
            <span v-html="$sanitize(learnMoreAboutGoalTracking)"/>
            <span v-if="!ecommerceEnabled">
              <br /><br/>

              {{ translate('Goals_Optional') }} {{ translate('Goals_Ecommerce') }}:
              <span v-html="$sanitize(youCanEnableEcommerceReports)"/>
            </span>
          </div>

          <table v-content-table>
            <thead>
              <tr>
                <th class="first">{{ translate('General_Id') }}</th>
                <th>{{ translate('Goals_GoalName') }}</th>
                <th>{{ translate('General_Description') }}</th>
                <th>{{ translate('Goals_GoalIsTriggeredWhen') }}</th>
                <th>{{ translate('General_ColumnRevenue') }}</th>

                <component
                  v-if="beforeGoalListActionsHeadComponent"
                  :is="beforeGoalListActionsHeadComponent"
                ></component>

                <th v-if="userCanEditGoals">{{ translate('General_Edit') }}</th>
                <th v-if="userCanEditGoals">{{ translate('General_Delete') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!Object.keys(goals || {}).length">
                <td colspan='8'>
                  <br/>
                  {{ translate('Goals_ThereIsNoGoalToManage', siteName) }}
                  <br/><br/>
                </td>
              </tr>
              <tr v-for="goal in goals || []" :id="goal.idgoal" :key="goal.idgoal">
                <td class="first">{{ goal.idgoal }}</td>
                <td>{{ goal.name }}</td>
                <td>{{ goal.description }}</td>
                <td>
                  <span class='matchAttribute'>
                    {{ goalMatchAttributeTranslations[goal.match_attribute]
                      || goal.match_attribute }}
                  </span>
                  <span v-if="goal.match_attribute === 'visit_duration'">
                    {{ lcfirst(translate('General_OperationGreaterThan')) }}
                    {{ translate('Intl_NMinutes', goal.pattern) }}
                  </span>
                  <span v-else-if="!!goal.pattern_type">
                    <br/>
                    {{ translate('Goals_Pattern') }} {{ goal.pattern_type }}: {{ goal.pattern }}
                  </span>
                </td>
                <td
                  class="center"
                  v-html="$sanitize(
                    goal.revenue === 0 || goal.revenue === '0' ? '-' : goal.revenue_pretty,
                    )"
                >
                </td>

                <component
                  v-if="beforeGoalListActionsBodyComponent[goal.idgoal]"
                  :is="beforeGoalListActionsBodyComponent[goal.idgoal]"
                ></component>

                <td v-if="userCanEditGoals" style="padding-top:2px">
                  <button
                    @click="editGoal(goal.idgoal)"
                    class="table-action"
                    :title="translate('General_Edit')"
                  >
                    <span class="icon-edit"></span>
                  </button>
                </td>
                <td v-if="userCanEditGoals" style="padding-top:2px">
                  <button
                    @click="deleteGoal(goal.idgoal)"
                    class="table-action"
                    :title="translate('General_Delete')"
                  >
                    <span class="icon-delete"></span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="tableActionBar" v-if="userCanEditGoals && !onlyShowAddNewGoal">
            <button id="add-goal" @click="createGoal()">
              <span class="icon-add"></span>
              {{ translate('Goals_AddNewGoal') }}
            </button>
          </div>
        </ContentBlock>
      </div>

      <div class="ui-confirm" ref="confirm">
        <h2>{{ translate('Goals_DeleteGoalConfirm', `"${goalToDelete?.name}"`) }}</h2>
        <input role="yes" type="button" :value="translate('General_Yes')"/>
        <input role="no" type="button" :value="translate('General_No')"/>
      </div>
    </div>

    <!-- v-show required until funnels/multiattribution are using vue and not angularjs -->
    <div v-show="userCanEditGoals">
      <div class="addEditGoal" v-show="showEditGoal">
        <ContentBlock
          :content-title="goal.idgoal
            ? translate('Goals_UpdateGoal')
            : translate('Goals_AddNewGoal')"
        >
          <div v-html="$sanitize(addNewGoalIntro)"></div>

          <div v-form>
            <div>
              <Field
                uicontrol="text"
                name="goal_name"
                v-model="goal.name"
                :maxlength="50"
                :title="translate('Goals_GoalName')">
              </Field>
            </div>

            <div>
              <Field
                uicontrol="text"
                name="goal_description"
                v-model="goal.description"
                :maxlength="255"
                :title="translate('General_Description')"
              />
            </div>

            <div class="row goalIsTriggeredWhen">
              <div class="col s12">
                <h3>{{ translate('Goals_GoalIsTriggered') }}</h3>
              </div>
            </div>

            <div class="row">
              <div class="col s12 m6 goalTriggerType">
                <div>
                  <Field
                    uicontrol="select" name="trigger_type"
                    :model-value="triggerType"
                    @update:model-value="triggerType = $event; changedTriggerType()"
                    :full-width="true"
                    :options="goalTriggerTypeOptions"
                  />
                </div>
              </div>
              <div class="col s12 m6">
                <Alert severity="info" v-show="triggerType === 'manually'">
                  <span v-html="$sanitize(whereVisitedPageManuallyCallsJsTrackerText)"></span>
                </Alert>

                <div>
                  <Field
                    uicontrol="radio"
                    name="match_attribute"
                    v-show="triggerType !== 'manually'"
                    :full-width="true"
                    :model-value="goal.match_attribute"
                    @update:model-value="goal.match_attribute = $event; initPatternType()"
                    :options="goalMatchAttributeOptions"
                  />
                </div>
              </div>
            </div>

            <div class="row whereTheMatchAttrbiute" v-show="triggerType !== 'manually'">
              <h3 class="col s12">{{ translate('Goals_WhereThe') }}
                <span v-show="goal.match_attribute === 'url'">
                  {{ translate('Goals_URL') }}
                </span>
                <span v-show="goal.match_attribute === 'title'">
                  {{ translate('Goals_PageTitle') }}
                </span>
                <span v-show="goal.match_attribute === 'file'">
                  {{ translate('Goals_Filename') }}
                </span>
                <span v-show="goal.match_attribute === 'external_website'">
                  {{ translate('Goals_ExternalWebsiteUrl') }}
                </span>
                <span v-show="goal.match_attribute === 'visit_duration'">
                  {{ translate('Goals_VisitDuration') }}
                </span>
              </h3>
            </div>

            <div class="row" v-show="triggerType !== 'manually'">
              <div class="col s12 m6 l4"
                   v-show="goal.match_attribute === 'event'">
                <div>
                  <Field
                    uicontrol="select" name="event_type"
                    v-model="eventType"
                    :full-width="true"
                    :options="eventTypeOptions"
                  />
                </div>
              </div>

              <div class="col s12 m6 l4" v-if="!isMatchAttributeNumeric">
                <div>
                  <Field
                    uicontrol="select"
                    name="pattern_type"
                    v-model="goal.pattern_type"
                    :full-width="true"
                    :options="patternTypeOptions"
                  />
                </div>
              </div>

              <div class="col s12 m6 l4" v-if="isMatchAttributeNumeric">
                <div>
                  <Field
                    uicontrol="select" name="pattern_type"
                    v-model="goal.pattern_type"
                    :full-width="true"
                    :options="numericComparisonTypeOptions"
                  />
                </div>
              </div>

              <div class="col s12 m6 l4">
                <div>
                  <Field
                    uicontrol="text" name="pattern"
                    v-model="goal.pattern"
                    :maxlength="255"
                    :title="patternFieldLabel"
                    :full-width="true"
                  />
              </div>
            </div>

            <div id="examples_pattern" class="col s12">
              <Alert severity="info">
                <span v-show="goal.match_attribute === 'url'">
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_Contains', "'checkout/confirmation'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_IsExactly', "'http://example.com/thank-you.html'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_MatchesExpression', "'(.*)\\\/demo\\\/(.*)'") }}
                </span>
                <span v-show="goal.match_attribute === 'title'">
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_Contains', "'Order confirmation'") }}
                </span>
                <span v-show="goal.match_attribute === 'file'">
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_Contains', "'files/brochure.pdf'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_IsExactly', "'http://example.com/files/brochure.pdf'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_MatchesExpression', "'(.*)\\\.zip'") }}
                </span>
                <span v-show="goal.match_attribute === 'external_website'">
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_Contains', "'amazon.com'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_IsExactly', "'http://mypartner.com/landing.html'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ matchesExpressionExternal }}
                </span>
                <span v-show="goal.match_attribute === 'event'">
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_Contains', "'video'") }}
                  <br />
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_IsExactly', "'click'") }}
                  <br />{{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_MatchesExpression', "'(.*)_banner'") }}"
                </span>
                <span v-show="goal.match_attribute === 'visit_duration'">
                  {{ translate('General_ForExampleShort') }}
                  {{ translate('Goals_AtLeastMinutes', '5', '0.5') }}
                </span>
              </Alert>
            </div>
          </div>

          <div>
            <Field
              uicontrol="checkbox"
              name="case_sensitive"
              v-model="goal.case_sensitive"
              v-show="triggerType !== 'manually' && !isMatchAttributeNumeric"
              :title="caseSensitiveTitle"
            />
          </div>

          <div>
            <Field
              uicontrol="radio"
              name="allow_multiple"
              :model-value="!!goal.allow_multiple && goal.allow_multiple !== '0' ? 1 : 0"
              @update:model-value="goal.allow_multiple = $event"
              v-if="goal.match_attribute !== 'visit_duration'"
              :options="allowMultipleOptions"
              :introduction="translate('Goals_AllowMultipleConversionsPerVisit')"
              :inline-help="translate('Goals_HelpOneConversionPerVisit')"
            />
          </div>

          <h3>{{ translate('Goals_GoalRevenue') }} {{ translate('Goals_Optional') }}</h3>

          <div>
            <Field
              uicontrol="number"
              name="revenue"
              v-model="goal.revenue"
              :placeholder="translate('Goals_DefaultRevenueLabel')"
              :inline-help="translate('Goals_DefaultRevenueHelp')"
            />
          </div>

          <div>
            <Field
              uicontrol="checkbox"
              name="use_event_value"
              v-model="goal.event_value_as_revenue"
              :title="translate('Goals_UseEventValueAsRevenue')"
              v-show="goal.match_attribute === 'event'"
              :inline-help="useEventValueAsRevenueHelp"
            />
          </div>

          <div ref="endedittable">
            <component :is="endEditTableComponent" v-if="endEditTableComponent"/>
          </div>

          <input type="hidden" name="goalIdUpdate" value=""/>

          <SaveButton
            :saving="isLoading"
            @confirm="save()"
            :value="submitText"
          />

          <div v-if="!onlyShowAddNewGoal">
            <div
              class='entityCancel'
              v-show="showEditGoal"
              @click="showListOfReports()"
              v-html="$sanitize(cancelText)"
             >
            </div>
          </div>
        </div>
      </ContentBlock>
    </div>
    </div>

    <a id='bottom'></a>
  </div>
</template>

<script lang="ts">
import { IScope } from 'angular';
import { defineComponent, markRaw, nextTick } from 'vue';
import {
  Matomo,
  AjaxHelper,
  AjaxOptions,
  translate,
  ContentBlock,
  ActivityIndicator,
  MatomoUrl,
  ContentTable,
  Alert,
  ReportingMenuStore,
} from 'CoreHome';
import {
  Form,
  Field,
  SaveButton,
} from 'CorePluginsAdmin';
import Goal from '../Goal';
import PiwikApiMock from './PiwikApiMock';
import ManageGoalsStore from './ManageGoals.store';

interface ManageGoalsState {
  showEditGoal: boolean;
  showGoalList: boolean;
  goal: Goal;
  isLoading: boolean;
  eventType: string;
  triggerType: string;
  apiMethod: string;
  submitText: string;
  goalToDelete: Goal|null;
  addEditTableComponent: boolean;
}

function ambiguousBoolToInt(n: string|number|boolean): 1|0 {
  return !!n && n !== '0' ? 1 : 0;
}

export default defineComponent({
  inheritAttrs: false,
  props: {
    onlyShowAddNewGoal: Boolean,
    userCanEditGoals: Boolean,
    ecommerceEnabled: Boolean,
    goals: {
      type: Object,
      required: true,
    },
    addNewGoalIntro: String,
    goalTriggerTypeOptions: Object,
    goalMatchAttributeOptions: Array,
    eventTypeOptions: Array,
    patternTypeOptions: Array,
    numericComparisonTypeOptions: Array,
    allowMultipleOptions: Array,
    showAddGoal: Boolean,
    showGoal: Number,
    beforeGoalListActionsBody: Object,
    endEditTable: String,
    beforeGoalListActionsHead: String,
  },
  data(): ManageGoalsState {
    return {
      showEditGoal: false,
      showGoalList: true,
      goal: {} as unknown as Goal,
      isLoading: false,
      eventType: 'event_category',
      triggerType: 'visitors',
      apiMethod: '',
      submitText: '',
      goalToDelete: null,
      addEditTableComponent: false,
    };
  },
  components: {
    SaveButton,
    ContentBlock,
    ActivityIndicator,
    Field,
    Alert,
  },
  directives: {
    ContentTable,
    Form,
  },
  created() {
    ManageGoalsStore.setIdGoalShown(this.showGoal);
  },
  unmounted() {
    ManageGoalsStore.setIdGoalShown(undefined);
  },
  mounted() {
    if (this.showAddGoal) {
      this.createGoal();
    } else if (this.showGoal) {
      this.editGoal(this.showGoal);
    } else {
      this.showListOfReports();
    }

    // this component can be used in multiple places, one where
    // Matomo.helper.compileAngularComponents() is already called, one where it's not.
    // to make sure this function is only applied once to the slot data, we explicitly do not
    // add it to vue, then on the next update, add it and call compileAngularComponents()
    nextTick(() => {
      this.addEditTableComponent = true;

      nextTick(() => {
        const el = this.$refs.endedittable as HTMLElement;
        const scope = Matomo.helper.getAngularDependency('$rootScope').$new(true);
        $(el).data('scope', scope);
        Matomo.helper.compileAngularComponents(el, { scope });
      });
    });
  },
  beforeUnmount() {
    const el = this.$refs.endedittable as HTMLElement;
    ($(el).data('scope') as IScope).$destroy();
  },
  methods: {
    scrollToTop() {
      setTimeout(() => {
        Matomo.helper.lazyScrollTo('.pageWrap', 200);
      });
    },
    initGoalForm(
      goalMethodAPI: string,
      submitText: string,
      goalName: string,
      description: string,
      matchAttribute: string,
      pattern: string,
      patternType: string,
      caseSensitive: boolean,
      revenue: number,
      allowMultiple: boolean,
      useEventValueAsRevenue: boolean,
      goalId: string|number,
    ) {
      Matomo.postEvent('Goals.beforeInitGoalForm', goalMethodAPI, goalId);

      this.apiMethod = goalMethodAPI;

      this.goal = {} as unknown as Goal;
      this.goal.name = goalName;
      this.goal.description = description;

      let actualMatchAttribute = matchAttribute;
      if (actualMatchAttribute === 'manually') {
        this.triggerType = 'manually';
        actualMatchAttribute = 'url';
      } else {
        this.triggerType = 'visitors';
      }

      if (actualMatchAttribute.indexOf('event') === 0) {
        this.eventType = actualMatchAttribute;
        actualMatchAttribute = 'event';
      } else {
        this.eventType = 'event_category';
      }

      this.goal.match_attribute = actualMatchAttribute;
      this.goal.allow_multiple = allowMultiple;
      this.goal.pattern_type = patternType;
      this.goal.pattern = pattern;
      this.goal.case_sensitive = caseSensitive;
      this.goal.revenue = revenue;
      this.goal.event_value_as_revenue = useEventValueAsRevenue;
      this.submitText = submitText;
      this.goal.idgoal = goalId;
    },
    showListOfReports() {
      Matomo.postEvent('Goals.cancelForm');
      this.showGoalList = true;
      this.showEditGoal = false;
      this.scrollToTop();
    },
    showAddEditForm() {
      this.showGoalList = false;
      this.showEditGoal = true;
    },
    createGoal() {
      const parameters = {
        isAllowed: true,
      };
      Matomo.postEvent('Goals.initAddGoal', parameters);

      if (parameters && !parameters.isAllowed) {
        return;
      }

      this.showAddEditForm();
      this.initGoalForm(
        'Goals.addGoal',
        translate('Goals_AddGoal'),
        '',
        '',
        'url',
        '',
        'contains',
        false,
        0,
        false,
        false,
        0,
      );
      this.scrollToTop();
    },
    editGoal(goalId: string|number) {
      this.showAddEditForm();
      const goal = this.goals[`${goalId}`] as Goal;
      this.initGoalForm(
        'Goals.updateGoal',
        translate('Goals_UpdateGoal'),
        goal.name,
        goal.description,
        goal.match_attribute,
        goal.pattern,
        goal.pattern_type,
        !!goal.case_sensitive && goal.case_sensitive !== '0',
        parseInt(`${goal.revenue}`, 10),
        !!goal.allow_multiple && goal.allow_multiple !== '0',
        !!goal.event_value_as_revenue && goal.event_value_as_revenue !== '0',
        goalId,
      );
      this.scrollToTop();
    },
    deleteGoal(goalId: string|number) {
      this.goalToDelete = this.goals[`${goalId}`];
      Matomo.helper.modalConfirm((this.$refs.confirm as HTMLElement), {
        yes: () => {
          this.isLoading = true;

          AjaxHelper.fetch({
            idGoal: goalId,
            method: 'Goals.deleteGoal',
          }).then(() => {
            window.location.reload();
          }).finally(() => {
            this.isLoading = false;
          });
        },
      });
    },
    save() {
      const parameters: QueryParameters = {};
      // TODO: test removal of encoding, should be handled by ajax request
      parameters.name = this.goal.name;
      parameters.description = this.goal.description;

      if (this.isManuallyTriggered) {
        parameters.matchAttribute = 'manually';
        parameters.patternType = 'regex';
        parameters.pattern = '.*';
        parameters.caseSensitive = 0;
      } else {
        parameters.matchAttribute = this.goal.match_attribute;

        if (parameters.matchAttribute === 'event') {
          parameters.matchAttribute = this.eventType;
        }

        parameters.patternType = this.goal.pattern_type;
        parameters.pattern = this.goal.pattern;
        parameters.caseSensitive = ambiguousBoolToInt(this.goal.case_sensitive);
      }
      parameters.revenue = this.goal.revenue || 0;
      parameters.allowMultipleConversionsPerVisit = ambiguousBoolToInt(this.goal.allow_multiple);
      parameters.useEventValueAsRevenue = ambiguousBoolToInt(this.goal.event_value_as_revenue);

      parameters.idGoal = this.goal.idgoal;
      parameters.method = this.apiMethod;

      const isCreate = parameters.method === 'Goals.addGoal';
      const isUpdate = parameters.method === 'Goals.updateGoal';

      const options: AjaxOptions = {};

      const piwikApiMock = new PiwikApiMock(parameters, options);
      if (isUpdate) {
        Matomo.postEvent('Goals.beforeUpdateGoal', parameters, piwikApiMock);
      } else if (isCreate) {
        Matomo.postEvent('Goals.beforeAddGoal', parameters, piwikApiMock);
      }

      if (parameters?.cancelRequest) {
        return;
      }

      this.isLoading = true;

      AjaxHelper.fetch(parameters, options).then(() => {
        const subcategory = MatomoUrl.parsed.value.subcategory as string;
        if (subcategory === 'Goals_AddNewGoal'
          && Matomo.helper.isAngularRenderingThePage()
        ) {
          // when adding a goal for the first time we need to load manage goals page afterwards
          ReportingMenuStore.reloadMenuItems().then(() => {
            MatomoUrl.updateHash({
              ...MatomoUrl.hashParsed.value,
              subcategory: 'Goals_ManageGoals',
            });

            this.isLoading = false;
          });
        } else {
          window.location.reload();
        }
      }).catch(() => {
        this.scrollToTop();
        this.isLoading = false;
      });
    },
    changedTriggerType() {
      if (!this.isManuallyTriggered && !this.goal.pattern_type) {
        this.goal.pattern_type = 'contains';
      }
    },
    initPatternType() {
      if (this.isMatchAttributeNumeric) {
        this.goal.pattern_type = 'greater_than';
      } else {
        this.goal.pattern_type = 'contains';
      }
    },
    lcfirst(s: string) {
      return `${s.slice(0, 1).toLowerCase()}${s.slice(1)}`;
    },
    ucfirst(s: string) {
      return `${s.slice(0, 1).toUpperCase()}${s.slice(1)}`;
    },
  },
  computed: {
    learnMoreAboutGoalTracking() {
      return translate(
        'Goals_LearnMoreAboutGoalTrackingDocumentation',
        '<a target="_blank" rel="noreferrer noopener" '
        + 'href="https://matomo.org/docs/tracking-goals-web-analytics/">',
        '</a>',
      );
    },
    youCanEnableEcommerceReports() {
      const link = MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'SitesManager',
        action: 'index',
      });

      const ecommerceReportsText = '<a href="https://matomo.org/docs/ecommerce-analytics/" '
        + `rel="noreferrer noopener" target="_blank">${translate('Goals_EcommerceReports')}</a>`;
      const websiteManageText = `<a href='${link}'>${translate('SitesManager_WebsitesManagement')}</a>`;

      return translate(
        'Goals_YouCanEnableEcommerceReports',
        ecommerceReportsText,
        websiteManageText,
      );
    },
    siteName() {
      return Matomo.helper.htmlDecode(Matomo.siteName);
    },
    whereVisitedPageManuallyCallsJsTrackerText() {
      const link = 'https://developer.matomo.org/guides/tracking-javascript-guide#manually-trigger-goal-conversions';
      return translate(
        'Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore',
        `<a target="_blank" rel="noreferrer noopener" href="${link}">`,
        '</a>',
      );
    },
    caseSensitiveTitle() {
      return `${translate('Goals_CaseSensitive')} ${translate('Goals_Optional')}`;
    },
    useEventValueAsRevenueHelp() {
      return `${translate('Goals_EventValueAsRevenueHelp')} <br/><br/> ${translate('Goals_EventValueAsRevenueHelp2')}`;
    },
    cancelText() {
      return translate(
        'General_OrCancel',
        '<a class=\'entityCancelLink\'>',
        '</a>',
      );
    },
    isMatchAttributeNumeric() {
      return ['visit_duration'].indexOf(this.goal.match_attribute) > -1;
    },
    patternFieldLabel() {
      return this.goal.match_attribute === 'visit_duration'
        ? translate('Goals_TimeInMinutes')
        : translate('Goals_Pattern');
    },
    goalMatchAttributeTranslations() {
      return {
        manually: translate('Goals_ManuallyTriggeredUsingJavascriptFunction'),
        file: translate('Goals_Download'),
        url: translate('Goals_VisitUrl'),
        title: translate('Goals_VisitPageTitle'),
        external_website: translate('Goals_ClickOutlink'),
        event_action: `${translate('Goals_SendEvent')} (${translate('Events_EventAction')})`,
        event_category: `${translate('Goals_SendEvent')} (${translate('Events_EventCategory')})`,
        event_name: `${translate('Goals_SendEvent')} (${translate('Events_EventName')})`,
        visit_duration: `${this.ucfirst(translate('Goals_VisitDuration'))}`,
      };
    },
    beforeGoalListActionsBodyComponent() {
      if (!this.beforeGoalListActionsBody) {
        return {};
      }

      const componentsByIdGoal: Record<string, unknown> = {};
      Object.values(this.goals as Record<string, Goal>).forEach((g) => {
        const template = this.beforeGoalListActionsBody![g.idgoal];
        if (!template) {
          return;
        }

        componentsByIdGoal[g.idgoal] = {
          template,
        };
      });
      return markRaw(componentsByIdGoal);
    },
    endEditTableComponent() {
      if (!this.endEditTable || !this.addEditTableComponent) {
        return null;
      }

      const endedittable = this.$refs.endedittable as HTMLElement;
      return markRaw({
        template: this.endEditTable,
        mounted() {
          Matomo.helper.compileVueEntryComponents(endedittable);
        },
        beforeUnmount() {
          Matomo.helper.destroyVueComponent(endedittable);
        },
      });
    },
    beforeGoalListActionsHeadComponent() {
      if (!this.beforeGoalListActionsHead) {
        return null;
      }

      return markRaw({
        template: this.beforeGoalListActionsHead,
      });
    },
    isManuallyTriggered() {
      return this.triggerType === 'manually';
    },
    matchesExpressionExternal() {
      const url = "'http://www.amazon.com\\/(.*)\\/yourAffiliateId'";
      return translate('Goals_MatchesExpression', url);
    },
  },
});
</script>
