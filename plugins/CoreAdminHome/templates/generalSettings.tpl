{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='UsersManager'}

{if $isSuperUser}
    <h2>{'General_GeneralSettings'|translate}</h2>
    {ajaxErrorDiv id=ajaxError}
    {ajaxLoadingDiv id=ajaxLoading}
    <table class="adminTable" style='width:900px;'>
        <tr>
            <td style='width:400px'>{'General_AllowPiwikArchivingToTriggerBrowser'|translate}</td>
            <td style='width:220px'>
                <fieldset>
                    <label><input type="radio" value="1" name="enableBrowserTriggerArchiving"{if $enableBrowserTriggerArchiving==1} checked="checked"{/if} />
                        {'General_Yes'|translate} <br/>
                        <span class="form-description">{'General_Default'|translate}</span>
                    </label><br/><br/>

                    <label><input type="radio" value="0" name="enableBrowserTriggerArchiving"{if $enableBrowserTriggerArchiving==0} checked="checked"{/if} />
                        {'General_No'|translate} <br/>
                        <span class="form-description">{'General_ArchivingTriggerDescription'|translate:"<a href='?module=Proxy&action=redirect&url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>":"</a>"}</span>
                    </label>
                </fieldset>
            <td>
                {capture assign=browserArchivingHelp}
                    {'General_ArchivingInlineHelp'|translate}
                    <br/>
                    {'General_SeeTheOfficialDocumentationForMoreInformation'|translate:"<a href='?module=Proxy&action=redirect&url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>":"</a>"}
                {/capture}
                {$browserArchivingHelp|inlineHelp}
            </td>
        </tr>
        <tr>
            <td><label for="todayArchiveTimeToLive">{'General_ReportsContainingTodayWillBeProcessedAtMostEvery'|translate}</label></td>
            <td>
                {'General_NSeconds'|translate:"<input size='3' value='$todayArchiveTimeToLive' id='todayArchiveTimeToLive' />"}
            </td>
            <td width='450px'>
                {capture assign=archiveTodayTTLHelp}
                    {if $showWarningCron}
                        <strong>
                            {'General_NewReportsWillBeProcessedByCron'|translate}<br/>
                            {'General_ReportsWillBeProcessedAtMostEveryHour'|translate}
                            {'General_IfArchivingIsFastYouCanSetupCronRunMoreOften'|translate}<br/>
                        </strong>
                    {/if}
                    {'General_SmallTrafficYouCanLeaveDefault'|translate:10}
                    <br/>
                    {'General_MediumToHighTrafficItIsRecommendedTo'|translate:1800:3600}
                {/capture}
                {$archiveTodayTTLHelp|inlineHelp}
            </td>
        </tr>
        <tr>
            <td style='width:400px'>{'CoreAdminHome_CheckReleaseGetVersion'|translate}</td>
            <td style='width:220px'>
                <fieldset>
                    <label><input type="radio" value="0" name="enableBetaReleaseCheck"{if $enableBetaReleaseCheck==0} checked="checked"{/if} />
                        {'CoreAdminHome_LatestStableRelease'|translate} <br/>
                        <span class="form-description">{'General_Recommended'|translate}</span>
                    </label><br/><br/>

                    <label><input type="radio" value="1" name="enableBetaReleaseCheck"{if $enableBetaReleaseCheck==1} checked="checked"{/if} />
                        {'CoreAdminHome_LatestBetaRelease'|translate} <br/>
                        <span class="form-description">{'CoreAdminHome_ForBetaTestersOnly'|translate}</span>
                    </label>
                </fieldset>
            <td>
                {capture assign=checkReleaseHelp}
                    {'CoreAdminHome_DevelopmentProcess'|translate:"<a href='?module=Proxy&action=redirect&url=http://piwik.org/participate/development-process/' target='_blank'>":"</a>"}
                    <br/>
                    {'CoreAdminHome_StableReleases'|translate:"<a href='?module=Proxy&action=redirect&url=http://piwik.org/participate/user-feedback/' target='_blank'>":"</a>"}
                {/capture}
                {$checkReleaseHelp|inlineHelp}
            </td>
        </tr>
    </table>
    <h2>{'CoreAdminHome_EmailServerSettings'|translate}</h2>
    <div id='emailSettings'>
        <table class="adminTable" style='width:600px;'>
            <tr>
                <td>{'General_UseSMTPServerForEmail'|translate}<br/>
                    <span class="form-description">{'General_SelectYesIfYouWantToSendEmailsViaServer'|translate}</span>
                </td>
                <td style='width:200px'>
                    <label><input type="radio" name="mailUseSmtp" value="1" {if $mail.transport eq 'smtp'} checked {/if}/> {'General_Yes'|translate}</label>
                    <label><input type="radio" name="mailUseSmtp" value="0"
                                  style="margin-left:20px;" {if $mail.transport eq ''} checked {/if}/>  {'General_No'|translate}</label>
                </td>
            </tr>
        </table>
    </div>
    <div id='smtpSettings'>
        <table class="adminTable" style='width:550px;'>
            <tr>
                <td><label for="mailHost">{'General_SmtpServerAddress'|translate}</label></td>
                <td style='width:200px'><input type="text" id="mailHost" value="{$mail.host|escape}"></td>
            </tr>
            <tr>
                <td><label for="mailPort">{'General_SmtpPort'|translate}</label><br/>
                    <span class="form-description">{'General_OptionalSmtpPort'|translate}</span></td>
                <td><input type="text" id="mailPort" value="{$mail.port}"></td>
            </tr>
            <tr>
                <td><label for="mailType">{'General_AuthenticationMethodSmtp'|translate}</label><br/>
                    <span class="form-description">{'General_OnlyUsedIfUserPwdIsSet'|translate}</span>
                </td>
                <td>
                    <select id="mailType">
                        <option value="" {if $mail.type eq ''} selected="selected" {/if}></option>
                        <option id="plain" {if $mail.type eq 'Plain'} selected="selected" {/if} value="Plain">Plain</option>
                        <option id="login" {if $mail.type eq 'Login'} selected="selected" {/if} value="Login"> Login</option>
                        <option id="cram-md5" {if $mail.type eq 'Crammd5'} selected="selected" {/if} value="Crammd5"> Crammd5</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="mailUsername">{'General_SmtpUsername'|translate}</label><br/>
                    <span class="form-description">{'General_OnlyEnterIfRequired'|translate}</span></td>
                <td>
                    <input type="text" id="mailUsername" value="{$mail.username|escape}"/>
                </td>
            </tr>
            <tr>
                <td><label for="mailPassword">{'General_SmtpPassword'|translate}</label><br/>
				<span class="form-description">{'General_OnlyEnterIfRequiredPassword'|translate}<br/>
                    {'General_WarningPasswordStored'|translate:"<strong>":"</strong>"}</span>
                </td>
                <td>
                    <input type="password" id="mailPassword" value="{$mail.password|escape}"/>
                </td>
            </tr>
            <tr>
                <td><label for="mailEncryption">{'General_SmtpEncryption'|translate}</label><br/>
                    <span class="form-description">{'General_EncryptedSmtpTransport'|translate}</span></td>
                <td>
                    <select id="mailEncryption">
                        <option value="" {if $mail.encryption eq ''} selected="selected" {/if}></option>
                        <option id="ssl" {if $mail.encryption eq 'ssl'} selected="selected" {/if} value="ssl">SSL</option>
                        <option id="tls" {if $mail.encryption eq 'tls'} selected="selected" {/if} value="tls">TLS</option>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <div class="ui-confirm" id="confirmTrustedHostChange">
        <h2>{'CoreAdminHome_TrustedHostConfirm'|translate}</h2>
        <input role="yes" type="button" value="{'General_Yes'|translate}"/>
        <input role="no" type="button" value="{'General_No'|translate}"/>
    </div>
    <h2 id="trustedHostsSection">{'CoreAdminHome_TrustedHostSettings'|translate}</h2>
    <div id='trustedHostSettings'>
        {* untrusted host warning (display again) *}
        {if isset($isValidHost) && isset($invalidHostMessage) && !$isValidHost}
            <div class="ajaxSuccess">
                <a style="float:right" href="http://piwik.org/faq/troubleshooting/#faq_171" target="_blank"><img src="themes/default/images/help_grey.png"/></a>
                <strong>{'General_Warning'|translate}:&nbsp;</strong>{$invalidHostMessage}
            </div>
        {/if}
        {if count($trustedHosts) eq 1 && (!isset($isValidHost) || $isValidHost)}
            {'CoreAdminHome_PiwikIsInstalledAt'|translate}:&nbsp;&nbsp;
            <input name="trusted_host" type="text" value="{$trustedHosts[0]}"/>
        {else}
            <p>{'CoreAdminHome_PiwikIsInstalledAt'|translate}:</p>
            <table class="adminTable">
                <tr>
                    <th style="width:250px">{'CoreAdminHome_ValidPiwikHostname'|translate}</th>
                    <th style="width:10px">&nbsp;</th>
                </tr>
                {foreach from=$trustedHosts item=host key=hostIdx}
                    <tr>
                        <td><input name="trusted_host" type="text" value="{$host}"/></td>
                        <td>
                            <a href="#" class="remove-trusted-host">x</a>
                        </td>
                    </tr>
                {/foreach}
            </table>
            <div class="adminTable add-trusted-host-container">
                <a href="#" class="add-trusted-host"><em>{'General_Add'|translate}</em></a>
            </div>
        {/if}
    </div>
    <h2>{'CoreAdminHome_BrandingSettings'|translate}</h2>
    <div id='brandSettings'>
        {'CoreAdminHome_CustomLogoHelpText'|translate}
        <table class="adminTable" style='width:600px;'>
            <tr>
                <td> {'CoreAdminHome_UseCustomLogo'|translate}</td>
                <td style='width:200px'>
                    <label><input type="radio" name="useCustomLogo" value="1" {if $branding.use_custom_logo == 1} checked {/if}/> {'General_Yes'|translate}
                    </label>
                    <label><input type="radio" name="useCustomLogo" value="0"
                                  style="margin-left:20px;" {if $branding.use_custom_logo == 0} checked {/if}/>  {'General_No'|translate}</label>
                </td>
            </tr>
        </table>
    </div>
    <div id='logoSettings'>
        {capture assign=giveUsFeedbackText}"{'General_GiveUsYourFeedback'|translate}"{/capture}
        {capture assign=customLogoHelp}
            {'CoreAdminHome_CustomLogoFeedbackInfo'|translate:$giveUsFeedbackText:"<a href='?module=CorePluginsAdmin&action=index' target='_blank'>":"</a>"}
        {/capture}
        {$customLogoHelp|inlineHelp}
        <form id="logoUploadForm" method="post" enctype="multipart/form-data" action="index.php?module=CoreAdminHome&format=json&action=uploadCustomLogo">
            <table class="adminTable" style='width:550px;'>
                <tr>
                    {if $logosWriteable}
                        <td><label for="customLogo">{'CoreAdminHome_LogoUpload'|translate}:<br/>
                                <span class="form-description">{'CoreAdminHome_LogoUploadDescription'|translate:"JPG / PNG / GIF":110}</span></label></td>
                        <td style='width:200px'>
                            <input name="customLogo" type="file" id="customLogo"/><img src="themes/logo.png?r={math equation='rand(10,1000)'}" id="currentLogo"
                                                                                       height="150"/>
                        </td>
                    {else}
                        <td>
                            <span class="ajaxSuccess">{'CoreAdminHome_LogoNotWriteable'|translate:"<ul style='list-style: disc inside;'><li>/themes/</li><li>/themes/logo.png</li><li>/themes/logo-header.png</li></ul>"}</span>
                        </td>
                    {/if}
                </tr>
            </table>
        </form>
    </div>
    <input type="submit" value="{'General_Save'|translate}" id="generalSettingsSubmit" class="submit"/>
    <br/>
    <br/>
    {capture assign=clickDeleteLogSettings}{'PrivacyManager_DeleteDataSettings'|translate}{/capture}
    <h2>{'PrivacyManager_DeleteDataSettings'|translate}</h2>
    <p>
        {'PrivacyManager_DeleteDataDescription'|translate} {'PrivacyManager_DeleteDataDescription2'|translate}
        <br/>
        <a href='{url module="PrivacyManager" action="privacySettings"}#deleteLogsAnchor'>
            {'PrivacyManager_ClickHereSettings'|translate:"'$clickDeleteLogSettings'"}
        </a>
    </p>
{/if}
<h2>{'CoreAdminHome_OptOutForYourVisitors'|translate}</h2>

<p>{'CoreAdminHome_OptOutExplanation'|translate}
    {capture name=optOutUrl}{$piwikUrl}index.php?module=CoreAdminHome&action=optOut&language={$language}{/capture}
    {assign var=optOutUrl value=$smarty.capture.optOutUrl}
    {capture name=iframeOptOut}
        <iframe frameborder="no" width="600px" height="200px" src="{$smarty.capture.optOutUrl}"></iframe>{/capture}
    <code>{$smarty.capture.iframeOptOut|escape:'html'}</code>
    <br/>
    {'CoreAdminHome_OptOutExplanationBis'|translate:"<a href='$optOutUrl' target='_blank'>":"</a>"}
</p>

{include file="CoreAdminHome/templates/footer.tpl"}
