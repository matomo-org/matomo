/**
 * Model for Sites Manager. Fetches only sites one has at least Admin permission.
 */
(function () {
    angular.module('piwikApp').factory('sitesManagerTypeModel', sitesManagerTypeModel);

    sitesManagerTypeModel.$inject = ['piwikApi'];

    function sitesManagerTypeModel(piwikApi)
    {
        var typesPromise = null;

        var model = {
            typesById: {},
            fetchTypeById: fetchTypeById,
            fetchAvailableTypes: fetchAvailableTypes,
            hasMultipleTypes: hasMultipleTypes,
            removeEditSiteIdParameterFromHash: removeEditSiteIdParameterFromHash,
            getEditSiteIdParameter: getEditSiteIdParameter
        };

        return model;

        function getEditSiteIdParameter() {
            var search = String(window.location.hash).substr('#/'.length);
            var searchParams = piwik.helper.getArrayFromQueryString(search);
            if (searchParams.editsiteid && $.isNumeric(searchParams.editsiteid)) {
                return searchParams.editsiteid;
            }
        }
        function removeEditSiteIdParameterFromHash() {
            window.location.hash = window.location.hash.replace(/editsiteid=\d+/g, '');
        }

        function hasMultipleTypes(typeId)
        {
            return fetchAvailableTypes().then(function (types) {
                return types && types.length > 1;
            });
        }

        function fetchTypeById(typeId)
        {
            return fetchAvailableTypes().then(function () {
                return model.typesById[typeId];
            });
        }

        function fetchAvailableTypes()
        {
            if (!typesPromise) {
                typesPromise = piwikApi.fetch({method: 'API.getAvailableMeasurableTypes', filter_limit: '-1'}).then(function (types) {

                    angular.forEach(types, function (type) {
                        model.typesById[type.id] = type;
                    });

                    return types;
                });
            }

            return typesPromise;
        }
    }
})();
