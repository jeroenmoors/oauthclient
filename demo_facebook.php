<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Demo_facebook extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->library(array('oauthclient'));
		
		$this->oauthclient->setService("facebook");
		$this->oauthclient->setOauthDataStore("user_oauth");
		$this->oauthclient->setConsumerKey("application-id-goes-here");
		$this->oauthclient->setConsumerSecret("application-secret-goes-here");
		$this->oauthclient->setResponseUrl("http://your-domain/demo_facebook/response");
	}

	function index()
	{
		redirect('/demo_facebook/connect/');
	}
	
	function connect() {
		$this->oauthclient->connect();
	}
	
	function response() {
		$userId = $this->oauthclient->response();
		print "<p>User ID ".$userId." is logged in.</p>";
		
		print "<a href=\"/demo_facebook/profile/".$userId."\">View user profile.</a>";
	}	
	
	function profile($userId = 1) {
		$this->oauthclient->setUserId($userId);
		$xmlProfile = $this->oauthclient->getProfile();
		print "<h1>User profile</h1>";
		print "<pre>";
		print_r($xmlProfile); 
		print "</pre>";
	}
}
