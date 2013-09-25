<?php
// ========================= //
//	Metabox Hooks/Callbacks  //
// ========================= //

/*
 * Add language and traslations metaboxes
 */
add_action('add_meta_boxes', 'nLingual_add_meta_box');
function nLingual_add_meta_box(){
	foreach(nL_post_types() as $type){
		add_meta_box(
			'nLingual_language',
			__('Language', NL_TXTDMN),
			'nLingual_language_metabox',
			$type
		);
		add_meta_box(
			'nLingual_translations',
			__('Translations', NL_TXTDMN),
			'nLingual_translations_metabox',
			$type
		);
	}
}

function nLingual_language_metabox($post){
	?>
	<select name="language" style="width:100%">
	<?php foreach(nL_languages() as $slug => $lang):?>
		<option value="<?php echo $slug?>" <?php if(nL_in_this_lang($post->ID, $slug)) echo 'selected'?>><?php echo $lang['name']?></option>
	<?php endforeach;?>
	</select>
	<?php
}

function nLingual_translations_metabox($post){
	global $wpdb;
	wp_nonce_field(__FILE__, 'nLingual_translations');

	// Loop through each language and present controls for each translation
	foreach(nL_languages() as $lang => $data){
		if(nL_in_this_lang($post->ID, $lang)) continue;

		$translation = nL_get_translation($post->ID, $lang);

		// Get a list of available posts in the selected language
		$lang_posts = new WP_Query(array(
			'post_type' => $post->post_type,
			'posts_per_page' => -1,
			'language' => $lang,
			'orderby' => 'post_title',
			'order' => 'ASC',
		));
		?>
		<p>
			<strong><?php echo $data['name']?>:</strong>
			<select name="translations[<?php echo $lang?>]">
				<option value="-1"><?php _ex('None', 'no translation', NL_TXTDMN)?></option>
			<?php foreach($lang_posts->posts as $lang_post):?>
				<option value="<?php echo $lang_post->ID?>" <?php if($lang_post->ID == $translation) echo 'selected'?>><?php echo $lang_post->post_title?></option>
			<?php endforeach;?>
			</select>
			or <a href="<?php echo admin_url()?>?nL_new_translation=<?php echo $post->ID?>&language=<?php echo $lang?>&_nL_nonce=<?php echo wp_create_nonce(__FILE__)?>" class="button-secondary">
				<?php _ef('Create a new %1$s %2$s', NL_TXTDMN, strtolower(nL_get_lang('name', $lang)), strtolower(get_post_type_object($post->post_type)->labels->singular_name))?>
			</a>
		</p>
		<?php
	}
}