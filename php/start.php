<?php
//======================================================================
// CREATE THE DATABASE
//======================================================================


error_reporting(-1);
ini_set('display_errors', 'On');

/* the first connection to the database */
DEFINE('DB_HOST', "localhost");
DEFINE('DB_USER', "root");
DEFINE('DB_PASSWORD', ""); //Note: this should be your root password


try {
  $db_connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD)
    OR die("Connection failed: " . $db_connection->connect_error);
} catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), nl2br("\r\n");
}

/* Check if database is there or will create it */
$create_stmt = "CREATE DATABASE IF NOT EXISTS login_db";

/* Check if database drop was sucessful */
if(mysqli_query($db_connection, $create_stmt)) {
	echo nl2br("Database was successfully created.\r\n");
} else {
	echo "Error dropping database: " . mysqli_error() . nl2br("\r\n");
}
$prep_stmt = $db_connection -> prepare($create_stmt);
$prep_stmt->execute();
$prep_stmt->close();

/* Change to the created database */
$db_connection->select_db("login_db");

/* Drop all tables for clean install */
$db_connection->query('SET foreign_key_checks = 0');
if ($result = $db_connection->query("SHOW TABLES")) {
  while($row = $result->fetch_array(MYSQLI_NUM)) {
    $db_connection->query('DROP TABLE IF EXISTS '.$row[0]);
	}
	echo "Tables removed successfully." . nl2br("\r\n");
} else {
	echo "No tables were removed." . nl2br("\r\n");
}
$db_connection->query('SET foreign_key_checks = 1');


/* Salt used for seasoning */
$salt = 'authentication';

//-----------------------------------------------------
// Create Database Tables
//-----------------------------------------------------
echo "Table creation started." . nl2br("\r\n");

/* Below Are Tables That Have Only Primary Keys */
/* ------------------------------------------ */



/* Role */
$create_roles = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Roles(
        role_id int NOT NULL AUTO_INCREMENT,
        role_type varchar(255) NOT NULL,
        PRIMARY KEY(role_id));");
$create_roles->execute();
$create_roles->close();

/* Contacts */
$create_contacts = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Contacts(
        contact_id int NOT NULL AUTO_INCREMENT,
        last_name varchar(255) NOT NULL,
        first_name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(255) NOT NULL,
        street_1 varchar(255),
        street_2 varchar(255),
        city varchar(255),
        state_code varchar(255),
        post_code int(5),
        updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY(contact_id));");
$create_contacts->execute();
$create_contacts->close();

/* Below Are Tables That Have Foreign Keys */
/* ------------------------------------------ */

/* Users */
$create_users = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Users(
        user_id int NOT NULL AUTO_INCREMENT,
        role_id int NOT NULL,
        contact_id int NOT NULL,
	creation_date timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(user_id),
        FOREIGN KEY(role_id) REFERENCES Roles(role_id),
        FOREIGN KEY(contact_id) REFERENCES Contacts(contact_id));");
$create_users->execute();
$create_users->close();

/* Credentials */
$create_credentials = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Credentials(
	user_id int NOT NULL,
        username varchar(255) NOT NULL,
        password_salted varchar(255) NOT NULL,
	PRIMARY KEY(username),
        FOREIGN KEY(user_id) REFERENCES Users(user_id));");
$create_credentials->execute();
$create_credentials->close();

# everything above is mostly unchanged, here's our stuff:

/* USER SETTINGS */
$create_user_settings = $db_connection->prepare(
    "CREATE OR REPLACE TABLE User_Settings(
        user_id INT NOT NULL,
        background_color VARCHAR(255),
        font VARCHAR(255),
        text_size VARCHAR(255),
        PRIMARY KEY(user_id),
        FOREIGN KEY(user_id) REFERENCES Users(user_id)
    );");
$create_user_settings->execute();
$create_user_settings->close();

#next set of tables, for food half of database:

/* CATEGORIES */
$create_categories = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Categories(
        category_id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255),
        PRIMARY KEY(category_id)
    );");
$create_categories->execute();
$create_categories->close();

/* RECIPES */
$create_recipes = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Recipes(
        recipe_id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(255),
        category_id INT,
        description_text VARCHAR(65535),
        prep_time VARCHAR(255),
        cook_time VARCHAR(255),
        image_url VARCHAR(255),
        user_id INT NOT NULL,
        PRIMARY KEY(recipe_id),
        FOREIGN KEY(category_id) REFERENCES Categories(category_id),
        FOREIGN KEY(user_id) REFERENCES Users(user_id)
    );");
$create_recipes->execute();
$create_recipes->close();


