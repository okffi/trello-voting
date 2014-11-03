<div id="trellovoting">
	<?php foreach($labels as $label => $cards) {?>
		<h3><?php echo $label ?></h3>
		<table class="striped">
		  <tr>
		    <th><?php _e('Card name', 'trellovoting') ?></th>
	    	<th><?php _e('Votes', 'trellovoting') ?></th>
		  </tr>
		  <?php $i = 1; foreach ($cards as $card) { ?>
		  	<tr <?php if(++$i%2 == 0) { echo 'class="nth"'; } ?> >
		  		<td><a href="<?php echo $card['url'] ?>" target="_blank"><?php echo $card['name'] ?></a></td>
		  		<td><?php echo $card['votes'] ?></td>
		  	</tr>
		  <?php } ?>
		</table>
		<br><br>
	<?php } ?>
</div>