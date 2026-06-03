<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $max = max(1, (int) config('creator.profile.bio_max_length', 300));

        DB::table('profiles')
            ->whereNotNull('bio')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row) use ($max): void {
                $bio = (string) $row->bio;
                if (mb_strlen($bio) <= $max) {
                    return;
                }

                DB::table('profiles')
                    ->where('id', $row->id)
                    ->update(['bio' => mb_substr($bio, 0, $max)]);
            });

        Schema::table('profiles', function (Blueprint $table) use ($max): void {
            $table->string('bio', $max)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->text('bio')->nullable()->change();
        });
    }
};
