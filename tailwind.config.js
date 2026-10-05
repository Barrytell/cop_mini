import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Support/CmsText.php',
    ],
    theme: {
        extend: {
            colors: {
                cream: '#f6f3ee',
                ink: '#102033',
                navy: {
                    50: '#f3f6fb',
                    100: '#e4ebf5',
                    700: '#1e3a5f',
                    800: '#16304f',
                    900: '#0b1f3a',
                },
                forest: {
                    50: '#f3f6fb',
                    100: '#e4ebf5',
                    700: '#1e3a5f',
                    800: '#16304f',
                    900: '#0b1f3a',
                },
                gold: {
                    100: '#f8f1de',
                    400: '#d4bc7d',
                    500: '#c4a35a',
                    700: '#786028',
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
