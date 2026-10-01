import AjaxHelper from './AjaxHelper';
import dropMalformedQueryParameters from '../MatomoUrl/queryParameterNames';

declare global {
  interface Window {
    ajaxHelper: typeof AjaxHelper;
  }
}

window.ajaxHelper = AjaxHelper;

// Apply the shared parameter-name validation to query strings produced by jQuery.
(function guardSerializedParameterNames(jq: JQueryStatic): void {
  const originalParam = jq.param;

  function param(this: unknown, ...args: unknown[]): string {
    return dropMalformedQueryParameters(
      (originalParam as (...a: unknown[]) => string).apply(this, args),
    );
  }

  jq.param = param as unknown as JQueryStatic['param'];
}(window.$));
