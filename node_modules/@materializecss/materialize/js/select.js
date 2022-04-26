(function($) {
  'use strict';

  let _defaults = {
    classes: '',
    dropdownOptions: {}
  };

  class FormSelect extends Component {
    constructor(el, options) {
      super(FormSelect, el, options);
      if (this.$el.hasClass('browser-default')) return;
      this.el.M_FormSelect = this;
      this.options = $.extend({}, FormSelect.defaults, options);
      this.isMultiple = this.$el.prop('multiple');
      this.el.tabIndex = -1;
      this._values = [];
      this._setupDropdown();
      this._setupEventHandlers();
    }
    static get defaults() {
      return _defaults;
    }
    static init(els, options) {
      return super.init(this, els, options);
    }
    static getInstance(el) {
      let domElem = !!el.jquery ? el[0] : el;
      return domElem.M_FormSelect;
    }
    destroy() {
      this._removeEventHandlers();
      this._removeDropdown();
      this.el.M_FormSelect = undefined;
    }
    _setupEventHandlers() {
      this._handleSelectChangeBound = this._handleSelectChange.bind(this);
      this._handleOptionClickBound = this._handleOptionClick.bind(this);
      this._handleInputClickBound = this._handleInputClick.bind(this);
      $(this.dropdownOptions)
        .find('li:not(.optgroup)')
        .each((el) => {
          el.addEventListener('click', this._handleOptionClickBound);
        });
      this.el.addEventListener('change', this._handleSelectChangeBound);
      this.input.addEventListener('click', this._handleInputClickBound);
    }
    _removeEventHandlers() {
      $(this.dropdownOptions)
        .find('li:not(.optgroup)')
        .each((el) => {
          el.removeEventListener('click', this._handleOptionClickBound);
        });
      this.el.removeEventListener('change', this._handleSelectChangeBound);
      this.input.removeEventListener('click', this._handleInputClickBound);
    }
    _handleSelectChange(e) {
      this._setValueToInput();
    }
    _handleOptionClick(e) {
      e.preventDefault();
      let virtualOption = $(e.target).closest('li')[0];
      this._selectOptionElement(virtualOption);
      e.stopPropagation();
    }
    _arraysEqual(a, b) {
      if (a === b) return true;
      if (a == null || b == null) return false;
      if (a.length !== b.length) return false;
      for (let i = 0; i < a.length; ++i) if (a[i] !== b[i]) return false;
      return true;
    }
    _selectOptionElement(virtualOption) {
      if (!$(virtualOption).hasClass('disabled') && !$(virtualOption).hasClass('optgroup')) {
        const value = this._values.filter((value) => value.optionEl === virtualOption)[0];
        const previousSelectedValues = this.getSelectedValues();
        if (this.isMultiple) {
          // Multi-Select
          this._toggleEntryFromArray(value);
        } else {
          // Single-Select
          this._deselectAll();
          this._selectValue(value);
        }
        // Refresh Input-Text
        this._setValueToInput();
        // Trigger Change-Event only when data is different
        const actualSelectedValues = this.getSelectedValues();
        const selectionHasChanged = !this._arraysEqual(
          previousSelectedValues,
          actualSelectedValues
        );
        if (selectionHasChanged) this.$el.trigger('change');
      }
      if (!this.isMultiple) this.dropdown.close();
    }
    _handleInputClick() {
      if (this.dropdown && this.dropdown.isOpen) {
        this._setValueToInput();
        this._setSelectedStates();
      }
    }
    _setupDropdown() {
      this.wrapper = document.createElement('div');
      $(this.wrapper).addClass('select-wrapper ' + this.options.classes);
      this.$el.before($(this.wrapper));

      // Move actual select element into overflow hidden wrapper
      let $hideSelect = $('<div class="hide-select"></div>');
      $(this.wrapper).append($hideSelect);
      $hideSelect[0].appendChild(this.el);

      if (this.el.disabled) this.wrapper.classList.add('disabled');

      // Create dropdown
      this.$selectOptions = this.$el.children('option, optgroup');
      this.dropdownOptions = document.createElement('ul');
      this.dropdownOptions.id = `select-options-${M.guid()}`;
      $(this.dropdownOptions).addClass(
        'dropdown-content select-dropdown ' + (this.isMultiple ? 'multiple-select-dropdown' : '')
      );

      // Create dropdown structure
      if (this.$selectOptions.length) {
        this.$selectOptions.each((realOption) => {
          if ($(realOption).is('option')) {
            // Option
            const virtualOption = this._createAndAppendOptionWithIcon(
              realOption,
              this.isMultiple ? 'multiple' : undefined
            );
            this._addOptionToValues(realOption, virtualOption);
          } else if ($(realOption).is('optgroup')) {
            // Optgroup
            const selectOptions = $(realOption).children('option');
            $(this.dropdownOptions).append(
              $(
                '<li class="optgroup"><span>' + realOption.getAttribute('label') + '</span></li>'
              )[0]
            );
            selectOptions.each((realOption) => {
              const virtualOption = this._createAndAppendOptionWithIcon(
                realOption,
                'optgroup-option'
              );
              this._addOptionToValues(realOption, virtualOption);
            });
          }
        });
      }
      $(this.wrapper).append(this.dropdownOptions);

      // Add input dropdown
      this.input = document.createElement('input');
      $(this.input).addClass('select-dropdown dropdown-trigger');
      this.input.setAttribute('type', 'text');
      this.input.setAttribute('readonly', 'true');
      this.input.setAttribute('data-target', this.dropdownOptions.id);
      if (this.el.disabled) $(this.input).prop('disabled', 'true');

      $(this.wrapper).prepend(this.input);
      this._setValueToInput();

      // Add caret
      let dropdownIcon = $(
        '<svg class="caret" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>'
      );
      $(this.wrapper).prepend(dropdownIcon[0]);
      // Initialize dropdown
      if (!this.el.disabled) {
        let dropdownOptions = $.extend({}, this.options.dropdownOptions);
        dropdownOptions.coverTrigger = false;
        let userOnOpenEnd = dropdownOptions.onOpenEnd;
        // Add callback for centering selected option when dropdown content is scrollable
        dropdownOptions.onOpenEnd = (el) => {
          let selectedOption = $(this.dropdownOptions)
            .find('.selected')
            .first();
          if (selectedOption.length) {
            // Focus selected option in dropdown
            M.keyDown = true;
            this.dropdown.focusedIndex = selectedOption.index();
            this.dropdown._focusFocusedItem();
            M.keyDown = false;
            // Handle scrolling to selected option
            if (this.dropdown.isScrollable) {
              let scrollOffset =
                selectedOption[0].getBoundingClientRect().top -
                this.dropdownOptions.getBoundingClientRect().top; // scroll to selected option
              scrollOffset -= this.dropdownOptions.clientHeight / 2; // center in dropdown
              this.dropdownOptions.scrollTop = scrollOffset;
            }
          }
          // Handle user declared onOpenEnd if needed
          if (userOnOpenEnd && typeof userOnOpenEnd === 'function')
            userOnOpenEnd.call(this.dropdown, this.el);
        };
        // Prevent dropdown from closing too early
        dropdownOptions.closeOnClick = false;
        this.dropdown = M.Dropdown.init(this.input, dropdownOptions);
      }
      // Add initial selections
      this._setSelectedStates();
    }
    _addOptionToValues(realOption, virtualOption) {
      this._values.push({ el: realOption, optionEl: virtualOption });
    }
    _removeDropdown() {
      $(this.wrapper)
        .find('.caret')
        .remove();
      $(this.input).remove();
      $(this.dropdownOptions).remove();
      $(this.wrapper).before(this.$el);
      $(this.wrapper).remove();
    }
    _createAndAppendOptionWithIcon(realOption, type) {
      const li = document.createElement('li');
      if (realOption.disabled) li.classList.add('disabled');
      if (type === 'optgroup-option') li.classList.add(type);
      // Text / Checkbox
      const span = document.createElement('span');
      if (this.isMultiple)
        span.innerHTML = `<label><input type="checkbox"${
          realOption.disabled ? ' disabled="disabled"' : ''
        }><span>${realOption.innerHTML}</span></label>`;
      else span.innerHTML = realOption.innerHTML;
      li.appendChild(span);
      // add Icon
      const iconUrl = realOption.getAttribute('data-icon');
      const classes = realOption.getAttribute('class');
      if (iconUrl) {
        const img = $(`<img alt="" class="${classes}" src="${iconUrl}">`);
        li.prepend(img[0]);
      }
      // Check for multiple type
      $(this.dropdownOptions).append(li);
      return li;
    }

    _selectValue(value) {
      value.el.selected = true;
      value.optionEl.classList.add('selected');
      const checkbox = value.optionEl.querySelector('input[type="checkbox"]');
      if (checkbox) checkbox.checked = true;
    }
    _deselectValue(value) {
      value.el.selected = false;
      value.optionEl.classList.remove('selected');
      const checkbox = value.optionEl.querySelector('input[type="checkbox"]');
      if (checkbox) checkbox.checked = false;
    }
    _deselectAll() {
      this._values.forEach((value) => {
        this._deselectValue(value);
      });
    }
    _isValueSelected(value) {
      const realValues = this.getSelectedValues();
      return realValues.some((realValue) => realValue === value.el.value);
    }
    _toggleEntryFromArray(value) {
      const isSelected = this._isValueSelected(value);
      if (isSelected) this._deselectValue(value);
      else this._selectValue(value);
    }
    _getSelectedOptions() {
      return Array.prototype.filter.call(this.el.selectedOptions, (realOption) => realOption);
    }

    _setValueToInput() {
      const realOptions = this._getSelectedOptions();
      const values = this._values.filter((value) => realOptions.indexOf(value.el) >= 0);
      const texts = values.map((value) => value.optionEl.querySelector('span').innerText.trim());
      // Set input-text to first Option with empty value which indicates a description like "choose your option"
      if (texts.length === 0) {
        const firstDisabledOption = this.$el.find('option:disabled').eq(0);
        if (firstDisabledOption.length > 0 && firstDisabledOption[0].value === '') {
          this.input.value = firstDisabledOption.text();
          return;
        }
      }
      this.input.value = texts.join(', ');
    }
    _setSelectedStates() {
      this._values.forEach((value) => {
        const optionIsSelected = $(value.el).prop('selected');
        $(value.optionEl)
          .find('input[type="checkbox"]')
          .prop('checked', optionIsSelected);
        if (optionIsSelected) {
          this._activateOption($(this.dropdownOptions), $(value.optionEl));
        } else $(value.optionEl).removeClass('selected');
      });
    }
    _activateOption(ul, li) {
      if (!li) return;
      if (!this.isMultiple) ul.find('li.selected').removeClass('selected');
      $(li).addClass('selected');
    }

    getSelectedValues() {
      return this._getSelectedOptions().map((realOption) => realOption.value);
    }
  }

  M.FormSelect = FormSelect;

  if (M.jQueryLoaded) M.initializeJqueryWrapper(FormSelect, 'formSelect', 'M_FormSelect');
})(cash);
