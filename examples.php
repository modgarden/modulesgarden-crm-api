<?php

// include our class to connect with WHMCS
require_once __DIR__ . '/whmcs.class.php';

$whmcsApiPath = 'http://www.your.whmcs.installation.com/includes/api.php';


// set up whmcs connection
$whmcs = new WHMCS($whmcsApiPath, 'api-user', 'api-user-password', 'optional-access-key');

// using Htaccess ?
//$whmcs->setHtaccessAuth('login', 'password');

try{

    // test call
    $response = $whmcs->callCRM('whmcs/getVersion');
    dump($response);

    // bring me data for client with id #1
    $response = $whmcs->getClient(1, null, true);
    dump($response);

    ##
    ## This section is related with Object that are used in overall
    ## as assigned to contacts, or relations, etc
    ##

    // each contact have assigned type that is configurable (like Lead/Potential)
    // this call will obtain this types
    $response = $whmcs->callCRM('settings/getContactTypes');
    dump($response);

    // obtain all statuses in system, contact may have assigned one of them
    $response = $whmcs->callCRM('settings/getContactStatuses');
    dump($response);

    // Get all Custom Field Groups
    $response = $whmcs->callCRM('settings/getFieldGroups');
    dump($response);

    // Get all Fields with group assigned to them
    $response = $whmcs->callCRM('settings/getFields');
    dump($response);

    // other approach, get groups with fields assigned to them
    $response = $whmcs->callCRM('settings/getFieldsWithGroups');
    dump($response);

    // obtain campaigns informations
    $response = $whmcs->callCRM('campaigns/getList');
    dump($response);

    // obtain followups possible types
    $response = $whmcs->callCRM('followups/getTypes');
    dump($response);



    ##
    ## Notifications
    ##
    // obtain campaigns informations
    $response = $whmcs->callCRM('notifications/get', array(
        'adminID' => 1, // get for specified admin id
    ));
    dump($response);

    ##
    ## Follow-ups
    ##
    // obtain list of followups for admin
    $response = $whmcs->callCRM('followups/getForAdmin', array(
        'id' => 1,  // Admin ID
    ));
    dump($response);
    
    // obtain list of followups for admin for specific date
    $response = $whmcs->callCRM('followups/getFor', array(
        'adminID' => 1,               // Admin ID (optional)
        'date'    => '2015-10-01',    // requested day, as string. Must be valid to parse by Carbon::parse() method (optional, if not provided it will be set to "today")
    ));
    dump($response);

    // obtain reminders assigned to followup
    $response = $whmcs->callCRM('followups/getReminders', array(
        'id' => 1,  // followup ID
    ));
    dump($response);


    ##
    ## Contacts
    ##
    // obtain array of contacts filterred by some params
    $response = $whmcs->callCRM('contacts/get', array(
        'type'      => 2,           // Contact Type ID (optional)
        'campaign'  => 1,           // ID (optional)
        'admin'     => 1,           // Admin ID (optional)
        'status'    => 1,           // Status ID (optional)
    ));
    dump($response);

    // obtain single contact
    $response = $whmcs->callCRM('contacts/getSingle', array(
        'id' => 2, // ID of requested contact (required)
    ));
    dump($response);

    // get notes for requested contact
    $response = $whmcs->callCRM('contacts/getNotes', array(
        'id' => 2, // ID of requested contact (required)
    ));
    dump($response);

    // get followups for requested contact
    $response = $whmcs->callCRM('contacts/getFollowups', array(
        'id' => 2, // ID of requested contact (required)
    ));
    dump($response);

    // Create Follow-up for Contact
    $response = $whmcs->callCRM('contacts/addFollowup', array(
         'id'         => 8,  // Contact ID (contact cant be in Archive)
         // followup parameters:
         'type'       => 1, // followup type ID (required) (see method 'followups/getTypes')
         'admin'      => 1, // Admin ID assigned to that followup (this admin will see followup on his calendar) (required)
         'date'       => '2015-10-27 10:37:17',     // requested date, as string. Must be valid to parse by Carbon::parse() (required)
         'description'=> 'some text',    // string, basically its description for this particular followup, only for display (optional)
     ));
    dump($response);

    // Create Reminder for Follow-up
    $response = $whmcs->callCRM('contacts/followups/addReminder', array(
        'id'         => 1,  // Contact ID (contact cant be in Archive)
        'followupID' => 1,  // followup ID
        // reminder parameters:
        'type'       => 'email', // or 'sms'    (required)
        'for'        => 'admin', // or 'client' (required) -- it seems tjat type:'sms' for:'client' is not handled now, maybe in further versions
        'target_id'  => 1,       // (required) depending on 'for' it can be  Admin ID, or Client ID. In simple words, who will recieve that reminder
        'template_id'=> 32,      // Template id to use. This have to be ID of record from table `tblemailtemplates` (required)
        'date'       => '2015-10-27 10:37:17',     // requested date, as string. Must be valid to parse by Carbon::parse() method (optional, if not provided it will be set to "today") (required)
        'email'      => array(   // used only for 'email' type and optional
            'cc'    => array(1,2),    // (optional)
                                      // means that when email reminder will be sent,
                                      // copy of that email will be sent to admins with ID specified in this array
                                      // (in this case two admins, with ID=1 and ID=2)
            'reply' => 1,   // (optional)
                            // similar, but this is required only one ID, not array, just single number
        ),
    ));
    dump($response);

    
    // Create Note for Contact
    $response = $whmcs->callCRM('contacts/addNote', array(
        'id'         => 1,  // Contact ID (contact cant be in Archive)
        // note parameters:
        'admin'      => 1,  // Admin ID as an author of this note (optional, if not proivded, it will be set as Id of admin who was used in creditails)
        'content'    => 'This is sample <b>note</b> text.',  // note content (support html code) (required)
    ));
    dump($response);

    
    // Create New Contact
    $response = $whmcs->callCRM('contacts/add', array(
        'status_id'    => 1,    // statis assigned to this contact (required)
        'type_id'      => 1,    // contact type id (required)

        'admin_id'     => 1,    // admin assinged to this contact (optional)
        'client_id'    => 1,    // client assigned to this contact (optional)
        'ticket_id'    => 1,    // ticket assigned to this contact (optional)
        'dynamic' => array(
            // custom fields
            '1' => 'some value',    // set up value for custom field with ID=>1 (assuming it is text/textarea field)
            '2' => array(15,12),    // custom field id => 2. Assuming that this field is "relation" based.
                                    // That mean we defined possible options to choose. Each option have his ID number, in this case we selected to assign two choices
                                    // choice with id=>15, and id=>12 for this field
        ),
        'static'      => array(
            'name'      => 'new Contact name',          // contact name  (required)
            'email'     => 'contact@email.cot.com',     // contact email (optional)
            'phone'     => 'some telephone number',     // contact phone (optional)
            'priority'  => 2,                           // null/0/1 -> low, 2-> medium, 3->important, 4->urgent (optional)
        ),
    ));
    dump($response);

} catch (\Exception $e) {
    dump($e->getMessage());
    dump($e->getTraceAsString());
}


// just a simple helper
function dump()
{
    array_map(function($x) {
        echo '<pre>';
        var_dump($x);
        echo '</pre>';
    }, func_get_args());
}
