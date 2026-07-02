<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks\Core;

class ameTweakSection {
	private $id;
	private $label;

	private $priority = 10;

	private $description;

	private bool $isProPlaceholder = false;
	private string $proPlaceholderMessage = '';
	const DEFAULT_PRO_PLACEHOLDER_MESSAGE = 'This feature is available in Admin Menu Editor Pro.';

	public function __construct($id, $label, $description = '') {
		$this->id = $id;
		$this->label = $label;
		$this->description = $description;
	}

	public function getId() {
		return $this->id;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getPriority(): int {
		return $this->priority;
	}

	public function setPriority($priority): self {
		$this->priority = $priority;
		return $this;
	}

	public function setDescription($description): self {
		$this->description = $description;
		return $this;
	}

	public function getDescription() {
		return $this->description;
	}

	public function isProPlaceholder(): bool {
		return $this->isProPlaceholder;
	}

	public function getProPlaceholderMessage(): string {
		return $this->proPlaceholderMessage ?: self::DEFAULT_PRO_PLACEHOLDER_MESSAGE;
	}

	public static function fromDefinition(string $id, array $definition = []): self {
		$section = new self(
			$id,
			$definition['label'] ?? $id,
			$definition['description'] ?? ''
		);
		if ( isset($definition['priority']) ) {
			$section->setPriority($definition['priority']);
		}
		if ( isset($definition['isProPlaceholder']) ) {
			$section->isProPlaceholder = (bool)$definition['isProPlaceholder'];
			$section->proPlaceholderMessage = $definition['proPlaceholderMessage'] ?? '';
		}
		return $section;
	}

	public function toArray(): array {
		$sectionData = [
			'id'       => $this->getId(),
			'label'    => $this->getLabel(),
			'priority' => $this->getPriority(),
		];

		if ( !empty($this->description) ) {
			$sectionData['description'] = $this->getDescription();
		}

		return $sectionData;
	}
}