<?php

namespace YahnisElsts\AdminMenuEditor\DynamicStylesheets;

class DynamicStylesheetBundle extends Stylesheet {
	const CACHE_TTL_FALLBACK = 12 * 3600;

	/**
	 * @var \YahnisElsts\AdminMenuEditor\DynamicStylesheets\Stylesheet[]
	 */
	protected $stylesheets = [];

	/**
	 * @var string|null A hash representing the combination of stylesheet handles, used for cache versioning.
	 */
	protected $stylesheetNamesHash = null;

	public function __construct($handle, $cache = null, $cacheKeySuffix = '', $additionalQueryParameters = []) {
		parent::__construct(
			$handle,
			function () {
				return $this->generateCombinedCss();
			},
			null,
			$cache,
			$cacheKeySuffix,
			$additionalQueryParameters
		);

		if ( $cache !== null ) {
			$this->cache = $cache;
		}
	}

	public function addStylesheet(Stylesheet $stylesheet) {
		$this->stylesheets[] = $stylesheet;
		$this->stylesheetNamesHash = null;
	}

	protected function generateCombinedCss() {
		//Sort the stylesheets by priority first (lowest to highest).
		usort($this->stylesheets, function (Stylesheet $a, Stylesheet $b) {
			$priorityA = $a->getPriority();
			$priorityB = $b->getPriority();
			return $priorityA <=> $priorityB;
		});

		$parts = [];
		foreach ($this->stylesheets as $stylesheet) {
			$content = $stylesheet->generateCss();
			if ( !is_string($content) ) {
				continue;
			}
			$content = trim($content);
			if ( !empty($content) ) {
				$parts[] = $content;
			}
		}
		return implode("\n", $parts);
	}

	protected function getLastModifiedTimestamp() {
		//Use the most recent timestamp of all stylesheets.
		$lastModifiedTimestamp = 0;
		foreach ($this->stylesheets as $stylesheet) {
			$timestamp = $stylesheet->getLastModifiedTimestamp();
			if ( ($timestamp !== null) && ($timestamp > $lastModifiedTimestamp) ) {
				$lastModifiedTimestamp = $timestamp;
			}
		}
		return $lastModifiedTimestamp;
	}

	protected function getCacheTtl() {
		//Use the lowest non-zero TTL.
		$ttl = null;
		foreach ($this->stylesheets as $stylesheet) {
			$stylesheetTtl = $stylesheet->getCacheTtl();
			if ( ($stylesheetTtl === null) || ($stylesheetTtl <= 0) ) {
				continue;
			}
			if ( ($ttl === null) || ($stylesheetTtl < $ttl) ) {
				$ttl = $stylesheetTtl;
			}
		}

		if ( ($ttl !== null) && ($ttl > 0) ) {
			return $ttl;
		} else {
			return self::CACHE_TTL_FALLBACK;
		}
	}

	protected function getVersionPrefix() {
		$prefix = parent::getVersionPrefix();
		$namesHash = $this->getStylesheetNamesHash();
		if ( !empty($namesHash) ) {
			if ( empty($prefix) ) {
				$prefix = 'N' . $namesHash;
			} else {
				$prefix .= '-N' . $namesHash;
			}
		}
		return $prefix;
	}

	protected function getStylesheetNamesHash() {
		if ( $this->stylesheetNamesHash === null ) {
			$stylesheetNames = '';
			foreach ($this->stylesheets as $stylesheet) {
				$stylesheetNames .= '!' . $stylesheet->handle;
			}

			if ( !empty($stylesheetNames) ) {
				$this->stylesheetNamesHash = substr(sha1($stylesheetNames, false), 0, 5);
			} else {
				$this->stylesheetNamesHash = '';
			}
		}
		return $this->stylesheetNamesHash;
	}

}