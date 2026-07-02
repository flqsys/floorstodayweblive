<?php

namespace YahnisElsts\AdminMenuEditor\TableColumns;

use amePersistentProModule;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\ModuleSettings;
use YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore;
use YahnisElsts\AdminMenuEditor\ImportExport\ameExportableModule;
use function YahnisElsts\AdminMenuEditor\Collections\w;

class TableColumnsModule extends amePersistentProModule implements ameExportableModule {
	const SCREEN_STALENESS_THRESHOLD = 30 * 24 * 60 * 60;
	const SAVE_SETTINGS_ACTION = 'ame_save_table_column_settings';

	const EXCLUDED_SCREENS = [
		//"Appearance -> Menus" uses the "manage_nav-menus_columns" filter to add "Show advanced menu properties"
		//options to Screen Options. It doesn't have an actual table with columns.
		'nav-menus' => true,
	];

	/**
	 * WordPress doesn't have a consistent naming scheme for the filters that manage row actions,
	 * so here's a mapping of screen IDs and screen base IDs to the relevant filters.
	 */
	const ROW_ACTION_FILTERS_BY_SCREEN = [
		'edit-comments' => ['comment_row_actions'],
		'edit'          => ['post_row_actions', 'page_row_actions'],
		'users'         => ['user_row_actions'],
		//We currently only support the general plugin actions filter. There's also a separate,
		//dynamic filter for each plugin, which some plugins use instead.
		'plugins'       => ['plugin_action_links'],
		'edit-tags'     => ['tag_row_actions'],
		'upload'        => ['media_row_actions'],
		//Note: The "Links" screen doesn't have a filter for row actions, so it's not included here.
	];

	const HIDEABLE_ITEM_COMPONENT = 'tc';

	protected $optionName = 'ws_ame_table_columns';
	protected $tabSlug = 'table-columns';
	protected $tabTitle = 'Tables';

	/**
	 * @var array<string,bool>
	 */
	private array $screenHooksAdded = [];

	private ?\ameBaseActorAccessEvaluator $visibilityChecker = null;

	private ?\ameBaseActorAccessEvaluator $customOrderChecker = null;

	protected $settingsFormAction = self::SAVE_SETTINGS_ACTION;

	private array $seenColumns = [];
	private array $seenBulkActions = [];
	private array $seenRowActions = [];

	public function __construct($menuEditor) {
		$this->settingsWrapperEnabled = true;
		parent::__construct($menuEditor);

		$this->localTabStyles['ame-table-columns-ui'] = 'table-columns.css';

		add_action('current_screen', [$this, 'addScreenHooks']);

		//Easy Hide integration.
		add_action(
			'admin_menu_editor-register_hideable_items',
			[$this, 'registerHideableItems'],
			10, 1
		);
		add_filter(
			'admin_menu_editor-save_hideable_items-' . self::HIDEABLE_ITEM_COMPONENT,
			[$this, 'saveHideableItems'],
			10, 2
		);
	}

	/**
	 * @param \WP_Screen|mixed $screen
	 * @return void
	 */
	public function addScreenHooks($screen = null) {
		global $hook_suffix;

		//Sanity check.
		if ( !($screen instanceof \WP_Screen) || empty($screen->id) ) {
			return;
		}
		//Skip excluded screens.
		if ( !empty(self::EXCLUDED_SCREENS[$screen->id]) ) {
			return;
		}

		if ( empty($this->screenHooksAdded[$screen->id]) ) {
			add_filter(
				'manage_' . $screen->id . '_columns',
				$this->makeElementFilterCallback([$this, 'processColumns'], $screen),
				PHP_INT_MAX - 1000
			);
			add_filter(
				'bulk_actions-' . $screen->id,
				$this->makeElementFilterCallback([$this, 'processBulkActions'], $screen),
				PHP_INT_MAX - 1000
			);

			$rowActionFilters = [];
			if ( isset(self::ROW_ACTION_FILTERS_BY_SCREEN[$screen->id]) ) {
				$rowActionFilters = self::ROW_ACTION_FILTERS_BY_SCREEN[$screen->id];
			} else if ( isset(self::ROW_ACTION_FILTERS_BY_SCREEN[$screen->base]) ) {
				$rowActionFilters = self::ROW_ACTION_FILTERS_BY_SCREEN[$screen->base];
			}
			$rowActionHandler = $this->makeElementFilterCallback([$this, 'processRowActions'], $screen);
			foreach ($rowActionFilters as $filter) {
				add_filter($filter, $rowActionHandler, PHP_INT_MAX - 1000);
			}

			if ( !empty($hook_suffix) ) {
				add_action('admin_print_styles-' . $hook_suffix, fn() => $this->maybePrintScreenStyles($screen));
			}

			add_action('admin_footer', fn() => $this->saveDetectedChanges($screen));

			$this->screenHooksAdded[$screen->id] = true;
		}
	}

