'use strict';

angular.module('subnavbar').component('subnavbar', {
  templateUrl: '/app/components/subnavbar/subnavbar.html',
  bindings: {
    onAction: '&',
    onApplyFilter: '&',
    data: '=',
    filter: '='
  },

  controller: function ($scope) {
    $scope.activeButton = this.data;
    $scope.filter = this.filter;

    this.setActiveButton = function (type) {
      $scope.activeButton = type;
      this.data = type;
      if (this.onAction) {
        this.onAction();
      }
    };

    $scope.show = function () {
      let v = "filter";
      const item = document.getElementById(v);
      if (item.classList.contains("hide")) {
        document.getElementById(v).classList.remove("hide");
        document.getElementById(v).classList.add("active");
      } else {
        document.getElementById(v).classList.remove("active");
        document.getElementById(v).classList.add("hide");
      }

    }
    this.applyFilter = () => {
      this.filter = $scope.filter;
      if (this.onApplyFilter) {
        this.onApplyFilter();
        $scope.show();
      }
    }
  }
})