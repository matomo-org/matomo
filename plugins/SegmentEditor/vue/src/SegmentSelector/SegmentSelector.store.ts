/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  computed,
  DeepReadonly,
  reactive,
  readonly,
} from 'vue';
import {
  SavedSegment,
  SegmentSelectorEntry,
  SegmentSelectorTranslations,
  SegmentSelectorUserContext,
  SegmentSelectorViewModel,
} from '../types';
import {
  getCanUserEditSegment,
  getDeleteSegmentTitle,
  getEditSegmentTitle,
  getStarSegmentTitle,
} from './SegmentSelector.helpers';

interface SegmentSelectorStoreConfig {
  availableSegments: SavedSegment[];
  currentSegment: string;
  isUserAnonymous: boolean;
  loginUrl: string;
  manageSegmentsUrl: string;
  segmentAccess: string;
  translations: SegmentSelectorTranslations;
  userContext: SegmentSelectorUserContext;
}

interface SegmentSelectorStoreState {
  availableSegments: SavedSegment[];
  currentSegment: string;
  isUserAnonymous: boolean;
  isInitialized: boolean;
  loginUrl: string;
  manageSegmentsUrl: string;
  panelExpanded: boolean;
  renderVersion: number;
  segmentAccess: string;
  translations: SegmentSelectorTranslations;
  userContext: SegmentSelectorUserContext;
}

declare const piwikHelper: {
  escape: (value: string) => string;
  htmlDecode: (value: string) => string;
  normalize: (value: string) => string;
};

declare const piwik: {
  config: {
    data_comparison_segment_limit: number;
  };
};

declare const ajaxHelper: new () => {
  addParams: (params: Record<string, unknown>, method: string) => void;
  send: () => void;
  setCallback: (
    callback: (response: {
      result?: string;
      starred?: string | number | boolean;
      starred_by?: string | null;
    }) => void,
  ) => void;
  useCallbackInCaseOfError: () => void;
};

// eslint-disable-next-line no-underscore-dangle
declare const _pk_translate: (key: string, values?: string[] | number[]) => string;

declare global {
  interface Window {
    CoreHome: {
      ComparisonsStoreInstance: {
        addSegmentComparison: (comparison: { segment: string }) => void;
        getSegmentComparisons: () => Array<{ params: { segment: string } }>;
        isComparisonEnabled: () => boolean | null;
        removeSegmentComparisonByDefinition: (definition: string) => void;
      };
    };
  }
}

class SegmentSelectorStore {
  private privateState: SegmentSelectorStoreState = reactive<SegmentSelectorStoreState>({
    availableSegments: [],
    currentSegment: '',
    isUserAnonymous: false,
    isInitialized: false,
    loginUrl: '',
    manageSegmentsUrl: '',
    panelExpanded: false,
    renderVersion: 0,
    segmentAccess: 'read',
    translations: {},
    userContext: {
      isAnonymous: false,
      hasSuperUserAccess: false,
      login: '',
    },
  });

  readonly state = computed(() => readonly(this.privateState));

  private starChangeCallbacks: Array<(segment: SavedSegment, isError?: boolean) => void> = [];

  init(config: SegmentSelectorStoreConfig) {
    this.privateState.availableSegments = config.availableSegments;
    this.privateState.currentSegment = config.currentSegment || '';
    this.privateState.isUserAnonymous = config.isUserAnonymous;
    this.privateState.isInitialized = true;
    this.privateState.loginUrl = config.loginUrl;
    this.privateState.manageSegmentsUrl = config.manageSegmentsUrl;
    this.privateState.segmentAccess = config.segmentAccess;
    this.privateState.translations = config.translations;
    this.privateState.userContext = config.userContext;
    this.privateState.renderVersion += 1;
  }

  onStarChange(callback: (segment: SavedSegment, isError?: boolean) => void) {
    this.starChangeCallbacks.push(callback);

    let isUnsubscribed = false;
    return () => {
      if (isUnsubscribed) {
        return;
      }

      isUnsubscribed = true;
      const index = this.starChangeCallbacks.indexOf(callback);
      if (index !== -1) {
        this.starChangeCallbacks.splice(index, 1);
      }
    };
  }

  notifyChange() {
    this.privateState.renderVersion += 1;
  }

  setAvailableSegments(segments: SavedSegment[]) {
    this.privateState.availableSegments = segments;
    this.notifyChange();
  }

