
<?php require_once "crm_functions.php";
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Zoho CRM</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
</head>
<body>
<?php 
$token = zoho_crm::acquire_token();
if (!$token) {  ?>
	<a href="<?php echo zoho_crm::build_oauth_url(); ?>">Authorize me</a>
	<?php }else{
	echo "<h3>Zoho CRM</h3>";
	echo "<p><a href='modules.php' title=''>Module</a></p>";
	echo "<p><a href='organization.php' title=''>Organization</a></p>";
	echo "<p><a href='roles.php' title=''>Roles</a></p>";
	echo "<p><a href='users.php' title=''>Users</a></p>";
	echo "<p><a href='contact.php' title=''>contact Roles</a></p>";
	 } ?>
</body>
</html>