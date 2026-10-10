import forms from '@tailwindcss/forms';
import legacyTheme from './resources/css/tailwind-legacy-theme.js';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
    ],

    theme: legacyTheme,

    plugins: [forms],
};
