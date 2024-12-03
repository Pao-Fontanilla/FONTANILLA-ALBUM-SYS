<?php  
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get album ID from the URL
$album_id = isset($_GET['album_id']) ? $_GET['album_id'] : null;

if (!$album_id) {
    header("Location: index.php");
    exit();
}

// Fetch album details
$album = getAlbumByID($album_id);
if (!$album) {
    header("Location: index.php");
    exit();
}

// Fetch photos in the album
$photos = getAllPhotos($pdo, $album_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Album</title>
    <link rel="stylesheet" href="styles1/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h2>ALBUM: <?php echo htmlspecialchars($album['album_name']); ?> by <?php echo htmlspecialchars($album['username']); ?></h2>

    <!-- Edit Album Name Form (Only visible to the album owner) -->
    <?php if ($_SESSION['username'] == $album['username']) { ?>
        <div class="view container">
		<center><h3>Rename Album</h3></center>
            <form action="core/handleForms.php" method="POST">
                <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                <p>
                    <label for="new_album_name">New Album Name</label>
                    <input type="text" name="new_album_name" value="<?php echo htmlspecialchars($album['album_name']); ?>" required>
                </p>
                <button type="submit" name="edit_album">Rename Album</button>
            </form>
        </div>
    <?php } ?>

    <!-- Photo Upload Form (Only visible to the album owner) -->
    <?php if ($_SESSION['username'] == $album['username']) { ?>
        <div class="view container">
            <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
                <center><h3>Upload A Photo</h3></center>
                <p>
                    <label for="#">Description:</label>
                    <input type="text" name="photoDescription">
                </p>
                <p>
                    <label for="#">Photo Upload:</label>
                    <input type="file" name="image" required>
                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                    <button type="submit" name="insertPhotoBtn" style="margin-top: 10px;">Upload Photo</button>
                </p>
            </form>
        </div>
    <?php } ?>

    <!-- Display Photos -->
    <?php foreach ($photos as $photo) { ?>
        <div class="view container">
            <img src="images/<?php echo htmlspecialchars($photo['photo_name']); ?>" alt="" style="width: 100%;">
            <a href="profile.php?username=<?php echo htmlspecialchars($photo['username']); ?>">
                <h2><?php echo htmlspecialchars($photo['username']); ?></h2>
            </a>
            <p><i><?php echo htmlspecialchars($photo['date_added']); ?></i></p>
            <h4><?php echo htmlspecialchars($photo['description']); ?></h4>

            <!-- Only show edit/delete options for the photo owner -->
            <?php if ($_SESSION['username'] == $photo['username']) { ?>
                <a href="editphoto.php?photo_id=<?php echo $photo['photo_id']; ?>" style="float: right;"> Edit </a>
                <br><br>
                <a href="deletephoto.php?photo_id=<?php echo $photo['photo_id']; ?>" style="float: right;" onclick="return confirm('Are you sure you want to delete this photo?');"> Delete</a>
            <?php } ?><br>
        </div>
    <?php } ?>
</body>
</html>
