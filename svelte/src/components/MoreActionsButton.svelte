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
    import { blur } from '../actions/blur';
    import type { Store } from '../lib/store';
    import { replaceStringParams } from '../lib/utils';
    import ModalDialog from './ModalDialog.svelte';
    import LabelModal from './LabelModal.svelte';

    export let store: Store;
    export let transparent = false;
    export let dropup = false;

    let expanded = false;

    $: label =
        $store.params.tray == 'label' && $store.message == null
            ? $store.labels.find((label) => label.id == $store.params.labelid)
            : null;

    $: messages = Array.from($store.selectedMessages.values());
    $: someRead = messages.some((message) => !message.draft && !message.unread);
    $: someUnread = messages.some((message) => !message.draft && message.unread);
    $: someStarred = messages.some((message) => message.starred);
    $: someUnstarred = messages.some((message) => !message.starred);
    $: disabled =
        $store.params.tray == 'trash'
            ? !$store.totalCount
            : !label && !someRead && !someUnread && !someStarred && !someUnstarred;

    const closeMenu = () => {
        expanded = false;
    };

    const toggleMenu = () => {
        expanded = !expanded;
    };

    const setUnread = (unread: boolean) => {
        closeMenu();
        store.setUnread(
            messages.filter((message) => !message.draft).map((message) => message.id),
            unread,
        );
    };

    const setStarred = (starred: boolean) => {
        closeMenu();
        store.setStarred(
            messages.map((message) => message.id),
            starred,
        );
    };

    const openEditLabelModal = () => {
        closeMenu();
        store.showDialog('editlabel');
    };

    const confirmEditLabel = (name: string, color: string) => {
        store.hideDialog();
        if (label) {
            store.updateLabel(label.id, name, color);
        }
    };

    const openDeleteLabelModal = () => {
        closeMenu();
        store.showDialog('deletelabel');
    };

    const deletelabelconfirm = () => {
        store.hideDialog();
        store.deleteLabel($store.params.labelid || 0);
    };

    const openEmptyTrashModal = () => {
        closeMenu();
        store.showDialog('emptytrash');
    };

    const confirmEmptyTrash = () => {
        store.hideDialog();
        store.emptyTrash($store.params.courseid);
    };
</script>

<div class="btn-group" class:dropup use:blur={closeMenu}>
    <button
        type="button"
        class="local-mail-action-more-button btn dropdown-toggle"
        class:btn-secondary={!transparent}
        class:disabled
        {disabled}
        aria-expanded={expanded}
        title={$store.strings.more}
        on:click={toggleMenu}
    >
        <i class="fa fa-fw fa-ellipsis-v" />
    </button>
    {#if expanded}
        <div class="dropdown-menu show">
            {#if $store.params.tray == 'trash'}
                <button type="button" class="dropdown-item" on:click={openEmptyTrashModal}>
                    {$store.strings.emptytrash}
                </button>
            {:else}
                {#if someUnread}
                    <button type="button" class="dropdown-item" on:click={() => setUnread(false)}>
                        {$store.strings.markasread}
                    </button>
                {/if}
                {#if someRead}
                    <button type="button" class="dropdown-item" on:click={() => setUnread(true)}>
                        {$store.strings.markasunread}
                    </button>
                {/if}
                {#if someUnstarred}
                    <button type="button" class="dropdown-item" on:click={() => setStarred(true)}>
                        {$store.strings.markasstarred}
                    </button>
                {/if}
                {#if someStarred}
                    <button type="button" class="dropdown-item" on:click={() => setStarred(false)}>
                        {$store.strings.markasunstarred}
                    </button>
                {/if}
                {#if label}
                    {#if someUnread || someRead || someUnstarred || someStarred}
                        <div class="dropdown-divider" />
                    {/if}
                    <button type="button" class="dropdown-item" on:click={openEditLabelModal}>
                        {$store.strings.editlabel}
                    </button>
                    <button type="button" class="dropdown-item" on:click={openDeleteLabelModal}>
                        {$store.strings.deletelabel}
                    </button>
                {/if}
            {/if}
        </div>
    {/if}
</div>

{#if $store.params.tray == 'trash'}
    {#if $store.params.dialog == 'emptytrash'}
        <ModalDialog
            title={$store.strings.emptytrash}
            cancelText={$store.strings.cancel}
            confirmText={$store.strings.emptytrash}
            confirmClass="btn-danger"
            onCancel={() => store.hideDialog()}
            onConfirm={confirmEmptyTrash}
        >
            {$store.strings.emptytrashconfirm}
        </ModalDialog>
    {/if}
{:else if label}
    {#if $store.params.dialog == 'editlabel'}
        <LabelModal
            {store}
            {label}
            onCancel={() => store.hideDialog()}
            onSubmit={confirmEditLabel}
        />
    {:else if $store.params.dialog == 'deletelabel'}
        <ModalDialog
            title={$store.strings.deletelabel}
            cancelText={$store.strings.cancel}
            confirmText={$store.strings.deletelabel}
            confirmClass="btn-danger"
            onCancel={() => store.hideDialog()}
            onConfirm={deletelabelconfirm}
        >
            {replaceStringParams($store.strings.deletelabelconfirm, label.name)}
        </ModalDialog>
    {/if}
{/if}

<style global>
    .local-mail-action-more-button::after {
        display: none !important;
    }
</style>
