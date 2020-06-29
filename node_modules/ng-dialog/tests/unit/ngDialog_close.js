describe('ngDialog', function () {
  'use strict';

  beforeEach(module('ngDialog'));

  var animationEndEvent = 'animationend webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend';
  var $timeout;
  var $document;
  var ngDialog;
  beforeEach(inject(function(_$timeout_, _$document_, _ngDialog_) {
    $timeout = _$timeout_;
    $document = _$document_;
    ngDialog = _ngDialog_;
  }));

  afterEach(inject(function (ngDialog, $document) {
    //ngDialog.closeAll();
  }));

  it('should allow one to close a dialog using just the id, without animation', function() {
    spyOn(ngDialog, 'close').and.callThrough();
    spyOn(ngDialog.__PRIVATE__, 'performCloseDialog').and.callThrough();
    spyOn(ngDialog.__PRIVATE__, 'closeDialogElement').and.callThrough();
    var dialog = ngDialog.open({
      disableAnimation: true
    });
    $timeout.flush();
    var element = angular.element($document[0].getElementById(dialog.id));
    var id = element.attr('id');
    ngDialog.close(id);
    expect(ngDialog.close.calls.count()).toEqual(1);
    expect(ngDialog.__PRIVATE__.performCloseDialog.calls.count()).toEqual(1);
    expect(ngDialog.__PRIVATE__.closeDialogElement.calls.count()).toEqual(1);
    expect(ngDialog.isOpen(id)).toBe(false);
  });

  it('should allow one to close a dialog using just the id, without animation', function() {
    spyOn(ngDialog, 'close').and.callThrough();
    spyOn(ngDialog.__PRIVATE__, 'performCloseDialog').and.callThrough();
    spyOn(ngDialog.__PRIVATE__, 'closeDialogElement').and.callThrough();
    var dialog = ngDialog.open({
      disableAnimation: false
    });
    $timeout.flush();
    var element = angular.element($document[0].getElementById(dialog.id));
    var id = element.attr('id');
    angular.element(element);
    ngDialog.close(id);

    element.triggerHandler('animationend');

    expect(ngDialog.close.calls.count()).toEqual(1);
    expect(ngDialog.__PRIVATE__.performCloseDialog.calls.count()).toEqual(1);
    expect(ngDialog.__PRIVATE__.closeDialogElement.calls.count()).toEqual(1);
    expect(ngDialog.isOpen(id)).toBe(false);
  });
});
