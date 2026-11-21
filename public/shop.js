const main = document.querySelector('#main');
const params = new URLSearchParams(location.search);
const currency = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
const date = new Intl.DateTimeFormat('vi-VN', { dateStyle: 'medium', timeStyle: 'short' });
const formatMoney = (amount) => currency.format(amount);
const statusLabels = { pending: 'Chờ xác nhận', awaiting_payment: 'Chờ thanh toán', confirmed: 'Đã xác nhận', shipping: 'Đang giao', completed: 'Hoàn tất', cancelled: 'Đã hủy' };
const methods = { COD: 'Thanh toán khi nhận hàng', BANK: 'Chuyển khoản (mô phỏng)', QR_CODE: 'VietQR (mô phỏng)', MOMO: 'Ví điện tử (mô phỏng)', CARD: 'Thẻ (mô phỏng)' };
const favoriteIds = new Set();
let toastTimer;
let session;
let cart = [];
let coupon = '';
try {
  const saved = JSON.parse(localStorage.getItem('lzon-cart') || '{}');
  if (Array.isArray(saved.items)) cart = saved.items.filter((item) => Number.isSafeInteger(item?.id) && Number.isSafeInteger(item?.quantity));
  if (typeof saved.coupon === 'string') coupon = saved.coupon;
} catch { /* A fresh cart is used if browser storage is unavailable. */ }

function node(tag, content, className) {
  const element = document.createElement(tag);
  if (content !== undefined) element.textContent = content;
  if (className) element.className = className;
  return element;
}

function link(label, href, classes) {
  const element = node('a', label, classes);
  element.href = href;
  return element;
}

function button(label, classes = 'button secondary') {
  const element = node('button', label, classes);
  element.type = 'button';
  return element;
}

function messageBox() {
  const element = node('p', undefined, 'notice');
  element.setAttribute('aria-live', 'polite');
  return element;
}

function message(element, value, error = false) {
  element.dataset.error = error;
  element.textContent = value;
}

function heading(title, note) {
  document.title = `${title} · Lzon Poke`;
  const header = node('header', undefined, 'page-title');
  header.append(node('h1', title));
  if (note) header.append(node('p', note, 'muted'));
  main.replaceChildren(header);
}

function field(label, name, properties = {}, tag = 'input') {
  const wrapper = node('label', label);
  const element = node(tag);
  Object.assign(element, { name, ...properties });
  wrapper.append(element);
  return wrapper;
}

function selectField(label, name, choices) {
  const wrapper = field(label, name, {}, 'select');
  for (const [value, text] of choices) wrapper.lastChild.add(new Option(text, value));
  return wrapper;
}

function submitButton(label) {
  const element = button(label, 'button primary');
  element.type = 'submit';
  return element;
}

function bind(form, notice, work) {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const submit = form.querySelector('[type=submit]');
    if (submit.disabled) return;
    submit.disabled = true;
    message(notice, '');
    try { await work(Object.fromEntries(new FormData(form))); }
    catch (error) { message(notice, error.message, true); }
    finally { submit.disabled = false; }
  });
}

async function api(path, method = 'GET', body) {
  const headers = { Accept: 'application/json' };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  if (method !== 'GET' && session) headers['X-CSRF-Token'] = session.csrf;
  let response;
  try { response = await fetch(path, { method, headers, body: JSON.stringify(body) }); }
  catch { throw new Error('Không kết nối được với cửa hàng.'); }
  const data = await response.json();
  if (!response.ok) throw Object.assign(new Error(data.message), { status: response.status });
  return data;
}

const loginUrl = (page) => `/account?${new URLSearchParams({ next: page })}`;

function requireUser(page, title, admin = false) {
  if (session && (!admin || session.user.role === 'admin')) return true;
  heading(title, session ? 'Khu vực dành cho quản trị viên.' : 'Đăng nhập để tiếp tục.');
  main.append(session ? link('Về cửa hàng', '/', 'button secondary') : link('Đăng nhập', loginUrl(page), 'button primary'));
  return false;
}

function saveCart() {
  try { localStorage.setItem('lzon-cart', JSON.stringify({ items: cart, coupon })); } catch { /* Cart remains available on the current page. */ }
  const count = cart.reduce((sum, item) => sum + item.quantity, 0);
  const badge = document.querySelector('#cart-count');
  badge.textContent = count;
  badge.hidden = !count;
  document.querySelector('#cart-link').setAttribute('aria-label', `Giỏ hàng, ${count} sản phẩm`);
}

