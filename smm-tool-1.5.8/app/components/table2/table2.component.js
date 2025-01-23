'use strict';

angular.module('table2').component('table2', {
  templateUrl: '../../smm-tool-Frontend/smm-tool-1.5.8/app/components/table2/table2.html',
  bindings: {
    items: '=', // Input data passed to the table
    data: '=', // Selected
    count: '<',
    selectedtab: '=', // Selected
  },
  controller: function ($scope) {
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
    $scope.isDialogOpen = false;
    $scope.date = null;
    $scope.selectedImage = null;
    $scope.itemsPerPage = 2;
    $scope.currentPage = 1;
    $scope.imageCount = 0;
    $scope.imagePage = 0;
    $scope.handleAction = function (action, item) {
      $scope.show(item.id);
      $scope.$emit('childAction', { action, item });
    };
    $scope.openDialog = function (image, index) {
      $scope.imageCount = image.length;
      $scope.selectedImage = image;
      $scope.imagePage = index;
      $scope.isDialogOpen = true;
      console.log($scope.selectedImage);
    };

    $scope.closeDialog = function () {
      $scope.imagePage = 0;
      $scope.isDialogOpen = false;
      $scope.selectedImage = null;
    };
    // $scope.checkDate = function (date, id) {
    //   console.log(id);
    //   date = new Date(date);
    //   console.log(date);
    //   date = date.getMonth() + 1 + "/" + date.getDate() + "/" + date.getFullYear();
    //   console.log(date);
    //   if ($scope.date === null) {
    //     $scope.date = date;
    //     return true;
    //   } else if ($scope.date === date) {
    //     return false;
    //   } else {
    //     $scope.date = date;
    //     return true;
    //   }
    // }
    $scope.cachedDates = {};
    $scope.checkDate = function (date, id) {
      console.log($scope.cachedDates);
      if ($scope.cachedDates[id] !== undefined) {
        return $scope.cachedDates[id];
      }
      date = new Date(date);
      const formattedDate = date.getMonth() + 1 + "/" + date.getDate() + "/" + date.getFullYear();
      if ($scope.date === null || $scope.date !== formattedDate) {
        $scope.date = formattedDate;
        $scope.cachedDates[id] = true;
        return true;
      }
      $scope.cachedDates[id] = false;
      return false;
    };
    $scope.nextPage = function () {
      $scope.currentPage = $scope.currentPage + 1;
      $scope.cachedDates = {};
      $scope.date = null;
    }
    $scope.previousPage = function () {
      $scope.currentPage = $scope.currentPage - 1;
      $scope.cachedDates = {};
      $scope.date = null;
    }
  },
});
