<?php
    session_start();

    if($_POST['pass'] == 1 && $_POST["login"] == "adm"){
        $_SESSION["login"] = "adm";
    }
    else{
        $_SESSION["login"] = "nonAdm";
    }

    header("Location: index.php");
?>