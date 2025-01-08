'use strict';

angular.module('subnavbar').component('subnavbar', {
  templateUrl: '/app/components/subnavbar/subnavbar.html',
  bindings: {
    onAction: '&',
    onApplyFilter: '&',
    data: '<'
  },

  controller: function ($scope) {
    $scope.activeButton = 'Published';
    $scope.filter = {
      min: 0,
      max: 70,
      selectedOption: "",
      selectedType: "All",
      startDate: "",
      endDate: "",

    };

    this.setActiveButton = function (type) {
      if (this.onAction) {
        this.activeButton = type;
        this.onAction({ type });
      }
    };
    this.applyFilter = function () {
      console.log($scope.endDate);
      if (this.onApplyFilter) {
        this.onApplyFilter({
          filters: {
            min: $scope.min,
            max: $scope.max,
            selectedOption: $scope.selectedOption,
            selectedType: $scope.selectedType,
            startDate: $scope.startDate,
            endDate: $scope.endDate,
          },
        });
      }
    }
  }
})