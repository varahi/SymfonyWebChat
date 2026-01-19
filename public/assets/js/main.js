// public/js/main.js
(function () {
    'use strict';

    /* -------------------------
       Конфигурация / Константы
    --------------------------*/
    const CONFIG = {
        endpoints: {
            sendMessage: '/api/chat/message',
            getOperatorMessages: '/api/get-operator-messages',
            getUser: '/api/get-user',
            setClientData: '/api/session/set-client-data',
            openSession: '/api/open-session',
            closeSession: '/api/close-session',
            adminCloseSession: '/api/admin-close-session',
            clearHistory: '/api/clear-history',
        },
        pollingIntervalMs: 3000,
        maxHistoryItems: 50
    };

    /* -------------------------
       Helpers
    --------------------------*/
    const h = {
        qs: (sel, root = document) => root.querySelector(sel),
        qsa: (sel, root = document) => Array.from(root.querySelectorAll(sel)),
        el: (tag, attrs = {}) => {
            const e = document.createElement(tag);
            Object.entries(attrs).forEach(([k, v]) => {
                if (k === 'text') e.textContent = v;
                else if (k === 'html') e.innerHTML = v;
                else e.setAttribute(k, String(v));
            });
            return e;
        },
        escapeHtml: (s = '') => String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;'),
        safeHtmlOrMarkdown: (content) => {
            if (typeof renderMarkdown === 'function') return renderMarkdown(content);
            return h.escapeHtml(content);
        },
        nowIso: () => new Date().toISOString()
    };

    /* -------------------------
       API wrapper
    --------------------------*/
    const Api = {
        async postJson(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return res;
        },
        async sendMessage(message) {
            const res = await this.postJson(CONFIG.endpoints.sendMessage, { message });
            return res.json();
        },
        async getOperatorMessages() {
            const res = await fetch(CONFIG.endpoints.getOperatorMessages, { method: 'GET' });
            return res.json();
        },
        async getUser() {
            const res = await fetch(CONFIG.endpoints.getUser, { method: 'GET' });
            return res.json();
        },
        async setClientData(name, phone) {
            return this.postJson(CONFIG.endpoints.setClientData, { name, phone });
        },
        async openSession() {
            return fetch(CONFIG.endpoints.openSession, { method: 'POST' });
        },
        async closeSession() {
            return fetch(CONFIG.endpoints.closeSession, { method: 'POST' });
        },
        async adminCloseSession() {
            return fetch(CONFIG.endpoints.adminCloseSession, { method: 'POST' });
        },
        async clearServerHistory() {
            return fetch(CONFIG.endpoints.clearHistory, { method: 'POST' });
        }
    };

    /* -------------------------
        Close session by admin
    --------------------------*/
    const adminBtns = h.qsa('.btn-primary-admin');

    adminBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const sessionId = btn.dataset.sessionId;
            if (!sessionId) return;

            if (!confirm('Вы уверены, что хотите завершить эту сессию?')) return;

            btn.disabled = true;
            btn.textContent = 'Завершаем...';

            try {
                const res = await fetch(CONFIG.endpoints.adminCloseSession, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session: sessionId })
                });

                if (!res.ok) throw new Error('Ошибка сервера при завершении сессии');

                // ✅ Динамически меняем состояние кнопки
                btn.textContent = 'Завершено';
                btn.disabled = true;
                btn.dataset.sessionClosed = '1';
                btn.style.background = '#6c757d'; // можно сменить цвет, чтобы визуально показать, что завершено

            } catch (err) {
                console.error(err);
                alert(err.message || 'Не удалось завершить сессию');
                btn.disabled = false;
                btn.textContent = 'Завершить связь';
            }
        });
    });


    /* -------------------------
       History manager (localStorage)
    --------------------------*/
    const History = (function () {
        const KEY = 'chatHistory';
        function load() {
            try {
                const raw = localStorage.getItem(KEY);
                return raw ? JSON.parse(raw) : [{ role: 'bot', content: 'Привет! Чем могу помочь?', timestamp: h.nowIso() }];
            } catch (e) {
                console.error('Ошибка загрузки истории из localStorage', e);
                return [{ role: 'bot', content: 'Привет! Чем могу помочь?', timestamp: h.nowIso() }];
            }
        }
        function save(items) {
            localStorage.setItem(KEY, JSON.stringify(items));
        }
        let items = load();

        return {
            all: () => items.slice(),
            push: (msg) => {
                items.push(msg);
                if (items.length > CONFIG.maxHistoryItems) items = items.slice(-CONFIG.maxHistoryItems);
                save(items);
            },
            replaceAll: (newItems) => {
                items = newItems.slice();
                save(items);
            },
            clear: () => {
                items = [{ role: 'bot', content: 'История очищена. Чем могу помочь?', timestamp: h.nowIso() }];
                save(items);
            }
        };
    })();

    /* -------------------------
       UI renderer
    --------------------------*/
    const UI = (function () {
        const chatMessages = h.qs('#chat-messages');
        const sessionBtn = h.qs('#session-btn');
        function scrollToBottom() {
            if (!chatMessages) return;
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        function renderMessage(role, content) {
            const wrapper = h.el('div');
            wrapper.className = `message ${role}-message`;

            // содержание (markdown если есть, иначе безопасный html)
            wrapper.innerHTML = h.safeHtmlOrMarkdown(content);

            // (опционально) timestamp small
            const timeEl = h.el('div', { class: 'message-time', text: new Date().toLocaleString() });
            timeEl.style.fontSize = '10px';
            timeEl.style.opacity = '0.6';
            timeEl.style.marginTop = '6px';
            wrapper.appendChild(timeEl);

            chatMessages.appendChild(wrapper);

            if (typeof hljs !== 'undefined') {
                wrapper.querySelectorAll('pre code').forEach(block => hljs.highlightElement(block));
            }

            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function displayMessage(role, content, save = true) {
            renderMessage(role, content);
            if (save) {
                History.push({ role, content, timestamp: h.nowIso() });
            }
        }

        // function loadHistoryToUi() {
        //     chatMessages.innerHTML = '';
        //     History.all().forEach(m => renderMessage(m.role, m.content));
        // }

        function loadHistoryToUi() {
            chatMessages.innerHTML = '';
            History.all().forEach(m => renderMessage(m.role, m.content));

            // 👇 ВАЖНО: прижимаем чат к низу при загрузке iframe
            scrollToBottom();
        }

        function ensureClientInputsShown(prefill = {}) {
            // создаёт встроенную форму под quick-questions (или возвращает существующую)
            let container = h.qs('#client-info-inline');
            if (container) return container;

            const quick = h.qs('.quick-questions');
            if (!quick) return null;

            container = h.el('div', { id: 'client-info-inline' });
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '8px';
            container.style.marginTop = '12px';
            container.style.padding = '8px 0';

            const nameInput = h.el('input');
            nameInput.id = 'client-name';
            nameInput.placeholder = 'Ваше имя';
            nameInput.value = prefill.name || '';
            Object.assign(nameInput.style, { padding: '8px', borderRadius: '4px', border: '1px solid #ccc' });

            const phoneInput = h.el('input');
            phoneInput.id = 'client-phone';
            applyPhoneMask(phoneInput);
            phoneInput.placeholder = 'Телефон, например +7...';
            phoneInput.value = prefill.phone || '';
            Object.assign(phoneInput.style, { padding: '8px', borderRadius: '4px', border: '1px solid #ccc' });

            const controls = h.el('div');
            controls.style.display = 'flex';
            controls.style.gap = '8px';

            const saveBtn = h.el('button', { id: 'save-client-info', text: 'Сохранить и связаться' });
            Object.assign(saveBtn.style, { padding: '8px 12px', borderRadius: '6px', border: 'none', cursor: 'pointer', background: '#2b8aef', color: 'white' });

            const cancelBtn = h.el('button', { id: 'cancel-client-info', text: 'Отмена' });
            Object.assign(cancelBtn.style, { padding: '8px 12px', borderRadius: '6px', border: '1px solid #ccc', cursor: 'pointer', background: 'white' });

            controls.appendChild(saveBtn);
            controls.appendChild(cancelBtn);

            container.appendChild(nameInput);
            container.appendChild(phoneInput);
            container.appendChild(controls);

            quick.parentNode.insertBefore(container, quick.nextSibling);

            return container;
        }

        function removeClientInputs() {
            const existing = h.qs('#client-info-inline');
            if (existing) existing.remove();
        }

        function updateSessionBtnState(isClosed) {
            if (!sessionBtn) return;
            sessionBtn.dataset.sessionClosed = isClosed ? '1' : '0';
            sessionBtn.textContent = isClosed ? 'Связаться с оператором' : 'Завершить связь';
        }

        function applyPhoneMask(input) {
            input.addEventListener('input', () => {
                let v = input.value.replace(/\D/g, ''); // оставляем только цифры

                // если начинается не с 7 — делаем +7
                if (v.startsWith('8')) v = '7' + v.slice(1);
                if (!v.startsWith('7')) v = '7' + v;

                let formatted = '+7';

                if (v.length > 1) formatted += ' (' + v.slice(1, 4);
                if (v.length >= 4) formatted += ') ' + v.slice(4, 7);
                if (v.length >= 7) formatted += '-' + v.slice(7, 9);
                if (v.length >= 9) formatted += '-' + v.slice(9, 11);

                input.value = formatted;
            });
        }

        return {
            displayMessage,
            loadHistoryToUi,
            ensureClientInputsShown,
            removeClientInputs,
            updateSessionBtnState,
            applyPhoneMask,
            get sessionBtn() { return sessionBtn; }
        };
    })();

    /* -------------------------
       Session manager
    --------------------------*/
    const SessionManager = (function () {
        let initializing = false;

        async function openSessionWithClientData(name, phone, uiBtnRef) {
            // save locally first
            try {
                localStorage.setItem('clientName', name);
                localStorage.setItem('clientPhone', phone);
            } catch (e) {
                console.warn('Ошибка сохранения в localStorage', e);
            }

            // persist to server
            const res = await Api.setClientData(name, phone);
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Ошибка сервера при сохранении client data');
            }

            // open server session
            const openRes = await Api.openSession();
            if (!openRes.ok) {
                throw new Error('Ошибка открытия сессии на сервере');
            }

            // success — update UI: show system/operator message and update button
            UI.removeClientInputs();
            UI.displayMessage('operator', 'Вы связались с оператором');
            UI.updateSessionBtnState(false);

            // may start polling if not started yet (Polling module handles its own start)
            return true;
        }

        async function closeSessionGracefully() {
            const res = await Api.closeSession();
            if (!res.ok) {
                throw new Error('Ошибка закрытия сессии');
            }

            UI.displayMessage('operator', 'Вы завершили связь с оператором');
            UI.updateSessionBtnState(true);
            return true;
        }

        return {
            openSessionWithClientData,
            closeSessionGracefully
        };
    })();

    /* -------------------------
       Polling manager
    --------------------------*/
    const Polling = (function () {
        let timerId = null;
        let lastCount = 0;

        async function pollOnce() {
            try {
                const data = await Api.getOperatorMessages();
                if (!data || !Array.isArray(data.messages)) return;
                const messages = data.messages;

                // show only new messages
                const newItems = messages.slice(lastCount);
                newItems.forEach(m => UI.displayMessage('operator', m.text));
                lastCount = messages.length;
            } catch (e) {
                console.error('Polling error', e);
            }
        }

        function start() {
            if (timerId) return;
            // initial immediate poll
            pollOnce();
            timerId = setInterval(pollOnce, CONFIG.pollingIntervalMs);
        }

        function stop() {
            if (!timerId) return;
            clearInterval(timerId);
            timerId = null;
        }

        return { start, stop };
    })();

    /* -------------------------
       Wiring / Initialization
    --------------------------*/
    async function init() {
        // cache DOM references and load history to UI
        UI.loadHistoryToUi();

        // attach send handlers
        const sendBtn = h.qs('#send-button');
        const input = h.qs('#user-input');

        if (sendBtn) sendBtn.addEventListener('click', onSend);
        if (input) input.addEventListener('keypress', (e) => { if (e.key === 'Enter') onSend(); });

        // clear history
        h.qs('#clear-btn')?.addEventListener('click', async () => {
            if (!confirm('Очистить всю историю чата и оператора?')) return;
            History.clear();
            UI.loadHistoryToUi();
            try { await Api.clearServerHistory(); } catch (e) { console.warn('clear server history failed', e); }
        });

        // session button behaviour: show inline form or close session
        const sessionBtn = UI.sessionBtn;
        if (sessionBtn) {
            sessionBtn.addEventListener('click', async () => {
                const isClosed = sessionBtn.dataset.sessionClosed === '1';

                if (isClosed) {
                    // show inline inputs
                    const container = UI.ensureClientInputsShown({
                        name: localStorage.getItem('clientName') || '',
                        phone: localStorage.getItem('clientPhone') || ''
                    });
                    if (!container) return;

                    // ensure unique listeners (remove/replace node)
                    const saveBtn = container.querySelector('#save-client-info');
                    const cancelBtn = container.querySelector('#cancel-client-info');

                    // replace to remove previous listeners if any
                    const newSave = saveBtn.cloneNode(true);
                    const newCancel = cancelBtn.cloneNode(true);
                    saveBtn.replaceWith(newSave);
                    cancelBtn.replaceWith(newCancel);

                    newSave.addEventListener('click', async (ev) => {
                        ev.preventDefault();
                        const name = container.querySelector('#client-name').value.trim();
                        const phone = container.querySelector('#client-phone').value.trim();

                        if (!name) { alert('Введите имя'); return; }
                        if (!phone) { alert('Введите телефон'); return; }

                        newSave.disabled = true;
                        newSave.textContent = 'Сохраняю...';
                        try {
                            await SessionManager.openSessionWithClientData(name, phone, sessionBtn);
                            // ensure polling started
                            Polling.start();
                        } catch (err) {
                            console.error(err);
                            alert(err.message || 'Ошибка сохранения данных. Попробуйте ещё раз.');
                            newSave.disabled = false;
                            newSave.textContent = 'Сохранить и связаться';
                        }
                    });

                    newCancel.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        UI.removeClientInputs();
                    });

                } else {
                    // session is open -> close
                    try {
                        await SessionManager.closeSessionGracefully();
                        // optionally stop polling (we might want to keep polling for new operator messages — here we stop)
                        Polling.stop();
                    } catch (err) {
                        console.error(err);
                        alert('Не удалось завершить связь. Попробуйте ещё раз.');
                    }
                }
            });
        }

        // init user & polling
        try {
            const u = await Api.getUser();
            window.currentUserId = u.userId;
            // Start polling (it will skip if nothing to show)
            Polling.start();
        } catch (e) {
            console.warn('Не удалось получить userId', e);
        }
    }

    /* -------------------------
       Send handler
    --------------------------*/
    async function onSend() {
        const input = h.qs('#user-input');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;

        UI.displayMessage('user', text);
        input.value = '';

        // typing indicator (if present)
        const typing = h.qs('#typing-indicator');
        if (typing) typing.style.display = 'flex';

        try {
            const resp = await Api.sendMessage(text);
            // if (resp && resp.response) UI.displayMessage('bot', resp.response);
            // else UI.displayMessage('bot', 'Ответ пуст или ошибка сервера');

            if (!resp || !('response' in resp)) {
                UI.displayMessage('bot', 'Ответ пуст или ошибка сервера');
            } else if (resp.response) {
                // есть текст — показываем
                UI.displayMessage('bot', resp.response);
            }

        } catch (e) {
            console.error('Send message error', e);
            UI.displayMessage('bot', '⚠️ Ошибка соединения');
        } finally {
            if (typing) typing.style.display = 'none';
        }
    }

    // start
    document.addEventListener('DOMContentLoaded', init);

    // expose some for debugging (optional)
    window.__chatApi = Api;
    window.__chatUI = UI;
    window.__chatHistory = History;

})();
