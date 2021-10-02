declare global {
  interface PiwikGlobal {
    timezoneOffset: number;
    addCustomPeriod: <T>(name: string, periodClass: T) => void;
  }

  let piwik: PiwikGlobal;
}
