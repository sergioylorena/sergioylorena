<?php
require 'config/database.php';

// Asegurarse de que el boton de editar post fué clickeado
if (isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $previous_thumbnail_name = filter_var($_POST['previous_thumbnail_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT);
    $thumbnail = $_FILES['thumbnail'];

    // set is_ destaca si ya se realizó el chequeo
    $is_featured = $is_featured == 1 ?: 0;

    // Chequear y validar los valores del input
    if (!$title) {
        $_SESSION['edit-post'] = "No se pudo actualizar el Post!";
    } elseif (!$category_id) {
        $_SESSION['edit-post'] = "No se pudo actualizar el Post!";
    } elseif (!$body) {
        $_SESSION['edit-post'] = "No se pudo actualizar el Post!";
    } else {
        // Borrar una miniatura existente si fué actualizada por una valida
        if ($thumbnail['name']) {
            $previous_thumbnail_path = '../images/' . $previous_thumbnail_name;
            if ($previous_thumbnail_path) {
                unlink($previous_thumbnail_path);
            }

            // MINIS (PARA Edit)
            // Renombre de imagen
            $time = time(); // Asegurarse de que cada imagen es unica
            $thumbnail_name = $time . $thumbnail['name'];
            $thumbnail_tmp_name = $thumbnail['tmp_name'];
            $thumbnail_destination_path = '../images/' . $thumbnail_name;

            // Asegurarse de que el archivo es una imagen
            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extension = explode('.', $thumbnail_name);
            $extension = end($extension);
            if (in_array($extension, $allowed_files)) {
                // Asegurarse de que no es muy grande (2mb+)
                if ($thumbnail['size'] < 2000000) {
                    // Cargar avatar
                    move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
                } else {
                    $_SESSION['edit-post'] = "Fallo en la actualización. Miniatura demasiado pesada!. Debe pesar menos de 2 MB";
                }
            } else {
                $_SESSION['edit-post'] = "Fallo en la actualización. La Miniatura debe ser de formato png, jpg o jpeg";
            }
        }
    }


    if ($_SESSION['edit-post']) {
        // Si algo fué invalido, se redirigirá al panel administrativo
        header('location: ' . ROOT_URL . 'admin/');
        die();
    } else {
        // set is_ remplaza el featured actual si el de mas reciente adición tiene valor de 1
        if ($is_featured == 1) {
            $zero_all_is_featured_query = "UPDATE posts SET is_featured=0";
            $zero_all_is_featured_result = mysqli_query($connection, $zero_all_is_featured_query);
        }

        // setear el nombre de la mini si una nueva fué cargada, o en caso contrario mantener el nombre antiguo
        $thumbnail_to_insert = $thumbnail_name ?? $previous_thumbnail_name;

        $query = "UPDATE posts SET title='$title', body='$body', thumbnail='$thumbnail_to_insert', category_id=$category_id, is_featured=$is_featured WHERE id=$id LIMIT 1";
        $result = mysqli_query($connection, $query);
    }


    if (!mysqli_errno($connection)) {
        $_SESSION['edit-post-success'] = "Post actualizado correctamente";
    }
}

header('location: ' . ROOT_URL . 'admin/');
die();
