# iFrame Resizer
[![NPM version](https://badge.fury.io/js/iframe-resizer.svg)](http://badge.fury.io/js/iframe-resizer)
[![NPM Downloads](https://img.shields.io/npm/dt/iframe-resizer.svg)](https://npm-stat.com/charts.html?package=iframe-resizer&from=2015-09-01)
[![](https://data.jsdelivr.com/v1/package/npm/iframe-resizer/badge?style=rounded)](https://www.jsdelivr.com/package/npm/iframe-resizer) <!--
[![Build Status](https://travis-ci.org/davidjbradshaw/iframe-resizer.svg?branch=master)](https://travis-ci.org/davidjbradshaw/iframe-resizer)
[![Known Vulnerabilities](https://snyk.io/test/github/davidjbradshaw/iframe-resizer/badge.svg)](https://snyk.io/test/github/davidjbradshaw/iframe-resizer)
-->[![Coverage Status](https://coveralls.io/repos/davidjbradshaw/iframe-resizer/badge.svg?branch=master&service=github)](https://coveralls.io/github/davidjbradshaw/iframe-resizer)
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://www.paypal.me/davidjbradshaw)

This library enables the automatic resizing of the height and width of both same and cross domain iFrames to fit their contained content. It provides a range of features to address the most common issues with using iFrames, these include:

* Height and width resizing of the iFrame to content size.
* Works with multiple and nested iFrames.
* Domain authentication for cross domain iFrames.
* Provides a range of page size calculation methods to support complex CSS layouts.
* Detects changes to the DOM that can cause the page to resize using [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver).
* Detects events that can cause the page to resize (Window Resize, CSS Animation and Transition, Orientation Change and Mouse events).
* Simplified messaging between iFrame and host page via [postMessage](https://developer.mozilla.org/en-US/docs/Web/API/window.postMessage).
* Fixes in page links in iFrame and supports links between the iFrame and parent page.
* Provides custom sizing and scrolling methods.
* Exposes parent position and viewport size to the iFrame.
* Works with [ViewerJS](http://viewerjs.org/) to support PDF and ODF documents.
* Fallback support down to IE8.

### Install

This package can be installed via NPM (`npm install iframe-resizer -save`) or Yarn (`yarn add iframe-resizer`).

### CDNs
This package is also available on [cdnjs](https://cdnjs.com/libraries/iframe-resizer) and [jsDelivr](https://www.jsdelivr.com/package/npm/iframe-resizer).

### Getting started
The package contains two minified JavaScript files in the [js](js) folder. The first ([iframeResizer.min.js](https://raw.githubusercontent.com/davidjbradshaw/iframe-resizer/master/js/iframeResizer.min.js)) is for the page hosting the iFrames. It can be called with **native** JavaScript;

```js
var iframes = iFrameResize( [{options}], [css selector] || [iframe] );
```

or via **jQuery**. (See [notes](#browser-compatibility) below for using native version with IE8).

```js
$('iframe').iFrameResize( [{options}] );
```

The second file ([iframeResizer.contentWindow.min.js](https://raw.github.com/davidjbradshaw/iframe-resizer/master/js/iframeResizer.contentWindow.min.js)) is a **native** JavaScript file that needs placing in the page contained within your iFrame. <i>This file is designed to be a guest on someone else's system, so has no dependencies and won't do anything until it's activated by a message from the containing page</i>.

### Typical setup

The normal configuration is to have the iFrame resize when the browser window changes size or the content of the iFrame changes. To set this up you need to configure one of the dimensions of the iFrame to a percentage and tell the library to only update the other dimension. Normally you would set the width to 100% and have the height scale to fit the content.

```html
<style>iframe{width: 1px;min-width: 100%;}</style>
<iframe id="myIframe" src="http://anotherdomain.com/iframe.html" scrolling="no"></iframe>
<script>iFrameResize({log:true}, '#myIframe')</script>
```

**Notes:** Using <i>min-width</i> to set the width of the iFrame, works around an issue in iOS that can prevent the iFrame from sizing correctly.  Also the scrolling attribute is set to 'no' in the iFrame tag, as older versions of IE don't allow this to be turned off in code and can just slightly add a bit of extra space to the bottom of the content that it doesn't report when it returns the height.

If you have problems, check the [troubleshooting](#troubleshooting) section below.

### Example
To see this working take a look at this [example](http://davidjbradshaw.com/iframe-resizer/example/) and watch the [console](https://developer.mozilla.org/en-US/docs/Tools/Web_Console).

## Options

### log

	default: false
	type:    boolean

Setting the `log` option to true will make the scripts in both the host page and the iFrame output everything they do to the JavaScript console so you can see the communication between the two scripts.

### autoResize

	default: true
	type:    boolean

When enabled changes to the Window size or the DOM will cause the iFrame to resize to the new content size. Disable if using size method with custom dimensions.

<i>Note: When set to false the iFrame will still inititally size to the contained content, only additional resizing events are disabled.</i>

### bodyBackground

	default: null
	type:    string

Override the body background style in the iFrame.

### bodyMargin

	default: null
	type:    string || number

Override the default body margin style in the iFrame. A string can be any valid value for the CSS margin attribute, for example '8px 3em'. A number value is converted into px.

### bodyPadding

	default: null
	type:    string || number

Override the default body padding style in the iFrame. A string can be any valid value for the CSS margin attribute, for example '8px 3em'. A number value is converted into px.

### checkOrigin

	default: true
	type:    boolean || array

When set to true, only allow incoming messages from the domain listed in the `src` property of the iFrame tag. If your iFrame navigates between different domains, ports or protocols; then you will need to provide an array of URLs or disable this option.

### inPageLinks

	default: false
	type:    boolean

When enabled in page linking inside the iFrame and from the iFrame to the parent page will be enabled.

### interval

	default: 32  (in ms)
	type:    number

In browsers that don't support [mutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver), such as IE10, the library falls back to using setInterval, to check for changes to the page size. The default value is equal to two frame refreshes at 60Hz, setting this to a higher value will make screen redraws noticeable to the user.

Setting this property to a negative number will force the interval check to run instead of [mutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver).

Set to zero to disable.

### heightCalculationMethod

    default: 'bodyOffset'
    values:  'bodyOffset' | 'bodyScroll' | 'documentElementOffset' | 'documentElementScroll' |
             'max' | 'min' | 'grow' | 'lowestElement' | 'taggedElement'

By default the height of the iFrame is calculated by converting the margin of the `body` to <i>px</i> and then adding the top and bottom figures to the offsetHeight of the `body` tag.

In cases where CSS styles causes the content to flow outside the `body` you may need to change this setting to one of the following options. Each can give different values depending on how CSS is used in the page and each has varying side-effects. You will need to experiment to see which is best for any particular circumstance.

* **bodyScroll** uses `document.body.scrollHeight` <sup>*</sup>
* **documentElementOffset** uses `document.documentElement.offsetHeight`
* **documentElementScroll** uses `document.documentElement.scrollHeight` <sup>*</sup>
* **max** takes the largest value of the main four options <sup>*</sup>
* **min** takes the smallest value of the main four options <sup>*</sup>
* **grow** same as **max** but disables the double resize that is used to workout if the iFrame needs to shrink. This provides much better performance if your iFrame will only ever increase in size
* **lowestElement** Loops though every element in the the DOM and finds the lowest bottom point <sup>†</sup>
* **taggedElement** Finds the bottom of the lowest element with a `data-iframe-height` attribute

<i>Notes:</i>

<i>**If the default option doesn't work then the best solutions is to either to use** taggedElement, **or to use** lowestElement **in modern browsers and** max **in IE10 downwards.**</i>

```js
var isOldIE = (navigator.userAgent.indexOf("MSIE") !== -1); // Detect IE10 and below

iFrameResize( {
	heightCalculationMethod: isOldIE ? 'max' : 'lowestElement'
});
```

Alternatively it is possible to add your own custom sizing method directly inside the iFrame, see [iFrame Page Options](https://github.com/davidjbradshaw/iframe-resizer#iframe-page-options) section below.

<sup> † </sup> <i>The **lowestElement** option is the most reliable way of determining the page height. However, it does have a performance impact in older versions of IE. In one screen refresh (16ms) Chrome can calculate the position of around 10,000 html nodes, whereas IE 8 can calculate approximately 50. The **taggedElement** option provides much greater performance by limiting the number of elements that need their position checked.</i>

<sup> * </sup><i>The **bodyScroll**, **documentElementScroll**, **max** and **min** options can cause screen flicker and will prevent the [interval](#interval) trigger downsizing the iFrame when the content shrinks. This is mainly an issue in IE 10 and below, where the [mutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver) event is not supported. To overcome this you need to manually trigger a page resize by calling the [parentIFrame.size()](#size-customheight-customwidth) method when you remove content from the page.</i>


### maxHeight / maxWidth

    default: infinity
    type:    integer

Set maximum height/width of iFrame.

### minHeight / minWidth

    default: 0
    type:    integer

Set minimum height/width of iFrame.

### resizeFrom

    default: 'parent'
    values: 'parent', 'child'

Listen for resize events from the parent page, or the iFrame. Select the 'child' value if the iFrame can be resized independently of the browser window. <i>Selecting this value can cause issues with some height calculation methods on mobile devices</i>.

### scrolling

    default: false
    type:    boolean | 'omit'

Enable scroll bars in iFrame.

* **true** applies `scrolling="yes"`
* **false** applies `scrolling="no"`
* **'omit'** applies no `scrolling` attribute to the iFrame

### sizeHeight

	default: true
	type:    boolean

Resize iFrame to content height.

### sizeWidth

	default: false
	type:    boolean

Resize iFrame to content width.


### tolerance

	default: 0
	type:    integer

Set the number of pixels the iFrame content size has to change by, before triggering a resize of the iFrame.

### widthCalculationMethod

    default: 'scroll'
    values:  'bodyOffset' | 'bodyScroll' | 'documentElementOffset' | 'documentElementScroll' |
             'max' | 'min' | 'scroll' | 'rightMostElement' | 'taggedElement'

By default the width of the page is worked out by taking the greater of the **documentElement** and **body** scrollWidth values.

Some CSS techniques may require you to change this setting to one of the following options. Each can give different values depending on how CSS is used in the page and each has varying side-effects. You will need to experiment to see which is best for any particular circumstance.

* **bodyOffset** uses `document.body.offsetWidth`
* **bodyScroll** uses `document.body.scrollWidth` <sup>*</sup>
* **documentElementOffset** uses `document.documentElement.offsetWidth`
* **documentElementScroll** uses `document.documentElement.scrollWidth` <sup>*</sup>
* **scroll** takes the largest value of the two scroll options
* **max** takes the largest value of the main four options <sup>*</sup>
* **min** takes the smallest value of the main four options <sup>*</sup>
* **rightMostElement** Loops though every element in the the DOM and finds the right most point <sup>†</sup>
* **taggedElement** Finds the left most element with a `data-iframe-width` attribute

Alternatively it is possible to add your own custom sizing method directly inside the iFrame, see [iFrame Page Options](https://github.com/davidjbradshaw/iframe-resizer#iframe-page-options) section below.

<sup> † </sup> <i>The **rightMostElement** option is the most reliable way of determining the page width. However, it does have a performance impact in older versions of IE. In one screen refresh (16ms) Chrome can calculate the position of around 10,000 html nodes, whereas IE 8 can calculate approximately 50. The **taggedElement** option provides much greater performance by limiting the number of elements that need their position checked.</i>

<sup> * </sup><i>The **bodyScroll**, **documentElementScroll**, **max** and **min** options can cause screen flicker and will prevent the [interval](#interval) trigger downsizing the iFrame when the content shrinks. This is mainly an issue in IE 10 and below, where the [mutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver) event is not supported. To overcome this you need to manually trigger a page resize by calling the [parentIFrame.size()](#size-customheight-customwidth) method when you remove content from the page.</i>



## Callback Methods

### closedCallback

	type: function (iframeID)

Called when iFrame is closed via `parentIFrame.close()` or `iframe.iFrameResizer.close()` methods. See below for details.

### initCallback

	type: function (iframe)

Initial setup callback function.

### messageCallback

	type: function ({iframe,message})

Receive message posted from iFrame with the `parentIFrame.sendMessage()` method.

### resizedCallback

	type: function ({iframe,height,width,type})

Function called after iFrame resized. Passes in messageData object containing the **iFrame**, **height**, **width** and the **type** of event that triggered the iFrame to resize.

### scrollCallback

	type: function ({x,y})

Called before the page is repositioned after a request from the iFrame, due to either an in page link, or a direct request from either [parentIFrame.scrollTo()](#scrolltoxy) or [parentIFrame.scrollToOffset()](#scrolltooffsetxy). If this callback function returns false, it will stop the library from repositioning the page, so that you can implement your own animated page scrolling instead.



## IFrame Page Options

The following options can be set from within the iFrame page by creating a `window.iFrameResizer` object before the JavaScript file is loaded into the page.

```html
<script>
  window.iFrameResizer = {
    targetOrigin: 'http://mydomain.com'
  }
</script>
<script src="js/iframeresizer.contentwindow.js"></script>
```

### targetOrigin

	default: '*'
	type: string

This option allows you to restrict the domain of the parent page, to prevent other sites mimicing your parent page.

### messageCallback

	type: function (message)

Receive message posted from the parent page with the `iframe.iFrameResizer.sendMessage()` method (See below for details).

### readyCallback

    type: function()

This function is called once iFrame-Resizer has been initialized after receiving a call from the parent page. If you need to call any of the parentIFrame methods (See below) during page load, then they should be called from this callback.

### heightCalculationMethod / widthCalculationMethod

    default: null
    type: string | function() { return integer }

These options can be used to override the option set in the parent page (See above for details on available values). This can be useful when moving between pages in the iFrame that require different values for these options.

Altenatively you can pass a custom function that returns the size as an integer. This can be useful when none of the standard ways of working out the size are suitable. However, normally problems with sizing are due to CSS issues and this should be looked at first.

## IFrame Page Methods

These methods are available in the iFrame via the `window.parentIFrame` object. These method should be contained by a test for the `window.parentIFrame` object, in case the page is not loaded inside an iFrame. For example:

```js
if ('parentIFrame' in window) {
  parentIFrame.close();
}
```

### autoResize([bool])

Turn autoResizing of the iFrame on and off. Returns bool of current state.

### close()

Remove the iFrame from the parent page.

### getId()

Returns the ID of the iFrame that the page is contained in.

### getPageInfo(callback || false)

Ask the containing page for its positioning coordinates. You need to provide a callback which receives an object with the following properties:

* **iframeHeight** The height of the iframe in pixels
* **iframeWidth** The width of the iframe in pixels
* **clientHeight** The height of the viewport in pixels
* **clientWidth** The width of the viewport in pixels
* **offsetLeft** The number of pixels between the left edge of the containing page and the left edge of the iframe
* **offsetTop** The number of pixels between the top edge of the containing page and the top edge of the iframe
* **scrollLeft** The number of pixels between the left edge of the iframe and the left edge of the iframe viewport
* **scrollTop** The number of pixels between the top edge of the iframe and the top edge of the iframe viewport

Your callback function will be recalled when the parent page is scrolled or resized.

Pass `false` to disable the callback.

### scrollTo(x,y)

Scroll the parent page to the coordinates x and y.

### scrollToOffset(x,y)

Scroll the parent page to the coordinates x and y relative to the position of the iFrame.

### sendMessage(message,[targetOrigin])

Send data to the containing page, `message` can be any data type that can be serialized into JSON. The `targetOrigin` option is used to restrict where the message is sent to; to stop an attacker mimicking your parent page. See the MDN documentation on [postMessage](https://developer.mozilla.org/en-US/docs/Web/API/Window.postMessage) for more details.

### setHeightCalculationMethod(heightCalculationMethod)

Change the method use to workout the height of the iFrame.

### size ([customHeight],[ customWidth])

Manually force iFrame to resize. This method optionally accepts two arguments: **customHeight** & **customWidth**. To use them you need first to disable the `autoResize` option to prevent auto resizing and enable the `sizeWidth` option if you wish to set the width.

```js
iFrameResize({
  autoResize: false,
  sizeWidth: true
});
```

Then you can call the `size` method with dimensions:

```js
if ('parentIFrame' in window) {
  parentIFrame.size(100); // Set height to 100px
}
```



## IFrame Object Methods

Once the iFrame has been initialized, an `iFrameResizer` object is bound to it. This has the following methods available.

### close()

Remove the iFrame from the page.

### moveToAnchor(anchor)

Move to anchor in iFrame.

### removeListeners()

Detach event listeners. This is option allows Virtual Doms to remove an iFrame.

### resize()

Tell the iFrame to resize itself.

### sendMessage(message,[targetOrigin])

Send data to the containing page, `message` can be any data type that can be serialized into JSON. The `targetOrigin` option is used to restrict where the message is sent to, in case your iFrame navigates away to another domain.


## Troubleshooting

The first steps to investigate a problem is to make sure you are using the latest version and then enable the [log](#log) option, which outputs everything that happens to the [JavaScript Console](https://developers.google.com/chrome-developer-tools/docs/console#opening_the_console). This will enable you to see what both the iFrame and host page are up to and also see any JavaScript error messages.

Solutions for the most common problems are outlined in this section. If you need futher help, then please ask questions on [StackOverflow](http://stackoverflow.com/questions/tagged/iframe-resizer) with the `iframe-resizer` tag.

Bug reports and pull requests are welcome on the [issue tracker](https://github.com/davidjbradshaw/iframe-resizer/issues). Please read the [contributing guidelines](https://github.com/davidjbradshaw/iframe-resizer/blob/master/CONTRIBUTING.md) before openning a ticket, as this will ensure a faster resolution.

### Multiple IFrames on one page
When the resizer does not work using multiple IFrames on one page, make sure that each frame has an unique id or no ids at all.

### IFrame not sizing correctly
If a larger element of content is removed from the normal document flow, through the use of absolute positioning, it can prevent the browser working out the correct size of the page. In such cases you can change the [heightCalculationMethod](#heightcalculationmethod) to uses one of the other sizing methods.

### IFrame not downsizing
The most likely cause of this problem is having set the height of an element to be 100% of the page somewhere in your CSS. This is normally on the `html` or `body` elements, but it could be on any element in the page. This can sometimes be got around by using the `taggedElement` height calculation method and added a `data-iframe-height` attribute to the element that you want to define the bottom position of the page. You may find it useful to use `position: relative` on this element to define a bottom margin or allow space for a floating footer.

Not having a valid [HTML document type](http://en.wikipedia.org/wiki/Document_type_declaration) in the iFrame can also sometimes prevent downsizing. At it's most simplest this can be the following.

```html
<!DOCTYPE html>
```

### IFrame not resizing
The most common cause of this is not placing the [iframeResizer.contentWindow.min.js](https://raw.github.com/davidjbradshaw/iframe-resizer/master/js/iframeResizer.contentWindow.min.js) script inside the iFramed page. If the other page is on a domain outside your control and you can not add JavaScript to that page, then now is the time to give up all hope of ever getting the iFrame to size to the content. As it is impossible to work out the size of the contained page, without using JavaScript on both the parent and child pages.

### IFrame not detecting CSS :hover events
If your page resizes via CSS `:hover` events, these won't be detected by default. It is however possible to create `mouseover` and `mouseout` event listeners on the elements that are resized via CSS and have these events call the [parentIFrame.size()](##parentiframesize-customheight-customwidth) method. With jQuery this can be done as follows

```js
function resize(){
  if ('parentIFrame' in window) {
    // Fix race condition in FireFox with setTimeout
    setTimeout(parentIFrame.size.bind(parentIFrame),0);
  }
}

$(*Element with hover style*).hover(resize);
```

### IFrame not detecting textarea resizes

Both FireFox and the WebKit based browsers allow the user to resize `textarea` input boxes. Unfortunately the WebKit browsers don't trigger the mutation event when this happens. This can be worked around to some extent with the following code.

```js
function store(){
  this.x = this.offsetWidth;
  this.y = this.offsetHeight;
}

$('textarea').each(store).on('mouseover mouseout',function(){
  if (this.offsetWidth !== this.x || this.offsetHeight !== this.y){
    store.call(this);
    if ('parentIFrame' in window){
      parentIFrame.size();
    }
  }
});
```

### IFrame flickers

Some of the alternate [height calculation methods](#heightcalculationmethod), such as **max** can cause the iFrame to flicker. This is due to the fact that to check for downsizing, the iFrame first has to be downsized before the new height can be worked out. This effect can be reduced by setting a [minSize](#minheight--minwidth) value, so that the iFrame is not reset to zero height before regrowing.

In modern browsers, if the default [height calculation method](#heightcalculationmethod) does not work, then it is normally best to use **lowestElement**, which is flicker free, and then provide a fallback to **max** in IE10 downwards.

```js
var isOldIE = (navigator.userAgent.indexOf("MSIE") !== -1); // Detect IE10 and below

iFrameResize({
  heightCalculationMethod: isOldIE ? 'max' : 'lowestElement',
  minSize:100
});
```
<i>Please see the notes section under [heightCalculationMethod](#heightcalculationmethod) to understand the limitations of the different options.</i>

### ParentIFrame not found errors
The `parentIFrame` object is created once the iFrame has been initially resized. If you wish to use it during page load you will need call it from the readyCallback.

```html
<script>
  window.iFrameResizer = {
    readyCallback: function(){
      var myId = window.parentIFrame.getId();
      console.log('The ID of the iFrame in the parent page is: '+myId);
    }
  }
</script>
<script src="js/iframeresizer.contentwindow.js"></script>
```

### PDF and OpenDocument files
It is not possible to add the required JavaScript to PDF and ODF files. However, you can get around this limitation by using [ViewerJS](http://viewerjs.org/) to render these files inside a HTML page, that also contains the iFrame JavaScript file ([iframeResizer.contentWindow.min.js](https://raw.github.com/davidjbradshaw/iframe-resizer/master/js/iframeResizer.contentWindow.min.js)).

### Unexpected message received error
By default the origin of incoming messages is checked against the `src` attribute of the iFrame. If they don't match an error is thrown. This behaviour can be disabled by setting the [checkOrigin](#checkorigin) option to **false**.

### Width not resizing
By default only changes in height are detected, if you want to calculate the width you need to set the `sizeWidth` opion to true and the `sizeHeight` option to false.


## Browser compatibility
### jQuery version

Basic support works with all browsers which support [window.postMessage](http://caniuse.com/#feat=x-doc-messaging) (IE8+). Some advanced features require the native version polyfil to work in IE8.

### Native version

Additionally requires support for [Array.prototype.forEach](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach) and [Function.prototype.bind](https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_objects/Function/bind) (IE9+), plus [document.querySelectorAll](https://developer.mozilla.org/en-US/docs/Web/API/Document.querySelectorAll) (IE8 Standards Mode). For **IE8** force [Standards Mode](http://en.wikipedia.org/wiki/Internet_Explorer_8#Standards_mode) and include the [IE8 PolyFils](https://github.com/davidjbradshaw/iframe-resizer/blob/master/src/ie8.polyfils.js) on the host page.

```html
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--[if lte IE 8]>
  <script type="text/javascript" src="js/ie8.polyfils.min.js"></script>
<![endif]-->
```


## Upgrading to version 3

The requirements for IE8 support in version 3 have changed from version 2. If you still require IE8 support than you will need to ensure you include the new ie8.polyfil.js file, as outlined above.

The parentIFrame methods object in the iFrame is now always available and the `enablePublicMethods` option has been removed. The `enableInPageLinks` option has been rename to `inPageLinks`.


## Version History

* v3.6.5 [#658](https://github.com/davidjbradshaw/iframe-resizer/pull/658) Add `.npmignore` to project [[Sebastian Lamelas]](smulesoft).
* v3.6.4 [#651](https://github.com/davidjbradshaw/iframe-resizer/pull/651) Fix issue resource leak when iframe removed from the page [[Steffen Eckardt](seckardt)]. [#651](https://github.com/davidjbradshaw/iframe-resizer/pull/651) Make Require.js optional when it is included on the page before iframe-resizer [[Dahmian Owen](dahmian)].
* v3.6.3 [#635](https://github.com/davidjbradshaw/iframe-resizer/pull/635) Fix issue with undefined ID [[Henry Schein](ddxdental)]. [#582](https://github.com/davidjbradshaw/iframe-resizer/pull/582) Add `omit` option to `scrolling` config [[Matt Ryan](mryand)].
* v3.6.2 [#596](https://github.com/davidjbradshaw/iframe-resizer/pull/596) Add Passive Event Listener for Performance [[Henrik Vendelbo](thepian)]. [#613](https://github.com/davidjbradshaw/iframe-resizer/pull/613) Check if the iFrameResize function is attached to the prototype of jQuery [[Paul Antal](paul-antal)]. [#620](https://github.com/davidjbradshaw/iframe-resizer/pull/620) Fixed an issue where host page fires init before iframe receiver setup [[Mark Zhou](mrmarktyy)]. [#620](https://github.com/davidjbradshaw/iframe-resizer/pull/620) Add `removeListeners` method to better support React [[Khang Nguyen](khangiskhan)].
* v3.6.1 [#576](https://github.com/davidjbradshaw/iframe-resizer/pull/576) Fix race condition caused by react-iframe-resizer removing the domNode and calling `close()`.
* v3.6.0 [#562](https://github.com/davidjbradshaw/iframe-resizer/pull/562) Fix issue with debounce getPageInfo when their is more than one iFrame on the page [[Thomas Pringle](thomaspringle)]. [#568](https://github.com/davidjbradshaw/iframe-resizer/pull/568) Fix bug in Chrome 65 when iframe parent element has `display:none` set [[Steve Hong](aniude)].
* v3.5.16 [#554](https://github.com/davidjbradshaw/iframe-resizer/issues/554) Fix throttling of init event [[SHOTA](senta)]. [#553](https://github.com/davidjbradshaw/iframe-resizer/issues/553) Prevents unhandled exception in IE11 [[vitoss](vitoss)]. [#555](https://github.com/davidjbradshaw/iframe-resizer/issues/555) Fix IE PolyFil and make grunt-cli local [[Jan Schmidle](bitcloud)].
* v3.5.15 [#498](https://github.com/davidjbradshaw/iframe-resizer/issues/498) Fix bug "Cannot read property 'firstRun' of undefined" [[Shaun Johansen](shaunjohansen)]. [#517] Fix readyState issue in iFrame [[lostincomputer](lostincomputer)].
* v3.5.14 [#477](https://github.com/davidjbradshaw/iframe-resizer/issues/477) Fix bug when iFrame closed before first resize.
* v3.5.13 [#473](https://github.com/davidjbradshaw/iframe-resizer/issues/473) Improve no response from iFrame warning message.
* v3.5.12 [#475](https://github.com/davidjbradshaw/iframe-resizer/issues/475) Delay resizeCallback until after the iFrame has resized [[Codener](codener)].
* v3.5.11 [#470](https://github.com/davidjbradshaw/iframe-resizer/issues/470) Fix jQuery reference error [[Russell Schick](rschick)].
* v3.5.10 [#461](https://github.com/davidjbradshaw/iframe-resizer/issues/461) Don't run for server-side render
* v3.5.9 Show warning message if no response from iFrame. [#463](https://github.com/davidjbradshaw/iframe-resizer/issues/463) Suppress warning message when code loaded via module [[Sergey Pereskokov](SerjoPepper)].
* v3.5.8 [#315](https://github.com/davidjbradshaw/iframe-resizer/issues/315) Allow Scrolling to be set to 'auto'.
* v3.5.7 [#438](https://github.com/davidjbradshaw/iframe-resizer/issues/438) Check jQuery pluging wrapper not already loaded. [#423](https://github.com/davidjbradshaw/iframe-resizer/issues/423) Properly remove event listeners [[Aaron Hardy](Aaronius)]. [#401](https://github.com/davidjbradshaw/iframe-resizer/issues/401) Make tagged element fall back to all elements if tag not found. [#381](https://github.com/davidjbradshaw/iframe-resizer/issues/381) Fixing disconnect when iframe is missing temporarly [[Jeff Hicken](jhicken)]. Added warnings for missing iFrame and deprecated options.
* v3.5.5 [#373](https://github.com/davidjbradshaw/iframe-resizer/issues/373) Add option for custom size calculation methods in iFrame. [#374](https://github.com/davidjbradshaw/iframe-resizer/issues/374) Fix bug with in page links called from parent page.
* v3.5.4 [#362](https://github.com/davidjbradshaw/iframe-resizer/issues/362) Handle jQuery being loaded in odd ways. [#297](https://github.com/davidjbradshaw/iframe-resizer/issues/297) Ensure document ready before resizing
* v3.5.3 [#283](https://github.com/davidjbradshaw/iframe-resizer/issues/283) Added *readystatechange* event listener.
* v3.5.2 [#314](https://github.com/davidjbradshaw/iframe-resizer/pull/314) Add iframeHeight and iframeWidth properties to pageInfo [[Pierre Olivier](https://github.com/pomartel)]. [#303](https://github.com/davidjbradshaw/iframe-resizer/issues/303) Fix issue with IE8 polyFils.
* v3.5.1 [#286](https://github.com/davidjbradshaw/iframe-resizer/issues/286) Fixed *taggedElement / lowestElement / rightMostElement* to calculate correct margin [[Dan Ballance](danballance)].
* v3.5.0 Recall getPageInfo callback when parent page position changes. Added *Array.prototype.forEach* to IE8 polyfils.
* v3.4.2 Only teardown events on close if currently enabled.
* v3.4.1 [#271](https://github.com/davidjbradshaw/iframe-resizer/issues/271) Fix bower.json to point to *js* folder, rather then *src* [[Yachi](https://github.com/yachi)].
* v3.4.0 [#262](https://github.com/davidjbradshaw/iframe-resizer/issues/262) Add *getPageInfo* method to *parentIFrame* [[Pierre Olivier](https://github.com/pomartel)]. [#263](https://github.com/davidjbradshaw/iframe-resizer/issues/263) Change *leftMostElement* to rightMostElement [[Luiz Panariello](https://github.com/LuizPanariello)]. [#265](https://github.com/davidjbradshaw/iframe-resizer/issues/265) Fix issue when no options being passed and added test for this.
* v3.3.1 Point index.js to the JS folder, instead of the src folder. Added touch event listeners. *AutoResize* method now returns current state.
* v3.3.0 [#97](https://github.com/davidjbradshaw/iframe-resizer/issues/97) Add *autoResize* method to *parentIFrame*. Fix bug when *setHeightCalculationMethod* is called with invalid value. Add interval timer to event teardown. Log targetOrigin*. [#253](https://github.com/davidjbradshaw/iframe-resizer/issues/253) Work around bug with MooTools interfering with system objects.
* v3.2.0 Added calculation of margin to *LowestElement*, *LeftMostElement* and *taggedElement* calculation modes. Check callback function is a function before calling it. [#246](https://github.com/davidjbradshaw/iframe-resizer/issues/246) Fixed issue when *scrollCallback* changes the page position. [#247](https://github.com/davidjbradshaw/iframe-resizer/issues/247) Fix rounding issue when page is zoomed in Chrome [[thenewguy](https://github.com/thenewguy)].
* v3.1.1 Added *readyCallback* to iFrame. Create *iFrameResizer* object on iFrame during setup, rather than waiting for init message to be returned from iFrame. Add ref to iFrame in host page log messages. [#245](https://github.com/davidjbradshaw/iframe-resizer/issues/245) Fix issue with iFrame not correctly resizing when multiple images are injected into the page [[mdgbayly](https://github.com/mdgbayly)]. [#246](https://github.com/davidjbradshaw/iframe-resizer/issues/246) Fix issue with including ':' in messages passed to iFrames.
* v3.1.0 [#101](https://github.com/davidjbradshaw/iframe-resizer/issues/101) Support async loading of iFrame script. [#239](https://github.com/davidjbradshaw/iframe-resizer/issues/239) Throttle size checking to once per screen refresh (16ms). Fixed issue with hidden iFrames in FireFox. Improved handling of parent page events. [#236](https://github.com/davidjbradshaw/iframe-resizer/issues/236) Cope with iFrames that don't have a *src* value. [#242](https://github.com/davidjbradshaw/iframe-resizer/issues/242) Fix issue where iFrame is removed and then put back with same ID [[Alban Mouton](https://github.com/albanm)].
* v3.0.0 Added *taggedElement* size calculation method. [#199](https://github.com/davidjbradshaw/iframe-resizer/issues/199) Added in page options to iFrame. [#70](https://github.com/davidjbradshaw/iframe-resizer/issues/70) Added width calculation method options. Added methods to bound iFrames to comunicate from parent to iFrame. Ignore calls to setup an already bound iFrame. Improved event handling. Refactored MutationObserver functions. Moved IE8 polyfil from docs to own JS file and added *Funtion.prototype.bind()*. Added detection for tab focus. Fixed bug with nested inPageLinks. Public methods in iFrame now always enabled and option removed. Renamed enableInPageLinks to inPageLinks. Added double iFrame example.
* v2.8.10 Fixed bug with resizeFrom option not having default value in iFrame, if called from old version in parent page.
* v2.8.9 [#220](https://github.com/davidjbradshaw/iframe-resizer/issues/220) Switched from using *deviceorientation* to *orientationchange* event listner [[Brandon Kobel](https://github.com/kobelb)].
* v2.8.8 [#213](https://github.com/davidjbradshaw/iframe-resizer/issues/213) Ensure initCallback fires when iFrame not sized during initialisation. Check autoResize option before resizing from parent. Lower message about resize before initialisation from 'warn' to 'log'. Updated hover example.
* v2.8.7 [#205](https://github.com/davidjbradshaw/iframe-resizer/issues/205) Fix race condition when page resized during page init [[Ian Caunce](https://github.com/IanCaunce)]. [#203](https://github.com/davidjbradshaw/iframe-resizer/issues/203) Added option for *checkOrigin* to have list of allowed domains for the iFrame [[Andrej Golcov](https://github.com/andrej2k)]. [#202](https://github.com/davidjbradshaw/iframe-resizer/issues/202) Handle script being loaded more than once [[Nickolay Ribal](https://github.com/elektronik2k5)].
[#167](https://github.com/davidjbradshaw/iframe-resizer/issues/167) Added WebPack support [[Stephan Salat](https://github.com/ssalat)].
* v2.8.6 [#163](https://github.com/davidjbradshaw/iframe-resizer/issues/163) Moved window resize event detection from iFrame to parent page. [#160](https://github.com/davidjbradshaw/iframe-resizer/issues/160) Warn, rather than error, if iFrame has been unexpectantly removed from page. The *parentIFrame.close()* method nolonger calls *resizedCallback()*.
* v2.8.5 [#173](https://github.com/davidjbradshaw/iframe-resizer/issues/173) Scope settings to iFrame. [#171](https://github.com/davidjbradshaw/iframe-resizer/issues/171) Fixed *parentIFrame.close()* to work with 0 height iframes [Both [Reed Dadoune](https://github.com/ReedD)].
* v2.8.4 Added switch for inPageLinking support.
* v2.8.3 Throw error if passed a non-DOM object.
* v2.8.2 [#145](https://github.com/davidjbradshaw/iframe-resizer/issues/145) Fixed in page links, to work with HTML IDs that are not valid CSS IDs [[Erin Millard](https://github.com/ezzatron)]. Moved map files from src to js folder. Added to NPM.
* v2.8.1 [#138](https://github.com/davidjbradshaw/iframe-resizer/issues/138) Added option to pass in iFrame object, instead of selector.
* v2.8.0 [#68](https://github.com/davidjbradshaw/iframe-resizer/issues/68) Added support for in page links and *scrollCallback()* function. [#140](https://github.com/davidjbradshaw/iframe-resizer/issues/140) Added listener for *transitionend* event [[Mat Brown](https://github.com/outoftime)]. Added listeners for animation events. Added listener for *deviceorientation* event. Improved logging for nested iFrames.
* v2.7.1 [#131](https://github.com/davidjbradshaw/iframe-resizer/issues/131) Fix code that works out position of iFrame on host page.
* v2.7.0 [#129](https://github.com/davidjbradshaw/iframe-resizer/issues/129) Parse data passed to *parentIFrame.sendMessage()* into JSON to allow complex data types to be sent to *messageCallback()*.
* v2.6.5 [#107](https://github.com/davidjbradshaw/iframe-resizer/issues/107) Added Node support for use with Browserify.
* v2.6.4 [#115](https://github.com/davidjbradshaw/iframe-resizer/issues/115) Added *parentIFrame.scrollToOffset()* method.
* v2.6.3 [#115](https://github.com/davidjbradshaw/iframe-resizer/issues/115) Fixed issue with the range check sometimes causing non-resizing messages to be rejected.
* v2.6.2 [#104](https://github.com/davidjbradshaw/iframe-resizer/issues/104) Fixed issue with jQuery.noConflict [[Dmitry Mukhutdinov](https://github.com/flyingleafe)].
* v2.6.1 [#91](https://github.com/davidjbradshaw/iframe-resizer/issues/91) Fixed issue with jQuery version requiring empty object if no options are being set.
* v2.6.0 Added *parentIFrame.scrollTo()* method. Added *Tolerance* option. [#85](https://github.com/davidjbradshaw/iframe-resizer/issues/85) Update troubleshooting guide [[Kevin Sproles](https://github.com/kevinsproles)].
* v2.5.2 [#67](https://github.com/davidjbradshaw/iframe-resizer/issues/67) Allow lowercase `<iframe>` tags for XHTML complience [[SlimerDude](https://github.com/SlimerDude)]. [#69](https://github.com/davidjbradshaw/iframe-resizer/issues/69) Fix watch task typo in gruntfile.js [[Matthew Hupman](https://github.com/mhupman)]. Remove trailing comma in heightCalcMethods array [#76](https://github.com/davidjbradshaw/iframe-resizer/issues/76) [[Fabio Scala](https://github.com/fabioscala)].
* v2.5.1 [#58](https://github.com/davidjbradshaw/iframe-resizer/issues/58) Fixed endless loop and margin issues with an unnested mid-tier iframe. [#59](https://github.com/davidjbradshaw/iframe-resizer/issues/59) Fixed main property of [Bower](http://bower.io/) config file.
* v2.5.0 Added *minHeight*, *maxHeight*, *minWidth* and *maxWidth* options. Added *initCallback* and *closedCallback* functions (Close event calling *resizedCallback* is deprecated). Added **grow** and **lowestElement** *heightCalculationMethods*. Added AMD support. [#52](https://github.com/davidjbradshaw/iframe-resizer/issues/52) Added *sendMessage* example. [#54](https://github.com/davidjbradshaw/iframe-resizer/issues/54) Work around IE8's borked JS execution stack. [#55](https://github.com/davidjbradshaw/iframe-resizer/issues/55) Check datatype of passed in options.
* v2.4.8 Fix issue when message passed to messageCallback contains a colon.
* v2.4.7 [#49](https://github.com/davidjbradshaw/iframe-resizer/issues/49) Deconflict requestAnimationFrame.
* v2.4.6 [#46](https://github.com/davidjbradshaw/iframe-resizer/issues/46) Fix iFrame event listener in IE8.
* v2.4.5 [#41](https://github.com/davidjbradshaw/iframe-resizer/issues/41) Prevent error in FireFox when body is hidden by CSS [[Scott Otis](/Scotis)].
* v2.4.4 Enable nested iFrames ([#31](https://github.com/davidjbradshaw/iframe-resizer/issues/31) Filter incoming iFrame message in host-page script. [#33](https://github.com/davidjbradshaw/iframe-resizer/issues/33) Squash unexpected message warning when using nested iFrames. Improved logging for nested iFrames). [#38](https://github.com/davidjbradshaw/iframe-resizer/issues/38) Detect late image loads that cause a resize due to async image loading in WebKit [[Yassin](/ynh)]. Fixed :Hover example in FireFox. Increased trigger timeout lock to 64ms.
* v2.4.3 Simplified handling of double fired events. Fixed test coverage.
* v2.4.2 Fix missing 'px' unit when resetting height.
* v2.4.1 Fix screen flicker issue with scroll height calculation methods in v2.4.0.
* v2.4.0 Improved handling of alternate sizing methods, so that they will now shrink on all trigger events, except *Interval*. Prevent error when incoming message to iFrame is an object.
* v2.3.2 Fix backwards compatibility issue between V2 iFrame and V1 host-page scripts.
* v2.3.1 Added setHeightCalculationMethod() method in iFrame. Added *min* option to the height calculation methods. Invalid value for *heightCalculationMethod* is now a warning rather than an error and now falls back to the default value.
* v2.3.0 Added extra *heightCalculationMethod* options. Inject clearFix into 'body' to work around CSS floats preventing the height being correctly calculated. Added meaningful error message for non-valid values in *heightCalculationMethod*. Stop **click** events firing for 50ms after **size** events. Fixed hover example in old IE.
* v2.2.3 [#26](https://github.com/davidjbradshaw/iframe-resizer/issues/26) Locally scope jQuery to $, so there is no dependancy on it being defined globally.
* v2.2.2 [#25](https://github.com/davidjbradshaw/iframe-resizer/issues/25) Added click listener to Window, to detect CSS checkbox resize events.
* v2.2.1 [#24](https://github.com/davidjbradshaw/iframe-resizer/issues/24) Prevent error when incoming message to host page is an object [[Torjus Eidet](https://github.com/torjue)].
* v2.2.0 Added targetOrigin option to sendMessage function. Added bodyBackground option. Expanded troubleshooting section.
* v2.1.1 [#16](https://github.com/davidjbradshaw/iframe-resizer/issues/16) Option to change the height calculation method in the iFrame from offsetHeight to scrollHeight. Troubleshooting section added to docs.
* v2.1.0 Added sendMessage() and getId() to window.parentIFrame. Changed width calculation to use scrollWidth. Removed deprecated object name in iFrame.
* v2.0.0 Added native JS public function, renamed script filename to reflect that jQuery is now optional. Renamed *do(Heigh/Width)* to *size(Height/Width)*, renamed *contentWindowBodyMargin* to *bodyMargin* and renamed *callback* *resizedCallback*. Improved logging messages. Stop *resize* event firing for 50ms after *interval* event. Added multiple page example. Workout unsized margins inside the iFrame. The *bodyMargin* property now accepts any valid value for a CSS margin. Check message origin is iFrame. Removed deprecated methods.
* v1.4.4 Fixed *bodyMargin* bug.
* v1.4.3 CodeCoverage fixes. Documentation improvements.
* v1.4.2 Fixed size(250) example in IE8.
* v1.4.1 Setting `interval` to a negative number now forces the interval test to run instead of [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver).
* v1.4.0 [#12](https://github.com/davidjbradshaw/iframe-resizer/issues/12) Option to enable scrolling in iFrame, off by default. [#13](https://github.com/davidjbradshaw/iframe-resizer/issues/13) Bower dependancies updated.
* v1.3.7 Stop *resize* event firing for 50ms after *size* event. Added size(250) to example.
* v1.3.6 [#11](https://github.com/davidjbradshaw/iframe-resizer/issues/11) Updated jQuery to v1.11.0 in example due to IE11 having issues with jQuery v1.10.1.
* v1.3.5 Documentation improvements. Added Grunt-Bump to build script.
* v1.3.0 IFrame code now uses default values if called with an old version of the host page script. Improved function naming. Old names have been deprecated and removed from docs.
* v1.2.5 Fix publish to [plugins.jquery.com](https://plugins.jquery.com).
* v1.2.0 Added autoResize option, added height/width values to iFrame public size function, set HTML tag height to auto, improved documentation [All [Jure Mav](https://github.com/jmav)]. Plus setInterval now only runs in browsers that don't support [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver) and is on by default, sourceMaps added and close() method introduced to parentIFrame object in iFrame.
* v1.1.1 Added event type to messageData object.
* v1.1.0 Added DOM [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver) trigger to better detect content changes in iFrame, [#7](https://github.com/davidjbradshaw/iframe-resizer/issues/7) Set height of iFrame body element to auto to prevent resizing loop, if it's set to a percentage.
* v1.0.3 [#6](https://github.com/davidjbradshaw/iframe-resizer/issues/6) Force incoming messages to string. Migrated to Grunt 4.x. Published to Bower.
* v1.0.2 [#2](https://github.com/davidjbradshaw/iframe-resizer/issues/2) mime-type changed for IE8-10.
* v1.0.0 Initial pubic release.


## License
Copyright &copy; 2013-18 [David J. Bradshaw](https://github.com/davidjbradshaw).
Licensed under the [MIT License](LICENSE).

[![NPM](https://nodei.co/npm/iframe-resizer.png?downloads=true&downloadRank=true&stars=true)](https://nodei.co/npm/iframe-resizer/)
