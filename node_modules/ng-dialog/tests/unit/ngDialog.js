describe('ngDialog', function () {
  'use strict';

  var any = jasmine.any,
      spy = jasmine.createSpy;

  beforeEach(module('ngDialog'));

  afterEach(inject(function (ngDialog, $document) {
    ngDialog.closeAll();
    [].slice.call(
      $document.find('body').children()
    )
    .map(angular.element)
    .forEach(function (elm) {
      if (elm.hasClass('ngdialog')) {
        // yuck
        elm.triggerHandler('animationend');
      }
    });
  }));

  it('should inject the ngDialog service', inject(function(ngDialog) {
    expect(ngDialog).toBeDefined();
  }));

  describe('no options', function () {
    var inst, elm;
    beforeEach(inject(function (ngDialog, $document, $timeout) {
      inst = ngDialog.open();
      $timeout.flush();
      elm = $document[0].getElementById(inst.id);
    }));

    it('should have returned a dialog instance object', function() {
      expect(inst).toBeDefined();
    });

    it('should include a document id', function() {
      expect(inst.id).toEqual('ngdialog1');
    });

    it('should have created an element on the DOM', function() {
      expect(elm).toBeDefined();
    });

    it('should have an empty template', function() {
      expect(elm.textContent).toEqual('Empty template');
    });
  });

  describe('with a plain template', function () {
    var elm;
    beforeEach(inject(function (ngDialog, $timeout, $document) {
      var id = ngDialog.open({
        template: '<div><p>some text {{1 + 1}}</p></div>',
        plain: true
      }).id;
      $timeout.flush();
      elm = $document[0].getElementById(id);
    }));

    it('should have compiled the html', inject(function () {
      expect(elm.textContent).toEqual('some text 2');
    }));
  });

  describe('with a plain template URL', function () {
    var elm;
    beforeEach(inject(function (ngDialog, $timeout, $document, $httpBackend) {
      $httpBackend.whenGET('test.html').respond('<div><p>some text {{1 + 1}}</p></div>');
      var id = ngDialog.open({
        templateUrl: 'test.html'
      }).id;
      $httpBackend.flush();
      $timeout.flush();
      elm = $document[0].getElementById(id);
    }));

    it('should have compiled the html', inject(function () {
      expect(elm.textContent).toEqual('some text 2');
    }));
  });

  describe('with already cached template URL', function () {
    var elm;
    beforeEach(inject(function (ngDialog, $timeout, $document, $httpBackend, $compile, $rootScope) {
      $httpBackend.whenGET('cached.html').respond('<div><p>some text {{1 + 1}}</p></div>');
      $compile('<div><div ng-include src="\'cached.html\'"></div></div>')($rootScope);

      $rootScope.$digest();
      $httpBackend.flush();

      var id = ngDialog.open({
        templateUrl: 'cached.html'
      }).id;

      $timeout.flush();

      elm = $document[0].getElementById(id);
    }));

    it('should have compiled the html', inject(function () {
      expect(elm.textContent).toEqual('some text 2');
    }));
  });

  describe('with an appended class', function () {
    var elm;
    beforeEach(inject(function (ngDialog, $timeout, $document) {
      var id = ngDialog.open({
        appendClassName: 'ngdialog-custom'
      }).id;
      $timeout.flush();
      elm = $document[0].getElementById(id);
    }));

    it('should have the additional class', inject(function () {
      expect(elm.className.split(' ')).toContain('ngdialog-custom');
    }));
  });

  describe('controller instantiation', function () {
    var Ctrl;
    beforeEach(inject(function (ngDialog, $timeout, $q) {
      Ctrl = spy('DialogCtrl');
      Ctrl.$inject = ['$scope', '$element', '$log', 'myLocal', 'localPromise'];
      ngDialog.open({
        controller: Ctrl,
        resolve: {
          myLocal: function () {
            return 'local';
          },
          localPromise: function () {
            return $q.when('async local!');
          }
        }
      });
      $timeout.flush();
    }));

    it('should have instantiated the controller', function() {
      expect(Ctrl).toHaveBeenCalled();
    });

    describe('dependencies', function () {
      var injected;
      beforeEach(function () {
        injected = Ctrl.calls.mostRecent().args;
      });

      it('should inject a scope', function() {
        expect(injected[0].$watch).toEqual(any(Function));
      });

      it('should inject the root dialog html element', function() {
        expect(injected[1].prop('id')).toEqual('ngdialog1');
      });

      it('should inject another angular service', inject(function($log) {
        expect(injected[2]).toBe($log);
      }));

      it('should inject a local value', function() {
        expect(injected[3]).toEqual('local');
      });

      it('should inject an asynchronous local value', function() {
        expect(injected[4]).toEqual('async local!');
      });
    });
  });

  describe('public functions checking', function () {
    var inst;
    var elm;

    beforeEach(inject(function (ngDialog, $document, $timeout) {
      inst = ngDialog.open();
      $timeout.flush();
      elm = $document[0].getElementById(inst.id);
    }));

    it('should be able to check if a dialog is open', inject(function(ngDialog) {
        expect(ngDialog.isOpen(inst.id)).toBe(true);
    }));

  });

  describe('bindToController data checking', function () {
    var Ctrl;
    beforeEach(inject(function (ngDialog, $timeout) {
      Ctrl = spy('DialogCtrl');
      ngDialog.open({
        controller: Ctrl,
        controllerAs: 'CtrlVM',
        bindToController: true,
        data: {
          testData: 'testData'
        }
      });
      $timeout.flush();
    }));

    it('should have placed ngDialogId on the controller', function() {
      expect(Ctrl.calls.first().object.ngDialogId).toEqual('ngdialog1');
    });

    it('should have placed ngDialogData on the controller', function() {
      expect(Ctrl.calls.first().object.ngDialogData.testData).toEqual('testData');
    });

    it('should have placed closeThisDialog function on the controller', function() {
      expect(Ctrl.calls.first().object.closeThisDialog).toEqual(jasmine.any(Function));
    });

    it('should not have placed confirm function on the controller', function() {
      expect(Ctrl.calls.first().object.confirm).toBeUndefined();
    });
  });

  describe('bindToController data checking on openConfirm', function () {
  	var Ctrl;
  	beforeEach(inject(function (ngDialog, $timeout) {
  		Ctrl = spy('DialogCtrl');
  		ngDialog.openConfirm({
  			controller: Ctrl,
  			controllerAs: 'CtrlVM',
  			bindToController: true,
  			data: {
  				testData: 'testData'
  			}
  		});
  		$timeout.flush();
  	}));

  	it('should have placed confirm function on the controller', function () {
  		expect(Ctrl.calls.first().object.confirm).toEqual(jasmine.any(Function));
  	});
  });

  describe('openOnePerName', function () {
      var dialogOptions = {
          name: 'Do Something'
      };

      describe('when feature is off - default', function () {
          var ngDialog;
          var $timeout;

          beforeEach(inject(function (_ngDialog_, _$timeout_) {
              ngDialog = _ngDialog_;
              $timeout = _$timeout_;
          }));

          it('should allow opening 2 dialogs with the same name', function () {
              var firstDialog = ngDialog.open(dialogOptions);
              expect(firstDialog).toBeDefined();
              expect(firstDialog.id).toBe('ngdialog1');

              var secondDialog = ngDialog.open(dialogOptions);
              expect(secondDialog).toBeDefined();
              expect(secondDialog.id).toBe('ngdialog2');
              $timeout.flush();
          });
      });

      describe('when feature is on (openOnePerName = true)', function () {
          var ngDialog;
          var $timeout;

          beforeEach(module(function (ngDialogProvider) {
              ngDialogProvider.setOpenOnePerName(true);
          }));

          beforeEach(inject(function (_ngDialog_, _$timeout_) {
              ngDialog = _ngDialog_;
              $timeout = _$timeout_;
          }));

          it('should not allow opening 2 dialogs with the same name', function () {
              var firstDialog = ngDialog.open(dialogOptions);
              expect(firstDialog).toBeDefined();
              expect(firstDialog.id).toBe('do-something-dialog');
              $timeout.flush();

              var secondDialog = ngDialog.open(dialogOptions);
              expect(secondDialog).toBeUndefined();
          });
      });
  });

  describe('with an width', function () {
    var elm, open;
    beforeEach(inject(function (ngDialog, $timeout, $document) {
      open = function(width) {
        var id = ngDialog.open({
          width: width
        }).id;
        $timeout.flush();
        elm = $document[0].getElementById(id).querySelector('.ngdialog-content');
      }
    }));

    it('should transform number to px', function () {
      open(400);
      expect(elm.style.cssText.trim()).toBe('width: 400px;');
    });

    it('should set other width metrics', function () {
      open('40%');
      expect(elm.style.cssText.trim()).toBe('width: 40%;');
    });
  });

  describe('with onOpenCallback', function () {
    var elm, open;
    beforeEach(inject(function (ngDialog, $timeout, $document) {
      open = function (onOpenCallback) {
        var id = ngDialog.open({
          onOpenCallback: onOpenCallback
        }).id;
        $timeout.flush();
      }
    }));

    it('onOpenCallback method should be called on opening of the dialog', function () {
      var callback = spy();
      open(callback);
      expect(callback).toHaveBeenCalled();
    })
  });

  describe('with custom height', function () {
    var elm, open;
    beforeEach(inject(function (ngDialog, $timeout, $document) {
      open = function(height) {
        var id = ngDialog.open({
          height: height
        }).id;
        $timeout.flush();
        elm = $document[0].getElementById(id).querySelector('.ngdialog-content');
      }
    }));

    it('should set modal height from number to px', function () {
      open(400);
      expect(elm.style.cssText.trim()).toBe('height: 400px;');
    });

    it('should set modal height using other units', function () {
      open('40%');
      expect(elm.style.cssText.trim()).toBe('height: 40%;');
    });
  });

  describe('without custom height', function () {
    var elm, id;
    beforeEach(inject(function (ngDialog, $timeout, $document) {
        id = ngDialog.open().id;
        $timeout.flush();
        elm = $document[0].getElementById(id).querySelector('.ngdialog-content');
    }));

    it('should have id', function() {
      expect(id).toBeDefined();
      expect(id).toEqual('ngdialog1');
    });

    it('should have created an element on the DOM', function() {
      expect(elm).toBeDefined();
    });

    it('should have content', function() {
      expect(elm.clientHeight).toBeGreaterThan(0);
    });
  });

  describe('body classes should be applied / removed correctly', function () {
    var elm, first, second, ngDialog, flush;

    beforeEach(inject(function (_ngDialog_, $timeout, $document) {
      ngDialog = _ngDialog_

      first = ngDialog.open({
        bodyClassName: 'ngdialog-first'
      });

      $timeout.flush();

      second = ngDialog.open({
        bodyClassName: 'ngdialog-second'
      });

      $timeout.flush();

      elm = angular.element($document.find('body'));

      flush = function () {
        [].slice.call(
          $document.find('body').children()
        )
        .map(angular.element)
        .forEach(function (elm) {
          if (elm.hasClass('ngdialog')) {
            // yuck
            elm.triggerHandler('animationend');
          }
        });
      };
    }));

    it('should have two body classes applied', function () {
      expect(elm.hasClass('ngdialog-first')).toEqual(true);
      expect(elm.hasClass('ngdialog-second')).toEqual(true);
    });

    it('should properly remove one body class', function () {
      first.close();
      flush();

      expect(elm.hasClass('ngdialog-second')).toEqual(true);
      expect(elm.hasClass('ngdialog-first')).toEqual(false);
    });

    it('should properly remove all classes on closeAll', function () {
      ngDialog.closeAll();
      flush();

      expect(elm.hasClass('ngdialog-second')).toEqual(false);
      expect(elm.hasClass('ngdialog-first')).toEqual(false);
    });
  });

});
