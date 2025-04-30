<?php
require 'config/database.php';

// Obtener la data del formulario si el botón de enviar es clickeado
if (isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_admin = filter_var($_POST['userrole'], FILTER_SANITIZE_NUMBER_INT);
    $avatar = $_FILES['avatar'];

    // Validar los campos de input
    if (!$firstname) {
        $_SESSION['add-user'] = "Por favor introduce tu Nombre";
    } elseif (!$lastname) {
        $_SESSION['add-user'] = "Por favor introduce tu Apellido";
    } elseif (!$username) {
        $_SESSION['add-user'] = "Por favor introduce tu Nombre de Usuario";
    } elseif (!$email) {
        $_SESSION['add-user'] = "Por favor introduce un Email válido";
    } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
        $_SESSION['add-user'] = "La contraseña debe tener mas de 8 caracteres";
    } elseif (!$avatar['name']) {
        $_SESSION['add-user'] = "Por favor añade un avatar";
    } else {
        // Chequear si las contraseñas no coinciden
        if ($createpassword !== $confirmpassword) {
            $_SESSION['signup'] = "Las contraseñas no coinciden!";
        } else {
            // hash de contraseñas
            $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

            // Chequear si el username o el email ya existen en la DB
            $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
            $user_check_result = mysqli_query($connection, $user_check_query);
            if (mysqli_num_rows($user_check_result) > 0) {
                $_SESSION['add-user'] = "Nombre de Usuario o Email ya existente";
            } else {
                // AVATAAAAAAR
                // Renombrar avatar
                $time = time(); // Asegurarse de que cada imagen es unica
                $avatar_name = $time . $avatar['name'];
                $avatar_tmp_name = $avatar['tmp_name'];
                $avatar_destination_path = '../images/' . $avatar_name;

                // Asegurarse de que el archivo es una imagen
                $allowed_files = ['png', 'jpg', 'jpeg'];
                $extention = explode('.', $avatar_name);
                $extention = end($extention);
                if (in_array($extention, $allowed_files)) {
                    // Asegurarse de que la imagen no es muy grande (1mb+)
                    if ($avatar['size'] < 1000000) {
                        // Cargar Avatar
                        move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                    } else {
                        $_SESSION['add-user'] = "Archivo muy pesado! Debe pesar menos de 1MB";
                    }
                } else {
                    $_SESSION['add-user'] = "El archivo debe ser formato png, jpg, o jpeg";
                }
            }
        }
    }

    // Redirigir a la pagina de añadir usuario si hay algún problema
    if (isset($_SESSION['add-user'])) {
        $_SESSION['add-user-data'] = $_POST;
        header('location: ' . ROOT_URL . '/admin/add-user.php');
        die();
    } else {
        // Insertar nuevo usuario a la tabla en la DB
        $insert_user_query = "INSERT INTO users SET firstname='$firstname', lastname='$lastname', username='$username', email='$email', password='$hashed_password', avatar='$avatar_name', is_admin=$is_admin";
        $insert_user_result = mysqli_query($connection, $insert_user_query);

        if (!mysqli_errno($connection)) {
            // Redireccionar a la pagina de inicio de sesión (Con un mensajito de exito)
            $_SESSION['add-user-success'] = "New user $firstname $lastname added successfully.";
            header('location: ' . ROOT_URL . 'admin/manage-users.php');
            die();
        }
    }
} else {
    // Si el boton no fué clickeado, redirecciona.
    header('location: ' . ROOT_URL . 'admin/add-user.php');
    die();
}
