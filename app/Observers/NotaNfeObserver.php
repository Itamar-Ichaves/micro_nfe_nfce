<?php

namespace App\Observers;

use App\Models\NotaNfe;
use Illuminate\Support\Str;

class NotaNfeObserver
{ 
    public function creating(NotaNfe $client)
    {
       
        $client->id = (string) Str::uuid();
       
    }
    /**
     * Handle the plan "updating" event.
     *
     * @param  \App\Models\NotaNfe   
     * @return void
     */
    public function updating(NotaNfe $client)
    {
    }
}
