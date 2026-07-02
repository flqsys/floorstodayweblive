<?php

namespace YahnisElsts\AdminMenuEditor\TableColumns;

use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Utils\Forms\KnockoutSaveForm;

class TablesTab {
	const FORCE_REFRESH_PARAM = 'ame-force-table-columns-refresh';
	const REFRESH_DONE_PARAM = 'ame-table-columns-refresh-done';

	protected TableColumnsModule $module;
	protected \WPMenuEditor $menuEditor;

	private bool $shouldRefreshTables = false;

	public function __construct(TableColumnsModule $module, \WPMenuEditor $menuEditor) {
		$this->module = $module;
		$this->menuEditor = $menuEditor;
	}

	public function enqueueScripts(\ameBaseScriptDependencies $baseDeps, array $queryParams) {
		$settings = $this->getSettings();

		$emptyObject = new \stdClass();
		$titleChangesFound = false;

		$screenData = [];
		foreach (\ameUtils::get($settings, ['screens'], []) as $screenId => $screenSettings) {
			$columnData = [];
			$lastColumnPosition = -1;

			foreach (\ameUtils::get($screenSettings, ['columns'], []) as $columnId => $columnSettings) {
				$columnDisplayTitle = $this->module->getColumnDisplayTitle($columnId, $columnSettings);

				$position = \ameUtils::get($columnSettings, 'position', null);
				if ( $position === null ) {
					$position = $lastColumnPosition + 1;
				}
				$lastColumnPosition = $position;

				$columnData[$columnId] = [
					'title'           => $columnDisplayTitle,
					'position'        => $position,
					'present'         => (bool)\ameUtils::get($columnSettings, 'present', false),
					'enabledForActor' => \ameUtils::get($columnSettings, ['enabledForActor'], $emptyObject),
				];
			}

			//Get the latest menu title(s) for the screen.
			$menuItemExists = null;
			if ( !empty($screenSettings['menuUrl']) && $this->menuEditor->can_find_items_by_url() ) {
				$item = $this->menuEditor->get_menu_item_by_url($screenSettings['menuUrl']);
				$menuItemExists = !empty($item);
				if ( $item && !empty($item['full_title']) ) {
					$currentTitle = $this->module->convertFullMenuTitle($item['full_title']);
					$oldTitle = \ameUtils::get($screenSettings, ['menuTitle'], []);
					if ( $currentTitle !== $oldTitle ) {
						$screenSettings['menuTitle'] = $currentTitle;
						$settings->set(['screens', $screenId, 'menuTitle'], $currentTitle);
						$titleChangesFound = true;
					}
				}
			}

			//Pick a display title for the screen.
			$screenTitle = $this->module->getScreenDisplayTitle($screenId, $screenSettings);

			//Does the screen still exist? Unfortunately, we can't determine this reliably, but
			//checking if the menu item/post type/taxonomy exists seems good enough.
			$screenExists = ($menuItemExists !== false);
			if ( !$menuItemExists ) {
				//Check taxonomy first because taxonomy pages usually also have a post type.
				if ( !empty($screenSettings['taxonomy']) ) {
					$screenExists = taxonomy_exists($screenSettings['taxonomy']);
				} else if ( !empty($screenSettings['postType']) ) {
					$screenExists = post_type_exists($screenSettings['postType']);
				}
			}

			$storedElementData = \ameUtils::get($screenSettings, ['elements'], []);
			$availableElements = CssScreenElement::guessAvailableElements($screenId, $screenSettings);
			$serializedCssElementData = [];
			foreach ($availableElements as $id => $element) {
				$serializedCssElementData[$id] = array_merge(
					$element->jsonSerialize(),
					[
						'present'         => true,
						'enabledForActor' => \ameUtils::get($storedElementData, [$id, 'enabledForActor'], $emptyObject),
					]
				);
			}

			$screenData[$screenId] = [
				'title'              => $screenTitle,
				'columns'            => (object)$columnData,
				'bulkActions'        => (object)\ameUtils::get($screenSettings, ['bulkActions'], []),
				'rowActions'         => (object)\ameUtils::get($screenSettings, ['rowActions'], []),
				'elements'           => (object)$serializedCssElementData,
				'probablyExists'     => $screenExists,
				'defaultOrder'       => \ameUtils::get($screenSettings, ['defaultOrder'], []),
				'customOrderEnabled' => \ameUtils::get($screenSettings, ['customOrderEnabled'], $emptyObject),
			];
		}

		if ( $titleChangesFound ) {
			$settings->save();
		}

		$this->shouldRefreshTables = empty($queryParams[self::REFRESH_DONE_PARAM])
			&& (
				empty($screenData)
				|| (!empty($queryParams[self::FORCE_REFRESH_PARAM]) && check_admin_referer(self::FORCE_REFRESH_PARAM))
				|| (!$settings->get('isFirstRefreshDone', false))
			);

		if ( $this->shouldRefreshTables ) {
			//Refresh mode.
			$refreshScript = $this->module->registerLocalScript(
				'ame-table-columns-refresh',
				'columns-refresh.js',
				[$baseDeps['ame-pro-common-lib']]
			);

			$pagesWithTables = [
				//Posts
				admin_url('edit.php'),
				//Pages
				admin_url('edit.php?post_type=page'),
				//Users
				admin_url('users.php'),
				//Plugins
				admin_url('plugins.php'),
				//Comments
				admin_url('edit-comments.php'),
			];
			//Categories
			if ( taxonomy_exists('category') ) {
				$pagesWithTables[] = admin_url('edit-tags.php?taxonomy=category');
			}
			//Tags
			if ( taxonomy_exists('post_tag') ) {
				$pagesWithTables[] = admin_url('edit-tags.php?taxonomy=post_tag');
			}

			$refreshScript->addJsVariable(
				'wsAmeTableColumnsRefreshData',
				[
					'pageUrls'    => $pagesWithTables,
					'redirectUrl' => $this->module->getTabUrl([self::REFRESH_DONE_PARAM => 1]),
				]
			);

			$refreshScript->enqueue();
		} else {
			//Editor mode.
			$script = $this->module->createScriptDependency('table-columns.js')
				->deps('jquery', $baseDeps['ame-pro-common-lib'], $baseDeps['ame-mini-functional-lib'])
				->deps($baseDeps()->koPackage()->koSortable()->cookies());

			$script
				->addJsVariable(
					'wsAmeTableColumnsSettingsData',
					[
						'screens'                  => $screenData,
						'orderStrategy'            => $this->module->getCustomOrderChecker()->configToJs(),
						'columnVisibilityStrategy' => $this->module->getVisibilityChecker()->configToJs(),
						'saveFormConfig'           => $this->getSettingsForm()->getJsSaveFormConfig(),
						'preferenceCookiePath'     => ADMIN_COOKIE_PATH,
					]
				)
				->enqueue();
		}
	}

