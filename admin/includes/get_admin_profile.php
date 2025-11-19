<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Debug session
    error_log("Session data: " . print_r($_SESSION, true));
    
    $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
    
    if (!$adminId) {
        echo json_encode(['success' => false, 'error' => 'Not logged in', 'session' => $_SESSION]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, email, phone, avatar FROM admin WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo json_encode([
            'success' => true,
            'admin' => $admin,
            'debug' => [
                'admin_id' => $adminId,
                'session' => $_SESSION
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Admin not found', 'admin_id' => $adminId]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>