const app = document.querySelector('#app');
const $ = (selector) => document.querySelector(selector);
const template = (name) => $(`#${name}-template`).content.cloneNode(true);
const roleLabel = { admin: 'Quản trị viên', member: 'Thành viên' };
const viewLabel = { users: 'Người dùng', audit: 'Nhật ký hoạt động', overview: 'Tổng quan',
  profile: 'Hồ sơ cá nhân', sessions: 'Phiên đăng nhập', activity: 'Lịch sử bảo mật', password: 'Đổi mật khẩu' };
const eventLabel = {
  admin_created: 'Tạo quản trị viên', registered: 'Đăng ký tài khoản', login: 'Đăng nhập',
  login_failed: 'Đăng nhập thất bại', logout: 'Đăng xuất', profile_updated: 'Cập nhật hồ sơ',
  password_changed: 'Đổi mật khẩu', role_admin: 'Cấp quyền quản trị', role_member: 'Chuyển về thành viên',
  account_enabled: 'Mở khóa tài khoản', account_disabled: 'Khóa tài khoản', session_revoked: 'Kết thúc phiên khác',
  order_created: 'Đặt đơn hàng', payment_demo: 'Xác nhận thanh toán mô phỏng',
  product_updated: 'Cập nhật sản phẩm', order_status_updated: 'Cập nhật trạng thái đơn hàng',
};
const date = new Intl.DateTimeFormat('vi-VN', { dateStyle: 'medium' });
const dateTime = new Intl.DateTimeFormat('vi-VN', { dateStyle: 'medium', timeStyle: 'short' });
let session;
const requestedPage = new URLSearchParams(location.search).get('next');
const nextPage = ['/checkout', '/orders', '/wishlist', '/shop-admin'].includes(requestedPage) ? requestedPage : null;
const initialMode = new URLSearchParams(location.search).get('mode') === 'register' ? 'register' : 'login';

function element(tag, content, className) {
  const node = document.createElement(tag);
  if (content !== undefined) node.textContent = content;
  if (className) node.className = className;
  return node;
}

function notice(node, message, error = false) {
  if (!node?.isConnected) return;
  node.dataset.error = error;
  node.textContent = message;
  node.hidden = !message;
}

async function api(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json' };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  if (session && method !== 'GET') headers['X-CSRF-Token'] = session.csrf;
  let response;
  try {
    response = await fetch(path, { method, headers, body: JSON.stringify(body) });
  } catch {
    throw new Error('Không kết nối được với máy chủ. Vui lòng thử lại.');
  }
  const data = await response.json();
  if (!response.ok) {
    if (response.status === 401 && session) renderAuth('login', '', data.message, true);
    throw Object.assign(new Error(data.message), { status: response.status });
  }
  return data;
}

function bindForm(form, messageNode, submit) {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('[type="submit"]');
    if (button.disabled) return;
    button.disabled = true;
    notice(messageNode, '');
    try {
      await submit(Object.fromEntries(new FormData(form)));
    } catch (error) {
      notice(messageNode, error.message, true);
    } finally {
      button.disabled = false;
    }
  });
}