function addToCart(product) {
  const item = cart.find(({ id }) => id === product.id);
  if (!product.stock || (item && item.quantity >= Math.min(product.stock, 99))) throw new Error('Đã đạt số lượng còn trong kho.');
  if (!item && cart.length >= 30) throw new Error('Giỏ hàng đã có 30 sản phẩm khác nhau.');
  if (item) item.quantity += 1;
  else cart.push({ id: product.id, quantity: 1 });
  saveCart();
}

function showToast(text, error = false) {
  const toast = document.querySelector('#shop-toast');
  clearTimeout(toastTimer);
  toast.hidden = false;
  message(toast, text, error);
  toastTimer = setTimeout(() => { toast.hidden = true; }, 3500);
}

function photo(image, name) {
  const element = node('img');
  element.src = `/shop-images/${encodeURIComponent(image)}`;
  element.alt = name;
  element.loading = 'lazy';
  return element;
}

function priceLine(product) {
  const price = node('p', formatMoney(product.price), 'price');
  if (product.old_price > product.price) price.append(node('del', formatMoney(product.old_price)));
  return price;
}

function productCard(product, mode = 'catalog') {
  const card = node('article', undefined, 'card');
  const detail = `/product?id=${product.id}`;
  const picture = link('', detail, 'card-cover');
  picture.append(photo(product.image, product.name));
  card.append(picture);
  if (mode === 'sale') {
    const discount = Math.max(1, Math.round(100 * (product.old_price - product.price) / product.old_price));
    card.append(node('span', `-${discount}%`, 'card-badge sale'));
  } else if (product.featured) card.append(node('span', 'Best Seller', 'card-badge'));
  const heart = button('♥', 'favorite');
  heart.dataset.productFavorite = product.id;
  heart.setAttribute('aria-label', `Yêu thích ${product.name}`);
  heart.setAttribute('aria-pressed', String(favoriteIds.has(product.id)));
  heart.addEventListener('click', async () => {
    if (!session) { location.assign(loginUrl('/wishlist')); return; }
    heart.disabled = true;
    try {
      const { saved } = await api('/api/shop/wishlist', 'PUT', { id: product.id, saved: !favoriteIds.has(product.id) });
      if (saved) favoriteIds.add(product.id);
      else favoriteIds.delete(product.id);
      for (const control of document.querySelectorAll(`[data-product-favorite="${product.id}"]`)) control.setAttribute('aria-pressed', String(saved));
      if (mode === 'wishlist' && !saved) await wishlistPage();
      showToast(saved ? 'Đã thêm vào danh sách yêu thích.' : 'Đã bỏ yêu thích.');
    } catch (error) { showToast(error.message, true); }
    finally { heart.disabled = false; }
  });
  const content = node('div', undefined, 'card-meta');
  const title = node('h3');
  title.append(link(product.name, detail));
  const rating = Math.min(5, Math.max(0, product.rating));
  const score = node('p', '★'.repeat(rating) + '☆'.repeat(5 - rating), 'rating');
  score.setAttribute('aria-label', `${rating} trên 5 sao, ${product.reviews} lượt đánh giá`);
  score.append(node('span', `(${product.reviews})`));
  const actions = node('div', undefined, `card-actions${mode === 'sale' ? '' : ' split'}`);
  if (mode !== 'sale') actions.append(link('Chi tiết', detail, 'button secondary'));
  const buy = button(product.stock ? (mode === 'sale' ? 'Thêm vào giỏ hàng' : 'Mua ngay') : 'Hết hàng', 'button accent');
  buy.disabled = !product.stock;
  buy.addEventListener('click', () => {
    try {
      addToCart(product);
      if (mode === 'sale') showToast('Đã thêm vào giỏ hàng.');
      else location.assign('/cart');
    } catch (error) { showToast(error.message, true); }
  });
  actions.append(buy);
  content.append(node('small', product.studio || product.category, 'studio'), title, priceLine(product), score, actions);
  card.append(heart, content);
  return card;
}

