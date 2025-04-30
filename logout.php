<?php
require 'config/constants.php';
// Destruir todas las sesiones y redirigir al user a la pagina principal
session_destroy();
header('location: ' . ROOT_URL);
die();
