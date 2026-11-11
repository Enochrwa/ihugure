<?php
require_once 'config.php';
// Replace with your DB config
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $questionTitle = $_POST['question'];
    $categoryId = $_POST['category'];
    $answers = $_POST['answers'];
    $correctIndex = (int)$_POST['correct'];

    // Insert into inquiry
    $stmt = $pdo->prepare("INSERT INTO inquiry (title, complexity) VALUES (?, 1)");
    $stmt->execute([$questionTitle]);
    $inquiryId = $pdo->lastInsertId();

    // Insert into inquirycategory
    $stmt = $pdo->prepare("INSERT INTO inquirycategory (inquiryid, categoryid) VALUES (?, ?)");
    $stmt->execute([$inquiryId, $categoryId]);

    // Insert into question
    $stmt = $pdo->prepare("INSERT INTO question (title, inquiryid, languageid) VALUES (?, ?, 3)");
    $stmt->execute([$questionTitle, $inquiryId]);
    $questionId = $pdo->lastInsertId();

    // Insert answers
    $stmt = $pdo->prepare("INSERT INTO answer (title, correct, inquiryid, questionid, languageid) VALUES (?, ?, ?, ?, 3)");
    foreach ($answers as $index => $answer) {
        $isCorrect = ($index === $correctIndex) ? 1 : 0;
        $stmt->execute([$answer, $isCorrect, $inquiryId, $questionId]);
    }

    echo "<div class='alert alert-success text-center'>Question and answers saved successfully.</div>";
}

// Fetch categories for the form
$categories = $pdo->query("SELECT id, title FROM category")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Question</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">Add New Question</h2>
  <form method="post" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Question</label>
      <input type="text" name="question" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Answers</label>
      <?php for ($i = 0; $i < 3; $i++): ?>
        <div class="input-group mb-2">
          <input type="text" name="answers[]" class="form-control" placeholder="Answer <?= $i + 1 ?>" required>
          <div class="input-group-text">
            <input type="radio" name="correct" value="<?= $i ?>" required>
            <span class="ms-2">Correct?</span>
          </div>
        </div>
      <?php endfor; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Category</label>
      <select name="category" class="form-select" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['id']) ?>">
            <?= htmlspecialchars($cat['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

