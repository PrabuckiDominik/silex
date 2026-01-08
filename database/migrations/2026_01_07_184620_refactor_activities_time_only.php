<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedInteger('time')->default(0)->after('distance'); // nowa kolumna
            $table->dropColumn(['started_at', 'ended_at']); // usuwamy stare kolumny
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('user_id');
            $table->timestamp('ended_at')->nullable()->after('started_at');
            $table->dropColumn('time');
        });
    }
};
