(function ($) {
    var twoFactorAuth = {};
    twoFactorAuth.confirmDisable2FA = function (nonce) {
        piwikHelper.modalConfirm('#confirmDisable2FA',
            {yes: function () {
                broadcast.propagateNewPage('module=TwoFactorAuth&action=disableTwoFactorAuth&disableNonce='+ encodeURIComponent(nonce));
            }
        })
    };

    window.twoFactorAuth = twoFactorAuth;
})(jQuery);