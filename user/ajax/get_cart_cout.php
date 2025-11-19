<?php
session_start();
header('Content-Type: text/plain');

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    echo array_sum($_SESSION['cart']);
} else {
    echo '0';
}
?>