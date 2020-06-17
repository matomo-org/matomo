/*
 * Based on implementation by Neil Bartlett
 * https://github.com/neilbartlett/color-temperature
 */

const {log} = Math;

const temperature2rgb = (kelvin) => {
    const temp = kelvin / 100;
    let r,g,b;
    if (temp < 66) {
        r = 255
        g = -155.25485562709179 - 0.44596950469579133 * (g = temp-2) + 104.49216199393888 * log(g)
        b = temp < 20 ? 0 : -254.76935184120902 + 0.8274096064007395 * (b = temp-10) + 115.67994401066147 * log(b)
    } else {
        r = 351.97690566805693 + 0.114206453784165 * (r = temp-55) - 40.25366309332127 * log(r)
        g = 325.4494125711974 + 0.07943456536662342 * (g = temp-50) - 28.0852963507957 * log(g)
        b = 255
    }
    return [r,g,b,1];
}

module.exports = temperature2rgb;
