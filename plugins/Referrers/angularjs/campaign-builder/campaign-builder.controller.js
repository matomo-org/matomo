/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('CampaignBuilderController', CampaignBuilderController);

    CampaignBuilderController.$inject = ['$scope'];

    function CampaignBuilderController($scope) {
        this.hasExtraPlugin = $scope.hasExtraPlugin;

        this.reset = function () {
            this.websiteUrl = '';
            this.campaignName = '';
            this.campaignKeyword = '';
            this.campaignSource = '';
            this.campaignMedium = '';
            this.campaignContent = '';
            this.campaignGroup = '';
            this.campaignPlacement = '';
            this.generatedUrl = '';
        };

        this.generateUrl = function () {
            this.generatedUrl = String(this.websiteUrl);

            if (this.generatedUrl.indexOf('http') !== 0) {
                this.generatedUrl = 'https://' + this.generatedUrl.trim();
            }

            var urlHashPos = this.generatedUrl.indexOf('#');
            var urlHash = '';
            if (urlHashPos >= 0) {
                urlHash = this.generatedUrl.substr(urlHashPos);
                this.generatedUrl = this.generatedUrl.substr(0, urlHashPos);
            }

            if (this.generatedUrl.indexOf('/', 10) < 0 && this.generatedUrl.indexOf("?") < 0) {
                this.generatedUrl += '/';
            }

            var campaignName = encodeURIComponent(this.campaignName.trim());

            if (this.generatedUrl.indexOf('?') > 0 || this.generatedUrl.indexOf('#') > 0) {
                this.generatedUrl += '&';
            } else {
                this.generatedUrl += '?';
            }

            this.generatedUrl += 'mtm_campaign='+campaignName;

            if (this.campaignKeyword) {
                this.generatedUrl += '&mtm_kwd='+encodeURIComponent(this.campaignKeyword.trim());
            }

            if (this.campaignSource) {
                this.generatedUrl += '&mtm_source='+encodeURIComponent(this.campaignSource.trim());
            }

            if (this.campaignMedium) {
                this.generatedUrl += '&mtm_medium='+encodeURIComponent(this.campaignMedium.trim());
            }

            if (this.campaignContent) {
                this.generatedUrl += '&mtm_content='+encodeURIComponent(this.campaignContent.trim());
            }

            if (this.campaignGroup) {
                this.generatedUrl += '&mtm_group='+encodeURIComponent(this.campaignGroup.trim());
            }

            if (this.campaignPlacement) {
                this.generatedUrl += '&mtm_placement='+encodeURIComponent(this.campaignPlacement.trim());
            }

            this.generatedUrl += urlHash;

            $('#urlCampaignBuilderResult').effect("highlight", {}, 1500);
        };

        this.reset();
    }
})();