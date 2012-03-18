<?php
class twilio {
    public function __construct()
    {
        // Hook into routing
        Event::add('system.pre_controller', array($this, 'add'));
    }
 
    public function add()
    {
		plugin::add_sms_provider("twilio");
    }
 
    public function twilio()
    { # can prob get rid of this later
	    View::factory('twilio/myhtml')->render(TRUE);
    }
 
}
 
//instatiation of hook
new twilio;
