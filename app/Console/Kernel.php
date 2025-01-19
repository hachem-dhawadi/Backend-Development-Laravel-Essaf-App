<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Nexmo\Laravel\Facade\Nexmo;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\SendEmail;
use App\Models\Payement;
use Illuminate\Support\Facades\Mail;
use App\Models\Payment;



class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () { 
            $currentDateTime = now()->format('Y-m-d H:i:s');

            $upcomingReservations = Reservation::with('client')
                ->whereNotNull('user_id')
                ->whereRaw("DATE_SUB(date, INTERVAL TIME_TO_SEC(notification) SECOND) = '$currentDateTime'")
                ->get();

            // $upcomingReservations = Reservation::with('client')
            //     ->where('date', $currentDateTime)
            //     ->get();

            echo "reservation date: " . $currentDateTime . "\n";
            //$processedReservations = collect(); // Store processed reservations

            foreach ($upcomingReservations as $reservation) {
                // $notificationDateTime = $currentDateTime->sub($reservation->notification);


                echo "Notification time: " . $currentDateTime . "\n";
               // if (!$processedReservations->contains('id', $reservation->id)) {
                    // Access and display specific attributes.
                    $adresse = $reservation->room->service->adresse;
                    $service=$reservation->room->service->name;
                    $room=$reservation->room->name;
                    echo "reservation ID: " . $reservation->id . "\n";
                    echo "client ID: " . $reservation->client->id . "\n";
                    echo "client name: " . $reservation->client->name . "\n";
                    echo "client phone: " . $reservation->client->phone . "\n";
                    $phone = '+216' . $reservation->client->phone;
                    $name = $reservation->client->name;
                    $date= $reservation->date;
                    echo " phone:" . $phone . "\n";

                    Nexmo::message()->send([
                        'to' => $phone,
                        'from' => 'E-Saff APP',
                        'text' =>  " Dear $name This is an AI E-Saff reminder, your turn for $service , $room , on $adresse at $date is coming up soon. Please make sure to arrive on time and have any necessary documents or information with you. Thank you!"
                        // "This is an AI E-Saff reminder, your turn for [service] at [location] is coming up soon. Please make sure to arrive on time and have any necessary documents or information with you. Thank you!"
                    ]);

                    // Mail::to($reservation->client->email)->send(
                    //     new SendEmail('welcome to Esaaf app your turn')
                    // );

                  //  $processedReservations->push($reservation); // Add to processed reservations
               // }
            }


            //delete reservation
            // $currentDateTime = now();
            $currentDateTime = now()->subHour();
            //$currentDateTime = now()->subMinute(); 
            // Delete reservations with date older than current date
            Reservation::where('date', '<', $currentDateTime)->delete();

            //Delete payement
            //$thirtyDaysAgo = now()->subMinute();
            $thirtyDaysAgo = now()->subDays(30);
            echo "30 days: " . $thirtyDaysAgo . "\n";
            Payement::where('created_at', '<=', $thirtyDaysAgo)->delete();


        })->everyMinute();

        

    }






    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
