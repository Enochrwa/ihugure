<?php
// game_detail.php
require_once('config.php');

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

$gameId = $_GET['id'] ?? null;
if (!$gameId) {
    die('Game ID not provided.');
}

$stmt = $pdo->prepare("SELECT * FROM game WHERE id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$game) {
    die('Game not found.');
}

$sql = "
    SELECT
        q.id as question_id,
        q.title as question_title,
        a.id as answer_id,
        a.title as answer_title,
        a.correct as is_correct,
        gr.answerid as chosen_answer_id
    FROM game_response gr
    JOIN question q ON q.id = gr.questionid
    JOIN answer a ON a.inquiryid = q.inquiryid
    WHERE gr.gameid = ?
    ORDER BY q.id, a.id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$gameId]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$questions = [];
foreach ($responses as $row) {
    $qid = $row['question_id'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'title' => $row['question_title'],
            'answers' => [],
            'chosen_answer_id' => $row['chosen_answer_id']
        ];
    }
    $questions[$qid]['answers'][] = [
        'id' => $row['answer_id'],
        'title' => $row['answer_title'],
        'correct' => $row['is_correct']
    ];
}

$stmt = $pdo->prepare("SELECT * FROM game_logs WHERE gameid = ? ORDER BY id ASC");
$stmt->execute([$gameId]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Game Detail #<?= htmlspecialchars($gameId) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">

    <h1 class="mb-4">Game Detail (ID: <?= htmlspecialchars($gameId) ?>)</h1>
    <div class="mb-3">
        <strong>User Email:</strong> <?= htmlspecialchars($game['useremail']) ?><br>
        <strong>Payment Status:</strong> <?= htmlspecialchars($game['payment_status']) ?><br>
        <strong>Amount Invested:</strong> <?= htmlspecialchars($game['amount_invested']) ?>
    </div>

    <h2>Questions and Answers</h2>
    <?php foreach ($questions as $qid => $q): ?>
        <div class="mb-4">
            <h5><?= htmlspecialchars($q['title']) ?></h5>
            <ul class="list-group">
                <?php foreach ($q['answers'] as $answer):
                    $isChosen = ($answer['id'] == $q['chosen_answer_id']);
                    $isCorrect = ($answer['correct'] == 1);
                    $classes = "list-group-item d-flex justify-content-between align-items-center";
                    if ($isCorrect) {
                        $classes .= " list-group-item-success";
                    }
                    else if ($isChosen) {
                        $classes .= " list-group-item-danger";
                    }
                    ?>
                    <li class="<?= $classes ?>">
                        <?= htmlspecialchars($answer['title']) ?>
                        <span>
                            <?php if ($isCorrect): ?>
                                <span class="badge bg-success me-2">Correct</span>
                            <?php endif; ?>
                            <?php if ($isChosen): ?>
                                <span class="badge bg-primary">Chosen</span>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>

    <h2>Game Logs</h2>
    <?php if ($logs): ?>
        <ul class="list-group mb-4">
            <?php foreach ($logs as $log): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($log['event']) ?></strong>: <?= htmlspecialchars($log['info']) ?>(<?= htmlspecialchars($log['addtime']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No logs for this game.</p>
    <?php endif; ?>

    <a href="games.php" class="btn btn-secondary">Back to Games List</a>

</div>
</body>
</html>
