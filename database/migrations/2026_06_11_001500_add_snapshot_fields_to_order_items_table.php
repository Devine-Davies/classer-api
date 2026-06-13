<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'sku_snapshot')) {
                $table->string('sku_snapshot', 64)->nullable()->after('catalog_item_id');
            }

            if (! Schema::hasColumn('order_items', 'name_snapshot')) {
                $table->string('name_snapshot')->nullable()->after('sku_snapshot');
            }
        });

        $catalogByUid = DB::table('catalog_items')
            ->select(['uid', 'sku', 'title'])
            ->get()
            ->keyBy('uid');

        DB::table('order_items')->orderBy('id')->chunkById(200, function ($rows) use ($catalogByUid) {
            foreach ($rows as $row) {
                $catalog = $catalogByUid->get($row->catalog_item_id);

                $skuSnapshot = $catalog->sku ?? null;
                $nameSnapshot = $catalog->title ?? null;

                if (! $nameSnapshot && Schema::hasColumn('order_items', 'product_name')) {
                    $nameSnapshot = $row->product_name;
                }

                DB::table('order_items')
                    ->where('id', $row->id)
                    ->update([
                        'sku_snapshot' => $skuSnapshot ?: 'UNKNOWN',
                        'name_snapshot' => $nameSnapshot ?: 'Product',
                    ]);
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_name')) {
                $table->dropColumn('product_name');
            }

            if (Schema::hasColumn('order_items', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('catalog_item_id');
            }

            if (! Schema::hasColumn('order_items', 'currency')) {
                $table->string('currency', 3)->default('gbp')->after('line_amount');
            }
        });

        DB::table('order_items')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('order_items')
                    ->where('id', $row->id)
                    ->update([
                        'product_name' => $row->name_snapshot ?? 'Product',
                    ]);
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'sku_snapshot')) {
                $table->dropColumn('sku_snapshot');
            }

            if (Schema::hasColumn('order_items', 'name_snapshot')) {
                $table->dropColumn('name_snapshot');
            }
        });
    }
};
