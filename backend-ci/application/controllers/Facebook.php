<?php
// application/controllers/Facebook.php
class Facebook extends CI_Controller {
    private $access_token;
    private $page_id = '554959331025659'; 

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
    
    

    // public function getPageAccessToken() {
    //     $userAccessToken = $this->input->get('user_access_token');
    //     $pageId = $this->input->get('page_id');
    
    //     $url = "https://graph.facebook.com/v21.0/$pageId?fields=access_token&access_token=$userAccessToken";
    
    //     $response = file_get_contents($url);
    //     echo $response;
    // }
    

    public function get_page_access_token() {
        $user_access_token = $this->access_token;
        $url = "https://graph.facebook.com/v21.0/{$this->page_id}?fields=access_token&access_token={$user_access_token}";

        $response = $this->make_api_request($url);
        if (isset($response->access_token)) {
            return $response->access_token;
        } else {
            // Handle error (invalid token, etc.)
            return null;
        }
    }


    public function postMessage() {
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);

        // Extract the data
        $pageId = $inputData['pageId'];
        $accessToken = $inputData['accessToken'];
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


    public function getPostLikes() {
        // Get the request payload
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        // Extract the data
        // $postId = $inputData['postId'];
        // $accessToken = $inputData['pageaccessToken'];
    

        $postId = $this->input->get('postId');
        $accessToken = $this->input->get('pageaccessToken');

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



    public function getPostComments() {
        // Get the query parameters
        $postId = $this->input->get('postId'); // Post ID from query string
        $accessToken = $this->input->get('pageaccessToken'); // Access token from query string
    
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
        $accessToken = $inputData['accessToken'];
        $commentContent = $inputData['commentContent'];
    
        // Get the user ID from the session
        $userId = $this->session->userdata('user_id');
        // Assuming the user ID is stored in the session
    
        // Get the social account ID for Facebook from the database
        $this->db->select('id');
        $this->db->from('social_accounts');
        $this->db->where('user_id', $userId);
        $this->db->where('platform', 'Facebook');
        $query = $this->db->get();
    
        // // Check if a Facebook social account exists for the user
        // if ($query->num_rows() > 0) {
   $socialAccountId = $query->row()->id; // Fetch the social account ID
        // } else {
        //     // Handle the case where no Facebook account is found for the user
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'No Facebook account linked to this user.'
        //     ]);
        //     return;
        // }


        $this->db->select('id');
        $this->db->from('posts');
        $this->db->where('platform_post_ids', $postId); // Assuming $postId is the correct post identifier
        $query = $this->db->get();

        $postId = $query->row()->id;
    
        // Facebook Graph API URL for posting a comment
        $facebookGraphUrl = "https://graph.facebook.com/v21.0/$postId/comments?access_token=$accessToken";
    
        // Prepare the data for the API request
        $data = [
            'message' => $commentContent
        ];
    
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
    
        // Check if the request was successful
        if ($httpCode === 200 && isset($responseData['id'])) {
            // Get the comment ID returned by Facebook
            $commentId = $responseData['id'];
    
            // Save the comment details to the database
            $commentData = [
                'user_id' => $userId,
                'social_account_id' => $socialAccountId,
                'post_id' => $postId,
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
    
        // Extract the data from the request
        $postId = $inputData['postId']; // The post ID to update
        $newContent = $inputData['content']; // New content for the post
    
        // Validate that the post exists in the database
        $this->db->select('platform_post_ids');
        $this->db->from('posts');
        $this->db->where('platform_post_ids', $postId); // Check if the post exists
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            // Post exists, proceed to update
            $this->db->set('content', $newContent); // Set the new content
            $this->db->where('platform_post_ids', $postId); // Specify which post to update
            $this->db->update('posts'); // Perform the update
    
            // Check if the update was successful
            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Post updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No changes made to the post.'
                ]);
            }
        } else {
            // If the post doesn't exist
            echo json_encode([
                'status' => 'error',
                'message' => 'Post not found.'
            ]);
        }
    }
    
    
    
    // Publish a Post on the Page
    // public function publish_post($message) {
    //     $page_access_token = $this->get_page_access_token();
    //     if (!$page_access_token) {
    //         // Handle error (no valid page access token)
    //         return;
    //     }

    //     $url = "https://graph.facebook.com/v21.0/{$this->page_id}/feed";
    //     $data = [
    //         'message' => $message,
    //         'access_token' => $page_access_token
    //     ];

    //     $response = $this->make_api_request($url, 'POST', $data);
    //     return $response;
    // }

    // Get Likes and Reactions for a Post
    // public function get_likes_reactions($post_id) {
    //     $page_access_token = $this->get_page_access_token();
    //     if (!$page_access_token) {
    //         // Handle error
    //         return;
    //     }

    //     $url = "https://graph.facebook.com/v21.0/{$post_id}/likes?access_token={$page_access_token}";
    //     $response = $this->make_api_request($url);
    //     return $response;
    // }

    // Get Comments for a Post
    // public function get_comments($post_id) {
    //     $page_access_token = $this->get_page_access_token();
    //     if (!$page_access_token) {
    //         // Handle error
    //         return;
    //     }

    //     $url = "https://graph.facebook.com/v21.0/{$post_id}/comments?access_token={$page_access_token}";
    //     $response = $this->make_api_request($url);
    //     return $response;
    // }

    // Reply to a Comment
    // public function reply_to_comment($comment_id, $message) {
    //     $page_access_token = $this->get_page_access_token();
    //     if (!$page_access_token) {
    //         // Handle error
    //         return;
    //     }

    //     $url = "https://graph.facebook.com/v21.0/{$comment_id}/comments";
    //     $data = [
    //         'message' => $message,
    //         'access_token' => $page_access_token
    //     ];

    //     $response = $this->make_api_request($url, 'POST', $data);
    //     return $response;
    // }

    // Update a Post
    // public function update_post($post_id, $message) {
    //     $page_access_token = $this->get_page_access_token();
    //     if (!$page_access_token) {
    //         // Handle error
    //         return;
    //     }

    //     $url = "https://graph.facebook.com/v21.0/{$post_id}";
    //     $data = [
    //         'message' => $message,
    //         'access_token' => $page_access_token
    //     ];

    //     $response = $this->make_api_request($url, 'POST', $data);
    //     return $response;
    // }

    // Upload Image and Post with Text
    // public function post_with_image($image_url, $message) {
    //     $page_access_token = $this->get_page_access_token();
    //     if (!$page_access_token) {
    //         // Handle error
    //         return;
    //     }

    //     // Step 1: Upload Image
    //     $url = "https://graph.facebook.com/v21.0/{$this->page_id}/photos";
    //     $data = [
    //         'access_token' => $page_access_token,
    //         'message' => $message,
    //         'url' => $image_url
    //     ];

    //     $response = $this->make_api_request($url, 'POST', $data);
    //     if (isset($response->id)) {
    //         // Step 2: Post with Image ID
    //         $image_id = $response->id;
    //         $post_url = "https://graph.facebook.com/v21.0/{$this->page_id}/feed";
    //         $post_data = [
    //             'access_token' => $page_access_token,
    //             'message' => $message,
    //             'object_attachment' => $image_id
    //         ];

    //         return $this->make_api_request($post_url, 'POST', $post_data);
    //     }

    //     return null;
    // }

    // Helper function to make API requests
    private function make_api_request($url, $method = 'GET', $data = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }


    
}
