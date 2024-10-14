<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="userEditForm"
    :class="{ loading: isSavingUserInfo }"
    :content-title="`${formTitle} ${!isAdd ? `${theUser.login}` : ''}`"
  >
    <div
      class="row"
      v-form=""
    >
      <div v-if="isAdd" class="col s12 m6 invite-notes">
        <div class="form-help">
                     <span v-html="$sanitize(
                          translate('UsersManager_InviteSuccessNotification',
                          [inviteTokenExpiryDays]))">
                     </span>
        </div>
      </div>
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
          >
            <span class="icon-arrow-left">&nbsp;
            </span>{{ translate('UsersManager_BackToUser') }}</a>
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
              autocomplete="off"
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
              autocomplete="new-password"
              :title="translate('General_Password')"
            />
          </div>
          <div class="email-input">
            <Field
              v-model="theUser.email"
              :disabled="isSavingUserInfo || (currentUserRole !== 'superuser' && !isAdd)
                || isShowingPasswordConfirm"
              v-if="currentUserRole === 'superuser' || isAdd"
              uicontrol="text"
              name="user_email"
              autocomplete="off"
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
              <div class="col s12 m6 save-button">
                <SaveButton
                  v-if="currentUserRole === 'superuser' || isAdd"
                  :value="saveButtonLabel"
                  :disabled="isAdd && (!firstSiteAccess || !firstSiteAccess.id)"
                  :saving="isSavingUserInfo"
                  @confirm="saveUserInfo"
                />
              </div>
            </div>
            <p class="resend-notes" v-if="user && isPending"
            >
              {{ translate('UsersManager_InvitationSent') }}
              <span class="resend-link" @click="resendRequestedUser"
                    v-html="$sanitize(translate('UsersManager_ResendInvite') +
                    '/'+ translate('UsersManager_CopyLink'))"></span>
            </p>
            <PasswordConfirmation
              v-model="showPasswordConfirmationForInviteUser"
              @confirmed="inviteUser"
            />
          </div>
          <div
            class="entityCancel"
            v-if="isAdd"
          >
            <a
              href=""
              class="entityCancelLink"
              @click.prevent="onDoneEditing()"
            >
              <span class="icon icon-arrow-left">&nbsp;
              </span>{{ translate('UsersManager_BackToUser') }}</a>
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
          class="superuser-access form-group"
        >
          <p v-if="isMarketplacePluginEnabled">{{ translate('UsersManager_SuperUserIntro1') }}</p>
          <p v-else>{{ translate('UsersManager_SuperUserIntro1WithoutMarketplace') }}</p>
          <p><strong>{{ translate('UsersManager_SuperUserIntro2') }}</strong></p>
          <p><strong>{{ translate('UsersManager_SuperUserIntro3') }}</strong></p>
          <ul class="browser-default">
            <li v-html="$sanitize(translateSuperUserRiskString('Data'))"></li>
            <li v-html="$sanitize(translateSuperUserRiskString('Security'))"></li>
            <li v-html="$sanitize(translateSuperUserRiskString('Misconfiguration'))"></li>
            <li v-html="$sanitize(translateSuperUserRiskString('UserManagement'))"></li>
            <li v-html="$sanitize(translateSuperUserRiskString('ServiceDisruption'))"></li>
            <li
              v-html="$sanitize(translateSuperUserRiskString('Marketplace'))"
              v-if="isPluginsAdminEnabled && isMarketplacePluginEnabled"
            ></li>
            <li v-html="$sanitize(accountabilityRisk)"></li>
            <li v-html="$sanitize(translateSuperUserRiskString('Compliance'))"></li>
          </ul>
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
          <PasswordConfirmation
            v-model="showPasswordConfirmationForSuperUser"
            @confirmed="toggleSuperuserAccess"
            @aborted="setSuperUserAccessChecked()"
          >
            <h2>{{ translate('UsersManager_AreYouSure') }}</h2>
            <p v-if="theUser.superuser_access">
              {{ translate('UsersManager_RemoveSuperuserAccessConfirm') }}
            </p>
            <p v-if="!theUser.superuser_access">
              {{ translate('UsersManager_AddSuperuserAccessConfirm') }}
            </p>
          </PasswordConfirmation>
        </div>
        <div
          v-show="activeTab === '2fa'"
          v-if="currentUserRole === 'superuser' && !isAdd"
          class="twofa-reset form-group"
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
          <PasswordConfirmation
            v-model="showPasswordConfirmationFor2FA"
            @confirmed="reset2FA"
          >
            <h2>{{ translate('UsersManager_AreYouSure') }}</h2>
          </PasswordConfirmation>
        </div>
      </div>
    </div>
    <PasswordConfirmation
      v-model="isShowingPasswordConfirm"
      @confirmed="updateUser"
    >
      <h2 v-html="$sanitize(changePasswordTitle)"></h2>
      <Notification context="info" :noclear="true" v-if="user && isPending">
        <strong v-html="$sanitize(translate('UsersManager_InviteEmailChange'))"></strong>
      </Notification>
    </PasswordConfirmation>
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
  externalLink,
  Matomo,
  Notification,
} from 'CoreHome';
import {
  PasswordConfirmation,
  Form,
  Field,
  SaveButton,
} from 'CorePluginsAdmin';
import UserPermissionsEdit from '../UserPermissionsEdit/UserPermissionsEdit.vue';
import User from '../User';

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
  permissionsForIdSite: string | number;
  isSavingUserInfo: boolean;
  userHasAccess: boolean;
  firstSiteAccess: SiteRef | null;
  isUserModified: boolean;
  isPasswordModified: boolean;
  superUserAccessChecked: boolean | null;
  showPasswordConfirmationForSuperUser: boolean;
  showPasswordConfirmationFor2FA: boolean;
  showPasswordConfirmationForInviteUser: boolean;
  isResetting2FA: boolean;
  isShowingPasswordConfirm: boolean;
}

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
    inviteTokenExpiryDays: {
      type: String,
      required: true,
    },
    activatedPlugins: {
      type: Array,
      required: true,
    },
  },
  components: {
    Notification,
    ContentBlock,
    Field,
    SaveButton,
    UserPermissionsEdit,
    PasswordConfirmation,
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
      isPasswordModified: false,
      superUserAccessChecked: null,
      showPasswordConfirmationForSuperUser: false,
      showPasswordConfirmationFor2FA: false,
      showPasswordConfirmationForInviteUser: false,
      isResetting2FA: false,
      isShowingPasswordConfirm: false,
    };
  },
  emits: ['done', 'updated', 'resendInvite'],
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
      this.showPasswordConfirmationForSuperUser = true;
    },
    confirmReset2FA() {
      this.showPasswordConfirmationFor2FA = true;
    },
    toggleSuperuserAccess(password: string) {
      this.isSavingUserInfo = true;
      AjaxHelper.post(
        {
          method: 'UsersManager.setSuperUserAccess',
        },
        {
          userLogin: this.theUser.login,
          hasSuperUserAccess: this.theUser.superuser_access ? '0' : '1',
          passwordConfirmation: password,
        },
      ).then(() => {
        this.theUser.superuser_access = !this.theUser.superuser_access;
      }).catch(() => {
        // ignore error (still displayed to user)
      }).then(() => { // eslint-disable-line
        this.isSavingUserInfo = false;
        this.setSuperUserAccessChecked();
      });
    },
    saveUserInfo() {
      if (this.isAdd) {
        this.showPasswordConfirmationForInviteUser = true;
      } else {
        this.isShowingPasswordConfirm = true;
      }
    },
    resendRequestedUser() {
      this.$emit('resendInvite', {
        user: this.user,
      });
    },
    inviteUser(password: string) {
      this.isSavingUserInfo = true;
      return AjaxHelper.post(
        {
          method: 'UsersManager.inviteUser',
        },
        {
          userLogin: this.theUser.login,
          email: this.theUser.email,
          initialIdSite: this.firstSiteAccess ? this.firstSiteAccess.id : undefined,
          passwordConfirmation: password,
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
        this.$emit('updated', { user: readonly(this.theUser) });
      });
    },
    resetPasswordVar() {
      if (!this.isAdd) {
        // make sure password is not stored in the client after update/save
        this.theUser.password = 'XXXXXXXX';
      }
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
    reset2FA(password: string) {
      this.isResetting2FA = true;
      return AjaxHelper.post({
        method: 'TwoFactorAuth.resetTwoFactorAuth',
      }, {
        userLogin: this.theUser.login,
        passwordConfirmation: password,
      }).catch((e) => {
        this.isResetting2FA = false;
        throw e;
      }).then(() => {
        this.isResetting2FA = false;
        this.theUser.uses_2fa = false;
        this.activeTab = 'basic';

        this.showUserSavedNotification();
      });
    },
    updateUser(password: string) {
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
          passwordConfirmation: password,
          email: this.theUser.email,
        },
      ).then(() => {
        this.isSavingUserInfo = false;
        this.isUserModified = true;
        this.isPasswordModified = false;

        this.resetPasswordVar();
        this.showUserSavedNotification();
        this.$emit('updated', { user: readonly(this.theUser) });
      }).catch(() => {
        this.isSavingUserInfo = false;
      });
    },
    setSuperUserAccessChecked() {
      this.superUserAccessChecked = !!this.theUser.superuser_access;
    },
    onDoneEditing() {
      this.$emit('done', { isUserModified: this.isUserModified });
    },
    translateSuperUserRiskString(item: string) {
      return translate(
        `UsersManager_SuperUserRisk${item}`,
        '<strong>',
        '</strong>',
      );
    },
  },
  computed: {
    formTitle() {
      return this.isAdd ? translate('UsersManager_InviteNewUser') : '';
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
    isPluginsAdminEnabled() {
      return Matomo.config.enable_plugins_admin;
    },
    isActivityLogPluginEnabled() {
      return this.activatedPlugins.includes('ActivityLog');
    },
    isMarketplacePluginEnabled() {
      return this.activatedPlugins.includes('Marketplace');
    },
    isProfessionalServicesPluginEnabled() {
      return this.activatedPlugins.includes('ProfessionalServices');
    },
    accountabilityRisk() {
      const riskInfo = this.translateSuperUserRiskString('Accountability');
      let pluginInfo = '';

      if (this.isPluginsAdminEnabled && this.isProfessionalServicesPluginEnabled) {
        if (this.isActivityLogPluginEnabled) {
          pluginInfo = translate(
            'UsersManager_SuperUserRiskAccountabilityCheckActivityLog',
            '<a href="?module=ActivityLog&action=index" rel="noreferrer noopener" target="_blank">', '</a>',
          );
        } else if (this.isMarketplacePluginEnabled) {
          pluginInfo = translate(
            'UsersManager_SuperUserRiskAccountabilityGetActivityLogPlugin',
            externalLink('https://plugins.matomo.org/ActivityLog'), '</a>',
          );
        }
      }

      return pluginInfo ? `${riskInfo} ${pluginInfo}` : riskInfo;
    },
  },
});
</script>
