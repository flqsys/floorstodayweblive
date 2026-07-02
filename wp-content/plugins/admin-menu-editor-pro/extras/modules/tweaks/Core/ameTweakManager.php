<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Core;

use amePersistentModule;
use ameUtils;
use YahnisElsts\AdminMenuEditor\Customizable\Builders\ControlBuilder;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\EventButton;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Context;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\ModuleSettings;
use YahnisElsts\AdminMenuEditor\Utils\Forms\KnockoutSaveForm;
use function YahnisElsts\AdminMenuEditor\Collections\w;

class ameTweakManager extends amePersistentModule {
	const APPLY_TWEAK_AUTO = 'auto';
	const APPLY_TWEAK_MANUALLY = 'manual';

	const BASIC_TWEAK_PROPERTIES = ['id' => true, 'enabledForActor' => true];

	protected $tabSlug = 'tweaks';
	protected $tabTitle = 'Tweaks';
	protected $optionName = 'ws_ame_tweak_settings';

	protected $settingsFormAction = 'ame-save-tweak-settings';

	/**
	 * @var ameBaseTweak[]
	 */
	private array $tweaks = [];

	/**
	 * @var ameBaseTweak[]
	 */
	private array $pendingTweaks = [];

	/**
	 * @var ameBaseTweak[]
	 */
	private array $postponedTweaks = [];

	/**
	 * @var ameTweakSection[]
	 */
	private array $sections = [];

	/**
	 * @var ameTweakAlias[]
	 */
	private array $aliases = [];

	private ameAdminCssTweakManager $adminCssManager;

	/**
	 * @var null|array
	 */
	private ?array $cachedEnabledTweakSettings = null;

	/**
	 * @var callable[]
	 */
	private array $tweakBuilders = [];

	private bool $earlyInitDone = false;
	private bool $earlyTweaksRegistered = false;
	private array $defaultTweakFactories = [];
	private bool $screenHookAdded = false;

	public function __construct($menuEditor) {
		$this->settingsWrapperEnabled = true;
		parent::__construct($menuEditor, dirname(__DIR__));

		$this->initProFeatures();

		$this->adminCssManager = new ameAdminCssTweakManager();
		$this->tweakBuilders['admin-css'] = [$this->adminCssManager, 'createTweak'];

		//Some tweaks may need to run early, before the "init" hook. For example, the tweak that disables
		//admin menu customizations affects virtual caps, and some plugins trigger cap checks very early.
		//The earliest we can process tweaks is on "set_current_user" because we need to know the current
		//user to figure out which tweaks to apply.
		if ( did_action('set_current_user') ) {
			$this->earlyInit();
		} else {
			add_action('set_current_user', [$this, 'earlyInit'], 1);
		}

		add_action('init', [$this, 'onInit'], PHP_INT_MAX - 1000);

		$this->localTabStyles['ame-tweak-manager-css'] = 'tweaks.css';
	}

	public function earlyInit() {
		//"set_current_user" can potentially be triggered multiple times, so we need to make sure
		//we only run this once.
		if ( $this->earlyInitDone ) {
			return;
		}

		$userAvailable = function_exists('is_user_logged_in') && is_user_logged_in();
		if ( !$userAvailable ) {
			return;
		}
		$this->registerEarlyTweaks();

		$earlyTweaks = [];
		foreach ($this->pendingTweaks as $id => $tweak) {
			if ( $tweak->wantsToRunEarly() ) {
				$earlyTweaks[$id] = $tweak;
				unset($this->pendingTweaks[$id]);
			}
		}

		if ( !empty($earlyTweaks) ) {
			$this->processTweaks($earlyTweaks);
		}

		$this->earlyInitDone = true;
	}

	private function registerEarlyTweaks() {
		if ( $this->earlyTweaksRegistered ) {
			return;
		}

		$this->addSection('general', 'General');

		$tweakData = $this->loadDefaultTweakData();

		foreach (ameUtils::get($tweakData, 'sections', []) as $id => $section) {
			$this->addSectionInstance(ameTweakSection::fromDefinition($id, $section));
		}

		$defaultTweaks = ameUtils::get($tweakData, 'tweaks', []);
		$this->addDefaultTweaks($this->filterTweaksToRegister($defaultTweaks));

		if ( !empty($tweakData['definitionFactories']) ) {
			$this->defaultTweakFactories = array_merge($this->defaultTweakFactories, $tweakData['definitionFactories']);
		}

		$this->earlyTweaksRegistered = true;
	}

