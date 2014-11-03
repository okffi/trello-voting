<?php
/**
 * You can modify element classes to fit your theme, excluding elements
 * with .description, .card and .more-link. Be aware that by changing element
 * id's you need to change those also in trello_vote_js.js file.
 */
?>
<div id="trellovoting">
	<div class="grid">
		<div class="unit twohalf-fifths">
			<div class="card">
				<div class="image">
					<img src="" title="" />
				</div>
				<div class="description">
					<h2></h2>
					<p></p>
				</div>
				<div class="more-link">
					<h3><a href="" target="_blank"><?php _e('Read more...', 'trellovoting') ?></a></h3>
				</div>
			</div>
			<a id="vote" class="btn" data-nonce="" data-id="" href="#"><img src="<?php echo plugins_url().'/trello-voting/includes/heart.png' ?>" title="" /> <?php _e('Vote this suggestion!', 'trellovoting') ?></a>
		</div>
		<div class="unit zero-fifth align-center">
			<h5><span class="seperator"><?php _e('vs.', 'trellovoting') ?></span></h5>
		</div>
		<div class="unit twohalf-fifths">
			<div class="card">
				<div class="image">
					<img src="" title="" />
				</div>
				<div class="description">
					<h2></h2>
					<p></p>
				</div>
				<div class="more-link">
					<h3><a href="" target="_blank"><?php _e('Read more...', 'trellovoting') ?></a></h3>
				</div>
			</div>
			<a id="vote" class="btn" data-nonce="" data-id="" href="#"><img src="<?php echo plugins_url().'/trello-voting/includes/heart.png' ?>" title="" /> <?php _e('Vote this suggestion!', 'trellovoting') ?></a>
		</div>
		<div id="trellohover">
			<h2><?php _e('Fething new cards', 'trellovoting') ?></h2>
		</div>
	</div>

	<div class="grid"><div class="footer unit two-fifths pull-left">
			<h4><a id="nxt" href="#"><?php _e("I can't say, get new cards", 'trellovoting') ?></a></h4>
		</div>
		<div class="footer unit two-fifths pull-right align-right">
			<h4 class="align-right"><a href="<?php echo $results_url ?>" class="results-link"><?php _e('...or stop voting and see results', 'trellovoting') ?></a></h4>
		</div>
	</div>
</div>