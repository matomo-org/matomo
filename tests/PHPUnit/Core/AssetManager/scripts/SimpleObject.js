if (typeof SimpleObject !== 'object') {

    SimpleObject = (function () {

        var privateVar;

        function privateMethod(param) {
            privateVar = param;
        }

        return {

            publicMethod: function () {
                privateMethod('val');
            }
        }
    }());
}