	protected function loadDefaultTweakData(): array {
		//Register Pro feature placeholders for the free version, but only in the "Tweaks" tab.
		//They're just for display purposes, so there's no need to run them on other pages.
		//The variable is read in default-tweaks.php.
		$twmPlaceholdersEnabled = $this->isTweaksTab() && (
				!$this->menuEditor->is_pro_version()
				|| (defined('AME_TEST_MODE') && constant('AME_TEST_MODE'))
			);

		$data = [
			'sections'            => [],
			'tweaks'              => [],
			'definitionFactories' => [],
		];
		foreach ($this->getDefaultTweakFiles() as $file) {
			if ( file_exists($file) ) {
				$fileData = require($file);
				$data['sections'] = array_merge($data['sections'], ameUtils::get($fileData, 'sections', []));
				$data['tweaks'] = array_merge($data['tweaks'], ameUtils::get($fileData, 'tweaks', []));
				if ( !empty($fileData['definitionFactories']) ) {
					$data['definitionFactories'] = array_merge($data['definitionFactories'], $fileData['definitionFactories']);
				}
			}
		}
		return $data;
	}

	protected function getDefaultTweakFiles(): array {
		//Extension point for subclasses to add more default tweaks.
		return [__DIR__ . '/../default-tweaks.php'];
	}

	private function addDefaultTweaks($defaultTweaks) {
		foreach ($defaultTweaks as $id => $properties) {
			if ( isset($properties['selector']) ) {
				$tweak = new ameHideSelectorTweak(
					$id,
					$properties['label'] ?? null,
					$properties['selector']
				);

				if ( isset($properties['screens']) ) {
					$tweak->setScreens($properties['screens']);
				}
			} else if ( isset($properties['className']) ) {
				if ( isset($properties['includeFile']) ) {
					require_once $properties['includeFile'];
				}

				$className = $properties['className'];
				$tweak = new $className(
					$id,
					$properties['label'] ?? null
				);
			} else if ( isset($properties['factory']) ) {
				if ( isset($properties['includeFile']) ) {
					require_once $properties['includeFile'];
				}
				$tweak = call_user_func($properties['factory'], $id, $properties);
			} else if ( isset($properties['jquery-js']) ) {
				$tweak = new ameJqueryTweak(
					$id,
					$properties['label'] ?? null,
					$properties['jquery-js']
				);
			} else if ( isset($properties['callback']) ) {
				$tweak = new ameDelegatedTweak(
					$id,
					$properties['label'] ?? null,
					$properties['callback']
				);
			} else if ( !empty($properties['isGroup']) ) {
				$tweak = new ameDelegatedTweak(
					$id,
					$properties['label'] ?? null,
					'__return_false'
				);
			} else {
				throw new \LogicException(esc_html('Unknown tweak type in default-tweaks.php for tweak "' . $id . '"'));
			}

			if ( isset($properties['parent']) ) {
				$tweak->setParentId($properties['parent']);
			}
			if ( isset($properties['section']) ) {
				$tweak->setSectionId($properties['section']);
			}
			if ( isset($properties['description']) ) {
				$tweak->setDescription($properties['description']);
			}

			if ( isset($properties['hideableLabel']) ) {
				$tweak->setHideableLabel($properties['hideableLabel']);
			}
			if ( isset($properties['hideableCategory']) ) {
				$tweak->setHideableCategoryId($properties['hideableCategory']);
			}

			$this->addTweak($tweak);
		}
	}

	private function filterTweaksToRegister($tweaksById) {
		$tweakFilter = $this->getTweakRegistrationFilter();
		if ( $tweakFilter !== null ) {
			$tweaksById = array_intersect_key($tweaksById, $tweakFilter);
		}
		return $tweaksById;
	}

