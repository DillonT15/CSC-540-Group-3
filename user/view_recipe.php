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

  /* Initialize message variables */
    $comment_message = '';
    $rating_message = '';

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

  if ($recipe_id == 0) {
    header("Location: browse_recipes.php");
    exit();
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
      
      if ($check_result && $check_result->num_rows > 0) {
        // Update existing rating
        $update_rating = "UPDATE Ratings SET rating = $rating_value WHERE user_id = $user_id AND recipe_id = $recipe_id";
        if ($db_connection->query($update_rating)) {
          $rating_message = '<div class="alert alert-success">Rating updated successfully!</div>';
          // Remove the header redirect and let the page reload naturally
          echo '<meta http-equiv="refresh" content="2;url=view_recipe.php?id='.$recipe_id.'">';
        } else {
          $rating_message = '<div class="alert alert-danger">Error updating rating: ' . $db_connection->error . '</div>';
        }
      } else {
        // Insert new rating
        $insert_rating = "INSERT INTO Ratings (user_id, recipe_id, rating) VALUES ($user_id, $recipe_id, $rating_value)";
        if ($db_connection->query($insert_rating)) {
          $rating_message = '<div class="alert alert-success">Rating submitted successfully!</div>';
          // Remove the header redirect and let the page reload naturally
          echo '<meta http-equiv="refresh" content="2;url=view_recipe.php?id='.$recipe_id.'">';
        } else {
          $rating_message = '<div class="alert alert-danger">Error submitting rating: ' . $db_connection->error . '</div>';
        }
      }
    } else {
      $rating_message = '<div class="alert alert-warning">Please select a valid rating (1-5 stars).</div>';
    }
  } else {
    $rating_message = '<div class="alert alert-info">Please log in to rate this recipe.</div>';
  }
}
/* Redirect after successful submission */
if (!empty($rating_message) && strpos($rating_message, 'success') !== false) {
    header("Location: view_recipe.php?id=$recipe_id");
    exit();
}
if (!empty($comment_message) && strpos($comment_message, 'success') !== false) {
    header("Location: view_recipe.php?id=$recipe_id");
    exit();
}
    

  /* Fetch recipe details */
  $recipe_query = "SELECT 
      r.recipe_id,
      r.title,
      r.description_text,
      r.prep_time,
      r.cook_time,
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
?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title><?php echo htmlspecialchars($recipe['title']); ?> | Recipe Sharing Platform</title>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container mt-5">

    <!-- Back Button -->
    <div class="mb-3">
        <a href="browse_recipes.php" class="btn btn-secondary">← Back to Recipes</a>
    </div>

    <!-- Recipe Header -->
    <div class="row mb-5">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($recipe['description_text']); ?></p>
            
            <div class="mb-4">
                <span class="badge badge-primary badge-lg" style="color: black;"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                <span class="badge badge-info" style="color: black;">By <?php echo htmlspecialchars($recipe['creator_name']); ?></span>
                <!-- Display Tags -->
                <?php 
                    if ($tags_result && $tags_result->num_rows > 0) {
                        while ($tag = $tags_result->fetch_assoc()) {
                            echo '<span class="badge badge-secondary" style="color: black; background-color: #e0e0e0;">' . htmlspecialchars($tag['tag_name']) . '</span> ';
                        }
                    }
    ?>
            </div>

            <div class="mb-4">
                <div>
                    <strong>⭐ Rating:</strong> 
                    <?php 
                        if ($recipe['avg_rating']) {
                            echo number_format($recipe['avg_rating'], 1) . " / 5 (" . intval($recipe['rating_count']) . " ratings)";
                        } else {
                            echo "No ratings yet";
                        }
                    ?>
                </div>
                <div>
                    <strong>💬 Comments:</strong> <?php echo intval($recipe['comment_count']); ?>
                </div>
                <div>
                    <strong>❤️ Favorites:</strong> <?php echo intval($recipe['favorite_count']); ?>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>⏱️ Prep Time:</strong> <?php echo htmlspecialchars($recipe['prep_time']); ?> | 
                <strong>🔥 Cook Time:</strong> <?php echo htmlspecialchars($recipe['cook_time']); ?>
            </div>
        </div>
    </div>

    <!-- Rating Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Rate This Recipe</h5>
                </div>
                <div class="card-body">
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
                            <div class="mt-3">
                                <button type="submit" class="btn btn-warning">Submit Rating</button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-info">Please <a href="login.php">log in</a> to rate this recipe.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Ingredients Column -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ingredients</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php 
                            if ($ingredients_result && $ingredients_result->num_rows > 0) {
                                while ($ingredient = $ingredients_result->fetch_assoc()) {
                                    echo '<li class="list-group-item">';
                                    echo htmlspecialchars($ingredient['amount']) . ' ' . htmlspecialchars($ingredient['measuring_unit']) . ' ' . htmlspecialchars($ingredient['ingredient_name']);
                                    echo '</li>';
                                }
                            } else {
                                echo '<li class="list-group-item">No ingredients listed</li>';
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Instructions Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Instructions</h5>
                </div>
                <div class="card-body">
                    <?php 
                        if ($instructions_result && $instructions_result->num_rows > 0) {
                            while ($instruction = $instructions_result->fetch_assoc()) {
                                echo '<div class="mb-3">';
                                echo '<h6><strong>Step ' . intval($instruction['step_number']) . '</strong></h6>';
                                echo '<p>' . htmlspecialchars($instruction['instruction_text']) . '</p>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No instructions available</p>';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <!-- Comments Section -->
    <div class="row">
        <div class="col-md-8">
            <h3>Comments (<span id="comment-count"><?php echo intval($recipe['comment_count']); ?></span>)</h3>

            <?php 
                if ($comments_result && $comments_result->num_rows > 0) {
                    while ($comment = $comments_result->fetch_assoc()) {
                        ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($comment['commenter_name']); ?></h6>
                                <p class="card-text"><?php echo htmlspecialchars($comment['text']); ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($comment['created_timestamp'])); ?></small>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="alert alert-info">No comments yet. Be the first to comment!</div>';
                }
            ?>

            <!-- Add Comment Form -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Add a Comment</h5>
                </div>
                <div class="card-body">
                    <?php echo $comment_message; ?>
                    <?php if (isset($_SESSION['login_user'])) { ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <textarea class="form-control" name="comment_text" rows="3" placeholder="Share your thoughts about this recipe..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-info">Please <a href="login.php">log in</a> to post a comment.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>