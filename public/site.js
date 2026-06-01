(() => {
    'use strict';

    document.documentElement.classList.add('js-enabled');

    const $ = selector => document.querySelector(selector);
    const $$ = selector => Array.from(document.querySelectorAll(selector));
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
    const wrapIndex = (index, length) => {
        if (length <= 0) {
            return 0;
        }

        return ((index % length) + length) % length;
    };

    let closeNav = () => {};
    let closeLightbox = () => {};

    const sendActivity = (eventKey, label = '', details = '') => {
        const activityUrl = document.body?.dataset.activityUrl || '';
        if (!activityUrl) {
            return;
        }

        const payload = {
            event: eventKey,
            page: document.body?.dataset.activityPage || 'unknown',
            label,
            details,
        };

        const body = JSON.stringify(payload);

        if (navigator.sendBeacon) {
            const beaconSent = navigator.sendBeacon(
                activityUrl,
                new Blob([body], { type: 'application/json' })
            );

            if (beaconSent) {
                return;
            }
        }

        fetch(activityUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body,
            keepalive: true,
        }).catch(() => {});
    };

    const initHeaderState = () => {
        const header = $('#site-header');
        const toTop = $('#to-top');

        const updateHeaderState = () => {
            const scrolled = window.scrollY > 120;
            header?.classList.toggle('scrolled', scrolled);
            toTop?.classList.toggle('show', scrolled);
        };

        if (header || toTop) {
            updateHeaderState();
            window.addEventListener('scroll', updateHeaderState, { passive: true });
        }

        toTop?.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
            });
        });
    };

    const initNavigation = () => {
        const navToggle = $('#nav-toggle');
        const navPanel = $('#primary-nav');
        const navBackdrop = $('#nav-backdrop');

        const setNavState = open => {
            if (!navPanel || !navToggle) {
                return;
            }

            navPanel.classList.toggle('is-open', open);
            navToggle.classList.toggle('is-open', open);
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            navBackdrop?.classList.toggle('is-open', open);
            document.body.classList.toggle('menu-open', open);
        };

        closeNav = () => setNavState(false);
        const openNav = () => setNavState(true);
        const isNavOpen = () => navPanel?.classList.contains('is-open') ?? false;

        navToggle?.addEventListener('click', () => {
            if (isNavOpen()) {
                closeNav();
                return;
            }

            openNav();
        });

        navBackdrop?.addEventListener('click', closeNav);

        navPanel?.addEventListener('click', event => {
            const link = event.target.closest('.nav-link');
            if (!link) {
                return;
            }

            const href = link.getAttribute('href') || '';

            // Internal anchor (#section): handle with scrollIntoView
            if (href.startsWith('#')) {
                const target = document.getElementById(href.slice(1));
                if (target) {
                    event.preventDefault();
                    closeNav();
                    target.scrollIntoView({
                        behavior: prefersReducedMotion ? 'auto' : 'smooth',
                        block: 'start',
                    });

                    if (typeof target.focus === 'function') {
                        target.focus({ preventScroll: true });
                    }
                    return;
                }
            }

            // External/normal links (home.php, tentang.php, etc):
            // close nav, then force navigation to guarantee on mobile.
            closeNav();

            if (href && !href.startsWith('#')) {
                window.location.href = href;
            }
        });
    };

    const initScrollSpy = () => {
        const navSpyLinks = $$('#primary-nav .nav-link[href^="#"]');
        const sections = $$('main > section[id]');

        if (!navSpyLinks.length || !sections.length) {
            return;
        }

        const setActiveSection = sectionId => {
            navSpyLinks.forEach(link => {
                const isActive = link.getAttribute('href') === `#${sectionId}`;
                link.classList.toggle('active', isActive);

                if (isActive) {
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setActiveSection(entry.target.id);
                    }
                });
            }, {
                threshold: 0.25,
                rootMargin: '-20% 0px -45% 0px',
            });

            sections.forEach(section => observer.observe(section));
        } else {
            const updateActiveFromScroll = () => {
                const checkpoint = window.scrollY + 160;
                let activeSectionId = sections[0].id;

                sections.forEach(section => {
                    if (section.offsetTop <= checkpoint) {
                        activeSectionId = section.id;
                    }
                });

                setActiveSection(activeSectionId);
            };

            updateActiveFromScroll();
            window.addEventListener('scroll', updateActiveFromScroll, { passive: true });
            window.addEventListener('resize', updateActiveFromScroll);
        }
    };

    const initRevealAnimations = () => {
        const revealElements = $$('.reveal');
        if (!revealElements.length) {
            return;
        }

        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            revealElements.forEach(element => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        revealElements.forEach(element => observer.observe(element));
    };

    const initGallery = () => {
        const galleryGrid = $('.gallery-grid');
        const galleryItems = $$('.gallery-item');
        const filterButtons = $$('.filter-btn');
        const galleryTriggers = $$('.gallery-trigger');
        const showMoreBtn = $('#gallery-show-more');
        const lightbox = $('#lightbox');
        const lightboxImage = $('#lightbox-image');
        const lightboxCaption = $('#lightbox-caption');
        const initialLimit = galleryGrid ? Number(galleryGrid.dataset.initialLimit || '0') : 0;

        if (!galleryItems.length && !lightbox) {
            return;
        }

        let activeFilter = 'all';
        let galleryExpanded = initialLimit <= 0 || galleryItems.length <= initialLimit;

        const applyGalleryState = () => {
            galleryItems.forEach((item, index) => {
                const matchesFilter = activeFilter === 'all' || item.dataset.service === activeFilter;
                const beyondLimit = !galleryExpanded && initialLimit > 0 && index >= initialLimit;
                item.hidden = !matchesFilter || beyondLimit;
            });

            if (showMoreBtn) {
                const hasMoreItems = initialLimit > 0 && galleryItems.length > initialLimit;
                showMoreBtn.hidden = !hasMoreItems;

                if (hasMoreItems) {
                    showMoreBtn.textContent = galleryExpanded ? 'Tampilkan Lebih Sedikit' : 'Lihat Lainnya';
                }
            }
        };

        closeLightbox = () => {
            if (!lightbox) {
                return;
            }

            lightbox.classList.remove('is-open');
            lightbox.hidden = true;
            document.body.classList.remove('lightbox-open');

            if (lightboxImage) {
                lightboxImage.src = '';
                lightboxImage.alt = '';
            }

            if (lightboxCaption) {
                lightboxCaption.textContent = '';
            }
        };

        const openLightbox = (src, title = '') => {
            if (!lightbox || !lightboxImage || !src) {
                return;
            }

            lightbox.hidden = false;
            lightbox.classList.add('is-open');
            lightboxImage.src = src;
            lightboxImage.alt = title ? `Preview ${title}` : 'Preview gambar';

            if (lightboxCaption) {
                lightboxCaption.textContent = title;
            }

            document.body.classList.add('lightbox-open');
        };

        filterButtons.forEach(btn => {
            const isActive = (btn.dataset.filter || 'all') === activeFilter;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');

            btn.addEventListener('click', () => {
                activeFilter = btn.dataset.filter || 'all';

                filterButtons.forEach(other => {
                    const isActive = other === btn;
                    other.classList.toggle('is-active', isActive);
                    other.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                applyGalleryState();
                sendActivity('gallery_filter', btn.textContent.trim(), activeFilter);
            });
        });

        galleryTriggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                const imageSrc = trigger.dataset.image || '';
                const title = trigger.dataset.title || '';
                const service = trigger.closest('.gallery-item')?.dataset.service || '';

                openLightbox(imageSrc, title);
                sendActivity('gallery_preview', title, service);
            });
        });

        showMoreBtn?.addEventListener('click', () => {
            galleryExpanded = !galleryExpanded;
            applyGalleryState();
            sendActivity(
                'gallery_show_more',
                showMoreBtn.textContent.trim(),
                galleryExpanded ? 'expanded' : 'collapsed'
            );
        });

        $$('[data-close-lightbox]').forEach(btn => {
            btn.addEventListener('click', closeLightbox);
        });

        applyGalleryState();
    };

    // WhatsApp tracking
    $$('a[href*="wa.me"]').forEach(link => {
        link.addEventListener('click', () => sendActivity('whatsapp_click', link.textContent.trim()));
    });



    const initHeroSlider = () => {
        const heroSlider = $('#hero-slider');
        if (!heroSlider) {
            return;
        }

        const allSlides = $$('[data-hero-slide]');
        const dots = $$('[data-hero-dot]');
        const track = $('[data-hero-track]');

        if (!allSlides.length || !track) {
            return;
        }

        const autoplayDelay = clamp(Number(heroSlider.dataset.autoplayMs || '5000'), 3000, 15000);
        const resumeDelay = 4000;
        const swipeThresholdPx = 50;

        const realSlideCount = dots.length;
        const hasClones = realSlideCount > 1 && allSlides.length > realSlideCount;

        // Jika ada clone, start di index 1 (slide real pertama, setelah clone terakhir)
        let currentIndex = hasClones ? 1 : 0;
        if (hasClones) {
            track.style.transition = 'none';
            track.style.transform = `translateX(${-currentIndex * 100}%)`;
        }

        let autoplayTimer = 0;
        let resumeTimer = 0;
        let dragging = false;
        let pointerId = null;
        let dragStartX = 0;
        let dragCurrentX = 0;
        let dragStartTranslate = -currentIndex * 100;

        const getRealIndex = (slideIndex) => {
            const slide = allSlides[slideIndex];
            if (!slide) return 0;
            const realIndex = slide.dataset.realIndex;
            return realIndex !== undefined ? parseInt(realIndex, 10) : slideIndex;
        };

        const stopAutoplay = () => {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = 0;
            }
        };

        const cancelResumeTimer = () => {
            if (resumeTimer) {
                clearTimeout(resumeTimer);
                resumeTimer = 0;
            }
        };

        const pauseAutoplay = () => {
            stopAutoplay();
            cancelResumeTimer();
        };

        const queueAutoplayResume = (delay = resumeDelay) => {
            cancelResumeTimer();
            resumeTimer = window.setTimeout(() => {
                resumeTimer = 0;
                startAutoplay();
            }, delay);
        };

        const renderSlide = (animate = true) => {
            track.style.transition = animate ? '' : 'none';
            track.style.transform = `translateX(${-currentIndex * 100}%)`;

            allSlides.forEach((slide, index) => {
                slide.classList.toggle('is-active', index === currentIndex);
            });

            const activeRealIndex = getRealIndex(currentIndex);
            dots.forEach((dot) => {
                const dotRealIndex = parseInt(dot.dataset.realIndex || '0', 10);
                const isActive = dotRealIndex === activeRealIndex;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            heroSlider.setAttribute('aria-label', `Slide ${activeRealIndex + 1} dari ${realSlideCount}`);
        };

        const checkInfiniteLoop = () => {
            if (!hasClones) return;

            // Jika di clone terakhir (setelah slide real terakhir), lompat ke slide real pertama
            if (currentIndex >= allSlides.length - 1) {
                currentIndex = 1;
                dragStartTranslate = -currentIndex * 100;
                renderSlide(false);
                return;
            }

            // Jika di clone pertama (sebelum slide real pertama), lompat ke slide real terakhir
            if (currentIndex <= 0) {
                currentIndex = allSlides.length - 2;
                dragStartTranslate = -currentIndex * 100;
                renderSlide(false);
                return;
            }
        };

        const goToSlide = (index, { userInitiated = true, animate = true } = {}) => {
            currentIndex = index;
            dragStartTranslate = -currentIndex * 100;
            renderSlide(animate);

            // Setelah transisi selesai, cek infinite loop
            if (hasClones) {
                const transitionDuration = animate ? 600 : 0;
                window.setTimeout(() => {
                    checkInfiniteLoop();
                }, transitionDuration);
            }

            if (userInitiated) {
                pauseAutoplay();
                queueAutoplayResume();
            }
        };

        const advanceSlide = (delta = 1, options = {}) => {
            goToSlide(currentIndex + delta, options);
        };

        const startAutoplay = () => {
            stopAutoplay();

            if (realSlideCount <= 1) {
                return;
            }

            autoplayTimer = window.setInterval(() => {
                advanceSlide(1, { userInitiated: false });
            }, autoplayDelay);
        };

        const updateDragPosition = () => {
            const sliderWidth = heroSlider.getBoundingClientRect().width || 1;
            const deltaPercent = ((dragCurrentX - dragStartX) / sliderWidth) * 100;
            track.style.transform = `translateX(${dragStartTranslate + deltaPercent}%)`;
        };

        const beginDrag = event => {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            if (event.target.closest('[data-hero-dot], button, a, input, textarea, select, label')) {
                return;
            }

            dragging = true;
            pointerId = event.pointerId;
            dragStartX = event.clientX;
            dragCurrentX = event.clientX;
            dragStartTranslate = -currentIndex * 100;

            pauseAutoplay();
            heroSlider.classList.add('is-dragging');
            track.style.transition = 'none';

            if (heroSlider.setPointerCapture) {
                heroSlider.setPointerCapture(pointerId);
            }
        };

        const dragMove = event => {
            if (!dragging || event.pointerId !== pointerId) {
                return;
            }

            dragCurrentX = event.clientX;
            updateDragPosition();
        };

        const finishDrag = event => {
            if (!dragging || event.pointerId !== pointerId) {
                return;
            }

            dragging = false;
            heroSlider.classList.remove('is-dragging');

            if (heroSlider.releasePointerCapture && heroSlider.hasPointerCapture && heroSlider.hasPointerCapture(pointerId)) {
                heroSlider.releasePointerCapture(pointerId);
            }

            const deltaPx = dragCurrentX - dragStartX;
            if (deltaPx <= -swipeThresholdPx) {
                goToSlide(currentIndex + 1, { userInitiated: false });
            } else if (deltaPx >= swipeThresholdPx) {
                goToSlide(currentIndex - 1, { userInitiated: false });
            } else {
                renderSlide(true);
            }

            queueAutoplayResume();
            pointerId = null;
        };

        const cancelDrag = () => {
            if (!dragging) {
                return;
            }

            dragging = false;
            heroSlider.classList.remove('is-dragging');
            renderSlide(true);
            queueAutoplayResume();
            pointerId = null;
        };

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const targetRealIndex = parseInt(dot.dataset.realIndex || '0', 10);
                // Map real index ke index yang sebenarnya (tambah 1 jika ada clone)
                const targetIndex = hasClones ? targetRealIndex + 1 : targetRealIndex;
                goToSlide(targetIndex);
            });
        });

        heroSlider.addEventListener('pointerdown', beginDrag);
        heroSlider.addEventListener('pointermove', dragMove);
        heroSlider.addEventListener('pointerup', finishDrag);
        heroSlider.addEventListener('pointercancel', cancelDrag);
        heroSlider.addEventListener('mouseenter', pauseAutoplay);
        heroSlider.addEventListener('mouseleave', () => {
            if (!dragging) {
                queueAutoplayResume();
            }
        });
        heroSlider.addEventListener('focusin', pauseAutoplay);
        heroSlider.addEventListener('focusout', () => {
            if (!dragging) {
                queueAutoplayResume();
            }
        });
        heroSlider.addEventListener('keydown', event => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goToSlide(currentIndex - 1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                goToSlide(currentIndex + 1);
            }
        });
        heroSlider.addEventListener('dragstart', event => event.preventDefault());

        renderSlide(false);
        startAutoplay();
    };

    initHeaderState();
    initNavigation();
    initScrollSpy();
    initRevealAnimations();
    initGallery();
    initHeroSlider();

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeNav();
            closeLightbox();
        }
    });
})();
