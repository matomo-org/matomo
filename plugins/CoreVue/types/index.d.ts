declare global {
  interface PiwikGlobal {
    timezoneOffset: number;
    addCustomPeriod: <T>(name: string, periodClass: T) => void;
  }

  let piwik: PiwikGlobal;

  function _pk_translate(translationStringId: string, values: string[]): string;
}
