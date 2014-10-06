<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| ACCESS LEVEL
|--------------------------------------------------------------------------
|
| Sets user access level. 0 is public access; higher values indicate increasing
| levels of administrative responsiblity.
|
*/
global $my;
$access['levels'][] = 'Registered';
$access['levels'][] = 'Admnistrator';
$access['levels'][] = 'Super Administrator';


if( isset( $my->id) ) {
    $access['id']        = $my->id;
    $access['name']      = $my->name;
    $access['email']     = $my->email;
    $access['user_type'] = $my->usertype;   
} else {
    $access['id']        = 0;
    $access['name']      = '';
    $access['email']     = '';
    $access['user_type'] = 'Public';
}
foreach($access['levels'] as $key => $level) {
    if($my->usertype == $level) {
        $access['user_level'] = $key;
    } else {
        $access['user_level'] = 0;
    }
}
?>
