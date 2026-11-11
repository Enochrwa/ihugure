<?php
require_once('config.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $telephone = trim($_GET['telephone'] ?? '');

    if ($telephone === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Telephone not provided.'
        ]);
        exit;
    }

    // Get last 3 games by telephone
    $stmt = $pdo->prepare("
        SELECT *
        FROM game
        WHERE useremail = ?
        ORDER BY id DESC
        LIMIT 3
    ");
    $stmt->execute([$telephone]);
    $games = $stmt->fetchAll();

    if (!$games) {
        echo json_encode([
            'success' => false,
            'message' => 'No games found for this telephone.',
            'telephone' => $telephone,
            'games' => []
        ]);
        exit;
    }

    $resultGames = [];

    foreach ($games as $game) {
        $gameId = $game['id'];

        // Questions and answers
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
        $responses = $stmt->fetchAll();

        $questions = [];

        foreach ($responses as $row) {
            $qid = $row['question_id'];

            if (!isset($questions[$qid])) {
                $questions[$qid] = [
                    'id' => (int)$qid,
                    'title' => $row['question_title'],
                    'chosen_answer_id' => $row['chosen_answer_id'] ? (int)$row['chosen_answer_id'] : null,
                    'answers' => []
                ];
            }

            $questions[$qid]['answers'][] = [
                'id' => (int)$row['answer_id'],
                'title' => $row['answer_title'],
                'correct' => (bool)$row['is_correct'],
                'chosen' => ((int)$row['answer_id'] === (int)$row['chosen_answer_id'])
            ];
        }

        // Logs
        $stmt = $pdo->prepare("
            SELECT concat(event, ':', info) as event_info, addtime as time_occured
            FROM game_logs
            WHERE gameid = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$gameId]);
        $logs = $stmt->fetchAll();

        $resultGames[] = [
            'id' => (int)$game['id'],
            'useremail' => $game['useremail'] ?? null,
            'telephone' => $game['telephone'] ?? null,
            'payment_status' => $game['payment_status'] ?? null,
            'amount_invested' => $game['amount_invested'] ?? null,
            'questions' => array_values($questions),
            'logs' => $logs
        ];
    }

    echo json_encode([
        'success' => true,
        'telephone' => $telephone,
        'count' => count($resultGames),
        'games' => $resultGames
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Server error.',
        'error' => $e->getMessage()
    ]);
}