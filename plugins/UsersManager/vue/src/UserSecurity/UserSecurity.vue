<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <ContentBlock
      v-if="isUsersAdminEnabled"
      :content-title="translate('General_ChangePassword')"
      feature="true"
    >
      <form
        id="userSettingsTable"
        method="post"
        :action="recordPasswordChangeAction"
      >
        <input type="hidden" :value="changePasswordNonce" name="nonce"/>

        <div v-if="isValidHost">
          <Field
            uicontrol="password"
            name="password"
            :autocomplete="'off'"
            v-model="password"
            :title="translate('Login_NewPassword')"
            :inline-help="translate('UsersManager_IfYouWouldLikeToChangeThePasswordTypeANewOne')"
            v-auto-clear-password
            :ui-control-attributes="{
              passwordStrengthValidationRules: passwordStrengthValidationRules,
            }"
            @check:isValid="setPasswordStrengthValidation($event, 'passwordStrengthMet')"
          />

          <Field
            uicontrol="password"
            name="passwordBis"
            :autocomplete="'off'"
            v-model="passwordBis"
            :title="translate('Login_NewPasswordRepeat')"
            :inline-help="translate('UsersManager_TypeYourPasswordAgain')"
            v-auto-clear-password
            :ui-control-attributes="{
              passwordStrengthValidationRules: passwordStrengthValidationRules,
            }"
            @check:isValid="setPasswordStrengthValidation($event, 'passwordBisStrengthMet')"
          />

          <Field
            uicontrol="password"
            name="passwordConfirmation"
            :autocomplete="'off'"
            v-model="passwordConfirmation"
            :title="translate('UsersManager_YourCurrentPassword')"
            :inline-help="translate('UsersManager_TypeYourCurrentPassword')"
            v-auto-clear-password
          />

          <div class="alert alert-info" v-html="$sanitize(changePasswordInfoNotification)"></div>

          <input
            type="submit"
            :value="translate('General_Save')"
            class="btn"
            :disabled="!isPasswordChangeFormSubmitEnabled"
          />
        </div>

        <div v-if="!isValidHost">
          <div class="alert alert-danger">
            {{ translate('UsersManager_InjectedHostCannotChangePwd', invalidHost) }}
            <span v-if="!isSuperUser" v-html="$sanitize(emailYourAdminText)"></span>
          </div>
        </div>
      </form>
    </ContentBlock>

    <div ref="afterPassword">
      <component
        v-if="isUsersAdminEnabled && afterPasswordComponent"
        :is="afterPasswordComponent"
      />
    </div>

    <a name="authtokens" id="authtokens"></a>
    <ContentBlock :content-title="translate('UsersManager_AuthTokens')">
      <p>
        {{ translate('UsersManager_TokenAuthIntro') }}
        {{ translate('UsersManager_ExpiredTokensDeleteAutomatically') }}
      </p>
      <table v-content-table class="listAuthTokens">
        <thead>
        <tr>
          <th>{{ translate('General_CreationDate') }}</th>
          <th>{{ translate('General_Description') }}</th>
          <th>{{ translate('UsersManager_LastUsed') }}</th>
          <th>{{ translate('UsersManager_SecureUseOnly') }}</th>
          <th>{{ translate('UsersManager_ExpireDate') }}</th>
          <th>{{ translate('General_Actions') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="!tokens?.length">
          <td
            :colspan="5"
            v-html="$sanitize(noTokenCreatedYetText)"
          ></td>
        </tr>
        <tr v-for="theToken in (tokens || [])" :key="theToken.idusertokenauth">
          <td><span class="creationDate">{{ theToken.date_created }}</span></td>
          <td>{{ theToken.description }}</td>
          <td>
            {{ theToken.last_used ? theToken.last_used : translate('General_Never') }}
          </td>
          <td>
            {{ parseInt(theToken.secure_only, 10) === 1 ?
               translate('General_Yes') : translate('General_No') }}
          </td>
          <td>
            {{ theToken.date_expired ? theToken.date_expired : translate('General_Never') }}
          </td>
          <td>
            <form
              method="post"
              :action="deleteTokenAction"
              style="display: inline"
            >
              <input name="nonce" type="hidden" :value="deleteTokenNonce"/>
              <input name="idtokenauth" type="hidden" :value="theToken.idusertokenauth"/>
              <button
                type="submit"
                class="table-action"
                :title="translate('General_Delete')"
              >
                <span class="icon-delete"></span>
              </button>
            </form>
          </td>
        </tr>
        </tbody>
      </table>

      <div class="tableActionBar">
        <a :href="addNewTokenLink" class="addNewToken">
          <span class="icon-add"></span>
          {{ translate('UsersManager_CreateNewToken') }}
        </a>

        <form
          v-if="tokens?.length"
          method="post"
          :action="deleteTokenAction"
          style="display: inline"
        >
          <input name="nonce" type="hidden" :value="deleteTokenNonce">
          <input name="idtokenauth" type="hidden" value="all">
          <button type="submit" class="table-action delete-all-tokens">
            <span class="icon-delete"></span> {{ translate('UsersManager_DeleteAllTokens') }}
          </button>
        </form>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw } from 'vue';
import {
  ContentBlock,
  ContentTable, Matomo,
  MatomoUrl,
  translate,
  AutoClearPassword,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

interface UserSecurityState {
  password: string;
  passwordBis: string;
  passwordConfirmation: string;
  passwordStrengthMet: boolean;
  passwordBisStrengthMet: boolean;
}

export default defineComponent({
  props: {
    deleteTokenNonce: String,
    tokens: Array,
    isUsersAdminEnabled: Boolean,
    changePasswordNonce: String,
    isValidHost: Boolean,
    isSuperUser: Boolean,
    invalidHost: String,
    afterPasswordEventContent: String,
    invalidHostMailLinkStart: String,
    passwordStrengthValidationRules: Array,
  },
  components: {
    ContentBlock,
    Field,
  },
  directives: {
    ContentTable,
    AutoClearPassword,
  },
  data(): UserSecurityState {
    return {
      password: '',
      passwordBis: '',
      passwordConfirmation: '',
      passwordStrengthMet: false,
      passwordBisStrengthMet: false,
    };
  },
  mounted() {
    const afterPassword = this.$refs.afterPassword as HTMLElement;
    Matomo.helper.compileVueEntryComponents(afterPassword);
  },
  methods: {
    setPasswordStrengthValidation(event: boolean, field: string) {
      if (field === 'passwordStrengthMet') {
        this.passwordStrengthMet = event;
      }
      if (field === 'passwordBisStrengthMet') {
        this.passwordBisStrengthMet = event;
      }
    },
  },
  computed: {
    recordPasswordChangeAction() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'recordPasswordChange',
      })}`;
    },
    emailYourAdminText() {
      return translate(
        'UsersManager_EmailYourAdministrator',
        this.invalidHostMailLinkStart || '',
        '</a>',
      );
    },
    noTokenCreatedYetText() {
      const addNewTokenLink = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'addNewToken',
      })}`;

      return translate(
        'UsersManager_NoTokenCreatedYetCreateNow',
        `<a href="${addNewTokenLink}">`,
        '</a>',
      );
    },
    changePasswordInfoNotification() {
      const sessionsLoggedOut = translate('UsersManager_PasswordChangeTerminatesOtherSessions');
      let tokensNotRevoked = '';
      if (this.tokens?.length) {
        tokensNotRevoked = translate(
          'UsersManager_PasswordChangeDoesNotRevokeAuthTokens',
          `<a href="#authtokens">${translate('UsersManager_AuthTokens')}</a>`,
        );
      }

      return [sessionsLoggedOut, tokensNotRevoked].filter((item) => item).join('<br><br>');
    },
    deleteTokenAction() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'deleteToken',
      })}`;
    },
    addNewTokenLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'addNewToken',
      })}`;
    },
    afterPasswordComponent() {
      if (!this.afterPasswordEventContent) {
        return null;
      }

      const afterPassword = this.$refs.afterPassword as HTMLElement;
      return markRaw({
        template: this.afterPasswordEventContent,
        beforeUnmount() {
          Matomo.helper.destroyVueComponent(afterPassword);
        },
      });
    },
    isPasswordChangeFormSubmitEnabled() {
      return this.passwordConfirmation
        && (
          !this.passwordStrengthValidationRules?.length
          || (
            this.passwordStrengthValidationRules?.length
            && this.passwordStrengthMet
            && this.passwordBisStrengthMet
          )
        );
    },
  },
});
</script>
