<?php
require '../partials/header.php';

// Chequeo del estatus del inicio de sesión
if (!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}
