
matomo = {
    VueComponents: {},
    VueMethods: {},
    createVue: function (elem) {
        var app = Vue.createApp({
            methods: this.VueMethods
        });
        angular.forEach(this.VueComponents, function(component, name){
            if (!component.methods) {
                component.methods = {};
            }
            for (var method in matomo.VueMethods) {
                component.methods[method] = matomo.VueMethods[method];
            }
            app.component(name, component);
        });

        app.mount(elem);
        return app;
    }
};
