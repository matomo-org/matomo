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
         * Returns a color that is N % between two other colors.
         * 
         * @param {String|Array} spectrumStart The start color. If percentFromStart is 0, this color will
         *                                     be returned. Can be either a hex color or RGB array.
         *                                     It will be converted to an RGB array if a hex color is supplied.
         * @param {String|Array} spectrumEnd The end color. If percentFromStart is 1, this color will be
         *                                   returned. Can be either a hex color or RGB array. It will be
         *                                   converted to an RGB array if a hex color is supplied.
         * @param {Number} percentFromStart The percent from spectrumStart and twoard spectrumEnd that the
         *                                  result color should be. Must be a value between 0.0 & 1.0.
         * @return {String} A hex color.
         */
        getSingleColorFromGradient: function (spectrumStart, spectrumEnd, percentFromStart) {
            if (!(spectrumStart instanceof Array)) {
                spectrumStart = this.getRgb(spectrumStart);
            }

            if (!(spectrumEnd instanceof Array)) {
                spectrumEnd = this.getRgb(spectrumEnd);
            }

            var result = [];
            for (var channel = 0; channel != spectrumStart.length; ++channel) {
                var delta = (spectrumEnd[channel] - spectrumStart[channel]) * percentFromStart;

                result[channel] = Math.floor(spectrumStart[channel] + delta);
            }

            return this.getHexColor(result);
        },

        /**
         * Utility function that converts a hex color (ie, #fff or #1a1a1a) to an array of
         * RGB values.
         * 
         * @param {String} hexColor The color to convert.
         * @return {Array} An array with three integers between 0 and 255.
         */
        getRgb: function (hexColor) {
            if (hexColor[0] == '#') {
                hexColor = hexColor.substring(1);
            }

            if (hexColor.length == 3) {
                return [
                    parseInt(hexColor[0], 16),
                    parseInt(hexColor[1], 16),
                    parseInt(hexColor[2], 16)
                ];
            } else {
                return [
                    parseInt(hexColor.substring(0,2), 16),
                    parseInt(hexColor.substring(2,4), 16),
                    parseInt(hexColor.substring(4,6), 16)
                ];
            }
        },

        /**
         * Utility function that converts an RGB array to a hex color.
         * 
         * @param {Array} rgbColor An array with three integers between 0 and 255.
         * @return {String} The hex color, eg, #1a1a1a.
         */
        getHexColor: function (rgbColor) {
            // convert channels to hex with one leading 0
            for (var i = 0; i != rgbColor.length; ++i) {
                rgbColor[i] = ("00" + rgbColor[i].toString(16)).slice(-2);
            }

            // create hex string
            return '#' + rgbColor.join('');
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

                // convert to hex
                color = this.getHexColor(parts);
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