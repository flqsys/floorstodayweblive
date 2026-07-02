<?php

namespace YahnisElsts\AdminMenuEditor\RoleEditor\EditableRoles;

use YahnisElsts\WpDependencyWrapper\v1\ScriptDependency;

class EditableRolesFeature {
	/**
	 * @var ameEditableRoleFilter[] Editable roles per user. [userId => $filterInstance]
	 */
	private array $cachedEditableRoles = [];
	/**
	 * @var string[] Overall, most specific strategy per user. [123 => 'auto', 456 => 'user-defined-list', ...]
	 */
	private array $cachedOverallEditableRoleStrategy = [];
	/**
	 * @var bool Is the hook that clears the role cache already installed?
	 */
	private bool $isRoleCacheClearingHookSet = false;
	/**
	 * @var array
	 */
	private array $cachedEnabledRoleCaps = [];

	private \ameRoleEditor $roleEditor;
	private \WPMenuEditor $menuEditor;

	public function __construct(\ameRoleEditor $roleEditor, \WPMenuEditor $menuEditor) {
		$this->roleEditor = $roleEditor;
		$this->menuEditor = $menuEditor;
	}

	public function installHooks() {
		add_filter('editable_roles', [$this, 'filterEditableRoles'], 20, 1);
	}

	/**
	 * Apply "editable roles" settings.
	 *
	 * @param array|mixed $editableRoles
	 * @return array
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function filterEditableRoles($editableRoles) {
		//Sanity check: The role list should be an array or something array-like.
		if ( !is_array($editableRoles) && !($editableRoles instanceof \Traversable) ) {
			return $editableRoles;
		}

		//Do nothing if the core user API hasn't been loaded yet. There is at least one plugin that tries to get
		//editable roles before WordPress loads the user API and determines which user is logged in.
		if ( !is_callable('wp_get_current_user') ) {
			return $editableRoles;
		}

		// Do nothing if the overall strategy is "none" ("leave unchanged").
		$user = wp_get_current_user();
		if (
			isset($this->cachedOverallEditableRoleStrategy[$user->ID])
			&& ($this->cachedOverallEditableRoleStrategy[$user->ID] === 'none')
		) {
			return $editableRoles;
		}

		//It's possible that another plugin has already removed some roles from the array. We'll need the full list
		//so that we can restore enabled roles if the user has selected the "user-defined-list" strategy.
		if ( function_exists('wp_roles') ) {
			$allRoles = array_merge(wp_roles()->roles, $editableRoles);
		} else {
			$allRoles = $editableRoles;
		}

		//Try the cache first.
		if ( isset($this->cachedEditableRoles[$user->ID]) ) {
			return $this->cachedEditableRoles[$user->ID]->filter($allRoles, $editableRoles);
		}

		//A super admin always has full access to everything. Do not remove any roles.
		if ( is_multisite() && is_super_admin() ) {
			return $editableRoles;
		}

		$settings = \ameUtils::get($this->roleEditor->loadSettings(), 'editableRoles', []);
		$userRoles = $this->menuEditor->get_user_roles($user);

		//User-specific settings have precedence.
		//For users, "auto" means "use role settings".
		$userActorId = 'user:' . $user->user_login;
		if ( \ameUtils::get($settings, [$userActorId, 'strategy'], 'auto') !== 'auto' ) {
			$userSettings = $settings[$userActorId];
			if ( $userSettings['strategy'] === 'none' ) {
				//Leave the roles unchanged.
				$this->cachedOverallEditableRoleStrategy[$user->ID] = 'none';
				return $editableRoles;
			} else if ( $userSettings['strategy'] === 'user-defined-list' ) {
				//Allow editing only those roles that are on the list.
				$filteredResult = [];
				$allowedRoles = \ameUtils::get($userSettings, 'userDefinedList', []);
				foreach ($allRoles as $roleId => $role) {
					if ( isset($allowedRoles[$roleId]) ) {
						$filteredResult[$roleId] = $role;
					}
				}
				$this->cachedEditableRoles[$user->ID] = new ameEditableRoleReplacer(
					array_fill_keys(array_keys($filteredResult), true)
				);
				$this->cachedOverallEditableRoleStrategy[$user->ID] = 'user-defined-list';
				return $filteredResult;
			}
			//We'll only reach this line if the user's strategy setting is not valid.
			//In that case, let's leave the role list unchanged.
			return $editableRoles;
		}

		$leaveUnchanged = true;
		$hasAnyUserDefinedList = false;
		$autoDisabledRoles = [];
		$filteredResult = [];

		foreach ($allRoles as $roleId => $role) {
			$wasEnabled = isset($editableRoles[$roleId]);
			$canAutoDisable = false;

			//Include this role if at least one of the user's roles is allowed to edit it.
			foreach ($userRoles as $userRoleId) {
				$actorId = 'role:' . $userRoleId;

				$strategy = \ameUtils::get(
					$settings,
					[$actorId, 'strategy'],
					($actorId === 'role:administrator') ? 'none' : 'auto'
				);
				$leaveUnchanged = $leaveUnchanged && ($strategy === 'none');

				/*
				Special case: The "Administrator" role.

				Initially, Administrator had the same default restrictions as other roles. That is,
				they could only edit roles that had a subset of their capabilities. However, I got
				too many support requests from people who were surprised that their Administrator
				account couldn't edit users with custom roles that had some capabilities that the
				Administrator role didn't have.

				To avoid that, Administrator now defaults to "none" = leave unchanged. Of course,
				you can still change that in the settings.
				 */

