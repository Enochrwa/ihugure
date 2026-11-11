<?php
require_once('config.php');

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

$searchEmail = trim($_GET['useremail'] ?? '');
$paymentStatus = trim($_GET['payment_status'] ?? '');

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($searchEmail !== '') {
    $where[] = "useremail LIKE :email";
    $params[':email'] = "%$searchEmail%";
}

if ($paymentStatus !== '') {
    $where[] = "payment_status = :payment_status";
    $params[':payment_status'] = $paymentStatus;
}

$whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";

// Count
$countSql = "SELECT COUNT(*) FROM game $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalGames = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalGames / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Fetch
$sql = "SELECT * FROM game $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

function pageUrl($page, $searchEmail, $paymentStatus) {
    return '?' . http_build_query([
        'useremail' => $searchEmail,
        'payment_status' => $paymentStatus,
        'page' => $page
    ]);
}

function getPaginationPages($currentPage, $totalPages) {
    $pages = [];

    $pages[] = 1;

    $start = max(2, $currentPage - 2);
    $end = min($totalPages - 1, $currentPage + 2);

    if ($start > 2) {
        $pages[] = '...';
    }

    for ($i = $start; $i <= $end; $i++) {
        $pages[] = $i;
    }

    if ($end < $totalPages - 1) {
        $pages[] = '...';
    }

    if ($totalPages > 1) {
        $pages[] = $totalPages;
    }

    return $pages;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Games List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/admin.css" rel="stylesheet" />
</head>
<body>
<div class="container my-5">
    <h1 class="mb-4">Games List</h1>

    <form method="get" class="mb-4 row g-2 align-items-center">
        <div class="col-auto">
            <input
                type="text"
                name="useremail"
                class="form-control"
                placeholder="Search by user email"
                value="<?= htmlspecialchars($searchEmail) ?>"
            >
        </div>

        <div class="col-auto">
            <select name="payment_status" class="form-select">
                <option value="">All payment statuses</option>
                <option value="pending" <?= $paymentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="failed" <?= $paymentStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                <option value="cancelled" <?= $paymentStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>

        <div class="col-auto">
            <a href="games.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>User Email</th>
            <th>Payment Status</th>
            <th>Amount Invested</th>
            <th>Details</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($games): ?>
            <?php foreach ($games as $game): ?>
                <tr>
                    <td><?= htmlspecialchars($game['id']) ?></td>
                    <td><?= htmlspecialchars($game['useremail']) ?></td>
                    <td><?= htmlspecialchars($game['payment_status']) ?></td>
                    <td><?= htmlspecialchars($game['amount_invested']) ?></td>
                    <td>
                        <a href="game_detail.php?id=<?= urlencode($game['id']) ?>" class="btn btn-sm btn-outline-primary">
                            View Details
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No games found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">

                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= pageUrl(max(1, $page - 1), $searchEmail, $paymentStatus) ?>">
                        Previous
                    </a>
                </li>

                <?php foreach (getPaginationPages($page, $totalPages) as $p): ?>
                    <?php if ($p === '...'): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php else: ?>
                        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= pageUrl($p, $searchEmail, $paymentStatus) ?>">
                                <?= $p ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= pageUrl(min($totalPages, $page + 1), $searchEmail, $paymentStatus) ?>">
                        Next
                    </a>
                </li>

            </ul>
        </nav>
    <?php endif; ?>
</div>
</body>
</html>