<?php
// Include file cấu hình
require_once __DIR__ . '/../../config.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;
    
    public function __construct() {
        $config = getDatabaseConfig();
        $this->host = $config['host'];
        $this->db_name = $config['dbname'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            // Hiển thị thông báo thân thiện với người dùng trong development, ẩn trong production
            if (isLocalhost()) {
                die("Lỗi kết nối database: " . $exception->getMessage() . "<br>Host: " . $this->host . "<br>DB: " . $this->db_name);
            } else {
                die("Lỗi kết nối database. Vui lòng liên hệ quản trị viên.");
            }
        }
        return $this->conn;
    }
}
?>