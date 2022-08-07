<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use FFMpeg\Format\Video\X264;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSVideoFilters;

class ProcessVideoUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vdo:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert video mp4 to m3u8';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $lowFormat = (new X264('aac'))->setKiloBitrate(500);
        $hightFormat = (new X264('aac'))->setKiloBitrate(1000);
        $this->info('Converting video..');
        $name_vdo = readline('Enter a name video: ');
        $folder_generate = $name_vdo;
        FFMpeg::fromDisk('uploads')
            ->open("${name_vdo}.mp4")
            ->exportForHLS()
            // generate key
            // ->withRotatingEncryptionKey(function($filename, $contents) use($folder_generate){
            //     Storage::disk('public')->put("{$folder_generate}/key/{$filename}", $contents);
            // })
            // add quanlity videos
            ->addFormat($lowFormat, function(HLSVideoFilters $filter){
                $filter->resize(848, 480);
            })
            // ->addFormat($hightFormat)
            // show progress
            ->onProgress(function($progress){
                $this->info("Progress: {$progress}%");
            })
            ->toDisk('public')
            ->save("{$folder_generate}/{$name_vdo}.m3u8");
        $this->info('Done !');
    }
}