function renderAuth(mode = 'login', email = '', message = '', error = false) {
  session = undefined;
  const register = mode === 'register';
  app.replaceChildren(template('auth'));
  app.removeAttribute('aria-busy');
  const form = $('#auth-form');
  const password = $('#auth-password');
  const messageNode = $('#auth-notice');
  const toggle = $('.reveal');
  const switchButton = $('#auth-switch');
  $('#auth-title').textContent = register ? 'Tạo tài khoản' : 'Đăng nhập';
  $('#auth-intro').textContent = register ? 'Tạo tài khoản để đặt hàng và lưu những mẫu figure bạn thích.' : 'Đăng nhập để xem bộ sưu tập yêu thích và theo dõi đơn hàng.';
  $('#auth-submit').textContent = register ? 'Tạo tài khoản' : 'Đăng nhập';
  $('#register-name').hidden = !register;
  form.elements.name.required = register;
  form.elements.email.value = email;
  password.autocomplete = register ? 'new-password' : 'current-password';
  $('#password-hint').hidden = !register;
  if (register) password.setAttribute('aria-describedby', 'password-hint');
  $('#switch-label').textContent = register ? 'Đã có tài khoản?' : 'Chưa có tài khoản?';
  switchButton.textContent = register ? 'Đăng nhập' : 'Đăng ký';
  switchButton.addEventListener('click', () => {
    renderAuth(register ? 'login' : 'register', form.elements.email.value);
    $('#auth-title').focus();
  });
  toggle.addEventListener('click', () => {
    const hidden = password.type === 'password';
    password.type = hidden ? 'text' : 'password';
    toggle.textContent = hidden ? 'Ẩn' : 'Hiện';
    toggle.setAttribute('aria-pressed', String(hidden));
  });
  notice(messageNode, message, error);
  bindForm(form, messageNode, async (body) => {
    switchButton.disabled = true;
    try {
      const result = await api(`/api/${mode}`, { method: 'POST', body });
      if (register) {
        renderAuth('login', body.email, result.message);
        $('#auth-title').focus();
      } else {
        session = await api('/api/me');
        renderShell();
      }
    } finally {
      switchButton.disabled = false;
    }
  });
}

function initial(name) {
  return [...name.trim()][0]?.toLocaleUpperCase('vi') || '?';
}

function updateIdentity() {
  $('#identity-initial').textContent = initial(session.user.name);
  $('#identity-name').textContent = session.user.name;
  $('#identity-role').textContent = roleLabel[session.user.role];
}

function renderShell() {
  if (nextPage) {
    location.replace(nextPage);
    return;
  }
  app.replaceChildren(template('shell'));
  app.removeAttribute('aria-busy');
  updateIdentity();
  const isAdmin = session.user.role === 'admin';
  $('#admin-label').hidden = !isAdmin;
  $('#shop-admin-link').hidden = !isAdmin;
  for (const button of document.querySelectorAll('[data-view]')) {
    const view = button.dataset.view;
    button.hidden = !isAdmin && ['users', 'audit'].includes(view);
    button.addEventListener('click', () => showView(view));
  }
  $('#logout').addEventListener('click', async (event) => {
    const button = event.currentTarget;
    const messageNode = $('#dash-notice');
    button.disabled = true;
    try {
      await api('/api/logout', { method: 'POST' });
      renderAuth('login', '', 'Bạn đã đăng xuất.');
      $('#auth-title').focus();
    } catch (error) {
      notice(messageNode, error.message, true);
    } finally {
      button.disabled = false;
    }
  });
  showView(isAdmin ? 'users' : 'overview');
}

const renderView = { users: renderUsers, audit: renderAudit, overview: renderOverview,
  profile: renderProfile, sessions: renderSessions, activity: renderActivity, password: renderPassword };

function showView(view) {
  const area = $('#view');
  const messageNode = $('#dash-notice');
  for (const button of document.querySelectorAll('[data-view]')) {
    if (button.dataset.view === view) button.setAttribute('aria-current', 'page');
    else button.removeAttribute('aria-current');
  }
  $('#section-label').textContent = viewLabel[view];
  notice(messageNode, '');
  area.replaceChildren(template(view));
  area.querySelector('h1').focus();
  renderView[view](messageNode);
}

function renderProfile(messageNode) {
  const form = $('#profile-form');
  form.elements.name.value = session.user.name;
  form.elements.email.value = session.user.email;
  form.elements.role.value = roleLabel[session.user.role];
  bindForm(form, messageNode, async ({ name }) => {
    const result = await api('/api/me', { method: 'PATCH', body: { name } });
    if (!session) return;
    session.user.name = name.trim();
    updateIdentity();
    if (form.isConnected) notice(messageNode, result.message);
  });
}

function renderPassword(messageNode) {
  const form = $('#password-form');
  bindForm(form, messageNode, async ({ currentPassword, password, confirmation }) => {
    if (password !== confirmation) throw new Error('Mật khẩu mới và phần nhập lại chưa khớp.');
    const result = await api('/api/password', { method: 'POST', body: { currentPassword, password } });
    renderAuth('login', session?.user.email, result.message);
    $('#auth-title').focus();
  });
}

