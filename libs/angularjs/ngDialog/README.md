# ngDialog

Modal dialogs and popups provider for [Angular.js](http://angularjs.org/) applications.

ngDialog is small (~2Kb), has minimalistic API, highly customizable through themes and has only Angular.js as dependency.

### [Demo](http://likeastore.github.io/ngDialog)

## Install

You can download all necessary ngDialog files manually or install it with bower:

```bash
bower install ngDialog
```

## Usage

You need only to include ``ngDialog.js`` and  ``ngDialog.css`` (as minimal setup) to your project and then you can start using ``ngDialog`` provider in your directives, controllers and services. For example in controllers:

```javascript
var app = angular.module('exampleApp', ['ngDialog']);

app.controller('MainCtrl', function ($scope, ngDialog) {
	$scope.clickToOpen = function () {
		ngDialog.open({ template: 'popupTmpl.html' });
	};
});
```

## API

ngDialog service provides easy to use and minimalistic API, but in the same time it's powerful enough. Here is the list of accessible methods that you can use:

===

### ``.open(options)``

Method allows to open dialog window, creates new dialog instance on each call. It accepts ``options`` object as the only argument.

### Options:

##### ``template {String}``

Dialog template can be loaded through ``path`` to external html template or ``<script>`` tag with ``text/ng-template``:

```html
<script type="text/ng-template" id="templateId">
	<h1>Template heading</h1>
	<p>Content goes here</p>
</script>
```

```javascript
ngDialog.open({ template: 'templateId' });
```

Also it is possible to use simple string as template together with ``plain`` option.

##### ``plain {Boolean}``

If ``true`` allows to use plain string as template, default ``false``:

```javascript
ngDialog.open({
	template: '<p>my template</p>',
	plain: true
});
```

##### ``controller {String} | {Array} | {Object}``

Controller that will be used for dialog window if necessary. The controller can be specified either by referring it by name or directly inline.

```javascript
ngDialog.open({
	template: 'externalTemplate.html',
	controller: 'SomeController'
});
```

or

```javascript
ngDialog.open({
	template: 'externalTemplate.html',
	controller: ['$scope', 'otherService', function($scope, otherService) {
		// controller logic
	}]
});
```

##### ``scope {Object}``

Scope object that will be passed to dialog. If you use controller with separate ``$scope`` service this object will be passed to ``$scope.$parent`` param:

```javascript
$scope.value = true;

ngDialog.open({
	template: 'externalTemplate.html',
	className: 'ngdialog-theme-plain',
	scope: $scope
});
```

```html
<script type="text/ng-template" id="externalTemplate.html">
<p>External scope: <code>{{value}}</code></p>
</script>
```

##### ``scope.closeThisDialog(value)``

In addition ``.closeThisDialog(value)`` method gets injected to passed ``$scope``. This allows you to close dialog straight from handler in a popup element, for example:

```html
<div class="dialog-contents">
	<input type="text"/>
	<input type="button" value="OK" ng-click="checkInput() && closeThisDialog('Some value')"/>
</div>
```

Any value passed to this function will be attached to the object which resolves on the close promise for this dialog. For dialogs opened with the ``openConfirm()`` method the value is used as the reject reason.

##### ``data {String | Object | Array}``

Any data that you want to be stored in controller's ``$parent`` scope, it could be stringified JSON as well.

##### ``className {String}``

This option allows to controll dialog look, you can use built-in [themes](https://github.com/likeastore/ngDialog#themes) or create your own styled modals.

This example enables one of built-in ngDialog themes - ``ngdialog-theme-default`` (do not forget to include necessary css files):

```javascript
ngDialog.open({
	template: 'templateId',
	className: 'ngdialog-theme-default'
});
```

Check [themes](https://github.com/likeastore/ngDialog#themes) block to learn more.

##### ``showClose {Boolean}``

If ``false`` it allows to hide close button on modals, default ``true``.

##### ``closeByEscape {Boolean}``

It allows to close modals by clicking ``Esc`` button, default ``true``.

This will close all open modals if there several of them open at the same time.

##### ``closeByDocument {Boolean}``

It allows to close modals by clicking on overlay background, default ``true``. If [Hammer.js](https://github.com/EightMedia/hammer.js) is loaded, it will listen for ``tap`` instead of ``click``.

##### ``appendTo {String}``

Specify your element where to append dialog instance, accepts selector string (e.g. ``#yourId``, ``.yourClass``). If not specified appends dialog to ``body`` as default behavior.

### Defaults

##### ``setDefaults(options)``

You're able to set default settings through ``ngDialogProvider``:

```javascript
var app = angular.module('myApp', ['ngDialog']);
app.config(['ngDialogProvider', function (ngDialogProvider) {
	ngDialogProvider.setDefaults({
		className: 'ngdialog-theme-default',
		plain: true,
		showClose: true,
		closeByDocument: true,
		closeByEscape: true
	});
}]);
```

### Returns:

The ``open()`` method returns an object with some useful properties.

##### ``id {String}``

This is the ID of the dialog which was just created. It is the ID on the dialog's DOM element.

##### ``close(value) {Function}``

This is a function which will close the dialog which was opened by the current call to ``open()``. It takes an optional value to pass to the close promise.

##### ``closePromise {Promise}``

A promise which will resolve when the dialog is closed. It is resolved with an object containing: ``id`` - the ID of the closed dialog, ``value`` - the value the dialog was closed with, ``$dialog`` - the dialog element which at this point has been removed from the DOM and ``remainingDialogs`` - the number of dialogs still open.

The value property will be a special string if the dialog is dismissed by one of the built in mechanisms: `'$escape'`, `'$closeButton'` or `'$document'`.

This allows you do to something like this:

```javascript
var dialog = ngDialog.open({
	template: 'templateId'
});

dialog.closePromise.then(function (data) {
	console.log(data.id + ' has been dismissed.');
});
```

===

### ``.openConfirm(options)``

Opens a dialog that by default does not close when hitting escape or clicking outside the dialog window. The function returns a promise that is either resolved or rejected depending on the way the dialog was closed.

### Options:

The options are the same as the regular [``.open()``](https://github.com/likeastore/ngDialog#options) method with an extra function added to the scope:

##### ``scope.confirm()``

In addition to the ``.closeThisDialog()`` method. The method ``.confirm()`` is also injected to passed ``$scope``. Use this method to close the dialog and ``resolve`` the promise that was returned when opening the modal.

The function accepts a single optional parameter which is used as the value of the resolved promise.

```html
<div class="dialog-contents">
	Some message
	<button ng-click="closeThisDialog()">Cancel</button>
	<button ng-click="confirm()">Confirm</button>
</div>
```

### Returns:

An Angular promise object that is resolved if the ``.confirm()`` function is used to close the dialog, otherwise the promise is rejected. The resolve value and the reject reason is defined by the value passed to the ``confirm()`` or ``closeThisDialog()`` call respectively.

===

### ``.close(id, value)``

Method accepts dialog's ``id`` as string argument to close specific dialog window, if ``id`` is not specified it will close all currently active modals (same behavior as ``.closeAll()``). Takes an optional value to resolve the dialog promise with (or all dialog promises).

===

### ``.closeAll(value)``

Method manages closing all active modals on the page. Takes an optional value to resolve all of the dialog promises with.

===

### ``.setForceBodyReload({Boolean})``

Adds additional listener on every ``$locationChangeSuccess`` event and gets update version of ``body`` into dialog. Maybe useful in some rare cases when you're dependant on DOM changes, defaults to ``false``. Use it in module's config as provider instance:

```javascript
var app = angular.module('exampleApp', ['ngDialog']);

app.config(function (ngDialogProvider) {
	ngDialogProvider.setForceBodyReload(true);
});
```

## Directive

By default ngDialog module is served with ``ngDialog`` directive which can be used as attribute for buttons, links, etc. Almost all ``.open()`` options are available through tag attributes as well, the only difference is that ``ng-template`` id or path of template file is required.

Some imaginary button, for example, will look like:

```html
<button type="button"
	ng-dialog="templateId.html"
	ng-dialog-class="ngdialog-theme-flat"
	ng-dialog-controller="ModalCtrl"
	ng-dialog-close-previous>
	Open modal text
</button>
```

Directive contains one more additional but very useful option, it's an attribute named ``ng-dialog-close-previous``. It allows you to close previously opened dialogs automaticly.

## Events

Everytime when ngDialog is opened or closed we're broadcasting two events (dispatching events downwards to all child scopes):

- ``ngDialog.opened``

- ``ngDialog.closed``

This allows you to register your own listeners, example:

```javascript
$rootScope.$on('ngDialog.opened', function (e, $dialog) {
	console.log('ngDialog opened: ' + $dialog.attr('id'));
});
```

## Themes

Currently ngDialog contains two default themes that show how easily you can create your own. Check ``example`` folder for demonstration purposes.

## CDN

ngDialog is available for public on [cdnjs](http://cdnjs.com/libraries/ng-dialog). Please use following urls for version 0.1.6.

```html
//cdnjs.cloudflare.com/ajax/libs/ng-dialog/0.1.6/ng-dialog.min.css
//cdnjs.cloudflare.com/ajax/libs/ng-dialog/0.1.6/ng-dialog-theme-plain.min.css
//cdnjs.cloudflare.com/ajax/libs/ng-dialog/0.1.6/ng-dialog.min.js
```

## License

MIT Licensed

Copyright (c) 2013-2014, Likeastore.com <info@likeastore.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/likeastore/ngdialog/trend.png)](https://bitdeli.com/free "Bitdeli Badge")
