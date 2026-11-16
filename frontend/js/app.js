const authView = document.getElementById('auth-view');
const dashboardView = document.getElementById('dashboard-view');
const authAlert = document.getElementById('auth-alert');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');

function showAlert(message, type = 'error') {
    authAlert.textContent = message;
    authAlert.className = `alert ${type}`;
}

function hideAlert() {
    authAlert.textContent = '';
    authAlert.className = 'alert hidden';
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
        hideAlert();
        switchAuthTab(btn.dataset.tab);
    });
});

function showDashboard(user) {
    document.getElementById('me-id').textContent = user.id;
    document.getElementById('me-name').textContent = user.name;
    document.getElementById('me-phone').textContent = user.phone;
    authView.classList.add('hidden');
    dashboardView.classList.remove('hidden');
}

function showAuth() {
    dashboardView.classList.add('hidden');
    authView.classList.remove('hidden');
}

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert();
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
        showAlert('Registrasi berhasil! Silakan masuk dengan akun Anda.', 'success');
    } catch (err) {
        showAlert(err.message, 'error');
    }
});

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert();
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
        showDashboard(res.data.user);
    } catch (err) {
        showAlert(err.message, 'error');
    }
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
        showAlert('Akun berhasil dihapus.', 'success');
    } catch (err) {
        alert(err.message);
    }
});

// Auto-restore sesi jika token masih tersimpan
(async function init() {
    if (!Api.getToken()) return;
    try {
        const res = await Api.request('/api/users/me');
        showDashboard(res.data.user);
    } catch {
        Api.setToken(null);
    }
})();
