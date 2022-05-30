<?php
    session_start();

    include "database.php";

    $days = [];
    $times = [];
    $teachers = [];
    $groups = [];
    $subjects = [];
    $rooms = [];

    function get_days($conn){
        $sql = "SELECT * FROM `day`";
        $result = $conn->query($sql);
        $days = [];
        while($row = $result->fetch_assoc()){
            array_push($days, $row);
        }
        return $days;
    }

    function get_times($conn){
        $sql = "SELECT * FROM `time`";
        $result = $conn->query($sql);
        $times = [];
        while($row = $result->fetch_assoc()){
            array_push($times, $row);
        }
        return $times;
    }

    function get_teachers($conn, $day, $time, $chet){
        $sql = "SELECT * FROM `teacher`
                WHERE `teacher`.`teacher_id` NOT IN
                (
                    SELECT `teacher`.`teacher_id` FROM `teacher` 
                    JOIN `element` ON `element`.`teacher_id` = `teacher`.`teacher_id`
                    JOIN `day` ON `day`.`day_id` = `element`.`day_id`
                    JOIN `time` ON `time`.`time_id` = `element`.`time_id`
                    WHERE `day`.`day_id` = $day AND `time`.`time_id` = $time AND `chet` = $chet
                )
                AND `teacher`.`teacher_id` NOT IN
                (
                    SELECT `teacher_id` FROM
                        (
                        SELECT `teacher_id`, COUNT(*) AS `work_days` FROM `element` 
                        WHERE `day_id` = $day AND `chet` = $chet
                        GROUP BY `teacher_id`
                        ) 
                    AS `works` 
                    WHERE `works`.`work_days` >= 5
                )";
        $result = $conn->query($sql);
        $teachers = [];
        while($row = $result->fetch_assoc()){
            array_push($teachers, $row);
        }
        return $teachers;
    }

    function get_groups($conn, $day, $time, $chet){
        $sql = "SELECT * FROM `study_group`
                WHERE `study_group`.`study_group_id` NOT IN
                (
                    SELECT `study_group`.`study_group_id` FROM `study_group` 
                    JOIN `element` ON `element`.`study_group_id` = `study_group`.`study_group_id`
                    JOIN `day` ON `day`.`day_id` = `element`.`day_id`
                    JOIN `time` ON `time`.`time_id` = `element`.`time_id`
                    WHERE `day`.`day_id` = $day AND `time`.`time_id` = $time AND `chet` = $chet
                )
                AND `study_group`.`study_group_id` NOT IN
                (
                    SELECT `study_group_id` FROM
                        (
                        SELECT `study_group_id`, COUNT(*) AS `work_days` FROM `element` 
                        WHERE `day_id` = $day AND `chet` = $chet
                        GROUP BY `study_group_id`
                        ) 
                    AS `works` 
                    WHERE `works`.`work_days` >= 5
                )";
        $result = $conn->query($sql);
        $groups = [];
        while($row = $result->fetch_assoc()){
            array_push($groups, $row);
        }
        return $groups;
    }

    function get_subjects($conn, $teacher){
        $sql = "SELECT `subject`.`subject_id` AS `subject_id`, `subject`.`name` AS `subject_name`, `subject_type`.`name` AS `type` FROM `subject`
                JOIN `teacher` ON `teacher`.`teacher_id` = $teacher
                JOIN `subject_type` ON `subject_type`.`type_id` = `subject`.`type_id`
                WHERE `subject`.`area_id` = `teacher`.`area_id`";
        $result = $conn->query($sql);
        $subjects = [];
        while($row = $result->fetch_assoc()){
            array_push($subjects, $row);
        }
        return $subjects;
    }

    function get_rooms($conn, $day_id, $time_id, $group_id, $subject_id){
        $subject = get_subject($conn, $subject_id);
        $groupSize = get_academic_size($conn, $group_id);
        
        $type = $subject["type_id"];

        $rooms = [];
        $sql = "SELECT * FROM `room` 
                WHERE `room_id` NOT IN 
                ( 
                    SELECT `room_id` FROM `element` 
                    WHERE `day_id` = $day_id AND `time_id` = $time_id
                ) 
                AND `room_id` IN 
                ( 
                    SELECT `room`.`room_id` FROM `room` WHERE `room`.`type_id` = $type
                ) 
                AND `room_id` IN 
                ( 
                    SELECT `room`.`room_id` FROM `room` 
                    WHERE `room`.`size` >= $groupSize 
                )";
        $result = $conn->query($sql);
        if($result){
            while($row = $result->fetch_assoc()){
                array_push($rooms, $row);
            }
            return $rooms;
        }
    }

    function get_day($conn, $day_id){
        $sql = "SELECT * FROM `day` WHERE `day_id` = $day_id";
        $result = $conn->query($sql);
        if($result){
            $row = $result->fetch_assoc();
            return $row["name"];
        }
    }

    function get_time($conn, $time_id){
        $sql = "SELECT * FROM `time` WHERE `time_id` = $time_id";
        $result = $conn->query($sql);
        if($result){
            $row = $result->fetch_assoc();
            return $row["start_time"]."-".$row["end_time"];    
        }
    }

    function get_teacher($conn, $teacher_id){
        $sql = "SELECT * FROM `teacher` WHERE `teacher_id` = $teacher_id";
        $result = $conn->query($sql);
        if($result){
            $row = $result->fetch_assoc();
            return $row["name"];
        }
    }

    function get_group($conn, $group_id){
        $sql = "SELECT * FROM `study_group` WHERE `study_group_id` = $group_id";
        $result = $conn->query($sql);
        if($result){
            $row = $result->fetch_assoc();
            return $row["name"];
        }
    }

    function get_subject($conn, $subject_id){
        $sql = "SELECT `subject`.`subject_id` AS `subject_id`, `subject`.`name` AS `subject_name`, `subject_type`.`name` AS `type`, `subject_type`.`type_id` AS `type_id` FROM `subject`
                JOIN `subject_type` ON `subject_type`.`type_id` = `subject`.`type_id`
                WHERE `subject`.`subject_id` = $subject_id";
        $result = $conn->query($sql);
        if($result){
            $row = $result->fetch_assoc();
            return $row;
        }
    }

    function get_academic_size($conn, $group_id){
        $sql = "SELECT SUM(`size`) AS `size` FROM `academic_group` GROUP BY `study_group_id` HAVING `study_group_id` = $group_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row["size"];
    }

    function get_room($conn, $room_id){
        $sql = "SELECT * FROM `room` WHERE `room_id` = $room_id";
        $result = $conn->query($sql);
        if($result){
            $row = $result->fetch_assoc();
            return $row["number"];
        }
    }

    if($_POST["chet"]){
        $days = get_days($conn);
        $times = get_times($conn);
        if($_POST["day"] && $_POST["time"]){
            $teachers = get_teachers($conn, $_POST["day"], $_POST["time"], $_POST["chet"]);
            $groups = get_groups($conn, $_POST["day"], $_POST["time"], $_POST["chet"]);
            if($_POST["teacher"]){
                $subjects = get_subjects($conn, $_POST["teacher"]);
                if($_POST["subject"] && $_POST["group"]){
                    $rooms = get_rooms($conn, $_POST["day"], $_POST["time"], $_POST["group"], $_POST["subject"]);
                }
            }
        }
    }
