<?php

namespace FileBird\Support;

use FileBird\Support\AdminColumns\FileBirdColumn;

defined('ABSPATH') || exit;

// use Filebird\Support\AdminColumns\Column;
class AdminColumns
{
	public function __construct()
	{
		if( defined( 'ACP_VERSION' ) && version_compare( ACP_VERSION, '7.0.10', '<' ) ) {
			return;
		}
		add_filter('ac/column/types', array($this, 'register_column_type'), 10, 3);
	}

	public function register_column_type(array $factories, $table_screen, $container = null): array
	{
		// Only add column for Media screen
		if ((string) $table_screen->get_id() !== 'wp-media') {
			return $factories;
		}

		// Include custom column classes
		require_once __DIR__ . '/AdminColumns/FileBirdFormatter.php';
		require_once __DIR__ . '/AdminColumns/FileBirdColumn.php';

		// Register the custom FileBird Column
		$factories[] = FileBirdColumn::class;

		return $factories;
	}
}