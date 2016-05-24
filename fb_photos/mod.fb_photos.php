<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class Fb_photos { 


	public function __construct()
	{
		ee()->load->model('fb_photos_model');
		ee()->load->model('file_upload_preferences_model');
	}
	
	public function show_album()
	{	
		//Get the name
		$short_name = ee()->TMPL->fetch_param('name', '');
		$pagination = (ee()->TMPL->fetch_param('pagination', 'yes') == 'yes') ? TRUE : FALSE;
		$param      = ee()->TMPL->fetch_param('param', 'page');
		$page       = ee()->input->get($param, 0);
		$limit      = ee()->TMPL->fetch_param('limit', 25);
		$start      = ($pagination) ? ($page * $limit) : 0;


		//Get the album
		$album = ee()->fb_photos_model->get_album($short_name);
		if($album == NULL)
			return lang('no_album_found');	
		
		
		//Check the synchronisation
		$sync        = ($album['sync'] == 1) ? TRUE : FALSE;
		$sync_path   = '';
		if($sync)
		{
			//Get the sync folder data
			$sync_folder = ee()->file_upload_preferences_model->get_file_upload_preferences(NULL, $album['sync_to']);
			$sync_path   = (isset($sync_folder['server_path'])) ? $sync_folder['server_path'] : FALSE;
			$sync        = (is_dir($sync_path)) ? TRUE : FALSE;
		}
		
		//Get the photos
		$album_id   = $album['album_id'];
		$photos     = ee()->fb_photos_model->get_photos($album_id, $start, $limit, $sync, $sync_path);
		
		//Get the vars
		$used_vars = ee()->TMPL->var_single;
		
		//Set the photo list
		$photo_list  = array();
		$photos_data = (isset($photos['data'])) ? $photos['data'] : array();

		foreach($photos_data as $key => $photo)
		{
			$photo_list[$key] = array
			(
				'name'     => (isset($photo['name'])) ? $photo['name'] : '',
				'link'     => $photo['link'],
				'likes'    => (isset($photo['likes'])) ? count($photo['likes']['data']) : 0,
				'comments' => (isset($photo['comments'])) ? count($photo['comments']['data']) : 0,
				'sizes'    => '',
				'count'    => $key,
			);
			
			//Loop trough the available options
			foreach($photo['images'] as $size => $option)
			{
				$photo_list[$key]['sizes'] .= $size.' - '.$option['width'].'x'.$option['height'].'<br/>';
				
				//Check if we want this size
				if(isset($used_vars['img-'.$size]))
				{
					//Set the non synced vars
					$photo_list[$key]['img-'.$size] = $option['source'];
				
					//Sync the image
					if($sync)
					{
						$filename = $this->get_fb_filename($option['source']);
						$file     = $sync_path.$filename;
						
						if(!file_exists($file))
						{
							$filedata = @file_get_contents($option['source']);
							if($filedata !== FALSE)
							{
								//Create file on server
								$fp = fopen($file, 'w');
								fwrite($fp, $filedata);
								fclose($fp);
							}
							else
							{
								$file = $option['source'];	
							}
						}
						//Return the chached file
						$photo_list[$key]['img-'.$size] = str_replace(getcwd(), '', $file);
					}
					
				}
			}
		}
		
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $photo_list);
	}
	
	
	private function get_fb_filename($src)
	{
		$filename = $src;
		
		$filename = str_replace('https', 'http', $filename);
		$filename = explode('/', $filename);
		
		$parts = (count($filename)-1);

		$filename = $filename[($parts-1)].'-'.$filename[$parts];
		$filename = explode('?', $filename);
		$filename = reset($filename);
		
		return $filename;
	}
	
	
	
	
	private $info = array();
	
	public function album_info()
	{
		//Get params
		$short_name = ee()->TMPL->fetch_param('name', '');
		$item       = ee()->TMPL->fetch_param('item', 'all');
		
		//Get the album
		$album = ee()->fb_photos_model->get_album($short_name);
		if($album == NULL)
			return;
			
		//Check the synchronisation
		$sync        = ($album['sync'] == 1) ? TRUE : FALSE;
		$sync_path   = '';
		if($sync)
		{
			//Get the sync folder data
			$sync_folder = ee()->file_upload_preferences_model->get_file_upload_preferences(NULL, $album['sync_to']);
			$sync_path   = (isset($sync_folder['server_path'])) ? $sync_folder['server_path'] : FALSE;
			$sync        = (is_dir($sync_path)) ? TRUE : FALSE;
		}	
		
		//Get the id
		$album_id = $album['album_id'];
		
		//Check from cache
		if(!isset($this->info[$album_id]))
		{
			//Get the data
			$this->info[$album_id] = ee()->fb_photos_model->get_album_data($album_id, $sync, $sync_path);
		}
		
		
		//Check if we want to see all
		if($item == 'all')
			return '<pre>'.print_r($this->info[$album_id], TRUE).'</pre>';
		
		//Return the item
		$return_item = (isset($this->info[$album_id][$item])) ? $this->info[$album_id][$item] : '';
		if(is_array($return_item))
		{
			return implode('<br/>', $return_item);	
		}
		
		return $return_item;
	}
	
	
	
	
	
	public function album_pagination()
	{
		//Get the name
		$short_name = ee()->TMPL->fetch_param('name', '');
		$param      = ee()->TMPL->fetch_param('param', 'page');
		$page       = ee()->input->get($param, 0);
		$limit      = ee()->TMPL->fetch_param('limit', 25);
		$start      = ($page * $limit);
		
		//Get the album
		$album = ee()->fb_photos_model->get_album($short_name);
		if($album == NULL)
			return;
			
		//Check the synchronisation
		$sync        = ($album['sync'] == 1) ? TRUE : FALSE;
		$sync_path   = '';
		if($sync)
		{
			//Get the sync folder data
			$sync_folder = ee()->file_upload_preferences_model->get_file_upload_preferences(NULL, $album['sync_to']);
			$sync_path   = (isset($sync_folder['server_path'])) ? $sync_folder['server_path'] : FALSE;
			$sync        = (is_dir($sync_path)) ? TRUE : FALSE;
		}
		
		//Get the data
		$album_id    = $album['album_id'];
		$album_data  = ee()->fb_photos_model->get_album_data($album_id, $sync, $sync_path);
		$album_count = $album_data['count'];
		
		$prev        = ($start > 0) ? TRUE : FALSE;
		$next        = (($start+$limit) < $album_count) ? TRUE : FALSE;
		
		$vars = array
		(	
			'prev'   => $prev,
			'next'   => $next,
			
			'prev_offset' => ($page-1 < 0) ? 0 : ($page-1),
			'next_offset' => ($page+1),
		);
		
		return ee()->TMPL->parse_variables_row(ee()->TMPL->tagdata, $vars);
	}
		
	
	
}
