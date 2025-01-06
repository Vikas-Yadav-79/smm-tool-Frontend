angular.module('myApp').controller('AuthController', function($scope, $http, $location) {
    console.log('AuthController is loaded');
    
    $scope.registerData = { email: '', password: '', username: '' };
    $scope.loginData = { email: '', password: '' };
    $scope.updateData = { id: localStorage.getItem('user_id'), email: '', password: '', username: '' };

    $scope.register = function() {
        if ($scope.registerForm.$valid) { 
            console.log('Registering with:', $scope.registerData);
            $http.post('http://localhost/CodeIgniter-2.2.0/index.php/register', $scope.registerData)
                .then(function(response) {
                    if (response.data.status === 'success') {
                        alert('Registration successful');
                        localStorage.setItem('user_id', response.data.user.id);
                        localStorage.setItem('user_email', response.data.user.email);
                        $location.path('/dashboard'); // Redirect 
                    } else {
                        alert(response.data.message); 
                    }
                }, function(error) {
                    console.log('Error:', error);
                    alert('An error occurred during registration');
                });
        } else {
            alert('Registration form is invalid');
        }
    };

    $scope.login = function() {
        if ($scope.loginForm.$valid) {
            console.log('Logging in with:', $scope.loginData);
            $http.post('http://localhost/CodeIgniter-2.2.0/index.php/login', $scope.loginData)
                .then(function(response) {
                    if (response.data.status === 'success') {
                        alert('Login successful');
                        localStorage.setItem('user_id', response.data.user.id);
                        localStorage.setItem('user_email', response.data.user.email);
                    
                        $location.path('/dashboard'); // Redirect 
                    } else {
                        alert(response.data.message); 
                    }
                }, function(error) {
                    console.log('Error:', error);
                    alert('An error occurred while logging in');
                });
        } else {
            alert('Login form is invalid');
        }
    };

    // Update user function
    $scope.update = function() {
        if ($scope.updateForm.$valid) {
            console.log('Updating user with:', $scope.updateData);
            // if($scope.updateData.password === ''){
            //     $scope.updateData.password = ;

            // }
            $http.put('http://localhost/CodeIgniter-2.2.0/index.php/update', $scope.updateData)
                .then(function(response) {
                    if (response.data.status === 'success') {
                        alert('User updated successfully');
                        $location.path('/dashboard'); // Redirect to profile page after successful update
                    } else {
                        alert(response.data.message); // Show error message
                    }
                }, function(error) {
                    console.log('Error:', error);
                    alert('An error occurred while updating the user');
                });
        } else {
            alert('Update form is invalid');
        }
    };
    $scope.goToProfileUpdate = function() {
        $location.path('/profile');
    };
    $scope.logout = function (){
        localStorage.removeItem('user_id');
        localStorage.removeItem('user_email');
        $location.path('/login');
    }
});
