<?php 
if (isset($_POST['disable'])) {
    file_put_contents('../mode/maintenance', '0');
}
if (isset($_POST['enable'])) {
    file_put_contents('../mode/maintenance', '1');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Mode</title>

    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

    <h3 class="mb-4">Maintenance Mode</h3>

    <form method="post" class="d-flex gap-3">
        <button type="submit" name="enable" value="enable" class="btn btn-danger">
            Enable Maintenance Mode
        </button>

        <button type="submit" name="disable" value="disable" class="btn btn-success">
            Disable Maintenance Mode
        </button>
    </form>

</body>
</html>
