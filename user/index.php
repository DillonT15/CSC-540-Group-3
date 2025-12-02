<?php
//======================================================================
// User Home Page
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

include_once(realpath(dirname(__FILE__, 2).'/php/session.php'));
include_once(realpath(dirname(__FILE__, 2).'/php/path.php'));
include_once(ROOT_SRC_PATH.'/check_user.php');

$page_name = "user";
$user_check = $_SESSION['login_user'];

?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title>Welcome | Recipe Sharing Platform</title>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container mt-5">

    <!-- Hero Section -->
    <div class="jumbotron text-center bg-light p-5 rounded-3 shadow-sm">
        <h1 class="display-4 fw-bold">Welcome to the Recipe Sharing Platform 🍽️</h1>

        <p class="lead mt-3">
            A community-driven place to explore delicious recipes, save your favorites,
            and share your own creations with fellow food enthusiasts.
        </p>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="../login.php" class="btn btn-primary btn-lg mt-2">Login</a>
            <a href="../register.php" class="btn btn-success btn-lg mt-2">Register</a>
        <?php else: ?>
            <a href="browse_recipes.php" class="btn btn-info btn-lg mt-2">Browse Recipes</a>
        <?php endif; ?>
    </div>

    <!-- About / Features -->
    <div class="mt-5 text-center">
        <h2>What You Can Do Here</h2>
        <p class="lead mb-4">
            Whether you’re a seasoned chef or just starting out, you’ll find everything you need:
        </p>

        <div class="row mt-4">
            <div class="col-md-3">
                <h4>🔍 Discover</h4>
                <p>Browse community-posted recipes and find inspiration.</p>
            </div>
            <div class="col-md-3">
                <h4>📤 Share</h4>
                <p>Upload your own recipes and showcase your cooking skills.</p>
            </div>
            <div class="col-md-3">
                <h4>❤️ Favorite</h4>
                <p>Save recipes you love for later.</p>
            </div>
            <div class="col-md-3">
                <h4>💬 Comment</h4>
                <p>Join the discussion and share tips with others.</p>
            </div>
        </div>
    </div>

</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>