	private function getTweakRegistrationFilter(): ?array {
		//We may be able to improve performance by only registering tweaks that are enabled
		//for the current user. However, we still need to show all tweaks in the "Tweaks" tab.
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		//-- This is not processing form data, it's just checking which page the user is on.
		$isEasyHidePage = is_admin() && isset($_GET['page'])
			&& ($_GET['page'] === 'ame-easy-hide');
		$isCustomizableDevTab = is_admin() && isset($_GET['page']) && isset($_GET['sub_section'])
			&& ($_GET['page'] === 'menu_editor')
			&& ($_GET['sub_section'] === 'customizable-dev'); //Internal prototyping tab.
		//phpcs:enable

		if ( $this->isTweaksTab() || $isEasyHidePage || $isCustomizableDevTab ) {
			$tweakFilter = null;
		} else {
			$tweakFilter = $this->getEnabledTweakSettings();
		}
		return $tweakFilter;
	}

	private function isTweaksTab(): bool {
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		return is_admin()
			&& isset($_GET['page'], $_GET['sub_section'])
			&& ($_GET['page'] === 'menu_editor')
			&& ($_GET['sub_section'] === $this->tabSlug);
		//phpcs:enable
	}

	public function onInit() {
		$this->registerTweaks();

		$tweaksToProcess = $this->pendingTweaks;
		$this->pendingTweaks = [];
		$this->processTweaks($tweaksToProcess);
	}

	private function registerTweaks() {
		$this->registerEarlyTweaks();

		foreach ($this->defaultTweakFactories as $factoryCallback) {
			$tweakDefinitions = call_user_func($factoryCallback);
			$this->addDefaultTweaks($this->filterTweaksToRegister($tweakDefinitions));
		}

		$tweakFilter = $this->getTweakRegistrationFilter();

		do_action('admin-menu-editor-register_tweaks', $this, $tweakFilter);

		//Register user-defined tweaks.
		$settings = $this->loadSettings();
		$userDefinedTweakIds = ameUtils::get($settings, 'userDefinedTweaks', []);
		if ( !empty($userDefinedTweakIds) ) {
			$tweakSettings = isset($settings['tweaks']) ? $settings['tweaks'] : [];
			foreach ($userDefinedTweakIds as $id => $unused) {
				if ( !isset($tweakSettings[$id]['typeId']) ) {
					continue;
				}
				$properties = $tweakSettings[$id];
				if ( isset($this->tweakBuilders[$properties['typeId']]) ) {
					$properties['id'] = $id;
					$tweak = call_user_func($this->tweakBuilders[$properties['typeId']], $properties);
					if ( $tweak ) {
						$this->addTweak($tweak);
					}
				}
			}
		}
	}

	/**
	 * @param ameBaseTweak $tweak
	 * @param string $applicationMode
	 */
	public function addTweak(ameBaseTweak $tweak, string $applicationMode = self::APPLY_TWEAK_AUTO) {
		$this->tweaks[$tweak->getId()] = $tweak;
		if ( $applicationMode === self::APPLY_TWEAK_AUTO ) {
			$this->pendingTweaks[$tweak->getId()] = $tweak;
		}
	}

	/**
	 * @param ameTweakAlias $alias
	 * @return void
	 */
	public function addAlias(ameTweakAlias $alias) {
		$this->aliases[] = $alias;
	}

	public function getAliases(): array {
		return $this->aliases;
	}

	/**
	 * @param ameBaseTweak[] $tweaks
	 */
	protected function processTweaks(array $tweaks) {
		$settings = $this->getEnabledTweakSettings();

		foreach ($tweaks as $tweak) {
			if ( empty($settings[$tweak->getId()]) ) {
				continue; //This tweak is not enabled for the current user.
			}

			if ( $tweak->hasScreenFilter() ) {
				if ( !did_action('current_screen') ) {
					$this->postponedTweaks[$tweak->getId()] = $tweak;
					continue;
				} else if ( !$tweak->isEnabledForCurrentScreen() ) {
					continue;
				}
			}

			$settingsForThisTweak = null;
			if ( $tweak->supportsUserInput() ) {
				$settingsForThisTweak = ameUtils::get($settings, [$tweak->getId()], []);
			}
			$tweak->apply($settingsForThisTweak);
		}

		if ( !empty($this->postponedTweaks) && !$this->screenHookAdded ) {
			add_action('current_screen', [$this, 'processPostponedTweaks']);
			$this->screenHookAdded = true;
		}
	}

