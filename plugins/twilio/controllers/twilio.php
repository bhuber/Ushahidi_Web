<?php
require('Services/Twilio.php');

class Twilio_Controller extends Controller {
    /* Send SMS via REST API */
    public function _sms_send($from, $to, $msg)
    {
        $client = new Services_Twilio($config['twilio_sid'], $config['twilio_token']);
        $message = $client->account->sms_messages->create(
            $from,
            $to,
            $msg
        );
        return $message->sid;
    }

    /* Receive SMS GET request and return TwiML */
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
