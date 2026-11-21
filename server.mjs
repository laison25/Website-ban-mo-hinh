import { createServer } from 'node:http';
import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';
import { timingSafeEqual } from 'node:crypto';
import { images, initializeShop, shopRoutes } from './shop.mjs';
import { digest, hashPassword, normalizeEmail, normalizeName, openStore, randomToken, recordAudit,
  transaction, validEmail, validName, validPassword, verifyPassword } from './store.mjs';

const sessionLifetime = 8 * 60 * 60 * 1000;
const rateWindow = 15 * 60 * 1000;
const publicColumns = 'id, name, email, role, active, created_at';
const assets = new Map([
  ['/account', ['index.html', 'text/html']],
  ...['/', '/product', '/cart', '/checkout', '/orders', '/order', '/wishlist', '/shop-admin'].map((path) => [path, ['shop.html', 'text/html']]),
  ['/shop.css', ['shop.css', 'text/css']],
  ['/shop.js', ['shop.js', 'text/javascript']],
  ['/app.css', ['app.css', 'text/css']],
  ['/app.js', ['app.js', 'text/javascript']],
].map(([url, [file, type]]) => [url, [type, readFileSync(new URL(`./public/${file}`, import.meta.url))]]));
for (const image of [...images, 'logo.webp', 'main-banner.jpg', 'login-lzon-poke.webp', 'login-lzon-poke-portrait.webp']) {
  assets.set(`/shop-images/${image}`, [image.endsWith('.webp') ? 'image/webp' : 'image/jpeg',
    readFileSync(new URL(`./public/shop-images/${image}`, import.meta.url))]);
}
const publicRoutes = new Set(['/api/register', '/api/login', '/api/shop/products', '/api/shop/product', '/api/shop/quote']);

class HttpError extends Error {
  constructor(status, message) {
    super(message);
    this.status = status;
  }
}

function fail(status, message) {
  throw new HttpError(status, message);
}

async function readJson(req) {
  if (req.headers['content-type']?.split(';')[0].trim() !== 'application/json') {
    fail(415, 'Yêu cầu phải gửi dữ liệu JSON.');
  }
  const chunks = [];
  let size = 0;
  // Keep the connection available to send the 413 response before closing it.
  for await (const chunk of req.iterator({ destroyOnReturn: false })) {
    size += chunk.length;
    if (size > 32 * 1024) fail(413, 'Dữ liệu gửi lên quá lớn.');
    chunks.push(chunk);
  }
  let value;
  try {
    value = JSON.parse(Buffer.concat(chunks).toString('utf8'));
  } catch {
    fail(400, 'Dữ liệu JSON không hợp lệ.');
  }
  if (!value || typeof value !== 'object' || Array.isArray(value)) fail(400, 'Dữ liệu không hợp lệ.');
  return value;
}

function sameToken(left, right) {
  return typeof left === 'string' && /^[\w-]{43}$/.test(left) &&
    timingSafeEqual(Buffer.from(left), Buffer.from(right));
}

