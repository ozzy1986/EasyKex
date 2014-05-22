<?php

class User {

    private $db;

    public $data;

    /*private $user_fields = array(
        'id',
        'email'
    );

    private $entry_fields = array(
        'id',
        'user_id',
        'signature_data',
        'time_attempt'
    );*/

    public function __construct() {
        $this->db = new db;
    }

    // Get all data related to user by his id
    public function getUserDataById($user_id) {
        // get user
        $sql_user = "SELECT id FROM users WHERE id = '".$user_id."' LIMIT 1";
        $data_user = $this->db->query($sql_user);
        $user = $data_user->fetch_assoc();

        $sql_entry = "SELECT `signature_data` FROM `entries` WHERE `user_id` = '".$user_id."' LIMIT 1";
        $data_entry= $this->db->query($sql_entry);
        $entries = $data_entry->fetch_assoc();
        $entries_data = json_decode($entries['signature_data'], true);

        $result = array(
            'User' => $user,
            'Data' => $entries_data
        );

        $this->data = $result;
        return $result;
    }


    // Get all data related to user by his email
    public function getUserDataByEmail($email) {

        if ($user_id = $this->userExists($email)) {

            // get user
            $sql_user = "SELECT id FROM users WHERE id = '".$user_id."' LIMIT 1";
            $data_user = $this->db->query($sql_user);
            $user = $data_user->fetch_assoc();

            $sql_entry = "SELECT `signature_data` FROM `entries` WHERE `user_id` = '".$user_id."'";
            $data_entry= $this->db->query($sql_entry);
            $entries = $data_entry->fetch_assoc();
            $entries_data = json_decode($entries['signature_data'], true);

            $result = array(
                'User' => $user,
                'Data' => $entries_data
            );

            $this->data = $result;
            return $result;

        } else {
            return false;
        }

    }

    // Check if user with such email exists
    public function userExists($email) {
        $sql_user = "SELECT id FROM users WHERE `email` = '".$email."' LIMIT 1";
        $data_user = $this->db->query($sql_user);
        if ($data_user->num_rows > 0) {
            $user = $data_user->fetch_assoc();
            return $user['id'];
        } else {
            return false;
        }
    }

    // Add new user
    public function addUser($email, $data) {
        // if no such user then add him
        $sql_create_user = "INSERT INTO users (`email`) VALUES ('".$email."')";
        $result_create_user = $this->db->query($sql_create_user);

        // and add time data related to this user
        $sql_create_time_data = "INSERT INTO `entries` (`user_id`, `signature_data`, `time_attempt`) VALUES ('".$this->db->get_insert_id()."', '".$data."', '".date('Y-m-d h:i:s')."')";
        $this->db->query($sql_create_time_data);
    }

}