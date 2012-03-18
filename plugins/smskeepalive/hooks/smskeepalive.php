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

class Geocoder
{
	/* Get lat and lon array from string using Google Geocoding API */
	public function lat_lon_from_text($text)
	{
		$ch = curl_init();
        $ccTLD = self::get_default_ccTLD();
		curl_setopt($ch, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&gl='
            .$ccTLD.'&address='.urlencode($text));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		$json = curl_exec($ch);
		curl_close($ch);

		$lat = NULL;
		$lon = NULL;
        $result = json_decode($json);
        print_r(Kohana::config('settings.default_country'));
		if(isset($result->results[0]->geometry))
		{
			$lat = $result->results[0]->geometry->location->lat;
			$lon = $result->results[0]->geometry->location->lng;
        }

		return array("lat" => $lat, "lon" => $lon, "result" => $result);
	}

    /* Gets the default country as a ccTLD (top level domain ICANN code)
     */
    public static function get_default_ccTLD()
    {
        $country = ORM::factory('country')
            ->where('id', Kohana::config('settings.default_country')
            ->find();
        $iso = strtolower(Util::get_or_get($country[0]->iso, 'US'));
        $ccTLD = $iso === 'gb' ? 'uk' : $iso;
        return $ccTLD;
    }
}

public class Util
{
    public static function get_or_get(&$check, $alternate = NULL) 
    { 
            return (isset($check)) ? $check : $alternate; 
    } 
}

class smskeepalive
{
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
		$raw_message = Event::$data->message; #the raw string
		$from = Event::$data->message_from;#the phone number
		$reporterId = Event::$data->reporter_id;
		$message_date = Event::$data->message_date;
		
		$p = new MessageParser($raw_message,null,null);
	        $message_type = $p->getMessageType();
      		#$message = $p->getMessage();
		$message = $raw_message;		

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

		//$delimiter = $this->settings->delimiter;

		//$code_word = $this->settings->code_word;

		//echo Kohana::debug($message_elements);
		$location_description = $p->getLocation();
		$description = $message."\n\r\n\rThis reported was created automatically via SMS.";

		// STEP 0.9: GET LAT/LON FROM LOCATION	
		$loc = Geocoder::lat_lon_from_text($location_description);
        $lat = $loc['lat'];
        $lon = $loc['lon'];
        $pretty_address = isset($loc['result']->results[0]->formatted_address) ? 
            $loc['result']->results[0]->formatted_address : $location_description;

		// STEP 1: SAVE LOCATION
		$location = new Location_Model();
		$location->location_name = $pretty_address;
		$location->latitude = $lat;
		$location->longitude = $lon;
		$location->location_date = $message_date;
		$location->save();

		//STEP 2: Save the incident
		$incident = new Incident_Model();
		$incident->location_id = $location->id;
		$incident->user_id = 0;
		$incident->incident_title = $message;
		$incident->incident_description = $description;
		$incident->incident_date = $message_date;
		$incident->incident_dateadd = date("Y-m-d H:i:s",time());
		$incident->incident_mode = 2;
		// Incident Evaluation Info
		$incident->incident_active = 1;
		$incident->incident_verified = 1;
		$incident->save();

		//STEP 2.1: don't forget to set incident_id in the message
		Event::$data->incident_id = $incident->id;
		Event::$data->save();
		
		//STEP 3: Record Approval
		$verify = new Verify_Model();
		$verify->incident_id = $incident->id;
		$verify->user_id = 0;
		$verify->verified_date = date("Y-m-d H:i:s",time());
		$verify->verified_status = '3'; # active & verified
		$verify->save();
		
		// STEP 4: SAVE CATEGORIES (get from parser)
	        $categories = ORM::factory("category")
         		->where('category_title', $message_type)
           		->find();
       		if($categories->loaded)
        	{
			$incident_category = new Incident_Category_Model();
			$incident_category->incident_id = $incident->id;
			$incident_category->category_id = $categories->id;
			$incident_category->save();           
        	}//endif
	}//endfunc
}//endclass

new smskeepalive;
