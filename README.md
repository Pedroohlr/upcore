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

| Modulo | Categoria | Status |
| --- | --- | --- |
| Comentarios | Seguranca | Pronto |
| XML-RPC | Seguranca | Planejado |
| CAPTCHA | Seguranca | Planejado |
| URL de login personalizada | Seguranca | Planejado |
| Limite de tentativas de login | Seguranca | Planejado |
| Hardening basico | Seguranca | Pronto |
| Application Passwords | Seguranca | Pronto |
| Enumeracao de usuarios | Seguranca | Planejado |
| Limpeza de recursos do core | Performance | Planejado |
| Heartbeat API | Performance | Planejado |
| Limite de revisoes | Performance | Pronto |
| Assets do core no front-end | Performance | Planejado |
| WP-Cron | Performance | Planejado |
