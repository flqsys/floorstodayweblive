<script>
	import {link} from "svelte-spa-router";
	import {strings} from "../js/stores";
	import {licence} from "./stores";
	import SupportPage from "../components/SupportPage.svelte";
	import DocumentationSidebar from "./DocumentationSidebar.svelte";
	import SupportForm from "./SupportForm.svelte";
	import Notification from "../components/Notification.svelte";

	/**
	 * @typedef {Object} Props
	 * @property {string} [name]
	 * @property {function} [onRouteEvent]
	 */

	/** @type {Props} */
	let { name = "support", onRouteEvent } = $props();

	/**
	 * Potentially returns an error message detailing a problem with the currently set license key.
	 *
	 * @param {Object} licence
	 *
	 * @return {string}
	 */
	function getLicenceError( licence ) {
		// If there are any errors, just return the first (there's usually only 1 anyway).
		if ( licence.hasOwnProperty( "errors" ) && Object.values( licence.errors ).length > 0 ) {
			return Object.values( licence.errors )[ 0 ];
		}

		return "";
	}

	let licenceError = $derived( getLicenceError( $licence ) );
</script>

{#if $licence.is_set}
	{#if $licence.is_valid && licenceError.length === 0}
		<SupportPage {name} title={$strings.email_support_title} {onRouteEvent}>
			{#snippet header()}
				<p class="licence-type">{@html $licence.your_active_licence}</p>
			{/snippet}

			{#snippet content()}
				<SupportForm/>
			{/snippet}

			{#snippet footer()}
				<DocumentationSidebar/>
			{/snippet}
		</SupportPage>
	{:else}
		<SupportPage {name} title={$strings.email_support_title} {onRouteEvent}>
			{#snippet content()}
				<Notification warning inline>
					<p>
						{@html licenceError}
					</p>
				</Notification>
			{/snippet}

			{#snippet footer()}
				<DocumentationSidebar/>
			{/snippet}
		</SupportPage>
	{/if}
{:else}
	<SupportPage {name} title={$strings.email_support_title} {onRouteEvent}>
		{#snippet content()}
			<Notification warning inline>
				<p>
					{$strings.licence_not_entered}
					<a href="/license" use:link>
						{$strings.please_enter_licence}
					</a>
				</p>
				<p>{$strings.once_licence_entered}</p>
			</Notification>
		{/snippet}

		{#snippet footer()}
			<DocumentationSidebar/>
		{/snippet}
	</SupportPage>
{/if}
