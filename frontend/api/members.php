<?php
// ============================================================
//  /api/members.php  —  Member CRUD
//  Table: Member (member_id, name, age, gender, phone,
//                 join_date, membership_id FK, trainer_id FK)
// ============================================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: all members with plan name + trainer name ────
    case 'GET':
        $result = $conn->query("
            SELECT 
                m.member_id,
                m.name,
                m.age,
                m.gender,
                m.phone,
                m.join_date,
                m.membership_id,
                m.trainer_id,
                mp.plan_name,
                mp.price        AS plan_price,
                t.trainer_name,
                t.specialization
            FROM Member m
            LEFT JOIN Membership_Plan mp ON m.membership_id = mp.membership_id
            LEFT JOIN Trainer t          ON m.trainer_id    = t.trainer_id
            ORDER BY m.member_id
        ");
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        sendJSON(['success' => true, 'data' => $members]);
        break;

    // ── POST: add new member ──────────────────────────────
    case 'POST':
        $body  = getBody();
        $name  = $conn->real_escape_string(trim($body['name'] ?? ''));
        $age   = (int)($body['age'] ?? 0);
        $gender= $conn->real_escape_string(trim($body['gender'] ?? ''));
        $phone = $conn->real_escape_string(trim($body['phone'] ?? ''));
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
    sendJSON([
        'success' => false,
        'error' => 'Phone number must be exactly 10 digits.'
    ], 400);
}
        $date  = $conn->real_escape_string(trim($body['join_date'] ?? date('Y-m-d')));
        $planId= (int)($body['membership_id'] ?? 0);
        $trnId = (int)($body['trainer_id'] ?? 0);

        if (!$name || !$age || !$gender || !$planId) {
            sendJSON(['success' => false, 'error' => 'name, age, gender, and membership_id are required.'], 400);
        }

        $trainerVal = $trnId ? $trnId : 'NULL';

        $conn->query("
            INSERT INTO Member (name, age, gender, phone, join_date, membership_id, trainer_id)
            VALUES ('$name', $age, '$gender', '$phone', '$date', $planId, $trainerVal)
        ");
        sendJSON(['success' => true, 'id' => $conn->insert_id, 'message' => "Member '$name' added."]);
        break;

    // ── PUT: update member ────────────────────────────────
    case 'PUT':
        $body  = getBody();
        $id    = (int)($body['member_id'] ?? 0);
        $name  = $conn->real_escape_string(trim($body['name'] ?? ''));
        $age   = (int)($body['age'] ?? 0);
        $gender= $conn->real_escape_string(trim($body['gender'] ?? ''));
        $phone = $conn->real_escape_string(trim($body['phone'] ?? ''));
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
    sendJSON([
        'success' => false,
        'error' => 'Phone number must be exactly 10 digits.'
    ], 400);
}
        $date  = $conn->real_escape_string(trim($body['join_date'] ?? ''));
        $planId= (int)($body['membership_id'] ?? 0);
        $trnId = (int)($body['trainer_id'] ?? 0);

        if (!$id || !$name) {
            sendJSON(['success' => false, 'error' => 'member_id and name are required.'], 400);
        }

        $trainerVal = $trnId ? $trnId : 'NULL';

        $conn->query("
            UPDATE Member
            SET name='$name', age=$age, gender='$gender', phone='$phone',
                join_date='$date', membership_id=$planId, trainer_id=$trainerVal
            WHERE member_id=$id
        ");
        sendJSON(['success' => true, 'message' => "Member updated."]);
        break;

    // ── DELETE: remove member ─────────────────────────────
    case 'DELETE':
        $body = getBody();
        $id   = (int)($body['member_id'] ?? $_GET['member_id'] ?? 0);

        if (!$id) {
            sendJSON(['success' => false, 'error' => 'member_id is required.'], 400);
        }

        // Delete member's purchases first (FK constraint)
        $conn->query("DELETE FROM Purchase WHERE member_id=$id");
        $conn->query("DELETE FROM Member WHERE member_id=$id");

        sendJSON(['success' => true, 'message' => 'Member and their purchases deleted.']);
        break;

    default:
        sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$conn->close();
?>