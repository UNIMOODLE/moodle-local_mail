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
    import type { User } from '../lib/state';

    export let user: User | undefined = undefined;
    export let icon: string | undefined = undefined;
</script>

{#if user?.pictureurl}
    <img
        src={user.pictureurl}
        width="35"
        height="35"
        alt=""
        aria-hidden="true"
        class="rounded-circle"
    />
{:else}
    <span class="local-mail-user-picture-fallback m-0">
        {#if user}
            {user.firstname.slice(0, 1)}{user.lastname.slice(0, 1)}
        {:else if icon}
            <i class="fa {icon}" />
        {/if}
    </span>
{/if}

<style global>
    .local-mail-user-picture-fallback {
        width: 35px;
        height: 35px;
        background-color: rgba(0, 0, 0, 0.1);
        vertical-align: middle;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: var(--dark);
        font-weight: 400;
    }
</style>
