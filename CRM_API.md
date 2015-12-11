ModulesGarden CRM for WHMCS: API Reference
=====
for CRM in **2.2.0** version

## For now please review examples.php. More detailed readme file will be published

## Available Methods 
- Mainly for Rererences Objects
    + [`whmcs/getVersion`](examples.php#L18)
    + [`settings/getContactTypes`](examples.php#L32)
    + [`settings/getContactStatuses`](examples.php#L36)
    + [`settings/getFieldGroups`](examples.php#L40)
    + [`settings/getFields`](examples.php#L44)
    + [`settings/getFieldsWithGroups`](examples.php#L48)
    + [`campaigns/getList`](examples.php#L52)
    + [`followups/getTypes`](examples.php#L56)
- Contacts
    + [`contacts/get`](examples.php#L97)
    + [`contacts/getSingle`](examples.php#L106)
    + [`contacts/getNotes`](examples.php#L112)
    + [`contacts/getFollowups`](examples.php#L118)
    + [`contacts/addFollowup`](examples.php#L124)
    + [`contacts/followups/addReminder`](examples.php#L135)
    + [`contacts/addNote`](examples.php#L157)
    + [`contacts/add`](examples.php#L167)
- Follow-ups
    + [`followups/getForAdmin`](examples.php#L74)
    + [`followups/getFor`](examples.php#L80)
    + [`followups/getReminders`](examples.php#L87)
- Notifications
    + [`notifications/get`](examples.php#L65)


## Response

See comments for response to method `whmcs/getVersion`:
```php
$response = $whmcs->callCRM('whmcs/getVersion');
// our $response is parsed to array:
$response = array(
    'result' => "success",   // response result from whmcs API
    'data' => array(         // response body from CRM
        'message'       => "test CRM API call from WHMCS",
        'moduleVersion' => "2.2.0",
        'moduleWikiUrl' => http://www.docs.modulesgarden.com/CRM_For_WHMCS",
    ),
);
```
