import { addDynamicIconSelectors } from '@iconify/tailwind';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: ['icon-[heroicons--phone]', 'icon-[heroicons--envelope]'],
    plugins: [
        addDynamicIconSelectors(),
    ],
};
