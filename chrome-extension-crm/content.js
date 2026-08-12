// CRM MVP WHATSAPP WEB CONTENT SCRIPT (AUTOMATIC LOGGED-IN ADMIN AUTO-SYNC V1.0.3)
(function() {
    console.log("🚀 CRM MVP WhatsApp Web Stage Switcher Extension Loaded (Auto-Sync Admin)!");

    const SERVER_URL = 'http://127.0.0.1:8000';
    let activeAccount = null;
    let availableStages = [];
    let currentCustomerPhone = null;
    let currentCustomerName = null;
    let loggedInAdminPhone = null;

    // Detect Logged-In WA Admin Phone Number from WhatsApp Web Local Storage
    function getLoggedInAdminPhone() {
        try {
            const lastWid = localStorage.getItem('last-wid-md') || localStorage.getItem('last-wid');
            if (lastWid) {
                const digits = lastWid.split('@')[0].split(':')[0].replace(/[^0-9]/g, '');
                if (digits.length >= 8) return digits;
            }
        } catch (e) {}

        try {
            const userImgEl = document.querySelector('header img[src*="dyn"]') || document.querySelector('header [role="button"] img');
            if (userImgEl && userImgEl.alt) {
                const digits = userImgEl.alt.replace(/[^0-9]/g, '');
                if (digits.length >= 8) return digits;
            }
        } catch (e) {}

        return null;
    }

    // Fetch dynamic stages for the exact logged-in WA account from CRM Backend
    async function loadDynamicStages() {
        loggedInAdminPhone = getLoggedInAdminPhone();
        const queryParam = loggedInAdminPhone ? `?phone=${loggedInAdminPhone}` : '';

        try {
            const res = await fetch(`${SERVER_URL}/api/extension/stages${queryParam}`);
            const data = await res.json();

            if (data.status === 'success' && data.accounts && data.accounts.length > 0) {
                activeAccount = data.accounts[0];
                if (activeAccount && activeAccount.pipeline_stages && activeAccount.pipeline_stages.length > 0) {
                    availableStages = activeAccount.pipeline_stages;
                }
            }
        } catch (err) {
            console.log('CRM API offline, using fallback stages');
        }

        if (!availableStages || availableStages.length === 0) {
            availableStages = [
                { name: 'Inquiry Masuk', order: 1 },
                { name: 'Pitching', order: 2 },
                { name: 'Meeting Call', order: 3 },
                { name: 'Deal', order: 4 },
                { name: 'Lost', order: 5 }
            ];
        }

        updateAdminBadge();
        populateStageDropdown();
    }

    // Create & Inject Floating Bar and Floating Action Button (FAB)
    function injectFloatingUI() {
        if (!document.getElementById('crm-wa-floating-bar')) {
            const bar = document.createElement('div');
            bar.id = 'crm-wa-floating-bar';
            bar.innerHTML = `
                <div class="crm-bar-title">🚀 CRM MVP</div>
                <div class="crm-phone-badge" id="crm-admin-account-tag">Connecting...</div>
                <div class="crm-phone-badge" id="crm-active-lead-phone" style="color: #facc15;">Pilih Chat Customer...</div>
                <select class="crm-stage-select" id="crm-stage-dropdown" title="Pilih Stage Custom">
                    <option value="">Pilih Target Stage...</option>
                </select>
                <button class="crm-save-btn" id="crm-btn-update-stage">💾 Update Stage</button>
            `;

            (document.body || document.documentElement).appendChild(bar);
            document.getElementById('crm-btn-update-stage').addEventListener('click', updateCurrentLeadStage);

            updateAdminBadge();
            populateStageDropdown();
        }

        if (!document.getElementById('crm-floating-fab-btn')) {
            const fab = document.createElement('button');
            fab.id = 'crm-floating-fab-btn';
            fab.innerHTML = '🚀 CRM Stage Bar';
            fab.title = 'Tampilkan / Sembunyikan Stage Bar CRM';
            fab.addEventListener('click', () => {
                const bar = document.getElementById('crm-wa-floating-bar');
                if (bar) {
                    bar.style.display = (bar.style.display === 'none') ? 'flex' : 'none';
                }
            });
            (document.body || document.documentElement).appendChild(fab);
        }
    }

    function updateAdminBadge() {
        const tag = document.getElementById('crm-admin-account-tag');
        if (!tag) return;

        if (activeAccount) {
            tag.textContent = `🏢 ${activeAccount.name}`;
            tag.title = `Akun CRM: ${activeAccount.name} (${activeAccount.phone || 'Connected'})`;
        } else if (loggedInAdminPhone) {
            tag.textContent = `📱 WA: +${loggedInAdminPhone.substring(0, 12)}`;
        } else {
            tag.textContent = `🏢 Account CRM`;
        }
    }

    function populateStageDropdown() {
        const select = document.getElementById('crm-stage-dropdown');
        if (!select) return;

        select.innerHTML = '<option value="">Pilih Target Stage...</option>';

        availableStages.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.name;
            opt.textContent = `${s.order ? s.order + '. ' : ''}${s.name}`;
            select.appendChild(opt);
        });
    }

    // Monitor Active Chat Selection in WhatsApp Web UI
    function detectActiveChat() {
        injectFloatingUI();

        if (!loggedInAdminPhone) {
            const phone = getLoggedInAdminPhone();
            if (phone) {
                loadDynamicStages();
            }
        }

        const headerContainer = document.querySelector('#main header');
        const phoneBadge = document.getElementById('crm-active-lead-phone');

        if (headerContainer) {
            const titleEl = headerContainer.querySelector('span[dir="auto"]') || headerContainer.querySelector('span[title]');
            if (titleEl) {
                const titleText = titleEl.getAttribute('title') || titleEl.textContent || '';
                currentCustomerName = titleText;

                const digits = titleText.replace(/[^0-9]/g, '');
                if (digits.length >= 8) {
                    currentCustomerPhone = digits;
                    if (phoneBadge) phoneBadge.textContent = '👤 +' + digits;
                } else if (titleText.trim().length > 0) {
                    currentCustomerPhone = titleText.trim();
                    if (phoneBadge) phoneBadge.textContent = '👤 ' + titleText.substring(0, 14);
                }
            }
        } else {
            if (phoneBadge && (phoneBadge.textContent === 'Pilih Chat Customer...' || phoneBadge.textContent.includes('👤'))) {
                phoneBadge.textContent = 'Buka Chat Customer...';
            }
        }
    }

    // Submit Stage Update to CRM Backend
    async function updateCurrentLeadStage() {
        const select = document.getElementById('crm-stage-dropdown');
        const targetStage = select ? select.value : '';

        if (!currentCustomerPhone || currentCustomerPhone === 'Pilih Chat Customer...') {
            return showToast('⚠️ Buka chat customer di WhatsApp Web dulu!');
        }
        if (!targetStage) {
            return showToast('⚠️ Pilih target stage dari dropdown!');
        }

        try {
            const res = await fetch(`${SERVER_URL}/api/extension/update-stage`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    phone: currentCustomerPhone,
                    stage: targetStage,
                    account_id: activeAccount ? activeAccount.id : null
                })
            });

            const data = await res.json();
            if (data.status === 'success') {
                showToast(`✅ BERHASIL! Customer ${currentCustomerName || currentCustomerPhone} diupdate ke stage: ${targetStage}`);
            } else {
                showToast('❌ Gagal update stage ke server.');
            }
        } catch (err) {
            console.error('Error updating stage:', err);
            showToast('⚠️ Gagal terhubung ke CRM server. Pastikan server lokal aktif.');
        }
    }

    function showToast(message) {
        const existing = document.querySelector('.crm-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'crm-toast';
        toast.textContent = message;
        (document.body || document.documentElement).appendChild(toast);

        setTimeout(() => toast.remove(), 4000);
    }

    // Load initial dynamic stages
    loadDynamicStages();

    // DOM Observer loop
    setInterval(detectActiveChat, 1000);

})();