	public function processColumns(array $columns, \WP_Screen $screen): array {
		$this->seenColumns = $columns;
		return $this->filterElements($screen->id, 'columns', $columns, true);
	}

	public function processRowActions(array $actions, \WP_Screen $screen): array {
		foreach ($actions as $actionId => $link) {
			if ( !isset($this->seenRowActions[$actionId]) && is_string($link) ) {
				//Special case: Remove the hidden "Copied!" message from the "Copy URL" action on
				//the "Media" screen when in table view.
				if ( strpos($link, 'aria-hidden') !== false ) {
					$link = preg_replace(
						'@<span\s[^<>]{0,100}?aria-hidden=[\'"]true[\'"][^<>]{0,100}+>[^<>]{1,100}+</span>@',
						'',
						$link
					);
				}

				$this->seenRowActions[$actionId] = wp_strip_all_tags($link);
			}
		}
		return $this->filterElements($screen->id, 'rowActions', $actions);
	}

	public function processBulkActions(array $actions): array {
		foreach ($actions as $actionId => $actionTitle) {
			$this->seenBulkActions[$actionId] = $actionTitle;
		}
		return $actions;
	}

	/**
	 * Create a closure that performs common checks before calling the actual callback,
	 * and pases the screen object to the callback.
	 *
	 * This helps avoid code duplication like calling get_current_screen() every time.
	 * The caller should have already verified that the screen is valid and not excluded.
	 *
	 * @param callable $callback
	 * @param \WP_Screen $validatedScreen
	 * @return \Closure
	 */
	private function makeElementFilterCallback(callable $callback, \WP_Screen $validatedScreen): \Closure {
		return function ($elements = []) use ($callback, $validatedScreen) {
			//Sanity check.
			if ( !is_array($elements) ) {
				return $elements;
			}
			return call_user_func($callback, $elements, $validatedScreen);
		};
	}

	public function maybePrintScreenStyles(\WP_Screen $screen) {
		$settings = $this->loadSettings();
		$elementSettings = $settings->get(['screens', $screen->id, 'elements'], null);
		if ( !empty($elementSettings) ) {
			$hiddenElements = w($elementSettings)
				->filterValues(fn($singleElementSettings) => !$this->isElementVisible($singleElementSettings))
				->flatMapWithKeys(fn($unused, $id) => CssScreenElement::getElementOption($id));

			$selectorsToHide = $hiddenElements->flatMap(fn(CssScreenElement $e) => $e->getSelectors());
			if ( !$selectorsToHide->isEmpty() ) {
				//If everything that goes in the top tablenav is hidden, we should also hide
				//the tablenav itself to eliminate the empty space.
				if ( $hiddenElements->containsAllKeys(CssScreenElement::TOP_TABLE_NAV_ELEMENTS) ) {
					$selectorsToHide->push('.wrap .tablenav');
				}

				echo '<!-- AME (table-columns module) --><style>';
				//Generated CSS, escaping shouldn't be necessary. Doesn't include any user input.
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $selectorsToHide->implode(', ') . ' { display: none !important; } ';
				echo '</style>';
			}
		}
	}

	public function saveDetectedChanges(\WP_Screen $screen) {
		$anythingDetected = !empty($this->seenColumns)
			|| !empty($this->seenBulkActions)
			|| !empty($this->seenRowActions);

		if ( ($anythingDetected || $this->hasSettingsForScreen($screen->id)) && $this->userCanEditColumns() ) {
			$changesDetected = $this->mergeScreenData(
				$screen,
				$this->seenColumns,
				$this->seenRowActions,
				$this->seenBulkActions
			);
			if ( $changesDetected ) {
				$this->saveSettings();
			}
		}
	}

	private function hasSettingsForScreen($screenId): bool {
		$settings = $this->loadSettings();
		$screenSettings = $settings->get(['screens', $screenId], null);
		return !empty($screenSettings);
	}

