<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weather_data', function (Blueprint $table) {
            $table->decimal('apparent_temperature', 8, 2)->nullable()->after('temperature');
            $table->decimal('humidity', 8, 2)->nullable()->after('apparent_temperature');
            $table->decimal('wind_gust', 8, 2)->nullable()->after('wind_speed');
            $table->decimal('precipitation_probability', 8, 2)->nullable()->after('precipitation');
            $table->string('condition')->nullable()->after('weather_code');
            $table->boolean('is_day')->nullable()->after('condition');
        });

        Schema::table('currency_rates', function (Blueprint $table) {
            $table->date('rate_date')->nullable()->after('recorded_at');
            $table->string('source')->default('ExchangeRate-API')->after('rate_date');
        });

        Schema::table('ports', function (Blueprint $table) {
            $table->string('wpi_number')->nullable()->index()->after('unlocode');
            $table->string('harbor_size')->nullable()->after('port_type');
            $table->string('harbor_type')->nullable()->after('harbor_size');
            $table->string('data_source')->default('Manual')->after('status');
            $table->timestamp('imported_at')->nullable()->after('data_source');
        });

        Schema::table('news_cache', function (Blueprint $table) {
            $table->string('language', 10)->default('en')->after('query');
        });

        Schema::create('port_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename');
            $table->string('source')->default('World Port Index');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('port_import_batches');

        Schema::table('news_cache', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn(['wpi_number', 'harbor_size', 'harbor_type', 'data_source', 'imported_at']);
        });

        Schema::table('currency_rates', function (Blueprint $table) {
            $table->dropColumn(['rate_date', 'source']);
        });

        Schema::table('weather_data', function (Blueprint $table) {
            $table->dropColumn([
                'apparent_temperature',
                'humidity',
                'wind_gust',
                'precipitation_probability',
                'condition',
                'is_day',
            ]);
        });
    }
};
