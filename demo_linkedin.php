<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Demo_linkedin extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->library(array('oauthclient'));
	
		$this->oauthclient->setOauthDataStore("user_oauth");
		$this->oauthclient->setService("linkedin");
		$this->oauthclient->setConsumerKey("linkedin-consumer-key-goes-here");
		$this->oauthclient->setConsumerSecret("MHz3_WtII6D2IENngWfK_9176E1bUSSUH8jhrFLmAN_ussjN4sdjXBtbQmEXqKCi");
		$this->oauthclient->setResponseUrl("http://your-domain/demo_linkedin/response");
	}

	function index()
	{
		redirect('/demo_linkedin/connect/');
	}
	
	function connect() {
		$this->oauthclient->connect();
	}
	
	function response() {
		$userId = $this->oauthclient->response();
		print "<p>User ID ".$userId." is logged in.</p>";
		
		print "<a href=\"/demo_linkedin/profile/".$userId."\">View user profile.</a>";
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