	/**
	 * Get settings associated with tweaks that are enabled for the current user.
	 */
	protected function getEnabledTweakSettings(): ?array {
		if ( $this->cachedEnabledTweakSettings !== null ) {
			return $this->cachedEnabledTweakSettings;
		}

		$settings = ameUtils::get($this->loadSettings(), 'tweaks');
		if ( !is_array($settings) ) {
			$settings = [];
		}
		$results = [];

		$currentUser = wp_get_current_user();
		if ( $currentUser ) {
			$roles = $this->menuEditor->get_user_roles($currentUser);
			$isSuperAdmin = is_multisite() && is_super_admin($currentUser->ID);

			foreach ($settings as $id => $tweakSettings) {
				$enabledForActor = ameUtils::get($tweakSettings, 'enabledForActor', []);
				if ( !$this->appliesToUser($enabledForActor, $currentUser, $roles, $isSuperAdmin) ) {
					continue;
				}

				$results[$id] = $tweakSettings;
			}
		}

		$this->cachedEnabledTweakSettings = $results;
		return $results;
	}

	/**
	 * @param array $enabledForActor
	 * @param \WP_User $user
	 * @param array $roles
	 * @param bool $isSuperAdmin
	 * @return bool
	 */
	private function appliesToUser(
		array $enabledForActor, \WP_User $user, array $roles, bool $isSuperAdmin = false
	): bool {
		//User-specific settings have priority over everything else.
		$userActor = 'user:' . $user->user_login;
		if ( isset($enabledForActor[$userActor]) ) {
			return $enabledForActor[$userActor];
		}

		//The "Super Admin" flag has priority over regular roles.
		if ( $isSuperAdmin && isset($enabledForActor['special:super_admin']) ) {
			return $enabledForActor['special:super_admin'];
		}

		//If it's enabled for any role, it's enabled for the user.
		foreach ($roles as $role) {
			if ( !empty($enabledForActor['role:' . $role]) ) {
				return true;
			}
		}

		//By default, all tweaks are disabled.
		return false;
	}

	/**
	 * @param \WP_Screen|null $screen
	 */
	public function processPostponedTweaks(?\WP_Screen $screen = null) {
		if ( empty($screen) && function_exists('get_current_screen') ) {
			$screen = get_current_screen();
		}
		$screenId = isset($screen, $screen->id) ? $screen->id : null;

		foreach ($this->postponedTweaks as $tweak) {
			if ( !$tweak->isEnabledForScreen($screenId) ) {
				continue;
			}
			$tweak->apply();
		}

		$this->postponedTweaks = [];
	}

	public function addSection($id, $label, $priority = null): ameTweakSection {
		$section = new ameTweakSection($id, $label);
		if ( $priority !== null ) {
			$section->setPriority($priority);
		}
		$this->sections[$section->getId()] = $section;

		return $section;
	}

	public function addSectionInstance(ameTweakSection $section) {
		$this->sections[$section->getId()] = $section;
	}

	public function getSection($id): ?ameTweakSection {
		return ameUtils::get($this->sections, $id, null);
	}

	public function getSections(): array {
		return $this->sections;
	}

	protected function getWrapClasses(): array {
		return array_merge(parent::getWrapClasses(), ['ame-tab-list-bottom-margin-disabled']);
	}

