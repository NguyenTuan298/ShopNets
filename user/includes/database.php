<?php
class Database {
    private $host = "localhost";
    private $db_name = "shopnets";
    private $username = "root";
    private $password = "";
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            // Hiển thị thông báo thân thiện với người dùng
            die("Lỗi kết nối database. Vui lòng kiểm tra cấu hình.");
        }
        return $this->conn;
    }
}
?>