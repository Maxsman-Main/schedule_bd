<?php
    session_start();
?>
<div style="display:flex; align-items:center; justify-content:center;">
    <form method="POST" action="doLog.php" style="display:flex; flex-direction:column">
        Введите логин
        <input name="login">
        Введите пароль
        <input name="pass">
        <input type="submit">
    </form>
</div>