	public function enqueueTabScripts() {
		$codeEditorSettings = null;
		if ( function_exists('wp_enqueue_code_editor') ) {
			$codeEditorSettings = wp_enqueue_code_editor(['type' => 'text/html']);
		}

		$structure = $this->getInterfaceStructure();
		$structure->enqueueKoComponentDependencies();
		$serializationContext = new Context();

		//Collect all settings used in the UI structure.
		$settingsToSerialize = iterator_to_array($structure->getAllReferencedSettings($serializationContext));

		//Include the settings used to store the user-defined tweak suffix.
		$settings = $this->loadSettings();
		$settingsToSerialize[] = $settings->getSetting('lastUserTweakSuffix');

		//For user-defined tweaks, we also need their "typeId" setting and other metadata.
		//These are not directly referenced in the UI structure.
		$userDefinedTweaks = $settings->get('userDefinedTweaks', []);
		foreach (array_keys($userDefinedTweaks) as $tweakId) {
			foreach (['typeId', 'isUserDefined', 'label'] as $field) {
				$settingsToSerialize[] = $settings->getSetting('tweaks.' . $tweakId . '.' . $field);
			}
		}

		$scriptData = [
			'settings'           => AbstractSetting::serializeSettingsForJs($settingsToSerialize),
			'interfaceStructure' => $structure->serializeForJs($serializationContext),

			'preferenceCookiePath'      => ADMIN_COOKIE_PATH,
			'defaultCodeEditorSettings' => $codeEditorSettings,

			'isProVersion'        => $this->menuEditor->is_pro_version(),
			'lastUserTweakSuffix' => ameUtils::get($settings, 'lastUserTweakSuffix', 0),
			'saveFormConfig'      => $this->getSaveSettingsForm()->getJsSaveFormConfig(),
		];

		$useBundles = defined('WS_AME_USE_BUNDLES') && WS_AME_USE_BUNDLES;
		if ( $useBundles ) {
			//todo: If tweaks are added to the free version, it also need Webpack builds.
			// And possibly its own dist directory? And separate Webpack configs? That sounds
			// pretty complicated.
			$managerScript = $this->menuEditor->get_webpack_registry()->getWebpackEntryPoint('tweak-manager');
		} else {
			$managerScript = $this->createScriptDependency('tweak-manager.js')->setTypeToModule();
		}

		$baseDeps = $this->menuEditor->get_base_dependencies();
		$managerScript
			->addDependencies(
				'jquery',
				$baseDeps()->koPackage()->cookies()->qtip()
			)
			->setInFooter()
			->addJsVariable('wsTweakManagerData', $scriptData)
			->enqueue();
	}

	/**
	 * @return ameBaseTweak[]
	 */
	public function getRegisteredTweaks(): array {
		return $this->tweaks;
	}

	public function displaySettingsPage() {
		if ( !$this->userCanAccessModule() ) {
			echo 'Error: You do not have permission to view tweak settings.';
			return;
		}
		parent::displaySettingsPage();
	}

	public function handleSettingsForm($post = []) {
		if ( !$this->userCanAccessModule() ) {
			wp_die('You do not have permission to change tweak settings.');
		}

		parent::handleSettingsForm($post);

		$formSubmission = $this->getSaveSettingsForm()->processKnockoutSubmission($post);
		$submittedSettings = $formSubmission->getSettings();

		$submittedTweaks = w($submittedSettings['tweaks'])
			//To save space, filter out tweaks that are not enabled for anyone and have no other settings.
			//Most tweaks only have an "enabledForActor" property (previously also "id").
			->filterValues(function ($settings) {
				$additionalProperties = array_diff_key($settings, $this::BASIC_TWEAK_PROPERTIES);
				return !empty($settings['enabledForActor']) || !empty($additionalProperties);
			})
			//User-defined tweaks must have a type.
			->filterValues(function ($settings) {
				return empty($settings['isUserDefined']) || !empty($settings['typeId']);
			});

		//TODO: Give other components an opportunity to validate and sanitize tweak settings. E.g. a filter.
		//Sanitize CSS with FILTER_SANITIZE_FULL_SPECIAL_CHARS if unfiltered_html is not enabled. Always strip </style>.

		//Build a lookup array of user-defined tweaks so that we can register them later
		//without iterating through the entire list.
		$userDefinedTweakIds = $submittedTweaks
			->filterValues(fn($props) => !empty($props['isUserDefined']))
			->mapValues('__return_true');

		//We use an incrementing suffix to ensure each user-defined tweak gets a unique ID.
		$lastUserTweakSuffix = ameUtils::get($this->loadSettings(), 'lastUserTweakSuffix', 0);
		$newSuffix = ameUtils::get($submittedSettings, 'lastUserTweakSuffix', 0);
		if ( is_scalar($newSuffix) && is_numeric($newSuffix) ) {
			$newSuffix = max(intval($newSuffix), 0);
			if ( $newSuffix < 10000000 ) {
				$lastUserTweakSuffix = $newSuffix;
			}
		}

		$this->settings['tweaks'] = $submittedTweaks->toArray();
		$this->settings['userDefinedTweaks'] = $userDefinedTweakIds->toArray();
		$this->settings['lastUserTweakSuffix'] = $lastUserTweakSuffix;
		$this->saveSettings();

		$formSubmission->performSuccessRedirect();
	}

