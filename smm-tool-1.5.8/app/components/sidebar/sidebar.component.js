'use strict';

angular.module('sidebar').component('sidebar', {
  templateUrl: '../../smm-tool-Frontend/smm-tool-1.5.8/app/components/sidebar/sidebar.html',
  bindings: {
    onConnect: '&',
  },

  controller: function ($scope) {
    $scope.connect2 = (action) => {
      if (this.onConnect) {
        this.onConnect({ action });
      }
    };
  }
})