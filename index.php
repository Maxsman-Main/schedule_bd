<?php
 
    session_start();

    include "database.php";

    $days = ["Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"];
    $times = ["8:30", "10:10", "11:50", "13:30", "15:10", "16:50", "18:30", "20:10"];
    $lessons = [];
    $groups = [];

    function get_lessons($conn, $group, $chet){
        $sql = "SELECT `element`.`element_id` AS `id`, `subject`.`name` AS `subject_name`, `time`.`start_time`, `time`.`end_time`, `room`.`number`, `teacher`.`name` AS `teacher_name`, `day`.`name` AS `day_name` FROM `element` 
            JOIN `subject` ON `element`.`subject_id` = `subject`.`subject_id`
            JOIN `time` ON `time`.`time_id` = `element`.`time_id`
            JOIN `room` ON `room`.`room_id` = `element`.`room_id`
            JOIN `teacher` ON `teacher`.`teacher_id` = `element`.`teacher_id`
            JOIN `day` ON `day`.`day_id` = `element`.`day_id`
            JOIN `study_group` ON `study_group`.`study_group_id` = `element`.`study_group_id`
            WHERE `study_group`.`study_group_id` = ".$group." AND `chet` = ".$chet;
        $result = $conn->query($sql);
        $lessons = [];
        while($row = $result->fetch_assoc()){
            array_push($lessons, $row);
        }
        return $lessons;
    }

    function get_study_groups($conn){
        $sql = "SELECT * FROM `study_group";
        $result = $conn->query($sql);
        $groups = [];
        while($row = $result->fetch_assoc()){
            array_push($groups, $row);
        }
        return $groups;
    }

    $groups = get_study_groups($conn);
    if($_POST["group"] && $_POST["chet"] == "Чет"){
        $lessons = get_lessons($conn, $_POST["group"], 1);
    }
    else if($_POST["group"] && $_POST["chet"] == "Нечет"){
        $lessons = get_lessons($conn, $_POST["group"], 2);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>schedule</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="#">
            <img src="img/schedule.svg" class="logo">
        </a>
        <form method="POST" action="index.php">
            <input type="submit" style="width:50px" value="Чет" name="chet">
            <input type="submit" style="width:50px" value="Нечет" name="chet">
            <input type='hidden' name='group' value=<?php echo($_POST["group"]) ?>>
        </form>
        <form method="POST" action="index.php">
            <select name="group" style="width:300px">
                <option value="">Выберите учебную группу</option>
                <?php 
                    foreach($groups as $group){
                        echo("<option value=".$group["study_group_id"].">".$group["name"]."</option>");
                    }
                ?>
            </select>
            <input type='hidden' name='chet' value=<?php echo($_POST["chet"]) ?>>
            <input type="submit" value="Выбрать">
        </form>
        <?php 
            if($_SESSION["login"] == "adm"){ 
                echo("<a href='./addPage.php' style='cursor:pointer;'>Админка</a>");
            }
        ?>
    </nav>

    <div style="display:flex; justify-content:center">
        <?php 
            if($_POST["group"]){
                echo("Ваша учебная группа: ".$groups[$_POST["group"] - 1]["name"]); 
            }
            else{
                echo("Ваша учебная группа: Не выбрана");
            }
        ?>
    </div>
    <div style="display:flex; justify-content:center">
        <?php echo("Тип недели: ".$_POST["chet"]); ?>
    </div>

    <div class="content">
        <div class="week">
            <?php
                for($i = 0; $i < 6; $i++){
            ?>
                    <div class="day">
                    <p class="day_name"><?php echo($days[$i]); ?></p>
                        <?php
                            for($j = 0; $j < 8; $j++){
                        ?>
                            <div class="pair">
                                <?php
                                    if($_SESSION["login"] == "adm"){
                                        foreach($lessons as $lesson){
                                            if($lesson['start_time'] == $times[$j] && $lesson['day_name'] == $days[$i]){
                                                echo("
                                                    <form method='POST' action='doDel.php'>
                                                        <input type='submit' value='del'>
                                                        <input type='hidden' name='id' value=".$lesson['id'].">
                                                    </form>
                                                ");
                                            }
                                        }
                                    }
                                ?>
                                <div class="up">
                                    <p class="time">
                                        <?php
                                            foreach($lessons as $lesson){
                                                if($lesson['start_time'] == $times[$j] && $lesson['day_name'] == $days[$i]){
                                                    echo($lesson['start_time']."-".$lesson['end_time']);
                                                }
                                            }
                                        ?>
                                    </p>

                                    <p class="room">
                                        <?php
                                            foreach($lessons as $lesson){
                                                if($lesson['start_time'] == $times[$j] && $lesson['day_name'] == $days[$i]){
                                                    echo($lesson['number']);
                                                }
                                            }
                                    ?>
                                    </p>
                                </div>

                                <div class="down">
                                    <p class="name">
                                        <?php
                                            foreach($lessons as $lesson){
                                                if($lesson['start_time'] == $times[$j] && $lesson['day_name'] == $days[$i]){
                                                    echo($lesson['subject_name']);
                                                }
                                            }
                                        ?>
                                    </p>

                                    <p class="teacher">
                                        <?php
                                            foreach($lessons as $lesson){
                                                if($lesson['start_time'] == $times[$j] && $lesson['day_name'] == $days[$i]){
                                                    echo($lesson['teacher_name']);
                                                    
                                                }
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php
                            }
                        ?>
                    </div>
            <?php
                }  
            ?>  
        </div>
    </div>    
</body>
</html>