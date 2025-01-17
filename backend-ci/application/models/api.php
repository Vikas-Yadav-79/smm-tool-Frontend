<?php

class api extends CI_Model
{



    private $api_url = 'https://graph.instagram.com/v21.0/';



    public function __construct()
    {
        $this->load->library('session');
    }



    public function postSingle($image_url, $caption, $media_type, $collaborators, $user_tags)
    {
        $access_token = $this->session->userdata('Instagram_accessToken');
        if (isset($access_token)) {
            show_error("need to login");
        }
        if ($media_type[0] == 'IMAGE') {
            $params = [
                'image_url' => $image_url[0],
                'media_type' => $media_type[0],
                'access_token' => $access_token,
            ];
        } else {
            $params = [
                'video_url' => $image_url[0],
                'media_type' => $media_type[0],
                'access_token' => $access_token,
            ];
        }
        $params['caption'] = $caption;
        if (isset($collaborators) && !empty($collaborators)) {
            $params['collaborators'] = $collaborators;
        }
        if (isset($user_tags) && !empty($user_tags)) {
            $params['user_tags'] = $user_tags;
        }
        $create_media_url = "{$this->api_url}me/media";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $create_media_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['id'])) {
            $publish_url = "{$this->api_url}me/media_publish";
            $publish_params = [
                'creation_id' => $result['id'],
                'access_token' => $access_token
            ];
            $ch = curl_init($publish_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $publish_params);
            $publish_response = curl_exec($ch);
            $result2 = json_decode($publish_response, true);
            curl_close($ch);
            if (isset($result2['id'])) {
                echo json_encode(json_decode($publish_response, true));
            } else {
                show_error('Failed to publish post :-    ' . $publish_response);
            }
        } else {
            show_error('Failed to create post on instagram server :-    ' . $response);
        }
    }








    public function postMultiple($image_url, $caption, $media_type, $collaborators, $user_tags)
    {
        $access_token = $this->session->userdata('Instagram_accessToken');
        // $access_token = "IGAATpC2OpNHZABZAE9BQmFkX2Q0ejEzOXlETVlvN2JZAMmEzaTJqMjFWM2U5YlM0eHdPN3ZA1QlBWSW1IY0Iza1hKVC1haEEzbml1ckxrU2N3VWFxQXNuYnpLYllHNGJsSDhuZAnhlSHl5UFNsQWxoc3JDekZA3";
        if (isset($access_token)) {
            show_error("need to login");
        }
        $container_id = array();
        for ($x = 0; $x < count($image_url); $x++) {
            if ($media_type[$x] == 'IMAGE') {
                $params = [
                    'image_url' => $image_url[$x],
                    'media_type' => $media_type[$x],
                    'access_token' => $access_token,
                    'is_carousel_item' => true
                ];
            } else {
                $params = [
                    'video_url' => $image_url[$x],
                    'media_type' => $media_type[$x],
                    'access_token' => $access_token,
                    'is_carousel_item' => true,
                ];
            }
            // echo json_encode($params);
            $create_media_url = "{$this->api_url}me/media";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $create_media_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $response = curl_exec($ch);
            if (json_decode($response, true)['id'] == null) {
                echo json_decode($response, true);
            }
            $container_id[] = json_decode($response, true)['id'];
            curl_close($ch);
        }

        $publish_container_url = "{$this->api_url}me/media";
        $children = implode(',', $container_id);
        $publish_container_params = [
            'caption' => $caption,
            'children' => $children,
            'media_type' => 'CAROUSEL',
            'access_token' => $access_token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $publish_container_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $publish_container_params);
        $publish_container_response = curl_exec($ch);
        $carsoul_id = json_decode($publish_container_response, true)['id'];
        if ($carsoul_id == null) {
            echo json_decode($publish_container_response, true);
        }
        curl_close($ch);
        $publish_url = "{$this->api_url}me/media_publish";
        $publish_params = [
            'creation_id' => $carsoul_id,
            'access_token' => $access_token
        ];
        $ch = curl_init($publish_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $publish_params);
        $publish_response = curl_exec($ch);
        $publish_id = json_decode($publish_response, true)['id'];
        if ($publish_id === null) {
            $trouble_url = "{$this->api_url}/{$carsoul_id}?fields=status_code";
            $ch = curl_init($trouble_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $publish_params);
            $trouble_response = curl_exec($ch);
            show_error($trouble_response);
        }
        echo json_encode(json_decode($publish_response, true));
    }



    public function getlikesAction($post_id)
    {
        $access_token = $this->session->userdata('Instagram_accessToken');
        // $access_token = "IGAATpC2OpNHZABZAE9BQmFkX2Q0ejEzOXlETVlvN2JZAMmEzaTJqMjFWM2U5YlM0eHdPN3ZA1QlBWSW1IY0Iza1hKVC1haEEzbml1ckxrU2N3VWFxQXNuYnpLYllHNGJsSDhuZAnhlSHl5UFNsQWxoc3JDekZA3";
        if (isset($access_token)) {
            show_error("need to login");
        }
        $url = "{$this->api_url}{$post_id}?fields=like_count&access_token={$access_token}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_encode(json_decode($response, true));
    }

    public function commentsAction($post_id)
    {
        $access_token = $this->session->userdata('Instagram_accessToken');
        // $access_token = "IGAATpC2OpNHZABZAE9BQmFkX2Q0ejEzOXlETVlvN2JZAMmEzaTJqMjFWM2U5YlM0eHdPN3ZA1QlBWSW1IY0Iza1hKVC1haEEzbml1ckxrU2N3VWFxQXNuYnpLYllHNGJsSDhuZAnhlSHl5UFNsQWxoc3JDekZA3";
        if (isset($access_token)) {
            show_error("need to login");
        }
        $url = "{$this->api_url}{$post_id}/comments?access_token={$access_token}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        echo json_encode(json_decode($response, true));
    }

    public function replayAction($comment_id)
    {
        $access_token = $this->session->userdata('Instagram_accessToken');
        // $access_token = "IGAATpC2OpNHZABZAE9BQmFkX2Q0ejEzOXlETVlvN2JZAMmEzaTJqMjFWM2U5YlM0eHdPN3ZA1QlBWSW1IY0Iza1hKVC1haEEzbml1ckxrU2N3VWFxQXNuYnpLYllHNGJsSDhuZAnhlSHl5UFNsQWxoc3JDekZA3";
        if (isset($access_token)) {
            show_error("need to login");
        }
        $message = $this->input->post('message');
        $url = "{$this->api_url}{$comment_id}/replies";
        $params = [
            'message' => $message,
            'access_token' => $access_token
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        echo $this->access_token;
        echo json_encode(json_decode($response, true));
    }

    public function getpostAction()
    {
        $access_token = $this->session->userdata('Instagram_accessToken');
        // $access_token = "IGAATpC2OpNHZABZAE9BQmFkX2Q0ejEzOXlETVlvN2JZAMmEzaTJqMjFWM2U5YlM0eHdPN3ZA1QlBWSW1IY0Iza1hKVC1haEEzbml1ckxrU2N3VWFxQXNuYnpLYllHNGJsSDhuZAnhlSHl5UFNsQWxoc3JDekZA3";
        if (isset($access_token)) {
            show_error("need to login");
        }
        $url = "{$this->api_url}me/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url&access_token={$access_token}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['data']) && !empty($result['data'])) {
            echo json_encode($result['data']);
        } else {
            echo json_encode($result);
            // show_error('No posts found.');
        }
    }


    public function login_acessToken($code, $client_id, $client_secret)
    {
        $url = 'https://api.instagram.com/oauth/access_token';
        $redirect_uri1 = 'https://localhost/smm/smm-tool-Frontend/backend-ci/index.php/instagram/login';
        $data = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri1,
            'code' => $code
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            $result = json_decode($response, true);
            $shortLivedToken =  json_encode($result['access_token']);
            $shortLivedToken = str_replace('"', '', $shortLivedToken);
        }
        curl_close($ch);

        $url2 = "https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=" . $client_secret . "&access_token=" . $shortLivedToken;
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $url2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPGET, true);

        $response2 = curl_exec($ch2);
        if (curl_errno($ch2)) {
            echo 'Error:' . curl_error($ch2);
        } else {
            $result2 = json_decode($response2, true);
            $accesstoken1 = json_encode($result2['access_token']);
            $accesstoken1 = str_replace('"', '', $accesstoken1);
        }
        curl_close($ch2);
        $this->session->set_userdata('Instagram_accessToken', $accesstoken1);
        var_dump($this->session->all_userdata());
        $user_id = $this->session->userdata('user_id');
        $data2 = array(
            'user_id' => $user_id,
            'platform' => "Instagram",
            'access_token' => $accesstoken1
        );
        $db_response = $this->db->insert('social_accounts', $data2);
        $redirect_url = "http://127.0.0.1:8081/#/dashboard";
        redirect($redirect_url);
    }
    public function validate_user_tags($user_tags)
    {
        if (!is_array($user_tags)) {
            return ['status' => false, 'message' => 'user_tags must be an array.'];
        }
        foreach ($user_tags as $tag) {
            if (!isset($tag['username'], $tag['x'], $tag['y'])) {
                return ['status' => false, 'message' => 'Each tag must contain username, x, and y.'];
            }
            if (!is_string($tag['username']) || empty($tag['username'])) {
                return ['status' => false, 'message' => 'username must be a non-empty string.'];
            }

            if (!is_float($tag['x']) || !is_float($tag['y']) || $tag['x'] < 0.0 || $tag['x'] > 1.0 || $tag['y'] < 0.0 || $tag['y'] > 1.0) {
                return ['status' => false, 'message' => 'x and y must be floats between 0.0 and 1.0.'];
            }
        }
        return ['status' => true, 'message' => 'Validation passed.'];
    }
}
