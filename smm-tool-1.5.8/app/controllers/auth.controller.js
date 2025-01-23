angular.module('myApp').controller('AuthController', function ($scope, $http, $location) {
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
    $scope.getSocialMediaAccounts = function () {
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
            .then(function (response) {
                if (response.data.status === 'success') {
                    // Successfully retrieved the accounts, store them in the scope variable
                    $scope.socialAccounts = response.data.accounts;
                    console.log('Social media accounts:', $scope.socialAccounts);
                } else {
                    alert('Error fetching accounts: ' + response.data.message);
                }
            }, function (error) {
                console.error('Error:', error);
                alert('An error occurred while fetching social media accounts');
            });
    };

    $scope.postToFacebook = function () {
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
            .then(function (response) {
                if (response.data.status === 'success') {
                    alert('Post published successfully!');
                    console.log('Response:', response.data.response);
                } else {
                    alert('Error posting to Facebook: ' + JSON.stringify(response.data.response));
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                alert('An error occurred while posting to Facebook.');
            });
    };


    $scope.register = function () {
        if ($scope.registerForm.$valid) {
            console.log('Registering with:', $scope.registerData);
            $http.post('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/register', $scope.registerData)
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
                    alert('An error occurred while logging in ');
                });
        } else {
            alert('Login form is invalid');
        }
    };
    // Update user function
    $scope.update = function () {
        if ($scope.updateForm.$valid) {
            console.log('Updating user with:', $scope.updateData);
            if ($scope.updateData.password === '') {
                delete $scope.updateData.password;
            }
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
        alert("Hello");

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
                $http.post('http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/facebook/login')
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
        }, { scope: 'pages_manage_posts,pages_read_engagement,pages_manage_engagement,pages_show_list' }); // Request permissions
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
            $scope.data.forEach(post => {
                const dateObj = new Date(post.published_time);
                post.published_time = dateObj;
            });
            $scope.items = $scope.data;
            /// to add filter of facebook instagram etc



            // filter to inistial selected item 
            $scope.items = $scope.items.filter((item) => item.status === 'scheduled');
            $scope.tableLength = Math.ceil($scope.items.length / 2);
        })
        .catch(function (error) {
            console.error("Error fetching Instagram login URL:", error);
        });

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
        const searchQuery = query.toString().toLowerCase();
        $scope.filteredData = $scope.items.filter(item => {
            const content = item.content ? item.content.toString().toLowerCase() : '';
            return content.includes(searchQuery);
        });
        $scope.items = $scope.filteredData;
        $scope.tableLength = Math.ceil($scope.items.length / 2);
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

        $scope.filteredItems = [...$scope.items];
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
        if ($scope.selectedTable === "Scheduled") {
            if (selectedOption === 'asc') {
                $scope.filteredItems.sort((a, b) => new Date(a.schedule_time) - new Date(b.schedule_time));
            } else if (selectedOption === 'desc') {
                $scope.filteredItems.sort((a, b) => new Date(b.schedule_time) - new Date(a.schedule_time));
            }
        } else if ($scope.selectedTable === "Published") {
            if (selectedOption === 'asc') {
                $scope.filteredItems.sort((a, b) => new Date(a.published_time) - new Date(b.published_time));
            } else if (selectedOption === 'desc') {
                $scope.filteredItems.sort((a, b) => new Date(b.published_time) - new Date(a.published_time));
            }
        }
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
        $scope.tableLength = Math.ceil($scope.items.length / 2);
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
// pages_read_user_engagement