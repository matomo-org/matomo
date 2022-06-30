<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="userEditForm"
    :class="{ loading: isSavingUserInfo }"
    :content-title="`${formTitle} ${!isAdd ? `'${theUser.login}'` : ''}`"
  >
    <div
      class="row"
      v-form=""
    >
      <div
        class="col m2 entityList"
        v-if="!isAdd"
      >
        <ul class="listCircle">
          <li
            :class="{active: activeTab === 'basic'}"
            class="menuBasicInfo"
          >
            <a
              href=""
              @click.prevent="activeTab = 'basic'"
            >{{ translate('UsersManager_BasicInformation') }}</a>
          </li>
          <li
            :class="{active: activeTab === 'permissions'}"
            class="menuPermissions"
          >
            <a
              href=""
              @click.prevent="activeTab = 'permissions'"
              style="margin-right:3.5px"
            >
              {{ translate('UsersManager_Permissions') }}
            </a>
            <span
              class="icon-warning"
              v-if="!userHasAccess && !theUser.superuser_access"
            />
          </li>
          <li
            :class="{active: activeTab === 'superuser'}"
            class="menuSuperuser"
            v-if="currentUserRole === 'superuser'"
          >
            <a
              href=""
              @click.prevent="activeTab = 'superuser'"
            >{{ translate('UsersManager_SuperUserAccess') }}</a>
          </li>
          <li
            :class="{active: activeTab === '2fa'}"
            class="menuUserTwoFa"
            v-if="currentUserRole === 'superuser' && theUser.uses_2fa && !isAdd"
          >
            <a
              href=""
              @click.prevent="activeTab = '2fa'"
            >{{ translate('UsersManager_TwoFactorAuthentication') }}</a>
          </li>
        </ul>
        <div class="save-button-spacer hide-on-small-only">
        </div>
        <div
          class="entityCancel"
          @click.prevent="onDoneEditing()"
        >
          <a
            href=""
            class="entityCancelLink"
          >{{ translate('Mobile_NavigationBack') }}</a>
        </div>
      </div>
      <div class="visibleTab col m10">
        <div
          v-if="activeTab === 'basic'"
          class="basic-info-tab"
        >
          <div>
            <Field
              v-model="theUser.login"
              :disabled="isSavingUserInfo || !isAdd || isShowingPasswordConfirm"
              uicontrol="text"
              name="user_login"
              :maxlength="100"
              :title="translate('General_Username')"
            />
          </div>
          <div>
            <Field
               v-if="!isPending"
               :model-value="theUser.password"
               :disabled="isSavingUserInfo || (currentUserRole !== 'superuser' && !isAdd)
                || isShowingPasswordConfirm"
              @update:model-value="theUser.password = $event; isPasswordModified = true"
              uicontrol="password"
              name="user_password"
              :title="translate('General_Password')"
            />
          </div>
          <div>
            <Field
              v-model="theUser.email"
              :disabled="isSavingUserInfo || (currentUserRole !== 'superuser' && !isAdd)
                || isShowingPasswordConfirm"
              v-if="currentUserRole === 'superuser' || isAdd"
              uicontrol="text"
              name="user_email"
              :maxlength="100"
              :title="translate('UsersManager_Email')"
            />
          </div>
          <div>
            <Field
              v-model="firstSiteAccess"
              :disabled="isSavingUserInfo"
              v-if="isAdd"
              uicontrol="site"
              name="user_site"
              :ui-control-attributes="{ onlySitesWithAdminAccess: true }"
              :title="translate('UsersManager_FirstWebsitePermission')"
              :inline-help="translate('UsersManager_FirstSiteInlineHelp')"
            />
          </div>
          <div>
            <div class="form-group row" style="position: relative">
              <div class="col s12 m6">
                <SaveButton
                    style="position: absolute;bottom: 0"
                    v-if="currentUserRole === 'superuser' || isAdd"
                    :value="saveButtonLabel"
                    :disabled="isAdd && (!firstSiteAccess || !firstSiteAccess.id)"
                    :saving="isSavingUserInfo"
                    @confirm="saveUserInfo()"
                />
              </div>
              <div class="col s12 m6">
                <div v-if="isAdd" class="form-help">
                     <span class="inline-help"
                      v-html="$sanitize(
                          translate('UsersManager_InviteSuccessNotification', [7]))"></span>
                </div>
              </div>
            </div>
          </div>
          <div
            class="entityCancel"
            v-if="isAdd"
          >
            <a
              href=""
              class="entityCancelLink"
              @click.prevent="onDoneEditing()"
            >{{ translate('General_Cancel') }}</a>
          </div>
        </div>
        <div
          v-if="!isAdd"
          v-show="activeTab === 'permissions'"
          class="user-permissions"
        >
          <div
            v-if="!theUser.superuser_access"
          >
            <UserPermissionsEdit
              :user-login="theUser.login"
              @user-has-access-detected="userHasAccess = $event.hasAccess"
              @access-changed="isUserModified = true"
              :access-levels="accessLevels"
              :filter-access-levels="filterAccessLevels"
            />
          </div>
          <div
            v-if="theUser.superuser_access"
            class="alert alert-info"
          >
            {{ translate('UsersManager_SuperUsersPermissionsNotice') }}
          </div>
        </div>
        <div
          v-if="activeTab === 'superuser' && currentUserRole === 'superuser' && !isAdd"
          class="superuser-access"
        >
          <p>{{ translate('UsersManager_SuperUserIntro1') }}</p>
          <p><strong>{{ translate('UsersManager_SuperUserIntro2') }}</strong></p>
          <div>
            <Field
              v-model="superUserAccessChecked"
              @click="confirmSuperUserChange()"
              :disabled="isSavingUserInfo"
              uicontrol="checkbox"
              name="superuser_access"
              :title="translate('UsersManager_HasSuperUserAccess')"
            />
          </div>
          <div class="superuser-confirm-modal modal" ref="superUserConfirmModal">
            <div class="modal-content">
              <h2>{{ translate('UsersManager_AreYouSure') }}</h2>
              <p v-if="theUser.superuser_access">
                {{ translate('UsersManager_RemoveSuperuserAccessConfirm') }}
              </p>
              <p v-if="!theUser.superuser_access">
                {{ translate('UsersManager_AddSuperuserAccessConfirm') }}
              </p>
              <div>
                <Field
                  v-model="passwordConfirmationForSuperUser"
                  uicontrol="password"
                  name="currentUserPasswordForSuperUser"
                  :autocomplete="false"
                  :full-width="true"
                  :title="translate('UsersManager_YourCurrentPassword')"
                />
              </div>
            </div>
            <div class="modal-footer">
              <a
                href=""
                class="modal-action modal-close btn"
                @click.prevent="toggleSuperuserAccess()"
                style="margin-right:3.5px"
              >{{ translate('General_Yes') }}</a>
              <a
                href=""
                class="modal-action modal-close modal-no"
                @click.prevent="setSuperUserAccessChecked(); passwordConfirmationForSuperUser = ''"
              >{{ translate('General_No') }}</a>
            </div>
          </div>
        </div>
        <div
          v-show="activeTab === '2fa'"
          v-if="currentUserRole === 'superuser' && !isAdd"
          class="twofa-reset"
        >
          <p>{{ translate('UsersManager_ResetTwoFactorAuthenticationInfo') }}</p>
          <div
            class="resetTwoFa"
          >
            <SaveButton
              :saving="isResetting2FA"
              @confirm="confirmReset2FA()"
              :value="translate('UsersManager_ResetTwoFactorAuthentication')"
            />
          </div>
          <div class="twofa-confirm-modal modal" ref="twofaConfirmModal">
            <div class="modal-content">
              <h2>{{ translate('UsersManager_AreYouSure') }}</h2>
              <p>{{ translate('UsersManager_ConfirmWithPassword') }}</p>
              <div>
                <Field
                  v-model="passwordConfirmation"
                  uicontrol="password"
                  name="currentUserPasswordTwoFa"
                  :autocomplete="false"
                  :full-width="true"
                  :title="translate('UsersManager_YourCurrentPassword')"
                />
              </div>
            </div>
            <div class="modal-footer">
              <a
                href=""
                class="modal-action modal-close btn"
                @click.prevent="reset2FA()"
                style="margin-right:3.5px"
              >{{ translate('General_Yes') }}</a>
              <a
                href=""
                class="modal-action modal-close modal-no"
                @click="$event.preventDefault(); passwordConfirmation = ''"
              >{{ translate('General_No') }}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="change-password-modal modal" ref="changePasswordModal">
      <div class="modal-content">
        <h2 v-html="$sanitize(changePasswordTitle)"></h2>
        <p>{{ translate('UsersManager_ConfirmWithPassword') }}</p>
        <div>
          <Field
            v-model="passwordConfirmation"
            uicontrol="password"
            name="currentUserPasswordChangePwd"
            :autocomplete="false"
            :full-width="true"
            :title="translate('UsersManager_YourCurrentPassword')"
          />
        </div>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          @click.prevent="updateUser()"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
          @click="$event.preventDefault(); passwordConfirmation = ''"
        >{{ translate('General_No') }}</a>
      </div>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent, readonly } from 'vue';
