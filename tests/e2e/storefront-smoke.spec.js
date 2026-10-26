import { expect, test } from '@playwright/test';

const routes = [
    '/',
    '/shop',
    '/categories',
    '/categories/furniture',
    '/product/luna-sofa',
    '/about',
    '/contact',
    '/cart',
    '/login',
    '/register',
];

const viewports = [
    { width: 360, height: 740 },
    { width: 390, height: 844 },
    { width: 768, height: 900 },
    { width: 1024, height: 900 },
    { width: 1440, height: 960 },
];

for (const viewport of viewports) {
    test('public routes render without JS errors or horizontal overflow at ' + viewport.width + 'px', async ({ page }) => {
        await page.setViewportSize(viewport);

        for (const path of routes) {
            const pageErrors = [];
            const consoleErrors = [];
            const failedLocalAssets = [];

            const onPageError = error => pageErrors.push(error.message);
            const onConsole = message => {
                if (message.type() !== 'error') return;

                // Ignore only browser-generated resource fetch diagnostics; local
                // asset failures are captured separately below. JavaScript console
                // errors remain test failures.
                if (/Failed to load resource|net::ERR_|favicon\.ico/i.test(message.text())) return;
                consoleErrors.push(message.text());
            };
            const onRequestFailed = request => {
                try {
                    const url = new URL(request.url());
                    if (url.origin === 'http://127.0.0.1:8000') {
                        failedLocalAssets.push(request.url() + ' (' + (request.failure()?.errorText ?? 'request failed') + ')');
                    }
                } catch {
                    // Ignore malformed request URLs.
                }
            };

            page.on('pageerror', onPageError);
            page.on('console', onConsole);
            page.on('requestfailed', onRequestFailed);

            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
            expect(response, path + ' must return a document response').not.toBeNull();
            expect(response.status(), path + ' must not return an HTTP error').toBeLessThan(400);

            await expect(page.locator('nav[aria-label="ناوبری اصلی فروشگاه"]'), path + ' main navigation').toBeVisible();
            await expect(page.locator('footer'), path + ' footer').toBeAttached();

            if (viewport.width < 1024) {
                await expect(page.locator('.livora-mobile-nav'), path + ' mobile navigation').toBeVisible();
            } else {
                await expect(page.locator('.livora-mobile-nav'), path + ' desktop hides mobile navigation').toBeHidden();
            }

            const overflow = await page.evaluate(() => (
                Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
                - document.documentElement.clientWidth
            ));

            expect(overflow, path + ' horizontal overflow at ' + viewport.width + 'px').toBeLessThanOrEqual(1);
            expect(pageErrors, path + ' uncaught JavaScript exceptions').toEqual([]);
            expect(consoleErrors, path + ' JavaScript console errors').toEqual([]);
            expect(failedLocalAssets, path + ' failed local assets').toEqual([]);

            page.off('pageerror', onPageError);
            page.off('console', onConsole);
            page.off('requestfailed', onRequestFailed);
        }
    });
}

test('mobile search opens, focuses its input and restores focus when closed', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const searchTrigger = page.locator('.livora-mobile-nav button[aria-label="جستجو"]');
    await searchTrigger.click();

    const searchOverlay = page.locator('.livora-search-overlay');
    await expect(searchOverlay).toBeVisible();
    await expect(page.locator('#global-search')).toBeFocused();

    await page.locator('.livora-search-panel button[aria-label="بستن جستجو"]').click();
    await expect(searchOverlay).toBeHidden();
    await expect(searchTrigger).toBeFocused();
});

test('mobile menu is reachable from the visible header and can be closed', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const menuTrigger = page.locator('button[aria-label="باز کردن منو"]');
    await expect(menuTrigger).toBeVisible();
    await menuTrigger.click();

    const menu = page.locator('[role="dialog"][aria-label="منوی موبایل"]');
    await expect(menu).toBeVisible();
    await menu.locator('button[aria-label="بستن منو"]').last().click();
    await expect(menu).toBeHidden();
});
