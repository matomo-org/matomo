/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('MultiPairFieldController', MultiPairFieldController);

    MultiPairFieldController.$inject = ['$scope'];

    function MultiPairFieldController($scope){

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

        if ($scope.field1 && !$scope.field1.templateFile) {
            $scope.field1.templateFile = getTemplate($scope.field1);
        }

        if ($scope.field2 && !$scope.field2.templateFile) {
            $scope.field2.templateFile = getTemplate($scope.field2);
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
            angular.forEach($scope.formValue, function (table) {
                if (!table) {
                    hasAny = false;
                    return;
                }
                if ($scope.field1 && $scope.field2) {
                    if (!table[$scope.field1.key] && !table[$scope.field2.key]) {
                        hasAny = false;
                    }
                } else if ($scope.field1) {
                    if (!table[$scope.field1.key]) {
                        hasAny = false;
                    }
                } else if ($scope.field2) {
                    if (!table[$scope.field2.key]) {
                        hasAny = false;
                    }
                }
            });
            if (hasAny) {
                this.addEntry();
            }
        };

        this.addEntry = function () {
            if (angular.isArray($scope.formValue)) {
                var obj = {};
                if ($scope.field1 && $scope.field1.key) {
                    obj[$scope.field1.key] = '';
                }
                if ($scope.field2 && $scope.field2.key) {
                    obj[$scope.field2.key] = '';
                }
                $scope.formValue.push(obj);
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
