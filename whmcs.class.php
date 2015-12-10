<?php

use \Exception;

/**
 * Class to connect WHMCS instance
 * Few sample implementations of oryginal WHMCS API methods
 * and a wrapper to Integrate API calls with ModulesGarden CRM For WHMCS module
 *
 * relay on json response type
 * and php curl
 *
 * @author  Piotr Sarzyński <piotr@sarzynski.org> (github.com/picq)
 */
class WHMCS
{
    /**
     * Contain url to whmcs/includes/api.php
     *
     * @var     string
     */
    protected $url;


    /**
     * Admin username to authorize in WHMCS (role needs to have 'API Access')
     *
     * @var     string
     */
    protected $username;


    /**
     * Admin password to authorize in WHMCS (role needs to have 'API Access')
     *
     * @var     string
     */
    protected $password;


    /**
     * Access Key for API call, as alternative method to ip restriction
     *
     * @var     string
     * @link    http://docs.whmcs.com/API:Access_Keys
     */
    protected $accessKey;


    /**
     * Username for hataccess authorization
     *
     * @var     string
     */
    protected $hataccessUsername;


    /**
     * Password for hataccess authorization
     *
     * @var     string
     */
    protected $hataccessPassword;


    /**
     * Container for basic parameters send to API
     * we are going to generate this once, then only existing
     *
     * @var     array
     */
    protected $postParams = array();


    /**
     * Parse response to format
     * Currently only json supported
     *
     * @var     string
     */
    const RESPONSE_TYPE     = 'json';


    /**
     * Curl request timeout
     *
     * @var     integer
     */
    const RESPONSE_TIMEOUT  = 300;


    /**
     * Constructor, set basic variables
     *
     * @param string $url         full url to whmcs/includes/api.php
     * @param string $username    admin usrename
     * @param string $password    admin password
     */
    public function __construct($url, $username, $password)
    {
        $this->url       = $url;
        $this->username  = $username;
        $this->password  = $password;
    }


    /**
     * If our $url destination is protected by Htaccess authorization
     * Set credentials
     *
     * @param string $login login for Htaccess authorization
     * @param string $pass  password for Htaccess authorization
     */
    public function setHtaccessAuth($login, $pass)
    {
        $this->hataccessUsername = $login;
        $this->hataccessPassword = $pass;
    }


    /**
     * Set up Acess Key for API calls
     *
     * @param string $key
     * @link  http://docs.whmcs.com/API:Access_Keys
     */
    public function setAccessKey($key = '')
    {
        $this->accessKey = $key;
    }


    /**
     * Generate/obtain main parameters
     * Set up basic variables, like username/password etc
     *
     * @return array
     */
    protected function getMainPostParams()
    {
        // if already set just return
        if(is_array($this->postParams) && !empty($this->postParams)) {
            return $this->postParams;
        }

        // keep this data in object
        $this->postParams = array(
            'username'      => $this->username,
            'password'      => md5($this->password),
            'responsetype'  => self::RESPONSE_TYPE,
        );

        // access key ?
        if($this->accessKey != '') {
            $this->postParams['accesskey'] = $this->accessKey;
        }

        return $this->postParams;
    }


    /**
     * Wrap all our parameters to single array
     * Basically merge custom parameters with main one as authorization
     *
     * @param string    $action requested action
     * @param array     $params additional parameters
     * @return array
     */
    protected function generatePostParams($action, array $params = array())
    {
        // authorization etc
        $post = $this->getMainPostParams();

        // set up action
        $post['action'] = $action;

        // set up additional parameters
        foreach($params as $k => $v) {
            $post[$k] = $v;
        }

        return $post;
    }


    /**
     * Create and return Curl handler
     * Also set proper flags
     *
     * @param string $queryString already parsed string as request content
     * @return curl handler
     */
    protected function getNewCurlHandler($queryString)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::RESPONSE_TIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


