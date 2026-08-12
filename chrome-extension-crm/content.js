// CRM MVP WHATSAPP WEB CONTENT SCRIPT (ENHANCED V1.0.1)
(function() {
    console.log("🚀 CRM MVP WhatsApp Web Stage Switcher Extension Loaded!");

    const SERVER_URL = 'http://127.0.0.1:8000';
    let availableStages = ['Inquiry Masuk', 'Pitching', 'Meeting Call', 'Deal', 'Lost'];
    let currentCustomerPhone = null;
    let currentCustomerName = null;

    // Fetch dynamic stages from CRM MVP Backend
    async function loadDynamicStages() {
        try {
            const res = await fetch(`${SERVER_URL}/api/extension/stages`);
            const data = await res.json();
            if (data.status === 'success' && data.accounts) {
                const stageSet = new Set();
                data.accounts.forEach(acc => {
                    if (acc.pipeline_stages) {
                        acc.pipeline_stages.forEach(s => stageSet.add(s.name));
                    }
                });
                if (stageSet.size > 0) {
                    availableStages = Array.from(stageSet);
                }
            }
        } catch (err) {
            console.log('CRM API offline or CORS, using default stages');
        }
        populateDropdown();
    }

    // Create & Inject Floating Bar and Floating Action Button (FAB)
    function injectFloatingUI() {
        if (!document.getElementById('crm-wa-floating-bar')) {
            const bar = document.createElement('div');
            bar.id = 'crm-wa-floating-bar';
            bar.innerHTML = `
                <div class="crm-bar-title">🚀 CRM MVP</div>
                <div class="crm-phone-badge" id="crm-active-lead-phone">Pilih Chat Customer...</div>
                <select class="crm-stage-select" id="crm-stage-dropdown">
                    <option value="">Pilih Target Stage...</option>
                </select>
                <button class="crm-save-btn" id="crm-btn-update-stage">💾 Update Stage</button>
            `;

            (document.body || document.documentElement).appendChild(bar);
            document.getElementById('crm-btn-update-stage').addEventListener('click', updateCurrentLeadStage);
            populateDropdown();
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

    function populateDropdown() {
        const select = document.getElementById('crm-stage-dropdown');
        if (!select) return;

        select.innerHTML = '<option value="">Pilih Target Stage...</option>';
        availableStages.forEach(st => {
            const opt = document.createElement('option');
            opt.value = st;
            opt.textContent = st;
            select.appendChild(opt);
        });
    }

    // Monitor Active Chat Selection in WhatsApp Web UI
    function detectActiveChat() {
        injectFloatingUI();

        // Query active chat title / header in WA Web DOM
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
            if (phoneBadge && phoneBadge.textContent === 'Pilih Chat Customer...') {
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
