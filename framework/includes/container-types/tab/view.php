<?php if (!defined('ATTR')) die('Forbidden');
/**
 * @var array $tabs
 * @var array $values
 * @var array $options_data
 */

$global_lazy_tabs = attr()->theme->get_config('lazy_tabs');

?>
<div class="attr-options-tabs-wrapper">
	<div class="attr-options-tabs-list">
		<ul>
			<?php foreach ($tabs as $tab_id => &$tab): ?>
				<li <?php echo isset($tab['li-attr']) ? attr_attr_to_html($tab['li-attr']) : ''; ?> >
					<a href="#attr-options-tab-<?php echo esc_attr($tab_id) ?>" class="nav-tab attr-wp-link" ><?php
						echo htmlspecialchars($tab['title'], ENT_COMPAT, 'UTF-8') ?></a>
				</li>
			<?php endforeach; unset($tab); ?>
		</ul>
		<div class="attr-clear"></div>
	</div>
	<div class="attr-options-tabs-contents metabox-holder">
		<div class="attr-inner">
			<?php
			foreach ($tabs as $tab_id => &$tab):
				// prepare attributes
				{
					$attr = isset($tab['attr']) ? $tab['attr'] : array();

					$lazy_tabs = isset($tab['lazy_tabs']) ? $tab['lazy_tabs'] : $global_lazy_tabs;

					$attr['id'] = 'attr-options-tab-'. esc_attr($tab_id);

					if (!isset($attr['class'])) {
						$attr['class'] = 'attr-options-tab';
					} else {
						$attr['class'] = 'attr-options-tab '. $attr['class'];
					}

					if ($lazy_tabs) {
						$attr['data-attr-tab-html'] = attr()->backend->render_options(
							$tab['options'], $values, $options_data
						);
					}
				}
				?><div <?php echo attr_attr_to_html($attr) ?>><?php
					echo $lazy_tabs ? '' : attr()->backend->render_options($tab['options'], $values, $options_data);
				?></div><?php
				unset($tabs[$tab_id]); // free memory after printed and not needed anymore
			endforeach;
			unset($tab);
			?>
		</div>
	</div>
	<div class="attr-clear"></div>
</div>
