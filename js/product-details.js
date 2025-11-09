// 🐾 Product List
const products = [
    { 
        name: "Pink Ceramic Raised Cat Bowl", 
        img: "images/Pink Ceramic Raised Cat Bowl.jpg", 
        price: "$70.00", 
        rating: 4,
        description: "A stylish pink ceramic bowl raised at the perfect height to help your cat eat comfortably and prevent neck strain."
    },
    { 
        name: "Cute Mushroom Raised Cat Bowl", 
        img: "images/Cute Mushroom Raised Cat Bowl.jpg", 
        price: "$60.00", 
        rating: 4,
        description: "A cute mushroom-shaped raised bowl designed for easy eating and to reduce whisker fatigue for your cat."
    },
    { 
        name: "Foldable Cat Carrier Bag", 
        img: "images/Foldable Cat Carrier Bag.jpg", 
        price: "$110.00", 
        rating: 4.5,
        description: "A breathable, portable carrier ideal for travel and vet visits. Folds flat for easy storage."
    },
    { 
        name: "Exercise Wheel For Cat", 
        img: "images/Exercise Wheel For Cat.jpg", 
        price: "$300.00", 
        rating: 5,
        description: "A large, quiet wheel that lets your cat run safely indoors and burn off extra energy."
    },
    { 
        name: "Cat Tumbler Ball Toy", 
        img: "images/Cat Tumbler Boy Toy.jpg", 
        price: "$20.00", 
        rating: 3.5,
        description: "An interactive tumbler toy that wobbles and rolls unpredictably to keep your cat entertained."
    },
    { 
        name: "Spaceship Litter Box", 
        img: "images/Spaceship Litter Box.jpg", 
        price: "$35.00", 
        rating: 5,
        description: "A futuristic enclosed litter box that reduces odors and offers easy cleaning access."
    },
    { 
        name: "Hollow Plastic Ball", 
        img: "images/Hollow Plastic Ball.webp", 
        price: "$9.00", 
        rating: 4,
        description: "Lightweight plastic balls perfect for chasing and batting around the house."
    },
    { 
        name: "Mushroom Cat Scratcher", 
        img: "images/Mushroom Cat Scratcher.jpg", 
        price: "$130.20", 
        rating: 4,
        description: "A mushroom-shaped sisal scratcher that keeps your cat’s claws healthy and your furniture safe."
    },
    { 
        name: "Cupcake Cat Tree", 
        img: "images/cupcake cat tree.jpg", 
        price: "$150.00", 
        rating: 4,
        description: "A colorful cupcake-themed cat tree with multiple levels for climbing, scratching, and lounging."
    },
    { 
        name: "Cat Frog Bed", 
        img: "images/c40339c1a8de417f0b4ea5d968799846.jpg", 
        price: "$16.00", 
        rating: 4,
        description: "A soft and cozy frog-shaped bed that offers warmth and comfort for your cat’s naps."
    },
    { 
        name: "3-in-1 Interactive Cat Toy", 
        img: "images/3n1 Interactive Toy With Fluttering Butterfly Led Light Automatic Cat Toy.jpg", 
        price: "$60.00", 
        rating: 4.5,
        description: "A 3-in-1 automatic toy featuring a fluttering butterfly, LED lights, and spinning fun for endless play."
    },
    { 
        name: "Flower Cat Tree", 
        img: "images/Flower Cat Tree.jpg", 
        price: "$132.00", 
        rating: 4.5,
        description: "A colorful flower-themed cat tree perfect for climbing, scratching, and lounging."
    },
    { 
        name: "Cat Scratch Post", 
        img: "images/scratchcat.jpg", 
        price: "$100.00", 
        rating: 4,
        description: "A sturdy sisal post that keeps your cat’s claws healthy and your furniture safe. Great for daily scratching and play."
    },
    { 
        name: "Cat Mouse Toy", 
        img: "images/catmouse.jpg", 
        price: "$10.00", 
        rating: 4,
        description: "Realistic, lightweight mouse toys that encourage your cat’s natural hunting instincts."
    },
    { 
        name: "Cat Bed", 
        img: "images/catbed.jpg", 
        price: "$45.00", 
        rating: 4.5,
        description: "A soft and cozy bed that offers warmth and comfort for your cat’s naps."
    },
    { 
        name: "Cat Tree", 
        img: "images/cattree.jpg", 
        price: "$200.00", 
        rating: 4,
        description: "A multi-level climbing tree with scratching posts and cozy platforms for endless fun."
    },
    { 
        name: "Wiggly Worm Cat Teaser Wand", 
        img: "images/wiggly worm cat teaser wand.jpg", 
        price: "$15.00", 
        rating: 3.5,
        description: "Colorful worm wands that provide interactive playtime and exercise for you and your cat."
    },
    { 
        name: "Cat Food Bowl", 
        img: "images/catfoodbowl.jpg", 
        price: "$35.00", 
        rating: 4,
        description: "Dual-section bowl with a water dispenser, designed for easy feeding and cleaning."
    },
    { 
        name: "Cat Litter Box", 
        img: "images/litterbox.jpg", 
        price: "$99.00", 
        rating: 4,
        description: "Enclosed litter box that reduces odors and offers easy maintenance."
    },
    { 
        name: "Cat Carrier", 
        img: "images/catcarrier.jpg", 
        price: "$160.20", 
        rating: 4,
        description: "A breathable, portable carrier ideal for travel and vet visits."
    },
    { 
        name: "Cute Cartoon Ceramic Cat Bowl", 
        img: "images/cute cartoon ceramic cat bowl with high stand.jpg", 
        price: "$160.20", 
        rating: 5,
        description: "Adorable ceramic bowls that are stable, durable, and easy to clean."
    },
    { 
        name: "Flower Shaped Cat Bed", 
        img: "images/ed10f9b63f00da532aeae7a698b1a931.jpg", 
        price: "$32.00", 
        rating: 4,
        description: "A soft, petal-shaped bed that’s as comfy as it is cute."
    },
    { 
        name: "Banana Cat Bed", 
        img: "images/banana cat bed.jpg", 
        price: "$32.00", 
        rating: 4.5,
        description: "Fun banana-shaped bed where your cat can snuggle or hide."
    },
    { 
        name: "Three Tier Flower Cat Tree", 
        img: "images/three tier flower cat tree.jpg", 
        price: "$120.00", 
        rating: 4,
        description: "A colorful flower-themed cat tree perfect for climbing, scratching, and lounging."
    }
];

