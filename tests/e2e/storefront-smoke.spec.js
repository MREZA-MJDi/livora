import { expect, test } from '@playwright/test';

const routes = [
    '/',
    '/shop',
    '/categories',
    '/categories/furniture',
    '/product/luna-sofa',
    '/about',
    '/contact',
    '/blog',
    '/cart',
    '/account',
    '/account/profile',
    '/account/addresses',
    '/account/orders',
    '/account/wishlist',
    '/checkout',
    '/login',
    '/register',
];

const viewports = [
    { width: 320, height: 720 },
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
                await expect(page.locator('.sg-mobile-nav'), path + ' mobile navigation').toBeVisible();
            } else {
                await expect(page.locator('.sg-mobile-nav'), path + ' desktop hides mobile navigation').toBeHidden();
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


test('mobile customer login, cart and checkout remain usable through order creation without email', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });

    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill('demo@livora.test');
    await page.locator('input[name="password"]').fill('password');
    await page.locator('button[type="submit"]').click();

    await expect(page).toHaveURL(/\/account(?:\/)?$/);

    let overflow = await page.evaluate(() => (
        Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
        - document.documentElement.clientWidth
    ));
    expect(overflow, 'customer account dashboard mobile overflow').toBeLessThanOrEqual(1);

    await page.goto('/product/stone-vase', { waitUntil: 'domcontentloaded' });
    await page.locator('#product-purchase button[type="submit"]').click();
    await page.goto('/cart', { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Stone Vase')).toBeVisible();
    await page.getByRole('link', { name: 'ادامه به تسویه حساب' }).click();

    await expect(page).toHaveURL(/\/checkout(?:\/)?$/);
    await expect(page.locator('form[action*="/checkout"]')).toBeVisible();

    // The province modal must reveal only cities for the selected province.
    await page.locator('button[aria-controls="iran-shipping-location-modal"]').first().click();
    const regionDialog = page.getByRole('dialog', { name: 'استان محل تحویل را انتخاب کنید' });
    await expect(regionDialog).toBeVisible();
    await regionDialog.getByRole('button', { name: /^تهران/ }).first().click();

    const cityDialog = page.getByRole('dialog', { name: 'شهر محل تحویل را انتخاب کنید' });
    await expect(cityDialog).toBeVisible();
    await cityDialog.getByRole('button', { name: /^تهران$/ }).first().click();

    await page.locator('input[name="first_name"]').fill('مشتری');
    await page.locator('input[name="last_name"]').fill('آزمایشی');
    await page.locator('input[name="phone"]').fill('09121234567');
    await page.locator('input[name="email"]').fill('');
    await page.locator('[name="address"]').fill('تهران، خیابان نمونه، پلاک ۱۲، واحد ۳');
    await page.locator('input[name="postal_code"]').fill('1234567890');

    await page.getByRole('button', { name: 'ثبت سفارش و ادامه پرداخت' }).click();
    await expect(page).toHaveURL(/\/checkout\/payment\/\d+/);
    await expect(page.getByText(/در انتظار پرداخت/).first()).toBeVisible();

    overflow = await page.evaluate(() => (
        Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
        - document.documentElement.clientWidth
    ));
    expect(overflow, 'mobile payment waiting page overflow').toBeLessThanOrEqual(1);
});

test('admin can log in and use the dashboard drawer on a narrow mobile viewport', async ({ page }) => {
    const adminEmail = process.env.ADMIN_EMAIL;
    const adminPassword = process.env.ADMIN_PASSWORD;

    test.skip(!adminEmail || !adminPassword, 'Admin credentials are injected only by the isolated browser CI job.');

    await page.setViewportSize({ width: 360, height: 740 });
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill(adminEmail);
    await page.locator('input[name="password"]').fill(adminPassword);
    await page.getByRole('button', { name: 'ورود به حساب' }).click();

    await expect(page).toHaveURL(new RegExp('admin/?$'));
    await expect(page.locator('.admin-shell')).toBeVisible();
    await expect(page.locator('[data-admin-sidebar]')).toBeAttached();

    const openSidebar = page.getByRole('button', { name: 'باز کردن منوی مدیریت' });
    await expect(openSidebar).toBeVisible();
    await openSidebar.click();

    const sidebar = page.locator('[data-admin-sidebar]');
    await expect(sidebar).toBeInViewport();
    await expect(page.locator('[data-admin-sidebar-backdrop]')).toBeVisible();

    await sidebar.getByRole('button', { name: 'بستن منوی مدیریت' }).click();
    await expect(sidebar).not.toBeInViewport();
    await expect(page.locator('[data-admin-sidebar-backdrop]')).toBeHidden();

    const overflow = await page.evaluate(() => (
        Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
        - document.documentElement.clientWidth
    ));
    expect(overflow, 'admin dashboard horizontal overflow at 360px').toBeLessThanOrEqual(1);
});

