<?php
class twilio {
    public function __construct()
    {
        // Hook into routing
        Event::add('system.pre_controller', array($this, 'add'));
    }
 
    public function add()
    {
	    // Hook into main_sidebar event and call the twilio method
   	 Event::add('ushahidi_action.main_sidebar', array($this, 'twilio'));
    }
 
    public function twilio()
    {
        // Print the words 'Hello World' in the front page side bar
        #echo "Hello World!!!";
	View::factory('twilio/myhtml')->render(TRUE);
    }
 
}
 
//instatiation of hook
new twilio;
