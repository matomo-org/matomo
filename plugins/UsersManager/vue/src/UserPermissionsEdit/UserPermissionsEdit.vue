<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="userPermissionsEdit"
    :class="{ loading: isLoadingAccess }"
  >
    <div
      class="row"
      v-if="!hasAccessToAtLeastOneSite"
    >
      <div>
        <Notification
          context="warning"
          type="transient"
          :noclear="true"
        >
          <strong>{{ translate('General_Warning') }}:</strong>
          {{ translate('UsersManager_NoAccessWarning') }}
        </Notification>
      </div>
    </div>
    <div class="row to-all-websites">
      <div class="col s12">
        <div>
          <span style="margin-right:3.5px">{{ translate('UsersManager_GiveAccessToAll') }}:</span>
          <div
            id="all-sites-access-select"
            style="margin-right:3.5px"
          >
            <Field
              v-model="allWebsitesAccssLevelSet"
              uicontrol="select"
              :options="filteredAccessLevels"
              :full-width="true"
            />
          </div>
          <a
            href=""
            class="btn"
            :class="{ disabled: isGivingAccessToAllSites }"
            @click.prevent="showChangeAccessAllSitesModal()"
          >
            {{ translate('General_Apply') }}
          </a>
        </div>
        <p style="margin-top:18px">{{ translate('UsersManager_OrManageIndividually') }}:</p>
      </div>
    </div>
    <div class="filters row">
      <div class="col s12 m12 l8">
        <div class="input-field bulk-actions" style="margin-right:3.5px">
          <a
            class="dropdown-trigger btn"
            href=""
            :class="{ disabled: isBulkActionsDisabled }"
            data-target="user-permissions-edit-bulk-actions"
            v-dropdown-menu="{ activates: '#user-permissions-edit-bulk-actions' }"
          >
            {{ translate('UsersManager_BulkActions') }}
          </a>
          <ul
            id="user-permissions-edit-bulk-actions"
            class="dropdown-content"
          >
            <li>
              <a
                class="dropdown-trigger"
                data-target="user-permissions-bulk-set-access"
                v-dropdown-menu="{ activates: '#user-permissions-bulk-set-access' }"
              >{{ translate('UsersManager_SetPermission') }}</a>
              <ul
                id="user-permissions-bulk-set-access"
                class="dropdown-content"
              >
                <li v-for="access in filteredAccessLevels" :key="access.key">
                  <a
                    href=""
                    @click.prevent="
                      siteAccessToChange = null;
                      roleToChangeTo = access.key;
                      showChangeAccessConfirm();"
                  >{{ access.value }}</a>
                </li>
              </ul>
            </li>
            <li>
              <a
                href=""
                @click.prevent="
                  siteAccessToChange = null;
                  roleToChangeTo = 'noaccess';
                  showRemoveAccessConfirm();"
              >{{ translate('UsersManager_RemovePermissions') }}</a>
            </li>
          </ul>
        </div>
        <div class="input-field site-filter" style="margin-right:3.5px">
          <input
            type="text"
            :value="siteNameFilter"
            @keydown="onChangeSiteFilter($event);"
            @change="onChangeSiteFilter($event);"
            :placeholder="translate('UsersManager_FilterByWebsite')"
          />
        </div>
        <div class="input-field access-filter" style="margin-right:3.5px">
          <div>
            <Field
              v-model="accessLevelFilter"
              uicontrol="select"
              :options="filteredSelectAccessLevels"
              :full-width="true"
              :placeholder="translate('UsersManager_FilterByAccess')"
            />
          </div>
        </div>
      </div>
      <div
        class="col s12 m12 l4 sites-for-permission-pagination-container"
        v-if="totalEntries > limit"
      >
        <div class="sites-for-permission-pagination">
          <a
            class="prev"
            :class="{ disabled: offset <= 0 }"
          >
            <span
              class="pointer"
              @click="gotoPreviousPage()"
            >&#xAB; {{ translate('General_Previous') }}</span>
          </a>
          <span class="counter">
            <span v-text="paginationText"></span>
          </span>
          <a
            class="next"
            :class="{ disabled: offset + limit >= totalEntries }"
          >
            <span
              class="pointer"
              @click="gotoNextPage()"
            >{{ translate('General_Next') }} &#xBB;</span>
          </a>
        </div>
      </div>
    </div>
    <div
      class="roles-help-notification"
    >
      <Notification
        v-if="isRoleHelpToggled"
        context="info"
        type="persistent"
        :noclear="true"
      >
        <span v-html="$sanitize(rolesHelpText)"></span>
      </Notification>
    </div>
    <div
      class="capabilities-help-notification"
    >
      <Notification
        v-if="isCapabilitiesHelpToggled"
        context="info"
        type="persistent"
        :noclear="true"
      >
        <span>
          {{ translate('UsersManager_CapabilitiesHelp') }}
        </span>
      </Notification>
    </div>
    <table
      id="sitesForPermission"
      v-content-table
    >
      <thead>
        <tr>
          <th class="select-cell">
            <span class="checkbox-container">
              <label>
                <input
                  type="checkbox"
                  id="perm_edit_select_all"
                  :checked="isAllCheckboxSelected"
                  @change="onAllCheckboxChange($event)"
                />
                <span />
              </label>
            </span>
          </th>
          <th>{{ translate('General_Name') }}</th>
          <th class="role_header">
            <span v-html="$sanitize(`${translate('UsersManager_Role')} `)"></span>
            <a
              href=""
              class="helpIcon"
              @click.prevent="isRoleHelpToggled = !isRoleHelpToggled"
              :class="{ sticky: isRoleHelpToggled }"
            >
              <span class="icon-help" />
            </a>
          </th>
          <th class="capabilities_header">
            <span v-html="$sanitize(`${translate('UsersManager_Capabilities')} `)"></span>
            <a
              href=""
              class="helpIcon"
              @click.prevent="isCapabilitiesHelpToggled = !isCapabilitiesHelpToggled"
              :class="{ sticky: isCapabilitiesHelpToggled }"
            >
              <span class="icon-help" />
            </a>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          class="select-all-row"
          v-if="isAllCheckboxSelected && siteAccess.length < totalEntries"
        >
          <td colspan="4">
            <div v-if="!areAllResultsSelected">
              <span
                v-html="$sanitize(theDisplayedWebsitesAreSelectedText)"
                style="margin-right:3.5px"
              ></span>
              <a
                href="#"
                @click.prevent="areAllResultsSelected = !areAllResultsSelected"
                v-html="$sanitize(clickToSelectAllText)"
              ></a>
            </div>
            <div v-if="areAllResultsSelected">
              <span
                v-html="$sanitize(allWebsitesAreSelectedText)"
                style="margin-right:3.5px"
              ></span>
              <a
                href="#"
                @click.prevent="areAllResultsSelected = !areAllResultsSelected"
                v-html="$sanitize(clickToSelectDisplayedWebsitesText)"
              ></a>
            </div>
          </td>
        </tr>
        <tr v-for="(entry, index) in siteAccess" :key="entry.idsite">
          <td class="select-cell">
            <span class="checkbox-container">
              <label>
                <input
                  type="checkbox"
                  :id="`perm_edit_select_row${index}`"
                  v-model="selectedRows[index]"
                  @click="onRowSelected()"
                />
                <span />
              </label>
            </span>
          </td>
          <td>
            <span>{{ entry.site_name }}</span>
          </td>
          <td>
            <div
              class="role-select"
            >
            <Field
              :model-value="entry.role"
              @update:model-value="onRoleChange(entry, $event);"
              uicontrol="select"
              :options="filteredAccessLevels"
              :full-width="true"
            />
            </div>
          </td>
          <td>
            <div>
              <CapabilitiesEdit
                :idsite="entry.idsite"
                :site-name="entry.site_name"
                :user-login="userLogin"
                :user-role="entry.role"
                :capabilities="entry.capabilities"
                @change="fetchAccess()"
              >
              </CapabilitiesEdit>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="delete-access-confirm-modal modal" ref="deleteAccessConfirmModal">
      <div class="modal-content">
        <h3
          v-if="siteAccessToChange"
          v-html="$sanitize(deletePermConfirmSingleText)"
        ></h3>
        <p
          v-if="!siteAccessToChange"
          v-html="$sanitize(deletePermConfirmMultipleText)"
        ></p>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          @click.prevent="changeUserRole()"
          style="margin-right:3.5px"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
          @click.prevent="siteAccessToChange = null; roleToChangeTo = null;"
        >{{ translate('General_No') }}</a>
      </div>
    </div>
    <div class="change-access-confirm-modal modal" ref="changeAccessConfirmModal">
      <div class="modal-content">
        <h3
          v-if="siteAccessToChange"
          v-html="$sanitize(changePermToSiteConfirmSingleText)"
        ></h3>
        <p
          v-if="!siteAccessToChange"
          v-html="$sanitize(changePermToSiteConfirmMultipleText)"
        ></p>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          @click.prevent="changeUserRole()"
          style="margin-right:3.5px"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
          @click.prevent="
            siteAccessToChange.role = previousRole;
            siteAccessToChange = null;
            roleToChangeTo = null;"
        >{{ translate('General_No') }}</a>
      </div>
    </div>
    <div class="confirm-give-access-all-sites modal" ref="confirmGiveAccessAllSitesModal">
      <div class="modal-content">
        <h3 v-html="$sanitize(changePermToAllSitesConfirmText)"></h3>
        <p>{{ translate('UsersManager_ChangePermToAllSitesConfirm2') }}</p>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          @click.prevent="giveAccessToAllSites()"
          style="margin-right:3.5px"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
          @click="$event.preventDefault()"
        >{{ translate('General_No') }}</a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import {
  Notification,
  DropdownMenu,
  ContentTable,
  debounce,
  translate,
  AjaxHelper,
  Site,
  Matomo,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import CapabilitiesEdit from '../CapabilitiesEdit/CapabilitiesEdit.vue';
import Capability from '../CapabilitiesStore/Capability';

interface SiteAccess {
  idsite: string|number;
  site_name: string;
  role: string;
  capabilities: Capability[];
}

interface AccessLevel {
  key: string|number;
  value: unknown;
  type: string;
}

interface UserPermissionsEditState {
  siteAccess: SiteAccess[];
  offset: number;
  totalEntries: number|null;
  accessLevelFilter: string;
  siteNameFilter: string;
  isLoadingAccess: boolean;
  allWebsitesAccssLevelSet: string;
  isAllCheckboxSelected: boolean;
  selectedRows: Record<string, boolean>;
  isBulkActionsDisabled: boolean;
  areAllResultsSelected: boolean;
  previousRole: string|null;
  hasAccessToAtLeastOneSite: boolean;
  isRoleHelpToggled: boolean;
  isCapabilitiesHelpToggled: boolean;
  isGivingAccessToAllSites: boolean;
  roleToChangeTo: string|null;
  siteAccessToChange: SiteAccess|null;
}

const { $ } = window;

export default defineComponent({
  props: {
    userLogin: {
      type: String,
      required: true,
    },
    limit: {
      type: Number,
      default: 10,
    },
    accessLevels: {
      type: Array,
      required: true,
    },
    filterAccessLevels: {
      type: Array,
      required: true,
    },
  },
  components: {
    Notification,
    Field,
    CapabilitiesEdit,
  },
  directives: {
    DropdownMenu,
    ContentTable,
  },
  data(): UserPermissionsEditState {
    return {
      siteAccess: [],
      offset: 0,
      totalEntries: null,
      accessLevelFilter: '',
      siteNameFilter: '',
      isLoadingAccess: false,
      allWebsitesAccssLevelSet: 'view',
      isAllCheckboxSelected: false,
      selectedRows: {},
      isBulkActionsDisabled: true,
      areAllResultsSelected: false,
      previousRole: null,
      hasAccessToAtLeastOneSite: true,
      isRoleHelpToggled: false,
      isCapabilitiesHelpToggled: false,
      isGivingAccessToAllSites: false,
      roleToChangeTo: null,
      siteAccessToChange: null,
    };
  },
  emits: ['userHasAccessDetected', 'accessChanged'],
  created() {
    this.onChangeSiteFilter = debounce(this.onChangeSiteFilter, 300);

    watch(
      () => this.allPropsWatch,
      () => {
        if (this.limit) {
          this.fetchAccess();
        }
      },
    );

    this.fetchAccess();
  },
  watch: {
    accessLevelFilter() {
      this.offset = 0;
      this.fetchAccess();
    },
  },
  methods: {
    onAllCheckboxChange(event: Event) {
      this.isAllCheckboxSelected = (event.target as HTMLInputElement).checked;
      if (!this.isAllCheckboxSelected) {
        this.clearSelection();
      } else {
        this.siteAccess.forEach((e, i) => {
          this.selectedRows[i] = true;
        });
        this.isBulkActionsDisabled = false;
      }
    },
    clearSelection() {
      this.selectedRows = {};
      this.areAllResultsSelected = false;
      this.isBulkActionsDisabled = true;
      this.isAllCheckboxSelected = false;
      this.siteAccessToChange = null;
    },
    onRowSelected() {
      setTimeout(() => {
        const selectedRowKeyCount = this.selectedRowsCount;
        this.isBulkActionsDisabled = selectedRowKeyCount === 0;
        this.isAllCheckboxSelected = selectedRowKeyCount === this.siteAccess.length;
      });
    },
    fetchAccess() {
      this.isLoadingAccess = true;
      return AjaxHelper.fetch<AjaxHelper>(
        {
          method: 'UsersManager.getSitesAccessForUser',
          limit: this.limit,
          offset: this.offset,
          filter_search: this.siteNameFilter,
          filter_access: this.accessLevelFilter,
          userLogin: this.userLogin,
        },
        { returnResponseObject: true },
      ).then((helper) => {
        const result = helper.getRequestHandle()!;

        this.isLoadingAccess = false;
        this.siteAccess = result.responseJSON as SiteAccess[];
        this.totalEntries = parseInt(result.getResponseHeader('x-matomo-total-results')!, 10) || 0;
        this.hasAccessToAtLeastOneSite = !!result.getResponseHeader('x-matomo-has-some');

        this.$emit('userHasAccessDetected', { hasAccess: this.hasAccessToAtLeastOneSite });

        this.clearSelection();
      }).catch(() => {
        this.isLoadingAccess = false;

        this.clearSelection();
      });
    },
    gotoPreviousPage() {
      this.offset = Math.max(0, this.offset - this.limit);

      this.fetchAccess();
    },
    gotoNextPage() {
      const newOffset = this.offset + this.limit;
      if (newOffset >= (this.totalEntries || 0)) {
        return;
      }

      this.offset = newOffset;
      this.fetchAccess();
    },
    showRemoveAccessConfirm() {
      $(this.$refs.deleteAccessConfirmModal as HTMLElement).modal({
        dismissible: false,
      }).modal('open');
    },
    changeUserRole() {
      const getSelectedSites = () => {
        const result: (string|number)[] = [];
        Object.keys(this.selectedRows).forEach((index) => {
          if (this.selectedRows[index]
            && this.siteAccess[index as unknown as number] // safety check
          ) {
            result.push(this.siteAccess[index as unknown as number].idsite);
          }
        });
        return result;
      };

      const getAllSitesInSearch = () => (
        AjaxHelper.fetch<{ idsite: string }[]>(
          {
            method: 'UsersManager.getSitesAccessForUser',
            filter_search: this.siteNameFilter,
            filter_access: this.accessLevelFilter,
            userLogin: this.userLogin,
            filter_limit: '-1',
          },
        ).then((access) => access.map((a) => a.idsite))
      );

      this.isLoadingAccess = true;

      return Promise.resolve().then(() => {
        if (this.siteAccessToChange) {
          return [this.siteAccessToChange.idsite];
        }

        if (this.areAllResultsSelected) {
          return getAllSitesInSearch();
        }

        return getSelectedSites();
      }).then((idSites) => AjaxHelper.post(
        {
          method: 'UsersManager.setUserAccess',
        }, {
          userLogin: this.userLogin,
          access: this.roleToChangeTo,
          idSites,
        },
      )).catch(() => {
        // ignore (errors will still be displayed to the user)
      }).then(() => { // eslint-disable-line
        this.$emit('accessChanged');

        return this.fetchAccess();
      });
    },
    showChangeAccessConfirm() {
      $(this.$refs.changeAccessConfirmModal as HTMLElement).modal({
        dismissible: false,
      }).modal('open');
    },
    getRoleDisplay(role: string|null) {
      let result = null;
      (this.filteredAccessLevels as AccessLevel[]).forEach((entry) => {
        if (entry.key === role) {
          result = entry.value;
        }
      });
      return result;
    },
    giveAccessToAllSites() {
      this.isGivingAccessToAllSites = true;
      AjaxHelper.fetch<Site[]>({
        method: 'SitesManager.getSitesWithAdminAccess',
        filter_limit: -1,
      }).then((allSites) => {
        const idSites = allSites.map((s) => s.idsite);
        return AjaxHelper.post(
          {
            method: 'UsersManager.setUserAccess',
          }, {
            userLogin: this.userLogin,
            access: this.allWebsitesAccssLevelSet,
            idSites,
          },
        );
      }).then(() => this.fetchAccess()).finally(() => {
        this.isGivingAccessToAllSites = false;
      });
    },
    showChangeAccessAllSitesModal() {
      $(this.$refs.confirmGiveAccessAllSitesModal as HTMLElement).modal({
        dismissible: false,
      }).modal('open');
    },
    onChangeSiteFilter(event: KeyboardEvent) {
      setTimeout(() => {
        const inputValue = (event.target as HTMLInputElement).value;
        if (this.siteNameFilter !== inputValue) {
          this.siteNameFilter = inputValue;
          this.offset = 0;
          this.fetchAccess();
        }
      });
    },
    onRoleChange(entry: SiteAccess, newRole: string) {
      this.previousRole = entry.role;
      this.roleToChangeTo = newRole;
      this.siteAccessToChange = entry;
      this.showChangeAccessConfirm();
    },
  },
  computed: {
    rolesHelpText() {
      return translate(
        'UsersManager_RolesHelp',
        '<a href="https://matomo.org/faq/general/faq_70/" target="_blank" rel="noreferrer noopener">',
        '</a>',
        '<a href="https://matomo.org/faq/general/faq_69/" target="_blank" rel="noreferrer noopener">',
        '</a>',
      );
    },
    theDisplayedWebsitesAreSelectedText() {
      const text = translate(
        'UsersManager_TheDisplayedWebsitesAreSelected',
        `<strong>${this.siteAccess.length}</strong>`,
      );
      return `${text} `;
    },
    clickToSelectAllText() {
      return translate('UsersManager_ClickToSelectAll', `<strong>${this.totalEntries}</strong>`);
    },
    allWebsitesAreSelectedText() {
      return translate(
        'UsersManager_AllWebsitesAreSelected',
        `<strong>${this.totalEntries}</strong>`,
      );
    },
    clickToSelectDisplayedWebsitesText() {
      return translate(
        'UsersManager_ClickToSelectDisplayedWebsites',
        `<strong>${this.siteAccess.length}</strong>`,
      );
    },
    deletePermConfirmSingleText() {
      return translate(
        'UsersManager_DeletePermConfirmSingle',
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.siteAccessToChangeName}</strong>`,
      );
    },
    deletePermConfirmMultipleText() {
      return translate(
        'UsersManager_DeletePermConfirmMultiple',
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.affectedSitesCount}</strong>`,
      );
    },
    changePermToSiteConfirmSingleText() {
      return translate(
        'UsersManager_ChangePermToSiteConfirmSingle',
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.siteAccessToChangeName}</strong>`,
        `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`,
      );
    },
    changePermToSiteConfirmMultipleText() {
      return translate(
        'UsersManager_ChangePermToSiteConfirmMultiple',
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.affectedSitesCount}</strong>`,
        `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`,
      );
    },
    changePermToAllSitesConfirmText() {
      return translate(
        'UsersManager_ChangePermToAllSitesConfirm',
        `<strong>${this.userLogin}</strong>`,
        `<strong>${this.getRoleDisplay(this.allWebsitesAccssLevelSet)}</strong>`,
      );
    },
    paginationLowerBound() {
      return this.offset + 1;
    },
    paginationUpperBound() {
      if (!this.totalEntries) {
        return '?';
      }
      return Math.min(this.offset + this.limit, this.totalEntries);
    },
    filteredAccessLevels() {
      return (this.accessLevels as AccessLevel[]).filter((entry) => entry.key !== 'superuser' && entry.type === 'role');
    },
    filteredSelectAccessLevels() {
      return (this.filterAccessLevels as AccessLevel[]).filter(
        (entry) => entry.key !== 'superuser',
      );
    },
    selectedRowsCount() {
      let selectedRowKeyCount = 0;
      Object.values(this.selectedRows).forEach((v) => {
        if (v) {
          selectedRowKeyCount += 1;
        }
      });
      return selectedRowKeyCount;
    },
    affectedSitesCount() {
      if (this.areAllResultsSelected) {
        return this.totalEntries;
      }

      return this.selectedRowsCount;
    },
    allPropsWatch() {
      // see https://github.com/vuejs/vue/issues/844#issuecomment-390500758
      // eslint-disable-next-line no-sequences
      return (this.userLogin, this.limit, this.accessLevels, this.filterAccessLevels, Date.now());
    },
    siteAccessToChangeName() {
      return this.siteAccessToChange
        ? Matomo.helper.htmlEntities(this.siteAccessToChange.site_name)
        : '';
    },
    paginationText() {
      const text = translate(
        'General_Pagination',
        `${this.paginationLowerBound}`,
        `${this.paginationUpperBound}`,
        `${this.totalEntries}`,
      );
      return ` ${text} `;
    },
  },
});
</script>
