import { defineConfig, devices } from '@playwright/test';

const baseURL = 'http://127.0.0.1:8011';
const environment = {
    ...process.env,
    APP_ENV: 'testing',
    APP_URL: baseURL,
    CACHE_STORE: 'array',
    DB_CONNECTION: 'pgsql',
    DB_SEARCH_PATH: 'ciptaoffice_e2e',
    DB_URL: '',
    MAIL_MAILER: 'array',
    QUEUE_CONNECTION: 'sync',
    SESSION_DRIVER: 'database',
};

export default defineConfig({
    testDir: './tests/Browser',
    outputDir: './storage/framework/testing/playwright',
    fullyParallel: false,
    workers: 1,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chrome',
            use: {
                ...devices['Desktop Chrome'],
                channel: 'chrome',
            },
        },
    ],
    webServer: [
        {
            command: 'npm run dev -- --host 127.0.0.1',
            reuseExistingServer: true,
            stderr: 'pipe',
            stdout: 'ignore',
            timeout: 120_000,
            url: 'http://127.0.0.1:5173/@vite/client',
        },
        {
            command: 'php tests/Browser/server.php',
            env: environment,
            reuseExistingServer: false,
            stderr: 'pipe',
            stdout: 'ignore',
            timeout: 120_000,
            url: `${baseURL}/cms/login`,
        },
    ],
});
