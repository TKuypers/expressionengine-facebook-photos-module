<?=form_open($action)?>

<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
                    
  <caption><?=lang('fb_photos:settings')?></caption>
    
  <tbody>
  
    <tr class="<?=alternator('even', 'odd');?>">
      <td width="30%">
      <strong><label for="name"><?=lang('fb_photos:name');?> <em>*</em></label></strong>
      <?=form_error('name', '<div class="notice">', '</div>')?>
      </td>
      <td><?=form_input('name', element('name', $form_values));?></td>
    </tr>
    
    
    
    <tr class="<?=alternator('even', 'odd');?>">
      <td width="30%">
      <strong><label for="app_id"><?=lang('fb_photos:app_id');?> <em>*</em></label></strong>
      <?=form_error('app_id', '<div class="notice">', '</div>')?>
      </td>
      <td><?=form_input('app_id', element('app_id', $form_values));?></td>
    </tr>
    
    
    
    <tr class="<?=alternator('even', 'odd');?>">
      <td width="30%">
      <strong><label for="secret"><?=lang('fb_photos:secret');?> <em>*</em></label></strong>
      <?=form_error('secret', '<div class="notice">', '</div>')?>
      </td>
      <td><?=form_input('secret', element('secret', $form_values));?></td>
    </tr>
    
    <tr class="<?=alternator('even', 'odd');?>">
      <td width="30%">
      <strong><label for="access_token"><?=lang('fb_photos:access_token');?> <em>*</em></label></strong>
      </td>
       <td><?=form_input('access_token', element('access_token', $form_values));?></td>
    </tr>
    
    
    <tr class="<?=alternator('even', 'odd');?>">
      <td width="30%">
      <strong><label for="file_upload"><?=lang('fb_photos:file_upload');?></label></strong>
      </td>
      <td><?=form_checkbox('file_upload', '1', element('file_upload', $form_values));?></td>
    </tr>

  </tbody>
                  
</table>

<div id="publish_submit_buttons"><?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));?></div>         
<?=form_close();?>