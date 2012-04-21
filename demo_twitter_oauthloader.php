<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Demo_twitter extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
        $this->load->library(array('oauthloader'));
        $this->oauthloader->twitter();
	}

	function index()
	{
		redirect('/demo_twitter_oauthloader/connect/');
	}
	
	
	function connect() {
		$this->oauthclient->connect();
	}
	
	function response() {
		$userId = $this->oauthclient->response();
		print "<p>User ID ".$userId." is logged in.</p>";
		
		print "<a href=\"/demo_twitter_oauthloader/profile/".$userId."\">View user profile.</a>";
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
