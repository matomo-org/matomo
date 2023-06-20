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
      this.labelEl = null;
      this._labelFor = false;
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
      // Returns label to its original owner
      if (this._labelFor) this.labelEl.setAttribute("for", this.el.id);
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
          el.addEventListener('keydown', (e) => {
            if (e.key === " " || e.key === "Enter") this._handleOptionClickBound(e);
          });
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
      this.dropdownOptions.setAttribute("role", "listbox");
      this.dropdownOptions.setAttribute("aria-multiselectable", this.isMultiple);

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
            let lId = "opt-group-" + M.guid();
            let groupParent = $(
              `<li class="optgroup" role="group" aria-labelledby="${lId}" tabindex="-1"><span id="${lId}" role="presentation">${realOption.getAttribute('label')}</span></li>`
            )[0];
            let groupChildren = [];
            $(this.dropdownOptions).append(groupParent);
            selectOptions.each((realOption) => {
              const virtualOption = this._createAndAppendOptionWithIcon(
                realOption,
                'optgroup-option'
              );
              let cId = "opt-child-" + M.guid();
              virtualOption.id = cId;
              groupChildren.push(cId);
              this._addOptionToValues(realOption, virtualOption);
            });
            groupParent.setAttribute("aria-owns", groupChildren.join(" "));
          }
        });
      }
      $(this.wrapper).append(this.dropdownOptions);

      // Add input dropdown
      this.input = document.createElement('input');
      this.input.id = "m_select-input-" + M.guid();
      $(this.input).addClass('select-dropdown dropdown-trigger');
      this.input.setAttribute('type', 'text');
      this.input.setAttribute('readonly', 'true');
      this.input.setAttribute('data-target', this.dropdownOptions.id);
      this.input.setAttribute('aria-readonly', 'true');
      this.input.setAttribute("aria-required", this.el.hasAttribute("required"));
      if (this.el.disabled) $(this.input).prop('disabled', 'true');

      // Makes new element to assume HTML's select label and
      //   aria-attributes, if exists
      if (this.el.hasAttribute("aria-labelledby")){
        this.labelEl = document.getElementById(this.el.getAttribute("aria-labelledby"));
      }
      else if (this.el.id != ""){
        let lbl = $(`label[for='${this.el.id}']`);
        if (lbl.length){
          this.labelEl = lbl[0];
          this.labelEl.removeAttribute("for");
          this._labelFor = true;
        }
      }
      // Tries to find a valid label in parent element
      if (!this.labelEl){
        let el = this.el.parentElement;
        if (el) el = el.getElementsByTagName("label")[0];
        if (el) this.labelEl = el;
      }
      if (this.labelEl && this.labelEl.id == ""){
        this.labelEl.id = "m_select-label-" + M.guid();
      }

      if (this.labelEl){
        this.labelEl.setAttribute("for", this.input.id);
        this.dropdownOptions.setAttribute("aria-labelledby", this.labelEl.id);
      }
      else this.dropdownOptions.setAttribute("aria-label", "");

      let attrs = this.el.attributes;
      for (let i = 0; i < attrs.length; ++i){
        const attr = attrs[i];
        if (attr.name.startsWith("aria-"))
          this.input.setAttribute(attr.name, attr.value);
      }

      // Adds aria-attributes to input element
      this.input.setAttribute("role", "combobox");
      this.input.setAttribute("aria-owns", this.dropdownOptions.id);
      this.input.setAttribute("aria-controls", this.dropdownOptions.id);
      this.input.setAttribute("aria-expanded", false);

      $(this.wrapper).prepend(this.input);
      this._setValueToInput();

      // Add caret
      let dropdownIcon = $(
        '<svg class="caret" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>'
      );
      $(this.wrapper).prepend(dropdownIcon[0]);
      // Initialize dropdown
      if (!this.el.disabled) {
        let dropdownOptions = $.extend({}, this.options.dropdownOptions);
        dropdownOptions.coverTrigger = false;
        let userOnOpenEnd = dropdownOptions.onOpenEnd;
        let userOnCloseEnd = dropdownOptions.onCloseEnd;
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
          // Sets "aria-expanded" to "true"
          this.input.setAttribute("aria-expanded", true);
          // Handle user declared onOpenEnd if needed
          if (userOnOpenEnd && typeof userOnOpenEnd === 'function')
            userOnOpenEnd.call(this.dropdown, this.el);
        };
        // Add callback for reseting "expanded" state
        dropdownOptions.onCloseEnd = (el) => {
          // Sets "aria-expanded" to "false"
          this.input.setAttribute("aria-expanded", false);
          // Handle user declared onOpenEnd if needed
          if (userOnCloseEnd && typeof userOnCloseEnd === 'function')
            userOnCloseEnd.call(this.dropdown, this.el);
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
      li.setAttribute("role", "option");
      if (realOption.disabled){
        li.classList.add('disabled');
        li.setAttribute("aria-disabled", true);
      }
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
        img[0].setAttribute("aria-hidden", true);
        li.prepend(img[0]);
      }
      // Check for multiple type
      $(this.dropdownOptions).append(li);
      return li;
    }

    _selectValue(value) {
      value.el.selected = true;
      value.optionEl.classList.add('selected');
      value.optionEl.setAttribute("aria-selected", true);
      const checkbox = value.optionEl.querySelector('input[type="checkbox"]');
      if (checkbox) checkbox.checked = true;
    }
    _deselectValue(value) {
      value.el.selected = false;
      value.optionEl.classList.remove('selected');
      value.optionEl.setAttribute("aria-selected", false);
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
        } else {
          $(value.optionEl).removeClass('selected');
          $(value.optionEl).attr("aria-selected", false);
        }
      });
    }
    _activateOption(ul, li) {
      if (!li) return;
      if (!this.isMultiple) ul.find('li.selected').removeClass('selected');
      $(li).addClass('selected');
      $(li).attr("aria-selected", true);
    }

    getSelectedValues() {
      return this._getSelectedOptions().map((realOption) => realOption.value);
    }
  }

  M.FormSelect = FormSelect;

  if (M.jQueryLoaded) M.initializeJqueryWrapper(FormSelect, 'formSelect', 'M_FormSelect');
})(cash);
