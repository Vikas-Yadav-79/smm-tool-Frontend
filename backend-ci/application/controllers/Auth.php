<?php
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('api');
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');  // Allow all origins
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');  // Allow these methods
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $plainPassword = $data['password'];
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        if ($this->User_model->register($data)) {
            // Try to log the user in after registration
            $user = $this->User_model->login($data['email'], $plainPassword);
            if ($user) {
                // Set session data if login is successful
                $this->session->set_userdata('user_id', $user->id);
                $this->session->set_userdata('email', $user->email);
                echo json_encode(['status' => 'success', 'message' => 'User registered', 'user' => $user]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Login failed after registration']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
    }




    public function login()
    {

        $data = json_decode(file_get_contents('php://input'), true);
        // var_dump($data);
        // die();   
        $user = $this->User_model->login($data['email'], $data['password']);

        if ($user) {
            $this->session->set_userdata('user_id', $user->id);
            $this->session->set_userdata('user_email', $user->email);
            $this->db->where('user_id', $user->id);
            $query = $this->db->get('social_accounts');
            $result = $query->result_array();
            if (count($result) !== 0) {
                foreach ($result as $row) {
                    if (isset($row['access_token'])) {
                        if ($row['platform'] === 'Instagram') {
                            $result2 =  $this->api->check_access_token($row['access_token']);
                            if ($result2['status'] == 'success') {
                                $access_token =  $result2['access_token'];
                            } else {
                                $access_token = $row['access_token'];
                            }
                            $this->session->set_userdata($row['platform'] . '_accessToken', $access_token);
                        } else if ($row['platform'] === 'Facebook') {
                        } else {
                            $this->session->set_userdata($row['platform'] . '_accessToken', $row['access_token']);
                        }
                    }
                }
            }
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    }

    // public function is_logged_in() {
    //     if (!$this->session->userdata('user_id')) {
    //         // User is not logged in
    //         echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    //         exit;  // Stop further execution
    //     }
    // }

    // public function is_logged_in() {
    //     // Check if the user ID is passed in the request
    //     $data = json_decode(file_get_contents('php://input'), true);
    //     if (isset($data['id']) && $data['id'] == $this->session->userdata('user_id')) {
    //         return true;  // User is logged in
    //     } else {
    //         // User is not logged in or ID mismatch
    //         echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    //         exit;  // Stop further execution
    //     }
    // }

    public function logout()
    {
        $this->session->sess_destroy();  // Destroy the session
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
    }


    public function checkConnection()
    {

        $userid = $this->session->userdata('user_id');
        $this->db->select('platform,account_name');
        $this->db->where('user_id', $userid);
        $query = $this->db->get('social_accounts');
        $result = $query->result_array();
        echo json_encode(['status' => 'success', 'connection' => $result]);
    }


    public function update()
    {
        // $this->is_logged_in();

        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = $data['id'];  // Get user ID from request
        unset($data['id']); // Don't include ID in the update data
        if ($this->User_model->update_user($user_id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'User updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
    }
}
