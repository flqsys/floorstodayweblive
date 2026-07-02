<script>
	import {setContext} from "svelte";
	import {strings} from "../js/stores";
	import {tools, toolsLocked} from "./stores";
	import Page from "../components/Page.svelte";
	import Notifications from "../components/Notifications.svelte";
	import ToolNotification from "./ToolNotification.svelte";
	import ToolPanel from "./ToolPanel.svelte";
	import NoTools from "./NoTools.svelte";

	/**
	 * @typedef {Object} Props
	 * @property {string} [name]
	 * @property {function} [onRouteEvent]
	 */

	/** @type {Props} */
	let { name = "tools", onRouteEvent } = $props();

	// Let all child components know if tools are currently locked.
	// All panels etc respond to settingsLocked, so we fake it here as we're not in a settings context.
	setContext( "settingsLocked", toolsLocked );
</script>

<Page {name} {onRouteEvent}>
	<Notifications tab={name} component={ToolNotification}/>
	<h2 class="page-title">{$strings.tools_title}</h2>

	<div class="tools-page wrapper">
		{#each Object.values( $tools ).filter( ( tool ) => tool.render ) as tool (tool.id)}
			<ToolPanel {tool}/>
		{:else}
			<NoTools/>
		{/each}
	</div>
</Page>
