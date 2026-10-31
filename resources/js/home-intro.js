import '../css/home-intro.css';

const track = document.querySelector('[data-sg-intro-track]');

if (track) {
    const stage = track.querySelector('[data-sg-intro-stage]');
    const nav = document.querySelector('nav[aria-label="ناوبری اصلی فروشگاه"]');
    const roomImage = track.querySelector('[data-sg-intro-room-image]');
    const room = track.querySelector('[data-sg-intro-room]');
    const cloth = track.querySelector('[data-sg-intro-cloth]');
    const curtainLeft = track.querySelector('[data-sg-intro-curtain-left]');
    const curtainRight = track.querySelector('[data-sg-intro-curtain-right]');
    const lamp = track.querySelector('[data-sg-intro-lamp]');
    const cord = track.querySelector('[data-sg-intro-cord]');
    const hand = track.querySelector('[data-sg-intro-hand]');
    const brand = track.querySelector('[data-sg-intro-brand]');
    const hint = track.querySelector('[data-sg-intro-hint]');
    const skipButton = track.querySelector('[data-sg-intro-skip]');
    const progressBar = track.querySelector('[data-sg-intro-progress]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const clamp = (value, min = 0, max = 1) => Math.min(max, Math.max(min, value));
    const lerp = (from, to, amount) => from + (to - from) * amount;
    const ease = (value) => {
        const t = clamp(value);
        return t < 0.5
            ? 4 * t * t * t
            : 1 - Math.pow(-2 * t + 2, 3) / 2;
    };

    let startY = 0;
    let scrollLength = 1;
    let stageWidth = 0;
    let stageHeight = 0;
    let frameRequested = false;
    let completionSent = false;
    let resizeObserver = null;

    const signalComplete = () => {
        if (completionSent) {
            return;
        }

        completionSent = true;
        track.dataset.sgIntroComplete = 'true';
        window.dispatchEvent(new Event('sg:intro:complete'));
    };

    const removeIntro = () => {
        track.remove();
        signalComplete();
    };

    const waitForImage = (image) => {
        if (image.complete && image.naturalWidth > 0) {
            return Promise.resolve();
        }

        if (typeof image.decode === 'function') {
            return image.decode();
        }

        return new Promise((resolve, reject) => {
            image.addEventListener('load', resolve, { once: true });
            image.addEventListener('error', reject, { once: true });
        });
    };

    const measure = () => {
        if (!track.isConnected || !stage) {
            return;
        }

        const navHeight = Math.round(nav?.getBoundingClientRect().height || 68);
        document.documentElement.style.setProperty('--sg-intro-header-height', navHeight + 'px');

        stageWidth = stage.clientWidth;
        stageHeight = stage.clientHeight;

        const aspectRatio = 1672 / 941;
        const imageWidth = Math.min(
            Math.max(stageWidth, stageHeight * aspectRatio),
            stageWidth * 1.55
        );
        const imageHeight = imageWidth / aspectRatio;
        const imageLeft = Math.min(
            0,
            Math.max(stageWidth - imageWidth, stageWidth / 2 - 0.557 * imageWidth)
        );
        const imageTop = imageHeight >= stageHeight
            ? Math.min(stageHeight - imageHeight, stageHeight / 2 - 0.6 * imageHeight)
            : (stageHeight - imageHeight) / 2;

        Object.assign(roomImage.style, {
            width: imageWidth + 'px',
            height: imageHeight + 'px',
            left: imageLeft + 'px',
            top: imageTop + 'px',
        });

        startY = track.getBoundingClientRect().top + window.scrollY;
        scrollLength = Math.max(1, track.offsetHeight - stage.offsetHeight);
    };

    const render = () => {
        frameRequested = false;

        if (!track.isConnected || stageWidth <= 0 || stageHeight <= 0) {
            return;
        }

        const progress = clamp((window.scrollY - startY) / scrollLength);
        const curtainOpen = ease((progress - 0.13) / 0.47);
        const curtainExit = ease((progress - 0.61) / 0.34);
        const reveal = ease((progress - 0.23) / 0.58);
        const handIn = ease((progress - 0.035) / 0.14);
        const handLift = ease((progress - 0.19) / 0.15);
        const handFade = 1 - ease((progress - 0.33) / 0.09);
        const lampGlow = ease((progress - 0.19) / 0.32);
        const brandIn = ease((progress - 0.39) / 0.16);
        const brandOut = 1 - ease((progress - 0.73) / 0.16);

        const angle = lerp(-10, 0, curtainOpen) + 80 * curtainExit;
        const curtainScale = lerp(1.18, 1.04, curtainOpen) * (1 + 0.045 * curtainExit);
        const curtainShift = stageWidth * 0.17 * curtainExit;
        const curtainLift = stageHeight * 0.075 * curtainExit;

        curtainLeft.style.transform =
            'translate3d(' + (-curtainShift) + 'px, ' + (-curtainLift) + 'px, 0) rotate(' + angle + 'deg) scale(' + curtainScale + ')';
        curtainRight.style.transform =
            'translate3d(' + curtainShift + 'px, ' + (-curtainLift) + 'px, 0) rotate(' + (-angle) + 'deg) scale(' + curtainScale + ')';

        room.style.transform = 'scale(' + (1 + 0.045 * reveal) + ')';
        cloth.style.clipPath =
            'ellipse(' + (reveal * 92) + '% ' + (reveal * 118) + '% at 50% 61%)';

        const lampTop = lerp(stageHeight * 0.72, stageHeight * 0.19, lampGlow);
        lamp.style.top = lampTop + 'px';
        lamp.style.setProperty('--sg-intro-glow', String(lampGlow));

        const handHeight = hand.getBoundingClientRect().height;
        const handTop = stageHeight * -0.08;
        const handY = lerp(stageHeight * 0.16, stageHeight * -0.62, handLift);
        hand.style.transform = 'translate3d(-50%, ' + handY + 'px, 0)';
        hand.style.opacity = String(handIn * handFade);

        const cordTop = Math.max(0, handTop + handHeight * 0.77 + handY);
        cord.style.top = cordTop + 'px';
        cord.style.height = Math.max(0, lampTop - cordTop) + 'px';
        cord.style.opacity = String(handIn * (1 - handLift));

        const brandOpacity = brandIn * brandOut;
        brand.style.opacity = String(brandOpacity);
        brand.style.transform =
            'translate3d(-50%, calc(-50% - ' + (brandIn * 2.2) + 'vh), 0) scale(' + lerp(0.97, 1, brandIn) + ')';
        hint.style.opacity = String(1 - ease(progress / 0.2));

        if (progressBar) {
            progressBar.style.transform = 'scaleX(' + progress + ')';
        }

        if (progress >= 0.999) {
            signalComplete();
        }
    };

    const requestRender = () => {
        if (frameRequested) {
            return;
        }

        frameRequested = true;
        window.requestAnimationFrame(render);
    };

    const onSkip = () => {
        const target = startY + track.offsetHeight + 1;
        skipButton?.blur();
        window.scrollTo({
            top: target,
            behavior: reduceMotion.matches ? 'auto' : 'smooth',
        });
    };

    const start = async () => {
        if (
            reduceMotion.matches ||
            window.scrollY > 12 ||
            !stage ||
            !roomImage ||
            !room ||
            !cloth ||
            !curtainLeft ||
            !curtainRight ||
            !lamp ||
            !cord ||
            !hand ||
            !brand ||
            !hint
        ) {
            removeIntro();
            return;
        }

        try {
            await Promise.all(Array.from(track.querySelectorAll('img'), waitForImage));
        } catch {
            removeIntro();
            return;
        }

        if (!track.isConnected || window.scrollY > 12) {
            removeIntro();
            return;
        }

        track.dataset.sgIntroStatus = 'active';
        measure();
        render();

        window.addEventListener('scroll', requestRender, { passive: true });
        window.addEventListener('resize', measure, { passive: true });
        window.addEventListener('resize', requestRender, { passive: true });
        window.addEventListener('orientationchange', measure, { passive: true });
        skipButton?.addEventListener('click', onSkip);

        if ('ResizeObserver' in window) {
            resizeObserver = new ResizeObserver(() => {
                measure();
                requestRender();
            });

            if (nav) {
                resizeObserver.observe(nav);
            }

            resizeObserver.observe(stage);
        }

        window.addEventListener('pagehide', () => {
            resizeObserver?.disconnect();
        }, { once: true });
    };

    start();
}
