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
        <a href="#" class="btn btn-success btn-lg mt-3">Create Recipe</a>






    </div>
            <!-- All Recipes will be listed below here -->







</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>

