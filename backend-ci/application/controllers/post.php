<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class post extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->enableCors();
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
  public function getposts($userid)
  {
    $query = $this->db->get_where('posts', array('user_id' => $userid));
    $result = $query->result_array();


    // $query2 = $this->db->get('post_images');
    // $result2 = $query2->result_array();
    // $posts_with_images = [];
    // foreach ($result as $post) {
    //   $post_id = $post['id'];
    //   // Filter images for the current post_id
    //   $post_images = array_filter($result2, function ($image) use ($post_id) {
    //     return $image['post_id'] == $post_id;
    //   });
    //   // Get just the image URLs as an array
    //   $image_urls = array_map(function ($image) {
    //     return $image['image_url'];
    //   }, $post_images);
    //   $image_media_type =
    //     array_map(function ($image) {
    //       return $image['media_type'];
    //     }, $post_images);

    //   $temp = $post;
    //   // Add image URLs to the post data
    //   $temp['image_urls'] = $image_urls;
    //   $temp['media_type'] = $image_media_type;

    //   // Add the post to the final array
    //   $posts_with_images[] = $temp;
    // }
    // echo json_encode($posts_with_images);
    $post_platform = [];
    foreach ($result as $post) {
      $this->db->select('platform');
      $query3 = $this->db->get_where('social_accounts', array('user_id' => $post['social_account_id']));
      $result3 = $query3->result_array();
      $temp = $post;
      $temp['platform'] = $result3[0]['platform'];
      $post_platform[] = $temp;
    }
    echo json_encode($post_platform);
  }

  public function save_post() {}
}
