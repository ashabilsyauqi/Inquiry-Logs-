// CRM MVP WHATSAPP WEB CONTENT SCRIPT (ENHANCED BRAND-SPECIFIC STAGES V1.0.2)
(function() {
    console.log("🚀 CRM MVP WhatsApp Web Stage Switcher Extension Loaded!");

    const SERVER_URL = 'http://127.0.0.1:8000';
    let accountsData = [];
    let selectedAccountId = null;
    let currentCustomerPhone = null;
    let currentCustomerName = null;

    // Fetch dynamic brand accounts & their exact custom pipeline stages
    async function loadDynamicStages() {
        try {
            const res = await fetch(`${SERVER_URL}/api/extension/stages`);
            const data = await res.json();
            if (data.status === 'success' && data.accounts && data.accounts.length > 0) {
                accountsData = data.accounts;
                if (!selectedAccountId && accountsData.length > 0) {
                    selectedAccountId = accountsData[0].id;
                }
            }
        } catch (err) {
            console.log('CRM API offline, using fallback data');
        }
        populateBrandDropdown();
        populateStageDropdown();
    }

    // Create & Inject Floating Bar and Floating Action Button (FAB)
    function injectFloatingUI() {
        if (!document.getElementById('crm-wa-floating-bar')) {
            const bar = document.createElement('div');
            bar.id = 'crm-wa-floating-bar';
            bar.innerHTML = `
                <div class="crm-bar-title">🚀 CRM MVP</div>
                <div class="crm-phone-badge" id="crm-active-lead-phone">Pilih Chat...</div>
                <select class="crm-stage-select" id="crm-brand-dropdown" title="Pilih Brand Pipeline">
                    <option value="">Pilih Brand...</option>
                </select>
                <select class="crm-stage-select" id="crm-stage-dropdown" title="Pilih Stage Custom">
                    <option value="">Pilih Target Stage...</option>
                </select>
                <button class="crm-save-btn" id="crm-btn-update-stage">💾 Update Stage</button>
            `;

            (document.body || document.documentElement).appendChild(bar);

            document.getElementById('crm-brand-dropdown').addEventListener('change', (e) => {
                selectedAccountId = e.target.value;
                populateStageDropdown();
            });

            document.getElementById('crm-btn-update-stage').addEventListener('click', updateCurrentLeadStage);
            
            populateBrandDropdown();
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

    function populateBrandDropdown() {
        const select = document.getElementById('crm-brand-dropdown');
        if (!select) return;

        select.innerHTML = '';
        accountsData.forEach(acc => {
            const opt = document.createElement('option');
            opt.value = acc.id;
            opt.textContent = `🏢 ${acc.name}`;
            if (acc.id == selectedAccountId) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function populateStageDropdown() {
        const select = document.getElementById('crm-stage-dropdown');
        if (!select) return;

        select.innerHTML = '<option value="">Pilih Target Stage...</option>';

        const currentAcc = accountsData.find(a => a.id == selectedAccountId) || accountsData[0];
        const stages = (currentAcc && currentAcc.pipeline_stages) 
            ? currentAcc.pipeline_stages 
            : [
                { name: 'Inquiry Masuk', order: 1 },
                { name: 'Pitching', order: 2 },
                { name: 'Meeting Call', order: 3 },
                { name: 'Deal', order: 4 },
                { name: 'Lost', order: 5 }
            ];

        stages.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.name;
            opt.textContent = `${s.order ? s.order + '. ' : ''}${s.name}`;
            select.appendChild(opt);
        });
    }

    // Monitor Active Chat Selection in WhatsApp Web UI
    function detectActiveChat() {
        injectFloatingUI();

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
                    if (phoneBadge) phoneBadge.textContent = '+' + digits;
                } else if (titleText.trim().length > 0) {
                    currentCustomerPhone = titleText.trim();
                    if (phoneBadge) phoneBadge.textContent = titleText.substring(0, 16);
                }
            }
        } else {
            if (phoneBadge && phoneBadge.textContent === 'Pilih Chat...') {
                phoneBadge.textContent = 'Buka Chat Customer...';
            }
        }
    }

    // Submit Stage Update to CRM Backend
    async function updateCurrentLeadStage() {
        const select = document.getElementById('crm-stage-dropdown');
        const targetStage = select ? select.value : '';

        if (!currentCustomerPhone || currentCustomerPhone === 'Pilih Chat...') {
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
                    stage: targetStage
                })
            });

            const data = await res.json();
            if (data.status === 'success') {
                showToast(`✅ BERHASIL! Stage ${currentCustomerName || currentCustomerPhone} diupdate ke: ${targetStage}`);
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
