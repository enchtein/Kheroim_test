<?php
include ('include/include_all.php'); // подключение всех файлов

// Определяем IP адресс
$user_ip = $_SERVER['REMOTE_ADDR'];
// Определяем браузер
$user_browser = $_SERVER['HTTP_USER_AGENT'];

$today = date("Y-m-d H:i:s"); // текущая дата и время начала работы скрипта
$dir = '../Nexteum/load_csv'; // путь сохранения csv файлов
$all_files = scandir($dir); // в данном массиве все файлы директории $dir
array_shift($all_files); // удаляем из массива '.'
array_shift($all_files); // удаляем из массива '..'
// теперь в данном массиве остались только файлы .csv

if ($all_files != '') {
    foreach ($all_files as $key => $value) {
        life_time_csv('../Nexteum/load_csv/' . $value); // проверка времени жизни файлов и удаление при необходимости
    }
}

// вызов функции отбора последнего файла по данному ip-адрессу
$param = chose_last_csv($all_files, $user_ip);

if ($param != '') { // делаем проверку на пустоту переменной в которой указывается последний файл
    $last_pars_id = array_search(max($param),$param); // здесь содержиться ид последнего парса
    $last_file = $user_ip . "_" . $param[$last_pars_id]['id'] . ".csv"; // здесь содержиться имя файла с последнего парса
}

