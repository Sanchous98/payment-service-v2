<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('payments:subscription:execute-scheduled-payments')
    ->everyMinute()
    ->description('Execute scheduled payments')
    ->evenInMaintenanceMode()
    ->withoutOverlapping();
