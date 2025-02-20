/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function _pk_translate(translationStringId, values) {
    if (typeof(piwik_translations) !== 'undefined'
        && typeof(piwik_translations[translationStringId]) != 'undefined'
    ) {
        var translation = piwik_translations[translationStringId];
        if (typeof values != 'undefined' && values && values.length) {
            values.unshift(translation);
            return sprintf.apply(null, values);
        } else {
            translation = translation.replaceAll('%%', '%');
        }

        return translation;
    }

    return "The string "+translationStringId+" was not loaded in javascript. Make sure it is added in the Translate.getClientSideTranslationKeys hook.";
}

function _pk_externalRawLink(url, values) {

  if (!url) {
    return '';
  }

  const campaignOverride = (typeof values != 'undefined' && values.length > 0 && values[0] ? values[0] : null);
  const sourceOverride = (typeof values != 'undefined' && values.length > 1 && values[1] ? values[1] : null);
  const mediumOverride = (typeof values != 'undefined' && values.length > 2 && values[2] ? values[2] : null);

  let returnURL = null;
  try {
      returnURL = new URL(url);
  } catch(error) {
      console.log('Error parsing URL: ' + url);
  }
  if (!returnURL) {
    return '';
  }

  const validDomain = returnURL.host === 'matomo.org' || returnURL.host.endsWith('.matomo.org');
  const urlParams = new URLSearchParams(window.location.search);
  const module = urlParams.get('module');
  const action = urlParams.get('action');

  // Apply campaign parameters if domain is ok, config is not disabled and a value for medium exists
  if (validDomain && !window.piwik.disableTrackingMatomoAppLinks
    && ((module && action) || mediumOverride)) {
    const campaign = (campaignOverride === null ? 'Matomo_App' : campaignOverride);
    let source = (window.Cloud === undefined ? 'Matomo_App_OnPremise' : 'Matomo_App_Cloud');
    if (sourceOverride !== null) {
      source = sourceOverride;
    }

    const medium = (mediumOverride === null ? 'App.' + module + '.' + action : mediumOverride);

    returnURL.searchParams.set('mtm_campaign', campaign);
    returnURL.searchParams.set('mtm_source', source);
    returnURL.searchParams.set('mtm_medium', medium);
  }

  return returnURL.toString();
}


