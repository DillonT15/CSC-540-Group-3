<?php
//======================================================================
// VIEW FAVORITES PAGE
//======================================================================

error_reporting(E_ALL);
ini_set('display_errors', 0);

include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
include_once (ROOT_SRC_PATH . '/config.php'); // DB connection

$page_name = "user";

// make sure the user is logged in
if (!isset($_SESSION['login_user'])) {
    $user_logged_in = false;
} else {
    $user_logged_in = true;
    $user_id = intval($_SESSION['user_id']);

    // remove from favorites
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite']) && isset($_POST['recipe_id'])) {
        $remove_recipe_id = intval($_POST['recipe_id']);
        $delete_query = "DELETE FROM Favorites WHERE user_id = $user_id AND recipe_id = $remove_recipe_id";
        $db_connection->query($delete_query);

        // reload the page to update the list
        header("Location: favorites.php");
        exit();
    }

    // fetch all recipes favorited by the user
    $favorites_query = "
        SELECT r.recipe_id, r.title, r.description_text, c.name AS category_name,
               CONCAT(con.first_name, ' ', con.last_name) AS creator_name
        FROM Favorites f
        JOIN Recipes r ON f.recipe_id = r.recipe_id
        LEFT JOIN Categories c ON r.category_id = c.category_id
        LEFT JOIN Users u ON r.user_id = u.user_id
        LEFT JOIN Contacts con ON u.contact_id = con.contact_id
        WHERE f.user_id = $user_id
        ORDER BY r.title ASC
    ";
    $favorites_result = $db_connection->query($favorites_query);
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title>View Favorites | Recipe Sharing Platform</title>
</head>


<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container mt-5">

    <div class="text-center mb-5">
        <h1 class="display-4">View Favorites</h1>
        <p class="lead">All of your favorited recipes are listed below!!</p>
    </div>




    <?php if (!$user_logged_in): ?>
        <div class="alert alert-info text-center">
            Please <a href="login.php">log in</a> to view your favorite recipes.
        </div>
    <?php else: ?>
        <?php if ($favorites_result && $favorites_result->num_rows > 0): ?>
            <div class="row">
                <?php while ($recipe = $favorites_result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($recipe['title']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($recipe['category_name']); ?></h6>
                                <p class="card-text flex-grow-1"><?= htmlspecialchars(substr($recipe['description_text'], 0, 100)) . '...'; ?></p>
                                <p class="text-muted mb-2">By <?= htmlspecialchars($recipe['creator_name']); ?></p>
                                <a href="view_recipe.php?id=<?= $recipe['recipe_id']; ?>" 
                                    class="btn btn-primary btn-sm w-100 mb-2">
                                    View Recipe
                                    </a>

                                <!-- Remove from Favorites Form -->
                                <form method="POST" class="mt-auto">
                                    <input type="hidden" name="remove_favorite" value="1">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                                    <button class="btn btn-danger btn-sm w-100">Unfavorite</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                You currently have no favorite recipes!!
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>

