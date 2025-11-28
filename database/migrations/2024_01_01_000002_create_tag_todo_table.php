<?php

use App\Models\Tag;
use App\Models\Todo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_todo', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Todo::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tag::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['todo_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_todo');
    }
};

