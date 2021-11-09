import { deleteCookie, getCookie, setCookie } from './CookieHelper';

function CookieHelper() {
  return {
    setCookie,
    getCookie,
    deleteCookie,
  };
}

window.angular.module('piwikApp.service').factory('cookieHelper', CookieHelper);
