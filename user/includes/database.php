<?php
// Include the simple config file
require_once __DIR__ . '/../../config-simple.php';

class Database {
    public $conn;
    
    public function getConnection() {
        $this->conn = createPDOConnection();
        return $this->conn;
    }
}
?>