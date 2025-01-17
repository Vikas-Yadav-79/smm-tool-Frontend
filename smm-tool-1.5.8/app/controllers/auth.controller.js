angular.module('myApp').controller('AuthController', function ($scope, $http, $location) {
    console.log('AuthController is loaded');

    $scope.registerData = { email: '', password: '', username: '' };
    $scope.loginData = { email: '', password: '' };
    $scope.updateData = { id: localStorage.getItem('user_id'), email: localStorage.getItem('user_email'), password: '', username: localStorage.getItem('user_name') };

    $scope.postData = {
        message: '',
        mediaType: '', // 'image', 'video', or leave blank for text-only
        mediaUrl: ''
    };

    $scope.pageId = ''; // Set your Facebook Page ID here
    $scope.accessToken = ''; // Retrieve the Page Access Token from your session


    $scope.socialAccounts = []; // To store the social media accounts

    // Function to fetch social media accounts
    $scope.getSocialMediaAccounts = function() {
        // Get the user ID from localStorage or session
        var userId = localStorage.getItem('user_id');

        if (!userId) {
            alert('User is not logged in');
            return;
        }

        // Send GET request to fetch social media accounts
        $http.get('http://localhost/CodeIgniter-2.2.0/index.php/facebook/getSocialMediaAccounts', {
            params: { user_id: userId }
        })
        .then(function(response) {
            if (response.data.status === 'success') {
                // Successfully retrieved the accounts, store them in the scope variable
                $scope.socialAccounts = response.data.accounts;
                console.log('Social media accounts:', $scope.socialAccounts);
            } else {
                alert('Error fetching accounts: ' + response.data.message);
            }
        }, function(error) {
            console.error('Error:', error);
            alert('An error occurred while fetching social media accounts');
        });
    };

    $scope.postToFacebook = function() {
        if (!$scope.pageId || !$scope.accessToken) {
            alert('Page ID and Access Token are required');
            return;
        }

        const payload = {
            pageId: $scope.pageId,
            accessToken: $scope.accessToken,
            postData: $scope.postData
        };

        $http.post('https://localhost/CodeIgniter-2.2.0/index.php/facebook/postMessage', payload)
            .then(function(response) {
                if (response.data.status === 'success') {
                    alert('Post published successfully!');
                    console.log('Response:', response.data.response);
                } else {
                    alert('Error posting to Facebook: ' + JSON.stringify(response.data.response));
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('An error occurred while posting to Facebook.');
            });
    };


    $scope.register = function() {
        if ($scope.registerForm.$valid) { 
            console.log('Registering with:', $scope.registerData);
            $http.post('http://localhost/CodeIgniter-2.2.0/index.php/register', $scope.registerData)
                .then(function (response) {
                    if (response.data.status === 'success') {
                        alert('Registration successful');
                        localStorage.setItem('user_id', response.data.user.id);
                        localStorage.setItem('user_email', response.data.user.email);
                        localStorage.setItem('user_name', response.data.user.username);
                        // $scope.updateData.password=response.data.user.password
                        $location.path('/dashboard'); // Redirect 
                    } else {
                        alert(response.data.message);
                    }
                }, function (error) {
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
                .then(function (response) {
                    if (response.data.status === 'success') {
                        alert('Login successful');
                        localStorage.setItem('user_id', response.data.user.id);
                        localStorage.setItem('user_email', response.data.user.email);

                        $location.path('/dashboard'); // Redirect 
                    } else {
                        alert(response.data.message);
                    }
                }, function (error) {
                    console.log('Error:', error);
                    alert('An error occurred while logging in');
                });
        } else {
            alert('Login form is invalid');
        }
    };

    // Update user function
    $scope.update = function () {
        if ($scope.updateForm.$valid) {
            console.log('Updating user with:', $scope.updateData);
            if($scope.updateData.password === ''){
                delete $scope.updateData.password;
            }
            
            $http.put('http://localhost/CodeIgniter-2.2.0/index.php/update', $scope.updateData)
                .then(function (response) {
                    if (response.data.status === 'success') {
                        alert('User updated successfully');
                        $location.path('/dashboard'); // Redirect to profile page after successful update
                    } else {
                        alert(response.data.message); // Show error message
                    }
                }, function (error) {
                    console.log('Error:', error);
                    alert('An error occurred while updating the user');
                });
        } else {
            alert('Update form is invalid');
        }
    };

    $scope.goToProfileUpdate = function() {
        alert("Hello");

        $location.path('/profile');
    };
    $scope.logout = function () {
        localStorage.removeItem('user_id');
        localStorage.removeItem('user_email');
        $location.path('/login');
    };


    

    $scope.facebookLogin = function() {
        FB.login(function(response) {
          if (response.authResponse) {
            // Successfully logged in, get the access token
            var accessToken = response.authResponse.accessToken;
    
            // Send the access token to the backend (CodeIgniter)
            $http.post('https://localhost/CodeIgniter-2.2.0/index.php/facebook/login', /*{ accessToken: accessToken }*/)
              .then(function(response) {
                console.log('Logged in successfully');
                // Handle the response, such as storing the token or redirecting
                $location.path('/dashboard');

              }, function(error) {
                console.log('Error logging in');
              });
          } else {
            console.log('User cancelled login or did not fully authorize.');
          }
        }, { scope: 'pages_manage_posts,pages_read_engagement,pages_manage_engagement,pages_show_list' }); // Request permissions
      };





});
// pages_read_user_engagement