<?php
$dragIconUrl = plugins_url('modules/redirector/drag-indicator.svg', AME_ROOT_DIR . '/placeholder');
?>
<div id="ame-tc-settings-page-container" style="display: none;" data-bind="visible: true">
	<div class="ame-sticky-top-bar">
		<div class="ame-tc-top-bar-content">
			<?php require AME_ROOT_DIR . '/modules/actor-selector/actor-selector-template.php'; ?>
		</div>
	</div>
	<div class="clear"></div>

	<div id="ame-tc-settings-form-wrapper">
		<div data-bind="foreach: screens" class="ame-txt-screen-list">
			<div class="ws-ame-postbox ame-tc-screen" data-bind="css: { 'ws-ame-closed-postbox': !isOpen() }">
				<div class="ws-ame-postbox-header">
					<h3>
						<span data-bind="text: title"></span>
					</h3>
					<a href="#" class="ame-tc-delete-item"
					   title="It seems this admin screen no longer exists. You can delete these settings."
					   data-bind="visible: canDelete, click: $parent.deleteScreen.bind($parent)"
					><span class="dashicons dashicons-trash"></span></a>
					<button class="ws-ame-postbox-toggle" data-bind="click: toggle"></button>
				</div>
				<div class="ws-ame-postbox-content">
					<div class="ame-tc-screen-settings-section">
						<h4>Columns</h4>
						<div data-bind="sortable: {
						data: $data.columns,
						template: 'ame-tc-element-template',
						allowDrop: false,
						options: {
							handle: '.ame-tc-drag-handle'
						}}" class="ame-tc-element-list ame-tc-column-list">

						</div>

						<div class="ame-tc-other-screen-options">
							<div class="ame-tc-order-settings">
								<label>
									<input type="checkbox"
									       data-bind="checked: customColumnOrder.isEnabled, indeterminate: customColumnOrder.isIndeterminate"/>
									<span>Enable custom order</span>
								</label>
								<a href="#" class="ame-tc-reset-order" title="Reset column order" data-bind="
							   click: resetOrder.bind($data),
							   css: { 'ame-tc-disabled': isDefaultOrder },
							   visible: !isDefaultOrder()">
									<span class="dashicons dashicons-image-rotate"></span>
								</a>
							</div>
						</div>
					</div>

					<!-- ko if: elements.length > 0 -->
					<div class="ame-tc-screen-settings-section">
						<h4>Screen Elements</h4>
						<div class="ame-tc-element-list"
						     data-bind="template: { foreach: elements, name: 'ame-tc-element-template' }">
						</div>
					</div>
					<!-- /ko -->

					<!-- ko if: rowActions().length > 0 -->
					<div class="ame-tc-screen-settings-section">
						<h4>Actions</h4>
						<div class="ame-tc-element-list"
						     data-bind="template: { foreach: rowActions, name: 'ame-tc-element-template' }">
						</div>
					</div>
					<!-- /ko -->
				</div>
			</div>
		</div>
	</div>

	<!-- ko if: screens().length === 0 -->
	<div class="ame-tc-no-screens-message">
		<p>No table columns detected. Try going to an admin page that contains a table and then back to this page.</p>
	</div>
	<!-- /ko -->

	<div id="ame-tc-action-container">
		<div id="ame-tc-main-actions">
			<!--suppress HtmlUnknownTag -->
			<ame-save-settings-form params="form: saveSettingsForm"></ame-save-settings-form>
		</div>
	</div>

</div>


<div style="display: none">
	<template id="ame-tc-element-template">
		<div class="ame-tc-element" data-bind="class: extraItemClass">
			<!-- ko if: canMove -->
			<div class="ame-tc-drag-handle">
				<img src="<?php echo esc_url($dragIconUrl); ?>" alt="Drag indicator" width="24">
			</div>
			<!-- /ko -->
			<label>
				<input type="checkbox"
				       data-bind="checked: visibility.isEnabled, indeterminate: visibility.isIndeterminate"/>
				<span class="ame-tc-element-title"
				      data-bind="text: title, class: extraTitleClass"></span>
			</label>
			<a href="#" class="ame-tc-delete-item"
			   data-bind="
							   visible: canDelete,
							   click: $data.requestDeletion.bind($data),
							   attr: { title: $data.deleteTooltip }">
				<span class="dashicons dashicons-trash"></span>
			</a>
		</div>
	</template>
</div>