<script>
	import {strings, urls} from "../js/stores";
	import Upsell from "../components/Upsell.svelte";

	let benefits = [
		{
			icon: $urls.assets + "img/icon/offload-remaining.svg",
			alt: "offload icon",
			text: $strings.tools_upsell_benefits.offload,
		},
		{
			icon: $urls.assets + "img/icon/download.svg",
			alt: "download icon",
			text: $strings.tools_upsell_benefits.download,
		},
		{
			icon: $urls.assets + "img/icon/remove-from-bucket.svg",
			alt: "remove from bucket icon",
			text: $strings.tools_upsell_benefits.remove_bucket,
		},
		{
			icon: $urls.assets + "img/icon/remove-from-server.svg",
			alt: "remove from server icon",
			text: $strings.tools_upsell_benefits.remove_server,
		},
	];
</script>

<Upsell {benefits}>
	{#snippet heading()}
		<div>{$strings.no_tools_header}</div>
	{/snippet}

	{#snippet description()}
		<div>{@html $strings.no_tools_description}</div>
	{/snippet}
</Upsell>
