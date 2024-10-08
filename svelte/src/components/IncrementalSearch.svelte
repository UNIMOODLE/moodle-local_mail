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
    import { onDestroy } from 'svelte';
    import {
        RecipientType,
        type MessageSummary,
        type ViewParams,
        type ServiceError,
    } from '../lib/state';
    import { callServices, type MessageQuery, type SearchMessagesRequest } from '../lib/services';
    import type { Store } from '../lib/store';
    import { viewUrl } from '../lib/url';
    import ListMessageSubject from './ListMessageSubject.svelte';
    import ListMessageUsers from './ListMessageUsers.svelte';

    export let store: Store;
    export let enabled: boolean;
    export let content: string;
    export let loading = false;
    export let onClick: (params: ViewParams) => void;

    $: if (enabled) {
        search(content);
    } else {
        messages = undefined;
        loading = false;
        window.clearTimeout(timeoutId);
    }

    const LIMIT = 10;
    const DELAY = 500;

    let timeoutId: number | undefined;
    let messages: ReadonlyArray<MessageSummary> | undefined;
    let moreResults = false;

    const search = async (content: string) => {
        loading = true;
        window.clearTimeout(timeoutId);

        timeoutId = window.setTimeout(async () => {
            const params = $store.params;
            const query: MessageQuery = {
                courseid: params.courseid,
                labelid: params.tray == 'label' ? params.labelid : undefined,
                draft: params.tray == 'drafts' ? true : params.tray == 'sent' ? false : undefined,
                roles:
                    params.tray == 'inbox'
                        ? Object.values(RecipientType)
                        : params.tray == 'sent'
                          ? ['from']
                          : undefined,
                starred: params.tray == 'starred' ? true : undefined,
                deleted: params.tray == 'trash',
                content: content.trim(),
                stopid: $store.incrementalSearchStopId,
            };
            const request: SearchMessagesRequest = {
                methodname: 'search_messages',
                query,
                limit: LIMIT + 1,
            };
            let responses: unknown[];
            try {
                responses = await callServices([request]);
            } catch (error) {
                store.setError(error as ServiceError);
                loading = false;
                messages = undefined;
                return;
            }
            loading = false;
            messages = responses[0] as ReadonlyArray<MessageSummary>;
            moreResults = messages.length > LIMIT || Boolean($store.incrementalSearchStopId);
            messages = messages.slice(0, LIMIT);
        }, DELAY);
    };

    const clickHandler = (message: MessageSummary, i: number) => {
        return (event: MouseEvent) => {
            if (!message.draft) {
                event.preventDefault();
                onClick(messageParams(message, i));
            }
        };
    };

    onDestroy(() => {
        window.clearTimeout(timeoutId);
    });

    $: messageParams = (message: MessageSummary, i: number) => {
        return {
            ...$store.params,
            messageid: message.id,
            offset: i,
            search: { content: content.trim() },
        };
    };

    $: allParams = {
        ...$store.params,
        messageid: undefined,
        offset: 0,
        search: { content: content.trim() },
    };
</script>

{#if enabled && messages != null}
    <div
        class="dropdown-menu dropdown-menu-left overflow-hidden show p-0 w-100"
        style="min-width: 18rem"
    >
        {#each messages as message, i}
            {#if i > 0}
                <div class="dropdown-divider my-0" />
            {/if}
            <a
                class="dropdown-item local-mail-incremental-search-item"
                class:font-weight-bold={message.unread}
                href={viewUrl(messageParams(message, i))}
                on:click={clickHandler(message, i)}
            >
                <ListMessageSubject {store} {message} />
                <div class="local-mail-incremental-search-muted d-flex">
                    <ListMessageUsers {store} {message} />
                    <div title={message.fulltime} class="ml-auto">
                        {message.shorttime}
                    </div>
                </div>
            </a>
        {/each}
        {#if moreResults}
            <div class="dropdown-divider my-0" />
            <a
                class="dropdown-item py-2"
                href={viewUrl(allParams)}
                on:click|preventDefault={() => onClick(allParams)}
            >
                {$store.strings.searchallmessages}
            </a>
        {:else if !messages.length}
            <div class="px-4 py-2 text-danger">
                {$store.strings.nomessagesfound}
            </div>
        {/if}
    </div>
{/if}

<style global>
    .local-mail-incremental-search-muted {
        opacity: 0.6;
    }
</style>