	public function display() {
		if ( $this->shouldRefreshTables ) {
			$settings = $this->getSettings();
			if ( !$settings->get('isFirstRefreshDone', false) ) {
				$settings->set('isFirstRefreshDone', true);
				$settings->save();
			}

			$this->module->outputTemplate('columns-refresh');
		} else {
			$this->menuEditor->display_settings_page_header($this->getWrapClasses());

			if ( !$this->module->outputTemplate($this->module->getModuleId()) ) { //Equivalent to outputMainTemplate().
				printf(
					"[ %1\$s : Module \"%2\$s\" doesn't have a primary template. ]",
					esc_html(__METHOD__),
					esc_html($this->module->getModuleId())
				);
			}

			$this->menuEditor->display_settings_page_footer();
		}
	}

	public function handleSettingsFormAction(array $post) {
		if ( !$this->userCanSubmitForm() ) {
			wp_die('You do not have permission to edit table columns.');
		}

		$formSubmission = $this->getSettingsForm()->processKnockoutSubmission($post);
		$newSettings = $formSubmission->getSettings();

		//Optionally, we'll remove settings associated with roles or users that no longer exist.
		if ( $this->menuEditor->get_plugin_option('delete_orphan_actor_settings') ) {
			$cleaner = new \ameActorAccessCleaner();
		} else {
			$cleaner = null;
		}

		$settings = $this->getSettings();
		$screensSetting = $settings->getSetting('screens');

		//Merge new screen settings.
		$mergedScreens = $screensSetting->getValue();
		//Delete screens that are not in the submitted data.
		$mergedScreens = array_intersect_key($mergedScreens, $newSettings['screens']);
		foreach ($mergedScreens as $screenId => $screen) {
			$submittedScreen = \ameUtils::get($newSettings, ['screens', $screenId], []);
			if ( !empty($submittedScreen['customOrderEnabled']) ) {
				$screen['customOrderEnabled'] = $submittedScreen['customOrderEnabled'];
				if ( $cleaner ) {
					$screen['customOrderEnabled'] = $cleaner->cleanUpDictionary($screen['customOrderEnabled']);
				}
			} else {
				unset($screen['customOrderEnabled']);
			}

			foreach (['columns', 'rowActions', 'elements'] as $collectionKey) {
				$storedItems = \ameUtils::get($screen, [$collectionKey], []);
				$submittedItems = \ameUtils::get($submittedScreen, [$collectionKey], []);

				if ( $collectionKey === 'elements' ) {
					//Elements are a bit different because we doņ't initially store any of them.
					//Their records are only created when the user updates their settings.
					$allowedSubmittedItems = array_intersect_key($submittedItems, CssScreenElement::VALID_ELEMENT_IDS);
					$mergedItems = array_fill_keys(array_keys($allowedSubmittedItems), []);
				} else {
					//Delete items that are not in the submitted data.
					$mergedItems = array_intersect_key($storedItems, $submittedItems);
				}

				//Update settings for items.
				foreach ($mergedItems as $itemId => $item) {
					$submittedItem = \ameUtils::get($submittedItems, [$itemId], []);
					if ( !empty($submittedItem['enabledForActor']) ) {
						$item['enabledForActor'] = $submittedItem['enabledForActor'];
						if ( $cleaner ) {
							$item['enabledForActor'] = $cleaner->cleanUpDictionary($item['enabledForActor']);
						}
					} else {
						unset($item['enabledForActor']);
					}

					if ( $collectionKey === 'columns' ) {
						$item['position'] = \ameUtils::get($submittedItem, 'position', null);
					}

					$mergedItems[$itemId] = $item;
				}

				$screen[$collectionKey] = $mergedItems;
			}

			$mergedScreens[$screenId] = $screen;
		}

		//Save the new settings.
		$validationResult = $screensSetting->validate(new \WP_Error(), $mergedScreens, true);
		if ( is_wp_error($validationResult) ) {
			wp_die(esc_html($validationResult->get_error_message() . ' [' . $validationResult->get_error_code() . ']'));
		}

		$sanitizedValue = $validationResult;
		$screensSetting->update($sanitizedValue);
		$settings->save();

		$formSubmission->performSuccessRedirect();
	}

