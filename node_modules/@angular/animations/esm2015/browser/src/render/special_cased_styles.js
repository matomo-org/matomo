/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { eraseStyles, setStyles } from '../util';
/**
 * Returns an instance of `SpecialCasedStyles` if and when any special (non animateable) styles are
 * detected.
 *
 * In CSS there exist properties that cannot be animated within a keyframe animation
 * (whether it be via CSS keyframes or web-animations) and the animation implementation
 * will ignore them. This function is designed to detect those special cased styles and
 * return a container that will be executed at the start and end of the animation.
 *
 * @returns an instance of `SpecialCasedStyles` if any special styles are detected otherwise `null`
 */
export function packageNonAnimatableStyles(element, styles) {
    let startStyles = null;
    let endStyles = null;
    if (Array.isArray(styles) && styles.length) {
        startStyles = filterNonAnimatableStyles(styles[0]);
        if (styles.length > 1) {
            endStyles = filterNonAnimatableStyles(styles[styles.length - 1]);
        }
    }
    else if (styles) {
        startStyles = filterNonAnimatableStyles(styles);
    }
    return (startStyles || endStyles) ? new SpecialCasedStyles(element, startStyles, endStyles) :
        null;
}
/**
 * Designed to be executed during a keyframe-based animation to apply any special-cased styles.
 *
 * When started (when the `start()` method is run) then the provided `startStyles`
 * will be applied. When finished (when the `finish()` method is called) the
 * `endStyles` will be applied as well any any starting styles. Finally when
 * `destroy()` is called then all styles will be removed.
 */
