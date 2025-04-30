<?php
require 'config/database.php';

// Obtener los datos del registro si el boton de registrarse fué clickeado
if (isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];

    // Validar los valores de ingreso
    if (!$firstname) {
        $_SESSION['signup'] = "Por favor, introduce tu nombre";
    } elseif (!$lastname) {
        $_SESSION['signup'] = "Por favor, introduce tu apellido";
    } elseif (!$username) {
        $_SESSION['signup'] = "Por favor, introduce tu nombre de usuario";
    } elseif (!$email) {
        $_SESSION['signup'] = "Por favor, introduce un email valido";
    } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
        $_SESSION['signup'] = "La contraseña debe tener mas de 8 caracteres";
    } elseif (!$avatar['name']) {
        $_SESSION['signup'] = "Por favor, añade un avatar";
    } else {
        // Chequear si las constraseñas no coinciden
        if ($createpassword !== $confirmpassword) {
            $_SESSION['signup'] = "Las contraseñas no coinciden!";
        } else {
            // hash contraseña
            $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

            // Chequear si el username o el email ya existen en la DB
            $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
            $user_check_result = mysqli_query($connection, $user_check_query);
            if (mysqli_num_rows($user_check_result) > 0) {
                $_SESSION['signup'] = "El nombre de usuario o el email ya existen!";
            } else {
                // AVATARRRRRRR
                // renombrar avatar
                $time = time(); // Asegurarse de que cada imagen es unica
                $avatar_name = $time . $avatar['name'];
                $avatar_tmp_name = $avatar['tmp_name'];
                $avatar_destination_path = 'images/' . $avatar_name;

                // Asegurarse de que el archivo es una imagen
                $allowed_files = ['png', 'jpg', 'jpeg'];
                $extention = explode('.', $avatar_name);
                $extention = end($extention);
                if (in_array($extention, $allowed_files)) {
                    // Asegurarse de que el archivo no es muy grande (1mb+)
                    if ($avatar['size'] < 1000000) {
                        // Cargar avatar
                        move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                    } else {
                        $_SESSION['signup'] = "Archivo muy grande! Debe pesar menos de 1 MB";
                    }
                } else {
                    $_SESSION['signup'] = "El archivo debe ser formato png, jpg, o jpeg";
                }
            }
        }
    }

    // Redirigir a la pagina de registro si hay algun problema
    if (isset($_SESSION['signup'])) {
        // Regresar la información a la pagina de registro
        $_SESSION['signup-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    } else {
        // Insertar nuevo usuario en la tabla de usuarios
        $insert_user_query = "INSERT INTO users SET firstname='$firstname', lastname='$lastname', username='$username', email='$email', password='$hashed_password', avatar='$avatar_name', is_admin=0";
        $insert_user_result = mysqli_query($connection, $insert_user_query);

        if (!mysqli_errno($connection)) {
            // Redirigir a la pagina de inicio de sesión (Con un mensajito exitoso)
            $_SESSION['signup-success'] = "Registro exitoso!. Por favor inicia sesión";
            header('location: ' . ROOT_URL . 'signin.php');
            die();
        }
    }
} else {
    // Si el boton no fué clickeado, rebotar y volver.
    header('location: ' . ROOT_URL . 'signup.php');
    die();
}
