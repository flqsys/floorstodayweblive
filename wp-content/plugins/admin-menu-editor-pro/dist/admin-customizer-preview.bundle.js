"use strict";
(self["wsAmeWebpackChunk"] = self["wsAmeWebpackChunk"] || []).push([["admin-customizer-preview"],{

/***/ "./extras/modules/admin-customizer/preview-handler.ts"
/*!************************************************************!*\
  !*** ./extras/modules/admin-customizer/preview-handler.ts ***!
  \************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../pro-customizables/assets/customizable.js */ "./extras/pro-customizables/assets/customizable.js");
/* harmony import */ var _admin_customizer_base_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./admin-customizer-base.js */ "./extras/modules/admin-customizer/admin-customizer-base.js");
/* harmony import */ var _style_generator_style_generator_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../style-generator/style-generator.js */ "./extras/style-generator/style-generator.js");




//Compatibility note: This script is not compatible with IE11 because it uses some
//modern JS features like the URLSearchParams class.
var AmeAdminCustomizerPreview;
(function (AmeAdminCustomizerPreview) {
    var ThrottledPreviewRegistry = _pro_customizables_assets_customizable_js__WEBPACK_IMPORTED_MODULE_0__.AmeCustomizable.ThrottledPreviewRegistry;
    const $ = jQuery;
    class PreviewHandler extends _admin_customizer_base_js__WEBPACK_IMPORTED_MODULE_1__.AmeAdminCustomizerBase.AdminCustomizerBase {
        constructor(scriptData) {
            super(scriptData);
            this.currentPreviewValues = {};
            this.changesetName = scriptData.changesetName;
            this.previewRegistry = new ThrottledPreviewRegistry((settingId, defaultResult) => {
                if (this.currentPreviewValues.hasOwnProperty(settingId)) {
                    return this.currentPreviewValues[settingId];
                }
                //Try the script data. It should have the current value from the changeset.
                if (scriptData.settings.hasOwnProperty(settingId)
                    && scriptData.settings[settingId].hasOwnProperty('value')) {
                    return scriptData.settings[settingId].value;
                }
                return defaultResult;
            });
            this.connection = AmeAcCommunicator.connectToParent({
                'previewSetting': (settingId, value) => {
                    this.currentPreviewValues[settingId] = value;
                    if (!this.previewRegistry.canPreview(settingId)) {
                        return false;
                    }
                    this.previewRegistry.queuePreview(settingId);
                    return true;
                },
                'getCurrentUrl': () => {
                    return window.location.href;
                }
            }, scriptData.allowedCommOrigins, scriptData.isWpDebugEnabled);
            this.connection.promise.then((c) => {
                if (typeof c === 'undefined') {
                    if (console && console.warn) {
                        console.warn('Connection succeeded, but the communicator is undefined. This should be impossible.');
                    }
                    return; //This should never happen.
                }
                //Let the parent know the current URL. The parent might not be able to
                //read it due to cross-domain restrictions, and if there are any redirects,
                //the actual URL might not match the frame src that was set by the parent.
                c.execute('notifyPreviewUrlChanged', window.location.href);
            });
            $(() => {
                this.addPreviewParamsToLinks();
                //Handle clicks on links.
                $(document.body).on('click.ame-ac-preview', 'a', (event) => {
                    return this.handleLinkClick(event);
                });
                //Block form submissions. Theme Customizer supports those, but we don't
                //(at least for now).
                $(document.body).on('submit.ame-ac-preview', 'form', function (event) {
                    event.preventDefault();
                });
            });
            //For convenience, support for StyleGenerator previews is built-in.
            for (const previewConfig of (scriptData.stylePreviewConfigs || [])) {
                const previewInstance = new _style_generator_style_generator_js__WEBPACK_IMPORTED_MODULE_2__.AmeStyleGenerator.Preview.StyleGeneratorPreview(previewConfig);
                this.previewRegistry.registerPreviewUpdater(previewInstance.getPreviewableSettingIDs(), previewInstance);
            }
        }
        /**
         * Add preview-specific query parameters to all links.
         */
        addPreviewParamsToLinks() {
            const self = this;
            $('a[href]').each(function () {
                const element = this;
                if (!(element instanceof HTMLAnchorElement)) {
                    return;
                }
                const $link = $(this);
                //Don't modify internal anchors like "#abc".
                if (self.isInternalAnchor($link)) {
                    return;
                }
                //Flag and skip non-previewable links.
                if (!self.isPreviewableLink(element)) {
                    $link.addClass('ame-ac-not-previewable');
                    return;
                }
                //Add the preview query parameter(s).
                const params = new URLSearchParams(element.search);
                params.set('ame-ac-preview', '1');
                params.set('ame-ac-changeset', self.changesetName);
                element.search = '?' + params.toString();
            });
        }
        isPreviewableLink(element) {
            return this.isPreviewableUrl(element);
        }
        isInternalAnchor($link) {
            const href = $link.attr('href');
            if (typeof href === 'undefined') {
                return false;
            }
            return (href.substring(0, 1) === '#');
        }
        handleLinkClick(event) {
            const $link = $(event.target).closest('a');
            //Let anchors work as normal.
            if (this.isInternalAnchor($link)) {
                return;
            }
            //Prevent the browser from navigating to non-previewable links.
            const anchorElement = $link.get(0);
            if (!this.isPreviewableLink(anchorElement)) {
                event.preventDefault();
                return;
            }
            //Tell the parent (i.e. the admin customizer) to load the link.
            if (this.connection.isConnected) {
                event.preventDefault();
                this.connection.execute('setPreviewUrl', anchorElement.href);
            }
        }
        // noinspection JSUnusedGlobalSymbols Used in other modules.
        registerPreviewer(settingId, callback) {
            this.previewRegistry.registerPreviewCallback(settingId, callback);
        }
        // noinspection JSUnusedGlobalSymbols Also used in other modules.
        registerPreviewUpdater(settingIds, updater) {
            this.previewRegistry.registerPreviewUpdater(settingIds, updater);
        }
        // noinspection JSUnusedGlobalSymbols
        registerRpcMethod(methodName, handler) {
            this.connection.addRpcMethod(methodName, handler);
        }
    }
    AmeAdminCustomizerPreview.PreviewHandler = PreviewHandler;
    const previewHandler = new PreviewHandler(wsAmeAcPreviewData);
    window['wsAdminCustomizerPreview'] = previewHandler;
    $('body').trigger('adminMenuEditor:acPreviewStart', [previewHandler]);
})(AmeAdminCustomizerPreview || (AmeAdminCustomizerPreview = {}));