export class SpecialCasedStyles {
    constructor(_element, _startStyles, _endStyles) {
        this._element = _element;
        this._startStyles = _startStyles;
        this._endStyles = _endStyles;
        this._state = 0 /* Pending */;
        let initialStyles = SpecialCasedStyles.initialStylesByElement.get(_element);
        if (!initialStyles) {
            SpecialCasedStyles.initialStylesByElement.set(_element, initialStyles = {});
        }
        this._initialStyles = initialStyles;
    }
    start() {
        if (this._state < 1 /* Started */) {
            if (this._startStyles) {
                setStyles(this._element, this._startStyles, this._initialStyles);
            }
            this._state = 1 /* Started */;
        }
    }
    finish() {
        this.start();
        if (this._state < 2 /* Finished */) {
            setStyles(this._element, this._initialStyles);
            if (this._endStyles) {
                setStyles(this._element, this._endStyles);
                this._endStyles = null;
            }
            this._state = 1 /* Started */;
        }
    }
    destroy() {
        this.finish();
        if (this._state < 3 /* Destroyed */) {
            SpecialCasedStyles.initialStylesByElement.delete(this._element);
            if (this._startStyles) {
                eraseStyles(this._element, this._startStyles);
                this._endStyles = null;
            }
            if (this._endStyles) {
                eraseStyles(this._element, this._endStyles);
                this._endStyles = null;
            }
            setStyles(this._element, this._initialStyles);
            this._state = 3 /* Destroyed */;
        }
    }
}
SpecialCasedStyles.initialStylesByElement = new WeakMap();
function filterNonAnimatableStyles(styles) {
    let result = null;
    const props = Object.keys(styles);
    for (let i = 0; i < props.length; i++) {
        const prop = props[i];
        if (isNonAnimatableStyle(prop)) {
            result = result || {};
            result[prop] = styles[prop];
        }
    }
    return result;
}
function isNonAnimatableStyle(prop) {
    return prop === 'display' || prop === 'position';
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3BlY2lhbF9jYXNlZF9zdHlsZXMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9hbmltYXRpb25zL2Jyb3dzZXIvc3JjL3JlbmRlci9zcGVjaWFsX2Nhc2VkX3N0eWxlcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFDSCxPQUFPLEVBQUMsV0FBVyxFQUFFLFNBQVMsRUFBQyxNQUFNLFNBQVMsQ0FBQztBQUUvQzs7Ozs7Ozs7OztHQVVHO0FBQ0gsTUFBTSxVQUFVLDBCQUEwQixDQUN0QyxPQUFZLEVBQUUsTUFBbUQ7SUFDbkUsSUFBSSxXQUFXLEdBQThCLElBQUksQ0FBQztJQUNsRCxJQUFJLFNBQVMsR0FBOEIsSUFBSSxDQUFDO0lBQ2hELElBQUksS0FBSyxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsSUFBSSxNQUFNLENBQUMsTUFBTSxFQUFFO1FBQzFDLFdBQVcsR0FBRyx5QkFBeUIsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUNuRCxJQUFJLE1BQU0sQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO1lBQ3JCLFNBQVMsR0FBRyx5QkFBeUIsQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDO1NBQ2xFO0tBQ0Y7U0FBTSxJQUFJLE1BQU0sRUFBRTtRQUNqQixXQUFXLEdBQUcseUJBQXlCLENBQUMsTUFBTSxDQUFDLENBQUM7S0FDakQ7SUFFRCxPQUFPLENBQUMsV0FBVyxJQUFJLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxJQUFJLGtCQUFrQixDQUFDLE9BQU8sRUFBRSxXQUFXLEVBQUUsU0FBUyxDQUFDLENBQUMsQ0FBQztRQUN6RCxJQUFJLENBQUM7QUFDM0MsQ0FBQztBQUVEOzs7Ozs7O0dBT0c7QUFDSCxNQUFNLE9BQU8sa0JBQWtCO0lBTTdCLFlBQ1ksUUFBYSxFQUFVLFlBQXVD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QyxDQUFDO1NBQzdCO0tBQ0Y7SUFDRCxPQUFPLE1BQU0sQ0FBQztBQUNoQixDQUFDO0FBRUQsU0FBUyxvQkFBb0IsQ0FBQyxJQUFZO0lBQ3hDLE9BQU8sSUFBSSxLQUFLLFNBQVMsSUFBSSxJQUFJLEtBQUssVUFBVSxDQUFDO0FBQ25ELENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cbmltcG9ydCB7ZXJhc2VTdHlsZXMsIHNldFN0eWxlc30gZnJvbSAnLi4vdXRpbCc7XG5cbi8qKlxuICogUmV0dXJucyBhbiBpbnN0YW5jZSBvZiBgU3BlY2lhbENhc2VkU3R5bGVzYCBpZiBhbmQgd2hlbiBhbnkgc3BlY2lhbCAobm9uIGFuaW1hdGVhYmxlKSBzdHlsZXMgYXJlXG4gKiBkZXRlY3RlZC5cbiAqXG4gKiBJbiBDU1MgdGhlcmUgZXhpc3QgcHJvcGVydGllcyB0aGF0IGNhbm5vdCBiZSBhbmltYXRlZCB3aXRoaW4gYSBrZXlmcmFtZSBhbmltYXRpb25cbiAqICh3aGV0aGVyIGl0IGJlIHZpYSBDU1Mga2V5ZnJhbWVzIG9yIHdlYi1hbmltYXRpb25zKSBhbmQgdGhlIGFuaW1hdGlvbiBpbXBsZW1lbnRhdGlvblxuICogd2lsbCBpZ25vcmUgdGhlbS4gVGhpcyBmdW5jdGlvbiBpcyBkZXNpZ25lZCB0byBkZXRlY3QgdGhvc2Ugc3BlY2lhbCBjYXNlZCBzdHlsZXMgYW5kXG4gKiByZXR1cm4gYSBjb250YWluZXIgdGhhdCB3aWxsIGJlIGV4ZWN1dGVkIGF0IHRoZSBzdGFydCBhbmQgZW5kIG9mIHRoZSBhbmltYXRpb24uXG4gKlxuICogQHJldHVybnMgYW4gaW5zdGFuY2Ugb2YgYFNwZWNpYWxDYXNlZFN0eWxlc2AgaWYgYW55IHNwZWNpYWwgc3R5bGVzIGFyZSBkZXRlY3RlZCBvdGhlcndpc2UgYG51bGxgXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBwYWNrYWdlTm9uQW5pbWF0YWJsZVN0eWxlcyhcbiAgICBlbGVtZW50OiBhbnksIHN0eWxlczoge1trZXk6IHN0cmluZ106IGFueX18e1trZXk6IHN0cmluZ106IGFueX1bXSk6IFNwZWNpYWxDYXNlZFN0eWxlc3xudWxsIHtcbiAgbGV0IHN0YXJ0U3R5bGVzOiB7W2tleTogc3RyaW5nXTogYW55fXxudWxsID0gbnVsbDtcbiAgbGV0IGVuZFN0eWxlczoge1trZXk6IHN0cmluZ106IGFueX18bnVsbCA9IG51bGw7XG4gIGlmIChBcnJheS5pc0FycmF5KHN0eWxlcykgJiYgc3R5bGVzLmxlbmd0aCkge1xuICAgIHN0YXJ0U3R5bGVzID0gZmlsdGVyTm9uQW5pbWF0YWJsZVN0eWxlcyhzdHlsZXNbMF0pO1xuICAgIGlmIChzdHlsZXMubGVuZ3RoID4gMSkge1xuICAgICAgZW5kU3R5bGVzID0gZmlsdGVyTm9uQW5pbWF0YWJsZVN0eWxlcyhzdHlsZXNbc3R5bGVzLmxlbmd0aCAtIDFdKTtcbiAgICB9XG4gIH0gZWxzZSBpZiAoc3R5bGVzKSB7XG4gICAgc3RhcnRTdHlsZXMgPSBmaWx0ZXJOb25BbmltYXRhYmxlU3R5bGVzKHN0eWxlcyk7XG4gIH1cblxuICByZXR1cm4gKHN0YXJ0U3R5bGVzIHx8IGVuZFN0eWxlcykgPyBuZXcgU3BlY2lhbENhc2VkU3R5bGVzKGVsZW1lbnQsIHN0YXJ0U3R5bGVzLCBlbmRTdHlsZXMpIDpcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbnVsbDtcbn1cblxuLyoqXG4gKiBEZXNpZ25lZCB0byBiZSBleGVjdXRlZCBkdXJpbmcgYSBrZXlmcmFtZS1iYXNlZCBhbmltYXRpb24gdG8gYXBwbHkgYW55IHNwZWNpYWwtY2FzZWQgc3R5bGVzLlxuICpcbiAqIFdoZW4gc3RhcnRlZCAod2hlbiB0aGUgYHN0YXJ0KClgIG1ldGhvZCBpcyBydW4pIHRoZW4gdGhlIHByb3ZpZGVkIGBzdGFydFN0eWxlc2BcbiAqIHdpbGwgYmUgYXBwbGllZC4gV2hlbiBmaW5pc2hlZCAod2hlbiB0aGUgYGZpbmlzaCgpYCBtZXRob2QgaXMgY2FsbGVkKSB0aGVcbiAqIGBlbmRTdHlsZXNgIHdpbGwgYmUgYXBwbGllZCBhcyB3ZWxsIGFueSBhbnkgc3RhcnRpbmcgc3R5bGVzLiBGaW5hbGx5IHdoZW5cbiAqIGBkZXN0cm95KClgIGlzIGNhbGxlZCB0aGVuIGFsbCBzdHlsZXMgd2lsbCBiZSByZW1vdmVkLlxuICovXG5leHBvcnQgY2xhc3MgU3BlY2lhbENhc2VkU3R5bGVzIHtcbiAgc3RhdGljIGluaXRpYWxTdHlsZXNCeUVsZW1lbnQgPSBuZXcgV2Vha01hcDxhbnksIHtba2V5OiBzdHJpbmddOiBhbnl9PigpO1xuXG4gIHByaXZhdGUgX3N0YXRlID0gU3BlY2lhbENhc2VkU3R5bGVzU3RhdGUuUGVuZGluZztcbiAgcHJpdmF0ZSBfaW5pdGlhbFN0eWxlcyE6IHtba2V5OiBzdHJpbmddOiBhbnl9O1xuXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHJpdmF0ZSBfZWxlbWVudDogYW55LCBwcml2YXRlIF9zdGFydFN0eWxlczoge1trZXk6IHN0cmluZ106IGFueX18bnVsbCxcbiAgICAgIHByaXZhdGUgX2VuZFN0eWxlczoge1trZXk6IHN0cmluZ106IGFueX18bnVsbCkge1xuICAgIGxldCBpbml0aWFsU3R5bGVzID0gU3BlY2lhbENhc2VkU3R5bGVzLmluaXRpYWxTdHlsZXNCeUVsZW1lbnQuZ2V0KF9lbGVtZW50KTtcbiAgICBpZiAoIWluaXRpYWxTdHlsZXMpIHtcbiAgICAgIFNwZWNpYWxDYXNlZFN0eWxlcy5pbml0aWFsU3R5bGVzQnlFbGVtZW50LnNldChfZWxlbWVudCwgaW5pdGlhbFN0eWxlcyA9IHt9KTtcbiAgICB9XG4gICAgdGhpcy5faW5pdGlhbFN0eWxlcyA9IGluaXRpYWxTdHlsZXM7XG4gIH1cblxuICBzdGFydCgpIHtcbiAgICBpZiAodGhpcy5fc3RhdGUgPCBTcGVjaWFsQ2FzZWRTdHlsZXNTdGF0ZS5TdGFydGVkKSB7XG4gICAgICBpZiAodGhpcy5fc3RhcnRTdHlsZXMpIHtcbiAgICAgICAgc2V0U3R5bGVzKHRoaXMuX2VsZW1lbnQsIHRoaXMuX3N0YXJ0U3R5bGVzLCB0aGlzLl9pbml0aWFsU3R5bGVzKTtcbiAgICAgIH1cbiAgICAgIHRoaXMuX3N0YXRlID0gU3BlY2lhbENhc2VkU3R5bGVzU3RhdGUuU3RhcnRlZDtcbiAgICB9XG4gIH1cblxuICBmaW5pc2goKSB7XG4gICAgdGhpcy5zdGFydCgpO1xuICAgIGlmICh0aGlzLl9zdGF0ZSA8IFNwZWNpYWxDYXNlZFN0eWxlc1N0YXRlLkZpbmlzaGVkKSB7XG4gICAgICBzZXRTdHlsZXModGhpcy5fZWxlbWVudCwgdGhpcy5faW5pdGlhbFN0eWxlcyk7XG4gICAgICBpZiAodGhpcy5fZW5kU3R5bGVzKSB7XG4gICAgICAgIHNldFN0eWxlcyh0aGlzLl9lbGVtZW50LCB0aGlzLl9lbmRTdHlsZXMpO1xuICAgICAgICB0aGlzLl9lbmRTdHlsZXMgPSBudWxsO1xuICAgICAgfVxuICAgICAgdGhpcy5fc3RhdGUgPSBTcGVjaWFsQ2FzZWRTdHlsZXNTdGF0ZS5TdGFydGVkO1xuICAgIH1cbiAgfVxuXG4gIGRlc3Ryb3koKSB7XG4gICAgdGhpcy5maW5pc2goKTtcbiAgICBpZiAodGhpcy5fc3RhdGUgPCBTcGVjaWFsQ2FzZWRTdHlsZXNTdGF0ZS5EZXN0cm95ZWQpIHtcbiAgICAgIFNwZWNpYWxDYXNlZFN0eWxlcy5pbml0aWFsU3R5bGVzQnlFbGVtZW50LmRlbGV0ZSh0aGlzLl9lbGVtZW50KTtcbiAgICAgIGlmICh0aGlzLl9zdGFydFN0eWxlcykge1xuICAgICAgICBlcmFzZVN0eWxlcyh0aGlzLl9lbGVtZW50LCB0aGlzLl9zdGFydFN0eWxlcyk7XG4gICAgICAgIHRoaXMuX2VuZFN0eWxlcyA9IG51bGw7XG4gICAgICB9XG4gICAgICBpZiAodGhpcy5fZW5kU3R5bGVzKSB7XG4gICAgICAgIGVyYXNlU3R5bGVzKHRoaXMuX2VsZW1lbnQsIHRoaXMuX2VuZFN0eWxlcyk7XG4gICAgICAgIHRoaXMuX2VuZFN0eWxlcyA9IG51bGw7XG4gICAgICB9XG4gICAgICBzZXRTdHlsZXModGhpcy5fZWxlbWVudCwgdGhpcy5faW5pdGlhbFN0eWxlcyk7XG4gICAgICB0aGlzLl9zdGF0ZSA9IFNwZWNpYWxDYXNlZFN0eWxlc1N0YXRlLkRlc3Ryb3llZDtcbiAgICB9XG4gIH1cbn1cblxuLyoqXG4gKiBBbiBlbnVtIG9mIHN0YXRlcyByZWZsZWN0aXZlIG9mIHdoYXQgdGhlIHN0YXR1cyBvZiBgU3BlY2lhbENhc2VkU3R5bGVzYCBpcy5cbiAqXG4gKiBEZXBlbmRpbmcgb24gaG93IGBTcGVjaWFsQ2FzZWRTdHlsZXNgIGlzIGludGVyYWN0ZWQgd2l0aCwgdGhlIHN0YXJ0IGFuZCBlbmRcbiAqIHN0eWxlcyBtYXkgbm90IGJlIGFwcGxpZWQgaW4gdGhlIHNhbWUgd2F5LiBUaGlzIGVudW0gZW5zdXJlcyB0aGF0IGlmIGFuZCB3aGVuXG4gKiB0aGUgZW5kaW5nIHN0eWxlcyBhcmUgYXBwbGllZCB0aGVuIHRoZSBzdGFydGluZyBzdHlsZXMgYXJlIGFwcGxpZWQuIEl0IGlzXG4gKiBhbHNvIHVzZWQgdG8gcmVmbGVjdCB3aGF0IHRoZSBjdXJyZW50IHN0YXR1cyBvZiB0aGUgc3BlY2lhbCBjYXNlZCBzdHlsZXMgYXJlXG4gKiB3aGljaCBoZWxwcyBwcmV2ZW50IHRoZSBzdGFydGluZy9lbmRpbmcgc3R5bGVzIG5vdCBiZSBhcHBsaWVkIHR3aWNlLiBJdCBpc1xuICogYWxzbyB1c2VkIHRvIGNsZWFudXAgdGhlIHN0eWxlcyBvbmNlIGBTcGVjaWFsQ2FzZWRTdHlsZXNgIGlzIGRlc3Ryb3llZC5cbiAqL1xuY29uc3QgZW51bSBTcGVjaWFsQ2FzZWRTdHlsZXNTdGF0ZSB7XG4gIFBlbmRpbmcgPSAwLFxuICBTdGFydGVkID0gMSxcbiAgRmluaXNoZWQgPSAyLFxuICBEZXN0cm95ZWQgPSAzLFxufVxuXG5mdW5jdGlvbiBmaWx0ZXJOb25BbmltYXRhYmxlU3R5bGVzKHN0eWxlczoge1trZXk6IHN0cmluZ106IGFueX0pIHtcbiAgbGV0IHJlc3VsdDoge1trZXk6IHN0cmluZ106IGFueX18bnVsbCA9IG51bGw7XG4gIGNvbnN0IHByb3BzID0gT2JqZWN0LmtleXMoc3R5bGVzKTtcbiAgZm9yIChsZXQgaSA9IDA7IGkgPCBwcm9wcy5sZW5ndGg7IGkrKykge1xuICAgIGNvbnN0IHByb3AgPSBwcm9wc1tpXTtcbiAgICBpZiAoaXNOb25BbmltYXRhYmxlU3R5bGUocHJvcCkpIHtcbiAgICAgIHJlc3VsdCA9IHJlc3VsdCB8fCB7fTtcbiAgICAgIHJlc3VsdFtwcm9wXSA9IHN0eWxlc1twcm9wXTtcbiAgICB9XG4gIH1cbiAgcmV0dXJuIHJlc3VsdDtcbn1cblxuZnVuY3Rpb24gaXNOb25BbmltYXRhYmxlU3R5bGUocHJvcDogc3RyaW5nKSB7XG4gIHJldHVybiBwcm9wID09PSAnZGlzcGxheScgfHwgcHJvcCA9PT0gJ3Bvc2l0aW9uJztcbn1cbiJdfQ==