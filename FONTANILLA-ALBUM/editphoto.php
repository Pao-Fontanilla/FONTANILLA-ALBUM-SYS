<?php  
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the photo ID from the URL
$photo_id = isset($_GET['photo_id']) ? $_GET['photo_id'] : null;

if (!$photo_id) {
    header("Location: index.php");
    exit();
}

// Fetch photo details using the photo ID
$photo = getPhotoByID($pdo, $photo_id);
if (!$photo) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Photo</title>
    <link rel="stylesheet" href="styles1/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1>EDIT PHOTOGRAPH</h1>

    <div class="view container">
        <!-- Edit Photo Form -->
        <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="photo_id" value="<?php echo htmlspecialchars($photo['photo_id']); ?>">

            <p>
                <label for="photoDescription">Description:</label>
                <input type="text" name="photoDescription" value="<?php echo htmlspecialchars($photo['description']); ?>" required>
            </p>

            <!-- Display the current image -->
            <p>
                <label for="currentImage">Current Image:</label><br>
                <img src="images/<?php echo htmlspecialchars($photo['photo_name']); ?>" alt="Current Photo" style="max-width: 100%; height: auto; margin-bottom: 15px;">
            </p>

            <p>
                <label for="image">Change Photo:</label>
                <input type="file" name="image">
            </p>

            <button type="submit" name="updatePhotoBtn">Update Photo</button>
        </form>
    </div>
</body>
</html>