/***/ },

/***/ "./extras/modules/admin-customizer/admin-customizer-base.js"
/*!******************************************************************!*\
  !*** ./extras/modules/admin-customizer/admin-customizer-base.js ***!
  \******************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeAdminCustomizerBase: () => (/* binding */ AmeAdminCustomizerBase)
/* harmony export */ });

var AmeAdminCustomizerBase;
(function (AmeAdminCustomizerBase) {
    class AdminCustomizerBase {
        constructor(scriptData) {
            this.allowedCommOrigins = scriptData.allowedCommOrigins;
            if (this.allowedCommOrigins.length === 0) {
                this.allowedCommOrigins = [window.location.origin];
            }
            this.allowedPreviewUrls = scriptData.allowedPreviewUrls;
            this.parsedAllowedUrls = this.allowedPreviewUrls.map(url => new URL(url));
        }
        isPreviewableUrl(url) {
            if (typeof url === 'string') {
                url = new URL(url);
            }
            if (typeof url.protocol === 'undefined') {
                return false;
            }
            //Only HTTP(S) links are previewable.
            if ((url.protocol !== 'http:') && (url.protocol !== 'https:')) {
                return false;
            }
            //Check against the list of allowed URLs.
            for (const allowedUrl of this.parsedAllowedUrls) {
                //Protocol and host must match. The path must start with the path
                //of the allowed URL (possibly without a trailing slash).
                if ((url.protocol === allowedUrl.protocol) && (url.host === allowedUrl.host)) {
                    const allowedPath = allowedUrl.pathname.replace(/\/$/, '');
                    if (url.pathname.indexOf(allowedPath) === 0) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
    AmeAdminCustomizerBase.AdminCustomizerBase = AdminCustomizerBase;
})(AmeAdminCustomizerBase || (AmeAdminCustomizerBase = {}));
//# sourceMappingURL=admin-customizer-base.js.map

/***/ },

/***/ "./extras/style-generator/style-generator.js"
/*!***************************************************!*\
  !*** ./extras/style-generator/style-generator.js ***!
  \***************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AmeStyleGenerator: () => (/* binding */ AmeStyleGenerator)
/* harmony export */ });
var AmeStyleGenerator;
(function (AmeStyleGenerator) {
    const $ = jQuery;
    const valueDescriptorTags = ['constant', 'setting', 'funcCall', 'var', 'array'];
    function isValueDescriptorData(input) {
        return valueDescriptorTags.includes(input.t);
    }
    class ValueDescriptor {
    }
    class ConstantValue extends ValueDescriptor {
        constructor(value) {
            super();
            this.value = value;
        }
        getValue() {
            return this.value;
        }
    }
    class ArrayValue extends ValueDescriptor {
        constructor(items) {
            super();
            this.items = items;
        }
        getValue() {
            return this.items.map(item => item.getValue());
        }
        getItemDescriptors() {
            return this.items;
        }
    }
    class SettingReference extends ValueDescriptor {
        constructor(settingId, valueGetter) {
            super();
            this.settingId = settingId;
            this.valueGetter = valueGetter;
        }
        getValue() {
            return this.valueGetter(this.settingId);
        }
    }
    class VariableReference extends ValueDescriptor {
        constructor(name, valueGetter) {
            super();
            this.name = name;
            this.valueGetter = valueGetter;
        }
        getValue() {
            return this.valueGetter(this.name);
        }
    }
    class FunctionCall extends ValueDescriptor {
        constructor(args, callback) {
            super();
            this.args = args;
            this.callback = callback;
        }
        getValue() {
            return this.callback(this.resolveArgs(this.args));
        }
        resolveArgs(args) {
            if (Array.isArray(args)) {
                return args.map(arg => arg.getValue());
            }
            return Object.keys(args).reduce((result, key) => {
                result[key] = args[key].getValue();
                return result;
            }, {});
        }
    }
    //endregion
    function isEmptyCssValue(value) {
        return (typeof value === 'undefined') || (value === '') || (value === null);
    }
    function convertToRgba(color, opacity = 1.0) {
        color = color.trim();
        if (color === '') {
            return 'transparent';
        }
        //Strip the leading hash, if any.
        if (color[0] === '#') {
            color = color.substring(1);
        }
        //If the color is in the shorthand format, expand it.
        if (color.length === 3) {
            color = color[0] + color[0] + color[1] + color[1] + color[2] + color[2];
        }
        //The color should now be in the full 6-digit format. Convert it to RGBA.
        if (color.length === 6) {
            const red = parseInt(color.substring(0, 2), 16);
            const green = parseInt(color.substring(2, 4), 16);
            const blue = parseInt(color.substring(4, 6), 16);
            return `rgba(${red}, ${green}, ${blue}, ${opacity})`;
        }
        //The color may be invalid, or it's not in a hex format we recognize.
        return color;
    }
    function uniqueArrayValues(array) {
        return array.filter((value, index) => array.indexOf(value) === index);
    }
    function constrain(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }
    function modifyHexColorAsHsl(args, operation) {
        const color = args.color || '';
        if (isEmptyCssValue(color)) {
            return '';
        }
        const hue = args.hue || null;
        const saturation = args.saturation || null;
        const lightness = args.lightness || null;
        if ((hue === null) && (saturation === null) && (lightness === null)) {
            return color;
        }
        let output = $.Color(color);
        output = operation(output, hue, saturation, lightness);
        return output.toHexString();
    }
    // noinspection JSUnusedGlobalSymbols -- Used dynamically by declaration generators received from the server.
    const builtinFunctions = {
        simpleProperty: function (args) {
            if (isEmptyCssValue(args.value)) {
                return [];
            }
            return [args.name + ': ' + args.value + ';'];
        },
        formatLength: function (args) {
            if (isEmptyCssValue(args.value)) {
                return '';
            }
            //Normalize numeric values. For example, while JS accepts "1." as a number,
            //"1.px" is not a valid CSS length value, so it should be converted to "1px".
            const numericValue = parseFloat(String(args.value));
            if (isNaN(numericValue)) {
                return '';
            }
            return '' + numericValue + (args.unit || '');
        },
        shadow: function (args) {
            const mode = args.mode || 'default';
            const color = args.color || '';
            if (mode === 'default') {
                return [];
            }
            if ((mode === 'none') || (color === '') || (color === null) || (color === 'transparent')) {
                return ['box-shadow: none;'];
            }
            if (mode !== 'custom') {
                return [];
            }
            const components = [];
            if (args.inset) {
                components.push('inset');
            }
            const horizontal = args['offset-x'] || 0;
            const vertical = args['offset-y'] || 0;
            const blur = args.blur || 0;
            const spread = args.spread || 0;
            components.push(`${horizontal}px ${vertical}px ${blur}px ${spread}px`);
            const colorOpacity = args.colorOpacity || 1.0;
            if (colorOpacity < 1.0) {
                components.push(convertToRgba(color, colorOpacity));
            }
            else {
                components.push(color);
            }
            return [`box-shadow: ${components.join(' ')};`];
        },
        boxSides: function (args) {
            if (typeof args.cssPropertyPrefix !== 'string') {
                throw new Error('Invalid config for the boxSides generator: missing cssPropertyPrefix');
            }
            const compositeValue = args.value || {};
            const unit = compositeValue.unit || '';
            const declarations = [];
            for (const side of ['top', 'right', 'bottom', 'left']) {
                const value = compositeValue[side];
                if (isEmptyCssValue(value)) {
                    continue;
                }
                const property = args.cssPropertyPrefix + side;
                declarations.push(`${property}: ${value}${unit};`);
            }
            return declarations;
        },
        firstNonEmpty(args) {
            for (const arg of args) {
                if (!isEmptyCssValue(arg)) {
                    return arg;
                }
            }
            return null;
        },
        /**
         * Take a HEX color, convert it to HSL to edit its components,
         * then convert back to HEX.
         *
         * @param args
         */
        editHexAsHsl: function (args) {
            return modifyHexColorAsHsl(args, (color, hue, saturation, lightness) => {
                if (hue !== null) {
                    color = color.hue(hue);
                }
                if (saturation !== null) {
                    color = color.saturation(saturation);
                }
                if (lightness !== null) {
                    color = color.lightness(lightness);
                }
                return color;
            });
        },
        adjustHexAsHsl: function (args) {
            return modifyHexColorAsHsl(args, (color, hue, saturation, lightness) => {
                if (hue !== null) {
                    color = color.hue(constrain(color.hue() + hue, 0, 360));
                }
                if (saturation !== null) {
                    color = color.saturation(constrain(color.saturation() + saturation, 0, 1.0));
                }
                if (lightness !== null) {
                    color = color.lightness(constrain(color.lightness() + lightness, 0, 1.0));
                }
                return color;
            });
        },
        mixColors: function (args) {
            const color1 = args.color1 || '';
            const color2 = args.color2 || '';
            if (isEmptyCssValue(color1) || isEmptyCssValue(color2)) {
                return '';
            }
            const weight = args.weight || 50;
            if (weight <= 0) {
                return color2;
            }
            else if (weight >= 100) {
                return color1;
            }
            return $.Color(color2).transition($.Color(color1), weight / 100).toHexString();
        },
        changeLightness: function (args) {
            const color = args.color || '';
            if (isEmptyCssValue(color)) {
                return '';
            }
            const amount = args.amount || 0;
            if (amount === 0) {
                return color;
            }
            let output = $.Color(color);
            //Amount is a number between 0 and 100, while lightness is between 0.0 and 1.0.
            let newLightness = output.lightness() + (amount / 100);
            //Clamp to 0.0 - 1.0.
            newLightness = constrain(newLightness, 0.0, 1.0);
            return output.lightness(newLightness).toHexString();
        },
        darken: function (args) {
            const color = args.color || '';
            const amount = args.amount || 0;
            return builtinFunctions.changeLightness({ color, amount: -Math.abs(amount) });
        },
        lighten: function (args) {
            const color = args.color || '';
            const amount = args.amount || 0;
            return builtinFunctions.changeLightness({ color, amount: Math.abs(amount) });
        },
        compare: function (args) {
            const value1 = args.value1;
            const value2 = args.value2;
            const operator = args.op;
            const thenResult = (typeof args.thenResult !== 'undefined') ? args.thenResult : true;
            const elseResult = (typeof args.elseResult !== 'undefined') ? args.elseResult : null;
            let result;
            switch (operator) {
                case '==':
                    result = value1 == value2;
                    break;
                case '!=':
                    result = value1 != value2;
                    break;
                case '>':
                    result = value1 > value2;
                    break;
                case '>=':
                    result = value1 >= value2;
                    break;
                case '<':
                    result = value1 < value2;
                    break;
                case '<=':
                    result = value1 <= value2;
                    break;
                case 'in':
                    result = Array.isArray(value2) && value2.includes(value1);
                    break;
                default:
                    throw new Error(`Unknown operator: ${operator}`);
            }
            return result ? thenResult : elseResult;
        },
        not: function (args) {
            return !args.value;
        },
        ifTruthy: function (args) {
            const value = args.value;
            const thenResult = (typeof args.thenResult !== 'undefined') ? args.thenResult : true;
            const elseResult = (typeof args.elseResult !== 'undefined') ? args.elseResult : null;
            return value ? thenResult : elseResult;
        },
        ifSome: function (args) {
            const values = args.values;
            const thenResult = args.thenResult;
            const elseResult = (typeof args.elseResult !== 'undefined') ? args.elseResult : null;
            for (const value of values) {
                if (!!value) {
                    return thenResult;
                }
            }
            return elseResult;
        },
        ifAll: function (args) {
            const values = args.values;
            const thenResult = args.thenResult;
            const elseResult = args.elseResult !== undefined ? args.elseResult : null;
            if (!values || (values.length === 0)) {
                return elseResult;
            }
            for (const value of values) {
                if (!value) {
                    return elseResult;
                }
            }
            return thenResult;
        },
        ifImageSettingContainsImage: function (args) {
            const thenResult = args.thenResult !== undefined ? args.thenResult : true;
            const elseResult = args.elseResult !== undefined ? args.elseResult : null;
            if ((typeof args.value !== 'object') || !args.value) {
                return elseResult;
            }
            const image = args.value;
            const hasAttachment = !!image.attachmentId;
            const hasExternalUrl = !!image.externalUrl;
            const hasImage = hasAttachment || hasExternalUrl;
            return hasImage ? thenResult : elseResult;
        },
        div: function (args) {
            //For length settings, the values can be strings like "10px". Let's extract the numeric
            //part for calculations.
            const numerator = (typeof args.numerator === 'string') ? parseFloat(args.numerator) : args.numerator;
            const denominator = (typeof args.denominator === 'string') ? parseFloat(args.denominator) : args.denominator;
            if (denominator === 0) {
                return Number.NaN;
            }
            return numerator / denominator;
        },
        ceil: function (args) {
            return Math.ceil(args.value);
        }
    };
    let Preview;
    (function (Preview) {
        const $ = jQuery;
        const inactiveSettingMarker = { '_ame_inactive_setting': true };
        class PreviewSession {
            constructor(config) {
                this.settings = {};
                this.valueReaders = new Set();
                this.notFound = {};
                this.variables = {};
                this.styleBlocks = [];
                this.stylesheetsToDisable = [];
                this.stylesheetWasEnabled = {};
                /**
                 * Whether this is the first time the preview is being updated.
                 * This is set to false after preview() is called for the first time.
                 */
                this._isBeforeFirstUpdate = true;
                //Optimization: Create bound getters once instead of every time we need
                //to create a setting or variable reference.
                this.settingValueGetter = this.getSettingPreviewValue.bind(this);
                this.variableValueGetter = (variableName) => {
                    if (variableName in this.variables) {
                        return this.variables[variableName].getValue();
                    }
                    return null;
                };
                //Optionally, disable already generated custom stylesheets while the preview
                //is active to prevent old settings from interfering with the preview of new settings.
                if (Array.isArray(config.stylesheetsToDisable)) {
                    this.stylesheetsToDisable = config.stylesheetsToDisable;
                }
                //Variables
                for (const variableName in config.variables) {
                    if (!config.variables.hasOwnProperty(variableName)) {
                        continue;
                    }
                    this.variables[variableName] = this.createValueDescriptor(config.variables[variableName], true);
                }
                //CSS statement groups
                for (const statementConfig of config.statements) {
                    const statement = this.createCssStatement(statementConfig);
                    this.styleBlocks.push(new PreviewStyleBlock([statement]));
                }
            }
            createValueDescriptor(data, allowUnknownVariables = false) {
                switch (data.t) {
                    case 'constant':
                        return new ConstantValue(data.value);
                    case 'array':
                        return new ArrayValue(data.items.map((valueData) => this.createValueDescriptor(valueData, allowUnknownVariables)));
                    case 'setting':
                        this.registerPreviewableSettingId(data.id);
                        return new SettingReference(data.id, this.settingValueGetter);
                    case 'var':
                        if (!this.variables.hasOwnProperty(data.name) && !allowUnknownVariables) {
                            throw new Error('Unknown variable: ' + data.name);
                        }
                        return new VariableReference(data.name, this.variableValueGetter);
                    case 'funcCall':
                        let functionName;
                        if (data.name in builtinFunctions) {
                            functionName = data.name;
                        }
                        else {
                            throw new Error('Unknown function: ' + data.name);
                        }
                        const func = builtinFunctions[functionName];
                        //Initialize the function arguments.
                        let args;
                        if (Array.isArray(data.args)) {
                            args = data.args.map(arg => this.createValueDescriptor(arg, allowUnknownVariables));
                        }
                        else {
                            args = {};
                            for (const argName in data.args) {
                                if (!data.args.hasOwnProperty(argName)) {
                                    continue;
                                }
                                args[argName] = this.createValueDescriptor(data.args[argName], allowUnknownVariables);
                            }
                        }
                        // @ts-ignore - Can't really statically check this since the values come from the server.
                        return new FunctionCall(args, func);
                }
            }
            /**
             * Get the IDs of all settings that are referenced by the given descriptor.
             *
             * @param descriptor
             * @private
             */
            getSettingIdsUsedBy(descriptor) {
                if (descriptor instanceof SettingReference) {
                    return [descriptor.settingId];
                }
                if (descriptor instanceof ArrayValue) {
                    let result = [];
                    for (const item of descriptor.getItemDescriptors()) {
                        result = result.concat(this.getSettingIdsUsedBy(item));
                    }
                    return uniqueArrayValues(result);
                }
                if (descriptor instanceof FunctionCall) {
                    let result = [];
                    const args = descriptor.args;
                    if (Array.isArray(args)) {
                        for (const arg of args) {
                            result = result.concat(this.getSettingIdsUsedBy(arg));
                        }
                    }
                    else {
                        for (const argName in args) {
                            if (args.hasOwnProperty(argName)) {
                                result = result.concat(this.getSettingIdsUsedBy(args[argName]));
                            }
                        }
                    }
                    return uniqueArrayValues(result);
                }
                if (descriptor instanceof VariableReference) {
                    const varDef = this.getVariableDefinition(descriptor.name);
                    if (varDef === null) {
                        return [];
                    }
                    return this.getSettingIdsUsedBy(varDef);
                }
                return [];
            }
            getVariableDefinition(variableName) {
                if (!this.variables.hasOwnProperty(variableName)) {
                    return null;
                }
                return this.variables[variableName];
            }
            createItemsFromConfigs(configs, combinedParentSelectors = []) {
                let generators = [];
                let statements = [];
                for (const config of configs) {
                    if (isValueDescriptorData(config)) {
                        generators.push(this.makeDeclarationGeneratorWrapper(config));
                    }
                    else {
                        statements.push(this.createCssStatement(config, combinedParentSelectors));
                    }
                }
                return { generators, statements };
            }
            createCssStatement(config, combinedParentSelectors = []) {
                const selectors = (config.t === 'ruleset')
                    ? CssRuleSet.combineSelectors(config.selectors, combinedParentSelectors)
                    : combinedParentSelectors;
                const children = config.children
                    ? this.createItemsFromConfigs(config.children, selectors)
                    : { generators: [], statements: [] };
                switch (config.t) {
                    case 'ruleset':
                        return new CssRuleSet(selectors, children);
                    case 'conditionalAtRule':
                        return new ConditionalAtRule(config.identifier, config.condition, children);
                    case 'ifStatement':
                        const condition = this.createValueDescriptor(config.condition, true);
                        const usedSettingIds = this.getSettingIdsUsedBy(condition);
                        const conditionCallback = () => {
                            //For performance, conditions that reference settings should
                            //only be checked when at least one setting is active.
                            if (usedSettingIds.length > 0) {
                                if (!usedSettingIds.some((id) => this.isSettingActive(id))) {
                                    return false;
                                }
                            }
                            const isTruthy = condition.getValue();
                            return !!isTruthy; //Convert to boolean.
                        };
                        return new IfStatement(conditionCallback, children);
                }
            }
            getPreviewableSettingIDs() {
                return Object.keys(this.settings);
            }
            preview(settingId, value, otherSettingReader) {
                if (this._isBeforeFirstUpdate) {
                    this._isBeforeFirstUpdate = false;
                    this.disableAssociatedStylesheets();
                }
                this.valueReaders.add(otherSettingReader);
                if (!this.settings.hasOwnProperty(settingId)) {
                    this.settings[settingId] = ko.observable(value);
                }
                else {
                    this.settings[settingId](value);
                }
            }
            dispose() {
                //Dispose of all style blocks.
                for (const block of this.styleBlocks) {
                    block.dispose();
                }
                this.reEnableAssociatedStylesheets();
            }
            disableAssociatedStylesheets() {
                for (const stylesheetSelector of this.stylesheetsToDisable) {
                    const $link = $(stylesheetSelector);
                    if ($link.length > 0) {
                        this.stylesheetWasEnabled[stylesheetSelector] = $link.prop('disabled');
                        $link.prop('disabled', true);
                    }
                }
            }
            reEnableAssociatedStylesheets() {
                for (const stylesheetSelector of this.stylesheetsToDisable) {
                    const $link = $(stylesheetSelector);
                    if (($link.length > 0) && this.stylesheetWasEnabled.hasOwnProperty(stylesheetSelector)) {
                        $link.prop('disabled', this.stylesheetWasEnabled[stylesheetSelector]);
                    }
                }
            }
            isSettingActive(settingId) {
                if (this.settings.hasOwnProperty(settingId)) {
                    return this.settings[settingId]() !== inactiveSettingMarker;
                }
                return false;
            }
            getSettingPreviewValue(settingId) {
                if (!this.settings.hasOwnProperty(settingId)) {
                    const value = this.getSettingFromReaders(settingId);
                    this.settings[settingId] = ko.observable(value).extend({ deferred: true });
                }
                const observable = this.settings[settingId];
                let value = observable();
                if (value === inactiveSettingMarker) {
                    value = this.getSettingFromReaders(settingId);
                    observable(value);
                }
                return value;
            }
            getSettingFromReaders(settingId) {
                for (const reader of this.valueReaders) {
                    const value = reader(settingId, this.notFound);
                    if (value !== this.notFound) {
                        return value;
                    }
                }
                throw new Error('Setting not found for preview: ' + settingId);
            }
            makeDeclarationGeneratorWrapper(config) {
                const generator = this.createValueDescriptor(config);
                return new DeclarationGeneratorWrapper(generator, this);
            }
            registerPreviewableSettingId(settingId) {
                if (!this.settings.hasOwnProperty(settingId)) {
                    this.settings[settingId] = ko.observable(inactiveSettingMarker);
                }
            }
            get isBeforeFirstUpdate() {
                return this._isBeforeFirstUpdate;
            }
        }
        /**
         * Preview manager for the style generator.
         *
         * This is a thin wrapper around the PreviewSession class. It initializes the session
         * as needed and destroys it when the preview is cleared. This makes it simpler to manage
         * active settings, style blocks, and CSS rule-sets: instead of having to carefully
         * track dependencies and deactivate/reactivate them in the right order whenever the preview
         * is disabled/enabled, we can just destroy the session and start over.
         */
        class StyleGeneratorPreview {
            constructor(config) {
                this.config = config;
                this.currentSession = null;
            }
            getOrCreateSession() {
                if (this.currentSession === null) {
                    this.currentSession = new PreviewSession(this.config);
                }
                return this.currentSession;
            }
            getPreviewableSettingIDs() {
                return this.getOrCreateSession().getPreviewableSettingIDs();
            }
            preview(settingId, value, otherSettingReader) {
                const session = this.getOrCreateSession();
                const shouldPreviewAll = (this.config.previewAllOnFirstUpdate && session.isBeforeFirstUpdate);
                session.preview(settingId, value, otherSettingReader);
                if (shouldPreviewAll) {
                    //Preview all registered settings the first time the preview is updated.
                    const notFound = {};
                    for (const otherId of session.getPreviewableSettingIDs()) {
                        const otherValue = otherSettingReader(otherId, notFound);
                        if ((otherId !== settingId) && (otherValue !== notFound)) {
                            session.preview(otherId, otherValue, otherSettingReader);
                        }
                    }
                }
            }
            clearPreview() {
                if (this.currentSession !== null) {
                    this.currentSession.dispose();
                    this.currentSession = null;
                }
            }
        }
        Preview.StyleGeneratorPreview = StyleGeneratorPreview;
        class DeclarationGeneratorWrapper {
            constructor(generator, settingSource) {
                this.generator = generator;
                this.settingSource = settingSource;
                //Introspect the generator and see which settings it uses.
                //This will be useful to determine if the generator is active.
                this.usedSettingIds = DeclarationGeneratorWrapper.findReferencedSettingIds(generator, settingSource);
                this.cssDeclarations = ko.computed({
                    read: () => this.getDeclarations(),
                    deferEvaluation: true,
                }).extend({ deferred: true });
            }
            /**
             * Recursively find all settings used by a value descriptor (such as a function call).
             *
             * @param {ValueDescriptor} thing
             * @param variableSource Needed to get variable definitions and not just the final values.
             */
            static findReferencedSettingIds(thing, variableSource) {
                let settingIds = [];
                if (thing instanceof SettingReference) {
                    settingIds.push(thing.settingId);
                }
                else if (thing instanceof FunctionCall) {
                    if (Array.isArray(thing.args)) {
                        for (const arg of thing.args) {
                            settingIds = settingIds.concat(DeclarationGeneratorWrapper.findReferencedSettingIds(arg, variableSource));
                        }
                    }
                    else {
                        for (const key in thing.args) {
                            settingIds = settingIds.concat(DeclarationGeneratorWrapper.findReferencedSettingIds(thing.args[key], variableSource));
                        }
                    }
                }
                else if (thing instanceof VariableReference) {
                    const value = variableSource.getVariableDefinition(thing.name);
                    if (value !== null) {
                        settingIds = settingIds.concat(DeclarationGeneratorWrapper.findReferencedSettingIds(value, variableSource));
                    }
                }
                return settingIds;
            }
            isActive() {
                //Check if any of the input settings are active.
                let hasSettingLookups = false;
                for (const settingId of this.usedSettingIds) {
                    hasSettingLookups = true;
                    if (this.settingSource.isSettingActive(settingId)) {
                        return true;
                    }
                }
                //If there are no input settings, the generator is always active: it just
                //generates a fixed declaration.
                return !hasSettingLookups;
            }
            getDeclarations() {
                return this.generator.getValue();
            }
            dispose() {
                this.cssDeclarations.dispose();
            }
        }
        class StatementEvaluationResult {
            constructor(declarations, compiledStatements) {
                this.declarations = declarations;
                this.compiledStatements = compiledStatements;
            }
            formatDeclarations(indentLevel = 1) {
                if (this.declarations.length < 1) {
                    return '';
                }
                const indent = '\t'.repeat(indentLevel);
                return this.declarations.map(decl => indent + decl).join('\n');
            }
            formatAll(indentLevel) {
                if (this.isEmpty) {
                    return '';
                }
                let output = this.formatDeclarations(indentLevel + 1);
                if (output !== '') {
                    output += '\n';
                }
                const indent = '\t'.repeat(indentLevel);
                for (const statementCss of this.compiledStatements) {
                    if (statementCss !== '') {
                        output += indent + statementCss + '\n';
                    }
                }
                return output;
            }
            get isEmpty() {
                return (this.declarations.length === 0) && (this.compiledStatements.length === 0);
            }
            static createEmpty() {
                return new StatementEvaluationResult([], []);
            }
        }
        class CssStatement {
            constructor(children) {
                this.affectsOutputNesting = false;
                this.declarationSources = children.generators;
                this.nestedStatements = children.statements;
                this.cssText = ko.computed({
                    read: () => this.generateCss(),
                    deferEvaluation: true,
                }).extend({ deferred: true });
            }
            dispose() {
                //Dispose declaration sources.
                for (const source of this.declarationSources) {
                    source.dispose();
                }
                //Dispose nested statements.
                for (const statement of this.nestedStatements) {
                    statement.dispose();
                }
                //Dispose the CSS text observable.
                this.cssText.dispose();
            }
            evaluate(indentLevel = 0) {
                const declarations = this.getOwnDeclarations();
                const compiledStatements = [];
                const childIndentLevel = this.affectsOutputNesting ? (indentLevel + 1) : indentLevel;
                for (const statement of this.nestedStatements) {
                    if (statement.isActive()) {
                        const result = statement.evaluate(childIndentLevel);
                        declarations.push(...result.declarations);
                        compiledStatements.push(...result.compiledStatements);
                    }
                }
                return new StatementEvaluationResult(declarations, compiledStatements);
            }
            getOwnDeclarations() {
                const declarations = [];
                for (const source of this.declarationSources) {
                    if (source.isActive()) {
                        declarations.push(...source.cssDeclarations());
                    }
                }
                return declarations;
            }
            generateCss() {
                return this.evaluate().formatAll(0);
            }
            isActive() {
                for (const source of this.declarationSources) {
                    if (source.isActive()) {
                        return true;
                    }
                }
                for (const statement of this.nestedStatements) {
                    if (statement.isActive()) {
                        return true;
                    }
                }
                return false;
            }
        }
        class CssRuleSet extends CssStatement {
            constructor(combinedSelectors, deserializedChildren) {
                if (combinedSelectors.length < 1) {
                    throw {
                        error: 'Invalid config for CssRuleSet: at least one selector is required.',
                        children: deserializedChildren
                    };
                }
                super(deserializedChildren);
                this.effectiveSelectors = combinedSelectors;
                //this.effectiveSelectors = CssRuleSet.combineSelectors(selectors, parent.effectiveSelectors);
                this.selectorText = this.effectiveSelectors.join(', ');
            }
            evaluate(indentLevel = 0) {
                const innerResult = super.evaluate(indentLevel);
                if (innerResult.isEmpty) {
                    return innerResult;
                }
                const compiledStatements = innerResult.compiledStatements;
                if (innerResult.declarations.length > 0) {
                    const myIndent = '\t'.repeat(indentLevel);
                    const ownStatement = myIndent + this.selectorText + '{\n'
                        + innerResult.formatDeclarations(indentLevel + 1) + '\n'
                        + myIndent + '}';
                    compiledStatements.unshift(ownStatement);
                }
                return new StatementEvaluationResult([], compiledStatements);
            }
            static combineSelectors(selectors, parentSelectors) {
                if (parentSelectors.length < 1) {
                    return selectors;
                }
                const combinedSelectors = [];
                for (const selector of selectors) {
                    if (selector === '') {
                        continue;
                    }
                    if (selector.includes('&')) {
                        //Insert the parent selectors into the current selector at the position of the "&".
                        for (const parentSelector of parentSelectors) {
                            combinedSelectors.push(selector.replace('&', parentSelector.trim()));
                        }
                    }
                    else {
                        //Just append the current selector to the parent selectors.
                        for (const parentSelector of parentSelectors) {
                            combinedSelectors.push(`${parentSelector} ${selector}`);
                        }
                    }
                }
                return combinedSelectors;
            }
        }
        class ConditionalAtRule extends CssStatement {
            constructor(identifier, condition, children) {
                super(children);
                this.identifier = identifier;
                this.condition = condition;
                this.affectsOutputNesting = true;
            }
            evaluate(indentLevel = 0) {
                const innerResult = super.evaluate(indentLevel);
                if (innerResult.isEmpty) {
                    return innerResult;
                }
                //Put everything inside a new compiled statement for this at-rule.
                const ownStatement = '\t'.repeat(indentLevel) + this.getAtRuleText()
                    + ' {\n' + innerResult.formatAll(indentLevel + 1) + '\n}';
                return new StatementEvaluationResult([], [ownStatement]);
            }
            getAtRuleText() {
                return '@' + this.identifier + ' ' + this.condition;
            }
        }
        class IfStatement extends CssStatement {
            constructor(condition, children) {
                super(children);
                this.condition = condition;
            }
            evaluate(indentLevel = 0) {
                if (!this.condition()) {
                    return StatementEvaluationResult.createEmpty();
                }
                return super.evaluate(indentLevel);
            }
        }
        class PreviewStyleBlock {
            constructor(statements, condition = null) {
                this.statements = statements;
                this.$styleElement = null;
                this.cssText = ko.computed({
                    read: () => {
                        if ((condition !== null) && !condition()) {
                            return '';
                        }
                        let pieces = [];
                        for (const statement of this.statements) {
                            if (statement.isActive()) {
                                const css = statement.cssText();
                                if (css !== '') {
                                    pieces.push(css);
                                }
                            }
                        }
                        if (pieces.length === 0) {
                            return '';
                        }
                        return pieces.join('\n');
                    },
                    deferEvaluation: true,
                }).extend({ deferred: true });
                this.updateStyleElement(this.cssText());
                this.cssChangeSubscription = this.cssText.subscribe((cssText) => {
                    this.updateStyleElement(cssText);
                });
            }
            updateStyleElement(cssText) {
                if (cssText === '') {
                    if (this.$styleElement) {
                        this.$styleElement.remove();
                        this.$styleElement = null;
                    }
                    return;
                }
                if (!this.$styleElement) {
                    this.$styleElement = $('<style></style>').appendTo('head');
                }
                this.$styleElement.text(cssText);
            }
            clear() {
                if (this.$styleElement) {
                    this.$styleElement.remove();
                    this.$styleElement = null;
                }
            }
            dispose() {
                //Stop listening for CSS changes.
                this.cssChangeSubscription.dispose();
                this.cssText.dispose();
                //Dispose rule sets.
                for (const ruleset of this.statements) {
                    ruleset.dispose();
                }
                //Remove the style element.
                this.clear();
            }
        }
    })(Preview = AmeStyleGenerator.Preview || (AmeStyleGenerator.Preview = {}));
})(AmeStyleGenerator || (AmeStyleGenerator = {}));
//# sourceMappingURL=style-generator.js.map

/***/ }

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["customizable"], () => (__webpack_exec__("./extras/modules/admin-customizer/preview-handler.ts")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=admin-customizer-preview.bundle.js.map