import {
  ContentBlock,
  SiteRef,
  translate,
  AjaxHelper,
  NotificationsStore,
} from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';
import UserPermissionsEdit from '../UserPermissionsEdit/UserPermissionsEdit.vue';
import User from '../User';
import KeyPressEvent = JQuery.KeyPressEvent;

const DEFAULT_USER: User = {
  login: '',
  superuser_access: false,
  uses_2fa: false,
  password: '',
  email: '',
  invite_status: '',
};

interface UserEditFormState {
  theUser: User;
  activeTab: string;
  permissionsForIdSite: string|number;
  isSavingUserInfo: boolean;
  userHasAccess: boolean;
  firstSiteAccess: SiteRef|null;
  isUserModified: boolean;
  passwordConfirmation: string;
  isPasswordModified: boolean;
  superUserAccessChecked: boolean|null;
  passwordConfirmationForSuperUser: string;
  isResetting2FA: boolean;
  isShowingPasswordConfirm: boolean;
}

const { $ } = window;

export default defineComponent({
  props: {
    user: Object,
    currentUserRole: {
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
    initialSiteId: {
      type: [String, Number],
      required: true,
    },
    initialSiteName: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    Field,
    SaveButton,
    UserPermissionsEdit,
  },
  directives: {
    Form,
  },
  data(): UserEditFormState {
    return {
      theUser: (this.user as User) || { ...DEFAULT_USER },
      activeTab: 'basic',
      permissionsForIdSite: 1,
      isSavingUserInfo: false,
      userHasAccess: true,
      firstSiteAccess: {
        id: this.initialSiteId,
        name: this.initialSiteName,
      },
      isUserModified: false,
      passwordConfirmation: '',
      isPasswordModified: false,
      superUserAccessChecked: null,
      passwordConfirmationForSuperUser: '',
      isResetting2FA: false,
      isShowingPasswordConfirm: false,
    };
  },
  emits: ['done', 'updated'],
  watch: {
    user(newVal) {
      this.onUserChange(newVal);
    },
  },
  created() {
    this.onUserChange(this.user as User);
  },
  methods: {
    onUserChange(newVal: User) {
      this.theUser = newVal || { ...DEFAULT_USER };

      if (!this.theUser.password) {
        this.resetPasswordVar();
      }

      this.setSuperUserAccessChecked();
    },
    confirmSuperUserChange() {
      $(this.$refs.superUserConfirmModal as HTMLElement).modal({
        dismissible: false,
      }).modal('open');
    },
    confirmReset2FA() {
      $(this.$refs.twofaConfirmModal as HTMLElement).modal({ dismissible: false }).modal('open');
    },
    toggleSuperuserAccess() {
      this.isSavingUserInfo = true;
      AjaxHelper.post(
        {
          method: 'UsersManager.setSuperUserAccess',
        },
        {
          userLogin: this.theUser.login,
          hasSuperUserAccess: this.theUser.superuser_access ? '0' : '1',
          passwordConfirmation: this.passwordConfirmationForSuperUser!,
        },
      ).then(() => {
        this.theUser.superuser_access = !this.theUser.superuser_access;
      }).catch(() => {
        // ignore error (still displayed to user)
      }).then(() => { // eslint-disable-line
        this.isSavingUserInfo = false;
        this.isUserModified = true;
        this.passwordConfirmationForSuperUser = '';
        this.setSuperUserAccessChecked();
      });
    },
    saveUserInfo() {
      return Promise.resolve().then(() => {
        if (this.isAdd) {
          return this.createUser();
        }

        return this.confirmUserChange();
      }).then(() => {
        this.$emit('updated', { user: readonly(this.theUser) });
      });
    },
    createUser() {
      this.isSavingUserInfo = true;
      return AjaxHelper.post(
        {
          method: 'UsersManager.inviteUser',
        },
        {
          userLogin: this.theUser.login,
          email: this.theUser.email,
          initialIdSite: this.firstSiteAccess ? this.firstSiteAccess.id : undefined,
        },
      ).catch((e) => {
        this.isSavingUserInfo = false;
        throw e;
      }).then(() => {
        this.firstSiteAccess = null;
        this.isSavingUserInfo = false;
        this.isUserModified = true;
        this.theUser.invite_status = 'pending';

        this.resetPasswordVar();
        this.showUserCreatedNotification();
      });
    },
    resetPasswordVar() {
      if (!this.isAdd) {
        // make sure password is not stored in the client after update/save
        this.theUser.password = 'XXXXXXXX';
      }
    },
    confirmUserChange() {
      this.passwordConfirmation = '';
      this.isShowingPasswordConfirm = true;

      const onEnter = (event: KeyPressEvent) => {
        const keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode === 13) {
          $(this.$refs.changePasswordModal as HTMLElement).modal('close');
          this.updateUser();
        }
      };

      $(this.$refs.changePasswordModal as HTMLElement).modal({
        dismissible: false,
        onOpenEnd: () => {
          this.isShowingPasswordConfirm = false;
          $('.modal.open #currentUserPasswordChangePwd').focus().off('keypress').keypress(onEnter);
        },
      }).modal('open');
    },
    showUserSavedNotification() {
      NotificationsStore.show({
        message: translate('General_YourChangesHaveBeenSaved'),
        context: 'success',
        type: 'toast',
      });
    },
    showUserCreatedNotification() {
      NotificationsStore.show({
        message: translate('UsersManager_InviteSuccess'),
        context: 'success',
        type: 'toast',
      });
    },
    reset2FA() {
      this.isResetting2FA = true;
      return AjaxHelper.post({
        method: 'TwoFactorAuth.resetTwoFactorAuth',
        userLogin: this.theUser.login,
        passwordConfirmation: this.passwordConfirmation,
      }).catch((e) => {
        this.isResetting2FA = false;
        throw e;
      }).then(() => {
        this.isResetting2FA = false;
        this.theUser.uses_2fa = false;
        this.activeTab = 'basic';

        this.showUserSavedNotification();
      }).finally(() => {
        this.passwordConfirmation = '';
      });
    },
    updateUser() {
      this.isSavingUserInfo = true;
      return AjaxHelper.post(
        {
          method: 'UsersManager.updateUser',
        },
        {
          userLogin: this.theUser.login,
          password: (this.isPasswordModified && this.theUser.password)
            ? this.theUser.password
            : undefined,
          passwordConfirmation: this.passwordConfirmation ? this.passwordConfirmation : undefined,
          email: this.theUser.email,
        },
      ).then(() => {
        this.isSavingUserInfo = false;
        this.passwordConfirmation = '';
        this.isUserModified = true;
        this.isPasswordModified = false;

        this.resetPasswordVar();
        this.showUserSavedNotification();
      }).catch(() => {
        this.isSavingUserInfo = false;
        this.passwordConfirmation = '';
      });
    },
    setSuperUserAccessChecked() {
      this.superUserAccessChecked = !!this.theUser.superuser_access;
    },
    onDoneEditing() {
      this.$emit('done', { isUserModified: this.isUserModified });
    },
  },
  computed: {
    formTitle() {
      return this.isAdd ? translate('UsersManager_InviteNewUser') : translate('UsersManager_EditUser');
    },
    saveButtonLabel() {
      return this.isAdd
        ? translate('UsersManager_InviteUser')
        : translate('UsersManager_SaveBasicInfo');
    },
    isPending() {
      if (!this.user) {
        return true;
      }
      if (this.user.invite_status === 'pending' || Number.isInteger(this.user.invite_status)) {
        return true;
      }
      return false;
    },
    isAdd() {
      return !this.user;
    },
    changePasswordTitle() {
      return translate(
        'UsersManager_AreYouSureChangeDetails',
        `<strong>${this.theUser.login}</strong>`,
      );
    },
  },
});
</script>
