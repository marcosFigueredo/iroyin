import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/Browser',
    fullyParallel: false,
    timeout: 15_000,
    expect: {
        timeout: 5_000,
    },
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL: 'http://127.0.0.1:4173',
        locale: 'pt-BR',
        timezoneId: 'America/Bahia',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=4173',
        url: 'http://127.0.0.1:4173/ping.txt',
        reuseExistingServer: false,
        timeout: 30_000,
        env: {
            APP_ENV: 'testing',
            APP_DEBUG: 'false',
            SESSION_DRIVER: 'array',
            CACHE_STORE: 'array',
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: ':memory:',
        },
    },
});
