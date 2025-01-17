'use strict';

angular.module('sidebar').component('sidebar', {
  templateUrl: '/app/components/sidebar/sidebar.html',

  controller: function ($scope) {
    $scope.connect = function (action) {
      confirm('Are you sure you want to connect' + action);
      if (action === 'Facebook') {

      } else if (action === 'Instagram') {
        // window.open($scope.authUrl, 'width=600,height=600');
      } else if (action === 'Linkedin') {

      }
    };
  }
})