test('mobile search opens, focuses its input and restores focus when closed', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const searchTrigger = page.locator('.sg-mobile-nav button[aria-label="جستجو"]');
    await searchTrigger.click();

    const searchOverlay = page.locator('.sg-search-overlay');
    await expect(searchOverlay).toBeVisible();
    await expect(page.locator('#global-search')).toBeFocused();

    await page.locator('.sg-search-panel button[aria-label="بستن جستجو"]').click();
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


test('public category, product and banner image frames preserve their editor crop ratios', async ({ page }) => {
    const expectRatio = async (selector, expected, label) => {
        const element = page.locator(selector).first();
        if (await element.count() === 0) return;
        const ratio = await element.evaluate(node => {
            const rect = node.getBoundingClientRect();
            return rect.width / rect.height;
        });
        expect(ratio, label).toBeCloseTo(expected, 1);
    };

    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);

        await page.goto('/categories', { waitUntil: 'domcontentloaded' });
        await expectRatio('.sg-category-index-tile__media', 4 / 3, 'category directory card ratio');

        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await expectRatio('.category-card-media', 4 / 3, 'homepage category card ratio');
        await expectRatio('.sg-product-grid .product-card-media', 4 / 5, 'homepage product card ratio');

        await page.goto('/categories/furniture', { waitUntil: 'domcontentloaded' });
        await expectRatio('.sg-category-detail-visual', 4 / 3, 'category detail hero ratio');

        await page.goto('/shop', { waitUntil: 'domcontentloaded' });
        await expectRatio('.shop-hero-banner', 12 / 5, 'shop banner ratio');
        await expectRatio('.sg-product-grid--catalog .product-card-media', 4 / 5, 'shop product card ratio');

        if (viewport.width < 640) {
            await page.goto('/product/luna-sofa', { waitUntil: 'domcontentloaded' });
            await expectRatio('.product-gallery-stage', 4 / 5, 'public product gallery ratio');
        }
    }
});


test('about page contains the SillaGallery story and the footer offers useful shopping paths', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/about', { waitUntil: 'domcontentloaded' });

    await expect(page.getByRole('heading', { level: 1 })).toContainText('درباره');
    await expect(page.getByText('خانه، با انتخاب‌هایی شکل می‌گیرد که به آن معنا می‌دهند')).toBeVisible();
    await expect(page.getByRole('link', { name: 'مشاهده محصولات و مجموعه‌ها' })).toHaveAttribute('href', '/shop');
    await expect(page.locator('footer a[href$="/about"]').first()).toHaveAttribute('href', /\\/about$/);
    await expect(page.locator('footer a[href$="/terms"]').first()).toHaveAttribute('href', /\\/terms$/);
    await expect(page.locator('footer a[href$="/contact"]').last()).toHaveAttribute('href', /\\/contact$/);

    const overflow = await page.evaluate(() => (
        Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
        - document.documentElement.clientWidth
    ));
    expect(overflow, 'about page and marketing footer mobile overflow').toBeLessThanOrEqual(1);
});

test('admin category catalogue supports search and status filtering', async ({ page }) => {
    const adminEmail = process.env.ADMIN_EMAIL;
    const adminPassword = process.env.ADMIN_PASSWORD;

    test.skip(!adminEmail || !adminPassword, 'Admin credentials are injected only by the isolated browser CI job.');

    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill(adminEmail);
    await page.locator('input[name="password"]').fill(adminPassword);
    await page.getByRole('button', { name: 'ورود به حساب' }).click();
    await expect(page).toHaveURL(new RegExp('admin/?$'));

    await page.goto('/admin/categories', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#category-search')).toBeVisible();
    await page.locator('#category-search').fill('furniture');
    await page.locator('#category-status').selectOption('all');
    await page.getByRole('button', { name: 'اعمال فیلتر' }).click();

    await expect(page).toHaveURL(/search=furniture/);
    await expect(page.locator('.admin-table')).toContainText('furniture');
    await expect(page.locator('.admin-table')).toContainText('تعداد محصولات');
});
