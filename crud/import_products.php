<?php
require_once '../HTML/config.php';

// Set content type to HTML
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Products</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        h2 { color: #4caf50; }
        p { margin: 10px 0; }
        a { color: #4caf50; text-decoration: none; }
        a:hover { text-decoration: underline; }
        ul { color: #ff6b6b; }
    </style>
</head>
<body>
<?php

// All products from product.php and product-1.php
$products = [
    // From product.php
    ['name' => 'Cat Scratch Post', 'price' => 100.00, 'stock' => 50, 'image_url' => '../HTML/images/scratchcat.jpg', 'description' => 'Durable cat scratch post for your feline friend'],
    ['name' => 'Cat Mouse Toy', 'price' => 10.00, 'stock' => 100, 'image_url' => '../HTML/images/catmouse.jpg', 'description' => 'Interactive mouse toy for cats'],
    ['name' => 'Cat Bed', 'price' => 45.00, 'stock' => 75, 'image_url' => '../HTML/images/catbed.jpg', 'description' => 'Comfortable cat bed for rest and relaxation'],
    ['name' => 'Cat Tree', 'price' => 200.00, 'stock' => 30, 'image_url' => '../HTML/images/cattree.jpg', 'description' => 'Multi-level cat tree for climbing and playing'],
    ['name' => 'Wiggly Worm Cat Teaser Wand', 'price' => 15.00, 'stock' => 80, 'image_url' => '../HTML/images/wiggly worm cat teaser wand.jpg', 'description' => 'Interactive wand toy with wiggly worm attachment'],
    ['name' => 'Cat Food Bowl', 'price' => 35.00, 'stock' => 90, 'image_url' => '../HTML/images/catfoodbowl.jpg', 'description' => 'Sturdy food bowl for your cat'],
    ['name' => 'Cat Litter Box', 'price' => 99.00, 'stock' => 60, 'image_url' => '../HTML/images/litterbox.jpg', 'description' => 'Hygienic litter box for cats'],
    ['name' => 'Cat Carrier', 'price' => 160.20, 'stock' => 40, 'image_url' => '../HTML/images/catcarrier.jpg', 'description' => 'Safe and comfortable cat carrier for travel'],
    ['name' => 'Cute Cartoon Ceramic Cat Bowl', 'price' => 40.00, 'stock' => 70, 'image_url' => '../HTML/images/cute cartoon ceramic cat bowl with high stand.jpg', 'description' => 'Adorable ceramic cat bowl with high stand'],
    ['name' => 'Flower Shaped Cat Bed', 'price' => 32.00, 'stock' => 55, 'image_url' => '../HTML/images/ed10f9b63f00da532aeae7a698b1a931.jpg', 'description' => 'Beautiful flower-shaped cat bed'],
    ['name' => 'Banana Cat Bed', 'price' => 32.00, 'stock' => 65, 'image_url' => '../HTML/images/banana cat bed.jpg', 'description' => 'Fun banana-shaped cat bed'],
    ['name' => 'Three Tier Flower Cat Tree', 'price' => 120.00, 'stock' => 25, 'image_url' => '../HTML/images/three tier flower cat tree.jpg', 'description' => 'Beautiful three-tier flower cat tree'],
    
    // From product-1.php
    ['name' => 'Pink Ceramic Raised Cat Bowl', 'price' => 70.00, 'stock' => 45, 'image_url' => '../HTML/images/Pink Ceramic Raised Cat Bowl.jpg', 'description' => 'Elegant pink ceramic raised cat bowl'],
    ['name' => 'Cute Mushroom Raised Cat Bowl', 'price' => 60.00, 'stock' => 50, 'image_url' => '../HTML/images/Cute Mushroom Raised Cat Bowl.jpg', 'description' => 'Adorable mushroom-themed raised cat bowl'],
    ['name' => 'Foldable Cat Carrier Bag', 'price' => 110.00, 'stock' => 35, 'image_url' => '../HTML/images/Foldable Cat Carrier Bag.jpg', 'description' => 'Convenient foldable cat carrier bag'],
    ['name' => 'Exercise Wheel For Cat', 'price' => 300.00, 'stock' => 20, 'image_url' => '../HTML/images/Exercise Wheel For Cat.jpg', 'description' => 'Exercise wheel for active cats'],
    ['name' => 'Cat Tumbler Ball Toy', 'price' => 20.00, 'stock' => 85, 'image_url' => '../HTML/images/Cat Tumbler Boy Toy.jpg', 'description' => 'Interactive tumbler ball toy for cats'],
    ['name' => 'Spaceship Litter Box', 'price' => 35.00, 'stock' => 70, 'image_url' => '../HTML/images/Spaceship Litter Box.jpg', 'description' => 'Futuristic spaceship-themed litter box'],
    ['name' => 'Hollow Plastic Ball', 'price' => 9.00, 'stock' => 120, 'image_url' => '../HTML/images/Hollow Plastic Ball.webp', 'description' => 'Simple hollow plastic ball toy'],
    ['name' => 'Mushroom Cat Scratcher', 'price' => 130.20, 'stock' => 30, 'image_url' => '../HTML/images/Mushroom Cat Scratcher.jpg', 'description' => 'Cute mushroom-shaped cat scratcher'],
    ['name' => 'Cupcake Cat Tree', 'price' => 150.00, 'stock' => 25, 'image_url' => '../HTML/images/cupcake cat tree.jpg', 'description' => 'Delightful cupcake-themed cat tree'],
    ['name' => 'Cat Frog Bed', 'price' => 16.00, 'stock' => 95, 'image_url' => '../HTML/images/c40339c1a8de417f0b4ea5d968799846.jpg', 'description' => 'Adorable frog-shaped cat bed'],
    ['name' => '3-in-1 Interactive Cat Toy', 'price' => 60.00, 'stock' => 40, 'image_url' => '../HTML/images/3n1 Interactive Toy With Fluttering Butterfly Led Light Automatic Cat Toy.jpg', 'description' => 'Advanced 3-in-1 interactive cat toy with LED lights'],
    ['name' => 'Flower Cat Tree', 'price' => 132.00, 'stock' => 28, 'image_url' => '../HTML/images/Flower Cat Tree.jpg', 'description' => 'Beautiful flower-themed cat tree'],
];

$added = 0;
$skipped = 0;
$errors = [];

foreach ($products as $product) {
    // Check if product already exists
    $check = $conn->prepare("SELECT product_id FROM products WHERE name = ?");
    $check->bind_param("s", $product['name']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $skipped++;
        continue;
    }
    
    // Insert product
    $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $category_id = null; // No category assigned
    $stmt->bind_param("issdis", $category_id, $product['name'], $product['description'], $product['price'], $product['stock'], $product['image_url']);
    
    if ($stmt->execute()) {
        $added++;
    } else {
        $errors[] = "Failed to add {$product['name']}: " . $stmt->error;
    }
    
    $stmt->close();
    $check->close();
}

echo "<h2>Product Import Results</h2>";
echo "<p><strong>Added:</strong> $added products</p>";
echo "<p><strong>Skipped (already exist):</strong> $skipped products</p>";

if (!empty($errors)) {
    echo "<h3>Errors:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p><a href='../admins/admin_page.php'>Go to Admin Panel</a></p>";
?>
</body>
</html>

