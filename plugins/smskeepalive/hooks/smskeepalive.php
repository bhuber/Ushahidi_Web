<?php defined('SYSPATH') or die('No direct script access.');
//require_once 'MessageParser.class.php';
/**
 * smskeepalive Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class smskeepalive {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
		$this->settings = ORM::factory('smskeepalive')
				->where('id', 1)
				->find();
		
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		Event::add('ushahidi_action.message_sms_add', array($this, '_parse_sms'));		
	}

	/**
	 * Check the SMS message and parse it
	 */
	public function _parse_sms()
	{
		//the message
		$raw_message = Event::$data->message;
		$from = Event::$data->message_from;
		$reporterId = Event::$data->reporter_id;
		$message_date = Event::$data->message_date;
		
		$p = new MessageParser($raw_message,null,null);
        $message_type = $p->getMessageType();
        $message = $p->getMessage();
		

		//check to see if we're using the white list, and if so, if our SMSer is whitelisted
		/*$num_whitelist = ORM::factory('smskeepalive_whitelist')
		->count_all();
		if($num_whitelist > 0)
		{
			//check if the phone number of the incoming text is white listed
			$whitelist_number = ORM::factory('smskeepalive_whitelist')
				->where('phone_number', $from)
				->count_all();
			if($whitelist_number == 0)
			{
				return;
			}
		}
		*/
		//the delimiter
		//$delimiter = $this->settings->delimiter;
		
		//the code word
		//$code_word = $this->settings->code_word;
		
		
		//echo Kohana::debug($message_elements);
		
		$lat = '7.77';
		//longitude
		$lon = '-9.42';
		
		//title
		$title = $message;
		
		$location_description = $p->getLocation();
		
		$description = $message."\n\r\n\rThis reported was created automatically via SMS.";
		
		$categories = array();
		
		//for testing:
		/*
		echo "lat: ". $lat."<br/>";
		echo "lon: ". $lon."<br/>";
		echo "title: ". $title."<br/>";
		echo "description: ". $description."<br/>";
		echo "category: ". Kohana::debug($categories)."<br/>";
		*/
		
		// STEP 1: SAVE LOCATION
		$location = new Location_Model();
		$location->location_name = $location_description;
		$location->latitude = $lat;
		$location->longitude = $lon;
		$location->location_date = $message_date;
		$location->save();
		//STEP 2: Save the incident
		$incident = new Incident_Model();
		$incident->location_id = $location->id;
		$incident->user_id = 0;
		$incident->incident_title = $title;
		$incident->incident_description = $description;
		$incident->incident_date = $message_date;
		$incident->incident_dateadd = $message_date;
		$incident->incident_mode = 2;
		// Incident Evaluation Info
		$incident->incident_active = 1;
		$incident->incident_verified = 1;
		//Save
		$incident->save();
		
		//STEP 3: Record Approval
        //ToDo: depending on message type we create a report or not
		$verify = new Verify_Model();
		$verify->incident_id = $incident->id;
		$verify->user_id = 0;
		$verify->verified_date = date("Y-m-d H:i:s",time());
		if ($incident->incident_active == 1)
		{
			$verify->verified_status = '1';
		}
		elseif ($incident->incident_verified == 1)
		{
			$verify->verified_status = '2';
		}
		elseif ($incident->incident_active == 1 && $incident->incident_verified == 1)
		{
			$verify->verified_status = '3';
		}
		else
		{
			$verify->verified_status = '0';
		}
		$verify->save();
		
		
		// STEP 3: SAVE CATEGORIES
		/*
		ORM::factory('Incident_Category')->where('incident_id',$incident->id)->delete_all();		// Delete Previous Entries
		foreach($categories as $item)
		{
			if(is_numeric($item))
			{
				$incident_category = new Incident_Category_Model();
				$incident_category->incident_id = $incident->id;
				$incident_category->category_id = $item;
				$incident_category->save();
			}
		}
        */
		//don't forget to set incident_id in the message
		Event::$data->incident_id = $incident->id;
		Event::$data->save();
		
	}
	

}

new smskeepalive;