	protected function getSettings() {
		return $this->module->loadSettings();
	}

	protected function userCanSubmitForm(): bool {
		return $this->module->userCanEditColumns();
	}

	/**
	 * @var null|KnockoutSaveForm
	 */
	protected ?KnockoutSaveForm $saveForm = null;

	protected function getSettingsForm(): KnockoutSaveForm {
		if ( $this->saveForm === null ) {
			$this->saveForm = $this->createSettingsForm();
		}
		return $this->saveForm;
	}

	protected function createSettingsForm(): KnockoutSaveForm {
		$s = new SchemaFactory();

		$elementId = $s->string()->min(1)->max(200);
		$elementStruct = $s->struct([
			'enabledForActor' => $s->actorAccess()->settingParams(['deleteWhenBlank' => true]),
			'position'        => $s->int()->defaultValue(null),
		]);
		$elementCollection = $s->record($elementId, $elementStruct);

		$inputSchema = $s->struct([
			'screens' => $s->record(
				$s->string()->min(1)->max(250),
				$s->struct([
					'customOrderEnabled' => $s->record($s->string(), $s->boolean()),
					'columns'            => $elementCollection,
					'rowActions'         => $elementCollection,
					'elements'           => $elementCollection,
				])
			),
		]);

		return KnockoutSaveForm::builderFor($this->module)
			->settingsFieldSchema($inputSchema)
			->build();
	}

	protected function getWrapClasses(): array {
		return [];
	}
}