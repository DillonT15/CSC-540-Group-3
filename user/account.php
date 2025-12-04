<?php
//======================================================================
// USER DASHBOARD PAGE
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

  /* Quick Paths */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php'));
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php'));
  include_once (ROOT_SRC_PATH . '/config.php');

  /* Page Name */
  $page_name = "user";
  $user_check = $_SESSION['login_user'];

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

// Get user statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM Recipes WHERE user_id = {$user_info['user_id']}) as recipe_count,
        (SELECT COUNT(*) FROM Favorites WHERE user_id = {$user_info['user_id']}) as favorite_count,
        (SELECT COUNT(*) FROM Comments WHERE user_id = {$user_info['user_id']}) as comment_count
";
$stats_result = $db_connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title>My Profile | Recipe Sharing Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            color: white;
            padding: 60px 0 80px;
            margin-bottom: -40px;
            border-radius: 0 0 40px 40px;
        }

        .profile-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            padding: 50px;
            margin-bottom: 40px;
        }

        /* Avatar Section */
        .profile-avatar-section {
            text-align: center;
            padding-bottom: 40px;
            border-bottom: 2px solid #f7fafc;
            margin-bottom: 40px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 64px;
            color: white;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
            box-shadow: 0 8px 24px rgba(255, 107, 107, 0.3);
        }

        .profile-name {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .profile-role {
            display: inline-block;
            padding: 8px 20px;
            background: #f7fafc;
            color: #4a5568;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: capitalize;
            margin-bottom: 20px;
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f7fafc;
            border-radius: 12px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #FF6B6B;
            font-family: 'Playfair Display', serif;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-family: 'Lora', serif;
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f7fafc;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            font-size: 28px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .info-item {
            padding: 20px;
            background: #f7fafc;
            border-radius: 12px;
            border-left: 4px solid #FF6B6B;
        }

        .info-label {
            font-size: 13px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 16px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn-primary-custom {
            flex: 1;
            padding: 16px 32px;
            background: #FF6B6B;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: #EE5A52;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
        }

        .btn-secondary-custom {
            flex: 1;
            padding: 16px 32px;
            background: white;
            color: #718096;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: #f7fafc;
            color: #4a5568;
            text-decoration: none;
            border-color: #cbd5e0;
        }

        .btn-danger-custom {
            padding: 16px 32px;
            background: white;
            color: #E53E3E;
            border: 2px solid #E53E3E;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-danger-custom:hover {
            background: #E53E3E;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        .quick-link-card {
            padding: 24px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .quick-link-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            text-decoration: none;
        }

        .quick-link-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }

        .quick-link-content h4 {
            font-family: 'Lora', serif;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .quick-link-content p {
            font-size: 14px;
            color: #718096;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-header h1 {
                font-size: 32px;
            }

            .profile-card {
                padding: 30px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .quick-links {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once(ROOT_PATH . '/include/header.php'); ?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="container text-center">
        <h1>My Profile</h1>
        <p style="font-size: 18px; opacity: 0.95;">Manage your account information and preferences</p>
    </div>
</div>

<main class="container">

    <!-- Profile Card -->
    <div class="profile-card">
        
        <!-- Avatar Section -->
        <div class="profile-avatar-section">
            <div class="profile-avatar">
                <?php 
                    $initials = strtoupper(substr($user_info['first_name'], 0, 1) . substr($user_info['last_name'], 0, 1));
                    echo $initials;
                ?>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h2>
            <div class="profile-role"><?php echo htmlspecialchars($user_info['role_type']); ?></div>
            
            <!-- Stats Section -->
            <div class="stats-section">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['recipe_count']; ?></div>
                    <div class="stat-label">Recipes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['favorite_count']; ?></div>
                    <div class="stat-label">Favorites</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['comment_count']; ?></div>
                    <div class="stat-label">Comments</div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="info-section">
            <h3 class="section-title">
                <span class="section-icon">📧</span>
                Contact Information
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">User ID</div>
                    <div class="info-value">#<?php echo htmlspecialchars($user_info['user_id']); ?></div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="info-section">
            <h3 class="section-title">
                <span class="section-icon">📍</span>
                Address Information
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Street Address</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($user_info['street_1']); ?>
                        <?php if (!empty($user_info['street_2'])): ?>
                            <br><?php echo htmlspecialchars($user_info['street_2']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['city']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">State</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['state_code']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Postal Code</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_info['post_code']); ?></div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="edit_account.php" class="btn-primary-custom">✏️ Edit Profile</a>
            <a href="browse_recipes.php" class="btn-secondary-custom">🔍 Browse Recipes</a>
            
        </div>

    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="favorites.php" class="quick-link-card">
            <div class="quick-link-icon">❤️</div>
            <div class="quick-link-content">
                <h4>My Favorites</h4>
                <p>View your saved recipes</p>
            </div>
        </a>
        <a href="browse_recipes.php" class="quick-link-card">
            <div class="quick-link-icon">📖</div>
            <div class="quick-link-content">
                <h4>Browse Recipes</h4>
                <p>Discover new dishes</p>
            </div>
        </a>
        <a href="#" class="quick-link-card">
            <div class="quick-link-icon">✨</div>
            <div class="quick-link-content">
                <h4>Create Recipe</h4>
                <p>Share your creation</p>
            </div>
        </a>
        <a href="edit_account.php" class="quick-link-card">
            <div class="quick-link-icon">⚙️</div>
            <div class="quick-link-content">
                <h4>Settings</h4>
                <p>Manage your account</p>
            </div>
        </a>
    </div>

</main>

<div style="margin-bottom: 80px;"></div>

<?php include_once(ROOT_PATH . '/include/footer.php'); ?>
</body>
</html>