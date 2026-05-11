<?php
// ============================================================
//  /api/dashboard.php  —  All stats for the dashboard page
//  Uses the exact SQL queries from gym_management.sql
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

// ── Total members ─────────────────────────────────────────
$r = $conn->query("SELECT COUNT(*) AS total_members FROM Member");
$totalMembers = (int)$r->fetch_assoc()['total_members'];

// ── Total trainers ────────────────────────────────────────
$r = $conn->query("SELECT COUNT(*) AS c FROM Trainer");
$totalTrainers = (int)$r->fetch_assoc()['c'];

// ── Total plans ───────────────────────────────────────────
$r = $conn->query("SELECT COUNT(*) AS c FROM Membership_Plan");
$totalPlans = (int)$r->fetch_assoc()['c'];

// ── Total products ────────────────────────────────────────
$r = $conn->query("SELECT COUNT(*) AS c FROM Product");
$totalProducts = (int)$r->fetch_assoc()['c'];

// ── Total purchases ───────────────────────────────────────
$r = $conn->query("SELECT COUNT(*) AS c FROM Purchase");
$totalPurchases = (int)$r->fetch_assoc()['c'];

// ── Total revenue ─────────────────────────────────────────
$r = $conn->query("SELECT SUM(total_price) AS total_revenue FROM Purchase");
$totalRevenue = (float)($r->fetch_assoc()['total_revenue'] ?? 0);

// ── Highest product price ─────────────────────────────────
$r = $conn->query("SELECT MAX(price) AS highest_price FROM Product");
$highestPrice = (float)($r->fetch_assoc()['highest_price'] ?? 0);

// ── Average product price ─────────────────────────────────
$r = $conn->query("SELECT AVG(price) AS average_price FROM Product");
$avgPrice = round((float)($r->fetch_assoc()['average_price'] ?? 0), 2);

// ── Low stock products (< 10) ─────────────────────────────
$r = $conn->query("SELECT * FROM Product WHERE stock_quantity < 10");
$lowStock = [];
while ($row = $r->fetch_assoc()) $lowStock[] = $row;

// ── Plan distribution (members per plan) ─────────────────
$r = $conn->query("
    SELECT mp.plan_name, COUNT(m.member_id) AS member_count
    FROM Membership_Plan mp
    LEFT JOIN Member m ON mp.membership_id = m.membership_id
    GROUP BY mp.membership_id
    ORDER BY mp.membership_id
");
$planDistribution = [];
while ($row = $r->fetch_assoc()) $planDistribution[] = $row;

// ── Recent members (last 5) ───────────────────────────────
$r = $conn->query("
    SELECT m.name, mp.plan_name, m.join_date
    FROM Member m
    LEFT JOIN Membership_Plan mp ON m.membership_id = mp.membership_id
    ORDER BY m.member_id DESC
    LIMIT 5
");
$recentMembers = [];
while ($row = $r->fetch_assoc()) $recentMembers[] = $row;

// ── Recent purchases (last 5) ────────────────────────────
$r = $conn->query("
    SELECT m.name AS member_name, p.product_name, pu.total_price, pu.purchase_date
    FROM Purchase pu
    JOIN Member  m ON pu.member_id  = m.member_id
    JOIN Product p ON pu.product_id = p.product_id
    ORDER BY pu.purchase_id DESC
    LIMIT 5
");
$recentPurchases = [];
while ($row = $r->fetch_assoc()) $recentPurchases[] = $row;

// ── Products by category ──────────────────────────────────
$r = $conn->query("
    SELECT category, COUNT(*) AS total_products
    FROM Product
    GROUP BY category
");
$categoryBreakdown = [];
while ($row = $r->fetch_assoc()) $categoryBreakdown[] = $row;

// ── Trainers with salary > 25000 ─────────────────────────
$r = $conn->query("SELECT * FROM Trainer WHERE salary > 25000");
$highSalaryTrainers = [];
while ($row = $r->fetch_assoc()) $highSalaryTrainers[] = $row;

// ── Send everything ───────────────────────────────────────
sendJSON([
    'success' => true,
    'stats' => [
        'total_members'   => $totalMembers,
        'total_trainers'  => $totalTrainers,
        'total_plans'     => $totalPlans,
        'total_products'  => $totalProducts,
        'total_purchases' => $totalPurchases,
        'total_revenue'   => $totalRevenue,
        'highest_price'   => $highestPrice,
        'avg_price'       => $avgPrice,
    ],
    'low_stock'           => $lowStock,
    'plan_distribution'   => $planDistribution,
    'recent_members'      => $recentMembers,
    'recent_purchases'    => $recentPurchases,
    'category_breakdown'  => $categoryBreakdown,
    'high_salary_trainers'=> $highSalaryTrainers,
]);

$conn->close();
?>