<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Oauthloader {
	function __construct() {
		$this->ci =& get_instance();
		$this->ci->load->library(array('oauthclient'));
	}
	
	function linkedin() {
		$this->ci->oauthclient->setOauthDataStore("user_oauth");
		$this->ci->oauthclient->setService("linkedin");
		$this->ci->oauthclient->setConsumerKey("REPLACE-ME");
		$this->ci->oauthclient->setConsumerSecret("REPLACE-ME");
		$this->ci->oauthclient->setResponseUrl(site_url("auth/linkedin/response/"));
	}
	
	function twitter() {
		$this->ci->oauthclient->setOauthDataStore("user_oauth");
		$this->ci->oauthclient->setService("twitter");
		$this->ci->oauthclient->setConsumerKey("REPLACE-ME");
		$this->ci->oauthclient->setConsumerSecret("REPLACE-ME");
		$this->ci->oauthclient->setResponseUrl(site_url("auth/twitter/response/"));		
	}
	
	function facebook() {
		$this->ci->oauthclient->setService("facebook");
		$this->ci->oauthclient->setOauthDataStore("user_oauth");
		$this->ci->oauthclient->setConsumerKey("REPLACE-ME");
		$this->ci->oauthclient->setConsumerSecret("REPLACE-ME");
		$this->ci->oauthclient->setResponseUrl(site_url("auth/facebook/response/"));
	}
}
