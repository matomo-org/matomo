/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import Alert from './Alert.vue';

describe('CoreHome/Alert', () => {
  function createWrapper(severity: string, message = 'This is a warning') {
    return mount(Alert, {
      props: { severity },
      slots: { default: message },
    });
  }

  it('should render slot content and severity class', () => {
    const wrapper = createWrapper('warning');

    expect(wrapper.classes()).toContain('alert');
    expect(wrapper.classes()).toContain('alert-warning');
    expect(wrapper.text()).toContain('This is a warning');
  });

  it('should update severity class when prop changes', async () => {
    const wrapper = createWrapper('info', 'Informational message');

    expect(wrapper.classes()).toContain('alert-info');
    expect(wrapper.classes()).not.toContain('alert-error');

    await wrapper.setProps({ severity: 'error' });

    expect(wrapper.classes()).toContain('alert-error');
    expect(wrapper.classes()).not.toContain('alert-info');
  });
});
