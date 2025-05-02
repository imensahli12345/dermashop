<?php
/**
 * Cart Functions
 * 
 * Functions for managing the shopping cart using sessions
 */

// Initialize cart if it doesn't exist
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [],
            'total' => 0,
            'item_count' => 0
        ];
    }
}

// Add item to cart
function addToCart($product_id, $quantity = 1) {
    global $connexion;
    
    // Initialize cart if needed
    initCart();
    
    // Validate product exists and is in stock
    try {
        $stmt = $connexion->prepare("SELECT * FROM products WHERE id = ? AND stock >= ?");
        $stmt->execute([$product_id, $quantity]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not available or insufficient stock'
            ];
        }
        
        // Check if product already in cart
        $items = $_SESSION['cart']['items'];
        $found = false;
        
        foreach ($items as $key => $item) {
            if ($item['id'] == $product_id) {
                // Update quantity
                $new_quantity = $item['quantity'] + $quantity;
                
                // Check if new quantity exceeds stock
                if ($new_quantity > $product['stock']) {
                    return [
                        'success' => false,
                        'message' => 'Cannot add more of this product (stock limit reached)'
                    ];
                }
                
                $_SESSION['cart']['items'][$key]['quantity'] = $new_quantity;
                $_SESSION['cart']['items'][$key]['subtotal'] = number_format($new_quantity * $product['price'], 2);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Add new item to cart
            $_SESSION['cart']['items'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image' => $product['image'],
                'subtotal' => number_format($quantity * $product['price'], 2)
            ];
        }
        
        // Update cart totals
        updateCartTotals();
        
        return [
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart' => $_SESSION['cart']
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error adding product to cart: ' . $e->getMessage()
        ];
    }
}

// Update cart item quantity
function updateCartItem($product_id, $quantity) {
    global $connexion;
    
    // Initialize cart if needed
    initCart();
    
    // Validate quantity
    if ($quantity <= 0) {
        return removeCartItem($product_id);
    }
    
    // Validate product exists and is in stock
    try {
        $stmt = $connexion->prepare("SELECT * FROM products WHERE id = ? AND stock >= ?");
        $stmt->execute([$product_id, $quantity]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not available or insufficient stock'
            ];
        }
        
        // Update quantity
        $items = $_SESSION['cart']['items'];
        $found = false;
        
        foreach ($items as $key => $item) {
            if ($item['id'] == $product_id) {
                $_SESSION['cart']['items'][$key]['quantity'] = $quantity;
                $_SESSION['cart']['items'][$key]['subtotal'] = number_format($quantity * $product['price'], 2);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            return [
                'success' => false,
                'message' => 'Product not found in cart'
            ];
        }
        
        // Update cart totals
        updateCartTotals();
        
        return [
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart' => $_SESSION['cart']
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating cart: ' . $e->getMessage()
        ];
    }
}

// Remove item from cart
function removeCartItem($product_id) {
    // Initialize cart if needed
    initCart();
    
    $items = $_SESSION['cart']['items'];
    $found = false;
    
    foreach ($items as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['cart']['items'][$key]);
            $found = true;
            break;
        }
    }
    
    // Re-index the array
    $_SESSION['cart']['items'] = array_values($_SESSION['cart']['items']);
    
    if (!$found) {
        return [
            'success' => false,
            'message' => 'Product not found in cart'
        ];
    }
    
    // Update cart totals
    updateCartTotals();
    
    return [
        'success' => true,
        'message' => 'Product removed from cart',
        'cart' => $_SESSION['cart']
    ];
}

// Clear entire cart
function clearCart() {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0,
        'item_count' => 0
    ];
    
    return [
        'success' => true,
        'message' => 'Cart cleared successfully'
    ];
}

// Update cart totals
function updateCartTotals() {
    $total = 0;
    $item_count = 0;
    
    foreach ($_SESSION['cart']['items'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $item_count += $item['quantity'];
    }
    
    $_SESSION['cart']['total'] = number_format($total, 2);
    $_SESSION['cart']['item_count'] = $item_count;
}

// Get current cart
function getCart() {
    initCart();
    return $_SESSION['cart'];
}
?> 