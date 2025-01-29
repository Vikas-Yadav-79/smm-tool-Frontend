'use strict';

angular.module('sidebar').component('sidebar', {
  templateUrl: '../../smm-tool-Frontend/smm-tool-1.5.8/app/components/sidebar/sidebar.html',
  bindings: {
    onConnect: '&',
  },

  controller: function ($scope, $http) {
    $scope.connected = {
      Instagram: false,
      Facebook: false,
      LinkedIn: false,
    }

    $http.get('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/checkConnection').then(function (response) {
      $scope.connectiondata = response.data.connection;
      console.log($scope.connectiondata);
      $scope.connectiondata.forEach(element => {
        if (element.account_name !== '' && element.platform !== 'Facebook') {
          $scope.connected[element.platform] = true;
        }
      });
    }, function (error) {

    });
    $scope.connect2 = (action) => {
      if (this.onConnect) {
        this.onConnect({ action });
      }
    };
  }
})