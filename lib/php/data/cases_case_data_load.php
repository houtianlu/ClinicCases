<?php //scripts for case data tab in case detail
@session_start();
require(__DIR__ . '/../../../db.php');
require(CC_PATH . '/lib/php/auth/session_check.php');
require(CC_PATH . '/lib/php/utilities/convert_times.php');

if (isset($_REQUEST['id'])) {
	$case_id = $_REQUEST['id'];
}

if (isset($_REQUEST['type'])) {
	$type = $_REQUEST['type'];
}

//Get case data

$q = $dbh->prepare("SELECT * FROM cm WHERE id = '$case_id'");

$q->execute();

$case_data = $q->fetch(PDO::FETCH_ASSOC);

//Get columns config
$q = $dbh->prepare("SELECT * from cm_columns ORDER BY display_order ASC");

$q->execute();

$columns = $q->fetchAll(PDO::FETCH_ASSOC);

$data = null;

foreach ($columns as $col) {
	//push the value of the field in case_data onto $columns
	if ($col['db_name'] !== 'assigned_users')//we don't want assigned users in this view
	{
		$field =  $col['db_name'];
		$field_value = $case_data[$field];
		$col['value'] = $field_value;
		$data[] = $col;
	}
}

if (!$_SESSION['mobile']){
    include '../../../html/templates/interior/cases_case_data.php';
}
