import NumberFormatter from './NumberFormatter';

declare global {
  interface Window {
    NumberFormatter: typeof NumberFormatter;
  }
}

window.NumberFormatter = NumberFormatter;
