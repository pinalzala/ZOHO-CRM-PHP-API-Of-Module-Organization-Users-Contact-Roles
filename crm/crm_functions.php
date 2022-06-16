<?php
require_once '../config/config.php';
define("token_store_crm", "../token/tokens");

$response = json_decode(file_get_contents(token_store_crm), TRUE);
define("api_domain",stripslashes($response['api_domain']));

class zoho_crm{

      
    public $access_token = '';

	public function __construct($passed_access_token)
	{
		$this->access_token = $passed_access_token;
	}

    protected function curl_get($url)
	{

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$headers = array(
			"Accept: application/json",
			"Authorization: Bearer " . $this->access_token
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($output, true);
		return $response;
	}
	protected function curl_post($uri, $inputarray) {
		$trimmed = json_encode($inputarray);
		try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->access_token,
		));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $trimmed);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		} catch (Exception $e) {
		}
		if ($httpcode == "201") {
			return json_decode($output, true);
		} else {
			return array('error' => 'HTTP status code not expected - got ', 'description' => $httpcode);
		}
	}
	protected function curl_put($uri, $inputarray) { 
		$trimmed = json_encode($inputarray);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $uri,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'PUT',
		CURLOPT_POSTFIELDS => $trimmed,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->access_token,
		),
		));
        $response = curl_exec($curl);
	  }
	protected function curl_delete($uri) {
		$output = "";
		try {
		  $ch = curl_init($uri);
		  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');    
		  curl_setopt($ch, CURLOPT_HEADER, 0);
		  curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$this->access_token,
		));
		  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		  curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		  $output = curl_exec($ch);
		  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		} catch (Exception $e) {
		}
			if ($httpcode == "200") {
				return json_decode($output, true);
			} else {
				return array('error' => 'HTTP status code not expected - got ', 'description' => $httpcode);
			}
	  }

    public static function build_oauth_url() {
		$response = "https://accounts.zoho.com/oauth/v2/auth?scope=ZohoAnalytics.data.all,ZohoAnalytics.modeling.create,Desk.tickets.ALL,ZohoCRM.users.ALL,ZohoCRM.org.ALL,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL&client_id=".client_id."&response_type=code&access_type=offline&prompt=consent&redirect_uri=".urlencode(callback_uri);
		return $response;
	}

    public static function acquire_token() {
		
		$response = zoho_crm::get_tokens_from_store();
		if (empty($response['access_token'])) {	// No token at all, needs to go through login flow. Return false to indicate this.
			return false;
			exit;
		}
		else {
			if (time() > (int)$response['access_token_expires']) { // Token needs refreshing. Refresh it and then return the new one.
				$refreshed = zoho_crm::refresh_oauth_token($response['refresh_token']);

                $get_refresh_token = zoho_crm::get_tokens_from_store();
				
				$refreshed['refresh_token'] = $get_refresh_token['refresh_token'];

				if (zoho_crm::save_tokens_to_store($refreshed)) {
					$newtokens = zoho_crm::get_tokens_from_store();
					return $newtokens['access_token'];
				}
				exit;
			} else {
				return $response['access_token']; // Token currently valid. Return it.
				exit;
			}
	}
}

public static function get_tokens_from_store() {
    $response = json_decode(file_get_contents(token_store_crm), TRUE);
    return $response;
}

public static function refresh_oauth_token($refresh) {
    $arraytoreturn = array();
    $output = "";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{Accounts_URL}/oauth/v2/token"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            ));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);		

        $data = "client_id=".client_id."&redirect_uri=".urlencode(callback_uri)."&client_secret=".urlencode(client_secret)."&refresh_token=".$refresh."&grant_type=refresh_token&scope=ZohoAnalytics.data.all,ZohoAnalytics.modeling.create,Desk.tickets.ALL,ZohoCRM.modules.ALL"; 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
    } catch (Exception $e) {
    }

    $out = json_decode($output, true);
    $arraytoreturn = Array('access_token' => $out['access_token'], 'expires_in' => $out['expires_in'], 'api_domain'=>$out['api_domain']);
    return $arraytoreturn;
}

public static function save_tokens_to_store($tokens) {
    $tokentosave = array();
    $tokentosave = array('access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token'], 'access_token_expires' => (time() + (int)$tokens['expires_in']), 'api_domain'=>$tokens['api_domain']);
    if (file_put_contents(token_store_crm, json_encode($tokentosave))) {
        return true;
    } else {
        return false;
    }
}

/// get modules
public function crm_modules(){
    $response = $this->curl_get(api_domain . "/crm/v3/settings/modules");
    return $response;
}

/// get organization
public function crm_organization(){
    $response = $this->curl_get(api_domain . "/crm/v3/org");
    return $response;
}

//roles
public function crm_roles(){
    $response = $this->curl_get(api_domain . "/crm/v3/settings/roles");
    return $response;
}

//get contact
public function crm_contactroles(){
    $response = $this->curl_get(api_domain . "/crm/v3/Contacts/roles");
    return $response;
}

//delete contact
public function crm_contactrolesdelete(){
    $response = $this->curl_delete(api_domain . "/crm/v3/Contacts/roles?ids=Add your Contact id");
    return $response;
}


//add contact
public function crm_contactrolesadd(){
	$array=array (
		'contact_roles' => 
		array (
		  0 => 
		  array (
			'name' => 'testing 1',
			'sequence_number' => 1,
		  ),
		  1 => 
		  array (
			'name' => 'testing 2',
			'sequence_number' => 2,
		  ),
		),
	);
    $response = $this->curl_post(api_domain . "/crm/v3/Contacts/roles",$array);
    return $response;
}

//update contact
public function crm_contactrolesupdate(){
	$array=array (
		'contact_roles' => 
		array (
		  0 => 
		  array (
			'name' => 'test 1',
			'sequence_number' => 2,
            'id'=>'',//contact id
		  ),
		  1 => 
		  array (
			'name' => 'test 2',
			'sequence_number' => 10,
			'id'=>'',//contact id

		  ),
		),
	);
    $response = $this->curl_put(api_domain . "/crm/v3/Contacts/roles",$array);
    return $response;
}

//get users
public function crm_users(){
    $response = $this->curl_get(api_domain . "/crm/v3/users?type=AllUsers");
    return $response;
}

//get profile
public function crm_profile(){
    $response = $this->curl_get(api_domain . "/crm/v3/settings/profiles");
    return $response;
}


}

?>