	/**
	 * @var KnockoutSaveForm|null
	 */
	private ?KnockoutSaveForm $settingsForm = null;

	private function getSaveSettingsForm(): KnockoutSaveForm {
		if ( $this->settingsForm === null ) {
			$this->settingsForm = KnockoutSaveForm::builderFor($this)
				->build();
		}
		return $this->settingsForm;

	}

	protected function userCanAccessModule(): bool {
		return (
			current_user_can('edit_theme_options')

			//The following check is currently redundant because AME core already checks this when
			//loading any settings page tab. It's only included here in case some future code calls
			//this method from outside the context of a settings page.
			&& $this->menuEditor->current_user_can_edit_menu()
		);
	}

	public function setTweakSettings($tweakSettings) {
		$this->loadSettings();
		$this->settings['tweaks'] = $tweakSettings;
		$this->saveSettings();
	}

	public function createSettingInstances(ModuleSettings $settings): array {
		$f = $settings->settingFactory();
		$s = new SchemaFactory();
		$parentSettings = parent::createSettingInstances($settings);

		$basePerTweakSettingsFields = [
			'enabledForActor' => $s->actorFeatureMap()->settingParams(['deleteWhenBlank' => true]),
			'isUserDefined'   => $s->boolean()->defaultValue(null)->settingParams(['deleteWhenBlank' => true]),
			'typeId'          => $s->string()->defaultValue(null)->settingParams(['deleteWhenBlank' => true]),
		];

		$perTweakSettingsSchemas = [];
		foreach ($this->tweaks as $tweak) {
			$perTweakSettingsSchemas[$tweak->getId()] = $s->struct(array_merge(
				$basePerTweakSettingsFields,
				$tweak->getSettingsSchemaFields($s)
			));
		}

		return array_merge($parentSettings, $f->buildSettings([
			'tweaks'              => $s->struct($perTweakSettingsSchemas),
			'userDefinedTweaks'   => $s->record(
				$s->string()->min(1),
				$s->boolean()
			),
			'lastUserTweakSuffix' => $s->number()->min(0)->defaultValue(0),
			'configFormatVersion' => $s->number()->min(1)->defaultValue(1),
		]));
	}

