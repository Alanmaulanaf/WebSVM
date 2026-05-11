<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediksi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('suhu_c', 5, 1);
            $table->enum('warna', ['tidak berwarna','berwarna']);
            $table->enum('bau',   ['tidak berbau','berbau']);
            $table->enum('rasa',  ['tawar','tidak berasa','asam','manis']);

            $table->enum('label_prediksi', ['ya','tidak']);
            $table->float('prob_ya'); 
            $table->boolean('sesuai_permenkes_proxy')->default(false);
            $table->string('versi_model', 50)->default('svm_rbf_v1');

            $table->timestamp('diprediksi_pada')->useCurrent();
            $table->enum('sumber', ['manual','import'])->default('manual');

            $table->index(['user_id']);
            $table->index(['warna','bau','rasa','suhu_c']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi');
    }
};
