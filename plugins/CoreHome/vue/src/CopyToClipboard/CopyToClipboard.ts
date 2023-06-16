/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding } from 'vue';
import { translate } from '../translate';

interface CopyToClipboardArgs {
  // state
  transitionOpen?: boolean;

  // event handlers
  onClickHandler?: (event: MouseEvent) => void;
  onTransitionEndHandler?: (event: Event) => void;
}

function onClickHandler(binding: DirectiveBinding<CopyToClipboardArgs>, event: Event) {
  if (event.target) {
    const el = event.target as HTMLInputElement;
    if (el.parentElement && el.parentElement.parentElement) {
      const pre = el.parentElement.parentElement.getElementsByTagName('pre')[0];

      if (pre) {
        const textarea = document.createElement('textarea');
        textarea.value = pre.innerHTML;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        const icon = el.parentElement.getElementsByTagName('i')[0];
        if (icon) {
          icon.classList.remove('copyToClipboardIcon');
          icon.classList.add('copyToClipboardIconCheck');
        }
        const copied = el.parentElement.parentElement.getElementsByClassName('copyToClipboardCopiedDiv')[0];
        if (copied) {
          (copied as HTMLDivElement).style.display = 'inline-block';
          setTimeout(() => { (copied as HTMLDivElement).style.display = 'none'; }, 2000);
        }
      }
    }
  }
}

function onTransitionEndHandler(binding: DirectiveBinding<CopyToClipboardArgs>, event: Event) {
  if (event.target) {
    if (binding.value.transitionOpen) {
      const el = event.target as HTMLInputElement;
      if (el.parentElement && el.parentElement.parentElement) {
        const icon = el.parentElement.getElementsByTagName('i')[0];
        if (icon) {
          icon.classList.remove('copyToClipboardIconCheck');
          icon.classList.add('copyToClipboardIcon');
        }
      }
      binding.value.transitionOpen = false;
    } else {
      binding.value.transitionOpen = true;
    }
  }
}

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<CopyToClipboardArgs>): void {
    const tagName = el.tagName.toLowerCase();
    if (tagName === 'pre') {
      const btn = document.createElement('button');
      btn.setAttribute('type', 'button');
      btn.className = 'copyToClipboardButton';

      const icon = document.createElement('i');
      icon.className = 'copyToClipboardIcon';
      btn.appendChild(icon);

      const sp = document.createElement('span');
      sp.className = 'copyToClipboardSpan';
      sp.innerHTML = translate('General_Copy');
      btn.appendChild(sp);

      const div = document.createElement('div');
      div.className = 'copyToClipboardCopiedDiv';
      div.innerHTML = translate('General_CopiedToClipboard');

      const pe = el.parentElement;
      if (pe) {
        pe.classList.add('copyToClipboardWrapper');
        pe.appendChild(btn);
        pe.appendChild(div);
      }

      binding.value.onClickHandler = onClickHandler.bind(null, binding);
      btn.addEventListener('click', binding.value.onClickHandler);

      binding.value.onTransitionEndHandler = onTransitionEndHandler.bind(null, binding);
      btn.addEventListener('transitionend', binding.value.onTransitionEndHandler);
    }
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<CopyToClipboardArgs>): void {
    el.removeEventListener('click', binding.value.onClickHandler!);
    el.removeEventListener('transitionend', binding.value.onTransitionEndHandler!);
  },
};
