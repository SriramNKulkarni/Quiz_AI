<?php
$conn = new mysqli("localhost", "root", "S148VtvAj8GhK!R", "leap_demo");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
