<?php
include "db.php";

$quiz_id = $_POST["quiz_id"];
$answers = $_POST["answers"] ?? [];

$score = 0;
$total = 0;

$stmt = $conn->prepare("
    SELECT * FROM weekly_quiz_questions
    WHERE weekly_quiz_id = ?
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

while ($q = $result->fetch_assoc()) {

    $total++;
    $question_id = $q["id"];
    $correct_answer = $q["correct_answer"];
    $student_answer = $answers[$question_id] ?? "";

    if (trim(strtolower($student_answer)) === trim(strtolower($correct_answer))) {
        $score++;
    }
}

/* Save attempt */
$stmt = $conn->prepare("
    INSERT INTO weekly_quiz_attempts (weekly_quiz_id, score, total)
    VALUES (?, ?, ?)
");
$stmt->bind_param("idi", $quiz_id, $score, $total);
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Weekly Quiz Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5 text-center">
    <h2>Quiz Result</h2>
    <div class="alert alert-info mt-4">
        <h4>Your Score: <?php echo "$score / $total"; ?></h4>
    </div>

    <a href="index.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</div>

</body>
</html>