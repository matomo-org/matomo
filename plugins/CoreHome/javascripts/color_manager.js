/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

    /**
     * The ColorManager class allows JS code to grab colors defined in CSS for
     * components that don't manage HTML (like jqPlot or sparklines). Such components
     * can't use CSS colors directly since the colors are used to generate images
     * or by <canvas> elements.
     * 
     * Colors obtained via ColorManager are defined in CSS like this:
     * 
     * .my-color-namespace[data-name=color-name] {
     *     color: #fff
     * }
     * 
     * and can be accessed in JavaScript like this:
     * 
     * piwik.ColorManager.getColor("my-color-namespace", "color-name");
     * 
     * The singleton instance of this class can be accessed via piwik.ColorManager.
     */
    var ColorManager = function () {
        // empty
    };
    
    ColorManager.prototype = {

        /**
         * Returns the color for a namespace and name.
         * 
         * @param {String} namespace The string identifier that groups related colors
         *                           together. For example, 'sparkline-colors'.
         * @param {String} name The name of the color to retrieve. For example, 'lineColor'.
         * @return {String} A hex color, eg, '#fff'.
         */
        getColor: function (namespace, name) {
            var element = this._getElement();

            element.attr('class', 'color-manager ' + namespace).attr('data-name', name);
            return this._normalizeColor(element.css('color'));
        },
        
        /**
         * Returns the colors for a namespace and a list of names.
         * 
         * @param {String} namespace The string identifier that groups related colors
         *                           together. For example, 'sparkline-colors'.
         * @param {Array} names An array of color names to retrieve.
         * @param {Boolean} asArray Whether the result should be an array or an object.
         * @return {Object|Array} An object mapping color names with color values or an
         *                        array of colors.
         */
        getColors: function (namespace, names, asArray) {
            var colors = asArray ? [] : {};
            for (var i = 0; i != names.length; ++i) {
                var name = names[i],
                    color = this.getColor(namespace, name);
                if (color) {
                    if (asArray) {
                        colors.push(color);
                    } else {
                        colors[name] = color;
                    }
                }
            }
            return colors;
        },

        /**
         * Turns a color string that might be an rgb value rgb(12, 34, 56) into
         * a hex color string.
         */
        _normalizeColor: function (color) {
            if (color == this._getTransparentColor()) {
                return null;
            }

            if (color
                && color[0] != '#'
            ) {
                // parse rgb(#, #, #) and get rgb numbers
                var parts = color.split(/[()rgb,\s]+/);
                parts = [+parts[1], +parts[2], +parts[3]];

                // convert parts to hex with one leading 0
                for (var i = 0; i != parts.length; ++i) {
                    parts[i] = ("00" + parts[i].toString(16)).slice(-2);
                }

                // create hex string
                color = '#' + parts.join('');
            }
            return color;
        },
        
        /**
         * Returns the manufactured <div> element used to obtain color data. When
         * getting color data the class and data-name attribute of this element are
         * changed.
         */
        _getElement: function () {
            if (!this.$element) {
                $('body').append('<div id="color-manager"></div>');
                this.$element = $('#color-manager');
            }

            return this.$element;
        },

        /**
         * Returns this browser's representation of the 'transparent' color. Used to
         * compare against colors obtained in getColor. If a color is 'transparent'
         * it means there's no color for that namespace/name combination.
         */
        _getTransparentColor: function () {
            if (!this.transparentColor) {
                this.transparentColor = $('<div style="color:transparent;"></div>').appendTo($('body')).css('color');
            }

            return this.transparentColor;
        }
    };
    
    piwik.ColorManager = new ColorManager();

}(jQuery));
