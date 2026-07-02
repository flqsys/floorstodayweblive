<script>
	import {setContext} from "svelte";
	import {settingsLocked} from "../js/stores";
	import {tools} from "./stores";
	import Page from "../components/Page.svelte";
	import Notifications from "../components/Notifications.svelte";
	import ToolNotification from "./ToolNotification.svelte";
	import BackNextButtonsRow from "../components/BackNextButtonsRow.svelte";
	import Panel from "../components/Panel.svelte";
	import PanelRow from "../components/PanelRow.svelte";

	/**
	 * @typedef {Object} Props
	 * @property {string} [name]
	 * @property {function} [onRouteEvent]
	 */

	/** @type {Props} */
	let { name = "move-public-objects", onRouteEvent } = $props();

	// Let all child components know if settings are currently locked.
	setContext( "settingsLocked", settingsLocked );

	const tool = $tools.move_public_objects;

	/**
	 * Handles a Skip button click.
	 *
	 * @return {Promise<void>}
	 */
	async function handleSkip() {
		onRouteEvent( { event: "next", default: "/" } );
	}

	/**
	 * Handles a Next button click.
	 *
	 * @return {Promise<void>}
	 */
	async function handleNext() {
		await tools.start( tool );
		onRouteEvent( { event: "next", default: "/" } );
	}
</script>

<Page {name} subpage {onRouteEvent}>
	<Notifications tab="media" component={ToolNotification}/>

	<Panel
		heading={tool.name}
		helpURL={tool.doc_url}
		helpDesc={tool.doc_desc}
		multi
	>
		<PanelRow class="body flex-column">
			<p>{@html tool.prompt}</p>
		</PanelRow>
	</Panel>

	<BackNextButtonsRow
		onSkip={handleSkip}
		onNext={handleNext}
		nextText={tool.button}
		skipVisible={true}
		nextDisabled={$settingsLocked}
	/>
</Page>
