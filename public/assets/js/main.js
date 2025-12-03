document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    // --- История чата (localStorage) ---
    let chatHistory = JSON.parse(localStorage.getItem('chatHistory')) || [
        { role: 'bot', content: 'Привет! Чем могу помочь?' }
    ];

    function loadHistory() {
        chatMessages.innerHTML = '';
        chatHistory.forEach(message => {
            displayMessage(message.role, message.content, false);
        });
    }

    function displayMessage(role, content, saveToHistory = true) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}-message`;
        messageDiv.innerHTML = renderMarkdown ? renderMarkdown(content) : escapeHtml(content);

        if (typeof hljs !== 'undefined') {
            messageDiv.querySelectorAll('pre code').forEach(block => {
                hljs.highlightElement(block);
            });
        }

        chatMessages.appendChild(messageDiv);

        if (saveToHistory) {
            chatHistory.push({
                role,
                content,
                timestamp: new Date().toISOString()
            });
            saveHistory();
        }

        scrollToBottom();
    }

    function saveHistory() {
        localStorage.setItem('chatHistory', JSON.stringify(chatHistory));
        if (chatHistory.length > 50) {
            chatHistory = chatHistory.slice(-50);
        }
    }

    // fallback если нет renderMarkdown
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // --- Очистка истории ---
    function clearHistory() {
        if (confirm('Очистить всю историю чата и оператора?')) {
            localStorage.removeItem('chatHistory');
            chatHistory = [
                { role: 'bot', content: 'История очищена. Чем могу помочь?' }
            ];
            loadHistory();

            fetch('/api/clear-history', { method: 'POST' })
                .then(res => res.json())
                .catch(err => console.error('Ошибка очистки истории оператора', err));
        }
    }

    // --- Отправка сообщения ---
    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        displayMessage('user', message);
        userInput.value = '';

        showTypingIndicator();

        try {
            const response = await fetch('/api/chat/message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            const data = await response.json();
            displayMessage('bot', data.response);
        } catch (error) {
            displayMessage('bot', '⚠️ Ошибка соединения');
            console.error(error);
        } finally {
            hideTypingIndicator();
        }
    }

    // --- Индикатор "печатает" ---
    function showTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.style.display = 'flex';
            scrollToBottom();
        }
    }
    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.style.display = 'none';
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // --- Polling операторских сообщений ---
    let lastReceived = 0;
    function startPolling() {
        setInterval(async () => {
            if (!window.currentUserId) return;

            try {
                const res = await fetch('/api/get-operator-messages');
                const data = await res.json();

                if (!data.messages) return;

                const newMessages = data.messages.filter((m, index) => index >= lastReceived);

                newMessages.forEach(msg => {
                    displayMessage('operator', msg.text);
                });

                lastReceived = data.messages.length;
            } catch (e) {
                console.error("Ошибка получения операторских сообщений", e);
            }
        }, 3000);
    }

    // --- UI: добавление/удаление формы под кнопками ---
    function createClientInputsContainer() {
        // контейнер под quick-questions
        const quick = document.querySelector('.quick-questions');
        if (!quick) return null;

        // если уже есть — возвращаем существующий
        let existing = document.getElementById('client-info-inline');
        if (existing) return existing;

        const container = document.createElement('div');
        container.id = 'client-info-inline';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        container.style.marginTop = '12px';
        container.style.padding = '8px 0';

        // имя
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.id = 'client-name';
        nameInput.placeholder = 'Ваше имя';
        nameInput.style.padding = '8px';
        nameInput.style.borderRadius = '4px';
        nameInput.style.border = '1px solid #ccc';

        // телефон
        const phoneInput = document.createElement('input');
        phoneInput.type = 'text';
        phoneInput.id = 'client-phone';
        phoneInput.placeholder = 'Телефон, например +7...';
        phoneInput.style.padding = '8px';
        phoneInput.style.borderRadius = '4px';
        phoneInput.style.border = '1px solid #ccc';

        // кнопки
        const controls = document.createElement('div');
        controls.style.display = 'flex';
        controls.style.gap = '8px';

        const saveBtn = document.createElement('button');
        saveBtn.id = 'save-client-info';
        saveBtn.textContent = 'Сохранить и связаться';
        saveBtn.style.padding = '8px 12px';
        saveBtn.style.borderRadius = '6px';
        saveBtn.style.border = 'none';
        saveBtn.style.cursor = 'pointer';
        saveBtn.style.background = '#2b8aef';
        saveBtn.style.color = 'white';

        const cancelBtn = document.createElement('button');
        cancelBtn.id = 'cancel-client-info';
        cancelBtn.textContent = 'Отмена';
        cancelBtn.style.padding = '8px 12px';
        cancelBtn.style.borderRadius = '6px';
        cancelBtn.style.border = '1px solid #ccc';
        cancelBtn.style.cursor = 'pointer';
        cancelBtn.style.background = 'white';

        controls.appendChild(saveBtn);
        controls.appendChild(cancelBtn);

        container.appendChild(nameInput);
        container.appendChild(phoneInput);
        container.appendChild(controls);

        // вставляем после quick-questions
        quick.parentNode.insertBefore(container, quick.nextSibling);

        // возвращаем контейнер
        return container;
    }

    function removeClientInputsContainer() {
        const existing = document.getElementById('client-info-inline');
        if (existing) existing.remove();
    }

    // --- Обработка клика по session-btn ---
    const btn = document.getElementById('session-btn');
    if (btn) {
        btn.addEventListener('click', async () => {
            const closed = btn.dataset.sessionClosed === '1';

            if (closed) {
                // показываем инпуты под кнопками (если ещё не показаны)
                const container = createClientInputsContainer();
                if (!container) return;

                // подставляем сохранённые значения, если есть
                const storedName = localStorage.getItem('clientName') || '';
                const storedPhone = localStorage.getItem('clientPhone') || '';
                container.querySelector('#client-name').value = storedName;
                container.querySelector('#client-phone').value = storedPhone;

                // обработчики кнопок
                const saveBtn = container.querySelector('#save-client-info');
                const cancelBtn = container.querySelector('#cancel-client-info');

                // чтобы не навесить обработчики по многу раз — удаляем старые слушатели (безопасный способ)
                saveBtn.replaceWith(saveBtn.cloneNode(true));
                cancelBtn.replaceWith(cancelBtn.cloneNode(true));

                const newSave = container.querySelector('#save-client-info');
                const newCancel = container.querySelector('#cancel-client-info');

                newSave.addEventListener('click', async () => {
                    const name = container.querySelector('#client-name').value.trim();
                    const phone = container.querySelector('#client-phone').value.trim();

                    if (!name) {
                        alert('Введите имя');
                        return;
                    }
                    if (!phone) {
                        alert('Введите телефон');
                        return;
                    }

                    newSave.disabled = true;
                    newSave.textContent = 'Сохраняю...';

                    try {
                        // сохраняем в localStorage
                        localStorage.setItem('clientName', name);
                        localStorage.setItem('clientPhone', phone);

                        // отправляем на сервер для обновления client_session
                        const res = await fetch('/api/session/set-client-data', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name, phone })
                        });

                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err.message || 'Ошибка сервера');
                        }

                        // после успешного сохранения — открываем сессию
                        const openRes = await fetch('/api/open-session', { method: 'POST' });
                        if (openRes.ok) {
                            // можно обновить страницу или просто обновить текст кнопки
                            // лучше обновить страницу, чтобы сессия и интерфейс были в синхронизации
                            window.location.reload();
                        } else {
                            // если open-session не вернул OK — всё равно закрываем форму
                            removeClientInputsContainer();
                            alert('Сессия не открыта, но данные сохранены.');
                        }
                    } catch (e) {
                        console.error('Ошибка при сохранении client info:', e);
                        alert('Ошибка сохранения данных. Попробуйте ещё раз.');
                        newSave.disabled = false;
                        newSave.textContent = 'Сохранить и связаться';
                    }
                });

                newCancel.addEventListener('click', () => {
                    removeClientInputsContainer();
                });

            } else {
                // Сессия открыта → закрываем
                fetch('/api/clear-session', { method: 'POST' })
                    .then(res => {
                        if (res.status === 204) window.location.reload();
                    })
                    .catch(err => console.error('Ошибка закрытия сессии:', err));
            }
        });
    }

    document.getElementById('clear-btn')?.addEventListener('click', clearHistory);

    // --- Получение userId и запуск polling ---
    async function initUser() {
        try {
            const res = await fetch('/api/get-user');
            const data = await res.json();
            window.currentUserId = data.userId;
            startPolling();
        } catch (err) {
            console.error("Ошибка получения userId:", err);
        }
    }

    // --- Инициализация ---
    loadHistory();
    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    initUser();
});
