<?php
// application/controllers/Facebook.php
class Facebook extends CI_Controller {
    private $access_token;
    private $page_id = '554959331025659'; 
    public $page_access_token;
    public $longTermAccessToken;


    public function __construct() {
        parent::__construct();
        // Load the Facebook SDK
        $this->load->library('Facebook_lib');
        $this->load->helper('url');
        $this->load->library('session');
        $this->access_token = $this->session->userdata('accessToken');
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');  // Allow all origins
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE','OPTIONS');  // Allow these methods
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    // Handle Facebook login
   
    
    public function get_page_access_token() {
        // $user_access_token = $this->longTermAccessToken;
        $user_access_token = $this->session->userdata('facebook_Long_term_access_token');

        if (empty($user_access_token)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'longTermAccessToken is missing.'
            ]);
            return;
        }

        $url = "https://graph.facebook.com/v21.0/{$this->page_id}?fields=access_token&access_token={$user_access_token}";

        $response = $this->make_api_request($url);
        if (isset($response->access_token)) {
            $this->page_access_token=$response->access_token;
            $this->session->set_userdata('facebook_Page_access_token', $this->page_access_token);
            // return $response->access_token;  
            echo json_encode([
                'status' => 'success',
                'pageAccessToken' => $this->page_access_token
            ]);

        } else {
            // Handle error (invalid token, etc.)
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing useraccessToken.'
            ]);
        }
    }

    public function getLongTermAccessToken() {
        $appId = '480761234690688';  // Your Facebook App ID
        $appSecret = '14a6fe0369f8c0ef1012f48563db2d57';  // Your Facebook App Secret
    
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        // Check if 'useraccessToken' is present in the request
        if (isset($inputData['useraccessToken'])) {
            $shortTermToken = $inputData['useraccessToken'];  // Get the short-term token from the request    
            // Facebook API URL to exchange short-term token for long-term token
            $url = "https://graph.facebook.com/v21.0/oauth/access_token?grant_type=fb_exchange_token&client_id=$appId&client_secret=$appSecret&fb_exchange_token=$shortTermToken";
    
            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            // Decode the response
            $responseData = json_decode($response, true);
    
            error_log("Facebook API Response: " . print_r($responseData, true));
            var_dump($responseData);

            // Check if the response contains the long-term token
            if ($httpCode === 200 && isset($responseData['access_token'])) {
                // Return the long-term access token as JSON response

                $this->longTermAccessToken = $responseData['access_token'];

                var_dump($this->longTermAccessToken);

                $this->session->set_userdata('facebook_Long_term_access_token', $this->longTermAccessToken);

                // var_dump($this->longTermAccessToken);
    

                echo json_encode([
                    'status' => 'success',
                    'longTermAccessToken' => $responseData['access_token']
                ]);
            } else {
                // Handle error and return error message
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Unable to get long-term access token.',
                    'details' => $responseData
                ]);
            }
        } else {
            // Handle missing useraccessToken in the request
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing useraccessToken.'
            ]);
        }
    }



    private function make_api_request($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log the response body and HTTP code for debugging
        error_log("Response: " . $response);
        error_log("HTTP Code: " . $httpCode);

        return json_decode($response);
    }

    public function getSocialMediaAccounts() {
        // Get the logged-in user's ID from the session
        $userId = $this->session->userdata('user_id');
    
        // Fetch all social media accounts associated with the user
        $this->db->select('*');
        $this->db->from('social_accounts');
        $this->db->where('user_id', $userId);
        $query = $this->db->get();
    
        // Check if any accounts are found
        if ($query->num_rows() > 0) {
            echo json_encode(['status' => 'success', 'accounts' => $query->result_array()]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No accounts found']); // No accounts found
        }
    }
    

    public function login() {
        // Get the access token from the POST request
        $accessToken = $this->input->post('accessToken');
        var_dump($accessToken);
        
        // Store the access token in session
        $this->session->set_userdata('facebook_access_token', $accessToken);
    
        // Use the token to get user data from Facebook
        $userData = $this->facebook_lib->getUserData($accessToken); // id , name from facebook
        // var_dump($userData);
        // exit;
        
        // Example: Get the user ID from the response
        $facebookUserID = $userData['id'];
        $userName = $userData['name']; // You can use other user details as well
        
        // Check if the user already exists in the social_accounts table
        $this->db->where('user_id', $this->session->userdata('user_id')); // Assuming you have user_id stored in session
        $this->db->where('platform', 'Facebook');
        $query = $this->db->get('social_accounts');
        
        if ($query->num_rows() > 0) {
            // If the user already has a Facebook account, update the access token
            $this->db->where('user_id', $this->session->userdata('user_id'));
            $this->db->where('platform', 'Facebook');
            $this->db->update('social_accounts', ['access_token' => $accessToken]);
        } else {
            // If the user doesn't have a Facebook account, insert a new record
            $data = [
                'user_id' => $this->session->userdata('user_id'), // Assuming user_id is stored in session
                'platform' => 'Facebook',
                'account_name' => $userName, // Store the user's name or other relevant details
                'access_token' => $accessToken,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('social_accounts', $data);
        }
        
        // You can now use this data to manage posts, comments, etc.
        // Example: Get user details
        echo json_encode($userData);
    }
    public function postMessage() {
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);

        // Extract the data
        $pageId = '554959331025659';
        $accessToken =$this->session->userdata('facebook_Page_access_token');
        $postData = $inputData['postData'];

        // Facebook Graph API URL for posting to a page
        $facebookGraphUrl = "https://graph.facebook.com/v16.0/$pageId/feed";

        // Prepare the data for the API request
        $data = [
            'message' => $postData['message'],
            'access_token' => $accessToken
        ];

        // If media is included
        if (!empty($postData['mediaType']) && !empty($postData['mediaUrl'])) {
            if ($postData['mediaType'] === 'image') {
                $facebookGraphUrl = "https://graph.facebook.com/v16.0/$pageId/photos";
                $data['url'] = $postData['mediaUrl'];
            } elseif ($postData['mediaType'] === 'video') {
                $facebookGraphUrl = "https://graph.facebook.com/v16.0/$pageId/videos";
                $data['file_url'] = $postData['mediaUrl'];
                $data['description'] = $postData['message'];
            }
        }

        // Make the HTTP POST request to Facebook Graph API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        $responseData = json_decode($response, true);


        // Send the response back to the frontend
         // If the post is successful
    if ($httpCode === 200 && isset($responseData['id'])) {
        // Get the platform post ID
        $platformPostId = $responseData['id'];
        $user_id = $this->session->userdata('user_id');

        // if (!$user_id) {
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'User is not logged in.'
        //     ]);
        //     return;
        // }


        $this->db->select('id'); // Select only the 'id' column
        $this->db->from('social_accounts'); // Specify the table
        $this->db->where('user_id', $user_id); // Condition: user_id matches session user_id
        $this->db->where('platform', 'Facebook'); // Condition: platform is Facebook
        $query = $this->db->get();



        // Prepare the data for storing in the database
        

        // Insert the post content into the database

        if ($query && $query->num_rows() > 0) {
            // Fetch the social_account_id
            $result = $query->row(); // Get the first row of the result
            $social_account_id = $result->id;

            $platformPostId1 = isset($responseData['post_id']) ? $responseData['post_id'] : $responseData['id'];


            $postContent = [
                // Assuming the user is logged in
               'user_id' => $user_id,
               'social_account_id' => $social_account_id,
               'title' => $postData['message'],
               'content' => $postData['message'],
               'status' => 'published', // You can set it to 'scheduled' or 'draft' if needed
               'published_time' => date('Y-m-d H:i:s'),
               'platform_post_ids' => $platformPostId1, // Store platform post ID
               'image_urls' => isset($postData['mediaUrl']) ? json_encode([$postData['mediaUrl']]) : null, // Store media URL if available
           ];

           $this->db->insert('posts', $postContent);

        }

        // Return the appropriate response based on media type
        if ($postData['mediaType'] === 'image' || $postData['mediaType'] === 'video') {
            echo json_encode([
                'status' => 'success',
                'response' => [
                    'id' => $platformPostId,
                    'post_id' => $platformPostId1
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'response' => [
                    'id' => $platformPostId
                ]
            ]);
        }
    } else {
        // If the request failed, return an error response
        echo json_encode([
            'status' => 'error',
            'response' => $responseData
        ]);
    }
    }
    public function postMessage1() {
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        $pageId = '554959331025659';
        $accessToken = $this->session->userdata('facebook_Page_access_token');
        $postData = $inputData['postData'];
    
        $facebookGraphUrl = "https://graph.facebook.com/v16.0/$pageId/feed";
    
        $data = [
            'message' => $postData['message'],
            'access_token' => $accessToken,
        ];
    
        // If media is included
        if (!empty($postData['mediaType']) && !empty($postData['mediaUrls'])) {
            if ($postData['mediaType'] === 'image') {
                $attachedMedia = [];
                foreach ($postData['mediaUrls'] as $imageUrl) {
                    // Upload each image as unpublished
                    $uploadData = [
                        'url' => $imageUrl,
                        'published' => false,
                        'access_token' => $accessToken,
                    ];
    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v16.0/$pageId/photos");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($uploadData));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
    
                    $responseData = json_decode($response, true);
    
                    if ($httpCode === 200 && isset($responseData['id'])) {
                        // Add the uploaded image ID to the attached_media array
                        $attachedMedia[] = [
                            'media_fbid' => $responseData['id'],
                        ];
                    } else {
                        // Handle upload error
                        echo json_encode([
                            'status' => 'error',
                            'response' => $responseData,
                        ]);
                        return;
                    }
                }
    
                // Add attached_media to the post data
                if (!empty($attachedMedia)) {
                    $data['attached_media'] = json_encode($attachedMedia);
                }
            }
        }
    
        // Make the HTTP POST request to create the post
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        $responseData = json_decode($response, true);
    
        // Send the response back to the frontend
        if ($httpCode === 200 && isset($responseData['id'])) {
            echo json_encode([
                'status' => 'success',
                'response' => $responseData,
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'response' => $responseData,
            ]);
        }
    }
    
    


    public function getPostLikes() {
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        // Extract the data
        // $postId = $inputData['postId'];
        // $accessToken = $inputData['pageaccessToken'];
    

        $postId = $this->input->get('postId');
        // $accessToken = $this->input->get('pageaccessToken');
        $accessToken = $this->session->userdata('facebook_Page_access_token');
        // var_dump($accessToken);
        // exit;

        // Facebook Graph API URL for fetching likes
        $facebookGraphUrl = "https://graph.facebook.com/v16.0/$postId/likes?access_token=$accessToken";
    
        // Make the HTTP GET request to Facebook Graph API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        $responseData = json_decode($response, true);
    
        // Check if the request was successful
        if ($httpCode === 200 && isset($responseData['data'])) {
            echo json_encode([
                'status' => 'success',
                'likes' => $responseData['data'] // List of likes
            ]);
        } else {
            // If the request failed, return an error response
            echo json_encode([
                'status' => 'error',
                'response' => $responseData
            ]);
        }
    }

 
    
    public function replyToComment() {
        // Get the JSON input data from Postman (comment ID, message, access token)
        $input_data = json_decode(file_get_contents('php://input'), true);

        // Get the access token, comment ID, and reply message from the request
        $access_token = $this->session->userdata('facebook_Page_access_token');;
        $comment_id = $input_data['comment_id']; // The ID of the comment you're replying to
        $message = $input_data['message']; // The message you want to reply with

        // Construct the URL to reply to the comment
        $url = "https://graph.facebook.com/v12.0/{$comment_id}/comments?message=" . urlencode($message) . "&access_token={$access_token}";

        // Initialize CURL to send the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        // Output the response from Facebook
        echo $response;
    }



        public function getAllFacebookPosts() {
            // Get the page access token from session or wherever it's stored
            $pageAccessToken = $this->session->userdata('facebook_Page_access_token'); // Assuming it's stored in session
        
            if (empty($pageAccessToken)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Page access token is missing'
                ]);
                return;
            }
        
            // Facebook Graph API URL to get posts from the page
            $facebookGraphUrl = "https://graph.facebook.com/v21.0/me/posts?access_token=" . $pageAccessToken;
        
            // Initialize cURL to make the API request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            // Execute the cURL request and capture the response
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        
            // Decode the response
            $responseData = json_decode($response, true);
        
            // Check if the request was successful
            if ($httpCode === 200) {
                // Loop through each post and get the images
                foreach ($responseData['data'] as &$post) {
                    // Fetch the post details to get attachments (images)
                    $postId = $post['id'];
                    $postDetailsUrl = "https://graph.facebook.com/v21.0/{$postId}?fields=attachments&access_token={$pageAccessToken}";
        
                    // Make a request to get the post details
                    $ch2 = curl_init();
                    curl_setopt($ch2, CURLOPT_URL, $postDetailsUrl);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        
                    // Execute the request and get the response
                    $postDetailsResponse = curl_exec($ch2);
                    $postDetailsData = json_decode($postDetailsResponse, true);
                    curl_close($ch2);
        
                    // Check if there are attachments (images)
                    if (isset($postDetailsData['attachments']['data'])) {
                        $post['images'] = $postDetailsData['attachments']['data']; // Store images in the post data
                    } else {
                        $post['images'] = []; // No images for this post
                    }
                }
        
                // Return the posts with images
                echo json_encode([
                    'status' => 'success',
                    'posts' => $responseData['data']
                ]);
            } else {
                // If the request failed, return an error response
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch posts from Facebook',
                    'response' => $responseData
                ]);
            }
        }
        
        
    

    public function getPostComments() {
        // Get the query parameters
        $postId = $this->input->get('postId'); // Post ID from query string
        $accessToken = $this->session->userdata('facebook_Page_access_token'); // Access token from query string
    
        // Facebook Graph API URL for fetching comments
        $facebookGraphUrl = "https://graph.facebook.com/v21.0/$postId/comments?access_token=$accessToken";
    
        // Make the HTTP GET request to Facebook Graph API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        $responseData = json_decode($response, true);
    
        // Check if the request was successful
        if ($httpCode === 200 && isset($responseData['data'])) {
            echo json_encode([
                'status' => 'success',
                'comments' => $responseData['data'] // List of comments
            ]);
        } else {
            // If the request failed, return an error response
            echo json_encode([
                'status' => 'error',
                'response' => $responseData
            ]);
        }
    }
    
    public function postComment() {
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        // Extract the data
        $postId = $inputData['postId'];
        $accessToken = $this->session->userdata('facebook_Page_access_token');
        $commentContent = $inputData['commentContent'];
        
        // Get the user ID from the session
        $userId = $this->session->userdata('user_id');
    
        // Get the social account ID for Facebook from the database
        $this->db->select('id');
        $this->db->from('social_accounts');
        $this->db->where('user_id', $userId);
        $this->db->where('platform', 'Facebook');
        $query = $this->db->get();
        $socialAccountId = $query->row()->id; // Fetch the social account ID
    
        // Get the post ID from the database
        $this->db->select('id');
        $this->db->from('posts');
        $this->db->where('platform_post_ids', $postId); // Assuming $postId is the correct post identifier
        $query = $this->db->get();
        $postDbId = $query->row()->id;
    
        // Facebook Graph API URL for posting a comment
        $facebookGraphUrl = "https://graph.facebook.com/v21.0/$postId/comments?access_token=$accessToken";
    
        // Prepare the data for the API request
        $data = [
            'message' => $commentContent,
            'access_token' => $accessToken
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        $responseData = json_decode($response, true);
    
        // Check if the request was successful
        if ($httpCode === 200 && isset($responseData['id'])) {
            // Get the comment ID returned by Facebook
            $commentId = $responseData['id'];
    
            // Save the comment details to the database
            $commentData = [
                'user_id' => $userId,
                'social_account_id' => $socialAccountId,
                'post_id' => $postDbId,
                'comment_id' => $commentId,
                'content' => $commentContent,
                'created_at' => date('Y-m-d H:i:s')
            ];
    
            // Insert the comment into the database
            $this->db->insert('comments', $commentData);
    
            // Return success response
            echo json_encode([
                'status' => 'success',
                'comment_id' => $commentId
            ]);
        } else {
            // If the request failed, return an error response
            echo json_encode([
                'status' => 'error',
                'response' => $responseData
            ]);
        }
    }
    
    
 
    
    

    public function updatePost() {
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        // Extract the data
        $postId = $inputData['postId'];
        $newMessage = $inputData['newMessage'];
        $accessToken = $this->session->userdata('facebook_Page_access_token');
    
        // Validate inputs
        if (!$postId || !$newMessage || !$accessToken) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters.'
            ]);
            return;
        }
    
        // Check if the post exists in the database
        $this->db->select('platform_post_ids');
        $this->db->from('posts');
        $this->db->where('platform_post_ids', $postId);
        $query = $this->db->get();
    
        if ($query->num_rows() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Post not found in the database.'
            ]);
            return;
        }
    
        // Facebook Graph API URL for updating a post
        $facebookGraphUrl = "https://graph.facebook.com/v21.0/$postId";
    
        // Prepare the data for the API request
        $data = [
            'message' => $newMessage,
            'access_token' => $accessToken
        ];
    
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $facebookGraphUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        // Decode the response
        $responseData = json_decode($response, true);
    
        // Check if the request was successful
        if ($httpCode === 200) {
            // Update the database with the new content
            $updateData = [
                'content' => $newMessage,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->where('platform_post_ids', $postId);
            $this->db->update('posts', $updateData);
    
            echo json_encode([
                'status' => 'success',
                'message' => 'Post updated successfully.',
                'updated_post' => $newMessage
            ]);
        } else {
            // If the request failed, return an error response
            echo json_encode([
                'status' => 'error',
                'response' => $responseData
            ]);
        }
    }
    
        
}