// ⭐ Generate star icons
function getStars(rating) {
    let stars = "";
    for (let i = 1; i <= 5; i++) {
        if (rating >= i) stars += '<i class="fa-solid fa-star" style="color: gold;"></i>';
        else if (rating >= i - 0.5) stars += '<i class="fa-solid fa-star-half-stroke" style="color: gold;"></i>';
        else stars += '<i class="fa-regular fa-star" style="color: gold;"></i>';
    }
    return stars;
}

// 🧭 Get current product name from URL
const params = new URLSearchParams(window.location.search);
const productName = params.get("name");

// 🕵️ Find product by name
const product = products.find(p => p.name === productName);

// 📦 Display single product
const productContainer = document.getElementById("product-details");

if (product) {
    productContainer.innerHTML = `
        <div class="product-container single-product">
            <div class="product-image">
                <img src="${product.img}" alt="${product.name}">
            </div>
            <div class="product-details">
                <p>Home / Cat Accessories</p>
                <h1>${product.name}</h1>
                <h4>${product.price}</h4>
                <input type="number" value="1">
                <a href="#" class="purchase-btn-2">Add To Cart</a>
                <h3>Description:</h3>
                <p>${product.description}</p>
                <h3>Product Details <i class="fa fa-indent"></i></h3>
            </div>
        </div>
    `;
} else {
    productContainer.innerHTML = `<p>Product not found.</p>`;
}

// 🐾 Shuffle related products (excluding current one)
function shuffle(array) {
    let currentIndex = array.length, randomIndex;
    while (currentIndex > 0) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex--;
        [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
    }
    return array;
}

const filteredProducts = products.filter(p => p.name !== productName);
const relatedContainer = document.getElementById("related-container");
const shuffled = shuffle([...filteredProducts]).slice(0, 4);

// ⭐ Display related products with clickable product boxes
relatedContainer.innerHTML = "";
shuffled.forEach(prod => {
    const div = document.createElement("div");
    div.classList.add("cat2"); // same style as your example
    div.innerHTML = `
        <a href="product-detail.php?name=${encodeURIComponent(prod.name)}">
            <img src="${prod.img}" alt="${prod.name}">
        </a>
        <h4>${prod.name}</h4>
        <div class="rating">
            ${getStars(prod.rating)}
        </div>
        <p><strong>${prod.price}</strong></p>
        <a href="product-detail.php?name=${encodeURIComponent(prod.name)}">
            <button class="purchase-btn">Purchase</button>
        </a>
    `;
    relatedContainer.appendChild(div);
});
