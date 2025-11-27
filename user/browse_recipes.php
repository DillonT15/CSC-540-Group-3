<?php
//======================================================================
// This is the homepage for User
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // set to 1 to display errors, 0 to hide them

  /* Quick Paths */
  /* note the 2 after __FILE__, because it's 2 directories deep */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
  // Session will be included in header.php

  /* Page Name */
  $page_name = "user";

?>
<?php
//======================================================================
// ADMIN DASHBOARD PAGE
//======================================================================

error_reporting(E_ALL);
ini_set('display_errors', 0); // set to 1 to display errors, 0 to hide them

  /* Quick Paths */
  /* note the 2 after __FILE__, because it's 2 directories deep */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
  // Session will be included in header.php
  
  /* Check Role */
  include_once (ROOT_SRC_PATH .'/check_user.php');

  include_once(ROOT_SRC_PATH . '/config.php'); // ensures $db_connection exists

  $user_check = $_SESSION['login_user'];
  // Check user and get roll session from database

  /* Page Name */
  $page_name = "admin";
?>




<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title>Browse Recipes | Recipe Sharing Platform</title>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container mt-5">

    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-4">Browse Recipes</h1>
        <p class="lead">Explore all recipes shared by our community members. Click on any recipe to view details and instructions. You can also Create a Recipe:</p>
            <!-- Placeholder for creating recipe button. Still need to implement creating a recipe below-->
        <a href="create_recipe.php" class="btn btn-success btn-lg mt-3">Create Recipe</a>
    





    </div>
            <!-- All Recipes will be listed below here -->
<?php
// Fetch all recipes with related data
$query = "SELECT 
    r.recipe_id,
    r.title,
    r.description_text,
    r.prep_time,
    r.cook_time,
    c.name as category_name,
    CONCAT(con.first_name, ' ', con.last_name) as creator_name,
    AVG(rat.rating) as avg_rating,
    COUNT(DISTINCT com.comment_id) as comment_count,
    COUNT(DISTINCT f.user_id) as favorite_count
FROM Recipes r
LEFT JOIN Categories c ON r.category_id = c.category_id
LEFT JOIN Users u ON r.user_id = u.user_id
LEFT JOIN Contacts con ON u.contact_id = con.contact_id
LEFT JOIN Ratings rat ON r.recipe_id = rat.recipe_id
LEFT JOIN Comments com ON r.recipe_id = com.recipe_id
LEFT JOIN Favorites f ON r.recipe_id = f.recipe_id
GROUP BY r.recipe_id
ORDER BY r.recipe_id DESC";

$recipes = $db_connection->query($query);

if ($recipes && $recipes->num_rows > 0) {
    echo '<div class="row">';
    
    while ($recipe = $recipes->fetch_assoc()) {
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                    <p class="card-text">
                        <small class="text-muted">By <?php echo htmlspecialchars($recipe['creator_name']); ?></small>
                    </p>
                    <p class="card-text"><?php echo htmlspecialchars(substr($recipe['description_text'], 0, 100)); ?>...</p>
                    
                    <div class="mb-2">
                        <span class="badge badge-primary"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                    </div>
                    
                    <div class="mb-2">
                        <small>
                            ⭐ <?php echo number_format($recipe['avg_rating'], 1); ?> | 
                            💬 <?php echo $recipe['comment_count']; ?> | 
                            ❤️ <?php echo $recipe['favorite_count']; ?>
                        </small>
                    </div>
                    
                    <div class="mb-2">
                        <small>
                            <strong>Prep:</strong> <?php echo htmlspecialchars($recipe['prep_time']); ?> | 
                            <strong>Cook:</strong> <?php echo htmlspecialchars($recipe['cook_time']); ?>
                        </small>
                    </div>
                    
                    <a href="view_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-primary btn-sm">View Recipe</a>
                </div>
            </div>
        </div>
        <?php
    }
    
    echo '</div>';
} else {
    echo '<div class="alert alert-info">No recipes found. Be the first to create one!</div>';
}
?>






</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>

