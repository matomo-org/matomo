<?php

return array(
    'Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication' => DI\decorate(function ($previous) {
        /** @var Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication $previous */

        $secret = \Piwik\Plugins\TwoFactorAuth\tests\Fixtures\TwoFactorFixture::USER_2FA_SECRET;

        $fakeCorrectAuthCode = \Piwik\Container\StaticContainer::get('test.vars.fakeCorrectAuthCode');
        if (!empty($fakeCorrectAuthCode)) {
            require_once PIWIK_DOCUMENT_ROOT . '/libs/Authenticator/TwoFactorAuthenticator.php';
            $authenticator = new \TwoFactorAuthenticator();
            foreach ([$_GET, $_POST, $_REQUEST] as $params) {
                $params['authcode'] = $authenticator->getCode($secret);
                $params['authCode'] = $params['authcode'];
            }

            if (!\Piwik\Session::isStarted()) {
                \Piwik\Session::start();
            }

            $session = new \Piwik\Session\SessionNamespace('TwoFactorAuthenticator');
            $session->secret = $secret;
        }

        return $previous;
    }),
    'Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao' => DI\decorate(function ($previous) {
        /** @var Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao $previous */

        $restoreCodes = \Piwik\Container\StaticContainer::get('test.vars.restoreRecoveryCodes');
        if (!empty($restoreCodes)) {
            // we ensure this recovery code always works for those users
            $previous->insertRecoveryCode('with2FA', '123456');
            $previous->insertRecoveryCode('with2FADisable', '123456');
        }

        return $previous;
    }),
    'Piwik\Plugins\TwoFactorAuth\SystemSettings' => DI\decorate(function ($previous) {
        /** @var Piwik\Plugins\TwoFactorAuth\SystemSettings $previous */

        Piwik\Access::doAsSuperUser(function () use ($previous) {
            $requireTwoFa = \Piwik\Container\StaticContainer::get('test.vars.requireTwoFa');
            if (!empty($requireTwoFa)) {
                $previous->twoFactorAuthRequired->setValue(1);
            } else {
                $previous->twoFactorAuthRequired->setValue(0);
            }
        });

        return $previous;
    })
);
