# Version History

- v4.2.11 Remove unicorn/prefer-number-properties from eslint rules and revert `Number.parseInt` to `parseInt`

- v4.2.10 [#787](https://github.com/davidjbradshaw/iframe-resizer/issues/787) Replace `const` with `var` in index.js for IE10 [[Thomas Jaggi](https://github.com/backflip)]

- v4.2.9 [#783](https://github.com/davidjbradshaw/iframe-resizer/issues/783) Bind `requestAnimationFrame` to `window` to fix issue with FireFox Content-Scripts [[Greg Soltis](https://github.com/gsoltis)]

- v4.2.8 [#779](https://github.com/davidjbradshaw/iframe-resizer/issues/779) Fix issue with `javascript:void(0)` and `about:blank` URLs [[ceckoslab](https://github.com/ceckoslab)]

- v4.2.7 Add support for NPM funding

- v4.2.4 [#772](https://github.com/davidjbradshaw/iframe-resizer/issues/772) Fix issue with iframes inside ShaddowDOM elements [[Martin Belanger](https://github.com/martinbelanger)]

- v4.2.3 [#683](https://github.com/davidjbradshaw/iframe-resizer/issues/683) Include border top/bottom, plus padding top/bottom, when calculating heights on iframe with `box-sizing: border-box;` [[Jim Doyle](https://github.com//superelement)]. [#768](https://github.com/davidjbradshaw/iframe-resizer/issues/768) Fix issue with hidden iframes [[Tony Living]](https://github.com//tonyliving)

- v4.2.2 [#761](https://github.com/davidjbradshaw/iframe-resizer/pull/761) Check for iframe.src when parsing it for remoteHost [[Filip Stollar](https://github.com//SuNaden)]

- v4.2.1 [#723](https://github.com/davidjbradshaw/iframe-resizer/pull/723) Fix option to turn off `autoResize` from iframe, when `resizeFrom` is set to `parent` [[Dennis Kronbügel]](https://github.com//deBFM)

- v4.2.0 Add `onClose()` event to parent

- v4.1.1 [#686](https://github.com/davidjbradshaw/iframe-resizer/pull/694) Fix IE11 regression with Object.values [[Jonathan Lehman](https://github.com//jdlehman)]

- v4.1.0 [#686](https://github.com/davidjbradshaw/iframe-resizer/pull/686) Split client(Height/Width) into document and window values [[Bernhard Mäder](https://github.com//nuschk)]

- v4.0.4 [#674](https://github.com/davidjbradshaw/iframe-resizer/pull/674) Fix default export

- v4.0.3 [#606](https://github.com/davidjbradshaw/iframe-resizer/pull/606) Force height of clearFix div in iframe to 0

- v4.0.2 [#671](https://github.com/davidjbradshaw/iframe-resizer/pull/671) Fix issue with window resize

- v4.0.1 Fix documentation links in `README.md`

- v4.0.0 Drop support for IE8-10 and Andriod 4, renamed event handlers from `fooCallback` to `onFoo` and restructure documentation. Reformat code with Prettier and add eslint to build

- v3.6.5 [#658](https://github.com/davidjbradshaw/iframe-resizer/pull/658) Add `.npmignore` to project [[Sebastian Lamelas](https://github.com/smulesoft)]

- v3.6.4 [#651](https://github.com/davidjbradshaw/iframe-resizer/pull/651) Fix issue resource leak when iframe removed from the page [[Steffen Eckardt](https://github.com/seckardt)]. [#651](https://github.com/davidjbradshaw/iframe-resizer/pull/651) Make Require.js optional when it is included on the page before iframe-resizer [[Dahmian Owen](https://github.com/dahmian)]

- v3.6.3 [#635](https://github.com/davidjbradshaw/iframe-resizer/pull/635) Fix issue with undefined ID [[Henry Schein](https://github.com/ddxdental)]. [#582](https://github.com/davidjbradshaw/iframe-resizer/pull/582) Add `omit` option to `scrolling` config [[Matt Ryan](https://github.com/mryand)]

- v3.6.2 [#596](https://github.com/davidjbradshaw/iframe-resizer/pull/596) Add Passive Event Listener for Performance [[Henrik Vendelbo](https://github.com/thepian)]. [#613](https://github.com/davidjbradshaw/iframe-resizer/pull/613) Check if the iFrameResize function is attached to the prototype of jQuery [[Paul Antal](https://github.com/paul-antal)]. [#620](https://github.com/davidjbradshaw/iframe-resizer/pull/620) Fixed an issue where host page fires init before iframe receiver setup [[Mark Zhou](https://github.com/mrmarktyy)]. [#620](https://github.com/davidjbradshaw/iframe-resizer/pull/620) Add `removeListeners` method to better support React [[Khang Nguyen](https://github.com/khangiskhan)]

- v3.6.1 [#576](https://github.com/davidjbradshaw/iframe-resizer/pull/576) Fix race condition caused by react-iframe-resizer removing the domNode and calling `close()`

- v3.6.0 [#562](https://github.com/davidjbradshaw/iframe-resizer/pull/562) Fix issue with debounce getPageInfo when their is more than one iFrame on the page [[Thomas Pringle](https://github.com/thomaspringle)]. [#568](https://github.com/davidjbradshaw/iframe-resizer/pull/568) Fix bug in Chrome 65 when iframe parent element has `display:none` set [[Steve Hong](https://github.com/aniude)]

- v3.5.16 [#554](https://github.com/davidjbradshaw/iframe-resizer/issues/554) Fix throttling of init event [[SHOTA](https://github.com/senta)]. [#553](https://github.com/davidjbradshaw/iframe-resizer/issues/553) Prevents unhandled exception in IE11 [[vitoss](https://github.com/vitoss)]. [#555](https://github.com/davidjbradshaw/iframe-resizer/issues/555) Fix IE PolyFil and make grunt-cli local [[Jan Schmidle](https://github.com/bitcloud)]

- v3.5.15 [#498](https://github.com/davidjbradshaw/iframe-resizer/issues/498) Fix bug "Cannot read property 'firstRun' of undefined" [[Shaun Johansen](https://github.com/shaunjohansen)]. [#517] Fix readyState issue in iFrame [[lostincomputer](https://github.com/lostincomputer)]

- v3.5.14 [#477](https://github.com/davidjbradshaw/iframe-resizer/issues/477) Fix bug when iFrame closed before first resize

- v3.5.13 [#473](https://github.com/davidjbradshaw/iframe-resizer/issues/473) Improve no response from iFrame warning message

- v3.5.12 [#475](https://github.com/davidjbradshaw/iframe-resizer/issues/475) Delay onResize until after the iFrame has resized [[Codener](https://github.com/codener)]

- v3.5.11 [#470](https://github.com/davidjbradshaw/iframe-resizer/issues/470) Fix jQuery reference error [[Russell Schick](https://github.com/rschick)]

- v3.5.10 [#461](https://github.com/davidjbradshaw/iframe-resizer/issues/461) Don't run for server-side render

- v3.5.9 Show warning message if no response from iFrame. [#463](https://github.com/davidjbradshaw/iframe-resizer/issues/463) Suppress warning message when code loaded via module [[Sergey Pereskokov](https://github.com/SerjoPepper)]

- v3.5.8 [#315](https://github.com/davidjbradshaw/iframe-resizer/issues/315) Allow Scrolling to be set to 'auto'

- v3.5.7 [#438](https://github.com/davidjbradshaw/iframe-resizer/issues/438) Check jQuery pluging wrapper not already loaded. [#423](https://github.com/davidjbradshaw/iframe-resizer/issues/423) Properly remove event listeners [[Aaron Hardy](https://github.com/Aaronius)]. [#401](https://github.com/davidjbradshaw/iframe-resizer/issues/401) Make tagged element fall back to all elements if tag not found. [#381](https://github.com/davidjbradshaw/iframe-resizer/issues/381) Fixing disconnect when iframe is missing temporarly [[Jeff Hicken](https://github.com/jhicken)]. Added warnings for missing iFrame and deprecated options

- v3.5.5 [#373](https://github.com/davidjbradshaw/iframe-resizer/issues/373) Add option for custom size calculation methods in iFrame. [#374](https://github.com/davidjbradshaw/iframe-resizer/issues/374) Fix bug with in page links called from parent page

- v3.5.4 [#362](https://github.com/davidjbradshaw/iframe-resizer/issues/362) Handle jQuery being loaded in odd ways. [#297](https://github.com/davidjbradshaw/iframe-resizer/issues/297) Ensure document ready before resizing

- v3.5.3 [#283](https://github.com/davidjbradshaw/iframe-resizer/issues/283) Added _readystatechange_ event listener

- v3.5.2 [#314](https://github.com/davidjbradshaw/iframe-resizer/pull/314) Add iframeHeight and iframeWidth properties to pageInfo [[Pierre Olivier](https://github.com/pomartel)]. [#303](https://github.com/davidjbradshaw/iframe-resizer/issues/303) Fix issue with IE8 polyFils

- v3.5.1 [#286](https://github.com/davidjbradshaw/iframe-resizer/issues/286) Fixed _taggedElement / lowestElement / rightMostElement_ to calculate correct margin [[Dan Ballance](https://github.com/danballance)]

- v3.5.0 Recall getPageInfo callback when parent page position changes. Added _Array.prototype.forEach_ to IE8 polyfils

- v3.4.2 Only teardown events on close if currently enabled

- v3.4.1 [#271](https://github.com/davidjbradshaw/iframe-resizer/issues/271) Fix bower.json to point to _js_ folder, rather then _src_ [[Yachi](https://github.com/yachi)]

- v3.4.0 [#262](https://github.com/davidjbradshaw/iframe-resizer/issues/262) Add _getPageInfo_ method to _parentIFrame_ [[Pierre Olivier](https://github.com/pomartel)]. [#263](https://github.com/davidjbradshaw/iframe-resizer/issues/263) Change _leftMostElement_ to rightMostElement [[Luiz Panariello](https://github.com/LuizPanariello)]. [#265](https://github.com/davidjbradshaw/iframe-resizer/issues/265) Fix issue when no options being passed and added test for this

- v3.3.1 Point index.js to the JS folder, instead of the src folder. Added touch event listeners. _AutoResize_ method now returns current state

- v3.3.0 [#97](https://github.com/davidjbradshaw/iframe-resizer/issues/97) Add _autoResize_ method to _parentIFrame_. Fix bug when _setHeightCalculationMethod_ is called with invalid value. Add interval timer to event teardown. Log targetOrigin\*. [#253](https://github.com/davidjbradshaw/iframe-resizer/issues/253) Work around bug with MooTools interfering with system objects

- v3.2.0 Added calculation of margin to _LowestElement_, _LeftMostElement_ and _taggedElement_ calculation modes. Check callback function is a function before calling it. [#246](https://github.com/davidjbradshaw/iframe-resizer/issues/246) Fixed issue when _onScroll_ changes the page position. [#247](https://github.com/davidjbradshaw/iframe-resizer/issues/247) Fix rounding issue when page is zoomed in Chrome [[thenewguy](https://github.com/thenewguy)]

- v3.1.1 Added _onReady_ to iFrame. Create _iFrameResizer_ object on iFrame during setup, rather than waiting for init message to be returned from iFrame. Add ref to iFrame in host page log messages. [#245](https://github.com/davidjbradshaw/iframe-resizer/issues/245) Fix issue with iFrame not correctly resizing when multiple images are injected into the page [[mdgbayly](https://github.com/mdgbayly)]. [#246](https://github.com/davidjbradshaw/iframe-resizer/issues/246) Fix issue with including ':' in messages passed to iFrames

- v3.1.0 [#101](https://github.com/davidjbradshaw/iframe-resizer/issues/101) Support async loading of iFrame script. [#239](https://github.com/davidjbradshaw/iframe-resizer/issues/239) Throttle size checking to once per screen refresh (16ms). Fixed issue with hidden iFrames in FireFox. Improved handling of parent page events. [#236](https://github.com/davidjbradshaw/iframe-resizer/issues/236) Cope with iFrames that don't have a _src_ value. [#242](https://github.com/davidjbradshaw/iframe-resizer/issues/242) Fix issue where iFrame is removed and then put back with same ID [[Alban Mouton](https://github.com/albanm)]

- v3.0.0 Added _taggedElement_ size calculation method. [#199](https://github.com/davidjbradshaw/iframe-resizer/issues/199) Added in page options to iFrame. [#70](https://github.com/davidjbradshaw/iframe-resizer/issues/70) Added width calculation method options. Added methods to bound iFrames to comunicate from parent to iFrame. Ignore calls to setup an already bound iFrame. Improved event handling. Refactored MutationObserver functions. Moved IE8 polyfil from docs to own JS file and added _Funtion.prototype.bind()_. Added detection for tab focus. Fixed bug with nested inPageLinks. Public methods in iFrame now always enabled and option removed. Renamed enableInPageLinks to inPageLinks. Added double iFrame example

- v2.8.10 Fixed bug with resizeFrom option not having default value in iFrame, if called from old version in parent page

- v2.8.9 [#220](https://github.com/davidjbradshaw/iframe-resizer/issues/220) Switched from using _deviceorientation_ to _orientationchange_ event listner [[Brandon Kobel]/https://github.com/kobelb)]

- v2.8.8 [#213](https://github.com/davidjbradshaw/iframe-resizer/issues/213) Ensure onInit fires when iFrame not sized during initialisation. Check autoResize option before resizing from parent. Lower message about resize before initialisation from 'warn' to 'log'. Updated hover example

- v2.8.7 [#205](https://github.com/davidjbradshaw/iframe-resizer/issues/205) Fix race condition when page resized during page init [[Ian Caunce](https://github.com/IanCaunce)]. [#203](https://github.com/davidjbradshaw/iframe-resizer/issues/203) Added option for _checkOrigin_ to have list of allowed domains for the iFrame [[Andrej Golcov](https://github.com/andrej2k)]. [#202](https://github.com/davidjbradshaw/iframe-resizer/issues/202) Handle script being loaded more than once [[Nickolay Ribal](https://github.com/elektronik2k5)].
  [#167](https://github.com/davidjbradshaw/iframe-resizer/issues/167) Added WebPack support [[Stephan Salat](https://github.com/ssalat)]

- v2.8.6 [#163](https://github.com/davidjbradshaw/iframe-resizer/issues/163) Moved window resize event detection from iFrame to parent page. [#160](https://github.com/davidjbradshaw/iframe-resizer/issues/160) Warn, rather than error, if iFrame has been unexpectantly removed from page. The _parentIFrame.close()_ method nolonger calls _onResized()_

- v2.8.5 [#173](https://github.com/davidjbradshaw/iframe-resizer/issues/173) Scope settings to iFrame. [#171](https://github.com/davidjbradshaw/iframe-resizer/issues/171) Fixed _parentIFrame.close()_ to work with 0 height iframes [Both [Reed Dadoune](https://github.com/ReedD)]

- v2.8.4 Added switch for inPageLinking support

- v2.8.3 Throw error if passed a non-DOM object

- v2.8.2 [#145](https://github.com/davidjbradshaw/iframe-resizer/issues/145) Fixed in page links, to work with HTML IDs that are not valid CSS IDs [[Erin Millard](https://github.com/ezzatron)]. Moved map files from src to js folder. Added to NPM

- v2.8.1 [#138](https://github.com/davidjbradshaw/iframe-resizer/issues/138) Added option to pass in iFrame object, instead of selector

- v2.8.0 [#68](https://github.com/davidjbradshaw/iframe-resizer/issues/68) Added support for in page links and _onScroll()_ function. [#140](https://github.com/davidjbradshaw/iframe-resizer/issues/140) Added listener for _transitionend_ event [[Mat Brown](https://github.com/outoftime)]. Added listeners for animation events. Added listener for _deviceorientation_ event. Improved logging for nested iFrames

- v2.7.1 [#131](https://github.com/davidjbradshaw/iframe-resizer/issues/131) Fix code that works out position of iFrame on host page

- v2.7.0 [#129](https://github.com/davidjbradshaw/iframe-resizer/issues/129) Parse data passed to _parentIFrame.sendMessage()_ into JSON to allow complex data types to be sent to _onMessage()_

- v2.6.5 [#107](https://github.com/davidjbradshaw/iframe-resizer/issues/107) Added Node support for use with Browserify

- v2.6.4 [#115](https://github.com/davidjbradshaw/iframe-resizer/issues/115) Added _parentIFrame.scrollToOffset()_ method

- v2.6.3 [#115](https://github.com/davidjbradshaw/iframe-resizer/issues/115) Fixed issue with the range check sometimes causing non-resizing messages to be rejected

- v2.6.2 [#104](https://github.com/davidjbradshaw/iframe-resizer/issues/104) Fixed issue with jQuery.noConflict [[Dmitry Mukhutdinov](https://github.com/flyingleafe)]

- v2.6.1 [#91](https://github.com/davidjbradshaw/iframe-resizer/issues/91) Fixed issue with jQuery version requiring empty object if no options are being set

- v2.6.0 Added _parentIFrame.scrollTo()_ method. Added _Tolerance_ option. [#85](https://github.com/davidjbradshaw/iframe-resizer/issues/85) Update troubleshooting guide [[Kevin Sproles](https://github.com/kevinsproles)]

- v2.5.2 [#67](https://github.com/davidjbradshaw/iframe-resizer/issues/67) Allow lowercase `<iframe>` tags for XHTML complience [[SlimerDude](https://github.com/SlimerDude)]. [#69](https://github.com/davidjbradshaw/iframe-resizer/issues/69) Fix watch task typo in gruntfile.js [[Matthew Hupman](https://github.com/mhupman)]. Remove trailing comma in heightCalcMethods array [#76](https://github.com/davidjbradshaw/iframe-resizer/issues/76) [[Fabio Scala](https://github.com/fabioscala)]

- v2.5.1 [#58](https://github.com/davidjbradshaw/iframe-resizer/issues/58) Fixed endless loop and margin issues with an unnested mid-tier iframe. [#59](https://github.com/davidjbradshaw/iframe-resizer/issues/59) Fixed main property of [Bower](https://github.com/http://bower.io/) config file

- v2.5.0 Added _minHeight_, _maxHeight_, _minWidth_ and _maxWidth_ options. Added _onInit_ and _onClosed_ functions (Close event calling _onResized_ is deprecated). Added **grow** and **lowestElement** _heightCalculationMethods_. Added AMD support. [#52](https://github.com/davidjbradshaw/iframe-resizer/issues/52) Added _sendMessage_ example. [#54](https://github.com/davidjbradshaw/iframe-resizer/issues/54) Work around IE8's borked JS execution stack. [#55](https://github.com/davidjbradshaw/iframe-resizer/issues/55) Check datatype of passed in options

- v2.4.8 Fix issue when message passed to onMessage contains a colon

- v2.4.7 [#49](https://github.com/davidjbradshaw/iframe-resizer/issues/49) Deconflict requestAnimationFrame

- v2.4.6 [#46](https://github.com/davidjbradshaw/iframe-resizer/issues/46) Fix iFrame event listener in IE8

- v2.4.5 [#41](https://github.com/davidjbradshaw/iframe-resizer/issues/41) Prevent error in FireFox when body is hidden by CSS [[Scott Otis](https://github.com//Scotis)]

- v2.4.4 Enable nested iFrames ([#31](https://github.com/davidjbradshaw/iframe-resizer/issues/31) Filter incoming iFrame message in host-page script. [#33](https://github.com/davidjbradshaw/iframe-resizer/issues/33) Squash unexpected message warning when using nested iFrames. Improved logging for nested iFrames). [#38](https://github.com/davidjbradshaw/iframe-resizer/issues/38) Detect late image loads that cause a resize due to async image loading in WebKit [[Yassin](https://github.com//ynh)]. Fixed :Hover example in FireFox. Increased trigger timeout lock to 64ms

- v2.4.3 Simplified handling of double fired events. Fixed test coverage

- v2.4.2 Fix missing 'px' unit when resetting height

- v2.4.1 Fix screen flicker issue with scroll height calculation methods in v2.4.0

- v2.4.0 Improved handling of alternate sizing methods, so that they will now shrink on all trigger events, except _Interval_. Prevent error when incoming message to iFrame is an object

- v2.3.2 Fix backwards compatibility issue between V2 iFrame and V1 host-page scripts

- v2.3.1 Added setHeightCalculationMethod() method in iFrame. Added _min_ option to the height calculation methods. Invalid value for _heightCalculationMethod_ is now a warning rather than an error and now falls back to the default value

- v2.3.0 Added extra _heightCalculationMethod_ options. Inject clearFix into 'body' to work around CSS floats preventing the height being correctly calculated. Added meaningful error message for non-valid values in _heightCalculationMethod_. Stop **click** events firing for 50ms after **size** events. Fixed hover example in old IE

- v2.2.3 [#26](https://github.com/davidjbradshaw/iframe-resizer/issues/26) Locally scope jQuery to \$, so there is no dependancy on it being defined globally

- v2.2.2 [#25](https://github.com/davidjbradshaw/iframe-resizer/issues/25) Added click listener to Window, to detect CSS checkbox resize events

- v2.2.1 [#24](https://github.com/davidjbradshaw/iframe-resizer/issues/24) Prevent error when incoming message to host page is an object [[Torjus Eidet](https://github.com/torjue)]

- v2.2.0 Added targetOrigin option to sendMessage function. Added bodyBackground option. Expanded troubleshooting section

- v2.1.1 [#16](https://github.com/davidjbradshaw/iframe-resizer/issues/16) Option to change the height calculation method in the iFrame from offsetHeight to scrollHeight. Troubleshooting section added to docs

- v2.1.0 Added sendMessage() and getId() to window.parentIFrame. Changed width calculation to use scrollWidth. Removed deprecated object name in iFrame

- v2.0.0 Added native JS public function, renamed script filename to reflect that jQuery is now optional. Renamed _do(Heigh/Width)_ to _size(Height/Width)_, renamed _contentWindowBodyMargin_ to _bodyMargin_ and renamed _callback_ _onResized_. Improved logging messages. Stop _resize_ event firing for 50ms after _interval_ event. Added multiple page example. Workout unsized margins inside the iFrame. The _bodyMargin_ property now accepts any valid value for a CSS margin. Check message origin is iFrame. Removed deprecated methods

- v1.4.4 Fixed _bodyMargin_ bug

- v1.4.3 CodeCoverage fixes. Documentation improvements

- v1.4.2 Fixed size(250) example in IE8

- v1.4.1 Setting `interval` to a negative number now forces the interval test to run instead of [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver)

- v1.4.0 [#12](https://github.com/davidjbradshaw/iframe-resizer/issues/12) Option to enable scrolling in iFrame, off by default. [#13](https://github.com/davidjbradshaw/iframe-resizer/issues/13) Bower dependancies updated

- v1.3.7 Stop _resize_ event firing for 50ms after _size_ event. Added size(250) to example

- v1.3.6 [#11](https://github.com/davidjbradshaw/iframe-resizer/issues/11) Updated jQuery to v1.11.0 in example due to IE11 having issues with jQuery v1.10.1

- v1.3.5 Documentation improvements. Added Grunt-Bump to build script

- v1.3.0 IFrame code now uses default values if called with an old version of the host page script. Improved function naming. Old names have been deprecated and removed from docs

- v1.2.5 Fix publish to [plugins.jquery.com](https://plugins.jquery.com)

- v1.2.0 Added autoResize option, added height/width values to iFrame public size function, set HTML tag height to auto, improved documentation [All [Jure Mav](https://github.com/jmav)]. Plus setInterval now only runs in browsers that don't support [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver) and is on by default, sourceMaps added and close() method introduced to parentIFrame object in iFrame

- v1.1.1 Added event type to messageData object

- v1.1.0 Added DOM [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver) trigger to better detect content changes in iFrame, [#7](https://github.com/davidjbradshaw/iframe-resizer/issues/7) Set height of iFrame body element to auto to prevent resizing loop, if it's set to a percentage

- v1.0.3 [#6](https://github.com/davidjbradshaw/iframe-resizer/issues/6) Force incoming messages to string. Migrated to Grunt 4.x. Published to Bower

- v1.0.2 [#2](https://github.com/davidjbradshaw/iframe-resizer/issues/2) mime-type changed for IE8-10

- v1.0.0 Initial pubic release.
