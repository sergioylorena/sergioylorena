<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    // Obtener data del formulario
    $username_email = filter_var($_POST['username_email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$username_email) {
        $_SESSION['signin'] = "Nombre de usuario o Email requeridos!";
    } elseif (!$password) {
        $_SESSION['signin'] = "Contraseña requerida!";
    } else {
        // fetchear usuario de la DB
        $fetch_user_query = "SELECT * FROM users WHERE username='$username_email' OR email='$username_email'";
        $fetch_user_result = mysqli_query($connection, $fetch_user_query);

        if (mysqli_num_rows($fetch_user_result) == 1) {
            // convert el registro en un assoc array
            $user_record = mysqli_fetch_assoc($fetch_user_result);
            $db_password = $user_record['password'];
            // comparar las contraseñas con la contraseña de la DB
            if (password_verify($password, $db_password)) {
                // setear sesion del control de acceso
                $_SESSION['user-id'] = $user_record['id'];
                // setear sesión si el usuario es un admin
                if ($user_record['is_admin'] == 1) {
                    $_SESSION['user_is_admin'] = true;
                }
                // Logear usuario
                header('location: ' . ROOT_URL . 'admin/');
            } else {
                $_SESSION['signin'] = "Por favor, revisa los datos ingresados";
            }
        } else {
            $_SESSION['signin'] = "Usuario no encontrado!";
        }
    }

    // Si hay algun problema, redirigir al usuario a la pagina de inicio de sesión, con sus datos actuales.
    if (isset($_SESSION['signin'])) {
        $_SESSION['signin-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signin.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}