function emptyRow(tbody, columns, message) {
  const row = tbody.insertRow();
  const cell = row.insertCell();
  cell.colSpan = columns;
  cell.className = 'muted';
  cell.textContent = message;
}

async function renderOverview(messageNode) {
  const count = $('#overview-sessions');
  const lastLogin = $('#overview-login');
  $('#overview-greeting').textContent = `Xin chào, ${session.user.name}`;
  $('#overview-email').textContent = session.user.email;
  $('#overview-role').textContent = roleLabel[session.user.role];
  for (const button of $('#view').querySelectorAll('[data-open]')) {
    button.addEventListener('click', () => showView(button.dataset.open));
  }
  try {
    const { sessions, lastLogin: loginTime } = await api('/api/me/security');
    count.textContent = sessions.length;
    lastLogin.textContent = loginTime === null ? '—' : dateTime.format(loginTime);
  } catch (error) {
    if (count.isConnected) notice(messageNode, error.message, true);
  }
}

function renderSessions(messageNode) {
  const rows = $('#session-rows');
  const dialog = $('#revoke-dialog');
  const summary = $('#revoke-summary');
  const dialogNotice = $('#revoke-notice');
  const form = $('#revoke-form');
  let selected;

  async function load() {
    rows.replaceChildren();
    emptyRow(rows, 4, 'Đang tải danh sách phiên…');
    try {
      const { sessions } = await api('/api/me/security');
      if (!rows.isConnected) return;
      rows.replaceChildren();
      if (!sessions.length) emptyRow(rows, 4, 'Không có phiên hoạt động.');
      for (const item of sessions) {
        const row = rows.insertRow();
        row.insertCell().textContent = item.browser;
        row.insertCell().textContent = dateTime.format(item.created_at);
        row.insertCell().textContent = dateTime.format(item.expires_at);
        const action = row.insertCell();
        if (item.current) {
          action.append(element('span', 'Phiên hiện tại', 'pill'));
        } else {
          const revoke = element('button', 'Kết thúc phiên', 'secondary');
          revoke.addEventListener('click', () => {
            selected = item.reference;
            summary.textContent = `Trình duyệt ${item.browser}, đăng nhập lúc ${dateTime.format(item.created_at)}.`;
            notice(dialogNotice, '');
            dialog.showModal();
          });
          action.append(revoke);
        }
      }
    } catch (error) {
      if (!rows.isConnected) return;
      rows.replaceChildren();
      notice(messageNode, error.message, true);
    }
  }

  $('#cancel-revoke').addEventListener('click', () => dialog.close());
  bindForm(form, dialogNotice, async () => {
    const result = await api('/api/me/sessions', { method: 'DELETE', body: { reference: selected } });
    if (!rows.isConnected) return;
    dialog.close();
    notice(messageNode, result.message);
    load();
  });
  load();
}

async function renderActivity(messageNode) {
  const rows = $('#activity-rows');
  emptyRow(rows, 2, 'Đang tải lịch sử…');
  try {
    const { events } = await api('/api/me/security');
    if (!rows.isConnected) return;
    rows.replaceChildren();
    if (!events.length) emptyRow(rows, 2, 'Chưa có hoạt động.');
    for (const event of events) {
      const row = rows.insertRow();
      row.insertCell().textContent = eventLabel[event.event] || event.event;
      row.insertCell().textContent = dateTime.format(event.created_at);
    }
  } catch (error) {
    if (!rows.isConnected) return;
    rows.replaceChildren();
    notice(messageNode, error.message, true);
  }
}