	public function getInterfaceStructure(): InterfaceStructure {
		$settings = $this->loadSettings();
		$b = $settings->elementBuilder();

		//Sections.
		//Sort sections by priority, then by label.
		uasort($this->sections, function (ameTweakSection $a, ameTweakSection $b) {
			$priorityA = $a->getPriority();
			$priorityB = $b->getPriority();
			if ( $priorityA !== $priorityB ) {
				return $priorityA <=> $priorityB;
			}
			return strnatcasecmp($a->getLabel(), $b->getLabel());
		});

		$sectionBuilders = [];
		foreach ($this->sections as $section) {
			$builder = $b->section($section->getLabel())
				->id('twm-section_' . $section->getId())
				->classes('ame-twm-section')
				->params(['childrenContainerClasses' => ['ame-check-or-radio-collection']]);

			$description = $section->getDescription();
			if ( !empty($description) ) {
				$builder->description($description);
			}

			if ( $section->isProPlaceholder() ) {
				$builder->classes('ame-twm-pro-placeholder');

				$message = $section->getProPlaceholderMessage();
				if ( empty($message) ) { //This should never happen because the getter should have a default message.
					$message = 'This is a Pro version feature.';
				}
				$builder->add(
					$b->html(sprintf(
						'<div class="ame-twm-placeholder-overlay"></div>
						 <div class="ame-twm-pro-banner">
							<div class="ame-twm-pro-message">%s</div>
							<a href="%s" target="_blank" class="button ame-twm-upgrade-button">Upgrade to Pro</a>
						</div>',
						esc_html($message),
						esc_attr('https://adminmenueditor.com/')
					))
				);
			}

			$sectionBuilders[$section->getId()] = $builder;
		}

		//Tweaks.
		$tweakControlsById = [];
		$tweakSectionsByTweakId = []; //For alias tooltips.

		foreach ($this->tweaks as $tweak) {
			$plainTweakId = $tweak->getId();

			$tweakControl = $tweak->createUiElement($b, $settings, 'tweaks.' . $plainTweakId);
			$tweakControlsById[$plainTweakId] = $tweakControl;

			if ( !empty($tweak->getParentId()) ) {
				$parentTweakId = $tweak->getParentId();
				if ( isset($tweakControlsById[$parentTweakId]) ) {
					$parentControl = $tweakControlsById[$parentTweakId];
					$parentControl->add($tweakControl);
					continue;
				} else {
					//Parent not found; put it in a section instead (fallthrough).
				}
			}

			$plainSectionId = $tweak->getSectionId() ?? 'general';
			if ( !isset($sectionBuilders[$plainSectionId]) ) {
				continue;
			}

			$sectionBuilder = $sectionBuilders[$plainSectionId];
			$sectionBuilder->add($tweakControl);
			$tweakSectionsByTweakId[$plainTweakId] = $sectionBuilder;
		}

		//Aliases
		$aliasCounter = 0;
		foreach ($this->aliases as $alias) {
			$targetTweakId = $alias->getTweakId();
			if ( !isset($tweakControlsById[$targetTweakId]) ) {
				continue;
			}

			//An alias is just another control that points to the same setting.
			$targetControl = $tweakControlsById[$targetTweakId];
			if ( !($targetControl instanceof ControlBuilder) ) {
				throw new \LogicException(sprintf(
					'Invalid alias: "%s" is not a ControlBuilder instance.',
					$targetTweakId
				));
			}

			$targetSettings = $targetControl->getSettings();
			$firstSetting = reset($targetSettings);
			if ( $firstSetting === false ) {
				throw new \LogicException(sprintf(
					'Invalid alias: target control for "%s" has no settings.',
					$targetTweakId
				));
			}

			$aliasCounter++;
			$aliasControl = $b->actorFeatureCheckbox($firstSetting)
				->label($alias->getLabel() ?? $targetTweakId)
				->id('alias-' . $targetTweakId . '-' . $aliasCounter);

			$tooltip = 'This is an alias for: "' . $targetControl->getParam('label', $targetTweakId) . '"';
			if ( isset($tweakSectionsByTweakId[$targetTweakId]) ) {
				$builder = $tweakSectionsByTweakId[$targetTweakId];
				$tooltip .= ' in the section "' . $builder->getTitle() . '".';
			}
			$aliasControl->tooltip($tooltip);

			if ( !empty($alias->getParentId()) ) {
				$parentTweakId = $alias->getParentId();
				if ( isset($tweakControlsById[$parentTweakId]) ) {
					$parentControl = $tweakControlsById[$parentTweakId];
					$parentControl->add($aliasControl);
					continue;
				}
			}

			$plainSectionId = $alias->getSectionId() ?? 'general';
			if ( !isset($sectionBuilders[$plainSectionId]) ) {
				continue;
			}
			$sectionBuilder = $sectionBuilders[$plainSectionId];
			$sectionBuilder->add($aliasControl);
		}

		//"Add CSS snippet" button.
		if ( isset($sectionBuilders['admin-css']) ) {
			$adminCssSection = $sectionBuilders['admin-css'];
			$adminCssSection->add(
				new EventButton(
					[],
					[
						'label'     => 'Add CSS snippet',
						'eventName' => 'adminMenuEditor:addCssSnippet',
						'wrap'      => true,
					]
				)
			);
		}

		$structure = $b->structure();
		foreach ($sectionBuilders as $b) {
			$structure->add($b);
		}

		return $structure->build();
	}

	protected function initProFeatures() {
		//This will be overridden in the Pro version to initialize Pro tweak managers.
	}
}