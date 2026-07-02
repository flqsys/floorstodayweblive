<script>
	import {tools} from "./stores";
	import {strings} from "../js/stores";
	import Button from "../components/Button.svelte";

	/**
	 * @typedef {Object} Props
	 * @property {any} [tool]
	 * @property {boolean} [disabled]
	 * @property {boolean} [small]
	 */

	/** @type {Props} */
	let { tool = {}, disabled = false, small = false } = $props();

	/**
	 * Handles a Pause or Resume button click.
	 */
	function handlePauseResume() {
		tools.pauseResume( tool );
	}

	/**
	 * Handles a Cancel button click.
	 */
	function handleCancel() {
		tools.cancel( tool );
	}
</script>

{#if tool}
	<Button outline {small} {disabled} class="pause" onclick={handlePauseResume}>
		{#if tool.is_paused}
			{$strings.resume_button}
		{:else}
			{$strings.pause_button}
		{/if}
	</Button>
	<Button outline {small} {disabled} onclick={handleCancel}>{$strings.cancel_button}</Button>
{/if}
