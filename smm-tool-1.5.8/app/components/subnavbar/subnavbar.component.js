'use strict';

angular.module('subnavbar').component('subnavbar', {
  templateUrl: '../../smm-tool-Frontend/smm-tool-1.5.8/app/components/subnavbar/subnavbar.html',
  bindings: {
    onAction: '&',
    onApplyFilter: '&',
    data: '=',
    filter: '=',
    onSearch: '&',
    onRest: '&',
  },

  controller: function ($scope) {
    $scope.activeButton = this.data;
    $scope.filter = this.filter;
    $scope.search = "";
    $scope.showcross = false;
    this.setActiveButton = (type) => {
      $scope.activeButton = type;
      this.data = type;
      if (this.onAction) {
        this.onAction({ type });
      }
    };
    $scope.triggerSearch = ($event) => {
      if ($event.key === 'Enter') {
        this.onSearch({ query: $scope.search });
        $event.target.blur();
        $scope.showcross = true;
      }
    }
    $scope.redoTable = () => {
      $scope.search = "";
      $scope.showcross = false;
      this.onRest();
      console.log(this.onRest());
    }
    setTimeout(() => {
      componentHandler.upgradeDom();
    }, 0);
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