  setCurrentSegment(segment: string) {
    this.privateState.currentSegment = segment || '';
    this.notifyChange();
  }

  getCurrentSegment() {
    return this.privateState.currentSegment;
  }

  setPanelExpanded(isExpanded: boolean) {
    this.privateState.panelExpanded = isExpanded;
    this.notifyChange();
  }

  getPanelExpanded() {
    return this.privateState.panelExpanded;
  }

  getSegmentAccess() {
    return this.privateState.segmentAccess;
  }

  getTranslations() {
    return this.privateState.translations;
  }

  getUserContext() {
    return this.privateState.userContext;
  }

  normalizeStarredState(starred: SavedSegment['starred']) {
    if (typeof starred === 'boolean') {
      return starred;
    }

    if (typeof starred === 'number') {
      return starred !== 0;
    }

    if (typeof starred === 'string') {
      return starred === '1' || starred.toLowerCase() === 'true';
    }

    return false;
  }

  getSegmentFromId(idSegment?: string | number | null) {
    if (typeof idSegment === 'undefined' || idSegment === null || idSegment === '') {
      return null;
    }

    return this.privateState.availableSegments.find((segment) => `${segment.idsegment}` === `${idSegment}`) || null;
  }

  private decodeDefinition(definition: string) {
    const candidates = [definition];

    try {
      candidates.push(piwikHelper.htmlDecode(definition));
    } catch (e) {
      // Ignore decode failures and keep original value.
    }

    try {
      candidates.push(piwikHelper.htmlDecode(decodeURIComponent(definition)));
    } catch (e) {
      // Ignore decode failures and keep original value.
    }

    return candidates.filter((candidate, index, values) => (
      typeof candidate !== 'undefined' && values.indexOf(candidate) === index
    ));
  }

  private getSegmentByDefinition(definition: string) {
    const candidates = this.decodeDefinition(definition);
    return this.privateState.availableSegments.find((segment) => (
      candidates.indexOf(segment.definition) !== -1
    )) || null;
  }

  private getPlainSegmentName(segment: SavedSegment) {
    return piwikHelper.htmlDecode(segment.name);
  }

  private getSegmentTooltipText(segment: SavedSegment) {
    let segmentName = piwikHelper.htmlDecode(segment.name);
    const { userContext } = this.privateState;

    if (userContext.hasSuperUserAccess && segment.login !== userContext.login) {
      segmentName += ' (';
      segmentName += _pk_translate('General_CreatedByUser', [segment.login || '']);

      if (Number(segment.enable_all_users) === 0) {
        segmentName += `, ${_pk_translate('SegmentEditor_VisibleToSuperUser')}`;
      }

      segmentName += ')';
    }

    return segmentName;
  }

  private isSegmentVisibleToSuperUserOnly(segment: SavedSegment) {
    const { userContext } = this.privateState;
    return userContext.hasSuperUserAccess
      && segment.login !== userContext.login
      && Number(segment.enable_all_users) === 0;
  }

  private isSegmentSharedWithMeBySuperUser(segment: SavedSegment) {
    const { userContext } = this.privateState;
    return segment.login !== userContext.login
      && Number(segment.enable_all_users) === 1;
  }

  private getCurrentSegmentTitle() {
    const current = this.getCurrentSegment();

    if (current !== '') {
      const segment = this.getSegmentByDefinition(current);
      if (segment) {
        return this.getPlainSegmentName(segment);
      }
      return _pk_translate('SegmentEditor_CustomSegment');
    }

    return this.privateState.translations.SegmentEditor_DefaultAllVisits;
  }

  private getCurrentSegmentTooltip() {
    let title = `${_pk_translate('SegmentEditor_ChooseASegment')}.`;
    title += ` ${_pk_translate('SegmentEditor_CurrentlySelectedSegment', [this.getCurrentSegmentTitle()])}`;
    return title;
  }

  private getComparedSegmentDefinitions() {
    return window.CoreHome.ComparisonsStoreInstance.getSegmentComparisons().map(
      (comparison) => comparison.params.segment,
    );
  }

  private getComparisonLimit() {
    return piwik.config.data_comparison_segment_limit + 1;
  }

  private isComparisonAvailable() {
    const comparisonService = window.CoreHome.ComparisonsStoreInstance;
    const isEnabled = comparisonService.isComparisonEnabled();
    return isEnabled || isEnabled === null;
  }

