import assert from 'node:assert/strict';
import { once } from 'node:events';
import { randomUUID } from 'node:crypto';
import { request } from 'node:http';
import test from 'node:test';
import { createApp } from '../server.mjs';
import { hashPassword, openStore } from '../store.mjs';

const origin = 'http://127.0.0.1:3000';
const password = 'Mật khẩu dùng riêng cho kiểm thử';

async function fixture(t, appOrigin = origin) {
  appOrigin = new URL(appOrigin).origin;
  const db = openStore(':memory:');
  const server = createApp({ db, origin: appOrigin });
  server.listen(0, '127.0.0.1');
  await once(server, 'listening');
  t.after(async () => {
    await new Promise((resolve) => server.close(resolve));
    db.close();
  });

  function send(path, { method = 'GET', body, cookie, csrf, source = appOrigin, agent } = {}) {
    const payload = body === undefined ? null : JSON.stringify(body);
    const headers = { Host: new URL(appOrigin).host, Origin: source };
    if (cookie) headers.Cookie = cookie;
    if (csrf) headers['X-CSRF-Token'] = csrf;
    if (agent) headers['User-Agent'] = agent;
    if (payload) Object.assign(headers, { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(payload) });
    return new Promise((resolve, reject) => {
      const req = request({ host: '127.0.0.1', port: server.address().port, path, method, headers }, (res) => {
        res.setEncoding('utf8');
        let content = '';
        res.on('data', (chunk) => { content += chunk; });
        res.on('error', reject);
        res.on('end', () => resolve({ status: res.statusCode, headers: res.headers, data: res.headers['content-type']
          .startsWith('application/json') ? JSON.parse(content) : content }));
      });
      req.on('error', reject);
      req.end(payload);
    });
  }

  async function login(email, secret = password, agent) {
    const response = await send('/api/login', { method: 'POST', body: { email, password: secret }, agent });
    assert.equal(response.status, 200);
    const header = response.headers['set-cookie'][0];
    assert.match(header, /HttpOnly; SameSite=Lax; Max-Age=28800/);
    const cookie = header.split(';')[0];
    const { status, data } = await send('/api/me', { cookie });
    assert.equal(status, 200);
    return { cookie, csrf: data.csrf, user: data.user, header };
  }
  return { db, send, login };
}

test('đăng ký, đăng nhập, cập nhật hồ sơ, đổi mật khẩu và đăng xuất', async (t) => {
  const { db, send, login } = await fixture(t);
  const email = 'an@example.test';
  assert.match((await send('/account')).data, /<title>Lzon Poke · Tài khoản cửa hàng<\/title>/);
  assert.match((await send('/')).headers['content-security-policy'], /script-src 'self'/);
  assert.equal((await send('/api/me')).status, 401);
  const registration = await send('/api/register', { method: 'POST', body: { name: 'Nguyễn An', email, password, role: 'admin' } });
  assert.equal(registration.status, 200);
  const duplicate = await send('/api/register', { method: 'POST', body: { name: 'Tên khác', email, password } });
  assert.deepEqual(duplicate.data, registration.data);
  assert.notEqual(db.prepare('SELECT password_hash FROM users').get().password_hash, password);
  const account = await login(email);
  const secondSession = await login(email);
  assert.equal(account.user.role, 'member');
  assert.equal(account.user.name, 'Nguyễn An');
  assert.equal(account.user.password_hash, undefined);
  assert.equal((await send('/api/admin/users', account)).status, 403);
  const profile = { method: 'PATCH', cookie: account.cookie, body: { name: 'An Nguyễn' } };
  assert.equal((await send('/api/me', profile)).status, 403);
  assert.equal((await send('/api/me', { ...profile, csrf: account.csrf, source: 'https://other.example' })).status, 403);
  assert.equal((await send('/api/me', { ...profile, csrf: account.csrf })).status, 200);
  assert.equal((await send('/api/me', account)).data.user.name, 'An Nguyễn');
  const nextPassword = `${password} mới`;
  assert.equal((await send('/api/password', { ...account, method: 'POST', body: { currentPassword: password, password: nextPassword } })).status, 200);
  assert.equal((await send('/api/me', account)).status, 401);
  assert.equal((await send('/api/me', secondSession)).status, 401);
  assert.equal((await send('/api/login', { method: 'POST', body: { email, password } })).status, 401);
  const newSession = await login(email, nextPassword);
  assert.equal((await send('/api/logout', { ...newSession, method: 'POST' })).status, 200);
  assert.equal((await send('/api/me', newSession)).status, 401);
});

