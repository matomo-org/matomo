<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
      class="pagedUsersList"
      :class="{loading: isLoadingUsers}"
  >
    <div class="userListFilters row">
      <div class="col s12 m12 l8">
        <div class="input-field col s12 m3 l3">
          <a
              class="dropdown-trigger btn bulk-actions"
              href=""
              data-target="user-list-bulk-actions"
              :class="{ disabled: isBulkActionsDisabled }"
              v-dropdown-menu
          >
            {{ translate('UsersManager_BulkActions') }}
          </a>
          <ul
              id="user-list-bulk-actions"
              class="dropdown-content"
          >
            <li>
              <a
                  class="dropdown-trigger"
                  data-target="bulk-set-access"
                  v-dropdown-menu
              >
                {{ translate('UsersManager_SetPermission') }}
              </a>
              <ul
                  id="bulk-set-access"
                  class="dropdown-content"
              >
                <li v-for="access in bulkActionAccessLevels" :key="access.key">
                  <a
                      href=""
                      @click.prevent="
                      userToChange = null; roleToChangeTo = access.key; showAccessChangeConfirm();
                    "
                  >
                    {{ access.value }}
                  </a>
                </li>
              </ul>
            </li>
            <li>
              <a
                  href=""
                  @click.prevent="
                  userToChange = null; roleToChangeTo = 'noaccess'; showAccessChangeConfirm();
                "
              >
                {{ translate('UsersManager_RemovePermissions') }}
              </a>
            </li>
            <li v-if="currentUserRole === 'superuser'">
              <a
                  href=""
                  @click.prevent="showDeleteConfirm()"
              >{{ translate('UsersManager_DeleteUsers') }}</a>
            </li>
          </ul>
        </div>
        <div class="input-field col s12 m3 l3">
          <div
              class="permissions-for-selector"
          >
            <Field
                :model-value="userTextFilter"
                @update:model-value="onUserTextFilterChange($event)"
                name="user-text-filter"
                uicontrol="text"
                :full-width="true"
                :placeholder="translate('UsersManager_UserSearch')"
            />
          </div>
        </div>
        <div class="input-field col s12 m3 l3">
          <div>
            <Field
                :model-value="accessLevelFilter"
                @update:model-value="accessLevelFilter = $event; changeSearch({
                filter_access: accessLevelFilter,
                offset: 0,
              })"
                name="access-level-filter"
                uicontrol="select"
                :options="filterAccessLevels"
                :full-width="true"
                :placeholder="translate('UsersManager_FilterByAccess')"
            />
          </div>
        </div>
        <div class="input-field col s12 m3 l3">
          <div>
            <Field
                :model-value="statusLevelFilter"
                @update:model-value="statusLevelFilter = $event; changeSearch({
                filter_status: statusLevelFilter,
                offset: 0,
              })"
                name="status-level-filter"
                uicontrol="select"
                :options="filterStatusLevels"
                :full-width="true"
                :placeholder="translate('UsersManager_FilterByStatus')"
            />
          </div>
        </div>
      </div>
      <div
          class="input-field col s12 m12 l4 users-list-pagination-container"
          v-if="totalEntries > searchParams.limit"
      >
        <div class="usersListPagination">
          <a
              class="btn prev"
              :class="{ disabled: searchParams.offset <= 0 }"
              @click.prevent="gotoPreviousPage()"
          >
            <span class="pointer">&#xAB; {{ translate('General_Previous') }}</span>
          </a>
          <div class="counter">
            <span
                :class="{ visibility: isLoadingUsers ? 'hidden' : 'visible' }"
            >
              {{ translate(
                'General_Pagination',
                paginationLowerBound,
                paginationUpperBound,
                totalEntries
            ) }}
            </span>
            <ActivityIndicator
                :loading="isLoadingUsers"
            />
          </div>
          <a
              class="btn next"
              :class="{ disabled: searchParams.offset + searchParams.limit >= totalEntries }"
              @click.prevent="gotoNextPage()"
          >
            <span class="pointer">{{ translate('General_Next') }} &#xBB;</span>
          </a>
        </div>
      </div>
    </div>
    <div
        class="roles-help-notification"
        v-if="isRoleHelpToggled"
    >
      <Notification
          context="info"
          type="persistent"
          :noclear="true"
      >
        <span v-html="$sanitize(rolesHelpText)"></span>
      </Notification>
    </div>
    <ContentBlock>
      <table
          id="manageUsersTable"
          :class="{ loading: isLoadingUsers }"
          v-content-table
      >
        <thead>
        <tr>
          <th class="select-cell">
              <span class="checkbox-container">
                <label>
                  <input
                      type="checkbox"
                      id="paged_users_select_all"
                      checked="checked"
                      v-model="isAllCheckboxSelected"
                      @change="onAllCheckboxChange()"
                  />
                  <span/>
                </label>
              </span>
          </th>
          <th class="first">{{ translate('UsersManager_Username') }}</th>
          <th class="role_header">
            <span style="margin-right: 3.5px">{{ translate('UsersManager_RoleFor') }}</span>
            <a
                href=""
                class="helpIcon"
                @click.prevent="isRoleHelpToggled = !isRoleHelpToggled"
                :class="{ sticky: isRoleHelpToggled }"
            >
              <span class="icon-help"/>
            </a>
            <div>
              <Field
                  class="permissions-for-selector"
                  :model-value="permissionsForSite"
                  @update:model-value="onPermissionsForUpdate($event);"
                  uicontrol="site"
                  :ui-control-attributes="{
                    onlySitesWithAdminAccess: currentUserRole !== 'superuser',
                  }"
              />
            </div>
          </th>
          <th v-if="currentUserRole === 'superuser'">{{ translate('UsersManager_Email') }}</th>
          <th
              v-if="currentUserRole === 'superuser'"
              :title="translate('UsersManager_UsesTwoFactorAuthentication')"
          >{{ translate('UsersManager_2FA') }}
          </th>
          <th v-if="currentUserRole === 'superuser'">{{ translate('UsersManager_LastSeen') }}</th>
          <th>{{ translate('UsersManager_Status') }}</th>
          <th class="actions-cell-header">
            <div>{{ translate('General_Actions') }}</div>
          </th>
        </tr>
        </thead>
        <tbody>
        <tr
            class="select-all-row"
            v-if="isAllCheckboxSelected && users.length && users.length < totalEntries"
        >
          <td colspan="8">
            <div v-if="!areAllResultsSelected">
                <span
                    v-html="$sanitize(translate(
                    'UsersManager_TheDisplayedUsersAreSelected',
                    `<strong>${users.length}</strong>`,
                  ))"
                    style="margin-right:3.5px"
                ></span>
              <a
                  class="toggle-select-all-in-search"
                  href="#"
                  @click.prevent="areAllResultsSelected = !areAllResultsSelected"
                  v-html="$sanitize(translate(
                    'UsersManager_ClickToSelectAll',
                    `<strong>${totalEntries}</strong>`,
                  ))"
              ></a>
            </div>
            <div v-if="areAllResultsSelected">
                <span v-html="$sanitize(translate(
                    'UsersManager_AllUsersAreSelected',
                    `<strong>${totalEntries}</strong>`,
                  ))"
                      style="margin-right:3.5px"
                ></span>
              <a
                  class="toggle-select-all-in-search"
                  href="#"
                  @click.prevent="areAllResultsSelected = !areAllResultsSelected"
                  v-html="$sanitize(translate(
                    'UsersManager_ClickToSelectDisplayedUsers',
                    `<strong>${users.length}</strong>`,
                  ))"
              ></a>
            </div>
          </td>
        </tr>
        <tr
            v-for="(user, index) in users"
            :id="`row${index}`"
            :key="user.login"
        >
          <td class="select-cell">
              <span class="checkbox-container">
                <label>
                  <input
                      type="checkbox"
                      :id="`paged_users_select_row${index}`"
                      v-model="selectedRows[index]"
                      @click="onRowSelected()"
                  />
                  <span/>
                </label>
              </span>
          </td>
          <td id="userLogin">{{ user.login }}</td>
          <td class="access-cell">
            <div>
              <Field
                  :model-value="user.role"
                  @update:model-value="
                    userToChange = user;
                    roleToChangeTo = $event.value;
                    showAccessChangeConfirm();
                    $event.abort();"
                  :model-modifiers="{abortable: true}"
                  :disabled="user.role === 'superuser'"
                  uicontrol="select"
                  :options="
                    user.login === 'anonymous' ? anonymousAccessLevels :
                    (user.role === 'noaccess' ? onlyRoleAccessLevels : accessLevels)"
                />
              </div>
            </td>
            <td
              id="email"
              v-if="currentUserRole === 'superuser'"
          >{{ user.email }}
          </td>
          <td
              id="twofa"
              v-if="currentUserRole === 'superuser'"
          >
              <span
                  class="icon-ok"
                  v-if="user.uses_2fa"
              />
            <span
                class="icon-close"
                v-if="!user.uses_2fa"
            />
          </td>
          <td
              id="last_seen"
              v-if="currentUserRole === 'superuser'"
          >
            {{ user.last_seen ? `${user.last_seen} ago`:'-' }}
          </td>
          <td id="status">
              <span :class="Number.isInteger(user.invite_status)? 'pending':user.invite_status"
                    :title="user.invite_status === 'expired' ?
                            translate('UsersManager_ExpiredInviteAutomaticallyRemoved', '3') :
                            ''"
              >{{ getInviteStatus(user.invite_status) }}</span>
          </td>
          <td class="center actions-cell">
            <button
                class="resend table-action"
                title="Resend/Copy Invite Link"
                @click="userToChange = user; resendRequestedUser()"
                v-if="(
                  currentUserRole === 'superuser'
                  || (currentUserRole === 'admin' && user.invited_by === currentUserLogin)
                ) && user.invite_status !== 'active'"
            >
              <span class="icon-email"/>
            </button>

            <button
                class="edituser table-action"
                title="Edit"
                @click="$emit('editUser', { user: user })"
                v-if="user.login !== 'anonymous'"
            >
              <span class="icon-edit"/>
            </button>
            <button
                class="deleteuser table-action"
                title="Delete"
                @click="userToChange = user; showDeleteConfirm()"
                v-if="(
                  currentUserRole === 'superuser'
                  || (
                    currentUserRole === 'admin'
                    && user.invited_by === currentUserLogin
                    && user.invite_status !== 'active'
                  )
                ) && user.login !== 'anonymous'"
            >
              <span class="icon-delete"/>
            </button>
          </td>
        </tr>
        </tbody>
      </table>
    </ContentBlock>
    <PasswordConfirmation
      v-model="showPasswordConfirmationForUserRemoval"
      @confirmed="deleteRequestedUsers"
      @aborted="resetUserAndRoleToChange"
    >
      <h2
        v-if="userToChange"
        v-html="$sanitize(translate(
            'UsersManager_DeleteUserConfirmSingle',
            `<strong>${userToChange.login}</strong>`,
          ))"
      ></h2>
      <h2
        v-if="!userToChange"
        v-html="$sanitize(translate(
            'UsersManager_DeleteUserConfirmMultiple',
            `<strong>${affectedUsersCount}</strong>`,
          ))"
      ></h2>
    </PasswordConfirmation>

    <PasswordConfirmation
      v-model="showPasswordConfirmationForAnonymousAccess"
      @confirmed="changeUserRole"
      @aborted="resetUserAndRoleToChange"
    >
      <h3
        v-if="userToChange"
        v-html="$sanitize(deleteUserPermConfirmSingleText)"
      ></h3>
      <h3
        v-if="!userToChange"
        v-html="$sanitize(deleteUserPermConfirmMultipleText)"
      ></h3>
      <h3>
        <em>{{ translate('General_Note') }}:
          <span v-html="$sanitize(translate(
              'UsersManager_AnonymousUserRoleChangeWarning',
              'anonymous',
              getRoleDisplay(roleToChangeTo),
            ))">
            </span>
        </em>
      </h3>
    </PasswordConfirmation>

    <div class="change-user-role-confirm-modal modal" ref="changeUserRoleConfirmModal">
      <div class="modal-content">
        <h3
            v-if="userToChange"
            v-html="$sanitize(deleteUserPermConfirmSingleText)"
        ></h3>
        <p
            v-if="!userToChange"
            v-html="$sanitize(deleteUserPermConfirmMultipleText)"
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
            @click.prevent="resetUserAndRoleToChange()"
        >{{ translate('General_No') }}</a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  DropdownMenu,
  ActivityIndicator,
  Notification,
  ContentBlock,
  ContentTable,
  debounce,
  translate,
  SiteRef,
  Matomo,
  externalLink,
} from 'CoreHome';
import { Field, PasswordConfirmation } from 'CorePluginsAdmin';
import User from '../User';
import SearchParams from './SearchParams';

