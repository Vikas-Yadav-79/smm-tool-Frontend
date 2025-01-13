'use strict';

angular.module('table').component('table', {
  templateUrl: '/app/components/table/table.html',
  bindings: {
    items: '<', // Input data passed to the table
  },
  controller: function ($rootScope, $scope, $timeout) {
    $scope.items = this.items;
    $scope.show = function (id) {
      let v = "id-" + id;
      const item = document.getElementById(v);
      if (item.classList.contains("hide")) {
        document.getElementById(v).classList.remove("hide");
        document.getElementById(v).classList.add("active");
      } else {
        document.getElementById(v).classList.remove("active");
        document.getElementById(v).classList.add("hide");
      }

    }

    $scope.handleAction = function (action, item) {
      $scope.show(item.id);
      $scope.$emit('childAction', { action, item });
    };
  },
});
