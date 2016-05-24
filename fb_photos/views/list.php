<div class="box">
	<div class="tbl-ctrls">
		<?=form_open($base_url, array('name' => 'album-form', 'class' => 'album-form'))?>
			<fieldset class="tbl-search right">
				<a class="btn tn action" href="<?=ee('CP/URL')->make('addons/settings/fb_photos/settings')?>"><?=lang('settings')?></a>
			</fieldset>
			<h1><?=lang('albums')?></h1>
			<div class="tbl-wrap pb">

				<table cellspacing="0" id="albums" class=" grid-input-form" >
					<thead>
						<tr>
							<th><?=lang('name')?></th>
							<th><?=lang('short_name')?></th>
							<th><?=lang('sync')?></th>
							<th><?=lang('sync_to')?></th>
						</tr>
					</thead>
					<tbody>
						<? foreach($data as $row): ?>
						<tr>
							<? foreach($row as $column): ?>
								<td><?=$column?></td>
							<? endforeach; ?>
						</tr>
					<? endforeach; ?>
					</tbody>
				</table>
			</div>
			<fieldset class="form-ctrls">
				<input class="btn submit" type="submit" value="<?=lang('save')?>">
			</fieldset>
		</form>
	</div>
</div>
