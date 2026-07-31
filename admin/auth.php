<?php

session_start();

if (empty($_SESSION['is_admin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}
