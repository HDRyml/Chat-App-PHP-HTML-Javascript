const authView = document.getElementById('auth-view');
const dashboardView = document.getElementById('dashboard-view');
const authAlert = document.getElementById('auth-alert');
const contactAlert = document.getElementById('contact-alert');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const contactForm = document.getElementById('contact-form');
const contactList = document.getElementById('contact-list');

function showAlert(el, message, type = 'error') {
    el.textContent = message;
    el.className = `alert ${type}`;
}

function hideAlert(el) {
    el.textContent = '';
    el.className = 'alert hidden';
}

function switchAuthTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    loginForm.classList.toggle('hidden', tabName !== 'login');
    registerForm.classList.toggle('hidden', tabName !== 'register');
}

document.querySelectorAll('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        hideAlert(authAlert);
        switchAuthTab(btn.dataset.tab);
    });
});

function resetContactForm() {
    document.getElementById('contact-edit-id').value = '';
    document.getElementById('contact-name').value = '';
    document.getElementById('contact-phone').value = '';
    document.getElementById('contact-submit-btn').textContent = 'Tambah Kontak';
    document.getElementById('contact-cancel-btn').classList.add('hidden');
}

async function loadContacts() {
    contactList.innerHTML = '';
    try {
        const res = await Api.request('/api/contacts');
        const contacts = res.data.contacts || [];
        if (contacts.length === 0) {
            const empty = document.createElement('li');
            empty.style.color = '#94a3b8';
            empty.style.fontSize = '0.88rem';
            empty.textContent = 'Belum ada kontak tersimpan.';
            contactList.appendChild(empty);
            return;
        }

        contacts.forEach((c) => {
            const li = document.createElement('li');
            li.className = 'contact-item';

            const info = document.createElement('div');
            info.className = 'contact-info';
            const nameEl = document.createElement('strong');
            nameEl.textContent = c.name;
            const phoneEl = document.createElement('small');
            phoneEl.textContent = c.phone;
            info.append(nameEl, phoneEl);

            const actions = document.createElement('div');
            actions.className = 'contact-actions';

            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'btn-secondary';
            editBtn.textContent = 'Edit';
            editBtn.addEventListener('click', () => {
                hideAlert(contactAlert);
                document.getElementById('contact-edit-id').value = c.id;
                document.getElementById('contact-name').value = c.name;
                document.getElementById('contact-phone').value = c.phone;
                document.getElementById('contact-submit-btn').textContent = 'Simpan Perubahan';
                document.getElementById('contact-cancel-btn').classList.remove('hidden');
            });

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'btn-danger';
            delBtn.textContent = 'Hapus';
            delBtn.addEventListener('click', async () => {
                if (!confirm(`Hapus kontak ${c.name}?`)) return;
                hideAlert(contactAlert);
                try {
                    await Api.request(`/api/contacts/${c.id}`, { method: 'DELETE' });
                    await loadContacts();
                } catch (err) {
                    showAlert(contactAlert, err.message, 'error');
                }
            });

            actions.append(editBtn, delBtn);
            li.append(info, actions);
            contactList.appendChild(li);
        });
    } catch (err) {
        showAlert(contactAlert, err.message, 'error');
    }
}

async function showDashboard(user) {
    document.getElementById('me-name').textContent = user.name;
    document.getElementById('me-phone').textContent = user.phone;
    resetContactForm();
    hideAlert(contactAlert);
    authView.classList.add('hidden');
    dashboardView.classList.remove('hidden');
    await loadContacts();
}

function showAuth() {
    contactList.innerHTML = '';
    resetContactForm();
    hideAlert(contactAlert);
    dashboardView.classList.add('hidden');
    authView.classList.remove('hidden');
}

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert(authAlert);
    const fd = new FormData(registerForm);
    try {
        await Api.request('/api/users', {
            method: 'POST',
            body: {
                name: fd.get('name'),
                phone: fd.get('phone'),
                password: fd.get('password'),
            },
        });
        registerForm.reset();
        switchAuthTab('login');
        showAlert(authAlert, 'Registrasi berhasil! Silakan masuk dengan akun Anda.', 'success');
    } catch (err) {
        showAlert(authAlert, err.message, 'error');
    }
});

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert(authAlert);
    const fd = new FormData(loginForm);
    try {
        const res = await Api.request('/api/auth/login', {
            method: 'POST',
            body: {
                phone: fd.get('phone'),
                password: fd.get('password'),
            },
        });
        Api.setToken(res.data.token);
        loginForm.reset();
        await showDashboard(res.data.user);
    } catch (err) {
        showAlert(authAlert, err.message, 'error');
    }
});

contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert(contactAlert);
    const editId = document.getElementById('contact-edit-id').value;
    const body = {
        name: document.getElementById('contact-name').value,
        phone: document.getElementById('contact-phone').value,
    };

    try {
        if (editId) {
            await Api.request(`/api/contacts/${editId}`, { method: 'PUT', body });
        } else {
            await Api.request('/api/contacts', { method: 'POST', body });
        }
        resetContactForm();
        await loadContacts();
    } catch (err) {
        showAlert(contactAlert, err.message, 'error');
    }
});

document.getElementById('contact-cancel-btn').addEventListener('click', () => {
    resetContactForm();
    hideAlert(contactAlert);
});

document.getElementById('btn-logout').addEventListener('click', () => {
    Api.setToken(null);
    showAuth();
});

document.getElementById('btn-delete-account').addEventListener('click', async () => {
    if (!confirm('Yakin ingin menghapus akun ini?')) return;
    try {
        await Api.request('/api/users/me', { method: 'DELETE' });
        Api.setToken(null);
        showAuth();
        showAlert(authAlert, 'Akun berhasil dihapus.', 'success');
    } catch (err) {
        alert(err.message);
    }
});

// Auto-restore sesi jika token masih tersimpan
(async function init() {
    if (!Api.getToken()) return;
    try {
        const res = await Api.request('/api/users/me');
        await showDashboard(res.data.user);
    } catch {
        Api.setToken(null);
    }
})();
