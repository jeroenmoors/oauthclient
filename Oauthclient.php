<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
*	Version 1.1 - 20/04/2012 - jeroen.moors@fluoline.net  
*	Version 1.0 - 14/01/2012 - jeroen.moors@fluoline.net  
*
*	Oauthclient a oAuth client library to access 
* 		- Twitter  (oAuth v1)
* 		- Linkedin (oAuth v1)
* 		- Facebook (oAuth v2)
*		- Foursquare (oAuth v2)(Not fully tested)
* 
*	More info: http://jeroen.is/oauthclient
*/


class Oauthclient {
	var $consumerKey;
	var $consumerSecret;
	
	var $oauthToken;
	var $oauthTokenSecret;
	
	var $serviceType;
	
	var $oauthDataStore;

	var $responseUrl;
	
	var $userId;
	    
    var $_oAuthVersion;
    var $_oAuthHost;
    var $_oAuthAuthorizeUrl;
    var $_oAuthAccessTokenUrl;
    var $_oAuthRedirectUrl;
    var $_callMyProfile;
    
	function __construct()
	{
        $this->ci =& get_instance();
        $this->oauthDataStore = "user_oauth";
    }
    	
	function setService($service) {
		switch($service) {
			case "twitter":
				$this->_oAuthVersion 			= 1;
				$this->_oAuthRequestTokenUrl 	= "https://api.twitter.com/oauth/request_token";
				$this->_oAuthAuthorizeUrl		= "https://api.twitter.com/oauth/authorize";
				$this->_oAuthAccessTokenUrl		= "https://api.twitter.com/oauth/access_token";
				$this->_oAuthRedirectUrl		= "https://twitter.com/oauth/authenticate?oauth_token=";

				$this->_callMyProfile			= "http://twitter.com/account/verify_credentials.xml";
				break;
				
			case "linkedin":
				$this->_oAuthVersion 	= 1;

				$this->_oAuthRequestTokenUrl 	= "https://api.linkedin.com/uas/oauth/requestToken";
				$this->_oAuthAuthorizeUrl		= "https://api.linkedin.com/uas/oauth/authorize";
				$this->_oAuthAccessTokenUrl		= "https://api.linkedin.com/uas/oauth/accessToken";
				$this->_oAuthRedirectUrl		= "https://www.linkedin.com/uas/oauth/authorize?oauth_token=";

				$this->_callMyProfile			= "https://api.linkedin.com/v1/people/~:(id,first-name,last-name,headline,picture-url,twitter-accounts,industry,positions)";
				break;

			case "facebook":
				$this->_oAuthVersion 			= 2;
				$this->_oAuthRedirectUrl		= "https://www.facebook.com/dialog/oauth?scope=offline_access&client_id=".$this->consumerKey."&redirect_uri=".urlencode($this->responseUrl);
				$this->_oAuthAccessTokenUrl 	= "https://graph.facebook.com/oauth/access_token?client_id=".$this->consumerKey."&redirect_uri=".urlencode($this->responseUrl)."&client_secret=".$this->consumerSecret."&code=";

				$this->_callMyProfile			= "https://graph.facebook.com/me";
				break;

			case "foursquare":
				$this->_oAuthVersion 			= 2;
				$this->_oAuthRedirectUrl		= "https://foursquare.com/oauth2/authenticate?client_id=".$this->consumerKey."&response_type=code&redirect_uri=".urlencode($this->responseUrl);				
				$this->_oAuthAccessTokenUrl 	= "https://foursquare.com/oauth2/access_token?client_id=".$this->consumerKey."&client_secret=".$this->consumerSecret."&grant_type=authorization_code&redirect_uri=".urlencode($this->responseUrl)."&code=code";

				$this->_callMyProfile			= "TO BE DEFINED";
				break;

				
			default:
				die("OAuthClient: Unknown service: '".$service."'");
		}	
		
		$this->serviceType = $service;
	}
		
	function setConsumerKey($key) {
		$this->consumerKey = $key;
		$this->setService($this->serviceType);
	}
	
	function setConsumerSecret($secret) {
		$this->consumerSecret = $secret;
		$this->setService($this->serviceType);
	}

	function setResponseUrl($url) {
		$this->responseUrl = $url;
		$this->setService($this->serviceType);
	}
	
	function setOAuthDataStore($table) {
		$this->oauthDataStore = $table;
	}
    
    function setUserId($userId) {
		$this->userId = $userId;
		
		if (!$this->serviceType) {
			die("OAuthclient: You must set ServiceType before setting userId");
		}
		// Load the user token & secret for the current service
        $this->ci->db->where("id", $this->userId);
        $this->ci->db->where("service", $this->serviceType);
        $this->ci->db->select('oauth_token, oauth_token_secret');
        $query = $this->ci->db->get($this->oauthDataStore);
        if ($query->num_rows()) {
			$row = $query->row();
            $this->oauthToken  		= $row->oauth_token;
			$this->oauthTokenSecret = $row->oauth_token_secret;
			return true;
        } else {
			return false;
		}
	}
    
