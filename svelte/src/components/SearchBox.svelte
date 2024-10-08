<!--
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    local_mail
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
-->
<svelte:options immutable={true} />

<script lang="ts">
    import { tick } from 'svelte';
    import { blur } from '../actions/blur';
    import type { SearchParams, ViewParams } from '../lib/state';
    import type { Store } from '../lib/store';
    import SearchOptions from './SearchOptions.svelte';
    import IncrementalSearch from './IncrementalSearch.svelte';

    export let store: Store;

    let entering = !$store.params.search;
    let advancedExpanded = false;
    let inputNode: HTMLElement;
    let advancedNode: SearchOptions;
    let content = '';
    let sendername = '';
    let recipientname = '';
    let unread = false;
    let withfilesonly = false;
    let maxtime = 0;
    let loading = false;

    const updateFields = (search?: SearchParams) => {
        content = search?.content || '';
        sendername = search?.sendername || '';
        recipientname = search?.recipientname || '';
        unread = search?.unread || false;
        withfilesonly = search?.withfilesonly || false;
        maxtime = search?.maxtime || 0;
    };

    $: search = $store.params.search;

    $: updateFields(search);

    $: advancedEnabled = Boolean(
        search?.sendername ||
            search?.recipientname ||
            search?.unread ||
            search?.withfilesonly ||
            search?.maxtime,
    );

    $: searchEnabled = Boolean(search?.content) || advancedEnabled;

    $: submitEnabled = Boolean(
        content.trim() ||
            sendername.trim() ||
            recipientname.trim() ||
            unread ||
            withfilesonly ||
            maxtime,
    );

    $: searchFields = [
        { label: '', value: content },
        { label: $store.strings.searchfrom, value: sendername },
        { label: $store.strings.searchto, value: recipientname },
        {
            label: $store.strings.searchdate,
            value: maxtime > 0 ? new Date(maxtime * 1000).toLocaleDateString() : '',
        },
        { label: $store.strings.searchunreadonly, value: unread },
        { label: $store.strings.searchhasattachments, value: withfilesonly },
    ].filter(({ value }) => Boolean(value));

    const startEntering = async () => {
        entering = true;
        if (advancedEnabled) {
            advancedExpanded = true;
        }
        await tick();
        inputNode.focus();
    };

    const stopEntering = async () => {
        entering = !searchEnabled;
        advancedExpanded = false;
        updateFields(search);
    };

    const toggleDropdown = async () => {
        if (advancedExpanded) {
            advancedExpanded = false;
            await tick();
            inputNode.focus();
        } else {
            advancedExpanded = true;
            startEntering();
            await tick();
            advancedNode.focus();
        }
    };

    const cancel = async () => {
        entering = true;
        advancedExpanded = false;
        updateFields();
        await store.navigate({
            ...$store.params,
            offset: undefined,
            search: undefined,
        });
        await tick();
        inputNode.focus();
    };

    const submit = async () => {
        await store.navigate({
            ...$store.params,
            messageid: undefined,
            offset: undefined,
            search: advancedExpanded
                ? {
                      content: content.trim(),
                      sendername: sendername.trim(),
                      recipientname: recipientname.trim(),
                      unread,
                      withfilesonly,
                      maxtime,
                  }
                : {
                      content: content.trim(),
                  },
        });
        advancedExpanded = false;
        entering = false;
    };

    const handleKeypress = (event: KeyboardEvent) => {
        if (event.key == 'Enter') {
            event.preventDefault();
            if (submitEnabled) {
                submit();
            } else {
                cancel();
            }
        }
    };

    const handleIncrementalSearchClick = async (params: ViewParams) => {
        await store.navigate(params);
        advancedExpanded = false;
        entering = false;
    };
</script>

<form class="local-mail-search-box position-relative" use:blur={stopEntering}>
    <div
        class="local-mail-search-box-icon position-absolute h-100 d-flex justify-content-center align-items-center px-0"
        style="top: 0; left: 0"
    >
        <i class="fa fa-fw {loading ? 'fa-spinner fa-pulse' : 'fa-search'}" aria-hidden="true" />
    </div>

    {#if entering}
        <input
            type="text"
            class="local-mail-search-box-input form-control"
            class:local-mail-search-box-input-enabled={searchEnabled || submitEnabled}
            placeholder={$store.strings.search}
            aria-label={$store.strings.search}
            autocomplete="off"
            bind:value={content}
            bind:this={inputNode}
            on:keypress={handleKeypress}
        />
    {:else}
        <button
            type="button"
            class="local-mail-search-box-input-enabled alert-primary form-control text-left text-truncate"
            on:click={startEntering}
        >
            {#each searchFields as { label, value }, i}
                {#if i > 0}<span class="dimmed_text">,&ensp;</span>{/if}
                {#if value === true}
                    <span class="dimmed_text">{label}</span>
                {:else}
                    {#if label}<span class="dimmed_text">{label}: </span>{/if}
                    {value}
                {/if}
            {/each}
        </button>
    {/if}
    <div class="position-absolute h-100 d-flex align-items-center" style="top: 0; right: 0">
        {#if searchEnabled || submitEnabled}
            <button
                type="button"
                class="local-mail-search-box-icon btn px-0"
                title={$store.strings.clearsearch}
                on:click|preventDefault={cancel}
            >
                <i class="fa fa-fw fa-times" aria-hidden="true" />
            </button>
        {/if}
        <button
            type="button"
            aria-expanded={advancedExpanded}
            class="local-mail-search-box-icon btn px-0"
            title={$store.strings.searchoptions}
            on:click|preventDefault={toggleDropdown}
        >
            <i
                class="fa fa-fw {advancedExpanded ? 'fa-caret-up' : 'fa-sliders'}"
                aria-hidden="true"
            />
        </button>
    </div>
    {#if advancedExpanded}
        <SearchOptions
            bind:this={advancedNode}
            {store}
            bind:sendername
            bind:recipientname
            bind:unread
            bind:withfilesonly
            bind:maxtime
            {submit}
            {submitEnabled}
        />
    {/if}
    {#if $store.settings.incrementalsearch}
        <IncrementalSearch
            {store}
            enabled={entering && !advancedExpanded && !!content.trim()}
            {content}
            bind:loading
            onClick={handleIncrementalSearchClick}
        />
    {/if}
</form>

<style global>
    .local-mail-search-box {
        width: 100%;
        max-width: 100%;
    }
    .local-mail-search-box-input {
        padding-left: 2.5rem;
        padding-right: 2.5rem;
    }
    .local-mail-search-box-input-enabled {
        padding-left: 2.5rem;
        padding-right: 5rem;
    }
    .local-mail-search-box-icon {
        width: 2.5rem;
    }
</style>
