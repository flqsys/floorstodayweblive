namespace RexEditableRolesFeature {
	export class RexEditableRolesDialog extends RexBaseDialog {
		private editor: RexRoleEditor;
		private readonly visibleActors: KnockoutObservableArray<IAmeActor>;
		selectedActor: KnockoutObservable<RexBaseActor | null> = ko.observable(null);

		private actorSettings: { [actorId: string]: RexObservableEditableRoleSettings; } = {};
		private selectedActorSettings: KnockoutObservable<RexObservableEditableRoleSettings>;

		editableRoleStrategy: KnockoutComputed<string>;
		isAutoStrategyAllowed: KnockoutComputed<boolean>;
		isListStrategyAllowed: KnockoutComputed<boolean>;

		private readonly defaultStrategyByActor: Record<string, RexEditableRoleStrategy> = {
			//Administrators are allowed to edit all roles by default, so set
			//the strategy to "none" (leave unchanged).
			'role:administrator': 'none',
		};

		constructor(editor: RexRoleEditor) {
			super();
			this.editor = editor;
			this.visibleActors = ko.observableArray([] as IAmeActor[]);

			this.options.minWidth = 600;
			this.options.buttons.push({
				text: 'Save Changes',
				'class': 'button button-primary',
				click: this.onConfirm.bind(this),
				disabled: false
			});

			//Super Admin is always set to "leave unchanged" because
			//they can edit all roles.
			const superAdmin = editor.getSuperAdmin();
			const superAdminSettings = new RexObservableEditableRoleSettings();
			superAdminSettings.strategy('none');

			const dummySettings = new RexObservableEditableRoleSettings();
			this.selectedActorSettings = ko.computed(() => {
				const selectedActor = this.selectedActor();
				if (selectedActor === null) {
					return dummySettings;
				}
				if (selectedActor === superAdmin) {
					return superAdminSettings;
				}
				const actorId = selectedActor.getId();
				if (!this.actorSettings.hasOwnProperty(actorId)) {
					//This will happen when an actor doesn't have any custom settings.
					const defaultSettings = new RexObservableEditableRoleSettings();

					//Does this actor have a different default?
					if (this.defaultStrategyByActor.hasOwnProperty(actorId)) {
						defaultSettings.strategy(this.defaultStrategyByActor[actorId]);
					}

					this.actorSettings[actorId] = defaultSettings;
				}
				return this.actorSettings[actorId];
			});

			this.editableRoleStrategy = ko.computed({
				read: () => {
					return this.selectedActorSettings().strategy();
				},
				write: (newValue: string) => {
					this.selectedActorSettings().strategy(newValue as RexEditableRoleStrategy);
				}
			});

			this.isAutoStrategyAllowed = ko.computed(() => {
				const actor = this.selectedActor();
				if (actor == null) {
					return true;
				}
				return !(
					(actor === superAdmin)
					|| ((actor instanceof RexUser) && actor.isSuperAdmin)
				);
			});
			this.isListStrategyAllowed = this.isAutoStrategyAllowed;
		}

		onOpen(event: JQueryEventObject, ui: any) {
			const _ = wsAmeLodash;

			//Copy editable role settings into observables.
			_.forOwn(this.editor.actorEditableRoles, (settings: RexEditableRoleSettings, actorId) => {
				if (!this.actorSettings.hasOwnProperty(actorId)) {
					this.actorSettings[actorId] = new RexObservableEditableRoleSettings();
				}
				const observableSettings = this.actorSettings[actorId];
				observableSettings.strategy(settings.strategy);
				observableSettings.userDefinedList.clear();
				if (settings.userDefinedList !== null) {
					_.forOwn(settings.userDefinedList, (_ignored, roleId) => {
						observableSettings.userDefinedList.add(roleId);
					});
				}
			});

			this.visibleActors(this.editor.actorSelector.getVisibleActors());

			//Select either the currently selected actor or the first role.
			const selectedActor = this.editor.selectedActor();
			if (selectedActor) {
				this.selectedActor(selectedActor);
			} else {
				this.selectedActor(_.head(this.editor.roles()) || null);
			}
		}

		onConfirm() {
			//Save editable roles
			const _ = wsAmeLodash;
			let settings = this.editor.actorEditableRoles;
			_.forEach(this.actorSettings, (observableSettings, actorId) => {
				if (typeof actorId === 'undefined') {
					throw new Error('Actor ID is undefined. This should never happen.');
				}

				const strategy = observableSettings.strategy();
				const defaultStrategyForActor = this.defaultStrategyByActor[actorId] ?? EditableRoleDefaultStrategy;
				if ((strategy === defaultStrategyForActor) && !observableSettings.hasUserDefinedList()) {
					//This actor has the default strategy and the user hasn't selected any roles in
					//the user-defined list, so we don't need to store anything.
					delete settings[actorId];
				} else {
					settings[actorId] = observableSettings.toPlainObject();
				}
			});

			this.isOpen(false);
		}

		isRoleSetToEditable(role: RexBaseActor) {
			return this.selectedActorSettings().userDefinedList.getPresenceObservable(role.name());
		}

		isRoleEnabled(role: RexBaseActor) {
			return this.editableRoleStrategy() === 'user-defined-list';
		}

		selectItem(actor: RexBaseActor) {
			this.selectedActor(actor);
		}

		getItemText(actor: RexBaseActor): string {
			return this.editor.actorSelector.getNiceName(actor);
		}

		getItemRowId(actor: IAmeActor): string {
			return 'rex-editable-roles-row--'+ actor.getId().replace(':', '_');
		}

		getRoleOptionId(role: IAmeRole): string {
			return 'rex-editable-roles-role-option--' + role.getRoleName();
		}
	}
}