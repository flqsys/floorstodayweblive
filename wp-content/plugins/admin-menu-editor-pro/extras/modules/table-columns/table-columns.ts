'use strict';
///<reference path="../../../js/common.d.ts"/>

declare const wsAmeTableColumnsSettingsData: AmeTableColumnsSettings.SettingsScriptData;

namespace AmeTableColumnsSettings {
	import indexByProperty = AmeMiniFunc.indexByProperty;
	const _ = wsAmeLodash;

	export interface SettingsScriptData {
		screens: Record<string, ScreenData>;
		orderStrategy: AmeFeatureStrategySerializableInputs;
		columnVisibilityStrategy: AmeFeatureStrategySerializableInputs;
		saveFormConfig: AmeKoFreeExtensions.SaveFormConfigFromServer;
		preferenceCookiePath: string;
	}

	interface ElementData {
		title: string;
		defaultPosition?: number | null;
		present: boolean;
		enabledForActor: AmeActorFeatureMapData | [];
		//We include [] as a possible type for enabledForActor as a precaution, in case the PHP code
		//doesn't go out of its way to serialize an empty associative array as an object.
	}

	interface ColumnData extends ElementData {
		position: number;
	}

	interface ScreenData {
		title: string;

		columns: Record<string, ColumnData>;
		rowActions: Record<string, ElementData>;
		bulkActions: Record<string, ElementData>;
		elements: Record<string, ElementData>;

		probablyExists: boolean;

		//Note: We could use the defaultPosition property of each column instead of having a separate
		//defaultOrder array, but this is the old implementation and it works. Still, maybe refactor
		//later to simplify the code (make sure to migrate the existing order data if we do that).
		defaultOrder: string[];
		customOrderEnabled: AmeActorFeatureMapData;
	}

	type ElementDeletionHandler = (null | ((element: Element) => void));

	class Element {
		public readonly title: string
		public readonly present: boolean;
		public readonly visibility: AmeActorFeatureState;
		public readonly defaultPosition: number | null;

		public readonly canDelete: KnockoutObservable<boolean>;
		public readonly canMove: boolean;
		public deleteTooltip: string;

		public extraItemClass: string = '';
		public extraTitleClass: string = '';

		constructor(
			public readonly id: string,
			data: ElementData,
			visibilityStrategy: AmeActorFeatureStrategy,
			protected readonly deleteSelf: ElementDeletionHandler = null,
			supportsDeletion: boolean = false,
			supportsMoving: boolean = false
		) {
			this.title = data.title;
			this.present = data.present;
			this.defaultPosition = (typeof data.defaultPosition === 'number') ? data.defaultPosition : null;
			this.visibility = new AmeActorFeatureState(
				new AmeObservableActorFeatureMap(_.isArray(data.enabledForActor) ? {} : data.enabledForActor),
				visibilityStrategy
			);

			this.canDelete = ko.pureComputed(() => supportsDeletion && !this.present);
			this.canMove = supportsMoving;
			this.deleteTooltip = 'Delete "' + this.title + '"';
		}

		requestDeletion() {
			if (!this.canDelete() || (this.deleteSelf === null)) {
				alert('You cannot delete this item.');
				return;
			}

			this.deleteSelf(this);
		}

		toJs() {
			return {
				id: this.id,
				enabledForActor: this.visibility.toJs(),
			}
		}
	}

	class Column extends Element {
		public readonly initialPosition: number;

		constructor(
			id: string,
			data: ColumnData,
			columnVisibilityStrategy: AmeActorFeatureStrategy,
			deleteSelf: ElementDeletionHandler
		) {
			super(id, data, columnVisibilityStrategy, deleteSelf, true, true);
			this.initialPosition = data.position;

			this.deleteTooltip = 'Delete column "' + this.title + '"';
			this.extraItemClass = 'ame-tc-column';
			this.extraTitleClass = 'ame-tc-column-title';
		}
	}

	class Screen {
		public readonly title: string;
		public readonly columns: KnockoutObservableArray<Column>;

		public readonly defaultOrder: Record<string, number> = {};
		public readonly customColumnOrder: AmeActorFeatureState;
		public readonly isDefaultOrder: KnockoutObservable<boolean>;

		public readonly rowActions: KnockoutObservableArray<Element>;
		public readonly elements: Element[];

		public readonly canDelete: KnockoutObservable<boolean>;
		public readonly isOpen: KnockoutObservable<boolean> = ko.observable(true);

		constructor(
			public readonly id: string,
			data: ScreenData,
			customOrderStrategy: AmeActorFeatureStrategy,
			columnVisibilityStrategy: AmeActorFeatureStrategy
		) {
			this.title = data.title;
			_.forEach(data.defaultOrder, (columnId, index) => {
				this.defaultOrder[columnId] = index;
			});

			const deleteColumnHandler: ElementDeletionHandler = (column) => {
				if (column instanceof Column) {
					this.columns.remove(column);
				}
			};
			this.columns = ko.observableArray(Object.entries(data.columns).map(
				([id, columnData]) => new Column(
					id,
					columnData,
					columnVisibilityStrategy,
					deleteColumnHandler
				)
			));

			//Sort the columns by their initial position.
			this.columns.sort((a, b) => a.initialPosition - b.initialPosition);

			this.customColumnOrder = new AmeActorFeatureState(
				new AmeObservableActorFeatureMap(data.customOrderEnabled),
				customOrderStrategy
			);

			//The columns count as being in the default order if all the "default" columns
			//are in the correct order. Any other columns (e.g. columns that no longer exist)
			//can be in any order.
			this.isDefaultOrder = ko.pureComputed(() =>
				this.columns().every((column, index) => {
					const defaultIndex = this.defaultOrder[column.id];
					return (typeof defaultIndex === 'undefined') || (index === defaultIndex);
				}));

			const deleteRowActionHandler: ElementDeletionHandler = (action) => {
				if (action instanceof Element) {
					this.rowActions.remove(action);
				}
			};
			this.rowActions = ko.observableArray(Object.entries(data.rowActions).map(
				([id, actionData]) => new Element(
					id, actionData, columnVisibilityStrategy, deleteRowActionHandler, true
				)
			));

			this.elements = Object.entries(data.elements).map(
				([id, elementData]) => new Element(id, elementData, columnVisibilityStrategy)
			);

			function compareElementsForSorting(a: Element, b: Element): number {
				const defaultIndexA = a.defaultPosition;
				const defaultIndexB = b.defaultPosition;

				if (defaultIndexA === defaultIndexB) {
					return a.title.localeCompare(b.title);
				}

				if (defaultIndexA === null) {
					return 1;
				}
				if (defaultIndexB === null) {
					return -1;
				}
				return defaultIndexA - defaultIndexB;
			}

			this.rowActions.sort(compareElementsForSorting);
			this.elements.sort(compareElementsForSorting);

			this.canDelete = ko.pureComputed(() => !data.probablyExists);
		}

