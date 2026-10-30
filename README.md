# legaladdressinestonia-wp

WordPress-сайт **legaladdressinestonia.com**.

| Окружение | URL | Ветка |
|---|---|---|
| Staging | https://staging.dragonslots-gr.gr/ | `staging` |
| Production | https://legaladdressinestonia.com/ | `main` (деплой пока вручную) |

Сервер: `95.111.245.41` (cPanel).

## Что лежит в репозитории

Только код: `wp-content/themes`, `wp-content/plugins`, `wp-content/mu-plugins`.
Ядро WordPress, `wp-config.php`, `uploads` и база в git **не хранятся**.

`wp-content/mu-plugins/staging-guard.php` — на любом хосте `staging.*`:
noindex (meta + `X-Robots-Tag`), `robots.txt` → `Disallow: /`, отключены sitemap
и отправка писем, оранжевый админ-бар. На продакшене не активируется.

## Первоначальная настройка (один раз)

### 1. DNS
A-запись `staging.dragonslots-gr.gr` → `95.111.245.41`.

### 2. cPanel
1. **Domains → Create a New Domain**: `staging.dragonslots-gr.gr`,
   document root, например, `/home/<user>/staging.dragonslots-gr.gr`.
2. **SSL/TLS Status → Run AutoSSL**.
3. **WordPress Manager / Softaculous**: установить WP в этот document root
   (или перенести копию прода — см. ниже).
4. В `wp-config.php` staging добавить:
   ```php
   define( 'WP_ENVIRONMENT_TYPE', 'staging' );
   ```
5. **Directory Privacy** → папка staging → включить пароль (basic auth).
6. **SSH Access → Manage SSH Keys → Generate a New Key** (без пароля),
   затем **Manage → Authorize**. Скачать приватный ключ.
   Если SSH Access не виден — попросить хостера включить shell-доступ.

### 3. GitHub Secrets
Settings → Environments → создать `staging` → добавить секреты:

| Secret | Значение |
|---|---|
| `SSH_HOST` | `95.111.245.41` |
| `SSH_PORT` | порт SSH (часто `22`, на некоторых cPanel — другой) |
| `SSH_USER` | логин cPanel |
| `SSH_PRIVATE_KEY` | содержимое приватного ключа целиком |
| `STAGING_PATH` | путь из шага 2.1, без слеша в конце |

### 4. Копия прода на staging (по желанию)
cPanel → WordPress Manager → **Clone / Stage**, либо All-in-One WP Migration.
После переноса заменить URL в базе (`legaladdressinestonia.com` →
`staging.dragonslots-gr.gr`), например, плагином Better Search Replace.

## Рабочий процесс

```bash
git checkout staging
# правки в теме/плагинах
git commit -am "..."
git push            # → GitHub Actions выкладывает на staging
```

Проверили на staging → merge `staging` → `main`.

Сторонние плагины из каталога WordPress лучше ставить через админку,
а в git хранить только свою тему и свои плагины.
