<?php
include "db.php";

/* ==============================
   HANDLE ACTIONS
============================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* TOGGLE STATUS (active <-> closed) */
    if (isset($_POST["toggle_status_id"])) {

        $quiz_id = (int)$_POST["toggle_status_id"];

        $stmt = $conn->prepare("
            UPDATE weekly_quizzes 
            SET status = IF(status='active','closed','active')
            WHERE id = ?
        ");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();

        header("Location: view_weekly_quizzes.php");
        exit;
    }

    /* DELETE QUIZ */
    if (isset($_POST["delete_quiz_id"])) {

        $quiz_id = (int)$_POST["delete_quiz_id"];

        /* Delete related questions first */
        $stmt = $conn->prepare("DELETE FROM weekly_quiz_questions WHERE weekly_quiz_id = ?");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();

        /* Then delete quiz */
        $stmt = $conn->prepare("DELETE FROM weekly_quizzes WHERE id = ?");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();

        header("Location: view_weekly_quizzes.php");
        exit;
    }
}

/* ==============================
   FETCH QUIZZES
============================== */
$result = $conn->query("SELECT * FROM weekly_quizzes ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Weekly Quizzes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2 class="mb-4">Weekly Quizzes</h2>

    <a href="index.php" class="btn btn-secondary mb-3">Back</a>

    <?php if ($result->num_rows > 0): ?>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Grade Group</th>
                    <th>Topic</th>
                    <th>Total Questions</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>View</th>
                    <th>Toggle</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo $row["grade_group"]; ?></td>
                    <td><?php echo htmlspecialchars($row["topic"]); ?></td>
                    <td><?php echo $row["total_questions"]; ?></td>
                    <td><?php echo $row["start_date"]; ?></td>
                    <td><?php echo $row["end_date"]; ?></td>

                    <!-- STATUS BADGE -->
                    <td>
                        <?php if ($row["status"] === "active"): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Closed</span>
                        <?php endif; ?>
                    </td>

                    <!-- VIEW QUESTIONS -->
                    <td>
                        <a href="view_weekly_questions.php?quiz_id=<?php echo $row["id"]; ?>" 
                           class="btn btn-sm btn-primary">
                           View
                        </a>
                    </td>

                    <!-- TOGGLE STATUS -->
                    <td>
                        <form method="POST">
                            <input type="hidden" name="toggle_status_id" value="<?php echo $row["id"]; ?>">
                            
                            <?php if ($row["status"] === "active"): ?>
                                <button type="submit" class="btn btn-sm btn-warning">
                                    Close
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-sm btn-success">
                                    Activate
                                </button>
                            <?php endif; ?>

                        </form>
                    </td>

                    <!-- DELETE QUIZ -->
                    <td>
                        <form method="POST" 
                              onsubmit="return confirmDelete(<?php echo $row['id']; ?>)">
                            <input type="hidden" name="delete_quiz_id" value="<?php echo $row["id"]; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>

                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>

    <?php else: ?>
        <div class="alert alert-info">No Weekly Quizzes Created Yet.</div>
    <?php endif; ?>

</div>

<script>
function confirmDelete(id) {
    return confirm("Are you sure you want to delete quiz ID " + id + " ?");
}
</script>

</body>
</html>