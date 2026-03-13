<?php
include "db.php";

$error_message = "";

if (isset($_POST["check_quiz"])) {

    $grade_group = $_POST["grade_group"];

    $stmt = $conn->prepare("
        SELECT id FROM weekly_quizzes
        WHERE grade_group = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param("s", $grade_group);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $quiz = $result->fetch_assoc();
        header("Location: attempt_weekly_quiz.php?quiz_id=" . $quiz["id"]);
        exit;
    } else {
        $error_message = "No active weekly quiz available for this grade group.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Weekly Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Select Grade Group</h2>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Grade Group</label>
            <select name="grade_group" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="3-5">3-5</option>
                <option value="6-7">6-7</option>
                <option value="8-10">8-10</option>
                <option value="11-12">11-12</option>
            </select>
        </div>

        <button type="submit" name="check_quiz" class="btn btn-primary">
            Proceed to Quiz
        </button>

        <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</div>

</body>
</html>