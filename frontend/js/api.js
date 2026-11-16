const API_BASE = '../backend';

const Api = {
    getToken() {
        return localStorage.getItem('chat_token');
    },
    setToken(token) {
        if (token) {
            localStorage.setItem('chat_token', token);
        } else {
            localStorage.removeItem('chat_token');
        }
    },
    async request(path, { method = 'GET', body = null } = {}) {
        const headers = {};
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        if (body) {
            headers['Content-Type'] = 'application/json';
        }

        const res = await fetch(`${API_BASE}${path}`, {
            method,
            headers,
            body: body ? JSON.stringify(body) : null,
        });

        const rawText = await res.text();
        let payload = null;
        try {
            payload = JSON.parse(rawText);
        } catch {
            const snippet = rawText.replace(/<[^>]*>/g, ' ').trim().slice(0, 180);
            throw new Error(snippet || `Invalid server response (HTTP ${res.status})`);
        }

        if (!res.ok) {
            throw new Error(payload?.message || `HTTP ${res.status}`);
        }
        return payload;
    },
};
