/**
 * KISSAI SCHOOL - LyCol System - JavaScript Principal
 * Port: 8080
 */

// Configuration globale
const APP_CONFIG = {
    baseUrl: 'http://localhost:8080',
    apiUrl: 'http://localhost:8080/admin/configuration',
    version: '1.0.0'
};

// Classe principale de l'application
class LyColApp {
    constructor() {
        this.init();
    }

    init() {
        console.log('🚀 KISSAI SCHOOL - LyCol System initialisé');
        this.setupEventListeners();
        this.loadSystemStats();
        this.setupNotifications();
    }

    setupEventListeners() {
        // Gestion des formulaires
        document.addEventListener('DOMContentLoaded', () => {
            this.setupForms();
            this.setupModals();
            this.setupTabs();
            this.setupNotifications();
        });

        // Gestion des clics sur les cartes
        document.addEventListener('click', (e) => {
            if (e.target.closest('.card')) {
                this.handleCardClick(e);
            }
        });
    }

    setupForms() {
        const forms = document.querySelectorAll('form[data-ajax]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form);
            });
        });
    }

    setupModals() {
        const modalTriggers = document.querySelectorAll('[data-target]');
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const target = trigger.getAttribute('data-target');
                this.openModal(target);
            });
        });

        // Fermeture des modales
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-background') || 
                e.target.classList.contains('modal-close')) {
                this.closeModal(e.target.closest('.modal'));
            }
        });
    }

    setupTabs() {
        const tabTriggers = document.querySelectorAll('.tabs li');
        tabTriggers.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchTab(tab);
            });
        });
    }

    setupNotifications() {
        // Auto-fermeture des notifications
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        });
    }

    async handleFormSubmit(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Envoi...';

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Succès', result.message, 'success');
                if (result.redirect) {
                    setTimeout(() => window.location.href = result.redirect, 1000);
                }
            } else {
                this.showNotification('Erreur', result.message, 'danger');
            }

        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            this.showNotification('Erreur', 'Une erreur est survenue', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    async loadSystemStats() {
        try {
            const response = await fetch(`${APP_CONFIG.apiUrl}/system-stats-api`);
            const stats = await response.json();

            if (stats.success) {
                this.updateDashboardStats(stats.data);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des statistiques:', error);
        }
    }

    updateDashboardStats(stats) {
        const elements = {
            'total-students': stats.totalStudents || 0,
            'total-teachers': stats.totalTeachers || 0,
            'total-classes': stats.totalClasses || 0,
            'disk-usage': stats.diskUsage || '0%',
            'memory-usage': stats.memoryUsage || '0%'
        };

        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    async checkLicense() {
        try {
            const response = await fetch(`${APP_CONFIG.apiUrl}/check-license`);
            const result = await response.json();

            if (result.success) {
                this.updateLicenseStatus(result.data);
            }
        } catch (error) {
            console.error('Erreur lors de la vérification de licence:', error);
        }
    }

    updateLicenseStatus(licenseData) {
        const statusElement = document.getElementById('license-status');
        if (statusElement) {
            const isActive = licenseData.status === 'ACTIVE';
            statusElement.className = `license-status ${isActive ? 'active' : 'expired'}`;
            statusElement.innerHTML = `
                <strong>Statut:</strong> ${isActive ? 'Actif' : 'Expiré'}<br>
                <strong>Type:</strong> ${licenseData.type}<br>
                <strong>Expire le:</strong> ${licenseData.expiryDate}
            `;
        }
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('is-active');
            document.body.classList.add('is-clipped');
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-clipped');
        }
    }

    switchTab(selectedTab) {
        const tabContainer = selectedTab.closest('.tabs');
        const tabs = tabContainer.querySelectorAll('li');
        const tabContents = document.querySelectorAll('.tab-content');

        // Désactiver tous les onglets
        tabs.forEach(tab => tab.classList.remove('is-active'));
        tabContents.forEach(content => content.classList.remove('is-active'));

        // Activer l'onglet sélectionné
        selectedTab.classList.add('is-active');
        const targetId = selectedTab.querySelector('a').getAttribute('href').substring(1);
        const targetContent = document.getElementById(targetId);
        if (targetContent) {
            targetContent.classList.add('is-active');
        }
    }

    handleCardClick(event) {
        const card = event.target.closest('.card');
        const link = card.querySelector('a');
        if (link) {
            window.location.href = link.href;
        }
    }

    showNotification(title, message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification is-${type}`;
        notification.innerHTML = `
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <strong>${title}</strong><br>
            ${message}
        `;

        const container = document.querySelector('.notifications') || document.body;
        container.appendChild(notification);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Utilitaires
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
}

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', () => {
    window.lycolApp = new LyColApp();
});

// Fonctions globales pour compatibilité
window.showNotification = (title, message, type) => {
    if (window.lycolApp) {
        window.lycolApp.showNotification(title, message, type);
    }
};

window.clearCache = async () => {
    try {
        const response = await fetch(`${APP_CONFIG.apiUrl}/clear-cache`);
        const result = await response.json();
        
        if (result.success) {
            showNotification('Succès', 'Cache vidé avec succès', 'success');
        } else {
            showNotification('Erreur', 'Erreur lors du vidage du cache', 'danger');
        }
    } catch (error) {
        console.error('Erreur lors du vidage du cache:', error);
        showNotification('Erreur', 'Erreur lors du vidage du cache', 'danger');
    }
};




