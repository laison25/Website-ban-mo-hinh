import { stdin } from 'node:process';
import { hashPassword, normalizeEmail, normalizeName, openStore, recordAudit, transaction, validEmail, validName, validPassword } from './store.mjs';

try {
  const email = normalizeEmail(process.argv[2]);
  const name = normalizeName(process.argv[3]);
  if (!validEmail(email) || !validName(name) || stdin.isTTY) {
    throw new Error('Dùng: node create-admin.mjs <email> "<họ tên>". Truyền mật khẩu qua stdin.');
  }
  stdin.setEncoding('utf8');
  let input = '';
  for await (const chunk of stdin) {
    input += chunk;
    if (input.length > 512) throw new Error('Mật khẩu quá dài.');
  }
  const password = input.replace(/\r?\n$/, '');
  if (!validPassword(password)) throw new Error('Mật khẩu phải có từ 15 đến 128 ký tự.');
  const hash = await hashPassword(password);
  const db = openStore();
  try {
    transaction(db, () => {
      const result = db.prepare(`INSERT INTO users (name, email, password_hash, role, created_at)
        VALUES (?, ?, ?, 'admin', ?) ON CONFLICT(email) DO NOTHING`).run(name, email, hash, Date.now());
      if (!result.changes) throw new Error('Email đã tồn tại. Hãy chọn một email khác.');
      recordAudit(db, 'admin_created', null, email);
    });
  } finally {
    db.close();
  }
  console.log(`Đã tạo tài khoản quản trị ${email}.`);
} catch (error) {
  console.error(error.message);
  process.exitCode = 1;
}
