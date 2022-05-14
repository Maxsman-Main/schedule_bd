<?php
    $servername = "localhost";
    $username = "root";
    $password = "1234";
    $db = "fin_lab";
    $conn = mysqli_connect($servername, $username, $password, $db);
    $conn -> set_charset("utf8");
?>