?>
<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav>

    <ul>
        <li>
            <a href="addPage.php">Админ панель</a>
        </li>

        <li>
            <a href="index.php">Расписание</a>
        </li>
    </ul>

    </nav>

    <main class="admin_main flex">
        <div class="choose flex">
            <h1 class="choose_title">Выбор параметров для пары</h1>
            <div>
                Четный/нечентный №1
                <form method="POST">
                    <select name="chet" style="width:200px">
                        <option value="">Выберите Чет/Нечет</option>
                        <option value="1">Четный</option>
                        <option value="2">Нечетный</option>
                    </select>
                    <input type="submit">
                </form>
            </div>
            <div>
                День №2
                <form method="POST">
                    <select name="day" style="width:200px">
                        <option value="">Выберите день</option>
                        <?php 
                            foreach($days as $day){
                                echo("<option value=".$day["day_id"].">".$day["name"]."</option>");
                            }
                        ?>
                    </select>
                    <input type="hidden" name="chet" value=<?php echo($_POST["chet"]); ?>>
                    <input type="submit">
                </form>
            </div>
            <div>
                Пара №3
                <form method="POST">
                    <select name="time" style="width:200px">
                        <option value="">Выберите пару</option>
                        <?php 
                            foreach($times as $time){
                                echo("<option value=".$time["time_id"].">".$time["start_time"]."-".$time["end_time"]."</option>");
                            }
                        ?>
                    </select>
                    <input type="hidden" name="chet" value=<?php echo($_POST["chet"]); ?>>
                    <input type="hidden" name="day" value=<?php echo($_POST["day"]); ?>>
                    <input type="submit">
                </form>
            </div>
            <div>
                Преподаватель №4
                <form method="POST">
                    <select name="teacher" style="width:200px">
                        <option value="">Выберите преподавателя</option>
                        <?php 
                            foreach($teachers as $teacher){
                                echo("<option value=".$teacher["teacher_id"].">".$teacher["name"]."</option>");
                            }
                        ?>
                    </select>
                    <input type="hidden" name="chet" value=<?php echo($_POST["chet"]); ?>>
                    <input type="hidden" name="time" value=<?php echo($_POST["time"]); ?>>
                    <input type="hidden" name="day" value=<?php echo($_POST["day"]); ?>>
                    <input type="submit">
                </form>
            </div>
            <div>
                Группа №5
                <form method="POST">
                    <select name="group" style="width:200px">
                        <option value="">Выберите учебную группу</option>
                        <?php 
                            foreach($groups as $group){
                                echo("<option value=".$group["study_group_id"].">".$group["name"]."</option>");
                            }
                        ?>
                    </select>
                    <input type="hidden" name="chet" value=<?php echo($_POST["chet"]); ?>>
                    <input type="hidden" name="time" value=<?php echo($_POST["time"]); ?>>
                    <input type="hidden" name="teacher" value=<?php echo($_POST["teacher"]); ?>>
                    <input type="hidden" name="day" value=<?php echo($_POST["day"]); ?>>
                    <input type="submit">
                </form>
            </div>
            <div>
                Предмет №6
                <form method="POST">
                    <select name="subject" style="width:200px">
                        <option value="">Выберите предмет</option>
                        <?php 
                            foreach($subjects as $subject){
                                echo("<option value=".$subject["subject_id"].">".$subject["subject_name"].", Тип: ".$subject["type"]."</option>");
                            }
                        ?>
                    </select>
                    <input type="hidden" name="chet" value=<?php echo($_POST["chet"]); ?>>
                    <input type="hidden" name="time" value=<?php echo($_POST["time"]); ?>>
                    <input type="hidden" name="teacher" value=<?php echo($_POST["teacher"]); ?>>
                    <input type="hidden" name="group" value=<?php echo($_POST["group"]); ?>>
                    <input type="hidden" name="day" value=<?php echo($_POST["day"]); ?>>
                    <input type="submit">
                </form>
            </div>
            <div>
                Аудитория №7
                <form method="POST">
                    <select name="room" style="width:200px">
                        <option value="">Выберите аудиторию</option>
                        <?php 
                            foreach($rooms as $room){
                                echo("<option value=".$room["room_id"].">".$room["number"]."</option>");
                            }
                        ?>
                    </select>
                    <input type="hidden" name="chet" value=<?php echo($_POST["chet"]); ?>>
                    <input type="hidden" name="time" value=<?php echo($_POST["time"]); ?>>
                    <input type="hidden" name="teacher" value=<?php echo($_POST["teacher"]); ?>>
                    <input type="hidden" name="group" value=<?php echo($_POST["group"]); ?>>
                    <input type="hidden" name="subject" value=<?php echo($_POST["subject"]); ?>>
                    <input type="hidden" name="day" value=<?php echo($_POST["day"]); ?>>
                    <input type="submit">
                </form>
            </div>
        </div>

        <div class="table flex">
            <h1 class="table_title">Выбранные параметры</h1>
            <?php
                echo("<div class='t_item flex'>");
                if($_POST["chet"] == 1){
                    echo("<div class='left_t'>1. Тип</div><div class='right_t'>Четный</div>");
                }
                else if($_POST["chet"] == 2){
                    echo("<div class='left_t'>1. Тип</div><div class='right_t'>Нечетный</div>");
                }
                else{
                    echo("<div class='left_t'>1. Тип</div><div class='right_t'></div>");
                }
                echo("</div>");
                echo("<div class='t_item flex'>");
                echo("<div class='left_t'>2. День</div><div class='right_t'>".get_day($conn, $_POST["day"])."</div>");
                echo("</div>");
                echo("<div class='t_item flex'>");
                echo("<div class='left_t'>3. Пара</div><div class='right_t'>".get_time($conn, $_POST["time"])."</div>");
                echo("</div>");
                echo("<div class='t_item flex'>");
                echo("<div class='left_t'>4. Преподаватель</div><div class='right_t' style='align-items:center'>".get_teacher($conn, $_POST["teacher"])."</div>");
                echo("</div>");
                echo("<div class='t_item flex'>");
                echo("<div class='left_t'>5. Группа</div><div class='right_t'>".get_group($conn, $_POST["group"])."</div>");
                echo("</div>");
                $subj = get_subject($conn, $_POST["subject"]);
                echo("<div class='t_item flex'>");
                echo("<div class='left_t'>6. Предмет</div><div class='right_t'>".$subj["subject_name"].", Тип: ".$subj["type"]."</div>");
                echo("</div>");
                echo("<div class='t_item flex'>");
                echo("<div class='left_t'>7. Аудитория</div><div class='right_t'>".get_room($conn, $_POST["room"])."</div>");
                echo("</div>");
                if($_POST["room"]){
                    echo("<form method='POST' action='add.php'>
                            <input type='submit' value='Внести в расписание'>
                            <input type='hidden' name='chet' value=".$_POST["chet"].">
                            <input type='hidden' name='time' value=".$_POST["time"].">
                            <input type='hidden' name='teacher' value=".$_POST["teacher"].">
                            <input type='hidden' name='group' value=".$_POST["group"].">
                            <input type='hidden' name='subject' value=".$_POST["subject"].">
                            <input type='hidden' name='day' value=".$_POST["day"].">
                            <input type='hidden' name='room' value=".$_POST["room"].">
                        </form>"
                    );
                }
            ?>
        </div>
    </main>
</body>