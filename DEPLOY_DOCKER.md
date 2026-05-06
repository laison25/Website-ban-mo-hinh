# Huong dan chay bang Docker

Tai lieu nay dung de minh chung phan trien khai ky thuat tot cho BCCD mon Ma nguon mo.

## Yeu cau

- Cai Docker Desktop.
- May con trong port `8080`, `8081`, `3307`.

## Chay project

Mo terminal tai thu muc project va chay:

```powershell
docker compose up -d --build
```

Sau khi container khoi dong:

- Website: `http://localhost:8080/website-ban-mo-hinh-php-v3/`
- phpMyAdmin: `http://localhost:8081/`
- MySQL host tu may that: `127.0.0.1:3307`
- Database: `website_ban_mo_hinh`
- User MySQL: `root`
- Password MySQL: `root`

File `database/website_ban_mo_hinh.sql` se duoc import tu dong khi MySQL container khoi tao lan dau.

## Tai khoan demo

- Admin: `admin / 123456`
- User: `user / 123456`

## Dung container

```powershell
docker compose down
```

Neu muon xoa ca database Docker de import lai tu dau:

```powershell
docker compose down -v
```

## Noi dung can trinh bay khi bao cao

- `Dockerfile`: dong goi ung dung PHP Apache va cai extension `mysqli`.
- `docker-compose.yml`: tao 3 dich vu `web`, `db`, `phpmyadmin`.
- Bien moi truong `APP_DB_HOST`, `APP_DB_NAME`, `APP_DB_USER`, `APP_DB_PASS`: giup ung dung ket noi MySQL trong Docker ma khong can sua code khi chuyen moi truong.
- Port `8080`: port public de demo website.
- Port `8081`: port public de quan ly database bang phpMyAdmin.
