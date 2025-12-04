<?php
//======================================================================
// VIEW RECIPE PAGE
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

  /* Quick Paths */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
  
  /* Page Name */
  $page_name = "user";

?>
<?php
  /* Get database connection */
  include_once (ROOT_SRC_PATH . '/config.php');

  /* Get recipe ID from URL */
  $recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  /* Handle comment submission */
  $comment_message = '';
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_text'])) {
    if (isset($_SESSION['login_user'])) {
      $comment_text = $db_connection->real_escape_string($_POST['comment_text']);
      $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
      
      if ($user_id > 0 && !empty($comment_text)) {
        $insert_comment_query = "INSERT INTO Comments (user_id, recipe_id, text, created_timestamp) 
                                VALUES ($user_id, $recipe_id, '$comment_text', NOW())";
        
        if ($db_connection->query($insert_comment_query)) {
          $comment_message = '<div class="alert alert-success">Comment posted successfully!</div>';
          // Refresh page to show new comment
          header("refresh:1;url=view_recipe.php?id=$recipe_id");
        } else {
          $comment_message = '<div class="alert alert-danger">Error posting comment. Please try again.</div>';
        }
      } else {
        $comment_message = '<div class="alert alert-warning">Please enter a comment.</div>';
      }
    } else {
      $comment_message = '<div class="alert alert-info">Please log in to post a comment.</div>';
    }
  }

  /* Handle rating submission */
$rating_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating'])) {
  if (isset($_SESSION['login_user'])) {
    $rating_value = intval($_POST['rating']);
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    
    if ($user_id > 0 && $rating_value >= 1 && $rating_value <= 5) {
      // Check if user already rated this recipe
      $check_rating = "SELECT rating_id FROM Ratings WHERE user_id = $user_id AND recipe_id = $recipe_id";
      $check_result = $db_connection->query($check_rating);
      
      if ($check_result->num_rows > 0) {
        // Update existing rating
        $update_rating = "UPDATE Ratings SET rating = $rating_value WHERE user_id = $user_id AND recipe_id = $recipe_id";
        if ($db_connection->query($update_rating)) {
          $rating_message = '<div class="alert alert-success">Rating updated successfully!</div>';
          header("refresh:1;url=view_recipe.php?id=$recipe_id");
        }
      } else {
        // Insert new rating
        $insert_rating = "INSERT INTO Ratings (user_id, recipe_id, rating) VALUES ($user_id, $recipe_id, $rating_value)";
        if ($db_connection->query($insert_rating)) {
          $rating_message = '<div class="alert alert-success">Rating submitted successfully!</div>';
          header("refresh:1;url=view_recipe.php?id=$recipe_id");
        }
      }
    } else {
      $rating_message = '<div class="alert alert-warning">Please select a valid rating (1-5 stars).</div>';
    }
  } else {
    $rating_message = '<div class="alert alert-info">Please log in to rate this recipe.</div>';
  }
}

  if ($recipe_id == 0) {
    header("Location: browse_recipes.php");
    exit();
  }

  /* Fetch recipe details */
  $recipe_query = "SELECT 
      r.recipe_id,
      r.title,
      r.description_text,
      r.prep_time,
      r.cook_time,
      r.image_url,
      c.name as category_name,
      CONCAT(con.first_name, ' ', con.last_name) as creator_name,
      AVG(rat.rating) as avg_rating,
      COUNT(DISTINCT rat.rating_id) as rating_count,
      COUNT(DISTINCT com.comment_id) as comment_count,
      COUNT(DISTINCT f.user_id) as favorite_count
    FROM Recipes r
    LEFT JOIN Categories c ON r.category_id = c.category_id
    LEFT JOIN Users u ON r.user_id = u.user_id
    LEFT JOIN Contacts con ON u.contact_id = con.contact_id
    LEFT JOIN Ratings rat ON r.recipe_id = rat.recipe_id
    LEFT JOIN Comments com ON r.recipe_id = com.recipe_id
    LEFT JOIN Favorites f ON r.recipe_id = f.recipe_id
    WHERE r.recipe_id = $recipe_id
    GROUP BY r.recipe_id";

  $recipe_result = $db_connection->query($recipe_query);
  $recipe = $recipe_result->fetch_assoc();

  if (!$recipe) {
    header("Location: browse_recipes.php");
    exit();
  }

  /* Fetch tags */
