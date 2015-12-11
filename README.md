# ModulesGarden CRM for WHMCS API
This is a simple class which allows you to interact with the WHMCS API using PHP and ModulesGarden CRM API. Examples included

> Build for ModulesGarden CRM in **2.2.0** version

## Usage

#### Require Connection Class
```php
require_once 'whmcs.class.php';
```

#### Initialize Connection
```php
// Url for curl, must point to <main whmcs folder>/includes/api.php
$apiUrl       = 'http://www.your.whmcs.installation.com/includes/api.php';
$apiLogin     = 'api-user';
$apiPassword  = 'api-user-password';
$apiAccessKey = 'optional-access-key';

// set up whmcs connection
$whmcs = new WHMCS($apiUrl, $apiLogin, $apiPassword, $apiAccessKey);

// In case of using Htaccess to access this file you can set up credentials
$whmcs->setHtaccessAuth('login', 'password');
```

Variable  | Description
------------- | -------------
`$apiUrl`  | The URL should point to your WHMCS install's 
`$apiLogin`  | username of the WHMCS Administrator that will be used to authenticate (this admin role need API access)  
`$apiPassword`  | password for admins 
`$apiAccessKey`  | alternative you can use  Access Keys instead IP Restriction for API calls. For more information about the access key; see [docs](http://docs.whmcs.com/API:Access_Keys)

#### Execute WHMCS API method

1. `WHMCS` class main job is to call WHMCS method. For full list of supported method check [API Reference](http://docs.whmcs.com/API). Here we will focus on syntax how to call each method

> example [**Get Client Details**](http://docs.whmcs.com/API:Get_Clients_Details)  

Is already wrapped in simple method:
```php
$uid        = 1;    // client ID
$email      = '';   // client Email
$withStats  = true; // request user statistics
$whmcs->getClient($uid = 0, $email = '', $withStats);
```

This can be done in different approach, by our connection class:

```php
$action = 'getclientsdetails';
$values = array(
    'clientid'  => 1,       
    // we are going to comment this for now. 
    // Acording to WMCS docs: Please note either the clientid or email is required
    //'email'     => '',      
    'stats'     => true,
);
$whmcs->callAPI($action, $values);
```

In similar approach, you can many diferent WHMCS API methods.


#### Execute ModulesGarden CRM API method
We provided for `WHMCS` class special method that wrap parameters in correct format for CRM.  
See: examples check [examples.php](examples.php) file

1. Syntax
```php
$action = 'contacts/getNotes';   // requested CRM action
$params = array(                 // Additional parameters for CRM action (optional, not every method require additional parameters)
    'id' => 2, // ID of requested contact (in this case, required)
);

// Get List of Notes from Contact
$whmcs->callCRM($action, $params);
```

2. Example

```php
$action = 'contacts/addNote';   // requested CRM action
$params = array( 
    'id'         => 1,  // Contact ID (contact cant be in Archive)
    'admin'      => 1,  // Admin ID as an author of this note (optional, if not proivded, it will be set as Id of admin who was used in creditails)
    'content'    => 'This is sample <b>note</b> text.',  // note content (support html code) (required)
);

// Create Note for Contact
$whmcs->callCRM($action, $params);
```

### Community
Here are some Intresting project from community:
- [whmcs-php](https://github.com/TheKatastrophe/whmcs-php) by @TheKatastrophe
- [crm-api-test.php for older CRM](https://gist.github.com/picq/e5ca60cbf0de002722f9) by @picq

Thanks to @TheKatastrophe for oryginal [whmcs-php](https://github.com/TheKatastrophe/whmcs-php) that lead us to this project. `WHMCS` class in this project have similar syntax, and have some roots from oryginal @TheKatastrophe idea.

## CRM API
See the [API Reference](CRM_API.md).

## WHMCS API
See the [API Reference](http://docs.whmcs.com/API).
