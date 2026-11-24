(function () {
    'use strict';

    var config = window.COPILOT_CONFIG || {};
    var BASE_URL = String(config.baseUrl || '').replace(/\/+$/, '');
    var MAX_CHARS = Number(config.maxChars) > 0 ? Number(config.maxChars) : 2000;

    var TOOL_LABELS = {
        get_jatuh_tempo_cicilan: 'Jatuh Tempo Cicilan',
        get_booking_per_bulan: 'Booking per Bulan',
        get_konversi_booking_akad: 'Konversi Booking-Akad'
    };

    var QUICK_PROMPTS = [
        {
            tool: 'get_jatuh_tempo_cicilan',
            requiresPermission: true,
            label: 'Cicilan jatuh tempo 7 hari',
            prompt: 'Tampilkan cicilan jatuh tempo 7 hari ke depan'
        },
        {
            tool: 'get_booking_per_bulan',
            requiresPermission: false,
            label: 'Booking per bulan',
            prompt: 'Rekap booking per bulan tahun ini'
        },
        {
            tool: 'get_konversi_booking_akad',
            requiresPermission: false,
            label: 'Konversi booking-akad',
            prompt: 'Berapa persentase konversi booking ke akad tahun ini?'
        }
    ];

    var state = {
        csrfHash: String(config.csrfHash || ''),
        meta: null,
        messages: [],
        isSending: false,
        isLinkingTelegram: false,
        isUnlinkingTelegram: false,
        isSavingSettings: false,
        activeTab: 'chat',
        selectedProviderId: '',
        pairingCode: ''
    };

    var dom = {};

    function byId(id) {
        return document.getElementById(id);
    }

    function apiUrl(path) {
        return BASE_URL + '/' + String(path || '').replace(/^\/+/, '');
    }

    function updateCsrfToken(payload) {
        if (payload && typeof payload.token === 'string' && payload.token !== '') {
            state.csrfHash = payload.token;
        }
    }

    function formatDateTime(value) {
        if (!value) {
            return '-';
        }
        var normalized = String(value).trim().replace(' ', 'T');
        var date = new Date(normalized);
        if (isNaN(date.getTime())) {
            return String(value);
        }
        return date.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function extractErrorMessage(json, status) {
        var msg = (json && (json.messages || json.message)) ? String(json.messages || json.message) : '';
        if (json && json.errors && typeof json.errors === 'object') {
            var firstKey = Object.keys(json.errors)[0];
            if (firstKey && json.errors[firstKey]) {
                msg = String(json.errors[firstKey]);
            }
        }
        if (msg !== '') {
            return msg;
        }
        if (status === 401) {
            return 'Sesi login Anda telah berakhir. Silakan login kembali.';
        }
        if (status === 403) {
            return 'Anda tidak memiliki izin untuk mengakses fitur ini.';
        }
        if (status === 429) {
            return 'Batas permintaan sementara tercapai. Silakan tunggu beberapa saat sebelum mencoba lagi.';
        }
        if (status === 502) {
            return 'Provider AI belum siap atau API key server belum dikonfigurasi. Hubungi Admin.';
        }
        return 'Permintaan gagal diproses. Silakan coba beberapa saat lagi.';
    }

    async function apiRequest(path, options) {
        var opts = options ? Object.assign({}, options) : {};
        var method = String(opts.method || 'GET').toUpperCase();
        var headers = Object.assign({
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }, opts.headers || {});

        if (state.csrfHash) {
            headers['X-CSRF-TOKEN'] = state.csrfHash;
        }

        if (opts.jsonBody !== undefined) {
            headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(opts.jsonBody);
            delete opts.jsonBody;
        }

        opts.method = method;
        opts.headers = headers;
        opts.credentials = 'same-origin';

        var response = await fetch(apiUrl(path), opts);
        var json = null;
        try {
            json = await response.json();
        } catch (err) {
            json = null;
        }

        updateCsrfToken(json);

        if (!response.ok || (json && json.success === false)) {
            var error = new Error(extractErrorMessage(json, response.status));
            error.status = response.status;
            error.payload = json;
            throw error;
        }

        return (json && json.data !== undefined) ? json.data : json;
    }

    function appendInlineFormattedText(parent, line) {
        var pattern = /(\*\*[^*]+\*\*|`[^`]+`)/g;
        var lastIndex = 0;
        var match;

        while ((match = pattern.exec(line)) !== null) {
            if (match.index > lastIndex) {
                parent.appendChild(document.createTextNode(line.slice(lastIndex, match.index)));
            }
            var token = match[0];
            if (token.indexOf('**') === 0 && token.lastIndexOf('**') === token.length - 2 && token.length > 4) {
                var strong = document.createElement('strong');
                strong.textContent = token.slice(2, -2);
                parent.appendChild(strong);
            } else if (token.indexOf('`') === 0 && token.lastIndexOf('`') === token.length - 1 && token.length > 2) {
                var code = document.createElement('code');
                code.className = 'copilot-msg-inline-code';
                code.textContent = token.slice(1, -1);
                parent.appendChild(code);
            } else {
                parent.appendChild(document.createTextNode(token));
            }
            lastIndex = pattern.lastIndex;
        }

        if (lastIndex < line.length) {
            parent.appendChild(document.createTextNode(line.slice(lastIndex)));
        }
    }

    function renderSafeFormattedText(container, rawText, isAssistant) {
        var text = String(rawText == null ? '' : rawText).replace(/\r\n/g, '\n');

        if (!isAssistant) {
            var userLines = text.split('\n');
            var userP = document.createElement('p');
            userP.className = 'copilot-msg-p';
            userLines.forEach(function (line, index) {
                if (index > 0) {
                    userP.appendChild(document.createElement('br'));
                }
                userP.appendChild(document.createTextNode(line));
            });
            container.appendChild(userP);
            return;
        }

        var lines = text.split('\n');
        var listEl = null;
        var listTag = '';
        var paragraphEl = null;

        function flushParagraph() {
            if (paragraphEl) {
                container.appendChild(paragraphEl);
                paragraphEl = null;
            }
        }

        function flushList() {
            if (listEl) {
                container.appendChild(listEl);
                listEl = null;
                listTag = '';
            }
        }

        lines.forEach(function (line) {
            var trimmed = line.trim();
            var ulMatch = /^\s*[-*•]\s+(.+)$/.exec(line);
            var olMatch = /^\s*\d+[.)]\s+(.+)$/.exec(line);

            if (trimmed === '') {
                flushParagraph();
                flushList();
                return;
            }

            if (ulMatch) {
                flushParagraph();
                if (!listEl || listTag !== 'ul') {
                    flushList();
                    listEl = document.createElement('ul');
                    listEl.className = 'copilot-msg-list';
                    listTag = 'ul';
                }
                var ulItem = document.createElement('li');
                appendInlineFormattedText(ulItem, ulMatch[1]);
                listEl.appendChild(ulItem);
                return;
            }

            if (olMatch) {
                flushParagraph();
                if (!listEl || listTag !== 'ol') {
                    flushList();
                    listEl = document.createElement('ol');
                    listEl.className = 'copilot-msg-list';
                    listTag = 'ol';
                }
                var olItem = document.createElement('li');
                appendInlineFormattedText(olItem, olMatch[1]);
                listEl.appendChild(olItem);
                return;
            }

            flushList();
            if (!paragraphEl) {
                paragraphEl = document.createElement('p');
                paragraphEl.className = 'copilot-msg-p';
            } else {
                paragraphEl.appendChild(document.createElement('br'));
            }
            appendInlineFormattedText(paragraphEl, line);
        });

        flushParagraph();
        flushList();

        if (!container.hasChildNodes()) {
            var emptyP = document.createElement('p');
            emptyP.className = 'copilot-msg-p';
            emptyP.textContent = text;
            container.appendChild(emptyP);
        }
    }

    function createMessageNode(msg) {
        var role = msg.role === 'user' ? 'user' : 'assistant';
        var wrapper = document.createElement('div');
        wrapper.className = 'copilot-msg copilot-msg--' + role;
        if (msg.localId) {
            wrapper.setAttribute('data-local-id', msg.localId);
        }

        var bubble = document.createElement('div');
        bubble.className = 'copilot-msg-bubble';
        renderSafeFormattedText(bubble, msg.content, role === 'assistant');
        wrapper.appendChild(bubble);

        var meta = document.createElement('div');
        meta.className = 'copilot-msg-meta';

        var who = document.createElement('span');
        who.textContent = role === 'user' ? 'Anda' : 'AI Copilot';
        meta.appendChild(who);

        if (msg.created_at) {
            var timeSpan = document.createElement('span');
            timeSpan.className = 'copilot-msg-time';
            timeSpan.textContent = '· ' + formatDateTime(msg.created_at);
            meta.appendChild(timeSpan);
        }

        var toolsUsed = (msg.metadata && Array.isArray(msg.metadata.tools_used))
            ? msg.metadata.tools_used
            : [];
        toolsUsed.forEach(function (toolName) {
            var tag = document.createElement('span');
            tag.className = 'copilot-msg-tool-tag';
            tag.textContent = TOOL_LABELS[toolName] || toolName;
            tag.title = toolName;
            meta.appendChild(tag);
        });

        wrapper.appendChild(meta);
        return wrapper;
    }

    function scrollMessagesToBottom() {
        if (dom.messages) {
            dom.messages.scrollTop = dom.messages.scrollHeight;
        }
    }

    function renderMessages() {
        if (!dom.messages) {
            return;
        }

        while (dom.messages.firstChild) {
            dom.messages.removeChild(dom.messages.firstChild);
        }

        if (!state.messages || state.messages.length === 0) {
            if (dom.emptyState) {
                dom.emptyState.hidden = false;
                dom.messages.appendChild(dom.emptyState);
            }
            return;
        }

        if (dom.emptyState) {
            dom.emptyState.hidden = true;
        }

        state.messages.forEach(function (msg) {
            dom.messages.appendChild(createMessageNode(msg));
        });

        scrollMessagesToBottom();
    }

    function showChatError(message) {
        if (!dom.chatError || !dom.chatErrorText) {
            return;
        }
        dom.chatErrorText.textContent = message;
        dom.chatError.hidden = false;
    }

    function hideChatError() {
        if (dom.chatError) {
            dom.chatError.hidden = true;
        }
    }

    function setSendingState(sending) {
        state.isSending = sending;
        if (dom.input) {
            dom.input.disabled = sending;
        }
        if (dom.sendBtn) {
            dom.sendBtn.disabled = sending;
        }
        if (dom.typingIndicator) {
            dom.typingIndicator.hidden = !sending;
        }
        if (dom.quickPrompts) {
            var chips = dom.quickPrompts.querySelectorAll('.copilot-prompt-chip');
            Array.prototype.forEach.call(chips, function (chip) {
                chip.disabled = sending;
            });
        }
        if (sending) {
            scrollMessagesToBottom();
        }
    }

    function updateCharCounter() {
        if (!dom.input || !dom.charCount) {
            return;
        }
        var length = dom.input.value.length;
        dom.charCount.textContent = length + ' / ' + MAX_CHARS;
        if (length >= Math.floor(MAX_CHARS * 0.9)) {
            dom.charCount.classList.add('is-near-limit');
        } else {
            dom.charCount.classList.remove('is-near-limit');
        }
    }

    function renderProviderBadges(meta) {
        var providerConfig = (meta && meta.provider_config) ? meta.provider_config : {};
        var providers = Array.isArray(providerConfig.available_providers) ? providerConfig.available_providers : [];
        var activeId = String(providerConfig.provider || '');
        var activeModel = String(providerConfig.model || '');

        var activeSpec = providers.find(function (p) {
            return p && p.id === activeId;
        }) || null;

        if (dom.providerBadge) {
            var providerLabel = activeSpec && activeSpec.label ? activeSpec.label : (activeId || 'Provider');
            var textSpan = dom.providerBadge.querySelector('span');
            if (textSpan) {
                textSpan.textContent = activeModel ? (providerLabel + ' · ' + activeModel) : providerLabel;
            }
        }

        if (dom.keyBadge) {
            var hasKey = Boolean(activeSpec && activeSpec.has_api_key);
            dom.keyBadge.className = 'copilot-pill ' + (hasKey ? 'copilot-pill--success' : 'copilot-pill--warning');
            var keySpan = dom.keyBadge.querySelector('span');
            if (keySpan) {
                keySpan.textContent = hasKey ? 'API Key .env: Aktif' : 'API Key .env: Belum Diatur';
            }
        }
    }

    function renderProjectsAndTools(meta) {
        var projects = (meta && Array.isArray(meta.accessible_projects)) ? meta.accessible_projects : [];
        var allowedTools = (meta && Array.isArray(meta.allowed_tools)) ? meta.allowed_tools : [];

        if (dom.projectsList) {
            while (dom.projectsList.firstChild) {
                dom.projectsList.removeChild(dom.projectsList.firstChild);
            }

            if (projects.length === 0) {
                var noProj = document.createElement('span');
                noProj.className = 'copilot-chip copilot-chip--muted';
                noProj.textContent = 'Belum ada proyek ditugaskan';
                dom.projectsList.appendChild(noProj);
            } else {
                var maxVisible = 10;
                projects.slice(0, maxVisible).forEach(function (project) {
                    var chip = document.createElement('span');
                    chip.className = 'copilot-chip';
                    chip.textContent = project.nama_proyek || ('Proyek #' + project.id_proyek);
                    dom.projectsList.appendChild(chip);
                });

                if (projects.length > maxVisible) {
                    var extraChip = document.createElement('span');
                    extraChip.className = 'copilot-chip copilot-chip--tool';
                    extraChip.textContent = '+' + (projects.length - maxVisible) + ' proyek lainnya';
                    extraChip.title = projects.map(function (p) {
                        return p.nama_proyek || ('Proyek #' + p.id_proyek);
                    }).join(', ');
                    dom.projectsList.appendChild(extraChip);
                }
            }
        }

        if (dom.toolsList) {
            while (dom.toolsList.firstChild) {
                dom.toolsList.removeChild(dom.toolsList.firstChild);
            }

            if (allowedTools.length === 0) {
                var noTool = document.createElement('span');
                noTool.className = 'copilot-chip copilot-chip--muted';
                noTool.textContent = 'Tidak ada tool aktif';
                dom.toolsList.appendChild(noTool);
            } else {
                allowedTools.forEach(function (toolName) {
                    var chip = document.createElement('span');
                    chip.className = 'copilot-chip copilot-chip--tool';
                    chip.title = toolName;

                    var labelSpan = document.createElement('span');
                    labelSpan.textContent = TOOL_LABELS[toolName] || toolName;
                    chip.appendChild(labelSpan);

                    var codeSpan = document.createElement('span');
                    codeSpan.className = 'copilot-chip-code';
                    codeSpan.textContent = '(' + toolName + ')';
                    chip.appendChild(codeSpan);

                    dom.toolsList.appendChild(chip);
                });
            }
        }

        renderQuickPrompts(allowedTools);
    }

    function renderQuickPrompts(allowedTools) {
        if (!dom.quickPrompts) {
            return;
        }
        var tools = Array.isArray(allowedTools) ? allowedTools : [];
        while (dom.quickPrompts.firstChild) {
            dom.quickPrompts.removeChild(dom.quickPrompts.firstChild);
        }

        QUICK_PROMPTS.forEach(function (item) {
            if (item.requiresPermission && tools.indexOf(item.tool) === -1) {
                return;
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'copilot-prompt-chip';
            btn.setAttribute('data-prompt', item.prompt);
            btn.disabled = state.isSending;

            var icon = document.createElement('i');
            icon.className = 'fas fa-bolt';
            icon.setAttribute('aria-hidden', 'true');
            btn.appendChild(icon);

            var span = document.createElement('span');
            span.textContent = item.label;
            btn.appendChild(span);

            btn.addEventListener('click', function () {
                if (state.isSending || !dom.input) {
                    return;
                }
                dom.input.value = item.prompt;
                updateCharCounter();
                sendChatMessage(item.prompt);
            });

            dom.quickPrompts.appendChild(btn);
        });
    }

    function renderTelegramStatus(telegram) {
        var tg = telegram || {};
        var isLinked = Boolean(tg.linked);

        if (dom.telegramStatusBadge) {
            dom.telegramStatusBadge.className = 'copilot-pill ' + (isLinked ? 'copilot-pill--success' : 'copilot-pill--neutral');
            dom.telegramStatusBadge.textContent = isLinked ? 'Terhubung' : 'Belum Terhubung';
        }

        if (dom.telegramLinkedBox) {
            dom.telegramLinkedBox.hidden = !isLinked;
        }
        if (dom.telegramUnlinkedBox) {
            dom.telegramUnlinkedBox.hidden = isLinked;
        }

        if (isLinked) {
            state.pairingCode = '';
            if (dom.telegramCodeBox) {
                dom.telegramCodeBox.hidden = true;
            }
            if (dom.telegramPendingNote) {
                dom.telegramPendingNote.hidden = true;
            }
            if (dom.telegramUsername) {
                dom.telegramUsername.textContent = tg.telegram_username
                    ? ('@' + String(tg.telegram_username).replace(/^@/, ''))
                    : 'Akun Telegram Terverifikasi';
            }
            if (dom.telegramVerifiedAt) {
                dom.telegramVerifiedAt.textContent = formatDateTime(tg.verified_at);
            }
            return;
        }

        if (dom.telegramPendingNote) {
            if (tg.pending_expires_at && !state.pairingCode) {
                dom.telegramPendingNote.textContent = 'Ada kode tautan aktif yang berlaku hingga ' + formatDateTime(tg.pending_expires_at) + '. Buat kode baru jika Anda belum menyalinnya.';
                dom.telegramPendingNote.hidden = false;
            } else {
                dom.telegramPendingNote.hidden = true;
            }
        }
    }

    function renderSelectedProviderKeyStatus() {
        if (!dom.settingKeyStatus || !dom.settingProvider || !state.meta) {
            return;
        }
        var providers = (state.meta.provider_config && Array.isArray(state.meta.provider_config.available_providers))
            ? state.meta.provider_config.available_providers
            : [];
        var selectedId = dom.settingProvider.value;
        var spec = providers.find(function (p) {
            return p && p.id === selectedId;
        });

        while (dom.settingKeyStatus.firstChild) {
            dom.settingKeyStatus.removeChild(dom.settingKeyStatus.firstChild);
        }

        if (!spec) {
            return;
        }

        var badge = document.createElement('span');
        badge.className = 'copilot-pill ' + (spec.has_api_key ? 'copilot-pill--success' : 'copilot-pill--warning');
        badge.textContent = spec.has_api_key
            ? 'API key .env terkonfigurasi'
            : 'API key .env belum diisi di server';
        dom.settingKeyStatus.appendChild(badge);
    }

    function renderAdminSettings(meta) {
        var isAdmin = Boolean(meta && meta.is_admin === true);

        if (dom.settingsPanel) {
            dom.settingsPanel.hidden = !isAdmin;
        }
        if (dom.tabSettingsItem) {
            dom.tabSettingsItem.hidden = !isAdmin;
        }

        if (!isAdmin) {
            if (state.activeTab === 'settings') {
                switchMobileTab('chat');
            }
            return;
        }

        var providerConfig = meta.provider_config || {};
        var providers = Array.isArray(providerConfig.available_providers) ? providerConfig.available_providers : [];

        if (dom.settingProvider) {
            while (dom.settingProvider.firstChild) {
                dom.settingProvider.removeChild(dom.settingProvider.firstChild);
            }
            providers.forEach(function (spec) {
                var option = document.createElement('option');
                option.value = spec.id;
                option.textContent = spec.label + (spec.has_api_key ? ' (API Key OK)' : ' (Belum ada API Key)');
                dom.settingProvider.appendChild(option);
            });
            dom.settingProvider.value = providerConfig.provider || '';
            state.selectedProviderId = dom.settingProvider.value;
        }

        if (dom.modelSuggestions) {
            while (dom.modelSuggestions.firstChild) {
                dom.modelSuggestions.removeChild(dom.modelSuggestions.firstChild);
            }
            var seenModels = {};
            providers.forEach(function (spec) {
                if (spec.default_model && !seenModels[spec.default_model]) {
                    seenModels[spec.default_model] = true;
                    var opt = document.createElement('option');
                    opt.value = spec.default_model;
                    opt.label = spec.label;
                    dom.modelSuggestions.appendChild(opt);
                }
            });
        }

        if (dom.settingModel) {
            dom.settingModel.value = providerConfig.model || '';
        }
        if (dom.settingBaseUrl) {
            dom.settingBaseUrl.value = providerConfig.base_url || '';
            var activeSpec = providers.find(function (p) {
                return p && p.id === (providerConfig.provider || '');
            });
            dom.settingBaseUrl.placeholder = (activeSpec && activeSpec.default_base_url)
                ? activeSpec.default_base_url
                : 'Kosongkan untuk default provider';
        }

        renderSelectedProviderKeyStatus();
    }

    function renderPolicy(policy) {
        var pol = policy || {};
        var maxMsg = Number(pol.rate_limit_max) > 0 ? Number(pol.rate_limit_max) : 20;
        var winMin = Number(pol.rate_limit_window_minutes) > 0 ? Number(pol.rate_limit_window_minutes) : 10;
        var retentionDays = Number(pol.retention_days) > 0 ? Number(pol.retention_days) : 90;

        if (dom.policyRateLimit) {
            dom.policyRateLimit.textContent = 'Maksimal ' + maxMsg + ' pesan per ' + winMin + ' menit.';
        }
        if (dom.policyRetention) {
            dom.policyRetention.textContent = 'Riwayat percakapan disimpan selama ' + retentionDays + ' hari.';
        }
    }

    function renderMeta(meta) {
        state.meta = meta;
        renderProviderBadges(meta);
        renderProjectsAndTools(meta);
        renderTelegramStatus(meta ? meta.telegram : null);
        renderAdminSettings(meta);
        renderPolicy(meta ? meta.policy : null);
    }

    async function loadMeta() {
        try {
            var data = await apiRequest('/api/copilot/meta');
            renderMeta(data);
        } catch (err) {
            showChatError(err.message || 'Gagal memuat status AI Copilot.');
        }
    }

    async function loadHistory() {
        try {
            var data = await apiRequest('/api/copilot/history?limit=30');
            state.messages = Array.isArray(data && data.messages) ? data.messages : [];
            renderMessages();
        } catch (err) {
            showChatError(err.message || 'Gagal memuat riwayat percakapan.');
        }
    }

    async function sendChatMessage(rawText) {
        var text = String(rawText == null ? '' : rawText).trim();
        if (!text) {
            showChatError('Silakan ketik pertanyaan terlebih dahulu.');
            return;
        }
        if (text.length > MAX_CHARS) {
            showChatError('Pesan maksimal ' + MAX_CHARS + ' karakter.');
            return;
        }
        if (state.isSending) {
            return;
        }

        hideChatError();

        var localId = 'local-' + Date.now();
        var optimisticMsg = {
            localId: localId,
            role: 'user',
            content: text,
            created_at: new Date().toISOString()
        };

        state.messages.push(optimisticMsg);
        renderMessages();

        if (dom.input) {
            dom.input.value = '';
            updateCharCounter();
        }

        setSendingState(true);

        try {
            var result = await apiRequest('/api/copilot/chat', {
                method: 'POST',
                jsonBody: { message: text }
            });

            state.messages.push({
                role: 'assistant',
                content: result && result.reply ? result.reply : '',
                metadata: {
                    provider: result ? result.provider : '',
                    model: result ? result.model : '',
                    tools_used: (result && Array.isArray(result.tools_used)) ? result.tools_used : []
                },
                created_at: new Date().toISOString()
            });
            renderMessages();
        } catch (err) {
            if (!err.status || err.status === 422 || err.status === 429 || err.status === 401 || err.status === 403) {
                state.messages = state.messages.filter(function (m) {
                    return m.localId !== localId;
                });
                renderMessages();
                if (dom.input && dom.input.value.trim() === '') {
                    dom.input.value = text;
                    updateCharCounter();
                }
            }
            showChatError(err.message || 'Gagal memproses pertanyaan AI Copilot.');
        } finally {
            setSendingState(false);
            if (dom.input) {
                dom.input.focus();
            }
        }
    }

    async function handleGenerateTelegramLink() {
        if (state.isLinkingTelegram) {
            return;
        }
        state.isLinkingTelegram = true;
        if (dom.telegramLinkBtn) {
            dom.telegramLinkBtn.disabled = true;
        }

        try {
            var data = await apiRequest('/api/copilot/telegram/link', {
                method: 'POST'
            });
            var code = String(data && data.code ? data.code : '');
            state.pairingCode = code;

            if (dom.telegramCode) {
                dom.telegramCode.textContent = code;
            }
            if (dom.telegramCommand) {
                dom.telegramCommand.textContent = '/link ' + code;
            }
            if (dom.telegramStepCmd) {
                dom.telegramStepCmd.textContent = '/link ' + code;
            }
            if (dom.telegramExpires) {
                var mins = (data && data.expires_in_minutes) ? data.expires_in_minutes : 10;
                dom.telegramExpires.textContent = 'Berlaku ' + mins + ' menit (hingga ' + formatDateTime(data ? data.expires_at : '') + ')';
            }
            if (dom.telegramCodeBox) {
                dom.telegramCodeBox.hidden = false;
            }
            if (dom.telegramPendingNote) {
                dom.telegramPendingNote.hidden = true;
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal membuat kode', text: err.message });
            } else {
                showChatError(err.message);
            }
        } finally {
            state.isLinkingTelegram = false;
            if (dom.telegramLinkBtn) {
                dom.telegramLinkBtn.disabled = false;
            }
        }
    }

    async function copyTextToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (err) {
                // Fallback below
            }
        }
        var temp = document.createElement('textarea');
        temp.value = text;
        temp.style.position = 'fixed';
        temp.style.left = '-9999px';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.select();
        var copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (err) {
            copied = false;
        }
        document.body.removeChild(temp);
        return copied;
    }

    async function handleCopyTelegramCommand() {
        var code = state.pairingCode || (dom.telegramCode ? dom.telegramCode.textContent.trim() : '');
        if (!code || code === '------') {
            return;
        }
        var commandText = '/link ' + code;
        var ok = await copyTextToClipboard(commandText);

        if (ok && dom.telegramCopyBtn) {
            var copyLabel = byId('copilot-telegram-copy-label');
            if (copyLabel) {
                copyLabel.textContent = 'Tersalin!';
                setTimeout(function () {
                    copyLabel.textContent = 'Salin /link';
                }, 1800);
            }
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: ok ? 'success' : 'info',
                title: ok ? 'Perintah Disalin' : 'Salin Manual',
                text: ok ? ('Perintah "' + commandText + '" disalin ke clipboard.') : commandText,
                timer: 1800,
                showConfirmButton: false
            });
        }
    }

    async function confirmAction(title, text) {
        if (typeof Swal !== 'undefined') {
            var result = await Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                showCancelButton: true,
                confirmButtonText: 'Ya, Putuskan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626'
            });
            return Boolean(result && result.isConfirmed);
        }
        return window.confirm(text);
    }

    async function handleUnlinkTelegram() {
        if (state.isUnlinkingTelegram) {
            return;
        }

        var confirmed = await confirmAction(
            'Putuskan Tautan Telegram?',
            'Akun Telegram Anda tidak akan bisa mengakses AI Copilot sampai ditautkan ulang.'
        );
        if (!confirmed) {
            return;
        }

        state.isUnlinkingTelegram = true;
        if (dom.telegramUnlinkBtn) {
            dom.telegramUnlinkBtn.disabled = true;
        }

        try {
            await apiRequest('/api/copilot/telegram/link', {
                method: 'DELETE'
            });
            state.pairingCode = '';
            await loadMeta();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Tautan Diputuskan',
                    text: 'Tautan Telegram berhasil diputuskan.',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal Memutuskan Tautan', text: err.message });
            } else {
                showChatError(err.message);
            }
        } finally {
            state.isUnlinkingTelegram = false;
            if (dom.telegramUnlinkBtn) {
                dom.telegramUnlinkBtn.disabled = false;
            }
        }
    }

    function showSettingsFeedback(message, isSuccess) {
        if (!dom.settingsFeedback) {
            return;
        }
        dom.settingsFeedback.className = 'copilot-inline-feedback ' +
            (isSuccess ? 'copilot-inline-feedback--success' : 'copilot-inline-feedback--error');
        dom.settingsFeedback.textContent = message;
        dom.settingsFeedback.hidden = false;
    }

    function handleProviderChange() {
        if (!dom.settingProvider || !state.meta) {
            return;
        }
        var providers = (state.meta.provider_config && Array.isArray(state.meta.provider_config.available_providers))
            ? state.meta.provider_config.available_providers
            : [];
        var prevId = state.selectedProviderId;
        var nextId = dom.settingProvider.value;

        var prevSpec = providers.find(function (p) { return p && p.id === prevId; });
        var nextSpec = providers.find(function (p) { return p && p.id === nextId; });

        if (dom.settingModel && nextSpec) {
            var currentModel = dom.settingModel.value.trim();
            if (currentModel === '' || (prevSpec && currentModel === prevSpec.default_model)) {
                dom.settingModel.value = nextSpec.default_model || '';
            }
        }

        if (dom.settingBaseUrl && nextSpec) {
            var currentBaseUrl = dom.settingBaseUrl.value.trim();
            if (currentBaseUrl === '' || (prevSpec && currentBaseUrl === prevSpec.default_base_url)) {
                dom.settingBaseUrl.value = nextSpec.default_base_url || '';
            }
            dom.settingBaseUrl.placeholder = nextSpec.default_base_url || 'Kosongkan untuk default provider';
        }

        state.selectedProviderId = nextId;
        renderSelectedProviderKeyStatus();
    }

    async function handleSaveSettings(event) {
        event.preventDefault();
        if (state.isSavingSettings || !dom.settingProvider || !dom.settingModel) {
            return;
        }

        var provider = dom.settingProvider.value.trim();
        var model = dom.settingModel.value.trim();
        var baseUrl = dom.settingBaseUrl ? dom.settingBaseUrl.value.trim() : '';

        if (!provider || !model) {
            showSettingsFeedback('Provider dan nama model wajib diisi.', false);
            return;
        }

        state.isSavingSettings = true;
        if (dom.settingsSaveBtn) {
            dom.settingsSaveBtn.disabled = true;
        }
        if (dom.settingsFeedback) {
            dom.settingsFeedback.hidden = true;
        }

        try {
            await apiRequest('/api/copilot/settings', {
                method: 'POST',
                jsonBody: {
                    provider: provider,
                    model: model,
                    base_url: baseUrl !== '' ? baseUrl : null
                }
            });

            await loadMeta();
            showSettingsFeedback('Pengaturan provider AI berhasil disimpan.', true);
        } catch (err) {
            showSettingsFeedback(err.message || 'Gagal menyimpan pengaturan AI.', false);
        } finally {
            state.isSavingSettings = false;
            if (dom.settingsSaveBtn) {
                dom.settingsSaveBtn.disabled = false;
            }
        }
    }

    function switchMobileTab(tabName) {
        state.activeTab = tabName;

        var tabButtons = document.querySelectorAll('#copilot-mobile-tabs [data-copilot-tab]');
        Array.prototype.forEach.call(tabButtons, function (btn) {
            var isTarget = btn.getAttribute('data-copilot-tab') === tabName;
            btn.classList.toggle('active', isTarget);
            btn.setAttribute('aria-selected', isTarget ? 'true' : 'false');
        });

        var panels = document.querySelectorAll('.copilot-page [data-copilot-panel]');
        Array.prototype.forEach.call(panels, function (panel) {
            var isTarget = panel.getAttribute('data-copilot-panel') === tabName;
            panel.classList.toggle('is-active', isTarget);
        });
    }

    function bindEvents() {
        if (dom.chatForm) {
            dom.chatForm.addEventListener('submit', function (event) {
                event.preventDefault();
                sendChatMessage(dom.input ? dom.input.value : '');
            });
        }

        if (dom.input) {
            dom.input.addEventListener('input', updateCharCounter);
            dom.input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendChatMessage(dom.input.value);
                }
            });
        }

        if (dom.chatErrorClose) {
            dom.chatErrorClose.addEventListener('click', hideChatError);
        }

        if (dom.refreshMetaBtn) {
            dom.refreshMetaBtn.addEventListener('click', function () {
                loadMeta();
            });
        }

        if (dom.historyRefreshBtn) {
            dom.historyRefreshBtn.addEventListener('click', function () {
                loadHistory();
            });
        }

        if (dom.telegramLinkBtn) {
            dom.telegramLinkBtn.addEventListener('click', handleGenerateTelegramLink);
        }

        if (dom.telegramCopyBtn) {
            dom.telegramCopyBtn.addEventListener('click', handleCopyTelegramCommand);
        }

        if (dom.telegramRefreshBtn) {
            dom.telegramRefreshBtn.addEventListener('click', loadMeta);
        }

        if (dom.telegramUnlinkBtn) {
            dom.telegramUnlinkBtn.addEventListener('click', handleUnlinkTelegram);
        }

        if (dom.settingProvider) {
            dom.settingProvider.addEventListener('change', handleProviderChange);
        }

        if (dom.settingsForm) {
            dom.settingsForm.addEventListener('submit', handleSaveSettings);
        }

        var tabButtons = document.querySelectorAll('#copilot-mobile-tabs [data-copilot-tab]');
        Array.prototype.forEach.call(tabButtons, function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-copilot-tab') || 'chat';
                switchMobileTab(target);
            });
        });
    }

    function cacheDom() {
        dom.providerBadge = byId('copilot-provider-badge');
        dom.keyBadge = byId('copilot-key-badge');
        dom.refreshMetaBtn = byId('copilot-refresh-meta-btn');
        dom.projectsList = byId('copilot-projects-list');
        dom.toolsList = byId('copilot-tools-list');

        dom.messages = byId('copilot-messages');
        dom.emptyState = byId('copilot-empty-state');
        dom.typingIndicator = byId('copilot-typing-indicator');
        dom.chatError = byId('copilot-chat-error');
        dom.chatErrorText = byId('copilot-chat-error-text');
        dom.chatErrorClose = byId('copilot-chat-error-close');
        dom.historyRefreshBtn = byId('copilot-history-refresh-btn');

        dom.quickPrompts = byId('copilot-quick-prompts');
        dom.chatForm = byId('copilot-chat-form');
        dom.input = byId('copilot-input');
        dom.charCount = byId('copilot-char-count');
        dom.sendBtn = byId('copilot-send-btn');

        dom.telegramStatusBadge = byId('copilot-telegram-status-badge');
        dom.telegramUnlinkedBox = byId('copilot-telegram-unlinked');
        dom.telegramPendingNote = byId('copilot-telegram-pending-note');
        dom.telegramCodeBox = byId('copilot-telegram-code-box');
        dom.telegramCode = byId('copilot-telegram-code');
        dom.telegramExpires = byId('copilot-telegram-expires');
        dom.telegramCommand = byId('copilot-telegram-command');
        dom.telegramStepCmd = byId('copilot-telegram-step-cmd');
        dom.telegramCopyBtn = byId('copilot-telegram-copy-btn');
        dom.telegramLinkBtn = byId('copilot-telegram-link-btn');
        dom.telegramRefreshBtn = byId('copilot-telegram-refresh-btn');
        dom.telegramLinkedBox = byId('copilot-telegram-linked');
        dom.telegramUsername = byId('copilot-telegram-username');
        dom.telegramVerifiedAt = byId('copilot-telegram-verified-at');
        dom.telegramUnlinkBtn = byId('copilot-telegram-unlink-btn');

        dom.settingsPanel = byId('copilot-settings-panel');
        dom.tabSettingsItem = byId('copilot-tab-settings-item');
        dom.settingsForm = byId('copilot-settings-form');
        dom.settingProvider = byId('copilot-setting-provider');
        dom.settingKeyStatus = byId('copilot-setting-key-status');
        dom.settingModel = byId('copilot-setting-model');
        dom.modelSuggestions = byId('copilot-model-suggestions');
        dom.settingBaseUrl = byId('copilot-setting-base-url');
        dom.settingsFeedback = byId('copilot-settings-feedback');
        dom.settingsSaveBtn = byId('copilot-settings-save-btn');

        dom.policyRateLimit = byId('copilot-policy-rate-limit');
        dom.policyRetention = byId('copilot-policy-retention');
    }

    function init() {
        cacheDom();
        if (!dom.chatForm) {
            return;
        }
        updateCharCounter();
        bindEvents();
        loadMeta();
        loadHistory();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