$tags_query = "SELECT 
    tn.tag_name
  FROM Tags t
  JOIN Tag_Names tn ON t.tag_id = tn.tag_id
  WHERE t.recipe_id = $recipe_id";

$tags_result = $db_connection->query($tags_query);

  /* Fetch ingredients */
  $ingredients_query = "SELECT 
      il.amount,
      il.measuring_unit,
      i.ingredient_name
    FROM Ingredients_Lists il
    JOIN Ingredients i ON il.ingredient_id = i.ingredient_id
    WHERE il.recipe_id = $recipe_id
    ORDER BY il.recipe_id";

  $ingredients_result = $db_connection->query($ingredients_query);

  /* Fetch instructions */
  $instructions_query = "SELECT 
      step_number,
      instruction_text
    FROM Instructions
    WHERE recipe_id = $recipe_id
    ORDER BY step_number ASC";

  $instructions_result = $db_connection->query($instructions_query);

  /* Fetch comments */
  $comments_query = "SELECT 
      com.comment_id,
      com.text,
      com.created_timestamp,
      CONCAT(con.first_name, ' ', con.last_name) as commenter_name
    FROM Comments com
    JOIN Users u ON com.user_id = u.user_id
    JOIN Contacts con ON u.contact_id = con.contact_id
    WHERE com.recipe_id = $recipe_id
    ORDER BY com.created_timestamp DESC";

  $comments_result = $db_connection->query($comments_query);


// favorite and unfavorite code
if (isset($_SESSION['login_user']) && isset($_POST['toggle_favorite'])) { 
    $user_id = intval($_SESSION['user_id']);

    $check_query = "SELECT * FROM Favorites WHERE user_id = $user_id AND recipe_id = $recipe_id";
    $check_result = $db_connection->query($check_query);

    if ($check_result->num_rows > 0) {
        // unfavorite a recipe
        $delete_query = "DELETE FROM Favorites WHERE user_id = $user_id AND recipe_id = $recipe_id";
        $db_connection->query($delete_query);
    } else {
        // favorite a recipe
        $insert_query = "INSERT INTO Favorites (user_id, recipe_id) VALUES ($user_id, $recipe_id)";
        $db_connection->query($insert_query);
    }

    // reload the page so the button updates
    header("Location: view_recipe.php?id=$recipe_id");
    exit();
}

// check if user has already favorited the recipe
$is_favorited = false;
if (isset($_SESSION['login_user'])) {
    $user_id = intval($_SESSION['user_id']);
    $fav_query = "SELECT 1 FROM Favorites WHERE user_id = $user_id AND recipe_id = $recipe_id";
    $fav_result = $db_connection->query($fav_query);
    $is_favorited = ($fav_result->num_rows > 0);
}



