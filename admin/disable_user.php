<?php
require_once('config.php');

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disable_user'])) {
    
    $telephone = trim($_POST['telephone'] ?? '');

    // Validation
    if (!preg_match('/^07[8923][0-9]{7}$/', $telephone)) {
        $error = "Invalid telephone number. Must match 07[8|9|2|3] + 7 digits.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE user SET account_type = 'D' WHERE telephone = :telephone");
            $stmt->execute(['telephone' => $telephone]);

            if ($stmt->rowCount() > 0) {
                $success = "User updated successfully!";
            } else {
                $error = "No user found with that telephone number.";
            }
        } catch (PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_user'])) {
    try {
            $stmt = $pdo->prepare("UPDATE user SET account_type = NULL WHERE telephone = :telephone");
            $stmt->execute(['telephone' => $_POST['enable_user']]);

            if ($stmt->rowCount() > 0) {
                $success = "User updated successfully!";
            } else {
                $error = "No user found with that telephone number.";
            }
        } catch (PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Disable a User</title>
</head>
<body>
    <h2>Disable a User</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="telephone">Telephone:</label>
        <input type="text" id="telephone" name="telephone" required pattern="07[8923][0-9]{7}" title="Must match 07[8|9|2|3] followed by 7 digits">
        <button type="submit" name="disable_user">Disable User</button>
    </form>
    <hr>
<h3>Disabled Users</h3>
<form method="post">
<?php
try {
    $stmt = $pdo->query("SELECT telephone FROM user WHERE account_type = 'D' ORDER BY telephone");
    $disabledUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($disabledUsers):
?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Telephone</th>
            </tr>
            <?php foreach ($disabledUsers as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['telephone']); ?></td>
                    <td><button name='enable_user' value='<?php echo htmlspecialchars($user['telephone']); ?>'>Enable</button></td>
                </tr>
            <?php endforeach; ?>
        </table>
<?php
    else:
        echo "<p>No disabled users found.</p>";
    endif;

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error fetching disabled users: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
</form>
</body>
</html>
