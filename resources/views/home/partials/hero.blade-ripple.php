@php
    $hotspots = [
        [
            'position' => [
                'base' => ['top' => '6%', 'left' => '70%'],
                'md'   => ['top' => '61%', 'left' => '68%'],
                'lg'   => ['top' => '64%', 'left' => '71%'],
            ],
            'content' => '
                <p class="font-medium text-white">
                    Classer Home
                </p>

                <p class="mt-2 text-white/75 leading-relaxed">
                    This is a tooltip body. You can include <strong>HTML</strong>,
                    <code>code</code>, links, or small lists here.
                </p>
            ',
        ]
    ];
@endphp

<style>
    [data-water-ripple-source] {
        position: absolute;
        inset: 0;
        z-index: 1;
        transition: opacity 300ms ease;
    }

    [data-water-ripple-source] img,
    [data-water-ripple-source] image {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: right center !important;
        display: block !important;
    }

    [data-water-ripple-canvas] {
        opacity: 0;
        transition: opacity 300ms ease;
    }

    [data-water-ripple-hero].is-water-ready [data-water-ripple-canvas] {
        opacity: 1;
    }

    [data-water-ripple-hero].is-water-ready [data-water-ripple-source] {
        opacity: 0;
    }
</style>

<section class="relative w-full h-[75svh] min-h-[460px] md:min-h-[560px] overflow-hidden" data-water-ripple-hero data-water-x="65" data-water-y="66" data-water-speed="0.95" data-water-cadence="1000" data-water-strength="220" data-water-radius="10" data-water-damping="0.985" data-water-refraction="3" data-water-render-scale="0.55" data-water-max-fps="60" data-water-mask-radius="200" data-water-mask-feather="400" data-water-prewarm="true" data-water-prewarm-pulses="8" data-water-prewarm-steps="440">
    {{-- image/background/content here --}}
    <x-hotspots :hotspots="$hotspots" />

    {{-- Original image source / fallback --}}
    <div data-water-ripple-source>
        @include('home.partials.images.hero-image')
    </div>

    {{-- Canvas water displacement layer --}}
    <canvas class="absolute inset-0 z-[2] pointer-events-none" data-water-ripple-canvas aria-hidden="true"></canvas>

    {{-- Dark overlay for legibility --}}
    <div class="absolute inset-0 z-[3] bg-gradient-to-r from-black/85 via-black/55 to-black/10"></div>

    {{-- Subtle bottom vignette --}}
    <div class="absolute inset-x-0 bottom-0 z-[4] h-40 bg-gradient-to-t from-black/100 to-transparent"></div>

    {{-- Optional soft highlight so the image does not feel flat --}}
    <div class="absolute inset-0 z-[5] bg-[radial-gradient(circle_at_75%_35%,rgba(255,255,255,0.16),transparent_35%)]"></div>

    {{-- Content --}}
    <div class="w-full px-4 md:px-6 pt-12 pb-5 relative z-10 h-full flex items-center">
        <div class="mx-auto w-full max-w-7xl">
            <div class="max-w-2xl">
                <h1 class="text-white font-medium text-5xl md:text-6xl lg:text-7xl leading-[1.02] tracking-[-0.035em]">
                    Finally, <br>
                    a home for <br>
                    your <span class="font-acent">adventures</span>..
                </h1>

                <p class="mt-6 text-white/85 text-base md:text-lg leading-relaxed max-w-md">
                    Built for action camera owners who have thousands of clips on their hard drives and zero time to sort them.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5">
                    <div class="flex">
                        @include('partials.catalog-item-purchase-form', [
                        'btnClasses' => 'bg-white text-black shadow-lg shadow-black/20',
                        'buttonLabel' => 'Order now',
                        'formClass' => '',
                        'catalogItemSkus' => [
                        'PRODUCT-J3VQXNTI',
                        'PLAN-NT8P1DOQ',
                        ],
                        ]);
                    </div>

                    <p class="text-white/75 text-base leading-none">
                        Free Classer software included
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="module">
    /**
     * Water Ripple Hero Effect
     *
     * Usage:
     * 1. Add data-water-ripple-hero to your hero section.
     * 2. Add a child wrapper containing your original hero image with data-water-ripple-source.
     * 3. Add a canvas with data-water-ripple-canvas.
     * 4. Include this script after the hero markup or bundle it with your JS.
     *
     * Example markup:
     *
     * <section
     *   data-water-ripple-hero
     *   data-water-x="68"
     *   data-water-y="68"
     *   data-water-speed="0.85"
     *   data-water-cadence="1200"
     *   data-water-strength="520"
     *   data-water-radius="14"
     *   data-water-damping="0.985"
     *   data-water-refraction="7"
     *   data-water-render-scale="0.55"
     *   data-water-max-fps="30"
     *   data-water-mask-radius="110"
     *   data-water-mask-feather="90"
     *   data-water-prewarm="true"
     *   data-water-prewarm-pulses="5"
     *   data-water-prewarm-steps="90"
     * >
     *   <div data-water-ripple-source>
     *     <img src="/path/to/image.jpg" alt="" />
     *   </div>
     *
     *   <canvas data-water-ripple-canvas aria-hidden="true"></canvas>
     * </section>
     */

    (() => {
        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

        const toNumber = (value, fallback) => {
            const number = Number(value);
            return Number.isFinite(number) ? number : fallback;
        };

        const getConfig = (hero) => ({
            x: toNumber(hero.dataset.waterX, 68),
            y: toNumber(hero.dataset.waterY, 68),
            speed: Math.max(toNumber(hero.dataset.waterSpeed, 0.85), 0.1),
            cadence: Math.max(toNumber(hero.dataset.waterCadence, 1200), 100),
            strength: toNumber(hero.dataset.waterStrength, 520),
            radius: Math.max(toNumber(hero.dataset.waterRadius, 14), 2),
            damping: clamp(toNumber(hero.dataset.waterDamping, 0.985), 0.9, 0.999),
            refraction: Math.max(toNumber(hero.dataset.waterRefraction, 7), 0),
            renderScale: clamp(toNumber(hero.dataset.waterRenderScale, 0.55), 0.25, 1),
            maxFps: clamp(toNumber(hero.dataset.waterMaxFps, 30), 12, 60),
            maskRadius: Math.max(toNumber(hero.dataset.waterMaskRadius, 110), 0),
            maskFeather: Math.max(toNumber(hero.dataset.waterMaskFeather, 90), 1),
            prewarm: hero.dataset.waterPrewarm !== 'false',
            prewarmPulses: Math.max(Math.round(toNumber(hero.dataset.waterPrewarmPulses, 5)), 0),
            prewarmSteps: Math.max(Math.round(toNumber(hero.dataset.waterPrewarmSteps, 90)), 0),
        });

        const getDevicePixelRatio = () => Math.min(window.devicePixelRatio || 1, 2);

        const getHeroSize = (hero, config) => {
            const rect = hero.getBoundingClientRect();
            const dpr = getDevicePixelRatio();
            const scale = config.renderScale;

            return {
                cssWidth: rect.width,
                cssHeight: rect.height,
                width: Math.max(1, Math.round(rect.width * dpr * scale)),
                height: Math.max(1, Math.round(rect.height * dpr * scale)),
                dpr,
                scale,
            };
        };

        const getOrigin = ({
            width,
            height
        }, config) => ({
            x: Math.round(width * (clamp(config.x, 0, 100) / 100)),
            y: Math.round(height * (clamp(config.y, 0, 100) / 100)),
        });

        const getSourceImage = (hero) => {
            const source = hero.querySelector('[data-water-ripple-source]');
            const image = source?.querySelector('img');

            return image || null;
        };

        const loadDrawableImage = (sourceImage) =>
            new Promise((resolve, reject) => {
                if (!sourceImage) {
                    reject(new Error('No source image found for water ripple hero.'));
                    return;
                }

                const src = sourceImage.currentSrc || sourceImage.src;

                if (!src) {
                    reject(new Error('Source image has no src.'));
                    return;
                }

                const image = new Image();

                if (!src.startsWith('data:') && !src.startsWith('blob:')) {
                    image.crossOrigin = 'anonymous';
                }

                image.onload = () => resolve(image);
                image.onerror = () => reject(new Error('Could not load source image for canvas ripple.'));
                image.src = src;
            });

        const drawImageCover = (ctx, image, width, height) => {
            const imageRatio = image.naturalWidth / image.naturalHeight;
            const canvasRatio = width / height;

            const drawHeight = imageRatio > canvasRatio ? height : width / imageRatio;
            const drawWidth = imageRatio > canvasRatio ? height * imageRatio : width;

            // Matches object-position: right center.
            const x = width - drawWidth;
            const y = (height - drawHeight) / 2;

            ctx.clearRect(0, 0, width, height);
            ctx.drawImage(image, x, y, drawWidth, drawHeight);
        };

        const createBuffers = (size) => ({
            previous: new Float32Array(size.width * size.height),
            current: new Float32Array(size.width * size.height),
        });

        const disturbWater = ({
            buffer,
            size,
            origin,
            radius,
            strength
        }) => {
            const radiusSquared = radius * radius;
            const minX = clamp(origin.x - radius, 1, size.width - 2);
            const maxX = clamp(origin.x + radius, 1, size.width - 2);
            const minY = clamp(origin.y - radius, 1, size.height - 2);
            const maxY = clamp(origin.y + radius, 1, size.height - 2);

            for (let y = minY; y <= maxY; y += 1) {
                for (let x = minX; x <= maxX; x += 1) {
                    const dx = x - origin.x;
                    const dy = y - origin.y;
                    const distanceSquared = dx * dx + dy * dy;

                    if (distanceSquared > radiusSquared) {
                        continue;
                    }

                    const falloff = 1 - distanceSquared / radiusSquared;
                    const index = y * size.width + x;

                    buffer[index] += strength * falloff;
                }
            }
        };

        const evolveWater = ({
            previous,
            current,
            size,
            damping
        }) => {
            const {
                width,
                height
            } = size;

            for (let y = 1; y < height - 1; y += 1) {
                for (let x = 1; x < width - 1; x += 1) {
                    const index = y * width + x;

                    const value =
                        (previous[index - 1] +
                            previous[index + 1] +
                            previous[index - width] +
                            previous[index + width]) /
                        2 -
                        current[index];

                    current[index] = value * damping;
                }
            }

            return {
                previous: current,
                current: previous,
            };
        };

        const getRadialMaskAmount = ({
            x,
            y,
            origin,
            maskRadius,
            maskFeather
        }) => {
            const dx = x - origin.x;
            const dy = y - origin.y;
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance <= maskRadius) {
                return 0;
            }

            if (distance >= maskRadius + maskFeather) {
                return 1;
            }

            const progress = (distance - maskRadius) / maskFeather;

            // Smoothstep easing for a soft fade-in.
            return progress * progress * (3 - 2 * progress);
        };

        const renderWater = ({
            ctx,
            sourceData,
            outputData,
            water,
            size,
            origin,
            config
        }) => {
            const {
                width,
                height,
                dpr,
                scale
            } = size;
            const source = sourceData.data;
            const output = outputData.data;

            output.set(source);

            const scaledMaskRadius = config.maskRadius * scale * dpr;
            const scaledMaskFeather = config.maskFeather * scale * dpr;

            for (let y = 1; y < height - 1; y += 1) {
                for (let x = 1; x < width - 1; x += 1) {
                    const maskAmount = getRadialMaskAmount({
                        x,
                        y,
                        origin,
                        maskRadius: scaledMaskRadius,
                        maskFeather: scaledMaskFeather,
                    });

                    if (maskAmount <= 0) {
                        continue;
                    }

                    const index = y * width + x;

                    const horizontalDelta = water[index - 1] - water[index + 1];
                    const verticalDelta = water[index - width] - water[index + width];

                    const sourceX = clamp(
                        Math.round(x + horizontalDelta * config.refraction * maskAmount),
                        0,
                        width - 1
                    );

                    const sourceY = clamp(
                        Math.round(y + verticalDelta * config.refraction * maskAmount),
                        0,
                        height - 1
                    );

                    const sourceIndex = (sourceY * width + sourceX) * 4;
                    const outputIndex = index * 4;

                    const highlight = clamp(1 + water[index] * 0.0035 * maskAmount, 0.82, 1.22);

                    output[outputIndex] = clamp(source[sourceIndex] * highlight, 0, 255);
                    output[outputIndex + 1] = clamp(source[sourceIndex + 1] * highlight, 0, 255);
                    output[outputIndex + 2] = clamp(source[sourceIndex + 2] * highlight, 0, 255);
                    output[outputIndex + 3] = source[sourceIndex + 3];
                }
            }

            ctx.putImageData(outputData, 0, 0);
        };

        const setupCanvas = ({
            hero,
            canvas,
            ctx,
            image,
            config
        }) => {
            const size = getHeroSize(hero, config);

            canvas.width = size.width;
            canvas.height = size.height;
            canvas.style.width = `${size.cssWidth}px`;
            canvas.style.height = `${size.cssHeight}px`;

            drawImageCover(ctx, image, size.width, size.height);

            const sourceData = ctx.getImageData(0, 0, size.width, size.height);
            const outputData = ctx.createImageData(size.width, size.height);
            const buffers = createBuffers(size);

            return {
                size,
                sourceData,
                outputData,
                buffers,
            };
        };

        const prewarmWater = ({
            water,
            config
        }) => {
            if (!config.prewarm || config.prewarmPulses <= 0 || config.prewarmSteps <= 0) {
                return water;
            }

            const origin = getOrigin(water.size, config);
            const disturbanceRadius = Math.round(config.radius * water.size.scale * water.size.dpr);
            const stepSpacing = Math.max(1, Math.floor(config.prewarmSteps / config.prewarmPulses));

            let buffers = water.buffers;

            for (let step = 0; step < config.prewarmSteps; step += 1) {
                if (step % stepSpacing === 0) {
                    const pulseFalloff = 1 - step / config.prewarmSteps;

                    disturbWater({
                        buffer: buffers.previous,
                        size: water.size,
                        origin,
                        radius: disturbanceRadius,
                        strength: config.strength * Math.max(pulseFalloff, 0.25),
                    });
                }

                buffers = evolveWater({
                    previous: buffers.previous,
                    current: buffers.current,
                    size: water.size,
                    damping: config.damping,
                });
            }

            return {
                ...water,
                buffers,
            };
        };

        const initWaterRippleHero = async (hero) => {
            const canvas = hero.querySelector('[data-water-ripple-canvas]');
            const ctx = canvas?.getContext('2d', {
                alpha: true,
                willReadFrequently: true,
            });

            if (!canvas || !ctx) {
                return;
            }

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (prefersReducedMotion) {
                return;
            }

            try {
                const image = await loadDrawableImage(getSourceImage(hero));

                let config = getConfig(hero);
                let water = setupCanvas({
                    hero,
                    canvas,
                    ctx,
                    image,
                    config
                });

                water = prewarmWater({
                    water,
                    config
                });

                let lastPulse = performance.now();
                let lastFrame = 0;
                let animationFrame = null;

                renderWater({
                    ctx,
                    sourceData: water.sourceData,
                    outputData: water.outputData,
                    water: water.buffers.previous,
                    size: water.size,
                    origin: getOrigin(water.size, config),
                    config,
                });

                hero.classList.add('is-water-ready');

                const reset = () => {
                    config = getConfig(hero);
                    water = setupCanvas({
                        hero,
                        canvas,
                        ctx,
                        image,
                        config
                    });
                    water = prewarmWater({
                        water,
                        config
                    });
                    lastPulse = performance.now();
                    lastFrame = 0;
                };

                const animate = (now) => {
                    config = getConfig(hero);

                    const frameInterval = 1000 / config.maxFps;

                    if (now - lastFrame < frameInterval) {
                        animationFrame = requestAnimationFrame(animate);
                        return;
                    }

                    lastFrame = now;

                    if (!lastPulse || now - lastPulse >= config.cadence) {
                        const origin = getOrigin(water.size, config);
                        const disturbanceRadius = Math.round(config.radius * water.size.scale * water.size.dpr);

                        disturbWater({
                            buffer: water.buffers.previous,
                            size: water.size,
                            origin,
                            radius: disturbanceRadius,
                            strength: config.strength,
                        });

                        lastPulse = now;
                    }

                    water.buffers = evolveWater({
                        previous: water.buffers.previous,
                        current: water.buffers.current,
                        size: water.size,
                        damping: config.damping,
                    });

                    renderWater({
                        ctx,
                        sourceData: water.sourceData,
                        outputData: water.outputData,
                        water: water.buffers.previous,
                        size: water.size,
                        origin: getOrigin(water.size, config),
                        config,
                    });

                    animationFrame = requestAnimationFrame(animate);
                };

                const start = () => {
                    if (!animationFrame) {
                        animationFrame = requestAnimationFrame(animate);
                    }
                };

                const stop = () => {
                    if (!animationFrame) {
                        return;
                    }

                    cancelAnimationFrame(animationFrame);
                    animationFrame = null;
                };

                const handleResize = () => reset();

                const handleVisibilityChange = () => {
                    if (document.hidden) {
                        stop();
                        return;
                    }

                    reset();
                    start();
                };

                window.addEventListener('resize', handleResize);
                document.addEventListener('visibilitychange', handleVisibilityChange);

                start();
            } catch (error) {
                console.warn('[water-ripple-hero]', error);
            }
        };

        document.querySelectorAll('[data-water-ripple-hero]').forEach(initWaterRippleHero);
    })();
</script>