<?php
include "db.php";

$success_message = "";
$error_message = "";

if (isset($_POST["generate_weekly"])) {

    $grade_group = $_POST["grade_group"];
    $topic = $_POST["topic"];
    $question_types = $_POST["question_types"] ?? [];
    $num_questions = (int)$_POST["num_questions"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

    if (empty($topic)) {
        $error_message = "Please select a topic.";
    } elseif (empty($question_types)) {
        $error_message = "Please select at least one question type.";
    } else {

        $data = [
            "grade_group" => $grade_group,
            "topic" => $topic,
            "question_types" => $question_types,
            "num_questions" => $num_questions,
            "start_date" => $start_date,
            "end_date" => $end_date
        ];

        $ch = curl_init("http://127.0.0.1:8000/generate-weekly-quiz");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        

        $result = json_decode($response, true);

        if (isset($result["status"]) && $result["status"] === "success") {
            $success_message = "Weekly Quiz Created Successfully! Quiz ID: " . $result["weekly_quiz_id"];
        } else {
            $error_message = "Failed to generate weekly quiz.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Weekly Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Generate Weekly Quiz</h2>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- Grade Group -->
        <div class="mb-3">
            <label class="form-label">Select Grade Group</label>
            <select id="grade_group" name="grade_group" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="3-5">3-5</option>
                <option value="6-7">6-7</option>
                <option value="8-10">8-10</option>
                <option value="11-12">11-12</option>
            </select>
        </div>

        <!-- Suggest Topics Button -->
        <button type="button" class="btn btn-primary mb-3" onclick="suggestTopics()">
            Suggest Topics
        </button>

        <!-- Topics Dropdown -->
        <div class="mb-3">
            <label class="form-label">Select Topic</label>
            <select id="topic" name="topic" class="form-select" required>
                <option value="">-- Click Suggest Topics First --</option>
            </select>
        </div>

        <!-- Question Types -->
        <div class="mb-3">
            <label class="form-label">Select Question Types</label><br>
            <input type="checkbox" name="question_types[]" value="MCQ"> MCQ<br>
            <input type="checkbox" name="question_types[]" value="TRUE_FALSE"> True/False<br>
            <input type="checkbox" name="question_types[]" value="FILL_BLANK"> Fill in the Blank<br>
            <input type="checkbox" name="question_types[]" value="MATCH"> Match the Following<br>
        </div>

        <!-- Number of Questions -->
        <div class="mb-3">
            <label class="form-label">Number of Questions</label>
            <input type="number" name="num_questions" class="form-control" min="1" max="50" required>
        </div>

        <!-- Start Date -->
        <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="datetime-local" name="start_date" class="form-control" required>
        </div>

        <!-- End Date -->
        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="datetime-local" name="end_date" class="form-control" required>
        </div>

        <button type="submit" name="generate_weekly" class="btn btn-success">
            Generate Weekly Quiz
        </button>

        <a href="index.php" class="btn btn-secondary">Back</a>

    </form>
</div>

<script>
function suggestTopics() {

    const gradeGroup = document.getElementById("grade_group").value;

    if (!gradeGroup) {
        alert("Please select grade group first.");
        return;
    }

    fetch("http://127.0.0.1:8000/suggest-weekly-topics", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            grade_group: gradeGroup
        })
    })
    .then(response => response.json())
    .then(data => {

        const topicDropdown = document.getElementById("topic");
        topicDropdown.innerHTML = "";

        if (data.topics) {
            data.topics.forEach(topic => {
                const option = document.createElement("option");
                option.value = topic;
                option.text = topic;
                topicDropdown.appendChild(option);
            });
        } else {
            alert("Failed to fetch topics.");
        }
    })
    .catch(error => {
        alert("Error connecting to AI service.");
    });
}
</script>

</body>
</html>