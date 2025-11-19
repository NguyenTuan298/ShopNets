<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connect.php';

function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400) {
    sendJsonResponse(['success' => false, 'error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            if ($search !== '') {
                $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE username LIKE :search OR email LIKE :search ORDER BY id ASC");
                $stmt->execute([':search' => "%$search%"]);
            } else {
                $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY id ASC");
            }
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJsonResponse(['success' => true, 'data' => $users]);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                sendError('User ID is required');
            }

            $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                sendError('User not found', 404);
            }

            $username = trim($input['username'] ?? '');
            $email = trim($input['email'] ?? '');

            $errors = [];
            if ($username === '') $errors[] = 'Username is required';
            if ($email === '') $errors[] = 'Email is required';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1");
            $stmt->execute([':username' => $username, ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already exists';
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
            $stmt->execute([':email' => $email, ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already exists';
            }

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':id' => $id
            ]);

            sendJsonResponse(['success' => true, 'message' => 'User updated successfully']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = $input['id'] ?? $_GET['id'] ?? $_POST['id'] ?? 0;
            
            if (!$id) {
                sendError('User ID is required');
            }

            $stmt = $pdo->prepare('SELECT username FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                sendError('User not found', 404);
            }

            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);

            sendJsonResponse(['success' => true, 'message' => 'User "' . $user['username'] . '" deleted successfully']);
            break;

        default:
            sendError('Method not allowed', 405);
    }

} catch (Exception $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>
