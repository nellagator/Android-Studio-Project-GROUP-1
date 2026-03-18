<?php
$conn = new mysqli("localhost", "root", "", "loandb");
if ($conn->connect_error) die("DB Error");
$r = $conn->query("SHOW COLUMNS FROM students");
echo "<pre>";
while($row = $r->fetch_assoc()) {
    echo $row["Field"] . "\n";
}
echo "</pre>";
?>