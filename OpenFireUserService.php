<?php

/*
MIT License
Copyright (c) 2013 - 2014 Cyerus, Jordy Wille

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/


/**
 * A simple PHP class for use with the OpenFire UserService plugin.
 *
 * @author Cyerus
 */
class OpenFireUserService
{
	/**
	 * Stores all the default values.
	 * @var string
	 */
	private $settings = array(
		'host'			=> 'localhost',
		'port'			=> '9090',
		'plugin'		=> '/plugins/userService/userservice',
		'secret'		=> 'SuperSecret',
		
		'useCurl'		=> true,
		'useSSL'		=> false,
		
		'subscriptions'	=> array(-1, 0, 1, 2)
	);
	
	
	/**
	 * Forward the POST request and analyze the result
	 * 
	 * @param	string[]	$parameters		Parameters
	 * @return	array
	 */
	private function doRequest($parameters = array())
	{
		$base = ($this->useSSL) ? "https" : "http";
		$url = $base . "://" . $this->host;
		
		if($this->useCurl)
		{
			$result = $this->doRequestCurl($url, $parameters);
		}
		else
		{
			$result = $this->doRequestFopen($url, $parameters);
		}
		
		return $this->analyzeResult($result);
	}
	
	/**
	 * Analyze the result for errors, and reorder the result
	 * 
	 * @param	string[]	$result
	 * @return	array
	 */
	private function analyzeResult($result)
	{
		// TODO
		
		return $result;
	}
	
	/**
	 * Sends the actual POST request to OpenFire's UserService using cURL
	 * 
	 * @param	string		$url			URL
	 * @param	string[]	$parameters		Parameters
	 * @return	array
	 */
	private function doRequestCurl($url, $parameters)
	{
		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL				=> $url . $this->plugin,
			CURLOPT_PORT			=> $this->port,
			CURLOPT_POST			=> true,
			CURLOPT_POSTFIELDS		=> http_build_query($parameters),
			CURLOPT_RETURNTRANSFER	=> true
		));

		$result = curl_exec ($ch);

		curl_close ($ch);
		
		return $result;
	}
	
	/**
	 * Sends the actual POST request to OpenFire's UserService using cURL
	 * 
	 * @param	string		$url			URL
	 * @param	string[]	$parameters		Parameters
	 * @return	array
	 */
	private function doRequestFopen($url, $parameters)
	{
		$fopen = fopen($url . ":" . $this->port . $this->plugin . "?" . http_build_query($parameters), 'r');

		$result = fread($fopen, 1024);

		fclose($fopen);
		
		return $result;
	}
	
	/**
	 * Creates a new OpenFire user
	 * 
	 * @param	string		$username	Username
	 * @param	string		$password	Password
	 * @param	string		$name		Name	(Optional)
	 * @param	string		$email		Email	(Optional)
	 * @param	string[]	$groups		Groups	(Optional)
	 * @return	array 
	 */
	public function addUser($username, $password, $name = false, $email = false, $groups = false)
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
	
	/**
	 * Deletes an OpenFire user
	 * 
	 * @param	string		$username	Username
	 * @return	array
	 */
	public function deleteUser($username)
	{
		return $this->doRequest(array(
			'type'		=> 'delete',
			'secret'	=> $this->secret,
			'username'	=> $username
		));
	}
	
	/**
	 * Disables an OpenFire user
	 * 
	 * @param	string		$username	Username
	 * @return	array
	 */
	public function disableUser($username)
	{
		return $this->doRequest(array(
			'type'		=> 'disable',
			'secret'	=> $this->secret,
			'username'	=> $username
		));
	}
	
	/**
	 * Enables an OpenFire user
	 * 
	 * @param	string		$username	Username
	 * @return	array
	 */
	public function enableUser($username)
	{
		return $this->doRequest(array(
			'type'		=> 'enable',
			'secret'	=> $this->secret,
			'username'	=> $username
		));
	}
	
	/**
	 * Updates an OpenFire user
	 * 
	 * @param string $username Username
	 * @param string $password Password (Optional)
	 * @param string $name Name (Optional)
	 * @param string $email Email (Optional)
	 * @param string[] $groups Groups (Optional)
	 * @return array 
	 */
	public function updateUser($username, $password = false, $name = false, $email = false, $groups = false)
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

	/**
	 * Adds to this OpenFire user's roster
	 * 
	 * @param	string		$username		Username
	 * @param	string		$itemJid		Item JID
	 * @param	string		$name			Name		 (Optional)
	 * @param	int			$subscription	Subscription (Optional)
	 * @return	array 
	 */
	public function addToRoster($username, $itemJid, $name = false, $subscription = false)
	{
		$parameters = array(
			'type'			=> 'add_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $itemJid
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
	
	/**
	 * Updates this OpenFire user's roster
	 * 
	 * @param	string	$username		Username
	 * @param	string	$itemJid		Item JID
	 * @param	string	$name			Name		 (Optional)
	 * @param	int		$subscription	Subscription (Optional)
	 * @return	array 
	 */
	public function updateRoster($username, $itemJid, $name = false, $subscription = false)
	{
		$parameters = array(
			'type'			=> 'update_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $itemJid
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
	
	/**
	 * Removes from this OpenFire user's roster
	 * 
	 * @param string $username Username
	 * @param string $itemJid Item JID
	 * @return array 
	 */
	public function deleteFromRoster($username, $itemJid)
	{
		return $this->doRequest(array(
			'type'			=> 'delete_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $itemJid
		));
	}
	
	/**
	 * Simple construct (unused)
	 */
	public function __construct() {	}
	
	/**
	 * Stores a configuration parameter
	 * 
	 * @param string $name Name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->settings))
		{
			return $this->settings[$name];
		}
		
		return null;
	}
	
	/**
	 * Grabs a configuration parameter
	 * 
	 * @param string $name Name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->settings[$name] = $value;
	}
}
