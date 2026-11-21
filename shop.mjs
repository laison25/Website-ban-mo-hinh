import { readFileSync, readdirSync } from 'node:fs';
import { transaction, recordAudit } from './store.mjs';

const seed = JSON.parse(readFileSync(new URL('./catalog.json', import.meta.url), 'utf8'));
const imageFolder = new URL('./public/shop-images/', import.meta.url);
export const images = readdirSync(imageFolder).filter((name) => /^(?:product-\d+|\d+)\.jpg$/.test(name));
const orderStates = { pending: ['confirmed', 'cancelled'], awaiting_payment: ['pending', 'cancelled'],
  confirmed: ['shipping', 'cancelled'], shipping: ['completed'], completed: [], cancelled: [] };
const paymentMethods = ['COD', 'BANK', 'QR_CODE', 'MOMO', 'CARD'];

export function initializeShop(db) {
  db.exec(`
    CREATE TABLE IF NOT EXISTS products (
      id INTEGER PRIMARY KEY, name TEXT NOT NULL, category TEXT NOT NULL, studio TEXT NOT NULL,
      description TEXT NOT NULL, price INTEGER NOT NULL CHECK(price > 0),
      old_price INTEGER NOT NULL CHECK(old_price >= 0), stock INTEGER NOT NULL CHECK(stock >= 0),
      rating INTEGER NOT NULL DEFAULT 5, reviews INTEGER NOT NULL DEFAULT 0,
      sku TEXT NOT NULL UNIQUE, size_label TEXT NOT NULL, image TEXT NOT NULL,
      featured INTEGER NOT NULL DEFAULT 0 CHECK(featured IN (0,1)), archived INTEGER NOT NULL DEFAULT 0 CHECK(archived IN (0,1))
    ) STRICT;
    CREATE TABLE IF NOT EXISTS shop_orders (
      id INTEGER PRIMARY KEY, user_id INTEGER NOT NULL REFERENCES users(id), request_key TEXT NOT NULL,
      customer_name TEXT NOT NULL, email TEXT NOT NULL, phone TEXT NOT NULL, address TEXT NOT NULL, note TEXT NOT NULL,
      method TEXT NOT NULL, status TEXT NOT NULL, payment TEXT NOT NULL, subtotal INTEGER NOT NULL,
      discount INTEGER NOT NULL, total INTEGER NOT NULL, coupon TEXT NOT NULL, created_at INTEGER NOT NULL,
      UNIQUE(user_id, request_key)
    ) STRICT;
    CREATE INDEX IF NOT EXISTS shop_orders_user ON shop_orders(user_id, id);
    CREATE TABLE IF NOT EXISTS shop_order_items (
      order_id INTEGER NOT NULL REFERENCES shop_orders(id), product_id INTEGER NOT NULL REFERENCES products(id),
      name TEXT NOT NULL, price INTEGER NOT NULL, quantity INTEGER NOT NULL, PRIMARY KEY(order_id, product_id)
    ) STRICT;
    CREATE TABLE IF NOT EXISTS wishlist (
      user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      product_id INTEGER NOT NULL REFERENCES products(id), PRIMARY KEY(user_id, product_id)
    ) STRICT;
  `);
  transaction(db, () => {
    if (db.prepare('SELECT 1 FROM products LIMIT 1').get()) return;
    const insert = db.prepare(`INSERT INTO products (name, category, studio, description, price, old_price,
      stock, rating, reviews, sku, size_label, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`);
    for (const product of seed) insert.run(...product);
  });
}

