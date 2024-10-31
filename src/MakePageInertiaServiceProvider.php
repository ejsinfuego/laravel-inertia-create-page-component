<?php

namespace Ejs\MakePage;

use Illuminate\Support\ServiceProvider;
use Ejs\MakePage\Console\Commands\MakePageInertia; // Ensure this class exists in the specified namespace

class MakePageInertiaServiceProvider extends ServiceProvider
{
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      $this->commands([
        MakePageInertia::class,
      ]);
    }
  }

  public function register()
  {
    //
  }
}
