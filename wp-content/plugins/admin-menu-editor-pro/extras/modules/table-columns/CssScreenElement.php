<?php

namespace YahnisElsts\AdminMenuEditor\TableColumns;

use YahnisElsts\AdminMenuEditor\Options\Option;

class CssScreenElement implements \JsonSerializable {
	const SEARCH_BOX = 'search-box';
	const PAGINATION = 'pagination';
	const BULK_ACTIONS = 'bulk-actions';
	const EXTRA_NAVIGATION = 'extra-navigation';
	const ADD_NEW_BUTTON = 'add-new-button';

	const VALID_ELEMENT_IDS = [
		self::SEARCH_BOX       => true,
		self::PAGINATION       => true,
		self::BULK_ACTIONS     => true,
		self::EXTRA_NAVIGATION => true,
		self::ADD_NEW_BUTTON   => true,
	];

	const TOP_TABLE_NAV_ELEMENTS = [
		self::BULK_ACTIONS,
		self::EXTRA_NAVIGATION,
		self::PAGINATION,
	];

	private string $id;
	private string $title;
	private array $selectors;
	private int $displayOrder;

	private static ?array $predefinedElements = null;

	public function __construct(string $id, string $title, array $selectors, int $displayOrder) {
		$this->id = $id;
		$this->title = $title;
		$this->selectors = $selectors;
		$this->displayOrder = $displayOrder;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getSelectors(): array {
		return $this->selectors;
	}

	/**
	 * Create a copy of this element with a different title.
	 *
	 * @param string $newTitle
	 * @return CssScreenElement
	 */
	public function withTitle(string $newTitle): CssScreenElement {
		return new CssScreenElement(
			$this->id,
			$newTitle,
			$this->selectors,
			$this->displayOrder
		);
	}

	private static function getPredefinedElements(): ?array {
		if ( self::$predefinedElements === null ) {
			self::$predefinedElements = [
				self::ADD_NEW_BUTTON   => new CssScreenElement(
					self::ADD_NEW_BUTTON,
					'Add button',
					['.page-title-action', '.add-new-h2'],
					10
				),
				self::BULK_ACTIONS     => new CssScreenElement(
					self::BULK_ACTIONS,
					'Bulk actions',
					['.tablenav .bulkactions'],
					20
				),
				self::EXTRA_NAVIGATION => new CssScreenElement(
					self::EXTRA_NAVIGATION,
					'Filters',
					['.tablenav .actions:not(.bulkactions)'],
					30
				),
				self::PAGINATION       => new CssScreenElement(
					self::PAGINATION,
					'Pagination',
					['.tablenav .tablenav-pages'],
					40
				),
				self::SEARCH_BOX       => new CssScreenElement(
					self::SEARCH_BOX,
					'Search box',
					['.wrap form p.search-box'],
					50
				),
			];
		}

		return self::$predefinedElements;
	}

	public static function getKnownElement(string $id): CssScreenElement {
		$elements = self::getPredefinedElements();
		if ( isset($elements[$id]) ) {
			return $elements[$id];
		}
		throw new \InvalidArgumentException('Unknown screen element ID: ' . $id);
	}

	public static function getElementOption(string $id): Option {
		$elements = self::getPredefinedElements();
		if ( isset($elements[$id]) ) {
			return Option::some($elements[$id]);
		}
		return Option::none();
	}

	/**
	 * @param string $screenId
	 * @param array $screenSettings
	 * @return array<string,CssScreenElement>
	 */
	public static function guessAvailableElements(string $screenId, array $screenSettings): array {
		$availableElements = [];

		//First, add all predefined elements that are present according to the settings.
		foreach ($screenSettings['elements'] ?? [] as $elementId => $elementSettings) {
			try {
				$element = self::getKnownElement($elementId);
				$availableElements[$elementId] = $element;
			} catch (\InvalidArgumentException $e) {
				//Unknown element. This could happen if the settings are from a different version
				//that supported different elements.
			}
		}

		//Then figure out what other elements are likely present based on the screen type and
		//settings. This is not meant to be perfectly accurate, just based on common patterns.
		$expectedElements = [];

		//If any bulk actions were detected, the "Bulk actions" element should be included.
		if ( !empty($screenSettings['bulkActions']) && !isset($availableElements[self::BULK_ACTIONS]) ) {
			$expectedElements[] = self::BULK_ACTIONS;
		}

		//Some elements are usually present on every screen that uses a WP list table.
		$usuallyPresentElements = [self::SEARCH_BOX, self::PAGINATION];
		foreach ($usuallyPresentElements as $elementId) {
			$expectedElements[] = $elementId;
		}

		//Post list tables usually have a filter box next to "Bulk actions", and so do the "Users"
		//and "Comments" screens.
		if (
			!empty($screenSettings['postType'])
			|| ($screenId === 'users')
			|| ($screenId === 'edit-comments')
		) {
			$expectedElements[] = self::EXTRA_NAVIGATION;
		}

		//Most screens have some kind of "Add new" button, but the "Comments" screen doesn't.
		if ( $screenId !== 'edit-comments' ) {
			$expectedElements[] = self::ADD_NEW_BUTTON;
		}

		foreach ($expectedElements as $elementId) {
			if ( !isset($availableElements[$elementId]) ) {
				$availableElements[$elementId] = self::getKnownElement($elementId);
			}
		}

		//On the "Users" screen, the extra navigation element actually contains a "Change role"
		//dropdown instead of filters. Let's detect that and give it a different title.
		if ( ($screenId === 'users') && isset($availableElements[self::EXTRA_NAVIGATION]) ) {
			$element = $availableElements[self::EXTRA_NAVIGATION];
			$element = $element->withTitle('Change role box');
			$availableElements[self::EXTRA_NAVIGATION] = $element;
		}

		return $availableElements;
	}

	public function jsonSerialize(): array {
		return [
			'title'           => $this->title,
			'defaultPosition' => $this->displayOrder,
		];
	}
}