function renderShowcase(products) {
  const slider = document.querySelector('#hero-slider');
  if (!products.length) return;
  const frame = document.querySelector('#showcase-slide');
  const dots = [...products].map((product, index) => {
    const dot = button('', 'hero-dot');
    dot.setAttribute('aria-label', `Hiện ${product.name}`);
    dot.addEventListener('click', () => display(index));
    return dot;
  });
  let current = 0;
  function display(index) {
    current = (index + products.length) % products.length;
    const product = products[current];
    const banner = link('', `/product?id=${product.id}`, 'showcase-link');
    const content = node('div');
    const image = photo(product.image, '');
    image.loading = 'eager';
    content.append(node('p', 'Bộ sưu tập nổi bật', 'eyebrow'), node('h2', product.name),
      node('p', `${product.studio} · ${formatMoney(product.price)}`), node('span', 'Xem chi tiết', 'button accent'));
    banner.append(image, content);
    frame.replaceChildren(banner);
    dots.forEach((dot, dotIndex) => dot.setAttribute('aria-pressed', String(dotIndex === current)));
  }
  const prev = slider.querySelector('.prev');
  const next = slider.querySelector('.next');
  prev.hidden = next.hidden = products.length < 2;
  prev.addEventListener('click', () => display(current - 1));
  next.addEventListener('click', () => display(current + 1));
  if (products.length > 1) slider.querySelector('.hero-dots').append(...dots);
  display(0);
  slider.hidden = false;
}

async function catalog() {
  main.replaceChildren(document.querySelector('#catalog-template').content.cloneNode(true));
  const form = document.querySelector('#catalog-filters');
  const grid = document.querySelector('#product-grid');
  const count = document.querySelector('#product-count');
  const filters = new URLSearchParams();
  for (const name of ['q', 'category', 'min', 'max', 'sort']) {
    const value = params.get(name);
    if (value) {
      filters.set(name, value);
      form.elements[name].value = value;
    }
  }
  const q = filters.get('q');
  if (q) document.querySelector('.header-search input').value = q;
  const [base, favorites] = await Promise.all([api('/api/shop/products'),
    session ? api('/api/shop/wishlist').catch(() => ({ products: [] })) : { products: [] }]);
  for (const product of favorites.products) favoriteIds.add(product.id);
  const selected = filters.get('category') || '';
  for (const category of ['', ...base.categories]) {
    const categoryLink = link(category || 'Tất cả sản phẩm', category ? `/?${new URLSearchParams({ category })}#catalog` : '/#catalog', 'category-link');
    if (category === selected) categoryLink.setAttribute('aria-current', 'true');
    document.querySelector('#category-links').append(categoryLink);
  }
  renderShowcase(base.products.slice(0, 4));
  const sale = base.products.filter((product) => product.old_price > product.price && product.stock > 0).slice(0, 4);
  document.querySelector('#flash-section').hidden = !sale.length;
  document.querySelector('#flash-grid').append(...sale.map((product) => productCard(product, 'sale')));
  try {
    const result = filters.size ? await api(`/api/shop/products?${filters}`) : base;
    count.textContent = `${result.products.length} sản phẩm`;
    grid.replaceChildren(...result.products.map((product) => productCard(product)));
    if (!result.products.length) grid.append(node('p', 'Không tìm thấy sản phẩm phù hợp.', 'muted'));
  } catch (error) {
    message(document.querySelector('#catalog-notice'), error.message, true);
  }
  if (location.hash === '#catalog') document.querySelector('#catalog').scrollIntoView();
}

async function productPage() {
  const { product } = await api(`/api/shop/product?${new URLSearchParams({ id: params.get('id') || '' })}`);
  document.title = `${product.name} · Lzon Poke`;
  const detail = node('article', undefined, 'detail');
  const content = node('div', undefined, 'panel stack');
  const notice = messageBox();
  const actions = node('div', undefined, 'actions');
  const add = button('Thêm vào giỏ', 'button primary');
  add.disabled = product.stock === 0;
  add.addEventListener('click', () => {
    try { addToCart(product); message(notice, 'Đã thêm vào giỏ hàng.'); }
    catch (error) { message(notice, error.message, true); }
  });
  actions.append(add, link('Xem giỏ hàng', '/cart', 'button secondary'));
  if (session) {
    const saved = (await api('/api/shop/wishlist')).products.some(({ id }) => id === product.id);
    const toggle = button(saved ? 'Bỏ yêu thích' : 'Lưu yêu thích');
    toggle.setAttribute('aria-pressed', String(saved));
    toggle.addEventListener('click', async () => {
      toggle.disabled = true;
      try {
        const result = await api('/api/shop/wishlist', 'PUT', { id: product.id, saved: toggle.getAttribute('aria-pressed') !== 'true' });
        toggle.setAttribute('aria-pressed', String(result.saved));
        toggle.textContent = result.saved ? 'Bỏ yêu thích' : 'Lưu yêu thích';
      } catch (error) { message(notice, error.message, true); }
      finally { toggle.disabled = false; }
    });
    actions.append(toggle);
  }
  content.append(node('p', `${product.category} · ${product.studio}`, 'muted'), node('h1', product.name), priceLine(product),
    node('p', product.stock ? `Còn ${product.stock} sản phẩm` : 'Hết hàng'), node('p', product.description),
    node('small', `SKU ${product.sku} · ${product.size_label}`), actions, notice);
  detail.append(photo(product.image, product.name), content);
  main.replaceChildren(detail);
}

