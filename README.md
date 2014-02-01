# PHP OpenFireUserService
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Cyerus/OpenFire-UserService-PHP-Class/badges/quality-score.png?s=4b432225f3bcb77877b2c473d588808c7c30444f)](https://scrutinizer-ci.com/g/Cyerus/OpenFire-UserService-PHP-Class/)

Copyright (C) 2014 by Cyerus, Jordy Wille, 
All rights reserved.

Php OpenFire UserService, or POFUS, is a simple PHP class designed
to work with the OpenFire UserService plugin. It allows for remote
user management of the OpenFire server.

## LICENSE
Php OpenFireUserService, or POFUS, is licensed under a MIT style license, 
see LICENSE.txt for further information

## FEATURES
- Simple no-nonsense class for the OpenFire UserService plugin
- Lightweight and fast

## REQUIREMENTS
- PHP 5.4+

## INSTALLATION
Download and load the lib/OpenFireUserService.php file, all there
is to it really.

## POFUS Usage
This is a very basic example, which should be able to run on its own.

```php
<?php
require_once ('lib/OpenFireUserService.php');

// Create the OpenFireUserService object.
$pofus = new OpenFireUserService();

// Set the required config parameters
$pofus->secret = "SuperSecret";
$pofus->host = "jabber.yourserver.com";
$pofus->port = "9090";  // default 9090

// Optional parameters (showing default values)
$pofus->useCurl = true;
$pofus->useSSL = false;
$pofus->plugin = "/plugins/userService/userservice";  // plugin folder location

// Add a new user to OpenFire and add him to a group
$result = $pofus->addUser('Username', 'Password', 'Real Name', 'email@email.tld', array('Group 1'));

// Check result if command is succesful
if($result) {
    // Display result, and check if it's an error or correct response
    echo ($result['result']) ? 'Success: ' : 'Error: ';
    echo $result['message'];
} else {
    // Something went wrong, probably connection issues
}
```

## Problems / Bugs
If you find any problems with POFUS, please use githubs issue tracker at 
https://github.com/Cyerus/OpenFire-UserService-PHP-Class/issues

## LINKS
- [Github](https://github.com/Cyerus/OpenFire-UserService-PHP-Class/)

## CONTACT
- Cyerus <cyerus.eve@gmail.com>

## ACKNOWLEDGEMENTS
- POFUS is written in [PHP](http://php.net)
- OpenFire (http://www.igniterealtime.org/projects/openfire/)
- OpenFire UserService plugin (http://www.igniterealtime.org/projects/openfire/plugins.jsp)
