<?php
$host = "localhost";
$user = "root"; // ඔයාගේ DB username එක
$pass = "";     // ඔයාගේ DB password එක
$dbname = "nature_nest_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>