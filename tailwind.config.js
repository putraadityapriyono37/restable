import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                terracotta: '#C1502E',
                'terracotta-dark': '#A84325',
                charcoal: '#1F1B18',
                cream: '#F5EFE6',
                'cream-dark': '#EAE1D3',
                olive: '#4A5240',
                ochre: '#B8862E',
                brick: '#8C3B2E',
            },
        },
    },

    plugins: [forms],
};
