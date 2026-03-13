<?php
include "db.php";

if (!isset($_GET["quiz_id"])) {
    header("Location: index.php");
    exit;
}

$quiz_id = (int)$_GET["quiz_id"];

$result = $conn->prepare("
    SELECT * FROM weekly_quiz_questions
    WHERE weekly_quiz_id = ?
");
$result->bind_param("i", $quiz_id);
$result->execute();
$questions = $result->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Weekly Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Weekly Quiz</h2>

    <form method="POST" action="submit_weekly.php">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

<?php
$index = 1;

while ($question = $questions->fetch_assoc()) {

    $type = $question["question_type"];
    $question_id = $question["id"];
    $options = json_decode($question["options_json"], true);

    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5>Q$index: " . $question["question_text"] . "</h5>";

    // MCQ
    if ($type === "MCQ") {
        foreach ($options as $opt) {
            echo "<div class='form-check'>";
            echo "<input class='form-check-input' type='radio' name='answers[$question_id]' value='" . htmlspecialchars($opt) . "' required>";
            echo "<label class='form-check-label'>" . htmlspecialchars($opt) . "</label>";
            echo "</div>";
        }
    }

    // TRUE/FALSE
    if ($type === "TRUE_FALSE") {
        echo "<div class='form-check'>";
        echo "<input class='form-check-input' type='radio' name='answers[$question_id]' value='True' required>";
        echo "<label class='form-check-label'>True</label>";
        echo "</div>";

        echo "<div class='form-check'>";
        echo "<input class='form-check-input' type='radio' name='answers[$question_id]' value='False' required>";
        echo "<label class='form-check-label'>False</label>";
        echo "</div>";
    }

    // FILL BLANK
    if ($type === "FILL_BLANK") {
        echo "<input type='text' name='answers[$question_id]' class='form-control' required>";
    }

    echo "</div>";
    echo "</div>";

    $index++;
}
?>

        <button type="submit" class="btn btn-success">Submit Quiz</button>
    </form>
</div>

</body>
</html>