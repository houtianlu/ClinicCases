<?php
//Upgrade documents from ClinicCases 6 to 7.  Files are moved out 
//of the webroot and the renamed to relevant db id number

require('db.php');

//Specify the full path to your old ClinicCases doc file
$path_to_old_docs = '/var/www/cliniccases/docs';

//Check to see if the new doc folder has been created
if (file_exists(CC_DOC_PATH))
{
	echo "Good, your new docs folder exists.\n";
}
else
{
    die("Your new docs folder as defined in the config file(CC_DOC_PATH) doesn't
       exist.  Please create it.\n");
}


//Check to see if the new doc folder is writable
if (is_writable(CC_DOC_PATH))
	
	{echo "Good, Your new docs folder is writable.  Let's proceed.\n";}
	
	else

    {die( "Your new docs folder as defined in the config file (CC_DOC_PATH) is not
   writable. Please fix this and try again.\n"); }

//Remove all backslashes from file names in the docs directory

if ($handle = opendir($path_to_old_docs)) {

           /* This is the correct way to loop over the directory. */
           while (false !== ($entry = readdir($handle))) {
                        echo "$entry\n";
                        $old_file_name = 'docs/' . $entry;
                        $clean_file_name = 'docs/' . str_replace("\\","",$entry);
                        rename($old_file_name,$clean_file_name);
           }
}

$query = $dbh->prepare("SELECT * FROM cm_documents WHERE local_file_name != ''
  AND extension != 'url'");

$query->execute();

$count = $query->rowCount();

echo "We will be moving $count documents from your old docs directory
    to ". CC_DOC_PATH . "\n";

$docs = $query->fetchAll(PDO::FETCH_ASSOC);

$done = 0;

foreach ($docs as $doc)
{
	$doc_id = $doc['id'];

	$new_doc_name = $doc['id'] . '.' . $doc['extension'];

    $old_doc_path = $old_file_name;

	$new_doc_path = CC_DOC_PATH . '/' .  $new_doc_name;
	
	rename($old_doc_path,$new_doc_path);

	if (!empty($doc['folder']))
	{$escaped_folder = rawurlencode($doc['folder']);}
	else
	{$escaped_folder = '';}

    $update_db = $dbh->prepare("UPDATE cm_documents 
    SET local_file_name = :new_doc,folder = :folder	WHERE id = :id ");
	
	$data = array('new_doc' => $new_doc_name, 'id' => $doc_id, 'folder' => $escaped_folder);
	
	$update_db->execute($data);
	
	$done = $done + 1;

	$completed = $done / $count * 100;

	echo round($completed, 2) . "% completed\n";
}

//TODO This still leaves files that have been uploaded via the board;  need to address these
