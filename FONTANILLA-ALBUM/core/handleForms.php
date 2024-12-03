<?php  
require_once 'dbConfig.php';
require_once 'models.php';

// User Registration
if (isset($_POST['insertNewUserBtn'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {
        if ($password == $confirm_password) {
            $insertQuery = insertNewUser($pdo, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
            $_SESSION['message'] = $insertQuery['message'];

            if ($insertQuery['status'] == '200') {
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../login.php");
                exit();
            } else {
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../register.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Passwords do not match";
            $_SESSION['status'] = '400';
            header("Location: ../register.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Please fill out all fields.";
        $_SESSION['status'] = '400';
        header("Location: ../register.php");
        exit();
    }
}

// User Login
if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $loginQuery = checkIfUserExists($pdo, $username);
        $userIDFromDB = $loginQuery['userInfoArray']['user_id'];
        $usernameFromDB = $loginQuery['userInfoArray']['username'];
        $passwordFromDB = $loginQuery['userInfoArray']['password'];

        if (password_verify($password, $passwordFromDB)) {
            $_SESSION['user_id'] = $userIDFromDB;
            $_SESSION['username'] = $usernameFromDB;
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid username/password";
            $_SESSION['status'] = "400";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Please fill out all fields.";
        $_SESSION['status'] = '400';
        header("Location: ../login.php");
        exit();
    }
}

// Logout
if (isset($_GET['logoutUserBtn'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    header("Location: ../login.php");
    exit();
}

// Handle Album Creation
if (isset($_POST['create_album'])) {
    $album_name = $_POST['album_name'];
    $user_id = $_SESSION['user_id'];

    if (createAlbum($album_name, $user_id)) {
        $_SESSION['message'] = "Album created successfully!";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Error: Could not create album.";
        $_SESSION['status'] = '400';
    }
    header("Location: ../index.php");
    exit();
}

// Handle Edit Album Name
if (isset($_POST['edit_album'])) {
    $album_id = $_POST['album_id'];
    $new_album_name = $_POST['new_album_name'];

    // Call the function to edit the album name
    $editAlbumResult = editAlbumName($album_id, $new_album_name);

    if ($editAlbumResult) {
        $_SESSION['message'] = "Album name updated successfully!";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Error: Could not update album name.";
        $_SESSION['status'] = '400';
    }

    // Redirect to the index.php (albums list)
    header("Location: ../index.php");
    exit();
}

// Handle Album Deletion
if (isset($_POST['delete_album'])) {
    $album_id = $_POST['album_id'];

    // Call the deleteAlbum function to delete the album and associated photos
    $deleteAlbumResult = deleteAlbum($album_id);

    if ($deleteAlbumResult) {
        $_SESSION['message'] = "Album deleted successfully!";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Error: Could not delete album.";
        $_SESSION['status'] = '400';
    }

    // Redirect to index.php after album deletion
    header("Location: ../index.php");
    exit();
}

// Insert Photo
if (isset($_POST['insertPhotoBtn'])) {
    $description = $_POST['photoDescription'];
    $album_id = $_POST['album_id']; // Album ID to associate the photo
    $fileName = $_FILES['image']['name'];
    $tempFileName = $_FILES['image']['tmp_name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueID = sha1(md5(rand(1, 9999999)));
    $imageName = $uniqueID . "." . $fileExtension;

    if (insertPhoto($pdo, $imageName, $_SESSION['username'], $description, $album_id)) {
        $folder = "../images/" . $imageName;
        if (move_uploaded_file($tempFileName, $folder)) {
            $_SESSION['message'] = "Photo uploaded successfully!";
            $_SESSION['status'] = '200';
        } else {
            $_SESSION['message'] = "Error: Could not upload photo.";
            $_SESSION['status'] = '400';
        }
    } else {
        $_SESSION['message'] = "Error: Could not save photo to the database.";
        $_SESSION['status'] = '400';
    }
    header("Location: ../viewalbum.php?album_id=" . $album_id);
    exit();
}

// Delete Photo
if (isset($_POST['deletePhotoBtn'])) {
    $photo_name = $_POST['photo_name'];
    $photo_id = $_POST['photo_id'];

    if (deletePhoto($pdo, $photo_id)) {
        unlink("../images/" . $photo_name);
        $_SESSION['message'] = "Photo deleted successfully!";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Error: Could not delete photo.";
        $_SESSION['status'] = '400';
    }
    header("Location: ../index.php");
    exit();
}

// Update Photo
if (isset($_POST['updatePhotoBtn'])) {
    $photo_id = $_POST['photo_id'];
    $description = $_POST['photoDescription'];
    $imageName = null;

    // Check if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Handle file upload
        $fileName = $_FILES['image']['name'];
        $tempFileName = $_FILES['image']['tmp_name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueID = sha1(md5(rand(1, 9999999))); // Generate unique ID for the image
        $imageName = $uniqueID . "." . $fileExtension;

        // Move the uploaded file to the server directory
        $folder = "../images/" . $imageName;
        if (!move_uploaded_file($tempFileName, $folder)) {
            $_SESSION['message'] = "Error uploading photo.";
            $_SESSION['status'] = '400';
            header("Location: ../editphoto.php?photo_id=" . $photo_id);
            exit();
        }
    }

    // Update photo in the database
    $updateResult = updatePhoto($pdo, $photo_id, $description, $imageName);

    if ($updateResult) {
        $_SESSION['message'] = "Photo updated successfully!";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Error updating photo.";
        $_SESSION['status'] = '400';
    }

    header("Location: ../viewalbum.php?album_id=" . $_POST['album_id']);
    exit();
}
?>
