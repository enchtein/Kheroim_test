<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парсер</title>
	<link rel="stylesheet" href="css/main.css">
    <!--NOT NEED--><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!--NOT NEED--><link rel="stylesheet" href="css/font-awesome.min.css">
</head>
<body>
<div class="container-fluid-my" id="padding_my">
	<div id="headerwrap">
		<div class="row">
			<div class="col-sm-12">
				<h1>Парсер YouTube / vimeo!</h1>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-5 text-center">
			ДЛЯ ВВОДА ДАННЫХ
		</div>
		<div class="col-sm-2 text-center">
		</div>
		<div class="col-sm-5 text-center">
			ДЛЯ Unvalid Links
		</div>
	</div>
	<form action="" method="post">
		<div class="row">
			<div class="col-sm-5 text-center">
				<textarea name="input_links" placeholder="Сюда вставить ссылки"></textarea>
			</div>
			<div class="col-sm-2 text-center">
				<button type="submit" name="start">Start</button>
			</div>
			<div class="col-sm-5 text-center">
				<textarea name="unvalid_links" placeholder="Здесь невалидные ссылки"><?php if (isset($_POST['start']) && !empty($bad_link)) { foreach ($bad_link as $value) { echo $value . "\n"; } } ?></textarea>
			</div>
		</div>
<?php ## для выгрузки не валидных ссылок
if (isset($_POST['start']) && file_exists($unvalid_link_on_csv)) :
?>
		<div class="row">
			<div class="col-sm-7 text-center">
			</div>
			<div class="col-sm-5 text-center">
				<a href="<?php echo "/Nexteum/" . $unvalid_link_on_csv; ?>" class="button">Load CSV File with Unvalid Links</a>
			</div>
		</div>
<?php
endif;
?>
		<div id="progress">
<?php ## Сделать select из БД lanching
$myarr = array('*', 'lanching');
$empl = new SQL();
$return_all_pars = $empl->select($myarr); // в этом массиве должна быть вся информация о всех парсах

foreach ($return_all_pars as $key => $value) {
    if ($value['user_ip'] == $user_ip) { // в случае совпадения формируем массив с данными по этому IP-адрессу
		$all_pars_from_user_ip[] = $value;
    }
}
?>
		<label>ДЛЯ ПРОЦЕССА ОБРАБОТКИ</label>
		<table border="1" width=100%>
			<tr>
				<th>id</th>
				<th>Status</th>
				<th>Time (start/end)</th>
				<th>Result</th>
				<th>Count</th>
			</tr>
<?php ## вывод результатов парса
if (!empty($all_pars_from_user_ip)) :
    foreach ($all_pars_from_user_ip as $value_pars) :
?> 
			<tr><td><?php echo $value_pars['id']; ?></td><td><?php if ($mass_list_on_screen['id'] == $value_pars['id']) { echo $mass_list_on_screen['status']; } else { echo "IT's old Pars"; } ?></td><td><?php echo $value_pars['time_of_csv']; ?></td><td><a href="<?php echo "/Nexteum/" . $value_pars['path_to_csv']; ?>" class="button">Load CSV File</a></td><td><?php if ($mass_list_on_screen['id'] == $value_pars['id']) { echo $mass_list_on_screen['count']; } else { echo "IT's old Pars"; } ?></td></tr>
<?php
    endforeach;
endif;
?>
		</table>
		</div>
	</form>
</div>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
</html>