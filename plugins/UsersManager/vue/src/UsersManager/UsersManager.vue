<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="usersManager" v-tooltips>
    <div v-show="!isEditing && !isInviting">
      <div v-content-intro>
        <h2>
          <EnrichedHeadline
            :help-url="externalRawLink('https://matomo.org/docs/manage-users/')"
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
                {{ translate('UsersManager_InviteNewUser') }}
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
          @change-user-role="onChangeUserRole($event.users, $event.role, $event.password)"
          @delete-user="onDeleteUser($event.users, $event.password)"
          @search-change="searchParams = $event.params; fetchUsers()"
          @resend-invite="triggerResendInviteForUser = $event.user"
          :initial-site-id="initialSiteId"
          :initial-site-name="initialSiteName"
          :is-loading-users="isLoadingUsers"
          :current-user-role="currentUserRole"
          :access-levels="accessLevels"
          :filter-access-levels="filterAccessLevels"
          :filter-status-levels="filterStatusLevels"
          :search-params="searchParams"
          :users="users"
          :total-entries="totalEntries"
        />
      </div>
    </div>
    <div v-show="isInviting">
      <component
        :is="usedInviteComponent"
        :invite-token-expiry-days="inviteTokenExpiryDays"
        :initial-site-id="initialSiteId"
        :initial-site-name="initialSiteName"
        @aborted="isInviting = false"
        @invited="onInvite($event.user)"
      ></component>
    </div>
    <component
      :is="usedResendInviteComponent"
      :user="triggerResendInviteForUser"
      :invite-token-expiry-days="inviteTokenExpiryDays"
      @close="triggerResendInviteForUser = null"
    ></component>
    <!-- TODO: whether a user is being edited should be part of the URL -->
    <div v-if="isEditing">
      <UserEditForm
        @done="onDoneEditing($event.isUserModified)"
        :user="userBeingEdited"
        :current-user-role="currentUserRole"
        :access-levels="accessLevels"
        :filter-access-levels="filterAccessLevels"
        :activated-plugins="activatedPlugins"
        :password-strength-validation-rules="passwordStrengthValidationRules"
        @resend-invite="triggerResendInviteForUser = $event.user"
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

<!--suppress JSConstantReassignment, TypeScriptValidateTypes -->
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
  translate,
  NotificationsStore,
  useExternalPluginComponent,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import PagedUsersList from '../PagedUsersList/PagedUsersList.vue';
import UserEditForm from '../UserEditForm/UserEditForm.vue';
import User from '../User';
import SearchParams from '../PagedUsersList/SearchParams';

interface UsersManagerState {
  isEditing: boolean;
  isInviting: boolean;
  isCurrentUserSuperUser: boolean;
  users: User[];
  userBeingEdited: User | null;
  totalEntries: null | number;
  searchParams: SearchParams;
  isLoadingUsers: boolean;
  addNewUserLoginEmail: string;
  loading: boolean;
  triggerResendInviteForUser: User | null;
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
    filterStatusLevels: {
      type: Array,
      required: true,
    },
    activatedPlugins: {
      type: Array,
      required: true,
    },
    inviteTokenExpiryDays: {
      type: String,
      required: true,
    },
    passwordStrengthValidationRules: {
      type: Array,
      default: () => [],
    },
    inviteComponent: {
      type: Object,
      required: false,
      default: null,
    },
    resendInviteComponent: {
      type: Object,
      required: false,
      default: null,
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
      isInviting: false,
      isCurrentUserSuperUser: true,
      users: [],
      totalEntries: null,
      searchParams: {
        offset: 0,
        limit: NUM_USERS_PER_PAGE,
        filter_search: '',
        filter_access: '',
        filter_status: '',
        idSite: this.initialSiteId,
      },
      isLoadingUsers: false,
      userBeingEdited: null,
      addNewUserLoginEmail: '',
      loading: false,
      triggerResendInviteForUser: null,
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
    onInvite(user: User) {
      this.isInviting = false;
      this.userBeingEdited = user;
      this.isEditing = true;
      this.fetchUsers();
    },
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
    onChangeUserRole(users: User[]|string, role: string, password: string) {
      this.isLoadingUsers = true;

      Promise.resolve().then(() => {
        if (users === 'all') {
          return this.getAllUsersInSearch();
        }
        return users as User[];
      }).then((usersResolved) => (
        usersResolved.filter((u) => u.role !== 'superuser').map((u) => u.login)
      )).then((userLogins) => {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const type = this.accessLevels.filter((a: any) => a.key === role).map((a: any) => a.type);

        let requests;
        if (type.length && type[0] === 'capability') {
          requests = userLogins.map((login) => ({
            method: 'UsersManager.addCapabilities',
            userLogin: login,
            capabilities: role,
            idSites: this.searchParams.idSite,
            passwordConfirmation: password,
          }));
        } else {
          requests = userLogins.map((login) => ({
            method: 'UsersManager.setUserAccess',
            userLogin: login,
            access: role,
            idSites: this.searchParams.idSite,
            passwordConfirmation: password,
          }));
        }

        return AjaxHelper.fetch(requests, { createErrorNotification: true });
      }).catch(() => {
        // ignore (errors will still be displayed to the user)
      }).finally(
        () => this.fetchUsers(),
      );
    },
    getAllUsersInSearch() {
      return AjaxHelper.fetch<User[]>({
        method: 'UsersManager.getUsersPlusRole',
        filter_search: this.searchParams.filter_search,
        filter_access: this.searchParams.filter_access,
        filter_status: this.searchParams.filter_status,
        idSite: this.searchParams.idSite,
        filter_limit: '-1',
      });
    },
    onDeleteUser(users: User[]|string, password: string) {
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
          passwordConfirmation: password,
        }));
        return AjaxHelper.fetch(requests, { createErrorNotification: true });
      }).then(() => {
        NotificationsStore.scrollToNotification(NotificationsStore.show({
          id: 'removeUserSuccess',
          message: translate('UsersManager_DeleteSuccess'),
          context: 'success',
          type: 'toast',
        }));
        this.fetchUsers();
      }, () => {
        if (users !== 'all' && users.length > 1) {
          // Show a notification that some users might not have been removed if an error occurs
          // and more than one users was tried to remove
          // Note: We do not scroll to this notification, as the error notification from AjaxHandler
          // will be created earlier, which will already be scrolled into view.
          NotificationsStore.show({
            id: 'removeUserSuccess',
            message: translate('UsersManager_DeleteNotSuccessful'),
            context: 'warning',
            type: 'toast',
          });
        }
        this.fetchUsers();
      });
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

      this.isInviting = true;
      this.userBeingEdited = null;
    },
  },
  computed: {
    usedInviteComponent() {
      if (this.inviteComponent) {
        const [plugin, component] = this.inviteComponent.split('.');
        return useExternalPluginComponent(plugin, component);
      }

      return useExternalPluginComponent('UsersManager', 'UserInvite');
    },
    usedResendInviteComponent() {
      if (this.resendInviteComponent) {
        const [plugin, component] = this.resendInviteComponent.split('.');
        return useExternalPluginComponent(plugin, component);
      }

      return useExternalPluginComponent('UsersManager', 'ResendInviteModal');
    },
  },
});
</script>
