/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageUsersController', ManageUsersController);

    ManageUsersController.$inject = ['piwik', 'piwikApi', '$timeout', '$rootScope'];

    function ManageUsersController(piwik, piwikApi, $timeout, $rootScope) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;
        var alreadyEdited = {};

        this.isLoading = false;
        this.showCreateUser = true;

        function setIsLoading()
        {
            self.isLoading = true;
            $timeout(function () {
                piwik.helper.lazyScrollTo('.loadingManageUsers', 50);
            });
        }

        function sendUpdateUserAJAX(row) {
            var parameters = {};
            parameters.userLogin = $(row).children('#userLogin').html();
            var password = $(row).find('input#password').val();
            if (password != '-') parameters.password = password;
            parameters.email = $(row).find('input#email').val();
            parameters.alias = $(row).find('input#alias').val();

            setIsLoading();

            piwikApi.post({
                module: 'API',
                method: 'UsersManager.updateUser'
            }, parameters).then(function () {
                piwik.helper.redirect();
                self.isLoading = false;
            }, function () {
                self.isLoading = false;
            });
        }

        function sendDeleteUserAJAX(login) {

            setIsLoading();

            piwikApi.post({
                module: 'API',
                method: 'UsersManager.deleteUser'
            }, {userLogin: login}).then(function () {
                piwik.helper.redirect();
                self.isLoading = false;
            }, function () {
                self.isLoading = false;
            });
        }

        function sendAddUserAJAX(row) {
            var parameters = {};
            parameters.userLogin = $(row).find('input#useradd_login').val();
            parameters.password = $(row).find('input#useradd_password').val();
            parameters.email = $(row).find('input#useradd_email').val();
            parameters.alias = $(row).find('input#useradd_alias').val();

            setIsLoading();

            piwikApi.post({
                module: 'API',
                method: 'UsersManager.addUser'
            }, parameters).then(function () {
                piwik.helper.redirect();
                self.isLoading = false;
            }, function () {
                self.isLoading = false;
            });
        }

        function submitOnEnter(e) {
            var key = e.keyCode || e.which;
            if (key == 13) {
                $(this).find('.adduser').click();
                $(this).find('.updateuser').click();
            }
        }

        this.editUser = function (idRow) {
            if (alreadyEdited[idRow] == 1) {
                return;
            }

            alreadyEdited[idRow] = 1;

            var $row = $('tr#' + idRow);

            $row.find('.editable').keypress(submitOnEnter);
            $row.find('.editable').each(
                // make the fields editable
                // change the EDIT button to VALID button
                function (i, n) {
                    var contentBefore = $(n).text();
                    var idName = $(n).attr('id');
                    if (idName != 'userLogin') {
                        var contentAfter = '<input id="' + idName + '" value="' + piwikHelper.htmlEntities(contentBefore) + '" size="25" />';
                        $(n).html(contentAfter);
                    }
                }
            );

            var $delete = $row.find('.edituser');

            $delete
                .toggle()
                .parent()
                .prepend($('<a class="canceluser">' + _pk_translate('General_OrCancel', ['', '']) + '</a>')
                    .click(function () {
                        piwikHelper.redirect();
                    })
                ).prepend($('<input type="submit" class="btn updateuser"  value="' + _pk_translate('General_Save') + '" />')
                .click(function () {
                    var $tr = $('tr#' + idRow);

                    sendUpdateUserAJAX($tr);
                })
            );
        }
        
        this.createUser = function () {

            var parameters = {isAllowed: true};
            $rootScope.$emit('UsersManager.initAddUser', parameters);
            if (parameters && !parameters.isAllowed) {
                return;
            }

            this.showCreateUser = false;

            var numberOfRows = $('table#users')[0].rows.length;
            var newRowId = numberOfRows + 1;
            newRowId = 'row' + newRowId;

            $($.parseHTML(' <tr id="' + newRowId + '" class="addNewUserRow">\
				<td><input id="useradd_login" placeholder="username" size="10" maxlength="100" /></td>\
				<td><input id="useradd_password" placeholder="password" size="10" /></td>\
				<td><input id="useradd_email" placeholder="email@domain.com" size="15" maxlength="100" /></td>\
				<td><input id="useradd_alias" placeholder="alias" size="15" maxlength="45" /></td>\
				<td>-</td>\
                <td>-</td>\
				<td><input type="submit" class="btn adduser"  value="' + _pk_translate('General_Save') + '" /></td>\
	  			<td><span class="cancel">' + sprintf(_pk_translate('General_OrCancel'), "", "") + '</span></td>\
	 		</tr>'))
                .appendTo('#users')
            ;
            $('#' + newRowId).keypress(submitOnEnter);
            $('.adduser').click(function () { sendAddUserAJAX($('tr#' + newRowId)); });
            $('.cancel').click(function () {
                piwikHelper.hideAjaxError();
                $(this).parents('tr').remove();
                $('.add-user').toggle();
                self.showCreateUser = true;
            });
        };

        this.deleteUser = function (loginToDelete) {

            var idRow = $(this).attr('id');

            var message = _pk_translate('UsersManager_DeleteConfirm');
            $('#confirmUserRemove').find('h2').text(sprintf(message, '"' + loginToDelete + '"'));

            piwikHelper.modalConfirm('#confirmUserRemove', {yes: function () {
                sendDeleteUserAJAX(loginToDelete);
            }});
        };

        this.regenerateUserTokenAuth = function (userLogin) {
            var parameters = { userLogin: userLogin };
            var confirm = '#confirmTokenRegenerate';

            if (userLogin == piwik.userLogin) {
                confirm = '#confirmTokenRegenerateSelf';
            }

            piwikHelper.modalConfirm(confirm, {yes: function () {
                setIsLoading();

                piwikApi.post({
                    module: 'API',
                    method: 'UsersManager.regenerateTokenAuth'
                }, parameters).then(function () {
                    piwik.helper.redirect();
                    self.isLoading = false;
                }, function () {
                    self.isLoading = false;
                });
            }});
        };

        $(document).ready(function () {
            var alreadyEdited = [];
            // when click on edituser, the cells become editable

            // Show the token_auth
            $('.token_auth').click(function () {
                var token = $(this).data('token');

                if ($('.token_auth_content', this).text() != token) {
                    $('.token_auth_content', this).text(token);
                }
            });
        });

    }
})();