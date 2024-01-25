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

import type { Course } from './state';

/**
 * List of supported colors for labels.
 */
export const colors: ReadonlyArray<string> = [
    '',
    'blue',
    'indigo',
    'purple',
    'pink',
    'red',
    'orange',
    'yellow',
    'green',
    'teal',
    'cyan',
];

/**
 * Converts HTML to plain text.
 *
 * @param html The HTML string to convert.
 * @returns The plain text representation of the HTML.
 */
export function convertHtmlToText(html: string): string {
    const el = document.createElement('DIV');
    el.innerHTML = html;
    return el.innerText || '';
}

/**
 * Converts a timestamp to a date string.
 *
 * @param time UNIX Timestamp.
 * @returns Date in YYYY-MM-DD format.
 */
export function dateFromTimestamp(time: number): string {
    if (time == 0) {
        return '';
    }
    const date = new Date(time * 1000);
    return [
        String(date.getFullYear()),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-');
}

/**
 * Formats the course name based on the specified field.
 *
 * @param course The course object.
 * @param field The field to format the course name ('shortname' or 'fullname').
 * @returns The formatted course name.
 */
export function formatCourseName(
    course: Course | undefined,
    field?: 'shortname' | 'fullname' | 'hidden',
): string {
    if (!course) {
        return '';
    } else if (field == 'shortname') {
        return convertHtmlToText(course.shortname);
    } else {
        return convertHtmlToText(course.fullname);
    }
}

/**
 * Format size to include units.
 *
 * @param size Number of bytes.
 * @return Formatted size.
 */
export function formatSize(size: number): string {
    const units = [
        { bytes: 2 ** 40, name: 'TB' },
        { bytes: 2 ** 30, name: 'GB' },
        { bytes: 2 ** 20, name: 'MB' },
        { bytes: 2 ** 10, name: 'KB' },
    ];

    for (const unit of units) {
        if (size >= unit.bytes) {
            const formatter = Intl.NumberFormat(undefined, { maximumFractionDigits: 1 });
            return `${formatter.format(size / unit.bytes)} ${unit.name}`;
        }
    }

    return `${size} B`;
}

/**
 * Removes leading and trailing spaces and replaces repeates space characters with a single space.
 *
 * @param name The name of the label.
 * @returns The normalized name.
 */
export function normalizeLabelName(name: string): string {
    return name.trim().replaceAll(/\s+/gu, ' ');
}

/**
 * Replaces {$a} parameters of a language string.
 *
 * @param string Language string.
 * @param param A string, a number or an object to replace parameters with.
 * @returns String with parameters replaced.
 */
export function replaceStringParams(
    string: string,
    params: string | number | Record<string, string | number>,
): string {
    string = string || '';
    if (typeof params == 'string' || typeof params == 'number') {
        string = string.replace('{$a}', params.toString());
    } else {
        for (const key in params) {
            string = string.replace(`{$a->${key}}`, params[key].toString());
        }
    }
    return string;
}

/**
 * Converts a date string to a timestamp.
 *
 * @param date Date in YYYY-MM-DD format.
 * @returns UNIX Timestamp.
 */
export function timestampFromDate(date: string): number {
    if (!date) {
        return 0;
    }
    return Math.floor(
        new Date(
            parseInt(date.slice(0, 4)),
            parseInt(date.slice(5, 7)) - 1,
            parseInt(date.slice(8, 10)),
            23,
            59,
            59,
        ).getTime() / 1000,
    );
}
