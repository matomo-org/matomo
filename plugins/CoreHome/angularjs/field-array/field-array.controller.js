/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('FieldArrayController', FieldArrayController);

    FieldArrayController.$inject = ['$scope'];

    function FieldArrayController($scope){

        function getTemplate(field) {
            var control = field.uiControl;
            if (control === 'password' || control === 'url' || control === 'search' || control === 'email') {
                control = 'text'; // we use same template for text and password both
            }

            var file = 'field-' + control;
            var fieldsSupportingArrays = ['textarea', 'checkbox', 'text'];
            if (field.type === 'array' && fieldsSupportingArrays.indexOf(control) !== -1) {
                file += '-array';
            }

            return 'plugins/CorePluginsAdmin/angularjs/form-field/' + file + '.html?cb=' + piwik.cacheBuster;
        }

        if ($scope.field && !$scope.field.templateFile) {
            $scope.field.templateFile = getTemplate($scope.field);
        }

        var self = this;
        $scope.$watch('formValue', function () {
            if (!$scope.formValue || !$scope.formValue.length) {
                self.addEntry();
            } else {
                self.onEntryChange();
            }
        }, true);

        this.onEntryChange = function () {
            var hasAny = true;
            angular.forEach($scope.formValue, function (entry) {
                if (!entry) {
                    hasAny = false;
                }
            });
            if (hasAny) {
                this.addEntry();
            }
        };

        this.addEntry = function () {
            if (angular.isArray($scope.formValue)) {
                $scope.formValue.push('');
            }
        };

        this.removeEntry = function (index) {
            if (index > -1) {
                $scope.formValue.splice(index, 1);
            }
        };

        if (!$scope.formValue || !$scope.formValue.length) {
            this.addEntry();
        }
    }

})();
