<?php
    session_start();

    include "database.php";

    $subject = $_POST["subject"];
    $time = $_POST["time"];
    $room = $_POST["room"];
    $teacher = $_POST["teacher"];
    $day = $_POST["day"];
    $group = $_POST["group"];
    $chet = $_POST["chet"];

    $sql = "INSERT INTO `element`(`subject_id`, `time_id`, `room_id`, `teacher_id`, `day_id`, `study_group_id`, `chet`) 
            VALUES('$subject', '$time', '$room', '$teacher', '$day', '$group', '$chet')";
    $conn->query($sql);

    header("Location: ./addPage.php");
?>