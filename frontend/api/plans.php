<?php
// ============================================================
//  /api/plans.php  —  Membership_Plan CRUD
//  Table: Membership_Plan (membership_id, plan_name,
//                          duration_months, price)
// ============================================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: fetch all plans ──────────────────────────────
    case 'GET':
        $result = $conn->query("
            SELECT 
                mp.*,
                COUNT(m.member_id) AS enrolled_members
            FROM Membership_Plan mp
            LEFT JOIN Member m ON mp.membership_id = m.membership_id
            GROUP BY mp.membership_id
            ORDER BY mp.membership_id
        ");
        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
        sendJSON(['success' => true, 'data' => $plans]);
        break;

    // ── POST: create new plan ─────────────────────────────
    case 'POST':
        $body = getBody();
        $name     = $conn->real_escape_string(trim($body['plan_name'] ?? ''));
        $duration = (int)($body['duration_months'] ?? 0);
        $price    = (float)($body['price'] ?? 0);

        if (!$name || !$duration || !$price) {
            sendJSON(['success' => false, 'error' => 'plan_name, duration_months, and price are required.'], 400);
        }

        $conn->query("
            INSERT INTO Membership_Plan (plan_name, duration_months, price)
            VALUES ('$name', $duration, $price)
        ");
        sendJSON(['success' => true, 'id' => $conn->insert_id, 'message' => "Plan '$name' created."]);
        break;

    // ── PUT: update existing plan ─────────────────────────
    case 'PUT':
        $body = getBody();
        $id       = (int)($body['membership_id'] ?? 0);
        $name     = $conn->real_escape_string(trim($body['plan_name'] ?? ''));
        $duration = (int)($body['duration_months'] ?? 0);
        $price    = (float)($body['price'] ?? 0);

        if (!$id || !$name) {
            sendJSON(['success' => false, 'error' => 'membership_id and plan_name are required.'], 400);
        }

        $conn->query("
            UPDATE Membership_Plan
            SET plan_name='$name', duration_months=$duration, price=$price
            WHERE membership_id=$id
        ");
        sendJSON(['success' => true, 'message' => "Plan updated."]);
        break;

    // ── DELETE: remove a plan ─────────────────────────────
    case 'DELETE':
        $body = getBody();
        $id   = (int)($body['membership_id'] ?? $_GET['membership_id'] ?? 0);

        if (!$id) {
            sendJSON(['success' => false, 'error' => 'membership_id is required.'], 400);
        }

        // Check if members are using this plan
        $check = $conn->query("SELECT COUNT(*) AS c FROM Member WHERE membership_id=$id");
        $count = $check->fetch_assoc()['c'];
        if ($count > 0) {
            sendJSON(['success' => false, 'error' => "Cannot delete: $count member(s) are on this plan."], 409);
        }

        $conn->query("DELETE FROM Membership_Plan WHERE membership_id=$id");
        sendJSON(['success' => true, 'message' => 'Plan deleted.']);
        break;

    default:
        sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$conn->close();
?>