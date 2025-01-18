'use strict';

angular.module('table').component('table', {
  templateUrl: '/app/components/table/table.html',
  bindings: {
    items: '<', // Input data passed to the table
    onAction: '&', // Callback for actions on items
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

    $scope.handleAction = function (action, item) {
      if (this.onAction) {
        this.onAction({ action, item });
      }
    };
  },
});
