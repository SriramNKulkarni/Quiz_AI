<?php
include "db.php";

if (!isset($_GET["quiz_id"])) {
    header("Location: view_weekly_quizzes.php");
    exit;
}

$quiz_id = (int)$_GET["quiz_id"];

/* ==============================
   HANDLE POST ACTIONS
============================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* DELETE QUESTION */
    if (isset($_POST["delete_question_id"])) {

        $delete_id = (int)$_POST["delete_question_id"];

        $stmt = $conn->prepare("DELETE FROM weekly_quiz_questions WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();

        // Update total_questions
        $conn->query("
            UPDATE weekly_quizzes 
            SET total_questions = (
                SELECT COUNT(*) FROM weekly_quiz_questions 
                WHERE weekly_quiz_id = $quiz_id
            )
            WHERE id = $quiz_id
        ");

        header("Location: view_weekly_questions.php?quiz_id=" . $quiz_id);
        exit;
    }

    /* UPDATE QUESTION */
    if (isset($_POST["update_question_id"])) {

        $update_id = (int)$_POST["update_question_id"];

        $question_text = $_POST["question_text"];
        $question_type = $_POST["question_type"];
        $marks = (int)$_POST["marks"];
        $difficulty = $_POST["difficulty_level"];
        $correct_answer = $_POST["correct_answer"];

        $options_json = null;

        if ($question_type === "MCQ") {
            $options = $_POST["options"] ?? [];
            $options_json = json_encode($options);
        }

        if ($question_type === "MATCH") {
            $left = $_POST["match_left"] ?? [];
            $right = $_POST["match_right"] ?? [];
            $pairs = [];
            for ($i = 0; $i < count($left); $i++) {
                $pairs[] = ["left" => $left[$i], "right" => $right[$i]];
            }
            $options_json = json_encode($pairs);
        }

        $stmt = $conn->prepare("
            UPDATE weekly_quiz_questions
            SET question_text=?, question_type=?, options_json=?, 
                correct_answer=?, marks=?, difficulty_level=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssssisi",
            $question_text,
            $question_type,
            $options_json,
            $correct_answer,
            $marks,
            $difficulty,
            $update_id
        );

        $stmt->execute();

        header("Location: view_weekly_questions.php?quiz_id=" . $quiz_id);
        exit;
    }

    /* ADD QUESTION */
    if (isset($_POST["add_question"])) {

        $question_text = $_POST["question_text"];
        $question_type = $_POST["question_type"];
        $marks = (int)$_POST["marks"];
        $difficulty = $_POST["difficulty_level"];
        $correct_answer = $_POST["correct_answer"];

        $options_json = null;

        if ($question_type === "MCQ") {
            $options = $_POST["options"] ?? [];
            $options_json = json_encode($options);
        }

        if ($question_type === "MATCH") {
            $left = $_POST["match_left"] ?? [];
            $right = $_POST["match_right"] ?? [];
            $pairs = [];
            for ($i = 0; $i < count($left); $i++) {
                $pairs[] = ["left" => $left[$i], "right" => $right[$i]];
            }
            $options_json = json_encode($pairs);
        }

        $stmt = $conn->prepare("
            INSERT INTO weekly_quiz_questions
            (weekly_quiz_id, question_text, question_type, options_json, correct_answer, marks, difficulty_level)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issssis",
            $quiz_id,
            $question_text,
            $question_type,
            $options_json,
            $correct_answer,
            $marks,
            $difficulty
        );

        $stmt->execute();

        // Update total_questions
        $conn->query("
            UPDATE weekly_quizzes 
            SET total_questions = (
                SELECT COUNT(*) FROM weekly_quiz_questions 
                WHERE weekly_quiz_id = $quiz_id
            )
            WHERE id = $quiz_id
        ");

        header("Location: view_weekly_questions.php?quiz_id=" . $quiz_id);
        exit;
    }
}

/* ==============================
   FETCH QUIZ
============================== */
$stmt = $conn->prepare("SELECT * FROM weekly_quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    echo "Quiz not found.";
    exit;
}

/* ==============================
   FETCH QUESTIONS
============================== */
$stmt = $conn->prepare("
    SELECT * FROM weekly_quiz_questions
    WHERE weekly_quiz_id = ?
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Weekly Quiz Questions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<a href="view_weekly_quizzes.php" class="btn btn-secondary mb-4">Back</a>

<div class="card mb-4">
    <div class="card-body">
        <h3><?php echo htmlspecialchars($quiz["title"]); ?></h3>
        <p><strong>Topic:</strong> <?php echo htmlspecialchars($quiz["topic"]); ?></p>
        <p><strong>Total Questions:</strong> <?php echo $quiz["total_questions"]; ?></p>
    </div>
</div>

<h4>Questions</h4>

<?php
$index = 1;

while ($q = $questions->fetch_assoc()) {

$options = json_decode($q["options_json"], true);

echo "<div class='card mb-3'>";
echo "<div class='card-body'>";

echo "<h5>Q$index: " . htmlspecialchars($q["question_text"]) . "</h5>";
echo "<p><strong>Type:</strong> {$q["question_type"]}</p>";
echo "<p><strong>Marks:</strong> {$q["marks"]}</p>";
echo "<p><strong>Difficulty:</strong> {$q["difficulty_level"]}</p>";

if ($q["question_type"] === "MCQ" && !empty($options)) {
    echo "<ul>";
    foreach ($options as $opt) {
        echo "<li>" . htmlspecialchars($opt) . "</li>";
    }
    echo "</ul>";
}

if ($q["question_type"] === "MATCH" && !empty($options)) {
    echo "<ul>";
    foreach ($options as $pair) {
        echo "<li>" . htmlspecialchars($pair["left"]) . " → " . htmlspecialchars($pair["right"]) . "</li>";
    }
    echo "</ul>";
}

echo "<p><strong>Correct Answer:</strong> " . htmlspecialchars($q["correct_answer"]) . "</p>";

echo "<form method='POST' class='d-inline'>
<input type='hidden' name='delete_question_id' value='{$q["id"]}'>
<button type='submit' class='btn btn-danger btn-sm'>Delete</button>
</form> ";

echo "<button class='btn btn-primary btn-sm' onclick='toggleEdit({$q["id"]})'>Edit</button>";

echo "</div>";
echo "</div>";

/* EDIT FORM */
echo "
<div id='editForm{$q["id"]}' style='display:none;' class='card mb-4'>
<div class='card-body'>
<form method='POST'>
<input type='hidden' name='update_question_id' value='{$q["id"]}'>

<div class='mb-2'>
<label>Question Text</label>
<textarea name='question_text' class='form-control'>" . htmlspecialchars($q["question_text"]) . "</textarea>
</div>

<input type='hidden' name='question_type' value='{$q["question_type"]}'>

<div class='mb-2'>
<label>Marks</label>
<input type='number' name='marks' value='{$q["marks"]}' class='form-control'>
</div>

<div class='mb-2'>
<label>Difficulty</label>
<select name='difficulty_level' class='form-control'>
<option value='easy'>easy</option>
<option value='medium'>medium</option>
<option value='hard'>hard</option>
</select>
</div>

<div class='mb-2'>
<label>Correct Answer</label>
<input type='text' name='correct_answer' value='" . htmlspecialchars($q["correct_answer"]) . "' class='form-control'>
</div>

<button type='submit' class='btn btn-success'>Update</button>
</form>
</div>
</div>
";

$index++;
}
?>

<hr>

<h4>Add New Question</h4>

<form method="POST">
<input type="hidden" name="add_question" value="1">

<div class="mb-2">
<label>Question Text</label>
<textarea name="question_text" class="form-control"></textarea>
</div>

<div class="mb-2">
<label>Type</label>
<select name="question_type" class="form-control">
<option value="MCQ">MCQ</option>
<option value="TRUE_FALSE">TRUE_FALSE</option>
<option value="FILL_BLANK">FILL_BLANK</option>
<option value="MATCH">MATCH</option>
</select>
</div>

<div class="mb-2">
<label>Marks</label>
<input type="number" name="marks" value="1" class="form-control">
</div>

<div class="mb-2">
<label>Difficulty</label>
<select name="difficulty_level" class="form-control">
<option value="easy">easy</option>
<option value="medium">medium</option>
<option value="hard">hard</option>
</select>
</div>

<div class="mb-2">
<label>Correct Answer</label>
<input type="text" name="correct_answer" class="form-control">
</div>

<div class="mb-2">
<label>MCQ Options (if MCQ)</label>
<input type="text" name="options[]" class="form-control mb-1">
<input type="text" name="options[]" class="form-control mb-1">
<input type="text" name="options[]" class="form-control mb-1">
<input type="text" name="options[]" class="form-control mb-1">
</div>

<button type="submit" class="btn btn-success">Add Question</button>
</form>

</div>

<script>
function toggleEdit(id) {
    var form = document.getElementById("editForm" + id);
    form.style.display = form.style.display === "none" ? "block" : "none";
}
</script>

</body>
</html>