describe('ngDialog inside a directive', function () {
  var any = jasmine.any,
  spy = jasmine.createSpy;

  beforeEach(module('ngDialog'));
  beforeEach(module('inside-directive'));

  var $compile;
  var $rootScope;
  var $timeout;
  var ngDialog;
  beforeEach(inject(function(_$compile_, _$rootScope_, _$timeout_, _ngDialog_) {
    $compile = _$compile_;
    $rootScope = _$rootScope_;
    $timeout = _$timeout_;
    ngDialog = _ngDialog_;
  }));

  describe('with plain template', function() {

    it('should function as normal', function() {

      $rootScope.$on('ngDialog.opened', function($event, $dialog) {
        console.log($dialog);
        var ExampleController = $dialog.dialog.data('$ngDialogControllerController');
        expect(ExampleController.$scope.closeThisDialog).toEqual(jasmine.any(Function));
        $dialog.dialog.find('button').triggerHandler('click');
      });

      $rootScope.$on('ngDialog.closed', function($event, $dialog) {
        expect($dialog.attr('class').indexOf('inside-directive'));
      });

      var $scope = $rootScope.$new();
      var element = angular.element('<div inside-directive-plain>Testing example directive</a>')
      $compile(element)($scope);
      $rootScope.$digest();

      element.triggerHandler('click');
      $rootScope.$digest();

      $timeout(angular.noop, 100);
      $timeout.flush();
    });
  });
});
