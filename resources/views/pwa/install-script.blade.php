<!-- PWA Install Button -->
<div id="pwa-install-button" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <button onclick="window.pwaInstaller.install()" style="
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: none;
        padding: 16px 24px;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
        backdrop-filter: blur(10px);
    " 
    onmouseover="this.style.transform='translateY(-3px) scale(1.02)'; this.style.boxShadow='0 8px 25px rgba(245, 158, 11, 0.6)'" 
    onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(245, 158, 11, 0.4)'">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Install App
    </button>
</div>

<script>
(function() {
    'use strict';
    
    console.log('🚀 PWA Installer loaded');
    
    class PWAInstaller {
        constructor() {
            this.deferredPrompt = null;
            this.button = document.getElementById('pwa-install-button');
            this.isInstalled = false;
            
            this.init();
        }
        
        init() {
            this.checkIfInstalled();
            this.setupEventListeners();
            this.showButtonAfterDelay();
            this.logDebugInfo();
        }
        
        checkIfInstalled() {
            // Check if app is already installed
            if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
                this.isInstalled = true;
                console.log('✅ App is already installed');
                return;
            }
            
            if (window.navigator.standalone === true) {
                this.isInstalled = true;
                console.log('✅ App is running in standalone mode (iOS)');
                return;
            }
        }
        
        setupEventListeners() {
            // Listen for the beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('🎯 beforeinstallprompt event fired');
                e.preventDefault();
                this.deferredPrompt = e;
                this.showButton();
            });
            
            // Listen for app installed event
            window.addEventListener('appinstalled', () => {
                console.log('✅ PWA was installed successfully');
                this.hideButton();
                this.deferredPrompt = null;
                this.isInstalled = true;
            });
            
            // Listen for standalone mode changes
            if (window.matchMedia) {
                window.matchMedia('(display-mode: standalone)').addEventListener('change', (e) => {
                    if (e.matches) {
                        console.log('✅ App entered standalone mode');
                        this.isInstalled = true;
                        this.hideButton();
                    }
                });
            }
        }
        
        showButtonAfterDelay() {
            // Show button after delay for testing/fallback
            if (!this.isInstalled) {
                setTimeout(() => {
                    this.showButton();
                    console.log('📱 Install button shown (fallback mode)');
                }, 2000);
            }
        }
        
        showButton() {
            if (this.button && !this.isInstalled) {
                this.button.style.display = 'block';
                this.button.style.opacity = '0';
                this.button.style.transform = 'translateY(20px)';
                
                // Animate in
                setTimeout(() => {
                    this.button.style.transition = 'all 0.3s ease';
                    this.button.style.opacity = '1';
                    this.button.style.transform = 'translateY(0)';
                }, 10);
            }
        }
        
        hideButton() {
            if (this.button) {
                this.button.style.transition = 'all 0.3s ease';
                this.button.style.opacity = '0';
                this.button.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    this.button.style.display = 'none';
                }, 300);
            }
        }
        
        async install() {
            console.log('📱 Install button clicked');
            
            if (this.deferredPrompt) {
                console.log('🚀 Showing native install prompt');
                
                try {
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    
                    console.log(`User ${outcome} the install prompt`);
                    
                    if (outcome === 'accepted') {
                        console.log('✅ User accepted the install');
                        this.hideButton();
                    }
                    
                } catch (error) {
                    console.error('❌ Error during install prompt:', error);
                }
                
                this.deferredPrompt = null;
                
            } else {
                // Manual instructions for different platforms
                this.showManualInstructions();
            }
        }
        
        showManualInstructions() {
            const userAgent = navigator.userAgent.toLowerCase();
            const isIOS = /ipad|iphone|ipod/.test(userAgent);
            const isAndroid = /android/.test(userAgent);
            const isSafari = /safari/.test(userAgent) && !/chrome/.test(userAgent);
            const isChrome = /chrome/.test(userAgent);
            const isFirefox = /firefox/.test(userAgent);
            
            let title = "Install sebagai Aplikasi";
            let instructions = "";
            
            if (isIOS) {
                title = "📱 Install di iOS";
                instructions = "1. Tap tombol Share (⬆️) di bawah\n2. Scroll dan pilih 'Add to Home Screen'\n3. Tap 'Add' untuk install";
            } else if (isAndroid && isChrome) {
                title = "📱 Install di Android Chrome";
                instructions = "1. Tap menu (⋮) di pojok kanan atas\n2. Pilih 'Add to Home screen' atau 'Install app'\n3. Tap 'Install' untuk konfirmasi";
            } else if (isAndroid) {
                title = "📱 Install di Android";
                instructions = "1. Tap menu browser\n2. Cari opsi 'Add to Home screen'\n3. Ikuti instruksi untuk install";
            } else if (isChrome) {
                title = "💻 Install di Desktop Chrome";
                instructions = "1. Klik icon install (⬇️) di address bar, atau\n2. Klik menu (⋮) → 'Install Filament Admin...'\n3. Klik 'Install' untuk konfirmasi";
            } else if (isFirefox) {
                title = "💻 Firefox";
                instructions = "PWA install tidak tersedia di Firefox desktop.\nCoba buka di Chrome atau Edge.";
            } else {
                title = "💻 Install di Desktop";
                instructions = "1. Cari icon install di address bar, atau\n2. Buka menu browser\n3. Pilih opsi 'Install' atau 'Create shortcut'";
            }
            
            alert(title + "\n\n" + instructions);
        }
        
        logDebugInfo() {
            console.group('🔍 PWA Debug Information');
            console.log('User Agent:', navigator.userAgent);
            console.log('Is Secure Context:', window.isSecureContext);
            console.log('Service Worker Support:', 'serviceWorker' in navigator);
            console.log('Display Mode:', window.matchMedia && window.matchMedia('(display-mode: standalone)').matches ? 'standalone' : 'browser');
            console.log('iOS Standalone:', window.navigator.standalone);
            console.log('Current URL:', window.location.href);
            console.groupEnd();
        }
    }
    
    // Initialize when DOM is ready
    function initPWA() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.pwaInstaller = new PWAInstaller();
            });
        } else {
            window.pwaInstaller = new PWAInstaller();
        }
    }
    
    initPWA();
    
})();
</script>