export function shopRoutes(db, { fail, readJson, requireSession }) {
  const text = (value) => typeof value === 'string' ? value.trim() : '';
  const integer = (value, min, max) => Number.isSafeInteger(value) && value >= min && value <= max;
  const isId = (value) => integer(value, 1, Number.MAX_SAFE_INTEGER);
  const productById = db.prepare('SELECT * FROM products WHERE id = ?');
  const orderById = db.prepare('SELECT * FROM shop_orders WHERE id = ?');
  const orderItems = db.prepare('SELECT product_id, name, price, quantity FROM shop_order_items WHERE order_id = ? ORDER BY product_id');

  function quote(items, coupon) {
    if (!Array.isArray(items) || !integer(items.length, 1, 30)) fail(400, 'Giỏ hàng cần có từ 1 đến 30 sản phẩm.');
    const seen = new Set();
    const lines = items.map((item) => {
      const { id, quantity } = item ?? {};
      if (!isId(id) || !integer(quantity, 1, 99) || seen.has(id)) fail(400, 'Giỏ hàng có số lượng không hợp lệ.');
      seen.add(id);
      const product = productById.get(id);
      if (!product || product.archived || quantity > product.stock) {
        const subject = product ? ` của ${product.name}` : '';
        fail(409, `Tồn kho${subject} đã thay đổi. Hãy cập nhật giỏ hàng.`);
      }
      return { id, name: product.name, image: product.image, quantity, price: product.price };
    });
    const subtotal = lines.reduce((sum, item) => sum + item.quantity * item.price, 0);
    const code = text(coupon).toUpperCase();
    let discount = 0;
    if (code === 'LZON10') discount = Math.min(Math.floor(subtotal / 10), 500_000);
    else if (code === 'FREESHIP') discount = 150_000;
    else if (code === 'FIGURE500' && subtotal >= 5_000_000) discount = 500_000;
    else if (code) fail(400, 'Mã giảm giá không hợp lệ hoặc chưa đủ giá trị đơn tối thiểu.');
    discount = Math.min(discount, subtotal);
    return { lines, coupon: code, subtotal, discount, total: subtotal - discount };
  }

  function authenticatedWrite(sessionId, operation, admin = false) {
    return transaction(db, () => {
      const { user } = requireSession(sessionId);
      if (admin && user.role !== 'admin') fail(403, 'Khu vực này dành cho quản trị viên.');
      return operation(user);
    });
  }

  return new Map([
    ['GET /api/shop/products', ({ url }) => {
      const category = text(url.searchParams.get('category')).slice(0, 100);
      const q = text(url.searchParams.get('q')).slice(0, 100);
      const min = Number(url.searchParams.get('min') || 0);
      const max = Number(url.searchParams.get('max') || 1_000_000_000);
      if (!integer(min, 0, 1_000_000_000) || !integer(max, min, 1_000_000_000)) fail(400, 'Khoảng giá không hợp lệ.');
      const sorts = { featured: 'featured DESC, id DESC', newest: 'id DESC', low: 'price ASC, id DESC', high: 'price DESC, id DESC' };
      const key = url.searchParams.get('sort');
      const sort = Object.hasOwn(sorts, key) ? sorts[key] : sorts.featured;
      const products = db.prepare(`SELECT * FROM products WHERE archived = 0 AND (? = '' OR category = ?)
        AND price BETWEEN ? AND ? AND instr(fold_search(name || ' ' || studio || ' ' || sku), fold_search(?)) > 0
        ORDER BY ${sort}`).all(category, category, min, max, q);
      const categories = db.prepare('SELECT DISTINCT category FROM products WHERE archived = 0 ORDER BY category').all().map((row) => row.category);
      return { products, categories };
    }],
    ['GET /api/shop/product', ({ url }) => {
      const id = Number(url.searchParams.get('id'));
      const product = isId(id) ? productById.get(id) : null;
      if (!product || product.archived) fail(404, 'Không tìm thấy sản phẩm.');
      return { product };
    }],
    ['GET /api/shop/wishlist', ({ user }) => ({ products: db.prepare(`SELECT p.* FROM wishlist w
      JOIN products p ON p.id = w.product_id WHERE w.user_id = ? AND p.archived = 0 ORDER BY p.id DESC`).all(user.id) })],
    ['PUT /api/shop/wishlist', async ({ req, sessionId }) => {
      const { id, saved } = await readJson(req);
      if (!isId(id) || typeof saved !== 'boolean') fail(400, 'Yêu cầu lưu sản phẩm không hợp lệ.');
      return authenticatedWrite(sessionId, (user) => {
        const product = productById.get(id);
        if (!product || product.archived) fail(404, 'Không tìm thấy sản phẩm.');
        if (saved) db.prepare('INSERT OR IGNORE INTO wishlist VALUES (?, ?)').run(user.id, id);
        else db.prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?').run(user.id, id);
        return { saved };
      });
    }],
    ['POST /api/shop/quote', async ({ req }) => {
      const { items, coupon } = await readJson(req);
      return quote(items, coupon);
    }],
    ['POST /api/shop/orders', async ({ req, sessionId }) => {
      const { name, phone, address, note, method, items, coupon, requestKey } = await readJson(req);
      if (text(name).length < 2 || text(name).length > 120 || !/^\+?[0-9 .()-]{8,22}$/.test(text(phone)) ||
        text(address).length < 8 || text(address).length > 500 || text(note).length > 500 ||
        !paymentMethods.includes(method) || typeof requestKey !== 'string' || !/^[0-9a-f-]{36}$/.test(requestKey)) {
        fail(400, 'Kiểm tra tên, số điện thoại, địa chỉ và phương thức thanh toán.');
      }
      return authenticatedWrite(sessionId, (user) => {
        const previous = db.prepare('SELECT id FROM shop_orders WHERE user_id = ? AND request_key = ?').get(user.id, requestKey);
        if (previous) return { id: previous.id };
        const totals = quote(items, coupon);
        const status = method === 'COD' ? 'pending' : 'awaiting_payment';
        const { lastInsertRowid: id } = db.prepare(`INSERT INTO shop_orders (user_id, request_key, customer_name, email,
          phone, address, note, method, status, payment, subtotal, discount, total, coupon, created_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', ?, ?, ?, ?, ?)`).run(user.id, requestKey, text(name), user.email,
          text(phone), text(address), text(note), method, status, totals.subtotal, totals.discount, totals.total, totals.coupon, Date.now());
        const itemInsert = db.prepare('INSERT INTO shop_order_items VALUES (?, ?, ?, ?, ?)');
        const reserve = db.prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        for (const item of totals.lines) {
          if (!reserve.run(item.quantity, item.id, item.quantity).changes) fail(409, 'Tồn kho vừa thay đổi.');
          itemInsert.run(id, item.id, item.name, item.price, item.quantity);
        }
        recordAudit(db, 'order_created', user.email, user.email);
        return { id };
      });
    }],
    ['GET /api/shop/orders', ({ user }) => ({ orders: db.prepare(`SELECT id, status, payment, method, total, created_at
      FROM shop_orders WHERE user_id = ? ORDER BY id DESC LIMIT 100`).all(user.id) })],
    ['GET /api/shop/order', ({ user, url }) => {
      const id = Number(url.searchParams.get('id'));
      const order = isId(id) ? db.prepare('SELECT * FROM shop_orders WHERE id = ? AND user_id = ?').get(id, user.id) : null;
      if (!order) fail(404, 'Không tìm thấy đơn hàng.');
      const { request_key, user_id, ...details } = order;
      return { order: details, items: orderItems.all(id) };
    }],
    ['POST /api/shop/payment', async ({ req, sessionId }) => {
      const { id } = await readJson(req);
      if (!isId(id)) fail(400, 'Mã đơn hàng không hợp lệ.');
      return authenticatedWrite(sessionId, (user) => {
        const order = orderById.get(id);
        if (!order || order.user_id !== user.id) fail(404, 'Không tìm thấy đơn hàng.');
        if (order.status !== 'awaiting_payment' || order.payment !== 'unpaid') fail(409, 'Đơn không chờ thanh toán.');
        db.prepare("UPDATE shop_orders SET status = 'pending', payment = 'demo' WHERE id = ?").run(id);
        recordAudit(db, 'payment_demo', user.email, user.email);
        return { ok: true };
      });
    }],
    ['GET /api/admin/shop', () => ({
      products: db.prepare('SELECT * FROM products ORDER BY archived, id DESC').all(), images,
      orders: db.prepare(`SELECT id, customer_name, email, phone, address, note, method, status, payment, total,
        created_at FROM shop_orders ORDER BY id DESC LIMIT 100`).all()
        .map((order) => ({ ...order, items: orderItems.all(order.id), transitions: orderStates[order.status] })),
      stats: {
        products: db.prepare('SELECT count(*) AS count FROM products WHERE archived = 0').get().count,
        orders: db.prepare('SELECT count(*) AS count FROM shop_orders').get().count,
        pending: db.prepare("SELECT count(*) AS count FROM shop_orders WHERE status = 'pending'").get().count,
      },
    })],
    ['PUT /api/admin/products', async ({ req, sessionId }) => {
      const body = await readJson(req);
      const { id = null, price, old_price, stock, image, featured, archived } = body;
      const [name, category, studio, description, sku, size] = ['name', 'category', 'studio', 'description', 'sku', 'size_label'].map((key) => text(body[key]));
      if ((id !== null && !isId(id)) || name.length < 2 || name.length > 180 || !category || category.length > 100 ||
        studio.length > 100 || description.length > 3000 || !sku || sku.length > 80 || size.length > 80 ||
        !integer(price, 1, 1_000_000_000) || !integer(old_price, 0, 1_000_000_000) || !integer(stock, 0, 100_000) ||
        !images.includes(image) || typeof featured !== 'boolean' || typeof archived !== 'boolean') fail(400, 'Kiểm tra thông tin sản phẩm.');
      return authenticatedWrite(sessionId, (user) => {
        const duplicate = db.prepare('SELECT id FROM products WHERE sku = ? COLLATE NOCASE').get(sku);
        if (duplicate && duplicate.id !== id) fail(409, 'SKU đã tồn tại.');
        const values = [name, category, studio, description, price, old_price, stock, sku, size, image, Number(featured), Number(archived)];
        if (id === null) db.prepare(`INSERT INTO products (name, category, studio, description, price, old_price, stock,
          sku, size_label, image, featured, archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`).run(...values);
        else if (!db.prepare(`UPDATE products SET name=?, category=?, studio=?, description=?, price=?, old_price=?,
          stock=?, sku=?, size_label=?, image=?, featured=?, archived=? WHERE id=?`).run(...values, id).changes) fail(404, 'Không tìm thấy sản phẩm.');
        recordAudit(db, 'product_updated', user.email);
        return { ok: true };
      }, true);
    }],
    ['PATCH /api/admin/orders', async ({ req, sessionId }) => {
      const { id, status } = await readJson(req);
      if (!isId(id) || typeof status !== 'string') fail(400, 'Mã đơn hoặc trạng thái không hợp lệ.');
      return authenticatedWrite(sessionId, (user) => {
        const order = orderById.get(id);
        if (!order) fail(404, 'Không tìm thấy đơn hàng.');
        if (!orderStates[order.status].includes(status)) fail(409, 'Không thể chuyển sang trạng thái này.');
        if (status === 'cancelled') {
          const restore = db.prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
          for (const item of orderItems.all(id)) restore.run(item.quantity, item.product_id);
        }
        db.prepare('UPDATE shop_orders SET status = ? WHERE id = ?').run(status, id);
        recordAudit(db, 'order_status_updated', user.email, order.email);
        return { ok: true };
      }, true);
    }],
  ]);
}
