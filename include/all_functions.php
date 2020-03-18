<?php
## Валидация ссылок
function getResponseCode($url) {
    $header = '';
    $options = array(
        CURLOPT_URL => trim($url),
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true
    );

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    curl_exec($ch);
    if (!curl_errno($ch)) {
        $header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    curl_close($ch);

    if ($header > 0 && $header < 400) {
        return true; // если удовлетворяет условиям - true
    } else {
        return false; // если НЕ удовлетворяет условиям - false
    }
}

## удалять инфу по прохожению 5-ти дней
function life_time_csv($name) {
    $filename = $name;
    $file_to = filemtime($filename); // время изменения файла
    $today = time(); // текущее время
    $diff = $today-$file_to;
    $RemDays = (int)floor($diff / 86400); // целых дней в этой разнице
    $RemTime = gmdate('H:i:s', $diff % 86400); // остатки дня в чч:мм:сс

    if ($RemDays > 5) { // удаление происходит если файл лежит без изменений больше 5-ти дней
        unlink($filename);//удаляем файл
    }
}

## функция отбора последнего файла по данному ip-адрессу
function chose_last_csv($array, $user_ip) {
    foreach ($array as $value) {
        $expansion = '.csv'; // ищем от сюда (убираем расширение .csv)
        $count_expansion = strlen($expansion);
        $row = strpos($value, $expansion); // первое вхождение
        $num_wo_expansion = substr($value, 0, $row); // имя файла без расширения

        $bottom_underline = '_'; // ищем от сюда (режем по нижнему регистру)
        $count_bottom_underline = strlen($bottom_underline);
        $_underline = strpos($num_wo_expansion, $bottom_underline); // первое вхождение
        $clear_ip = substr($num_wo_expansion, 0,$_underline); // чисто IP

        if ($clear_ip == $user_ip) { // если IP-адресс в названии файла совпадает с IP-адрессом пользователя - продолжаем
            $total_mass['ip'] = $clear_ip;
            $pos_ip = $clear_ip; // ищем от сюда (режем по ip)
            $count_pos_ip = strlen($pos_ip);
            $_ip = strpos($num_wo_expansion, $pos_ip); // первое вхождение
            $clear_id = substr($num_wo_expansion, $_ip+$count_pos_ip+1); // чисто ID
            $total_mass['id'] = $clear_id;
            $result [] = $total_mass;
        }
    }
    return $result; // возвращаем результат
}

## функция открытия csv-файла
function open_csv($file_name) {
    $handle = fopen('../Nexteum/load_csv/' . $file_name, "r");
    while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
        $num = count($data);
        $my_csv[] = $data;
    }
    fclose($handle);

    $for_keys = $my_csv[0];
    for ($k = 1; $k < count($my_csv); $k++) {
        for ($i = 0; $i < 5; $i++) {// Ставим 5 ибо нас интересуют только поля без приписки old_
            $pre_result[$for_keys[$i]] = $my_csv[$k][$i];
        }
        $result[] = $pre_result;
    }
    return $result;
}

## функция сравнения файлов
function compare_csv($old_mass_from_csv, $new_mass_from_pars) {
    foreach ($old_mass_from_csv as $key1 => $value1) {
        foreach ($new_mass_from_pars as $key2 => $value2) {
            $key_from_search = array_search($value2['link'], $value1);
            if($key_from_search != '') {
                $found_not_match = array_diff($value1, $value2); // сравниваем старые данные с новыми (ищем различия)
                if (!empty($found_not_match)) {
                    $result[$key2] = $found_not_match;
                }
                $with_data[$key2] = $value2; // массив найденных занчений (но данные из нового парса)
            } else {
                $ostatok[$key2] = $value2;
            }
        }
    }

    if (!empty($result)) { // если есть не совпадающие значения - продолжаем
        // делаем приписку что это старые значение (из прошлого файла)
        foreach ($result as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $newKey = "old_" . $key2;
                $pre_new_arr[$newKey] = $value2; // заполнить ключ=>значение
            }
            $new_arr[$key] = $pre_new_arr;
            unset($pre_new_arr); // очистить переходный массив
        }

        foreach ($new_arr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $for_add_keys[$key] = $key2;
            }
        }

        $pre_for_add_keys = array_unique($for_add_keys); // ВСЕ НОВЫЕ ключи с припиской _old (без дубликатов)
        $pre_trans = array_flip($pre_for_add_keys); // меняем местами ключ/зачение
        foreach ($pre_trans as $key => $value) {
            $trans[$key] = '';
        }

        foreach ($with_data as $key => $value) {
            $finish[$key] = array_merge($value, $trans);
        }

        foreach ($finish as $key => $value) {
            if (array_key_exists($key, $new_arr)) { // проверить присутствие зачения в массиве
                $total[$key] = array_merge($finish[$key], $new_arr[$key]);
            }
        }

        foreach ($total as $key => $value) {
            unset($ostatok[$key]);
        }

        foreach ($ostatok as $value) {
            $pre_ostatok[] = array_merge($value, $trans);
        }
        $compare_ostatok_total = array_merge($pre_ostatok, $total);

        return $compare_ostatok_total;
    }
}
?>