interface AccessLevel {
  key: string;
  value: unknown;
  type: string
}

interface PagedUsersListState {
  areAllResultsSelected: boolean;
  selectedRows: Record<string, boolean>;
  isAllCheckboxSelected: boolean;
  isBulkActionsDisabled: boolean;
  userToChange: User | null;
  roleToChangeTo: string | null;
  accessLevelFilter: string | null;
  statusLevelFilter: string | null;
  isRoleHelpToggled: boolean;
  userTextFilter: string;
  permissionsForSite: SiteRef;
  showPasswordConfirmationForUserRemoval: boolean;
  showPasswordConfirmationForAnonymousAccess: boolean;
}

const { $ } = window;

export default defineComponent({
  props: {
    initialSiteId: {
      type: [String, Number],
      required: true,
    },
    initialSiteName: {
      type: String,
      required: true,
    },
    currentUserRole: String,
    isLoadingUsers: Boolean,
    accessLevels: {
      type: Array,
      required: true,
    },
    filterAccessLevels: {
      type: Array,
      required: true,
    },
    filterStatusLevels: {
      type: Array,
      required: true,
    },
    totalEntries: Number,
    users: {
      type: Array,
      required: true,
    },
    searchParams: {
      type: Object,
      required: true,
    },
  },
  components: {
    Field,
    ActivityIndicator,
    Notification,
    ContentBlock,
    PasswordConfirmation,
  },
  directives: {
    DropdownMenu,
    ContentTable,
  },
  data(): PagedUsersListState {
    return {
      areAllResultsSelected: false,
      selectedRows: {},
      isAllCheckboxSelected: false,
      isBulkActionsDisabled: true,
      userToChange: null,
      roleToChangeTo: null,
      accessLevelFilter: null,
      statusLevelFilter: null,
      isRoleHelpToggled: false,
      userTextFilter: '',
      permissionsForSite: {
        id: this.initialSiteId,
        name: this.initialSiteName,
      },
      showPasswordConfirmationForUserRemoval: false,
      showPasswordConfirmationForAnonymousAccess: false,
    };
  },
  emits: ['editUser', 'changeUserRole', 'deleteUser', 'searchChange', 'resendInvite'],
  created() {
    this.onUserTextFilterChange = debounce(this.onUserTextFilterChange, 300);
  },
  watch: {
    users() {
      this.clearSelection();
    },
  },
  methods: {
    getInviteStatus(inviteStatus: string | number) {
      if (Number.isInteger(inviteStatus)) {
        return translate('UsersManager_InviteDayLeft', inviteStatus);
      }
      if (inviteStatus === 'expired') {
        return translate('UsersManager_Expired');
      }
      return translate('UsersManager_Active');
    },
    onPermissionsForUpdate(site: SiteRef) {
      this.permissionsForSite = site;
      this.changeSearch({ idSite: this.permissionsForSite.id });
    },
    clearSelection() {
      this.selectedRows = {};
      this.areAllResultsSelected = false;
      this.isBulkActionsDisabled = true;
      this.isAllCheckboxSelected = false;
      this.userToChange = null;
    },
    resetUserAndRoleToChange() {
      this.userToChange = null;
      this.roleToChangeTo = null;
    },
    onAllCheckboxChange() {
      if (!this.isAllCheckboxSelected) {
        this.clearSelection();
      } else {
        for (let i = 0; i !== this.users.length; i += 1) {
          this.selectedRows[i] = true;
        }
        this.isBulkActionsDisabled = false;
      }
    },
    changeUserRole(password: string) {
      this.$emit('changeUserRole', {
        users: this.userOperationSubject,
        role: this.roleToChangeTo,
        password,
      });
    },
    onRowSelected() {
      const selectedRowKeyCount = this.selectedCount;
      this.isBulkActionsDisabled = selectedRowKeyCount === 0;
      this.isAllCheckboxSelected = selectedRowKeyCount === this.users.length;
    },
    deleteRequestedUsers(password: string) {
      this.$emit('deleteUser', {
        users: this.userOperationSubject,
        password,
      });
    },
    resendRequestedUser() {
      this.$emit('resendInvite', {
        user: this.userToChange,
      });
    },
    showDeleteConfirm() {
      this.showPasswordConfirmationForUserRemoval = true;
    },

    showAccessChangeConfirm() {
      const containsAnonymous = this.userOperationSubject === 'all' || (
        Array.isArray(this.userOperationSubject)
        && this.userOperationSubject.filter((user) => user.login === 'anonymous').length
      );

      if (containsAnonymous && this.roleToChangeTo === 'view') {
        this.showPasswordConfirmationForAnonymousAccess = true;
      } else {
        $(this.$refs.changeUserRoleConfirmModal as HTMLElement)
          .modal({
            dismissible: false,
          })
          .modal('open');
      }
    },
    getRoleDisplay(role: string | null) {
      let result = null;
      (this.accessLevels as AccessLevel[]).forEach((entry) => {
        if (entry.key === role) {
          result = entry.value;
        }
      });
      return result;
    },
    changeSearch(changes: Partial<SearchParams>) {
      const params = { ...this.searchParams, ...changes };
      this.$emit('searchChange', { params });
    },
    gotoPreviousPage() {
      this.changeSearch({
        offset: Math.max(0, this.searchParams.offset - this.searchParams.limit),
      });
    },
    gotoNextPage() {
      const newOffset = this.searchParams.offset + this.searchParams.limit;
      if (newOffset >= this.totalEntries!) {
        return;
      }

      this.changeSearch({
        offset: newOffset,
      });
    },
    onUserTextFilterChange(filter: string) {
      this.userTextFilter = filter;
      this.changeSearch({
        filter_search: filter,
        offset: 0,
      });
    },
  },
  computed: {
    currentUserLogin() {
      return Matomo.userLogin;
    },
    paginationLowerBound() {
      return this.searchParams.offset + 1;
    },
    paginationUpperBound() {
      if (this.totalEntries === null) {
        return '?';
      }

      const searchParams = this.searchParams as SearchParams;

      return Math.min(searchParams.offset + searchParams.limit, this.totalEntries!);
    },
    userOperationSubject() {
      if (this.userToChange) {
        return [this.userToChange];
      }

      if (this.areAllResultsSelected) {
        return 'all';
      }

      return this.selectedUsers;
    },
    selectedUsers() {
      const users = this.users as User[];

      const result: User[] = [];
      Object.keys(this.selectedRows)
        .forEach((index) => {
          const indexN = parseInt(index, 10);
          if (this.selectedRows[index]
                && users[indexN] // sanity check
          ) {
            result.push(users[indexN]);
          }
        });
      return result;
    },
    rolesHelpText() {
      return translate(
        'UsersManager_RolesHelp',
        externalLink('https://matomo.org/faq/general/faq_70/'),
        '</a>',
        externalLink('https://matomo.org/faq/general/faq_69/'),
        '</a>',
      );
    },
    affectedUsersCount() {
      if (this.areAllResultsSelected) {
        return this.totalEntries || 0;
      }

      return this.selectedCount;
    },
    selectedCount() {
      let selectedRowKeyCount = 0;
      Object.keys(this.selectedRows)
        .forEach((key) => {
          if (this.selectedRows[key]) {
            selectedRowKeyCount += 1;
          }
        });
      return selectedRowKeyCount;
    },
    deleteUserPermConfirmSingleText() {
      return translate(
        'UsersManager_DeleteUserPermConfirmSingle',
        `<strong>${this.userToChange?.login || ''}</strong>`,
        `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`,
        `<strong>${Matomo.helper.htmlEntities(this.permissionsForSite?.name || '')}</strong>`,
      );
    },
    deleteUserPermConfirmMultipleText() {
      return translate(
        'UsersManager_DeleteUserPermConfirmMultiple',
        `<strong>${this.affectedUsersCount}</strong>`,
        `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`,
        `<strong>${Matomo.helper.htmlEntities(this.permissionsForSite?.name || '')}</strong>`,
      );
    },
    bulkActionAccessLevels() {
      return (this.accessLevels as AccessLevel[]).filter(
        (e) => e.key !== 'noaccess' && e.key !== 'superuser',
      );
    },
    anonymousAccessLevels() {
      return (this.accessLevels as AccessLevel[]).filter(
        (e) => e.key === 'noaccess' || e.key === 'view',
      );
    },
    onlyRoleAccessLevels() {
      return (this.accessLevels as AccessLevel[]).filter(
        (e) => e.type === 'role',
      );
    },
  },
});
</script>
