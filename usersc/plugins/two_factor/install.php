<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.
$check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($check > 0){
	err($plugin_name.' has already been installed!');
}else{
	$db->query("ALTER TABLE settings ADD twofa BOOLEAN");
	$db->query("ALTER TABLE settings ADD forcetwofa BOOLEAN");
	$db->query("ALTER TABLE users ADD twoKey varchar(255)");
	$db->query("ALTER TABLE users ADD twoEnabled BOOLEAN DEFAULT 0");
	$db->query("ALTER TABLE users ADD twofaforced BOOLEAN DEFAULT 0");
	$db->query("UPDATE settings SET twofa = 1");
	$db->query("UPDATE settings SET forcetwofa = 0");
 $fields = array(
	 'plugin'=>$plugin_name,
	 'status'=>'installed',
 );
 $db->insert('us_plugins',$fields);
 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['login.php']['pre'] = 'hooks/loginpre.php';
$hooks['loginSuccess']['body'] = 'hooks/loginsuccessbody.php';
$hooks['account.php']['body'] = 'hooks/accountbody.php';
$hooks['admin.php?view=user']['form'] = 'hooks/adminuserform.php';
$hooks['admin.php?view=user']['post'] = 'hooks/adminuserpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
