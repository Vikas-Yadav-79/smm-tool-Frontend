'use strict';

angular.module('sidebar').component('sidebar', {
  templateUrl: '/app/components/sidebar/sidebar.html',
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