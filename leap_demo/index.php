<!DOCTYPE html>
<html>
<head>
    <title>LEAP AI Quiz Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Generate Academic Quiz</h2>

    <form method="POST" action="generate.php">

        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Grade</label>
                <input type="number" name="grade" class="form-control" required>
            </div>

            <div class="col">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>

            <div class="col">
                <label class="form-label">Chapter</label>
                <input type="text" name="chapter" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Difficulty</label>
            <select name="difficulty" class="form-select">
                <option value="Easy">Easy</option>
                <option value="Medium">Medium</option>
                <option value="Hard">Hard</option>
            </select>
        </div>

        <hr>

        <h5>Question Distribution</h5>

        <div class="row mb-3">
            <div class="col">
                <label>MCQ</label>
                <input type="number" name="mcq_count" class="form-control" placeholder="Enter Count">
            </div>

            <div class="col">
                <label>True / False</label>
                <input type="number" name="tf_count" class="form-control" placeholder="Enter Count">
            </div>

            <div class="col">
                <label>Fill in the Blank</label>
                <input type="number" name="fill_count" class="form-control" placeholder="Enter Count">
            </div>

            <div class="col">
                <label>Match the Following</label>
                <input type="number" name="match_count" class="form-control" placeholder="Enter Count">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Generate Quiz</button>
    </form>

    <!-- ================= Weekly Quiz Section ================= -->

   

    <hr class="my-5">

        <h2 class="mb-3">Weekly Quiz Management</h2>

            <a href="generate_weekly.php" class="btn btn-warning me-2">
                Generate Weekly Quiz
            </a>

            <a href="view_weekly_quizzes.php" class="btn btn-info me-2">
                View Weekly Quizzes
            </a>

            <a href="select_weekly_quiz.php" class="btn btn-success">
            Attempt Weekly Quiz
            </a>

    

</div>

</body>
</html>