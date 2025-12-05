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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
        }

        /* Page Header */
        .browse-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 60px;
            border-radius: 0 0 40px 40px;
            text-align: center;
        }

        .browse-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .browse-header p {
            font-size: 20px;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .btn-create {
            display: inline-block;
            padding: 16px 40px;
            background: white;
            color: #FF6B6B;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .btn-create:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            color: #FF6B6B;
            text-decoration: none;
        }

        /* Categories Sidebar */
        .categories-sidebar {
            background: white;
            padding: 32px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .sidebar-title {
            font-family: 'Lora', serif;
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #FF6B6B;
        }

        .category-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #4a5568;
            transition: all 0.3s ease;
        }

        .category-item:hover {
            background: #f7fafc;
            color: #FF6B6B;
            text-decoration: none;
            transform: translateX(5px);
        }

        .category-item.active {
            background: #FFF5F5;
            color: #FF6B6B;
            font-weight: 600;
        }

        .category-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        /* Recipe Grid */
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 32px;
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
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.2) 100%);
        }

        /* Category Badge on Image */
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

        .recipe-author {
            font-size: 14px;
            color: #a0aec0;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .recipe-description {
            font-size: 15px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
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

        .recipe-times {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            padding: 12px;
            background: #f7fafc;
            border-radius: 8px;
            font-size: 13px;
            color: #4a5568;
        }

        .btn-view {
            width: 100%;
            padding: 12px 24px;
            background: #FF6B6B;
            color: white;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            display: block;
        }

        .btn-view:hover {
            background: #EE5A52;
            color: white;
            text-decoration: none;
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

        /* Responsive */
        @media (max-width: 992px) {
            .categories-sidebar {
                position: static;
                margin-bottom: 40px;
            }
        }

        @media (max-width: 768px) {
            .browse-header h1 {
                font-size: 36px;
            }

            .recipes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<!-- Page Header -->
<div class="browse-header">
    <div class="container">
        <h1>Browse Recipes</h1>
        <p>Explore delicious recipes shared by our community members. Find your next favorite dish!</p>
        <a href="create_recipe.php" class="btn-create">✨ Create New Recipe</a>
    </div>
</div>

<main role="main" class="container">
    <div class="row">

    <!-- Categories Sidebar -->
        <div class="col-lg-3">
            <div class="categories-sidebar">
                <h3 class="sidebar-title">Categories</h3>
                <?php
                // Fetch all categories
                $categories_query = "SELECT category_id, name FROM Categories ORDER BY name ASC";
                $categories_result = $db_connection->query($categories_query);
                
                echo '<a href="browse_recipes.php" class="category-item' . (!isset($_GET['category']) ? ' active' : '') . '">';
                echo '<span class="category-icon">🍽️</span>';
                echo '<span>All Recipes</span>';
                echo '</a>';
                
                if ($categories_result && $categories_result->num_rows > 0) {
                    while ($category = $categories_result->fetch_assoc()) {
                        $active = (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? ' active' : '';
                        
                        // Assign icons based on category name
                        $icon = '🍴';
                        $cat_name = strtolower($category['name']);
                        if (strpos($cat_name, 'breakfast') !== false) $icon = '🥞';
                        elseif (strpos($cat_name, 'lunch') !== false) $icon = '🥗';
                        elseif (strpos($cat_name, 'dinner') !== false) $icon = '🍝';
                        elseif (strpos($cat_name, 'dessert') !== false) $icon = '🍰';
                        
                        echo '<a href="browse_recipes.php?category=' . $category['category_id'] . '" class="category-item' . $active . '">';
                        echo '<span class="category-icon">' . $icon . '</span>';
                        echo '<span>' . htmlspecialchars($category['name']) . '</span>';
                        echo '</a>';
                    }
                }
                ?>
            </div>
        </div>
            <!-- All Recipes will be listed below here -->

 <!-- Recipes Grid -->
<div class="col-lg-9">
<?php
// Build query with optional category filter
            $where_clause = '';
            if (isset($_GET['category']) && is_numeric($_GET['category'])) {
                $category_id = intval($_GET['category']);
                $where_clause = "WHERE r.category_id = $category_id";
            }

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
$where_clause
GROUP BY r.recipe_id
ORDER BY r.recipe_id DESC";

$recipes = $db_connection->query($query);

if ($recipes && $recipes->num_rows > 0) {
    echo '<div class="recipes-grid">';
    
    while ($recipe = $recipes->fetch_assoc()) {
                    $image_url = $recipe['image_url'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600';
                    ?>
                    <div class="recipe-card">
                        <!-- Recipe Image -->
                        <div class="recipe-image" style="background-image: url('<?php echo htmlspecialchars($image_url); ?>');">
                            <div class="category-badge"><?php echo htmlspecialchars($recipe['category_name']); ?></div>
                        </div>

                        <!-- Recipe Content -->
                        <div class="recipe-content">
                            <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="recipe-author">By <?php echo htmlspecialchars($recipe['creator_name']); ?></p>
                            <p class="recipe-description"><?php echo htmlspecialchars($recipe['description_text']); ?></p>

                            <div class="recipe-meta">
                                <?php if ($recipe['avg_rating']): ?>
                                    <div class="meta-item">
                                        <span class="icon">⭐</span>
                                        <span><?php echo number_format($recipe['avg_rating'], 1); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="meta-item">
                                    <span class="icon">💬</span>
                                    <span><?php echo intval($recipe['comment_count']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="icon">❤️</span>
                                    <span><?php echo intval($recipe['favorite_count']); ?></span>
                                </div>
                            </div>

                            <div class="recipe-times">
                                <span><strong>Prep:</strong> <?php echo htmlspecialchars($recipe['prep_time']); ?></span>
                                <span>|</span>
                                <span><strong>Cook:</strong> <?php echo htmlspecialchars($recipe['cook_time']); ?></span>
                            </div>

                            <a href="view_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn-view">View Recipe</a>
                        </div>
                    </div>
                    <?php
                }
                
                echo '</div>';
            } else {
                ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🍳</div>
                    <h3>No Recipes Found</h3>
                    <p>Be the first to share a recipe in this category!</p>
                    <a href="create_recipe.php" class="btn-create">Create Recipe</a>
                </div>
                <?php
            }
            ?>
        </div>

    </div>
</main>

<div style="margin-bottom: 80px;"></div>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>