	private function mergeScreenData(
		\WP_Screen $screen,
		           $defaultColumns,
		           $seenRowActions = [],
		           $seenBulkActions = []
	): bool {
		if ( empty($screen->id) ) {
			return false;
		}

		$settings = $this->loadSettings();
		$screenSettings = $settings->get(['screens', $screen->id], null);
		$changesDetected = false;

		if ( !is_array($screenSettings) ) {
			$screenSettings = [];
		}

		/** @noinspection PhpCastIsUnnecessaryInspection -- Defensive casts in case some plugin messes things up. */
		$newScreenData = [
			'postType'     => strval($screen->post_type),
			'taxonomy'     => strval($screen->taxonomy),
			'defaultOrder' => array_keys($defaultColumns),
			//Preserve the existing menu URL data if we currently can't look up menu items.
			//This can happen if there is no custom admin menu defined (and so no reverse lookup).
			'menuUrl'      => \ameUtils::get($screenSettings, 'menuUrl', null),
		];

		if ( $this->menuEditor->can_find_items_by_url() ) {
			$currentMenuItem = $this->menuEditor->get_current_menu_item();
			if ( $currentMenuItem ) {
				$newScreenData['menuUrl'] = \ameUtils::get($currentMenuItem, 'url');

				//Save the menu title only once. This is because cleaning it could be relatively slow.
				//If the menu still exists when the user saves settings, the title should be updated then.
				if ( empty($screenSettings['menuTitle']) && !empty($currentMenuItem['full_title']) ) {
					$newScreenData['menuTitle'] = $this->convertFullMenuTitle($currentMenuItem['full_title']);
				}
			} else {
				$newScreenData['menuUrl'] = null;
			}
		}

		foreach ($newScreenData as $key => $newValue) {
			$oldValue = \ameUtils::get($screenSettings, $key);
			if ( $newValue !== $oldValue ) {
				$screenSettings[$key] = $newValue;
				$changesDetected = true;
			}
		}

		$previousLastUpdated = \ameUtils::get($screenSettings, 'lastUpdated', 0);
		$screenSettings['lastUpdated'] = time();
		//Usually, it's not worth a database query just to update the timestamp. However, to help
		//identify screens that no longer exist, we'll update it occasionally for existing screens
		//before the screen data goes "stale".
		$timestampUpdateThreshold = self::SCREEN_STALENESS_THRESHOLD / 2;
		$timeSinceLastUpdate = $screenSettings['lastUpdated'] - $previousLastUpdated;
		if ( $timeSinceLastUpdate >= $timestampUpdateThreshold ) {
			$changesDetected = true;
		}

		$elementCollections = [
			'columns'     => $defaultColumns,
			'bulkActions' => $seenBulkActions,
			'rowActions'  => $seenRowActions,
		];
		foreach ($elementCollections as $collectionKey => $elementsOnScreen) {
			list($updatedElements, $elementsChanged) = $this->mergeElementCollections(
				\ameUtils::get($screenSettings, $collectionKey, []),
				$elementsOnScreen
			);
			$screenSettings[$collectionKey] = $updatedElements;
			$changesDetected = $changesDetected || $elementsChanged;
		}

		$settings->set(['screens', $screen->id], $screenSettings);

		return $changesDetected;
	}

	private function mergeElementCollections(array $storedElements, array $elementsOnScreen): array {
		$changesDetected = false;
		$defaultPosition = 0;

		//Add new elements, update element titles and default positions.
		foreach ($elementsOnScreen as $id => $title) {
			$title = str_replace('&nbsp;', ' ', $title); //Fix "Quick Edit" showing up as "Quick&nbsp;Edit".

			$newElementData = ['title' => $title, 'defaultPosition' => $defaultPosition];
			$defaultPosition++;

			if ( !isset($storedElements[$id]) ) {
				$storedElements[$id] = [];
			}
			foreach ($newElementData as $key => $newValue) {
				$oldValue = \ameUtils::get($storedElements[$id], $key);
				if ( $newValue !== $oldValue ) {
					$storedElements[$id][$key] = $newValue;
					$changesDetected = true;
				}
			}
		}

		//Update presence flags. Basically, these track if the element was present in the default
		//element list when the screen info was last updated.
		foreach ($storedElements as $id => $element) {
			$wasPresent = \ameUtils::get($element, 'present', false);
			$isPresent = array_key_exists($id, $elementsOnScreen);
			if ( $wasPresent !== $isPresent ) {
				$storedElements[$id]['present'] = $isPresent;
				$changesDetected = true;
			}
		}

		return [$storedElements, $changesDetected];
	}

