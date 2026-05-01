/**
 * Bulma JavaScript Extensions
 * Version: 0.9.4
 */

// Bulma JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Navbar burger functionality
    const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
    
    if ($navbarBurgers.length > 0) {
        $navbarBurgers.forEach(function($el) {
            $el.addEventListener('click', function() {
                const target = $el.dataset.target;
                const $target = document.getElementById(target);
                
                $el.classList.toggle('is-active');
                $target.classList.toggle('is-active');
            });
        });
    }
    
    // Dropdown functionality
    const $dropdowns = Array.prototype.slice.call(document.querySelectorAll('.dropdown:not(.is-hoverable)'), 0);
    
    if ($dropdowns.length > 0) {
        $dropdowns.forEach(function($el) {
            $el.addEventListener('click', function(event) {
                event.stopPropagation();
                $el.classList.toggle('is-active');
            });
        });
        
        document.addEventListener('click', function(event) {
            closeDropdowns();
        });
    }
    
    // Close dropdowns
    function closeDropdowns() {
        const $dropdowns = Array.prototype.slice.call(document.querySelectorAll('.dropdown.is-active'), 0);
        
        if ($dropdowns.length > 0) {
            $dropdowns.forEach(function($el) {
                $el.classList.remove('is-active');
            });
        }
    }
    
    // Modal functionality
    const $modals = Array.prototype.slice.call(document.querySelectorAll('.modal'), 0);
    
    if ($modals.length > 0) {
        $modals.forEach(function($el) {
            const $close = Array.prototype.slice.call($el.querySelectorAll('.delete, .modal-background, .modal-close'), 0);
            
            $close.forEach(function($closeEl) {
                $closeEl.addEventListener('click', function() {
                    closeModal($el);
                });
            });
        });
    }
    
    // Close modal
    function closeModal($el) {
        $el.classList.remove('is-active');
        document.documentElement.classList.remove('is-clipped');
    }
    
    // Notification close functionality
    const $notifications = Array.prototype.slice.call(document.querySelectorAll('.notification .delete'), 0);
    
    if ($notifications.length > 0) {
        $notifications.forEach(function($delete) {
            const $notification = $delete.parentNode;
            
            $delete.addEventListener('click', function() {
                $notification.parentNode.removeChild($notification);
            });
        });
    }
    
    // Tabs functionality
    const $tabs = Array.prototype.slice.call(document.querySelectorAll('.tabs li'), 0);
    
    if ($tabs.length > 0) {
        $tabs.forEach(function($tab) {
            $tab.addEventListener('click', function() {
                const target = $tab.dataset.target;
                const $target = document.getElementById(target);
                
                // Remove active class from all tabs and content
                $tabs.forEach(function($tab) {
                    $tab.classList.remove('is-active');
                });
                
                const $tabContents = Array.prototype.slice.call(document.querySelectorAll('.tab-content'), 0);
                $tabContents.forEach(function($content) {
                    $content.classList.remove('is-active');
                });
                
                // Add active class to clicked tab and corresponding content
                $tab.classList.add('is-active');
                if ($target) {
                    $target.classList.add('is-active');
                }
            });
        });
    }
    
    // File input functionality
    const $fileInputs = Array.prototype.slice.call(document.querySelectorAll('.file-input'), 0);
    
    if ($fileInputs.length > 0) {
        $fileInputs.forEach(function($input) {
            $input.addEventListener('change', function() {
                const fileName = $input.files[0] ? $input.files[0].name : 'Aucun fichier sélectionné';
                const $fileName = $input.parentNode.querySelector('.file-name');
                
                if ($fileName) {
                    $fileName.textContent = fileName;
                }
            });
        });
    }
    
    // Form validation
    const $forms = Array.prototype.slice.call(document.querySelectorAll('form'), 0);
    
    if ($forms.length > 0) {
        $forms.forEach(function($form) {
            $form.addEventListener('submit', function(event) {
                const $requiredFields = Array.prototype.slice.call($form.querySelectorAll('[required]'), 0);
                let isValid = true;
                
                $requiredFields.forEach(function($field) {
                    if (!$field.value.trim()) {
                        isValid = false;
                        $field.classList.add('is-danger');
                    } else {
                        $field.classList.remove('is-danger');
                    }
                });
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    }
    
    // Auto-hide notifications after 5 seconds
    const $autoHideNotifications = Array.prototype.slice.call(document.querySelectorAll('.notification[data-auto-hide]'), 0);
    
    if ($autoHideNotifications.length > 0) {
        $autoHideNotifications.forEach(function($notification) {
            setTimeout(function() {
                if ($notification.parentNode) {
                    $notification.parentNode.removeChild($notification);
                }
            }, 5000);
        });
    }
    
    // Loading states for buttons
    const $buttons = Array.prototype.slice.call(document.querySelectorAll('.button[data-loading]'), 0);
    
    if ($buttons.length > 0) {
        $buttons.forEach(function($button) {
            $button.addEventListener('click', function() {
                const originalText = $button.textContent;
                $button.classList.add('is-loading');
                $button.textContent = 'Chargement...';
                
                // Reset after 3 seconds (or when form is submitted)
                setTimeout(function() {
                    $button.classList.remove('is-loading');
                    $button.textContent = originalText;
                }, 3000);
            });
        });
    }
    
    // Tooltip functionality
    const $tooltips = Array.prototype.slice.call(document.querySelectorAll('[data-tooltip]'), 0);
    
    if ($tooltips.length > 0) {
        $tooltips.forEach(function($tooltip) {
            const tooltipText = $tooltip.dataset.tooltip;
            
            $tooltip.addEventListener('mouseenter', function() {
                const $tooltipEl = document.createElement('div');
                $tooltipEl.className = 'tooltip is-tooltip-multiline';
                $tooltipEl.textContent = tooltipText;
                $tooltipEl.style.position = 'absolute';
                $tooltipEl.style.zIndex = '1000';
                
                document.body.appendChild($tooltipEl);
                
                const rect = $tooltip.getBoundingClientRect();
                $tooltipEl.style.left = rect.left + 'px';
                $tooltipEl.style.top = (rect.top - $tooltipEl.offsetHeight - 10) + 'px';
                
                $tooltip._tooltipElement = $tooltipEl;
            });
            
            $tooltip.addEventListener('mouseleave', function() {
                if ($tooltip._tooltipElement) {
                    $tooltip._tooltipElement.remove();
                    $tooltip._tooltipElement = null;
                }
            });
        });
    }
    
    // Collapsible functionality
    const $collapsibles = Array.prototype.slice.call(document.querySelectorAll('.collapsible'), 0);
    
    if ($collapsibles.length > 0) {
        $collapsibles.forEach(function($collapsible) {
            const $trigger = $collapsible.querySelector('.collapsible-trigger');
            const $content = $collapsible.querySelector('.collapsible-content');
            
            if ($trigger && $content) {
                $trigger.addEventListener('click', function() {
                    $collapsible.classList.toggle('is-active');
                    $content.style.display = $collapsible.classList.contains('is-active') ? 'block' : 'none';
                });
            }
        });
    }
    
    // Initialize any custom components
    if (typeof BulmaExtensions !== 'undefined') {
        BulmaExtensions.init();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Bulma;
}