				if ( $strategy === 'user-defined-list' ) {
					$hasAnyUserDefinedList = true;
					if ( isset($settings[$actorId]['userDefinedList'][$roleId]) ) {
						$filteredResult[$roleId] = $role;
						break;
					}
				} else if ( ($strategy === 'auto') ) {
					//Shortcut: A user with role X can assign role X to other users (assuming that they can edit users).
					if ( $roleId === $userRoleId ) {
						$shouldLeaveEnabled = true;
					} else {
						//Does the target role have the same or fewer capabilities as the user's role?
						$targetCaps = $this->getEnabledCoreCapabilitiesForRole($roleId, $role);
						$sameCaps = array_intersect_key(
							$targetCaps,
							$this->getEnabledCoreCapabilitiesForRole($userRoleId)
						);
						$shouldLeaveEnabled = (count($sameCaps) === count($targetCaps));
					}

					$canAutoDisable = !$shouldLeaveEnabled;
					if ( $wasEnabled && $shouldLeaveEnabled ) {
						$filteredResult[$roleId] = $role;
						break;
					}
				} else if ( $strategy === 'none' ) {
					if ( $wasEnabled ) {
						$filteredResult[$roleId] = $role;
						$canAutoDisable = false;
						break;
					}
				} else {
					//This should never happen.
					throw new \RuntimeException(sprintf(
						'Invalid editable role strategy "%s" for actor "%s".',
						$strategy,
						$actorId
					));
				}
			}

			if ( $canAutoDisable && !isset($filteredResult[$roleId]) ) {
				$autoDisabledRoles[] = $roleId;
			}
		}

		//Are all of the roles set to "none" = leave unchanged?
		if ( $leaveUnchanged ) {
			$this->cachedOverallEditableRoleStrategy[$user->ID] = 'none';
			return $editableRoles;
		}

		$overallStrategy = $hasAnyUserDefinedList ? 'user-defined-list' : 'auto';
		$this->cachedOverallEditableRoleStrategy[$user->ID] = $overallStrategy;

		//We won't need the capability cache again unless something changes or replaces the current user mid-request.
		//That's probably going to be rare, so we can throw away the cache to free up some RAM.
		$this->cachedEnabledRoleCaps = [];
		//Update the user cache.
		if ( $overallStrategy === 'auto' ) {
			$this->cachedEditableRoles[$user->ID] = new ameEditableRoleLimiter($autoDisabledRoles);
		} else {
			$this->cachedEditableRoles[$user->ID] = new ameEditableRoleReplacer(
				array_fill_keys(array_keys($filteredResult), true)
			);
		}

		if ( !$this->isRoleCacheClearingHookSet ) {
			$this->isRoleCacheClearingHookSet = true;
			//Clear cache when user roles or capabilities change.
			add_action('updated_user_meta', [$this, 'clearEditableRoleCache'], 10, 0);
			add_action('deleted_user_meta', [$this, 'clearEditableRoleCache'], 10, 0);
			//Clear cache when switching to another site because users can have different roles
			//on different sites.
			add_action('switch_blog', [$this, 'clearEditableRoleCache'], 10, 0);
		}

		return $filteredResult;
	}

	/**
	 * @param string $roleId
	 * @param array|null $roleData
	 * @return boolean[]
	 */
	private function getEnabledCoreCapabilitiesForRole($roleId, $roleData = null): array {
		if ( isset($this->cachedEnabledRoleCaps[$roleId]) ) {
			return $this->cachedEnabledRoleCaps[$roleId];
		}

		if ( $roleData ) {
			$capabilities = $roleData['capabilities'] ?? null;
		} else {
			$roleObject = get_role($roleId);
			$capabilities = $roleObject->capabilities ?? null;
		}
		if ( !isset($capabilities) || !is_array($capabilities) ) {
			return [];
		}

		$enabledCaps = array_filter($capabilities);

		//Keep only core capabilities like "edit_posts" and filter out custom capabilities added by plugins or themes.
		$enabledCaps = array_intersect_key($enabledCaps, $this->roleEditor->getDefaultCapabilities());

		$this->cachedEnabledRoleCaps[$roleId] = $enabledCaps;
		return $enabledCaps;
	}

	public function clearEditableRoleCache() {
		$this->cachedEditableRoles = [];
		$this->cachedOverallEditableRoleStrategy = [];
		$this->cachedEnabledRoleCaps = [];
	}

	/**
	 * @param int $userId
	 * @param string $default The default strategy to return if there is no cached value for the user.
	 * @return string
	 */
	public function getCachedStrategy(int $userId, string $default): string {
		if ( isset($this->cachedOverallEditableRoleStrategy[$userId]) ) {
			return $this->cachedOverallEditableRoleStrategy[$userId];
		}
		return $default;
	}

	public function enqueueScript(ScriptDependency $roleEditorScript): void {
		ScriptDependency::create(
			plugins_url('editable-roles-ui.js', __FILE__),
			'ame-rex-editable-roles-ui',
			__DIR__ . '/editable-roles-ui.js',
			[
				'ame-knockout',
				'ame-actor-manager',
				'ame-ko-extensions',
				$roleEditorScript,
			]
		)->enqueue();
	}

	public function outputTemplate(): void {
		include __DIR__ . '/editable-roles-template.php';
	}
}

