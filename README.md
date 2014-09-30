WebEx API wrapper class

 ----------------------------------
 - Turning the crypticly documented WebEx API into a robust tool in PHP.  
 
Usage
 ----------------------------------
 include('WebexAPI.class.php');
 $webex_list = new WebexAPI($webex_db_item);
   /* 
    * REQUIRED VALUES:
      $webex_db_item->webex_user, 
      $webex_db_item->webex_password, 
      $webex_db_item->webex_url, 
      $webex_db_item->webex_sid, $webex_db_item->webex_pid
    *
    */
 $events = $webex_list->getWebex();
 
TODO:
 ----------------------------------
 - Plenty!
 
 
