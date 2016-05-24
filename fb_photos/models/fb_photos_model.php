<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine 3.x Facebook photo module
 *
 * @package     ExpressionEngine
 * @module      Facebook Photos
 * @author      Ties Kuypers
 * @copyright   Copyright (c) 2016 - Ties Kuypers
 * @link        http://expertees.nl/expressionengine-facebook-photos-module
 * @license 
 *
 * Copyright (c) 2016, Expertees webdevelopment
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
class Fb_photos_model extends CI_Model {
    
	
	
	private $facebook;
	private $settings;
	
	public function __construct()
	{
		//Load the facebook SDK
		require PATH_THIRD.'fb_photos/libraries/facebook/facebook.php';
		
		//Load the settings
		$this->settings = $this->get_settings();
	}
	
	
	private function connect_to_facebook()
	{
		
		if($this->settings != NULL && $this->facebook == NULL)
		{
			$config   = array
			(
				'appId'      => $this->settings->app_id,
				'secret'     => $this->settings->secret,
				'fileUpload' => $this->settings->file_upload
			);	
			
			//Start the API
			$this->facebook = new Facebook($config);			
			$this->facebook->setAccessToken($this->settings->access_token);	
		}
	}
	
	
	
	
	public function has_settings()
	{
		$site_id = ee()->config->item('site_id');
		
		$query  = ee()->db->select('COUNT(configuration_id) AS total')->get('fb_photos_configuration');
		$result = $query->row(); 
		
		if($result->total == 1)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	
	
	
	
	public function get_albums($configuration_id)
	{
		//Set the query
		$query = ee()->db->where('configuration_id', $configuration_id)->get('fb_photos_albums');

		//Check if we have a result
		if($query->num_rows() > 0)
		{
			$result = $query->result_array();
			$albums = array();
			foreach($result as $album)
			{
				$albums[$album['album_id']] = $album;	
			}
			
			return $albums;
		}
		
		return NULL;
	}
	
	
	public function get_album($short_name)
	{
		$query = ee()->db->where('short_name', $short_name)->get('fb_photos_albums');
		if($query->num_rows() > 0)
		{
			return $query->row_array();
		}
		
		return NULL;
	}



	public function get_album_by_id($album_id)
	{
		$query = ee()->db->where('album_id', $album_id)->get('fb_photos_albums');
		if($query->num_rows() > 0)
		{
			return $query->row_array();
		}
		
		return NULL;
	}
	
	
	
	
	public function save_album($album_id, $configuration_id, $name, $short_name, $sync, $sync_to)
	{
		//Set the query
		$query = ee()->db->where('album_id', $album_id)->get('fb_photos_albums');	
		
		$data = array
		(
			'configuration_id' => $configuration_id,
			'name'             => $name,
			'short_name'       => $short_name,
			'sync'             => ($sync == 1) ? 1 : 0, 
			'sync_to'          => $sync_to,
		);
		
		//Insert a new row
		if($query->num_rows() == 0)
		{
			$data['album_id'] = $album_id;
			
			ee()->db->insert('fb_photos_albums', $data);
			
			return ee()->db->insert_id();
		}
		else
		{
			//Save into the database
			ee()->db->where('album_id', $album_id)->update('fb_photos_albums', $data);
			if(ee()->db->affected_rows() > 0)
			{
				return $album_id;	
			}
		}
	}
	
	
	public function remove_album($album_id)
	{
		if(is_numeric($album_id))
		{
			ee()->db->delete('fb_photos_albums', array('album_id' => $album_id));
			if(ee()->db->affected_rows() > 0)
			{
				return TRUE;	
			}
		}
		
		return FALSE;
	}
	
	
	
	

	public function get_album_list()
	{
		//Connect to facebook
		$this->connect_to_facebook();
		
		//Try to load the albums
		try
		{
			return $this->facebook->api($this->settings->app_id.'/albums','GET');
		} 
		catch(FacebookApiException $e) 
		{
			return FALSE;
		}
	}
	
	
	
	
	public function get_photos($album_id, $start = 0, $limit = 30, $use_cache = FALSE, $sync_path = '')
	{	
		//Check if we want to use cache
		if($use_cache)
		{
			$sync_file = $album_id.'.'.$start.'.'.$limit;
			$cache_file = $sync_path.$sync_file;
			$cache     = $this->get_cached_file($cache_file);
			if($cache != NULL)
				return $cache;	
		}
		
		//Connect to facebook
		$this->connect_to_facebook();
		
		//Get the data from the api
		try
		{
			$facebook_data = $this->facebook->api($album_id.'/photos?offset='.$start.'&limit='.$limit.'','GET');
			if($use_cache)
			{
				//Set the cache
				$this->set_cache($cache_file, $facebook_data);
			}
			return $facebook_data;
		} 
		catch(FacebookApiException $e) 
		{
			return array();
		}
	}
	
	
	
	
	public function get_album_data($album_id, $use_cache = FALSE, $sync_path = '')
	{
		//Check if we want to use cache
		if($use_cache)
		{
			$sync_file = 'data.'.$album_id;
			$cache_file = $sync_path.$sync_file;
			$cache     = $this->get_cached_file($cache_file);
			if($cache != NULL)
				return $cache;	
		}
		
		//Connect to facebook
		$this->connect_to_facebook();
		
		//Get album data
		try
		{
			$facebook_data = $this->facebook->api($album_id,'GET');
			if($use_cache)
			{
				//Set the cache
				$this->set_cache($cache_file, $facebook_data);
			}
			return $facebook_data;
		} 
		catch(FacebookApiException $e) 
		{
			return array();
		}	
	}
	
	
	
	
	private function get_cached_file($cache)
	{
		if(file_exists($cache))
		{	
			$yesterday = (time()-86400);
			$filemtime = filemtime($cache);
			
			if($filemtime > $yesterday)
			{
				$cache_data = unserialize(file_get_contents($cache));
				return $cache_data;	
			}
		}
		return NULL;
	}
	
	
	private function set_cache($file, $data)
	{
		$fp = fopen($file, 'w');
		fwrite($fp, serialize($data));
		fclose($fp);		
	}
	
	
	
	
	
	public function get_upload_dropdown()
	{
		//Load
		ee()->load->model('file_upload_preferences_model');

		//Get the prefs
		$dropdown     = array();
		$upload_prefs = ee()->file_upload_preferences_model->get_file_upload_preferences();
		
		foreach($upload_prefs as $pref)
		{
			$dropdown[$pref['id']] = $pref['name'];	
		}
	
		return $dropdown;
	}
	
	
	
	
	
	
	public function get_settings()
	{
		$site_id = ee()->config->item('site_id');
		$query   = ee()->db->where('site_id', $site_id)	->get('fb_photos_configuration');
		
		if($query->num_rows() > 0)
		{
			$result              = $query->row();
			$result->file_upload = ($result->file_upload == 1) ? TRUE : FALSE;
			
			return $result;	
		}
		
		return NULL;
	}
	
	
	
	
	public function save_settings($configuration_id = 0, $name = '', $app_id = '', $secret = '', $access_token = '')
	{
		//Set the data
		$site_id = ee()->config->item('site_id');
		$data = array
		(
			'site_id'		   => $site_id,
			'configuration_id' => $configuration_id,
			'name'             => $name,
			'app_id'           => $app_id,
			'secret'           => $secret,
			'access_token'     => $access_token,
		);
		
		//Go find a configuration row
		$query = ee()->db->select('configuration_id')->where('site_id', $site_id)->get('fb_photos_configuration');

		//Check if we want to update or save
		if($query->num_rows() == 0) //Insert
		{
			//Insert the row
			ee()->db->insert('fb_photos_configuration', $data);
			
			return ee()->db->insert_id();
		}
		else //Update
		{
			//Where
			$configuration_id = $query->row()->configuration_id;
			$where            = array('site_id' => $site_id, 'configuration_id' => $configuration_id);
			
			//Save into the database
			if(ee()->db->where($where)->update('fb_photos_configuration', $data))
			{
				return $configuration_id;	
			}
		}
		
		return FALSE;
	}			
}