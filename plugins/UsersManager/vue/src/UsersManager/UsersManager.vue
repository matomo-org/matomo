<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="usersManager" v-tooltips>
    <div v-show="!isEditing">
      <div v-content-intro>
        <h2>
          <EnrichedHeadline
            help-url="https://matomo.org/docs/manage-users/"
            feature-name="Users Management"
          >
            {{ translate('UsersManager_ManageUsers') }}
          </EnrichedHeadline>
        </h2>
        <p v-if="currentUserRole === 'superuser'">
          {{ translate('UsersManager_ManageUsersDesc') }}
        </p>
        <p v-if="currentUserRole === 'admin'">
          {{ translate('UsersManager_ManageUsersAdminDesc') }}
        </p>
        <div class="row add-user-container">
          <div class="col s12">
            <div class="input-field" style="margin-right:3.5px">
              <a
                class="btn add-new-user"
                @click="onAddNewUser()"
              >
                {{ translate('UsersManager_AddUser') }}
              </a>
            </div>
            <div
              class="input-field"
              v-if="currentUserRole !== 'superuser'"
            >
              <a
                class="btn add-existing-user"
                @click="showAddExistingUserModal()"
              >
                {{ translate('UsersManager_AddExistingUser') }}
              </a>
            </div>
          </div>
        </div>
        <PagedUsersList
          @edit-user="onEditUser($event.user)"
          @change-user-role="onChangeUserRole($event.users, $event.role)"
          @delete-user="onDeleteUser($event.users)"
          @search-change="searchParams = $event.params; fetchUsers()"
          :initial-site-id="initialSiteId"
          :initial-site-name="initialSiteName"
          :is-loading-users="isLoadingUsers"
          :current-user-role="currentUserRole"
          :access-levels="accessLevels"
          :filter-access-levels="actualFilterAccessLevels"
          :search-params="searchParams"
          :users="users"
          :total-entries="totalEntries"
        />
      </div>
    </div>
    <!-- TODO: whether a user is being edited should be part of the URL -->
    <div v-if="isEditing">
      <UserEditForm
        @done="onDoneEditing($event.isUserModified)"
        :user="userBeingEdited"
        :current-user-role="currentUserRole"
        :access-levels="accessLevels"
        :filter-access-levels="actualFilterAccessLevels"
        :initial-site-id="initialSiteId"
        :initial-site-name="initialSiteName"
        @updated="userBeingEdited = $event.user"
      />
    </div>
    <div class="add-existing-user-modal modal" ref="addExistingUserModal">
      <div class="modal-content">
        <h3>{{ translate('UsersManager_AddExistingUser') }}</h3>
        <p>{{ translate('UsersManager_EnterUsernameOrEmail') }}:</p>
        <div>
          <Field
            v-model="addNewUserLoginEmail"
            name="add-existing-user-email"
            uicontrol="text"
          />
        </div>
      </div>
      <div class="modal-footer">
        <a
          href
          class="modal-action modal-close btn"
          @click.prevent="addExistingUser()"
          style="margin-right:3.5px"
        >{{ translate('General_Add') }}</a>
        <a
          href
          class="modal-action modal-close modal-no"
          @click.prevent="addNewUserLoginEmail = null"
        >{{ translate('General_Cancel') }}</a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/* eslint-disable newline-per-chained-call */

