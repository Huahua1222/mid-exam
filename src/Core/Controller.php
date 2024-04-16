<?php

namespace Exam\Core;

use DateTime;
use Time;
use Exam\Core\Connect;

class Controller
{
    private $handler = null;
    private $need_tables = [
        "Users" => "CREATE TABLE `Users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `dept` varchar(255) NULL,
            `Total_credits` int(10) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username_UNIQUE` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "TimeSlot" => "CREATE TABLE `TimeSlot` (
            `time_slot_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `day` VARCHAR(255) NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            PRIMARY KEY (`time_slot_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "Course" => "CREATE TABLE `Course` (
            `ID` INT(10) UNSIGNED NOT NULL,
            `Name` VARCHAR(255) NOT NULL,
            `dept` varchar(255) NULL,
            `request` TINYINT(1) NOT NULL DEFAULT 0,
            `Credits` INT(10) UNSIGNED NOT NULL,
            `MaxPeople` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "CourseTimeSlots" => "CREATE TABLE `CourseTimeSlots` (
            `Course_ID` INT(10) UNSIGNED NOT NULL,
            `Time_Slot_ID` INT(10) UNSIGNED NOT NULL,
            PRIMARY KEY (`Course_ID`, `Time_Slot_ID`),
            FOREIGN KEY (`Course_ID`) REFERENCES `Course`(`ID`),
            FOREIGN KEY (`Time_Slot_ID`) REFERENCES `TimeSlot`(`time_slot_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "TimeTable" => "CREATE TABLE `TimeTable` (
            `course_ID` INT(10) UNSIGNED NOT NULL,
            `time_slot_id` INT(10) UNSIGNED NOT NULL,
            `user_id` INT(11) NOT NULL,
            FOREIGN KEY (`course_ID`) REFERENCES `Course`(`ID`),
            FOREIGN KEY (`time_slot_id`) REFERENCES `TimeSlot`(`time_slot_id`),
            FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ];
    public function __construct()
    {
        $connect = new Connect();
        $this->handler = $connect->getHandler();
        if (!isset($this->handler))
            die("Can't get DB handler");
        $this->init_Table();
    }
    private function init_Table(): void
    {
        foreach ($this->need_tables as $table => $val) {
            if (!$this->table_Exists($table)) {
                $this->handler->exec($val);
                if ($table == "TimeSlot") $this->init_TimeSlot();
            }
        }
    }
    private function init_TimeSlot(): void
    {
        $start = [
            "08:10:00",
            "09:10:00",
            "10:10:00",
            "11:10:00",
            "12:10:00",
            "13:10:00",
            "14:10:00",
            "15:10:00",
            "16:10:00",
            "17:10:00",
            "18:30:00",
            "19:25:00",
            "20:25:00",
            "21:20:00",
        ];
        $end = [
            "09:00:00",
            "10:00:00",
            "11:00:00",
            "12:00:00",
            "13:00:00",
            "14:00:00",
            "15:00:00",
            "16:00:00",
            "17:00:00",
            "18:00:00",
            "19:20:00",
            "20:15:00",
            "21:15:00",
            "22:10:00",
        ];
        $day = ["星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期天"];
        foreach ($day as $d) {
            for ($i = 0; $i < 14; $i++) {
                $this->insert_TimeSlot($d, $start[$i], $end[$i]);
            }
        }
    }
    private function table_Exists(string $table): bool
    {
        $stmt = $this->handler->query("SHOW TABLES LIKE '$table'");
        return !($stmt->rowCount() == 0);
    }
    public function insert_User(string $user, string $password): bool|array
    {
        $sql = "INSERT INTO Users (`username`,`password`) VALUES (?,?)";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$user, $password]);
        if (!$ret)
            return false;
        return $stmt->fetch();
    }
    public function check_User(string $user): bool|array
    {
        $sql = "SELECT * from `Users` WHERE `username` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$user]);
        if (!$ret)
            return false;
        return $stmt->fetch();
    }

    public function insert_TimeSlot(string $day, string $start_time, string $end_time)
    {
        $sql = "INSERT INTO TimeSlot (`day`,`start_time`,`end_time`) VALUES (?,?,?)";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$day, $start_time, $end_time]);
        if (!$ret) return false;
        return true;
    }

    public function insert_Course(int $ID, string $Name, ?string $dept, int $request, int $Credits, int $MaxPeople): bool
    {
        $sql = "INSERT INTO Course (`ID`,`Name`,`dept`,`request`,`Credits`,`MaxPeople`) VALUES (?,?,?,?,?,?)";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$ID, $Name, $dept, $request, $Credits, $MaxPeople]);
        if (!$ret) return false;
        return true;
    }

    public function insert_CourseTimeSlots(int $Course_ID, int $Time_Slot_ID)
    {
        $sql = "INSERT INTO CourseTimeSlots (`Course_ID`,`Time_Slot_ID`) VALUES (?,?)";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$Course_ID, $Time_Slot_ID]);
        if (!$ret) return false;
        return true;
    }

    public function insert_TimeTable(int $course_ID, int $time_slot_id, int $user_id)
    {
        $sql = "INSERT INTO TimeTable (`course_ID`,`time_slot_id`,`user_id`) VALUES (?,?,?)";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$course_ID, $time_slot_id, $user_id]);
        if (!$ret) return false;
        return true;
    }

    public function delete_TimeSlot(string $day, string $start_time, string $end_time)
    {
        $sql = "DELETE FROM `TimeSlot` WHERE `day` = ? AND `start_time` = ? AND `end_time` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$day, $start_time, $end_time]);
        if (!$ret) return false;
        return true;
    }

    public function delete_Course(int $ID, string $Name): bool
    {
        $sql = "DELETE FROM `Course` WHERE `ID` = ? AND `Name` = ? ";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$ID, $Name]);
        if (!$ret) return false;
        return true;
    }


    public function delete_TimeTable(int $course_id, int $time_slot_id, int $user_id)
    {
        $sql = "DELETE FROM `TimeTable` WHERE `course_ID` = ? AND `time_slot_id` = ? AND `user_id` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$course_id, $time_slot_id, $user_id]);
        if (!$ret) return false;
        return true;
    }

    public function delete_CourseTimeSlots(int $course_id, int $time_slot_id)
    {
        $sql = "DELETE FROM `CourseTimeSlots` WHERE `course_ID` = ? AND `time_Slot_ID` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$course_id, $time_slot_id]);
        if (!$ret) return false;
        return true;
    }

    //search
    public function search_User_TimeTable(string $username, int $courseID): bool
    {
        $user = $this->check_User($username);

        $course = $this->check_Course($courseID);
        if (!$course) return false;

        $sql = "SELECT * FROM TimeTable WHERE user_id = ? AND course_ID = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$user['id'], $course['ID']]);
        if (!$ret)
            return false;
        return true;
    }
    public function display_User_TimeTable(string $username): bool|array
    {
        $sql = "SELECT Course.Name, TimeSlot.day, TimeSlot.start_time, TimeSlot.end_time
                FROM Users
                INNER JOIN TimeTable ON Users.id = TimeTable.user_id
                INNER JOIN Course ON TimeTable.course_ID = Course.ID
                INNER JOIN TimeSlot ON TimeTable.time_slot_id = TimeSlot.time_slot_id
                WHERE Users.username = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$username]);
        if (!$ret)
            return false;
        return $stmt->fetchAll();
    }

    public function check_Course(int $courseID): bool|array
    {
        $sql = "SELECT * FROM Course WHERE ID = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$courseID]);
        if (!$ret)
            return false;
        return $stmt->fetchall();
    }

    public function updateCourse(int $ID, string $column, $Value): bool
    {
        if (!$this->check_Course($ID)) {
            return false;
        }
        $validColumns = ['Name', 'dept', 'request', 'Credits', 'MaxPeople'];
        if (!in_array($column, $validColumns)) {
            return false;
        }

        $sql = "UPDATE Course SET `$column` = ? WHERE `ID` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$Value, $ID]);
        if (!$ret) return false;
        return true;
    }

    public function Update_User_dept(string $username, string $dept): bool
    {
        $sql = "UPDATE Users SET `dept` = ? WHERE `username` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$dept, $username]);
        if (!$ret) return false;
        return true;
    }

    public function Update_User_TotalCerdits(string $username): bool
    {
        $calcTotalCreditsSql = "SELECT SUM(Course.Credits) as Total_credits
                            FROM Users
                            INNER JOIN TimeTable ON Users.id = TimeTable.user_id
                            INNER JOIN Course ON TimeTable.course_ID = Course.ID
                            WHERE Users.username = ?";
        $calcStmt = $this->handler->prepare($calcTotalCreditsSql);
        $calcStmt->execute([$username]);
        $result = $calcStmt->fetch();

        $sql = "UPDATE Users SET `Total_credits` = ? WHERE `username` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$result['Total_credits'], $username]);
        if (!$ret) return false;
        return true;
    }

    public function updateTimeSlots(int $time_slot_id, string $day, string $start_time, string $end_time): bool
    {
        $start_datetime = DateTime::createFromFormat('H:i:s', $start_time);
        $end_datetime = DateTime::createFromFormat('H:i:s', $end_time);

        if (!$start_datetime || !$end_datetime) return false;

        $start_time_formatted = $start_datetime->format('H:i:s');
        $end_time_formatted = $end_datetime->format('H:i:s');

        $sql = "UPDATE TimeSlot SET `day` = ?,`start_time` = ?,`end_time`=? WHERE `time_slot_id` = ?";
        $stmt = $this->handler->prepare($sql);

        $ret = $stmt->execute([$day, $start_time_formatted, $end_time_formatted, $time_slot_id]);
        if (!$ret) return false;
        return true;
    }
    public function updateCourseTimeSlots(int $Course_ID, int $Time_Slot_ID): bool
    {
        $sql = "UPDATE CourseTimeSlots SET `Time_Slot_id`=? WHERE `Course_ID` = ?";
        $stmt = $this->handler->prepare($sql);
        $ret = $stmt->execute([$Course_ID, $Time_Slot_ID]);
        if (!$ret) return false;
        return true;
    }
    public function search_Courses_By_TimeSlot(int $class):bool| array
    {   
        $sql = "SELECT * FROM Course WHERE Course.ID IN (SELECT Course_ID FROM CourseTimeSlots WHERE Time_Slot_ID = ? )";
        $stmt = $this->handler->prepare($sql);
        $ret=$stmt->execute([$class]);
        if(!$ret){
            return false;
        }

        $courses = $stmt->fetchAll();
    }

    public function search_Courses_By_Name(string $Name):bool|array
    {
        $sql = "SELECT * FROM Course WHERE Name LIKE  ? ";
        $stmt = $this->handler->prepare($sql);
        $ret=$stmt->execute(['%'.$Name.'%']);
        if(!$ret){
            return false;
        }
        return $stmt->fetchall();
    }
    
    public function search_Courses_By_Dept(?string $dept):bool|array
    {
        $sql = "SELECT * FROM Course WHERE dept LIKE ? ";
        $stmt = $this->handler->prepare($sql);
        $ret=$stmt->execute([$dept]);
        if(!$ret){
            return false;
        }
        return $stmt->fetchAll();
    }

    
}
