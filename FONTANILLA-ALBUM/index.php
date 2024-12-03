<?php  
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums</title>
    <link rel="stylesheet" href="styles1/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php  
    if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
        if ($_SESSION['status'] == "200") {
            echo "<h1 style='color: green;'>{$_SESSION['message']}</h1>";
        } else {
            echo "<h1 style='color: red;'>{$_SESSION['message']}</h1>";  
        }
    }
    unset($_SESSION['message']);
    unset($_SESSION['status']);
    ?>

    <!-- Album Creation Form -->
    <div class="container">
        <form action="core/handleForms.php" method="POST">
            <label for="album_name">Create a New Album</label>
            <input type="text" name="album_name" placeholder="Album Name" required>
            <button type="submit" name="create_album">Create Album</button>
        </form>
    </div>

    <!-- Display Albums -->
    <h2>ALBUMS</h2>

    <div class="albumList">
        <?php
        // Fetch all albums
        $albums = getAllAlbums(); // Fetch all albums without filtering by user_id
        if (empty($albums)) { 
            echo "<p>No album found.</p>";
        } else {
            foreach ($albums as $album) { ?>
                <div class="albumContainer">
                    <h3><?php echo htmlspecialchars($album['album_name']); ?></h3>
                    <p>Created by: <?php echo htmlspecialchars($album['username']); ?></p>

                    <!-- View Album Button -->
                    <form action="viewalbum.php" method="GET">
                        <input type="hidden" name="album_id" value="<?php echo $album['album_id']; ?>">
                        <button type="submit" name="view_album">View Album</button>
                    </form>

                    <!-- Delete Album Button (Visible only to the creator) -->
                    <?php if ($_SESSION['user_id'] == $album['user_id']) { ?>
                        <form method="POST" action="core/handleForms.php" onsubmit="return confirm('Are you sure you want to delete this album?');">
                            <input type="hidden" name="album_id" value="<?php echo $album['album_id']; ?>">
                            <button type="submit" name="delete_album">Delete Album</button>
                        </form>
                    <?php } ?>
                </div>
            <?php }
        }
        ?>
    </div>

</body>
</html>
