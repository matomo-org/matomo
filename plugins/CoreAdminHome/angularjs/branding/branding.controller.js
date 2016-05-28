/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller to save mail smtp settings
 */
(function () {
    angular.module('piwikApp').controller('BrandingController', BrandingController);

    BrandingController.$inject = ['$scope', 'piwikApi'];

    function BrandingController($scope, piwikApi) {

        var self = this;
        this.isLoading = false;

        function refreshCustomLogo() {
            var selectors = ['#currentLogo', '#currentFavicon'];
            var index;
            for (index = 0; index < selectors.length; index++) {
                var imageDiv = $(selectors[index]);
                if (imageDiv && imageDiv.data("src") && imageDiv.data("srcExists")) {
                    var logoUrl = imageDiv.data("src");
                    imageDiv.attr("src", logoUrl + "?" + (new Date()).getTime());
                    imageDiv.show();
                } else {
                    imageDiv.hide();
                }
            }
        }

        this.updateLogo = function () {
            var isSubmittingLogo = (this.customLogo != undefined && this.customLogo != '');
            var isSubmittingFavicon = (this.customFavicon != undefined && this.customFavicon != '');

            if (!isSubmittingLogo && !isSubmittingFavicon) {
                return;
            }

            var $uploadError = $('.uploaderror');
            $uploadError.fadeOut();
            var frameName = "upload" + (new Date()).getTime();
            var uploadFrame = $("<iframe name=\"" + frameName + "\" />");
            uploadFrame.css("display", "none");
            uploadFrame.load(function (data) {
                setTimeout(function () {
                    var frameContent = $(uploadFrame.contents()).find('body').html();
                    frameContent = $.trim(frameContent);

                    if ('0' === frameContent) {
                        $uploadError.show();
                    } else {
                        // Upload succeed, so we update the images availability
                        // according to what have been uploaded
                        if (isSubmittingLogo) {
                            $('#currentLogo').data("srcExists", true)
                        }
                        if (isSubmittingFavicon) {
                            $('#currentFavicon').data("srcExists", true)
                        }
                        refreshCustomLogo();
                    }

                    if ('1' === frameContent || '0' === frameContent) {
                        uploadFrame.remove();
                    }
                }, 1000);
            });
            $("body:first").append(uploadFrame);
            var submittingForm = $('#logoUploadForm');
            submittingForm.attr("target", frameName);
            submittingForm.submit();

            this.customLogo = '';
            this.customFavicon = '';
        };

        refreshCustomLogo();

        this.toggleCustomLogo = function () {
            refreshCustomLogo();
        };

        this.save = function () {

            this.isLoading = true;

            piwikApi.post({module: 'API', method: 'CoreAdminHome.setBrandingSettings'}, {
                useCustomLogo: this.enabled ? '1' : '0'
            }).then(function (success) {
                self.isLoading = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('CoreAdminHome_SettingsSaveSuccess'), {
                    id: 'generalSettings', context: 'success'
                });
                notification.scrollToNotification();
            }, function () {
                self.isLoading = false;
            });
        };
    }
})();