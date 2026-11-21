import { DatabaseSync } from 'node:sqlite';
import { mkdirSync } from 'node:fs';
import { dirname } from 'node:path';
import { randomBytes, scrypt, timingSafeEqual, createHash } from 'node:crypto';
import { promisify } from 'node:util';

const deriveKey = promisify(scrypt);
const scryptOptions = { N: 32768, r: 8, p: 3, maxmem: 64 * 1024 * 1024 };
const dummySalt = randomBytes(16);
const dummyKey = randomBytes(64);
export const databasePath = process.env.DB_PATH || new URL('./data/vilock.sqlite', import.meta.url);
export const randomToken = () => randomBytes(32).toString('base64url');
export const digest = (token) => createHash('sha256').update(token).digest('hex');
export const validPassword = (value) => typeof value === 'string' &&
  [...value].length >= 15 && [...value].length <= 128;
export const normalizeEmail = (value) => typeof value === 'string' ? value.trim().toLowerCase() : '';
export const validEmail = (email) => email.length <= 254 && /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$/u.test(email);
export const normalizeName = (value) => typeof value === 'string' ? value.trim() : '';
export const validName = (name) => name.length >= 2 && name.length <= 80 && !/[\p{Cc}]/u.test(name);

export async function hashPassword(password) {
  const salt = randomBytes(16);
  const key = await deriveKey(password, salt, 64, scryptOptions);
  return `${salt.toString('hex')}:${key.toString('hex')}`;
}

export async function verifyPassword(password, hash) {
  const [salt, key] = hash ? hash.split(':').map((value) => Buffer.from(value, 'hex')) : [dummySalt, dummyKey];
  const suppliedKey = await deriveKey(password, salt, 64, scryptOptions);
  return timingSafeEqual(key, suppliedKey) && Boolean(hash);
}

export function openStore(path = databasePath) {
  if (path instanceof URL) mkdirSync(new URL('.', path), { recursive: true });
  else if (path !== ':memory:') mkdirSync(dirname(path), { recursive: true });
  const db = new DatabaseSync(path, { timeout: 5000, enableForeignKeyConstraints: true });
  db.function('fold_search', { deterministic: true }, (value) => value.toLowerCase().normalize('NFD')
    .replace(/\p{M}/gu, '').replaceAll('đ', 'd'));
  db.exec(`
    PRAGMA journal_mode = WAL;
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE,
      password_hash TEXT NOT NULL, role TEXT NOT NULL CHECK (role IN ('member', 'admin')),
      active INTEGER NOT NULL DEFAULT 1 CHECK (active IN (0, 1)), created_at INTEGER NOT NULL
    ) STRICT;
    CREATE TABLE IF NOT EXISTS sessions (
      id TEXT PRIMARY KEY, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
      csrf_token TEXT NOT NULL, expires_at INTEGER NOT NULL, browser TEXT NOT NULL DEFAULT 'Không xác định'
    ) STRICT;
    CREATE INDEX IF NOT EXISTS sessions_by_user ON sessions(user_id);
    CREATE INDEX IF NOT EXISTS sessions_by_expiration ON sessions(expires_at);
    CREATE TABLE IF NOT EXISTS audit (
      id INTEGER PRIMARY KEY, event TEXT NOT NULL, actor TEXT, target TEXT, created_at INTEGER NOT NULL
    ) STRICT;
  `);
  transaction(db, () => {
    const columns = db.prepare('PRAGMA table_info(sessions)').all();
    if (!columns.some(({ name }) => name === 'browser')) {
      db.exec("ALTER TABLE sessions ADD COLUMN browser TEXT NOT NULL DEFAULT 'Không xác định'");
    }
    db.exec('CREATE INDEX IF NOT EXISTS audit_by_actor ON audit(actor, id); CREATE INDEX IF NOT EXISTS audit_by_target ON audit(target, id)');
  });
  return db;
}

export function recordAudit(db, event, actor = null, target = null) {
  db.prepare('INSERT INTO audit (event, actor, target, created_at) VALUES (?, ?, ?, ?)')
    .run(event, actor, target, Date.now());
}

export function transaction(db, operation) {
  db.exec('BEGIN IMMEDIATE');
  try {
    const result = operation();
    db.exec('COMMIT');
    return result;
  } catch (error) {
    db.exec('ROLLBACK');
    throw error;
  }
}
