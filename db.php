<?php
class db {

    private $db;

    private $db_access;


    public function __construct() {
        include 'db_settings.php';
        $this->db_access = $db_access;

        $this->db = new mysqli($this->db_access['host'], $this->db_access['user'], $this->db_access['password'], $this->db_access['db']);

        if ($this->db->connect_errno > 0) {
            die('Unable to connect to database [' . $this->db->connect_error . ']');
        }

        return $this->db;

    }

    public function query($query) {
        if (!$result = $this->db->query($query)) {
            die('There was an error running the query [' . $this->db->error . ']');
        } else {
            return $result;
        }
    }

    public function get_insert_id() {
        if ($this->db->insert_id) {
            return $this->db->insert_id;
        } else {
            return false;
        }
    }

}