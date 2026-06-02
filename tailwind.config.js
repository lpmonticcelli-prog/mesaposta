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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Injetando o DNA da sua marca
                brand: {
                    dark: '#111111',       // O preto de fundo do menu
                    gold: '#ffc20c',       // O dourado principal
                    'gold-hover': '#e0a800' // O dourado escuro para quando passar o mouse
                }
            }
        },
    },

    plugins: [forms],
};