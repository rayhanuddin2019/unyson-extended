<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @var string $id
 * @var array $option
 * @var array $data
 * @var string $sortable_image url
 */
$attr = $option['attr'];
unset($attr['name']);
unset($attr['value']);

if ($option['sortable']) {
	$attr['class'] .= ' is-sortable';
}

// must contain characters that will remain the same after htmlspecialchars()
$increment_placeholder = '###-addable-popup-increment-'. attr_rand_md5() .'-###';
?>
<div <?php echo attr_attr_to_html($attr); ?>>
	
	<?php echo attr()->backend->option_type('hidden')->render($id, array('value' => '~'), array(
		'id_prefix' => $data['id_prefix'],
		'name_prefix' => $data['name_prefix'],
	)); ?>
	<div class="items-wrapper">
		<?php foreach ($data['value'] as $key => $value): ?>
			<div class="item attr-backend-options-virtual-context">
				<div class="input-wrapper">
					<?php echo attr()->backend->option_type('hidden')->render('', array('value' => json_encode($value)), array(
						'id_prefix' => $data['id_prefix'] . $id . '-' . $key . '-',
						'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
					));?>
				</div>
				<img src="<?php echo esc_attr($sortable_image); ?>" class="sort-item"/>

				<div class="content"><!-- will be populated from js --></div>
				<a href="#" class="dashicons attr-x delete-item"></a>
				<small class="dashicons dashicons-admin-page clone-item" title="<?php echo __('Clone','attr') ?>"></small>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="default-item attr-backend-options-virtual-context">
		<div class="input-wrapper">
			<?php echo attr()->backend->option_type('hidden')->render('', array('value' => '[]'), array(
				'id_prefix' => $data['id_prefix'] . $id . '-' . $increment_placeholder,
				'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
			)); ?>
		</div>
		<img src="<?php echo esc_attr($sortable_image); ?>" class="sort-item"/>

		<div class="content"></div>
		<a href="#" class="dashicons attr-x delete-item"></a>
		<small class="dashicons dashicons-admin-page clone-item" title="<?php echo __('Clone','attr') ?>"></small>
	</div>
	<?php
	echo attr_html_tag('button', array(
		'type' => 'button',
		'class' => 'button add-new-item',
		'onclick' => 'return false;',
		'data-increment-placeholder' => $increment_placeholder,
	), attr_htmlspecialchars($option['add-button-text']));
	?>
</div>

