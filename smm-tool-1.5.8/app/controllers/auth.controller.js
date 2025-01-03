angular.module('myApp').controller('AuthController', function($scope) {
    console.log('AuthController is loaded');
    $scope.loginData = { username: '', password: '' };
    $scope.signupData = { username: '', email: '', password: '' };
  
    $scope.login = function() {
      if ($scope.loginForm.$valid) {
        console.log('Logging in with:', $scope.loginData);
      } else {
        console.log('Login form is invalid');
      }
    };
  
    $scope.signup = function() {
      if ($scope.signupForm.$valid) {
        console.log('Signing up with:', $scope.signupData);
      } else {
        console.log('Signup form is invalid');
      }
    };
  });
  