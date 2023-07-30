(function () {
  const QUERY_PARAM_NAME = 'tracker_install_check';

  var utils = {
    getQueryParameter: function (url, parameter) {
      var regexp = new RegExp('[?&]' + parameter + '(=([^&#]*)|&|#|$)');
      var matches = regexp.exec(url);

      if (!matches) {
        return null;
      }

      if (!matches[2]) {
        return '';
      }

      var value = matches[2].replace(/\+/g, " ");

      return decodeURIComponent(value);
    }
  };

  function init() {
    Matomo.addPlugin('JsTrackerInstallCheck', {
      log: function () {
        const installTestParam = utils.getQueryParameter(window.location.href, QUERY_PARAM_NAME);
        if (installTestParam || installTestParam === '') {
          return '&tracker_install_check=' + installTestParam;
        }

        return '';
      }
    });

    Matomo.on('TrackerSetup', function (tracker) {
      tracker.JsTrackerInstallCheck = {
        doSomething: function () {
          // TODO - Do something
        }
      };

      // Set a timeout to poll for a response
      setTimeout(function () {
        const installTestParam = utils.getQueryParameter(window.location.href, QUERY_PARAM_NAME);
        if (!installTestParam) {
          return;
        }

        // Close the window after a few seconds since things should have loaded and sent a request
        window.close();
      }, 5000);


    });
  }

  if ('object' === typeof window.Matomo) {
    init();
  } else {
    // tracker might not be loaded yet
    if ('object' !== typeof window.matomoPluginAsyncInit) {
      window.matomoPluginAsyncInit = [];
    }

    window.matomoPluginAsyncInit.push(init);
  }

})();
