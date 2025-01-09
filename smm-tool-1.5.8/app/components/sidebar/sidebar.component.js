'use strict';

angular.module('sidebar').component('sidebar', {
  templateUrl: '/app/components/sidebar/sidebar.html',
  bindings: {
    onConnect: '&',
    handel: '='
  },

  controller: function ($scope) {
    console.log(this.onConnect);
    $scope.connect2 = (action) => {
      console.log(this.onConnect);
      this.handel = action;
      if (this.onConnect) {
        console.log(this.onConnect);
        this.onConnect();
      }
    };
  }
})