// Запуск обработки данных
if (isset($_POST['start'])) {
    ## очистка таблицы
    $table_name = '`parser`'; // название таблицы
    $for_clear_table = new SQL(); // отправка запроса на очистку таблицы
    $clear_table = $for_clear_table->truncate_table($table_name); // все данные таблицы parser должны быть удалены
    /*
    ## очистка таблицы
    $table_name = '`lanching`'; // название таблицы
    $for_clear_table = new SQL(); // отправка запроса на очистку таблицы
    $clear_table=$for_clear_table->truncate_table($table_name); // все данные таблицы lanching должны быть удалены
    echo "Очищенно от скверны"; die;
    */
    $date_new_file = date("Y-m-d H:i:s"); // текущая дата и время начала парса в новый файл
    $work_message = "Начата обработка...";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);

    // Проверка ссылок
    $pre_arr = explode("\r\n", $_POST['input_links']); // разбиваем строки
    foreach ($pre_arr as $value) {
        $checking = getResponseCode($value);
        if ($checking != '') {
            $good_link[] = $value; // сюда пишем валидные ссылки
        } else {
            $bad_link[] = $value; // сюда пишем НЕ валидные ссылки (вывести их в INPUT и в CSV)
        }
    }

    $arr = $good_link;

    ## функция для проверки последнего элемента на пустоту
    if (!function_exists("array_key_last")) {
    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }
        return array_keys($array)[count($array)-1];
        }
    }
    $new = array_key_last($arr);

    if ($arr[$new] == '') {
        unset($arr[$new]);
    }

    ## Формируем два массива (для vimeo.com и youtube.com)
    $work_message = "Сортировка данных...";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);
    foreach ($arr as $value) {
        $pos_dot_com = '.com'; // ищем отсюда
        $count_pos_dot_com = strlen($pos_dot_com);
        $row = strpos($value, $pos_dot_com); // первое вхождение
        $num = substr($value, 0,$row+$count_pos_dot_com);
        if ($num == "https://www.youtube.com") {
            $youtube_for_check_mass[] = $value; // добавление в массив youtube
        } else {
            $vimeo_for_check_mass[] = $value; // добавление в массив vimeo
        }
    }

    // How to see full content of long strings with var_dump() in PHP
    ini_set("xdebug.var_display_max_children", -1);
    ini_set("xdebug.var_display_max_data", -1);
    ini_set("xdebug.var_display_max_depth", -1);

    //FOR vimeo.com START
    if (empty($vimeo_for_check_mass)) {
        echo "vimeo_for_check_mass empty...";
    } else {
        $work_message = "vimeo...";
        echo htmlspecialchars($work_message);
        ob_flush();
        flush();
        sleep(1);
        foreach ($vimeo_for_check_mass as $key => $value_vimeo_link) {
            $result = file_get_contents($value_vimeo_link);
            $for_pos = '<link rel="alternate" href="'; // ищем отсюда
            $count_pos = strlen($for_pos);
            $row = strpos($result, $for_pos); // первое вхождение
            $num = substr($result, $row+$count_pos);
            $length = strlen($num); //длинна
            $sec_pos = '"';
            $sec_search = strpos($num, $sec_pos); // первое вхождение
            $total_res = substr($num, 0, $sec_search);
            $total_result = file_get_contents($total_res); // Переход по ссылке в которой содержиться вся инфа

            // Вытаскиваем нужные данные

            // Link on Video
            $length_pos_link = strlen('"<iframe src=\"'); //длинна
            $pos_link = strpos($total_result, '"<iframe src=\"');
            $pos_link_2 = substr($total_result, $pos_link+$length_pos_link);
            $pos_link_3 = strpos($pos_link_2, '?');
            $total_link = substr($pos_link_2, 0, $pos_link_3);
            $total_link_2 = str_replace("\/", "/", $total_link); // заменяем слеши

            // Name of Video
            $length_pos_name = strlen('allowfullscreen title=\"'); //длинна до Name
            $pos_name = strpos($pos_link_2, 'allowfullscreen title=\"');
            $pos_name_2 = substr($pos_link_2, $pos_name+$length_pos_name);
            $pos_name_3 = strpos($pos_name_2, '\"');
            $pre_finish_total_name = substr($pos_name_2, 0, $pos_name_3);
            $total_name = str_replace("'", "\'", $pre_finish_total_name); // заменяем слеши

            // Description
            $length_pos_descr = strlen('"description":"'); //длинна до Description
            $pos_descr = strpos($pos_name_2, '"description":"');
            $pos_descr_2 = substr($pos_name_2, $pos_descr+$length_pos_descr);
            $pos_descr_3 = strpos($pos_descr_2, '","thumbnail_url":"');
            $total_descr = substr($pos_descr_2, 0, $pos_descr_3);
            $pre_finish_total_descr_2 = str_replace("\/", "/", $total_descr); // заменяем слеши
            $total_descr_2 = str_replace("'", "\'", $pre_finish_total_descr_2); // заменяем слеши

            // Preview
            $length_pos_preview = strlen('","thumbnail_url":"'); //длинна до Preview
            $pos_preview = strpos($pos_descr_2, '","thumbnail_url":"');
            $pos_preview_2 = substr($pos_descr_2, $pos_preview+$length_pos_preview);
            $pos_preview_3 = strpos($pos_preview_2, '","thumbnail_width"');
            $total_preview = substr($pos_preview_2, 0, $pos_preview_3);
            $total_preview_2 = str_replace("\/", "/", $total_preview); // заменяем слеши

            // Формирование результирующего массива
            $res_vimeo_mass[$key]['ORIGINAL_link'] = $value_vimeo_link;	// исходная ссылка
            $res_vimeo_mass[$key]['link'] = $total_link_2; // ссылка на видос
            $res_vimeo_mass[$key]['name'] = $total_name; // название видоса
            $res_vimeo_mass[$key]['preview'] = $total_preview_2; // превью видоса
            $res_vimeo_mass[$key]['descr'] = $total_descr_2; // описание видоса
        }
    }
    //FOR vimeo.com END

    /* FOR youtube.com START */
    if (empty($youtube_for_check_mass)) {
        echo "youtube_for_check_mass empty...";
    } else {
        $work_message = "youtube...";
        echo htmlspecialchars($work_message);
        ob_flush();
        flush();
        sleep(1);
        foreach ($youtube_for_check_mass as $key => $value_youtybe_link) {
            $result_youtube = file_get_contents($value_youtybe_link);
            $for_on_xml = 'type="text/xml+oembed" href="'; // ищем отсюда
            $length_on_xml = strlen('type="text/xml+oembed" href="'); //длинна до Preview
            $row_on_xml = strpos($result_youtube, $for_on_xml); // первое вхождение
            $total_on_xml = substr($result_youtube, $row_on_xml+$length_on_xml);
            $pos_on_xml = strpos($total_on_xml, '" title="');
            $total_preview_2 = substr($total_on_xml, 0, $pos_on_xml);
            $for_link_on__youtube_video = file_get_contents($total_preview_2);

            // Link on Video
            $length_on_youtube_link = strlen('" src="'); //длинна до Link on Video
            $row_on_youtube_link = strpos($for_link_on__youtube_video, '" src="'); // первое вхождение
            $total_on_youtube_link = substr($for_link_on__youtube_video, $row_on_youtube_link+$length_on_youtube_link);
            $pos__youtube_link_3 = strpos($total_on_youtube_link, '?feature=oembed" frameborder="');
            $youtube_link = substr($total_on_youtube_link, 0, $pos__youtube_link_3); // Link on YouTybe Video

            // Name of Video
            $length_on_youtube_title = strlen('<title>'); //длинна до Name of Video
            $row_on_youtube_title = strpos($for_link_on__youtube_video, '<title>'); // первое вхождение
            $total_on_youtube_title = substr($for_link_on__youtube_video, $row_on_youtube_title+$length_on_youtube_title);
            $pos__on_youtube_title_3 = strpos($total_on_youtube_title, '</title>');
            $pre_finish_youtube_name = substr($total_on_youtube_title, 0, $pos__on_youtube_title_3); // Name of Video
            $youtube_name = str_replace("'", "\'", $pre_finish_youtube_name);

            // YouTube Preview
            $length_on_youtube_preview = strlen('<thumbnail_url>'); //длинна до YouTube Preview
            $row_on_youtube_preview = strpos($for_link_on__youtube_video, '<thumbnail_url>'); // первое вхождение
            $total_on_youtube_preview = substr($for_link_on__youtube_video, $row_on_youtube_preview+$length_on_youtube_preview);
            $pos__on_youtube_preview_3 = strpos($total_on_youtube_preview, '</thumbnail_url>');
            $youtube_preview = substr($total_on_youtube_preview, 0, $pos__on_youtube_preview_3); // YouTube Preview

            // Description
            $length_on_description = strlen('\"description\":{\"simpleText\":'); //длинна до YouTube Description
            $row_on_youtube_description = strpos($result_youtube, '\"description\":{\"simpleText\":'); // первое вхождение
            $total_on_youtube_description = substr($result_youtube, $row_on_youtube_description+$length_on_description+1);
            $pos__on_youtube_description_3 = strpos($total_on_youtube_description, '\"},\"lengthSeconds\":');
            $prepair_youtube_description = substr($total_on_youtube_description, 0, $pos__on_youtube_description_3); // YouTube Description
            $pre_finish_youtube_description = str_replace("\/", "/", $prepair_youtube_description); // заменяем слеши
            $youtube_description = str_replace("'", "\'", $pre_finish_youtube_description);

            // Формирование результирующего массива
            $res_youtube_mass[$key]['ORIGINAL_link'] = $value_youtybe_link; // исходная ссылка
            $res_youtube_mass[$key]['link'] = $youtube_link; // ссылка на видос
            $res_youtube_mass[$key]['name'] = $youtube_name; // название видоса
            $res_youtube_mass[$key]['preview'] = $youtube_preview; // превью видоса
            $res_youtube_mass[$key]['descr'] = $youtube_description; // описание видоса
        }
    }
    /* FOR youtube.com END */

    // записать результат в файл csv
    if ($res_vimeo_mass == '') {
        $total_mass_to_load = $res_youtube_mass;
    } else {
        $total_mass_to_load = $res_vimeo_mass;
    }
    if ($res_vimeo_mass != '' && $res_youtube_mass != '') {
        $total_mass_to_load = array_merge($res_vimeo_mass, $res_youtube_mass);
    }
    ## ДЛЯ ПРОЦЕССА ОБРАБОТКИ
    $on_start = count($arr); //кол-во исходных ссылок
    $on_finish = count($total_mass_to_load); //кол-во обработаных ссылок
    if ($on_finish == '') {
        $for_status = "Nothing was processed";
    } elseif ($on_start > $on_finish) {
        $for_status = "Done only part";
    } else {
        $for_status = "All Done";
    }

    // открытие последнего файла
    if ($last_file != '') {
    $result_open_csv = open_csv($last_file);

    // Вызов функции сравнения фалов
    $re = compare_csv($result_open_csv, $total_mass_to_load);
    }

    // сделать добавление в бд
    $work_message = "запись в БД parser...";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);

    foreach ($total_mass_to_load as $value) {
        $name_col_for_update = '`parser`';
        $param_col = '(`id`, `ORIGINAL_link`, `link`, `name`, `preview`, `descr`)';
        $param_val = "(NULL, '" . $value['ORIGINAL_link'] . "', '" . $value['link'] . "', '" . $value['name'] . "', '" . $value['preview'] . "', '" . $value['descr'] . "')";

        // Подаем данные на добавление в табл parser
        $mass_for_add = array($name_col_for_update, $param_col, $param_val);
        $add = new SQL(); // отправка запроса на добавление
        $add_data = $add->insert($mass_for_add); // должно вернуть id потому что добавил функцию lastInsertId()
    }

    // сделать добавление в бд
    $work_message = "запись в БД lanching...";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);

    // Подаем данные на добавление в табл lanching
    $name_col_for_update = '`lanching`';
    $param_col = '(`id`, `user_ip`, `user_browser`, `time_of_csv`, `path_to_csv`)';
    $param_val = "(NULL, '" . $user_ip . "', '" . $user_browser . "', '" . $date_new_file . "', '')";

    $mass_for_add = array($name_col_for_update, $param_col, $param_val);
    $add = new SQL(); // отправка запроса на добавление
    $add_data = $add->insert($mass_for_add); // должно вернуть id потому что добавил функцию lastInsertId()

    $last_id_db = $add_data; // возврат добавленной id в табл lanching

    ## ДЛЯ ПРОЦЕССА ОБРАБОТКИ
    $mass_list_on_screen['id'] = $last_id_db;
    $mass_list_on_screen['status'] = $for_status;
    $mass_list_on_screen['count'] = $on_finish . " from " . $on_start;

	## Формируем путь к файлу
    $link_on_csv = 'load_csv/' . $user_ip . '_' . $last_id_db . '.csv'; // полный путь к файлу

    // сделать добавление в бд
    $work_message = "запись в БД lanching (update)...";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);

    // Подаем на добавление только link_on_csv в табл lanching (update)
    $path_name_col_for_update = '`lanching`';
    $path_param_col = "`path_to_csv`= '" . $link_on_csv . "'";
    $path_param_val = "`lanching`.`id` = " . $last_id_db;

    $path_mass_for_add = array($path_name_col_for_update, $path_param_col, $path_param_val);
    $path_add = new SQL(); // отправка запроса на добавление
    $path_add_data = $add->update($path_mass_for_add); // должно вернуть id_lanching потому что добавил функцию lastInsertId()

    // присвоить выходной результат конечному массиву по условию
    if (!empty($re)) {
        $mass_for_put_in_csv = $re;
    } else {
        $mass_for_put_in_csv = $total_mass_to_load;
    }

    foreach ($mass_for_put_in_csv as $key => $value) { // выбиравем все ключи с результирующего масива
        $pre_only_keys = array_keys($value);
        foreach ($pre_only_keys as $value) {
            $only_keys[] = $value;
        }
    }

    $for_title = array_unique($only_keys); // названия колонок в csv файле

    $work_message = "запись в файл...";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);

    if ($mass_for_put_in_csv != '') {
        array_unshift($mass_for_put_in_csv, $for_title); // Добавление заголовка в начало массива

        $fp = fopen($link_on_csv, 'w');
        foreach ($mass_for_put_in_csv as $value) {
            fputcsv($fp, $value, "|");
        }

        fclose($fp);
    }
	
    // записываем файл с невалидными ссылками
    $for_title_unvalid_links = "Unvalid_links"; // названия колонок в csv файле
    $unvalid_link_on_csv = 'unvalid_links/unvalid_links.csv'; // полный путь к файлу
	if (!empty($bad_link)) {
        array_unshift($bad_link, $for_title_unvalid_links); // Добавление заголовка в начало массива
        $unvalid_fp = fopen($unvalid_link_on_csv, 'w');
        fputcsv($unvalid_fp, $bad_link, "\n");
        fclose($unvalid_fp);
    } else {
        if (file_exists($unvalid_link_on_csv)) {
            unlink($unvalid_link_on_csv); // удаляем файл
        }
	}

    $work_message = "Done";
    echo htmlspecialchars($work_message);
    ob_flush();
    flush();
    sleep(1);
}
?>
<?php
include ('include/html_from.php'); // подключение формы
?>