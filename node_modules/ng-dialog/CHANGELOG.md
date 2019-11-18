# 1.0.0
- [x] Changes close element to button with proper accessibility rules ([PR-553](https://github.com/likeastore/ngDialog/pull/533)) 

# 0.5.0
- [x] Angular version in `package.json` is now `1.4.x` ([ISSUE-244](https://github.com/likeastore/ngDialog/issues/244))
- [x] fixed spelling of `ariaLabelledById` and `ariaLabelledBySelector` inside README file by [@rylan](https://github.com/rylan) ([PR-242](https://github.com/likeastore/ngDialog/pull/242))
- [x] fixed bug with $templateCache when template was already cached before calling ngDialog by [@mixer2](https://github.com/mixer2) ([PR-241](https://github.com/likeastore/ngDialog/pull/241))
- [x] add dialog close value to the $broadcast'ed events by [@Kidlike](https://github.com/Kidlike) ([PR-252](https://github.com/likeastore/ngDialog/pull/252))
- [x] fix for removing package version from bower.json as it's ignored by bower by [@kkirsche](https://github.com/kkirsche) ([PR-255](https://github.com/likeastore/ngDialog/pull/255))
- [x] fix for non-working `ngDialog.close()` when no arguments are provided by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-243](https://github.com/likeastore/ngDialog/issues/243)) 
- [x] added documentation for `controllerAs` option by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-248](https://github.com/likeastore/ngDialog/issues/248)) 
- [x] new option `disableAnimation` that could be used for disabling animation of dialog by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-159](https://github.com/likeastore/ngDialog/issues/159)) 
- [x] new attribute `ng-dialog-overlay` for ngDialog directive by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-198](https://github.com/likeastore/ngDialog/issues/198)) 
- [x] new attribute `ng-dialog-bind-to-controller` for ngDialog directive by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-236](https://github.com/likeastore/ngDialog/issues/236)) 
- [x] fix for error when `controllerAs` is used together with inline controller definition by   [@andrewogburn](https://github.com/andrewogburn) ([PR-260](https://github.com/likeastore/ngDialog/pull/260), [ISSUE-259](https://github.com/likeastore/ngDialog/issues/259)) 
- [x] added method `getOpenDialogs()` that returns array of all opened dialogs by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-240](https://github.com/likeastore/ngDialog/issues/240))
- [x] added two new events emmited by ngDialog - `ngDialog.templateLoading` and `ngDialog.templateLoaded` by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-231](https://github.com/likeastore/ngDialog/issues/231)) 
- [x] added `ngDialogId` field to scope of opened dialog (this field is equal to id of dialog) by [@egor-smirnov](https://github.com/egor-smirnov) ([ISSUE-274](https://github.com/likeastore/ngDialog/issues/274), [ISSUE-264](https://github.com/likeastore/ngDialog/issues/264)) 
- [x] fix for odd timing issue when using the `closeByNavigation` option alongside `angular-ui-router package` by   [@jdelibas](https://github.com/jdelibas) ([PR-277](https://github.com/likeastore/ngDialog/pull/277)) 
- [x] fix for disabling of body scroll when dialog is opened for some cases by [@marmotz](https://github.com/marmotz) ([PR-280](https://github.com/likeastore/ngDialog/pull/280), [ISSUE-206](https://github.com/likeastore/ngDialog/issues/206)) 
- [x] fix for avoiding require multiple angular when it's required already when using CommonJS by [@michaeleekk](https://github.com/michaeleekk) ([PR-284](https://github.com/likeastore/ngDialog/pull/284))
- [x] fix for `frunt build` that was resetting  html.ngdialog changes by [@davidvuong](https://github.com/davidvuong) ([PR-285](https://github.com/likeastore/ngDialog/pull/285))
- [x] moved Angular to dev.dependencies by [@platdesign](https://github.com/platdesign) ([PR-287](https://github.com/likeastore/ngDialog/pull/287))
- [x] fix for unboudning all keydown events when dialog is closed by [@daanoz](https://github.com/daanoz) ([PR-291](https://github.com/likeastore/ngDialog/pull/291))
- [x] ignore elements with tabindex=-1 when tabbing by [@roaks3](https://github.com/roaks3) ([PR-292](https://github.com/likeastore/ngDialog/pull/292), [ISSUE-281](https://github.com/likeastore/ngDialog/issues/281)) 

That was huge. Thanks everybody!

# 0.4.0

- [x] new `resolve` option for defining locals for ngDialog controller by [@rur](https://github.com/rur) ([PR-182](https://github.com/likeastore/ngDialog/pull/182))
- [x] support for `controllerAs` pattern by [@andrewogburn](https://github.com/andrewogburn) and [@sprbikkel](https://github.com/sprbikkel) ([PR-205](https://github.com/likeastore/ngDialog/pull/205), [PR-224](https://github.com/likeastore/ngDialog/pull/224))
- [x] added accessibility improvements (keyboard focus management / ARIA attribute) by [@richardszalay](https://github.com/richardszalay) ([PR-166](https://github.com/likeastore/ngDialog/pull/166))
- [x] added `isOpen(id)` public method by [@kasimoglou](https://github.com/kasimoglou) ([PR-219](https://github.com/likeastore/ngDialog/pull/219)) 
- [x] fix for `esc` should only close top dialog by [@jemise111](https://github.com/jemise111) ([PR-226](https://github.com/likeastore/ngDialog/pull/226))
- [x] fix for flickering dialogs in Internet Explorer by [@MvHMontySCOUT](https://github.com/MvHMontySCOUT) ([PR-207](https://github.com/likeastore/ngDialog/pull/207), discussion - [ISSUE-142](https://github.com/likeastore/ngDialog/issues/142))
- [x] fix issue when opening multiple dialogs simultaneously by [@bchelli](https://github.com/bchelli) ([PR-221](https://github.com/likeastore/ngDialog/pull/221))
- [x] various minor bug fixes, general improvements and examples updates

Thanks everybody, you're awesome! :dancer: :+1:

# 0.3.12

- [x] better `box-sizing` policy

# 0.3.11

- [x] prevent the modal from closing if preCloseCallback returns a falsy value

# 0.3.10

- [x] fix negative dialogs count

# 0.3.9

- [x] fix destroy scope with animation for multiple dialog [ISSUE-125](https://github.com/likeastore/ngDialog/issues/125)

# 0.3.8

- [x] Make ngDialog work with AngularJS 1.3 when `$compileProvider` debug info is disabled - `$compileProvider.debugInfoEnabled(false)`.

# 0.3.7

- [x] support for [UMD pattern](https://github.com/umdjs/umd)
- [x] get rid of `module` variable in source code
- [x] get rid of `window` dependency in favor of `$window`

# 0.3.6

- [x] finally (after many requests) `$scope.ngDialogData` holds reference to the objects passed instead of copying them.

# 0.3.5

- [x] fix for HammerJS 1.1 breaking dialog

# 0.3.4

- [x] add support for `overlay` option (https://github.com/likeastore/ngDialog/issues/117)

# 0.3.3

- [x] successful tests and support for Angular.js `1.3.x`

# 0.3.2

- [x] fixed an issue with Hammer.js manager

# 0.3.1

- [x] `ngDialog.closing` event
- [x] `closeByNavigation` option
- [x] `templateUrl` option

# 0.3.0

- [x] `.openConfirm()` method
- [x] `.setForceBodyReload()` method
- [x] add support for `.setDefaults()` method
- [x] fix scroll jump bug
- [x] fix event broadcasting to occur at the times they should
- [x] fix for `ngDialogData` being passed after controller instantiation
- [x] allow objects for `ngDialogData`
- [x] `cache` option
- [x] `preCloseCallback` option
- [x] `appendTo` option
- [x] `name` option
- [x] minor code fixes and optimizations, examples improvements

# 0.2.2
