<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="shopPricing"
    :class="{ 'shopPricing--stacked': stacked, 'shopPricing--prominent': prominent }"
    v-if="selectedVariation"
  >
    <div
      class="shopPricing__periods"
      role="radiogroup"
      v-if="hasBothPeriods"
      :aria-label="translate('Marketplace_BillingPeriod')"
    >
      <label
        class="shopPricing__period"
        :class="{ 'shopPricing__period--selected': selectedPeriod === PERIOD_ANNUAL }"
      >
        <input
          class="shopPricing__periodInput"
          type="radio"
          :name="periodGroupName"
          :checked="selectedPeriod === PERIOD_ANNUAL"
          @change="selectPeriod(PERIOD_ANNUAL)"
        />
        <span class="shopPricing__periodText">{{ translate('Marketplace_PayAnnually') }}</span>
        <span
          class="shopPricing__freeMonths"
          v-if="freeMonthsLabel"
        >{{ freeMonthsLabel }}</span>
      </label>
      <label
        class="shopPricing__period"
        :class="{ 'shopPricing__period--selected': selectedPeriod === PERIOD_MONTHLY }"
      >
        <input
          class="shopPricing__periodInput"
          type="radio"
          :name="periodGroupName"
          :checked="selectedPeriod === PERIOD_MONTHLY"
          @change="selectPeriod(PERIOD_MONTHLY)"
        />
        <span class="shopPricing__periodText">{{ translate('Marketplace_PayMonthly') }}</span>
      </label>
    </div>

    <label class="shopPricing__tierField" v-if="tiers.length > 1">
      <span class="shopPricing__tierLabel">{{ translate('Marketplace_SelectUsers') }}</span>
      <select
        class="shopPricing__tier"
        :value="selectedTier"
        @change="selectTier($event)"
      >
        <option v-for="tier in tiers" :key="tier" :value="tier">{{ tier }}</option>
      </select>
    </label>

    <select
      class="shopPricing__currency"
      v-if="currencies.length > 2"
      :aria-label="translate('SitesManager_Currency')"
      :value="selectedCurrency"
      @change="selectCurrency($event)"
    >
      <option v-for="currency in currencies" :key="currency" :value="currency">
        {{ currency }}
      </option>
    </select>

    <div class="shopPricing__price" :title="priceTitle">
      <div class="shopPricing__amount" v-if="prominent" v-html="$sanitize(amountLabel)" />
      <div class="shopPricing__amount" v-else>
        <span class="shopPricing__amountValue">{{ prettyAmount }}</span>
        <span class="shopPricing__amountPeriod">{{ amountPeriod }}</span>
      </div>
      <div
        class="shopPricing__billing"
        v-if="billingNote"
        v-html="$sanitize(billingNote)"
      />
    </div>

    <ul class="shopPricing__featureList" v-if="prominent">
      <li class="shopPricing__featureItem" v-if="selectedTier">
        <span class="shopPricing__featureCheck" aria-hidden="true">✓</span>
        {{ selectedTier }}
      </li>
      <li class="shopPricing__featureItem">
        <span class="shopPricing__featureCheck" aria-hidden="true">✓</span>
        {{ translate('Marketplace_UnlimitedWebsites') }}
      </li>
    </ul>

    <div class="shopPricing__cta">
      <a
        class="btn shopPricing__addToCart addToCartLink"
        target="_blank"
        rel="noreferrer noopener"
        :title="translate('Marketplace_ClickToCompletePurchase')"
        :href="selectedVariation.addToCartUrl"
      >{{ ctaLabel }}</a>

      <button
        class="shopPricing__currencySwitch"
        type="button"
        v-if="alternativeCurrency"
        @click="currentCurrency = alternativeCurrency"
      >{{ translate('Marketplace_SwitchToCurrency', alternativeCurrency) }}</button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { NumberFormatter, translate } from 'CoreHome';
import { IPluginShopVariation, PluginDetails } from '../types';
import {
  PERIOD_ANNUAL,
  PERIOD_MONTHLY,
  ShopPeriod,
  annualSavings,
  distinctCurrencies,
  distinctTiers,
  freeMonths,
  monthlyAmount,
  tierKey,
  toShopPeriod,
  usableVariations,
  variationPrice,
} from './shopPricing';

