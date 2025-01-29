<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class api_controller extends CI_Controller
{
  private $app_id;
  private $app_secret;

  public function __construct()
  {
    $this->app_id = getenv("INSTAGRAM_APP_ID");
    $this->app_secret = getenv("INSTAGRAM_APP_SECRET");

    parent::__construct();
    $this->enableCors();
    $this->load->helper('url');
    $this->load->model('api');
  }

  private function enableCors()
  {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      exit(0);
    }
  }

  public function index()
  {
    echo "loading";
  }

  public function post()
  {
    $userid = $this->session->userdata('user_id');
    if (isset($userid)) {
      show_error("your are not authorised to access this data");
    }
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $image_url = $data['image_url'];
    $caption = $data['caption'];
    $media_type = $data['media_type'];
    $collaborators = null;
    $user_tags = null;
    if (!is_array($image_url) || !empty($image_urls)) {
      show_error("image_url must be an array or not empty , check for parameter name it should if 'image_urls' ", 400);
    } elseif (!is_array($media_type) || empty($media_type)) {
      show_error("media_type must be an array or not empty , check for parameter name it should if 'media_type'", 400);
    } elseif (count($image_url) !==  count($media_type) && count($image_url) > 10) {
      show_error("image_url and media_type must same in length and no more then 10 image_url is allow", 400);
    }
    if (isset($data['collaborators']) && !empty($data['collaborators'])) {
      $collaborators = $data['collaborators'];
      if (!is_array($collaborators) || count($collaborators) > 3) {
        show_error("collaborators must be an array and no more than 3 members");
      }
    }
    if (isset($data['user_tags']) && !empty($data['user_tags'])) {
      $user_tags = $data['user_tags'];
      $result = $this->api->validate_user_tags($user_tags);
      if (!$result['status']) {
        show_error($result['message'], 400);
      }
    }
    if (count($image_url) == 1 && count($media_type) == 1) {
      return $this->api->postSingle($image_url, $caption, $media_type, $collaborators, $user_tags);
    } else if (count($image_url) ==  count($media_type) && count($image_url) <= 10) {
      return $this->api->postMultiple($image_url, $caption, $media_type, $collaborators, $user_tags);
    } else {
      return;
    }
  }
  public function getlikes($post_id)
  {
    $userid = $this->session->userdata('user_id');
    if (!isset($userid)) {
      show_error("your are not authorised to access this data");
    }
    return $this->api->getlikesAction($post_id);
  }

  public function comments($post_id)
  {
    $userid = $this->session->userdata('user_id');
    if (!isset($userid)) {
      show_error("your are not authorised to access this data");
    }
    return $this->api->commentsAction($post_id);
  }
  public function replay()
  {
    $userid = $this->session->userdata('user_id');
    if (!isset($userid)) {
      show_error("your are not authorised to access this data");
    }
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $message = $data['message'];
    $comment_id = $data['comment_id'];
    return $this->api->replay($comment_id, $message);
  }
  public function getpost()
  {
    $userid = $this->session->userdata('user_id');
    if (!isset($userid)) {
      show_error("your are not authorised to access this data");
    }
    return $this->api->getpostAction();
  }
  public function loginDilogbox()
  {
    $userid = $this->session->userdata('user_id');
    if (!isset($userid)) {
      show_error("your are not authorised to access this data");
    }
    $url = 'https://www.instagram.com/oauth/authorize?enable_fb_login=0&force_authentication=1&client_id=' . $this->app_id . '&redirect_uri=https://localhost/smm/smm-tool-Frontend/backend-ci/index.php/instagram/login&response_type=code&scope=instagram_business_basic%2Cinstagram_business_manage_messages%2Cinstagram_business_manage_comments%2Cinstagram_business_content_publish'; // The URL to redirect to
    redirect($url);
  }
  public function login()
  {
    $code = $this->input->get('code');
    $this->api->login_acessToken($code, $this->app_id, $this->app_secret);
  }
}
