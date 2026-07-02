<?php
if ( !defined('ABSPATH') ) {
	exit; //No direct access.
}
?>
<script type="text/html" id="rex-editable-roles-screen-template">
	<div id="rex-editable-roles-container">
		<div class="ame-role-table-container">
			<table class="widefat ame-role-table">
				<tbody data-bind="foreach: visibleActors">
				<tr data-bind="css: {
					'alternate': (($index() % 2) === 0),
					'ame-selected-role-table-row': $data === $parent.selectedActor()
				},
				attr: { 'id': $parent.getItemRowId($data) },
				click: $parent.selectItem.bind($parent)">
					<td class="ame-column-role-name">
						<span data-bind="text: $parent.getItemText($data)"></span>
					</td>
					<td class="ame-column-selected-role-tip">
						<div class="ame-selected-role-tip">
							<svg xmlns="http://www.w3.org/2000/svg" class="ame-rex-svg-triangle"
							     viewBox="0 0 50 100">
								<polygon points="51,0 0,50 51,100"/>
							</svg>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<div id="rex-editable-roles-options">
			<fieldset>
				<p><label>
						<input type="radio" value="auto"
						       data-bind="checked: editableRoleStrategy, enable: isAutoStrategyAllowed"
						       name="editable-roles-behaviour">
						Automatic
						<br><span class="description">
							Only allows to assign the roles that have the same or fewer core capabilities.
						</span>
					</label></p>
				<p><label>
						<input type="radio" value="none" data-bind="checked: editableRoleStrategy"
						       name="editable-roles-behaviour">
						Leave unchanged
						<br><span class="description">
							Lets other plugins control this setting.
						</span>
					</label></p>
				<p><label>
						<input type="radio" value="user-defined-list"
						       data-bind="checked: editableRoleStrategy, enable: isListStrategyAllowed"
						       name="editable-roles-behaviour">
						Custom
						<br><span class="description">
							Lets you manually choose the roles that the selected role or user can
							assign to other users.
						</span>
					</label></p>
			</fieldset>
			<!-- ko if: $root.roles().length > 0 -->
			<ul id="rex-editable-role-list" data-bind="foreach: editor.roles">
				<li>
					<label>
						<input type="checkbox"
						       data-bind="
						        checked: $parent.isRoleSetToEditable($data),
						        enable: $parent.isRoleEnabled($data),
								attr: { 'id': $parent.getRoleOptionId($data) }
						       ">
						<span data-bind="text: displayName"></span>
					</label>
				</li>
			</ul>
			<!-- /ko -->
		</div>

	</div>
</script>