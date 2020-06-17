// ported from jQuery's $.type
const classToType = {};
for (let name of ['Boolean', 'Number', 'String', 'Function', 'Array', 'Date', 'RegExp', 'Undefined', 'Null']) {
    classToType[`[object ${name}]`] = name.toLowerCase();
}
module.exports = function(obj) {
    return classToType[Object.prototype.toString.call(obj)] || "object";
};