        // uf there is username (pass might be empty so single condition)
        if(!empty($this->hataccessUsername)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->hataccessUsername}:{$this->hataccessPassword}");
        }

        return $ch;
    }


    /**
     * Try to decode API response from JSON format
     *
     * @param mixed $response   what we obtain from api
     * @return mixed
     * @throws WhmcsException
     */
    protected function formatResponse($response)
    {
        // decode from json, since this wrapper handle only json format
        $return = json_decode($response, true);

        // check for errors, just in case
        if($return === null && json_last_error() !== JSON_ERROR_NONE)
        {
            switch (json_last_error())
            {
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error = 'Unknown error';
                    break;
            }
            throw new WhmcsException($error);
        }

        return $return;
    }


    /**
     * Perform WHMCS API call
     *
     * @param string    $action requested action
     * @param array     $params additional parameters for action
     * @return array    response parsed to array
     * @throws WhmcsException   in error
     */
    public function callAPI($action = '', array $params = array())
    {
        // obtain main data for auth
        $post = $this->generatePostParams($action, $params);

        // build whole string for curl
        $queryString = http_build_query($post);

        // curl
        $ch = $this->getNewCurlHandler($queryString);

        // perform
        $response = utf8_decode(curl_exec($ch));

        // parse response
        if(curl_error($ch)) {
            throw new WhmcsException(curl_error($ch));
        }
        // close connection
        curl_close($ch);

        return $this->formatResponse($response);
    }


    /**
     * Wrapper of standard WHMCS api call
     * to use with ModulesGarden CRM for WHMCS module
     * since this module hase its own API that is supported by WHMCS API
     *      (
     *          TL;TR: technical stuff
     *
     *          request to whmcs api, to run whmcs command 'mgcrm'
     *          'mgcrm' - its custom API method that is added to WHMCS by CRM
     *          'mgcrm' method permit two variable (as we need to be transparent by whmcs api itself)
     *              'mgcrmaction' its CRM API method name that we request
     *              'mgcrmparams' its array with parameters that can be send for requested method
     *
     *      )
     *
     *
     * This is available from CRM version 2.2.x
     *
     * @notice          this is only wrapper, CRM hve its own documentation available methods to use, and parameters
     * @param string    $action     CRM method to execute
     * @param array     $params     selected method additional parameters
     * @return array    response parsed to array
     */
    public function callCRM($action = '', array $params = array())
    {
        return $this->callAPI('mgcrm', array(
            'mgcrmaction' => $action,
            'mgcrmparams' => $params,
        ));
    }



    /**
     * WHMCS COMMAND
     *
     * API:Validate Login
     * This command can be used to validate an email address and password against a registered user in WHMCS
     *
     *
     * @param   string    $username   the email address of the user trying to login
     * @param   type      $password   the password they supply for authentication
     * @link    http://docs.whmcs.com/API:Validate_Login
     * @return  mixed
     */
    public function authenticate($username = '', $password = '')
    {
        return $this->callAPI("validatelogin", array(
                'email'     => $username,
                'password2' => $password,
            )
        );
    }


    /**
     * WHMCS COMMAND
     *
     * API:Get Clients Details
     * This command is used to retrieve all the data held about a client in the WHMCS System for a given ID or email address
     *
     *
     * @param   type $uid               the id number of the client
     * @param   type $email             the email address of the client
     * @param   boolean $withStats      request user stats
     * @throws  WhmcsException          if both: $uid or $email are not provided
     * @link    http://docs.whmcs.com/API:Get_Clients_Details
     * @return  mixed
     */
    public function getClient($uid = 0, $email = '', $withStats = false)
    {
        $params = array();

        if(is_numeric($uid) && $uid > 0) {
            $params["clientid"] = $uid;
        } elseif(!empty($email)) {
            $params["email"]    = $email;
        } else {
            throw new WhmcsException('Not Invalid Request parameter. at least one must be priovided (uid/email)');
        }

        if($withStats === true) {
            $params['stats'] = true;
        }

        return $this->callAPI("getclientsdetails", $params);
    }


    /**
     * WHMCS COMMAND
     *
     * API:Get Stats
     * This command is used to generate current stats
     *
     *
     * @return  mixed
     * @throws  WhmcsException
     * @link    http://docs.whmcs.com/API:Get_Stats
     */
    public function getStats()
    {
        return $this->callAPI("getstats");
    }

}

/**
 * This class may thow WhmcsException
 * Its basic extends nothing fancy
 */
class WhmcsException extends Exception {}
