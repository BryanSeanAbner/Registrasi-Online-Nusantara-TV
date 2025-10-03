<?php

namespace App\Console\Commands;

use App\Models\Seat;
use Illuminate\Console\Command;

class GenerateSeats extends Command
{
    protected $signature = 'seats:generate {event_id} {--section=Main} {--rows=A-J} {--cols=1-20}';
    protected $description = 'Generate seats grid for an event';

    public function handle(): int
    {
        $eventId = (int) $this->argument('event_id');
        [$rowStart, $rowEnd] = explode('-', $this->option('rows'));
        [$colStart, $colEnd] = explode('-', $this->option('cols'));
        $section = $this->option('section');

        for ($r = ord($rowStart); $r <= ord($rowEnd); $r++) {
            $rowLabel = chr($r);
            for ($c = (int)$colStart; $c <= (int)$colEnd; $c++) {
                $label = "{$rowLabel}-{$c}";
                Seat::firstOrCreate(
                    ['event_id'=>$eventId,'label'=>$label],
                    ['section'=>$section,'row'=>$rowLabel,'col'=>$c,'type'=>'regular','status'=>'available']
                );
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
