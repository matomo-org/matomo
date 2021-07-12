
matomo = {
    VueComponents: {},
    createVue: function (elem) {
        var app = Vue.createApp({});
        angular.forEach(this.VueComponents, function(component, name){
            app.component(name, component);
        });
        app.mount(elem);
        return app;
    }
};
