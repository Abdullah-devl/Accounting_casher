<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. info (Company Info)
        Schema::create('info', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->string('NAMEAR', 500)->nullable();
            $table->string('NAMEEN', 500)->nullable();
            $table->string('FON', 500)->nullable();
            $table->string('MOBILE', 500)->nullable();
            $table->text('ADDRES')->nullable();
            $table->text('ADDRES2')->nullable();
            $table->string('CT', 500)->nullable();
            $table->string('VAT', 500)->nullable();
            $table->string('MAIL', 500)->nullable();
            $table->string('WEB', 500)->nullable();
            $table->binary('img')->nullable();
            $table->string('PATH', 500)->nullable();
        });

        // 2. CURRNECY
        Schema::create('CURRNECY', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAME', 500);
            $table->string('PART_NAME', 500)->nullable();
            $table->double('CURRENCY_VAL')->default(1.0);
        });

        // 3. CNEW000 (Currency Rates History)
        Schema::create('CNEW000', function (Blueprint $table) {
            $table->dateTime('DATE_');
            $table->string('GUID_CURRENCY', 128);
            $table->double('VAL1')->default(1.0);
            $table->primary(['DATE_', 'GUID_CURRENCY']);
        });

        // 4. ACCOUNT
        Schema::create('ACCOUNT', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->string('CODE', 128)->unique();
            $table->string('NAME', 500);
            $table->string('PARENT_GUID', 128)->nullable();
            $table->double('END_ACCOUNT')->default(0.0);
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->string('MOBILE', 500)->nullable();
            $table->boolean('FREEZ')->default(false);
            $table->integer('TYPE'); // 0 = main (no entries), 1 = sub (entries allowed)
            $table->double('DEBIT')->default(0.0); // opening debit
            $table->double('CREDIT')->default(0.0); // opening credit
            $table->boolean('APPLE')->default(false);
            $table->dateTime('DATEF')->nullable();
            $table->dateTime('DATET')->nullable();
        });

        // 5. CATEGORY (Groups/Categories)
        Schema::create('CATEGORY', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAMEAR', 500);
        });

        // 6. JOB (Cost Centers)
        Schema::create('JOB', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAMEAR', 500);
        });

        // 7. STORE (Warehouses/Stores)
        Schema::create('STORE', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAMEAR', 500);
        });

        // 8. ITEM
        Schema::create('ITEM', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAME', 500);
            $table->text('NOTE')->nullable();
            $table->string('GROUP_GUID', 128)->nullable();

            $table->string('barcode1', 500)->nullable();
            $table->string('UNITE1', 500)->nullable();
            $table->double('QTY1')->default(1.0);
            $table->double('COST1')->default(0.0);
            $table->double('PRICE1')->default(0.0);

            $table->string('barcode2', 500)->nullable();
            $table->string('UNITE2', 500)->nullable();
            $table->double('QTY2')->default(0.0);
            $table->double('COST2')->default(0.0);
            $table->double('PRICE2')->default(0.0);

            $table->string('barcode3', 500)->nullable();
            $table->string('UNITE3', 500)->nullable();
            $table->double('QTY3')->default(0.0);
            $table->double('COST3')->default(0.0);
            $table->double('PRICE3')->default(0.0);

            $table->integer('DEFULT_UNITE')->default(1);
            $table->dateTime('DATEP')->nullable();
            $table->dateTime('DATEE')->nullable();
            $table->integer('DAY_MEPER')->default(30);
            $table->double('QTY_MEPER')->default(0.0);
            $table->boolean('FREEZ')->default(false);
            $table->binary('IMG')->nullable();
            $table->string('GC', 500)->nullable();
            $table->double('VC')->default(1.0);
            $table->boolean('CT_PER')->default(true);
            $table->double('PER')->default(15.0);
        });

        // 9. TYPE_BILL (Invoice configurations)
        Schema::create('TYPE_BILL', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('CODE');
            $table->string('NAME', 500);
            $table->integer('NUMBER');
            $table->string('day_item', 128)->nullable();
            $table->string('day_disc', 128)->nullable();
            $table->string('cash_day', 128)->nullable();
            $table->string('cash_vat', 128)->nullable();
            $table->string('GUID_STORE', 128)->nullable();
            $table->string('GUID_JOB', 128)->nullable();
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->double('VAL_CURRENCY')->default(1.0);
            $table->integer('PAY')->default(0);
            $table->boolean('vat_activty')->default(true);
            $table->double('val_vat')->default(15.0);
            $table->string('ROW1_R', 500)->nullable();
            $table->string('ROW2_R', 500)->nullable();
            $table->integer('ROWS_C')->default(0);
            $table->boolean('SHOW_JOB')->default(false);
            $table->boolean('SHOW_CURRENCY')->default(false);
            $table->boolean('SHOW_VAT')->default(false);
            $table->boolean('SHOW_STORE')->default(false);
            $table->boolean('SHOW_DISES')->default(false);
            $table->boolean('ALLOW_DISES')->default(false);
            $table->boolean('TYPE')->default(false); // maps to POS boolean in VB.NET
        });

        // 10. TYPE_CASH (Receipt configurations)
        Schema::create('TYPE_CASH', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->string('NAME', 500);
            $table->integer('CODE');
            $table->integer('NUMBER');
            $table->string('GUID_JOB', 128)->nullable();
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->double('VAL_CURRENCY')->default(1.0);
            $table->string('ROW1_R', 500)->nullable();
            $table->string('ROW2_R', 500)->nullable();
            $table->integer('ROWS_C')->default(0);
            $table->boolean('SHOW_JOB')->default(false);
            $table->boolean('SHOW_CURRENCY')->default(false);
            $table->string('DAY_CASH', 128)->nullable();
        });

        // 11. BILL1 (Invoices Header)
        Schema::create('BILL1', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->integer('TYPE_NUMBER');
            $table->dateTime('DATE');
            $table->integer('TYPE_PAY')->default(0);
            $table->string('CUST', 500)->nullable();
            $table->text('NOTE')->nullable();
            $table->string('STORE_GUID', 128)->nullable();
            $table->string('GUID_JOB', 128)->nullable();
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->double('CURRENCY_VAL')->default(1.0);
            $table->double('TOT_DIS')->default(0.0);
            $table->double('TOT_VAT')->default(0.0);
            $table->double('DIS')->default(0.0);
            $table->double('TOT_FINLY')->default(0.0);
            $table->string('ACCOUNT', 128)->nullable();
            $table->string('GUID_BIIL', 128)->nullable();
            $table->integer('POS')->default(0);
            $table->boolean('WAIT')->default(false);
            $table->double('TOT_PAY')->default(0.0);
            $table->double('TOT_LEFT')->default(0.0);
        });

        // 12. BILL2 (Invoices Detail)
        Schema::create('BILL2', function (Blueprint $table) {
            $table->string('PARENT_GUID', 128);
            $table->string('GUID_ITEM', 128);
            $table->double('QTY');
            $table->double('PRICE');
            $table->string('UNITE', 500)->nullable();
            $table->double('QTY_UNITE')->default(1.0);
            $table->double('COST')->default(0.0);
            $table->double('EARN')->default(0.0);
            $table->double('DIS')->default(0.0);
            $table->double('VAT')->default(0.0);
            $table->double('TOTALS');
            $table->double('TOTALS_FINLY');
            
            $table->foreign('PARENT_GUID')->references('GUID')->on('BILL1')->onDelete('cascade');
        });

        // 13. DAY1 (Journal Entries Header)
        Schema::create('DAY1', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('TYPE_NUMBER');
            $table->integer('NUMBER');
            $table->dateTime('DATE');
            $table->text('NOTE')->nullable();
            $table->string('TYPE', 500)->nullable();
            $table->string('note_day', 500)->nullable();
            $table->string('GUID_JOB', 128)->nullable();
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->double('CURRENCY_VAL')->default(1.0);
        });

        // 14. DAY2 (Journal Entries Detail)
        Schema::create('DAY2', function (Blueprint $table) {
            $table->string('PARENT_GUID', 128);
            $table->string('ACCOUNT_GUID', 128);
            $table->double('DEBIT')->default(0.0);
            $table->double('CREDIT')->default(0.0);
            $table->text('NOTE')->nullable();
            $table->string('GUID_JOB', 128)->nullable();
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->double('CURRENCY_VAL')->default(1.0);
            $table->double('VAL_LOCALY')->default(0.0);

            $table->foreign('PARENT_GUID')->references('GUID')->on('DAY1')->onDelete('cascade');
        });

        // 15. CASH_DAY (Receipt/Payment Vouchers)
        Schema::create('CASH_DAY', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->integer('TYPE_NUMBER');
            $table->string('GUID_ACCOUNT', 128)->nullable();
            $table->text('NOTE')->nullable();
            $table->dateTime('DATE');
            $table->string('GUID_JOB', 128)->nullable();
            $table->string('GUID_CURRENCY', 128)->nullable();
            $table->double('CURRENCY_VAL')->default(1.0);
            $table->string('GUID_CUSTOMER', 128)->nullable();
            $table->double('VAL_VOCHERS')->default(0.0);
            $table->string('DIS_DEBIT', 500)->nullable();
            $table->string('DIS_CREDIT', 500)->nullable();
            $table->double('VAL_VOCHERS2')->default(0.0);
            $table->string('GUID_CURRENCY1', 128)->nullable();
            $table->double('CURRENCY_VAL1')->default(1.0);
        });

        // 16. TB_BACH (POS Shifts)
        Schema::create('TB_BACH', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->dateTime('DATE');
            $table->string('GUSER', 128)->nullable();
            $table->boolean('CP')->default(false); // false = open, true = closed
            $table->double('opening_cash')->nullable();
            $table->double('expected_cash')->nullable();
            $table->double('actual_cash')->nullable();
            $table->dateTime('closed_at')->nullable();
        });

        // 17. TB_BACH2 (Shift bills association)
        Schema::create('TB_BACH2', function (Blueprint $table) {
            $table->string('PARENTGUID', 128);
            $table->string('GUID_BILL', 128);
            $table->primary(['PARENTGUID', 'GUID_BILL']);
        });

        // 18. TB_POSES (POS devices config)
        Schema::create('TB_POSES', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAME', 500);
            $table->string('GUID_USER', 128)->nullable();
            $table->string('GUID_SALE', 128)->nullable();
            $table->string('GUID_RSALE', 128)->nullable();
            $table->string('ACCOUNT_USER', 128)->nullable();
            $table->boolean('FREEZ')->default(false);
            $table->boolean('PAS')->default(false);
            $table->string('PRINTER', 500)->nullable();
            $table->string('ACCOUNT_CASH', 128)->nullable();
        });

        // 19. TB_POSI (POS fast items)
        Schema::create('TB_POSI', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAME', 500);
            $table->string('GUIDI', 128)->nullable();
            $table->string('UNITE', 500)->nullable();
            $table->double('QTY')->default(0.0);
            $table->double('COST')->default(0.0);
            $table->double('PRICE')->default(0.0);
            $table->string('COLORB', 500)->nullable();
            $table->string('COLORF', 500)->nullable();
            $table->boolean('FREEZ')->default(false);
        });

        // 20. FILES_STORE
        Schema::create('FILES_STORE', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->dateTime('DATE');
            $table->integer('NUMBER');
            $table->string('NAME', 500);
            $table->text('NOTE')->nullable();
            $table->integer('DAY_MEMPER')->default(0);
            $table->integer('DAY_EXUSE')->default(0);
            $table->boolean('FREEZ')->default(false);
            $table->binary('FILES')->nullable();
            $table->string('EXT', 500)->nullable();
            $table->string('PATH', 500)->nullable();
            $table->dateTime('DATE_EX')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('FILES_STORE');
        Schema::dropIfExists('TB_POSI');
        Schema::dropIfExists('TB_POSES');
        Schema::dropIfExists('TB_BACH2');
        Schema::dropIfExists('TB_BACH');
        Schema::dropIfExists('CASH_DAY');
        Schema::dropIfExists('DAY2');
        Schema::dropIfExists('DAY1');
        Schema::dropIfExists('BILL2');
        Schema::dropIfExists('BILL1');
        Schema::dropIfExists('TYPE_CASH');
        Schema::dropIfExists('TYPE_BILL');
        Schema::dropIfExists('ITEM');
        Schema::dropIfExists('STORE');
        Schema::dropIfExists('JOB');
        Schema::dropIfExists('CATEGORY');
        Schema::dropIfExists('ACCOUNT');
        Schema::dropIfExists('CNEW000');
        Schema::dropIfExists('CURRNECY');
        Schema::dropIfExists('info');
    }
};
