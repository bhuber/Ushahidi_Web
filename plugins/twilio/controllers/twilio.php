<?php
require('Services/Twilio.php');

class Twilio_Controller extends Controller {
	private function _get_client()
	{
		return new Services_Twilio($config['twilio_sid'], $config['twilio_token']);
	}

        public function _send_sms($from, $to, $msg)
        {
		$client = $this->_get_client();
		$message = $client->account->sms_messages->create(
		    $from,
		    $to,
		    $msg
		);
		return $message->sid;
        }

	public function _make_call($from, $to, $speech)
	{
		$client = $this->_get_client();
		$call = $client->account->calls->create(
			$from,
			$to,
			'http://'.$_SERVER["SERVER_ADDR"].':'.$_SERVER["SERVER_PORT"].'/twilio/voice?speech='.urlencode($speech)
		);
	}

        /* Receive SMS GET request and return TwiML */
	public function sms()
	{
		try {
			$response = sms::add($_GET['From'], $_GET['Body']);
			if($response === TRUE)
			{
				 $response = 'Message received.';
			}
		} catch(Exception $e) {
			$response = substr($e->getMessage(), 0, 160);
		}
		$view = View::factory('twilio/sms_response');
		$view->response = $response;
		$view->render(TRUE);
	}

	/* Receive voice GET request and return TwiML */
	public function voice()
	{
		$response = new Services_Twilio_Twiml();
		$response->say($_GET['speech']);
		print $response;
	}
}
