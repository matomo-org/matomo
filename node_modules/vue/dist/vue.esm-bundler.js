import * as runtimeDom from '@vue/runtime-dom';
import { initCustomFormatter, warn, registerRuntimeCompiler } from '@vue/runtime-dom';
export * from '@vue/runtime-dom';
import { compile } from '@vue/compiler-dom';
import { isString, NOOP, extend, generateCodeFrame } from '@vue/shared';

function initDev() {
    {
        initCustomFormatter();
    }
}

// This entry is the "full-build" that includes both the runtime
if ((process.env.NODE_ENV !== 'production')) {
    initDev();
}
const compileCache = Object.create(null);
function compileToFunction(template, options) {
    if (!isString(template)) {
        if (template.nodeType) {
            template = template.innerHTML;
        }
        else {
            (process.env.NODE_ENV !== 'production') && warn(`invalid template option: `, template);
            return NOOP;
        }
    }
    const key = template;
    const cached = compileCache[key];
    if (cached) {
        return cached;
    }
    if (template[0] === '#') {
        const el = document.querySelector(template);
        if ((process.env.NODE_ENV !== 'production') && !el) {
            warn(`Template element not found or is empty: ${template}`);
        }
        // __UNSAFE__
        // Reason: potential execution of JS expressions in in-DOM template.
        // The user must make sure the in-DOM template is trusted. If it's rendered
        // by the server, the template should not contain any user data.
        template = el ? el.innerHTML : ``;
    }
    const { code } = compile(template, extend({
        hoistStatic: true,
        onError: (process.env.NODE_ENV !== 'production') ? onError : undefined,
        onWarn: (process.env.NODE_ENV !== 'production') ? e => onError(e, true) : NOOP
    }, options));
    function onError(err, asWarning = false) {
        const message = asWarning
            ? err.message
            : `Template compilation error: ${err.message}`;
        const codeFrame = err.loc &&
            generateCodeFrame(template, err.loc.start.offset, err.loc.end.offset);
        warn(codeFrame ? `${message}\n${codeFrame}` : message);
    }
    // The wildcard import results in a huge object with every export
    // with keys that cannot be mangled, and can be quite heavy size-wise.
    // In the global build we know `Vue` is available globally so we can avoid
    // the wildcard object.
    const render = (new Function('Vue', code)(runtimeDom));
    render._rc = true;
    return (compileCache[key] = render);
}
registerRuntimeCompiler(compileToFunction);

export { compileToFunction as compile };