		toJs() {
			const columns = this.columns();
			const columnsById: Record<string, unknown> = {};
			for (let index = 0; index < this.columns().length; index++) {
				const column = columns[index];
				columnsById[column.id] = {
					...column.toJs(),
					position: index
				};
			}

			const rowActionsById =
				indexByProperty(this.rowActions().map(a => a.toJs()), 'id');
			const elementsById =
				indexByProperty(this.elements.map(e => e.toJs()), 'id');

			return {
				id: this.id,
				customOrderEnabled: this.customColumnOrder.toJs(),
				columns: columnsById,
				rowActions: rowActionsById,
				elements: elementsById
			};
		}

		resetOrder() {
			if (this.isDefaultOrder()) {
				alert('The columns are already in the default order.');
				return;
			}

			this.columns.sort((a, b) => {
				const defaultIndexA = this.defaultOrder[a.id];
				const defaultIndexB = this.defaultOrder[b.id];

				if (typeof defaultIndexA === 'undefined') {
					return 1;
				}
				if (typeof defaultIndexB === 'undefined') {
					return -1;
				}

				return defaultIndexA - defaultIndexB;
			});
		}

		toggle() {
			this.isOpen(!this.isOpen());
		}
	}

	class TableColumnsSettingsVm {
		public readonly screens: KnockoutObservableArray<Screen>;
		public readonly saveSettingsForm: AmeKoFreeExtensions.SaveSettingsForm;

		constructor(scriptData: SettingsScriptData) {
			const actorSelector = new AmeActorSelector(AmeActors, true, true);

			const selectedActor = actorSelector.createActorObservable(ko);
			const allActors = ko.pureComputed<IAmeActor[]>(() => {
				return actorSelector.getVisibleActors();
			});

			//Reselect the previously selected actor.
			actorSelector.setSelectedActorFromUrl();

			const customOrderStrategy = new AmeActorFeatureStrategy({
				...ameUnserializeFeatureStrategySettings(scriptData.orderStrategy),
				getSelectedActor: selectedActor,
				getAllActors: allActors
			});
			const columnVisibilityStrategy = new AmeActorFeatureStrategy({
				...ameUnserializeFeatureStrategySettings(scriptData.columnVisibilityStrategy),
				getSelectedActor: selectedActor,
				getAllActors: allActors
			});

			this.screens = ko.observableArray(Object.entries(scriptData.screens).map(
				([id, screenData]) => new Screen(
					id, screenData, customOrderStrategy, columnVisibilityStrategy
				)
			));

			//Sort the screens alphabetically.
			this.screens.sort((a, b) => a.title.localeCompare(b.title));

			this.saveSettingsForm = new AmeKoFreeExtensions.SaveSettingsForm({
				...scriptData.saveFormConfig,
				settingsGetter: () => {
					return {
						screens: indexByProperty(this.screens().map(screen => screen.toJs()), 'id')
					};
				},
				selectedActor: selectedActor
			});

			//Remember which sections (screens) are open.
			const openScreenIds = ko.computed<string[]>({
				read: () => {
					return this.screens().filter(screen => screen.isOpen()).map(screen => screen.id);
				},
				write: (value: string[]) => {
					this.screens().forEach(screen => {
						screen.isOpen(value.includes(screen.id));
					});
				}
			});

			const openScreensCookie = new WsAmePreferenceCookie(
				'ame_tc_open_screens',
				90,
				true,
				scriptData.preferenceCookiePath
			);
			const initialOpenScreenIds = openScreensCookie.readAndRefresh(null);

			if ((initialOpenScreenIds !== null) && (Array.isArray(initialOpenScreenIds))) {
				openScreenIds(initialOpenScreenIds);
			} else {
				//Open the first screen by default, if there is one.
				const firstScreen = this.screens()[0];
				if (firstScreen) {
					openScreenIds([firstScreen.id]);
				} else {
					openScreenIds([]);
				}
			}

			//Rate limit to avoid too many cookie writes.
			openScreenIds.extend({rateLimit: {timeout: 1000, method: 'notifyWhenChangesStop'}});

			openScreenIds.subscribe((screenIds) => {
				openScreensCookie.write(screenIds);
			});
		}

		deleteScreen(screen: Screen) {
			if (screen.canDelete()) {
				this.screens.remove(screen);
			} else {
				alert('You cannot delete this screen.');
			}
		}
	}

	jQuery(function () {
		const settingsVm = new TableColumnsSettingsVm(wsAmeTableColumnsSettingsData);
		ko.applyBindings(settingsVm, jQuery('#ame-tc-settings-page-container')[0]);
	});
}
