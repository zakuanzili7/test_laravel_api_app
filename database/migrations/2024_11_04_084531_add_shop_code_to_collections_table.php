<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('collections', function (Blueprint $table) {
            // Add the shop_code column before the code column
            $table->string('shop_code')->nullable()->after('id');

            // Create foreign key constraint
            $table->foreign('shop_code')
                ->references('shop_code')
                ->on('shops')
                ->onDelete('set null'); // Change to desired action (e.g., 'cascade', 'set null')
        });
    }

    public function down()
    {
        Schema::table('collections', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['shop_code']);
            
            // Drop the shop_code column
            $table->dropColumn('shop_code');
        });
    }
};
