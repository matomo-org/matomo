(function() {
  angular.module('inside-directive', [])
    .controller('InsideDirective', InsideDirective)

    InsideDirective.$inject = ['$scope'];

  function InsideDirective($scope) {
    this.$scope = $scope;
  }

  angular.module('inside-directive').directive('inside-directive-plain', exampleDirective);

  exampleDirective.$inject = ['$rootScope', 'ngDialog'];

  function exampleDirective($rootScope, ngDialog) {
    return {
      restrict: 'A',
      link: function(scope, element, attrs) {
        element.on('click', function() {
          scope.$dialog = ngDialog.open({
            template: '<button class="icon icon--close" data-ng-click="closeThisDialog()">close dialog</button> test',
            plain: true,
            controller: 'InsideDirective',
            className: 'inside-directive-plain',
            name: 'inside-directive-plain',
            showclose: false
          });
        });

      }
    };
  };

})();
