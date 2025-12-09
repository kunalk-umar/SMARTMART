// Mock Database (Updated with Indian Prices)
// Database with STABLE Image Links
const products = [
    { 
        id: 1, 
        name: "Fresh Apples (1kg)", 
        price: 180.00, 
        category: "fruit", 
        image: "https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Red_Apple.jpg/400px-Red_Apple.jpg" 
    },
    { 
        id: 2, 
        name: "Bananas (1 Dozen)", 
        price: 60.00, 
        category: "fruit", 
        image: "https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Banana-Single.jpg/400px-Banana-Single.jpg" 
    },
    { 
        id: 3, 
        name: "Broccoli (1pc)", 
        price: 80.00, 
        category: "vegetable", 
        image: "https://upload.wikimedia.org/wikipedia/commons/thumb/0/03/Broccoli_and_cross_section_edit.jpg/400px-Broccoli_and_cross_section_edit.jpg" 
    },
    { 
        id: 4, 
        name: "Whole Milk (1L)", 
        price: 66.00, 
        category: "dairy", 
        image: "https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Glass_of_Milk_%2833657535532%29.jpg/400px-Glass_of_Milk_%2833657535532%29.jpg" 
    },
    { 
        id: 5, 
        name: "Farm Eggs (6pcs)", 
        price: 55.00, 
        category: "dairy", 
        image: "https://images.unsplash.com/photo-1491524062933-cb0289261700?auto=format&fit=crop&w=400&q=80" 
    },
    { 
        id: 6, 
        name: "Sourdough Bread", 
        price: 50.00, 
        category: "bakery", 
        image: "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Korb_mit_Br%C3%B6tchen.JPG/400px-Korb_mit_Br%C3%B6tchen.JPG" 
    },
];

let cart = [];

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    displayProducts(products);
});

// Display Products (Updated with ₹ symbol)
function displayProducts(items) {
    const container = document.getElementById('product-container');
    container.innerHTML = items.map(product => `
        <div class="product-card">
            <img src="${product.image}" alt="${product.name}">
            <div class="product-info">
                <h3>${product.name}</h3>
                <span class="price">₹${product.price.toFixed(2)}</span>
                <button class="add-btn" onclick="addToCart(${product.id})">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    `).join('');
}

// Add to Cart
function addToCart(id) {
    const product = products.find(p => p.id === id);
    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    updateCartUI();
}

// Toggle Cart Sidebar
function toggleCart() {
    document.getElementById('cart-sidebar').classList.toggle('active');
}

// Update Cart UI (Updated with ₹ symbol)
function updateCartUI() {
    const cartItemsContainer = document.getElementById('cart-items');
    const totalSpan = document.getElementById('total-price');
    const countSpan = document.getElementById('cart-count');

    cartItemsContainer.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div>
                <h4>${item.name}</h4>
                <p>₹${item.price} x ${item.quantity}</p>
            </div>
            <div>
                <p>₹${(item.price * item.quantity).toFixed(2)}</p>
                <button onclick="removeFromCart(${item.id})" style="color:red; border:none; background:none; cursor:pointer;">Remove</button>
            </div>
        </div>
    `).join('');

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    totalSpan.innerText = total.toFixed(2);
    countSpan.innerText = cart.reduce((sum, item) => sum + item.quantity, 0);
}

// Remove from Cart
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartUI();
}

// Search Function
function filterProducts() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const filtered = products.filter(p => p.name.toLowerCase().includes(query));
    displayProducts(filtered);
}

// Checkout (Mock)
let selectedPaymentMethod = 'Card';

// 1. Open the Modal
function checkout() {
    if (cart.length === 0) return alert("Cart is empty");
    if (!user.loggedIn) return alert("Please Login First!");

    // Show amount and open modal
    document.getElementById('pay-amount').innerText = document.getElementById('total-price').innerText;
    document.getElementById('payment-modal').style.display = 'flex';
}

// 2. Close Modal
function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// 3. Select Method
function selectPayment(method) {
    selectedPaymentMethod = method;
    // Visually update radio buttons
    if(method === 'Card') document.getElementById('p-card').checked = true;
    if(method === 'UPI') document.getElementById('p-upi').checked = true;
    if(method === 'Cash on Delivery') document.getElementById('p-cod').checked = true;
}

// 4. Send to Database
function confirmPayment() {
    const total = document.getElementById('total-price').innerText;
    const btn = document.querySelector('.pay-btn');
    btn.innerText = "Processing...";
    btn.disabled = true;

    fetch('place_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            total: total,
            items: cart,
            payment_method: selectedPaymentMethod // Sending the method!
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert("✅ Order Placed via " + selectedPaymentMethod);
            cart = [];
            updateCart();
            closePaymentModal();
            toggleCart();
        } else {
            alert("❌ Error: " + data.message);
        }
        btn.innerText = "Pay Now";
        btn.disabled = false;
    });
}

function openLogin() {
    // If you created login.html in the previous step:
    window.location.href = "login.html";
}
