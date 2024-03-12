<?php
// echo phpinfo();
echo $_SERVER['HTTP_HOST']."<br />";

$abs_us_root=$_SERVER['DOCUMENT_ROOT'];
echo $abs_us_root."<br />";
echo $_SERVER['PHP_SELF']."<br />";
$self_path=explode("/", $_SERVER['PHP_SELF']);
var_dump($self_path); echo "<br />";
$self_path_length=count($self_path);
echo $self_path_length."<br />";
$file_found=FALSE;

for($i = 1; $i < $self_path_length; $i++){
	array_splice($self_path, $self_path_length-$i, $i);
	$us_url_root=implode("/",$self_path)."/";
    
    echo $us_url_root ."<br />";
    echo $abs_us_root.$us_url_root."<br />";

	if (file_exists($abs_us_root.$us_url_root.'z_us_root.php')){
		$file_found=TRUE;
		break;
	}else{
		$file_found=FALSE;
	}
}

echo $file_found ."<br />";
if (file_exists($abs_us_root.$us_url_root.'users/helpers/helpers.php')){
    $file_found=TRUE;
}else{
    $file_found=FALSE;
}
echo $file_found ."<br />";
echo $abs_us_root.$us_url_root.'users/helpers/helpers.php';
 
