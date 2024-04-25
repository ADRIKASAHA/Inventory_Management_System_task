<?php
$connect = mysqli_connect('localhost', 'root', '', 'inventory');

if (!$connect) {
    die('Could not connect: ' . mysqli_connect_error());
}

session_start();

// Initialize session variables
$_SESSION['type'] = '';
$_SESSION['user_id'] = '';