/* TAG NAMES */
$create_tag_names = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Tag_Names(
        tag_id INT NOT NULL AUTO_INCREMENT,
        tag_name VARCHAR(255) NOT NULL,
        PRIMARY KEY(tag_id),
        UNIQUE(tag_name)
    );");
$create_tag_names->execute();
$create_tag_names->close();
/* TAGS */
$create_tags = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Tags(
        tag_id INT NOT NULL,
        recipe_id INT NOT NULL,
        PRIMARY KEY(tag_id, recipe_id),
        FOREIGN KEY(tag_id) REFERENCES Tag_Names(tag_id),
        FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id)
    );");
$create_tags->execute();
$create_tags->close();


/* INGREDIENTS */
$create_ingredients = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Ingredients(
        ingredient_id INT NOT NULL AUTO_INCREMENT,
        ingredient_name VARCHAR(255),
        PRIMARY KEY(ingredient_id)
    );");
$create_ingredients->execute();
$create_ingredients->close();
/* INGREDIENTS LISTS */
$create_ingredients_lists = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Ingredients_Lists(
        recipe_id INT NOT NULL,
        ingredient_id INT NOT NULL,
        amount VARCHAR(255),
        measuring_unit VARCHAR(255),
        PRIMARY KEY(recipe_id, ingredient_id),
        FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id),
        FOREIGN KEY(ingredient_id) REFERENCES Ingredients(ingredient_id)
    );");
$create_ingredients_lists->execute();
$create_ingredients_lists->close();


/* INSTRUCTIONS */
$create_instructions = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Instructions(
        recipe_id INT NOT NULL,
        step_number INT NOT NULL,
        instruction_text VARCHAR(2000),
        PRIMARY KEY(recipe_id, step_number),
        FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id)
    );");
$create_instructions->execute();
$create_instructions->close();

# onto social aspect of database:
# Could've been done after recipe tables but this keeps it cleanly separated 

/* COMMENTS */
$create_comments = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Comments(
        comment_id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        recipe_id INT NOT NULL,
        text VARCHAR(65535),
        created_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(comment_id),
        FOREIGN KEY(user_id) REFERENCES Users(user_id),
        FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id)
    );");
$create_comments->execute();
$create_comments->close();

/* FAVORITES */
$create_favorites = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Favorites(
        user_id INT NOT NULL,
        recipe_id INT NOT NULL,
        PRIMARY KEY(user_id, recipe_id),
        FOREIGN KEY(user_id) REFERENCES Users(user_id),
        FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id)
    );");
$create_favorites->execute();
$create_favorites->close();

/* RATINGS */
$create_ratings = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Ratings(
        rating_id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        recipe_id INT NOT NULL,
        rating INT,
        PRIMARY KEY(rating_id),
        UNIQUE(user_id, recipe_id), /* Unique rating so user doesnt input multiple ratings */
        FOREIGN KEY(user_id) REFERENCES Users(user_id),
        FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id)
    );");
$create_ratings->execute();
$create_ratings->close();


/* FRIENDS */ #previously 'Contacts', renamed to avoid confusion with already-there Contacts table
$create_friends = $db_connection->prepare(
    "CREATE OR REPLACE TABLE Friends(
        user_id INT NOT NULL,
        friend_user_id INT NOT NULL,
        created_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(user_id, friend_user_id),
        FOREIGN KEY(user_id) REFERENCES Users(user_id),
        FOREIGN KEY(friend_user_id) REFERENCES Users(user_id)
    );");
$create_friends->execute();
$create_friends->close();


/* Status Display */
echo nl2br("The database tables were successfully created.\r\n");


//-----------------------------------------------------
// Populate Tables of Database
//-----------------------------------------------------

