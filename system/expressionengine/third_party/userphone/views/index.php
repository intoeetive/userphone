
<ul class="tab_menu" id="tab_menu_tabs">
<li class="content_tab current"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=userphone'.AMP.'method=index'?>"><?=lang('member_phones')?></a>  </li> 
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=userphone'.AMP.'method=settings'?>"><?=lang('settings')?></a>  </li> 
</ul> 
<div class="clear_left shun"></div> 

<div id="filterMenu">
	<fieldset>
		<legend><?=lang('search_member')?></legend>

	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=userphone'.AMP.'method=index');?>

		<div class="group">
            <?php
			
			echo form_input('search', $selected['search'], ' style="width: 50%"');
            
            echo NBS.NBS.form_submit('submit', lang('search'), 'class="submit" id="search_button"');
            
            ?>
		</div>

	<?=form_close()?>
	</fieldset>
</div>


<div style="padding: 10px;">

<?php if ($total_count == 0):?>
	<div class="tableFooter">
		<p class="notice"><?=lang('no_records')?></p>
	</div>
<?php else:?>


	<?php

		$this->table->set_template($cp_table_template);
		$this->table->set_heading($table_headings); 

		echo $this->table->generate($data);
		
		$this->table->clear();
	?>




<span class="pagination"><?=$pagination?></span>


<?php endif; /* if $total_count > 0*/?>

</div>


