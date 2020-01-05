<?php

if (! defined('ATTR')) { die('Forbidden'); }

/*
echo 'ID';
attr_print($id);
echo 'OPTION';
attr_print($option);
echo 'DATA';
attr_print($data);
echo 'JSON';
attr_print($json);
 */

$wrapper_attr = array(
	'class' => $option['attr']['class'] . ' attr-icon-v2-preview-' . $option['preview_size'],
	'id' => $option['attr']['id'],
	'data-attr-modal-size' => $option['popup_size']
);

unset($option['attr']['class'], $option['attr']['id']);

?>

<div <?php echo attr_attr_to_html($wrapper_attr) ?>>
	<input <?php echo attr_attr_to_html($option['attr']) ?> type="hidden" />
</div>

