<?php
// ============================================================
//  /api/trainers.php  —  Trainer CRUD
//  Table: Trainer (trainer_id, trainer_name, specialization,
//                  phone, salary)
// ============================================================
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: all trainers with their member count ─────────
    case 'GET':
        $result = $conn->query("
            SELECT 
                t.*,
                COUNT(m.member_id) AS member_count
            FROM Trainer t
            LEFT JOIN Member m ON t.trainer_id = m.trainer_id
            GROUP BY t.trainer_id
            ORDER BY t.trainer_id
        ");
        $trainers = [];
        while ($row = $result->fetch_assoc()) {
            $trainers[] = $row;
        }
        sendJSON(['success' => true, 'data' => $trainers]);
        break;

    // ── POST: add new trainer ─────────────────────────────
    case 'POST':
        $body  = getBody();
        $name  = $conn->real_escape_string(trim($body['trainer_name'] ?? ''));
        $spec  = $conn->real_escape_string(trim($body['specialization'] ?? ''));
        $phone = $conn->real_escape_string(trim($body['phone'] ?? ''));
        $sal   = (float)($body['salary'] ?? 0);

        if (!$name || !$spec) {
            sendJSON(['success' => false, 'error' => 'trainer_name and specialization are required.'], 400);
        }

        $conn->query("
            INSERT INTO Trainer (trainer_name, specialization, phone, salary)
            VALUES ('$name', '$spec', '$phone', $sal)
        ");
        sendJSON(['success' => true, 'id' => $conn->insert_id, 'message' => "Trainer '$name' added."]);
        break;

    // ── PUT: update trainer ───────────────────────────────
    case 'PUT':
        $body  = getBody();
        $id    = (int)($body['trainer_id'] ?? 0);
        $name  = $conn->real_escape_string(trim($body['trainer_name'] ?? ''));
        $spec  = $conn->real_escape_string(trim($body['specialization'] ?? ''));
        $phone = $conn->real_escape_string(trim($body['phone'] ?? ''));
        $sal   = (float)($body['salary'] ?? 0);

        if (!$id || !$name) {
            sendJSON(['success' => false, 'error' => 'trainer_id and trainer_name are required.'], 400);
        }

        $conn->query("
            UPDATE Trainer
            SET trainer_name='$name', specialization='$spec', phone='$phone', salary=$sal
            WHERE trainer_id=$id
        ");
        sendJSON(['success' => true, 'message' => "Trainer updated."]);
        break;

    // ── DELETE: remove trainer ────────────────────────────
    case 'DELETE':
        $body = getBody();
        $id   = (int)($body['trainer_id'] ?? $_GET['trainer_id'] ?? 0);

        if (!$id) {
            sendJSON(['success' => false, 'error' => 'trainer_id is required.'], 400);
        }

        // Check if members are assigned to this trainer
        $check = $conn->query("SELECT COUNT(*) AS c FROM Member WHERE trainer_id=$id");
        $count = $check->fetch_assoc()['c'];
        if ($count > 0) {
            sendJSON(['success' => false, 'error' => "Cannot delete: $count member(s) assigned to this trainer."], 409);
        }

        $conn->query("DELETE FROM Trainer WHERE trainer_id=$id");
        sendJSON(['success' => true, 'message' => 'Trainer deleted.']);
        break;

    default:
        sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$conn->close();
?>