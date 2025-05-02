<?php
/**
 * Cart AJAX Handler
 * 
 * Handles AJAX requests for cart operations
 */

session_start();
include 'config.php';
include 'cart_functions.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to use the cart'
    ]);
    exit;
}

header('Content-Type: application/json');

// Get the action from request
$action = isset($_POST['action']) ? $_POST['action'] : '';
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate action
if (empty($action)) {
    echo json_encode([
        'success' => false,
        'message' => 'No action specified'
    ]);
    exit;
}

// Handle different actions
switch ($action) {
    case 'add':
        // Check required fields
        if ($product_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid product ID'
            ]);
            exit;
        }
        
        $result = addToCart($product_id, $quantity);
        echo json_encode($result);
        break;
        
    case 'update':
        // Check required fields
        if ($product_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid product ID'
            ]);
            exit;
        }
        
        $result = updateCartItem($product_id, $quantity);
        echo json_encode($result);
        break;
        
    case 'remove':
        // Check required fields
        if ($product_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid product ID'
            ]);
            exit;
        }
        
        $result = removeCartItem($product_id);
        echo json_encode($result);
        break;
        
    case 'clear':
        $result = clearCart();
        echo json_encode($result);
        break;
        
    case 'get':
        $cart = getCart();
        echo json_encode([
            'success' => true,
            'cart' => $cart
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}
?> 