  private isSegmentSelected(definition: string) {
    return definition === this.privateState.currentSegment
      || definition === decodeURIComponent(this.privateState.currentSegment);
  }

  private isSegmentCompared(definition: string, comparedSegments: string[]) {
    return comparedSegments.indexOf(definition) !== -1
      || comparedSegments.indexOf(decodeURIComponent(definition)) !== -1;
  }

  private buildCompareState(definition: string, comparedSegments: string[]) {
    if (this.isSegmentCompared(definition, comparedSegments)) {
      return {
        state: 'active',
        title: _pk_translate('SegmentEditor_CompareThisSegment'),
      };
    }

    if (comparedSegments.length >= this.getComparisonLimit()) {
      return {
        state: 'disabled',
        title: _pk_translate('General_MaximumNumberOfSegmentsComparedIs', [this.getComparisonLimit()]),
      };
    }

    return {
      state: '',
      title: _pk_translate('SegmentEditor_CompareThisSegment'),
    };
  }

  getCanUserEditSegment(segment: SavedSegment | null | undefined) {
    return getCanUserEditSegment(
      segment,
      this.privateState.segmentAccess,
      this.privateState.userContext,
    );
  }

  getEditSegmentTitle(segment: SavedSegment, canEdit: boolean) {
    return getEditSegmentTitle(segment, canEdit, this.privateState.translations);
  }

  getDeleteSegmentTitle(segment: SavedSegment, canEdit: boolean) {
    return getDeleteSegmentTitle(segment, canEdit, this.privateState.translations);
  }

  getStarSegmentTitle(segment: SavedSegment, canEdit: boolean) {
    return getStarSegmentTitle(
      segment,
      canEdit,
      this.privateState.translations,
      this.privateState.userContext,
    );
  }

  toggleStarredSegmentById(idSegment?: string | number | null) {
    const segment = this.getSegmentFromId(idSegment);
    if (!segment) {
      return;
    }

    segment.starred = !this.normalizeStarredState(segment.starred);
    const method = segment.starred ? 'star' : 'unstar';
    this.notifyStarredSegment(segment);

    const LegacyAjaxHelper = ajaxHelper;
    const ajaxHandler = new LegacyAjaxHelper();
    ajaxHandler.addParams({
      module: 'API',
      format: 'json',
      method: `SegmentEditor.${method}`,
      userLogin: this.privateState.userContext.login,
      idSegment: idSegment || '',
    }, 'POST');
    ajaxHandler.useCallbackInCaseOfError();
    ajaxHandler.setCallback((response) => {
      if (!response || response.result === 'error') {
        segment.starred = !this.normalizeStarredState(segment.starred);
        this.notifyStarredSegment(segment, true);
        return;
      }

      segment.starred = this.normalizeStarredState(response.starred);
      segment.starred_by = response.starred_by;
      this.notifyStarredSegment(segment);
    });
    ajaxHandler.send();
  }

  private notifyStarredSegment(segment: SavedSegment, isError = false) {
    this.notifyChange();
    this.starChangeCallbacks.forEach((callback) => {
      callback(segment, isError);
    });
  }

