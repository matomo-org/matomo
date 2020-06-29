describe('ngDialog', function () {

  beforeEach(module('ngDialog'));

  var ngDialog;
  var $timeout;
  var $document;
  var $rootScope;
  beforeEach(inject(function (_ngDialog_, _$timeout_, _$document_, _$rootScope_) {
    ngDialog = _ngDialog_;
    $timeout = _$timeout_;
    $document = _$document_;
    $rootScope = _$rootScope_;
  }));

  describe('closeThisDialog on $dialog scope', function() {
    it('should expose closeThisDialog on the dialog scope', function() {
      var instance = ngDialog.open();

      $timeout(angular.noop, 100);
      $timeout.flush();

      var element = angular.element($document[0].getElementById(instance.id));
      expect(element.scope().closeThisDialog).toEqual(jasmine.any(Function));

      $rootScope.$on('ngDialog.closed', function($event, $dialog) {
        expect($dialog.attr('class').indexOf('inside-directive'));
      });

      expect(ngDialog.getOpenDialogs().length).toEqual(1);
      element.scope().closeThisDialog();
      expect(ngDialog.getOpenDialogs().length).toEqual(0);
    });
  });
});
