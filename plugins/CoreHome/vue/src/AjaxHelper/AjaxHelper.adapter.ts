import AjaxHelper from './AjaxHelper';

declare global {
  interface Window {
    ajaxHelper: typeof AjaxHelper;
  }
}

window.ajaxHelper = AjaxHelper;
