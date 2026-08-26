<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="shopPricing" v-if="selectedVariation">
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
          tabindex="7"
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
          tabindex="7"
          :name="periodGroupName"
          :checked="selectedPeriod === PERIOD_MONTHLY"
          @change="selectPeriod(PERIOD_MONTHLY)"
        />
        <span class="shopPricing__periodText">{{ translate('Marketplace_PayMonthly') }}</span>
      </label>
    </div>

    <select
      class="shopPricing__tier"
      tabindex="7"
      v-if="tiers.length > 1"
      :aria-label="translate('Marketplace_NumberOfUsers')"
      :value="selectedTier"
      @change="selectTier($event)"
    >
      <option v-for="tier in tiers" :key="tier" :value="tier">{{ tier }}</option>
    </select>

    <select
      class="shopPricing__currency"
      tabindex="7"
      v-if="currencies.length > 1"
      :aria-label="translate('SitesManager_Currency')"
      :value="selectedCurrency"
      @change="selectCurrency($event)"
    >
      <option v-for="currency in currencies" :key="currency" :value="currency">
        {{ currency }}
      </option>
    </select>

    <div class="shopPricing__price" :title="priceTitle">
      <div class="shopPricing__amount">
        <span class="shopPricing__leadIn" v-if="showFreeTrialLeadIn">{{
          translate('Marketplace_TryFreeTrialTitle')
        }}</span>
        <span class="shopPricing__amountValue">{{ prettyAmount }}</span>
        <span class="shopPricing__amountPeriod">{{ amountPeriod }}</span>
      </div>
      <div
        class="shopPricing__billing"
        v-if="billingNote"
        v-html="$sanitize(billingNote)"
      />
    </div>

    <div class="shopPricing__cta">
      <a
        class="btn addToCartLink"
        target="_blank"
        tabindex="7"
        rel="noreferrer noopener"
        :title="translate('Marketplace_ClickToCompletePurchase')"
        :href="selectedVariation.addToCartUrl"
      >{{ translate('Marketplace_AddToCart') }}</a>
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
 * them onto amounts that are already whole.
 */
function formatAmount(amount: number): string {
  return NumberFormatter.formatNumber(amount, 2, 0);
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
    showFreeTrialLeadIn: {
      type: Boolean,
      default: false,
    },
    // only bundles sold without a free trial offer a choice of billing period; everywhere else
    // the period is part of the tier and must not be lifted into its own control
    usePeriodTabs: {
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
        : Number(this.selectedVariation.price));
    },
    amountPeriod(): string {
      const perMonth = this.hasBothPeriods || this.selectedPeriod === PERIOD_MONTHLY;

      return translate(
        perMonth ? 'Marketplace_PerMonthWithCurrency' : 'Marketplace_PerYearWithCurrency',
        this.selectedCurrency,
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
    billingNote(): string {
      if (!this.hasBothPeriods || this.selectedPeriod !== PERIOD_ANNUAL) {
        return '';
      }

      const savings = annualSavings(this.annualVariation, this.monthlyVariation);
      // formatted here rather than taken from the shop's prettyPrice, which puts the
      // currency in front of an unseparated amount and would not match the price above
      const total = `${formatAmount(Number(this.annualVariation?.price))} `
        + `${this.selectedCurrency}`;

      if (savings <= 0) {
        return translate('Marketplace_BilledAnnually', `<strong>${total}</strong>`);
      }

      return translate(
        'Marketplace_BilledAnnuallyWithSavings',
        `<strong>${total}</strong>`,
        `<strong>${formatAmount(savings)} ${this.selectedCurrency}</strong>`,
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
