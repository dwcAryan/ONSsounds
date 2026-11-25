/**
 * ONS SOUND - Vanilla JavaScript Core
 */

document.addEventListener('DOMContentLoaded', function () {
    // Dynamic Header Height Sync for Fixed Navbar
    function syncHeaderHeight() {
        const header = document.querySelector('.site-header');
        if (header) {
            const h = header.offsetHeight;
            if (h > 0) {
                document.documentElement.style.setProperty('--header-height', h + 'px');
            }
        }
    }
    syncHeaderHeight();
    window.addEventListener('resize', syncHeaderHeight);
    window.addEventListener('orientationchange', syncHeaderHeight);

    // Jump To Top Button with Border Scroll Progress
    const scrollTopBtn = document.querySelector('.scroll-top-btn');
    const scrollProgressBar = document.getElementById('scrollProgressBar');

    if (scrollTopBtn) {
        let pathLength = 0;
        if (scrollProgressBar && typeof scrollProgressBar.getTotalLength === 'function') {
            pathLength = scrollProgressBar.getTotalLength();
            scrollProgressBar.style.strokeDasharray = `${pathLength} ${pathLength}`;
            scrollProgressBar.style.strokeDashoffset = `${pathLength}`;
        }

        function updateScrollTopProgress() {
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;

            // Only appear when user scrolls a little bit (e.g. > 150px)
            if (scrollTop > 150) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }

            // Update border progress stroke
            if (scrollProgressBar && pathLength > 0 && docHeight > 0) {
                const scrollFraction = Math.min(Math.max(scrollTop / docHeight, 0), 1);
                const drawLength = pathLength * scrollFraction;
                scrollProgressBar.style.strokeDashoffset = `${pathLength - drawLength}`;
            }
        }

        scrollTopBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', updateScrollTopProgress, { passive: true });
        updateScrollTopProgress();
    }

    // =========================================================================
    // Product Gallery: 5s Auto-Slide, Hover Pause, Hover Zoom, Thumbnails & Full View Lightbox
    // =========================================================================
    const mainImgBox = document.getElementById('productMainImgBox');
    const mainImg = document.getElementById('mainProductImg');
    const thumbsContainer = document.getElementById('galleryThumbsContainer');
    const openFullViewBtn = document.getElementById('openFullViewBtn');
    const lightboxModalEl = document.getElementById('productLightboxModal');
    const lightboxImg = document.getElementById('lightboxImg');

    if (mainImgBox && mainImg) {
        const thumbs = thumbsContainer ? thumbsContainer.querySelectorAll('.gallery-thumb') : [];
        let currentIndex = 0;
        let slideTimer = null;
        let isHovered = false;

        // Collect all image sources
        const images = [];
        if (thumbs.length > 0) {
            thumbs.forEach(t => images.push(t.getAttribute('data-src')));
        } else {
            images.push(mainImg.src);
        }

        // Switch active image function
        function switchImage(index, animate = true) {
            if (images.length === 0) return;
            currentIndex = (index + images.length) % images.length;
            const targetSrc = images[currentIndex];

            if (animate) {
                mainImg.style.opacity = '0.35';
                setTimeout(() => {
                    mainImg.src = targetSrc;
                    mainImg.style.opacity = '1';
                }, 150);
            } else {
                mainImg.src = targetSrc;
            }

            // Update lightbox image & counter if modal is open or prepared
            if (lightboxImg) {
                lightboxImg.src = targetSrc;
            }
            const counterEl = document.getElementById('lightboxCurrentIndex');
            if (counterEl) {
                counterEl.textContent = currentIndex + 1;
            }

            // Update active thumbnail
            thumbs.forEach(t => {
                const idx = parseInt(t.getAttribute('data-index')) || 0;
                if (idx === currentIndex) {
                    t.classList.add('active');
                } else {
                    t.classList.remove('active');
                }
            });
        }

        // 1. Auto-slide every 5 seconds (5000ms)
        function startAutoSlide() {
            stopAutoSlide();
            if (images.length <= 1) return;
            slideTimer = setInterval(() => {
                if (!isHovered) {
                    switchImage(currentIndex + 1);
                }
            }, 5000);
        }

        function stopAutoSlide() {
            if (slideTimer) {
                clearInterval(slideTimer);
                slideTimer = null;
            }
        }

        startAutoSlide();

        // 2. Pause on hover over active image box
        mainImgBox.addEventListener('mouseenter', function () {
            isHovered = true;
            stopAutoSlide();
        });

        mainImgBox.addEventListener('mouseleave', function () {
            isHovered = false;
            // Reset zoom transform smoothly
            mainImg.style.transform = 'scale(1)';
            mainImg.style.transformOrigin = 'center center';
            startAutoSlide();
        });

        // 3. Zoom into image on hover of active img
        mainImgBox.addEventListener('mousemove', function (e) {
            const rect = mainImgBox.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;

            mainImg.style.transformOrigin = `${x}% ${y}%`;
            mainImg.style.transform = 'scale(2)'; // 2x magnification on hover
        });

        // 4. Change active image by clicking thumbnail in list
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function (e) {
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-index')) || 0;
                switchImage(idx);
                // Restart 5-second timer on manual click
                startAutoSlide();
            });
        });

        // 5. Full View (Lightbox Modal)
        function openFullView() {
            if (lightboxImg) {
                lightboxImg.src = images[currentIndex];
            }
            if (lightboxModalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getOrCreateInstance(lightboxModalEl);
                modal.show();
            }
        }

        if (openFullViewBtn) {
            openFullViewBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                openFullView();
            });
        }

        // Clicking the main image box also triggers Full View (unless dragging)
        mainImgBox.addEventListener('click', function (e) {
            if (e.target.closest('#openFullViewBtn')) return;
            openFullView();
        });

        // Lightbox previous and next buttons
        const prevBtn = lightboxModalEl ? lightboxModalEl.querySelector('.lightbox-prev') : null;
        const nextBtn = lightboxModalEl ? lightboxModalEl.querySelector('.lightbox-next') : null;

        if (prevBtn) {
            prevBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                switchImage(currentIndex - 1, false);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                switchImage(currentIndex + 1, false);
            });
        }

        // Keyboard arrow navigation in lightbox
        document.addEventListener('keydown', function (e) {
            if (lightboxModalEl && lightboxModalEl.classList.contains('show')) {
                if (e.key === 'ArrowLeft') {
                    switchImage(currentIndex - 1, false);
                } else if (e.key === 'ArrowRight') {
                    switchImage(currentIndex + 1, false);
                }
            }
        });
    }

    // Sticky Add-to-Cart Bar on Product Details
    const detailAddToCartBtn = document.getElementById('detailAddToCartForm');
    const stickyCartBar = document.querySelector('.sticky-cart-bar');
    if (detailAddToCartBtn && stickyCartBar) {
        window.addEventListener('scroll', function () {
            const rect = detailAddToCartBtn.getBoundingClientRect();
            if (rect.bottom < 0) {
                stickyCartBar.classList.add('visible');
                document.body.classList.add('has-sticky-cart');
            } else {
                stickyCartBar.classList.remove('visible');
                document.body.classList.remove('has-sticky-cart');
            }
        });
    }

    // Quantity Selectors
    document.querySelectorAll('.qty-control').forEach(function (control) {
        const minusBtn = control.querySelector('.qty-minus');
        const plusBtn = control.querySelector('.qty-plus');
        const input = control.querySelector('.qty-input');

        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', function () {
                let val = parseInt(input.value) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });

            plusBtn.addEventListener('click', function () {
                let val = parseInt(input.value) || 1;
                let max = parseInt(input.getAttribute('max')) || 999;
                if (val < max) {
                    input.value = val + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });

    function promptUserLogin(message) {
        showToast('Please Log In', message || 'Please log in to continue.');
        const loginModalEl = document.getElementById('loginModal');
        if (loginModalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(loginModalEl);
            modal.show();
        } else {
            window.location.href = 'login.php';
        }
    }

    // Detail Product Add to Cart Form
    if (detailAddToCartBtn) {
        detailAddToCartBtn.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const orig = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding...';
                submitBtn.disabled = true;
            }

            const formData = new FormData(this);
            fetch('cart_action.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.innerHTML = orig;
                    submitBtn.disabled = false;
                }
                if (data.require_login) {
                    promptUserLogin(data.message);
                    return;
                }
                if (data.success) {
                    updateCartBadge(data.cart_count);
                    showToast('Success', data.message || 'Product added to cart!');
                } else {
                    showToast('Error', data.message || 'Failed to add to cart.');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.innerHTML = orig;
                    submitBtn.disabled = false;
                }
                detailAddToCartBtn.submit();
            });
        });
    }

    // Sticky Cart Bar Form
    const stickyForm = document.querySelector('.sticky-cart-bar form');
    if (stickyForm) {
        stickyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const orig = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                btn.disabled = true;
            }

            const formData = new FormData(this);
            fetch('cart_action.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (btn) {
                    btn.innerHTML = orig;
                    btn.disabled = false;
                }
                if (data.require_login) {
                    promptUserLogin(data.message);
                    return;
                }
                if (data.success) {
                    updateCartBadge(data.cart_count);
                    showToast('Success', data.message || 'Product added to cart!');
                } else {
                    showToast('Error', data.message || 'Failed to add to cart.');
                }
            })
            .catch(err => {
                if (btn) {
                    btn.innerHTML = orig;
                    btn.disabled = false;
                }
                stickyForm.submit();
            });
        });
    }

    // AJAX Add to Cart Buttons
    document.querySelectorAll('.ajax-add-to-cart').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
            const quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            this.disabled = true;

            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('cart_action.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    if (data.require_login) {
                        promptUserLogin(data.message);
                        return;
                    }
                    if (data.success) {
                        updateCartBadge(data.cart_count);
                        showToast('Success', data.message || 'Product added to cart!');
                    } else {
                        showToast('Error', data.message || 'Failed to add product to cart.');
                    }
                })
                .catch(err => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    showToast('Error', 'An error occurred while adding to cart.');
                });
        });
    });

    // AJAX Toggle Wishlist
    document.querySelectorAll('.ajax-toggle-wishlist').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');

            const formData = new FormData();
            formData.append('action', 'toggle_wishlist');
            formData.append('product_id', productId);

            fetch('wishlist_action.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.require_login) {
                        promptUserLogin(data.message);
                        return;
                    }
                    if (data.success) {
                        updateWishlistBadge(data.wishlist_count);
                        if (data.in_wishlist) {
                            this.classList.add('text-danger');
                            this.querySelector('i')?.classList.replace('bi-heart', 'bi-heart-fill');
                        } else {
                            this.classList.remove('text-danger');
                            this.querySelector('i')?.classList.replace('bi-heart-fill', 'bi-heart');
                        }
                        showToast('Wishlist', data.message);
                    } else {
                        showToast('Notice', data.message || 'Please log in to manage your wishlist.');
                    }
                })
                .catch(err => {
                    showToast('Error', 'Could not update wishlist.');
                });
        });
    });

    // Interactive Clickable Star Rating for Customer Review Form (Ref: media_1795575372461_d53781f8.png)
    const starBox = document.getElementById('interactiveStarRating');
    if (starBox) {
        const stars = starBox.querySelectorAll('.star-item');
        const ratingInput = document.getElementById('reviewRatingInput');
        const ratingLabel = document.getElementById('ratingTextDisplay');
        const ratingTexts = {
            1: '1 Star — Poor',
            2: '2 Stars — Fair',
            3: '3 Stars — Average',
            4: '4 Stars — Good',
            5: '5 Stars — Excellent'
        };

        function setDisplayRating(val) {
            stars.forEach(star => {
                const sVal = parseInt(star.getAttribute('data-rating')) || 1;
                if (sVal <= val) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
            if (ratingLabel) {
                ratingLabel.textContent = ratingTexts[val] || (val + ' Stars');
            }
        }

        stars.forEach(star => {
            const val = parseInt(star.getAttribute('data-rating')) || 1;

            star.addEventListener('mouseenter', function () {
                stars.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-rating')) || 1;
                    if (sVal <= val) {
                        s.classList.add('hovered');
                    } else {
                        s.classList.remove('hovered');
                    }
                });
                if (ratingLabel) {
                    ratingLabel.textContent = ratingTexts[val] || (val + ' Stars');
                }
            });

            star.addEventListener('click', function () {
                const currentVal = parseInt(this.getAttribute('data-rating')) || 5;
                if (ratingInput) {
                    ratingInput.value = currentVal;
                }
                setDisplayRating(currentVal);
            });
        });

        starBox.querySelector('.stars-box')?.addEventListener('mouseleave', function () {
            stars.forEach(s => s.classList.remove('hovered'));
            const currentSelected = parseInt(ratingInput?.value) || 5;
            setDisplayRating(currentSelected);
        });

        // Initialize default state
        setDisplayRating(parseInt(ratingInput?.value) || 5);
    }

    // Dynamic Badge Updaters
    function updateCartBadge(count) {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = count;
        }
    }

    function updateWishlistBadge(count) {
        const badge = document.querySelector('.wishlist-badge');
        if (badge) {
            badge.textContent = count;
        }
    }

    // Modal Switcher: Login <-> Register (Ref: media_1795532886934_fd7c5a3a.png, media_1795532856822_6357e814.jpg)
    document.querySelectorAll('.switch-to-register').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const loginEl = document.getElementById('loginModal');
            const regEl = document.getElementById('registerModal');
            if (loginEl && regEl && window.bootstrap) {
                const loginModal = bootstrap.Modal.getInstance(loginEl) || new bootstrap.Modal(loginEl);
                loginModal.hide();
                setTimeout(() => {
                    const regModal = bootstrap.Modal.getInstance(regEl) || new bootstrap.Modal(regEl);
                    regModal.show();
                }, 300);
            }
        });
    });

    document.querySelectorAll('.switch-to-login').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const loginEl = document.getElementById('loginModal');
            const regEl = document.getElementById('registerModal');
            if (loginEl && regEl && window.bootstrap) {
                const regModal = bootstrap.Modal.getInstance(regEl) || new bootstrap.Modal(regEl);
                regModal.hide();
                setTimeout(() => {
                    const loginModal = bootstrap.Modal.getInstance(loginEl) || new bootstrap.Modal(loginEl);
                    loginModal.show();
                }, 300);
            }
        });
    });

    // Password Visibility Toggle
    document.querySelectorAll('.toggle-password-visibility').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const input = this.closest('.position-relative')?.querySelector('.auth-password-input');
            const icon = this.querySelector('i');
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            }
        });
    });

    // Dynamic Account State Updater (No manual page refresh needed)
    function updateAccountHeaderUI(user) {
        if (!user) return;

        // 1. Hide auth modals cleanly
        const loginEl = document.getElementById('loginModal');
        const regEl = document.getElementById('registerModal');
        if (loginEl && window.bootstrap) {
            const m = bootstrap.Modal.getInstance(loginEl);
            if (m) m.hide();
        }
        if (regEl && window.bootstrap) {
            const m = bootstrap.Modal.getInstance(regEl);
            if (m) m.hide();
        }

        // 2. Update Desktop Header User Action
        const headerActions = document.querySelector('.header-right.header-actions');
        if (headerActions) {
            const existingBtn = headerActions.querySelector('button[data-bs-target="#loginModal"]');
            if (existingBtn) {
                const userDropdown = document.createElement('div');
                userDropdown.className = 'dropdown';
                const adminLink = user.role === 'admin' ? '<li><a class="dropdown-item py-2 text-danger fw-semibold" href="admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li><li><hr class="dropdown-divider my-1"></li>' : '';
                userDropdown.innerHTML = `
                    <button class="header-action-btn dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" title="Account" aria-label="Account">
                        <i class="bi bi-person"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userMenuButton" style="min-width: 200px;">
                        <li class="px-3 py-2 border-bottom">
                            <small class="text-muted d-block">Signed in as</small>
                            <strong class="d-block text-truncate">${user.name}</strong>
                        </li>
                        ${adminLink}
                        <li><a class="dropdown-item py-2" href="orders.php"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item py-2" href="addresses.php"><i class="bi bi-geo-alt me-2"></i>My Addresses</a></li>
                        <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-gear me-2"></i>Account Details</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item py-2 text-muted" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a></li>
                    </ul>
                `;
                existingBtn.replaceWith(userDropdown);
            }
        }

        // 3. Update Mobile Menu Offcanvas User Action
        const mobileAuthButtons = document.querySelector('#mobileNavOffcanvas .mt-2.pt-2.border-top.d-flex');
        if (mobileAuthButtons) {
            const mobileUserSection = document.createElement('div');
            mobileUserSection.className = 'mt-2 pt-2 border-top';
            mobileUserSection.innerHTML = `
                <small class="text-muted d-block mb-1">Signed in as <strong>${user.name}</strong></small>
                <a href="orders.php" class="nav-link py-2 text-dark"><i class="bi bi-box-seam me-2"></i>My Orders</a>
                <a href="addresses.php" class="nav-link py-2 text-dark"><i class="bi bi-geo-alt me-2"></i>My Addresses</a>
                <a href="profile.php" class="nav-link py-2 text-dark"><i class="bi bi-gear me-2"></i>Account Details</a>
                <a href="logout.php" class="nav-link py-2 text-muted"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a>
            `;
            mobileAuthButtons.replaceWith(mobileUserSection);
        }

        // Clear input values
        document.querySelectorAll('#ajaxLoginForm input[type="password"]').forEach(i => i.value = '');
        document.querySelectorAll('#ajaxRegisterForm input[type="password"]').forEach(i => i.value = '');
    }

    // AJAX Login Handler
    const ajaxLoginForm = document.getElementById('ajaxLoginForm');
    if (ajaxLoginForm) {
        ajaxLoginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const errAlert = this.querySelector('.auth-error-alert');
            const submitBtn = this.querySelector('.auth-submit-btn');
            const originalText = submitBtn.innerHTML;

            if (errAlert) {
                errAlert.classList.add('d-none');
                errAlert.textContent = '';
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Logging in...';

            const formData = new FormData(this);

            fetch('auth_action.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (data.success) {
                        showToast('Welcome', data.message || 'Logged in successfully!');
                        updateAccountHeaderUI(data.user);

                        // If user is on an explicit auth/checkout page, redirect cleanly
                        const path = window.location.pathname;
                        if (path.includes('login.php') || path.includes('register.php') || path.includes('checkout.php')) {
                            setTimeout(() => {
                                window.location.href = data.redirect || 'index.php';
                            }, 500);
                        }
                    } else {
                        if (errAlert) {
                            errAlert.textContent = data.message || 'Login failed. Please check your credentials.';
                            errAlert.classList.remove('d-none');
                        } else {
                            showToast('Error', data.message || 'Login failed.');
                        }
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    if (errAlert) {
                        errAlert.textContent = 'A connection error occurred. Please try again.';
                        errAlert.classList.remove('d-none');
                    }
                });
        });
    }

    // AJAX Register Handler with 2-Step OTP Verification
    const ajaxRegisterForm = document.getElementById('ajaxRegisterForm');
    const ajaxRegisterOtpForm = document.getElementById('ajaxRegisterOtpForm');
    const registerModalEl = document.getElementById('registerModal');
    const registerFormStep = document.getElementById('registerFormStep');
    const registerOtpStep = document.getElementById('registerOtpStep');
    const registerModalLabel = document.getElementById('registerModalLabel');
    const registerOtpTargetEmail = document.getElementById('registerOtpTargetEmail');
    const registerOtpInput = document.getElementById('registerOtpInput');
    const btnResendRegisterOtp = document.getElementById('btnResendRegisterOtp');
    const btnBackToRegisterDetails = document.getElementById('btnBackToRegisterDetails');

    function resetRegisterModal() {
        if (registerFormStep) registerFormStep.classList.remove('d-none');
        if (registerOtpStep) registerOtpStep.classList.add('d-none');
        if (registerModalLabel) registerModalLabel.textContent = 'Register';
        if (registerOtpInput) registerOtpInput.value = '';
        const errAlert = registerModalEl ? registerModalEl.querySelector('.auth-error-alert') : null;
        if (errAlert) {
            errAlert.classList.add('d-none');
            errAlert.textContent = '';
        }
    }

    if (registerModalEl) {
        registerModalEl.addEventListener('hidden.bs.modal', resetRegisterModal);
    }

    if (btnBackToRegisterDetails) {
        btnBackToRegisterDetails.addEventListener('click', function (e) {
            e.preventDefault();
            resetRegisterModal();
        });
    }

    // Step 1: Submit Details & Request OTP
    if (ajaxRegisterForm) {
        ajaxRegisterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const errAlert = registerModalEl ? registerModalEl.querySelector('.auth-error-alert') : null;
            const submitBtn = this.querySelector('.auth-submit-btn');
            const originalText = submitBtn.innerHTML;

            if (errAlert) {
                errAlert.classList.add('d-none');
                errAlert.textContent = '';
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending code...';

            const formData = new FormData(this);

            fetch('auth_action.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (data.success && data.step === 'verify_otp') {
                        // Switch to Step 2: OTP screen
                        if (registerFormStep) registerFormStep.classList.add('d-none');
                        if (registerOtpStep) registerOtpStep.classList.remove('d-none');
                        if (registerModalLabel) registerModalLabel.textContent = 'Verify Email';
                        if (registerOtpTargetEmail) registerOtpTargetEmail.textContent = data.email || '';
                        if (registerOtpInput) {
                            registerOtpInput.value = '';
                            setTimeout(() => registerOtpInput.focus(), 150);
                        }
                        showToast('Verification Code', data.message || 'Verification code sent to your email!');
                    } else if (data.success && data.user) {
                        // Direct registration fallback
                        showToast('Account Created', data.message || 'Registered and logged in!');
                        updateAccountHeaderUI(data.user);
                        resetRegisterModal();
                    } else {
                        if (errAlert) {
                            errAlert.textContent = data.message || 'Registration failed.';
                            errAlert.classList.remove('d-none');
                        } else {
                            showToast('Error', data.message || 'Registration failed.');
                        }
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    if (errAlert) {
                        errAlert.textContent = 'A connection error occurred. Please try again.';
                        errAlert.classList.remove('d-none');
                    }
                });
        });
    }

    // Step 2: Submit OTP & Complete Registration
    if (ajaxRegisterOtpForm) {
        ajaxRegisterOtpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const errAlert = registerModalEl ? registerModalEl.querySelector('.auth-error-alert') : null;
            const submitBtn = this.querySelector('.auth-otp-submit-btn');
            const originalText = submitBtn.innerHTML;

            if (errAlert) {
                errAlert.classList.add('d-none');
                errAlert.textContent = '';
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

            const formData = new FormData(this);

            fetch('auth_action.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (data.success) {
                        showToast('Welcome', data.message || 'Account verified and created!');
                        updateAccountHeaderUI(data.user);
                        resetRegisterModal();

                        const path = window.location.pathname;
                        if (path.includes('login.php') || path.includes('register.php') || path.includes('checkout.php')) {
                            setTimeout(() => {
                                window.location.href = data.redirect || 'index.php';
                            }, 500);
                        }
                    } else {
                        if (errAlert) {
                            errAlert.textContent = data.message || 'Invalid verification code.';
                            errAlert.classList.remove('d-none');
                        } else {
                            showToast('Error', data.message || 'Invalid verification code.');
                        }
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    if (errAlert) {
                        errAlert.textContent = 'A network error occurred. Please try again.';
                        errAlert.classList.remove('d-none');
                    }
                });
        });
    }

    // Resend OTP in Register Modal
    if (btnResendRegisterOtp) {
        btnResendRegisterOtp.addEventListener('click', function (e) {
            e.preventDefault();
            const errAlert = registerModalEl ? registerModalEl.querySelector('.auth-error-alert') : null;
            btnResendRegisterOtp.textContent = 'Sending...';

            const formData = new FormData();
            formData.append('action', 'resend_register_otp');

            fetch('auth_action.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    btnResendRegisterOtp.textContent = 'Resend code';
                    if (data.success) {
                        showToast('New Code Sent', data.message || 'A new code has been sent to your email.');
                    } else {
                        if (errAlert) {
                            errAlert.textContent = data.message || 'Could not resend code.';
                            errAlert.classList.remove('d-none');
                        }
                    }
                })
                .catch(() => {
                    btnResendRegisterOtp.textContent = 'Resend code';
                });
        });
    }

    // Top Search Drawer: Dedicated Scroll, Autofocus & Real-Time Dynamic Search
    const searchTopDrawer = document.getElementById('searchTopDrawer');
    const searchDrawerInput = document.getElementById('searchDrawerInput');
    const searchDrawerClearBtn = document.getElementById('searchDrawerClearBtn');
    const searchDrawerSpinner = document.getElementById('searchDrawerSpinner');
    const searchInspirationHeading = document.getElementById('searchInspirationHeading');
    const searchViewAllLink = document.getElementById('searchViewAllLink');
    const searchResultCount = document.getElementById('searchResultCount');
    const searchDrawerGrid = document.getElementById('searchDrawerGrid');
    const scrollContainer = searchTopDrawer ? searchTopDrawer.querySelector('.search-drawer-scroll-container') : null;

    // Cache initial inspiration cards HTML and heading
    let initialInspirationHTML = searchDrawerGrid ? searchDrawerGrid.innerHTML : '';
    let initialInspirationHeading = searchInspirationHeading ? searchInspirationHeading.textContent : 'Need some inspiration?';
    let searchDebounceTimer = null;
    let searchCache = {};
    let searchAbortController = null;

    if (searchTopDrawer) {
        searchTopDrawer.addEventListener('shown.bs.offcanvas', function () {
            if (scrollContainer) {
                scrollContainer.scrollTop = 0;
            }
            if (searchDrawerInput) {
                searchDrawerInput.focus();
            }
        });

        searchTopDrawer.addEventListener('hidden.bs.offcanvas', function () {
            if (searchDrawerSpinner) searchDrawerSpinner.classList.add('d-none');
            if (searchAbortController) searchAbortController.abort();
        });
    }

    if (searchDrawerInput && searchDrawerGrid) {
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>'"]/g, function (tag) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#39;',
                    '"': '&quot;'
                }[tag] || tag);
            });
        }

        function renderSearchResults(query, data) {
            // Update heading
            if (searchInspirationHeading) {
                searchInspirationHeading.innerHTML = `Search results for &ldquo;${escapeHtml(query)}&rdquo;`;
            }

            // Update View All link
            if (searchViewAllLink && searchResultCount) {
                searchResultCount.textContent = data.count;
                searchViewAllLink.href = data.view_all_url || `products.php?q=${encodeURIComponent(query)}`;
                searchViewAllLink.classList.remove('d-none');
            }

            // Render products or empty state
            if (data.products && data.products.length > 0) {
                let cardsHtml = '';
                data.products.forEach(prod => {
                    cardsHtml += `
                        <div class="col search-prod-col">
                            <a href="${prod.url}" class="search-inspiration-card text-decoration-none text-dark d-block">
                                <div class="search-inspiration-img-box mb-2">
                                    <img src="${prod.image}" alt="${escapeHtml(prod.name)}" class="img-fluid" loading="lazy">
                                </div>
                                <div class="search-inspiration-meta">
                                    <div class="search-stars mb-1">
                                        ${prod.stars_html || ''}
                                    </div>
                                    <div class="search-prod-title fw-medium text-truncate">${escapeHtml(prod.name)}</div>
                                    <div class="search-prod-price text-muted small">${prod.formatted_price}</div>
                                </div>
                            </a>
                        </div>
                    `;
                });
                searchDrawerGrid.innerHTML = cardsHtml;
            } else {
                searchDrawerGrid.innerHTML = `
                    <div class="col-12 w-100">
                        <div class="search-empty-state py-4 my-2">
                            <i class="bi bi-search mb-2 d-block fs-3 text-secondary"></i>
                            <h6 class="fw-semibold text-dark mb-1">No products found</h6>
                            <p class="text-muted small mb-3">We couldn't find any items matching &ldquo;${escapeHtml(query)}&rdquo;.</p>
                            <a href="products.php" class="btn btn-dark btn-sm rounded-1 px-3 py-1">View all products</a>
                        </div>
                    </div>
                `;
            }
        }

        // Live input typing with 280ms debounce + Client-side Cache + AbortController
        searchDrawerInput.addEventListener('input', function () {
            const query = this.value.trim();

            if (query.length > 0) {
                if (searchDrawerClearBtn) searchDrawerClearBtn.classList.remove('d-none');
            } else {
                if (searchDrawerClearBtn) searchDrawerClearBtn.classList.add('d-none');
            }

            clearTimeout(searchDebounceTimer);

            if (query.length === 0) {
                if (searchAbortController) searchAbortController.abort();
                if (searchDrawerSpinner) searchDrawerSpinner.classList.add('d-none');
                searchDrawerGrid.innerHTML = initialInspirationHTML;
                if (searchInspirationHeading) searchInspirationHeading.textContent = initialInspirationHeading;
                if (searchViewAllLink) searchViewAllLink.classList.add('d-none');
                return;
            }

            // Check client-side memory cache first (0ms, 0 DB queries!)
            if (searchCache[query]) {
                if (searchDrawerSpinner) searchDrawerSpinner.classList.add('d-none');
                renderSearchResults(query, searchCache[query]);
                return;
            }

            // Show spinner while debouncing/searching
            if (searchDrawerSpinner) searchDrawerSpinner.classList.remove('d-none');

            searchDebounceTimer = setTimeout(() => {
                if (searchAbortController) {
                    searchAbortController.abort();
                }
                searchAbortController = new AbortController();

                fetch(`search_api.php?q=${encodeURIComponent(query)}`, { signal: searchAbortController.signal })
                    .then(res => res.json())
                    .then(data => {
                        if (searchDrawerSpinner) searchDrawerSpinner.classList.add('d-none');
                        if (!data.success) return;

                        // Cache result in client memory
                        searchCache[query] = data;
                        renderSearchResults(query, data);
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            if (searchDrawerSpinner) searchDrawerSpinner.classList.add('d-none');
                        }
                    });
            }, 280);
        });

        // Clear button
        if (searchDrawerClearBtn) {
            searchDrawerClearBtn.addEventListener('click', function () {
                searchDrawerInput.value = '';
                searchDrawerClearBtn.classList.add('d-none');
                if (searchDrawerSpinner) searchDrawerSpinner.classList.add('d-none');
                searchDrawerGrid.innerHTML = initialInspirationHTML;
                if (searchInspirationHeading) searchInspirationHeading.textContent = initialInspirationHeading;
                if (searchViewAllLink) searchViewAllLink.classList.add('d-none');
                searchDrawerInput.focus();
            });
        }
    }

    // Close search drawer on Escape key or outside click
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (searchTopDrawer && searchTopDrawer.classList.contains('show') && window.bootstrap) {
                const offcanvas = bootstrap.Offcanvas.getInstance(searchTopDrawer);
                if (offcanvas) offcanvas.hide();
            }
        }
    });

    if (searchTopDrawer) {
        searchTopDrawer.addEventListener('click', function (e) {
            if (e.target === searchTopDrawer || e.target.classList.contains('search-drawer-scroll-container')) {
                const offcanvas = bootstrap.Offcanvas.getInstance(searchTopDrawer);
                if (offcanvas) offcanvas.hide();
            }
        });
    }

    // Reward / Discount Popup: Desktop Leave-Intent & Session Dismissal Protection
    const offerModalEl = document.getElementById('offerModal');
    if (offerModalEl && window.bootstrap) {
        const isDismissed = localStorage.getItem('ons_offer_dismissed') || sessionStorage.getItem('ons_offer_dismissed');

        if (!isDismissed) {
            let offerModalInstance = null;
            let offerFired = false;

            function triggerOfferPopup() {
                if (offerFired) return;
                if (localStorage.getItem('ons_offer_dismissed') || sessionStorage.getItem('ons_offer_dismissed')) return;
                offerFired = true;
                sessionStorage.setItem('ons_offer_dismissed', '1');
                offerModalInstance = bootstrap.Modal.getInstance(offerModalEl) || new bootstrap.Modal(offerModalEl);
                offerModalInstance.show();
            }

            // Desktop Leave-Intent: triggers when mouse moves towards browser top edge (address/tabs bar)
            if (window.innerWidth >= 992) {
                document.addEventListener('mouseleave', function (e) {
                    if (e.clientY <= 25) {
                        triggerOfferPopup();
                    }
                });
            } else {
                // Mobile/Tablet: triggers after sensible browsing duration (12 seconds)
                setTimeout(() => {
                    triggerOfferPopup();
                }, 12000);
            }

            // When user dismisses via close button or backdrop click, record dismissal permanently
            offerModalEl.addEventListener('hidden.bs.modal', function () {
                localStorage.setItem('ons_offer_dismissed', '1');
                sessionStorage.setItem('ons_offer_dismissed', '1');
            });
        }
    }

    // Offer Coupon Copy Buttons
    document.querySelectorAll('.copy-coupon-trigger').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const coupon = this.getAttribute('data-coupon') || 'OFF20%';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(coupon).then(() => {
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.replace('bi-copy', 'bi-check-lg');
                        setTimeout(() => icon.classList.replace('bi-check-lg', 'bi-copy'), 2500);
                    }
                    showToast('Coupon Copied', `Code ${coupon} copied to clipboard!`);
                });
            } else {
                showToast('Coupon Code', `Use code: ${coupon}`);
            }
        });
    });

    // Grab Discount Button: applies coupon to session & redirects to catalog
    document.querySelectorAll('.grab-discount-trigger').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const coupon = this.getAttribute('data-coupon') || 'OFF20%';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(coupon);
            }
            // Mark as claimed and save active promo code in sessionStorage
            sessionStorage.setItem('ons_applied_coupon', coupon);
            localStorage.setItem('ons_offer_dismissed', '1');
            localStorage.setItem('ons_offer_claimed', '1');

            showToast('Discount Activated', `20% OFF coupon ${coupon} applied! Opening products...`);

            const offerEl = document.getElementById('offerModal');
            if (offerEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(offerEl);
                if (modal) modal.hide();
            }
            setTimeout(() => {
                window.location.href = 'products.php';
            }, 600);
        });
    });

    // Hero Slider Toast Animation on Slide Change & Initial Refresh
    const heroCarousel = document.getElementById('heroCarousel');
    if (heroCarousel) {
        // Trigger animation whenever carousel changes slides
        heroCarousel.addEventListener('slide.bs.carousel', function (e) {
            const nextSlide = e.relatedTarget;
            if (nextSlide) {
                const title = nextSlide.querySelector('.hero-title');
                const btn = nextSlide.querySelector('.btn-ons-black');
                if (title) {
                    title.style.animation = 'none';
                    void title.offsetHeight; // force reflow
                    title.style.animation = '';
                }
                if (btn) {
                    btn.style.animation = 'none';
                    void btn.offsetHeight; // force reflow
                    btn.style.animation = '';
                }
            }
        });
    }

    // Toast Notification Creator
    function showToast(title, message) {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '99999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'alert alert-dark alert-dismissible fade show shadow-sm';
        toast.style.minWidth = '280px';
        toast.style.borderRadius = '8px';
        toast.style.backgroundColor = '#111';
        toast.style.color = '#fff';
        toast.style.border = '1px solid #333';
        toast.innerHTML = `
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
});