function renderUsers(messageNode) {
  const form = $('#filters');
  const rows = $('#users-rows');
  const previous = $('#previous');
  const next = $('#next');
  const counter = $('#results-count');
  const dialog = $('#edit-dialog');
  const editForm = $('#edit-form');
  const editNotice = $('#edit-notice');
  const stats = [$('#stat-total'), $('#stat-active'), $('#stat-admins')];
  let page = 1;
  let request = 0;
  let selected;
  let filters = new URLSearchParams();

  function edit(user) {
    selected = user;
    dialog.querySelector('#edit-email').textContent = user.email;
    editForm.elements.role.value = user.role;
    editForm.elements.active.value = String(Boolean(user.active));
    notice(editNotice, '');
    dialog.showModal();
  }

  function appendUser(user) {
    const row = rows.insertRow();
    const profile = element('div', undefined, 'person');
    const avatar = element('span', initial(user.name), 'avatar');
    avatar.setAttribute('aria-hidden', 'true');
    const identity = element('div');
    identity.append(element('strong', user.name), element('small', user.email));
    profile.append(avatar, identity);
    row.insertCell().append(profile);
    row.insertCell().textContent = roleLabel[user.role];
    row.insertCell().append(element('span', user.active ? 'Hoạt động' : 'Đã khóa', user.active ? 'pill' : 'pill inactive'));
    row.insertCell().textContent = date.format(user.created_at);
    const action = row.insertCell();
    if (user.id === session.user.id) {
      action.textContent = 'Bạn';
    } else {
      const button = element('button', 'Chỉnh sửa', 'secondary');
      button.setAttribute('aria-label', `Chỉnh sửa ${user.email}`);
      button.addEventListener('click', () => edit(user));
      action.append(button);
    }
  }

  async function load(targetPage) {
    const sequence = ++request;
    previous.disabled = next.disabled = true;
    counter.textContent = 'Đang tải danh sách…';
    const params = new URLSearchParams(filters);
    params.set('page', targetPage);
    try {
      const result = await api(`/api/admin/users?${params}`);
      if (sequence !== request || !rows.isConnected) return;
      const pageCount = Math.max(1, Math.ceil(result.total / 10));
      if (targetPage > pageCount) return load(pageCount);
      page = result.page;
      rows.replaceChildren();
      if (result.users.length) result.users.forEach(appendUser);
      else emptyRow(rows, 5, 'Không tìm thấy tài khoản phù hợp.');
      stats[0].textContent = result.stats.total;
      stats[1].textContent = result.stats.active;
      stats[2].textContent = result.stats.admins;
      previous.disabled = page <= 1;
      next.disabled = page >= pageCount;
      counter.textContent = `${result.total} tài khoản · Trang ${page}/${pageCount}`;
    } catch (error) {
      if (sequence !== request || !rows.isConnected) return;
      counter.textContent = 'Không tải được danh sách.';
      notice(messageNode, error.message, true);
    }
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    filters = new URLSearchParams(new FormData(form));
    notice(messageNode, '');
    load(1);
  });
  previous.addEventListener('click', () => load(page - 1));
  next.addEventListener('click', () => load(page + 1));
  $('#cancel-edit').addEventListener('click', () => dialog.close());
  bindForm(editForm, editNotice, async ({ role, active }) => {
    const result = await api('/api/admin/users', {
      method: 'PATCH', body: { id: selected.id, role, active: active === 'true' },
    });
    if (!dialog.isConnected) return;
    dialog.close();
    notice(messageNode, result.message);
    load(page);
  });
  load(1);
}

async function renderAudit(messageNode) {
  const rows = $('#audit-rows');
  emptyRow(rows, 4, 'Đang tải nhật ký…');
  try {
    const { events } = await api('/api/admin/audit');
    if (!rows.isConnected) return;
    rows.replaceChildren();
    if (!events.length) emptyRow(rows, 4, 'Chưa có sự kiện.');
    for (const event of events) {
      const row = rows.insertRow();
      for (const item of [eventLabel[event.event] || event.event, event.actor, event.target, dateTime.format(event.created_at)]) {
        row.insertCell().textContent = item || '—';
      }
    }
  } catch (error) {
    if (!rows.isConnected) return;
    rows.replaceChildren();
    notice(messageNode, error.message, true);
  }
}

try {
  session = await api('/api/me');
  renderShell();
} catch (error) {
  const showError = error.status !== 401;
  renderAuth(initialMode, '', showError ? error.message : '', showError);
}