	private function filterElements(
		$screenId,
		string $collectionKey,
		$defaultElements,
		bool $applyCustomOrder = false
	): array {
		$settings = $this->loadSettings();
		$screenSettings = $settings->get(['screens', $screenId], null);
		if ( empty($screenSettings) ) {
			return $defaultElements;
		}

		//Remove hidden elements.
		$elementSettings = \ameUtils::get($screenSettings, [$collectionKey], []);
		$result = array_filter($defaultElements, function ($elementId) use ($elementSettings) {
			return $this->isElementVisible($elementSettings[$elementId] ?? []);
		}, ARRAY_FILTER_USE_KEY);

		//Apply custom order if enabled for the current user.
		if ( $applyCustomOrder && $this->isCustomOrderEnabled($screenSettings) ) {
			uksort($result, function ($a, $b) use ($elementSettings) {
				$aPosition = \ameUtils::get($elementSettings, [$a, 'position'], null);
				$bPosition = \ameUtils::get($elementSettings, [$b, 'position'], null);
				if ( ($aPosition === null) && ($bPosition === null) ) {
					return 0;
				} else if ( $aPosition === null ) {
					return 1;
				} else if ( $bPosition === null ) {
					return -1;
				}
				return $aPosition - $bPosition;
			});
		}

		return $result;
	}

	private function isElementVisible($singleElementSettings) {
		$enabledForActor = \ameUtils::get($singleElementSettings, ['enabledForActor'], []);
		if ( empty($enabledForActor) ) {
			return true;
		}

		return $this->getVisibilityChecker()->isEnabled($enabledForActor);
	}

	public function getVisibilityChecker() {
		if ( $this->visibilityChecker === null ) {
			$this->visibilityChecker = \ameAccessEvaluatorBuilder::create($this->menuEditor)
				->roleDefault(true)       //All roles can see all elements by default.
				->superAdminDefault(true) //Super admins can see all elements. This overrides role settings.
				->defaultResult(true)     //If no other rules apply, the element is visible.
				->buildForUser(wp_get_current_user());
		}

		return $this->visibilityChecker;
	}

	private function isCustomOrderEnabled(array $screenSettings) {
		return $this->getCustomOrderChecker()->isEnabled(
			\ameUtils::get($screenSettings, ['customOrderEnabled'], [])
		);
	}

	public function getCustomOrderChecker() {
		if ( $this->customOrderChecker === null ) {
			$this->customOrderChecker = \ameAccessEvaluatorBuilder::create($this->menuEditor)
				->roleDefault(null)
				->superAdminDefault(null)
				->defaultResult(true)
				->buildForUser(wp_get_current_user());
		}

		return $this->customOrderChecker;
	}

	public function userCanEditColumns(): bool {
		return $this->menuEditor->current_user_can_edit_menu();
	}

	public function convertFullMenuTitle($fullTitle): array {
		$parts = explode('→', $fullTitle);
		return array_map(function ($title) {
			return trim(wp_strip_all_tags(\ameMenuItem::remove_update_count($title)));
		}, $parts);
	}

	public function createSettingInstances(ModuleSettings $settings): array {
		$f = $settings->settingFactory();
		$s = new SchemaFactory();
		$parentSettings = parent::createSettingInstances($settings);

		$enabledForActorSchema = $s->actorAccess()->settingParams(['deleteWhenBlank' => true]);

		$elementId = $s->string()->min(1)->max(200);
		$elementStruct = $s->struct([
			'title'           => $s->string()->defaultValue(''),
			'enabledForActor' => $enabledForActorSchema,
			//The "present" flag only matters for elements that we can detect. For other
			//elements, its value will be ignored, and they'll be treated as always present.
			'present'         => $s->boolean()->defaultValue(false),
			'defaultPosition' => $s->int()->defaultValue(null),
		]);
		$elementRecord = $s->record($elementId, $elementStruct);

		return array_merge($parentSettings, $f->buildSettings([
			'screens'            => $s->record(
				$s->string()->min(1)->max(200),
				$s->struct([
					'postType'           => $s->string()->defaultValue(''),
					'taxonomy'           => $s->string()->defaultValue(''),
					'defaultOrder'       => $s->arr($s->string()),
					'customOrderEnabled' => $s->record(
						$elementId,
						$s->boolean()
					),
					'menuUrl'            => $s->string()->nullable()->defaultValue(null),
					'lastUpdated'        => $s->int()->defaultValue(0),

					'columns'     => $s->record(
						$elementId,
						$elementStruct->withFields(['position' => $s->int()->defaultValue(null)])
					),
					'rowActions'  => $elementRecord,
					'bulkActions' => $elementRecord,
					'elements'    => $s->record($elementId, $elementStruct->withoutFields('title', 'defaultPosition')),
				])
			),
			'isFirstRefreshDone' => $s->boolean()->defaultValue(false),
		]));
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		$tab = $this->getTabHelper();
		$tab->enqueueScripts($this->menuEditor->get_base_dependencies(), $this->menuEditor->get_query_params());
	}