  getSelectorViewModel(searchValue: string) {
    const { renderVersion } = this.privateState;
    if (renderVersion < 0) {
      throw new Error('Segment selector render version must not be negative');
    }

    const comparedSegments = this.getComparedSegmentDefinitions();
    const normalizedSearch = (searchValue || '').length >= 2 ? piwikHelper.normalize(searchValue) : '';
    const lowerSearch = (searchValue || '').toLowerCase();
    const entries: SegmentSelectorEntry[] = [];

    const matchesSearch = (text: string) => {
      if (!normalizedSearch.length) {
        return true;
      }

      const normalizedText = piwikHelper.normalize(text);
      const lowerText = text.toLowerCase();

      return normalizedText.indexOf(normalizedSearch) !== -1
        || lowerText.indexOf(lowerSearch) !== -1;
    };

    const allVisitsCompareState = this.buildCompareState('', comparedSegments);
    const allVisitsLabel = [
      this.privateState.translations.SegmentEditor_DefaultAllVisits,
      this.privateState.translations.General_DefaultAppended,
    ].join(' ');

    if (matchesSearch(allVisitsLabel)) {
      entries.push({
        key: 'segment-all-visits',
        type: 'segment',
        classes: [
          this.privateState.currentSegment === '' ? 'segmentSelected' : '',
          this.isSegmentCompared('', comparedSegments) ? 'comparedSegment' : '',
        ].join(' ').trim(),
        idsegment: '',
        definition: '',
        label: allVisitsLabel,
        tooltip: allVisitsLabel,
        showStarButton: false,
        showEditButton: false,
        showCompareButton: this.isComparisonAvailable(),
        compareButtonClass: [
          'segmentAction compareSegment allVisitsCompareSegment',
          this.privateState.segmentAccess === 'write' ? 'allVisitsCompareSegment--write' : '',
        ].join(' ').trim(),
        compareTitle: allVisitsCompareState.title,
        compareState: allVisitsCompareState.state,
      });
    }

    let hasSharedHeader = false;
    let hasSuperUserHeader = false;

    this.privateState.availableSegments.forEach((segment) => {
      segment.starred = this.normalizeStarredState(segment.starred);

      const labelText = this.getPlainSegmentName(segment);
      const tooltipText = this.getSegmentTooltipText(segment);
      if (!matchesSearch(tooltipText)) {
        return;
      }

      if (this.isSegmentSharedWithMeBySuperUser(segment) && !hasSharedHeader) {
        hasSharedHeader = true;
        entries.push({
          key: 'header-shared-with-you',
          type: 'header',
          className: 'segmentsSharedWithMeBySuperUser',
          label: _pk_translate('SegmentEditor_SharedWithYou'),
          tooltip: '',
        });
      }

      if (this.isSegmentVisibleToSuperUserOnly(segment) && !hasSuperUserHeader) {
        hasSuperUserHeader = true;
        entries.push({
          key: 'header-visible-to-super-user',
          type: 'header',
          className: 'segmentsVisibleToSuperUser',
          label: _pk_translate('SegmentEditor_VisibleToSuperUser'),
          tooltip: '',
        });
      }

      const canEdit = this.getCanUserEditSegment(segment);
      const compareState = this.buildCompareState(segment.definition, comparedSegments);
      const classes = [];

      if (this.isSegmentSelected(segment.definition)) {
        classes.push('segmentSelected');
      }
      if (segment.starred) {
        classes.push('segmentStarred');
      }
      if (this.isSegmentCompared(segment.definition, comparedSegments)) {
        classes.push('comparedSegment');
      }

      entries.push({
        key: `segment-${segment.idsegment}`,
        type: 'segment',
        classes: classes.join(' '),
        idsegment: `${segment.idsegment || ''}`,
        definition: segment.definition,
        label: labelText,
        tooltip: tooltipText,
        showStarButton: true,
        starTitle: this.getStarSegmentTitle(segment, canEdit),
        starState: canEdit ? '' : 'disabled',
        showEditButton: this.privateState.segmentAccess === 'write',
        editTitle: this.getEditSegmentTitle(segment, canEdit),
        editState: canEdit ? '' : 'disabled',
        showCompareButton: this.isComparisonAvailable(),
        compareButtonClass: 'segmentAction compareSegment',
        compareTitle: compareState.title,
        compareState: compareState.state,
      });
    });

    if ((searchValue || '').length >= 2
      && entries.filter((entry) => entry.type === 'segment').length === 0
    ) {
      entries.push({
        key: 'no-results',
        type: 'no-results',
        classes: 'filterNoResults grayed',
        idsegment: '',
        definition: '',
        label: this.privateState.translations.General_SearchNoResults,
        tooltip: this.privateState.translations.General_SearchNoResults,
        showStarButton: false,
        showEditButton: false,
        showCompareButton: false,
      });
    }

    return {
      authorizedToCreateSegments: this.privateState.segmentAccess === 'write',
      currentSegmentTitle: this.getCurrentSegmentTitle(),
      currentSegmentTooltip: this.getCurrentSegmentTooltip(),
      currentSegmentValue: this.privateState.currentSegment,
      entries,
      isExpanded: this.privateState.panelExpanded,
      isUserAnonymous: !!this.privateState.isUserAnonymous,
      loginUrl: this.privateState.loginUrl,
      manageSegmentsUrl: this.privateState.manageSegmentsUrl,
    } as DeepReadonly<SegmentSelectorViewModel>;
  }
}

export default new SegmentSelectorStore();
