<?php
//======================================================================
// EDIT ACCOUNT PAGE
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
include_once (ROOT_PATH . '/php/config.php');

$user_id = intval($_POST['user_id']);

// Fetch the users profile details
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
    WHERE u.user_id = $user_id
");
if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();
$contact_id = $user['contact_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update') {
        // Update user info
        $first     = $db_connection->real_escape_string($_POST['first_name']);
        $last      = $db_connection->real_escape_string($_POST['last_name']);
        $email     = $db_connection->real_escape_string($_POST['email']);
        $phone     = $db_connection->real_escape_string($_POST['phone']);
        $street1   = $db_connection->real_escape_string($_POST['street_1']);
        $street2   = $db_connection->real_escape_string($_POST['street_2']);
        $city      = $db_connection->real_escape_string($_POST['city']);
        $state     = $db_connection->real_escape_string($_POST['state_code']);
        $pc        = $db_connection->real_escape_string($_POST['post_code']);

        $db_connection->query("
            UPDATE Contacts
            SET first_name='$first', last_name='$last', email='$email', phone='$phone',
                street_1='$street1', street_2='$street2', city='$city', state_code='$state',
                post_code='$pc'
            WHERE contact_id=$contact_id
        ");

        header("Location: index.php");
        exit();

    } elseif ($action === 'delete') {
        // Delete user completely (credentials, users, contact)
        $db_connection->query("DELETE FROM Credentials WHERE user_id=$user_id");
        $db_connection->query("DELETE FROM Users WHERE user_id=$user_id");
        $db_connection->query("DELETE FROM Contacts WHERE contact_id=$contact_id");

        header("Location: index.php");
        exit();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
     <div style="padding-bottom: 50px;"> 
</head>
<body class="admin">
<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container">
    <div class="container">
        <h1>Edit User Account</h1>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="action" value="update">

            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>">

            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>">

            <label>Email</label>
            <input type="text" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">

            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">

            <label>Street 1</label>
            <input type="text" name="street_1" class="form-control" value="<?php echo htmlspecialchars($user['street_1']); ?>">

            <label>Street 2</label>
            <input type="text" name="street_2" class="form-control" value="<?php echo htmlspecialchars($user['street_2']); ?>">

            <label>City</label>
            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city']); ?>">

            <label>State</label>
            <input type="text" name="state_code" class="form-control" value="<?php echo htmlspecialchars($user['state_code']); ?>">

            <label>Postal Code</label>
            <input type="text" name="post_code" class="form-control" value="<?php echo htmlspecialchars($user['post_code']); ?>">

            <br>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

        <br>
        <form method="POST" onsubmit="return confirm('Are you sure you want to DELETE this user?');">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger">Delete User</button>
        </form>

        <br>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>
</body>
</html>