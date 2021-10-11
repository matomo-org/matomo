import AjaxHelper from './AjaxHelper';

declare global {
  interface Window {
    ajaxHelper: AjaxHelper;
  }
}

window.ajaxHelper = AjaxHelper;

function ajaxQueue() {
  return globalAjaxQueue;
}

angular.module('piwikApp.service').service('globalAjaxQueue', ajaxQueue);
