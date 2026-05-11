<?php
// ============================================================
//  /api/products.php  —  Product CRUD
//  Table: Product (product_id, product_name, category,
//                  price, stock_quantity)
// ============================================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: all products ─────────────────────────────────
    case 'GET':
        $result = $conn->query("
            SELECT * FROM Product
            ORDER BY product_id
        ");
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        sendJSON(['success' => true, 'data' => $products]);
        break;

    // ── POST: add new product ─────────────────────────────
    case 'POST':
        $body  = getBody();
        $name  = $conn->real_escape_string(trim($body['product_name'] ?? ''));
        $cat   = $conn->real_escape_string(trim($body['category'] ?? ''));
        $price = (float)($body['price'] ?? 0);
        $stock = (int)($body['stock_quantity'] ?? 0);

        if (!$name || !$cat || !$price) {
            sendJSON(['success' => false, 'error' => 'product_name, category, and price are required.'], 400);
        }

        $conn->query("
            INSERT INTO Product (product_name, category, price, stock_quantity)
            VALUES ('$name', '$cat', $price, $stock)
        ");
        sendJSON(['success' => true, 'id' => $conn->insert_id, 'message' => "Product '$name' added."]);
        break;

    // ── PUT: update product ───────────────────────────────
    case 'PUT':
        $body  = getBody();
        $id    = (int)($body['product_id'] ?? 0);
        $name  = $conn->real_escape_string(trim($body['product_name'] ?? ''));
        $cat   = $conn->real_escape_string(trim($body['category'] ?? ''));
        $price = (float)($body['price'] ?? 0);
        $stock = (int)($body['stock_quantity'] ?? 0);

        if (!$id || !$name) {
            sendJSON(['success' => false, 'error' => 'product_id and product_name are required.'], 400);
        }

        $conn->query("
            UPDATE Product
            SET product_name='$name', category='$cat',
                price=$price, stock_quantity=$stock
            WHERE product_id=$id
        ");
        sendJSON(['success' => true, 'message' => 'Product updated.']);
        break;

    // ── DELETE: remove product ────────────────────────────
    case 'DELETE':
        $body = getBody();
        $id   = (int)($body['product_id'] ?? $_GET['product_id'] ?? 0);

        if (!$id) {
            sendJSON(['success' => false, 'error' => 'product_id is required.'], 400);
        }

        // Check if product has purchases linked
        $check = $conn->query("SELECT COUNT(*) AS c FROM Purchase WHERE product_id=$id");
        $count = $check->fetch_assoc()['c'];
        if ($count > 0) {
            sendJSON(['success' => false, 'error' => "Cannot delete: $count purchase record(s) linked to this product."], 409);
        }

        $conn->query("DELETE FROM Product WHERE product_id=$id");
        sendJSON(['success' => true, 'message' => 'Product deleted.']);
        break;

    default:
        sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$conn->close();
?>