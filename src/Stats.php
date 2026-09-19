<?php

declare(strict_types=1);

namespace UpCore;

/**
 * Contador de eventos por modulo (ex: tentativas de login bloqueadas),
 * usado no dashboard do painel. Guardado em tabela propria (nao em
 * wp_options) porque um evento pode disparar centenas de vezes por
 * minuto durante um ataque, e update_option nao foi feito para isso.
 */
final class Stats
{
    private const DB_VERSION = '1.0.0';
    private const DB_VERSION_OPTION = 'upcore_stats_db_version';

    public static function maybe_install(): void
    {
        if (get_option(self::DB_VERSION_OPTION) === self::DB_VERSION) {
            return;
        }

        global $wpdb;

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            module_slug VARCHAR(64) NOT NULL,
            metric VARCHAR(64) NOT NULL,
            count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (module_slug, metric)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }

    public static function record(string $module, string $metric): void
    {
        global $wpdb;

        $table = self::table_name();
        $now = current_time('mysql');

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table} (module_slug, metric, count, updated_at)
                 VALUES (%s, %s, 1, %s)
                 ON DUPLICATE KEY UPDATE count = count + 1, updated_at = %s",
                $module,
                $metric,
                $now,
                $now
            )
        );
    }

    /** @return array<string, array<string, int>> */
    public static function all(): array
    {
        global $wpdb;

        $table = self::table_name();
        $rows = $wpdb->get_results("SELECT module_slug, metric, count FROM {$table}", ARRAY_A);

        $result = [];

        foreach ((array) $rows as $row) {
            $result[$row['module_slug']][$row['metric']] = (int) $row['count'];
        }

        return $result;
    }

    private static function table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'upcore_stats';
    }
}