    function connect() {
		if ($this->_oAuthVersion == 1) {
			try {
				$oauth = new OAuth($this->consumerKey, $this->consumerSecret);
				$request_token_info = $oauth->getRequestToken($this->_oAuthRequestTokenUrl, $this->responseUrl);
				if(!empty($request_token_info)) {
					$this->ci->session->set_userdata("oauth_token_secret", $request_token_info['oauth_token_secret']);
					header("Location: ".$this->_oAuthRedirectUrl.$request_token_info['oauth_token']);
				} else {
					print "Failed fetching request token, response was: " . $oauth->getLastResponse();
				}
			} catch(OAuthException $E) {
				// TODO: Handle exceptions in a better way
				// print_r($E);
				// echo "Response (RequestToken): ". $E->lastResponse . "\n";
			}
		} else {
			// If oAuth v2, we must be sure the url's are correctly poppulated
			// therefor, set the service type again
			$this->setService($this->serviceType);
			header("Location: ".$this->_oAuthRedirectUrl);
		}
    }
    
    function response() {
		if ($this->_oAuthVersion == "1") {
			try {                        
				$oauth = new OAuth($this->consumerKey, $this->consumerSecret);
				$oauth->setToken($this->ci->input->get('oauth_token'), $this->ci->session->userdata("oauth_token_secret"));
				
				$access_token_info = $oauth->getAccessToken($this->_oAuthAccessTokenUrl);
				
				if(!empty($access_token_info)) {
					$this->oauthToken  		= $access_token_info['oauth_token'];
					$this->oauthTokenSecret = $access_token_info['oauth_token_secret'];
					$this->userId = $this->_getUserId();
					$this->_updateUser();
					return $this->userId;
				} else {
					print "Failed fetching access token, response was: " . $oauth->getLastResponse();
					return false;
				}
				
			} catch(OAuthException $E) {
				// TODO: Handle exceptions in a better way
				//echo "Response: ". $E->lastResponse . "\n";
				return false;
			}
		} else {
			if ($this->ci->input->get("code")) { 
				$response = file_get_contents($this->_oAuthAccessTokenUrl.$this->ci->input->get("code"));
				
				$accessToken = "";
				
				$matches = preg_split("/\&/",$response);
				foreach($matches as $match) {
					list($key, $value) = preg_split("/=/",$match);
					if ($key == "access_token") {
						$this->oauthTokenSecret = $value;
					}
				}

				// Fetch Remote user id
				$user = $this->_httpRequestJson($this->_callMyProfile, false);
				if ($user) {
					$this->oauthToken 	=  $user->id;
					$this->userId 		= $this->_getUserId();
					$this->_updateUser();
					return $this->userId;
				} else {
					return false;
				}
				
			} else {
				print "Error: ".$this->ci->input->get("error")." (".$this->ci->input->get("error_description").")";
			}
		}
    }

	private function _getUserId() {
		$query = $this->ci->db->get_where($this->oauthDataStore, array("service" => $this->serviceType, "oauth_token" => $this->oauthToken));
		if($row = $query->row()) {
			// Existing user, set it's id
			return $row->id;
		} else {
			// New token, new user!
			return $this->_createUser();
		}
	}

	private function _updateUser() {
		$data = array("oauth_token" => $this->oauthToken, "oauth_token_secret" => $this->oauthTokenSecret);
		$this->ci->db->where('id', 		$this->userId);
		$this->ci->db->where('service', $this->serviceType);
		$this->ci->db->update($this->oauthDataStore, $data);
	}

	private function _createUser() {
		$data = array("service" => $this->serviceType, "oauth_token" => $this->oauthToken, "oauth_token_secret" => $this->oauthTokenSecret);
		$this->ci->db->insert($this->oauthDataStore, $data);
		return $this->ci->db->insert_id();
	}

    function apiCall($call, $postData = null) {
		if ($this->_oAuthVersion == "1") {
			try {
				$oauth = new OAuth(
						$this->consumerKey,
						$this->consumerSecret,
						OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI); //initiate
								
				$oauth->setToken($this->oauthToken, $this->oauthTokenSecret);
				if (is_array($postData)) {
					$data = $oauth->fetch($call, $postData, OAUTH_HTTP_METHOD_POST);
				} else { 
					$data = $oauth->fetch($call);
				}
				$response_info = $oauth->getLastResponse();
				
				return new SimpleXMLElement($oauth->getLastResponse());
				
			} catch(OAuthException $E) {
				// TODO: Handle exceptions in a better way
				// echo "Exception caught!\n";
				// echo "Response: ". $E->lastResponse . "\n";
				return false;
			}
		} else {
			return $this->_httpRequestJson($call, true);
		}
    }
    
    function _httpRequestJson($url, $reauthenticateOnFailure = true, $requireAccessToken = true) {
		if ($requireAccessToken) {
			$url = $url ."?access_token=".$this->oauthTokenSecret;
		}
		$data = @file_get_contents($url);
		if (!$data) {
			if($reauthenticateOnFailure) {
				$this->connect();
			}
			return false;
		} else {
			return json_decode($data);
		}
	}
    
	function getProfile() {
		return $this->apiCall($this->_callMyProfile);
	}
}
