<?php

namespace YahnisElsts\AdminMenuEditor\Tweaks;

/*
 * This file no longer does much, but it still exists for backward compatibility. Most of the actual
 * classes get autoloaded. The exceptions are the classes in the "configurables.php" file, which still
 * haven't been moved to separate files (and some may be unused).
 */

/*
 * Idea: Show tweaks as options in menu properties, e.g. in a "Tweaks" section styled like the collapsible
 * property sheets in Delphi.
 */

require_once __DIR__ . '/configurables.php';

//TODO: When importing tweak settings, pick the largest of lastUserTweakSuffix. See mergeSettingsWith().
