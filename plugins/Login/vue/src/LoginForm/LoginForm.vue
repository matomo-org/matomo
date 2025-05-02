<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <form action="?module=Login" name="login_form" id="login_form" method="post">
    <div class="row">
      <div class="col s12 input-field">
        <input type="text" name="form_login" id="login_form_login" class="input" size="20"
               placeholder="" autocomplete="username" autocorrect="off" autocapitalize="none"
               spellcheck="false" tabindex="10" autofocus="autofocus" v-model="form.username" />
        <label for="login_form_login">
          <i class="icon-user icon"></i> {{ translate('Login_EmailOrUsername') }}
        </label>
      </div>
    </div>

    <div class="row">
      <div class="col s12 input-field">
        <input type="hidden" name="form_nonce" id="login_form_nonce" :value="nonceToken"/>
        <input type="hidden" name="form_redirect" id="login_form_redirect" value=""/>
        <input type="password" name="form_password" id="login_form_password" class="input"
               size="20" placeholder="" autocomplete="current-password" autocorrect="off"
               autocapitalize="none" spellcheck="false" tabindex="20" v-model="form.password"/>
        <label for="login_form_password">
          <i class="icon-locked icon"></i> {{ translate('General_Password') }}
        </label>
      </div>
    </div>

    <div class="row actions">
      <div class="col s6">
        <label>
          <input name="form_rememberme" type="checkbox" id="login_form_rememberme" value="1"
                 tabindex="90"
                 :checked="rememberMe" >
          <span>{{ translate('Login_RememberMe') }}</span>
        </label>
      </div>
      <div class="col s6 right-align">
        <a id="login_form_nav" href="#" tabindex="95"
           :title="translate('Login_ForgotPassword')">
          {{ translate('Login_ForgotPassword') }}
        </a>
      </div>
    </div>
    <div class="row">
      <input class="submit btn btn-block" id="login_form_submit" type="submit"
             :value="translate('Login_LogIn')" tabindex="100"
             v-bind:class="{disabled: !this.isFormComplete}" />
   </div>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    rememberMe: Boolean,
    nonceToken: String,
    attributes: String,
  },
  data() {
    return {
      form: {
        username: '',
        password: '',
      },
    };
  },
  computed: {
    isFormComplete() {
      return this.form.username !== '' && this.form.password !== '';
    },
  },
});
</script>
