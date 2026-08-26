/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  NumberFormatter: {
    // en-US grouping is enough here; the real formatter is locale driven and covered elsewhere
    formatNumber: (value: number, max: number, min: number) => Number(value).toLocaleString(
      'en-US',
      { maximumFractionDigits: max, minimumFractionDigits: min },
    ),
  },
  translate: (key: string, ...args: unknown[]) => (
    args.length ? `${key}(${args.join(',')})` : key
  ),
}));

import ShopPricing from './ShopPricing.vue';

interface VariationSeed {
  name: string;
  currency: string;
  period: string;
  price: string;
}

function variation(seed: VariationSeed) {
  return {
    ...seed,
    prettyPrice: `${seed.currency}${seed.price}`,
    addToCartUrl: `https://shop.example/${seed.name}/${seed.period}/${seed.currency}`,
  };
}

// mirrors the live EnterpriseBundle payload: the period is carried by `period`, and the
// monthly variation repeats it in its name
const BUNDLE_VARIATIONS = [
  {
    name: 'Up to 50 users monthly', currency: 'EUR', period: 'month', price: '3400',
  },
  {
    name: 'Up to 50 users monthly', currency: 'USD', period: 'month', price: '4080',
  },
  {
    name: 'Up to 50 users', currency: 'EUR', period: 'year', price: '34000',
  },
  {
    name: 'Up to 50 users', currency: 'USD', period: 'year', price: '40800',
  },
];

// mirrors PaidPlugin1: several tiers, one currency pair each, annual only
const TIERED_VARIATIONS = [
  {
    name: 'Up to 4 users', currency: 'EUR', period: 'year', price: '150',
  },
  {
    name: 'Up to 4 users', currency: 'USD', period: 'year', price: '175',
  },
  {
    name: '5 to 15 users', currency: 'EUR', period: 'year', price: '300',
  },
  {
    name: '5 to 15 users', currency: 'USD', period: 'year', price: '345',
  },
];

function mountPricing(seeds: VariationSeed[], props: Record<string, unknown> = {}) {
  return mount(ShopPricing as never, {
    props: {
      plugin: { shop: { variations: seeds.map(variation) } },
      numUsers: 1,
      ...props,
    },
    global: {
      config: {
        globalProperties: {
          translate: (key: string, ...args: unknown[]) => (
            args.length ? `${key}(${args.join(',')})` : key
          ),
          $sanitize: (value: string) => value,
        },
      },
    },
  });
}

