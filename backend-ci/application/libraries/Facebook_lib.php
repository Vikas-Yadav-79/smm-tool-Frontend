<?php
// application/libraries/Facebook_lib.php
require_once APPPATH . 'vendor/autoload.php';// Path to the SDK

use Facebook\Facebook;

class Facebook_lib {

    private $fb;
    private $helper;

    public function __construct() {
        $this->fb = new Facebook([
            'app_id' => '480761234690688',
            'app_secret' => '14a6fe0369f8c0ef1012f48563db2d57',
            'default_graph_version' => 'v12.0',
        ]);
    }

    // Get user data
    public function getUserData($accessToken) {
        try {
            $response = $this->fb->get('/me?fields=id,name,email', $accessToken);
            return $response->getGraphNode()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            return 'Error: ' . $e->getMessage();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }




    public function postToFacebook($accessToken, $data) {
        try {
            $endpoint = isset($data['media']) ? '/me/photos' : '/me/feed';
            $response = $this->fb->post($endpoint, $data, $accessToken);
            return $response->getGraphNode();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            return ['error' => 'Graph returned an error: ' . $e->getMessage()];
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            return ['error' => 'Facebook SDK returned an error: ' . $e->getMessage()];
        }
    }



    // Example method to post to Facebook
     // Fetch Page Access Token
    //  public function getPageAccessToken($userAccessToken, $pageId) {
    //     try {
    //         $response = $this->fb->get("/$pageId?fields=access_token", $userAccessToken);
    //         return $response->getGraphNode()->getField('access_token');
    //     } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }

    // Post to Page
    // public function postToPage($pageAccessToken, $message) {
    //     try {
    //         $response = $this->fb->post('/me/feed', ['message' => $message], $pageAccessToken);
    //         return $response->getGraphNode();
    //     } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }

    // Get Likes, Reactions, Comments
    // public function getPostEngagement($pageAccessToken, $postId, $type) {
    //     try {
    //         $response = $this->fb->get("/$postId/$type", $pageAccessToken);
    //         return $response->getGraphEdge()->asArray();
    //     } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }

    // Reply to Comment
    // public function replyToComment($pageAccessToken, $commentId, $message) {
    //     try {
    //         $response = $this->fb->post("/$commentId/comments", ['message' => $message], $pageAccessToken);
    //         return $response->getGraphNode();
    //     } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }

    // Update a Post
    // public function updatePost($pageAccessToken, $postId, $message) {
    //     try {
    //         $response = $this->fb->post("/$postId", ['message' => $message], $pageAccessToken);
    //         return $response->getGraphNode();
    //     } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }
    
    // public function postWithMedia($pageAccessToken, $message, $mediaPath) {
    //     try {
    //         // Step 1: Upload the media
    //         $mediaResponse = $this->fb->post(
    //             '/me/photos', // Change to '/me/videos' for video uploads
    //             [
    //                 'source' => $this->fb->fileToUpload($mediaPath),
    //                 'published' => false // Prevent publishing immediately
    //             ],
    //             $pageAccessToken
    //         );
    
    //         // Get the media ID from the response
    //         $mediaId = $mediaResponse->getGraphNode()->getField('id');
    
    //         // Step 2: Create the post with the uploaded media
    //         $postResponse = $this->fb->post(
    //             '/me/feed',
    //             [
    //                 'message' => $message,
    //                 'attached_media' => [['media_fbid' => $mediaId]]
    //             ],
    //             $pageAccessToken
    //         );
    
    //         return $postResponse->getGraphNode();
    //     } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     } catch (Facebook\Exceptions\FacebookSDKException $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }
    
}
