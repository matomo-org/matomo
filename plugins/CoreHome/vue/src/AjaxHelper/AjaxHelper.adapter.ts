import AjaxHelper from './AjaxHelper';

declare global {
  interface Window {
    ajaxHelper: typeof AjaxHelper;
  }
}

window.ajaxHelper = AjaxHelper;

function ajaxQueue() {
  return window.globalAjaxQueue;
}

window.angular.module('piwikApp.service').service('globalAjaxQueue', ajaxQueue);
