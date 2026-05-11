<?php
// ============================================================
//  /api/purchases.php  —  Purchase CRUD
//  Table: Purchase (purchase_id, member_id FK, product_id FK,
//                   quantity, total_price, purchase_date)
// ============================================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: all purchases with member + product details ──
    case 'GET':
        $result = $conn->query("
            SELECT
                pu.purchase_id,
                pu.member_id,
                pu.product_id,
                pu.quantity,
                pu.total_price,
                pu.purchase_date,
                m.name          AS member_name,
                p.product_name,
                p.category,
                p.price         AS unit_price
            FROM Purchase pu
            JOIN Member  m ON pu.member_id  = m.member_id
            JOIN Product p ON pu.product_id = p.product_id
            ORDER BY pu.purchase_id DESC
        ");
        $purchases = [];
        while ($row = $result->fetch_assoc()) {
            $purchases[] = $row;
        }

        // Also send revenue summary
        $rev = $conn->query("SELECT SUM(total_price) AS total_revenue, COUNT(*) AS total_purchases FROM Purchase");
        $summary = $rev->fetch_assoc();

        sendJSON([
            'success'  => true,
            'data'     => $purchases,
            'summary'  => $summary,
        ]);
        break;

    // ── POST: record a new purchase ───────────────────────
    case 'POST':
        $body      = getBody();
        $memberId  = (int)($body['member_id']  ?? 0);
        $productId = (int)($body['product_id'] ?? 0);
        $qty       = (int)($body['quantity']   ?? 0);
        $date      = $conn->real_escape_string(trim($body['purchase_date'] ?? date('Y-m-d')));

        if (!$memberId || !$productId || !$qty) {
            sendJSON(['success' => false, 'error' => 'member_id, product_id, and quantity are required.'], 400);
        }

        // Get product price and current stock from DB
        $prodResult = $conn->query("SELECT price, stock_quantity, product_name FROM Product WHERE product_id=$productId");
        if ($prodResult->num_rows === 0) {
            sendJSON(['success' => false, 'error' => 'Product not found.'], 404);
        }
        $product = $prodResult->fetch_assoc();
        $unitPrice = (float)$product['price'];
        $stock     = (int)$product['stock_quantity'];

        // Check stock availability
        if ($qty > $stock) {
            sendJSON(['success' => false, 'error' => "Only $stock unit(s) in stock for '{$product['product_name']}'."], 409);
        }

        $totalPrice = $unitPrice * $qty;

        // Insert purchase
        $conn->query("
            INSERT INTO Purchase (member_id, product_id, quantity, total_price, purchase_date)
            VALUES ($memberId, $productId, $qty, $totalPrice, '$date')
        ");
        $purchaseId = $conn->insert_id;

        // Deduct stock
        $conn->query("
            UPDATE Product
            SET stock_quantity = stock_quantity - $qty
            WHERE product_id = $productId
        ");

        sendJSON([
            'success'     => true,
            'id'          => $purchaseId,
            'total_price' => $totalPrice,
            'message'     => "Purchase recorded. Total: ₹$totalPrice",
        ]);
        break;

    // ── DELETE: remove a purchase record ──────────────────
    case 'DELETE':
        $body = getBody();
        $id   = (int)($body['purchase_id'] ?? $_GET['purchase_id'] ?? 0);

        if (!$id) {
            sendJSON(['success' => false, 'error' => 'purchase_id is required.'], 400);
        }

        // Restore stock before deleting
        $p = $conn->query("SELECT product_id, quantity FROM Purchase WHERE purchase_id=$id");
        if ($p->num_rows > 0) {
            $row = $p->fetch_assoc();
            $conn->query("
                UPDATE Product
                SET stock_quantity = stock_quantity + {$row['quantity']}
                WHERE product_id = {$row['product_id']}
            ");
        }

        $conn->query("DELETE FROM Purchase WHERE purchase_id=$id");
        sendJSON(['success' => true, 'message' => 'Purchase deleted and stock restored.']);
        break;

    default:
        sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$conn->close();
?>