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
  include_once (ROOT_SRC_PATH .'/check_admin.php');

  /* Page Name */
  $page_name = "admin";


// Fetch user info from database
$query = "
    SELECT 
        u.user_id,
        c.first_name,
        c.last_name,
        c.email,
        c.phone,
        c.street_1,
        c.street_2,
        c.city,
        c.state_code,
        c.post_code,
        cr.username,
        r.role_type
    FROM Users u
    INNER JOIN Contacts c ON u.contact_id = c.contact_id
    INNER JOIN Credentials cr ON u.user_id = cr.user_id
    INNER JOIN Roles r ON u.role_id = r.role_id
    WHERE cr.username = '$user_check'
";

$result = $db_connection->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $user_info = $row;
} else {
    die("User not found.");
}

$db_connection->close();
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
  include_once (ROOT_SRC_PATH .'/check_admin`.php');

  $user_check = $_SESSION['login_user'];
  // Check user and get roll session from database

  /* Page Name */
  $page_name = "admin";

?>
<!doctype html>
<html lang="en">
  <head>
  <?php include_once (ROOT_PATH . '/include/head.php'); ?>
  
  </head>
 <body class="<?php echo $page_name; ?>">

<?php include_once(ROOT_PATH . '/include/header.php'); ?>

<main class="container mt-5">
    <h1>My Account</h1>
    <p class="lead">View and update your personal information below.</p>
    <a href="edit_account.php" class="btn btn-primary mt-3">Edit Account</a>
    <table class="table table-bordered">
      <tr><th>First Name</th><td><?php echo $user_info['first_name']; ?></td></tr>
      <tr><th>Last Name</th><td><?php echo $user_info['last_name']; ?></td></tr>
      <tr><th>Email</th><td><?php echo $user_info['email']; ?></td></tr>
      <tr><th>Phone</th><td><?php echo $user_info['phone']; ?></td></tr>
      <tr><th>Street 1</th><td><?php echo $user_info['street_1']; ?></td></tr>
      <tr><th>Street 2</th><td><?php echo $user_info['street_2']; ?></td></tr>
      <tr><th>City</th><td><?php echo $user_info['city']; ?></td></tr>
      <tr><th>State</th><td><?php echo $user_info['state_code']; ?></td></tr>
      <tr><th>Postal Code</th><td><?php echo $user_info['post_code']; ?></td></tr>
      <tr><th>Username</th><td><?php echo $user_info['username']; ?></td></tr>
      <tr><th>Role</th><td><?php echo $user_info['role_type']; ?></td></tr>
    </table>

    
</main>

<?php include_once(ROOT_PATH . '/include/footer.php'); ?>
</body>
</html>