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

function onClickHandler(pre: HTMLElement) {
  if (pre) {
    const textarea = document.createElement('textarea');
    textarea.value = pre.innerText;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    const btn = (pre.parentElement as HTMLButtonElement);
    if (btn) {
      const icon = btn.getElementsByTagName('i')[0];
      if (icon) {
        icon.classList.remove('copyToClipboardIcon');
        icon.classList.add('copyToClipboardIconCheck');
      }
      const copied = btn.getElementsByClassName('copyToClipboardCopiedDiv')[0];
      if (copied) {
        (copied as HTMLDivElement).style.display = 'inline-block';
        setTimeout(() => {
          (copied as HTMLDivElement).style.display = 'none';
        }, 2500);
      }
    }
  }
}

function onTransitionEndHandler(el: HTMLElement, binding: DirectiveBinding<CopyToClipboardArgs>) {
  if (binding.value.transitionOpen) {
    const btn = (el.parentElement as HTMLButtonElement);
    if (btn) {
      const icon = btn.getElementsByTagName('i')[0];
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

export default {
  mounted(el: HTMLElement, binding: DirectiveBinding<CopyToClipboardArgs>): void {
    const tagName = el.tagName.toLowerCase();
    if (tagName === 'pre') {
      const btn = document.createElement('button');
      btn.setAttribute('type', 'button');
      btn.className = 'copyToClipboardButton';

      const positionDiv = document.createElement('div');
      positionDiv.className = 'copyToClipboardPositionDiv';

      const icon = document.createElement('i');
      icon.className = 'copyToClipboardIcon';
      btn.appendChild(icon);

      const sp = document.createElement('span');
      sp.className = 'copyToClipboardSpan';
      sp.innerHTML = translate('General_Copy');
      btn.appendChild(sp);

      positionDiv.appendChild(btn);

      const cdiv = document.createElement('div');
      cdiv.className = 'copyToClipboardCopiedDiv';
      cdiv.innerHTML = translate('General_CopiedToClipboard');

      positionDiv.appendChild(cdiv);

      const pe = el.parentElement;
      if (pe) {
        pe.classList.add('copyToClipboardWrapper');
        pe.appendChild(positionDiv);
      }

      binding.value.onClickHandler = onClickHandler.bind(null, el);
      btn.addEventListener('click', binding.value.onClickHandler);

      binding.value.onTransitionEndHandler = onTransitionEndHandler.bind(null, el, binding);
      btn.addEventListener('transitionend', binding.value.onTransitionEndHandler);
    }
  },
  unmounted(el: HTMLElement, binding: DirectiveBinding<CopyToClipboardArgs>): void {
    el.removeEventListener('click', binding.value.onClickHandler!);
    el.removeEventListener('transitionend', binding.value.onTransitionEndHandler!);
  },
};
