<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
class Fb_photos_mcp { 
    
	
	private $right_nav;
	
	
	public function __construct()
    {
		//Load
		ee()->load->model('fb_photos_model');
		
		//Set the right nav
		$this->right_nav = array
		(
			'fb_photos:albums'   => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=fb_photos',
			'fb_photos:settings' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=fb_photos'.AMP.'method=settings',
		);
		
		ee()->view->cp_page_title = lang('fb_photos:page_title');
		ee()->cp->set_right_nav($this->right_nav);
    }
	
	
	
	
	
	
	public function index($message = '')
	{
		//Check if we have settings
		$has_settings = ee()->fb_photos_model->has_settings();
		if(!$has_settings)
			ee()->functions->redirect($this->right_nav['fb_photos:settings']);
		
		//Load
		ee()->load->helper('array');

		//Get the settings
		$settings         = ee()->fb_photos_model->get_settings();
		$configuration_id = ($settings != NULL) ? $settings->configuration_id : 0;

		//Save the settings
		if(ee()->input->post('album_id'))
		{
			//Loop trough the albums
			$album_ids = ee()->input->post('album_id');
			$name       = ee()->input->post('name'); 
			$short_name = ee()->input->post('short_name');
			$sync       = ee()->input->post('sync');
			$sync_to    = ee()->input->post('sync_to');
				
			foreach($album_ids as $album_id)
			{	
				//Remove the album		
				if($short_name[$album_id] == '')
				{
					ee()->fb_photos_model->remove_album($album_id);
				}
				else
				{
					ee()->fb_photos_model->save_album($album_id, $configuration_id, $name[$album_id], $short_name[$album_id], element($album_id, $sync, 0), element($album_id, $sync_to, ''));
				}
			}
		}


		//Set breadcrumb
		ee()->view->cp_page_title = lang('fb_photos:albums');	
		
		//Get the facebook albums
		$albums       = ee()->fb_photos_model->get_album_list();
		$saved_albums = ee()->fb_photos_model->get_albums($configuration_id);
	
		//Set the data
		$data = array
		(
			'action'       => 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=fb_photos',
			'albums'       => $albums['data'],
			
			'saved_albums' => ($saved_albums != NULL) ? $saved_albums : array(),
			
			'upload_prefs' => ee()->fb_photos_model->get_upload_dropdown(),
		);
		
		return ee()->load->view('album_form', $data, TRUE);
	
	}
	
	
	
	
    
	public function settings()
	{
		//Check the form validation
		ee()->load->library('form_validation');
		ee()->load->helper('array');
		
		//Get the rules
		$form_values = array();
		$rules		 = ee()->fb_photos_model->get_setting_rules();
		
		//Set up the validation
		ee()->form_validation->set_rules($rules); 
		if(ee()->form_validation->run() == TRUE)
		{
			//Get the data	
			$form_values = array
			(
				'name'         => ee()->input->post('name'),
				'app_id'       => ee()->input->post('app_id'),
				'secret'       => ee()->input->post('secret'),
				'access_token' => ee()->input->post('access_token'),
				'file_upload'  => (ee()->input->post('file_upload') == 1) ? 1 : 0,
			);
			
			//Save the settings
			$save = ee()->fb_photos_model->save_settings(0, $form_values['name'], $form_values['app_id'], $form_values['secret'], $form_values['access_token'], $form_values['file_upload']);
			if($save === FALSE)
			{
				ee()->session->set_flashdata('message_failure', lang('fb_photos:settings_save_failed'));
			}
			else
			{
				ee()->session->set_flashdata('message_success', lang('fb_photos:settings_saved'));
			}
			ee()->functions->redirect($this->right_nav['fb_photos:settings']);

		}
		
		//Set breadcrumb
		ee()->view->cp_page_title = lang('fb_photos:settings');
		
		
		//Get the settings
		$settings = ee()->fb_photos_model->get_settings();
		if($settings != NULL)
		{
			//Set the values
			$form_values = array
			(
				'name'         => $settings->name,
				'app_id'       => $settings->app_id,
				'secret'       => $settings->secret,
				'access_token' => $settings->access_token,
				'file_upload'  => ($settings->file_upload == 1) ? TRUE : FALSE,
			);	
		}
		
		
		$data = array
		(
			'action'      => 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=fb_photos'.AMP.'method=settings',
			'form_values' => $form_values, 
		);
		
		//Return the form
		return ee()->load->view('settings_form', $data, TRUE);
	}
	
	
	
	
	
	
	
	
	
	
}
