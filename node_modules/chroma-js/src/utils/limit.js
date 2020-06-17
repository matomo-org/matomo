module.exports = (x, min=0, max=1) => {
    return x < min ? min : x > max ? max : x;
}
