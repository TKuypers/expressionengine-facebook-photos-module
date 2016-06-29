<?=form_open($action)?>

<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
                    
  <thead>
  	<tr>
    	<th><?=lang('fb_photos:album')?></th>
        <th><?=lang('fb_photos:short_name')?></th>
        <th><?=lang('fb_photos:images')?></th>
        <th><?=lang('fb_photos:likes')?></th>
        <th><?=lang('fb_photos:sync')?></th>
        <th><?=lang('fb_photos:sync_to')?></th>
        
        
    </tr>
  </thead> 
      
  <tbody>
 
  	<? foreach($albums as $album): ?>
    <tr class="<?=alternator('even', 'odd');?>">
 
 	  <?
      $curr_album = element($album['id'], $saved_albums, array());
	  ?>
 
 	  <?=form_hidden('album_id['.$album['id'].']', $album['id'])?>
      <?=form_hidden('name['.$album['id'].']', $album['name'])?>
      <td><?=$album['name']?></td>
      <td><?=form_input('short_name['.$album['id'].']', element('short_name', $curr_album, ''))?></td>
      <td><?=$album['count']?></td>
      <td><?=count(element('data', element('likes', $album, array('data' => array()))))?></td>
      <td><?=form_checkbox('sync['.$album['id'].']', 1, element('sync', $curr_album, ''))?></td>
      <td><?=form_dropdown('sync_to['.$album['id'].']', $upload_prefs, element('sync_to', $curr_album, ''));?></td>

    </tr>
    <? endforeach; ?>
    
    
  </tbody>
                  
</table>

<div id="publish_submit_buttons"><?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));?></div>         
<?=form_close();?>