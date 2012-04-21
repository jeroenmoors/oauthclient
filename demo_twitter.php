<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Demo_twitter extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->library(array('oauthclient'));
	
		$this->oauthclient->setOauthDataStore("user_oauth");
		$this->oauthclient->setService("twitter");
		$this->oauthclient->setConsumerKey("twitter-consumer-key-goes-here");
		$this->oauthclient->setConsumerSecret("twitter-consumer-secret-goes-here");
		$this->oauthclient->setResponseUrl("http://your-domain/demo_twitter/response/");
	}

	function index()
	{
		redirect('/demo_twitter/connect/');
	}
	
	
	function connect() {
		$this->oauthclient->connect();
	}
	
	function response() {
		$userId = $this->oauthclient->response();
		print "<p>User ID ".$userId." is logged in.</p>";
		
		print "<a href=\"/demo_twitter/profile/".$userId."\">View user profile.</a>";
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
