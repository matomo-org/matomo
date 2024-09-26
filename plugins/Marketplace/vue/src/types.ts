/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { iframeResizer } from 'iframe-resizer';

export type TObject = Record<string, unknown> | Record<string, never>;
export type TObjectArray = TObject[] | [] | null;
export type TNumberOrString = string | number | null;

export interface IPluginShopVariation {
  price: TNumberOrString;
  prettyPrice: string;
  currency: string;
  period: string;
  name: string;
  discount: TNumberOrString;
  prettyDiscount: string;
  addToCartUrl: string;
  addToCartEmbedUrl: string;
  cheapest: boolean | undefined;
  recommended: boolean | undefined;
}

export interface IPluginShopReviews {
  embedUrl: string;
  height: number;
  averageRating: TNumberOrString;
  ratingCount: number;
  reviewCount: number;
}

export interface IPluginShopDetails {
  url: string;
  variations: IPluginShopVariation[];
  reviews: IPluginShopReviews;
}

export interface PluginDetails {
  name: string;
  displayName: string;
  owner: string;
  description: string;
  homepage: string | null;
  createdDateTime: string | unknown; // "2017-05-17 06:34:21"
  donate: [];
  support: [];
  isTheme: boolean;
  keywords: string[];
  basePrice: number;
  authors: TObjectArray;
  repositoryUrl: string | null;
  lastUpdated: string,
  latestVersion: string
  numDownloads: number | null;
  screenshots: string[];
  previews: TObjectArray;
  activity: TObject;
  featured: boolean;
  isFree: boolean;
  isPaid: boolean;
  isBundle: boolean;
  isCustomPlugin: boolean;
  shop: IPluginShopDetails;
  bundle: TObject; // has nested plugins array
  specialOffer: string;
  versions: TObjectArray;
  isDownloadable: boolean;
  changelog: TObject;
  consumer: TObject;
  isInstalled: boolean;
  isActivated: boolean;
  isInvalid: boolean;
  canBeUpdated: boolean;
  canBePurchased: boolean;
  hasExceededLicense: boolean;
  isMissingLicense: boolean;
  missingRequirements: TObjectArray;
  isEligibleForFreeTrial: boolean;
  priceFrom: IPluginShopVariation;
  coverImage: string;
  numDownloadsPretty: TNumberOrString;
  hasDownloadLink: boolean;
  licenseStatus: string;
}

declare global {
  interface Window {
    iFrameResize: typeof iframeResizer;
  }
}
