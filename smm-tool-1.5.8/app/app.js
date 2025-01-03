// app/app.js
angular.module('myApp', ['ngRoute'])
  .config(function($routeProvider) {
    $routeProvider
      .when('/login', {
        templateUrl: 'app/components/auth/login.html',
        controller: 'AuthController' // Referencing the external controller
      })
      .when('/signup', {
        templateUrl: 'app/components/auth/signup.html',
        controller: 'AuthController' // Referencing the external controller
      })
      .when('/dashboard',{
        templateUrl: 'app/components/dashboardd/dashboard.html',
      })
      .otherwise({
        redirectTo: '/login'
      });
  });
