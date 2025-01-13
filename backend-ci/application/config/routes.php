<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "welcome";
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['register'] = 'auth/register';
$route['login'] = 'auth/login';
$route['update'] = 'auth/update';


$route['facebook/login'] = 'facebook/login';
// Route to get the page access token
$route['facebook/get_page_access_token'] = 'facebook/get_page_access_token';

// Route to publish a post on the page
$route['facebook/postMessage'] = 'facebook/postMessage';

$route['facebook/getPostLikes'] = 'facebook/getPostLikes';



$route['facebook/getPostComments'] = 'facebook/getPostComments';


$route['facebook/postComment'] = 'facebook/postComment';


$route['facebook/updatePost'] = 'facebook/updatePost';




// instagram all routes here 

$route['instagram/post'] = "api_controller/post";
$route['instagram/getlikes/(:any)'] = "api_controller/getlikes/$1";
$route['instagram/comments/(:any)'] = "api_controller/comments/$1";
$route['instagram/replay'] = "api_controller/replay";
$route['instagram/getpost'] = "api_controller/getpost";
$route['instagram/login_dilog'] = "api_controller/loginDilogbox";


$route['instagram/login'] = "api_controller/login";
$route['instagram/callback'] = "api_controller/callback";


$route['posts/getposts'] = "post/getposts";


// Route to get likes and reactions for a post
// $route['facebook/get_likes_reactions/(:any)'] = 'facebook/get_likes_reactions/$1';

// // Route to get comments for a post
// $route['facebook/get_comments/(:any)'] = 'facebook/get_comments/$1';

// // Route to reply to a comment
// $route['facebook/reply_to_comment/(:any)'] = 'facebook/reply_to_comment/$1';

// // Route to update a post
// $route['facebook/update_post/(:any)'] = 'facebook/update_post/$1';

// // Route to post with an image
// $route['facebook/post_with_image'] = 'facebook/post_with_image';

// $route['facebook/callback'] = 'facebook/callback';
// $route['facebook/profile'] = 'facebook/getUserProfile';
// $route['facebook/posts'] = 'facebook/getPosts'; // To fetch posts
// $route['facebook/publish'] = 'facebook/publishPost'; // To publish a post
// $route['facebook/comments'] = 'facebook/getComments'; // To fetch comments
// $route['facebook/reply'] = 'facebook/replyToComment'; // To reply to a comment


/* End of file routes.php */
/* Location: ./application/config/routes.php */