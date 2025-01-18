'use strict';

angular.module('table2').component('table2', {
  templateUrl: '/app/components/table2/table2.html',
  bindings: {
    items: '=', // Input data passed to the table
    data: '=', // Selected
    count: '<'
  },
  controller: function ($rootScope, $scope, $timeout) {
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
    $scope.itemsPerPage = 4;
    $scope.currentPage = 1;
    $scope.handleAction = function (action, item) {
      $scope.show(item.id);
      $scope.$emit('childAction', { action, item });
    };
  },
});
