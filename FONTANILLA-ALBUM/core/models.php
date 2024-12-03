<?php  

require_once 'dbConfig.php';

function checkIfUserExists($pdo, $username) {
    $response = array();
    $sql = "SELECT * FROM user_accounts WHERE username = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$username])) {
        $userInfoArray = $stmt->fetch();

        if ($stmt->rowCount() > 0) {
            $response = array(
                "result" => true,
                "status" => "200",
                "userInfoArray" => $userInfoArray
            );
        } else {
            $response = array(
                "result" => false,
                "status" => "400",
                "message" => "User doesn't exist in the database"
            );
        }
    }

    return $response;
}

function insertNewUser($pdo, $username, $first_name, $last_name, $password) {
    $response = array();
    $checkIfUserExists = checkIfUserExists($pdo, $username); 

    if (!$checkIfUserExists['result']) {
        $sql = "INSERT INTO user_accounts (username, first_name, last_name, password) 
        VALUES (?,?,?,?)";

        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $first_name, $last_name, $password])) {
            $response = array(
                "status" => "200",
                "message" => "User successfully inserted!"
            );
        } else {
            $response = array(
                "status" => "400",
                "message" => "An error occurred with the query!"
            );
        }
    } else {
        $response = array(
            "status" => "400",
            "message" => "User already exists!"
        );
    }

    return $response;
}

function getAllUsers($pdo) {
    $sql = "SELECT * FROM user_accounts";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute();

    if ($executeQuery) {
        return $stmt->fetchAll();
    }
}

function getUserByID($pdo, $username) {
    $sql = "SELECT * FROM user_accounts WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$username]);

    if ($executeQuery) {
        return $stmt->fetch();
    }
}

// Insert or update a photo
function insertPhoto($pdo, $photo_name, $username, $description, $album_id = null) {
    if (empty($album_id)) {
        $sql = "INSERT INTO photos (photo_name, username, description) VALUES(?,?,?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$photo_name, $username, $description]);
    } else {
        $sql = "INSERT INTO photos (photo_name, username, description, album_id) VALUES(?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$photo_name, $username, $description, $album_id]);
    }
}

// Get photos for a specific album or all photos
function getAllPhotos($pdo, $album_id = null) {
    if ($album_id) {
        $sql = "SELECT * FROM photos WHERE album_id = :album_id ORDER BY date_added DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['album_id' => $album_id]);
    } else {
        $sql = "SELECT * FROM photos ORDER BY date_added DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    return $stmt->fetchAll();
}

function getPhotoByID($pdo, $photo_id) {
    // Query to select the photo based on its ID
    $sql = "SELECT * FROM photos WHERE photo_id = ?";
    $stmt = $pdo->prepare($sql);
    
    // Execute the query with the provided photo_id
    $stmt->execute([$photo_id]);

    // Fetch the photo data
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the photo data
    return $photo;
}

function deletePhoto($pdo, $photo_id) {
    $sql = "DELETE FROM photos WHERE photo_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$photo_id]);
}

function insertComment($pdo, $photo_id, $username, $description) {
    $sql = "INSERT INTO comments (photo_id, username, description) VALUES(?,?,?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$photo_id, $username, $description]);
}

function getCommentByID($pdo, $comment_id) {
    $sql = "SELECT * FROM comments WHERE comment_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$comment_id]);

    return $stmt->fetch();
}

function updateComment($pdo, $description, $comment_id) {
    $sql = "UPDATE comments SET description = ? WHERE comment_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$description, $comment_id]);
}

function deleteComment($pdo, $comment_id) {
    $sql = "DELETE FROM comments WHERE comment_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$comment_id]);
}

// Create an album
function createAlbum($album_name, $user_id) {
    global $pdo;
    $sql = "INSERT INTO albums (album_name, user_id) VALUES (:album_name, :user_id)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['album_name' => $album_name, 'user_id' => $user_id]);
}

// Edit album name
function editAlbumName($album_id, $new_album_name) {
    global $pdo;
    $sql = "UPDATE albums SET album_name = :new_album_name WHERE album_id = :album_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['new_album_name' => $new_album_name, 'album_id' => $album_id]);
}

// Delete an album and associated photos
function deleteAlbum($album_id) {
    global $pdo;

    // Fetch all photos associated with the album
    $sql = "SELECT * FROM photos WHERE album_id = :album_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['album_id' => $album_id]);

    $photos = $stmt->fetchAll();

    // Delete the photos from the database and delete the actual image files
    foreach ($photos as $photo) {
        // Delete the actual image file from the server
        $image_path = "../images/" . $photo['photo_name'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the file
        }

        // Now delete the photo from the database
        $sql = "DELETE FROM photos WHERE photo_id = :photo_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['photo_id' => $photo['photo_id']]);
    }

    // Now delete the album from the database
    $sql = "DELETE FROM albums WHERE album_id = :album_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['album_id' => $album_id]);
}


// Get albums for a specific user
function getUserAlbums($user_id) {
    global $pdo;
    $sql = "SELECT * FROM albums WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll();
}

// Get a specific album by its ID for a user
function getUserAlbumByID($user_id, $album_id) {
    global $pdo;
    $sql = "SELECT * FROM albums WHERE user_id = :user_id AND album_id = :album_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'album_id' => $album_id]);
    return $stmt->fetch();
}

function getAlbumByID($album_id) {
    global $pdo;
    $sql = "SELECT a.*, u.username 
            FROM albums a
            JOIN user_accounts u ON a.user_id = u.user_id
            WHERE a.album_id = :album_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['album_id' => $album_id]);
    return $stmt->fetch();
}

function getAllAlbums() {
    global $pdo;
    $sql = "SELECT a.*, u.username 
            FROM albums a
            JOIN user_accounts u ON a.user_id = u.user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function updatePhoto($pdo, $photo_id, $description, $imageName = null) {
    $sql = "UPDATE photos SET description = ?";

    if ($imageName) {
        // Update the image if a new one was uploaded
        $sql .= ", photo_name = ?";
    }

    $sql .= " WHERE photo_id = ?";

    $stmt = $pdo->prepare($sql);

    // Execute the update query, binding parameters accordingly
    if ($imageName) {
        return $stmt->execute([$description, $imageName, $photo_id]);
    } else {
        return $stmt->execute([$description, $photo_id]);
    }
}

?>
