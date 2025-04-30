<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT);
    $thumbnail = $_FILES['thumbnail'];

    // setear is_featured a 0 si no ha sido chequeado
    $is_featured = $is_featured == 1 ?: 0;

    // Validar la data del formulario
    if (!$title) {
        $_SESSION['add-post'] = "Introduce el título del Post";
    } elseif (!$category_id) {
        $_SESSION['add-post'] = "Selecciona la categoría del Post";
    } elseif (!$body) {
        $_SESSION['add-post'] = "Introduce el cuerpo del Post";
    } elseif (!$thumbnail['name']) {
        $_SESSION['add-post'] = "Elige una miniatura de Post";
    } else {
        // MINIIIIIIII
        // Renombrar imagen
        $time = time(); // Cada imagen con nombre unico
        $thumbnail_name = $time . $thumbnail['name'];
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = '../images/' . $thumbnail_name;

        // Asegurarse de que el archivo es una imagen
        $allowed_files = ['png', 'jpg', 'jpeg'];
        $extension = explode('.', $thumbnail_name);
        $extension = end($extension);
        if (in_array($extension, $allowed_files)) {
            // Asegurarse de que la imagen no es muy grande (2mb+)
            if ($thumbnail['size'] < 2000000) {
                // Cargar Mini
                move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
            } else {
                $_SESSION['add-post'] = "Archivo muy grande! Debe pesar menos de 2MB";
            }
        } else {
            $_SESSION['add-post'] = "El archivo debe ser formato png, jpg, o jpeg";
        }
    }

    // Si hay algun problema con la data, redirigir a la pestaña de añadir post
    if (isset($_SESSION['add-post'])) {
        $_SESSION['add-post-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/add-post.php');
        die();
    } else {
        // setear is_featured de todos los post a 0 si is_featured para el post concreto es 1
        if ($is_featured == 1) {
            $zero_all_is_featured_query = "UPDATE posts SET is_featured=0";
            $zero_all_is_featured_result = mysqli_query($connection, $zero_all_is_featured_query);
        }

        // Insertar post en la DB
        $query = "INSERT INTO posts (title, body, thumbnail, category_id, author_id, is_featured) VALUES ('$title', '$body', '$thumbnail_name', $category_id, $author_id, $is_featured)";
        $result = mysqli_query($connection, $query);

        if (!mysqli_errno($connection)) {
            $_SESSION['add-post-success'] = "Nuevo Post añadido correctamente!";
            header('location: ' . ROOT_URL . 'admin/');
            die();
        }
    }
}

header('location: ' . ROOT_URL . 'admin/add-post.php');
die();
