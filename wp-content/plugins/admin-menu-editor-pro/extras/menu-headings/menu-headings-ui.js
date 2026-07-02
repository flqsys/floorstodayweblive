import { AmeCustomizableViewModel } from '../pro-customizables/assets/customizable.js';
var AmeMenuHeadingsUi;
(function (AmeMenuHeadingsUi) {
    const _ = wsAmeLodash;
    const $ = jQuery;
    var some = AmeMiniFunc.some;
    var none = AmeMiniFunc.none;
    class MenuHeadingConfigViewModel extends AmeCustomizableViewModel.SimpleVm {
        constructor(config, $dialog) {
            super();
            this.$dialog = $dialog;
            this.notFound = {};
            this.presetsById = {};
            this.isApplyingPreset = false;
            //region Dialog handling
            this.wasDialogOpened = false;
            this.settingIdPrefix = config.settingIdPrefix;
            this.defaults = config.defaults;
            //Convert [path => alias path] to [ID => alias ID] for convenience.
            const readAliasIds = {};
            for (const path in config.readAliases) {
                readAliasIds[config.settingIdPrefix + path] = config.settingIdPrefix + config.readAliases[path];
            }
            this.registerSettingReader((settingId, defaultValue) => {
                const value = AmeEditorApi.configDataAdapter.getSettingValue(settingId, this.notFound);
                if (value !== this.notFound) {
                    return value;
                }
                //Backwards compatibility: Try the alias, if any.
                const aliasSettingId = _.get(readAliasIds, settingId, null);
                if (aliasSettingId) {
                    const aliasValue = AmeEditorApi.configDataAdapter.getSettingValue(aliasSettingId, this.notFound);
                    if (aliasValue !== this.notFound) {
                        return aliasValue;
                    }
                }
                if (config.defaults.hasOwnProperty(settingId)) {
                    return config.defaults[settingId];
                }
                else {
                    return defaultValue;
                }
            }, config.settingIdPrefix);
            for (const preset of config.presets) {
                this.presetsById[preset.id] = preset;
            }
            const selectedPresetSettingId = config.settingIdPrefix + 'selectedPreset';
            this.presetExclusions = new Set([
                selectedPresetSettingId,
                config.settingIdPrefix + 'iconVisibility',
            ]);
            //Whenever the user selects a non-custom preset, apply it automatically.
            //Note: Careful to avoid applying presets when all settings are loaded for the first time,
            //or when settings are reloaded upon opening a dialog.
            const selectedPreset = this.getSettingObservable(selectedPresetSettingId);
            selectedPreset.subscribe((presetId) => {
                if ((presetId === 'custom') || !this.canApplyPresetsNow()) {
                    return;
                }
                this.applyPreset(presetId);
            });
            //If the user changes a setting that can be included in the presets, switch the selected
            //preset to "custom".
            this.addSettingChangeListener((setting) => {
                if (!this.presetExclusions.has(setting.id)
                    && this.canApplyPresetsNow()
                    && (selectedPreset() !== 'custom')) {
                    selectedPreset('custom');
                }
            });
            $dialog.on('dialogopen', () => {
                this.onOpenDialog();
            });
        }
        applyPreset(presetId) {
            if (!this.presetsById.hasOwnProperty(presetId)) {
                return;
            }
            if (this.isApplyingPreset) {
                //This should never happen unless there's a bug.
                throw new Error('Already applying a preset, cannot apply another one at the same time.');
            }
            this.isApplyingPreset = true;
            const presetSettings = this.presetsById[presetId].settings;
            //Set each setting to the preset value if it exists, or reset it to default.
            //Don't change excluded settings.
            this.updateSettings((settingId) => {
                if (this.presetExclusions.has(settingId)) {
                    return none;
                }
                //To get the dot-separated path, just remove the prefix.
                const path = settingId.substring(this.settingIdPrefix.length);
                const presetValue = _.get(presetSettings, path, this.notFound);
                if (presetValue !== this.notFound) {
                    return some(presetValue);
                }
                else if (this.defaults.hasOwnProperty(settingId)) {
                    return some(this.defaults[settingId]);
                }
                return none;
            });
            this.isApplyingPreset = false;
        }
        canApplyPresetsNow() {
            return !this.isApplyingPreset;
        }
        saveChanges() {
            //This is largely equivalent to how the menu styler UI does it. See that implementation
            //for more comments.
            const settingsById = this.getAllSettingValues();
            const sortedIds = _(settingsById).keys()
                .orderBy([id => id.length, id => id]).value();
            const updatedConfig = {};
            for (const settingId of sortedIds) {
                const value = settingsById[settingId];
                const path = AmeEditorApi.configDataAdapter.mapSettingIdToPath(settingId);
                if ((value !== null) && (path !== null)) {
                    _.set(updatedConfig, path, value);
                }
            }
            _.set(updatedConfig, ['menu_headings', 'modificationTimestamp'], Math.round(Date.now() / 1000));
            for (const [key, value] of Object.entries(updatedConfig)) {
                AmeEditorApi.configDataAdapter.setPath(key, value);
            }
            $(document).trigger('adminMenuEditor:menuConfigChanged');
        }
        onOpenDialog() {
            if (this.wasDialogOpened) {
                //Reload settings since the menu config might have changed (e.g. due to an import).
                //This also clears any unsaved changes from the last time the dialog was opened.
                this.reloadAllSettings();
            }
            this.wasDialogOpened = true;
        }
        // noinspection JSUnusedGlobalSymbols -- It's used in the template, PhpStorm just fails to detect it.
        onConfirmDialog() {
            this.saveChanges();
            this.closeDialog();
        }
        onCancelDialog() {
            this.closeDialog();
        }
        closeDialog() {
            this.$dialog.dialog('close');
        }
    }
    function detectMenuColors() {
        //Detect menu background color and text color, and store them in CSS variables.
        //This allows the heading presets to use the same colors as the menu, for a better
        //preview of how the headings would look in the menu.
        const $adminMenu = jQuery('#adminmenumain #adminmenu');
        if ($adminMenu.length > 0) {
            const bgColor = $adminMenu.css('background-color');
            const textColor = $adminMenu.find('li.menu-top')
                .not('.wp-menu-separator')
                .not('.ame-menu-heading-item')
                .first()
                .find('> a')
                .css('color');
            document.documentElement.style.setProperty('--ame-mh-detected-menu-background', bgColor);
            document.documentElement.style.setProperty('--ame-mh-detected-menu-color', textColor);
        }
    }
    let $headingDialog = null;
    let isDialogInitialized = false;
    function initializeDialog() {
        isDialogInitialized = true;
        //Load the heading preset stylesheet. It's not used outside of this dialog, so we
        //only load it when needed.
        $('<link rel="stylesheet" type="text/css">')
            .attr('href', ameMenuHeadingConfig.presetStylesheetUrl)
            .appendTo('head');
        $headingDialog = $('#ws-ame-menu-heading-settings-next');
        $headingDialog.dialog({
            autoOpen: false,
            closeText: ' ',
            draggable: false,
            modal: true,
            minWidth: 400,
            minHeight: 400,
            width: 800,
            classes: {
                'ui-dialog': 'ui-corner-all ws-ame-menu-heading-dialog',
            }
        });
        const vm = new MenuHeadingConfigViewModel(ameMenuHeadingConfig, $headingDialog);
        ko.applyBindings(vm, $headingDialog[0]);
        window['ameMenuHeadingConfigVm'] = vm; //Handy for testing and debugging.
    }
    $(function () {
        detectMenuColors();
        $('#ws_edit_heading_styles_next').on('click', function () {
            if (!isDialogInitialized) {
                initializeDialog();
            }
            $headingDialog?.dialog('open');
        });
    });
})(AmeMenuHeadingsUi || (AmeMenuHeadingsUi = {}));
//# sourceMappingURL=menu-headings-ui.js.map