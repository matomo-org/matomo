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

/**
 * What the "new plugins" widgets pass to their templates. See GetNewPlugins::keepRenderedFields():
 * only the admin variant is given screenshots.
 */
export interface PluginTeaser {
  name: string;
  displayName: string;
  description: string;
  screenshots?: string[];
}

/**
 * What the premium features widget passes to its template, see
 * GetPremiumFeatures::keepRenderedFields(). Optional fields are absent rather than null.
 */
export interface PluginPromo {
  name: string;
  displayName: string;
  description: string;
  isBundle?: boolean;
  specialOffer?: string;
}

/**
 * A plugin as the plugin list carries it: the fields its cards render, and nothing else.
 *
 * `Controller::keepPluginCardFields()` decides this set. Everything the details modal needs on top
 * of it is fetched for one plugin at a time by `Marketplace.getPluginDetails`, because the version
 * history and its rendered readme HTML made the list response over a megabyte.
 */
export interface PluginCard {
  name: string;
  displayName: string;
  description: string;
  owner: string;
  coverImage: string;
  isFree: boolean;
  isPaid: boolean;
  isInstalled: boolean;
  isActivated: boolean;
  isInvalid: boolean;
  isDownloadable: boolean;
  canBeUpdated: boolean;
  hasDownloadLink: boolean;
  hasExceededLicense: boolean;
  isMissingLicense: boolean;
  isEligibleForFreeTrial: boolean;
  isTrialRequested: boolean;
  canTrialBeRequested: boolean;
  missingRequirements: TObjectArray;
  numDownloads: number | null;
  numDownloadsPretty: TNumberOrString;
  priceFrom: IPluginShopVariation;
  consumer: TObject;
  licenseStatus: string;
  downloadNonce?: string; // only present for a plugin that can be downloaded
  isBundle?: boolean; // only sent for a plugin the Marketplace flags as one
}

/**
 * A card plus the fields only the details modal renders, as returned by
 * `Marketplace.getPluginDetails`. `versions` holds the latest version alone.
 */
export interface PluginDetails extends PluginCard {
  homepage: string | null;
  createdDateTime: string | unknown; // "2017-05-17 06:34:21"
  donate: [];
  support: [];
  isTheme: boolean;
  keywords: string[];
  basePrice: number;
  authors: TObjectArray;
  repositoryUrl: string | null;
  lastUpdated: string;
  latestVersion: string;
  screenshots: string[];
  previews: TObjectArray;
  activity: TObject;
  featured: boolean;
  isCustomPlugin: boolean;
  shop: IPluginShopDetails;
  bundle: TObject; // has nested plugins array
  specialOffer: string;
  versions: TObjectArray;
  changelog: TObject;
  canBePurchased: boolean;
}

declare global {
  interface Window {
    iFrameResize: typeof iframeResizer;
  }
}
