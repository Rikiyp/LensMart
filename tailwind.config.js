import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                paper: {
                    50: '#fbfaf7',
                    100: '#f7f5f0',
                    200: '#efece4',
                    300: '#e3ded2',
                    400: '#cfc8b8',
                },
                ink: {
                    50: '#f6f6f7',
                    100: '#e7e7e9',
                    200: '#d1d1d5',
                    300: '#b0b0b7',
                    400: '#888894',
                    500: '#6b6b77',
                    600: '#555560',
                    700: '#45454e',
                    800: '#3b3b42',
                    900: '#1c1c21',
                    950: '#101014',
                },
            },
            boxShadow: {
                soft: '0 20px 50px -30px rgba(16, 16, 20, 0.35)',
                card: '0 1px 2px rgba(16,16,20,.04), 0 12px 30px -18px rgba(16,16,20,.25)',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .5s ease-out both',
                marquee: 'marquee 32s linear infinite',
            },
        },
    },

    plugins: [forms],
};
