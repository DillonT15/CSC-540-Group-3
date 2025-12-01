<?php
//======================================================================
// ADMIN CREATE RECIPE PAGE
//======================================================================
//Identical to regular create recipe for user
session_start();
include_once(__DIR__ . '/../php/config.php');

error_reporting(E_ALL);

if (!isset($_SESSION['recipe_data'])) {
    $_SESSION['recipe_data'] = [
        'title'        => '',
        'categories'   => '',
        'tags'         => '',
        'description'  => '',
        'prep_time'    => '',
        'cook_time'    => '',
        'instructions' => [],
        'ingredients'  => []
    ];
}

if (isset($_POST['add_ingredient'])) {
    $name   = trim($_POST['ingredient_name']);
    $amount = trim($_POST['ingredient_amount']);

    if ($name !== "" && $amount !== "") {
        $_SESSION['recipe_data']['ingredients'][] = [
            'name'   => $name,
            'amount' => $amount
        ];
    }
}
/* Handle ingredient delete */
if (isset($_POST['delete_ingredient'])) {
    $index = (int)$_POST['delete_ingredient'];
    if (isset($_SESSION['recipe_data']['ingredients'][$index])) {
        array_splice($_SESSION['recipe_data']['ingredients'], $index, 1);
    }
}
/* Handle instruction adding */
if (isset($_POST['add_instruction'])) {
    $text = trim($_POST['instruction_text']);
    if ($text !== "") {
        $_SESSION['recipe_data']['instructions'][] = $text;
    }
}

/* Handle instruction delete */
if (isset($_POST['delete_instruction'])) {
    $index = (int)$_POST['delete_instruction'];
    if (isset($_SESSION['recipe_data']['instructions'][$index])) {
        array_splice($_SESSION['recipe_data']['instructions'], $index, 1);
    }
}


