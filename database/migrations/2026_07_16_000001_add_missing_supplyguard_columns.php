<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Kolom tambahan weather_data
        |--------------------------------------------------------------------------
        */

        $missingWeatherColumns = [
            'apparent_temperature' =>
                !Schema::hasColumn('weather_data', 'apparent_temperature'),

            'humidity' =>
                !Schema::hasColumn('weather_data', 'humidity'),

            'precipitation_probability' =>
                !Schema::hasColumn('weather_data', 'precipitation_probability'),

            'wind_gust' =>
                !Schema::hasColumn('weather_data', 'wind_gust'),

            'condition' =>
                !Schema::hasColumn('weather_data', 'condition'),

            'is_day' =>
                !Schema::hasColumn('weather_data', 'is_day'),
        ];

        Schema::table('weather_data', function (Blueprint $table) use ($missingWeatherColumns) {
            if ($missingWeatherColumns['apparent_temperature']) {
                $table->decimal('apparent_temperature', 8, 2)
                    ->nullable()
                    ->after('temperature');
            }

            if ($missingWeatherColumns['humidity']) {
                $table->decimal('humidity', 8, 2)
                    ->nullable()
                    ->after('apparent_temperature');
            }

            if ($missingWeatherColumns['precipitation_probability']) {
                $table->decimal('precipitation_probability', 8, 2)
                    ->nullable()
                    ->after('precipitation');
            }

            if ($missingWeatherColumns['wind_gust']) {
                $table->decimal('wind_gust', 8, 2)
                    ->nullable()
                    ->after('wind_speed');
            }

            if ($missingWeatherColumns['condition']) {
                $table->string('condition')
                    ->nullable()
                    ->after('weather_code');
            }

            if ($missingWeatherColumns['is_day']) {
                $table->boolean('is_day')
                    ->nullable()
                    ->after('condition');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Kolom tambahan ports
        |--------------------------------------------------------------------------
        */

        $missingPortColumns = [
            'wpi_number' =>
                !Schema::hasColumn('ports', 'wpi_number'),

            'harbor_size' =>
                !Schema::hasColumn('ports', 'harbor_size'),

            'harbor_type' =>
                !Schema::hasColumn('ports', 'harbor_type'),

            'data_source' =>
                !Schema::hasColumn('ports', 'data_source'),

            'imported_at' =>
                !Schema::hasColumn('ports', 'imported_at'),
        ];

        Schema::table('ports', function (Blueprint $table) use ($missingPortColumns) {
            if ($missingPortColumns['wpi_number']) {
                $table->string('wpi_number')
                    ->nullable()
                    ->after('unlocode');

                $table->index('wpi_number');
            }

            if ($missingPortColumns['harbor_size']) {
                $table->string('harbor_size')
                    ->nullable()
                    ->after('port_type');
            }

            if ($missingPortColumns['harbor_type']) {
                $table->string('harbor_type')
                    ->nullable()
                    ->after('harbor_size');
            }

            if ($missingPortColumns['data_source']) {
                $table->string('data_source')
                    ->nullable()
                    ->after('status');
            }

            if ($missingPortColumns['imported_at']) {
                $table->timestamp('imported_at')
                    ->nullable()
                    ->after('data_source');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Kolom tambahan news_cache
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('news_cache', 'language')) {
            Schema::table('news_cache', function (Blueprint $table) {
                $table->string('language', 10)
                    ->nullable()
                    ->after('query');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('news_cache')) {
            Schema::table('news_cache', function (Blueprint $table) {
                if (Schema::hasColumn('news_cache', 'language')) {
                    $table->dropColumn('language');
                }
            });
        }

        if (Schema::hasTable('ports')) {
            Schema::table('ports', function (Blueprint $table) {
                $columns = [
                    'wpi_number',
                    'harbor_size',
                    'harbor_type',
                    'data_source',
                    'imported_at',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('ports', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('weather_data')) {
            Schema::table('weather_data', function (Blueprint $table) {
                $columns = [
                    'apparent_temperature',
                    'humidity',
                    'precipitation_probability',
                    'wind_gust',
                    'condition',
                    'is_day',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('weather_data', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};