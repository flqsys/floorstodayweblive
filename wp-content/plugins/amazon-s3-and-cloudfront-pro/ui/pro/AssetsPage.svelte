<script>
	import {
		assetsSettings,
		assetsSettingsChanged,
		assetsSettingsLocked,
		currentAssetsSettings,
		enableAssets
	} from "./stores";
	import Page from "../components/Page.svelte";
	import Notifications from "../components/Notifications.svelte";
	import AssetsSettings from "./AssetsSettings.svelte";
	import AssetsUpgrade from "./AssetsUpgrade.svelte";
	import Footer from "../components/Footer.svelte";
	import {setContext} from "svelte";

	/**
	 * @typedef {Object} Props
	 * @property {string} [name]
	 * @property {function} [onRouteEvent]
	 */

	/** @type {Props} */
	let { name = "assets", onRouteEvent } = $props();

	// Let all child components know if settings are currently locked.
	setContext( "settingsLocked", assetsSettingsLocked );
</script>

<Page {name} {onRouteEvent} initialSettings={currentAssetsSettings}>
	<Notifications tab={name}/>
	<div class="assets-page wrapper">
		{#if $enableAssets}
			<AssetsSettings/>
		{:else}
			<AssetsUpgrade/>
		{/if}
	</div>
</Page>

<Footer settingsStore={assetsSettings} settingsChangedStore={assetsSettingsChanged} {onRouteEvent}/>
