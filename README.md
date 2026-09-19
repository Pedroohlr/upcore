# UpCore

Baseline de seguranca e performance da UpSites, distribuido como MU-plugin em todos os projetos WordPress.

## Arquitetura

- Cada melhoria e um "modulo" isolado (`src/Modules/*`), implementando a classe abstrata `UpCore\Module`.
- Todos os modulos vem **desativados por padrao**. So passam a agir quando habilitados via painel.
- O `UpCore\ModuleManager` so chama `register()` do modulo se ele estiver habilitado (`UpCore\Settings`, guardado em `wp_options` na chave `upcore_modules`).
- Um modulo com `status()` retornando `planned` nao pode ser habilitado (a REST API recusa o toggle) ate que a logica real seja implementada.
- Painel administrativo em React (`assets/admin-app`), consumindo a REST API propria em `wp-json/upcore/v1/modules`.

## Instalacao em um projeto

MU-plugins nao carregam subpastas automaticamente, entao a instalacao usa um loader:

1. Copie a pasta `upcore/` inteira para `wp-content/mu-plugins/upcore/`.
2. Copie `mu-loader/upcore-loader.php` para `wp-content/mu-plugins/upcore-loader.php`.

## Desenvolvimento

```bash
composer install       # gera vendor/autoload.php (sem dependencias externas)

cd assets/admin-app
npm install
npm run build           # gera assets/admin-app/build (commitado no repo)
npm run start            # build com watch, para desenvolvimento
```

## Status dos modulos

Todos os 13 modulos do baseline estao implementados. Alguns dependem de configuracao
por projeto via constantes no `wp-config.php` (ver coluna "Configuracao").

| Modulo | Categoria | Status | Configuracao |
| --- | --- | --- | --- |
| Comentarios | Seguranca | Pronto | - |
| XML-RPC | Seguranca | Pronto | `UPCORE_XMLRPC_ALLOWED` para reativar por projeto |
| CAPTCHA | Seguranca | Pronto | Exige `UPCORE_RECAPTCHA_SITE_KEY` e `UPCORE_RECAPTCHA_SECRET_KEY`; sem elas fica inerte |
| URL de login personalizada | Seguranca | Pronto | `UPCORE_LOGIN_SLUG` (padrao `gerenciar`), `UPCORE_LOGIN_URL_DISABLED` como valvula de emergencia |
| Limite de tentativas de login | Seguranca | Pronto | Filtro `upcore_login_throttle_client_ip` para projetos atras de proxy/CDN |
| Hardening basico | Seguranca | Pronto | - |
| Application Passwords | Seguranca | Pronto | - |
| Enumeracao de usuarios | Seguranca | Pronto | - |
| Limpeza de recursos do core | Performance | Pronto | - |
| Heartbeat API | Performance | Pronto | - |
| Limite de revisoes | Performance | Pronto | - |
| Assets do core no front-end | Performance | Pronto | Escopo atual: so jquery-migrate |
| WP-Cron | Performance | Pronto | Exige cron de sistema real configurado na hospedagem |

## Cuidado especial: URL de login personalizada

Esse e o modulo de maior risco (pode travar o acesso ao painel se algo der errado).
Antes de ativar em producao, teste manualmente: login, logout, "esqueci minha senha"
e, se o projeto usar WooCommerce, a pagina de conta do cliente. Em caso de bloqueio,
adicione `define('UPCORE_LOGIN_URL_DISABLED', true);` no `wp-config.php` para restaurar
o `/wp-login.php` padrao sem depender do painel.
