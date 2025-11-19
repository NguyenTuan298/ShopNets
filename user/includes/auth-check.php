<?php
session_start();
header('Content-Type: text/plain');
echo isset($_SESSION['user_id']) ? 'true' : 'false';
?>