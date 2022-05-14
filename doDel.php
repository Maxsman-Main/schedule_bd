<?php
    session_start();

    include 'database.php';

    $delID = $_POST["id"];

    $sql = "DELETE FROM `element` WHERE `element_id` = $delID";
    $conn->query($sql);

    header("Location: index.php");
?>