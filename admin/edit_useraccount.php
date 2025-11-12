<?php
//======================================================================
// EDIT ACCOUNT PAGE
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
include_once (ROOT_PATH . '/php/config.php');

$user_check = $_SESSION['login_user']; // username

// Fetch current profile details
$result = $db_connection->query("
    SELECT
        u.user_id,
        c.contact_id,
        c.first_name,
        c.last_name,
        c.email,
        c.phone,
        c.street_1,
        c.street_2,
        c.city,
        c.state_code,
        c.post_code
    FROM Users u
    INNER JOIN Contacts c ON u.contact_id = c.contact_id
    INNER JOIN Credentials cr ON u.user_id = cr.user_id
    WHERE cr.username = '$user_check'
");

$user = $result->fetch_assoc();
$contact_id = $user['contact_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first     = $_POST['first_name'];
    $last      = $_POST['last_name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $street1   = $_POST['street_1'];
    $street2   = $_POST['street_2'];
    $city      = $_POST['city'];
    $state     = $_POST['state_code'];
    $pc        = $_POST['post_code'];

    $db_connection->query("
        UPDATE Contacts
        SET first_name='$first', last_name='$last', email='$email', phone='$phone',
            street_1='$street1', street_2='$street2', city='$city', state_code='$state',
            post_code='$pc'
        WHERE contact_id=$contact_id
    ");

    header("Location: account.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <div style="padding-bottom: 50px;"> <!-- Temporary remove once css stylesheet is incorporated!-->
</head>
<body class="user">
<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container">
    <div class="container">
        <h1>Edit Account Information</h1>
        <form method="POST">

            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo $user['first_name']; ?>">

            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo $user['last_name']; ?>">

            <label>Email</label>
            <input type="text" name="email" class="form-control" value="<?php echo $user['email']; ?>">

            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>">

            <label>Street 1</label>
            <input type="text" name="street_1" class="form-control" value="<?php echo $user['street_1']; ?>">

            <label>Street 2</label>
            <input type="text" name="street_2" class="form-control" value="<?php echo $user['street_2']; ?>">

            <label>City</label>
            <input type="text" name="city" class="form-control" value="<?php echo $user['city']; ?>">

            <label>State</label>
            <input type="text" name="state_code" class="form-control" value="<?php echo $user['state_code']; ?>">

            <label>Postal Code</label>
            <input type="text" name="post_code" class="form-control" value="<?php echo $user['post_code']; ?>">

            <br>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="account.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>
</body>
</html>
