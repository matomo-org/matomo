<img src="https://raw.githubusercontent.com/davidjbradshaw/iframe-resizer/master/img/logo-no-background.svg" alt="iFrame Resizer" style="margin-bottom: -20">

[![NPM version](https://badge.fury.io/js/iframe-resizer.svg)](http://badge.fury.io/js/iframe-resizer)
[![NPM Downloads](https://img.shields.io/npm/dm/iframe-resizer.svg)](https://npm-stat.com/charts.html?package=iframe-resizer&from=2014-12-31)
[![](https://data.jsdelivr.com/v1/package/npm/iframe-resizer/badge?style=rounded)](https://www.jsdelivr.com/package/npm/iframe-resizer)
[![Coverage Status](https://coveralls.io/repos/davidjbradshaw/iframe-resizer/badge.svg?branch=master&service=github)](https://coveralls.io/github/davidjbradshaw/iframe-resizer)

This library enables the automatic resizing of the height and width of both same and cross domain iFrames to fit their contained content. It provides a range of features to address the most common issues with using iFrames, these include:

- Height and width resizing of the iFrame to content size.
- Works with multiple and nested iFrames.
- Domain authentication for cross domain iFrames.
- Provides a range of page size calculation methods to support complex CSS layouts.
- Detects changes to the DOM that can cause the page to resize using [MutationObserver](https://developer.mozilla.org/en/docs/Web/API/MutationObserver).
- Detects events that can cause the page to resize (Window Resize, CSS Animation and Transition, Orientation Change and Mouse events).
- Simplified messaging between iFrame and host page via [postMessage](https://developer.mozilla.org/en-US/docs/Web/API/window.postMessage).
- Fixes in page links in iFrame and supports links between the iFrame and parent page.
- Provides custom sizing and scrolling methods.
- Exposes parent position and viewport size to the iFrame.
- Provides `onMouseEnter` and `onMouseLeave` events for the iFrame.
- Works with [ViewerJS](http://viewerjs.org/) to support PDF and ODF documents.

## Donate

Iframe-resizer is the result of many 100s of hours of work, if you would like to join others in showing support for the continued development of this project, then please feel free to buy me a coffee.

<a href="https://www.buymeacoffee.com/davidjbradshaw " target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/yellow_img.png" alt="Buy Me A Coffee" style="height: auto !important;width: auto !important;" ></a>

## Getting Started

### Install

This package can be installed via NPM (`npm install iframe-resizer --save`).

### Usage

The package contains two minified JavaScript files in the [js](https://github.com/davidjbradshaw/iframe-resizer/tree/master/js) folder. The first ([iframeResizer.min.js](https://raw.githubusercontent.com/davidjbradshaw/iframe-resizer/master/js/iframeResizer.min.js)) is for the page hosting the iFrames. It can be called with via JavaScript:

```js
const iframes = iFrameResize( [{options}], [css selector] || [iframe] );
```

The second file ([iframeResizer.contentWindow.min.js](https://raw.github.com/davidjbradshaw/iframe-resizer/master/js/iframeResizer.contentWindow.min.js)) needs placing in the page(s) contained within your iFrame. <i>This file is designed to be a guest on someone else's system, so has no dependencies and won't do anything until it's activated by a message from the containing page</i>.

### Typical setup

The normal configuration is to have the iFrame resize when the browser window changes size or the content of the iFrame changes. To set this up you need to configure one of the dimensions of the iFrame to a percentage and tell the library to only update the other dimension. Normally you would set the width to 100% and have the height scale to fit the content.

```html
<style>
  iframe {
    width: 1px;
    min-width: 100%;
  }
</style>
<script src="/js/iframeResizer.min.js"></script>
<iframe id="myIframe" src="http://anotherdomain.com/iframe.html"></iframe>
<script>
  iFrameResize({ log: true }, '#myIframe')
</script>
```

**Note:** Using _min-width_ to set the width of the iFrame, works around an issue in iOS that can prevent the iFrame from sizing correctly.

If you have problems, check the [troubleshooting](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/troubleshooting.md) section.

### Example

To see this working take a look at this [example](https://davidjbradshaw.github.io/iframe-resizer/example/) and watch the [console](https://developer.mozilla.org/en-US/docs/Tools/Web_Console).

## API Documentation

IFrame-Resizer provides an extensive range of options and APIs for both the parent page and the iframed page.

- **Parent Page API**
  - [Options](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/parent_page/options.md)
  - [Events](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/parent_page/events.md)
  - [Methods](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/parent_page/methods.md)
- **IFramed Page API**
  - [Options](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/iframed_page/options.md)
  - [Events](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/iframed_page/events.md)
  - [Methods](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/iframed_page/methods.md)
- **Use with Libraries and Frameworks**
  - [React](https://github.com/davidjbradshaw/iframe-resizer-react)
  - [Vue](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/use_with/vue.md)
  - [Nuxt](https://github.com/davidjbradshaw/iframe-resizer/issues/831#issuecomment-665760332)
  - [Angular](https://github.com/davidjbradshaw/iframe-resizer/issues/478#issuecomment-347958630)
  - [Ember](https://github.com/alexlafroscia/ember-iframe-resizer-modifier)
  - [jQuery](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/use_with/jquery.md)
  - [Google Apps Script](https://stackoverflow.com/a/65724113/2087070)
- [Troubleshooting](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/troubleshooting.md)
- [Upgrade from version 3](https://github.com/davidjbradshaw/iframe-resizer/blob/master/docs/upgrade.md)
- [Version history](https://github.com/davidjbradshaw/iframe-resizer/blob/master/CHANGELOG.md)

## License

Copyright &copy; 2013-23 [David J. Bradshaw](https://github.com/davidjbradshaw) -
Licensed under the [MIT License](LICENSE)

<!--
[![NPM](https://nodei.co/npm/iframe-resizer.png?downloads=true&downloadRank=true&stars=true)](https://nodei.co/npm/iframe-resizer/)

[![Build Status](https://travis-ci.org/davidjbradshaw/iframe-resizer.svg?branch=master)](https://travis-ci.org/davidjbradshaw/iframe-resizer)
[![Known Vulnerabilities](https://snyk.io/test/github/davidjbradshaw/iframe-resizer/badge.svg)](https://snyk.io/test/github/davidjbradshaw/iframe-resizer)
-->
