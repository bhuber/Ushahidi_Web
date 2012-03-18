<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   Monitor Squared Team
 * @package	   SMS KeepAlive
 */

class smskeepalive_settings_Controller extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'settings';

		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
	}
	
	public function index()
	{
		
		$this->template->content = new View('smskeepalive/smskeepalive_admin');
		
		//create the form array
		$form = array
		(
		    'delimiter' => "",
			'code_word' => "",
			'cat_checkin' => "",
			'cat_checkout' => "",
			'cat_help' => "",
			'cat_clear' => "",
			'cat_status' => "",
			'whitelist' => ""
		);
		
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
				
		// check, has the form been submitted if so check the input values and save them
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST
			// fields with our own things
			$post = new Validation($_POST);
			
			// Add some filters
			$post->pre_filter('trim', TRUE);
			$post->add_rules('delimiter', 'length[1,1]');
			$post->add_rules('code_word', 'length[1,11]');
			$post->add_rules('cat_checkin', 'length[1,11]');
			$post->add_rules('cat_checkout', 'length[1,11]');
			$post->add_rules('cat_help', 'length[1,11]');
			$post->add_rules('cat_clear', 'length[1,11]');
			$post->add_rules('cat_status', 'length[1,11]');
			
			 if ($post->validate())
			{
				
				$settings = ORM::factory('smskeepalive')
					->where('id', 1)
					->find();
				$settings->delimiter = $post->delimiter;
				$settings->code_word = $post->code_word;
				$settings->cat_checkin = $post->cat_checkin;
				$settings->cat_checkout = $post->cat_checkout;
				$settings->cat_help = $post->cat_help;
				$settings->cat_clear = $post->cat_clear;
				$settings->cat_status = $post->cat_status;
				$settings->save();
				$form_saved = TRUE;
				$form = arr::overwrite($form, $post->as_array());
				
				//do the white list
				
				//delete everything in the white list db to make room for the new ones
				/*
				ORM::factory('smskeepalive_whitelist')->delete_all();
				
				$whitelist = nl2br(trim($post->whitelist));
				if($whitelist != "" && $whitelist != null)
				{
					$whitelist_array = explode("<br />", $whitelist);
					//now put back the new ones
					foreach($whitelist_array as $item)
					{
						$whitelist_item = ORM::factory('smskeepalive_whitelist');
						$whitelist_item->phone_number = trim($item);
						$whitelist_item->save();
					}
				}
				*/
			}
			
			// No! We have validation errors, we need to show the form again,
			// with the errors
			else
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('settings'));
				$form_error = TRUE;
			}
		}
		else
		{
			//get settings from the database
			$settings = ORM::factory('smskeepalive')
				->where('id', 1)
				->find();
			$form['delimiter'] = $settings->delimiter;
			$form['code_word'] = $settings->code_word;
			$form['cat_checkin'] = $settings->cat_checkin;
			$form['cat_checkout'] = $settings->cat_checkout;
			$form['cat_help'] = $settings->cat_help;
			$form['cat_clear'] = $settings->cat_clear;
			$form['cat_status'] = $settings->cat_status;
			
			//get the white listed numbers
			/*$whitelist = "";
			$count = 0;
			$listers = ORM::factory('smskeepalive_whitelist')->find_all();
			foreach($listers as $item)
			{
				$count++;
				if($count > 1)
				{
					$whitelist = $whitelist."\n";
				}
				$whitelist = $whitelist.$item->phone_number;
			}
			$form['whitelist'] = $whitelist;
			*/
		}
		
		
		
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form = $form;
		$this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;
		
	}//end index method
	
	

	
}
