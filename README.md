# legaladdressinestonia-wp

WordPress-сайт **legaladdressinestonia.com**.

| Окружение | URL | Ветка |
|---|---|---|
| Staging | https://staging.dragonslots-gr.gr/ | `staging` |
| Production | https://legaladdressinestonia.com/ | `main` (деплой пока вручную) |

Сервер: `95.111.245.41` (CyberPanel, OpenLiteSpeed, Contabo).

## Что лежит в репозитории

Только код: `wp-content/themes`, `wp-content/plugins`, `wp-content/mu-plugins`.
Ядро WordPress, `wp-config.php`, `uploads` и база в git **не хранятся**.

`wp-content/mu-plugins/staging-guard.php` — на любом хосте `staging.*`:
noindex (meta + `X-Robots-Tag`), `robots.txt` → `Disallow: /`, отключены sitemap
и отправка писем, оранжевый админ-бар. На продакшене не активируется.

## Как настроен staging

- CyberPanel: отдельный сайт `staging.dragonslots-gr.gr`, пользователь `stagi7842`.
- Корень сайта: `/home/staging.dragonslots-gr.gr/public_html`.
- В `wp-config.php`: `define( 'WP_ENVIRONMENT_TYPE', 'staging' );`.
- SSH-вход по ключу: публичный ключ в `/home/staging.dragonslots-gr.gr/.ssh/authorized_keys`.
- Деплой пишет физический `robots.txt` с `Disallow: /`.

### GitHub Secrets (Settings → Environments → `staging`)

| Secret | Значение |
|---|---|
| `SSH_HOST` | `95.111.245.41` |
| `SSH_PORT` | `22` |
| `SSH_USER` | `stagi7842` |
| `SSH_PRIVATE_KEY` | приватный ключ `~/.ssh/lae_staging_deploy` целиком |
| `STAGING_PATH` | `/home/staging.dragonslots-gr.gr/public_html` |

Пробелы и слеш на конце значений workflow срезает сам.
Деплой запускается пушем в `staging` или вручную: Actions → Deploy → staging → Run workflow.

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