/* Roles */
$insert_role = $db_connection->prepare(
	"INSERT INTO Roles
    	(role_id, role_type) VALUES(?,?);");
$insert_role->bind_param("is", $role_id, $role_title);

$role_id = 1;
$role_title = "administrator";
$insert_role->execute();

$role_id = 2;
$role_title = "user";
$insert_role->execute();

$role_id = 3;
$role_title = "guest";
$insert_role->execute();

$insert_role->close();

/* Categories */
$insert_category = $db_connection->prepare(
    "INSERT INTO Categories
        (category_id, name) VALUES(?,?);");
$insert_category->bind_param("is", $category_id, $category_name);

$category_id = 1;
$category_name = "Breakfast";
$insert_category->execute();

$category_id = 2;
$category_name = "Lunch";
$insert_category->execute();

$category_id = 3;
$category_name = "Dinner";
$insert_category->execute();

$category_id = 4;
$category_name = "Dessert";
$insert_category->execute();

$insert_category->close();

/* Contacts */
$insert_contacts = $db_connection->prepare(
	"INSERT INTO Contacts
		(contact_id, last_name, first_name, email, phone, street_1, street_2, city, state_code, post_code, updated) VALUES(?,?,?,?,?,?,?,?,?,?,?);");
$insert_contacts->bind_param("issssssssis", $contact_id, $last_name, $first_name, $email, $phone, $street_1, $street_2, $city, $state_code, $post_code, $updated);
$contact_id = 1;
$last_name = "Doe";
$first_name = "John";
$email = "johndoe@example.com";
$phone = "123-456-7890";
$street_1 = "123 Main St";
$street_2 = "";
$city = "Anytown";
$state_code = "CA";
$post_code = 12345;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$contact_id = 2;
$last_name = "Smith";
$first_name = "Jane";
$email = "janesmith@example.com";
$phone = "987-654-3210";
$street_1 = "456 Elm St";
$street_2 = "Apt 2";
$city = "Othertown";
$state_code = "NY";
$post_code = 54321;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$contact_id = 3;
$last_name = "Brown";
$first_name = "Charlie";
$email = "charliebrown@example.com";
$phone = "555-555-5555";
$street_1 = "789 Oak St";
$street_2 = "";
$city = "Sometown";
$state_code = "TX";
$post_code = 67890;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$insert_contacts->close();

/* Users */
$insert_users = $db_connection->prepare(
	"INSERT INTO Users
		(user_id, role_id, contact_id, creation_date) VALUES(?,?,?,?);");
$insert_users->bind_param("iiis", $user_id, $role_id, $contact_id, $creation_date);
$user_id = 1;
$role_id = 1;
$contact_id = 1;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$user_id = 2;
$role_id = 2;
$contact_id = 2;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$user_id = 3;
$role_id = 3;
$contact_id = 3;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$insert_users->close();



/* Credentials */
$insert_credentials = $db_connection->prepare(
	"INSERT INTO Credentials
		(username, user_id, password_salted) VALUES(?,?,?);");
$insert_credentials->bind_param("sis", $username, $user_id, $password_salted);
$username = "johndoe";
$user_id = 1;
$password_salted = crypt("SCSU2024", $salt);
//$password_salted = password_hash("password123", PASSWORD_DEFAULT);
$insert_credentials->execute();

$user_id = 2;
$username = "janesmith";
$password_salted = crypt("SCSU2025", $salt);
//$password_salted = password_hash("mypassword", PASSWORD_DEFAULT);
$insert_credentials->execute();

$user_id = 3;
$username = "charliebrown";
$password_salted = crypt("SCSU2026", $salt);
//$password_salted = password_hash("charlie123", PASSWORD_DEFAULT);
$insert_credentials->execute();

$insert_credentials->close();

/* Recipes */
$insert_recipe = $db_connection->prepare(
    "INSERT INTO Recipes
        (recipe_id, title, category_id, description_text, prep_time, cook_time, user_id) VALUES(?,?,?,?,?,?,?);");
$insert_recipe->bind_param("isissssi", $recipe_id, $title, $category_id, $description_text, $prep_time, $cook_time, $image_url, $user_id);

$recipe_id = 1;
$title = "Classic Pancakes";
$category_id = 1;
$description_text = "Fluffy and delicious pancakes perfect for weekend breakfast. These golden pancakes are easy to make and loved by everyone!";
$prep_time = "10 minutes";
$cook_time = "15 minutes";
$image_url = "https://images.unsplash.com/photo-1528207776546-365bb710ee93?w=1600";
$user_id = 1;
$insert_recipe->execute();

$recipe_id = 2;
$title = "Spaghetti Carbonara";
$category_id = 3;
$description_text = "A classic Italian pasta dish made with eggs, cheese, bacon, and black pepper. Simple yet incredibly satisfying.";
$prep_time = "5 minutes";
$cook_time = "20 minutes";
$image_url = "https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=1600";
$user_id = 2;
$insert_recipe->execute();

$recipe_id = 3;
$title = "Chocolate Chip Cookies";
$category_id = 4;
$description_text = "Homemade chocolate chip cookies that are crispy on the outside and chewy on the inside. A timeless favorite!";
$prep_time = "15 minutes";
$cook_time = "12 minutes";
$image_url = "https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=1600";
$user_id = 1;
$insert_recipe->execute();

$insert_recipe->close();

/* Instructions */
$insert_instruction = $db_connection->prepare(
    "INSERT INTO Instructions
        (recipe_id, step_number, instruction_text) VALUES(?,?,?);");
$insert_instruction->bind_param("iis", $recipe_id, $step_number, $instruction_text);

// Pancake instructions
$recipe_id = 1;
$step_number = 1;
$instruction_text = "In a large bowl, mix together flour, sugar, baking powder, and salt.";
$insert_instruction->execute();

$step_number = 2;
$instruction_text = "In another bowl, beat eggs and add milk and melted butter.";
$insert_instruction->execute();

$step_number = 3;
$instruction_text = "Pour wet ingredients into dry ingredients and stir until just combined.";
$insert_instruction->execute();

$step_number = 4;
$instruction_text = "Heat a griddle over medium heat and pour batter to form pancakes. Cook until bubbles form, then flip.";
$insert_instruction->execute();

// Carbonara instructions
$recipe_id = 2;
$step_number = 1;
$instruction_text = "Cook spaghetti according to package directions. Reserve 1 cup pasta water.";
$insert_instruction->execute();

$step_number = 2;
$instruction_text = "Fry bacon until crispy, then remove from heat.";
$insert_instruction->execute();

$step_number = 3;
$instruction_text = "Whisk together eggs and grated Parmesan cheese.";
$insert_instruction->execute();

$step_number = 4;
$instruction_text = "Toss hot pasta with bacon, then remove from heat and quickly stir in egg mixture. Add pasta water as needed.";
$insert_instruction->execute();

// Cookie instructions
$recipe_id = 3;
$step_number = 1;
$instruction_text = "Preheat oven to 375°F. Mix butter and sugars until creamy.";
$insert_instruction->execute();

$step_number = 2;
$instruction_text = "Beat in eggs and vanilla extract.";
$insert_instruction->execute();

$step_number = 3;
$instruction_text = "Gradually blend in flour, baking soda, and salt. Stir in chocolate chips.";
$insert_instruction->execute();

$step_number = 4;
$instruction_text = "Drop rounded tablespoons onto baking sheet. Bake 9-11 minutes until golden brown.";
$insert_instruction->execute();

$insert_instruction->close();

/* Ingredients */
$insert_ingredient = $db_connection->prepare(
    "INSERT INTO Ingredients
        (ingredient_id, ingredient_name) VALUES(?,?);");
$insert_ingredient->bind_param("is", $ingredient_id, $ingredient_name);

$ingredient_id = 1;
$ingredient_name = "All-purpose flour";
$insert_ingredient->execute();

$ingredient_id = 2;
$ingredient_name = "Sugar";
$insert_ingredient->execute();

$ingredient_id = 3;
$ingredient_name = "Eggs";
$insert_ingredient->execute();

$ingredient_id = 4;
$ingredient_name = "Milk";
$insert_ingredient->execute();

$ingredient_id = 5;
$ingredient_name = "Butter";
$insert_ingredient->execute();

$ingredient_id = 6;
$ingredient_name = "Spaghetti";
$insert_ingredient->execute();

$ingredient_id = 7;
$ingredient_name = "Bacon";
$insert_ingredient->execute();

$ingredient_id = 8;
$ingredient_name = "Parmesan cheese";
$insert_ingredient->execute();

$ingredient_id = 9;
$ingredient_name = "Chocolate chips";
$insert_ingredient->execute();

$insert_ingredient->close();

/* Ingredients Lists */
$insert_ingredients_list = $db_connection->prepare(
    "INSERT INTO Ingredients_Lists
        (recipe_id, ingredient_id, amount, measuring_unit) VALUES(?,?,?,?);");
$insert_ingredients_list->bind_param("iiss", $recipe_id, $ingredient_id, $amount, $measuring_unit);

// Pancakes ingredients
$recipe_id = 1;
$ingredient_id = 1;
$amount = "2";
$measuring_unit = "cups";
$insert_ingredients_list->execute();

$ingredient_id = 2;
$amount = "2";
$measuring_unit = "tablespoons";
$insert_ingredients_list->execute();

$ingredient_id = 3;
$amount = "2";
$measuring_unit = "whole";
$insert_ingredients_list->execute();

$ingredient_id = 4;
$amount = "1.5";
$measuring_unit = "cups";
$insert_ingredients_list->execute();

$ingredient_id = 5;
$amount = "3";
$measuring_unit = "tablespoons";
$insert_ingredients_list->execute();

// Carbonara ingredients
$recipe_id = 2;
$ingredient_id = 6;
$amount = "400";
$measuring_unit = "grams";
$insert_ingredients_list->execute();

$ingredient_id = 7;
$amount = "200";
$measuring_unit = "grams";
$insert_ingredients_list->execute();

$ingredient_id = 3;
$amount = "3";
$measuring_unit = "whole";
$insert_ingredients_list->execute();

$ingredient_id = 8;
$amount = "100";
$measuring_unit = "grams";
$insert_ingredients_list->execute();

// Cookies ingredients
$recipe_id = 3;
$ingredient_id = 1;
$amount = "2.25";
$measuring_unit = "cups";
$insert_ingredients_list->execute();

$ingredient_id = 5;
$amount = "1";
$measuring_unit = "cup";
$insert_ingredients_list->execute();

$ingredient_id = 2;
$amount = "1";
$measuring_unit = "cup";
$insert_ingredients_list->execute();

$ingredient_id = 3;
$amount = "2";
$measuring_unit = "whole";
$insert_ingredients_list->execute();

$ingredient_id = 9;
$amount = "2";
$measuring_unit = "cups";
$insert_ingredients_list->execute();

$insert_ingredients_list->close();

/* Ratings */
$insert_rating = $db_connection->prepare(
    "INSERT INTO Ratings
        (user_id, recipe_id, rating) VALUES(?,?,?);");
$insert_rating->bind_param("iii", $user_id, $recipe_id, $rating);

$user_id = 2;
$recipe_id = 1;
$rating = 5;
$insert_rating->execute();

$user_id = 3;
$recipe_id = 1;
$rating = 4;
$insert_rating->execute();

$user_id = 1;
$recipe_id = 2;
$rating = 5;
$insert_rating->execute();

$user_id = 3;
$recipe_id = 2;
$rating = 5;
$insert_rating->execute();

$user_id = 2;
$recipe_id = 3;
$rating = 4;
$insert_rating->execute();

$insert_rating->close();

/* Comments */
$insert_comment = $db_connection->prepare(
    "INSERT INTO Comments
        (user_id, recipe_id, text) VALUES(?,?,?);");
$insert_comment->bind_param("iis", $user_id, $recipe_id, $comment_text);

$user_id = 2;
$recipe_id = 1;
$comment_text = "These pancakes turned out amazing! My kids loved them!";
$insert_comment->execute();

$user_id = 1;
$recipe_id = 2;
$comment_text = "Best carbonara I've ever made. So creamy!";
$insert_comment->execute();

$user_id = 3;
$recipe_id = 3;
$comment_text = "Perfect cookies for my family gathering!";
$insert_comment->execute();

$insert_comment->close();

/* Favorites */
$insert_favorite = $db_connection->prepare(
    "INSERT INTO Favorites
        (user_id, recipe_id) VALUES(?,?);");
$insert_favorite->bind_param("ii", $user_id, $recipe_id);

$user_id = 2;
$recipe_id = 1;
$insert_favorite->execute();

$user_id = 1;
$recipe_id = 2;
$insert_favorite->execute();

$user_id = 3;
$recipe_id = 2;
$insert_favorite->execute();

$user_id = 2;
$recipe_id = 3;
$insert_favorite->execute();

$insert_favorite->close();

/* Tag Names */
$insert_tag_name = $db_connection->prepare(
    "INSERT INTO Tag_Names
        (tag_id, tag_name) VALUES(?,?);");
$insert_tag_name->bind_param("is", $tag_id, $tag_name);

$tag_id = 1;
$tag_name = "Quick";
$insert_tag_name->execute();

$tag_id = 2;
$tag_name = "Easy";
$insert_tag_name->execute();

$tag_id = 3;
$tag_name = "Family-Friendly";
$insert_tag_name->execute();

$tag_id = 4;
$tag_name = "Italian";
$insert_tag_name->execute();

$insert_tag_name->close();

/* Tags */
$insert_tag = $db_connection->prepare(
    "INSERT INTO Tags
        (tag_id, recipe_id) VALUES(?,?);");
$insert_tag->bind_param("ii", $tag_id, $recipe_id);

$tag_id = 2;
$recipe_id = 1;
$insert_tag->execute();

$tag_id = 3;
$recipe_id = 1;
$insert_tag->execute();

$tag_id = 1;
$recipe_id = 2;
$insert_tag->execute();

$tag_id = 4;
$recipe_id = 2;
$insert_tag->execute();

$tag_id = 3;
$recipe_id = 3;
$insert_tag->execute();

$insert_tag->close();




/* Status Display */
echo nl2br("The database tables were successfully populated.\r\n");
/* Return to homepage after 5 seconds */
header( "refresh:10;url=/csc540_login" );

/* ALWAYS CLOSE THE DB CONNECTION */
$db_connection->close();

?>
