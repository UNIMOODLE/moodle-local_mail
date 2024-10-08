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
    import { truncate } from '../actions/truncate';
    import type { Course, Settings, Strings } from '../lib/state';
    import { formatCourseName } from '../lib/utils';

    export let settings: Settings;
    export let strings: Strings;
    export let courses: ReadonlyArray<Course>;
    export let label: string;
    export let selected: number | undefined;
    export let required = false;
    export let readonly = false;
    export let primary = false;
    export let style: 'menu' | 'navbar' | 'filter-left' | 'filter-right';
    export let onChange: (id?: number) => void;

    let inputNode: HTMLInputElement;
    let inputText = '';
    let entering = false;
    let currentCourse: Course | undefined;

    $: currentCourse = courses.find((course) => course.id == selected);
    $: currentCourseName = formatCourseName(currentCourse, settings.filterbycourse);
    $: inputPattern = new RegExp(escape(inputText.trim()).replaceAll(/\s+/gu, '\\s+'), 'giu');
    $: dropdownCourses = courses.filter((course) =>
        formatCourseName(course, settings.filterbycourse).match(inputPattern),
    );
    $: dropdownIconClass = !entering ? 'fa-caret-down' : inputText ? 'fa-times' : 'fa-caret-up';
    $: courseHtml = (course: Course): string =>
        formatCourseName(course, settings.filterbycourse).replaceAll(inputPattern, (match) =>
            match.trim() ? '<mark>' + match + '</mark>' : match,
        );

    const escape = (text: string): string => text.replace(/[.*+?^${}()|[\]\\]/gu, '\\$&');

    const openDropdown = async () => {
        entering = true;
        inputText = '';
        await tick();
        inputNode.focus();
    };

    const closeDropdown = () => {
        entering = false;
        inputText = '';
    };

    const toggleDropdown = async () => {
        if (entering) {
            closeDropdown();
        } else {
            openDropdown();
        }
    };

    const selectAllCourses = async () => {
        await onChange();
        selected = undefined;
        entering = false;
        inputText = '';
    };

    const selectCourse = async (course: Course) => {
        await onChange(course.id);
        selected = course.id;
        entering = false;
        inputText = '';
    };

    const handleInputKey = (event: KeyboardEvent) => {
        if (event.key == 'Escape') {
            entering = false;
            inputText = '';
            inputNode.blur();
        }
    };
</script>

<div
    class="local-mail-course-select position-relative d-flex p-0"
    class:local-mail-course-select-menu={style == 'menu' || style == 'navbar'}
    class:local-mail-course-select-navbar={style == 'navbar'}
    class:list-group-item={style == 'menu' || style == 'navbar'}
    use:blur={closeDropdown}
>
    <div
        class="local-mail-course-select-icon position-absolute h-100 d-flex align-items-center"
        style="top: 0; left: 0"
    >
        <i class="fa fa-fw fa-graduation-cap" aria-hidden="true" />
    </div>

    {#if readonly}
        <div
            class="form-control alert-secondary pl-5 pr-2 text-left"
            use:truncate={currentCourseName}
        >
            {currentCourseName}
        </div>
    {:else if entering}
        <input
            type="text"
            class="form-control px-5 text-truncate"
            placeholder={entering ? strings.course : label}
            aria-label={entering ? strings.course : label}
            bind:value={inputText}
            bind:this={inputNode}
            on:focus={openDropdown}
            on:keyup={handleInputKey}
        />
    {:else if !currentCourse}
        <button
            type="button"
            class="form-control px-5 text-left"
            style="border-color: rgba(0, 0, 0, 0.125)"
            class:btn-secondary={style == 'menu' || style == 'navbar'}
            on:click={openDropdown}
        >
            {label}
        </button>
    {:else}
        <button
            type="button"
            class="form-control px-5 text-left"
            class:alert-primary={primary && (style == 'filter-left' || style == 'filter-right')}
            class:btn-secondary={style == 'menu' || style == 'navbar'}
            use:truncate={currentCourseName}
            on:click={toggleDropdown}
        >
            {currentCourseName}
        </button>
    {/if}
    {#if !readonly}
        <button
            type="button"
            aria-expanded={entering}
            title={strings.changecourse}
            class="btn position-absolute h-100 d-flex align-items-center px-2"
            style="top: 0; right: 0"
            on:click={toggleDropdown}
        >
            <i class="fa fa-fw {dropdownIconClass}" aria-hidden="true" />
        </button>
    {/if}

    {#if entering}
        <div
            class="dropdown-menu show"
            class:dropdown-menu-left={style == 'menu' || style == 'filter-left'}
            class:dropdown-menu-right={style == 'filter-right'}
            class:w-100={style == 'navbar'}
        >
            {#if !required}
                <button
                    type="button"
                    class="dropdown-item text-truncate"
                    class:px-3={style == 'navbar'}
                    on:click={() => selectAllCourses()}
                >
                    {strings.allcourses}
                </button>

                <div class="dropdown-divider" />
            {/if}
            {#each dropdownCourses as course (course.id)}
                <button
                    type="button"
                    class="dropdown-item text-truncate"
                    class:px-3={style == 'navbar'}
                    on:click={() => selectCourse(course)}
                >
                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                    {@html courseHtml(course)}
                </button>
            {:else}
                <div class="px-4 text-danger">
                    {strings.nocoursematchestext}
                </div>
            {/each}
        </div>
    {/if}
</div>

<style global>
    .local-mail-course-select {
        min-width: 0;
        flex-grow: 1;
        width: 100%;
    }

    .local-mail-course-select .dropdown-item :global(mark) {
        padding-left: 0;
        padding-right: 0;
        background-color: rgba(255, 255, 0, 0.2);
        color: inherit;
    }

    .local-mail-course-select-menu .form-control {
        padding-left: 2.75rem !important;
    }

    .local-mail-course-select-icon {
        padding-left: 0.5rem;
    }

    .local-mail-course-select-menu .local-mail-course-select-icon {
        padding-left: 1rem;
    }

    .local-mail-course-select input.form-control {
        border-color: rgba(0, 0, 0, 0.125);
    }

    .local-mail-course-select .dropdown-menu {
        max-width: 90vw;
    }

    .local-mail-course-select .dropdown-item:not(:focus):hover {
        color: inherit;
        background-color: #eee;
    }

    .local-mail-course-select > .position-absolute {
        z-index: 10;
    }

    .local-mail-course-select .form-control:focus {
        z-index: 3;
    }

    .local-mail-course-select-menu > .form-control {
        font-size: inherit;
        height: auto;
        border-width: 0;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .local-mail-course-select-navbar > .form-control {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
</style>
