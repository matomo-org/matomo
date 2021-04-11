/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { getControlAsyncValidators, getControlValidators, mergeValidators } from '../validators';
import { BuiltInControlValueAccessor } from './control_value_accessor';
import { DefaultValueAccessor } from './default_value_accessor';
import { ReactiveErrors } from './reactive_errors';
export function controlPath(name, parent) {
    return [...parent.path, name];
}
/**
 * Links a Form control and a Form directive by setting up callbacks (such as `onChange`) on both
 * instances. This function is typically invoked when form directive is being initialized.
 *
 * @param control Form control instance that should be linked.
 * @param dir Directive that should be linked with a given control.
 */
export function setUpControl(control, dir) {
    if (typeof ngDevMode === 'undefined' || ngDevMode) {
        if (!control)
            _throwError(dir, 'Cannot find control with');
        if (!dir.valueAccessor)
            _throwError(dir, 'No value accessor for form control with');
    }
    setUpValidators(control, dir, /* handleOnValidatorChange */ true);
    dir.valueAccessor.writeValue(control.value);
    setUpViewChangePipeline(control, dir);
    setUpModelChangePipeline(control, dir);
    setUpBlurPipeline(control, dir);
    setUpDisabledChangeHandler(control, dir);
}
/**
 * Reverts configuration performed by the `setUpControl` control function.
 * Effectively disconnects form control with a given form directive.
 * This function is typically invoked when corresponding form directive is being destroyed.
 *
 * @param control Form control which should be cleaned up.
 * @param dir Directive that should be disconnected from a given control.
 * @param validateControlPresenceOnChange Flag that indicates whether onChange handler should
 *     contain asserts to verify that it's not called once directive is destroyed. We need this flag
 *     to avoid potentially breaking changes caused by better control cleanup introduced in #39235.
 */
export function cleanUpControl(control, dir, validateControlPresenceOnChange = true) {
    const noop = () => {
        if (validateControlPresenceOnChange && (typeof ngDevMode === 'undefined' || ngDevMode)) {
            _noControlError(dir);
        }
    };
    // The `valueAccessor` field is typically defined on FromControl and FormControlName directive
    // instances and there is a logic in `selectValueAccessor` function that throws if it's not the
    // case. We still check the presence of `valueAccessor` before invoking its methods to make sure
    // that cleanup works correctly if app code or tests are setup to ignore the error thrown from
    // `selectValueAccessor`. See https://github.com/angular/angular/issues/40521.
    if (dir.valueAccessor) {
        dir.valueAccessor.registerOnChange(noop);
        dir.valueAccessor.registerOnTouched(noop);
    }
    cleanUpValidators(control, dir, /* handleOnValidatorChange */ true);
    if (control) {
        dir._invokeOnDestroyCallbacks();
        control._registerOnCollectionChange(() => { });
    }
}
function registerOnValidatorChange(validators, onChange) {
    validators.forEach((validator) => {
        if (validator.registerOnValidatorChange)
            validator.registerOnValidatorChange(onChange);
    });
}
/**
 * Sets up disabled change handler function on a given form control if ControlValueAccessor
 * associated with a given directive instance supports the `setDisabledState` call.
 *
 * @param control Form control where disabled change handler should be setup.
 * @param dir Corresponding directive instance associated with this control.
 */
export function setUpDisabledChangeHandler(control, dir) {
    if (dir.valueAccessor.setDisabledState) {
        const onDisabledChange = (isDisabled) => {
            dir.valueAccessor.setDisabledState(isDisabled);
        };
        control.registerOnDisabledChange(onDisabledChange);
        // Register a callback function to cleanup disabled change handler
        // from a control instance when a directive is destroyed.
        dir._registerOnDestroy(() => {
            control._unregisterOnDisabledChange(onDisabledChange);
        });
    }
}
/**
 * Sets up sync and async directive validators on provided form control.
 * This function merges validators from the directive into the validators of the control.
 *
 * @param control Form control where directive validators should be setup.
 * @param dir Directive instance that contains validators to be setup.
 * @param handleOnValidatorChange Flag that determines whether directive validators should be setup
 *     to handle validator input change.
 */
export function setUpValidators(control, dir, handleOnValidatorChange) {
    const validators = getControlValidators(control);
    if (dir.validator !== null) {
        control.setValidators(mergeValidators(validators, dir.validator));
    }
    else if (typeof validators === 'function') {
        // If sync validators are represented by a single validator function, we force the
        // `Validators.compose` call to happen by executing the `setValidators` function with
        // an array that contains that function. We need this to avoid possible discrepancies in
        // validators behavior, so sync validators are always processed by the `Validators.compose`.
        // Note: we should consider moving this logic inside the `setValidators` function itself, so we
        // have consistent behavior on AbstractControl API level. The same applies to the async
        // validators logic below.
        control.setValidators([validators]);
    }
    const asyncValidators = getControlAsyncValidators(control);
    if (dir.asyncValidator !== null) {
        control.setAsyncValidators(mergeValidators(asyncValidators, dir.asyncValidator));
    }
    else if (typeof asyncValidators === 'function') {
        control.setAsyncValidators([asyncValidators]);
    }
    // Re-run validation when validator binding changes, e.g. minlength=3 -> minlength=4
    if (handleOnValidatorChange) {
        const onValidatorChange = () => control.updateValueAndValidity();
        registerOnValidatorChange(dir._rawValidators, onValidatorChange);
        registerOnValidatorChange(dir._rawAsyncValidators, onValidatorChange);
    }
}
/**
 * Cleans up sync and async directive validators on provided form control.
 * This function reverts the setup performed by the `setUpValidators` function, i.e.
 * removes directive-specific validators from a given control instance.
 *
 * @param control Form control from where directive validators should be removed.
 * @param dir Directive instance that contains validators to be removed.
 * @param handleOnValidatorChange Flag that determines whether directive validators should also be
 *     cleaned up to stop handling validator input change (if previously configured to do so).
 * @returns true if a control was updated as a result of this action.
 */
export function cleanUpValidators(control, dir, handleOnValidatorChange) {
    let isControlUpdated = false;
    if (control !== null) {
        if (dir.validator !== null) {
            const validators = getControlValidators(control);
            if (Array.isArray(validators) && validators.length > 0) {
                // Filter out directive validator function.
                const updatedValidators = validators.filter(validator => validator !== dir.validator);
                if (updatedValidators.length !== validators.length) {
                    isControlUpdated = true;
                    control.setValidators(updatedValidators);
                }
            }
        }
        if (dir.asyncValidator !== null) {
            const asyncValidators = getControlAsyncValidators(control);
            if (Array.isArray(asyncValidators) && asyncValidators.length > 0) {
                // Filter out directive async validator function.
                const updatedAsyncValidators = asyncValidators.filter(asyncValidator => asyncValidator !== dir.asyncValidator);
                if (updatedAsyncValidators.length !== asyncValidators.length) {
                    isControlUpdated = true;
                    control.setAsyncValidators(updatedAsyncValidators);
                }
            }
        }
    }
    if (handleOnValidatorChange) {
        // Clear onValidatorChange callbacks by providing a noop function.
        const noop = () => { };
        registerOnValidatorChange(dir._rawValidators, noop);
        registerOnValidatorChange(dir._rawAsyncValidators, noop);
    }
    return isControlUpdated;
}
function setUpViewChangePipeline(control, dir) {
    dir.valueAccessor.registerOnChange((newValue) => {
        control._pendingValue = newValue;
        control._pendingChange = true;
        control._pendingDirty = true;
        if (control.updateOn === 'change')
            updateControl(control, dir);
    });
}
function setUpBlurPipeline(control, dir) {
    dir.valueAccessor.registerOnTouched(() => {
        control._pendingTouched = true;
        if (control.updateOn === 'blur' && control._pendingChange)
            updateControl(control, dir);
        if (control.updateOn !== 'submit')
            control.markAsTouched();
    });
}
function updateControl(control, dir) {
    if (control._pendingDirty)
        control.markAsDirty();
    control.setValue(control._pendingValue, { emitModelToViewChange: false });
    dir.viewToModelUpdate(control._pendingValue);
    control._pendingChange = false;
}
function setUpModelChangePipeline(control, dir) {
    const onChange = (newValue, emitModelEvent) => {
        // control -> view
        dir.valueAccessor.writeValue(newValue);
        // control -> ngModel
        if (emitModelEvent)
            dir.viewToModelUpdate(newValue);
    };
    control.registerOnChange(onChange);
    // Register a callback function to cleanup onChange handler
    // from a control instance when a directive is destroyed.
    dir._registerOnDestroy(() => {
        control._unregisterOnChange(onChange);
    });
}
/**
 * Links a FormGroup or FormArray instance and corresponding Form directive by setting up validators
 * present in the view.
 *
 * @param control FormGroup or FormArray instance that should be linked.
 * @param dir Directive that provides view validators.
 */
export function setUpFormContainer(control, dir) {
    if (control == null && (typeof ngDevMode === 'undefined' || ngDevMode))
        _throwError(dir, 'Cannot find control with');
    setUpValidators(control, dir, /* handleOnValidatorChange */ false);
}
/**
 * Reverts the setup performed by the `setUpFormContainer` function.
 *
 * @param control FormGroup or FormArray instance that should be cleaned up.
 * @param dir Directive that provided view validators.
 * @returns true if a control was updated as a result of this action.
 */
