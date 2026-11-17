<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

#[Signature('demo:seed {--force : Run without confirmation in production}')]
#[Description('Rebuild the database with the deterministic demo data and admin account')]
class DemoSeedCommand extends Command
{
    use ConfirmableTrait;

    public function handle(): int
    {
        if (! $this->confirmToProceed('This wipes every table and reseeds the demo data.')) {
            return self::FAILURE;
        }

        $this->components->task('Rebuilding database', fn () => $this->callSilently('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]) === self::SUCCESS);

        $this->components->info(sprintf(
            'Demo ready. Log in as %s / %s',
            config('demo.admin.email'),
            app()->isProduction() ? '(ADMIN_PASSWORD)' : config('demo.admin.password'),
        ));

        return self::SUCCESS;
    }
}
