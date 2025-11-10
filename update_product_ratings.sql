-- Update product ratings based on the screenshots provided
-- Run this in phpMyAdmin or MySQL command line

USE katakumi;

-- First, ensure the rating column exists (if it doesn't already)
-- ALTER TABLE products ADD COLUMN rating DECIMAL(2,1) DEFAULT NULL;

-- ========== PAGE 1 PRODUCTS ==========

-- Row 1
UPDATE products SET rating = 4.0 WHERE name = 'Cat Scratch Post';
UPDATE products SET rating = 4.0 WHERE name = 'Cat Mouse Toy';
UPDATE products SET rating = 4.5 WHERE name = 'Cat Bed';
UPDATE products SET rating = 4.0 WHERE name = 'Cat Tree';

-- Row 2
UPDATE products SET rating = 4.0 WHERE name = 'Wiggly Worm Cat Teaser Wand';
UPDATE products SET rating = 4.0 WHERE name = 'Cat Food Bowl';
UPDATE products SET rating = 4.0 WHERE name = 'Cat Litter Box';
UPDATE products SET rating = 4.0 WHERE name = 'Cat Carrier';

-- Row 3
UPDATE products SET rating = 5.0 WHERE name = 'Cute Cartoon Ceramic Cat Bowl';
UPDATE products SET rating = 4.0 WHERE name = 'Flower Shaped Cat Bed';
UPDATE products SET rating = 5.0 WHERE name = 'Banana Cat Bed';
UPDATE products SET rating = 4.0 WHERE name = 'Three Tier Flower Cat Tree';

-- ========== PAGE 2 PRODUCTS ==========

-- Row 1
UPDATE products SET rating = 4.0 WHERE name LIKE '%Pink%Ceramic%Bowl%' OR name = 'Pink Ceramic Raised Cat Bowl';
UPDATE products SET rating = 4.0 WHERE name LIKE '%Mushroom%Bowl%' OR name LIKE '%Cute Mushroom%';
UPDATE products SET rating = 4.5 WHERE name LIKE '%Foldable%Carrier%';
UPDATE products SET rating = 4.5 WHERE name LIKE '%Exercise Wheel%';

-- Row 2
UPDATE products SET rating = 4.0 WHERE name LIKE '%Spaceship%Ball%' OR name LIKE '%Cat Spaceship%Ball%';
UPDATE products SET rating = 3.5 WHERE name LIKE '%Spaceship%Litter%' OR name LIKE '%Teacup%Litter%';
UPDATE products SET rating = 4.0 WHERE name LIKE '%Hollow%Ball%' OR name LIKE '%Plastic Ball%';
UPDATE products SET rating = 4.5 WHERE name LIKE '%Mushroom%Scratcher%';

-- Row 3
UPDATE products SET rating = 4.0 WHERE name LIKE '%Cupcake%Tree%';
UPDATE products SET rating = 4.0 WHERE name LIKE '%Cat Frog Bed%' OR name LIKE '%Frog%Bed%';
UPDATE products SET rating = 4.0 WHERE name LIKE '%3-in-1%' OR name LIKE '%Interactive%Butterfly%';
UPDATE products SET rating = 4.5 WHERE name LIKE '%Flower Cat Tree%' AND name NOT LIKE '%Three Tier%';

-- Verify the updates
SELECT name, rating FROM products ORDER BY name;
