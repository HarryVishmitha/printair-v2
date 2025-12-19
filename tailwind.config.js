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
    theme: {
        extend: {
            fontFamily: {
                'be-vietnam-pro': ['Be Vietnam Pro', 'sans-serif'],
            },
            fontWeight: {
                thin: '100',
                extralight: '200',
                light: '300',
                normal: '400',
                medium: '500',
                semibold: '600',
                bold: '700',
                extrabold: '800',
                black: '900',
            },
        },
    },
};