import { defineComponent } from 'vue';
import {
  ContentIntro,
  EnrichedHeadline,
  Tooltips,
  Matomo,
  MatomoUrl,
  AjaxHelper,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import PagedUsersList from '../PagedUsersList/PagedUsersList.vue';
import UserEditForm from '../UserEditForm/UserEditForm.vue';
import User from '../User';
import SearchParams from '../PagedUsersList/SearchParams';

interface UsersManagerState {
  isEditing: boolean;
  isCurrentUserSuperUser: boolean;
  users: User[];
  userBeingEdited: User|null;
  totalEntries: null|number;
  searchParams: SearchParams;
  isLoadingUsers: boolean;
  addNewUserLoginEmail: string;
}

const NUM_USERS_PER_PAGE = 20;

const { $ } = window;

export default defineComponent({
  props: {
    currentUserRole: {
      type: String,
      required: true,
    },
    initialSiteName: {
      type: String,
      required: true,
    },
    initialSiteId: {
      type: String,
      required: true,
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
    EnrichedHeadline,
    PagedUsersList,
    UserEditForm,
    Field,
  },
  directives: {
    ContentIntro,
    Tooltips,
  },
  data(): UsersManagerState {
    return {
      isEditing: !!MatomoUrl.urlParsed.value.showadduser,
      isCurrentUserSuperUser: true,
      users: [],
      totalEntries: null,
      searchParams: {
        offset: 0,
        limit: NUM_USERS_PER_PAGE,
        filter_search: '',
        filter_access: '',
        idSite: this.initialSiteId,
      },
      isLoadingUsers: false,
      userBeingEdited: null,
      addNewUserLoginEmail: '',
    };
  },
  created() {
    this.fetchUsers();
  },
  watch: {
    limit() {
      this.fetchUsers();
    },
  },
  methods: {
    onEditUser(user: User) {
      Matomo.helper.lazyScrollToContent();
      this.isEditing = true;
      this.userBeingEdited = user;
    },
    onDoneEditing(isUserModified: boolean) {
      this.isEditing = false;
      if (isUserModified) { // if a user was modified, we must reload the users list
        this.fetchUsers();
      }
    },
    showAddExistingUserModal() {
      $(this.$refs.addExistingUserModal as HTMLElement).modal({ dismissible: false }).modal('open');
    },
    onChangeUserRole(users: User[]|string, role: string) {
      this.isLoadingUsers = true;

      Promise.resolve().then(() => {
        if (users === 'all') {
          return this.getAllUsersInSearch();
        }
        return users as User[];
      }).then((usersResolved) => (
        usersResolved.filter((u) => u.role !== 'superuser').map((u) => u.login)
      )).then((userLogins) => {
        const requests = userLogins.map((login) => ({
          method: 'UsersManager.setUserAccess',
          userLogin: login,
          access: role,
          idSites: this.searchParams.idSite,
          ignoreSuperusers: 1,
        }));

        return AjaxHelper.fetch(requests, { createErrorNotification: true });
      }).catch(() => {
        // ignore (errors will still be displayed to the user)
      }).then(() => this.fetchUsers());
    },
    getAllUsersInSearch() {
      return AjaxHelper.fetch<User[]>({
        method: 'UsersManager.getUsersPlusRole',
        filter_search: this.searchParams.filter_search,
        filter_access: this.searchParams.filter_access,
        idSite: this.searchParams.idSite,
        filter_limit: '-1',
      });
    },
    onDeleteUser(users: User[]|string) {
      this.isLoadingUsers = true;

      Promise.resolve().then(() => {
        if (users === 'all') {
          return this.getAllUsersInSearch();
        }
        return users as User[];
      }).then((usersResolved) => usersResolved.map((u) => u.login)).then((userLogins) => {
        const requests = userLogins.map((login) => ({
          method: 'UsersManager.deleteUser',
          userLogin: login,
        }));
        return AjaxHelper.fetch(requests, { createErrorNotification: true });
      }).catch(() => {
        // ignore (errors will still be displayed to the user)
      }).then(() => this.fetchUsers());
    },
    fetchUsers() {
      this.isLoadingUsers = true;
      return AjaxHelper.fetch<AjaxHelper>(
        {
          ...this.searchParams,
          method: 'UsersManager.getUsersPlusRole',
        },
        { returnResponseObject: true },
      ).then((helper) => {
        const result = helper.getRequestHandle()!;

        this.totalEntries = parseInt(
          result.getResponseHeader('x-matomo-total-results') || '0',
          10,
        );
        this.users = result.responseJSON as User[];

        this.isLoadingUsers = false;
      }).catch(() => {
        this.isLoadingUsers = false;
      });
    },
    addExistingUser() {
      this.isLoadingUsers = true;
      return AjaxHelper.fetch<{ value: boolean }>({
        method: 'UsersManager.userExists',
        userLogin: this.addNewUserLoginEmail,
      }).then((response) => {
        if (response && response.value) {
          return this.addNewUserLoginEmail;
        }

        return AjaxHelper.fetch<{ value: string }>({
          method: 'UsersManager.getUserLoginFromUserEmail',
          userEmail: this.addNewUserLoginEmail,
        }).then((r) => r.value);
      }).then((login) => (
        AjaxHelper.post(
          {
            method: 'UsersManager.setUserAccess',
          },
          {
            userLogin: login,
            access: 'view',
            idSites: this.searchParams.idSite,
          },
        )
      )).then(
        () => this.fetchUsers(),
      ).catch(() => {
        this.isLoadingUsers = false;
      });
    },
    onAddNewUser() {
      const parameters = { isAllowed: true };
      Matomo.postEvent('UsersManager.initAddUser', parameters);
      if (parameters && !parameters.isAllowed) {
        return;
      }

      this.isEditing = true;
      this.userBeingEdited = null;
    },
  },
  computed: {
    actualFilterAccessLevels() {
      if (this.currentUserRole === 'superuser') {
        return [...this.filterAccessLevels, { key: 'superuser', value: 'Superuser' }];
      }
      return this.filterAccessLevels;
    },
  },
});
</script>
