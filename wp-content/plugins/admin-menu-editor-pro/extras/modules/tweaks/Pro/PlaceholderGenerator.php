<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Pro;

use YahnisElsts\AdminMenuEditor\Tweaks\Core\ameTweakManager;

class PlaceholderGenerator {
	const GENERATE_PLACEHOLDERS_ACTION = 'ame-twm-generate-placeholders';

	private ameTweakManager $manager;

	public function __construct(ameTweakManager $manager) {
		$this->manager = $manager;

		add_action('admin_menu_editor-footer-tweaks', [$this, 'outputPlaceholderArea']);
	}

	public function outputPlaceholderArea() {
		if ( !current_user_can('install_plugins') ) {
			return;
		}

		$submissionUrl = $this->manager->getTabUrl();
		?>
		<form action="<?php echo esc_url($submissionUrl); ?>" method="post"
		      id="ame-twm-placeholder-code-form" style="margin-top: 20px;">
			<?php wp_nonce_field(self::GENERATE_PLACEHOLDERS_ACTION); ?>
			<input type="hidden" name="<?php echo esc_attr(self::GENERATE_PLACEHOLDERS_ACTION); ?>" value="1">
			<input type="submit" class="button" value="Generate Placeholder Code">
		</form>
		<?php

		if ( isset($_POST[self::GENERATE_PLACEHOLDERS_ACTION]) && check_admin_referer(self::GENERATE_PLACEHOLDERS_ACTION) ) {
			$this->outputPlaceholderCode();
		}
	}

	public function outputPlaceholderCode() {
		echo '<div id="ame-twm-placeholder-code"><pre>';
		//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		echo esc_html('$definitions = ' . var_export($this->generatePlaceholders(), true) . ';');
		echo '</pre></div>';
	}

	public function generatePlaceholders(): array {
		$freeSections = array_flip(['general', 'admin-css', 'gutenberg-general', 'plugins-page', 'environment-type']);

		$sectionPlaceholders = [];
		$tweakPlaceholders = [];

		foreach ($this->manager->getSections() as $sectionId => $section) {
			if ( isset($freeSections[$sectionId]) || $section->isProPlaceholder() ) {
				continue;
			}

			$placeholderSectionId = $this->getPlaceholderId($sectionId);
			$sectionData = [
				'label'    => $section->getLabel(),
				'priority' => $section->getPriority() + 1, //Placeholders after real sections, for easier debugging.
				'children' => [],
			];
			//Note: Section descriptions are not included in the placeholders as they usually
			//contain details and clarifications that are not relevant for a placeholder.

			$sectionPlaceholders[$placeholderSectionId] = $sectionData;
		}

		foreach ($this->manager->getRegisteredTweaks() as $tweakId => $tweak) {
			if ( \ameUtils::stringStartsWith($tweakId, 'plh-') ) {
				continue; //No placeholders for things that are already placeholders.
			}

			$parentPlaceholderId = $this->getPlaceholderId($tweak->getParentId());
			if ( !empty($parentPlaceholderId) && !isset($tweakPlaceholders[$parentPlaceholderId]) ) {
				continue;
			}
			$tweakSectionId = $this->getPlaceholderId($tweak->getSectionId());
			if ( !empty($tweakSectionId) && !isset($sectionPlaceholders[$tweakSectionId]) ) {
				continue;
			}

			if ( empty($parentPlaceholderId) && empty($tweakSectionId) ) {
				continue; //A tweak from the default "General" section, which doesn't have a placeholder.
			}

			$placeholderTweakId = $this->getPlaceholderId($tweakId);
			$tweakData = ['label' => $tweak->getLabel(), 'children' => []];

			if ( $tweakSectionId ) {
				$tweakData['section'] = $tweakSectionId;
				if ( empty($parentPlaceholderId) ) {
					$sectionPlaceholders[$tweakSectionId]['children'][] = $placeholderTweakId;
				}
			}

			if ( $parentPlaceholderId ) {
				$tweakData['parent'] = $parentPlaceholderId;
				$tweakPlaceholders[$parentPlaceholderId]['children'][] = $placeholderTweakId;
			}

			if ( $tweak->getDescription() ) {
				$tweakData['description'] = $tweak->getDescription();
			}
			$tweakPlaceholders[$placeholderTweakId] = $tweakData;
		}

		//Aliases - pretty much the same logic as tweaks.
		foreach ($this->manager->getAliases() as $alias) {
			$targetTweakId = $alias->getTweakId();
			$placeholderTargetTweakId = $this->getPlaceholderId($targetTweakId);
			if ( !isset($tweakPlaceholders[$placeholderTargetTweakId]) ) {
				continue;
			}

			$placeholderAliasId = $this->getPlaceholderId($targetTweakId . '--alias-' . substr(uniqid(), -4));
			$aliasData = ['label' => $alias->getLabel() ?? $targetTweakId];

			if ( !empty($alias->getParentId()) ) {
				$parentPlaceholderId = $this->getPlaceholderId($alias->getParentId());
				if ( isset($tweakPlaceholders[$parentPlaceholderId]) ) {
					$aliasData['parent'] = $parentPlaceholderId;
					$tweakPlaceholders[$parentPlaceholderId]['children'][] = $placeholderAliasId;
				}
			}

			$plainSectionId = $alias->getSectionId() ?? 'general';
			$tweakSectionId = $this->getPlaceholderId($plainSectionId);
			if ( isset($sectionPlaceholders[$tweakSectionId]) ) {
				$aliasData['section'] = $tweakSectionId;
				if ( empty($aliasData['parent']) ) {
					$sectionPlaceholders[$tweakSectionId]['children'][] = $placeholderAliasId;
				}
			}

			if ( empty($aliasData['parent']) && empty($aliasData['section']) ) {
				continue;
			}

			$tweakPlaceholders[$placeholderAliasId] = $aliasData;
		}

		$formattedSections = [];
		$formattedTweaks = [];
		$maxTweaksPerSection = 10;

		$sectionProps = array_flip(['label', 'description', 'priority']);
		$tweakProps = array_flip(['label', 'description', 'section', 'parent']);

		foreach ($sectionPlaceholders as $id => $data) {
			$formattedSectionData = array_intersect_key($data, $sectionProps);
			$formattedSections[$id] = $formattedSectionData;

			$includedTweaks = 0;
			foreach ($this->iterateTweakPlaceholders($data['children'], $tweakPlaceholders) as $tweakId => $tweakData) {
				if ( $includedTweaks >= $maxTweaksPerSection ) {
					break;
				}
				$formattedTweaks[$tweakId] = array_intersect_key($tweakData, $tweakProps);
				$includedTweaks++;
			}
		}

		return ['sections' => $formattedSections, 'tweaks' => $formattedTweaks];
	}

	private function getPlaceholderId($originalId): string {
		if ( empty($originalId) ) {
			return '';
		}
		return 'plh-' . $originalId;
	}

	private function iterateTweakPlaceholders($ids, $allPlaceholderTweaks): \Generator {
		foreach ($ids as $id) {
			if ( isset($allPlaceholderTweaks[$id]) ) {
				yield $id => $allPlaceholderTweaks[$id];
				foreach ($allPlaceholderTweaks[$id]['children'] ?? [] as $childId) {
					yield from $this->iterateTweakPlaceholders([$childId], $allPlaceholderTweaks);
				}
			}
		}
	}
}