function browserName(agent = '') {
  if (/\b(?:Edg|EdgA|EdgiOS)\//.test(agent)) return 'Microsoft Edge';
  if (/\b(?:OPR|Opera)\//.test(agent)) return 'Opera';
  if (/\b(?:Chrome|CriOS)\//.test(agent)) return 'Chrome';
  if (/\b(?:Firefox|FxiOS)\//.test(agent)) return 'Firefox';
  if (/\bSafari\//.test(agent)) return 'Safari';
  return 'Không xác định';
}

const sessionReference = (storedId) => digest(`session-reference:${storedId}`);

export function createApp({ db, origin }) {
  initializeShop(db);
  const expectedUrl = new URL(origin);
  const secureCookie = expectedUrl.protocol === 'https:';
  const cookieName = secureCookie ? '__Host-vilock' : 'vilock';
  const attempts = new Map();
  let hashing = 0;
  const cookie = (value, age) => `${cookieName}=${value}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${age}${secureCookie ? '; Secure' : ''}`;

  const cleanup = setInterval(() => {
    const now = Date.now();
    for (const [key, item] of attempts) if (item.until <= now) attempts.delete(key);
    db.prepare('DELETE FROM sessions WHERE expires_at <= ?').run(now);
  }, 60_000).unref();

  function limit(req, route, maximum) {
    const key = `${req.socket.remoteAddress}:${route}`;
    const now = Date.now();
    let entry = attempts.get(key);
    if (!entry || entry.until <= now) {
      if (!entry && attempts.size >= 10_000) fail(429, 'Máy chủ đang bận. Vui lòng thử lại sau.');
      entry = { count: 0, until: now + rateWindow };
      attempts.set(key, entry);
    }
    if (entry.count >= maximum) fail(429, 'Quá nhiều yêu cầu. Vui lòng thử lại sau.');
    entry.count += 1;
    return key;
  }

  async function passwordWork(operation) {
    if (hashing >= 3) fail(503, 'Máy chủ đang bận. Vui lòng thử lại sau.');
    hashing += 1;
    try {
      return await operation();
    } finally {
      hashing -= 1;
    }
  }

  const routes = new Map([
    ...shopRoutes(db, { fail, readJson, requireSession }),
    ['POST /api/register', async ({ req }) => {
      limit(req, 'register', 8);
      const body = await readJson(req);
      const name = normalizeName(body.name);
      const email = normalizeEmail(body.email);
      if (!validName(name) || !validEmail(email)) fail(400, 'Họ tên hoặc email không hợp lệ.');
      if (!validPassword(body.password)) fail(400, 'Mật khẩu phải có từ 15 đến 128 ký tự.');
      const hash = await passwordWork(() => hashPassword(body.password));
      // Keep the response identical for existing and new addresses.
      transaction(db, () => {
        const result = db.prepare(`INSERT INTO users (name, email, password_hash, role, created_at)
          VALUES (?, ?, ?, 'member', ?) ON CONFLICT(email) DO NOTHING`).run(name, email, hash, Date.now());
        if (result.changes) recordAudit(db, 'registered', email, email);
      });
      return { message: 'Nếu email có thể đăng ký, tài khoản đã được tạo. Bạn có thể thử đăng nhập.' };
    }],
    ['POST /api/login', async ({ req, res, sessionId }) => {
      const key = limit(req, 'login', 10);
      const body = await readJson(req);
      const email = normalizeEmail(body.email);
      if (!validEmail(email) || typeof body.password !== 'string' || [...body.password].length > 128) {
        fail(401, 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị khóa.');
      }
      const user = db.prepare('SELECT id, email, password_hash FROM users WHERE email = ?').get(email);
      const matches = await passwordWork(() => verifyPassword(body.password, user?.password_hash));
      const token = randomToken();
      const allowed = transaction(db, () => {
        const current = user && db.prepare('SELECT active, password_hash FROM users WHERE id = ?').get(user.id);
        if (!matches || !current?.active || current.password_hash !== user.password_hash) {
          recordAudit(db, 'login_failed', null, user?.email);
          return false;
        }
        if (sessionId) db.prepare('DELETE FROM sessions WHERE id = ?').run(sessionId);
        db.prepare('INSERT INTO sessions (id, user_id, csrf_token, expires_at, browser) VALUES (?, ?, ?, ?, ?)')
          .run(digest(token), user.id, randomToken(), Date.now() + sessionLifetime, browserName(req.headers['user-agent']));
        recordAudit(db, 'login', user.email, user.email);
        return true;
      });
      if (!allowed) fail(401, 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị khóa.');
      // Successful requests do not consume the failed-attempt allowance.
      const entry = attempts.get(key);
      if (entry) entry.count -= 1;
      res.setHeader('Set-Cookie', cookie(token, sessionLifetime / 1000));
      return { ok: true };
    }],
    ['GET /api/me', ({ user, csrf }) => ({ user, csrf })],
    ['GET /api/me/security', ({ user, sessionId }) => {
      const sessions = activeSessions(user.id).map(({ id, expires_at, browser }) => ({
        reference: sessionReference(id), current: id === sessionId, browser,
        created_at: expires_at - sessionLifetime, expires_at,
      }));
      const events = db.prepare(`SELECT event, created_at FROM audit
        WHERE actor = ? OR target = ? ORDER BY id DESC LIMIT 30`).all(user.email, user.email);
      const lastLogin = db.prepare("SELECT created_at FROM audit WHERE actor = ? AND event = 'login' ORDER BY id DESC LIMIT 1")
        .get(user.email)?.created_at ?? null;
      return { sessions, events, lastLogin };
    }],
    ['DELETE /api/me/sessions', async ({ req, user, sessionId }) => {
      const { reference } = await readJson(req);
      if (typeof reference !== 'string' || !/^[a-f0-9]{64}$/.test(reference)) fail(400, 'Mã phiên không hợp lệ.');
      transaction(db, () => {
        requireSession(sessionId);
        const target = activeSessions(user.id).find(({ id }) => sessionReference(id) === reference);
        if (!target) fail(404, 'Phiên này đã kết thúc hoặc không tồn tại.');
        if (target.id === sessionId) fail(400, 'Dùng nút Đăng xuất để kết thúc phiên hiện tại.');
        db.prepare('DELETE FROM sessions WHERE id = ? AND user_id = ?').run(target.id, user.id);
        recordAudit(db, 'session_revoked', user.email, user.email);
      });
      return { message: 'Đã kết thúc phiên đăng nhập.' };
    }],
    ['POST /api/logout', ({ user, res, sessionId }) => {
      transaction(db, () => {
        db.prepare('DELETE FROM sessions WHERE id = ?').run(sessionId);
        recordAudit(db, 'logout', user.email, user.email);
      });
      res.setHeader('Set-Cookie', cookie('', 0));
      return { ok: true };
    }],
    ['PATCH /api/me', async ({ req, user, sessionId }) => {
      const name = normalizeName((await readJson(req)).name);
      if (!validName(name)) fail(400, 'Họ tên phải có từ 2 đến 80 ký tự hợp lệ.');
      transaction(db, () => {
        requireSession(sessionId);
        db.prepare('UPDATE users SET name = ? WHERE id = ?').run(name, user.id);
        recordAudit(db, 'profile_updated', user.email, user.email);
      });
      return { message: 'Đã lưu hồ sơ.' };
    }],
    ['POST /api/password', async ({ req, res, user, sessionId }) => {
      limit(req, 'password', 8);
      const { currentPassword, password } = await readJson(req);
      if (!validPassword(currentPassword) || !validPassword(password)) {
        fail(400, 'Mật khẩu phải có từ 15 đến 128 ký tự.');
      }
      if (password === currentPassword) fail(400, 'Mật khẩu mới phải khác mật khẩu hiện tại.');
      const { password_hash: previousHash } = db.prepare('SELECT password_hash FROM users WHERE id = ?').get(user.id);
      const nextHash = await passwordWork(async () => {
        if (!await verifyPassword(currentPassword, previousHash)) fail(400, 'Mật khẩu hiện tại không đúng.');
        return hashPassword(password);
      });
      transaction(db, () => {
        requireSession(sessionId);
        const result = db.prepare('UPDATE users SET password_hash = ? WHERE id = ? AND password_hash = ?')
          .run(nextHash, user.id, previousHash);
        if (!result.changes) fail(409, 'Mật khẩu đã thay đổi. Hãy đăng nhập lại.');
        db.prepare('DELETE FROM sessions WHERE user_id = ?').run(user.id);
        recordAudit(db, 'password_changed', user.email, user.email);
      });
      res.setHeader('Set-Cookie', cookie('', 0));
      return { message: 'Đã đổi mật khẩu. Hãy đăng nhập lại.' };
    }],
    ['GET /api/admin/users', ({ url }) => {
      const search = (url.searchParams.get('q') || '').trim().slice(0, 100);
      const role = url.searchParams.get('role') || '';
      const status = url.searchParams.get('status') || '';
      const page = Math.min(1_000_000, Math.max(1, Number.parseInt(url.searchParams.get('page'), 10) || 1));
      if (!['', 'member', 'admin'].includes(role) || !['', 'active', 'disabled'].includes(status)) {
        fail(400, 'Bộ lọc không hợp lệ.');
      }
      const filter = `WHERE (instr(fold_search(name), fold_search(?)) > 0 OR instr(email, lower(?)) > 0 OR ? = '')
        AND (? = '' OR role = ?) AND (? = '' OR active = ?)`;
      const params = [search, search, search, role, role, status, status === 'active' ? 1 : 0];
      const { total } = db.prepare(`SELECT count(*) AS total FROM users ${filter}`).get(...params);
      const users = db.prepare(`SELECT ${publicColumns} FROM users ${filter} ORDER BY id DESC LIMIT 10 OFFSET ?`)
        .all(...params, (page - 1) * 10);
      const stats = db.prepare(`SELECT count(*) AS total, count(CASE WHEN active = 1 THEN 1 END) AS active,
        count(CASE WHEN role = 'admin' THEN 1 END) AS admins FROM users`).get();
      return { users, total, page, stats };
    }],
    ['PATCH /api/admin/users', async ({ req, user, sessionId }) => {
      const { id, role, active } = await readJson(req);
      if (!Number.isSafeInteger(id) || !['member', 'admin'].includes(role) || typeof active !== 'boolean') {
        fail(400, 'Dữ liệu tài khoản không hợp lệ.');
      }
      if (id === user.id) fail(400, 'Bạn không thể đổi quyền hoặc trạng thái của chính mình.');
      transaction(db, () => {
        if (requireSession(sessionId).user.role !== 'admin') fail(403, 'Khu vực này dành cho quản trị viên.');
        const target = db.prepare('SELECT email, role, active FROM users WHERE id = ?').get(id);
        if (!target) fail(404, 'Không tìm thấy tài khoản.');
        if (target.role === role && target.active === Number(active)) return;
        db.prepare('UPDATE users SET role = ?, active = ? WHERE id = ?').run(role, Number(active), id);
        db.prepare('DELETE FROM sessions WHERE user_id = ?').run(id);
        if (target.role !== role) recordAudit(db, role === 'admin' ? 'role_admin' : 'role_member', user.email, target.email);
        if (target.active !== Number(active)) recordAudit(db, active ? 'account_enabled' : 'account_disabled', user.email, target.email);
      });
      return { message: 'Đã cập nhật tài khoản.' };
    }],
    ['GET /api/admin/audit', () => ({ events: db.prepare('SELECT * FROM audit ORDER BY id DESC LIMIT 50').all() })],
  ]);

  function activeSessions(userId) {
    return db.prepare('SELECT id, expires_at, browser FROM sessions WHERE user_id = ? AND expires_at > ? ORDER BY expires_at DESC')
      .all(userId, Date.now());
  }

  function requireSession(sessionId) {
    const row = sessionId && db.prepare(`SELECT u.id, u.name, u.email, u.role, u.active, u.created_at, s.csrf_token
      FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.id = ? AND s.expires_at > ? AND u.active = 1`)
      .get(sessionId, Date.now());
    if (!row) fail(401, 'Phiên đăng nhập đã hết hạn.');
    const { csrf_token: csrf, ...user } = row;
    return { csrf, user };
  }

  const server = createServer({ requestTimeout: 15_000, headersTimeout: 10_000 }, async (req, res) => {
    res.setHeader('Cache-Control', 'no-store');
    res.setHeader('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
    res.setHeader('Referrer-Policy', 'no-referrer');
    res.setHeader('X-Content-Type-Options', 'nosniff');
    res.setHeader('X-Frame-Options', 'DENY');
    if (secureCookie) res.setHeader('Strict-Transport-Security', 'max-age=31536000');

    try {
      if (req.headers.host !== expectedUrl.host) fail(421, 'Tên máy chủ không hợp lệ.');
      if (!req.url.startsWith('/') || req.url.startsWith('//')) fail(400, 'Đường dẫn không hợp lệ.');
      const url = new URL(req.url, origin);
      const asset = assets.get(url.pathname);
      if (asset && (req.method === 'GET' || req.method === 'HEAD')) {
        res.setHeader('Content-Type', asset[0].startsWith('image/') ? asset[0] : `${asset[0]}; charset=utf-8`);
        res.end(req.method === 'GET' ? asset[1] : undefined);
        return;
      }

      const route = routes.get(`${req.method} ${url.pathname}`);
      if (!route) fail(404, 'Không tìm thấy đường dẫn.');
      const writes = req.method !== 'GET';
      if (writes && req.headers.origin !== expectedUrl.origin) fail(403, 'Nguồn yêu cầu không hợp lệ.');
      const token = req.headers.cookie?.split(';').map((part) => part.trim())
        .find((part) => part.startsWith(`${cookieName}=`))?.slice(cookieName.length + 1);
      const sessionId = /^[\w-]{43}$/.test(token || '') ? digest(token) : null;
      const publicRoute = publicRoutes.has(url.pathname);
      const session = publicRoute ? {} : requireSession(sessionId);
      if (!publicRoute && writes && !sameToken(req.headers['x-csrf-token'], session.csrf)) {
        fail(403, 'Yêu cầu bị từ chối. Hãy tải lại trang và thử lại.');
      }
      if (url.pathname.startsWith('/api/admin/') && session.user.role !== 'admin') {
        fail(403, 'Khu vực này dành cho quản trị viên.');
      }
      const result = await route({ req, res, url, sessionId, ...session });
      res.setHeader('Content-Type', 'application/json; charset=utf-8');
      res.end(JSON.stringify(result));
    } catch (error) {
      if (!(error instanceof HttpError)) console.error(error);
      res.statusCode = error instanceof HttpError ? error.status : 500;
      if (!req.complete) res.setHeader('Connection', 'close');
      res.setHeader('Content-Type', 'application/json; charset=utf-8');
      res.end(JSON.stringify({ message: error instanceof HttpError ? error.message : 'Máy chủ gặp lỗi. Vui lòng thử lại.' }));
    }
  });
  server.on('close', () => clearInterval(cleanup));
  return server;
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  const port = Number(process.env.PORT || 3000);
  const origin = process.env.APP_ORIGIN || `http://127.0.0.1:${port}`;
  const db = openStore();
  createApp({ db, origin }).listen(port, '127.0.0.1', () => console.log(`Lzon Poke · ${origin}`));
}
