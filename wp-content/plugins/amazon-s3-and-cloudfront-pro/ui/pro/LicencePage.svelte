<script>
	import {api, config, settings, strings} from "../js/stores";
	import {autofocus} from "../js/autofocus";
	import {licence} from "./stores";
	import Page from "../components/Page.svelte";
	import Notifications from "../components/Notifications.svelte";
	import Button from "../components/Button.svelte";
	import DefinedInWPConfig from "../components/DefinedInWPConfig.svelte";

	/**
	 * @typedef {Object} Props
	 * @property {string} [name]
	 * @property {function} [onRouteEvent]
	 */

	/** @type {Props} */
	let { name = "licence", onRouteEvent } = $props();

	let value = $state( "" );

	/**
	 * Handles an "Activate License" button click.
	 *
	 * @param {Object} event
	 *
	 * @return {Promise<void>}
	 */
	async function handleActivateLicence( event ) {
		const result = await api.post( "licences", { licence: value } );

		await updateLicenceInfo( result );
	}

	/**
	 * Handles a "Remove License" button click.
	 *
	 * @param {Object} event
	 *
	 * @return {Promise<void>}
	 */
	async function handleRemoveLicence( event ) {
		value = "";
		const result = await api.delete( "licences" );

		await updateLicenceInfo( result );
	}

	/**
	 * Update licence store with results of API call.
	 *
	 * @param {Object} response
	 *
	 * @return {Promise<void>}
	 */
	async function updateLicenceInfo( response ) {
		if ( response.hasOwnProperty( "licences" ) ) {
			config.update( currentConfig => {
				return {
					...currentConfig,
					licences: response.licences
				};
			} );
		}

		// Regardless of what just happened, make sure our settings are in sync (includes reference to license).
		await settings.fetch();
	}
</script>

<Page {name} {onRouteEvent}>
	<Notifications tab={name}/>
	<h2 class="page-title">{$strings.licence_title}</h2>

	<div class="licence-page wrapper" class:defined={$licence.is_set && $licence.is_defined}>
		{#if $licence.is_set}
			<label for="licence-key" class="screen-reader-text">{$strings.licence_title}</label>
			<input
				id="licence-key"
				type="text"
				class="licence-field disabled"
				name="licence"
				value={$licence.masked_licence}
				disabled
			>
			{#if $licence.is_defined}
				<DefinedInWPConfig defined/>
			{:else}
				<Button large outline onclick={handleRemoveLicence}>{$strings.remove_licence}</Button>
			{/if}
		{:else}
			<label for="enter-licence-key" class="screen-reader-text">{$strings.enter_licence_key}</label>
			<input
				id="enter-licence-key"
				type="text"
				class="licence-field"
				name="licence"
				minlength="4"
				placeholder={$strings.enter_licence_key}
				bind:value
				use:autofocus
			>
			<Button large primary onclick={handleActivateLicence} disabled={value.length === 0}>
				{$strings.activate_licence}
			</Button>
		{/if}
	</div>
</Page>
