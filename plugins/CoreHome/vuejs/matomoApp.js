
var matomo = (function() {
    var VueComponents = {};
    var VueMethods = {};

    return {
        registerComponent: function(name, component) {
            VueComponents[name] = component
        },
        registerMethod: function(name, method) {
            VueMethods[name] = method
        },
        getComponents: function() {
            return VueComponents;
        },
        getMethods: function() {
            return VueMethods;
        },
        createVue: function (elem) {
            var app = Vue.createApp({
                methods: VueMethods
            });
            angular.forEach(VueComponents, function (component, name) {
                if (!component.methods) {
                    component.methods = {};
                }
                for (var method in VueMethods) {
                    component.methods[method] = VueMethods[method];
                }
                app.component(name, component);
            });

            app.mount(elem);
            return app;
        }
    };
})();
