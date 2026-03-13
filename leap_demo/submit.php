<?php
include "db.php";

$quiz_id = $_POST["quiz_id"];
$selected_answers = $_POST["answers"] ?? [];
$match_answers = $_POST["match"] ?? [];

$score = 0;
$total = 0;

// Fetch questions
$result = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id");

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
    $total++; // count each question as 1 mark
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Quiz Result</h2>

<?php
foreach ($questions as $index => $q) {

    $question_id = $q["id"];
    $type = $q["question_type"];
    $correct_answer = $q["correct_answer"];

    $is_correct = false;
    $student_answer_display = "";

    echo "<div class='card mb-3 shadow-sm'>";
    echo "<div class='card-body'>";
    echo "<h5>Q" . ($index + 1) . ": " . $q["question_text"] . "</h5>";

    // ========================
    // MCQ + TRUE/FALSE
    // ========================
    if ($type === "mcq" || $type === "true_false") {

        $student_answer = $selected_answers[$question_id] ?? "Not Answered";
        $student_answer_display = $student_answer;

        if ($student_answer === $correct_answer) {
            $is_correct = true;
            $score++;
        }
    }

    // ========================
    // FILL IN BLANK
    // ========================
    if ($type === "fill_blank") {

        $student_answer = trim(strtolower($selected_answers[$question_id] ?? ""));
        $correct = trim(strtolower($correct_answer));

        $student_answer_display = $selected_answers[$question_id] ?? "Not Answered";

        if ($student_answer === $correct) {
            $is_correct = true;
            $score++;
        }
    }

    // ========================
    // MATCH (Partial Marks)
    // ========================
    if ($type === "match") {

        $pairs = $conn->query("SELECT * FROM match_pairs WHERE question_id = $question_id");

        $total_pairs = 0;
        $correct_pairs = 0;

        echo "<ul>";

        while ($pair = $pairs->fetch_assoc()) {

            $left = $pair["left_text"];
            $right = $pair["right_text"];

            $student_match = $match_answers[$question_id][$left] ?? "";

            $total_pairs++;

            if ($student_match === $right) {
                $correct_pairs++;
                echo "<li class='text-success'>✔ $left → $student_match</li>";
            } else {
                echo "<li class='text-danger'>✘ $left → $student_match (Correct: $right)</li>";
            }
        }

        echo "</ul>";

        // Partial scoring
        if ($total_pairs > 0) {
            $score += ($correct_pairs / $total_pairs);
        }

        echo "</div></div>";
        continue; // Skip normal answer display
    }

    // ========================
    // DISPLAY RESULT (Non-Match)
    // ========================

    if ($is_correct) {
        echo "<p class='text-success fw-bold'>✔ Your Answer: " . htmlspecialchars($student_answer_display) . "</p>";
    } else {
        echo "<p class='text-danger fw-bold'>✘ Your Answer: " . htmlspecialchars($student_answer_display) . "</p>";
        echo "<p class='text-success'>Correct Answer: " . htmlspecialchars($correct_answer) . "</p>";
    }

    echo "</div></div>";
}

// Save Attempt
$stmt = $conn->prepare("INSERT INTO attempts (quiz_id, score, total) VALUES (?, ?, ?)");
$stmt->bind_param("idi", $quiz_id, $score, $total);
$stmt->execute();
?>

    <div class="alert alert-info text-center mt-4">
        <h4>Your Final Score: <?php echo round($score, 2) . " / $total"; ?></h4>
    </div>

    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-primary">Generate New Quiz</a>
    </div>

</div>

</body>
</html>
