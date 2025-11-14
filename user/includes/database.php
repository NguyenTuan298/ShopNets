<?php
// Include the main config file
require_once __DIR__ . '/../../config.php';

class Database {
    public $conn;
    
    public function getConnection() {
        $this->conn = createPDOConnection();
        return $this->conn;
    }
}
?>