/*
 * ngDialog - easy modals and popup windows
 * http://github.com/likeastore/ngDialog
 * (c) 2013 MIT License, https://likeastore.com
 */

(function (window, angular, undefined) {
	'use strict';

	var module = angular.module('ngDialog', []);

	var $el = angular.element;
	var isDef = angular.isDefined;
	var style = (document.body || document.documentElement).style;
	var animationEndSupport = isDef(style.animation) || isDef(style.WebkitAnimation) || isDef(style.MozAnimation) || isDef(style.MsAnimation) || isDef(style.OAnimation);
	var animationEndEvent = 'animationend webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend';
	var forceBodyReload = false;

	module.provider('ngDialog', function () {
		var defaults = this.defaults = {
			className: 'ngdialog-theme-default',
			plain: false,
			showClose: true,
			closeByDocument: true,
			closeByEscape: true,
			appendTo: false
		};

		this.setForceBodyReload = function (_useIt) {
			forceBodyReload = _useIt || false;
		};

		this.setDefaults = function (newDefaults) {
			angular.extend(defaults, newDefaults);
		};

		var globalID = 0, dialogsCount = 0, closeByDocumentHandler, defers = {};

		this.$get = ['$document', '$templateCache', '$compile', '$q', '$http', '$rootScope', '$timeout', '$window', '$controller',
			function ($document, $templateCache, $compile, $q, $http, $rootScope, $timeout, $window, $controller) {
				var $body = $document.find('body');
				if (forceBodyReload) {
					$rootScope.$on('$locationChangeSuccess', function () {
						$body = $document.find('body');
					});
				}

				var privateMethods = {
					onDocumentKeydown: function (event) {
						if (event.keyCode === 27) {
							publicMethods.close('$escape');
						}
					},

					setBodyPadding: function (width) {
						var originalBodyPadding = parseInt(($body.css('padding-right') || 0), 10);
						$body.css('padding-right', (originalBodyPadding + width) + 'px');
						$body.data('ng-dialog-original-padding', originalBodyPadding);
					},

					resetBodyPadding: function () {
						var originalBodyPadding = $body.data('ng-dialog-original-padding');
						if (originalBodyPadding) {
							$body.css('padding-right', originalBodyPadding + 'px');
						} else {
							$body.css('padding-right', '');
						}
					},

					closeDialog: function ($dialog, value) {
						var id = $dialog.attr('id');
						if (typeof window.Hammer !== 'undefined') {
							window.Hammer($dialog[0]).off('tap', closeByDocumentHandler);
						} else {
							$dialog.unbind('click');
						}

						if (dialogsCount === 1) {
							$body.unbind('keydown');
						}

						if (!$dialog.hasClass("ngdialog-closing")){
							dialogsCount -= 1;
						}

						if (animationEndSupport) {
							$dialog.unbind(animationEndEvent).bind(animationEndEvent, function () {
								$dialog.scope().$destroy();
								$dialog.remove();
								if (dialogsCount === 0) {
									$body.removeClass('ngdialog-open');
									privateMethods.resetBodyPadding();
								}
								$rootScope.$broadcast('ngDialog.closed', $dialog);
							}).addClass('ngdialog-closing');
						} else {
							$dialog.scope().$destroy();
							$dialog.remove();
							if (dialogsCount === 0) {
								$body.removeClass('ngdialog-open');
								privateMethods.resetBodyPadding();
							}
							$rootScope.$broadcast('ngDialog.closed', $dialog);
						}
						if (defers[id]) {
							defers[id].resolve({
								id: id,
								value: value,
								$dialog: $dialog,
								remainingDialogs: dialogsCount
							});
							delete defers[id];
						}
					}
				};

				var publicMethods = {

					/*
					 * @param {Object} options:
					 * - template {String} - id of ng-template, url for partial, plain string (if enabled)
					 * - plain {Boolean} - enable plain string templates, default false
					 * - scope {Object}
					 * - controller {String}
					 * - className {String} - dialog theme class
					 * - showClose {Boolean} - show close button, default true
					 * - closeByEscape {Boolean} - default true
					 * - closeByDocument {Boolean} - default true
					 *
					 * @return {Object} dialog
					 */
					open: function (opts) {
						var self = this;
						var options = angular.copy(defaults);

						opts = opts || {};
						angular.extend(options, opts);

						globalID += 1;

						self.latestID = 'ngdialog' + globalID;

						var defer;
						defers[self.latestID] = defer = $q.defer();

						var scope = angular.isObject(options.scope) ? options.scope.$new() : $rootScope.$new();
						var $dialog, $dialogParent;

						$q.when(loadTemplate(options.template)).then(function (template) {
							template = angular.isString(template) ?
								template :
								template.data && angular.isString(template.data) ?
									template.data :
									'';

							$templateCache.put(options.template, template);

							if (options.showClose) {
								template += '<div class="ngdialog-close"></div>';
							}

							self.$result = $dialog = $el('<div id="ngdialog' + globalID + '" class="ngdialog"></div>');
							$dialog.html('<div class="ngdialog-overlay"></div><div class="ngdialog-content">' + template + '</div>');
							
							if (options.data && angular.isString(options.data)) {
								var firstLetter = options.data.replace(/^\s*/, '')[0];
								scope.ngDialogData = (firstLetter === '{' || firstLetter === '[') ? angular.fromJson(options.data) : options.data;
							} else if (options.data && angular.isObject(options.data)) {
								scope.ngDialogData = angular.fromJson(angular.toJson(options.data));
							}

							if (options.controller && (angular.isString(options.controller) || angular.isArray(options.controller) || angular.isFunction(options.controller))) {
								var controllerInstance = $controller(options.controller, {
									$scope: scope,
									$element: $dialog
								});
								$dialog.data('$ngDialogControllerController', controllerInstance);
							}

							if (options.className) {
								$dialog.addClass(options.className);
							}

							if (options.appendTo && angular.isString(options.appendTo)) {
								$dialogParent = angular.element(document.querySelector(options.appendTo));
							} else {
								$dialogParent = $body;
							}

							scope.closeThisDialog = function (value) {
								privateMethods.closeDialog($dialog, value);
							};

							$timeout(function () {
								$compile($dialog)(scope);

								var widthDiffs = $window.innerWidth - $body.prop('clientWidth');
								$body.addClass('ngdialog-open');
								var scrollBarWidth = widthDiffs - ($window.innerWidth - $body.prop('clientWidth'));
								if (scrollBarWidth > 0) {
									privateMethods.setBodyPadding(scrollBarWidth);
								}
								$dialogParent.append($dialog);
								$rootScope.$broadcast('ngDialog.opened', $dialog);
							});

							if (options.closeByEscape) {
								$body.bind('keydown', privateMethods.onDocumentKeydown);
							}

							closeByDocumentHandler = function (event) {
								var isOverlay = options.closeByDocument ? $el(event.target).hasClass('ngdialog-overlay') : false;
								var isCloseBtn = $el(event.target).hasClass('ngdialog-close');

								if (isOverlay || isCloseBtn) {
									publicMethods.close($dialog.attr('id'), isCloseBtn ? '$closeButton' : '$document');
								}
							};

							if (typeof window.Hammer !== 'undefined') {
								window.Hammer($dialog[0]).on('tap', closeByDocumentHandler);
							} else {
								$dialog.bind('click', closeByDocumentHandler);
							}

							dialogsCount += 1;

							return publicMethods;
						});

						return {
							id: 'ngdialog' + globalID,
							closePromise: defer.promise,
							close: function(value) {
								privateMethods.closeDialog($dialog, value);
							}
						};

						function loadTemplate (tmpl) {
							if (!tmpl) {
								return 'Empty template';
							}

							if (angular.isString(tmpl) && options.plain) {
								return tmpl;
							}

							return $templateCache.get(tmpl) || $http.get(tmpl, { cache: true });
						}
					},

					/*
					 * @param {Object} options:
					 * - template {String} - id of ng-template, url for partial, plain string (if enabled)
					 * - plain {Boolean} - enable plain string templates, default false
					 * - scope {Object}
					 * - controller {String}
					 * - className {String} - dialog theme class
					 * - showClose {Boolean} - show close button, default true
					 * - closeByEscape {Boolean} - default false
					 * - closeByDocument {Boolean} - default false
					 *
					 * @return {Object} dialog
					 */
					openConfirm: function (opts) {
						var defer = $q.defer();

						var options = {
							closeByEscape: false,
							closeByDocument: false
						};
						angular.extend(options, opts);

						options.scope = angular.isObject(options.scope) ? options.scope.$new() : $rootScope.$new();
						options.scope.confirm = function (value) {
							defer.resolve(value);
							openResult.close(value);
						};

						var openResult = publicMethods.open(options);
						openResult.closePromise.then(function (data) {
							if (data) {
								return defer.reject(data.value);
							}
							return defer.reject();
						});

						return defer.promise;
					},

					/*
					 * @param {String} id
					 * @return {Object} dialog
					 */
					close: function (id, value) {
						var $dialog = $el(document.getElementById(id));

						if ($dialog.length) {
							privateMethods.closeDialog($dialog, value);
						} else {
							publicMethods.closeAll(value);
						}

						return publicMethods;
					},

					closeAll: function (value) {
						var $all = document.querySelectorAll('.ngdialog');

						angular.forEach($all, function (dialog) {
							privateMethods.closeDialog($el(dialog), value);
						});
					}
				};

				return publicMethods;
			}];
	});

	module.directive('ngDialog', ['ngDialog', function (ngDialog) {
		return {
			restrict: 'A',
			scope : {
				ngDialogScope : '='
			},
			link: function (scope, elem, attrs) {
				elem.on('click', function (e) {
					e.preventDefault();

					var ngDialogScope = angular.isDefined(scope.ngDialogScope) ? scope.ngDialogScope : 'noScope';
					angular.isDefined(attrs.ngDialogClosePrevious) && ngDialog.close(attrs.ngDialogClosePrevious);

					ngDialog.open({
						template: attrs.ngDialog,
						className: attrs.ngDialogClass,
						controller: attrs.ngDialogController,
						scope: ngDialogScope ,
						data: attrs.ngDialogData,
						showClose: attrs.ngDialogShowClose === 'false' ? false : true,
						closeByDocument: attrs.ngDialogCloseByDocument === 'false' ? false : true,
						closeByEscape: attrs.ngDialogCloseByEscape === 'false' ? false : true
					});
				});
			}
		};
	}]);

})(window, window.angular);
