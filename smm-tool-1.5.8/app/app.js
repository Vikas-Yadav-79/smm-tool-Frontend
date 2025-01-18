// app/app.js
angular.module('myApp', ['ngRoute', 'sidebar', 'subnavbar', 'table2', 'comment'])
  .config(function ($routeProvider, $locationProvider) {
    $routeProvider
      .when('/login', {
        templateUrl: 'app/components/auth/login.html',
        controller: 'AuthController', // Referencing the external controller
        resolve: {
          auth: function ($location) {
            if (localStorage.getItem('user_id')) {
              $location.path('/dashboard');
            }

          }
        }
      })
      .when('/signup', {
        templateUrl: 'app/components/auth/signup.html',
        controller: 'AuthController', // Referencing the external controller
        resolve: {
          auth: function ($location) {
            if (localStorage.getItem('user_id')) {
              $location.path('/dashboard');
            }

          }
        }
      })
      .when('/dashboard', {
        templateUrl: 'app/components/dashboardd/dashboard.html',
        controller: 'AuthController',
        resolve: {
          auth: function ($location) {
            if (!localStorage.getItem('user_id')) {
              $location.path('/login');
            }

          }
        }
      })
      .when('/profile', {
        templateUrl: 'app/components/dashboardd/profile-update.html',
        controller: 'AuthController',
        resolve: {
          auth: function ($location) {
            if (!localStorage.getItem('user_id')) {
              $location.path('/login');
            }

          }
        }
      })
      .otherwise({
        redirectTo: '/login'
      });

    $locationProvider.html5Mode(false);
  });
