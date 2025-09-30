<?php

namespace Database\Seeders;

use App\Models\{User,Event,Registration}; 
use Illuminate\Support\Str;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $event = Event::firstOrCreate(['slug'=>'askara-demo'], [
            'title'=>'Askara Demo Day', 'starts_at'=>now()->addDays(7), 'venue'=>'Jakarta',
            'brand'=>['primary'=>'#111','logo'=>null]
        ]);
        for($i=1;$i<=5;$i++){
            Registration::firstOrCreate(
                ['qr_code'=>Str::ulid()], 
                [
                    'event_id'=>$event->id,'name'=>'Guest '.$i, 'phone'=>'08xxxxxxxxxx'
                ]
            );
        }
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
