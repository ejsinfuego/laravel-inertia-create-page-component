<?php

namespace Ejs\MakePage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class MakePageInertia extends Command implements PromptsForMissingInput
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:page {name} {--language} {--layout=} {--route=}';



  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new React page component with Inertia';

  /**
   * Execute the console command.
   */
  public function handle()
  {

    $name = $this->argument('name');
    $language = $this->askForLanguage();
    $layout = $this->choice('What is the layout of the page?', $this->getLayouts(), 0);
    $route = $this->option('route') ?? strtolower($name);
    $name = $this->removeWhiteSpace($name);
    // Create directories if they don't exist
    $pageDir = resource_path('js/Pages/' . $this->getDirectoryPath($name));
    File::makeDirectory($pageDir, 0755, true, true);

    // Generate React component
    $this->createReactComponent($name, $layout, $language);

    // Add route if requested
    if ($this->option('route')) {
      $this->addRoute($name, $route);
    }

    $this->info('React page created successfully!');
  }

  private function createReactComponent($name, $layout, $language)
  {
    $template = <<<EOT
import React from 'react';
import {$layout} from '@/Layouts/{$layout}';
import { Head } from '@inertiajs/react';

export default function {$name}() {
    return (
        <>
            <Head title="{$name}" />
            <{$layout}>
                <div className="py-12">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                {$name} Content
                            </div>
                        </div>
                    </div>
                </div>
            </{$layout}>
        </>
    );
}
EOT;

    $path = resource_path("js/Pages/{$name}.{$language}");
    File::put($path, $template);
  }

  private function addRoute($name, $route)
  {
    $routeTemplate = <<<EOT

Route::get('/{$route}', function () {
    return Inertia::render('{$name}');
})->name('{$route}');
EOT;

    $webRoutePath = base_path('routes/web.php');
    File::append($webRoutePath, $routeTemplate);
  }

  private function getDirectoryPath($name)
  {
    $parts = explode('/', $name);
    array_pop($parts);
    return implode('/', $parts);
  }

  public function askForLanguage()
  {
    $language = $this->choice('What is your preferred language?', ['jsx', 'tsx'], 0);

    return $language;
  }

  protected function promptForMissingArgumentsUsing(): array
  {
    return [
      'name' => function () {
        return $this->ask('What is the name of the page?');
      },
      'layout' => function () {
        return $this->ask('What is the layout of the page?');
      },
      'language' => function () {
        return $this->ask('What is your preferred language?');
      },
    ];
  }

  protected function removeWhiteSpace($string)
  {
    return str_replace(' ', '', $string);
  }

  protected function getLayouts(): array
  {
    // Get all layouts from the Layouts directory
    $layouts = File::files(resource_path('js/Layouts'));
    return array_map(function ($layout) {
      return pathinfo($layout, PATHINFO_FILENAME);
    }, $layouts);
  }
}
