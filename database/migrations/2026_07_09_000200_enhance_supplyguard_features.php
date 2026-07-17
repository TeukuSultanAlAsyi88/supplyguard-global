<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom dan tabel pendukung SupplyGuard.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pengayaan data cuaca
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('weather_data')) {
            $addApparentTemperature =
                !Schema::hasColumn(
                    'weather_data',
                    'apparent_temperature'
                );

            $addHumidity =
                !Schema::hasColumn(
                    'weather_data',
                    'humidity'
                );

            $addWindGust =
                !Schema::hasColumn(
                    'weather_data',
                    'wind_gust'
                );

            $addPrecipitationProbability =
                !Schema::hasColumn(
                    'weather_data',
                    'precipitation_probability'
                );

            $addCondition =
                !Schema::hasColumn(
                    'weather_data',
                    'condition'
                );

            $addIsDay =
                !Schema::hasColumn(
                    'weather_data',
                    'is_day'
                );

            Schema::table(
                'weather_data',
                function (Blueprint $table) use (
                    $addApparentTemperature,
                    $addHumidity,
                    $addWindGust,
                    $addPrecipitationProbability,
                    $addCondition,
                    $addIsDay
                ) {
                    if ($addApparentTemperature) {
                        $table
                            ->decimal(
                                'apparent_temperature',
                                8,
                                2
                            )
                            ->nullable()
                            ->after('temperature');
                    }

                    if ($addHumidity) {
                        $table
                            ->decimal(
                                'humidity',
                                8,
                                2
                            )
                            ->nullable()
                            ->after(
                                'apparent_temperature'
                            );
                    }

                    if ($addPrecipitationProbability) {
                        $table
                            ->decimal(
                                'precipitation_probability',
                                8,
                                2
                            )
                            ->nullable()
                            ->after('precipitation');
                    }

                    if ($addWindGust) {
                        $table
                            ->decimal(
                                'wind_gust',
                                8,
                                2
                            )
                            ->nullable()
                            ->after('wind_speed');
                    }

                    if ($addCondition) {
                        $table
                            ->string('condition')
                            ->nullable()
                            ->after('weather_code');
                    }

                    if ($addIsDay) {
                        $table
                            ->boolean('is_day')
                            ->nullable()
                            ->after('condition');
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pengayaan data nilai tukar
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('currency_rates')) {
            $addRateDate =
                !Schema::hasColumn(
                    'currency_rates',
                    'rate_date'
                );

            $addSource =
                !Schema::hasColumn(
                    'currency_rates',
                    'source'
                );

            Schema::table(
                'currency_rates',
                function (Blueprint $table) use (
                    $addRateDate,
                    $addSource
                ) {
                    if ($addRateDate) {
                        $table
                            ->date('rate_date')
                            ->nullable()
                            ->after('recorded_at');
                    }

                    if ($addSource) {
                        $table
                            ->string('source')
                            ->default(
                                'ExchangeRate-API'
                            )
                            ->after('rate_date');
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pengayaan data pelabuhan
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('ports')) {
            $addWpiNumber =
                !Schema::hasColumn(
                    'ports',
                    'wpi_number'
                );

            $addHarborSize =
                !Schema::hasColumn(
                    'ports',
                    'harbor_size'
                );

            $addHarborType =
                !Schema::hasColumn(
                    'ports',
                    'harbor_type'
                );

            $addDataSource =
                !Schema::hasColumn(
                    'ports',
                    'data_source'
                );

            $addImportedAt =
                !Schema::hasColumn(
                    'ports',
                    'imported_at'
                );

            Schema::table(
                'ports',
                function (Blueprint $table) use (
                    $addWpiNumber,
                    $addHarborSize,
                    $addHarborType,
                    $addDataSource,
                    $addImportedAt
                ) {
                    if ($addWpiNumber) {
                        $table
                            ->string('wpi_number')
                            ->nullable()
                            ->index()
                            ->after('unlocode');
                    }

                    if ($addHarborSize) {
                        $table
                            ->string('harbor_size')
                            ->nullable()
                            ->after('port_type');
                    }

                    if ($addHarborType) {
                        $table
                            ->string('harbor_type')
                            ->nullable()
                            ->after('harbor_size');
                    }

                    if ($addDataSource) {
                        $table
                            ->string('data_source')
                            ->default('Manual')
                            ->after('status');
                    }

                    if ($addImportedAt) {
                        $table
                            ->timestamp('imported_at')
                            ->nullable()
                            ->after('data_source');
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Bahasa berita
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('news_cache')
            && !Schema::hasColumn(
                'news_cache',
                'language'
            )
        ) {
            Schema::table(
                'news_cache',
                function (Blueprint $table) {
                    $table
                        ->string(
                            'language',
                            10
                        )
                        ->default('en')
                        ->after('query');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Riwayat proses impor World Port Index
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasTable(
                'port_import_batches'
            )
        ) {
            Schema::create(
                'port_import_batches',
                function (Blueprint $table) {
                    $table->id();

                    $table
                        ->foreignId('user_id')
                        ->nullable()
                        ->constrained()
                        ->nullOnDelete();

                    $table->string('filename');

                    $table
                        ->string('source')
                        ->default(
                            'World Port Index'
                        );

                    $table
                        ->unsignedInteger(
                            'total_rows'
                        )
                        ->default(0);

                    $table
                        ->unsignedInteger(
                            'imported_rows'
                        )
                        ->default(0);

                    $table
                        ->unsignedInteger(
                            'skipped_rows'
                        )
                        ->default(0);

                    $table
                        ->text('notes')
                        ->nullable();

                    $table->timestamps();

                    $table->index('source');
                    $table->index('created_at');
                }
            );
        }
    }

    /**
     * Membatalkan perubahan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'port_import_batches'
        );

        if (
            Schema::hasTable('news_cache')
            && Schema::hasColumn(
                'news_cache',
                'language'
            )
        ) {
            Schema::table(
                'news_cache',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'language'
                    );
                }
            );
        }

        if (Schema::hasTable('ports')) {
            $portColumns = collect([
                'wpi_number',
                'harbor_size',
                'harbor_type',
                'data_source',
                'imported_at',
            ])
                ->filter(
                    fn (string $column): bool =>
                        Schema::hasColumn(
                            'ports',
                            $column
                        )
                )
                ->values()
                ->all();

            if (!empty($portColumns)) {
                Schema::table(
                    'ports',
                    function (
                        Blueprint $table
                    ) use ($portColumns) {
                        $table->dropColumn(
                            $portColumns
                        );
                    }
                );
            }
        }

        if (
            Schema::hasTable(
                'currency_rates'
            )
        ) {
            $currencyColumns = collect([
                'rate_date',
                'source',
            ])
                ->filter(
                    fn (string $column): bool =>
                        Schema::hasColumn(
                            'currency_rates',
                            $column
                        )
                )
                ->values()
                ->all();

            if (!empty($currencyColumns)) {
                Schema::table(
                    'currency_rates',
                    function (
                        Blueprint $table
                    ) use ($currencyColumns) {
                        $table->dropColumn(
                            $currencyColumns
                        );
                    }
                );
            }
        }

        if (
            Schema::hasTable(
                'weather_data'
            )
        ) {
            $weatherColumns = collect([
                'apparent_temperature',
                'humidity',
                'wind_gust',
                'precipitation_probability',
                'condition',
                'is_day',
            ])
                ->filter(
                    fn (string $column): bool =>
                        Schema::hasColumn(
                            'weather_data',
                            $column
                        )
                )
                ->values()
                ->all();

            if (!empty($weatherColumns)) {
                Schema::table(
                    'weather_data',
                    function (
                        Blueprint $table
                    ) use ($weatherColumns) {
                        $table->dropColumn(
                            $weatherColumns
                        );
                    }
                );
            }
        }
    }
};