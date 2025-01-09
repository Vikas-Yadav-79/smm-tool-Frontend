<?php
class User_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Register a new user
    public function register($data) {
        unset($data['updated_at']);
        return $this->db->insert('users', $data);
    }

    // Login a user
    public function login($email, $password) {
        $this->db->where('email', $email);
        $query = $this->db->get('users');
    
        if ($query->num_rows() == 1) {
            $user = $query->row();
    
            return password_verify($password, $user->password) ? $user : false;
        }
    
        return false;
    }
    
    public function update_user($user_id, $data) {
        // Ensure the updated_at field is set to current timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }
}
