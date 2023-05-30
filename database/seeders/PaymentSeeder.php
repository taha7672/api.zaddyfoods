<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

class PaymentSeeder extends Seeder
{
    use Loggable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $payments = [
            ['tag' => 'stripe', 'input' => 1],
            ['tag' => 'razorpay'],
            ['tag' => 'mercado-pago'],
            ['tag' => 'paystack'],
            ['tag' => 'flutterWave'],
            ['tag' => 'paytabs'],
            ['tag' => 'cash'],
            ['tag' => 'wallet'],
        ];

        foreach ($payments as $payment) {
            try {
                Payment::updateOrCreate([
                    'tag' => data_get($payment, 'tag')
                ], $payment);
            } catch (Throwable $e) {
                $this->error($e);
            }
        }

    }

}
