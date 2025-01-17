angular.module('myApp').controller('AuthController', function ($scope, $http, $location) {
    $scope.registerData = { email: '', password: '', username: '' };
    $scope.loginData = { email: '', password: '' };
    $scope.updateData = { id: localStorage.getItem('user_id'), email: '', password: '', username: '' };

    $scope.register = function () {
        if ($scope.registerForm.$valid) {
            console.log('Registering with:', $scope.registerData);
            $http.post('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/register', $scope.registerData)
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
            $http.post('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/login', $scope.loginData)
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
            $http.put('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/update', $scope.updateData)
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
                $http.post('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/facebook/login', { accessToken: accessToken })
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



    // ohm's sidebar implementation
    $scope.items = [];
    $scope.data = [];
    $scope.selectedItem = "Scheduled";
    $scope.flag = false;
    $scope.user_id = localStorage.getItem('user_id');
    $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/posts/getposts/" + $scope.user_id)
        .then(function (response) {
            $scope.data = response.data;
            $scope.flag = true;
            $scope.items = $scope.data;
            console.log(response.data);
            /// to add filter of facebook instagram etc



            // filter to inistial selected item 
            $scope.items = $scope.items.filter((item) => item.status === 'scheduled');
            $scope.tableLength = Math.ceil($scope.items.length / 4);
        })
        .catch(function (error) {
            console.error("Error fetching Instagram login URL:", error);
        });

    $scope.filteredItems = [...$scope.items];
    $scope.filteredSearch = [...$scope.items];
    $scope.filter = {
        min: 0,
        max: 70,
        selectedOption: "",
        selectedType: "All",
        startDate: "",
        endDate: "",

    };

    $scope.filterData = (query) => {
        console.log(query);
        const searchQuery = query.toString().toLowerCase();
        console.log(searchQuery);
        $scope.filteredData = $scope.items.filter(item => {
            const content = item.content ? item.content.toString().toLowerCase() : '';
            return content.includes(searchQuery);
        });
        console.log($scope.$filteredData);
        $scope.items = $scope.filteredData;
        $scope.tableLength = Math.ceil($scope.items.length / 4);
    };

    $scope.connectHendal = function (action) {

        if (action === 'Facebook') {

        } else if (action === 'Instagram') {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/instagram/login_dilog';
            form.style.display = 'none';
            document.body.appendChild(form);
            form.submit();
        } else if (action === 'Linkedin') {

        }
    }
    $scope.onApplyFilter = function () {
        const { min, max, selectedOption, selectedType, startDate, endDate } = $scope.filter;
        if (selectedType !== 'All') {
            console.log('Selected type:', selectedType);
            $scope.filteredItems = $scope.filteredItems.filter((item) => {
                const matchesType = selectedType === 'All' || item.type === selectedType;
                return matchesType;
            });
        }
        if (startDate !== "" && endDate !== "") {
            $scope.filteredItems = $scope.filteredItems.filter((item) => {
                const itemDate = new Date(item.time);
                const withinDateRange =
                    (!startDate || itemDate >= new Date(startDate)) &&
                    (!endDate || itemDate <= new Date(endDate));
                return withinDateRange;
            });
        }
        if (selectedOption === 'asc') {
            $scope.filteredItems.sort((a, b) => new Date(a.time) - new Date(b.time));
        } else if (selectedOption === 'desc') {
            $scope.filteredItems.sort((a, b) => new Date(b.time) - new Date(a.time));
        }
        console.log($scope.filteredItems);
        $scope.items = $scope.filteredItems;
    };
    $scope.selectedTable = 'Scheduled';

    $scope.restTable = function () {
        console.log($scope.selectedTable);
        $scope.changeTabledata($scope.selectedTable);
    };

    $scope.changeTabledata = function (type) {
        let $filtered_data = [...$scope.data]; // Create a copy of the original data
        $scope.selectedTable = type; // Store the selected type
        if ($scope.selectedTable === 'Published') {
            $filtered_data = $filtered_data.filter((item) => item.status === 'published');
        } else if ($scope.selectedTable === 'Scheduled') {
            $filtered_data = $filtered_data.filter((item) => item.status === 'scheduled');
        } else if ($scope.selectedTable === 'Drafted') {
            $filtered_data = $filtered_data.filter((item) => item.status === 'draft');
        }
        $scope.items = $filtered_data; // Update the items displayed in the table 
        $scope.tableLength = Math.ceil($scope.items.length / 4);
    };

    $scope.$on('childAction', function (event, data) {
        if (data.action === 'edit') {
            alert(`Editing item: ${data.item.serialNo}`);
        } else if (data.action === 'analyze') {
            var dialog = document.querySelector('dialog');
            $scope.selectedItem = data.item;
            dialog.showModal();
        } else if (data.action === 'delete') {
            alert(`Deleting item: ${data.item.serialNo}`);
        }
    });
    $scope.closeDialog = function closeDialog() {
        var dialog = document.querySelector('dialog');
        $scope.selectedItem = null;
        dialog.close();
    }
});
