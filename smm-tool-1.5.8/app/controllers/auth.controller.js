angular.module('myApp').controller('AuthController', function ($scope, $http, $location) {
    console.log('AuthController is loaded');

    $scope.registerData = { email: '', password: '', username: '' };
    $scope.loginData = { email: '', password: '' };
    $scope.updateData = { id: localStorage.getItem('user_id'), email: '', password: '', username: '' };

    $scope.register = function () {
        if ($scope.registerForm.$valid) {
            console.log('Registering with:', $scope.registerData);
            $http.post('http://localhost/CodeIgniter-2.2.0/index.php/register', $scope.registerData)
                .then(function (response) {
                    if (response.data.status === 'success') {
                        alert('Registration successful');
                        localStorage.setItem('user_id', response.data.user.id);
                        localStorage.setItem('user_email', response.data.user.email);
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

    $scope.login = function () {
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
            // if($scope.updateData.password === ''){
            //     $scope.updateData.password = ;

            // }
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
    $scope.goToProfileUpdate = function () {
        $location.path('/profile');
    };
    $scope.logout = function () {
        localStorage.removeItem('user_id');
        localStorage.removeItem('user_email');
        $location.path('/login');
    };

    $scope.facebookLogin = function () {
        FB.login(function (response) {
            if (response.authResponse) {
                // Successfully logged in, get the access token
                var accessToken = response.authResponse.accessToken;

                // Send the access token to the backend (CodeIgniter)
                $http.post('http://localhost/CodeIgniter-2.2.0/index.php/facebook/login', { accessToken: accessToken })
                    .then(function (response) {
                        console.log('Logged in successfully');
                        // Handle the response, such as storing the token or redirecting
                        $location.path('/dashboard');

                    }, function (error) {
                        console.log('Error logging in');
                    });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, { scope: 'pages_manage_posts,pages_read_engagement,publish_pages' }); // Request permissions
    };


    const ctrl = this;

    // ohm's sidebar implementation
    $scope.items = [];
    $scope.selectedItem = "";
    $scope.authUrl = "https://www.instagram.com/oauth/authorize?enable_fb_login=0&force_authentication=1&client_id=1382135032788086&redirect_uri=https://localhost/codeigniter/index.php/instagram/login&response_type=code&scope=instagram_business_basic%2Cinstagram_business_manage_messages%2Cinstagram_business_manage_comments%2Cinstagram_business_content_publish";
    $scope.flag = false;
    $http.get("http://localhost/codeigniter/index.php/instagram/replay")
        .then(function (response) {
            $scope.data = response.data;
            $scope.flag = true;
            $scope.items = $scope.data;
        })
        .catch(function (error) {
            console.error("Error fetching Instagram login URL:", error);
        });
    this.filteredItems = [...$scope.items];

    this.onApplyFilter = function (filters) {
        console.log('Filters applied:', filters);

        const { min, max, selectedOption, selectedType, startDate, endDate } = filters;

        if (selectedType !== 'All') {
            console.log('Selected type:', selectedType);
            this.filteredItems = this.filteredItems.filter((item) => {
                const matchesType = selectedType === 'All' || item.type === selectedType;
                return matchesType;
            });
        }
        if (startDate !== "" && endDate !== "") {
            this.filteredItems = this.filteredItems.filter((item) => {
                const itemDate = new Date(item.time);
                const withinDateRange =
                    (!startDate || itemDate >= new Date(startDate)) &&
                    (!endDate || itemDate <= new Date(endDate));
                return withinDateRange;
            });
        }
        if (selectedOption === 'asc') {
            this.filteredItems.sort((a, b) => new Date(a.time) - new Date(b.time));
        } else if (selectedOption === 'desc') {
            this.filteredItems.sort((a, b) => new Date(b.time) - new Date(a.time));
        }
        console.log(this.filteredItems);
        this.items = this.filteredItems;
    };
    this.selectedTable = 'Scheduled';

    this.changeTabledata = function (type) {
        if (type === 'Published') {
            this.selectedTable = 'Published';
            console.log('Published');
        } else if (type === 'Scheduled') {
            this.selectedTable = 'Scheduled';
            console.log('Scheduled');
        } else if (type === 'Drafted') {
            this.selectedTable = 'Drafted';
            console.log('Drafted');
        }
    }
    this.handleTableAction = function (action, item) {
        if (action === 'edit') {
            alert(`Editing item: ${item.serialNo}`);
        } else if (action === 'analyze') {
            var dialog = document.querySelector('dialog');
            $scope.selectedItem = item;
            dialog.showModal();
        } else if (action === 'delete') {
            alert(`Deleting item: ${item.serialNo}`);
        }
    };
    $scope.closeDialog = function closeDialog() {
        var dialog = document.querySelector('dialog');
        $scope.selectedItem = null;
        dialog.close();
    }
});
