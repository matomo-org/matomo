/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The focus-trap exemption is what lets a control teleported out of a modal - the expandable
 * select renders its option list at page level so a scrolling ancestor cannot clip it - keep
 * focus inside that modal. Nothing else asserts it: the wrapper installs against Materialize's
 * private _handleFocus, so a rename there would silently stop installing it, and the attribute
 * name is a bare literal in two files that cannot share a constant.
 */
describe('materialize-bc modal focus trap', function () {
    var trapped;
    var readyCallbacks;

    function loadShim() {
        readyCallbacks = [];
        trapped = [];

        global.M = {
            initializeJqueryWrapper: function () {},
            Tabs: {},
            Modal: {
                prototype: {
                    _handleFocus: function (event) {
                        trapped.push(event.target);
                    },
                },
            },
        };
        global.$ = function () {
            return { ready: function (callback) { readyCallbacks.push(callback); } };
        };
        global.$.fn = {};

        // the shim installs from inside $(document).ready, so run what it registered
        delete require.cache[require.resolve('./materialize-bc.js')];
        require('./materialize-bc.js');
        readyCallbacks.forEach(function (callback) { callback(); });
    }

    function focusFrom(html, modalId) {
        document.body.innerHTML = html;
        var target = document.querySelector('.target');
        var modal = document.createElement('div');

        if (modalId) {
            modal.setAttribute('data-matomo-modal-id', modalId);
        }

        global.M.Modal.prototype._handleFocus.call({ el: modal }, { target: target });
    }

    beforeEach(function () {
        loadShim();
    });

    afterEach(function () {
        document.body.innerHTML = '';
        delete global.M;
        delete global.$;
    });

    it('lets an element through the trap of the modal it names', function () {
        focusFrom('<div data-matomo-modal-escapee="owner-a"><input class="target"></div>', 'owner-a');

        expect(trapped.length).to.equal(0);
    });

    it('still traps an element that names a different modal', function () {
        // an element belonging to an underlying modal must not hold focus over the one on top
        focusFrom('<div data-matomo-modal-escapee="owner-a"><input class="target"></div>', 'owner-b');

        expect(trapped.length).to.equal(1);
    });

    it('still traps an unmarked element', function () {
        focusFrom('<div><input class="target"></div>', 'owner-a');

        expect(trapped.length).to.equal(1);
    });

    it('still traps a marked element when the modal carries no id', function () {
        focusFrom('<div data-matomo-modal-escapee="owner-a"><input class="target"></div>', null);

        expect(trapped.length).to.equal(1);
    });

});
