/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

    var colorNames = {"aliceblue":"#f0f8ff","antiquewhite":"#faebd7","aqua":"#00ffff","aquamarine":"#7fffd4","azure":"#f0ffff",
        "beige":"#f5f5dc","bisque":"#ffe4c4","black":"#000000","blanchedalmond":"#ffebcd","blue":"#0000ff","blueviolet":"#8a2be2","brown":"#a52a2a","burlywood":"#deb887",
        "cadetblue":"#5f9ea0","chartreuse":"#7fff00","chocolate":"#d2691e","coral":"#ff7f50","cornflowerblue":"#6495ed","cornsilk":"#fff8dc","crimson":"#dc143c","cyan":"#00ffff",
        "darkblue":"#00008b","darkcyan":"#008b8b","darkgoldenrod":"#b8860b","darkgray":"#a9a9a9","darkgreen":"#006400","darkkhaki":"#bdb76b","darkmagenta":"#8b008b","darkolivegreen":"#556b2f",
        "darkorange":"#ff8c00","darkorchid":"#9932cc","darkred":"#8b0000","darksalmon":"#e9967a","darkseagreen":"#8fbc8f","darkslateblue":"#483d8b","darkslategray":"#2f4f4f","darkturquoise":"#00ced1",
        "darkviolet":"#9400d3","deeppink":"#ff1493","deepskyblue":"#00bfff","dimgray":"#696969","dodgerblue":"#1e90ff",
        "firebrick":"#b22222","floralwhite":"#fffaf0","forestgreen":"#228b22","fuchsia":"#ff00ff","gainsboro":"#dcdcdc","ghostwhite":"#f8f8ff","gold":"#ffd700","goldenrod":"#daa520","gray":"#808080","green":"#008000","greenyellow":"#adff2f",
        "honeydew":"#f0fff0","hotpink":"#ff69b4","indianred ":"#cd5c5c","indigo ":"#4b0082","ivory":"#fffff0","khaki":"#f0e68c",
        "lavender":"#e6e6fa","lavenderblush":"#fff0f5","lawngreen":"#7cfc00","lemonchiffon":"#fffacd","lightblue":"#add8e6","lightcoral":"#f08080","lightcyan":"#e0ffff","lightgoldenrodyellow":"#fafad2",
        "lightgrey":"#d3d3d3","lightgreen":"#90ee90","lightpink":"#ffb6c1","lightsalmon":"#ffa07a","lightseagreen":"#20b2aa","lightskyblue":"#87cefa","lightslategray":"#778899","lightsteelblue":"#b0c4de",
        "lightyellow":"#ffffe0","lime":"#00ff00","limegreen":"#32cd32","linen":"#faf0e6","magenta":"#ff00ff","maroon":"#800000","mediumaquamarine":"#66cdaa","mediumblue":"#0000cd","mediumorchid":"#ba55d3","mediumpurple":"#9370d8","mediumseagreen":"#3cb371","mediumslateblue":"#7b68ee",
        "mediumspringgreen":"#00fa9a","mediumturquoise":"#48d1cc","mediumvioletred":"#c71585","midnightblue":"#191970","mintcream":"#f5fffa","mistyrose":"#ffe4e1","moccasin":"#ffe4b5",
        "navajowhite":"#ffdead","navy":"#000080","oldlace":"#fdf5e6","olive":"#808000","olivedrab":"#6b8e23","orange":"#ffa500","orangered":"#ff4500","orchid":"#da70d6",
        "palegoldenrod":"#eee8aa","palegreen":"#98fb98","paleturquoise":"#afeeee","palevioletred":"#d87093","papayawhip":"#ffefd5","peachpuff":"#ffdab9","peru":"#cd853f","pink":"#ffc0cb","plum":"#dda0dd","powderblue":"#b0e0e6","purple":"#800080",
        "red":"#ff0000","rosybrown":"#bc8f8f","royalblue":"#4169e1","saddlebrown":"#8b4513","salmon":"#fa8072","sandybrown":"#f4a460","seagreen":"#2e8b57","seashell":"#fff5ee","sienna":"#a0522d","silver":"#c0c0c0","skyblue":"#87ceeb","slateblue":"#6a5acd","slategray":"#708090","snow":"#fffafa","springgreen":"#00ff7f","steelblue":"#4682b4",
        "tan":"#d2b48c","teal":"#008080","thistle":"#d8bfd8","tomato":"#ff6347","turquoise":"#40e0d0","violet":"#ee82ee","wheat":"#f5deb3","white":"#ffffff","whitesmoke":"#f5f5f5","yellow":"#ffff00","yellowgreen":"#9acd32"};

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

            if (color && colorNames[color]) {
                return colorNames[color];
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
                this.transparentColor =
                    $('<div style="color:transparent;display:none;"></div>').appendTo($('body')).css('color');
            }

            return this.transparentColor;
        }
    };

    piwik.ColorManager = new ColorManager();

}(jQuery));