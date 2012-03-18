<?php
/**
 * Performs install/uninstall methods for the smskeepalive plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   smskeepalive Installer
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class smskeepalive_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		//LOCATION    one of "lo,loc,location"
        //CHECKIN     one of "ci,check,checkin,in"
        //CHECKOUT    one of "co,checkout,out"
        //HELP        one of "lp,help,sos,911";
        //ALL_CLEAR   one of "ac,clear,safe"
        //STATUS      one of "status, st"
		// Also include table_prefix in name
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'smskeepalive3` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `delimiter` varchar(1) NOT NULL,
				  `code_word` varchar(11) NOT NULL,
				  `cat_checkin` varchar(11) NOT NULL,
				  `cat_checkout` varchar(11) NOT NULL,
				  `cat_help` varchar(11) NOT NULL,
				  `cat_clear` varchar(11) NOT NULL,
				  `cat_status` varchar(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'smskeepalive_whitelist` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `phone_number` varchar(20) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
		$num_settings = ORM::factory('smskeepalive')
				->where('id', 1)
				->count_all();
		if($num_settings == 0)
		{
			$settings = ORM::factory('smskeepalive');
			$settings->id = 1;
			$settings->delimiter = ";";
			$settings->code_word = "abc";
			$settings->cat_checkin = "2";
			$settings->cat_checkout = "3";
			$settings->cat_help = "1";
			$settings->cat_clear = "3";
			$settings->cat_status = "3";
			$settings->save();
		}
		
	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'smskeepalive3`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'smskeepalive_whitelist`');
	}
}