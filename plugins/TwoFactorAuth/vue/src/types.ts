/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface QRCodeConstructor {
  new (e: HTMLElement, options: unknown): unknown;
}

declare global {
  interface Window {
    QRCode: QRCodeConstructor;
    twoFaBarCodeSetupUrl: string;
  }
}
