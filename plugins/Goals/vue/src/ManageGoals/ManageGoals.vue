<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
X conversion check (mistakes get fixed in quickmigrate)
X property types
X state types
X look over template
X look over component code
- postEvent dynamic component check
- get to build
- test in UI
- check uses:
  ./plugins/Goals/templates/_addEditGoal.twig
  ./plugins/Goals/angularjs/manage-goals/manage-goals.directive.js
  ./plugins/MultiChannelConversionAttribution/angularjs/manage-attribution/manage-attribution.directive.js
  ./plugins/Funnels/angularjs/manage-funnel/manage-funnel.directive.js
- create PR
</todo>

<template>
  <div v-if="!onlyShowAddNewGoal">
    <div
      id='entityEditContainer'
      feature="true"
      v-show="showGoalList"
      class="managegoals"
    >
      <ContentBlock :content-title="translate('Goals_ManageGoals')">
        <ActivityIndicator :loading="isLoading"/>

        <div class="contentHelp">
          <span v-html="learnMoreAboutGoalTracking"/>
          <span v-if="!ecommerceEnabled">
            <br /><br/>

            {{ translate('Goals_Optional') }} {{ translate('Goals_Ecommerce') }}:
            <span v-html="youCanEnableEcommerceReports"/>
          </span>
        </div>

        <table v-content-table>
          <thead>
            <tr v-html="tableHeader">
            </tr>
          </thead>
          <tr v-if="!goals?.length">
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
                {{ goalMatchAttributeTranslations[goal.match_attribute] || goal.match_attribute }}
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
            <td class="center" v-html="goal.revenue === 0 ? '-' : $sanitize(goal.goalRevenuePretty)">
            </td>

            postEvent("Template.beforeGoalListActionsBody", goal) TODO

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
      <h2>{{ translate('Goals_DeleteGoalConfirm', `"${this.goal.name}"`) }}</h2>
      <input role="yes" type="button" value="{{ translate('General_Yes') }}"/>
      <input role="no" type="button" value="{{ translate('General_No') }}"/>
    </div>
  </div>

  <div v-if="userCanEditGoals">
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
              maxlength="255"
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
                  :model-value="goal.triggerType"
                  @update:model-value="goal.triggerType = $event; changedTriggerType()"
                  :full-width="true"
                  :options="goalTriggerTypeOptions"
                />
              </div>
            </div>
            <div class="col s12 m6">
              <Alert severity="info" v-show="goal.triggerType === 'manually'">
                <span v-html="whereVisitedPageManuallyCallsJsTrackerText"></span>
              </Alert>

              <div>
                <Field
                  uicontrol="radio"
                  name="match_attribute"
                  v-show="goal.triggerType !== 'manually'"
                  :full-width="true"
                  :model-value="goal.matchAttribute"
                  @update:model-value="goal.matchAttribute = $event; initPatternType()"
                  :options="goalMatchAttributeOptions"
                />
              </div>
            </div>
          </div>

          <div class="row whereTheMatchAttrbiute" v-show="goal.triggerType !== 'manually'">
            <h3 class="col s12">{{ translate('Goals_WhereThe') }}
              <span v-show="goal.matchAttribute === 'url'">
                {{ translate('Goals_URL') }}
              </span>
              <span v-show="goal.matchAttribute === 'title'">
                {{ translate('Goals_PageTitle') }}
              </span>
              <span v-show="goal.matchAttribute === 'file'">
                {{ translate('Goals_Filename') }}
              </span>
              <span v-show="goal.matchAttribute === 'external_website'">
                {{ translate('Goals_ExternalWebsiteUrl') }}
              </span>
              <span v-show="goal.matchAttribute === 'visit_duration'">
                {{ translate('Goals_VisitDuration') }}
              </span>
            </h3>
          </div>

          <div class="row" v-show="goal.triggerType !== 'manually'">
            <div class="col s12 m6 l4"
                 v-show="goal.matchAttribute === 'event'">
              <div>
                <Field
                  uicontrol="select" name="event_type"
                  v-model="goal.eventType"
                  :full-width="true"
                  options="eventTypeOptions"
                />
              </div>
            </div>

            <div class="col s12 m6 l4" v-if="!isMatchAttributeNumeric">
              <div>
                <Field
                  uicontrol="select"
                  name="pattern_type"
                  v-model="goal.patternType"
                  :full-width="true"
                  :options="patternTypeOptions"
                />
              </div>
            </div>

            <div class="col s12 m6 l4" v-if="isMatchAttributeNumeric">
              <div>
                <Field
                  uicontrol="select" name="pattern_type"
                  v-model="goal.patternType"
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
                  maxlength="255"
                  :title="patternFieldLabel"
                  :full-width="true"
                />
            </div>
          </div>

          <Alert id="examples_pattern" class="col s12" severity="info">
            <span v-show="goal.matchAttribute === 'url'">
              {{ translate('General_ForExampleShort') }} {{ translate('Goals_Contains', "'checkout/confirmation'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_IsExactly', "'http://example.com/thank-you.html'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_MatchesExpression', "'(.*)\\\/demo\\\/(.*)'") }}
            </span>
            <span v-show="goal.matchAttribute === 'title'">
              {{ translate('General_ForExampleShort') }} {{ translate('Goals_Contains', "'Order confirmation'") }}
            </span>
            <span v-show="goal.matchAttribute === 'file'">
              {{ translate('General_ForExampleShort') }} {{ translate('Goals_Contains', "'files/brochure.pdf'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_IsExactly', "'http://example.com/files/brochure.pdf'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_MatchesExpression', "'(.*)\\\.zip'") }}
            </span>
            <span v-show="goal.matchAttribute === 'external_website'">
              {{ translate('General_ForExampleShort') }} {{ translate('Goals_Contains', "'amazon.com'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_IsExactly', "'http://mypartner.com/landing.html'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_MatchesExpression', "'http://www.amazon.com\\\/(.*)\\\/yourAffiliateId'") }}
            </span>
            <span v-show="goal.matchAttribute === 'event'">
              {{ translate('General_ForExampleShort') }} {{ translate('Goals_Contains', "'video'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_IsExactly', "'click'") }}
              <br />{{ translate('General_ForExampleShort') }} {{ translate('Goals_MatchesExpression', "'(.*)_banner'") }}"
            </span>
            <span v-show="goal.matchAttribute === 'visit_duration'">
              {{ translate('General_ForExampleShort') }} {{ translate('Goals_AtLeastMinutes', '5', '0.5') }}
            </span>
          </Alert>
        </div>

        <div>
          <Field
            uicontrol="checkbox"
            name="case_sensitive"
            v-model="goal.caseSensitive"
            v-show="goal.triggerType !== 'manually' && !isMatchAttributeNumeric()"
            :title="caseSensitiveTitle"
          />
        </div>

        <div>
          <Field
            uicontrol="radio"
            name="allow_multiple"
            v-model="goal.allowMultiple"
            v-if="goal.matchAttribute !== 'visit_duration'"
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
            v-model="goal.useEventValueAsRevenue"
            :title="translate('Goals_UseEventValueAsRevenue')"
            v-show="goal.matchAttribute === 'event'"
            :inline-help="useEventValueAsRevenueHelp"
          />
        </div>

        postEvent("Template.endGoalEditTable") TODO

        <input type="hidden" name="goalIdUpdate" value=""/>

        <SaveButton
          :saving="isLoading"
          @confirm="save()"
          :value="goal.submitText"
        />

        <div v-if="!onlyShowAddNewGoal">
          <div
            class='entityCancel'
            v-show="showEditGoal"
            @click="showListOfReports()"
            v-html="cancelText"
          >
          </div>
        </div>
      </div>
    </ContentBlock>
  </div>
  </div>

  <a id='bottom'></a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  AjaxHelper,
  translate,
  ContentBlock,
  ActivityIndicator,
  MatomoUrl,
  ContentTable,
  Alert,
} from 'CoreHome';
import {
  Form,
  Field,
  SaveButton,
} from 'CorePluginsAdmin';
import Goal from '../Goal';

interface ManageGoalsState {
  showEditGoal: boolean;
  showGoalList: boolean;
  goal: Goal;
  isLoading: boolean;
}

export default defineComponent({
  props: {
    onlyShowAddNewGoal: Boolean,
    userCanEditGoals: Boolean,
    ecommerceEnabled: Boolean,
    beforeGoalListActionsHead: String,
    goals: Object,
    addNewGoalIntro: String,
    goalTriggerTypeOptions: Array,
    goalMatchAttributeOptions: Array,
    eventTypeOptions: Array,
    patternTypeOptions: Array,
    numericComparisonTypeOptions: Array,
    allowMultipleOptions: Array,
    showAddGoal: Boolean,
    showGoal: Number,
  },
  data(): ManageGoalsState {
    return {
      showEditGoal: false,
      showGoalList: true,
      goal: {} as unknown as Goal,
      isLoading: false,
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
    if (this.showAddGoal) {
      this.createGoal();
    } else if (this.showGoal) {
      this.editGoal(this.showGoal);
    }

    this.showListOfReports();
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

      this.goal = {};
      this.goal.name = goalName;
      this.goal.description = description;

      let actualMatchAttribute = matchAttribute;
      if (actualMatchAttribute == 'manually') {
        this.goal.triggerType = 'manually';
        actualMatchAttribute = 'url';
      } else {
        this.goal.triggerType = 'visitors';
      }

      if (0 === actualMatchAttribute.indexOf('event')) {
        this.goal.eventType = actualMatchAttribute;
        actualMatchAttribute = 'event';
      } else {
        this.goal.eventType = 'event_category';
      }

      this.goal.matchAttribute = actualMatchAttribute;
      this.goal.allowMultiple = allowMultiple == true ? "1" : "0";
      this.goal.patternType = patternType;
      this.goal.pattern = pattern;
      this.goal.caseSensitive = caseSensitive;
      this.goal.revenue = revenue;
      this.goal.apiMethod = goalMethodAPI;
      this.goal.useEventValueAsRevenue = useEventValueAsRevenue;
      this.goal.submitText = submitText;
      this.goal.goalId = goalId;
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
    // TODO
    createGoal() {
      const parameters = {
        isAllowed: true
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
        '',
        false,
        false,
        0,
      );
      this.scrollToTop();
    },
    editGoal(goalId: string|number) {
      this.showAddEditForm();
      const goal = Matomo.goals[`${goalId}`];
      this.initGoalForm(
        "Goals.updateGoal",
        translate('Goals_UpdateGoal'),
        goal.name,
        goal.description,
        goal.match_attribute,
        goal.pattern,
        goal.pattern_type,
        !!goal.case_sensitive && goal.case_sensitive !== '0',
        goal.revenue,
        goal.allow_multiple,
        !!goal.event_value_as_revenue && goal.event_value_as_revenue !== '0',
        goalId,
      );
      this.scrollToTop();
    },
    deleteGoal(goalId: string|number) {
      Matomo.helper.modalConfirm((this.$refs.confirm as HTMLElement), {
        yes: () => {
          this.isLoading = true;

          AjaxHelper.fetch({
            idGoal: goalId,
            method: 'Goals.deleteGoal',
          }).then(() => {
            location.reload();
          }).finally(() => {
            this.isLoading = false;
          });
        },
      });
    },
    lcfirst(s: string) {
      return `${s.substr(0, 1).toLowerCase()}${s.substr(1)}`;
    },
    ucfirst(s: string) {
      return `${s.substr(0, 1).toUpperCase()}${s.substr(1)}`;
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
    // can't do this in vue because of the Template.beforeGoalListActionsHead twig event
    // TODO: note that angularjs used inside Template.before*** will not work
    tableHeader() {
      const lines = [
        `<th>${translate('Goals_GoalName')}</th>`,
        `<th>${translate('General_Description')}</th>`,
        `<th>${translate('Goals_GoalIsTriggeredWhen')}</th>`,
        `<th>${translate('General_ColumnRevenue')}</th>`,
        this.beforeGoalListActionsHead,
        `<th v-if="userCanEditGoals">${translate('General_Edit')}</th>`,
        `<th v-if="userCanEditGoals">${translate('General_Delete')}</th>`,
      ]
      return lines.join('');
    },
    siteName() {
      // translate was called in original twig template
      return translate(Matomo.helper.htmlDecode(Matomo.siteName));
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
      return ['visit_duration'].indexOf(this.goal.matchAttribute) > -1;
    },
    patternFieldLabel() {
      return this.goal.matchAttribute === 'visit_duration'
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
  },
});
</script>
