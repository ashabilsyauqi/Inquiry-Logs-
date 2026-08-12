// CRM MVP WHATSAPP WEB CONTENT SCRIPT
(function() {
    console.log("🚀 CRM MVP WhatsApp Web Stage Switcher Extension Loaded!");

    const SERVER_URL = 'http://127.0.0.1:8000';
    let availableStages = [];
    let currentCustomerPhone = null;
    let currentCustomerName = null;

    // Fetch dynamic stages from CRM MVP Backend
    async function loadDynamicStages() {
        try {
            const res = await fetch(`${SERVER_URL}/api/extension/stages`);
            const data = await res.json();
            if (data.status === 'success' && data.accounts) {
                // Collect unique stages across pipeline stages
                const stageSet = new Set();
                data.accounts.forEach(acc => {
                    if (acc.pipeline_stages) {
                        acc.pipeline_stages.forEach(s => stageSet.add(s.name));
                    }
                });
                availableStages = Array.from(stageSet);
                if (availableStages.length === 0) {
                    availableStages = ['Inquiry Masuk', 'Pitching', 'Meeting Call', 'Deal', 'Lost'];
                }
            }
        } catch (err) {
            console.error('Failed to fetch stages from CRM:', err);
            availableStages = ['Inquiry Masuk', 'Pitching', 'Meeting Call', 'Deal', 'Lost'];
        }
    }

    loadDynamicStages();

    // Create & Inject Floating Command Bar
    function injectFloatingBar() {
        if (document.getElementById('crm-wa-floating-bar')) return;

        const bar = document.createElement('div');
        bar.id = 'crm-wa-floating-bar';
        bar.innerHTML = `
            <div class="crm-bar-title">🚀 CRM MVP</div>
            <div class="crm-phone-badge" id="crm-active-lead-phone">Pilih Chat...</div>
            <select class="crm-stage-select" id="crm-stage-dropdown">
                <option value="">Pilih Target Stage...</option>
            </select>
            <button class="crm-save-btn" id="crm-btn-update-stage">💾 Update Stage</button>
        `;

        document.body.appendChild(bar);

        document.getElementById('crm-btn-update-stage').addEventListener('click', updateCurrentLeadStage);
        populateDropdown();
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
        injectFloatingBar();

        // Detect chat header title in WhatsApp Web
        const headerTitleEl = document.querySelector('#main header span[dir="auto"]') || document.querySelector('#main header [role="button"] span');
        const phoneBadge = document.getElementById('crm-active-lead-phone');

        if (headerTitleEl) {
            const titleText = headerTitleEl.textContent || '';
            currentCustomerName = titleText;

            // Extract phone digits if present or use name
            const digits = titleText.replace(/[^0-9]/g, '');
            if (digits.length >= 8) {
                currentCustomerPhone = digits;
                if (phoneBadge) phoneBadge.textContent = '+' + digits;
            } else {
                currentCustomerPhone = titleText;
                if (phoneBadge) phoneBadge.textContent = titleText.substring(0, 15);
            }
        } else {
            if (phoneBadge) phoneBadge.textContent = 'Buka Chat Customer...';
        }
    }

    // Submit Stage Update to CRM Backend
    async function updateCurrentLeadStage() {
        const select = document.getElementById('crm-stage-dropdown');
        const targetStage = select ? select.value : '';

        if (!currentCustomerPhone) {
            return showToast('⚠️ Silakan buka chat customer terlebih dahulu.');
        }
        if (!targetStage) {
            return showToast('⚠️ Pilih target stage terlebih dahulu.');
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
                showToast(`✅ BERHASIL! Stage customer ${currentCustomerName || currentCustomerPhone} diupdate ke: ${targetStage}`);
            } else {
                showToast('❌ Gagal mengupdate stage.');
            }
        } catch (err) {
            console.error('Error updating stage:', err);
            showToast('⚠️ Gagal terhubung ke CRM server.');
        }
    }

    function showToast(message) {
        const existing = document.querySelector('.crm-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'crm-toast';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 4000);
    }

    // DOM Observer to update active chat selection on click
    setInterval(detectActiveChat, 1000);

})();