	public function displaySettingsPage() {
		$this->getTabHelper()->display();
	}

	public function handleSettingsForm($post = array()) {
		$this->getTabHelper()->handleSettingsFormAction($post);
	}

	/**
	 * @param string $screenId
	 * @param array $screenSettings
	 * @return string
	 */
	public function getScreenDisplayTitle(string $screenId, array $screenSettings): string {
		$screenTitle = $screenId;
		if ( !empty($screenSettings['menuTitle']) ) {
			$screenTitle = implode(' → ', $screenSettings['menuTitle']);
		}
		return $screenTitle;
	}

	/**
	 * @param string $columnId
	 * @param array $columnSettings
	 * @return string
	 */
	public function getColumnDisplayTitle(string $columnId, array $columnSettings): string {
		$columnDisplayTitle = wp_strip_all_tags(\ameUtils::get($columnSettings, 'title', ''));
		if ( $columnId === 'cb' ) {
			return '[Checkbox]';
		} else if ( empty($columnDisplayTitle) ) {
			return $columnId;
		}
		return $columnDisplayTitle;
	}

	private ?TablesTab $tabHelper = null;

	protected function getTabHelper(): TablesTab {
		if ( $this->tabHelper === null ) {
			$this->tabHelper = new TablesTab($this, $this->menuEditor);
		}
		return $this->tabHelper;
	}

	//region Export/import
	public function getExportOptionLabel(): string {
		return 'Table columns';
	}

	public function exportSettings(): ?array {
		$screens = $this->loadSettings()->get(['screens'], []);
		if ( empty($screens) ) {
			return null;
		}

		return ['screens' => $screens];
	}
	//endregion

	/**
	 * @param HideableItemStore $store
	 * @return void
	 */
	public function registerHideableItems(HideableItemStore $store) {
		$screens = $this->loadSettings()->get(['screens'], []);
		if ( empty($screens) ) {
			return;
		}

		$columnsCategory = $store->getOrCreateCategory('table-columns', 'Table Columns', null, false);
		foreach ($screens as $screenId => $screen) {
			$screenCategory = $store->getOrCreateCategory(
				'table-columns/s/' . $screenId,
				$this->getScreenDisplayTitle($screenId, $screen),
				$columnsCategory,
				true
			);

			foreach ($screen['columns'] as $columnId => $column) {
				$store->addItem(
					$this->makeHideableItemId($screenId, $columnId),
					$this->getColumnDisplayTitle($columnId, $column),
					[$screenCategory],
					null,
					!empty($column['enabledForActor']) ? $column['enabledForActor'] : [],
					self::HIDEABLE_ITEM_COMPONENT
				);
			}
		}
	}

	/**
	 * @param array $errors
	 * @param array $items
	 * @return array
	 */
	public function saveHideableItems(array $errors, array $items): array {
		$settings = $this->loadSettings();
		$screens = $settings->get(['screens'], []);
		if ( empty($screens) ) {
			return $errors;
		}

		$anySettingsModified = false;
		foreach ($screens as $screenId => $screen) {
			if ( empty($screen['columns']) ) {
				continue;
			}

			foreach ($screen['columns'] as $columnId => $column) {
				$id = $this->makeHideableItemId($screenId, $columnId);
				if ( isset($items[$id]) ) {
					$oldEnabled = \ameUtils::get($column, 'enabledForActor', []);
					$newEnabled = $items[$id]['enabled'] ?? [];
					if ( !\ameUtils::areAssocArraysEqual($newEnabled, $oldEnabled) ) {
						$settings->set(
							['screens', $screenId, 'columns', $columnId, 'enabledForActor'],
							$newEnabled
						);
						$anySettingsModified = true;
					}
				}
			}
		}

		if ( $anySettingsModified ) {
			$settings->save();
		}

		return $errors;
	}

	private function makeHideableItemId($screenId, $columnId): string {
		$screenId = str_replace('/', '--', $screenId);
		$columnId = str_replace('/', '--', $columnId);

		return 'table-columns/s/' . $screenId . '/' . $columnId;
	}
}
