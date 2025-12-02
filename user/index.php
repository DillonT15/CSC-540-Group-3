<?php
//======================================================================
// User Home Page
//======================================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

include_once(realpath(dirname(__FILE__, 2).'/php/session.php'));
include_once(realpath(dirname(__FILE__, 2).'/php/path.php'));
include_once(ROOT_SRC_PATH.'/check_user.php');

$page_name = "user";
$user_check = $_SESSION['login_user'];

?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once (ROOT_PATH . '/include/head.php'); ?>
    <title>Welcome | Recipe Sharing Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        /* Hero Section with Background Image */
        .hero-banner {
            position: relative;
            height: 600px;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.4)),
                        url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 80px;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-banner h1 {
            font-family: 'Playfair Display', serif;
            font-size: 72px;
            font-weight: 800;
            margin-bottom: 24px;
            text-shadow: 2px 4px 12px rgba(0, 0, 0, 0.4);
            line-height: 1.2;
        }

        .hero-banner p {
            font-size: 22px;
            margin-bottom: 40px;
            font-weight: 400;
            text-shadow: 1px 2px 6px rgba(0, 0, 0, 0.3);
            line-height: 1.6;
        }

        .hero-buttons .btn {
            margin: 10px;
            padding: 16px 48px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-hero-primary {
            background: #FF6B6B;
            border: none;
            color: white;
            box-shadow: 0 8px 24px rgba(255, 107, 107, 0.4);
        }

        .btn-hero-primary:hover {
            background: #EE5A52;
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(255, 107, 107, 0.5);
            color: white;
        }

        .btn-hero-secondary {
            background: white;
            border: none;
            color: #FF6B6B;
            box-shadow: 0 8px 24px rgba(255, 255, 255, 0.3);
        }

        .btn-hero-secondary:hover {
            background: #f8f8f8;
            transform: translateY(-3px);
            color: #FF6B6B;
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 20px;
            color: #718096;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Feature Cards with Images */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            margin-bottom: 100px;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .feature-image {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
        }

        .feature-discover {
            background-image: url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600');
        }

        .feature-share {
            background-image: url('https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600');
        }

        .feature-favorite {
            background-image: url('https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=600');
        }

        .feature-comment {
            background-image: url('https://images.unsplash.com/photo-1543362906-acfc16c67564?w=600');
        }

        .feature-content {
            padding: 32px 28px;
            text-align: center;
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }

        .feature-content h4 {
            font-family: 'Lora', serif;
            font-size: 26px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
        }

        .feature-content p {
            font-size: 16px;
            color: #718096;
            line-height: 1.7;
        }

        /* Call to Action Section */
        .cta-section {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            padding: 80px 40px;
            border-radius: 30px;
            text-align: center;
            color: white;
            margin: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            right: -150px;
        }

        .cta-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
        }

        .cta-section p {
            font-size: 20px;
            margin-bottom: 32px;
            opacity: 0.95;
            position: relative;
        }

        .cta-section .btn {
            background: white;
            color: #FF6B6B;
            padding: 16px 48px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .cta-section .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            color: #FF6B6B;
        }

        /* Stats Section */
        .stats-section {
            background: #f7fafc;
            padding: 60px 40px;
            border-radius: 30px;
            margin: 80px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 700;
            color: #FF6B6B;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 18px;
            color: #4a5568;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-banner {
                height: 500px;
            }

            .hero-banner h1 {
                font-size: 42px;
            }

            .hero-banner p {
                font-size: 18px;
            }

            .section-header h2 {
                font-size: 36px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .cta-section h2 {
                font-size: 36px;
            }
        }
    </style>
</head>
<body class="<?php echo $page_name; ?>">

<?php include_once (ROOT_PATH . '/include/header.php'); ?>

<!-- Hero Banner -->
<div class="hero-banner">
    <div class="hero-content">
        <h1>Recipe Sharing Platform</h1>
        <p>A community-driven place to explore delicious recipes, save your favorites, and share your culinary creations with food enthusiasts worldwide.</p>
        <div class="hero-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="../login.php" class="btn btn-hero-primary">Get Started</a>
                <a href="../register.php" class="btn btn-hero-secondary">Sign Up Free</a>
            <?php else: ?>
                <a href="browse_recipes.php" class="btn btn-hero-primary">Browse Recipes</a>
                <a href="create_recipe.php" class="btn btn-hero-secondary">Create Recipe</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<main role="main" class="container">
<!-- Section Header -->
    <div class="section-header">
        <h2>What You Can Do Here</h2>
        <p>Whether you're a seasoned chef or just starting out, you'll find everything you need to create, discover, and share amazing recipes.</p>
    </div>

    <!-- Features Grid with Images -->
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-image feature-discover"></div>
            <div class="feature-content">
                <span class="feature-icon">🔍</span>
                <h4>Discover</h4>
                <p>Browse thousands of community-posted recipes and find culinary inspiration from around the world.</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-image feature-share"></div>
            <div class="feature-content">
                <span class="feature-icon">📤</span>
                <h4>Share</h4>
                <p>Upload your own recipes and showcase your cooking skills with our vibrant community.</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-image feature-favorite"></div>
            <div class="feature-content">
                <span class="feature-icon">❤️</span>
                <h4>Favorite</h4>
                <p>Save recipes you love and build your personal collection for quick access anytime.</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-image feature-comment"></div>
            <div class="feature-content">
                <span class="feature-icon">💬</span>
                <h4>Comment</h4>
                <p>Join discussions, share cooking tips, and connect with fellow food enthusiasts.</p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">1,000+</div>
                <div class="stat-label">Recipes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Members</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5,000+</div>
                <div class="stat-label">Reviews</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">Countries</div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h2>Ready to Get Cooking?</h2>
        <p>Join our community today and start discovering amazing recipes</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="../register.php" class="btn">Join Now</a>
        <?php else: ?>
            <a href="browse_recipes.php" class="btn">Explore Recipes</a>
        <?php endif; ?>
    </div>

</main>

<?php include_once (ROOT_PATH . '/include/footer.php'); ?>

</body>
</html>