function table(labels) {
  const frame = node('div', undefined, 'panel table-wrap');
  const element = node('table');
  const row = element.createTHead().insertRow();
  for (const name of labels) {
    const cell = node('th', name);
    cell.scope = 'col';
    row.append(cell);
  }
  frame.append(element);
  return { frame, rows: element.createTBody() };
}

function addRow(rows, values) {
  const row = rows.insertRow();
  for (const value of values) {
    const cell = row.insertCell();
    if (value instanceof Node) cell.append(value);
    else cell.textContent = value;
  }
}

function downloadCsv(filename, rows) {
  const data = rows.map((row) => row.map((value) => {
    let cell = String(value ?? '');
    if (/^\s*[=+@-]|^[\t\r\n]/.test(cell)) cell = `'${cell}`;
    return `"${cell.replaceAll('"', '""')}"`;
  }).join(',')).join('\r\n');
  const url = URL.createObjectURL(new Blob(['\ufeff', data], { type: 'text/csv;charset=utf-8' }));
  const file = link('', url);
  file.download = filename;
  document.body.append(file);
  file.click();
  file.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function totalsText(summary, quote) {
  summary.replaceChildren(node('h2', 'Tóm tắt đơn'), node('p', `Tạm tính ${formatMoney(quote.subtotal)}`),
    node('p', `Giảm giá ${formatMoney(quote.discount)}`), node('p', `Tổng cộng ${formatMoney(quote.total)}`, 'price separator'));
}

async function cartPage() {
  heading('Giỏ hàng');
  if (!cart.length) {
    main.append(node('p', 'Giỏ hàng đang trống.', 'muted'), link('Xem sản phẩm', '/#catalog'));
    return;
  }
  const { products } = await api('/api/shop/products');
  const productMap = new Map(products.map((product) => [product.id, product]));
  const { frame, rows } = table(['Sản phẩm', 'Đơn giá', 'Số lượng', 'Thao tác']);
  const layout = node('div', undefined, 'layout');
  const side = node('section', undefined, 'panel stack');
  const totals = node('div', undefined, 'stack');
  const form = node('form', undefined, 'form');
  const notice = messageBox();
  const checkoutLink = link('Tiếp tục đặt hàng', '/checkout', 'button primary');
  const couponField = field('Mã giảm giá', 'coupon', { maxLength: 30, value: coupon, placeholder: 'LZON10' });
  form.append(couponField, submitButton('Áp dụng mã'));
  let version = 0;

  async function refresh(code = coupon) {
    const currentVersion = ++version;
    checkoutLink.hidden = true;
    try {
      const quote = await api('/api/shop/quote', 'POST', { items: cart, coupon: code });
      if (currentVersion !== version) return;
      coupon = quote.coupon;
      couponField.lastChild.value = coupon;
      saveCart();
      totalsText(totals, quote);
      checkoutLink.hidden = false;
    } catch (error) {
      if (currentVersion !== version) return;
      totals.replaceChildren(node('h2', 'Tóm tắt đơn'));
      message(notice, error.message, true);
    }
  }

  function drawRows() {
    rows.replaceChildren();
    for (const item of cart) {
      const product = productMap.get(item.id);
      const quantity = node('input');
      Object.assign(quantity, { type: 'number', min: '1', max: String(Math.max(1, Math.min(product?.stock || 1, 99))), value: String(item.quantity), disabled: !product?.stock });
      quantity.setAttribute('aria-label', `Số lượng ${product?.name || item.id}`);
      quantity.addEventListener('change', () => {
        if (!quantity.checkValidity() || !Number.isInteger(quantity.valueAsNumber)) { quantity.value = item.quantity; return; }
        item.quantity = quantity.valueAsNumber;
        saveCart();
        message(notice, '');
        refresh();
      });
      const remove = button('Xóa');
      remove.addEventListener('click', () => {
        cart = cart.filter(({ id }) => id !== item.id);
        saveCart();
        if (!cart.length) { cartPage(); return; }
        drawRows();
        message(notice, '');
        refresh();
      });
      addRow(rows, [product ? link(product.name, `/product?id=${item.id}`) : 'Sản phẩm không còn bán', product ? formatMoney(product.price) : '—', quantity, remove]);
    }
  }
  bind(form, notice, ({ coupon: code }) => refresh(code));
  side.append(totals, form, notice, checkoutLink);
  layout.append(frame, side);
  main.append(layout);
  drawRows();
  await refresh();
}

async function checkoutPage() {
  if (!requireUser('/checkout', 'Đặt hàng')) return;
  heading('Đặt hàng', 'Nhập thông tin nhận hàng và chọn phương thức thanh toán.');
  if (!cart.length) { main.append(link('Quay về giỏ hàng', '/cart')); return; }
  const quote = await api('/api/shop/quote', 'POST', { items: cart, coupon });
  const form = node('form', undefined, 'panel form');
  const summary = node('section', undefined, 'panel stack');
  const layout = node('div', undefined, 'layout');
  const notice = messageBox();
  const requestKey = crypto.randomUUID();
  form.append(field('Họ tên người nhận', 'name', { value: session.user.name, required: true, maxLength: 120, autocomplete: 'name' }),
    field('Số điện thoại', 'phone', { type: 'tel', required: true, maxLength: 22, autocomplete: 'tel' }),
    field('Địa chỉ giao hàng', 'address', { required: true, maxLength: 500, autocomplete: 'street-address' }, 'textarea'),
    field('Ghi chú', 'note', { maxLength: 500 }, 'textarea'), selectField('Thanh toán', 'method', Object.entries(methods)), notice,
    submitButton('Đặt hàng'));
  totalsText(summary, quote);
  for (const item of quote.lines) summary.append(node('p', `${item.quantity} × ${item.name}`, 'muted'));
  layout.append(form, summary);
  main.append(layout);
  bind(form, notice, async (details) => {
    const { id } = await api('/api/shop/orders', 'POST', { ...details, items: cart, coupon, requestKey });
    cart = [];
    coupon = '';
    saveCart();
    location.assign(`/order?id=${id}`);
  });
}

async function ordersPage() {
  if (!requireUser('/orders', 'Đơn hàng của tôi')) return;
  heading('Đơn hàng của tôi');
  const { orders } = await api('/api/shop/orders');
  if (!orders.length) { main.append(node('p', 'Bạn chưa có đơn hàng.', 'muted')); return; }
  const { frame, rows } = table(['Đơn hàng', 'Ngày đặt', 'Trạng thái', 'Tổng cộng']);
  for (const order of orders) addRow(rows, [link(`#${order.id}`, `/order?id=${order.id}`), date.format(order.created_at), statusLabels[order.status], formatMoney(order.total)]);
  main.append(frame);
}

async function orderPage() {
  if (!requireUser('/orders', 'Chi tiết đơn hàng')) return;
  const { order, items } = await api(`/api/shop/order?${new URLSearchParams({ id: params.get('id') || '' })}`);
  heading(`Đơn hàng #${order.id}`, `${statusLabels[order.status]} · ${date.format(order.created_at)}`);
  const layout = node('div', undefined, 'layout');
  const summary = node('section', undefined, 'panel stack');
  const { frame, rows } = table(['Sản phẩm', 'Đơn giá', 'Số lượng']);
  for (const item of items) addRow(rows, [item.name, formatMoney(item.price), item.quantity]);
  totalsText(summary, order);
  summary.append(node('p', `Người nhận ${order.customer_name} · ${order.phone}`), node('p', order.address), node('p', methods[order.method], 'separator'));
  if (order.note) summary.append(node('p', order.note, 'muted'));
  if (order.payment === 'demo') summary.append(node('p', 'Đã xác nhận thanh toán mô phỏng', 'pill'));
  if (order.status === 'awaiting_payment') {
    const notice = messageBox();
    const pay = button('Xác nhận thanh toán mô phỏng', 'button primary');
    pay.addEventListener('click', async () => {
      pay.disabled = true;
      try { await api('/api/shop/payment', 'POST', { id: order.id }); await orderPage(); }
      catch (error) { message(notice, error.message, true); pay.disabled = false; }
    });
    summary.append(pay, notice);
  }
  layout.append(frame, summary);
  main.append(layout, link('Quay về đơn hàng', '/orders'));
}

async function wishlistPage() {
  if (!requireUser('/wishlist', 'Sản phẩm yêu thích')) return;
  heading('Sản phẩm yêu thích');
  const { products } = await api('/api/shop/wishlist');
  if (!products.length) { main.append(node('p', 'Bạn chưa lưu sản phẩm nào.', 'muted')); return; }
  for (const product of products) favoriteIds.add(product.id);
  const grid = node('div', undefined, 'grid');
  grid.append(...products.map((product) => productCard(product, 'wishlist')));
  main.append(grid);
}

function productEditor(images, onSave) {
  const dialog = node('dialog');
  const form = node('form', undefined, 'form');
  form.method = 'post';
  const title = node('h2');
  const inputs = node('div', undefined, 'fields');
  for (const [label, name] of [['Tên sản phẩm', 'name'], ['Danh mục', 'category'], ['Studio', 'studio'], ['SKU', 'sku'], ['Kích thước', 'size_label']]) {
    inputs.append(field(label, name, { required: ['name', 'category', 'sku'].includes(name) }));
  }
  for (const [label, name] of [['Giá bán (đ)', 'price'], ['Giá cũ (đ)', 'old_price'], ['Tồn kho', 'stock']]) {
    inputs.append(field(label, name, { type: 'number', min: name === 'price' ? 1 : 0, required: true }));
  }
  const description = field('Mô tả', 'description', { maxLength: 3000 }, 'textarea');
  const photoField = selectField('Ảnh sản phẩm', 'image', images.map((image) => [image, image]));
  const featured = field('Sản phẩm nổi bật', 'featured', { type: 'checkbox' });
  const archived = field('Ẩn khỏi cửa hàng', 'archived', { type: 'checkbox' });
  featured.className = archived.className = 'check';
  const actions = node('div', undefined, 'actions');
  const cancel = button('Hủy');
  cancel.addEventListener('click', () => dialog.close());
  actions.append(cancel, submitButton('Lưu sản phẩm'));
  const notice = messageBox();
  form.append(title, inputs, description, photoField, featured, archived, notice, actions);
  dialog.append(form);
  let selectedId = null;
  bind(form, notice, async (data) => {
    await api('/api/admin/products', 'PUT', { ...data, id: selectedId, price: Number(data.price), old_price: Number(data.old_price),
      stock: Number(data.stock), featured: form.elements.featured.checked, archived: form.elements.archived.checked });
    dialog.close();
    await onSave();
  });
  return { dialog, open(product = {}) {
    form.reset();
    message(notice, '');
    selectedId = product.id ?? null;
    title.textContent = selectedId ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
    for (const input of form.querySelectorAll('input, select, textarea')) {
      if (input.type === 'checkbox') input.checked = Boolean(product[input.name]);
      else input.value = product[input.name] ?? (input.name === 'image' ? images[0] : input.type === 'number' ? 0 : '');
    }
    dialog.showModal();
  } };
}

async function adminPage(active = 'products', feedback = '') {
  if (!requireUser('/shop-admin', 'Quản lý cửa hàng', true)) return;
  const data = await api('/api/admin/shop');
  heading('Quản lý cửa hàng');
  const metrics = node('div', undefined, 'metrics');
  for (const [label, value] of [['Sản phẩm đang bán', data.stats.products], ['Tổng đơn hàng', data.stats.orders], ['Đơn chờ xác nhận', data.stats.pending]]) {
    const metric = node('div', undefined, 'panel');
    metric.append(node('span', label, 'muted'), node('strong', value));
    metrics.append(metric);
  }
  main.append(metrics);
  const tabs = node('div', undefined, 'actions');
  const notice = messageBox();
  for (const [tab, label] of [['products', 'Sản phẩm'], ['orders', 'Đơn hàng']]) {
    const control = button(label, `button ${active === tab ? 'primary' : 'secondary'}`);
    control.disabled = active === tab;
    control.addEventListener('click', () => adminPage(tab).catch((error) => message(notice, error.message, true)));
    tabs.append(control);
  }
  tabs.append(link('Người dùng và nhật ký', '/account', 'button secondary'));
  main.append(tabs, notice);
  message(notice, feedback);
  if (active === 'products') {
    const editor = productEditor(data.images, () => adminPage('products', 'Đã lưu sản phẩm.'));
    const add = button('Thêm sản phẩm', 'button primary');
    add.addEventListener('click', () => editor.open());
    const exportProducts = button('Xuất CSV sản phẩm');
    exportProducts.addEventListener('click', () => downloadCsv('lz-on-san-pham.csv', [
      ['SKU', 'Tên sản phẩm', 'Danh mục', 'Giá', 'Tồn kho', 'Hiển thị'],
      ...data.products.map((product) => [product.sku, product.name, product.category, product.price, product.stock, product.archived ? 'Đã ẩn' : 'Đang bán']),
    ]));
    tabs.append(add, exportProducts);
    const { frame, rows } = table(['Sản phẩm', 'SKU', 'Tồn kho', 'Giá', 'Hiển thị', 'Thao tác']);
    for (const product of data.products) {
      const edit = button('Sửa');
      edit.addEventListener('click', () => editor.open(product));
      addRow(rows, [product.name, product.sku, product.stock, formatMoney(product.price), product.archived ? 'Đã ẩn' : 'Đang bán', edit]);
    }
    main.append(frame, editor.dialog);
  } else {
    if (data.orders.length) {
      const exportOrders = button('Xuất CSV đơn hàng');
      exportOrders.addEventListener('click', () => downloadCsv('lz-on-don-hang.csv', [
        ['Mã đơn', 'Ngày đặt', 'Người nhận', 'Trạng thái', 'Tổng tiền'],
        ...data.orders.map((order) => [order.id, date.format(order.created_at), order.customer_name, statusLabels[order.status], order.total]),
      ]));
      tabs.append(exportOrders);
    }
    const { frame, rows } = table(['Đơn hàng', 'Người nhận', 'Tổng cộng', 'Trạng thái', 'Xử lý']);
    for (const order of data.orders) {
      const details = node('details');
      details.append(node('summary', `#${order.id} · ${date.format(order.created_at)}`), node('p', `${order.email} · ${order.phone}`),
        node('p', order.address), node('p', `${methods[order.method]}${order.payment === 'demo' ? ' · Đã xác nhận mô phỏng' : ''}`));
      for (const item of order.items) details.append(node('p', `${item.quantity} × ${item.name}`));
      if (order.note) details.append(node('p', order.note));
      const transition = node('form', undefined, 'form');
      if (order.transitions.length) {
        transition.append(selectField('Trạng thái mới', 'status', order.transitions.map((status) => [status, statusLabels[status]])), submitButton('Cập nhật'));
        bind(transition, notice, async ({ status }) => {
          await api('/api/admin/orders', 'PATCH', { id: order.id, status });
          await adminPage('orders', 'Đã cập nhật trạng thái đơn hàng.');
        });
      }
      addRow(rows, [details, order.customer_name, formatMoney(order.total), statusLabels[order.status], transition]);
    }
    main.append(data.orders.length ? frame : node('p', 'Chưa có đơn hàng.', 'muted'));
  }
}

try {
  document.querySelector('#footer-year').textContent = new Date().getFullYear();
  if (location.pathname === '/') document.querySelector('#home-link').setAttribute('aria-current', 'page');
  saveCart();
  try { session = await api('/api/me'); } catch (error) { if (error.status !== 401) throw error; }
  if (session) {
    document.querySelector('#account-link').textContent = 'Tài khoản';
    document.querySelector('#register-link').hidden = true;
  }
  const pages = { '/': catalog, '/product': productPage, '/cart': cartPage, '/checkout': checkoutPage,
    '/orders': ordersPage, '/order': orderPage, '/wishlist': wishlistPage, '/shop-admin': adminPage };
  await pages[location.pathname]();
} catch (error) {
  heading('Không thể mở trang', error.message);
  main.append(link('Về cửa hàng', '/', 'button secondary'));
  if (location.pathname === '/checkout') main.append(link('Quay về giỏ hàng', '/cart', 'button secondary'));
}
