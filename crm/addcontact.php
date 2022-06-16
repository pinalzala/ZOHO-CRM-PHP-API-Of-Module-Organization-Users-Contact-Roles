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
		    $sd = new zoho_crm($token);

		?>
		<script>
	
	</script>

		<?php
		echo "<h3>Contact Roles</h3>"; 
		$sd->crm_contactrolesadd();

    $response = $sd->crm_contactroles();
	
	echo "<pre>";
	print_r($response);
	echo "<pre>";

echo "<a href='index.php' title='Back to CRM page'>Back</a>";
    } ?>
</body>
</html>