describe('Marketplace/ShopPricing.vue', () => {
  describe('a bundle sold without a free trial', () => {
    it('collapses the period wording out of the tier so both periods share one tier', () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      expect(wrapper.vm.tiers).toEqual(['Up to 50 users']);
      expect(wrapper.find('.shopPricing__tier').exists()).toBe(false);
      expect(wrapper.findAll('.shopPricing__period')).toHaveLength(2);
    });

    it('presents the two periods as one radio group, not a pair of toggles', () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });
      const group = wrapper.find('.shopPricing__periods');
      const inputs = wrapper.findAll('.shopPricing__periodInput');

      expect(group.attributes('role')).toBe('radiogroup');
      expect(inputs).toHaveLength(2);
      inputs.forEach((input) => expect(input.attributes('type')).toBe('radio'));
      // radios only behave as one choice while they share a name
      expect(new Set(inputs.map((input) => input.attributes('name'))).size).toBe(1);
      expect(inputs[0].element.checked).toBe(true);
      expect(inputs[1].element.checked).toBe(false);
    });

    it('gives each mounted instance its own radio group', () => {
      const first = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });
      const second = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      expect(first.find('.shopPricing__periodInput').attributes('name'))
        .not.toBe(second.find('.shopPricing__periodInput').attributes('name'));
    });

    it('shows the annual price per month with what it saves against paying monthly', () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      // 34000 / 12
      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('2,833.33');
      expect(wrapper.find('.shopPricing__amountPeriod').text())
        .toBe('Marketplace_PerMonthWithCurrency(EUR)');
      // 3400 * 12 - 34000
      expect(wrapper.find('.shopPricing__billing').text()).toContain('6,800 EUR');
      expect(wrapper.find('.shopPricing__billing').text()).toContain('34,000 EUR');
    });

    it('switches to the flat monthly price and drops the billing note', async () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      await wrapper.findAll('.shopPricing__periodInput')[1].setValue(true);

      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('3,400');
      expect(wrapper.find('.shopPricing__billing').exists()).toBe(false);
    });

    it('keeps the chosen period when the currency changes', async () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      await wrapper.findAll('.shopPricing__periodInput')[1].setValue(true);
      await wrapper.find('.shopPricing__currency').setValue('USD');

      expect(wrapper.vm.selectedPeriod).toBe('month');
      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('4,080');
    });

    it('defaults to the first currency the marketplace lists, not the cheapest flag', () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      expect(wrapper.vm.currencies).toEqual(['EUR', 'USD']);
      expect(wrapper.vm.selectedCurrency).toBe('EUR');
    });

    it('points add to cart at the variation actually on screen', async () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      expect(wrapper.find('.addToCartLink').attributes('href')).toContain('/year/EUR');

      await wrapper.findAll('.shopPricing__periodInput')[1].setValue(true);

      expect(wrapper.find('.addToCartLink').attributes('href')).toContain('/month/EUR');
    });
  });

  describe('the free months badge', () => {
    it('counts whole months saved by paying annually', () => {
      const wrapper = mountPricing(BUNDLE_VARIATIONS, { usePeriodTabs: true });

      expect(wrapper.find('.shopPricing__freeMonths').text())
        .toBe('Marketplace_XMonthsFree(2)');
    });

    it('uses the singular wording when only one month is saved', () => {
      const wrapper = mountPricing([
        {
          name: 'Tier monthly', currency: 'EUR', period: 'month', price: '100',
        },
        {
          name: 'Tier', currency: 'EUR', period: 'year', price: '1100',
        },
      ], { usePeriodTabs: true });

      expect(wrapper.find('.shopPricing__freeMonths').text()).toBe('Marketplace_OneMonthFree');
    });

    it('is hidden when paying annually saves nothing', () => {
      const wrapper = mountPricing([
        {
          name: 'Tier monthly', currency: 'EUR', period: 'month', price: '100',
        },
        {
          name: 'Tier', currency: 'EUR', period: 'year', price: '1200',
        },
      ], { usePeriodTabs: true });

      expect(wrapper.find('.shopPricing__freeMonths').exists()).toBe(false);
    });
  });

  describe('anything that is not a new bundle', () => {
    it('offers no period choice and keeps the period wording in the tier', () => {
      const wrapper = mountPricing([
        ...BUNDLE_VARIATIONS,
      ], { usePeriodTabs: false });

      expect(wrapper.find('.shopPricing__periods').exists()).toBe(false);
      expect(wrapper.vm.tiers).toEqual(['Up to 50 users monthly', 'Up to 50 users']);
    });

    it('prices a tiered plugin over the period it is billed for', () => {
      const wrapper = mountPricing(TIERED_VARIATIONS);

      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('150');
      expect(wrapper.find('.shopPricing__amountPeriod').text())
        .toBe('Marketplace_PerYearWithCurrency(EUR)');
      expect(wrapper.find('.shopPricing__billing').exists()).toBe(false);
    });

    it('reprices when another tier is picked', async () => {
      const wrapper = mountPricing(TIERED_VARIATIONS);

      await wrapper.find('.shopPricing__tier').setValue('5 to 15 users');

      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('300');
    });

    it('shows the free trial lead-in only when asked to', () => {
      expect(mountPricing(TIERED_VARIATIONS).find('.shopPricing__leadIn').exists()).toBe(false);
      expect(
        mountPricing(TIERED_VARIATIONS, { showFreeTrialLeadIn: true })
          .find('.shopPricing__leadIn').exists(),
      ).toBe(true);
    });
  });

  describe('incomplete shop data', () => {
    it('renders nothing when there are no variations', () => {
      expect(mountPricing([]).find('.shopPricing').exists()).toBe(false);
    });

    it('ignores variations with a billing period it cannot price', () => {
      const wrapper = mountPricing([
        {
          name: 'Tier', currency: 'EUR', period: 'lifetime', price: '9000',
        },
        {
          name: 'Tier', currency: 'EUR', period: 'year', price: '150',
        },
      ]);

      expect(wrapper.vm.variations).toHaveLength(1);
      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('150');
    });

    it('falls back to an available currency when the picked one is gone', async () => {
      const wrapper = mountPricing([
        {
          name: 'Up to 4 users', currency: 'EUR', period: 'year', price: '150',
        },
        {
          name: 'Up to 4 users', currency: 'USD', period: 'year', price: '175',
        },
        {
          name: 'Unlimited users', currency: 'EUR', period: 'year', price: '600',
        },
      ]);

      await wrapper.find('.shopPricing__currency').setValue('USD');
      expect(wrapper.vm.selectedCurrency).toBe('USD');

      // the unlimited tier is EUR only
      await wrapper.find('.shopPricing__tier').setValue('Unlimited users');

      expect(wrapper.vm.selectedCurrency).toBe('EUR');
      expect(wrapper.find('.shopPricing__amountValue').text()).toBe('600');
    });
  });
});
