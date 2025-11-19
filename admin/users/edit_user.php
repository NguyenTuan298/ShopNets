<?php
require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $isAjax) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $user]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$errors = [];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        $errors[] = 'Invalid user ID';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                $errors[] = 'User not found';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error';
        }

        if (empty($errors)) {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($username === '') $errors[] = 'Username is required';
            if ($email === '') $errors[] = 'Email is required';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1");
                    $stmt->execute([':username' => $username, ':id' => $id]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Username already exists.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
                    $stmt->execute([':email' => $email, ':id' => $id]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Email already exists.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');
                    $stmt->execute([
                        ':username' => $username,
                        ':email' => $email,
                        ':id' => $id
                    ]);
                    
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                        exit;
                    } else {
                        header('Location: index.php?msg=' . urlencode('User updated successfully'));
                        exit;
                    }
                } catch (Exception $e) {
                    $errors[] = 'DB error: ' . $e->getMessage();
                }
            }
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
}

if (!$isAjax) {
    header('Location: index.php');
    exit;
}
?>