test('quản trị viên lọc người dùng, khóa, mở khóa, đổi vai trò và xem nhật ký', async (t) => {
  const { db, send, login } = await fixture(t);
  const adminEmail = 'admin@example.test';
  const email = 'dat@example.test';
  db.prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', ?)")
    .run('Quản trị', adminEmail, await hashPassword(password), Date.now());
  await send('/api/register', { method: 'POST', body: { name: 'Đỗ Thành Đạt', email, password } });
  const admin = await login(adminEmail);
  const member = await login(email);
  const result = await send('/api/admin/users?q=do%20thanh&role=member&status=active', admin);
  assert.equal(result.status, 200);
  assert.equal(result.data.total, 1);
  assert.equal(result.data.users[0].email, email);
  assert.equal(result.data.stats.admins, 1);
  const edit = (id, role, active) => send('/api/admin/users', { ...admin, method: 'PATCH', body: { id, role, active } });
  assert.equal((await edit(admin.user.id, 'member', false)).status, 400);
  assert.equal((await edit(member.user.id, 'member', false)).status, 200);
  assert.equal((await send('/api/me', member)).status, 401);
  assert.equal((await send('/api/login', { method: 'POST', body: { email, password } })).status, 401);
  assert.equal((await edit(member.user.id, 'member', true)).status, 200);
  const restored = await login(email);
  assert.equal((await edit(member.user.id, 'admin', true)).status, 200);
  assert.equal((await send('/api/me', restored)).status, 401);
  const promoted = await login(email);
  assert.equal((await send('/api/admin/users', promoted)).status, 200);
  const { data } = await send('/api/admin/audit', admin);
  for (const event of ['account_disabled', 'account_enabled', 'role_admin']) {
    assert.ok(data.events.some((row) => row.event === event && row.actor === adminEmail && row.target === email));
  }
});

test('giới hạn đăng nhập và gắn thuộc tính Secure khi dùng origin HTTPS', async (t) => {
  const { db, send, login } = await fixture(t, 'https://lzön.example');
  const email = 'admin@example.test';
  db.prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', ?)")
    .run('Quản trị', email, await hashPassword(password), Date.now());
  const { header } = await login(email);
  assert.match(header, /^__Host-vilock=.*; Secure$/);
  for (let attempt = 0; attempt < 10; attempt += 1) {
    assert.equal((await send('/api/login', { method: 'POST', body: { email, password: 'Sai mật khẩu' } })).status, 401);
  }
  assert.equal((await send('/api/login', { method: 'POST', body: { email, password } })).status, 429);
});

test('thành viên xem lịch sử của mình và chỉ được kết thúc phiên khác của chính mình', async (t) => {
  const { send, login } = await fixture(t);
  const email = 'minh@example.test';
  const otherEmail = 'an@example.test';
  for (const address of [email, otherEmail]) {
    await send('/api/register', { method: 'POST', body: { name: 'Thành viên', email: address, password } });
  }
  const first = await login(email, password, 'Mozilla/5.0 Chrome/145.0');
  const second = await login(email, password, 'Mozilla/5.0 Firefox/141.0');
  const other = await login(otherEmail);
  const security = (session) => send('/api/me/security', session);
  const revoke = (session, reference) => send('/api/me/sessions', { ...session, method: 'DELETE', body: { reference } });

  const { data } = await security(first);
  assert.equal(data.sessions.length, 2);
  assert.equal(data.events.length, 3);
  assert.ok(data.lastLogin > 0);
  const browser = data.sessions.find(({ current }) => current);
  const remote = data.sessions.find(({ current }) => !current);
  assert.equal(browser.browser, 'Chrome');
  assert.equal(remote.browser, 'Firefox');
  assert.equal(Object.hasOwn(remote, 'csrf_token'), false);
  assert.equal((await revoke(other, remote.reference)).status, 404);
  assert.equal((await revoke({ cookie: first.cookie }, remote.reference)).status, 403);
  assert.equal((await revoke(first, browser.reference)).status, 400);
  assert.equal((await revoke(first, remote.reference)).status, 200);
  assert.equal((await send('/api/me', second)).status, 401);
  assert.equal((await send('/api/me', first)).status, 200);
  assert.equal((await send('/api/me', other)).status, 200);
  const latest = (await security(first)).data;
  assert.equal(latest.sessions.length, 1);
  assert.equal(latest.events[0].event, 'session_revoked');
  assert.equal((await security(other)).data.events.length, 2);
});

