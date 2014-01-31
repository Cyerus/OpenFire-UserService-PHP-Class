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
	 * @var		string[]	$settings
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
	 * @return	false|string[]
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
	 * @param	string	$result
	 * @return	false|string[]
	 */
	private function analyzeResult($result)
	{
		if(preg_match('#^<error>[A-Za-z0-9 ]+</error>$#', $result, $matches))
		{
			return array(
				'result'	=> false,
				'message'	=> $matches[0]
			);
		}
		elseif(preg_match('#^<result>[A-Za-z0-9 ]+</result>$#', $result, $matches))
		{
			return array(
				'result'	=> true,
				'message'	=> $matches[0]
			);
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Sends the actual POST request to OpenFire's UserService using cURL
	 * 
	 * @param	string		$url			URL
	 * @param	string[]	$parameters		Parameters
	 * @return	string
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
	 * @return	string
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
	 * @param	string			$username	Username
	 * @param	string			$password	Password
	 * @param	string|false	$name		Name	(Optional)
	 * @param	string|false	$email		Email	(Optional)
	 * @param	string[]|false	$groups		Groups	(Optional)
	 * @return	false|string[]
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
		$this->addParameter($parameters, 'name', $name);
		
		// Email add request
		$this->addParameter($parameters, 'email', $email, 1);
		
		// Groups add request
		$this->addParameter($parameters, 'groups', $groups, 3);
		
		return $this->doRequest($parameters);
	}
	
	/**
	 * Deletes an OpenFire user
	 * 
	 * @param	string		$username	Username
	 * @return	false|string[]
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
	 * @return	false|string[]
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
	 * @return	string[]|false
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
	 * @param	string			$username	Username
	 * @param	string|false	$password	Password (Optional)
	 * @param	string|false	$name		Name (Optional)
	 * @param	string|false	$email		Email (Optional)
	 * @param	string[]|false	$groups		Groups (Optional)
	 * @return	false|string[]
	 */
	public function updateUser($username, $password = false, $name = false, $email = false, $groups = false)
	{
		$parameters = array(
			'type'		=> 'update',
			'secret'	=> $this->secret,
			'username'	=> $username
		);
		
		// Password change request
		$this->addParameter($parameters, 'password', $password);

		// Name change request
		$this->addParameter($parameters, 'name', $name);
		
		// Email change request
		$this->addParameter($parameters, 'email', $email, 1);
		
		// Groups change request
		$this->addParameter($parameters, 'email', $email, 3);
		
		return $this->doRequest($parameters);
	}

	/**
	 * Adds to this OpenFire user's roster
	 * 
	 * @param	string			$username		Username
	 * @param	string			$itemJid		Item JID
	 * @param	string|false	$name			Name		 (Optional)
	 * @param	int|false		$subscription	Subscription (Optional)
	 * @return	false|string[]
	 */
	public function addToRoster($username, $itemJid, $name = false, $subscription = false)
	{
		$parameters = array(
			'type'			=> 'add_roster',
			'secret'		=> $this->secret,
			'username'		=> $username,
			'item_jid'		=> $itemJid
		);
		
		// Name update request
		$this->addParameter($parameters, 'name', $name);
		
		// Subscription update request
		$this->addParameter($parameters, 'subscription', $subscription, 2);
		
		return $this->doRequest($parameters);
	}
	
	/**
	 * Updates this OpenFire user's roster
	 * 
	 * @param	string			$username		Username
	 * @param	string			$itemJid		Item JID
	 * @param	string|false	$name			Name		 (Optional)
	 * @param	int|false		$subscription	Subscription (Optional)
	 * @return	false|string[]
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
		$this->addParameter($parameters, 'name', $name);
		
		// Subscription update request
		$this->addParameter($parameters, 'subscription', $subscription, 2);
		
		return $this->doRequest($parameters);
	}
	
	/**
	 * Removes from this OpenFire user's roster
	 * 
	 * @param	string	$username	Username
	 * @param	string	$itemJid	Item JID
	 * @return	false|string[]
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
	 * Validates an Email address
	 * 
	 * @param	string	$email	Email
	 * @return	bool
	 */
	private function validateEmail($email)
	{
		if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates a string
	 * 
	 * @param	string	$string	String
	 * @return	bool
	 */
	private function validateString($string)
	{
		if(!empty($string) && is_string($string))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates a subscription
	 * 
	 * @param	int|false	$value	Value
	 * @return	bool
	 */
	private function validateSubscription($subscription)
	{
		if($subscription !== false && in_array($subscription, $this->subscriptions))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates groups
	 * 
	 * @param	int[]	$value	Value
	 * @return	bool
	 */
	private function validateGroups($groups)
	{
		if(is_array($groups) && !empty($groups))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Add a possible parameter
	 * 
	 * @param	string[]			$parameters		Parameters
	 * @param	string				$paramName		Parameter name
	 * @param	string|int|bool		$paramValue		Parameter value
	 * @param	int					$paramType		Parameter type
	 * @return	void
	 */
	private function addParameter(&$parameters, $paramName, $paramValue, $paramType = 0)
	{
		if	(($paramType == 0 && validateString($paramValue) && !empty($paramValue)) ||
			( $paramType == 1 && validateEmail($paramValue) && !empty($paramValue)) ||
			( $paramType == 2 && validateSubscription($paramValue)))
		{
			$parameters = array_merge($parameters, array(
				$paramName => $paramValue
			));
		}
		elseif($paramType == 3 && validateGroups($paramValue))
		{
			$parameters = array_merge($parameters, array(
				$paramName => implode(',', $paramValue)
			));
		}
	}
	
	/**
	 * Simple construct (unused)
	 */
	public function __construct() {	}
	
	/**
	 * Stores a configuration parameter
	 * 
	 * @param	string	$name	Name
	 * @return	string|bool|int|null
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
	 * @param	string				$name	Name
	 * @param	string|bool|int		$value	Value
	 * @return	void
	 */
	public function __set($name, $value)
	{
		$this->settings[$name] = $value;
	}
}