/* Handle field updates */
if (isset($_POST['final_submit'])) {

    $_SESSION['recipe_data']['title']       = $_POST['title'] ?? '';
    $_SESSION['recipe_data']['categories']  = $_POST['categories'] ?? '';
    $_SESSION['recipe_data']['tags']        = $_POST['tags'] ?? '';
    $_SESSION['recipe_data']['description'] = $_POST['description'] ?? '';
    $_SESSION['recipe_data']['prep_time']   = $_POST['prep_time'] ?? '';
    $_SESSION['recipe_data']['cook_time']   = $_POST['cook_time'] ?? '';

    $data = $_SESSION['recipe_data'];

    $cat_id = null;
    $cat_name = $data['categories'] ?? '';
    /* This part ensures that the categories are not case sensitive and doesnt result in the null error anymore */
    if ($cat_name) {
        $stmt_cat = $db_connection->prepare(
        "SELECT category_id FROM Categories WHERE LOWER(name)=LOWER(?)");
        $stmt_cat->bind_param("s", $cat_name);
        $stmt_cat->execute();
        $stmt_cat->bind_result($cat_id);
        $stmt_cat->fetch();
        $stmt_cat->close();
        if (!$cat_id) {
            $ins_cat = $db_connection->prepare("INSERT INTO Categories (name) VALUES (?)");
            $ins_cat->bind_param("s", $cat_name);
            $ins_cat->execute();
            $cat_id = $ins_cat->insert_id;
            $ins_cat->close();
        }
    }
    // Not using id 2 as example anymore, rather pulls current users id
    $user_id = $_SESSION['user_id'];
    $cat_id = $cat_id ?? 1;

    $stmt_recipe = $db_connection->prepare(
        "INSERT INTO Recipes (title, category_id, description_text, prep_time, cook_time, user_id) VALUES (?,?,?,?,?,?)"
    );
    $stmt_recipe->bind_param(
        "sisssi",
        $data['title'],
        $cat_id,
        $data['description'],
        $data['prep_time'],
        $data['cook_time'],
        $user_id
    );
    $stmt_recipe->execute();
    $recipe_id = $stmt_recipe->insert_id;
    $stmt_recipe->close();

    foreach ($data['ingredients'] as $ing) {
        $ing_stmt = $db_connection->prepare("SELECT ingredient_id FROM Ingredients WHERE ingredient_name=?");
        $ing_stmt->bind_param("s", $ing['name']);
        $ing_stmt->execute();
        $ing_stmt->bind_result($ingredient_id);
        $exists = $ing_stmt->fetch();
        $ing_stmt->close();

        if (!$exists) {
            $ins_ing = $db_connection->prepare("INSERT INTO Ingredients (ingredient_name) VALUES (?)");
            $ins_ing->bind_param("s", $ing['name']);
            $ins_ing->execute();
            $ingredient_id = $ins_ing->insert_id;
            $ins_ing->close();
        }

        $list_stmt = $db_connection->prepare(
            "INSERT INTO Ingredients_Lists (recipe_id, ingredient_id, amount, measuring_unit) VALUES (?, ?, ?, ?)"
        );
        $measuring_unit = "";
        $list_stmt->bind_param("iiss", $recipe_id, $ingredient_id, $ing['amount'], $measuring_unit);
        $list_stmt->execute();
        $list_stmt->close();
    }
    // Insert Instructions
    $step = 1;
    foreach ($data['instructions'] as $inst) {
        $ins_stmt = $db_connection->prepare(
            "INSERT INTO Instructions (recipe_id, step_number, instruction_text) VALUES (?,?,?)"
        );
        $ins_stmt->bind_param("iis", $recipe_id, $step, $inst);
        $ins_stmt->execute();
        $ins_stmt->close();
        $step++;
    }

    unset($_SESSION['recipe_data']);
    header("Location: manage_recipes.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php include_once(ROOT_PATH . '/include/head.php'); ?>
    <title>Create Recipe | Recipe Sharing Platform</title>
</head>
<body>
<?php include_once(ROOT_PATH . '/include/header.php'); ?>

<main role="main" class="container mt-5">

<div class="text-center mb-5">
    <h1 class="display-4">Create Recipe</h1>
    <p class="lead">Enter recipe details, ingredients, and instructions below.</p>
</div>

<form method="POST">

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Recipe Details</h5>
    </div>
    <div class="card-body">

        <div class="form-group mb-3">
            <label><strong>Title</strong></label>
            <input type="text" name="title" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['recipe_data']['title'] ?? '') ?>">
        </div>

        <div class="form-group mb-3">
            <label><strong>Categories</strong></label>
            <input type="text" name="categories" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['recipe_data']['categories'] ?? '') ?>">
        </div>

        <div class="form-group mb-3">
            <label><strong>Tags</strong></label>
            <input type="text" name="tags" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['recipe_data']['tags'] ?? '') ?>">
        </div>

        <div class="form-group mb-3">
            <label><strong>Description</strong></label>
            <textarea name="description" class="form-control" rows="3"><?=
                htmlspecialchars($_SESSION['recipe_data']['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label><strong>Prep Time</strong></label>
            <input type="text" name="prep_time" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['recipe_data']['prep_time'] ?? '') ?>">
        </div>

        <div class="form-group mb-3">
            <label><strong>Cook Time</strong></label>
            <input type="text" name="cook_time" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['recipe_data']['cook_time'] ?? '') ?>">
        </div>

    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Ingredients</h5>
    </div>
    <div class="card-body">

        <div class="row g-3">
            <div class="col-md-6">
                <label><strong>Ingredient</strong></label>
                <input type="text" name="ingredient_name" class="form-control">
            </div>
            <div class="col-md-4">
                <label><strong>Amount</strong></label>
                <input type="text" name="ingredient_amount" class="form-control">
            </div>
            <div class="col-md-2 d-grid">
                <label>&nbsp;</label>
                <button name="add_ingredient" class="btn btn-success">Add</button>
            </div>
        </div>

        <hr>
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Ingredient</th><th>Amount</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['recipe_data']['ingredients'] as $i => $ing): ?>
                <tr>
                    <td><?= htmlspecialchars($ing['name']) ?></td>
                    <td><?= htmlspecialchars($ing['amount']) ?></td>
                    <td>
                        <button name="delete_ingredient" value="<?= $i ?>" class="btn btn-danger btn-sm">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Instructions</h5>
    </div>
    <div class="card-body">

        <div class="row g-3">
            <div class="col-md-10">
                <label><strong>Step</strong></label>
                <input type="text" name="instruction_text" class="form-control">
            </div>
            <div class="col-md-2 d-grid">
                <label>&nbsp;</label>
                <button name="add_instruction" class="btn btn-warning">Add Step</button>
            </div>
        </div>

        <hr>
        <ol>
            <?php foreach ($_SESSION['recipe_data']['instructions'] as $i => $inst): ?>
                <li>
                    <?= htmlspecialchars($inst) ?>
                    <button name="delete_instruction" value="<?= $i ?>" class="btn btn-danger btn-sm">Delete</button>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>
 <!-- I fixed the publish recipe to ensure everything actually gets pushed in form-->
<div class="text-center mb-5">
    <button name="final_submit" class="btn btn-lg btn-primary">Publish Recipe</button>
</div>

</form>

</main>

<?php include_once(ROOT_PATH . '/include/footer.php'); ?>
</body>
</html>