?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title><?php echo htmlspecialchars($recipe['title']); ?> | Recipe Sharing Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
        }

        /* Hero Section */
        .recipe-hero {
            position: relative;
            height: 500px;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.4)),
                        url('<?php echo htmlspecialchars($recipe['image_url'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1600'); ?>') center/cover;
            display: flex;
            align-items: flex-end;
            margin-bottom: 60px;
            border-radius: 0 0 40px 40px;
        }

        .recipe-hero-content {
            padding: 60px;
            color: white;
            width: 100%;
        }

        .recipe-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 4px 12px rgba(0, 0, 0, 0.5);
        }

        .recipe-hero-meta {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .meta-item {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
        }

        /* Breadcrumb */
        .breadcrumb-custom {
            background: transparent;
            padding: 20px 0;
            margin-bottom: 0;
        }

        .breadcrumb-custom a {
            color: #FF6B6B;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        /* Recipe Info Bar */
        .recipe-info-bar {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .info-group {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item .icon {
            font-size: 24px;
        }

        .info-item .label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .info-item .value {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }

        /* Tags */
        .recipe-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .recipe-tag {
            background: #f7fafc;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            color: #4a5568;
            font-weight: 500;
            border: 1px solid #e2e8f0;
        }

        /* Main Content Grid */
        .recipe-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            margin-bottom: 60px;
        }

        /* Ingredients Card */
        .ingredients-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .ingredients-card h3 {
            font-family: 'Lora', serif;
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #FF6B6B;
        }

        .ingredient-item {
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 16px;
            color: #4a5568;
            display: flex;
            align-items: center;
        }

        .ingredient-item:last-child {
            border-bottom: none;
        }

        .ingredient-item::before {
            content: "✓";
            color: #48BB78;
            font-weight: bold;
            margin-right: 12px;
            font-size: 18px;
        }

        /* Instructions */
        .instructions-section {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .instructions-section h3 {
            font-family: 'Lora', serif;
            font-size: 32px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 2px solid #FF6B6B;
        }

        .instruction-step {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #e2e8f0;
        }

        .instruction-step:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .step-number {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }

        .step-text {
            font-size: 17px;
            line-height: 1.8;
            color: #4a5568;
            padding-top: 8px;
        }

        /* Rating Section */
        .rating-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }

        .rating-card h4 {
            font-family: 'Lora', serif;
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
        }

        /* Star Rating */
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            font-size: 40px;
            justify-content: flex-end;
            gap: 8px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #FFD700;
            transform: scale(1.1);
        }

        /* Favorite Button */
        .favorite-btn {
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .favorite-btn-active {
            background: #FF6B6B;
            color: white;
        }

        .favorite-btn-active:hover {
            background: #EE5A52;
            transform: translateY(-2px);
        }

        .favorite-btn-inactive {
            background: white;
            color: #FF6B6B;
            border: 2px solid #FF6B6B;
        }

        .favorite-btn-inactive:hover {
            background: #FF6B6B;
            color: white;
            transform: translateY(-2px);
        }

        /* Comments Section */
        .comments-section {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 60px;
        }

        .comments-section h3 {
            font-family: 'Lora', serif;
            font-size: 32px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 32px;
        }

        .comment-item {
            padding: 24px;
            background: #f7fafc;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .comment-author {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .comment-text {
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 8px;
        }

        .comment-date {
            font-size: 14px;
            color: #a0aec0;
        }

        .comment-form {
            background: #f7fafc;
            padding: 32px;
            border-radius: 12px;
            margin-top: 32px;
        }

        .comment-form h4 {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            resize: vertical;
            min-height: 120px;
        }

        .comment-form textarea:focus {
            outline: none;
            border-color: #FF6B6B;
        }

        .submit-btn {
            background: #FF6B6B;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 16px;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #EE5A52;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .recipe-content {
                grid-template-columns: 1fr;
            }

            .ingredients-card {
                position: static;
            }

            .recipe-hero h1 {
                font-size: 36px;
            }

            .recipe-hero-content {
                padding: 30px;
            }
        }
    </style>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<!-- Breadcrumb -->
<div class="container">
    <nav class="breadcrumb-custom">
        <a href="browse_recipes.php">← Back to Recipes</a>
    </nav>
</div>

<!-- Recipe Hero -->
<div class="recipe-hero">
    <div class="recipe-hero-content">
        <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
        <div class="recipe-hero-meta">
            <div class="meta-item">
                By <?php echo htmlspecialchars($recipe['creator_name']); ?>
            </div>
            <div class="meta-item">
                <?php echo htmlspecialchars($recipe['category_name']); ?>
            </div>
        </div>
    </div>
</div>

<main role="main" class="container">

    <!-- Recipe Info Bar -->
    <div class="recipe-info-bar">
        <div class="info-group">
            <div class="info-item">
                <span class="icon">⏱️</span>
                <div>
                    <div class="label">Prep Time</div>
                    <div class="value"><?php echo htmlspecialchars($recipe['prep_time']); ?></div>
                </div>
            </div>
            <div class="info-item">
                <span class="icon">🔥</span>
                <div>
                    <div class="label">Cook Time</div>
                    <div class="value"><?php echo htmlspecialchars($recipe['cook_time']); ?></div>
                </div>
            </div>
            <div class="info-item">
                <span class="icon">⭐</span>
                <div>
                    <div class="label">Rating</div>
                    <div class="value">
                        <?php 
                            if ($recipe['avg_rating']) {
                                echo number_format($recipe['avg_rating'], 1) . " (" . intval($recipe['rating_count']) . ")";
                            } else {
                                echo "No ratings";
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Favorite Button -->
        <?php if (isset($_SESSION['login_user'])) { ?>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="toggle_favorite" value="1">
                <button class="favorite-btn <?php echo $is_favorited ? 'favorite-btn-active' : 'favorite-btn-inactive'; ?>">
                    <span><?php echo $is_favorited ? '❤️' : '🤍'; ?></span>
                    <?php echo $is_favorited ? 'Favorited' : 'Add to Favorites'; ?>
                </button>
            </form>
        <?php } ?>
    </div>

    <!-- Description -->
    <div style="background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); margin-bottom: 40px;">
        <p style="font-size: 18px; line-height: 1.8; color: #4a5568; margin: 0;">
            <?php echo htmlspecialchars($recipe['description_text']); ?>
        </p>
    </div>

    <!-- Tags -->
    <?php if ($tags_result && $tags_result->num_rows > 0) { ?>
        <div class="recipe-tags">
            <?php while ($tag = $tags_result->fetch_assoc()) { ?>
                <span class="recipe-tag"><?php echo htmlspecialchars($tag['tag_name']); ?></span>
            <?php } ?>
        </div>
    <?php } ?>

    <!-- Main Content: Ingredients + Instructions -->
    <div class="recipe-content">
        <!-- Ingredients -->
        <div class="ingredients-card">
            <h3>Ingredients</h3>
            <div>
                <?php 
                    if ($ingredients_result && $ingredients_result->num_rows > 0) {
                        while ($ingredient = $ingredients_result->fetch_assoc()) {
                            echo '<div class="ingredient-item">';
                            echo htmlspecialchars($ingredient['amount']) . ' ' . 
                                 htmlspecialchars($ingredient['measuring_unit']) . ' ' . 
                                 htmlspecialchars($ingredient['ingredient_name']);
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="ingredient-item">No ingredients listed</div>';
                    }
                ?>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instructions-section">
            <h3>Instructions</h3>
            <?php 
                if ($instructions_result && $instructions_result->num_rows > 0) {
                    while ($instruction = $instructions_result->fetch_assoc()) {
                        echo '<div class="instruction-step">';
                        echo '<div class="step-number">' . intval($instruction['step_number']) . '</div>';
                        echo '<div class="step-text">' . htmlspecialchars($instruction['instruction_text']) . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No instructions available</p>';
                }
            ?>
        </div>
    </div>

    <!-- Rating Card -->
    <div class="rating-card">
        <h4>Rate This Recipe</h4>
        <?php echo $rating_message; ?>
        <?php if (isset($_SESSION['login_user'])) { ?>
            <form method="POST" action="">
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1">★</label>
                </div>
                <button type="submit" class="submit-btn" style="margin-left: 20px;">Submit Rating</button>
            </form>
        <?php } else { ?>
            <p style="color: #718096;">Please <a href="login.php" style="color: #FF6B6B; font-weight: 600;">log in</a> to rate this recipe.</p>
        <?php } ?>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
        <h3>Comments (<?php echo intval($recipe['comment_count']); ?>)</h3>

        <?php 
            if ($comments_result && $comments_result->num_rows > 0) {
                while ($comment = $comments_result->fetch_assoc()) {
                    ?>
                    <div class="comment-item">
                        <div class="comment-author"><?php echo htmlspecialchars($comment['commenter_name']); ?></div>
                        <div class="comment-text"><?php echo htmlspecialchars($comment['text']); ?></div>
                        <div class="comment-date"><?php echo date('M d, Y h:i A', strtotime($comment['created_timestamp'])); ?></div>
                    </div>
                    <?php
                }
            } else {
                echo '<p style="color: #718096;">No comments yet. Be the first to comment!</p>';
            }
        ?>

        <!-- Comment Form -->
        <div class="comment-form">
            <h4>Leave a Comment</h4>
            <?php echo $comment_message; ?>
            <?php if (isset($_SESSION['login_user'])) { ?>
                <form method="POST" action="">
                    <textarea name="comment_text" placeholder="Share your thoughts about this recipe..." required></textarea>
                    <button type="submit" class="submit-btn">Post Comment</button>
                </form>
            <?php } else { ?>
                <p style="color: #718096;">Please <a href="login.php" style="color: #FF6B6B; font-weight: 600;">log in</a> to post a comment.</p>
            <?php } ?>
        </div>
    </div>

</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>