export function cleanUpFormContainer(control, dir) {
    return cleanUpValidators(control, dir, /* handleOnValidatorChange */ false);
}
function _noControlError(dir) {
    return _throwError(dir, 'There is no FormControl instance attached to form control element with');
}
function _throwError(dir, message) {
    let messageEnd;
    if (dir.path.length > 1) {
        messageEnd = `path: '${dir.path.join(' -> ')}'`;
    }
    else if (dir.path[0]) {
        messageEnd = `name: '${dir.path}'`;
    }
    else {
        messageEnd = 'unspecified name attribute';
    }
    throw new Error(`${message} ${messageEnd}`);
}
export function isPropertyUpdated(changes, viewModel) {
    if (!changes.hasOwnProperty('model'))
        return false;
    const change = changes['model'];
    if (change.isFirstChange())
        return true;
    return !Object.is(viewModel, change.currentValue);
}
export function isBuiltInAccessor(valueAccessor) {
    // Check if a given value accessor is an instance of a class that directly extends
    // `BuiltInControlValueAccessor` one.
    return Object.getPrototypeOf(valueAccessor.constructor) === BuiltInControlValueAccessor;
}
export function syncPendingControls(form, directives) {
    form._syncPendingControls();
    directives.forEach(dir => {
        const control = dir.control;
        if (control.updateOn === 'submit' && control._pendingChange) {
            dir.viewToModelUpdate(control._pendingValue);
            control._pendingChange = false;
        }
    });
}
// TODO: vsavkin remove it once https://github.com/angular/angular/issues/3011 is implemented
export function selectValueAccessor(dir, valueAccessors) {
    if (!valueAccessors)
        return null;
    if (!Array.isArray(valueAccessors) && (typeof ngDevMode === 'undefined' || ngDevMode))
        _throwError(dir, 'Value accessor was not provided as an array for form control with');
    let defaultAccessor = undefined;
    let builtinAccessor = undefined;
    let customAccessor = undefined;
    valueAccessors.forEach((v) => {
        if (v.constructor === DefaultValueAccessor) {
            defaultAccessor = v;
        }
        else if (isBuiltInAccessor(v)) {
            if (builtinAccessor && (typeof ngDevMode === 'undefined' || ngDevMode))
                _throwError(dir, 'More than one built-in value accessor matches form control with');
            builtinAccessor = v;
        }
        else {
            if (customAccessor && (typeof ngDevMode === 'undefined' || ngDevMode))
                _throwError(dir, 'More than one custom value accessor matches form control with');
            customAccessor = v;
        }
    });
    if (customAccessor)
        return customAccessor;
    if (builtinAccessor)
        return builtinAccessor;
    if (defaultAccessor)
        return defaultAccessor;
    if (typeof ngDevMode === 'undefined' || ngDevMode) {
        _throwError(dir, 'No valid value accessor for form control with');
    }
    return null;
}
export function removeListItem(list, el) {
    const index = list.indexOf(el);
    if (index > -1)
        list.splice(index, 1);
}
// TODO(kara): remove after deprecation period
export function _ngModelWarning(name, type, instance, warningConfig) {
    if (warningConfig === 'never')
        return;
    if (((warningConfig === null || warningConfig === 'once') && !type._ngModelWarningSentOnce) ||
        (warningConfig === 'always' && !instance._ngModelWarningSent)) {
        ReactiveErrors.ngModelWarning(name);
        type._ngModelWarningSentOnce = true;
        instance._ngModelWarningSent = true;
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2hhcmVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvZm9ybXMvc3JjL2RpcmVjdGl2ZXMvc2hhcmVkLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRztBQUdILE9BQU8sRUFBQyx5QkFBeUIsRUFBRSxvQkFBb0IsRUFBRSxlQUFlLEVBQUMsTUFBTSxlQUFlLENBQUM7QUFLL0YsT0FBTyxFQUFDLDJCQUEyQixFQUF1QixNQUFNLDBCQUEwQixDQUFDO0FBQzNGLE9BQU8sRUFBQyxvQkFBb0IsRUFBQyxNQUFNLDBCQUEwQixDQUFDO0FBRzlELE9BQU8sRUFBQyxjQUFjLEVBQUMsTUFBTSxtQkFBbUIsQ0FBQztBQUlqRCxNQUFNLFVBQVUsV0FBVyxDQUFDLElBQWlCLEVBQUUsTUFBd0I7SUFDckUsT0FBTyxDQUFDLEdBQUcsTUFBTSxDQUFDLElBQUssRUFBRSxJQUFLLENBQUMsQ0FBQztBQUNsQyxDQUFDO0FBRUQ7Ozs7OztHQU1HO0FBQ0gsTUFBTSxVQUFVLFlBQVksQ0FBQyxPQUFvQixFQUFFLEdBQWM7SUFDL0QsSUFBSSxPQUFPLFNBQVMsS0FBSyxXQUFXLElBQUksU0FBUyxFQUFFO1FBQ2pELElBQUksQ0FBQyxPQUFPO1lBQUUsV0FBVyxDQUFDLEdBQUcsRUFBRSwwQkFBMEIsQ0FBQyxDQUFDO1FBQzNELElBQUksQ0FBQyxHQUFHLENBQUMsYUFBYTtZQUFFLFdBQVcsQ0FBQyxHQUFHLEVBQUUseUNBQXlDLENBQUMsQ0FBQztLQUNyRjtJQUVELGVBQWUsQ0FBQyxPQUFPLEVBQUUsR0FBRyxFQUFFLDZCQUE2QixDQUFDLElBQUksQ0FBQyxDQUFDO0lBRWxFLEdBQUcsQ0FBQyxhQUFjLENBQUMsVUFBVSxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsQ0FBQztJQUU3Qyx1QkFBdUIsQ0FBQyxPQUFPLEVBQUUsR0FBRyxDQUFDLENBQUM7SUFDdEMsd0JBQXdCLENBQUMsT0FBTyxFQUFFLEdBQUcsQ0FBQyxDQUFDO0lBRXZDLGlCQUFpQixDQUFDLE9BQU8sRUFBRSxHQUFHLENBQUMsQ0FBQztJQUVoQywwQkFBMEIsQ0FBQyxPQUFPLEVBQUUsR0FBRyxDQUFDLENBQUM7QUFDM0MsQ0FBQztBQUVEOzs7Ozs7Ozs7O0dBVUc7QUFDSCxNQUFNLFVBQVUsY0FBYyxDQUMxQixPQUF5QixFQUFFLEdBQWMsRUFDekMsa0NBQTJDLElBQUk7SUFDakQsTUFBTSxJQUFJLEdBQUcsR0FBRyxFQUFFO1FBQ2hCLElBQUksK0JBQStCLElBQUksQ0FBQyxPQUFPLFNBQVMsS0FBSyxXQUFXLElBQUksU0FBUyxDQUFDLEVBQUU7WUFDdEYsZUFBZSxDQUFDLEdBQUcsQ0FBQyxDQUFDO1NBQ3RCO0lBQ0gsQ0FBQyxDQUFDO0lBRUYsOEZBQThGO0lBQzlGLCtGQUErRjtJQUMvRixnR0FBZ0c7SUFDaEcsOEZBQThGO0lBQzlGLDhFQUE4RTtJQUM5RSxJQUFJLEdBQUcsQ0FBQyxhQUFhLEVBQUU7UUFDckIsR0FBRyxDQUFDLGFBQWEsQ0FBQyxnQkFBZ0IsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUN6QyxHQUFHLENBQUMsYUFBYSxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxDQUFDO0tBQzNDO0lBRUQsaUJBQWlCLENBQUMsT0FBTyxFQUFFLEdBQUcsRUFBRSw2QkFBNkIsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUVwRSxJQUFJLE9BQU8sRUFBRTtRQUNYLEdBQUcsQ0FBQyx5QkFBeUIsRUFBRSxDQUFDO1FBQ2hDLE9BQU8sQ0FBQywyQkFBMkIsQ0FBQyxHQUFHLEVBQUUsR0FBRSxDQUFDLENBQUMsQ0FBQztLQUMvQztBQUNILENBQUM7QUFFRCxTQUFTLHlCQUF5QixDQUFJLFVBQTJCLEVBQUUsUUFBb0I7SUFDckYsVUFBVSxDQUFDLE9BQU8sQ0FBQyxDQUFDLFNBQXdCLEVBQUUsRUFBRTtRQUM5QyxJQUFnQixTQUFVLENBQUMseUJBQXlCO1lBQ3RDLFNBQVUsQ0FBQyx5QkFBMEIsQ0FBQyxRQUFRLENBQUMsQ0FBQztJQUNoRSxDQUFDLENBQUMsQ0FBQztBQUNMLENBQUM7QUFFRDs7Ozs7O0dBTUc7QUFDSCxNQUFNLFVBQVUsMEJBQTBCLENBQUMsT0FBb0IsRUFBRSxHQUFjO0lBQzdFLElBQUksR0FBRyxDQUFDLGFBQWMsQ0FBQyxnQkFBZ0IsRUFBRTtRQUN2QyxNQUFNLGdCQUFnQixHQUFHLENBQUMsVUFBbUIsRUFBRSxFQUFFO1lBQy9DLEdBQUcsQ0FBQyxhQUFjLENBQUMsZ0JBQWlCLENBQUMsVUFBVSxDQUFDLENBQUM7UUFDbkQsQ0FBQyxDQUFDO1FBQ0YsT0FBTyxDQUFDLHdCQUF3QixDQUFDLGdCQUFnQixDQUFDLENBQUM7UUFFbkQsa0VBQWtFO1FBQ2xFLHlEQUF5RDtRQUN6RCxHQUFHLENBQUMsa0JBQWtCLENBQUMsR0FBRyxFQUFFO1lBQzFCLE9BQU8sQ0FBQywyQkFBMkIsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDO1FBQ3hELENBQUMsQ0FBQyxDQUFDO0tBQ0o7QUFDSCxDQUFDO0FBRUQ7Ozs7Ozs7O0dBUUc7QUFDSCxNQUFNLFVBQVUsZUFBZSxDQUMzQixPQUF3QixFQUFFLEdBQTZCLEVBQ3ZELHVCQUFnQztJQUNsQyxNQUFNLFVBQVUsR0FBRyxvQkFBb0IsQ0FBQyxPQUFPLENBQUMsQ0FBQztJQUNqRCxJQUFJLEdBQUcsQ0FBQyxTQUFTLEtBQUssSUFBSSxFQUFFO1FBQzFCLE9BQU8sQ0FBQyxhQUFhLENBQUMsZUFBZSxDQUFjLFVBQVUsRUFBRSxHQUFHLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQztLQUNoRjtTQUFNLElBQUksT0FBTyxVQUFVLEtBQUssVUFBVSxFQUFFO1FBQzNDLGtGQUFrRjtRQUNsRixxRkFBcUY7UUFDckYsd0ZBQXdGO1FBQ3hGLDRGQUE0RjtRQUM1RiwrRkFBK0Y7UUFDL0YsdUZBQXVGO1FBQ3ZGLDBCQUEwQjtRQUMxQixPQUFPLENBQUMsYUFBYSxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsQ0FBQztLQUNyQztJQUVELE1BQU0sZUFBZSxHQUFHLHlCQUF5QixDQUFDLE9BQU8sQ0FBQyxDQUFDO0lBQzNELElBQUksR0FBRyxDQUFDLGNBQWMsS0FBSyxJQUFJLEVBQUU7UUFDL0IsT0FBTyxDQUFDLGtCQUFrQixDQUN0QixlQUFlLENBQW1CLGVBQWUsRUFBRSxHQUFHLENBQUMsY0FBYyxDQUFDLENBQUMsQ0FBQztLQUM3RTtTQUFNLElBQUksT0FBTyxlQUFlLEtBQUssVUFBVSxFQUFFO1FBQ2hELE9BQU8sQ0FBQyxrQkFBa0IsQ0FBQyxDQUFDLGVBQWUsQ0FBQyxDQUFDLENBQUM7S0FDL0M7SUFFRCxvRkFBb0Y7SUFDcEYsSUFBSSx1QkFBdUIsRUFBRTtRQUMzQixNQUFNLGlCQUFpQixHQUFHLEdBQUcsRUFBRSxDQUFDLE9BQU8sQ0FBQyxzQkFBc0IsRUFBRSxDQUFDO1FBQ2pFLHlCQUF5QixDQUFjLEdBQUcsQ0FBQyxjQUFjLEVBQUUsaUJBQWlCLENBQUMsQ0FBQztRQUM5RSx5QkFBeUIsQ0FBbUIsR0FBRyxDQUFDLG1CQUFtQixFQUFFLGlCQUFpQixDQUFDLENBQUM7S0FDekY7QUFDSCxDQUFDO0FBRUQ7Ozs7Ozs7Ozs7R0FVRztBQUNILE1BQU0sVUFBVSxpQkFBaUIsQ0FDN0IsT0FBNkIsRUFBRSxHQUE2QixFQUM1RCx1QkFBZ0M7SUFDbEMsSUFBSSxnQkFBZ0IsR0FBRyxLQUFLLENBQUM7SUFDN0IsSUFBSSxPQUFPLEtBQUssSUFBSSxFQUFFO1FBQ3BCLElBQUksR0FBRyxDQUFDLFNBQVMsS0FBSyxJQUFJLEVBQUU7WUFDMUIsTUFBTSxVQUFVLEdBQUcsb0JBQW9CLENBQUMsT0FBTyxDQUFDLENBQUM7WUFDakQsSUFBSSxLQUFLLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxJQUFJLFVBQVUsQ0FBQyxNQUFNLEdBQUcsQ0FBQyxFQUFFO2dCQUN0RCwyQ0FBMkM7Z0JBQzNDLE1BQU0saUJBQWlCLEdBQUcsVUFBVSxDQUFDLE1BQU0sQ0FBQyxTQUFTLENBQUMsRUFBRSxDQUFDLFNBQVMsS0FBSyxHQUFHLENBQUMsU0FBUyxDQUFDLENBQUM7Z0JBQ3RGLElBQUksaUJBQWlCLENBQUMsTUFBTSxLQUFLLFVBQVUsQ0FBQyxNQUFNLEVBQUU7b0JBQ2xELGdCQUFnQixHQUFHLElBQUksQ0FBQztvQkFDeEIsT0FBTyxDQUFDLGFBQWEsQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDO2lCQUMxQzthQUNGO1NBQ0Y7UUFFRCxJQUFJLEdBQUcsQ0FBQyxjQUFjLEtBQUssSUFBSSxFQUFFO1lBQy9CLE1BQU0sZUFBZSxHQUFHLHlCQUF5QixDQUFDLE9BQU8sQ0FBQyxDQUFDO1lBQzNELElBQUksS0FBSyxDQUFDLE9BQU8sQ0FBQyxlQUFlLENBQUMsSUFBSSxlQUFlLENBQUMsTUFBTSxHQUFHLENBQUMsRUFBRTtnQkFDaEUsaURBQWlEO2dCQUNqRCxNQUFNLHNCQUFzQixHQUN4QixlQUFlLENBQUMsTUFBTSxDQUFDLGNBQWMsQ0FBQyxFQUFFLENBQUMsY0FBYyxLQUFLLEdBQUcsQ0FBQyxjQUFjLENBQUMsQ0FBQztnQkFDcEYsSUFBSSxzQkFBc0IsQ0FBQyxNQUFNLEtBQUssZUFBZSxDQUFDLE1BQU0sRUFBRTtvQkFDNUQsZ0JBQWdCLEdBQUcsSUFBSSxDQUFDO29CQUN4QixPQUFPLENBQUMsa0JBQWtCLENBQUMsc0JBQXNCLENBQUMsQ0FBQztpQkFDcEQ7YUFDRjtTQUNGO0tBQ0Y7SUFFRCxJQUFJLHVCQUF1QixFQUFFO1FBQzNCLGtFQUFrRTtRQUNsRSxNQUFNLElBQUksR0FBRyxHQUFHLEVBQUUsR0FBRSxDQUFDLENBQUM7UUFDdEIseUJBQXlCLENBQWMsR0FBRyxDQUFDLGNBQWMsRUFBRSxJQUFJLENBQUMsQ0FBQztRQUNqRSx5QkFBeUIsQ0FBbUIsR0FBRyxDQUFDLG1CQUFtQixFQUFFLElBQUksQ0FBQyxDQUFDO0tBQzVFO0lBRUQsT0FBTyxnQkFBZ0IsQ0FBQztBQUMxQixDQUFDO0FBRUQsU0FBUyx1QkFBdUIsQ0FBQyxPQUFvQixFQUFFLEdBQWM7SUFDbkUsR0FBRyxDQUFDLGFBQWMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLFFBQWEsRUFBRSxFQUFFO1FBQ3BELE9BQU8sQ0FBQyxhQUFhLEdBQUcsUUFBUSxDQUFDO1FBQ2pDLE9BQU8sQ0FBQyxjQUFjLEdBQUcsSUFBSSxDQUFDO1FBQzlCLE9BQU8sQ0FBQyxhQUFhLEdBQUcsSUFBSSxDQUFDO1FBRTdCLElBQUksT0FBTyxDQUFDLFFBQVEsS0FBSyxRQUFRO1lBQUUsYUFBYSxDQUFDLE9BQU8sRUFBRSxHQUFHLENBQUMsQ0FBQztJQUNqRSxDQUFDLENBQUMsQ0FBQztBQUNMLENBQUM7QUFFRCxTQUFTLGlCQUFpQixDQUFDLE9BQW9CLEVBQUUsR0FBYztJQUM3RCxHQUFHLENBQUMsYUFBYyxDQUFDLGlCQUFpQixDQUFDLEdBQUcsRUFBRTtRQUN4QyxPQUFPLENBQUMsZUFBZSxHQUFHLElBQUksQ0FBQztRQUUvQixJQUFJLE9BQU8sQ0FBQyxRQUFRLEtBQUssTUFBTSxJQUFJLE9BQU8sQ0FBQyxjQUFjO1lBQUUsYUFBYSxDQUFDLE9BQU8sRUFBRSxHQUFHLENBQUMsQ0FBQztRQUN2RixJQUFJLE9BQU8sQ0FBQyxRQUFRLEtBQUssUUFBUTtZQUFFLE9BQU8sQ0FBQyxhQUFhLEVBQUUsQ0FBQztJQUM3RCxDQUFDLENBQUMsQ0FBQztBQUNMLENBQUM7QUFFRCxTQUFTLGFBQWEsQ0FBQyxPQUFvQixFQUFFLEdBQWM7SUFDekQsSUFBSSxPQUFPLENBQUMsYUFBYTtRQUFFLE9BQU8sQ0FBQyxXQUFXLEVBQUUsQ0FBQztJQUNqRCxPQUFPLENBQUMsUUFBUSxDQUFDLE9BQU8sQ0FBQyxhQUFhLEVBQUUsRUFBQyxxQkFBcUIsRUFBRSxLQUFLLEVBQUMsQ0FBQyxDQUFDO0lBQ3hFLEdBQUcsQ0FBQyxpQkFBaUIsQ0FBQyxPQUFPLENBQUMsYUFBYSxDQUFDLENBQUM7SUFDN0MsT0FBTyxDQUFDLGNBQWMsR0FBRyxLQUFLLENBQUM7QUFDakMsQ0FBQztBQUVELFNBQVMsd0JBQXdCLENBQUMsT0FBb0IsRUFBRSxHQUFjO0lBQ3BFLE1BQU0sUUFBUSxHQUFHLENBQUMsUUFBYSxFQUFFLGNBQXVCLEVBQUUsRUFBRTtRQUMxRCxrQkFBa0I7UUFDbEIsR0FBRyxDQUFDLGFBQWMsQ0FBQyxVQUFVLENBQUMsUUFBUSxDQUFDLENBQUM7UUFFeEMscUJBQXFCO1FBQ3JCLElBQUksY0FBYztZQUFFLEdBQUcsQ0FBQyxpQkFBaUIsQ0FBQyxRQUFRLENBQUMsQ0FBQztJQUN0RCxDQUFDLENBQUM7SUFDRixPQUFPLENBQUMsZ0JBQWdCLENBQUMsUUFBUSxDQUFDLENBQUM7SUFFbkMsMkRBQTJEO0lBQzNELHlEQUF5RDtJQUN6RCxHQUFHLENBQUMsa0JBQWtCLENBQUMsR0FBRyxFQUFFO1FBQzFCLE9BQU8sQ0FBQyxtQkFBbUIsQ0FBQyxRQUFRLENBQUMsQ0FBQztJQUN4QyxDQUFDLENBQUMsQ0FBQztBQUNMLENBQUM7QUFFRDs7Ozs7O0dBTUc7QUFDSCxNQUFNLFVBQVUsa0JBQWtCLENBQzlCLE9BQTRCLEVBQUUsR0FBNkM7SUFDN0UsSUFBSSxPQUFPLElBQUksSUFBSSxJQUFJLENBQUMsT0FBTyxTQUFTLEtBQUssV0FBVyxJQUFJLFNBQVMsQ0FBQztRQUNwRSxXQUFXLENBQUMsR0FBRyxFQUFFLDBCQUEwQixDQUFDLENBQUM7SUFDL0MsZUFBZSxDQUFDLE9BQU8sRUFBRSxHQUFHLEVBQUUsNkJBQTZCLENBQUMsS0FBSyxDQUFDLENBQUM7QUFDckUsQ0FBQztBQUVEOzs7Ozs7R0FNRztBQUNILE1BQU0sVUFBVSxvQkFBb0IsQ0FDaEMsT0FBNEIsRUFBRSxHQUE2QztJQUM3RSxPQUFPLGlCQUFpQixDQUFDLE9BQU8sRUFBRSxHQUFHLEVBQUUsNkJBQTZCLENBQUMsS0FBSyxDQUFDLENBQUM7QUFDOUUsQ0FBQztBQUVELFNBQVMsZUFBZSxDQUFDLEdBQWM7SUFDckMsT0FBTyxXQUFXLENBQUMsR0FBRyxFQUFFLHdFQUF3RSxDQUFDLENBQUM7QUFDcEcsQ0FBQztBQUVELFNBQVMsV0FBVyxDQUFDLEdBQTZCLEVBQUUsT0FBZTtJQUNqRSxJQUFJLFVBQWtCLENBQUM7SUFDdkIsSUFBSSxHQUFHLENBQUMsSUFBSyxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUU7UUFDeEIsVUFBVSxHQUFHLFVBQVUsR0FBRyxDQUFDLElBQUssQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQztLQUNsRDtTQUFNLElBQUksR0FBRyxDQUFDLElBQUssQ0FBQyxDQUFDLENBQUMsRUFBRTtRQUN2QixVQUFVLEdBQUcsVUFBVSxHQUFHLENBQUMsSUFBSSxHQUFHLENBQUM7S0FDcEM7U0FBTTtRQUNMLFVBQVUsR0FBRyw0QkFBNEIsQ0FBQztLQUMzQztJQUNELE1BQU0sSUFBSSxLQUFLLENBQUMsR0FBRyxPQUFPLElBQUksVUFBVSxFQUFFLENBQUMsQ0FBQztBQUM5QyxDQUFDO0FBRUQsTUFBTSxVQUFVLGlCQUFpQixDQUFDLE9BQTZCLEVBQUUsU0FBYztJQUM3RSxJQUFJLENBQUMsT0FBTyxDQUFDLGNBQWMsQ0FBQyxPQUFPLENBQUM7UUFBRSxPQUFPLEtBQUssQ0FBQztJQUNuRCxNQUFNLE1BQU0sR0FBRyxPQUFPLENBQUMsT0FBTyxDQUFDLENBQUM7SUFFaEMsSUFBSSxNQUFNLENBQUMsYUFBYSxFQUFFO1FBQUUsT0FBTyxJQUFJLENBQUM7SUFDeEMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsU0FBUyxFQUFFLE1BQU0sQ0FBQyxZQUFZLENBQUMsQ0FBQztBQUNwRCxDQUFDO0FBRUQsTUFBTSxVQUFVLGlCQUFpQixDQUFDLGFBQW1DO0lBQ25FLGtGQUFrRjtJQUNsRixxQ0FBcUM7SUFDckMsT0FBTyxNQUFNLENBQUMsY0FBYyxDQUFDLGFBQWEsQ0FBQyxXQUFXLENBQUMsS0FBSywyQkFBMkIsQ0FBQztBQUMxRixDQUFDO0FBRUQsTUFBTSxVQUFVLG1CQUFtQixDQUFDLElBQWUsRUFBRSxVQUF1QjtJQUMxRSxJQUFJLENBQUMsb0JBQW9CLEVBQUUsQ0FBQztJQUM1QixVQUFVLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFFO1FBQ3ZCLE1BQU0sT0FBTyxHQUFHLEdBQUcsQ0FBQyxPQUFzQixDQUFDO1FBQzNDLElBQUksT0FBTyxDQUFDLFFBQVEsS0FBSyxRQUFRLElBQUksT0FBTyxDQUFDLGNBQWMsRUFBRTtZQUMzRCxHQUFHLENBQUMsaUJBQWlCLENBQUMsT0FBTyxDQUFDLGFBQWEsQ0FBQyxDQUFDO1lBQzdDLE9BQU8sQ0FBQyxjQUFjLEdBQUcsS0FBSyxDQUFDO1NBQ2hDO0lBQ0gsQ0FBQyxDQUFDLENBQUM7QUFDTCxDQUFDO0FBRUQsNkZBQTZGO0FBQzdGLE1BQU0sVUFBVSxtQkFBbUIsQ0FDL0IsR0FBYyxFQUFFLGNBQXNDO0lBQ3hELElBQUksQ0FBQyxjQUFjO1FBQUUsT0FBTyxJQUFJLENBQUM7SUFFakMsSUFBSSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsY0FBYyxDQUFDLElBQUksQ0FBQyxPQUFPLFNBQVMsS0FBSyxXQUFXLElBQUksU0FBUyxDQUFDO1FBQ25GLFdBQVcsQ0FBQyxHQUFHLEVBQUUsbUVBQW1FLENBQUMsQ0FBQztJQUV4RixJQUFJLGVBQWUsR0FBbUMsU0FBUyxDQUFDO0lBQ2hFLElBQUksZUFBZSxHQUFtQyxTQUFTLENBQUM7SUFDaEUsSUFBSSxjQUFjLEdBQW1DLFNBQVMsQ0FBQztJQUUvRCxjQUFjLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBdUIsRUFBRSxFQUFFO1FBQ2pELElBQUksQ0FBQyxDQUFDLFdBQVcsS0FBSyxvQkFBb0IsRUFBRTtZQUMxQyxlQUFlLEdBQUcsQ0FBQyxDQUFDO1NBRXJCO2FBQU0sSUFBSSxpQkFBaUIsQ0FBQyxDQUFDLENBQUMsRUFBRTtZQUMvQixJQUFJLGVBQWUsSUFBSSxDQUFDLE9BQU8sU0FBUyxLQUFLLFdBQVcsSUFBSSxTQUFTLENBQUM7Z0JBQ3BFLFdBQVcsQ0FBQyxHQUFHLEVBQUUsaUVBQWlFLENBQUMsQ0FBQztZQUN0RixlQUFlLEdBQUcsQ0FBQyxDQUFDO1NBRXJCO2FBQU07WUFDTCxJQUFJLGNBQWMsSUFBSSxDQUFDLE9BQU8sU0FBUyxLQUFLLFdBQVcsSUFBSSxTQUFTLENBQUM7Z0JBQ25FLFdBQVcsQ0FBQyxHQUFHLEVBQUUsK0RBQStELENBQUMsQ0FBQztZQUNwRixjQUFjLEdBQUcsQ0FBQyxDQUFDO1NBQ3BCO0lBQ0gsQ0FBQyxDQUFDLENBQUM7SUFFSCxJQUFJLGNBQWM7UUFBRSxPQUFPLGNBQWMsQ0FBQztJQUMxQyxJQUFJLGVBQWU7UUFBRSxPQUFPLGVBQWUsQ0FBQztJQUM1QyxJQUFJLGVBQWU7UUFBRSxPQUFPLGVBQWUsQ0FBQztJQUU1QyxJQUFJLE9BQU8sU0FBUyxLQUFLLFdBQVcsSUFBSSxTQUFTLEVBQUU7UUFDakQsV0FBVyxDQUFDLEdBQUcsRUFBRSwrQ0FBK0MsQ0FBQyxDQUFDO0tBQ25FO0lBQ0QsT0FBTyxJQUFJLENBQUM7QUFDZCxDQUFDO0FBRUQsTUFBTSxVQUFVLGNBQWMsQ0FBSSxJQUFTLEVBQUUsRUFBSztJQUNoRCxNQUFNLEtBQUssR0FBRyxJQUFJLENBQUMsT0FBTyxDQUFDLEVBQUUsQ0FBQyxDQUFDO0lBQy9CLElBQUksS0FBSyxHQUFHLENBQUMsQ0FBQztRQUFFLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLENBQUMsQ0FBQyxDQUFDO0FBQ3hDLENBQUM7QUFFRCw4Q0FBOEM7QUFDOUMsTUFBTSxVQUFVLGVBQWUsQ0FDM0IsSUFBWSxFQUFFLElBQXdDLEVBQ3RELFFBQXdDLEVBQUUsYUFBMEI7SUFDdEUsSUFBSSxhQUFhLEtBQUssT0FBTztRQUFFLE9BQU87SUFFdEMsSUFBSSxDQUFDLENBQUMsYUFBYSxLQUFLLElBQUksSUFBSSxhQUFhLEtBQUssTUFBTSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsdUJBQXVCLENBQUM7UUFDdkYsQ0FBQyxhQUFhLEtBQUssUUFBUSxJQUFJLENBQUMsUUFBUSxDQUFDLG1CQUFtQixDQUFDLEVBQUU7UUFDakUsY0FBYyxDQUFDLGNBQWMsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUNwQyxJQUFJLENBQUMsdUJBQXVCLEdBQUcsSUFBSSxDQUFDO1FBQ3BDLFFBQVEsQ0FBQyxtQkFBbUIsR0FBRyxJQUFJLENBQUM7S0FDckM7QUFDSCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7QWJzdHJhY3RDb250cm9sLCBGb3JtQXJyYXksIEZvcm1Db250cm9sLCBGb3JtR3JvdXB9IGZyb20gJy4uL21vZGVsJztcbmltcG9ydCB7Z2V0Q29udHJvbEFzeW5jVmFsaWRhdG9ycywgZ2V0Q29udHJvbFZhbGlkYXRvcnMsIG1lcmdlVmFsaWRhdG9yc30gZnJvbSAnLi4vdmFsaWRhdG9ycyc7XG5cbmltcG9ydCB7QWJzdHJhY3RDb250cm9sRGlyZWN0aXZlfSBmcm9tICcuL2Fic3RyYWN0X2NvbnRyb2xfZGlyZWN0aXZlJztcbmltcG9ydCB7QWJzdHJhY3RGb3JtR3JvdXBEaXJlY3RpdmV9IGZyb20gJy4vYWJzdHJhY3RfZm9ybV9ncm91cF9kaXJlY3RpdmUnO1xuaW1wb3J0IHtDb250cm9sQ29udGFpbmVyfSBmcm9tICcuL2NvbnRyb2xfY29udGFpbmVyJztcbmltcG9ydCB7QnVpbHRJbkNvbnRyb2xWYWx1ZUFjY2Vzc29yLCBDb250cm9sVmFsdWVBY2Nlc3Nvcn0gZnJvbSAnLi9jb250cm9sX3ZhbHVlX2FjY2Vzc29yJztcbmltcG9ydCB7RGVmYXVsdFZhbHVlQWNjZXNzb3J9IGZyb20gJy4vZGVmYXVsdF92YWx1ZV9hY2Nlc3Nvcic7XG5pbXBvcnQge05nQ29udHJvbH0gZnJvbSAnLi9uZ19jb250cm9sJztcbmltcG9ydCB7Rm9ybUFycmF5TmFtZX0gZnJvbSAnLi9yZWFjdGl2ZV9kaXJlY3RpdmVzL2Zvcm1fZ3JvdXBfbmFtZSc7XG5pbXBvcnQge1JlYWN0aXZlRXJyb3JzfSBmcm9tICcuL3JlYWN0aXZlX2Vycm9ycyc7XG5pbXBvcnQge0FzeW5jVmFsaWRhdG9yRm4sIFZhbGlkYXRvciwgVmFsaWRhdG9yRm59IGZyb20gJy4vdmFsaWRhdG9ycyc7XG5cblxuZXhwb3J0IGZ1bmN0aW9uIGNvbnRyb2xQYXRoKG5hbWU6IHN0cmluZ3xudWxsLCBwYXJlbnQ6IENvbnRyb2xDb250YWluZXIpOiBzdHJpbmdbXSB7XG4gIHJldHVybiBbLi4ucGFyZW50LnBhdGghLCBuYW1lIV07XG59XG5cbi8qKlxuICogTGlua3MgYSBGb3JtIGNvbnRyb2wgYW5kIGEgRm9ybSBkaXJlY3RpdmUgYnkgc2V0dGluZyB1cCBjYWxsYmFja3MgKHN1Y2ggYXMgYG9uQ2hhbmdlYCkgb24gYm90aFxuICogaW5zdGFuY2VzLiBUaGlzIGZ1bmN0aW9uIGlzIHR5cGljYWxseSBpbnZva2VkIHdoZW4gZm9ybSBkaXJlY3RpdmUgaXMgYmVpbmcgaW5pdGlhbGl6ZWQuXG4gKlxuICogQHBhcmFtIGNvbnRyb2wgRm9ybSBjb250cm9sIGluc3RhbmNlIHRoYXQgc2hvdWxkIGJlIGxpbmtlZC5cbiAqIEBwYXJhbSBkaXIgRGlyZWN0aXZlIHRoYXQgc2hvdWxkIGJlIGxpbmtlZCB3aXRoIGEgZ2l2ZW4gY29udHJvbC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHNldFVwQ29udHJvbChjb250cm9sOiBGb3JtQ29udHJvbCwgZGlyOiBOZ0NvbnRyb2wpOiB2b2lkIHtcbiAgaWYgKHR5cGVvZiBuZ0Rldk1vZGUgPT09ICd1bmRlZmluZWQnIHx8IG5nRGV2TW9kZSkge1xuICAgIGlmICghY29udHJvbCkgX3Rocm93RXJyb3IoZGlyLCAnQ2Fubm90IGZpbmQgY29udHJvbCB3aXRoJyk7XG4gICAgaWYgKCFkaXIudmFsdWVBY2Nlc3NvcikgX3Rocm93RXJyb3IoZGlyLCAnTm8gdmFsdWUgYWNjZXNzb3IgZm9yIGZvcm0gY29udHJvbCB3aXRoJyk7XG4gIH1cblxuICBzZXRVcFZhbGlkYXRvcnMoY29udHJvbCwgZGlyLCAvKiBoYW5kbGVPblZhbGlkYXRvckNoYW5nZSAqLyB0cnVlKTtcblxuICBkaXIudmFsdWVBY2Nlc3NvciEud3JpdGVWYWx1ZShjb250cm9sLnZhbHVlKTtcblxuICBzZXRVcFZpZXdDaGFuZ2VQaXBlbGluZShjb250cm9sLCBkaXIpO1xuICBzZXRVcE1vZGVsQ2hhbmdlUGlwZWxpbmUoY29udHJvbCwgZGlyKTtcblxuICBzZXRVcEJsdXJQaXBlbGluZShjb250cm9sLCBkaXIpO1xuXG4gIHNldFVwRGlzYWJsZWRDaGFuZ2VIYW5kbGVyKGNvbnRyb2wsIGRpcik7XG59XG5cbi8qKlxuICogUmV2ZXJ0cyBjb25maWd1cmF0aW9uIHBlcmZvcm1lZCBieSB0aGUgYHNldFVwQ29udHJvbGAgY29udHJvbCBmdW5jdGlvbi5cbiAqIEVmZmVjdGl2ZWx5IGRpc2Nvbm5lY3RzIGZvcm0gY29udHJvbCB3aXRoIGEgZ2l2ZW4gZm9ybSBkaXJlY3RpdmUuXG4gKiBUaGlzIGZ1bmN0aW9uIGlzIHR5cGljYWxseSBpbnZva2VkIHdoZW4gY29ycmVzcG9uZGluZyBmb3JtIGRpcmVjdGl2ZSBpcyBiZWluZyBkZXN0cm95ZWQuXG4gKlxuICogQHBhcmFtIGNvbnRyb2wgRm9ybSBjb250cm9sIHdoaWNoIHNob3VsZCBiZSBjbGVhbmVkIHVwLlxuICogQHBhcmFtIGRpciBEaXJlY3RpdmUgdGhhdCBzaG91bGQgYmUgZGlzY29ubmVjdGVkIGZyb20gYSBnaXZlbiBjb250cm9sLlxuICogQHBhcmFtIHZhbGlkYXRlQ29udHJvbFByZXNlbmNlT25DaGFuZ2UgRmxhZyB0aGF0IGluZGljYXRlcyB3aGV0aGVyIG9uQ2hhbmdlIGhhbmRsZXIgc2hvdWxkXG4gKiAgICAgY29udGFpbiBhc3NlcnRzIHRvIHZlcmlmeSB0aGF0IGl0J3Mgbm90IGNhbGxlZCBvbmNlIGRpcmVjdGl2ZSBpcyBkZXN0cm95ZWQuIFdlIG5lZWQgdGhpcyBmbGFnXG4gKiAgICAgdG8gYXZvaWQgcG90ZW50aWFsbHkgYnJlYWtpbmcgY2hhbmdlcyBjYXVzZWQgYnkgYmV0dGVyIGNvbnRyb2wgY2xlYW51cCBpbnRyb2R1Y2VkIGluICMzOTIzNS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNsZWFuVXBDb250cm9sKFxuICAgIGNvbnRyb2w6IEZvcm1Db250cm9sfG51bGwsIGRpcjogTmdDb250cm9sLFxuICAgIHZhbGlkYXRlQ29udHJvbFByZXNlbmNlT25DaGFuZ2U6IGJvb2xlYW4gPSB0cnVlKTogdm9pZCB7XG4gIGNvbnN0IG5vb3AgPSAoKSA9PiB7XG4gICAgaWYgKHZhbGlkYXRlQ29udHJvbFByZXNlbmNlT25DaGFuZ2UgJiYgKHR5cGVvZiBuZ0Rldk1vZGUgPT09ICd1bmRlZmluZWQnIHx8IG5nRGV2TW9kZSkpIHtcbiAgICAgIF9ub0NvbnRyb2xFcnJvcihkaXIpO1xuICAgIH1cbiAgfTtcblxuICAvLyBUaGUgYHZhbHVlQWNjZXNzb3JgIGZpZWxkIGlzIHR5cGljYWxseSBkZWZpbmVkIG9uIEZyb21Db250cm9sIGFuZCBGb3JtQ29udHJvbE5hbWUgZGlyZWN0aXZlXG4gIC8vIGluc3RhbmNlcyBhbmQgdGhlcmUgaXMgYSBsb2dpYyBpbiBgc2VsZWN0VmFsdWVBY2Nlc3NvcmAgZnVuY3Rpb24gdGhhdCB0aHJvd3MgaWYgaXQncyBub3QgdGhlXG4gIC8vIGNhc2UuIFdlIHN0aWxsIGNoZWNrIHRoZSBwcmVzZW5jZSBvZiBgdmFsdWVBY2Nlc3NvcmAgYmVmb3JlIGludm9raW5nIGl0cyBtZXRob2RzIHRvIG1ha2Ugc3VyZVxuICAvLyB0aGF0IGNsZWFudXAgd29ya3MgY29ycmVjdGx5IGlmIGFwcCBjb2RlIG9yIHRlc3RzIGFyZSBzZXR1cCB0byBpZ25vcmUgdGhlIGVycm9yIHRocm93biBmcm9tXG4gIC8vIGBzZWxlY3RWYWx1ZUFjY2Vzc29yYC4gU2VlIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIvaXNzdWVzLzQwNTIxLlxuICBpZiAoZGlyLnZhbHVlQWNjZXNzb3IpIHtcbiAgICBkaXIudmFsdWVBY2Nlc3Nvci5yZWdpc3Rlck9uQ2hhbmdlKG5vb3ApO1xuICAgIGRpci52YWx1ZUFjY2Vzc29yLnJlZ2lzdGVyT25Ub3VjaGVkKG5vb3ApO1xuICB9XG5cbiAgY2xlYW5VcFZhbGlkYXRvcnMoY29udHJvbCwgZGlyLCAvKiBoYW5kbGVPblZhbGlkYXRvckNoYW5nZSAqLyB0cnVlKTtcblxuICBpZiAoY29udHJvbCkge1xuICAgIGRpci5faW52b2tlT25EZXN0cm95Q2FsbGJhY2tzKCk7XG4gICAgY29udHJvbC5fcmVnaXN0ZXJPbkNvbGxlY3Rpb25DaGFuZ2UoKCkgPT4ge30pO1xuICB9XG59XG5cbmZ1bmN0aW9uIHJlZ2lzdGVyT25WYWxpZGF0b3JDaGFuZ2U8Vj4odmFsaWRhdG9yczogKFZ8VmFsaWRhdG9yKVtdLCBvbkNoYW5nZTogKCkgPT4gdm9pZCk6IHZvaWQge1xuICB2YWxpZGF0b3JzLmZvckVhY2goKHZhbGlkYXRvcjogKFZ8VmFsaWRhdG9yKSkgPT4ge1xuICAgIGlmICgoPFZhbGlkYXRvcj52YWxpZGF0b3IpLnJlZ2lzdGVyT25WYWxpZGF0b3JDaGFuZ2UpXG4gICAgICAoPFZhbGlkYXRvcj52YWxpZGF0b3IpLnJlZ2lzdGVyT25WYWxpZGF0b3JDaGFuZ2UhKG9uQ2hhbmdlKTtcbiAgfSk7XG59XG5cbi8qKlxuICogU2V0cyB1cCBkaXNhYmxlZCBjaGFuZ2UgaGFuZGxlciBmdW5jdGlvbiBvbiBhIGdpdmVuIGZvcm0gY29udHJvbCBpZiBDb250cm9sVmFsdWVBY2Nlc3NvclxuICogYXNzb2NpYXRlZCB3aXRoIGEgZ2l2ZW4gZGlyZWN0aXZlIGluc3RhbmNlIHN1cHBvcnRzIHRoZSBgc2V0RGlzYWJsZWRTdGF0ZWAgY2FsbC5cbiAqXG4gKiBAcGFyYW0gY29udHJvbCBGb3JtIGNvbnRyb2wgd2hlcmUgZGlzYWJsZWQgY2hhbmdlIGhhbmRsZXIgc2hvdWxkIGJlIHNldHVwLlxuICogQHBhcmFtIGRpciBDb3JyZXNwb25kaW5nIGRpcmVjdGl2ZSBpbnN0YW5jZSBhc3NvY2lhdGVkIHdpdGggdGhpcyBjb250cm9sLlxuICovXG5leHBvcnQgZnVuY3Rpb24gc2V0VXBEaXNhYmxlZENoYW5nZUhhbmRsZXIoY29udHJvbDogRm9ybUNvbnRyb2wsIGRpcjogTmdDb250cm9sKTogdm9pZCB7XG4gIGlmIChkaXIudmFsdWVBY2Nlc3NvciEuc2V0RGlzYWJsZWRTdGF0ZSkge1xuICAgIGNvbnN0IG9uRGlzYWJsZWRDaGFuZ2UgPSAoaXNEaXNhYmxlZDogYm9vbGVhbikgPT4ge1xuICAgICAgZGlyLnZhbHVlQWNjZXNzb3IhLnNldERpc2FibGVkU3RhdGUhKGlzRGlzYWJsZWQpO1xuICAgIH07XG4gICAgY29udHJvbC5yZWdpc3Rlck9uRGlzYWJsZWRDaGFuZ2Uob25EaXNhYmxlZENoYW5nZSk7XG5cbiAgICAvLyBSZWdpc3RlciBhIGNhbGxiYWNrIGZ1bmN0aW9uIHRvIGNsZWFudXAgZGlzYWJsZWQgY2hhbmdlIGhhbmRsZXJcbiAgICAvLyBmcm9tIGEgY29udHJvbCBpbnN0YW5jZSB3aGVuIGEgZGlyZWN0aXZlIGlzIGRlc3Ryb3llZC5cbiAgICBkaXIuX3JlZ2lzdGVyT25EZXN0cm95KCgpID0+IHtcbiAgICAgIGNvbnRyb2wuX3VucmVnaXN0ZXJPbkRpc2FibGVkQ2hhbmdlKG9uRGlzYWJsZWRDaGFuZ2UpO1xuICAgIH0pO1xuICB9XG59XG5cbi8qKlxuICogU2V0cyB1cCBzeW5jIGFuZCBhc3luYyBkaXJlY3RpdmUgdmFsaWRhdG9ycyBvbiBwcm92aWRlZCBmb3JtIGNvbnRyb2wuXG4gKiBUaGlzIGZ1bmN0aW9uIG1lcmdlcyB2YWxpZGF0b3JzIGZyb20gdGhlIGRpcmVjdGl2ZSBpbnRvIHRoZSB2YWxpZGF0b3JzIG9mIHRoZSBjb250cm9sLlxuICpcbiAqIEBwYXJhbSBjb250cm9sIEZvcm0gY29udHJvbCB3aGVyZSBkaXJlY3RpdmUgdmFsaWRhdG9ycyBzaG91bGQgYmUgc2V0dXAuXG4gKiBAcGFyYW0gZGlyIERpcmVjdGl2ZSBpbnN0YW5jZSB0aGF0IGNvbnRhaW5zIHZhbGlkYXRvcnMgdG8gYmUgc2V0dXAuXG4gKiBAcGFyYW0gaGFuZGxlT25WYWxpZGF0b3JDaGFuZ2UgRmxhZyB0aGF0IGRldGVybWluZXMgd2hldGhlciBkaXJlY3RpdmUgdmFsaWRhdG9ycyBzaG91bGQgYmUgc2V0dXBcbiAqICAgICB0byBoYW5kbGUgdmFsaWRhdG9yIGlucHV0IGNoYW5nZS5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHNldFVwVmFsaWRhdG9ycyhcbiAgICBjb250cm9sOiBBYnN0cmFjdENvbnRyb2wsIGRpcjogQWJzdHJhY3RDb250cm9sRGlyZWN0aXZlLFxuICAgIGhhbmRsZU9uVmFsaWRhdG9yQ2hhbmdlOiBib29sZWFuKTogdm9pZCB7XG4gIGNvbnN0IHZhbGlkYXRvcnMgPSBnZXRDb250cm9sVmFsaWRhdG9ycyhjb250cm9sKTtcbiAgaWYgKGRpci52YWxpZGF0b3IgIT09IG51bGwpIHtcbiAgICBjb250cm9sLnNldFZhbGlkYXRvcnMobWVyZ2VWYWxpZGF0b3JzPFZhbGlkYXRvckZuPih2YWxpZGF0b3JzLCBkaXIudmFsaWRhdG9yKSk7XG4gIH0gZWxzZSBpZiAodHlwZW9mIHZhbGlkYXRvcnMgPT09ICdmdW5jdGlvbicpIHtcbiAgICAvLyBJZiBzeW5jIHZhbGlkYXRvcnMgYXJlIHJlcHJlc2VudGVkIGJ5IGEgc2luZ2xlIHZhbGlkYXRvciBmdW5jdGlvbiwgd2UgZm9yY2UgdGhlXG4gICAgLy8gYFZhbGlkYXRvcnMuY29tcG9zZWAgY2FsbCB0byBoYXBwZW4gYnkgZXhlY3V0aW5nIHRoZSBgc2V0VmFsaWRhdG9yc2AgZnVuY3Rpb24gd2l0aFxuICAgIC8vIGFuIGFycmF5IHRoYXQgY29udGFpbnMgdGhhdCBmdW5jdGlvbi4gV2UgbmVlZCB0aGlzIHRvIGF2b2lkIHBvc3NpYmxlIGRpc2NyZXBhbmNpZXMgaW5cbiAgICAvLyB2YWxpZGF0b3JzIGJlaGF2aW9yLCBzbyBzeW5jIHZhbGlkYXRvcnMgYXJlIGFsd2F5cyBwcm9jZXNzZWQgYnkgdGhlIGBWYWxpZGF0b3JzLmNvbXBvc2VgLlxuICAgIC8vIE5vdGU6IHdlIHNob3VsZCBjb25zaWRlciBtb3ZpbmcgdGhpcyBsb2dpYyBpbnNpZGUgdGhlIGBzZXRWYWxpZGF0b3JzYCBmdW5jdGlvbiBpdHNlbGYsIHNvIHdlXG4gICAgLy8gaGF2ZSBjb25zaXN0ZW50IGJlaGF2aW9yIG9uIEFic3RyYWN0Q29udHJvbCBBUEkgbGV2ZWwuIFRoZSBzYW1lIGFwcGxpZXMgdG8gdGhlIGFzeW5jXG4gICAgLy8gdmFsaWRhdG9ycyBsb2dpYyBiZWxvdy5cbiAgICBjb250cm9sLnNldFZhbGlkYXRvcnMoW3ZhbGlkYXRvcnNdKTtcbiAgfVxuXG4gIGNvbnN0IGFzeW5jVmFsaWRhdG9ycyA9IGdldENvbnRyb2xBc3luY1ZhbGlkYXRvcnMoY29udHJvbCk7XG4gIGlmIChkaXIuYXN5bmNWYWxpZGF0b3IgIT09IG51bGwpIHtcbiAgICBjb250cm9sLnNldEFzeW5jVmFsaWRhdG9ycyhcbiAgICAgICAgbWVyZ2VWYWxpZGF0b3JzPEFzeW5jVmFsaWRhdG9yRm4+KGFzeW5jVmFsaWRhdG9ycywgZGlyLmFzeW5jVmFsaWRhdG9yKSk7XG4gIH0gZWxzZSBpZiAodHlwZW9mIGFzeW5jVmFsaWRhdG9ycyA9PT0gJ2Z1bmN0aW9uJykge1xuICAgIGNvbnRyb2wuc2V0QXN5bmNWYWxpZGF0b3JzKFthc3luY1ZhbGlkYXRvcnNdKTtcbiAgfVxuXG4gIC8vIFJlLXJ1biB2YWxpZGF0aW9uIHdoZW4gdmFsaWRhdG9yIGJpbmRpbmcgY2hhbmdlcywgZS5nLiBtaW5sZW5ndGg9MyAtPiBtaW5sZW5ndGg9NFxuICBpZiAoaGFuZGxlT25WYWxpZGF0b3JDaGFuZ2UpIHtcbiAgICBjb25zdCBvblZhbGlkYXRvckNoYW5nZSA9ICgpID0+IGNvbnRyb2wudXBkYXRlVmFsdWVBbmRWYWxpZGl0eSgpO1xuICAgIHJlZ2lzdGVyT25WYWxpZGF0b3JDaGFuZ2U8VmFsaWRhdG9yRm4+KGRpci5fcmF3VmFsaWRhdG9ycywgb25WYWxpZGF0b3JDaGFuZ2UpO1xuICAgIHJlZ2lzdGVyT25WYWxpZGF0b3JDaGFuZ2U8QXN5bmNWYWxpZGF0b3JGbj4oZGlyLl9yYXdBc3luY1ZhbGlkYXRvcnMsIG9uVmFsaWRhdG9yQ2hhbmdlKTtcbiAgfVxufVxuXG4vKipcbiAqIENsZWFucyB1cCBzeW5jIGFuZCBhc3luYyBkaXJlY3RpdmUgdmFsaWRhdG9ycyBvbiBwcm92aWRlZCBmb3JtIGNvbnRyb2wuXG4gKiBUaGlzIGZ1bmN0aW9uIHJldmVydHMgdGhlIHNldHVwIHBlcmZvcm1lZCBieSB0aGUgYHNldFVwVmFsaWRhdG9yc2AgZnVuY3Rpb24sIGkuZS5cbiAqIHJlbW92ZXMgZGlyZWN0aXZlLXNwZWNpZmljIHZhbGlkYXRvcnMgZnJvbSBhIGdpdmVuIGNvbnRyb2wgaW5zdGFuY2UuXG4gKlxuICogQHBhcmFtIGNvbnRyb2wgRm9ybSBjb250cm9sIGZyb20gd2hlcmUgZGlyZWN0aXZlIHZhbGlkYXRvcnMgc2hvdWxkIGJlIHJlbW92ZWQuXG4gKiBAcGFyYW0gZGlyIERpcmVjdGl2ZSBpbnN0YW5jZSB0aGF0IGNvbnRhaW5zIHZhbGlkYXRvcnMgdG8gYmUgcmVtb3ZlZC5cbiAqIEBwYXJhbSBoYW5kbGVPblZhbGlkYXRvckNoYW5nZSBGbGFnIHRoYXQgZGV0ZXJtaW5lcyB3aGV0aGVyIGRpcmVjdGl2ZSB2YWxpZGF0b3JzIHNob3VsZCBhbHNvIGJlXG4gKiAgICAgY2xlYW5lZCB1cCB0byBzdG9wIGhhbmRsaW5nIHZhbGlkYXRvciBpbnB1dCBjaGFuZ2UgKGlmIHByZXZpb3VzbHkgY29uZmlndXJlZCB0byBkbyBzbykuXG4gKiBAcmV0dXJucyB0cnVlIGlmIGEgY29udHJvbCB3YXMgdXBkYXRlZCBhcyBhIHJlc3VsdCBvZiB0aGlzIGFjdGlvbi5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNsZWFuVXBWYWxpZGF0b3JzKFxuICAgIGNvbnRyb2w6IEFic3RyYWN0Q29udHJvbHxudWxsLCBkaXI6IEFic3RyYWN0Q29udHJvbERpcmVjdGl2ZSxcbiAgICBoYW5kbGVPblZhbGlkYXRvckNoYW5nZTogYm9vbGVhbik6IGJvb2xlYW4ge1xuICBsZXQgaXNDb250cm9sVXBkYXRlZCA9IGZhbHNlO1xuICBpZiAoY29udHJvbCAhPT0gbnVsbCkge1xuICAgIGlmIChkaXIudmFsaWRhdG9yICE9PSBudWxsKSB7XG4gICAgICBjb25zdCB2YWxpZGF0b3JzID0gZ2V0Q29udHJvbFZhbGlkYXRvcnMoY29udHJvbCk7XG4gICAgICBpZiAoQXJyYXkuaXNBcnJheSh2YWxpZGF0b3JzKSAmJiB2YWxpZGF0b3JzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgLy8gRmlsdGVyIG91dCBkaXJlY3RpdmUgdmFsaWRhdG9yIGZ1bmN0aW9uLlxuICAgICAgICBjb25zdCB1cGRhdGVkVmFsaWRhdG9ycyA9IHZhbGlkYXRvcnMuZmlsdGVyKHZhbGlkYXRvciA9PiB2YWxpZGF0b3IgIT09IGRpci52YWxpZGF0b3IpO1xuICAgICAgICBpZiAodXBkYXRlZFZhbGlkYXRvcnMubGVuZ3RoICE9PSB2YWxpZGF0b3JzLmxlbmd0aCkge1xuICAgICAgICAgIGlzQ29udHJvbFVwZGF0ZWQgPSB0cnVlO1xuICAgICAgICAgIGNvbnRyb2wuc2V0VmFsaWRhdG9ycyh1cGRhdGVkVmFsaWRhdG9ycyk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG5cbiAgICBpZiAoZGlyLmFzeW5jVmFsaWRhdG9yICE9PSBudWxsKSB7XG4gICAgICBjb25zdCBhc3luY1ZhbGlkYXRvcnMgPSBnZXRDb250cm9sQXN5bmNWYWxpZGF0b3JzKGNvbnRyb2wpO1xuICAgICAgaWYgKEFycmF5LmlzQXJyYXkoYXN5bmNWYWxpZGF0b3JzKSAmJiBhc3luY1ZhbGlkYXRvcnMubGVuZ3RoID4gMCkge1xuICAgICAgICAvLyBGaWx0ZXIgb3V0IGRpcmVjdGl2ZSBhc3luYyB2YWxpZGF0b3IgZnVuY3Rpb24uXG4gICAgICAgIGNvbnN0IHVwZGF0ZWRBc3luY1ZhbGlkYXRvcnMgPVxuICAgICAgICAgICAgYXN5bmNWYWxpZGF0b3JzLmZpbHRlcihhc3luY1ZhbGlkYXRvciA9PiBhc3luY1ZhbGlkYXRvciAhPT0gZGlyLmFzeW5jVmFsaWRhdG9yKTtcbiAgICAgICAgaWYgKHVwZGF0ZWRBc3luY1ZhbGlkYXRvcnMubGVuZ3RoICE9PSBhc3luY1ZhbGlkYXRvcnMubGVuZ3RoKSB7XG4gICAgICAgICAgaXNDb250cm9sVXBkYXRlZCA9IHRydWU7XG4gICAgICAgICAgY29udHJvbC5zZXRBc3luY1ZhbGlkYXRvcnModXBkYXRlZEFzeW5jVmFsaWRhdG9ycyk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICBpZiAoaGFuZGxlT25WYWxpZGF0b3JDaGFuZ2UpIHtcbiAgICAvLyBDbGVhciBvblZhbGlkYXRvckNoYW5nZSBjYWxsYmFja3MgYnkgcHJvdmlkaW5nIGEgbm9vcCBmdW5jdGlvbi5cbiAgICBjb25zdCBub29wID0gKCkgPT4ge307XG4gICAgcmVnaXN0ZXJPblZhbGlkYXRvckNoYW5nZTxWYWxpZGF0b3JGbj4oZGlyLl9yYXdWYWxpZGF0b3JzLCBub29wKTtcbiAgICByZWdpc3Rlck9uVmFsaWRhdG9yQ2hhbmdlPEFzeW5jVmFsaWRhdG9yRm4+KGRpci5fcmF3QXN5bmNWYWxpZGF0b3JzLCBub29wKTtcbiAgfVxuXG4gIHJldHVybiBpc0NvbnRyb2xVcGRhdGVkO1xufVxuXG5mdW5jdGlvbiBzZXRVcFZpZXdDaGFuZ2VQaXBlbGluZShjb250cm9sOiBGb3JtQ29udHJvbCwgZGlyOiBOZ0NvbnRyb2wpOiB2b2lkIHtcbiAgZGlyLnZhbHVlQWNjZXNzb3IhLnJlZ2lzdGVyT25DaGFuZ2UoKG5ld1ZhbHVlOiBhbnkpID0+IHtcbiAgICBjb250cm9sLl9wZW5kaW5nVmFsdWUgPSBuZXdWYWx1ZTtcbiAgICBjb250cm9sLl9wZW5kaW5nQ2hhbmdlID0gdHJ1ZTtcbiAgICBjb250cm9sLl9wZW5kaW5nRGlydHkgPSB0cnVlO1xuXG4gICAgaWYgKGNvbnRyb2wudXBkYXRlT24gPT09ICdjaGFuZ2UnKSB1cGRhdGVDb250cm9sKGNvbnRyb2wsIGRpcik7XG4gIH0pO1xufVxuXG5mdW5jdGlvbiBzZXRVcEJsdXJQaXBlbGluZShjb250cm9sOiBGb3JtQ29udHJvbCwgZGlyOiBOZ0NvbnRyb2wpOiB2b2lkIHtcbiAgZGlyLnZhbHVlQWNjZXNzb3IhLnJlZ2lzdGVyT25Ub3VjaGVkKCgpID0+IHtcbiAgICBjb250cm9sLl9wZW5kaW5nVG91Y2hlZCA9IHRydWU7XG5cbiAgICBpZiAoY29udHJvbC51cGRhdGVPbiA9PT0gJ2JsdXInICYmIGNvbnRyb2wuX3BlbmRpbmdDaGFuZ2UpIHVwZGF0ZUNvbnRyb2woY29udHJvbCwgZGlyKTtcbiAgICBpZiAoY29udHJvbC51cGRhdGVPbiAhPT0gJ3N1Ym1pdCcpIGNvbnRyb2wubWFya0FzVG91Y2hlZCgpO1xuICB9KTtcbn1cblxuZnVuY3Rpb24gdXBkYXRlQ29udHJvbChjb250cm9sOiBGb3JtQ29udHJvbCwgZGlyOiBOZ0NvbnRyb2wpOiB2b2lkIHtcbiAgaWYgKGNvbnRyb2wuX3BlbmRpbmdEaXJ0eSkgY29udHJvbC5tYXJrQXNEaXJ0eSgpO1xuICBjb250cm9sLnNldFZhbHVlKGNvbnRyb2wuX3BlbmRpbmdWYWx1ZSwge2VtaXRNb2RlbFRvVmlld0NoYW5nZTogZmFsc2V9KTtcbiAgZGlyLnZpZXdUb01vZGVsVXBkYXRlKGNvbnRyb2wuX3BlbmRpbmdWYWx1ZSk7XG4gIGNvbnRyb2wuX3BlbmRpbmdDaGFuZ2UgPSBmYWxzZTtcbn1cblxuZnVuY3Rpb24gc2V0VXBNb2RlbENoYW5nZVBpcGVsaW5lKGNvbnRyb2w6IEZvcm1Db250cm9sLCBkaXI6IE5nQ29udHJvbCk6IHZvaWQge1xuICBjb25zdCBvbkNoYW5nZSA9IChuZXdWYWx1ZTogYW55LCBlbWl0TW9kZWxFdmVudDogYm9vbGVhbikgPT4ge1xuICAgIC8vIGNvbnRyb2wgLT4gdmlld1xuICAgIGRpci52YWx1ZUFjY2Vzc29yIS53cml0ZVZhbHVlKG5ld1ZhbHVlKTtcblxuICAgIC8vIGNvbnRyb2wgLT4gbmdNb2RlbFxuICAgIGlmIChlbWl0TW9kZWxFdmVudCkgZGlyLnZpZXdUb01vZGVsVXBkYXRlKG5ld1ZhbHVlKTtcbiAgfTtcbiAgY29udHJvbC5yZWdpc3Rlck9uQ2hhbmdlKG9uQ2hhbmdlKTtcblxuICAvLyBSZWdpc3RlciBhIGNhbGxiYWNrIGZ1bmN0aW9uIHRvIGNsZWFudXAgb25DaGFuZ2UgaGFuZGxlclxuICAvLyBmcm9tIGEgY29udHJvbCBpbnN0YW5jZSB3aGVuIGEgZGlyZWN0aXZlIGlzIGRlc3Ryb3llZC5cbiAgZGlyLl9yZWdpc3Rlck9uRGVzdHJveSgoKSA9PiB7XG4gICAgY29udHJvbC5fdW5yZWdpc3Rlck9uQ2hhbmdlKG9uQ2hhbmdlKTtcbiAgfSk7XG59XG5cbi8qKlxuICogTGlua3MgYSBGb3JtR3JvdXAgb3IgRm9ybUFycmF5IGluc3RhbmNlIGFuZCBjb3JyZXNwb25kaW5nIEZvcm0gZGlyZWN0aXZlIGJ5IHNldHRpbmcgdXAgdmFsaWRhdG9yc1xuICogcHJlc2VudCBpbiB0aGUgdmlldy5cbiAqXG4gKiBAcGFyYW0gY29udHJvbCBGb3JtR3JvdXAgb3IgRm9ybUFycmF5IGluc3RhbmNlIHRoYXQgc2hvdWxkIGJlIGxpbmtlZC5cbiAqIEBwYXJhbSBkaXIgRGlyZWN0aXZlIHRoYXQgcHJvdmlkZXMgdmlldyB2YWxpZGF0b3JzLlxuICovXG5leHBvcnQgZnVuY3Rpb24gc2V0VXBGb3JtQ29udGFpbmVyKFxuICAgIGNvbnRyb2w6IEZvcm1Hcm91cHxGb3JtQXJyYXksIGRpcjogQWJzdHJhY3RGb3JtR3JvdXBEaXJlY3RpdmV8Rm9ybUFycmF5TmFtZSkge1xuICBpZiAoY29udHJvbCA9PSBudWxsICYmICh0eXBlb2YgbmdEZXZNb2RlID09PSAndW5kZWZpbmVkJyB8fCBuZ0Rldk1vZGUpKVxuICAgIF90aHJvd0Vycm9yKGRpciwgJ0Nhbm5vdCBmaW5kIGNvbnRyb2wgd2l0aCcpO1xuICBzZXRVcFZhbGlkYXRvcnMoY29udHJvbCwgZGlyLCAvKiBoYW5kbGVPblZhbGlkYXRvckNoYW5nZSAqLyBmYWxzZSk7XG59XG5cbi8qKlxuICogUmV2ZXJ0cyB0aGUgc2V0dXAgcGVyZm9ybWVkIGJ5IHRoZSBgc2V0VXBGb3JtQ29udGFpbmVyYCBmdW5jdGlvbi5cbiAqXG4gKiBAcGFyYW0gY29udHJvbCBGb3JtR3JvdXAgb3IgRm9ybUFycmF5IGluc3RhbmNlIHRoYXQgc2hvdWxkIGJlIGNsZWFuZWQgdXAuXG4gKiBAcGFyYW0gZGlyIERpcmVjdGl2ZSB0aGF0IHByb3ZpZGVkIHZpZXcgdmFsaWRhdG9ycy5cbiAqIEByZXR1cm5zIHRydWUgaWYgYSBjb250cm9sIHdhcyB1cGRhdGVkIGFzIGEgcmVzdWx0IG9mIHRoaXMgYWN0aW9uLlxuICovXG5leHBvcnQgZnVuY3Rpb24gY2xlYW5VcEZvcm1Db250YWluZXIoXG4gICAgY29udHJvbDogRm9ybUdyb3VwfEZvcm1BcnJheSwgZGlyOiBBYnN0cmFjdEZvcm1Hcm91cERpcmVjdGl2ZXxGb3JtQXJyYXlOYW1lKTogYm9vbGVhbiB7XG4gIHJldHVybiBjbGVhblVwVmFsaWRhdG9ycyhjb250cm9sLCBkaXIsIC8qIGhhbmRsZU9uVmFsaWRhdG9yQ2hhbmdlICovIGZhbHNlKTtcbn1cblxuZnVuY3Rpb24gX25vQ29udHJvbEVycm9yKGRpcjogTmdDb250cm9sKSB7XG4gIHJldHVybiBfdGhyb3dFcnJvcihkaXIsICdUaGVyZSBpcyBubyBGb3JtQ29udHJvbCBpbnN0YW5jZSBhdHRhY2hlZCB0byBmb3JtIGNvbnRyb2wgZWxlbWVudCB3aXRoJyk7XG59XG5cbmZ1bmN0aW9uIF90aHJvd0Vycm9yKGRpcjogQWJzdHJhY3RDb250cm9sRGlyZWN0aXZlLCBtZXNzYWdlOiBzdHJpbmcpOiB2b2lkIHtcbiAgbGV0IG1lc3NhZ2VFbmQ6IHN0cmluZztcbiAgaWYgKGRpci5wYXRoIS5sZW5ndGggPiAxKSB7XG4gICAgbWVzc2FnZUVuZCA9IGBwYXRoOiAnJHtkaXIucGF0aCEuam9pbignIC0+ICcpfSdgO1xuICB9IGVsc2UgaWYgKGRpci5wYXRoIVswXSkge1xuICAgIG1lc3NhZ2VFbmQgPSBgbmFtZTogJyR7ZGlyLnBhdGh9J2A7XG4gIH0gZWxzZSB7XG4gICAgbWVzc2FnZUVuZCA9ICd1bnNwZWNpZmllZCBuYW1lIGF0dHJpYnV0ZSc7XG4gIH1cbiAgdGhyb3cgbmV3IEVycm9yKGAke21lc3NhZ2V9ICR7bWVzc2FnZUVuZH1gKTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIGlzUHJvcGVydHlVcGRhdGVkKGNoYW5nZXM6IHtba2V5OiBzdHJpbmddOiBhbnl9LCB2aWV3TW9kZWw6IGFueSk6IGJvb2xlYW4ge1xuICBpZiAoIWNoYW5nZXMuaGFzT3duUHJvcGVydHkoJ21vZGVsJykpIHJldHVybiBmYWxzZTtcbiAgY29uc3QgY2hhbmdlID0gY2hhbmdlc1snbW9kZWwnXTtcblxuICBpZiAoY2hhbmdlLmlzRmlyc3RDaGFuZ2UoKSkgcmV0dXJuIHRydWU7XG4gIHJldHVybiAhT2JqZWN0LmlzKHZpZXdNb2RlbCwgY2hhbmdlLmN1cnJlbnRWYWx1ZSk7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBpc0J1aWx0SW5BY2Nlc3Nvcih2YWx1ZUFjY2Vzc29yOiBDb250cm9sVmFsdWVBY2Nlc3Nvcik6IGJvb2xlYW4ge1xuICAvLyBDaGVjayBpZiBhIGdpdmVuIHZhbHVlIGFjY2Vzc29yIGlzIGFuIGluc3RhbmNlIG9mIGEgY2xhc3MgdGhhdCBkaXJlY3RseSBleHRlbmRzXG4gIC8vIGBCdWlsdEluQ29udHJvbFZhbHVlQWNjZXNzb3JgIG9uZS5cbiAgcmV0dXJuIE9iamVjdC5nZXRQcm90b3R5cGVPZih2YWx1ZUFjY2Vzc29yLmNvbnN0cnVjdG9yKSA9PT0gQnVpbHRJbkNvbnRyb2xWYWx1ZUFjY2Vzc29yO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gc3luY1BlbmRpbmdDb250cm9scyhmb3JtOiBGb3JtR3JvdXAsIGRpcmVjdGl2ZXM6IE5nQ29udHJvbFtdKTogdm9pZCB7XG4gIGZvcm0uX3N5bmNQZW5kaW5nQ29udHJvbHMoKTtcbiAgZGlyZWN0aXZlcy5mb3JFYWNoKGRpciA9PiB7XG4gICAgY29uc3QgY29udHJvbCA9IGRpci5jb250cm9sIGFzIEZvcm1Db250cm9sO1xuICAgIGlmIChjb250cm9sLnVwZGF0ZU9uID09PSAnc3VibWl0JyAmJiBjb250cm9sLl9wZW5kaW5nQ2hhbmdlKSB7XG4gICAgICBkaXIudmlld1RvTW9kZWxVcGRhdGUoY29udHJvbC5fcGVuZGluZ1ZhbHVlKTtcbiAgICAgIGNvbnRyb2wuX3BlbmRpbmdDaGFuZ2UgPSBmYWxzZTtcbiAgICB9XG4gIH0pO1xufVxuXG4vLyBUT0RPOiB2c2F2a2luIHJlbW92ZSBpdCBvbmNlIGh0dHBzOi8vZ2l0aHViLmNvbS9hbmd1bGFyL2FuZ3VsYXIvaXNzdWVzLzMwMTEgaXMgaW1wbGVtZW50ZWRcbmV4cG9ydCBmdW5jdGlvbiBzZWxlY3RWYWx1ZUFjY2Vzc29yKFxuICAgIGRpcjogTmdDb250cm9sLCB2YWx1ZUFjY2Vzc29yczogQ29udHJvbFZhbHVlQWNjZXNzb3JbXSk6IENvbnRyb2xWYWx1ZUFjY2Vzc29yfG51bGwge1xuICBpZiAoIXZhbHVlQWNjZXNzb3JzKSByZXR1cm4gbnVsbDtcblxuICBpZiAoIUFycmF5LmlzQXJyYXkodmFsdWVBY2Nlc3NvcnMpICYmICh0eXBlb2YgbmdEZXZNb2RlID09PSAndW5kZWZpbmVkJyB8fCBuZ0Rldk1vZGUpKVxuICAgIF90aHJvd0Vycm9yKGRpciwgJ1ZhbHVlIGFjY2Vzc29yIHdhcyBub3QgcHJvdmlkZWQgYXMgYW4gYXJyYXkgZm9yIGZvcm0gY29udHJvbCB3aXRoJyk7XG5cbiAgbGV0IGRlZmF1bHRBY2Nlc3NvcjogQ29udHJvbFZhbHVlQWNjZXNzb3J8dW5kZWZpbmVkID0gdW5kZWZpbmVkO1xuICBsZXQgYnVpbHRpbkFjY2Vzc29yOiBDb250cm9sVmFsdWVBY2Nlc3Nvcnx1bmRlZmluZWQgPSB1bmRlZmluZWQ7XG4gIGxldCBjdXN0b21BY2Nlc3NvcjogQ29udHJvbFZhbHVlQWNjZXNzb3J8dW5kZWZpbmVkID0gdW5kZWZpbmVkO1xuXG4gIHZhbHVlQWNjZXNzb3JzLmZvckVhY2goKHY6IENvbnRyb2xWYWx1ZUFjY2Vzc29yKSA9PiB7XG4gICAgaWYgKHYuY29uc3RydWN0b3IgPT09IERlZmF1bHRWYWx1ZUFjY2Vzc29yKSB7XG4gICAgICBkZWZhdWx0QWNjZXNzb3IgPSB2O1xuXG4gICAgfSBlbHNlIGlmIChpc0J1aWx0SW5BY2Nlc3Nvcih2KSkge1xuICAgICAgaWYgKGJ1aWx0aW5BY2Nlc3NvciAmJiAodHlwZW9mIG5nRGV2TW9kZSA9PT0gJ3VuZGVmaW5lZCcgfHwgbmdEZXZNb2RlKSlcbiAgICAgICAgX3Rocm93RXJyb3IoZGlyLCAnTW9yZSB0aGFuIG9uZSBidWlsdC1pbiB2YWx1ZSBhY2Nlc3NvciBtYXRjaGVzIGZvcm0gY29udHJvbCB3aXRoJyk7XG4gICAgICBidWlsdGluQWNjZXNzb3IgPSB2O1xuXG4gICAgfSBlbHNlIHtcbiAgICAgIGlmIChjdXN0b21BY2Nlc3NvciAmJiAodHlwZW9mIG5nRGV2TW9kZSA9PT0gJ3VuZGVmaW5lZCcgfHwgbmdEZXZNb2RlKSlcbiAgICAgICAgX3Rocm93RXJyb3IoZGlyLCAnTW9yZSB0aGFuIG9uZSBjdXN0b20gdmFsdWUgYWNjZXNzb3IgbWF0Y2hlcyBmb3JtIGNvbnRyb2wgd2l0aCcpO1xuICAgICAgY3VzdG9tQWNjZXNzb3IgPSB2O1xuICAgIH1cbiAgfSk7XG5cbiAgaWYgKGN1c3RvbUFjY2Vzc29yKSByZXR1cm4gY3VzdG9tQWNjZXNzb3I7XG4gIGlmIChidWlsdGluQWNjZXNzb3IpIHJldHVybiBidWlsdGluQWNjZXNzb3I7XG4gIGlmIChkZWZhdWx0QWNjZXNzb3IpIHJldHVybiBkZWZhdWx0QWNjZXNzb3I7XG5cbiAgaWYgKHR5cGVvZiBuZ0Rldk1vZGUgPT09ICd1bmRlZmluZWQnIHx8IG5nRGV2TW9kZSkge1xuICAgIF90aHJvd0Vycm9yKGRpciwgJ05vIHZhbGlkIHZhbHVlIGFjY2Vzc29yIGZvciBmb3JtIGNvbnRyb2wgd2l0aCcpO1xuICB9XG4gIHJldHVybiBudWxsO1xufVxuXG5leHBvcnQgZnVuY3Rpb24gcmVtb3ZlTGlzdEl0ZW08VD4obGlzdDogVFtdLCBlbDogVCk6IHZvaWQge1xuICBjb25zdCBpbmRleCA9IGxpc3QuaW5kZXhPZihlbCk7XG4gIGlmIChpbmRleCA+IC0xKSBsaXN0LnNwbGljZShpbmRleCwgMSk7XG59XG5cbi8vIFRPRE8oa2FyYSk6IHJlbW92ZSBhZnRlciBkZXByZWNhdGlvbiBwZXJpb2RcbmV4cG9ydCBmdW5jdGlvbiBfbmdNb2RlbFdhcm5pbmcoXG4gICAgbmFtZTogc3RyaW5nLCB0eXBlOiB7X25nTW9kZWxXYXJuaW5nU2VudE9uY2U6IGJvb2xlYW59LFxuICAgIGluc3RhbmNlOiB7X25nTW9kZWxXYXJuaW5nU2VudDogYm9vbGVhbn0sIHdhcm5pbmdDb25maWc6IHN0cmluZ3xudWxsKSB7XG4gIGlmICh3YXJuaW5nQ29uZmlnID09PSAnbmV2ZXInKSByZXR1cm47XG5cbiAgaWYgKCgod2FybmluZ0NvbmZpZyA9PT0gbnVsbCB8fCB3YXJuaW5nQ29uZmlnID09PSAnb25jZScpICYmICF0eXBlLl9uZ01vZGVsV2FybmluZ1NlbnRPbmNlKSB8fFxuICAgICAgKHdhcm5pbmdDb25maWcgPT09ICdhbHdheXMnICYmICFpbnN0YW5jZS5fbmdNb2RlbFdhcm5pbmdTZW50KSkge1xuICAgIFJlYWN0aXZlRXJyb3JzLm5nTW9kZWxXYXJuaW5nKG5hbWUpO1xuICAgIHR5cGUuX25nTW9kZWxXYXJuaW5nU2VudE9uY2UgPSB0cnVlO1xuICAgIGluc3RhbmNlLl9uZ01vZGVsV2FybmluZ1NlbnQgPSB0cnVlO1xuICB9XG59XG4iXX0=