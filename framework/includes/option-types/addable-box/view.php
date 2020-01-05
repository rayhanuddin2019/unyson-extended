<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 * @var  array $controls
 * @var  array $box_options
 */

$attr = $option['attr'];
unset($attr['name']);
unset($attr['value']);

// generate controls html
{
	ob_start(); ?>
	<small class="attr-option-box-controls">
		<?php foreach ($controls as $c_id => $control): ?>
			<small class="attr-option-box-control-wrapper"><a href="#" class="attr-option-box-control" data-control-id="<?php echo esc_attr($c_id) ?>" onclick="return false"><?php echo $control ?></a></small>
		<?php endforeach; ?>
	</small>
	<?php $controls_html = ob_get_clean();
}

if ($option['sortable']) {
	$attr['class'] .= ' is-sortable';
}

$attr['class'] .= ' width-type-'. $option['width'];

if (!empty($data['value'])) {
	$attr['class'] .= ' has-boxes';
}
?>
<div <?php echo attr_attr_to_html($attr); ?>>
	
	<?php echo attr()->backend->option_type('hidden')->render($id, array('value' => '~'), array(
		'id_prefix' => $data['id_prefix'],
		'name_prefix' => $data['name_prefix'],
	)); ?>
	<?php $i = 0; ?>
	<div class="attr-option-boxes metabox-holder">
		<?php foreach ($data['value'] as $value_index => &$values): ?>
			<?php $i++; ?>
			<div class="attr-option-box attr-backend-options-virtual-context" data-name-prefix="<?php echo attr_htmlspecialchars($data['name_prefix'] .'['. $id .']['. $i .']') ?>" data-values="<?php echo attr_htmlspecialchars(json_encode($values)) ?>">
				<?php ob_start() ?>
				<div class="attr-option-box-options attr-force-xs">
					<?php
					echo attr()->backend->render_options($box_options, $values, array(
						'id_prefix'   => $data['id_prefix'] . $id .'-'. $i .'-',
						'name_prefix' => $data['name_prefix'] .'['. $id .']['. $i .']',
					));
					?>
				</div>
				<?php
				echo attr()->backend->render_box(
					$data['id_prefix'] . $id .'-'. $i .'-box',
					'&nbsp;',
					ob_get_clean(),
					array(
						'html_after_title' => $controls_html,
						'attr' => array(
							'class' => 'attr-option-type-addable-box-pending-title-update',
						),
					)
				);
				?>
			</div>
		<?php endforeach; unset($values); ?>
	</div>
	<br class="default-box-template attr-hidden" data-template="<?php
		/**
		 * Place template in attribute to prevent it to be treated as html
		 * when this option will be used inside another option template
		 */

		$values = array();

		// must contain characters that will remain the same after htmlspecialchars()
		$increment_placeholder = '###-addable-box-increment-'. attr_rand_md5() .'-###';

		echo attr_htmlspecialchars(
			'<div class="attr-option-box attr-backend-options-virtual-context" data-name-prefix="'. attr_htmlspecialchars($data['name_prefix'] .'['. $id .']['. $increment_placeholder .']') .'">'.
				attr()->backend->render_box(
					$data['id_prefix'] . $id .'-'. $increment_placeholder .'-box',
					'&nbsp;',
					'<div class="attr-option-box-options attr-force-xs">'.
						attr()->backend->render_options($box_options, $values, array(
							'id_prefix'   => $data['id_prefix'] . $id .'-'. $increment_placeholder .'-',
							'name_prefix' => $data['name_prefix'] .'['. $id .']['. $increment_placeholder .']',
						)).
					'</div>',
					array(
						'html_after_title' => $controls_html,
					)
				).
			'</div>'
		);
	?>">
	<div class="attr-option-boxes-controls">
		<?php
		echo attr_html_tag('button', array(
			'type'    => 'button',
			'onclick' => 'return false;',
			'class'   => 'button attr-option-boxes-add-button',
			'data-increment' => ++$i,
			'data-increment-placeholder' => $increment_placeholder,
			'data-limit' => intval($option['limit']),
		), attr_htmlspecialchars($option['add-button-text']));
		?>
	</div>
</div>
