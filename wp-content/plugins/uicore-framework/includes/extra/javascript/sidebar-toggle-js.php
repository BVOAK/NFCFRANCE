<?php
defined("ABSPATH") || exit();
//INCLUDED IN CLASS JS
// TODO: Update script to handle the mobile versions

$js .= "
window.addEventListener('DOMContentLoaded', () => {
    // Listen to click on swatches list
    document.querySelectorAll('.uicore-sidebar-toggle').forEach(function(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const sidebar = document.querySelector('.uicore-sidebar');
            const sidebarTop = document.querySelector('.uicore-sidebar-top');
            const sidebarToggleTop = document.querySelector('.uicore-sidebar-toggle span.top');
            const sidebarToggleBottom = document.querySelector('.uicore-sidebar-toggle span.bottom');
            const textHide = document.querySelector('.text-wrap .hide');
            const textShow = document.querySelector('.text-wrap .show');
            const archive = document.querySelector('.uicore-archive');

            if (sidebar && sidebar.classList.contains('sidebar-hidden')) {
                // Toggle the sidebar
                if (sidebarTop) {
                    // slideToggle simulation
                    sidebar.style.transition = 'max-height 0.35s ease';
                    sidebar.style.overflow = 'hidden';
                    sidebar.style.maxHeight = sidebar.scrollHeight + 'px';
                    sidebar.classList.remove('sidebar-hidden');
                } else {
                    sidebar.style.transition = 'width 0.3s ease';
                    sidebar.style.width = '25%';
                    sidebar.classList.remove('sidebar-hidden');
                }

                // Update icon style
                if (sidebarToggleTop) sidebarToggleTop.style.transform = 'translateX(8px)';
                if (sidebarToggleBottom) sidebarToggleBottom.style.transform = 'translateX(0)';

                // Update text
                if (textHide) textHide.style.display = 'block';
                if (textShow) textShow.style.display = 'none';

                // Update content section widget
                if (archive) archive.style.width = '100%';

                return;
            }

            // Hide the sidebar
            if (sidebarTop) {
                // slideToggle simulation
                sidebar.style.transition = 'max-height 0.35s ease';
                sidebar.style.overflow = 'hidden';
                sidebar.style.maxHeight = '0';
                setTimeout(() => {
                    sidebar.classList.add('sidebar-hidden');
                }, 100);
            } else {
                sidebar.style.transition = 'width 0.3s ease';
                sidebar.style.width = '0';
                sidebar.classList.add('sidebar-hidden');
            }

            // Update icon style
            if (sidebarToggleTop) sidebarToggleTop.style.transform = 'translateX(0)';
            if (sidebarToggleBottom) sidebarToggleBottom.style.transform = 'translateX(8px)';

            // Update text
            if (textHide) textHide.style.display = 'none';
            if (textShow) textShow.style.display = 'block';

            // Update content section widget
            if (archive) archive.style.width = '100%';
        });
    });
});
";