test('đặt hàng dùng giá máy chủ, giới hạn quyền sở hữu và hoàn tồn kho khi hủy', async (t) => {
  const { db, send, login } = await fixture(t);
  const adminEmail = 'admin@example.test';
  const buyerEmail = 'buyer@example.test';
  const otherEmail = 'other@example.test';
  db.prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', ?)")
    .run('Quản trị', adminEmail, await hashPassword(password), Date.now());
  for (const email of [buyerEmail, otherEmail]) {
    await send('/api/register', { method: 'POST', body: { name: 'Người mua', email, password } });
  }
  const buyer = await login(buyerEmail);
  const other = await login(otherEmail);
  const admin = await login(adminEmail);
  const storefront = (await send('/')).data;
  for (const label of ['Lzon Poke', 'Danh mục sản phẩm', 'Flash Sale Figure', 'Sản phẩm đang bán', 'Giao hàng an toàn']) {
    assert.ok(storefront.includes(label));
  }
  const { data: catalog } = await send('/api/shop/products');
  assert.equal(catalog.products.length, 18);
  const { data: range } = await send('/api/shop/products?min=950000&max=2500000&sort=low');
  assert.ok(range.products.length > 1);
  assert.ok(range.products.every(({ price }, index) => price >= 950000 && price <= 2500000 &&
    (index === 0 || price >= range.products[index - 1].price)));
  assert.equal((await send('/api/shop/products?min=5000000&max=1000000')).status, 400);
  const product = catalog.products[0];
  const items = [{ id: product.id, quantity: 1, price: 1 }];
  const wishlist = { ...buyer, method: 'PUT', body: { id: product.id, saved: true } };
  assert.equal((await send('/api/shop/wishlist', wishlist)).status, 200);
  assert.equal((await send('/api/shop/wishlist', other)).data.products.length, 0);
  const body = { items, requestKey: randomUUID(), name: 'Người mua', phone: '0901234567', address: '123 Đường Mô Hình', method: 'QR_CODE', coupon: 'LZON10', total: 1 };
  const place = (input = body) => send('/api/shop/orders', { ...buyer, method: 'POST', body: input });
  assert.equal((await place({ ...body, items: [null] })).status, 400);
  assert.equal((await place({ ...body, items: [{ id: product.id, quantity: product.stock + 1 }] })).status, 409);
  const { status, data } = await place();
  assert.equal(status, 200);
  assert.equal((await place()).data.id, data.id);
  assert.equal((await send('/api/shop/orders', buyer)).data.orders.length, 1);
  const detail = `/api/shop/order?id=${data.id}`;
  const own = (await send(detail, buyer)).data;
  assert.equal(own.items[0].price, product.price);
  assert.equal(own.order.total, product.price - Math.min(Math.floor(product.price / 10), 500_000));
  assert.equal((await send(detail, other)).status, 404);
  const pay = (session) => send('/api/shop/payment', { ...session, method: 'POST', body: { id: data.id } });
  assert.equal((await pay(other)).status, 404);
  assert.equal((await pay(buyer)).status, 200);
  assert.equal((await pay(buyer)).status, 409);
  assert.equal((await send(detail, buyer)).data.order.payment, 'demo');
  assert.equal((await send('/api/admin/shop', buyer)).status, 403);
  const cancel = (session) => send('/api/admin/orders', { ...session, method: 'PATCH', body: { id: data.id, status: 'cancelled' } });
  assert.equal((await cancel(other)).status, 403);
  assert.equal((await send(`/api/shop/product?id=${product.id}`)).data.product.stock, product.stock - 1);
  assert.equal((await cancel(admin)).status, 200);
  assert.equal((await cancel(admin)).status, 409);
  assert.equal((await send(`/api/shop/product?id=${product.id}`)).data.product.stock, product.stock);
  const editProduct = (session, entry) => send('/api/admin/products', { ...session, method: 'PUT', body: entry });
  const edited = { ...product, archived: true, featured: Boolean(product.featured) };
  assert.equal((await editProduct(other, edited)).status, 403);
  assert.equal((await editProduct(admin, edited)).status, 200);
  assert.equal((await send(`/api/shop/product?id=${product.id}`)).status, 404);
  assert.equal((await send(detail, buyer)).data.items[0].name, product.name);
  assert.equal((await editProduct(admin, { ...edited, id: null })).status, 409);
});