let nextPeriodGroupId = 0;

export interface ShopPricingState {
  currentTier: string;
  currentCurrency: string;
  currentPeriod: ShopPeriod | '';
  periodGroupName: string;
}

/**
 * A price normalised to a month rarely divides evenly, so allow decimals without forcing
 * them onto amounts that are already whole. An amount with cents shows both digits, as money
 * does: 43.80 rather than 43.8.
 */
function formatAmount(amount: number): string {
  const rounded = Math.round(amount * 100) / 100;

  return NumberFormatter.formatNumber(rounded, 2, Number.isInteger(rounded) ? 0 : 2);
}

export default defineComponent({
  props: {
    plugin: {
      type: Object as PropType<PluginDetails>,
      required: true,
    },
    numUsers: {
      type: Number,
      required: true,
    },
    // the cart link is also where a trial starts, so it is named for the trial when there is one
    offersFreeTrial: {
      type: Boolean,
      default: false,
    },
    // only bundles sold without a free trial offer a choice of billing period; everywhere else
    // the period is part of the tier and must not be lifted into its own control
    usePeriodTabs: {
      type: Boolean,
      default: false,
    },
    /**
     * Lays the controls out in a column rather than a row.
     *
     * The block's own stacking is keyed off the viewport, which says nothing about the width it
     * was actually given: in the details page's sidebar it is narrow on the widest screen there
     * is. The parent owns that, so it is a prop rather than another media query.
     */
    stacked: {
      type: Boolean,
      default: false,
    },
    /**
     * Styles the stacked panel as the pricing card on plugins.matomo.org: the price leads at a
     * larger size with its currency and period beside it, and a checklist of what the tier
     * includes sits above a full-width cart button.
     */
    prominent: {
      type: Boolean,
      default: false,
    },
  },
  data(): ShopPricingState {
    // radios only group when they share a name, so keep it unique per instance
    nextPeriodGroupId += 1;

    return {
      currentTier: '',
      currentCurrency: '',
      currentPeriod: '',
      periodGroupName: `shopPricingPeriod${nextPeriodGroupId}`,
    };
  },
  computed: {
    PERIOD_ANNUAL(): ShopPeriod {
      return PERIOD_ANNUAL;
    },
    PERIOD_MONTHLY(): ShopPeriod {
      return PERIOD_MONTHLY;
    },
    variations(): IPluginShopVariation[] {
      return usableVariations(this.plugin);
    },
    tiers(): string[] {
      return distinctTiers(this.variations, this.usePeriodTabs);
    },
    selectedTier(): string {
      return this.tiers.includes(this.currentTier) ? this.currentTier : this.tiers[0] || '';
    },
    tierVariations(): IPluginShopVariation[] {
      // a plugin priced as a single offer has no tier name to group by
      return this.tiers.length
        ? this.variations.filter(
          (variation) => tierKey(variation, this.usePeriodTabs) === this.selectedTier,
        )
        : this.variations;
    },
    currencies(): string[] {
      return distinctCurrencies(this.tierVariations);
    },
    selectedCurrency(): string {
      // the marketplace lists its preferred currency first; its cheapest flag marks a price
      // point rather than a currency, so it is not a default to pick up here
      return this.currencies.includes(this.currentCurrency)
        ? this.currentCurrency
        : this.currencies[0] || '';
    },
    /**
     * The other currency when there are exactly two, which is offered as a one-click switch
     * rather than a select. Three or more still need the select to choose between.
     */
    alternativeCurrency(): string {
      if (this.currencies.length !== 2) {
        return '';
      }

      return this.currencies.find((currency) => currency !== this.selectedCurrency) || '';
    },
    currencyVariations(): IPluginShopVariation[] {
      return this.tierVariations.filter(
        (variation) => variation.currency === this.selectedCurrency,
      );
    },
    annualVariation(): IPluginShopVariation | undefined {
      return this.currencyVariations.find(
        (variation) => toShopPeriod(variation.period) === PERIOD_ANNUAL,
      );
    },
    monthlyVariation(): IPluginShopVariation | undefined {
      return this.currencyVariations.find(
        (variation) => toShopPeriod(variation.period) === PERIOD_MONTHLY,
      );
    },
    hasBothPeriods(): boolean {
      return this.usePeriodTabs && !!(this.annualVariation && this.monthlyVariation);
    },
    selectedPeriod(): ShopPeriod {
      if (this.currentPeriod === PERIOD_MONTHLY && this.monthlyVariation) {
        return PERIOD_MONTHLY;
      }

      return this.annualVariation ? PERIOD_ANNUAL : PERIOD_MONTHLY;
    },
    selectedVariation(): IPluginShopVariation | null {
      const variation = this.selectedPeriod === PERIOD_ANNUAL
        ? this.annualVariation
        : this.monthlyVariation;

      return variation || null;
    },
    prettyAmount(): string {
      if (!this.selectedVariation) {
        return '';
      }

      // with both billing periods on offer the two are only comparable per month, on their
      // own a price is clearest over the period it is actually billed for
      return formatAmount(this.hasBothPeriods
        ? monthlyAmount(this.selectedVariation, this.selectedPeriod)
        : variationPrice(this.selectedVariation) ?? 0);
    },
    amountPeriod(): string {
      const perMonth = this.hasBothPeriods || this.selectedPeriod === PERIOD_MONTHLY;

      return translate(
        perMonth ? 'Marketplace_PerMonthWithCurrency' : 'Marketplace_PerYearWithCurrency',
        this.selectedCurrency,
      );
    },
    /**
     * The price with its currency and period, as one sentence so translators can order the three.
     * The amount and currency arrive as spans so the price can outweigh the words around it.
     */
    amountLabel(): string {
      const perMonth = this.hasBothPeriods || this.selectedPeriod === PERIOD_MONTHLY;

      return translate(
        perMonth ? 'Marketplace_PricePerMonth' : 'Marketplace_PricePerYear',
        `<span class="shopPricing__amountValue">${this.prettyAmount}</span>`,
        `<span class="shopPricing__amountCurrency">${this.selectedCurrency}</span>`,
      );
    },
    numFreeMonths(): number {
      return freeMonths(this.annualVariation, this.monthlyVariation);
    },
    freeMonthsLabel(): string {
      if (this.numFreeMonths <= 0) {
        return '';
      }

      return this.numFreeMonths === 1
        ? translate('Marketplace_OneMonthFree')
        : translate('Marketplace_XMonthsFree', this.numFreeMonths);
    },
    ctaLabel(): string {
      return this.offersFreeTrial
        ? translate('Marketplace_StartFree30DayTrial')
        : translate('Marketplace_AddToCart');
    },
    /**
     * How the price on screen is billed, with what paying annually saves. Both periods have a
     * note, so switching between them does not change the panel's height.
     */
    billingNote(): string {
      if (!this.hasBothPeriods) {
        return '';
      }

      const savings = annualSavings(this.annualVariation, this.monthlyVariation);
      const prettySavings = `<strong>${formatAmount(savings)} ${this.selectedCurrency}</strong>`;

      if (this.selectedPeriod === PERIOD_MONTHLY) {
        return savings > 0
          ? translate('Marketplace_BilledMonthlyWithSavings', prettySavings)
          : translate('Marketplace_BilledMonthly');
      }

      // formatted here rather than taken from the shop's prettyPrice, which puts the
      // currency in front of an unseparated amount and would not match the price above
      const total = `${formatAmount(variationPrice(this.annualVariation) ?? 0)} `
        + `${this.selectedCurrency}`;

      if (savings <= 0) {
        return translate('Marketplace_BilledAnnually', `<strong>${total}</strong>`);
      }

      return translate(
        'Marketplace_BilledAnnuallyWithSavings',
        `<strong>${total}</strong>`,
        prettySavings,
      );
    },
    priceTitle(): string {
      return `${translate('Marketplace_ShownPriceIsExclTax')} `
        + `${translate('Marketplace_CurrentNumPiwikUsers', this.numUsers)}`;
    },
  },
  methods: {
    selectTier(event: Event) {
      this.currentTier = (event.target as HTMLSelectElement).value;
    },
    selectCurrency(event: Event) {
      this.currentCurrency = (event.target as HTMLSelectElement).value;
    },
    selectPeriod(period: ShopPeriod) {
      this.currentPeriod = period;
    },
  },
});
</script>
