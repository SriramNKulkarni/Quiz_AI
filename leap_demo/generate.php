<?php
include "db.php";

/* Prepare data for FastAPI */
$data = [
    "grade" => (int)$_POST["grade"],
    "subject" => $_POST["subject"],
    "chapter" => $_POST["chapter"],
    "difficulty" => $_POST["difficulty"],
    "mcq_count" => (int)$_POST["mcq_count"],
    "tf_count" => (int)$_POST["tf_count"],
    "fill_count" => (int)$_POST["fill_count"],
    "match_count" => (int)$_POST["match_count"]
];

/* Call FastAPI */
$ch = curl_init("http://127.0.0.1:8000/generate-quiz");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$result = json_decode($response, true);

if (!$result) {
    echo "<div class='alert alert-danger'>
            AI service unavailable. Please try again later.
          </div>";
    exit;
}

if ($result["status"] !== "success") {
    echo "<div class='container mt-5'>";
    echo "<div class='alert alert-danger'>";
    echo "<strong>Error:</strong> " . htmlspecialchars($result["message"] ?? "Unknown error.");
    echo "</div>";
    echo "<a href='index.php' class='btn btn-primary'>Go Back</a>";
    echo "</div>";
    exit;
}


/* Insert Quiz */
$stmt = $conn->prepare("INSERT INTO quizzes (grade, subject, chapter) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $data["grade"], $data["subject"], $data["chapter"]);
$stmt->execute();
$quiz_id = $stmt->insert_id;


/* Insert Questions */
foreach ($result["quiz"]["questions"] as $q) {

    $type = $q["type"] ?? "";
    $correct_answer = $q["correct_answer"] ?? null;

    $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, correct_answer, question_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $quiz_id, $q["question"], $correct_answer, $type);
    $stmt->execute();
    $question_id = $stmt->insert_id;


    /* ================= MCQ ================= */
    if ($type === "mcq" && isset($q["options"])) {

        foreach ($q["options"] as $option) {

            $stmt = $conn->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
            $stmt->bind_param("is", $question_id, $option);
            $stmt->execute();

        }

    }


    /* ================= MATCH ================= */
    if ($type === "match" && isset($q["pairs"])) {

        foreach ($q["pairs"] as $pair) {

            $left = $pair["left"] ?? "";
            $right = $pair["right"] ?? "";

            $stmt = $conn->prepare("INSERT INTO match_pairs (question_id, left_text, right_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $question_id, $left, $right);
            $stmt->execute();

        }

    }

}
?>

<!DOCTYPE html>

<html>
<head>
    <title>Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Quiz</h2>

```
<form method="POST" action="submit.php">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
```

<?php
$result_q = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id");
$index = 1;

while ($question = $result_q->fetch_assoc()) {

    $type = $question["question_type"];
    $question_id = $question["id"];

    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5>Q$index: " . htmlspecialchars($question["question_text"]) . "</h5>";

    /* ================= MCQ ================= */
    if ($type === "mcq") {

        $options = $conn->query("SELECT option_text FROM options WHERE question_id = $question_id");

        while ($opt = $options->fetch_assoc()) {

            echo "<div class='form-check'>";
            echo "<input class='form-check-input' type='radio' name='answers[$question_id]' value='" . htmlspecialchars($opt["option_text"]) . "' required>";
            echo "<label class='form-check-label'>" . htmlspecialchars($opt["option_text"]) . "</label>";
            echo "</div>";

        }

    }

    /* ================= TRUE/FALSE ================= */
    if ($type === "true_false") {

        echo "<div class='form-check'>";
        echo "<input class='form-check-input' type='radio' name='answers[$question_id]' value='True' required>";
        echo "<label class='form-check-label'>True</label>";
        echo "</div>";

        echo "<div class='form-check'>";
        echo "<input class='form-check-input' type='radio' name='answers[$question_id]' value='False' required>";
        echo "<label class='form-check-label'>False</label>";
        echo "</div>";

    }

    /* ================= FILL BLANK ================= */
    if ($type === "fill_blank") {

        echo "<input type='text' name='answers[$question_id]' class='form-control' required>";

    }

    /* ================= MATCH ================= */
    if ($type === "match") {

        $pairs = $conn->query("SELECT * FROM match_pairs WHERE question_id = $question_id");

        $rights = [];

        while ($pair = $pairs->fetch_assoc()) {
            $rights[] = $pair["right_text"];
        }

        shuffle($rights);

        $pairs = $conn->query("SELECT * FROM match_pairs WHERE question_id = $question_id");

        while ($pair = $pairs->fetch_assoc()) {

            echo "<div class='row mb-2'>";

            echo "<div class='col-6'>";
            echo htmlspecialchars($pair["left_text"]);
            echo "</div>";

            echo "<div class='col-6'>";
            echo "<select name='match[$question_id][" . htmlspecialchars($pair["left_text"]) . "]' class='form-select'>";

            foreach ($rights as $right) {
                echo "<option value='" . htmlspecialchars($right) . "'>" . htmlspecialchars($right) . "</option>";
            }

            echo "</select>";
            echo "</div>";

            echo "</div>";

        }

    }

    echo "</div>";
    echo "</div>";

    $index++;

}
?>

```
    <button type="submit" class="btn btn-success">Submit Quiz</button>
</form>
```

</div>

</body>
</html>