window.piwikHelper = {

    htmlDecode: function(value)
    {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = value;
        return textArea.value;
    },

    sendContentAsDownload: function (filename, content, mimeType) {
        if (!mimeType) {
            mimeType = 'text/plain';
        }
        function downloadFile(content)
        {
            var node = document.createElement('a');
            node.style.display = 'none';
            if ('string' === typeof content) {
                node.setAttribute('href', 'data:' + mimeType + ';charset=utf-8,' + encodeURIComponent(content));
            } else {
                node.href = window.URL.createObjectURL(blob);
            }
            node.setAttribute('download', filename);
            document.body.appendChild(node);
            node.click();
            document.body.removeChild(node);
        }

        var node;
        if ('function' === typeof Blob) {
            // browser supports blob
            try {
                var blob = new Blob([content], {type: mimeType});
                if (window.navigator.msSaveOrOpenBlob) {
                    window.navigator.msSaveBlob(blob, filename);
                    return;
                } else {
                    downloadFile(blob);
                    return;
                }
            } catch (e) {
                downloadFile(content);
            }
        }
        downloadFile(content);
    },

    /**
     * a nice cross-browser logging function
     */
    log: function() {
        try {
            console.log.apply(console, arguments); // Firefox, Chrome
        } catch (e) {
            try {
                opera.postError.apply(opera, arguments);  // Opera
            } catch (f) {
                // don't alert as log is not considered to be important enough
                // (as opposed to piwikHelper.error)
                //alert(Array.prototype.join.call(arguments, ' ')); // MSIE
            }
        }
    },

    error: function() {
        try {
            console.error.apply(console, arguments); // Firefox, Chrome
        } catch (e) {
            try {
                opera.postError.apply(opera, arguments);  // Opera
            } catch (f) {
                alert(Array.prototype.join.call(arguments, ' ')); // MSIE
            }
        }
    },

    htmlEntities: function(value)
    {
        if (!value) {
            return value;
        }
        var findReplace = [[/&/g, "&amp;"], [/</g, "&lt;"], [/>/g, "&gt;"], [/"/g, "&quot;"], [/{{/g, '{&#8291;{']];
        for(var item in findReplace) {
            value = value.replace(findReplace[item][0], findReplace[item][1]);
        }
        return value;
    },

    /**
     * @deprecated use window.vueSanitize instead
     */
    escape: function (value)
    {
        return window.vueSanitize(value);
    },

	/**
	 * Add break points to a string so that it can be displayed more compactly
	 */
	addBreakpoints: function(text, breakpointMarkup)
	{
		return text.replace(/([\/&=?\.%#:_-])/g, '$1' +
			(typeof breakpointMarkup == 'undefined' ? '<wbr>&#8203;' : breakpointMarkup));
			 // &#8203; is for internet explorer
	},

	/**
	 * Add breakpoints to a URL
	 * urldecodes and encodes htmlentities to display utf8 urls without XSS vulnerabilities
	 */
	addBreakpointsToUrl: function(url)
	{
		try {
			url = decodeURIComponent(url);
		} catch (e) {
			// might throw "URI malformed"
		}
		url = piwikHelper.addBreakpoints(url, '|||');
		url = $(document.createElement('p')).text(url).html();
		url = url.replace(/\|\|\|/g, '<wbr />&#8203;'); // &#8203; is for internet explorer
		return url;
	},

    // initial call for 'body' later in this file
    compileVueEntryComponents: function (selector, extraProps) {
      function toCamelCase(arg) {
        return arg[0] + arg.substring(1)
          .replace(/-[a-z]/g, function (s) { return s[1].toUpperCase(); });
      }

      function toKebabCase(arg) {
        return arg[0].toLowerCase() + arg.substring(1)
          .replace(/[A-Z]/g, function (s) { return '-' + s[0].toLowerCase(); });
      }

      // process vue-entry attributes
      $('[vue-entry]', selector).add($(selector).filter('[vue-entry]')).each(function () {
        if ($(this).closest('[vue-entry-ignore]').length) {
          return;
        }

        var entry = $(this).attr('vue-entry');
        var componentsToRegister = ($(this).attr('vue-components') || '').split(/\s+/).filter(function (s) {
          return !!s.length;
        });

        var parts = entry.split('.');
        if (parts.length !== 2) {
          throw new Error('Expects vue-entry to have format Plugin.Component, where Component is exported Vue component. Got: ' + entry);
        }

        var useExternalPluginComponent = CoreHome.useExternalPluginComponent;
        var createVueApp = CoreHome.createVueApp;
        var component;

        var shouldLoadOnDemand = (piwik.pluginsToLoadOnDemand || []).indexOf(parts[0]) !== -1;
        if (!shouldLoadOnDemand) {
          var plugin = window[parts[0]];
          if (!plugin) {
            // plugin may not be activated
            return;
          }

          component = plugin[parts[1]];
          if (!component) {
            throw new Error('Unknown component in vue-entry: ' + entry);
          }
        } else {
          component = useExternalPluginComponent(parts[0], parts[1]);
        }

        var paramsStr = '';
        var componentParams = {};

        function handleProperty(name, value) {
          if (name === 'vue-entry' || name === 'class' || name === 'style' || name === 'id') {
            return;
          }

          // append '_' to avoid accidentally using javascript keywords
          var camelName = toCamelCase(name) + '_';
          paramsStr += ':' + name + '=' + JSON.stringify(camelName) + ' ';

          try {
            value = JSON.parse(value);
          } catch (e) {
            // pass
          }

          componentParams[camelName] = value;
        }

        $.each(this.attributes, function () {
          handleProperty(this.name, this.value);
        });
        Object.entries(extraProps || {}).forEach(([name, value]) => {
          handleProperty(name, value);
        });

        var element = this;

        // NOTE: we could just do createVueApp(component, componentParams), but Vue will not allow
        // slots to be in the vue-entry element this way. So instead, we create a quick
        // template that references the root component and wraps the vue-entry component's html.
        // this allows using slots in twig.
        var app = createVueApp({
          template: '<root ' + paramsStr + '>' + this.innerHTML + '</root>',
          data: function () {
            return componentParams;
          },
        });
        app.component('root', component);

        componentsToRegister.forEach(function (componentRef) {
          var parts = componentRef.split('.');
          var pluginName = parts[0];
          var componentName = parts[1];

          var component = useExternalPluginComponent(pluginName, componentName);

          // the component is made available via kebab case, since casing is lost in HTML,
          // and tag names will appear all lower case when vue processes them
          app.component(toKebabCase(componentName), component);
        });

        var appInstance = app.mount(this);
        $(this).data('vueAppInstance', appInstance);

        var self = this;
        this.addEventListener('matomoVueDestroy', function () {
          $(self).data('vueAppInstance', null);
          app.unmount();
        });
      });

      // process vue-directive attributes (only uses .mounted/.unmounted hooks)
      piwikHelper.compileVueDirectives(selector);

      if (window.Vue) {
        window.Vue.nextTick(function () {
          piwikHelper.processDynamicHtml($(selector).parent());
        });
      }
    },

    compileVueDirectives: function (selector) {
      $('[vue-directive]', selector).add($(selector).filter('[vue-directive]')).each(function () {
        var vueDirectiveName = $(this).attr('vue-directive');
        if (!vueDirectiveName) {
          return;
        }

        var parts = vueDirectiveName.split('.');
        if (parts.length !== 2) {
          throw new Error('Expects vue-entry to have format Plugin.Component, where Component is exported Vue component. Got: ' + vueDirectiveName);
        }

        var plugin = window[parts[0]];
        if (!plugin) {
          throw new Error('Unknown plugin in vue-entry: ' + vueDirectiveName);
        }

        var directive = plugin[parts[1]];
        if (!directive) {
          throw new Error('Unknown component in vue-entry: ' + vueDirectiveName);
        }

        var directiveArgument = $(this).attr('vue-directive-value');

        var value;
        try {
          value = JSON.parse(directiveArgument || '{}');
        } catch (e) {
          console.log('failed to parse directive value ' + value + ': ' + directiveArgument);
          return;
        }

        var binding = { value: value };

        if (directive.mounted) {
          directive.mounted(this, binding);
        }

        this.addEventListener('matomoVueDestroy', function () {
          if (directive.unmounted) {
            directive.unmounted(this, binding);
          }
        });
      });
    },

    destroyVueComponent: function (selector) {
      $('[vue-entry]', selector).each(function () {
        this.dispatchEvent(new CustomEvent('matomoVueDestroy'));
      });
    },

    processDynamicHtml: function ($element) {
        piwik.postEvent('Matomo.processDynamicHtml', $element);
    },

    /**
     * Detects whether the current page is a reporting page or not.
     *
     * @returns {number}
     */
    isReportingPage: function ()
    {
        return $('.reporting-page').length;
    },

    /**
     * Moves an element further to the left by changing the left margin to make sure as much as possible of an element
     * is visible in the current viewport. The top position keeps unchanged.
     * @param elementToPosition
     */
    setMarginLeftToBeInViewport: function (elementToPosition) {
        var availableWidth = $(window).width();
        $(elementToPosition).css('marginLeft', '0px');
        var offset = $(elementToPosition).offset();
        if (!offset) {
            return;
        }
        var leftPos = offset.left;
        if (leftPos < 0) {
            leftPos = 0;
        }
        var widthSegmentForm = $(elementToPosition).outerWidth();
        if (leftPos + widthSegmentForm > availableWidth) {
            var extraSpaceForMoreBeauty = 16;
            var newLeft = availableWidth - widthSegmentForm - extraSpaceForMoreBeauty;
            if (newLeft < extraSpaceForMoreBeauty) {
                newLeft = extraSpaceForMoreBeauty;
            }
            var marginLeft = Math.abs(leftPos - newLeft);
            if (marginLeft > extraSpaceForMoreBeauty) {
                // we only move it further to the left if it is actually more than 16px to the left.
                // otherwise it is not really worth it and doesn't look as good.
                $(elementToPosition).css('marginLeft', (parseInt(marginLeft, 10) * -1) + 'px');
            }
        }
    },

    /**
     * Displays a Modal dialog. Text will be taken from the DOM node domSelector.
     * Given callback handles will be mapped to the buttons having a role attribute
     *
     * Dialog will be closed when a button is clicked and callback handle will be
     * called, if one was given for the clicked role
     *
     * @param {string} domSelector   domSelector for modal window
     * @param {object} handles       callback functions for available roles
     * @param {object} options       options for modal
     * @return {void}
     */
    modalConfirm: function(domSelector, handles, options)
    {
        if (!options) {
            options = {};
        }

        var domElem = $(domSelector);
        var buttons = [];

        var content = '<div class="modal"><div class="modal-content"></div>';
        content += '<div class="modal-footer"></div></div>';

        var $content = $(content).hide();
        var $footer = $content.find('.modal-footer');

        $('[role]', domElem).not('li').each(function(){
            var $button = $(this);

            // skip this button if it's part of another modal, the current modal can launch
            // (which is true if there are more than one parent elements contained in domElem,
            // w/ css class ui-confirm)
            var uiConfirm = $button.parents('.ui-confirm,[ui-confirm]').filter(function () {
              return domElem[0] === this || $.contains(domElem[0], this);
            });
            if (uiConfirm.length > 1) {
              return;
            }

            var role  = $button.attr('role');
            var title = $button.attr('title');
            var text  = $button.val();
            $button.hide();

            var button = $('<a href="javascript:;" class="modal-action modal-close waves-effect waves-light btn-flat "></a>');

            if(role === 'validation'){
                button = $('<a href="javascript:;" class="modal-action waves-effect waves-light btn"></a>');
            }

            button.text(text);
            if (title) {
                button.attr('title', title);
            }

            if (typeof handles !== 'undefined' && typeof handles[role] == 'function') {
                button.on('click', function(){
                    handles[role].apply()
                });
            }
            if (typeof $button.data('href') !== 'undefined') {
                button.on('click', function () {
                    window.location.href = $button.data('href');
                })
            }

            $footer.append(button);
        });

        $('body').append($content);
        $content.find('.modal-content').append(domElem);

        if (options && options.fixedFooter) {
            $content.addClass('modal-fixed-footer');
            delete options.fixedFooter;
        }

        if (options && options.extraWide) {
            // if given, the modal will be shown larger than usual and almost consume the full width.
            $content.addClass('modal-extra-wide');
            delete options.extraWide;
        }

        if (options && !options.onOpenEnd) {
            options.onOpenEnd = function () {
                if (options.focusSelector) {
                    $(options.focusSelector).focus();
                } else {
                    $(".modal.open a").focus();
                }
                var modalContent = $(".modal.open");
                if (modalContent && modalContent[0]) {
                    // make sure to scroll to the top of the content
                    modalContent[0].scrollTop = 0;
                }
            };
        }

        domElem.show();
        $content.modal(options).modal('open');
    },

    getQueryStringWithParametersModified: function (queryString, newParameters) {
        if (queryString != '') {
            var r, i, keyvalue, keysvalues = newParameters.split('&');
            var appendUrl = '';
            for (i = 0; i < keysvalues.length; i++) {
                keyvalue = keysvalues[i].split('=');
                r = new RegExp('(^|[?&])' + keyvalue[0] + '=[^&]*');
                queryString = queryString.replace(r, '');

                // empty value, eg. &segment=, we remove the parameter from URL entirely
                if (keyvalue[1].length == 0) {
                    continue;
                }
                appendUrl += '&' + keyvalue[0] + '=' + keyvalue[1];
            }
            queryString += appendUrl;
            if (queryString[0] == '&') {
                queryString = '?' + queryString.substring(1);
            }
        } else {
            queryString = '?' + newParameters;
        }

        return queryString;
    },

    /**
     * Returns the current query string with the given parameters modified
     * @param {String} newparams parameters to be modified
     * @return {String}
     */
    getCurrentQueryStringWithParametersModified: function(newparams)
    {
        var queryString = String(window.location.search);
        if (newparams) {
            queryString = this.getQueryStringWithParametersModified(queryString, newparams);
        }
        return String(window.location.pathname) + queryString;
    },

  /**
   * Given param1=v1&param2=k2
   * returns: { "param1": "v1", "param2": "v2" }
   *
   * @param query string
   * @return {Object}
   */
    getArrayFromQueryString: function (query) {
      var params = {};
      var vars = query.split("&");
      for (var i=0;i<vars.length;i++) {
        var keyValue = vars[i].split("=");
        // Jquery will urlencode these, but we wish to keep the current raw value
        // use case: &segment=visitorId%3D%3Dabc...
        params[keyValue[0]] = decodeURIComponent(keyValue[1]);
      }
      return params;
    },

    /**
     *  Returns query string for an object of key,values
     *  Note: we don't use $.param from jquery as it doesn't return array values the PHP way (returns a=v1&a=v2 instead of a[]=v1&a[]=v2)
     *  Example:
     *      piwikHelper.getQueryStringFromParameters({"a":"va","b":["vb","vc"],"c":1})
     *  Returns:
     *      a=va&b[]=vb&b[]=vc&c=1
     *  @param {object} parameters
     *  @return {string}
     */
    getQueryStringFromParameters: function(parameters)
    {
        var queryString = '';
        if(!parameters || parameters.length==0) {
            return queryString;
        }
        for(var name in parameters) {
            var value = parameters[name];
            if(typeof value == 'object') {
                for(var i in value) {
                    queryString += name + '[]=' + value[i] + '&';
                }
            } else {
                queryString += name + '=' + value + '&';
            }
        }
        return queryString.substring(0, queryString.length-1);
    },

    /**
     * Displays the given ajax error message within the given id element
     * @param {string} message       error message
     * @param {string} errorDivID    id of the domNode (defaults to ajaxError)
     * @return {void}
     */
    showAjaxError: function( message, errorDivID )
    {
        errorDivID = errorDivID || 'ajaxError';
        $('#'+errorDivID).html(message).show();
    },

    /**
     * Hides the error message with the given id
     * @param {string} [errorDivID]   id of the domNode (defaults to ajaxError)
     * @return {void}
     */
    hideAjaxError: function(errorDivID)
    {
        errorDivID = errorDivID || 'ajaxError';
        $('#'+errorDivID).hide();
    },

    /**
     * Shows the loading message with the given Id
     * @param {string} [loadingDivID]   id of the domNode (defaults to ajaxLoading)
     * @return {void}
     */
    showAjaxLoading: function(loadingDivID)
    {
        loadingDivID = loadingDivID || 'ajaxLoadingDiv';
        $('#'+loadingDivID).show();
    },

    /**
     * Hides the loading message with the given id
     * @param {string} [loadingDivID]   id of the domNode (defaults to ajaxLoading)
     * @return {void}
     */
    hideAjaxLoading: function(loadingDivID)
    {
        loadingDivID = loadingDivID || 'ajaxLoadingDiv';
        $('#'+loadingDivID).hide();
    },

    /**
     * Reloads the page after the given period
     * @param {int} timeoutPeriod
     * @return void
     */
    refreshAfter: function(timeoutPeriod)
    {
        if(timeoutPeriod == 0) {
            location.reload();
        } else {
            setTimeout("location.reload();",timeoutPeriod);
        }
    },

    redirect: function (params) {
        // add updated=X to the URL so that a "Your changes have been saved" message is displayed
        if (typeof params == 'object') {
            params = this.getQueryStringFromParameters(params);
        }
        var urlToRedirect = this.getCurrentQueryStringWithParametersModified(params);
        var updatedUrl = new RegExp('&updated=([0-9]+)');
        var updatedCounter = updatedUrl.exec(urlToRedirect);
        if (!updatedCounter) {
            urlToRedirect += '&updated=1';
        } else {
            updatedCounter = 1 + parseInt(updatedCounter[1]);
            urlToRedirect = urlToRedirect.replace(new RegExp('(&updated=[0-9]+)'), '&updated=' + updatedCounter);
        }
        var currentHashStr = window.location.hash;
        if(currentHashStr.length > 0) {
            urlToRedirect += currentHashStr;
        }
        this.redirectToUrl(urlToRedirect);
    },

    /**
     * Redirect to the given url
     * @param {string} url
     */
    redirectToUrl: function(url)
    {
        window.location = url;
    },

    lazyScrollToContent: function () {
        this.lazyScrollTo('#content', 250);
    },

    /**
     * Scrolls the window to the jquery element 'elem'
     * if the top of the element is not currently visible on screen
     * @param {string} elem Selector for the DOM node to scroll to, eg. '#myDiv'
     * @param {int} [time] Specifies the duration of the animation in ms
     * @param {boolean} [forceScroll] Whether to force scroll to an element.
     * @return {void}
     */
    lazyScrollTo: function(elem, time, forceScroll)
    {
        var $elem = $(elem);
        if (!$elem.length) {
            return;
        }

        var elemTop = $elem.offset().top;
        // only scroll the page if the graph is not visible
        if (elemTop < $(window).scrollTop()
            || elemTop > $(window).scrollTop()+$(window).height()
            || forceScroll
        ) {
            // scroll the page smoothly to the graph
            $.scrollTo(elem, time);
        }
    },

    /**
     * Returns the filtered/converted content of a textarea to be used for api requests
     * @param {string} textareaContent
     * @return {string}
     */
    getApiFormatTextarea: function (textareaContent) {
        if (typeof textareaContent == 'undefined') {
            return '';
        }
        return textareaContent.trim().split("\n").join(',');
    },

    shortcuts: {},

    /**
     * Register a shortcut
     *
     * @param {string} key key-stroke to be registered for this shortcut
     * @param {string } description  description to be shown in summary
     * @param callback method called when pressing the key
     */
    registerShortcut: function(key, description, callback) {

        piwikHelper.shortcuts[key] = description;

        Mousetrap.bind(key, callback);
    },

    calculateEvolution: function (currentValue, pastValue) {
        var dividend = currentValue - pastValue;
        var divisor = pastValue;

        if (dividend == 0) {
            return 0;
        } else if (divisor == 0) {
            return 1;
        } else {
            return Math.round((dividend / divisor) * 1000) / 1000;
        }
    },

    showVisitorProfilePopup: function (visitorId, idSite) {
      require('piwik/UI').VisitorProfileControl.showPopover(visitorId, idSite);
    },
};
if (typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g,"");
    };
}
/**
 * Returns true if the event keypress passed in parameter is the ENTER key
 * @param {Event} e   current window event
 * @return {boolean}
 */
function isEnterKey(e)
{
    return (window.event?window.event.keyCode:e.which)==13;
}

/**
 * Returns true if the event keypress passed in parameter is the ESCAPE key
 * @param {Event} e   current window event
 * @return {boolean}
 */
function isEscapeKey(e)
{
    return (window.event?window.event.keyCode:e.which)==27;
}

// workarounds
document.addEventListener('DOMContentLoaded', function () {
  (function($){
    try {
      // this code is not vital, so we make sure any errors are ignored

      //--------------------------------------
      //
      // monkey patch that works around bug in arc function of some browsers where
      // nothing gets drawn if angles are 2 * PI apart and in counter-clockwise direction.
      // affects some versions of chrome & IE 8
      //
      //--------------------------------------
      var oldArc = CanvasRenderingContext2D.prototype.arc;
      CanvasRenderingContext2D.prototype.arc = function(x, y, r, sAngle, eAngle, clockwise) {
        if (Math.abs(eAngle - sAngle - Math.PI * 2) < 0.000001 && !clockwise)
          eAngle -= 0.000001;
        oldArc.call(this, x, y, r, sAngle, eAngle, clockwise);
      };

      // Fix jQuery UI dialogs scrolling when click on links with tooltips
      jQuery.ui.dialog.prototype._focusTabbable = $.noop;

      // Fix jQuery UI tooltip displaying when dialog is closed by Esc key
      jQuery(document).keyup(function(e) {
        if (e.keyCode == 27) {
          $('.ui-tooltip').hide();
        }
      });

    } catch (e) {}

    piwikHelper.compileVueEntryComponents('body');

  }(jQuery));
}, false);
