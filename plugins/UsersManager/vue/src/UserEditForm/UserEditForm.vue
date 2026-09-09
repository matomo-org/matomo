<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="userEditForm [&_.card-title]:mt-[45px] [&_.card-title]:mb-0 [&_.card-title]:pl-[15px]"
    :class="{ loading: isSavingUserInfo }"
    :content-title="theUser.login"
  >
    <div
      class="row"
      v-form
    >
      <div
        class="col m2 entityList"
      >
        <ul class="listCircle -ml-[15px]">
          <li
            :class="{active: activeTab === 'basic'}"
            class="menuBasicInfo"
          >
            <a
              href=""
              :class="{ 'font-bold': activeTab === 'basic' }"
              @click.prevent="activeTab = 'basic'"
            >{{ translate('UsersManager_BasicInformation') }}</a>
          </li>
          <li
            :class="{active: activeTab === 'permissions'}"
            class="menuPermissions"
          >
            <a
              href=""
              class="mr-[3.5px]"
              :class="{ 'font-bold': activeTab === 'permissions' }"
              @click.prevent="activeTab = 'permissions'"
            >
              {{ translate('UsersManager_Permissions') }}
            </a>
            <span
              class="icon-warning text-[17px] bg-[#fefbe9] dark:bg-transparent border-[#a18a0b]
                before:content-['\e621'] before:text-[#a18a0b] hover:opacity-100"
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
              :class="{ 'font-bold': activeTab === 'superuser' }"
              @click.prevent="activeTab = 'superuser'"
            >{{ translate('UsersManager_SuperUserAccess') }}</a>
          </li>
          <li
            :class="{active: activeTab === '2fa'}"
            class="menuUserTwoFa"
            v-if="currentUserRole === 'superuser' && theUser.uses_2fa"
          >
            <a
              href=""
              :class="{ 'font-bold': activeTab === '2fa' }"
              @click.prevent="activeTab = '2fa'"
            >{{ translate('UsersManager_TwoFactorAuthentication') }}</a>
          </li>
        </ul>
        <div class="save-button-spacer hide-on-small-only h-12">
        </div>
        <div
          class="entityCancel absolute top-5"
          @click.prevent="onDoneEditing()"
        >
          <a
            href=""
            class="entityCancelLink text-[14px]"
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
              :disabled="true"
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
              :disabled="isSavingUserInfo || (currentUserRole !== 'superuser')
                || isShowingPasswordConfirm"
              @update:model-value="theUser.password = $event; isPasswordModified = true"
              uicontrol="password"
              name="user_password"
              autocomplete="new-password"
              :title="translate('General_Password')"
              v-auto-clear-password
              :ui-control-attributes="{
                passwordStrengthValidationRules: passwordStrengthValidationRules,
              }"
            />
          </div>
          <div>
            <Field
              v-model="theUser.email"
              :disabled="isSavingUserInfo || (currentUserRole !== 'superuser')
                || isShowingPasswordConfirm"
              v-if="currentUserRole === 'superuser'"
              uicontrol="text"
              name="user_email"
              autocomplete="off"
              :maxlength="100"
              :title="translate('UsersManager_Email')"
            />
          </div>
          <div>
            <div class="form-group row relative">
              <div class="col s12 m6 save-button mt-[3em]">
                <SaveButton
                  v-if="currentUserRole === 'superuser'"
                  class="absolute bottom-0 [&_.loadingPiwik]:absolute"
                  :value="translate('UsersManager_SaveBasicInfo')"
                  :saving="isSavingUserInfo"
                  @confirm="isShowingPasswordConfirm = true"
                />
              </div>
            </div>
            <p class="resend-notes mt-[3em] text-[16px]" v-if="user && isPending"
            >
              {{ translate('UsersManager_InvitationSent') }}
              <span class="resend-link text-link underline cursor-pointer" @click="resendRequestedUser"
                    v-html="$sanitize(translate('UsersManager_ResendInvite') +
                    '/'+ translate('UsersManager_CopyLink'))"></span>
            </p>
          </div>
        </div>
        <div
          v-show="activeTab === 'permissions'"
          class="user-permissions mb-8"
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
          v-if="activeTab === 'superuser' && currentUserRole === 'superuser'"
          class="superuser-access form-group mb-8"
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
          <div
            :class="{ 'disabled': isCurrentUser }"
            class="[&.disabled_.checkbox>label>span]:opacity-50 [&.disabled_.checkbox>label>span]:cursor-not-allowed
              [&.disabled_.checkbox>label>span:hover]:bg-background"
            :title="superUserAccessTooltipText"
          >
            <Field
              v-model="superUserAccessChecked"
              @update:model-value="confirmSuperUserChange()"
              :disabled="isCurrentUser"
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
          v-if="currentUserRole === 'superuser'"
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
import { defineComponent, PropType } from 'vue';
import {
  ContentBlock,
  translate,
  AjaxHelper,
  NotificationsStore,
  externalLink,
  Matomo,
  Notification,
  AutoClearPassword,
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

export interface UserEditFormState {
  theUser: User;
  activeTab: string;
  permissionsForIdSite: string | number;
  isSavingUserInfo: boolean;
  userHasAccess: boolean;
  isUserModified: boolean;
  isPasswordModified: boolean;
  superUserAccessChecked: boolean | null;
  showPasswordConfirmationForSuperUser: boolean;
  showPasswordConfirmationFor2FA: boolean;
  isResetting2FA: boolean;
  isShowingPasswordConfirm: boolean;
}

export default defineComponent({
  props: {
    user: Object as PropType<User>,
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
    activatedPlugins: {
      type: Array,
      required: true,
    },
    passwordStrengthValidationRules: {
      type: Array,
      default: () => [],
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
    AutoClearPassword,
  },
  data(): UserEditFormState {
    return {
      theUser: (this.user as User) || { ...DEFAULT_USER },
      activeTab: 'basic',
      permissionsForIdSite: 1,
      isSavingUserInfo: false,
      userHasAccess: true,
      isUserModified: false,
      isPasswordModified: false,
      superUserAccessChecked: null,
      showPasswordConfirmationForSuperUser: false,
      showPasswordConfirmationFor2FA: false,
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
    this.onUserChange({ ...this.user } as User);
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
        this.theUser = { ...this.theUser, superuser_access: !this.theUser.superuser_access };
      }).catch(() => {
        // ignore error (still displayed to user)
      }).finally(() => { // eslint-disable-line
        this.isSavingUserInfo = false;
        this.setSuperUserAccessChecked();
      });
    },
    resendRequestedUser() {
      this.$emit('resendInvite', {
        user: this.user,
      });
    },
    resetPasswordVar() {
      // make sure password is not stored in the client after update/save
      this.theUser.password = 'XXXXXXXX';
    },
    showUserSavedNotification() {
      NotificationsStore.show({
        message: translate('General_YourChangesHaveBeenSaved'),
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
        this.$emit('updated', { user: this.theUser });
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
    isPending() {
      if (!this.user) {
        return true;
      }
      if (this.user.invite_status === 'pending' || Number.isInteger(this.user.invite_status)) {
        return true;
      }
      return false;
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
    isCurrentUser(): boolean {
      return this.theUser.login === Matomo.userLogin;
    },
    superUserAccessTooltipText() {
      if (this.isCurrentUser) {
        return translate('UsersManager_CannotRevokeOwnSuperuserAccess');
      }

      return '';
    },
  },
});
</script>
