<?php
class Twilio_Controller extends Controller {
	public function sms()
	{
		$view = View::factory('twilio/sms_response');
		$view->ip_address = $_SERVER['REMOTE_ADDR'];
		$view->render(TRUE);
	}

	public function voice()
	{
		//not quite there yet
	}
}
