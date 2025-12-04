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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
        }

        /* Page Header */
        .favorites-header {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            color: white;
            border-radius: 0 0 40px 40px;
            margin-bottom: 60px;
        }

        .favorites-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .favorites-header p {
            font-size: 20px;
            opacity: 0.95;
        }

        /* Recipe Grid */
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 32px;
            margin-bottom: 80px;
        }

        /* Recipe Card */
        .recipe-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        /* Recipe Image */
        .recipe-image {
            width: 100%;
            height: 240px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .recipe-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.1) 100%);
        }

        /* Favorite Badge */
        .favorite-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 2;
        }

        /* Category Badge */
        .category-badge {
            position: absolute;
            bottom: 16px;
            left: 16px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #FF6B6B;
            z-index: 2;
        }

        /* Card Content */
        .recipe-content {
            padding: 24px;
        }

        .recipe-title {
            font-family: 'Lora', serif;
            font-size: 22px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recipe-description {
            font-size: 15px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recipe-meta {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #4a5568;
        }

        .meta-item .icon {
            font-size: 16px;
        }

        .recipe-author {
            font-size: 14px;
            color: #a0aec0;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* Action Buttons */
        .recipe-actions {
            display: flex;
            gap: 12px;
        }

        .btn-view {
            flex: 1;
            padding: 12px 24px;
            background: #FF6B6B;
            color: white;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-view:hover {
            background: #EE5A52;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .btn-remove {
            padding: 12px 20px;
            background: white;
            color: #E53E3E;
            border: 2px solid #E53E3E;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background: #E53E3E;
            color: white;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-family: 'Lora', serif;
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 16px;
        }

        .empty-state p {
            font-size: 18px;
            color: #718096;
            margin-bottom: 32px;
        }

        .btn-browse {
            display: inline-block;
            padding: 14px 32px;
            background: #FF6B6B;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-browse:hover {
            background: #EE5A52;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* Alert Styling */
        .alert-login {
            background: white;
            border: 2px solid #FF6B6B;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            max-width: 600px;
            margin: 60px auto;
        }

        .alert-login h3 {
            font-family: 'Lora', serif;
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 16px;
        }

        .alert-login p {
            font-size: 18px;
            color: #718096;
            margin-bottom: 24px;
        }

        .alert-login a {
            color: #FF6B6B;
            font-weight: 600;
            text-decoration: none;
        }

        .alert-login a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .favorites-header h1 {
                font-size: 36px;
            }

            .favorites-grid {
                grid-template-columns: 1fr;
            }

            .recipe-actions {
                flex-direction: column;
            }

            .btn-remove {
                width: 100%;
            }
        }
    </style>
</head>


<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<!-- Page Header -->
<div class="favorites-header">
    <h1>My Favorite Recipes</h1>
    <p>All your saved recipes in one delicious place</p>
</div>

<main role="main" class="container">

    <?php if (!$user_logged_in): ?>
        <div class="alert-login">
            <h3>Login Required</h3>
            <p>Please <a href="login.php">log in</a> to view your favorite recipes and start building your personal collection.</p>
        </div>
    <?php else: ?>
        <?php if ($favorites_result && $favorites_result->num_rows > 0): ?>
            <div class="favorites-grid">
                <?php while ($recipe = $favorites_result->fetch_assoc()): ?>
                    <div class="recipe-card">
                        <!-- Recipe Image -->
                        <div class="recipe-image" style="background-image: url('<?php echo htmlspecialchars($recipe['image_url'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600'); ?>');">
                            <div class="favorite-badge">❤️</div>
                            <div class="category-badge"><?= htmlspecialchars($recipe['category_name']); ?></div>
                        </div>

                        <!-- Recipe Content -->
                        <div class="recipe-content">
                            <h3 class="recipe-title"><?= htmlspecialchars($recipe['title']); ?></h3>
                            <p class="recipe-description"><?= htmlspecialchars($recipe['description_text']); ?></p>

                            <div class="recipe-meta">
                                <?php if ($recipe['prep_time']): ?>
                                    <div class="meta-item">
                                        <span class="icon">⏱️</span>
                                        <span><?= htmlspecialchars($recipe['prep_time']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($recipe['avg_rating']): ?>
                                    <div class="meta-item">
                                        <span class="icon">⭐</span>
                                        <span><?= number_format($recipe['avg_rating'], 1); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($recipe['comment_count'] > 0): ?>
                                    <div class="meta-item">
                                        <span class="icon">💬</span>
                                        <span><?= intval($recipe['comment_count']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="recipe-author">By <?= htmlspecialchars($recipe['creator_name']); ?></p>

                            <!-- Action Buttons -->
                            <div class="recipe-actions">
                                <a href="view_recipe.php?id=<?= $recipe['recipe_id']; ?>" class="btn-view">
                                    View Recipe
                                </a>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="remove_favorite" value="1">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id']; ?>">
                                    <button type="submit" class="btn-remove">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💔</div>
                <h3>No Favorites Yet</h3>
                <p>Start exploring recipes and save your favorites to see them here!</p>
                <a href="browse_recipes.php" class="btn-browse">Browse Recipes</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>

