(function () {
    return function (parameters, TagManager) {
        this.setUp = function (triggerEvent) {

            const eventNames = [
                "touchstart",
                "mouseover",
                "wheel",
                "scroll",
                "keydown"
            ];
            
            const init = () => {
                triggerEvent({event: 'UserInteraction'});
                removeListeners();
            }
            
            const removeListeners = () => {
                for (var i = 0, iLen = eventNames.length; i < iLen; i++) {
                    window.removeEventListener(eventNames[i], init);
                }
            }
         
            for (var i = 0, iLen = eventNames.length; i < iLen; i++) {
                window.addEventListener(eventNames[i], init, {once : true});
            }

        };
    };
})();