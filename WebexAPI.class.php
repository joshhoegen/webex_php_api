<?php 
class WebexAPI {
  function WebexAPI($api_creds) {
    if (is_array($api_creds)) {
      $api_creds = (object) $api_creds;
    }
    if(empty($api_creds->webex_user) || empty($api_creds->webex_password)|| 
      empty($api_creds->webex_pid)|| empty($api_creds->webex_sid)|| empty($api_creds->webex_url) ){
      	$this->webex_error_msg = 'Values: webex_user, webex_password, webex_pid, webex_sid & webex_url can not be blank.';
    } else {
    	$this->webex_user = $api_creds->webex_user;
	    $this->webex_password = $api_creds->webex_password;
	    $this->webex_pid = $api_creds->webex_pid;
	    $this->webex_sid = $api_creds->webex_sid;
	    $this->webex_url = $api_creds->webex_url;
	    $this->webex_list_type = isset($api_creds->webex_list_type) ? $api_creds->webex_list_type : 'event';
	    $this->webex_start_record = isset($api_creds->webex_start_record) ? $api_creds->webex_start_record : 0;
	    $this->webex_max_record = isset($api_creds->webex_max_record) ? $api_creds->webex_max_record : 100;
	    $this->webex_date_start = isset($api_creds->webex_date_start) ? date('m/d/Y G:H:i', strtotime($api_creds->webex_date_start)) : date('m/d/Y G:H:i');
	    $this->webex_max_date = isset($api_creds->webex_max_date) ? date('m/d/Y G:H:i', strtotime($api_creds->webex_max_date)) : date('m/d/Y G:H:i', strtotime('+3months'));
	    $this->webex_date_time_zone = isset($api_creds->webex_time_zone) ? $api_creds->webex_time_zone : 11; // Default NY, America
	    // Must be XML. Most useful way to do this
	    // change sort to order_by
	    $this->webex_sort = isset($api_creds->webex_sort) ? $api_creds->webex_sort : '<orderBy>EVENTNAME</orderBy>
		  <orderAD>ASC</orderAD>
		  <orderBy>STARTTIME</orderBy>
		  <orderAD>ASC</orderAD>'; 
	    $this->webex_response = '';
	    $this->webex_error_msg = '';
	    $this->webex_protocol = isset($api_creds->webex_protocol) ? $api_creds->webex_protocol : 'http';
    }
    
  }
  
  private function buildXML() {
    $webex_post = new stdClass;
    $webex_post->UID = $this->webex_user;
    $webex_post->PWD = $this->webex_password;
    $webex_post->SID = $this->webex_sid;
    $webex_post->PID = $this->webex_pid;
    $webex_post->XML = '<?xml version="1.0" ?>
    <serv:message xmlns:serv="http://www.webex.com/schemas/2002/06/service" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
      <header>
        <securityContext>
          <webExID>' . $this->webex_user . '</webExID>
          <password>' . $this->webex_password . '</password>
          <siteID>' . $this->webex_sid . '</siteID>
          <partnerID>' . $this->webex_pid . '</partnerID>
        </securityContext>
      </header>
      <body><bodyContent xmlns:meet="http://www.webex.com/schemas/2002/06/service/' . strtolower($this->webex_list_type) . '"
        xsi:type="java:com.webex.service.binding.' . strtolower($this->webex_list_type) . '.Lstsummary' . ucwords($this->webex_list_type) . '">
          <listControl>
            <startFrom>' . $this->webex_start_record . '</startFrom>
            <maximumNum>' . $this->webex_max_record . '</maximumNum>
          </listControl>
          <order>
            ' . $this->webex_sort . '
          </order>
          <dateScope>
            <startDateStart>' . $this->webex_date_start . '</startDateStart>
            <startDateEnd>' . $this->webex_max_date . '</startDateEnd>
            <timeZoneID>' . $this->webex_date_time_zone . '</timeZoneID>
          </dateScope>
        </bodyContent>
      </body>
    </serv:message>';
    
    return $webex_post;
  }
  
  private function postXML() {
    $post_data = $this->buildXML();
    $post_url = $this->webex_protocol.'://'.$this->webex_url . '.webex.com/WBXService/XMLService';
    $post_string = '';
    foreach ($post_data as $data_key => $data_value) {
      $post_string .= '' . $data_key . '=' . urlencode($data_value) . '&';
    }
    $post_string = substr($post_string, 0, -1);
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_POST, count($post_data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (!$response) {
      $this->webex_error_msg = 'ERROR:<br/><b>RESPONSE:</b><span>' . strip_tags($response) . '</span><br/>
        <br/>
        <b>POSTED DATA:</b><span>' . $post_data->XML . '</span>';
    }
    curl_close($ch);
    $this->webex_response = $response;
    return $response;
  }
  
  function getWebex() {
    $events = false;
    $event_type = strtolower($this->webex_list_type);
    if ($response = $this->postXML()) {
      // Parse XML for SUCCESS, output error otherwise
      $xml_obj = new SimpleXMLElement($response);
      // Make sure "meeting" list type works!!!
      $events = $xml_obj->children('serv', true)->body->bodyContent->children($event_type, true)->{$event_type};
    }
    return $events;
  }

  function groupWebex($goup1, $group2){
  	
  }
  
  function getSessionRegistrationLink($session_key) {
    return $this->webex_protocol.'://'.$this->webex_url.'.webex.com/'.$this->webex_url.'/e.php?AT=SINF&MK='.$session_key;
  }
  
  function getErrorMsg() {
    // Returns the error message of the last call.
    return $this->webex_error_msg;
  }
}