import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                cream: '#f4efe6',
                ink: '#1a1814',
                forest: {
                    50: '#f2f7f4',
                    100: '#dcebe3',
                    700: '#1d5a48',
                    800: '#143d33',
                    900: '#0c2822',
                },
                gold: {
                    100: '#f8efd8',
                    400: '#e0b15a',
                    500: '#c4922e',
                    700: '#8a6418',
                },
            },
            fontFamily: {
                sans: ['"Source Sans 3"', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
        },
    },
    plugins: [],
};
