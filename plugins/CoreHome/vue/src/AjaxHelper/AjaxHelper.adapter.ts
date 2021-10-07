import AjaxHelper from './AjaxHelper';

declare global {
  interface Window {
    ajaxHelper: AjaxHelper;
  }
}

window.ajaxHelper = AjaxHelper;
