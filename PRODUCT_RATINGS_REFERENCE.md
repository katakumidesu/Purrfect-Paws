# Product Ratings Reference

Based on the screenshots provided, here are the ratings for each product:

## Page 1 Products

### Row 1
| Product | Rating | Stars |
|---------|--------|-------|
| Cat Scratch Post | 4.5 | ⭐⭐⭐⭐✨ |
| Cat Mouse Toy | 4.0 | ⭐⭐⭐⭐☆ |
| Cat Bed | 4.5 | ⭐⭐⭐⭐✨ |
| Cat Tree | 4.0 | ⭐⭐⭐⭐☆ |

### Row 2
| Product | Rating | Stars |
|---------|--------|-------|
| Wiggly Worm Cat Teaser Wand | 4.0 | ⭐⭐⭐⭐☆ |
| Cat Food Bowl | 4.0 | ⭐⭐⭐⭐☆ |
| Cat Litter Box | 4.0 | ⭐⭐⭐⭐☆ |
| Cat Carrier | 4.0 | ⭐⭐⭐⭐☆ |

### Row 3
| Product | Rating | Stars |
|---------|--------|-------|
| Cute Cartoon Ceramic Cat Bowl | 5.0 | ⭐⭐⭐⭐⭐ |
| Flower Shaped Cat Bed | 4.0 | ⭐⭐⭐⭐☆ |
| Banana Cat Bed | 5.0 | ⭐⭐⭐⭐⭐ |
| Three Tier Flower Cat Tree | 4.0 | ⭐⭐⭐⭐☆ |

## Page 2 Products

### Row 1
| Product | Rating | Stars |
|---------|--------|-------|
| Pink Ceramic Raised Cat Bowl | 4.0 | ⭐⭐⭐⭐☆ |
| Cute Mushroom Raised Cat Bowl | 4.0 | ⭐⭐⭐⭐☆ |
| Foldable Cat Carrier Bag | 4.5 | ⭐⭐⭐⭐✨ |
| Exercise Wheel For Cat | 5.0 | ⭐⭐⭐⭐⭐ |

### Row 2
| Product | Rating | Stars |
|---------|--------|-------|
| Cat Spaceship Bed/Litter Box (Blue) | 3.5 | ⭐⭐⭐✨☆ |
| Teacup Litter Box (Purple) | 4.0 | ⭐⭐⭐⭐☆ |
| Banana Peel Cat Bed | 4.5 | ⭐⭐⭐⭐✨ |
| Mushroom Cat Scratcher | 4.0 | ⭐⭐⭐⭐☆ |

### Row 3
| Product | Rating | Stars |
|---------|--------|-------|
| Spaceship Cat Tree (Pink/Beige) | 4.0 | ⭐⭐⭐⭐☆ |
| Cat Frog Bed (Green) | 4.5 | ⭐⭐⭐⭐✨ |
| 3-in-1 Interactive Butterfly Toy | 4.0 | ⭐⭐⭐⭐☆ |
| Flower Cat Tree | 4.5 | ⭐⭐⭐⭐✨ |

## Rating Distribution Summary

- **5.0 stars (⭐⭐⭐⭐⭐)**: 3 products
  - Cute Cartoon Ceramic Cat Bowl
  - Banana Cat Bed
  - Exercise Wheel For Cat

- **4.5 stars (⭐⭐⭐⭐✨)**: 6 products
  - Cat Scratch Post
  - Cat Bed
  - Foldable Cat Carrier Bag
  - Banana Peel Cat Bed
  - Cat Frog Bed
  - Flower Cat Tree

- **4.0 stars (⭐⭐⭐⭐☆)**: 14 products
  - Cat Mouse Toy
  - Cat Tree
  - Wiggly Worm Cat Teaser Wand
  - Cat Food Bowl
  - Cat Litter Box
  - Cat Carrier
  - Flower Shaped Cat Bed
  - Three Tier Flower Cat Tree
  - Pink Ceramic Raised Cat Bowl
  - Cute Mushroom Raised Cat Bowl
  - Teacup Litter Box
  - Mushroom Cat Scratcher
  - Spaceship Cat Tree
  - 3-in-1 Interactive Butterfly Toy

- **3.5 stars (⭐⭐⭐✨☆)**: 1 product
  - Cat Spaceship Bed

## How to Apply These Ratings

### Option 1: Run SQL Script
```bash
# Open phpMyAdmin (http://localhost/phpmyadmin)
# Select 'katakumi' database
# Go to SQL tab
# Copy and paste contents from 'update_product_ratings.sql'
# Click 'Go'
```

### Option 2: Update via Admin Panel
1. Go to your admin page
2. Edit each product
3. Set the rating field to match the values above
4. Save

### Option 3: Run SQL from Command Line
```bash
mysql -u root -p katakumi < update_product_ratings.sql
```

## Notes

- ✨ = Half star (shown as star-half-stroke icon)
- ☆ = Empty star
- ⭐ = Full star

The system automatically displays these as FontAwesome icons:
- Full: `<i class="fa-solid fa-star"></i>`
- Half: `<i class="fa-solid fa-star-half-stroke"></i>`
- Empty: `<i class="fa-regular fa-star"></i>`
