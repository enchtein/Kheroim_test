<?php ## SQL запросы
require("bdconnect_pdo.php"); // вызов подключения к бд

class SQL
{
    function select($array) {
        global $link;
        if (count($array) < 3){
            $a = "SELECT $array[0] FROM $array[1]";
        } elseif (count($array) < 4) {
            $a = "SELECT $array[0] FROM $array[1] WHERE $array[2]";
        } elseif (count($array) == 4) {
            $a = "SELECT $array[0] FROM $array[1] WHERE $array[2] ORDER BY $array[3]";
        }
        $select_var = $link->prepare($a);
        $select_var->execute();
        $mass = $select_var->fetchAll();
        return ($mass);
    }

    function update($array) {
        global $link;
        if (count($array) < 3){
            $a = "UPDATE $array[0] SET $array[1]";
        } elseif (count($array) < 4) {
            $a = "UPDATE $array[0] SET $array[1] WHERE $array[2]";
        }
        $update_var = $link->prepare($a);
        $update_var->execute();
    }

    function insert($array) {
        global $link;
        $a = "INSERT INTO $array[0] $array[1] VALUES $array[2]";
        $insert_var = $link->prepare($a);
        $insert_var->execute();
        $last_add_id = $link->lastInsertId(); //получаем id последней добавленной записи
        return $last_add_id;
    }

    function delete_rows($array) {
        global $link;
        $a = "DELETE FROM $array[0] WHERE $array[1]";
        $delete_var = $link->prepare($a);
        $res_delete = $delete_var->execute();
        return $res_delete;
    }

    function truncate_table($table_name) {
        global $link;
        $a = "TRUNCATE TABLE $table_name";
        $delete_var = $link->prepare($a);
        $res_delete = $delete_var->execute();
        return $res_delete;
    }
}
?>