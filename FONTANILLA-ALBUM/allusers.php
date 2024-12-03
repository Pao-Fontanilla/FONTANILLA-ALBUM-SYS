<?php require_once 'core/dbConfig.php'; ?>
<?php require_once 'core/models.php'; ?>

<?php  
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles1/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>All Users</h1>
        <div class="userList">
            <?php $getAllUsers = getAllUsers($pdo); ?>
            <?php foreach ($getAllUsers as $row) { ?>
                <form action="profile.php" method="GET" style="margin-top: 10px;">
                    <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                    <button type="submit" class="userButton"><?php echo $row['username']; ?></button>
                </form>
            <?php } ?>
        </div>
    </div>

</body>
</html>
