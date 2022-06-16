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
		echo "<h3>Organization</h3>"; 
    $sd = new zoho_crm($token);
    $response = $sd->crm_organization();
foreach($response['org'] as $org){
    echo "<h4>Company Name: ".$org['company_name']."</h4>";
    echo "<p>Primary Email: ".$org['primary_email']."</p>";
    echo "<p>Id: ".$org['id']."</p>";
    echo "<p>Contact No: ".$org['phone']."</p>";
    echo "<p>Country: ".$org['country']."</p>";
    echo "<p>Country Code: ".$org['country_code']."</p>";
    echo "<p>City: ".$org['city']."</p>";
    echo "<p>Currency: ".$org['currency_locale']."</p>";
    echo "<p>Time Zone: ".$org['time_zone']."</p>";
    echo "<p>Photo Id: ".$org['photo_id']."</p>";
    echo "<p>Description: ".$org['description']."</p></br></br>";
}
echo "<a href='index.php' title='Back to CRM page'>Back</a>";
    } ?>
</body>
</html>