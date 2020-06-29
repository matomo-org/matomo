import angular from 'angular';
import ngDialog from 'ng-dialog';

let app = angular.module('exampleApp', [
    ngDialog
]);

app.run((ngDialog) => {
    ngDialog.open({
        template: 'dialog',
        className: 'ngdialog-theme-default'
    });
});
