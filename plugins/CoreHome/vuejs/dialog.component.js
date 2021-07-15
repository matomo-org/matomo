
matomo.VueComponents['matomoDialog'] = {
    props: ['trigger'],
    emits: ['yes', 'no', 'close'],
    watch: {
        trigger: {
            handler(newValue, oldValue) {
                var self = this;
                if (newValue) {
                    piwik.helper.modalConfirm(self.$el, {
                        yes: function () {
                            self.$emit('yes');
                        }, no: function () {
                            self.$emit('no');
                        }
                    }, {
                        onCloseEnd: function () {
                            self.$emit('close');
                        }
                    });
                }
            }
        }
    },
    template: `<div><slot></slot></div>`
};