interface ameEditableRoleFilter {
	/**
	 * @param array<string, array> $allRoles
	 * @param array<string, array> $editableRoles
	 * @return array<string, array> Filtered editable roles.
	 */
	public function filter($allRoles, $editableRoles);
}

/**
 * Replaces the list of editable roles with the specified list.
 * Any changes that were made by other plugins will be overwritten.
 */
class ameEditableRoleReplacer implements ameEditableRoleFilter {
	private $enabledRoles;

	/**
	 * @param array<string,mixed> $enabledRoles
	 */
	public function __construct($enabledRoles) {
		$this->enabledRoles = $enabledRoles;
	}

	public function filter($allRoles, $editableRoles) {
		$result = [];
		foreach ($allRoles as $roleId => $role) {
			if ( isset($this->enabledRoles[$roleId]) ) {
				$result[$roleId] = $role;
			}
		}
		return $result;
	}

}

/**
 * Removes the specified roles from the list of editable roles.
 */
class ameEditableRoleLimiter implements ameEditableRoleFilter {
	private $rolesToRemove;

	/**
	 * @param string[] $rolesToRemove
	 */
	public function __construct($rolesToRemove) {
		$this->rolesToRemove = $rolesToRemove;
	}

	public function filter($allRoles, $editableRoles) {
		foreach ($this->rolesToRemove as $roleId) {
			if ( array_key_exists($roleId, $editableRoles) ) {
				unset($editableRoles[$roleId]);
			}
		}
		return $editableRoles;
	}
}