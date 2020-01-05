<?php if (!defined('ATTR')) die('Forbidden');

class _ATTR_Customizer_Control_Option_Wrapper extends WP_Customize_Control {
	public function render_content() {
		attr()->backend->_set_default_render_design('customizer');
	
		?>
		<div class="attr-backend-customizer-option">
			<input class="attr-backend-customizer-option-input" type="hidden" <?php $this->link() ?> />
			<div class="attr-backend-customizer-option-inner attr-force-xs">
				<?php

					echo attr()->backend->render_options(
						array($this->id => $this->setting->get_attr_option()),
						array($this->id => $this->value()),
						array(),
						'customizer'
					);
				
				?>
			</div>
		</div>
		<?php
		attr()->backend->_set_default_render_design();
	}
}
