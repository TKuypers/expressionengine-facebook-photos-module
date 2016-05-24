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
use EllisLab\ExpressionEngine\Library\CP\Table;

class Fb_photos_mcp { 
    
	
	private $right_nav;
	
	
	public function __construct()
    {
		//Load
		ee()->load->model('fb_photos_model');
		ee()->load->helper('array');
    }
	
	
	
	
	public function index($message = '')
	{
		// check if we have settings
		$has_settings = ee()->fb_photos_model->has_settings();
		if(!$has_settings)
			ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_photos/settings')->compile());

		// the form
		$configuration_id = 1;
		$data 			  = array();
		$upload_dirs      = ee()->fb_photos_model->get_upload_dropdown();

		// check if we have data
		if(isset($_POST['settings']))
		{
			// loop trough the settings
			$settings = ee()->input->post('settings');
			foreach($settings as $album_id => $album)
			{
				// check if we have data
				if(empty($album['short_name']))
				{
					// check if there is a row in the database
					if(ee()->fb_photos_model->get_album_by_id($album_id) != NULL)
					{
						// delete the database row
						ee()->fb_photos_model->remove_album($album_id);
					}
				}
				else
				{
					$name       = $album['name'];
					$short_name = $album['short_name'];
					$sync       = ($album['sync'] == 'y') ? 1 : 0;
					$sync_to    = $album['sync_to'];

					// save the album
					ee()->fb_photos_model->save_album($album_id, $configuration_id, $name, $short_name, $sync, $sync_to);
				}

				// set the success message
				ee('CP/Alert')->makeBanner('success-message')
				->asSuccess()
				->withTitle(lang('saved'))
				->addToBody(lang('form_saved'))->now();
			}
		}


		// get the albums
		$albums       = ee()->fb_photos_model->get_album_list();
		$saved_albums = ee()->fb_photos_model->get_albums($configuration_id);

		if($saved_albums == NULL)
			$saved_albums = array();


		// loop trough the albums
		foreach($albums['data'] as $album)
		{
			$field_name = url_title(strtolower($album['name']), '_');
			$curr_album = element($album['id'], $saved_albums, array());

			// vars		
			$name       = $album['name'];
			$short_name = element('short_name', $curr_album, '');
			$sync       = (element('sync', $curr_album, 0) == 1) ? 'y' : 'n';
			$sync_to    = element('sync_to', $curr_album, 0);

			$data[] = array
			(
				$name.form_hidden('settings['.$album['id'].'][name]', $name),
				form_input('settings['.$album['id'].'][short_name]', $short_name),
				form_yes_no_toggle('settings['.$album['id'].'][sync]', $sync),
				form_dropdown('settings['.$album['id'].'][sync_to]', $upload_dirs, $sync_to)
			);
		}

		// final view variables we need to render the form
		$url = ee('CP/URL', 'addons/settings/fb_photos');
		$vars = array
		(
			'data'          => $data,
			'base_url' 		=> ee('CP/URL', 'addons/settings/fb_photos/'),
			'cp_page_title' => lang('albums'),
		);	

		return array
		(
		  	'body'       => ee('View')->make('fb_photos:list')->render($vars),
			'heading'    => lang('album_settings'),
		);
	}
	
	
	
	
    
	public function settings()
	{
		// check if we have a result
		$rules = array(
		 	'name'         => 'required|minLength[3]',
		 	'app_id'       => 'required|minLength[3]',
		 	'secret'       => 'required|minLength[3]',
		 	'access_token' => 'required|minLength[3]',
		);
		$result = ee('Validation')->make($rules)->validate($_POST);

		if($result->isValid())
		{
			$configuration_id = 1;
			$name  			  = ee()->input->post('name');
			$app_id           = ee()->input->post('app_id');
			$secret           = ee()->input->post('secret');
			$access_token     = ee()->input->post('access_token');

			// save the values
			$save = ee()->fb_photos_model->save_settings($configuration_id, $name, $app_id, $secret, $access_token);

			if($save !== FALSE)
			{
				ee('CP/Alert')->makeBanner('success-message')
					->asSuccess()
					->withTitle(lang('saved'))
					->addToBody(lang('form_saved'))
					->defer();

				ee()->functions->redirect(ee('CP/URL', 'addons/settings/fb_photos/')->compile());
				exit;
			}
		}
		else
		{
			$name         = '';
			$app_id       = '';
			$secret       = '';
			$access_token = '';

			//Get the settings
			$settings = ee()->fb_photos_model->get_settings();
			if($settings != NULL)
			{
				$name         = $settings->name;
				$app_id       = $settings->app_id;
				$secret       = $settings->secret;
				$access_token = $settings->access_token;
			}

			$form = array
			(
				array(
					array(
						'title' => 'name',
						'fields' => array(
							'name' => array(
								'type'     => 'text',
								'value'    => $name,
								'required' => TRUE
							)
						),
					),
					array(
						'title' => 'app_id',
						'fields' => array(
							'app_id' => array(
								'type'     => 'text',
								'value'    => $app_id,
								'required' => TRUE
							)
						),
					),
					array(
						'title' => 'secret',
						'fields' => array(
							'secret' => array(
								'type'     => 'text',
								'value'    => $secret,
								'required' => TRUE
							)
						),
					),
					array(
						'title' => 'access_token',
						'fields' => array(
							'access_token' => array(
								'type'     => 'text',
								'value'    => $access_token,
								'required' => TRUE
							),
						),
					),
				)
			);

			// final view variables we need to render the form
			$vars = array('sections' => $form);
			$vars += array
			(
				'base_url' 			    => ee('CP/URL', 'addons/settings/fb_photos/settings'),
				'cp_page_title' 		=> lang('api_settings'),
				'save_btn_text' 		=> 'btn_save_form',
				'save_btn_text_working' => 'btn_saving'
			);	

			// add the error to the form
			if($_POST)
				$vars['errors'] = $result;

			ee()->cp->add_js_script(array('file' => array('cp/form_group')));

			return array
			(
			  	'body'       => ee('View')->make('fb_photos:form')->render($vars),
			  	'breadcrumb' => array(ee('CP/URL', 'addons/settings/fb_photos/')->compile() => lang('fb_photos_module_name')),
				'heading'    => lang('album_settings')
			);
		}
	}	
}
