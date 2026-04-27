<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscriber;
use Illuminate\Support\Facades\DB;

class UpdateSubscriberCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscribers:update-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update registration counts for all subscribers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating subscriber registration counts...');
        
        // Obtener todos los emails únicos
        $uniqueEmails = Subscriber::distinct('email')->pluck('email');
        
        $bar = $this->output->createProgressBar($uniqueEmails->count());
        
        foreach ($uniqueEmails as $email) {
            $count = Subscriber::where('email', $email)->count();
            Subscriber::where('email', $email)->update(['registration_count' => $count]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Registration counts updated successfully!');
        
        return Command::SUCCESS;
    }
}