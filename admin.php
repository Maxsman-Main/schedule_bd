<?php
    session_start();
?>
<form method="POST" action="doLog.php">
    Введите логин
    <input name="login">
    Введите пароль
    <input name="pass">
    <input type="submit">
</form>