<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Check if it's an API request
    $isApiRequest = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                   (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
                   (isset($_GET['api']) && $_GET['api'] == '1');
    
    if ($isApiRequest) {
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Method Not Allowed', 'success' => false]);
        exit();
    }
    exit('Method Not Allowed');
}

// Check if it's an API request
$isApiRequest = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
               (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
               (isset($_GET['api']) && $_GET['api'] == '1');

// Get data from POST (for both form data and JSON)
$email = '';
$password = '';

if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $email = trim($jsonData['email'] ?? '');
    $password = $jsonData['password'] ?? '';
} else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
}

if (empty($email) || empty($password)) {
    if ($isApiRequest) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Please fill in all fields!', 'success' => false]);
        exit();
    }
    header('Location: login.php?error=Please+fill+in+all+fields!');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'admin';
        $_SESSION['last_activity'] = time();
        
        if ($isApiRequest) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'session_id' => session_id()
            ]);
            exit();
        }
        
        header('Location: ../index.php');
        exit();
    } else {
        if ($isApiRequest) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email or password incorrect!', 'success' => false]);
            exit();
        }
        header('Location: login.php?error=Email+or+password+incorrect!');
        exit();
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    
    if ($isApiRequest) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'System error!', 'success' => false]);
        exit();
    }
    
    header('Location: login.php?error=System+error!');
    exit();
}
?>