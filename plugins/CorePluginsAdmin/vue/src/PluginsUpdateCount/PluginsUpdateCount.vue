<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<script lang="ts">
import { defineComponent } from 'vue';
import { AjaxHelper } from 'CoreHome';

interface UpdatePluginsCount {
  observer: MutationObserver | null,
  pending: boolean,
}

export default defineComponent({
  props: {
    selector: {
      type: String,
      required: true,
    },
    observerSelector: {
      type: String,
      default: '#secondNavBar',
    },
  },
  data(): UpdatePluginsCount {
    return {
      observer: null,
      pending: false,
    };
  },
  methods: {
    async fetchAndUpdate(el: Element) {
      await AjaxHelper.fetch<{ value: number }>({
        module: 'API',
        method: 'CorePluginsAdmin.getNumberOfPluginUpdates',
      }).then((response) => {
        const count = response.value || 0;

        if (count) {
          const originalText = el.textContent?.trim() ?? '';
          el.textContent = `${originalText} (${count})`;
        }
      }).catch((error) => {
        console.error('Failed to fetch number of plugin updates:', error.message || error);
      });
    },
    maybeUpdate() {
      const el = document.querySelector(this.selector);
      if (!el) return;

      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }

      if (this.pending) return;
      this.pending = true;

      this.fetchAndUpdate(el).finally(() => {
        this.pending = false;
      });
    },
  },
  mounted() {
    const root = document.querySelector(this.observerSelector);
    if (!root) {
      return;
    }

    this.maybeUpdate();

    this.observer = new MutationObserver(() => this.maybeUpdate());
    this.observer.observe(root, { childList: true, subtree: true });
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }
  },
  render() {
    return null;
  },
});
</script>
