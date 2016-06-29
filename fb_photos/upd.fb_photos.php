<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine 2.x Facebook photo module
 *
 * @package     ExpressionEngine
 * @module      Facebook Photos
 * @author      Ties Kuypers
 * @copyright   Copyright (c) 2014 - Ties Kuypers
 * @link        http://expertees.nl/ee-addon/fb_photos
 * @license 
 *
 * Copyright (c) 2014, Expertees webdevelopment
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */
class Fb_photos_upd 
{ 
    var $version      = '1.0.3'; 
    var $name         = 'Fb_photos';
	
	// --------------------------------------------------------------------

	/**
	* Module Installer
	*
	* @access	public
	* @param    none
	* @return	bool
    */
	
	//@todo: add the tables
	function install() 
	{
		//Install the module
		$fields  = array('module_name'        => $this->name,
						 'module_version'     => $this->version,
						 'has_cp_backend'     => 'y',
						 'has_publish_fields' => 'n');
	
		//Insert the module in database
		ee()->db->insert('modules', $fields); 
		
		
		//Add the tabes
		ee()->db->query(
			"CREATE TABLE IF NOT EXISTS `".ee()->db->dbprefix('fb_photos_albums')."` (
			  `album_id` varchar(22) NOT NULL,
			  `configuration_id` int(11) NOT NULL,
			  `name` varchar(255) DEFAULT NULL,
			  `short_name` varchar(255) DEFAULT NULL,
			  `sync` tinyint(1) NOT NULL,
			  `sync_to` int(11) NOT NULL,
			  UNIQUE KEY `album_id` (`album_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			
		ee()->db->query(
			"CREATE TABLE IF NOT EXISTS `".ee()->db->dbprefix('fb_photos_configuration')."` (
			  `configuration_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `site_id` int(11) NOT NULL,
			  `name` varchar(255) DEFAULT NULL,
			  `app_id` varchar(255) DEFAULT NULL,
			  `secret` varchar(255) DEFAULT NULL,
			  `access_token` varchar(255) DEFAULT NULL,
			  `file_upload` tinyint(1) NOT NULL DEFAULT '0'
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
		
		return TRUE;

	}
    
    
	// --------------------------------------------------------------------
	/**
	* Module Updater
	*
	* @access	public
	* @param    none
	* @return	bool
    */
	function update($current = '')
	{
		//Check the current version
		if($current < $this->version)
		{
			//Handle updates
		}
		return TRUE; 
    }

	
	// --------------------------------------------------------------------
	/**
	* Module Uninstaller
	*
	* @access	public
	* @param    none
	* @return	bool
    */
	function uninstall()
	{ 
		//Load the class
		ee()->load->dbforge();
	
		//Remove the tables
		ee()->dbforge->drop_table('fb_photos_albums');
    	ee()->dbforge->drop_table('fb_photos_configuration');
	
		//Remove the module from the table
		ee()->db->query("DELETE FROM exp_modules WHERE module_name='$this->name'");

		return TRUE;
    }

}