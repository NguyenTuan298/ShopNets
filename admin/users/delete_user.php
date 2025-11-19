<?php
require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        $error = 'Invalid user ID';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT username FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $error = 'User not found';
            } else {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $stmt->execute([':id' => $id]);
                
                $success = true;
                $message = 'User "' . $user['username'] . '" has been deleted successfully.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        if (isset($error)) {
            echo json_encode(['success' => false, 'error' => $error]);
        } else {
            echo json_encode(['success' => true, 'message' => $message]);
        }
        exit;
    }
    
    if (isset($error)) {
        header('Location: index.php?error=' . urlencode($error));
    } else {
        header('Location: index.php?msg=' . urlencode($message));
    }
    exit;
}

if (!$isAjax) {
    header('Location: index.php');
    exit;
}
?>
