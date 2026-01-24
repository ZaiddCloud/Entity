<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('project:seed-realistic {--count=10}', function ($count = 10) {
    // Manually instance and call the command class logic if needed, or just rely on proper registration.
    // Since auto-discovery is failing, let's try to resolve it.
    // Actually, let's just instantiate the class and run its handle method if it extends Command.
    // But better, let's try to register it cleanly.
    
    $command = new \App\Console\Commands\SeedRealisticData();
    $command->setLaravel(app());
    $input = new \Symfony\Component\Console\Input\ArrayInput(['--count' => $count]);
    $output = new \Symfony\Component\Console\Output\ConsoleOutput();
    $command->run($input, $output);

})->purpose('Seed realistic data manually forced');
