/**
 * ONS SOUND — WordPress-Style Admin Panel JS
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Submenu Toggle
    const menuItems = document.querySelectorAll('#adminmenu li.wp-has-submenu');
    menuItems.forEach(function (item) {
        const link = item.querySelector('a.menu-top-link');
        if (link) {
            link.addEventListener('click', function (e) {
                // If clicked item has submenu and isn't a direct single-link page
                const submenu = item.querySelector('.wp-submenu');
                if (submenu && item.getAttribute('data-has-children') === '1') {
                    // Toggle current item
                    const isOpen = item.classList.contains('wp-menu-open');
                    if (isOpen) {
                        item.classList.remove('wp-menu-open');
                    } else {
                        // Close other menus
                        menuItems.forEach(other => other.classList.remove('wp-menu-open'));
                        item.classList.add('wp-menu-open');
                    }
                }
            });
        }
    });

    // 2. Mobile Responsive Menu Toggle & Overlay
    const responsiveToggle = document.getElementById('wp-responsive-toggle');
    const responsiveOverlay = document.getElementById('wp-responsive-overlay');

    if (responsiveToggle) {
        responsiveToggle.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('wp-responsive-open');
        });
    }

    if (responsiveOverlay) {
        responsiveOverlay.addEventListener('click', function () {
            document.body.classList.remove('wp-responsive-open');
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.body.classList.contains('wp-responsive-open')) {
            document.body.classList.remove('wp-responsive-open');
        }
    });

    // Auto-wrap wide tables for mobile responsiveness
    document.querySelectorAll('table.wp-list-table').forEach(function (table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // 3. Dismissible Notices
    document.querySelectorAll('.notice.is-dismissible').forEach(function (notice) {
        let btn = notice.querySelector('.notice-dismiss');
        if (!btn) {
            btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'notice-dismiss';
            btn.innerHTML = '&times;';
            btn.setAttribute('aria-label', 'Dismiss');
            notice.appendChild(btn);
        }
        btn.addEventListener('click', function () {
            notice.style.transition = 'opacity 0.2s';
            notice.style.opacity = '0';
            setTimeout(() => notice.remove(), 200);
        });
    });

    // 4. Select All Checkboxes in Tables
    const selectAllBoxes = document.querySelectorAll('#cb-select-all-1, #cb-select-all-2');
    selectAllBoxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
            const table = this.closest('table');
            if (table) {
                const rowBoxes = table.querySelectorAll('tbody input[type="checkbox"]');
                rowBoxes.forEach(rb => rb.checked = cb.checked);
            }
        });
    });

    // 5. Delete Confirmations
    document.querySelectorAll('.submitdelete, .action-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const msg = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
