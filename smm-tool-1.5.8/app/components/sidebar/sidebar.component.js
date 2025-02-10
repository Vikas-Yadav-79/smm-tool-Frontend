'use strict';

angular.module('sidebar').component('sidebar', {
  templateUrl: '../../smm-tool-Frontend/smm-tool-1.5.8/app/components/sidebar/sidebar.html',
  bindings: {
    onConnect: '&',
    onApplyFilter: '&',
    data: '=',
    selectedHandel: '='
  },

  controller: function ($scope, $http) {
    console.log(this.onApplyFilter);
    $scope.changedata = (handle) => {
      if (this.onApplyFilter) {
        $scope.selectedHandel = handle;
        this.onApplyFilter({ handle });
      }
    }
    $scope.connect2 = (action) => {
      if (this.onConnect) {
        this.onConnect({ action });
      }
    };
  }
})