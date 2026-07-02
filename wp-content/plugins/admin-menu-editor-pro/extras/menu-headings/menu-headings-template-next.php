<?php

use YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

/**
 * @var Renderer $renderer
 * @var InterfaceStructure $structure
 */
?>
<div style="display: none">
	<div id="ws-ame-menu-heading-settings-next" title="Menu Headings">
		<div id="ws-ame-mh-dialog-wrapper">
			<div id="ws-ame-mh-dialog-content">
				<?php $renderer->renderStructure($structure); ?>
			</div>

			<div class="ws_dialog_buttons">
				<?php
				submit_button(
					'OK',
					'primary',
					'ws-ame-save-menu-heading-settings',
					false,
					[
						'data-bind' => 'click: onConfirmDialog.bind($data)',
					]
				);
				?>

				<input type="button" class="button ws_close_dialog" value="Cancel"
				       data-bind="click: onCancelDialog.bind($data)">
			</div>
		</div>
	</div>
</div>
