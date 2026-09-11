/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IPluginShopVariation, PluginDetails } from '../types';

export const PERIOD_ANNUAL = 'year';
export const PERIOD_MONTHLY = 'month';

export type ShopPeriod = typeof PERIOD_ANNUAL | typeof PERIOD_MONTHLY;

const MONTHS_PER_YEAR = 12;

/**
 * A shop variation is one purchasable combination of tier, currency and billing period.
 * Periods are reported as either an annual or a monthly wording, so both spellings are
 * accepted; anything else is a period we cannot price and is dropped.
 */
export function toShopPeriod(period: string): ShopPeriod | null {
  const normalised = String(period || '').toLowerCase();

  if (normalised.startsWith('month')) {
    return PERIOD_MONTHLY;
  }

  if (normalised.startsWith('year') || normalised.startsWith('annual')) {
    return PERIOD_ANNUAL;
  }

  return null;
}

/**
 * The amount a variation costs, or null when the shop did not give a usable one.
 *
 * price is typed string | number | null, and null, undefined and '' all pass through Number()
 * as 0 rather than failing — which would advertise a paid product as free while still linking
 * to the cart. Anything that is not a finite, non-negative number is treated as absent.
 */
export function variationPrice(variation?: IPluginShopVariation): number | null {
  const raw = variation?.price;

  if (raw === null || raw === undefined || raw === '') {
    return null;
  }

  const price = Number(raw);

  return Number.isFinite(price) && price >= 0 ? price : null;
}

/**
 * The shop variations that can actually be offered: a known billing period, a currency to
 * price them in, a price to show and somewhere to buy them. Order is kept, the marketplace
 * lists the variation it considers the default first.
 */
export function usableVariations(plugin: PluginDetails): IPluginShopVariation[] {
  const variations: IPluginShopVariation[] = plugin?.shop?.variations || [];

  return variations.filter((variation) => (
    !!toShopPeriod(variation.period)
    && !!variation.currency
    && !!variation.addToCartUrl
    && variationPrice(variation) !== null
  ));
}

/**
 * Whether there is enough shop information to show a price for this plugin.
 */
export function hasShopPricing(plugin: PluginDetails): boolean {
  return usableVariations(plugin).length > 0;
}

// Only matches the English wording the shop uses today; a localised variation name would
// fall through and keep its period suffix, splitting the tier again.
const PERIOD_WORDING = /[\s/-]+(per\s+)?(month|months|monthly|year|years|yearly|annual|annually)$/i;

/**
 * The key a variation is grouped under in the tier picker.
 *
 * When the billing period gets its own control, the shop's period wording has to come out of
 * the name first — "Up to 20 users" and "Up to 20 users monthly" are one tier billed two ways,
 * and the period field already says which is which. Without that control the wording is the
 * only thing telling the two apart, so it stays part of the tier.
 */
export function tierKey(variation: IPluginShopVariation, stripPeriodWording: boolean): string {
  const name = String(variation.name || '');

  return (stripPeriodWording ? name.replace(PERIOD_WORDING, '') : name).trim();
}

/**
 * Distinct tiers on offer, in the order the marketplace sent them.
 */
export function distinctTiers(
  variations: IPluginShopVariation[],
  stripPeriodWording: boolean,
): string[] {
  return variations
    .map((variation) => tierKey(variation, stripPeriodWording))
    .filter((value, index, all) => !!value && all.indexOf(value) === index);
}

/**
 * Distinct currencies on offer, in the order the marketplace sent them.
 */
export function distinctCurrencies(variations: IPluginShopVariation[]): string[] {
  return variations
    .map((variation) => variation.currency)
    .filter((value, index, all) => !!value && all.indexOf(value) === index);
}

/**
 * Price of a variation expressed per month. An annual variation is billed in one go, so
 * its price is spread over the twelve months it covers.
 */
export function monthlyAmount(variation: IPluginShopVariation, period: ShopPeriod): number {
  const price = variationPrice(variation);

  if (price === null) {
    return 0;
  }

  return period === PERIOD_ANNUAL ? price / MONTHS_PER_YEAR : price;
}

/**
 * How much is saved over a year by paying annually instead of monthly. Returns 0 when
 * there is nothing to compare against or nothing to save.
 */
export function annualSavings(
  annual?: IPluginShopVariation,
  monthly?: IPluginShopVariation,
): number {
  const annualPrice = variationPrice(annual);
  const monthlyPrice = variationPrice(monthly);

  if (annualPrice === null || monthlyPrice === null) {
    return 0;
  }

  return Math.max(0, (monthlyPrice * MONTHS_PER_YEAR) - annualPrice);
}

/**
 * How many months of the annual plan are effectively free compared to paying monthly.
 */
export function freeMonths(
  annual?: IPluginShopVariation,
  monthly?: IPluginShopVariation,
): number {
  const monthlyPrice = variationPrice(monthly);
  const savings = annualSavings(annual, monthly);

  if (monthlyPrice === null || monthlyPrice <= 0 || savings <= 0) {
    return 0;
  }

  return Math.floor(savings / monthlyPrice);
}
