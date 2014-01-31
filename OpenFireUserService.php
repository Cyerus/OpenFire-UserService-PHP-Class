<?php

/**
 * Description of OpenFireUserService
 *
 * @author Cyerus
 */
class OpenFireUserService 
{
	
	private $_settings = array(
		'host'			=> 'localhost',
		'port'			=> '9090',
		'plugin'		=> '/plugins/userService/userservice',
		'secret'		=> 'SuperSecret',
		
		'useCurl'		=> true,
		'useSSL'		=> false,
		
		'subscriptions'	=> array(-1, 0, 1, 2)
	);
	
	private function doRequest($parameters = array())
	{
		$base = ($this->useSSL) ? "https" : "http";
		$url = $base . "://" . $this->host;
		
		if($this->useCurl)
		{
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url . $this->plugin);
			curl_setopt($ch, CURLOPT_PORT, $this->port);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$result = curl_exec ($ch);
			
			curl_close ($ch);
		}
		else
		{
			$fopen = fopen($url . ":" . $this->port . $this->plugin . "?" . http_build_query($parameters), 'r');
			
			$result = fread($fopen, 1024);
			
			fclose($fopen);
		}
		
		return $result;
	}
	
	public function add($username, $password, $name = false, $email = false, $groups = false)
	{
		$parameters = array(
			'type'		=> 'add',
			'secret'	=> $this->secret,
			'username'	=> $username,
			'password'	=> $password
		);
		
		// Name add request
		if(is_string($name) && !empty($name))
		{
			$parameters = array_merge($parameters, array(
				'name' => $name
			));
		}
		
		// Email add request
		if(is_string($email) && !empty($email))
		{
			$parameters = array_merge($parameters, array(
				'email' => $email
			));
		}
		
		// Groups add request
		if(is_array($groups) && !empty($groups))
		{
			$parameters = array_merge($parameters, array(
				'groups' => implode(',', $groups)
			));
		}
		
		return $this->doRequest($parameters);
	}
	
	public function delete($username)
	{
		return $this->doRequest(array(
			'type'		=> 'delete',
			'secret'	=> $this->secret,
			'username'	=> $username
		));
	}
	
	public function disable($username)
	{
		return $this->doRequest(array(
			'type'		=> 'disable',
			'secret'	=> $this->secret,
			'username'	=> $username
		));
	}
	
	public function enable($username)
	{
		return $this->doRequest(array(
			'type'		=> 'enable',
			'secret'	=> $this->secret,
			'username'	=> $username
		));
	}
	
	public function update($username, $password = false, $name = false, $email = false, $groups = false)
	{
		$parameters = array(
			'type'		=> 'update',
			'secret'	=> $this->secret,
			'username'	=> $username
		);
		
		// Password change request
		if(is_string($password) && !empty($password))
		{
			$parameters = array_merge($parameters, array(
				'password' => $password
			));
		}
		
		// Name change request
		if(is_string($name) && !empty($name))
		{
			$parameters = array_merge($parameters, array(
				'name' => $name
			));
		}
		
		// Email change request
		if(is_string($email) && !empty($email))
		{
			$parameters = array_merge($parameters, array(
				'email' => $email
			));
		}
		
		// Groups change request
		if(is_array($groups) && !empty($groups))
		{
			$parameters = array_merge($parameters, array(
				'groups' => implode(',', $groups)
			));
		}
		
		return $this->doRequest($parameters);
	}

	public function add_roster($username, $item_jid, $name = false, $subscription = false)
	{
		$parameters = array(
			'type'			=> 'add_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $item_jid
		);
		
		// Name add request
		if(is_string($name) && !empty($name))
		{
			$parameters = array_merge($parameters, array(
				'name' => $name
			));
		}
		
		// Subscription add request
		if($subscription !== false && in_array($subscription, $this->subscriptions))
		{
			$parameters = array_merge($parameters, array(
				'subscription' => $subscription
			));
		}
		
		return $this->doRequest($parameters);
	}
	
	public function update_roster($username, $item_jid, $name = false, $subscription = false)
	{
		$parameters = array(
			'type'			=> 'update_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $item_jid
		);
		
		// Name update request
		if(is_string($name) && !empty($name))
		{
			$parameters = array_merge($parameters, array(
				'name' => $name
			));
		}
		
		// Subscription update request
		if($subscription !== false && in_array($subscription, $this->subscriptions))
		{
			$parameters = array_merge($parameters, array(
				'subscription' => $subscription
			));
		}
		
		return $this->doRequest($parameters);
	}
	
	public function delete_roster($username, $item_jid)
	{
		return $this->doRequest(array(
			'type'			=> 'delete_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $item_jid
		));
	}
	
	
	public function __construct() {	}
	
	public function __get($name)
	{
		if (array_key_exists($name, $this->_settings))
		{
			return $this->_settings[$name];
		}
		
		return null;
	}
	
	public function __set($name, $value)
	{
		$this->_settings[$name] = $value;
	}
}
